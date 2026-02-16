<?php

namespace App\Models;

use App\Builders\InvoiceBuilder;
use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function newEloquentBuilder($query): InvoiceBuilder
    {
        return new InvoiceBuilder($query);
    }

    protected static function booted(): void
    {
        static::creating(function (self $invoice): void {
            if (blank($invoice->invoice_number)) {
                $invoice->invoice_number = 'pending-'.(string) Str::uuid();
            }
        });

        static::created(function (self $invoice): void {
            if (str_starts_with($invoice->invoice_number, 'pending-')) {
                $invoice->forceFill([
                    'invoice_number' => (string) $invoice->id,
                ])->saveQuietly();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function calculateTotals(): void
    {
        $subtotal = (float) $this->items()->sum('total');
        $taxRate = (float) $this->tax_rate;
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $total = round($subtotal + $taxAmount, 2);

        $this->forceFill([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ])->save();
    }

}
