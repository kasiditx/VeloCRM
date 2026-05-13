<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'customer_id' => $this->customer_id,
            'tax_id' => $this->tax_id,
            'branch' => $this->branch,
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'subtotal' => (float) $this->subtotal,
            'tax_total' => (float) $this->tax_total,
            'discount' => (float) $this->discount,
            'total' => (float) $this->total,
            'amount_paid' => (float) $this->amount_paid,
            'balance_due' => (float) $this->balance_due,
            'status' => $this->status,
            'currency' => $this->currency,
            'exchange_rate' => (float) $this->exchange_rate,
            'notes' => $this->notes,
            'is_recurring' => (bool) $this->is_recurring,
            'recurring_cycle' => $this->recurring_cycle,
            'next_recurring_date' => $this->next_recurring_date,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
