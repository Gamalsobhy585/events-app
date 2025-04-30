<?php

namespace Tests\Feature;

use App\Events\UserSaved;
use App\Models\User;
use App\Listeners\SaveUserBackgroundInformation;
use App\Models\Detail;
use App\Services\Interface\UserServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserEventTest extends TestCase
{
    use RefreshDatabase;

    private User $testUser;
    private SaveUserBackgroundInformation $listener;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->app['config']->set('app.key', 'base64:'.base64_encode(
            \Illuminate\Encryption\Encrypter::generateKey($this->app['config']['app.cipher'])
        ));
        
        $this->testUser = new User([
            'firstname' => 'Test',
            'middlename' => 'User',
            'lastname' => 'One',
            'email' => 'test@example.com'
        ]);
        
        $this->listener = new SaveUserBackgroundInformation(
            app(UserServiceInterface::class),
            app(Detail::class)
        );
    }

 
    public function test_user_saved_event_triggers_background_save(): void
    {
        Event::fake();
        
        $user = User::create([
            'firstname' => 'Test',
            'lastname' => 'User',
            'email' => 'test@example.com'
        ]);
        
        Event::assertDispatched(UserSaved::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }


    public function test_background_information_is_saved_correctly(): void
    {
        $user = $this->createUserWithCompleteInfo();
        
        $this->listener->handle(new UserSaved($user));
        
        $this->assertUserDetailsExist($user->id, [
            'full_name' => 'Jane Elizabeth Smith',
            'middle_initial' => 'E.',
            'avatar' => 'profile.jpg',
            'gender' => 'female'
        ]);
    }


    public function test_no_duplicate_details_on_multiple_events(): void
    {
        $user = $this->testUser;
        $user->save();
        
        $this->listener->handle(new UserSaved($user));
        $this->listener->handle(new UserSaved($user));
        
        $this->assertNoDuplicateDetails($user->id);
    }

  
    public function test_real_saves_to_database(): void
    {
        $user = User::create([
            'firstname' => 'Real',
            'lastname' => 'User', 
            'email' => 'realuser@example.com'
        ]);
        
        $this->assertDatabaseHas('users', ['email' => 'realuser@example.com']);
        
        $this->listener->handle(new UserSaved($user));
        
        $this->assertDatabaseHas('details', [
            'user_id' => $user->id,
            'key' => 'full_name'
        ]);
    }
    
 
    private function createUserWithCompleteInfo(): User
    {
        return User::create([
            'firstname' => 'Jane',
            'middlename' => 'Elizabeth',
            'lastname' => 'Smith',
            'prefixname' => 'Mrs',
            'email' => 'jane@example.com',
            'photo' => 'profile.jpg'
        ]);
    }
    

    private function assertUserDetailsExist(int $userId, array $expectedDetails): void
    {
        foreach ($expectedDetails as $key => $value) {
            $this->assertDatabaseHas('details', [
                'user_id' => $userId,
                'key' => $key,
                'value' => $value
            ]);
        }
    }
    

    private function assertNoDuplicateDetails(int $userId): void
    {
        $detailCounts = DB::table('details')
            ->where('user_id', $userId)
            ->select('key', DB::raw('count(*) as count'))
            ->groupBy('key')
            ->pluck('count', 'key');
            
        foreach (['full_name', 'middle_initial', 'avatar', 'gender'] as $key) {
            $this->assertEquals(1, $detailCounts[$key] ?? 0, "Duplicate entry found for key: $key");
        }
    }
}