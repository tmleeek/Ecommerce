<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Update description
 *
 * @package             Gmaps for EE2
 * @author              Rein de Vries (info@reinos.nl)
 * @copyright           Copyright (c) 2013 Rein de Vries
 * @license  			http://reinos.nl/add-ons/commercial-license
 * @link                http://reinos.nl/add-ons/gmaps
 */
 
include(PATH_THIRD.'gmaps/config.php');
 
class Gmaps_upd_302
{
	private $EE;
	private $version = '3.0.2';
	
	// ----------------------------------------------------------------

	/**
	 * Construct method
	 *
	 * @return      boolean         TRUE
	 */
	public function __construct()
	{		
		//load the classes
		ee()->load->dbforge();

		//require the settings
		require PATH_THIRD.'gmaps/settings.php';
	}

	// ----------------------------------------------------------------
	
	/**
	 * Run the update
	 *
	 * @return      boolean         TRUE
	 */
	public function run_update()
	{
		ee()->db->insert('extensions', array(
			'class'		=> GMAPS_CLASS.'_ext',
			'method'	=> 'sessions_end',
			'hook'		=> 'sessions_end',
			'settings'	=> '',
			'priority'	=> 10,
			'version'	=> GMAPS_VERSION,
			'enabled'	=> 'y'
		));
	}
}