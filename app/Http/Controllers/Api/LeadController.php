<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeadResource;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Lead::class);

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['New', 'Contacted', 'Qualified', 'Lost', 'Won'])],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Lead::query()->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        return LeadResource::collection($query->paginate($filters['per_page'] ?? 15));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Lead::class);

        $data = $this->validatedData($request);
        $ownerId = $request->user()->hasRole('Admin')
            ? ($data['assigned_to'] ?? $request->user()->id)
            : $request->user()->id;
        unset($data['assigned_to']);

        $lead = new Lead($data);
        $lead->user_id = $ownerId;
        $lead->save();

        return (new LeadResource($lead))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Lead $lead): LeadResource
    {
        $this->authorize('view', $lead);

        return new LeadResource($lead);
    }

    public function update(Request $request, Lead $lead): LeadResource
    {
        $this->authorize('update', $lead);

        $data = $this->validatedData($request);
        if ($request->user()->hasRole('Admin') && array_key_exists('assigned_to', $data)) {
            $lead->user_id = $data['assigned_to'] ?? $request->user()->id;
        }
        unset($data['assigned_to']);

        $lead->update($data);

        return new LeadResource($lead->refresh());
    }

    public function destroy(Lead $lead): Response
    {
        $this->authorize('delete', $lead);

        $lead->delete();

        return response()->noContent();
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['New', 'Contacted', 'Qualified', 'Lost', 'Won'])],
            'source' => ['nullable', 'string', 'max:100'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'assigned_to' => ['nullable', Rule::exists(User::class, 'id')],
        ]);
    }
}
