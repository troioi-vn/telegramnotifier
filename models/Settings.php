<?php

namespace Igniter\TelegramNotifier\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System\Actions\SettingsModel'];

    public $settingsCode = 'igniter_telegramnotifier_settings';

    public $settingsFieldsConfig = 'settings_model';
}