<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderShippedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public ?string $trackingNumber = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        
        $message = (new MailMessage)
            ->subject('Your Order Has Shipped - #' . $this->order->order_number)
            ->greeting('Great news!')
            ->line('Your order has been shipped and is on its way.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Shipping Address:** ' . $this->order->shipping_address);

        if ($this->trackingNumber) {
            $message->line('**Tracking Number:** ' . $this->trackingNumber);
        }

        return $message
            ->action('Track Order', $frontendUrl . '/orders/' . $this->order->id)
            ->line('Thank you for your patience!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'tracking_number' => $this->trackingNumber,
            'message' => 'Your order #' . $this->order->order_number . ' has been shipped.',
        ];
    }
}
