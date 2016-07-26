<?php
/**
 * Devotee Library
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Masuga Design
 * @link		http://www.masugadesign.com
 */

class Devotee_library
{
	protected $_addons = array();
	protected $EE = null;
	public $cache_path = null;
	public $cache_file = null;

	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->add_package_path(PATH_THIRD.'lamplighter');
		$this->EE->load->library('lamplighter_library');

		if ($this->EE->config->item('lamplighter_cachepath')) {
			$this->cache_path = $this->EE->config->item('lamplighter_cachepath');
		} else if ($this->EE->config->item('devotee_monitor_cachepath')) {
			$this->cache_path = $this->EE->config->item('devotee_monitor_cachepath');
		} else {
			$this->cache_path = $this->EE->lamplighter_library->ee3() ? SYSPATH.'user/cache/lamplighter/' : APPPATH . 'cache/lamplighter/';
		}
		// Create cache folder if it doesn't exist
		if( ! is_dir($this->cache_path))
		{
			mkdir($this->cache_path, DIR_WRITE_MODE);
		}
		// Establish the name of the cache file based on the path.
		$this->cache_file = $this->cache_path . 'addons';
		// Set the default cache time limit.
		$this->_cache_time = 60 * 60; // 1 hour
		// Include the ignored_addons array
		require PATH_THIRD.'lamplighter/ignored_addons.php';
		if (is_array($this->EE->config->item('lamplighter_ignored_addons'))) {
			$this->ignored_addons = array_merge($ignored_addons, $this->EE->config->item('lamplighter_ignored_addons'));
		} else if (is_array($this->EE->config->item('devotee_monitor_ignored_addons'))) {
			$this->ignored_addons = array_merge($ignored_addons, $this->EE->config->item('devotee_monitor_ignored_addons'));
		} else {
			$this->ignored_addons = $ignored_addons;
		}

	}

	/**
	 * This method deletes the cached file of available add-on updates.
	 */
	public function clear_cache()
	{
		if ( file_exists($this->cache_file) ) {
			unlink($this->cache_file);
		}
	}

	/**
	 * Get installed add-on information
	 *
	 * @param Boolean $show_hidden_addons
	 * @param Boolean $return_view
	 *
	 * @return Mixed
	 */
	public function get_addons($show_hidden_addons = FALSE, $return_view=TRUE, $ignore_cache=FALSE)
	{
		$this->EE->load->helper(array('file', 'language'));

		// If cache is still good, use it
		// Otherwise, fetch new data
		if(!$ignore_cache && file_exists($this->cache_file) AND filemtime($this->cache_file) > (time() - $this->_cache_time))
		{
			$updates = read_file($this->cache_file);
		}
		elseif($this->EE->input->get('C') == 'addons_plugins')
		{
			return $this->EE->load->view('acc_error', array(
				'error' => 'Sorry, but the plugins page causes issues when pulling add-on information.',
				'cp' => $this->EE->cp
			), TRUE);
		}
		else
		{
			$this->EE->load->helper('directory');
			$this->EE->load->library('addons');
			$this->EE->load->model('addons_model');
			$this->EE->load->library('api');

			// Scan third_party folder
			$map = directory_map(PATH_THIRD, 2);

			// Bail out if nothing found
			if($map === FALSE) {
				return 'No third-party add-ons were found.';
			}
			if ( $this->EE->lamplighter_library->ee3() ) {
				$this->EE->legacy_api->instantiate('channel_fields');
			} else {
				$this->EE->api->instantiate('channel_fields');
			}
			// Get fieldtypes because the add-ons library doesn't give all the info
			$fieldtypes = $this->EE->api_channel_fields->fetch_all_fieldtypes();

			// Set third-party add-ons
			$third_party = array_intersect_key($this->EE->addons->_packages, $map);

			// Get all installed add-ons
			$installed = array(
				'modules'     => $this->EE->addons->get_installed('modules'),
				'plugins'     => $this->get_plugins(),
				'extensions'  => $this->EE->addons->get_installed('extensions'),
				'fieldtypes'  => $this->EE->addons->get_installed('fieldtypes'),
				'accessories' => $this->EE->addons->get_installed('accessories')
			);

			// Loop through each third-party package
			foreach($third_party as $package => $types)
			{
				// Skip this if we already have it
				if(array_key_exists($package, $this->_addons))
				{
					continue;
				}

				// Check if this is a module
				if(array_key_exists($package, $installed['modules']))
				{
					$addon = $installed['modules'][$package];

					// Fix weird EE name issue
					$this->EE->lang->loadfile(( ! isset($this->lang_overrides[$package])) ? $package : $this->lang_overrides[$package]);
					$name = (lang(strtolower($package) . '_module_name') != FALSE) ? lang(strtolower($package) . '_module_name') : $addon['name'];

					$this->set_addon_info($package, $name, $addon['module_version'], $types);
				}
				// Check if this is a plugin
				elseif(array_key_exists($package, $installed['plugins']))
				{
					$addon = $installed['plugins'][$package];
					$this->set_addon_info($package, $addon['pi_name'], $addon['pi_version'], $types);
				}
				// Check if this is an extension
				elseif(array_key_exists($package, $installed['extensions']))
				{
					$addon = $installed['extensions'][$package];
					$this->set_addon_info($package, $addon['name'], $addon['version'], $types);
				}
				// Check if this is a fieldtype
				elseif(array_key_exists($package, $installed['fieldtypes']))
				{
					$addon = $fieldtypes[$package];
					$this->set_addon_info($package, $addon['name'], $addon['version'], $types);
				}
				// Check if this is an accessory
				elseif(array_key_exists($package, $installed['accessories']))
				{
					$addon = $installed['accessories'][$package];

					// We need to load the class if it's not devot:ee to get more info
					// Otherwise, we already have the info
					if($package != 'lamplighter')
					{
						if( ! class_exists($addon['class']))
						{
							require_once PATH_THIRD . "{$package}/acc.{$package}.php";
						}

						$acc = new $addon['class']();
					}
					else
					{
						$acc = array(
							'name'    => 'Lamplighter',
							'version' => LAMPLIGHTER_VERSION
						);
						$acc = (object) $acc;
					}

					if(isset($acc))
					{
						$this->set_addon_info($package, $acc->name, $acc->version, $types);

						unset($acc);
					}
				}
			}

			// Remove ignored add-ons from the _addons data member prior to fetching updates
			foreach ($this->ignored_addons as $index => $package)
				unset($this->_addons[$package]);

			// Check updates
			$updates = $this->get_updates();

			$updates_decoded = json_decode($updates);
			if( ! $updates_decoded)
			{
				return $this->EE->load->view('acc_error', array(
					'error' => 'Sorry, but something went wrong. Please try again later.',
					'cp' => $this->EE->cp
				), TRUE);
			}
			elseif( ! empty($updates_decoded->error))
			{
				return $this->EE->load->view('acc_error', array(
					'error' => $updates_decoded->error,
					'cp' => $this->EE->cp
				), TRUE);
			}

			// Write to cache
			write_file($this->cache_file, $updates);
		}

		$hidden_addons = $this->fetch_hidden_addons();

		if ( $return_view ) {

			// Return the view
			return $this->EE->load->view('acc_accessory', array(
				'updates'       => json_decode($updates),
				'last_check'    => $this->last_check_date(),
				'hidden_addons' => $hidden_addons,
				'show_hidden'	=> $show_hidden_addons,
				'cp'			=> $this->EE->cp
			), TRUE);

		} else {
			return json_decode($updates);
		}
	}

	/**
	 * Set add-on info
	 *
	 * @param   string  The package name
	 * @param   string  The actual add-on name
	 * @param   string  The version number
	 * @param   array   Add-on types (module, plugin, etc.)
	 * @return  void
	 */
	public function set_addon_info($package, $name, $version, $types)
	{
		$this->_addons[$package] = array(
			'name'    => $name,
			'version' => $version,
			'types'   => $this->abbreviate_types(array_keys($types))
		);
	}

	/**
	 * Get update info from the API
	 *
	 * @return  string
	 */
	public function get_updates()
	{
		$data = array(
			'data'    => $this->_addons,
			'site'    => md5( $this->EE->config->item('site_label') ),
			'version' => defined('APP_VER') ? APP_VER : $this->EE->config->item('app_version')
		);

		$ch = curl_init('https://monitor.devot-ee.com/');
		curl_setopt_array($ch, array(
			CURLOPT_POST           => TRUE,
			CURLOPT_CONNECTTIMEOUT => 2,
			CURLOPT_TIMEOUT        => 5,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_POSTFIELDS     => json_encode($data),
			CURLOPT_HTTPHEADER     => array(
				'Content-type: application/json'
			)
		));
		$response = curl_exec($ch);
		curl_close($ch);

		if( ! $response)
		{
			$response = json_encode(array(
				'error' => 'The API could not be reached. Please try again later.'
			));
		}

		return $response;
	}

	/**
	 * Create an abbreviated list of add-on types, and designate whether the current add-on
	 * is of a particular type
	 *
	 * @param   array  The add-on types
	 * @return  array
	 */
	public function abbreviate_types($types = array())
	{
		$available_types = array(
			'module'    => 'MOD',
			'extension' => 'EXT',
			'plugin'    => 'PLG',
			'fieldtype' => 'FLD',
			'accessory' => 'ACC'
		);

		$abbrevs = array();

		foreach($available_types as $key => $abbrev)
		{
			$abbrevs[$abbrev] = (in_array($key, $types)) ? TRUE : FALSE;
		}

		return $abbrevs;
	}

	/**
	 * This method fetches an array of add-ons that should not be displayed in
	 * the list of add-ons.
	 * @return array
	 */
	public function fetch_hidden_addons()
	{
		// Hidden add-ons
		$hidden_addon_query = $this->EE->db->select('package')
			->where('member_id', $this->EE->session->userdata('member_id'))
			->get('lamplighter_hidden_addons');
		$hidden_addons = array();
		foreach($hidden_addon_query->result() as $hid_ad) {
			$hidden_addons[] = $hid_ad->package;
		}
		return $hidden_addons;
	}

	/**
	 * Fetch the "last checked" date when the add-on update info was cached.
	 * @return integer
	 */
	public function last_check_date()
	{
		return method_exists($this->EE->localize, 'string_to_timestamp') ? $this->EE->localize->string_to_timestamp(date('Y-m-d H:i:s',filemtime($this->cache_file)), true) : filemtime($this->cache_file);
	}

	/**
	 * Get Plugins
	 *
	 * This method is a replacement for the method of the same name
	 * in the EE addons_model. The way it was written would cause errors
	 * in the CP when third party add-ons would get a list of plugins installed.
	 *
	 */
	function get_plugins()
	{
		$info = array();
		// EE3 has the plugins table of installed plugins. Also, plugins no longer have the $plugin_info array in the files.
		if ( $this->EE->lamplighter_library->ee3() ) {
			$plugin_results = $this->EE->db->get('plugins')->result_array();
			foreach($plugin_results as $row) {
				$info[$row['plugin_package']] = array(
					'pi_name' => $row['plugin_name'],
					'pi_version' => $row['plugin_version']
				);
			}
		// EE2 needs to use the old way of looking for plugin files in add-on folders.
		} else {
			$this->EE->load->helper('directory');
			$plugins = array();
			$info 	= array();
			$ext_len = strlen('.php');
			if (($map = directory_map(PATH_PI, TRUE)) !== FALSE) {
				foreach ($map as $file) {
					if (strncasecmp($file, 'pi.', 3) == 0 && substr($file, -$ext_len) == '.php' && strlen($file) > strlen('pi..php')) {
						if ( ! file_exists(PATH_PI.$file) || ! include(PATH_PI.$file) ) {
							continue 1;
						}
						$name = substr($file, 3, -$ext_len);
						$plugins[] = $name;
						if ( isset($plugin_info) ) {
							$info[$name] = array_unique($plugin_info);
						}
					}
				}
			}
			if (($map = directory_map(PATH_THIRD, 2)) !== FALSE) {
				foreach ($map as $pkg_name => $files) {
					if ( ! is_array($files)) {
						$files = array($files);
					}
					foreach ($files as $file) {
						if (is_array($file)) {
							// we're only interested in the top level files for the addon
							continue 1;
						}
						elseif (strncasecmp($file, 'pi.', 3) == 0 &&
								substr($file, -$ext_len) == '.php' &&
								strlen($file) > strlen('pi..php')) {
							if ( ! class_exists(ucfirst($pkg_name))) {
								if ( ! file_exists(PATH_THIRD.$pkg_name.'/'.$file) || ! include(PATH_THIRD.$pkg_name.'/'.$file) ) {
									continue 1;
								}
							}
							$plugins[] = $pkg_name;
							if ( isset($plugin_info) ) {
								$info[$pkg_name] = array_unique($plugin_info);
							}
						}
					}
				}
			}
		}
		return $info;
	}

	/**
	 * This method adds a row to the lamplighter_hidden_addons table based
	 * on package name and member ID.
	 * @param string $package
	 * @param integer $member_id
	 */
	public function hide_addon($package, $member_id=null)
	{
		$this->EE->db->insert('lamplighter_hidden_addons', array(
			'member_id' => $member_id,
			'package'   => $package
		));
	}

	/**
	 * This method removes a row from the lamplighter_hidden_addons table based
	 * on package name and member ID.
	 * @param string $package
	 * @param integer $member_id
	 */
	public function unhide_addon($package, $member_id=null)
	{
		$this->EE->db->where('package', $package)->where('member_id', $member_id)->delete('lamplighter_hidden_addons');
	}
}
