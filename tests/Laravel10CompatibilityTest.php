<?php

namespace Orchestra\Support\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Support\Providers\ServiceProvider;
use Orchestra\Testbench\TestCase;

class Laravel10CompatibilityTest extends TestCase
{
    /** @test */
    public function it_can_boot_with_laravel_10()
    {
        $this->assertInstanceOf(Application::class, $this->app);
        $this->assertTrue(version_compare($this->app->version(), '10.0', '>='));
    }

    /** @test */
    public function service_provider_can_be_instantiated()
    {
        $stub = new class($this->app) extends ServiceProvider {
            public function register()
            {
                //
            }

            public function boot()
            {
                //
            }
        };

        $this->assertInstanceOf(ServiceProvider::class, $stub);
    }

    /** @test */
    public function str_helper_methods_work()
    {
        $this->assertEquals('foo-bar', \Orchestra\Support\Str::slug('Foo Bar'));
        $this->assertEquals('Foo Bar', \Orchestra\Support\Str::humanize('foo-bar'));
    }

    /** @test */
    public function arr_helper_methods_work()
    {
        $array = ['foo.bar' => 'baz'];
        $expanded = \Orchestra\Support\Arr::expand($array);
        
        $this->assertEquals(['foo' => ['bar' => 'baz']], $expanded);
    }
}