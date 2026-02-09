# 053 - Stripe Payment Link Generation UI

**Labels:** `feature`, `invoicing`, `payments`, `ui`
**Depends on:** #051, #052

## Description

Add a "Generate Payment Link" button on the invoice detail page that calls the Stripe service to create a payment link and stores it on the invoice.

## Implementation

### Livewire Action on Invoice Detail
Add "Generate Payment Link" button (shown when invoice is Sent or Overdue and has no payment link):

```php
public function generatePaymentLink(): void
{
    $this->authorize('update', $this->invoice);

    $stripeService = app(StripeService::class);
    $paymentLink = $stripeService->createPaymentLink($this->invoice);

    $this->invoice->update([
        'stripe_payment_link' => $paymentLink,
    ]);

    session()->flash('success', 'Payment link generated!');
}
```

### UI Updates on Invoice Detail

**When payment link exists:**
- Display the payment link URL with a copy-to-clipboard button
- "Open Payment Page" button (opens in new tab)
- Show "Payment link active" indicator

**When no payment link:**
- "Generate Payment Link" button (only for Sent/Overdue invoices)

### Copy to Clipboard
Use Alpine.js for clipboard functionality:
```blade
<div x-data="{ copied: false }">
    <flux:input readonly :value="$invoice->stripe_payment_link" />
    <flux:button
        x-on:click="navigator.clipboard.writeText('{{ $invoice->stripe_payment_link }}'); copied = true; setTimeout(() => copied = false, 2000)"
        x-text="copied ? 'Copied!' : 'Copy'"
    />
</div>
```

## Files to Modify
- `resources/views/pages/invoices/show.blade.php` — add payment link section

## Acceptance Criteria
- [ ] "Generate Payment Link" calls Stripe and stores URL
- [ ] Payment link displayed with copy button
- [ ] Copy to clipboard works
- [ ] Button only shown for Sent/Overdue invoices without a link
- [ ] Error handling if Stripe call fails
- [ ] Feature test verifies link generation (mocked Stripe)
