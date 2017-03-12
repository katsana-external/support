<?php

namespace Orchestra\Support\TestCase\Traits;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Facade;
use Orchestra\Support\Traits\Uploadable;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadableTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        $app = new Container();

        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
    }

    /**
     * Teardown the test environment.
     */
    protected function tearDown()
    {
        m::close();
    }

    /**
     * Test Orchestra\Support\Traits\Uploadable::saveUploadedFile() method.
     *
     * @test
     */
    public function testSaveUploadedFileMethod()
    {
        $path = '/var/www/public/';
        $file = m::mock('\Symfony\Component\HttpFoundation\File\UploadedFile[getClientOriginalExtension,move]', [
            realpath(__DIR__.'/fixtures').'/test.gif',
            'test',
        ]);

        $file->shouldReceive('getClientOriginalExtension')->once()->andReturn('jpg')
            ->shouldReceive('move')->once()->with($path, m::type('String'))->andReturnNull();

        $stub = new UploadedStub();

        $filename = $stub->save($file, $path);
    }

    /**
     * Test Orchestra\Support\Traits\Uploadable::saveUploadedFile() method
     * when custom getUploadedFilename() are available.
     *
     * @test
     */
    public function testSaveUploadedFileMethodWithCustomFilename()
    {
        $path = '/var/www/public/';
        $file = m::mock('\Symfony\Component\HttpFoundation\File\UploadedFile[move]', [
            realpath(__DIR__.'/fixtures').'/test.gif',
            'test',
        ]);

        $file->shouldReceive('move')->once()->with($path, 'foo.jpg')->andReturnNull();

        $stub = new UploadedStubWithReplacement();

        $filename = $stub->save($file, $path);
    }

    /**
     * Test Orchestra\Support\Traits\Uploadable::deleteUploadedFile() method.
     *
     * @test
     */
    public function testDeleteMethod()
    {
        $filesystem = m::mock('\Illuminate\Filesystem\Filesystem');
        $filename = '/var/www/foo.jpg';

        $filesystem->shouldReceive('delete')->once()->with($filename)->andReturn(true);

        File::swap($filesystem);

        $stub = new UploadedStub();

        $this->assertTrue($stub->delete($filename));
    }
}

class UploadedStub
{
    use Uploadable;

    public function save(UploadedFile $file, $path)
    {
        return $this->saveUploadedFile($file, $path);
    }

    public function delete($file)
    {
        return $this->deleteUploadedFile($file);
    }
}

class UploadedStubWithReplacement
{
    use \Orchestra\Support\Traits\Uploadable;

    public function save(UploadedFile $file, $path)
    {
        return $this->saveUploadedFile($file, $path);
    }

    public function delete($file)
    {
        return $this->deleteUploadedFile($file);
    }

    protected function getUploadedFilename(UploadedFile $file)
    {
        return 'foo.jpg';
    }
}
