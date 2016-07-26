<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Default Model
 *
 * @package		Default name
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once PATH_THIRD.'gmaps/config.php';

class Gmaps_model
{

	private $EE;

	public function __construct()
	{							
		// Creat EE Instance
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Cout all itemst
	 *
	 * @access	public
	 * @return	array
	 */
	public function count_items()
	{
		$q = ee()->db->get(GMAPS_MAP.'_cache');
		return $q->num_rows();
	}

	// --------------------------------------------------------------------

	/**
	 * Cout all itemst
	 *
	 * @access	public
	 * @return	array
	 */
	public function delete_cache()
	{
		ee()->db->empty_table(GMAPS_MAP.'_cache');
	}

	

	// --------------------------------------------------------------------

	/**
	 * Get all aliases
	 *
	 * @access	public
	 * @return	void
	 */
	public function get_all_items($cache_id = '', $start = 0, $limit = false, $order = array())
	{
		$results = array();
		$q = '';

		//get all alias for an specific site_id
		if($cache_id == '')
		{
			ee()->db->select('*');
			ee()->db->from(GMAPS_MAP.'_cache');
		}

		//Fetch a list of entries in array
		else if(is_array($cache_id) && !empty($cache_id))
		{
			ee()->db->select('*');
			ee()->db->from(GMAPS_MAP.'_cache');
			ee()->db->where_in('cache_id', $cache_id);
		}

		//fetch only the alias for an entry_id
		else if(!is_array($cache_id))
		{
			ee()->db->select('*');
			ee()->db->from(GMAPS_MAP.'_cache');
			ee()->db->where('cache_id', $cache_id);
		}

		//do nothing
		else
		{
			return array();
		}

		//is there a start and limit
		if($limit !== false)
		{
			ee()->db->limit($start, $limit);
		}

		//do we need to order
		//given by the mcp table method http://ellislab.com/expressionengine/user-guide/development/usage/table.html
		if(!empty($order))
		{
			if(isset($order[GMAPS_MAP.'_address']))
			{
				ee()->db->order_by('address', $order[GMAPS_MAP.'_address']);	
			}
			if(isset($order[GMAPS_MAP.'_lat']))
			{
				ee()->db->order_by('lat', $order[GMAPS_MAP.'_lat']);	
			}
			if(isset($order[GMAPS_MAP.'_lng']))
			{
				ee()->db->order_by('lng', $order[GMAPS_MAP.'_lng']);	
			}
			if(isset($order[GMAPS_MAP.'_date']))
			{
				ee()->db->order_by('date', $order[GMAPS_MAP.'_date']);	
			}
		}
		
		//get the result
		$q = ee()->db->get();

		//format result
		if($q != '' && $q->num_rows())
		{
			foreach($q->result() as $val)
			{
				$results[] = $val;
			}
		}

		return $results;
	}

} // END CLASS

/* End of file default_model.php  */
/* Location: ./system/expressionengine/third_party/default/models/default_model.php */