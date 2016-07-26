<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Gmaps Extension
 *
 * @package		gmaps
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl/add-ons/gmaps
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */

include(PATH_THIRD.'gmaps/config.php');
 
class gmaps_ext 
{	
	
	public $name			= GMAPS_NAME;
	public $description		= GMAPS_DESCRIPTION;
	public $version			= GMAPS_VERSION;
	public $settings 		= array();
	public $docs_url		= GMAPS_DOCS;
	public $settings_exist	= 'n';
	public $required_by 	= array('Gmaps Module');
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{		
		//require the settings
		require PATH_THIRD.'gmaps/settings.php';
	}

	// ----------------------------------------------------------------

	/**
	 * sessions_start
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	function sessions_start($ee)
	{
		//set the session to the var
		ee()->session = $ee;

//		header('Access-Control-Allow-Origin: *');
//
//		//is the first segment 'entry_api'
//		$is_gmaps = ee()->uri->segment(1) == 'gmaps_api' ? true : false;
//
//		//is the request a page and is the first segment entry_api?
//		//than we need to trigger te services
//		if (REQ == 'PAGE' && $is_gmaps)
//		{
//			//load lib
//			ee()->load->library('gmaps_library', null, 'gmaps');
//
//			//stop the whole process because we will not show futher more
//			ee()->extensions->end_script = true;
//
//			//no input
//			if(ee()->input->post('input') == '')
//			{
//				echo 'no_post_value';
//				die();
//			}
//
//			//no method
//			if(ee()->uri->segment(2) == '')
//			{
//				echo 'no_method';
//				die();
//			}
//
//			//input value
//			$input = explode('|', ee()->input->post('input'));
//
//			//result var
//			$result = '';
//
//			switch(ee()->uri->segment(2))
//			{
//				case 'address':
//					$result = ee()->gmaps->geocode_address($input, 'php', 'all');
//					break;
//				case 'latlng':
//					$result = ee()->gmaps->geocode_latlng($input, 'php', 'all');
//					break;
//				case 'ip':
//					$result = ee()->gmaps->geocode_ip($input, 'php', 'all');
//					break;
//			}
//			//echo a json object
//			if($result != '')
//			{
//				echo json_encode($result);
//			}
//			else
//			{
//				echo 'no_result';
//			}
//
//			die();
//		}
	}

	// ----------------------------------------------------------------

	/**
	 * sessions_start
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	function sessions_end($ee)
	{
		//set the session to the var
		ee()->session = $ee;

//		//CP action
//		//is the first segment 'entry_api'
//		$is_gmaps_action = ee()->uri->segment(1) == 'gmaps_act' ? true : false;
//
//		//is the request a page and is the first segment entry_api?
//		//than we need to trigger te services
//		if ($is_gmaps_action)
//		{
//			// Load Library
//			if (class_exists('Gmaps_ACT') != TRUE) include 'act.gmaps.php';
//
//			$ACT = new Gmaps_ACT();
//
//			$ACT->init();
//
//			die();
//		}
	}

	// ----------------------------------------------------------------------

	/**
	 * Method for ee_debug_toolbar_add_panel hook
	 *
	 * @param 	array	Array of debug panels
	 * @param 	arrat	A collection of toolbar settings and values
	 * @return 	array	The amended array of debug panels
	 */
	public function ee_debug_toolbar_add_panel($panels, $view)
	{
		// do nothing if not a page
		if(REQ != 'PAGE') return $panels;

		//load lib
		ee()->load->library('gmaps_library', null, 'gmaps');

		// play nice with others
		$panels = (ee()->extensions->last_call != '' ? ee()->extensions->last_call : $panels);
	
		$panels['gmaps'] = new Eedt_panel_model();
		$panels['gmaps']->set_name('gmaps');
		$panels['gmaps']->set_button_label("Gmaps");
		$panels['gmaps']->set_panel_contents(ee()->load->view('debug_panel', array('logs' => gmaps_helper::get_log()), TRUE));

		if(gmaps_helper::log_has_error())
		{
			$panels['gmaps']->set_panel_css_class('flash');
		}

		return $panels;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		//the module will install the extension if needed
		return true;
	}	
	
	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		//the module will disable the extension if needed
		return true;
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		//the module will update the extension if needed
		return true;
	}	
	
	// ----------------------------------------------------------------------
}

/* End of file ext.gmaps.php */
/* Location: /system/expressionengine/third_party/gmaps/ext.gmaps.php */