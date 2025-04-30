<?php
namespace Tests\Unit;

use App\Events\UserSaved;
use App\Listeners\SaveUserBackgroundInformation;
use App\Models\Detail;
use App\Services\Interface\UserServiceInterface;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class SaveUserBackgroundInformationTest extends TestCase
{
    // use RefreshDatabase;
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_handle_saves_user_background_information()
    {
        $userService = Mockery::mock(UserServiceInterface::class);
        $detail = new Detail(); 
        $listener = new SaveUserBackgroundInformation($userService, $detail);
        
        $user = User::factory()->create([
            'firstname' => 'John',
            'middlename' => 'Middle',
            'lastname' => 'Doe',
            'prefixname' => 'Mr',
            'photo' => 'avatar.png'
        ]);

        $event = new UserSaved($user);

        $userService->shouldReceive('saveUserBackgroundInformation')
            ->once()
            ->with($user, $detail);

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) use ($user) {
                return $message === 'User background information saved successfully' &&
                       $context['user_id'] === $user->id;
            });

        $listener->handle($event);
        
        // Add explicit assertion
        $this->assertTrue(true, 'Background information save was attempted');
    }

    public function test_handle_retries_on_failure()
    {
        $userService = Mockery::mock(UserServiceInterface::class);
        $detail = new Detail();
        
        $listener = Mockery::mock(SaveUserBackgroundInformation::class, [$userService, $detail])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
            
        $listener->shouldReceive('attempts')
            ->once()
            ->andReturn(1);
            
        $listener->shouldReceive('release')
            ->once()
            ->with(60);
        
        $user = User::factory()->create([
            'firstname' => 'Retry',
            'lastname' => 'Failure'
        ]);
        
        $exception = new Exception('Test exception');
        
        $userService->shouldReceive('saveUserBackgroundInformation')
            ->once()
            ->andThrow($exception);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($user, $exception) {
                return str_contains($message, 'Failed to save user background information') &&
                       $context['user_id'] === $user->id;
            });

        $this->expectException(Exception::class);
        $listener->handle(new UserSaved($user));
    }

    public function test_failed_method_logs_critical_error()
    {
        $userService = Mockery::mock(UserServiceInterface::class);
        $listener = new SaveUserBackgroundInformation($userService, new Detail());
        $exception = new Exception('Permanent failure');
    
        Log::shouldReceive('critical')
            ->once()
            ->withArgs(function ($message, $context) use ($exception) {
                return str_contains($message, 'permanently') &&
                       $context['error'] === $exception->getMessage();
            });
    
        $listener->failed($exception);
        
        $this->assertTrue(true, 'Critical error was logged');
    }
}
