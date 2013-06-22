<?php namespace Orchestra\Support;

use Closure;
use InvalidArgumentException;

abstract class Manager extends \Illuminate\Support\Manager {

	/**
	 * Create a new instance.
	 *
	 * @access public
	 * @param  string   $driver
	 * @return mixed
	 * @see    self::driver()
	 */
	public function make($driver = null) 
	{
		return $this->driver($driver);
	}

	/**
	 * Create a new driver instance.
	 *
	 * @access protected
	 * @param  string  $driverName
	 * @return mixed
	 */
	protected function createDriver($driverName)
	{
		list($driver, $name) = $this->getDriverName($driverName);

		$method = 'create'.Str::studly($driver).'Driver';

		// We'll check to see if a creator method exists for the given driver. 
		// If not we will check for a custom driver creator, which allows 
		// developers to create drivers using their own customized driver 
		// creator Closure to create it.
		if (isset($this->customCreators[$driver]))
		{
			return $this->callCustomCreator($driverName);
		}
		elseif (method_exists($this, $method))
		{
			return call_user_func(array($this, $method), $name);
		}

		throw new InvalidArgumentException("Driver [$driver] not supported.");
	}

	/**
	 * Call a custom driver creator.
	 *
	 * @access protected
	 * @param  string  $driverName
	 * @return mixed
	 */
	protected function callCustomCreator($driverName)
	{
		list($driver, $name) = $this->getDriverName($driverName);

		return call_user_func($this->customCreators[$driver], $this->app, $name);
	}

	/**
	 * Get driver name.
	 * 
	 * @access protected
	 * @param  string   $driverName
	 * @return array
	 */
	protected function getDriverName($driverName)
	{
		if (false === strpos($driverName, '.')) $driverName = $driverName.'.default';

		list($driver, $name) = explode('.', $driverName, 2);

		if (Str::contains($name, '.'))
		{
			throw new InvalidArgumentException("Invalid character in driver name [{$name}].");
		}

		return array($driver, $name);
	}

}
