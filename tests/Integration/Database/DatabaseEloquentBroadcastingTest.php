<?php

namespace Illuminate\Tests\Integration\Database;

use Illuminate\Database\Eloquent\BroadcastableModelEventOccurred;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

/**
 * @group integration
 */
class DatabaseEloquentBroadcastingTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_eloquent_broadcasting_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function testBasicBroadcasting()
    {
        Event::fake([BroadcastableModelEventOccurred::class]);

        $model = new TestEloquentBroadcastUser;
        $model->name = 'Taylor';
        $model->save();

        Event::assertDispatched(function (BroadcastableModelEventOccurred $event) {
            return $event->model instanceof TestEloquentBroadcastUser &&
                   count($event->broadcastOn()) === 1 &&
                   $event->broadcastOn()[0]->name == 'private-Illuminate.Tests.Integration.Database.TestEloquentBroadcastUser.'.$event->model->id;
        });
    }

    public function testChannelRouteFormatting()
    {
        $model = new TestEloquentBroadcastUser;

        $this->assertEquals('Illuminate.Tests.Integration.Database.TestEloquentBroadcastUser.{testEloquentBroadcastUser}', $model->broadcastChannelRoute());
    }
}

class TestEloquentBroadcastUser extends Model
{
    use BroadcastsEvents;

    protected $table = 'test_eloquent_broadcasting_users';
}
