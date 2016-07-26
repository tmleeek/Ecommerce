<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  GMAPS settings
 *
 * @package		Gmaps
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @link        http://reinos.nl/add-ons/gmaps
 * @copyright 	Copyright (c) 2012 Reinos.nl Internet Media
 */

require_once(PATH_THIRD.'gmaps/config.php');
require_once(PATH_THIRD.'gmaps/libraries/gmaps_helper.php');

class Gmaps_settings {

	private $EE;
	private $_config_items = array();

	private $_config_defaults = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
		//load string helper
		ee()->load->helper('string');

		//set the default settings
		$this->default_settings = array(
			'module_dir'   => PATH_THIRD.GMAPS_MAP.'/',
			'theme_dir'   => PATH_THEMES.'third_party/'.GMAPS_MAP.'/',
			'site_id' => ee()->config->item('site_id'),
			'site_url' => reduce_double_slashes(ee()->config->item('site_url').'/'.ee()->config->item('site_index').'/'),
			'base_dir' => $_SERVER['DOCUMENT_ROOT'].'/',
		);		
		
		//if older than EE 2.4
		if(!defined('URL_THIRD_THEMES'))
		{
			//set the theme url
			$theme_url = ee()->config->slash_item('theme_folder_url') != '' ? ee()->config->slash_item('theme_folder_url').'third_party/'.GMAPS_MAP.'/' : ee()->config->item('theme_folder_url') .'third_party/'.GMAPS_MAP.'/'; 
			
			//lets define the URL_THIRD_THEMES
			$this->default_settings['theme_url'] = $theme_url;
		}
		else
		{
			//set the Theme dir
			$this->default_settings['theme_url'] = URL_THIRD_THEMES.GMAPS_MAP.'/';
		}
		
		// DB and BASE dependend
		if(isset(ee()->db))
		{
			//is the BASE constant defined?
			if(!defined('BASE'))
			{
				$s = '';

				if (ee()->config->item('admin_session_type') != 'c')
				{
					if(isset(ee()->session))
					{
						$s = ee()->session->userdata('session_id', 0);
					}
				}
				
				//lets define the BASE
				define('BASE', SELF.'?S='.$s.'&amp;D=cp');	
			}
			
			$this->default_settings['base_url'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.GMAPS_MAP;		
			$this->default_settings['base_url_js'] = str_replace('&amp;', '&', $this->default_settings['base_url']);	
		}

		//require the settings
		require PATH_THIRD.'gmaps/settings.php';

		//Custom (override) Config vars
		if(!empty($this->overide_settings))
		{
			foreach($this->overide_settings as $key=>$val)
			{
				//default value for the override, its always filled in otherwise with the default value
				$this->default_settings[$key] = ee()->config->item($key) != '' ? ee()->config->item($key) : str_replace(array('[theme_dir]', '[theme_url]'), array($this->default_settings['theme_dir'], $this->default_settings['theme_url']), $val);

				//suffix _override is the real override value, its only filled in when its overrided
				$this->default_settings[$key.'_override'] = ee()->config->item($key);
			}
		}
		
		//check if all default settings are present
		//e.g. for MSM we must recreate the settings for the other site_id
		$this->check_db_settings();

		//get the settings
		$this->settings = $this->load_settings();
	}

	// ----------------------------------------------------------------

	/**
	 * Insert the settings to the database
	 *
	 * @param none
	 * @return void
	 */
	public function first_import_settings()
	{	
		foreach($this->default_post as $key=>$val)
		{
			$data[] = array(
				'site_id' => $this->default_settings['site_id'],
				'var' => $key,
				'value'=> $val,
			);
		}
		
		//insert into db
		ee()->db->insert_batch(GMAPS_MAP.'_settings', $data);
		
		//clear data
		unset($data);
	}
	
	// ----------------------------------------------------------------

