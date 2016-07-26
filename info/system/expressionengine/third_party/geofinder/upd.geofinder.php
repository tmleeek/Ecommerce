<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Geofinder Module
 *
 * @package		Geofinder
 * @category	Modules
 * @author		Natural Logic, Jason Ferrell
 * @link		http://natural-logic.com
 */

class Geofinder_upd {

	var $version				= '2.5';
	var $module_name			= 'Geofinder';

	function Geofinder_upd()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	}
	
	// --------------------------------------------------------------------
	
	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */

    function install()
    {
		$this->EE->load->dbforge();
		
		$data = array(
			'module_name' 	 => $this->module_name,
			'module_version' => $this->version,
			'has_cp_backend' => 'n',
			'has_publish_fields' => 'n'
		);

		$this->EE->db->insert('modules', $data);
		
		$data = array(
					'class' => $this->module_name,
					'method' => 'find_locations'
					);				
		$this->EE->db->insert('actions', $data); 

		$data = array(
					'class' => $this->module_name,
					'method' => 'find_members'
					);				
		$this->EE->db->insert('actions', $data);
		
		$data = array(
					'class' => $this->module_name,
					'method' => 'find_geocode'
					);					
		$this->EE->db->insert('actions', $data);
		
		$fields = array(
					'geofinder_id'	=> array('type' => 'int', 'constraint' => '6', 'unsigned' => TRUE, 'auto_increment' => TRUE),
					'geoquery'		=> array('type'	=> 'varchar', 'constraint'	=> '255'),
					'latitude'		=> array('type' => 'text'),
					'longitude'		=> array('type' => 'text'),
					'country_code'  => array('type' => 'varchar', 'constraint' => '10')
			);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('geofinder_id', TRUE);

		$this->EE->dbforge->create_table('geofinder');

		return TRUE;		
    }

	// --------------------------------------------------------------------

	/**
	 * Module Uninstaller
	 *
	 * @access	public
	 * @return	bool
	 */

    function uninstall()
    {
		$this->EE->load->dbforge();

		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => $this->module_name));
		$module_id = $query->row('module_id');
		
		$this->EE->db->where('module_id', $module_id);
		$this->EE->db->delete('module_member_groups');
		
		$this->EE->db->where('module_name', $this->module_name);
		$this->EE->db->delete('modules');
		
		$this->EE->db->where('class', $this->module_name);
		$this->EE->db->delete('actions');
		
		$this->EE->db->where('class', $this->module_name.'_mcp');
		$this->EE->db->delete('actions');
		
		$this->EE->dbforge->drop_table('geofinder');

		return TRUE;
    }

	// --------------------------------------------------------------------

	/**
	 * Module Updater
	 *
	 * @access	public
	 * @return	bool
	 */
	function update($current = '')
	{
		$this->current = $current;
		if ($this->current == $this->version)
		{
			return FALSE;
		}
		return TRUE;
	}
    // END
}
// End Geofinder Update Class

/* End of file upd.geofinder.php */
/* Location: ./system/expressionengine/third_party/geofinder/upd.geofinder.php */