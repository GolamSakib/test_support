<?php

namespace App;

use App\Events\UserActivityLogEvent;
use Exception;

class AppSetting
{
    /**
     * Get Setting Value
     * @param array $setting
     * @param $key
     * @return mixed|void
     * @throws Exception
     */
    public function getSettingValue(array $setting, $key)
    {
        if (array_key_exists($key, $setting)) {
            return $setting[$key];
        }

        throw new Exception("$key does not exist in setting");
    }

    /**
     * Get Setting Value
     * @param string $table
     * @param string $action
     * @param int|null $actionRowId
     * @param int|null $user_id
     * @return void
     * @throws Exception
     */
    public function generateActivityLog(string $table, string $action, int $actionRowId = null, int $user_id = null): void
    {
        $data = [
            'table' => $table,
            'row_id' => $actionRowId,
            'user_id' => $user_id,
            'action' => $action,
        ];
        event(new UserActivityLogEvent($data));
    }

    /**
     * @return void
     */
    public function generateShopBreadCrumbd()
    {
    }
}
