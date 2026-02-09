# 057 - Overdue Invoice Detection

**Labels:** `feature`, `invoicing`, `backend`
**Depends on:** #009

## Description

Create a scheduled command that checks for invoices past their due date and marks them as overdue. Optionally sends reminder emails.

## Implementation

### Create Artisan Command
`App\Console\Commands\DetectOverdueInvoices`

```php
public function handle(): int
{
    $overdueInvoices = Invoice::query()
        ->where('status', InvoiceStatus::Sent)
        ->where('due_date', '<', now()->startOfDay())
        ->get();

    foreach ($overdueInvoices as $invoice) {
        $invoice->update(['status' => InvoiceStatus::Overdue]);

        // Send reminder to client
        if ($invoice->client->email) {
            Mail::to($invoice->client->email)
                ->send(new InvoiceOverdueReminder($invoice));
        }

        // Notify influencer
        Mail::to($invoice->user->email)
            ->send(new InvoiceOverdueNotification($invoice));
    }

    $this->info("Marked {$overdueInvoices->count()} invoice(s) as overdue.");

    return Command::SUCCESS;
}
```

### Schedule
In `routes/console.php`:
```php
Schedule::command('invoices:detect-overdue')->dailyAt('09:00');
```

### Create Mailables

**`App\Mail\InvoiceOverdueReminder`** (to client):
- Subject: "Payment Reminder: Invoice {number} is overdue"
- Content: invoice number, amount, due date, days overdue, payment link

**`App\Mail\InvoiceOverdueNotification`** (to influencer):
- Subject: "Invoice {number} is now overdue"
- Content: client name, invoice number, amount, due date

## Files to Create
- `app/Console/Commands/DetectOverdueInvoices.php`
- `app/Mail/InvoiceOverdueReminder.php`
- `app/Mail/InvoiceOverdueNotification.php`
- `resources/views/mail/invoice-overdue-reminder.blade.php`
- `resources/views/mail/invoice-overdue-notification.blade.php`

## Files to Modify
- `routes/console.php` — add schedule

## Acceptance Criteria
- [ ] Command finds Sent invoices past due date
- [ ] Status updated to Overdue
- [ ] Reminder email sent to client
- [ ] Notification email sent to influencer
- [ ] Runs daily at 9 AM via scheduler
- [ ] Feature test verifies detection and status change
