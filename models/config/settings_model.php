fields:
    section_telegram:
        label: Telegram Configuration
        type: section

    enable_notifications:
        label: Enable Notifications
        type: switch
        default: true

    telegram_bot_token:
        label: Telegram Bot Token
        type: text
        span: left
        comment: Enter your Telegram bot token obtained from BotFather

    telegram_chat_id:
        label: Telegram Chat ID
        type: text
        span: right
        comment: Enter the chat ID where notifications will be sent

    section_notification_options:
        label: Notification Options
        type: section

    notify_on_new_orders:
        label: Notify on new orders
        type: switch
        default: true

    notify_on_status_change:
        label: Notify on order status changes
        type: switch
        default: true