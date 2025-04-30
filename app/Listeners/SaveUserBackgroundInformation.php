<?php

namespace App\Listeners;

use App\Events\UserSaved;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Interface\UserServiceInterface;
use Illuminate\Queue\InteractsWithQueue;
use Exception;
use Illuminate\Support\Facades\Log;

class SaveUserBackgroundInformation implements ShouldQueue
{
    use InteractsWithQueue;
    public $tries = 3;
    public $backoff = 60;
    protected $userService;
    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function handle(UserSaved $event)
    {
        try {
            $this->userService->saveUserBackgroundInformation($event->user);
            Log::info('User background information saved successfully', ['user_id' => $event->user->id]);
        } catch (Exception $e) {
            Log::error('Failed to save user background information', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff);
            } else {
                $this->fail($e);
            }
        }
    }

    public function failed(Exception $exception)
    {
        Log::critical('User background information saving has failed permanently', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

    }
}