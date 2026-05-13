<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\Lead;
use App\Rules\ThaiTaxId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Customer::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Customer::query()->latest();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        return CustomerResource::collection($query->paginate($filters['per_page'] ?? 15));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Customer::class);

        $data = $this->validatedData($request);
        $this->authorizeLeadAccess($data['lead_id'] ?? null);

        $customer = new Customer($data);
        $customer->user_id = $request->user()->id;
        $customer->save();

        return (new CustomerResource($customer))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Customer $customer): CustomerResource
    {
        $this->authorize('view', $customer);

        return new CustomerResource($customer);
    }

    public function update(Request $request, Customer $customer): CustomerResource
    {
        $this->authorize('update', $customer);

        $data = $this->validatedData($request);
        $this->authorizeLeadAccess($data['lead_id'] ?? null);
        $customer->update($data);

        return new CustomerResource($customer->refresh());
    }

    public function destroy(Customer $customer): Response
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return response()->noContent();
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'tax_id' => ['nullable', 'digits:13', new ThaiTaxId],
            'branch' => ['nullable', 'string', 'max:255'],
            'lead_id' => ['nullable', Rule::exists(Lead::class, 'id')],
        ]);

        $data['tax_id'] = ! empty($data['tax_id'])
            ? preg_replace('/\D/', '', (string) $data['tax_id'])
            : null;

        return $data;
    }

    private function authorizeLeadAccess(?int $leadId): void
    {
        if ($leadId === null) {
            return;
        }

        $lead = Lead::findOrFail($leadId);
        $this->authorize('view', $lead);
    }
}
