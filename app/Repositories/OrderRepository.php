<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository
{
    public function createOrder($userId, $amount)
    {
        return Order::create([
            'user_id' => $userId,
            'total_amount' => $amount,
            'payment_status' => 'pending',
        ]);
    }

    public function updatePaymentStatus(Order $order, $status, $paymentId = null)
    {
        $order->payment_status = $status;
        if ($paymentId) {
            $order->payment_id = $paymentId;
        }
        $order->save();
        return $order;
    }
}
