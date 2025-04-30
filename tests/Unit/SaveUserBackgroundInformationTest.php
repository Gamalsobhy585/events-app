<?php

namespace Tests\Feature; // Adjust namespace as needed

use App\Events\UserSaved;
use App\Listeners\SaveUserBackgroundInformation;
use App\Models\Detail;
use App\Models\User;
use App\Services\Interface\UserServiceInterface;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class SaveUserBackgroundInformationTest extends TestCase
{
    use RefreshDatabase;
    private $userService;
    private $detail;
    private $listener;
    private $testUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userService = Mockery::mock(UserServiceInterface::class);
        $this->detail = new Detail();
        $this->listener = new SaveUserBackgroundInformation($this->userService, $this->detail);
        
        $this->testUser = User::factory()->create([
            'firstname' => 'John',
            'middlename' => 'Middle',
            'lastname' => 'Doe',
            'prefixname' => 'Mr',
            'photo' => 'avatar.png'
        ]);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_handle_saves_user_background_information(): void
    {
        $event = $this->createUserSavedEvent($this->testUser);
        $this->expectUserServiceToSaveInformation($this->testUser);
        $this->expectSuccessfulLogMessage($this->testUser->id);
        $this->listener->handle($event);
        $this->assertTrue(true, 'Background information save was attempted');
    }

    public function test_handle_retries_on_failure(): void
    {
        $user = $this->createRetryTestUser();
        $exception = new Exception('Test exception');
        $listener = $this->createPartialMockListener();  
        $this->expectAttemptCheck($listener);
        $this->expectReleaseJob($listener);
        $this->expectUserServiceToThrowException($exception);
        $this->expectErrorLogMessage($user->id);
        $this->expectException(Exception::class);
        $listener->handle(new UserSaved($user));
    }

    public function test_failed_method_logs_critical_error(): void
    {
        $exception = new Exception('Permanent failure');
        $this->expectCriticalLogMessage($exception);
        $this->listener->failed($exception);
        $this->assertTrue(true, 'Critical error was logged');
    }

    private function createRetryTestUser(): User
    {
        return User::factory()->create([
            'firstname' => 'Retry',
            'lastname' => 'Failure'
        ]);
    }

    private function createUserSavedEvent(User $user): UserSaved
    {
        return new UserSaved($user);
    }

    private function createPartialMockListener(): Mockery\MockInterface
    {
        return Mockery::mock(SaveUserBackgroundInformation::class, [$this->userService, $this->detail])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    private function expectUserServiceToSaveInformation(User $user): void
    {
        $this->userService->shouldReceive('saveUserBackgroundInformation')
            ->once()
            ->with($user, $this->detail);
    }

    private function expectUserServiceToThrowException(Exception $exception): void
    {
        $this->userService->shouldReceive('saveUserBackgroundInformation')
            ->once()
            ->andThrow($exception);
    }

    private function expectSuccessfulLogMessage(int $userId): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) use ($userId) {
                return $message === 'User background information saved successfully' &&
                       $context['user_id'] === $userId;
            });
    }

    private function expectErrorLogMessage(int $userId): void
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($userId) {
                return str_contains($message, 'Failed to save user background information') &&
                       $context['user_id'] === $userId;
            });
    }

    private function expectCriticalLogMessage(Exception $exception): void
    {
        Log::shouldReceive('critical')
            ->once()
            ->withArgs(function ($message, $context) use ($exception) {
                return str_contains($message, 'permanently') &&
                       $context['error'] === $exception->getMessage();
            });
    }

    private function expectAttemptCheck(Mockery\MockInterface $listener): void
    {
        $listener->shouldReceive('attempts')
            ->once()
            ->andReturn(1);
    }

    private function expectReleaseJob(Mockery\MockInterface $listener): void
    {
        $listener->shouldReceive('release')
            ->once()
            ->with(60);
    }
}