# 052 - Stripe Service Integration

**Labels:** `feature`, `invoicing`, `payments`
**Depends on:** #009

## Description

Install Stripe PHP SDK and create a service class for interacting with Stripe API. This is the backend integration only — UI is handled in #053.

## Implementation

### Install Package
```bash
composer require stripe/stripe-php
```

### Configuration
Add to `config/services.php`:
```php
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
],
```

Update `.env.example`:
```
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
```

### Create `App\Services\StripeService`

**`createPaymentLink(Invoice $invoice): string`**
1. Create a Stripe Price for the invoice amount:
   ```php
   $price = \Stripe\Price::create([
       'unit_amount' => (int) ($invoice->total * 100), // cents
       'currency' => 'usd',
       'product_data' => [
           'name' => "Invoice {$invoice->invoice_number}",
       ],
   ]);
   ```
2. Create a Payment Link:
   ```php
   $paymentLink = \Stripe\PaymentLink::create([
       'line_items' => [['price' => $price->id, 'quantity' => 1]],
       'metadata' => [
           'invoice_id' => $invoice->id,
           'invoice_number' => $invoice->invoice_number,
       ],
       'after_completion' => [
           'type' => 'redirect',
           'redirect' => ['url' => config('app.url') . '/portal/invoices/' . $invoice->id . '?paid=true'],
       ],
   ]);
   ```
3. Return the payment link URL

**`verifyWebhookSignature(string $payload, string $signature): \Stripe\Event`**
- Verify webhook signature using `STRIPE_WEBHOOK_SECRET`
- Return the parsed event
- Throw exception if signature invalid

### Error Handling
- Create `App\Exceptions\StripeException` for Stripe-specific errors
- Wrap all Stripe calls in try/catch

## Files to Create
- `app/Services/StripeService.php`
- `app/Exceptions/StripeException.php`

## Files to Modify
- `config/services.php` — add stripe config
- `.env.example` — add Stripe env vars

## Acceptance Criteria
- [ ] Stripe PHP SDK installed
- [ ] Service class creates payment links correctly
- [ ] Webhook signature verification works
- [ ] Configuration uses env variables
- [ ] Error handling with typed exceptions
- [ ] Unit tests with mocked Stripe (using Http::fake or Stripe mock)
