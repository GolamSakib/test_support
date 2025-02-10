<?php


namespace App\Listeners;

use App\Events\UserActivityLogEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserActivityLogListener
{
    public function __construct()
    {
    }


    /**
     * @param UserActivityLogEvent $eventData
     * @return bool
     */
    public function handle(UserActivityLogEvent $eventData): bool
    {
        $current_timestamp = Carbon::now()->toDateTimeString();
        return DB::table('activity_logs')->insert(
            [
                'user_id' => $eventData->getUserId(),
                'table_name' => $eventData->getTable(),
                'row_id' => $eventData->getActionRowId(),
                'action_name' => $eventData->getAction(),
                'activity' => $eventData->getActivity(),
                'ip' => request()->ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'created_at' => $current_timestamp,
                'updated_at' => $current_timestamp
            ]
        );
    }
}
