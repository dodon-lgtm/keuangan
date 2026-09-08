<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\Order;

class OrderObserver
{
    /**
     * Auto-calculate the order nominal from the product price and quantity
     * when the nominal was not manually provided in the form.
     */
    public function creating(Order $order): void
    {
        if (blank($order->nominal)) {
            $order->nominal = $order->product->harga_jual * $order->jumlah_pcs;
        }
    }

    /**
     * Auto-sync the customer status after a new order has been stored.
     */
    public function created(Order $order): void
    {
        self::syncCustomer($order->customer);
    }

    /**
     * Auto-calculate the order nominal when it is left blank during an update.
     */
    public function updating(Order $order): void
    {
        if (blank($order->nominal)) {
            $order->nominal = $order->product->harga_jual * $order->jumlah_pcs;
        }
    }

    /**
     * Keep the customer status in sync after an order has been updated.
     */
    public function updated(Order $order): void
    {
        self::syncCustomer($order->customer);
    }

    /**
     * Sync the customer first-order date and status based on their orders:
     *  - 1st order (or single order):  tanggal_order_pertama = earliest order date, status = 'new'
     *  - 2nd order or later:            status = 'repeat'
     */
    public static function syncCustomer(Customer $customer): void
    {
        $orderCount = $customer->orders()->count();

        if ($orderCount <= 1) {
            $customer->update([
                'tanggal_order_pertama' => $customer->orders()->min('tanggal'),
                'status_pelanggan' => Customer::STATUS_NEW,
            ]);

            return;
        }

        $customer->update([
            'status_pelanggan' => Customer::STATUS_REPEAT,
        ]);
    }
}
