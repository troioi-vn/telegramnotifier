<?php

namespace Igniter\TelegramNotifier;

use System\Classes\BaseExtension;
use Event;
use Admin\Models\Orders_model;

class Extension extends BaseExtension
{
    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'Telegram Notifier Settings',
                'description' => 'Manage Telegram bot settings for order notifications.',
                'icon' => 'fa fa-telegram',
                'model' => \Igniter\TelegramNotifier\Models\Settings::class,
                'permissions' => ['Igniter.TelegramNotifier.ManageSettings'],
            ],
        ];
    }

    public function boot()
    {
        // Subscribe to order creation events
        Event::listen('admin.order.placed', function ($order) {
            $this->sendOrderStatusNotification($order, 'new');
        });

        // Subscribe to order status update events
        Event::listen('admin.order.status.updated', function ($model, $statusId, $previousStatus) {
            $this->sendOrderStatusNotification($model, 'updated');
        });
    }

    protected function sendOrderStatusNotification($order, $type)
    {
        // Get settings
        $settings = \Igniter\TelegramNotifier\Models\Settings::instance();

        // Check if notifications are enabled
        if (!$settings->get('enable_notifications', true))
            return;

        // Get chat ID and bot token
        $chatId = $settings->get('telegram_chat_id');
        $botToken = $settings->get('telegram_bot_token');

        if (empty($chatId) || empty($botToken))
            return;

        // Build the message
        $message = $this->buildNotificationMessage($order, $type);

        // Send the message to Telegram
        $this->sendTelegramMessage($botToken, $chatId, $message);
    }

    protected function buildNotificationMessage($order, $type)
    {
        $message = "";
        
        if ($type == 'new') {
            $message .= "🔔 *NEW ORDER #".$order->order_id."*\n\n";
        } else {
            $message .= "🔄 *ORDER #".$order->order_id." UPDATED*\n\n";
        }
        
        // Add order details
        $message .= "📅 Date: ".mdate(setting('date_format').' '.setting('time_format'), strtotime($order->order_date))."\n";
        $message .= "💰 Total: ".currency_format($order->order_total)."\n";
        $message .= "👤 Customer: ".$order->first_name." ".$order->last_name."\n";
        $message .= "📱 Phone: ".$order->telephone."\n";
        $message .= "🏠 Type: ".($order->order_type == '1' ? 'Delivery' : 'Pick-up')."\n";
        
        if ($order->status) {
            $message .= "📊 Status: ".$order->status->status_name."\n";
        }
        
        // Add order items
        $message .= "\n📋 *Order Items:*\n";
        foreach ($order->getOrderMenus() as $menuItem) {
            $message .= "• ".$menuItem->quantity."x ".$menuItem->name." - ".currency_format($menuItem->subtotal)."\n";
            
            // Add options if any
            if (!empty($menuItem->options)) {
                foreach ($menuItem->options as $option) {
                    $message .= "  - ".$option->order_option_name.": ".$option->order_option_price."\n";
                }
            }
        }
        
        $message .= "\n🔗 View order: ".admin_url('orders/edit/'.$order->order_id);
        
        return $message;
    }

    protected function sendTelegramMessage($botToken, $chatId, $message)
    {
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ];
        
        // Use cURL to send the request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        // Log errors if any
        if ($response === false) {
            \Log::error('Telegram Notification Error: ' . curl_error($ch));
        }
        
        return $response;
    }
}