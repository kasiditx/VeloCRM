<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function checkout(string $token, PaymentGatewayManager $gateways): RedirectResponse
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_token', $token)
            ->firstOrFail();

        if ((float) $invoice->balance_due <= 0) {
            return redirect()
                ->route('public.invoice.show', $invoice->public_token)
                ->with('success', __('This invoice is already paid.'));
        }

        $returnUrl = route('public.invoice.show', $invoice->public_token);
        $checkout = $gateways
            ->driver()
            ->createCheckout($invoice, $returnUrl, $returnUrl);

        if (! $checkout->available || ! $checkout->redirectUrl) {
            return redirect()
                ->route('public.invoice.show', $invoice->public_token)
                ->with('error', $checkout->message ?: __('Payment gateway is not configured yet.'));
        }

        return redirect()->away($checkout->redirectUrl);
    }

    public function confirmTransfer(string $token): RedirectResponse
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_token', $token)
            ->firstOrFail();

        if ((float) $invoice->balance_due <= 0) {
            return redirect()
                ->route('public.invoice.show', $invoice->public_token)
                ->with('success', __('This invoice is already paid.'));
        }

        $invoice->payments()->firstOrCreate(
            [
                'gateway' => 'manual',
                'status' => 'pending',
            ],
            [
                'amount' => $invoice->balance_due,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'PromptPay / Bank Transfer',
                'notes' => __('Customer confirmed a bank transfer or PromptPay payment from the public invoice page.'),
            ]
        );

        return redirect()
            ->route('public.invoice.show', $invoice->public_token)
            ->with('success', __('Transfer confirmation received. Please keep your payment slip until the payment is reviewed.'));
    }

    public function webhook(string $gateway, Request $request, PaymentGatewayManager $gateways): JsonResponse
    {
        $result = $gateways->driver($gateway)->parseWebhook($request);

        if (! $result->valid) {
            return response()->json(['message' => $result->error ?: 'Invalid webhook.'], 401);
        }

        if (! $result->isPaid()) {
            return response()->json(['message' => 'Webhook received but payment was not marked paid.']);
        }

        $invoice = $this->resolveInvoice($result->invoiceToken, $result->invoiceId);

        if (! $invoice) {
            Log::warning('Payment webhook could not resolve invoice.', [
                'gateway' => $gateway,
                'invoice_token' => $result->invoiceToken,
                'invoice_id' => $result->invoiceId,
            ]);

            return response()->json(['message' => 'Invoice not found.'], 404);
        }

        if (! $result->transactionId) {
            return response()->json(['message' => 'Missing transaction id.'], 422);
        }

        DB::transaction(function () use ($invoice, $gateway, $result): void {
            $invoice->payments()->firstOrCreate(
                [
                    'gateway' => $gateway,
                    'transaction_id' => $result->transactionId,
                ],
                [
                    'amount' => $result->amount ?? $invoice->balance_due,
                    'payment_date' => now()->toDateString(),
                    'payment_method' => ucfirst($gateway),
                    'status' => 'paid',
                    'external_reference' => $result->externalReference,
                    'notes' => __('Confirmed by :gateway webhook.', ['gateway' => ucfirst($gateway)]),
                    'raw_payload' => $result->payload,
                    'verified_at' => now(),
                ]
            );
        });

        return response()->json(['message' => 'Payment recorded.']);
    }

    private function resolveInvoice(?string $token, ?int $invoiceId): ?Invoice
    {
        if (! $token && ! $invoiceId) {
            return null;
        }

        return Invoice::withoutGlobalScopes()
            ->when($token, fn ($query) => $query->where('public_token', $token))
            ->when(! $token && $invoiceId, fn ($query) => $query->whereKey($invoiceId))
            ->first();
    }
}
