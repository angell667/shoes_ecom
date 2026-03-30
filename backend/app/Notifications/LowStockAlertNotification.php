<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Product $product
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $adminUrl = config('app.frontend_url', 'http://localhost:5173') . '/admin/products';
        
        return (new MailMessage)
            ->subject('Low Stock Alert - ' . $this->product->name)
            ->line('A product is running low on stock.')
            ->line('**Product:** ' . $this->product->name)
            ->line('**SKU:** ' . $this->product->sku)
            ->line('**Current Stock:** ' . $this->product->stock . ' units')
            ->action('Manage Inventory', $adminUrl)
            ->line('Please restock this item soon.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'sku' => $this->product->sku,
            'current_stock' => $this->product->stock,
            'message' => 'Low stock alert for ' . $this->product->name,
        ];
    }
}
