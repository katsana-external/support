<?php

namespace Orchestra\Support\TestCase\Traits;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Orchestra\Support\Traits\Observable;

class ObservableTest extends TestCase
{
    /**
     * Teardown the test environment.
     */
    protected function tearDown()
    {
        m::close();
    }

    /**
     * Test Orchestra\Support\Traits\Observable::$dispatcher.
     *
     * @test
     */
    public function testEventDispatcher()
    {
        $dispatcher = m::mock('\Illuminate\Contracts\Events\Dispatcher');

        $this->assertNull(ObservableStub::getEventDispatcher());

        ObservableStub::setEventDispatcher($dispatcher);

        $this->assertEquals($dispatcher, ObservableStub::getEventDispatcher());

        ObservableStub::unsetEventDispatcher();

        $this->assertNull(ObservableStub::getEventDispatcher());
    }

    /**
     * Test Orchestra\Support\Traits\Observable::getObservableEvents()
     * method.
     *
     * @test
     */
    public function testGetObservableEventsMethod()
    {
        $stub1 = new ObservableStub();
        $stub2 = new ObservableStubWithoutEvents();

        $this->assertEquals(['saving', 'saved'], $stub1->getObservableEvents());
        $this->assertEquals([], $stub2->getObservableEvents());
    }

    /**
     * Test Orchestra\Support\Traits\Observable::observe()
     * method without event dispatcher.
     *
     * @test
     */
    public function testObserveWithoutDispatcher()
    {
        ObservableStub::flushEventListeners();

        ObservableStub::observe(new FoobarObserver());

        $stub = new ObservableStub();
        $stub->save();

        $this->assertFalse($stub->saving);
        $this->assertFalse($stub->saved);
    }

    /**
     * Test Orchestra\Support\Traits\Observable::observe()
     * method with event dispatcher.
     *
     * @test
     */
    public function testObserveWithDispatcher()
    {
        $dispatcher = m::mock('\Illuminate\Contracts\Events\Dispatcher');

        $stub = new ObservableStub();

        $dispatcher->shouldReceive('listen')->once()
                ->with('saving: '.__NAMESPACE__.'\\ObservableStub', __NAMESPACE__.'\\FoobarObserver@saving')
            ->shouldReceive('listen')->once()
                ->with('saved: '.__NAMESPACE__.'\\ObservableStub', __NAMESPACE__.'\\FoobarObserver@saved')
            ->shouldReceive('fire')->once()
                ->with('saving: '.__NAMESPACE__.'\\ObservableStub', $stub)
            ->shouldReceive('fire')->once()
                ->with('saved: '.__NAMESPACE__.'\\ObservableStub', $stub)
            ->shouldReceive('forget')->once()
                ->with('saving: '.__NAMESPACE__.'\\ObservableStub')
            ->shouldReceive('forget')->once()
                ->with('saved: '.__NAMESPACE__.'\\ObservableStub');

        ObservableStub::setEventDispatcher($dispatcher);

        ObservableStub::observe(new FoobarObserver());

        $stub->save();

        $this->assertFalse($stub->saving);
        $this->assertFalse($stub->saved);

        ObservableStub::flushEventListeners();
    }
}

class ObservableStub
{
    use Observable;

    public $saving = false;

    public $saved = false;

    public function save()
    {
        $this->fireObservableEvent('saving', false);
        $this->fireObservableEvent('saved', false);
    }

    public function getObservableEvents()
    {
        return ['saving', 'saved'];
    }
}

class ObservableStubWithoutEvents
{
    use Observable;
}

class FoobarObserver
{
    public function saving($stub)
    {
        $stub->saving = true;
    }

    public function saved($stub)
    {
        $stub->saving = true;
    }
}
