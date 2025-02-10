<?php

namespace App\Events;

use App\AppTrait\AuthTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserActivityLogEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels, AuthTrait;

    public $data;

    /**
     * @var mixed
     */
    private $table;


    /**
     * @var mixed
     */
    private $user_id;

    /**
     * @var mixed
     */
    private $action;


    /**
     * @var mixed
     */
    private $actionRowId;


    /**
     * @var
     */
    private $activity;


    /**
     * Create a new event instance.
     *
     * @return void
     * @throws Exception
     */
    public function __construct(array $data)
    {
        $this->generateException($data);

        $this->data = $data;
        $this->table = $data['table'];
        $this->actionRowId = (array_key_exists('row_id', $data) ? $data['row_id'] : $this->getActionRowId());
        $this->user_id = (array_key_exists('user_id', $data) ? $data['user_id'] : $this->getUserId());
        $this->action = $data['action'];
        $this->generateMessage();
    }

    /**
     * @param array $data
     * @return void
     * @throws Exception
     */
    private function generateException(array $data): void
    {
        if (!array_key_exists('table', $data)) {
            throw new \RuntimeException("Missing array_key = 'table' for generating activity log");
        }

        if (!array_key_exists('action', $data)) {
            throw new \RuntimeException("Missing array_key = 'action' for generating activity log");
        }
    }

    /**
     * @return mixed
     */
    public function getActionRowId()
    {
        return $this->actionRowId;
    }

    /**
     * @param mixed $actionRowId
     */
    public function setActionRowId(int $actionRowId): void
    {
        $this->actionRowId = $actionRowId;
    }

    /**
     * @return void
     */
    private function generateMessage()
    {
        $time = Carbon::now()->format('h:i:sa d-m-Y');
        $rowSuffix = '';
        if ($this->getActionRowId() !== '') {
            $rowSuffix = " row $this->actionRowId of";
        }

        $message = "$this->user_id $this->action" . "d" . "$rowSuffix  $this->table at " . $time;
        $this->setActivity($message);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('log');
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return mixed
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param mixed $activity
     */
    public function setActivity($activity): void
    {
        $this->activity = $activity;
    }
}
