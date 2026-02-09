# 054 - Stripe Webhook Handler

**Labels:** `feature`, `invoicing`, `payments`, `backend`
**Depends on:** #052

## Description

Create a webhook endpoint that receives Stripe payment events and automatically marks invoices as paid when payment completes.

## Implementation

### Create Controller
`App\Http\Controllers\Webhooks\StripeWebhookController`

```php
public function handle(Request $request): Response
{
    $stripeService = app(StripeService::class);

    try {
        $event = $stripeService->verifyWebhookSignature(
            $request->getContent(),
            $request->header('Stripe-Signature')
        );
    } catch (\Exception $e) {
        return response('Invalid signature', 400);
    }

    match ($event->type) {
        'checkout.session.completed' => $this->handleCheckoutCompleted($event),
        default => null,
    };

    return response('OK', 200);
}

private function handleCheckoutCompleted(\Stripe\Event $event): void
{
    $session = $event->data->object;
    $invoiceId = $session->metadata->invoice_id ?? null;

    if (!$invoiceId) {
        return;
    }

    $invoice = Invoice::find($invoiceId);
    if (!$invoice) {
        return;
    }

    $invoice->update([
        'status' => InvoiceStatus::Paid,
        'stripe_session_id' => $session->id,
        'paid_at' => now(),
    ]);

    // Send payment confirmation email
    Mail::to($invoice->user->email)
        ->send(new InvoicePaymentReceived($invoice));
}
```

### Route
```php
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])
    ->name('webhooks.stripe');
```

**Important:** This route must be excluded from CSRF verification.

### Update `bootstrap/app.php`
Exclude the webhook route from CSRF middleware:
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->validateCsrfTokens(except: [
        'webhooks/stripe',
    ]);
})
```

### Create Mailable
`App\Mail\InvoicePaymentReceived`:
- To: influencer email
- Subject: "Payment Received: {invoice_number}"
- Content: client name, amount paid, invoice number

## Files to Create
- `app/Http/Controllers/Webhooks/StripeWebhookController.php`
- `app/Mail/InvoicePaymentReceived.php`
- `resources/views/mail/invoice-payment-received.blade.php`

## Files to Modify
- `routes/web.php` — add webhook route
- `bootstrap/app.php` — exclude from CSRF

## Acceptance Criteria
- [ ] Webhook endpoint accepts POST at `/webhooks/stripe`
- [ ] Signature verification rejects invalid requests
- [ ] `checkout.session.completed` marks invoice as Paid
- [ ] `paid_at` timestamp recorded
- [ ] Payment confirmation email sent to influencer
- [ ] Unknown event types return 200 (acknowledge but ignore)
- [ ] CSRF excluded for webhook route
- [ ] Feature tests cover webhook handling
