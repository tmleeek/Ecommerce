<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Gmaps Module Control Panel File
 *
 * @package             Gmaps for EE2
 * @author              Rein de Vries (info@reinos.nl)
 * @copyright           Copyright (c) 2013 Rein de Vries
 * @license  			http://reinos.nl/add-ons/commercial-license
 * @link                http://reinos.nl/add-ons/gmaps
 */

class Gmaps_mcp {
	
	public $return_data;
	public $settings;
	
	private $show_per_page = 25;
	private $_base_url;

	private $error_msg;


	/**
	 * Constructor
	 */
	public function __construct()
	{		
		//load the library`s
		ee()->load->library('table');
		ee()->load->library('gmaps_library', null, 'gmaps');
		ee()->load->helper('form');	
	   
	   //set the right nav
		$right_nav = array();
		//$right_nav[lang('fb_tools_overview')] = $this->settings['base_url'];
		$right_nav[lang('gmaps_settings')] = ee()->gmaps_settings->item('base_url').AMP.'method=settings';
		$right_nav[lang('gmaps_show_cache')] = ee()->gmaps_settings->item('base_url').AMP.'method=cache';
		$right_nav[lang('gmaps_delete_cache')] = ee()->gmaps_settings->item('base_url').AMP.'method=delete_cache';
		//$right_nav[lang('gmaps_importer')] = ee()->gmaps_settings->item('base_url').AMP.'method=importer';
		ee()->cp->set_right_nav($right_nav);
		
		//require the default settings
		require PATH_THIRD.GMAPS_MAP.'/settings.php';

		// License check.
		//if ( ! $this->license()) return;
	}
	
	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @return 	void
	 */
	public function index()
	{
		// Set Breadcrumb and Page Title
		$this->_set_cp_var('cp_page_title', lang(GMAPS_MAP.'_module_name'));
		$vars['cp_page_title'] = lang(GMAPS_MAP.'_module_name');

		//show error if needed
		if ($this->error_msg != '')
		{
			return $this->error_msg;
		}

		//load the view
		return $this->settings(); 
	}

	// ----------------------------------------------------------------

	/**
	 * Settings Function
	 *
	 * @return 	void
	 */
	public function settings()
	{
		//reset the settings?
		if(isset($_GET['action']) && $_GET['action'] == 'reset')
		{
			ee()->gmaps_settings->save_setting('geocoding_providers', array('google_maps'));

			//set a message
			ee()->session->set_flashdata(
				'message_success',
				ee()->lang->line('preferences_updated')
			);

			//redirect
			ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.GMAPS_MAP.AMP.'method=settings');

		}

