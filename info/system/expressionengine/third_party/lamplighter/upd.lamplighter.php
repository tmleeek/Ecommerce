<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once PATH_THIRD.'/lamplighter/config.php';

/**
 * Lamplighter Module Install/Update File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Masuga Design
 * @link
 */
class Lamplighter_upd
{

	public $version = LAMPLIGHTER_VERSION;

	private $EE;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}

	// ----------------------------------------------------------------

	/**
	 * Installation Method
	 *
	 * @return 	boolean 	TRUE
	 */
	public function install()
	{
		// Load dbforge
		$this->EE->load->dbforge();

		$licensing = array(
			'id' 		=> array('type' => 'INT', 'unsigned' => TRUE,	'auto_increment' => TRUE),
			'key' 		=> array('type' => 'VARCHAR', 'constraint' => 255),
			'lamplighter_site_id'	=> array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0),
			'site_id'	=> array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0),
		);
		$this->EE->dbforge->add_field($licensing);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('lamplighter_license', TRUE);

		$this->_create_lamplighter_hidden_addons_table();

		$mod_data = array(
			'module_name'			=> 'Lamplighter',
			'module_version'		=> $this->version,
			'has_cp_backend'		=> 'y',
			'has_publish_fields'	=> 'n'
		);
		$this->EE->db->insert('modules', $mod_data);

		$data = array(
		    'class'     => 'Lamplighter',
		    'method'    => 'api_request'
		);
		// csrf_exempt was added in a later release of ExpressionEngine.
		if ( $this->EE->db->field_exists('csrf_exempt', 'actions')) {
			$data['csrf_exempt'] = 1;
		}
		$this->EE->db->insert('actions', $data);

		return TRUE;
	}

	// ----------------------------------------------------------------

	/**
	 * Uninstall
	 *
	 * @return 	boolean 	TRUE
	 */
	public function uninstall()
	{
		$this->EE->load->dbforge();
		$this->EE->dbforge->drop_table('lamplighter_license');

		$mod_id = $this->EE->db->select('module_id')
								->get_where('modules', array(
									'module_name'	=> 'Lamplighter'
								))->row('module_id');

		$this->EE->db->where('module_id', $mod_id)
					 ->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Lamplighter')
					 ->delete('modules');

		$this->EE->db->where('class', 'Lamplighter')
					 ->delete('actions');

		return TRUE;
	}

	// ----------------------------------------------------------------

	/**
	 * Module Updater
	 *
	 * @return 	boolean 	TRUE
	 */
	public function update($current = '')
	{

		// Are they the same?
		if ($current == $this->version)
		{
			return FALSE;
		}

		// Load DB Forge
		$this->EE->load->dbforge();

		// Confirm the hidden add-ons table exists (not sure when this was originally added)
		$this->_create_lamplighter_hidden_addons_table();

		//----------
		// v1.2.1
		//----------
		if (version_compare('1.2.1', $current) == 1) {

			/*
			Wouldn't normally need to check this but it is better to be
			safe than sorry since the update process was incorrect prior
			to 1.2.3
			*/
			if ( ! $this->EE->db->field_exists('lamplighter_site_id', 'lamplighter_license') ) {

				$this->EE->dbforge->modify_column('lamplighter_license',
					array(
						'site_id' => array(
							'name' => 'lamplighter_site_id',
							'type' => 'INT'
						)
					)
				);

				$this->EE->dbforge->add_column('lamplighter_license',
					array(
						'site_id' => array(
							'type' => 'INT'
						)
					)
				);

				$this->EE->db->set('site_id', $this->EE->config->item('site_id'))
						->update('lamplighter_license');

			}
		}

		/*
		csrf_exempt was added in a later release of ExpressionEngine. Let's always be sure
		that any existing installation is updated if EE has been updated since Lamplighter
		was originally installed.
		*/
		if ( $this->EE->db->field_exists('csrf_exempt', 'actions')) {
			$this->EE->db->update('actions', array(
				'csrf_exempt' => 1
			), array(
				'class' => 'Lamplighter'
			));
		}

		return TRUE;
	}

	/**
	 * This method creates the "lamplighter_hidden_addons" table if it doesn't
	 * already exist. This method was added to v1.3.0 for EE3 support since this
	 * table was normally added by the accessory which is no longer available in
	 * EE3.
	 */
	protected function _create_lamplighter_hidden_addons_table()
	{
		if ( ! $this->EE->db->table_exists('lamplighter_hidden_addons') ) {
			$this->EE->dbforge->add_field('id int(10) unsigned NOT NULL AUTO_INCREMENT');
			$this->EE->dbforge->add_field('member_id int(10) unsigned NOT NULL');
			$this->EE->dbforge->add_field('package varchar(100) NOT NULL');
			$this->EE->dbforge->add_key('id', TRUE);
			$this->EE->dbforge->add_key('member_id');
			$this->EE->dbforge->create_table('lamplighter_hidden_addons', TRUE);
		}
	}

}
