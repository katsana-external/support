<?php namespace Orchestra\Support;

use Closure,
	Illuminate\Support\Facades\Config,
	Illuminate\Support\Facades\Lang,
	Illuminate\Support\Facades\Request,
	Illuminate\Support\Facades\View;

class Table extends Builder {

	/**
	 * All of the registered table names.
	 *
	 * @var array
	 */
	public static $names = array();

	/**
	 * Create a new table instance of a named table.
	 *
	 * <code>
	 *		// Create a new table instance
	 *		$view = Table::of('user-table', function ($table) {
	 *			$table->with(User::all());
	 *
	 *			$table->column('username');
	 *			$table->column('password');
	 * 		});
	 * </code>
	 *
	 * @static
	 * @access   public
	 * @param    string	    $name
	 * @param    Closure	$callback
	 * @return   Table
	 */
	public static function of($name, Closure $callback = null)
	{
		if ( ! isset(static::$names[$name]))
		{
			static::$names[$name] = new static($callback);

			static::$names[$name]->name = $name;
		}

		return static::$names[$name];
	}
	
	/**
	 * Create a new Table instance.
	 * 			
	 * @access public
	 * @param  Closure      $callback
	 * @return void	 
	 */
	public function __construct(Closure $callback)
	{
		// Initiate Table\Grid, this wrapper emulate table designer
		// script to create the table.
		$this->grid = new Table\Grid(Config::get('orchestra::support.table', array()));
		
		$this->extend($callback);	
	}

	/**
	 * Render the table
	 *
	 * @access  public
	 * @return  string
	 */
	public function render()
	{
		// localize Table\Grid object
		$grid  = $this->grid;
		
		// Add paginate value for current listing while appending query string
		$input = Request::query();

		// we shouldn't append ?page
		if (isset($input['page'])) unset($input['page']);

		$paginate = (true === $grid->paginate ? $grid->model->appends($input)->links() : '');

		$emptyMessage = $grid->emptyMessage;

		if ( ! ($emptyMessage instanceof Lang))
		{
			$emptyMessage = Lang::get($emptyMessage);
		}

		$data = array(
			'tableAttributes' => $grid->attributes,
			'rowAttributes'   => $grid->rows->attributes,
			'emptyMessage'    => $emptyMessage,
			'columns'         => $grid->columns(),
			'rows'            => $grid->rows(),
			'pagination'      => $paginate,
		);

		// Build the view and render it.
		return View::make($grid->view)->with($data)->render();
	}
}