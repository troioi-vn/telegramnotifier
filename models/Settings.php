<?php

namespace Extension\TelegramNotifier\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System\Actions\SettingsModel'];

    public $settingsCode = 'extension_telegramnotifier_settings';

    public $settingsFieldsConfig = 'settings_model';

    public function __construct()
    {
        parent::__construct();
    }
}