<?php
namespace App\Jobs;

use App\Models\Message;
use App\Models\Customer;
use App\Models\ClientUser;
use Illuminate\Bus\Queueable;
use Twilio\TwiML\Voice\Client;
use App\Utility\SendSMSUtility;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendPushMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $message;
    public $tries = 3; // Number of retry attempts
    public $backoff = [60, 180, 300]; // Retry delay in seconds

    public function __construct(ClientUser $user,String $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    public function handle()
    {
        try {
            // Save message to database
            $messageRecord = Message::create([
                'client_id' => $this->user->client_id,
                'user_id' => $this->user->id,
                'content' => $this->message,
                'status' => 'pending'
            ]);


            $this->sendRegistrationSMS($this->user->phoneno, $this->message);

            // Update message status
            $messageRecord->update(['status' => 'sent']);

        } catch (\Exception $e) {
            Log::error('Push Message Failed', [
                'client_id' => $this->user->client_id,
                'error' => $e->getMessage()
            ]);

            throw $e; // This will trigger the job to retry
        }
    }

    public function failed(\Throwable $exception)
    {
        // Handle failed job
        Log::error('Push Message Failed Finally', [
            'client_id' => $this->user->client_id,
            'error' => $exception->getMessage()
        ]);

        // Update message status to failed
        Message::where('client_id', $this->user->client_id)
            ->where('user_id', $this->user->id)
            ->where('content', $this->message)
            ->update(['status' => 'failed']);
    }

    public function sendRegistrationSMS(string $phone, string $text)
    {
        SendSMSUtility::sendSMS($phone, $text);
    }


}
