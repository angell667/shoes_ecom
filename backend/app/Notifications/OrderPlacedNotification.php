<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        
        return (new MailMessage)
            ->subject('Order Confirmed - #' . $this->order->order_number)
            ->greeting('Thank you for your order!')
            ->line('Your order has been received and is being processed.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Order Total:** $' . number_format($this->order->total, 2))
            ->line('**Status:** ' . ucfirst($this->order->status))
            ->action('View Order', $frontendUrl . '/orders/' . $this->order->id)
            ->line('We will notify you when your order ships.')
            ->line('Thank you for shopping with us!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total' => $this->order->total,
            'status' => $this->order->status,
            'message' => 'Your order #' . $this->order->order_number . ' has been placed successfully.',
        ];
    }
}