	/**
	 * check if all default settings are present
	 * e.g. for MSM we must recreate the settings for the other site_id
	 *
	 * @param none
	 * @return void
	 */
	public function check_db_settings()
	{
		if (ee()->db->table_exists(GMAPS_MAP.'_settings'))
        {
        	//check if there is any result
        	$check = ee()->db->from(GMAPS_MAP.'_settings')->get();
        	if($check->num_rows() > 0)
        	{
				//get the site IDS
				$sites = $this->get_sites();
			
				//loop over the sites
				foreach($sites as $site_id)
				{
					foreach($this->default_post as $key=>$val)
					{
						//check the setting
						ee()->db->where('var', $key);
						ee()->db->where('site_id', $site_id);
						$q = ee()->db->get(GMAPS_MAP.'_settings');
						
						//if the setting not presents, we have to create this one
						if($q->num_rows() == 0)
						{
							$data = array(
								'site_id' => $site_id,
								'var' => $key,
								'value'=> $this->get_db_settings(1, $key),
							);
							
							//insert into db
							ee()->db->insert(GMAPS_MAP.'_settings', $data);
						}
					}
				}
			}
		}
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Get the Settings
	 *
	 * @param $all_sites
	 * @return mixed array
	 */
	public function load_settings($site_id = '')
	{
		if (ee()->db->table_exists(GMAPS_MAP.'_settings'))
        {
	        //get the settings from the database
			$get_setting = ee()->db->get_where(GMAPS_MAP.'_settings', array(
				'site_id' => ($site_id == '' ? $this->default_settings['site_id'] : $site_id)
			));

			//load helper
			ee()->load->helper('string');
			
			//set the settings
			$settings = array();
			foreach ($get_setting->result() as $row)
			{
				//is serialized?
                if(gmaps_helper::is_serialized($row->value))
                {
                    $settings[$row->var] = @unserialize($row->value);
                }
                //default value
                else
                {
                    $settings[$row->var] = $row->value;   
                }
			}
			
			//clear data
			unset($get_setting);
			
			//return the settings
			return array_merge($this->default_settings, $settings);
		}
	}

	// ----------------------------------------------------------------------
    
    /**
     * Get specific setting
     *
     * @param $all_sites
     * @return mixed array
     */
    public function get_settings($setting_name)
    {
        if(isset($this->settings[$setting_name]))
        {
            return $this->settings[$setting_name];
        }
        return '';
    }
    //alias
    public function get_setting($setting_name)
    {
    	return $this->get_settings($setting_name);
    }
    //alias
    public function item($setting_name)
    {
    	return $this->get_settings($setting_name);
    }
	
	// ----------------------------------------------------------------------
    
    /**
     * Get specific setting from the db instead of the array
     *
     * @param $all_sites
     * @return mixed array
     */
    public function get_db_settings($site_id = '', $setting_name = '')
    {
    	ee()->db->where('site_id', $site_id);
		ee()->db->where('var', $setting_name);
		ee()->db->from(GMAPS_MAP.'_settings');
		$q = ee()->db->get();
		
		if($q->num_rows() > 0)
		{
			return $q->row()->value;
		}
        return '';
    }

    // ----------------------------------------------------------------

	/**
	 * Prepare settings to save
	 *
	 * @return 	DB object
	 */
	public function save_post_settings()
	{
		if(isset($_POST))
		{
			//remove submit value
			unset($_POST['submit']);
		
			//loop through the post values
			foreach($_POST as $key=>$val)
			{
				$this->save_setting($key, $val);
			}
		}
		
		//set a message
		ee()->session->set_flashdata(
			'message_success',
			ee()->lang->line('preferences_updated')
		);
		
		//redirect
		ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.GMAPS_MAP.AMP.'method=settings');			
	}

	// ----------------------------------------------------------------

	/**
	 * Prepare settings to save
	 *
	 * @return 	DB object
	 */
	public function save_setting($key = '', $val = '')
	{
		if($key != '' && $val != '')
		{
			//set the where clause
			ee()->db->where('var', $key);
			ee()->db->where('site_id', $this->item('site_id'));
			
			//is this a array?
			if(is_array($val))
			{
				$val = serialize($val);
			}

			//update the record
			ee()->db->update(GMAPS_MAP.'_settings', array(
				'value' => $val
			));		
		}
	}

	// ----------------------------------------------------------------

	/**
	 * set a static setting
	 *
	 * @return 	DB object
	 */
	public function set_setting($key = '', $val = '')
	{
		$this->settings[$key] = $val;
	}
	
	// ----------------------------------------------------------------

	/**
	 * Get the sites
	 *
	 * @return 	DB object
	 */
	public function get_sites()
	{
		ee()->db->select('site_id');
		$sites = ee()->db->get('sites');
		
		$return = array();
		
		if($sites->num_rows() > 0)
		{
			foreach($sites->result() as $site)
			{
				$return[] = $site->site_id;
			}
		}
	
		return $return;
	}
}

/* End of file ./libraries/gmaps_settings.php */