		//is there some data tot save?
		if(ee()->input->post('submit') != '')
		{
			ee()->gmaps_settings->save_post_settings();
		}
				
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->gmaps_settings->item('base_url'), lang(GMAPS_MAP.'_module_name'));
		$this->_set_cp_var('cp_page_title', lang(GMAPS_MAP.'_settings'));
		$vars['cp_page_title'] = lang(GMAPS_MAP.'_settings');

		//default var array
		$vars = array();
		
		//settings
		$license_key = ee()->gmaps_settings->item('license_key');
		$report_stats = ee()->gmaps_settings->item('report_stats');
		$data_transfer = ee()->gmaps_settings->item('data_transfer');
		$dev_mode = ee()->gmaps_settings->item('dev_mode');
		$geocoding_providers = array_filter((array)ee()->gmaps_settings->item('geocoding_providers'));
		
		//vars for the view and the form
		$vars['settings']['default'] = array(
			GMAPS_MAP.'_license_key'   => form_input('license_key', $license_key),
			GMAPS_MAP.'_report_stats'  => array(form_dropdown('report_stats', array('1' => 'yes', '0' => 'no'), $report_stats), 'PHP & EE versions will be anonymously reported to help improve the product.'),
			GMAPS_MAP.'_dev_mode'  => array(form_dropdown('dev_mode', array('0' => 'no', '1' => 'yes'), $dev_mode), 'Set the Gmaps in dev mode and serve the JS files no as minified.'),
			GMAPS_MAP.'_data_transfer'  => array(form_dropdown('data_transfer', array('curl' => 'Curl', 'http' => 'HTTP'), $data_transfer), 'What kind of connection will be used to geocode the address'),
		);

		//vars for the view and the form
		foreach($this->def_geocoding_providers as $provider => $val)
		{

			$options = array(
				'name' => 'geocoding_providers[]',
				'value' => $provider,
				'checked' => (array_search($provider, $geocoding_providers) !== false ? true : false),
				'id'          => 'provider_'.$provider,
     		);

			//google maps is enabled
			if($provider == 'google_maps')
			{
				$options['disabled'] = '';

			}

			$sub = isset($val[1]) ? $val[1] : '';
			$vars['settings']['geocoding_providers'][$provider] = array(form_checkbox($options), $sub);
		}
		
		//load the view
		return ee()->load->view('settings', $vars, TRUE);   
	}

	// ----------------------------------------------------------------

	/**
	 * Overview Function
	 *
	 * @return 	void
	 */
	public function delete_cache()
	{
		if(ee()->input->post('confirm') == 'ok')
		{
			ee()->gmaps_model->delete_cache();

			//set a message
			ee()->session->set_flashdata(
				'message_success',
				ee()->lang->line('cache_deleted')
			);
			
			//redirect
			ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.GMAPS_MAP.AMP.'method=cache');			
	
		}
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->gmaps_settings->item('base_url'), lang(GMAPS_MAP.'_module_name'));
		$this->_set_cp_var('cp_page_title', lang(GMAPS_MAP.'_delete_cache'));
		$vars['cp_page_title'] = lang(GMAPS_MAP.'_delete_cache');


		//set vars
		$vars = array();

		//load the view
		return ee()->load->view('delete_cache', $vars, TRUE);  
	}

	// ----------------------------------------------------------------

	/**
	 * Overview Function
	 *
	 * @return 	void
	 */
	public function bulk_add_cache()
	{
		if(isset($_POST['submit']))
		{
			$data = ee()->gmaps->parse_channel_data(ee()->input->post('address'));
			$data = explode("\n", $data);

			
print_r($data);
			exit;

			//set a message
			ee()->session->set_flashdata(
				'message_success',
				ee()->lang->line('cache_deleted')
			);
			
			//redirect
			ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.GMAPS_MAP.AMP.'method=cache');			
	
		}
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->gmaps_settings->item('base_url'), lang(GMAPS_MAP.'_module_name'));
		$this->_set_cp_var('cp_page_title', lang(GMAPS_MAP.'_add_cache'));
		$vars['cp_page_title'] = lang(GMAPS_MAP.'_add_cache');


		//set vars
		$vars = array();

		//load the view
		return ee()->load->view('add_cache', $vars, TRUE);  
	}

	// ----------------------------------------------------------------

	/**
	 * Overview Function
	 *
	 * @return 	void
	 */
	public function cache()
	{
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->gmaps_settings->item('base_url'), lang(GMAPS_MAP.'_module_name'));
		$this->_set_cp_var('cp_page_title', lang(GMAPS_MAP.'_cache'));
		$vars['cp_page_title'] = lang(GMAPS_MAP.'_cache');


		//set vars
		$vars['theme_url'] = ee()->gmaps_settings->item('theme_url');
		$vars['base_url_js'] = ee()->gmaps_settings->item('base_url_js');
		$vars['table_headers'] = $this->table_headers;

		//load the view
		return ee()->load->view('cache_overview', $vars, TRUE);  
	}

	// ----------------------------------------------------------------

	/**
	 * This method will be called by the table class to get the results
	 *
	 * @return 	void
	 */
	public function _datasource($state)
	{
		$offset = $state['offset'];
		$order = $state['sort'];

		$results = ee()->gmaps_model->get_all_items('', $this->show_per_page, $offset, $order);

		$rows = array();

		if(!empty($results))
		{
			foreach($results as $key=>$val)
			{
				$rows[] = array(
					GMAPS_MAP.'_address' => $val->address,
					GMAPS_MAP.'_lat' => $val->lat,
					GMAPS_MAP.'_lng' => $val->lng,
					GMAPS_MAP.'_date' => ee()->localize->format_date('%d/%m/%Y', $val->date),
					//'actions' => '',
				);
			}
		}
		//empty
		else
		{
			$rows[] = array(
				GMAPS_MAP.'_address' => '',
				GMAPS_MAP.'_lat' => '',
				GMAPS_MAP.'_lng' => '',
				GMAPS_MAP.'_date' => '',
				//'actions' => '',
			);
		}

		//return the data
		return array(
			'rows' => $rows,
			'pagination' => array(
				'per_page'   => $this->show_per_page,
				'total_rows' => ee()->gmaps_model->count_items(),
			),
		);
	}

	// ----------------------------------------------------------------

	/**
	 * Overview Function
	 *
	 * @return 	void
	 */
	public function importer()
	{
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->gmaps_settings->item('base_url'), lang(GMAPS_MAP.'_module_name'));
		$this->_set_cp_var('cp_page_title', lang(GMAPS_MAP.'_cache'));
		$vars['cp_page_title'] = lang(GMAPS_MAP.'_cache');


		//set vars
		$vars['theme_url'] = ee()->gmaps_settings->item('theme_url');
		$vars['base_url_js'] = ee()->gmaps_settings->item('base_url_js');
		$vars['table_headers'] = $this->table_headers;

		//load the view
		return ee()->load->view('import_log', $vars, TRUE);
	}

	// ----------------------------------------------------------------

	/**
	 * This method will be called by the table class to get the results
	 *
	 * @return 	void
	 */
	public function _datasource_importer($state)
	{
		$offset = $state['offset'];
		$order = $state['sort'];

		$results = ee()->gmaps_model->get_all_items('', $this->show_per_page, $offset, $order);

		$rows = array();

		if(!empty($results))
		{
			foreach($results as $key=>$val)
			{
				$rows[] = array(
					GMAPS_MAP.'_address' => $val->address,
					GMAPS_MAP.'_lat' => $val->lat,
					GMAPS_MAP.'_lng' => $val->lng,
					GMAPS_MAP.'_date' => ee()->localize->format_date('%d/%m/%Y', $val->date),
					//'actions' => '',
				);
			}
		}
		//empty
		else
		{
			$rows[] = array(
				GMAPS_MAP.'_address' => '',
				GMAPS_MAP.'_lat' => '',
				GMAPS_MAP.'_lng' => '',
				GMAPS_MAP.'_date' => '',
				//'actions' => '',
			);
		}

		//return the data
		return array(
			'rows' => $rows,
			'pagination' => array(
				'per_page'   => $this->show_per_page,
				'total_rows' => ee()->gmaps_model->count_items(),
			),
		);
	}

	// ----------------------------------------------------------------

	/**
	 * license check
	 *
	 * @return 	void
	 */
	public function license()
	{ 
		if(!ee()->gmaps->License_check())
		{
			// Set Breadcrumb and Page Title
			ee()->cp->set_breadcrumb(ee()->gmaps_settings->item('base_url'), lang(GMAPS_MAP.'_module_name'));
			$this->_set_cp_var('cp_page_title', lang(GMAPS_MAP.'_settings'));
			$vars['cp_page_title'] = lang(GMAPS_MAP.'_settings');

			$this->error_msg =
				 "Your license key appears to be invalid. You can get a valid one here: "
				. "<a target='_blank' href='".GMAPS_DEVOTEE."'>".GMAPS_NAME."</a>. ";

			return false;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set cp var
	 *
	 * @access     private
	 * @param      string
	 * @param      string
	 * @return     void
	 */
	private function _set_cp_var($key, $val)
	{
		if (version_compare(APP_VER, '2.6.0', '<'))
		{
			ee()->cp->set_variable($key, $val);
		}
		else
		{
			ee()->view->$key = $val;
		}
	}

}

/* End of file mcp.gmaps.php */
/* Location: /system/expressionengine/third_party/gmaps/mcp.gmaps.php */

