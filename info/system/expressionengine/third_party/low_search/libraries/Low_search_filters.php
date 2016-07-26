<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Search Filters class, to run all filters
 *
 * @package        low_search
 * @author         Lodewijk Schutte ~ Low <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-search
 * @copyright      Copyright (c) 2015, Low
 */
class Low_search_filters {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Path to keep filter files
	 *
	 * @access     private
	 * @var        string
	 */
	private $_filters_path;

	/**
	 * Name of filters path, also for 3rd party filters
	 *
	 * @access     private
	 * @var        string
	 */
	private $_filters_dir = 'filters';

	/**
	 * Filter objects
	 *
	 * @access     private
	 * @var        array
	 */
	private $_filters = array();

	/**
	 * Entry ids
	 *
	 * @access     private
	 * @var        mixed     [null|array]
	 */
	private $_entry_ids;

	/**
	 * Are the entry ids in a fixed order?
	 *
	 * @access     private
	 * @var        bool
	 */
	private $_fixed_order;

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access     public
	 * @return     void
	 */
	public function __construct()
	{
		// include parent filter class
		if ( ! class_exists('Low_search_filter'))
		{
			require_once(PATH_THIRD.'low_search/filter.low_search.php');
		}

		// Load directory helper
		ee()->load->helper('directory');

		// Loop through all filters dirs found
		foreach ($this->_get_filters_dirs() AS $path)
		{
			// Read filters directory and load up the filters
			foreach (directory_map($path, 1) AS $item)
			{
				// Compose directory
				$dir = $path.$item;

				// Skip if not a dir
				if ( ! is_dir($dir)) continue;

				// Compose file name
				$file = $dir."/lsf.{$item}.php";

				// Skip if not a file
				if ( ! file_exists($file)) continue;

				// Compose class name
				$class = 'Low_search_filter_'.$item;

				if ( ! class_exists($class))
				{
					require_once($file);
				}

				$this->_filters[$item] = new $class();
			}
		}

		// Sort by priority
		uasort($this->_filters, array($this, '_by_priority'));
	}

	// --------------------------------------------------------------------

	/**
	 * Return the loaded filter names
	 *
	 * @access     public
	 * @return     array
	 */
	public function names()
	{
		return array_keys($this->_filters);
	}

	// --------------------------------------------------------------------

	/**
	 * Run loaded filters
	 *
	 * @access     public
	 * @return     void
	 */
	public function filter($reset = TRUE)
	{
		// Reset first?
		if ($reset) $this->reset();

		// Loop through each filter and run it
		foreach ($this->_filters AS $name => $filter)
		{
			// Skip disabled filters
			if ($this->_disabled($name)) continue;

			// Get IDs from filter
			$ids = $filter->filter($this->_entry_ids);

			// Are these IDs in fixed order?
			$fixed = $filter->fixed_order();

			// Keep existing order if there are results
			$this->_entry_ids
				= ( ! empty($this->_entry_ids) && ! empty($ids) && ! $fixed)
				? array_values(array_intersect($this->_entry_ids, $ids))
				: $ids;

			// Remember that we're returning a fixed order
			if ( ! $this->_fixed_order && $fixed)
			{
				$this->_fixed_order = $fixed;
			}

			// Break out when there aren't any search results
			if (is_array($this->_entry_ids) && empty($this->_entry_ids)) break;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Run loaded filters
	 *
	 * @access     public
	 * @param      array
	 * @return     array
	 */
	public function results($rows)
	{
		// Loop through each filter and run it
		foreach ($this->_filters AS $name => $filter)
		{
			// Skip disabled filters
			if ($this->_disabled($name)) continue;

			// Or else, trigger the results method
			$rows = $filter->results($rows);
		}

		return $rows;
	}

	// --------------------------------------------------------------------

	/**
	 * Set entry ids
	 *
	 * @access     public
	 * @param      array
	 */
	public function set_entry_ids($entry_ids)
	{
		$this->_entry_ids = (array) $entry_ids;
	}

	// --------------------------------------------------------------------

	/**
	 * Return the entry ids
	 *
	 * @access     public
	 * @return     mixed     [null|array]
	 */
	public function entry_ids()
	{
		return $this->_entry_ids;
	}

	// --------------------------------------------------------------------

	/**
	 * Return the entry ids
	 *
	 * @access     public
	 * @return     bool
	 */
	public function fixed_order()
	{
		return $this->_fixed_order;
	}

	// --------------------------------------------------------------------

	/**
	 * Reset IDs and whatnot
	 *
	 * @access     public
	 * @return     void
	 */
	public function reset()
	{
		$this->_entry_ids = NULL;
		$this->_fixed_order = NULL;
	}

	// --------------------------------------------------------------------

	/**
	 * Is filter enabled?
	 */
	private function _disabled($name)
	{
		return in_array($name, ee()->low_search_settings->get('disabled_filters'));
	}

	// --------------------------------------------------------------------

	/**
	 * Order filters by priority
	 */
	private function _by_priority($a, $b)
	{
		$a = $a->priority();
		$b = $b->priority();

		if ($a > $b) return 1;
		if ($a == $b) return 0;
		if ($a < $b) return -1;
	}

	// --------------------------------------------------------------------

	/**
	 * Get filters directories from all add-ons
	 */
	private function _get_filters_dirs()
	{
		// Init directories
		$dirs = array();

		// Read 3rd party dir
		foreach (directory_map(PATH_THIRD, 1) AS $item)
		{
			// The paths we're looking for
			$path = PATH_THIRD.$item;
			$dir  = $path."/{$this->_filters_dir}/";

			// Skip if we're not dealing with dirs
			if (@is_dir($path) && @is_dir($dir))
			{
				$dirs[] = $dir;
			}
		}

		return $dirs;
	}
}
// End of file Low_search_filters.php