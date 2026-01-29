<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Note;

class WaystoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_waystone_shows_only_shareable()
    {
        // create notes with different visibility
        Note::create(['slug' => 'p1','title' => 'Private','body'=> 'private note','visibility' => 'private','path' => '/notes/p1']);
        Note::create(['slug' => 's1','title' => 'Shared','body'=> 'shared note','visibility' => 'shareable','path' => '/notes/s1']);

        $resp = $this->get('/waystone');
        $resp->assertStatus(200);
        $resp->assertSeeText('shared note');
        $resp->assertDontSeeText('private note');
    }

    public function test_cannot_share_checkin_or_interaction()
    {
        $resp = $this->post('/share/checkin/1', ['confirm' => 'yes']);
        $resp->assertStatus(403);
    }
}
