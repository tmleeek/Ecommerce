<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------
 
/**
 * Encaf Structure First Accessory
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Accessory
 * @author		Encaffeinated, Inc.
 * @link		http://encaffeinated.com
 */

/** -------------------------------------
 ** Encaf Structure First
 ** Copyright 2012 Encaffeinated, Inc.
 ** License: Each purchase of this extension is licensed for a single ExpressionEngine installation.
 ** www.encaffeinated.com
 ** Version 1.2 - 1/27/2012
 ** -------------------------------------
 ** Version History
 ** 1.2 - 1/27/2012 - added config file variable for custom tab title
 ** 1.1 - 1/26/2012 - limit functionality to members who have permission to use Structure module
 ** 1.0 - 1/25/2012 - initial release
 ** -------------------------------------*/
 
class Structure_first_acc {
	
	public $name			= 'Encaf Structure First';
	public $id				= 'structure_first';
	public $version			= '1.2';
	public $description		= 'Add a Structure tab to the CP Main Menu in the first position for all users.';
	public $sections		= array();
	
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();
	} 

	/**
	 * Set Sections
	 */
	public function set_sections()
	{
		
		//make sure Structure is installed
		$query = $this->EE->db->query("SELECT module_id from exp_modules where module_name = 'Structure'");
		
		    if ($query->num_rows() != 0) { //continue

		    	// get structure's module_id
		    	$module_id = $query->row('module_id');
				
				//hide accessory tab, we dont need it
				$this->sections[] = '<script type="text/javascript" charset="utf-8">$("#accessoryTabs a.structure_first").parent().remove();</script>';

				//get the user's group_id & member_id
				$group_id = $this->EE->session->userdata['group_id'];
				$member_id = $this->EE->session->userdata['member_id'];

				// no group_id = fail
				if(!$group_id) {
					return;
				}

				//make sure user can access Structure
				$query = $this->EE->db->query("SELECT group_id from exp_module_member_groups where group_id = '{$group_id}' and module_id = '{$module_id}'");
				
				    if ($query->num_rows() > 0 OR $group_id == '1') {
				    	
						//data returned- the user can access strucure, also keep going for super admins
						
						//kill other structure tabs
						$query = $this->EE->db->query("SELECT quick_tabs from exp_members where quick_tabs like '%module=structure%' AND member_id = '{$member_id}'");
						
						    if ($query->num_rows() > 0) {
								foreach($query->result_array() as $row) {
									$all_tabs = $row['quick_tabs'];
									$tabs = explode("\n",$all_tabs);
									foreach($tabs as $key=>$value) {
										if(substr_count($value, 'module=structure')) {
										    unset($tabs[$key]);
										}
									}
									$all_tabs = implode("\n",$tabs);
									$data = array(
										'quick_tabs' => $all_tabs,
										);
									$sql = $this->EE->db->update_string('exp_members', $data, "member_id = '{$member_id}'");
									$this->EE->db->query($sql);							
								}					
							}

						//get the custom tab name from config file
						$tab_title = ( $this->EE->config->item('structure_first_tab_title') ) ? $this->EE->config->item('structure_first_tab_title') : 'Structure';

						//build html
						$html = '<li><a href="'.BASE.'&C=addons_modules&M=show_module_cp&module=structure" class="first_level" tabindex="-1">'.$tab_title.'</a></li>';

						//add slashes
						$html = addslashes($html);

						//add css, js and html
						$this->EE->cp->add_to_head('
						<!-- Structure First begin -->
						<script type="text/javascript">
							var structure_first = \''.$html.'\';
							$(document).ready(function()
							{
									$("div#mainMenu ul#navigationTabs li.home").after($(structure_first));
							});
						</script>');

					}

					// if user can't access structure, we just stay silent. nothing to do.

			}

	}
	
	// ----------------------------------------------------------------
	
}
 
/* End of file acc.structure_first.php */
/* Location: /system/expressionengine/third_party/structure_first/acc.structure_first.php */