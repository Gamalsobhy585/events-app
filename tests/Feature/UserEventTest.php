<?php

namespace Tests\Feature;

use App\Events\UserSaved;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

use Tests\TestCase;

class UserEventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->app['config']->set('app.key', 'base64:'.base64_encode(
            \Illuminate\Encryption\Encrypter::generateKey($this->app['config']['app.cipher'])
        ));
    }

    public function test_user_saved_event_triggers_background_save()
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

  
    
    public function test_background_information_is_saved_correctly()
    {
        $user = User::create([
            'firstname' => 'Jane',
            'middlename' => 'Elizabeth',
            'lastname' => 'Smith',
            'prefixname' => 'Mrs',
            'email' => 'jane@example.com',
            'photo' => 'profile.jpg' 
        ]);
        
     
        event(new UserSaved($user));
        
        $this->assertDatabaseHas('details', [
            'user_id' => $user->id,
            'key' => 'full_name',
            'value' => 'Jane Elizabeth Smith'
        ]);
        
        $this->assertDatabaseHas('details', [
            'user_id' => $user->id,
            'key' => 'middle_initial',
            'value' => 'E.'
        ]);
        
        $this->assertDatabaseHas('details', [
            'user_id' => $user->id,
            'key' => 'avatar',
            'value' => 'profile.jpg'
        ]);
        
        $this->assertDatabaseHas('details', [
            'user_id' => $user->id,
            'key' => 'gender',
            'value' => 'female'
        ]);
    }

    public function test_no_duplicate_details_on_multiple_events()
    {
        $user = User::create([
            'firstname' => 'Test',
            'middlename' => 'User',
            'lastname' => 'One',
            'email' => 'test2@example.com'
        ]);
        
        event(new UserSaved($user));
        event(new UserSaved($user));
        
        $detailCounts = DB::table('details')
            ->where('user_id', $user->id)
            ->select('key', DB::raw('count(*) as count'))
            ->groupBy('key')
            ->pluck('count', 'key');
            
        foreach (['full_name', 'middle_initial', 'avatar', 'gender'] as $key) {
            $this->assertEquals(1, $detailCounts[$key] ?? 0, "Duplicate entry found for key: $key");
        }
    }
}