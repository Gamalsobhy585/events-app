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
    use RefreshDatabase;
    
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
    }

    public function test_handle_retries_on_failure()
    {
        $userService = Mockery::mock(UserServiceInterface::class);
        $detail = new Detail(); 
        $listener = new SaveUserBackgroundInformation($userService, $detail);
        
        $listener = Mockery::mock(SaveUserBackgroundInformation::class, [$userService, $detail])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
            
        $listener->shouldReceive('attempts')
            ->once()
            ->andReturn(1);
            
        $listener->shouldReceive('release')
            ->once()
            ->with(60)
            ->andReturn(null);
        
        $user = User::factory()->create([
            'firstname' => 'Retry',
            'lastname' => 'Failure'
        ]);
        
        $event = new UserSaved($user);
        $exception = new Exception('Test exception');
        
        $userService->shouldReceive('saveUserBackgroundInformation')
            ->once()
            ->with($user, $detail)
            ->andThrow($exception);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($user, $exception) {
                return $message === 'Failed to save user background information' &&
                       $context['user_id'] === $user->id &&
                       $context['error'] === $exception->getMessage();
            });

        try {
            $listener->handle($event);
            $this->assertTrue(true);
            $this->fail('Exception should have been thrown');
        } catch (Exception $e) {
            $this->assertEquals('Test exception', $e->getMessage());
        }
        
        $this->assertTrue(true);
    }

    public function test_failed_method_logs_critical_error()
    {
        $userService = Mockery::mock(UserServiceInterface::class);
        $detail = new Detail(); 
        $listener = new SaveUserBackgroundInformation($userService, $detail);
        $exception = new Exception('Permanent failure');
    
        Log::shouldReceive('critical')
            ->once()
            ->withArgs(function ($message, $context) use ($exception) {
                return str_contains($message, 'permanently') &&
                       $context['error'] === $exception->getMessage();
            });
    
        $listener->failed($exception);
    
        $this->assertTrue(true);
    }
}
