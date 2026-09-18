<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Carbon;

class OrderObserver
{
    /**
     * Default the order date to today when it was left blank and seed a
     * starting nominal of zero (the real value is calculated from the
     * attached order_items afterwards).
     */
    public function creating(Order $order): void
    {
        if (blank($order->tanggal)) {
            $order->tanggal = Carbon::today();
        }

        if (blank($order->nominal)) {
            $order->nominal = 0;
        }
    }

    /**
     * Keep the order date defaulted to today when it is cleared during an update.
     */
    public function updating(Order $order): void
    {
        if (blank($order->tanggal)) {
            $order->tanggal = Carbon::today();
        }
    }

    /**
     * Recalculate the order nominal from the sum of its line item subtotals.
     */
    public static function recalcNominal(Order $order): void
    {
        $order->update([
            'nominal' => (int) $order->orderItems()->sum('subtotal'),
        ]);
    }
}
