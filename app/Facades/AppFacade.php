<?php

namespace App\Facades;

use App\AppSetting;
use Illuminate\Support\Facades\Facade;

/**
 * @method static AppSetting getSettingValue(array $setting, $key)
 * @method static AppSetting generateActivityLog(string $table, string $action, int $actionRowId = null, int $user_id = null)
 *
 * @see AppSetting
 */
class AppFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'SupportFacade';
    }
}
