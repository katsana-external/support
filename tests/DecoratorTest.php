<?php namespace Orchestra\Support\Tests;

class DecoratorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Stub instance.
	 *
	 * @var Orchestra\Support\Decorator
	 */
	protected $stub = null;

	/**
	 * Setup the test environment.
	 */
	public function setUp()
	{
		$this->stub = new DecoratorStub(function ($t) {});
	}

	/**
	 * Teardown the test environment.
	 */
	public function tearDown()
	{
		unset($this->stub);
	}
	
	/**
	 * Test Instance of Orchestra\Support\Decorator.
	 *
	 * @test
	 * @group support
	 */	
	public function testInstanceOfDecorator()
	{
		$stub1 = new DecoratorStub(function ($t) { });
		$stub2 = DecoratorStub::make(function ($t) { });
		
		$refl = new \ReflectionObject($stub1);
		$name = $refl->getProperty('name');
		$grid = $refl->getProperty('grid');
		
		$name->setAccessible(true);
		$grid->setAccessible(true);

		$this->assertInstanceOf('\Orchestra\Support\Decorator', $stub1);
		$this->assertInstanceOf('\Orchestra\Support\Decorator', $stub2);

		$this->assertNull($name->getValue($stub1));
		$this->assertNull($stub1->name);
		$this->assertInstanceOf('\Orchestra\Support\Tests\DecoratorGrid', 
			$grid->getValue($stub1));
		$this->assertInstanceOf('\Orchestra\Support\Tests\DecoratorGrid', $stub1->grid);
		$this->assertNull($grid->getValue($stub1)->name);
		$this->assertNull($stub1->grid->name);
	}

	/**
	 * Test Orchestra\Support\Decorator::extend() method.
	 *
	 * @test
	 * @support support
	 */
	public function testExtendMethod()
	{
		$stub = new DecoratorStub(function ($t) {});
		
		$this->assertNull($stub->grid->name);

		$stub->extend(function ($g)
		{
			$g->name = 'foo';
		});

		$this->assertEquals('foo', $stub->grid->name);
	}

	/**
	 * Test Orchestra\Support\Decorator::__get throws exception.
	 *
	 * @expectedException \InvalidArgumentException
	 * @group support
	 */
	public function testMagicMethodGetThrowsException()
	{
		$expected = $this->stub->expected;
	}
	
	/**
	 * test Orchestra\Support\Decorator::render() method.
	 *
	 * @test
	 * @group support
	 */
	public function testRenderMethod()
	{
		$mock1 = new DecoratorStub(function ($t) {});
		$mock2 = new DecoratorStub(function ($t) {});

		ob_start();
		echo $mock1;
		$output = ob_get_contents();
		ob_end_clean();

		$this->assertEquals('foo', $output);
		$this->assertEquals('foo', $mock2->render());
	}
}

class DecoratorGrid {

	public $name = null;

}

class DecoratorStub extends \Orchestra\Support\Decorator {

	public function __construct(\Closure $callback)
	{
		$this->grid = new DecoratorGrid;
	}

	public function render()
	{
		return 'foo';
	}
}