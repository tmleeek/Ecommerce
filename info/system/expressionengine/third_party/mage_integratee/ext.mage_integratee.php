<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mage_integratee_ext {

    var $name               = 'Mage Integratee';
    var $version            = '2.1.2';
    var $description        = 'Integrate Magento Layouts into ExpressionEngine Pages';
    var $settings_exist     = 'y';
    var $docs_url           = ''; // 'http://expressionengine.com/user_guide/';

    var $settings           = array();

    /**
     * Constructor
     *
     * @param   mixed   Settings array or empty string if none exist.
     */
    function __construct($settings='')
    {
        $this->settings = $settings;
        $this->site_id = ee()->config->item('site_id');
    }
    // END

    // --------------------------------
    //  Initialize Layout
    // --------------------------------

    function init_layout($row)
    {

        if(!isset($this->settings[$this->site_id])) {
            return $row;
        }

        $prefs = $this->settings[$this->site_id];

        //Get blocks
        $blocks = $prefs['blocks'];
        foreach(explode(",",$prefs['custom']) AS $block){
            if($block != "") {
                $blocks[$block] = 1;                    
            }
        }

        //Get Magento Run Codes
        $mageRunCode = $prefs['run_code'];
        $mageRunType = $prefs['run_type'];

        //die(print_r($prefs));

        //Set Magento Run Codes
        if(isset( ee()->config->_global_vars['MAGE_INTEGRATEE_RUN_CODE']))
        {
            $mageRunCode = ee()->config->_global_vars['MAGE_INTEGRATEE_RUN_CODE'];
        }

        if(isset( ee()->config->_global_vars['MAGE_INTEGRATEE_RUN_TYPE']))
        {
            $mageRunType = ee()->config->_global_vars['MAGE_INTEGRATEE_RUN_TYPE'];
        }

        //Check if template is excluded from processing
        if(in_array($row['template_id'], $prefs['exclude']))
        {
            return $row;
        }

        //Load and Cache Magento Layout
        if ( ! ee()->session->cache(__CLASS__, 'mage'))
        {

            //Verify Magento path
            if($prefs['mage'] === ""){
                return $row;
            } else {
              $mage = $prefs['mage'];
            }

            //Set path to /app directory
            if($mage[strlen($mage)-1] !== "/"){
                $mage .= "/app/Mage.php";
            } else {
                $mage .= "app/Mage.php";
            }

            //Initialize Magento
            require_once $mage;
            Mage::app($mageRunCode,$mageRunType);
            Mage::getSingleton('core/session', array('name'=>'frontend'));

            //Check if logged in
            if(Mage::getSingleton('customer/session')->IsLoggedIn()){ 
                //Load Layout Handles
                $layout = Mage::app()->getLayout();
                $layout->getUpdate()
                    ->addHandle('default')
                    ->addHandle('expressionengine_integratee')
                    ->addHandle('customer_logged_in')
                    ->load();

                ee()->config->_global_vars['mage:is_logged_in'] = true;
                ee()->config->_global_vars['mage:is_logged_out'] = false;
                ee()->config->_global_vars['mage:customer_group_id'] = Mage::getSingleton('customer/session')->getCustomerGroupId();
                ee()->config->_global_vars['mage:customer_group'] = Mage::getModel('customer/group')->load(Mage::getSingleton('customer/session')->getCustomerGroupId())->getCode();
            }
            else
            { 
                //Load Layout Handles
                $layout = Mage::app()->getLayout();
                $layout->getUpdate()
                    ->addHandle('default')
                    ->addHandle('expressionengine_integratee')
                    ->addHandle('customer_logged_out')
                    ->load();

                ee()->config->_global_vars['mage:is_logged_in'] = false;
                ee()->config->_global_vars['mage:is_logged_out'] = true;
                ee()->config->_global_vars['mage:customer_group_id'] = Mage::getSingleton('customer/session')->getCustomerGroupId();
                ee()->config->_global_vars['mage:customer_group'] = Mage::getModel('customer/group')->load(Mage::getSingleton('customer/session')->getCustomerGroupId())->getCode();
            }

            //Generate Blocks
            $layout->generateXml()
                ->generateBlocks();

            ee()->session->set_cache(__CLASS__, 'mage', '1');

        } else {

            Mage::app($mageRunCode,$mageRunType);
            Mage::getSingleton('core/session', array('name'=>'frontend'));

            //Check if logged in
            if(Mage::getSingleton('customer/session')->IsLoggedIn()){ 
                //Load Layout Handles
                $layout = Mage::app()->getLayout();
                $layout->getUpdate()
                    ->addHandle('default')
                    ->addHandle('expressionengine_integratee')
                    ->addHandle('customer_logged_in')
                    ->load();
            }
            else
            { 
                //Load Layout Handles
                $layout = Mage::app()->getLayout();
                $layout->getUpdate()
                    ->addHandle('default')
                    ->addHandle('expressionengine_integratee')
                    ->addHandle('customer_logged_out')
                    ->load();
            }

            //Generate Blocks
            $layout->generateXml()
                ->generateBlocks();

        }

        //Load Blocks
        foreach ($blocks as $block => $enabled) {
            
            //Check if block is enabled
            if(!$enabled) {
                continue;
            }

            //Check if block is cached
            if(ee()->session->cache(__CLASS__, $block)) {
                ee()->config->_global_vars['mage:'.$block] = ee()->session->cache(__CLASS__, $block);
                continue;
            }
            //Get block data
            $data = str_replace('?___SID=U', '', $layout->getBlock($block)->toHtml());

            //Set Cache
            ee()->session->set_cache(__CLASS__, $block, $data);

            //Set Variable
            ee()->config->_global_vars['mage:'.$block] = $data;


        }

        //Return Extension Data
        return $row;

    }
    // END


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
    function activate_extension()
    {

        // hooks array
        $hooks = array(
          'template_fetch_template' => 'init_layout'
        );

        // insert hooks and methods
        foreach ($hooks AS $hook => $method)
        {
          // data to insert
          $data = array(
            'class'     => get_class($this),
            'method'    => $method,
            'hook'      => $hook,
            'priority'  => 10,
            'version'   => $this->version,
            'enabled'   => 'y',
            'settings'  => ''
          );

          // insert in database
          ee()->db->insert('extensions', $data);
        }
    }
    // END

    /**
     * Update Extension
     *
     * This function performs any necessary db updates when the extension
     * page is visited
     *
     * @return  mixed   void on update / false if none
     */
    function update_extension($current = '')
    {
        if ($current == '' OR $current == $this->version)
        {
            return FALSE;
        }

        if ($current < '2.0.0')
        {
            // Update to version 1.0
        }

        ee()->db->where('class', __CLASS__);
        ee()->db->update(
                    'extensions',
                    array('version' => $this->version)
        );
    }
    // END

    /**
     * Disable Extension
     *
     * This method removes information from the exp_extensions table
     *
     * @return void
     */
    function disable_extension()
    {
        ee()->db->where('class', __CLASS__);
        ee()->db->delete('extensions');
    }
    // END

    // --------------------------------
    //  Settings
    // --------------------------------

    function settings_form($current)
    {
        $this->settings = $current;
        
        if(ee()->input->post('mage')) {
          $this->save_settings_form();
        }

        $prefs = $this->_fetch_preferences();
            
        $template_group_query = ee()->db->select('group_id, group_name')->where('site_id', $this->site_id)->get('template_groups');

        $templates = array();

        foreach($template_group_query->result() as $row) {
          
          $template = ee()->db->query("SELECT template_id as id, template_name as name FROM exp_templates WHERE group_id = ".$row->group_id);
          
          $templates[$row->group_name] = $template->result_array();
        }

        $vars = array(
          'templates' => $templates,
          'prefs' => $prefs
        );

        return ee()->load->view('settings_form', $vars, TRUE);

    }
    // END settings_form

    // --------------------------------
    //  Save Settings
    // --------------------------------

    function save_settings_form()
    {

        $allowed_prefs = array('mage','run_code','run_type','exclude','blocks','custom');

        foreach($allowed_prefs as $key => $pref)
        {
          
            if( ! isset($_POST[$pref]) ) continue;
            
            $this->settings[$this->site_id][$pref] = $_POST[$pref];
          
        }

        $data = array(
          'settings' => serialize($this->settings)
        );

        // Update the settings
        ee()->db->where('class', get_class($this))->update('extensions', $data);

        ee()->javascript->output('$.ee_notice("Settings saved!", {type : "success"})');

    }
    // END save_settings_form

    // --------------------------------
    //  Fetch Preferences
    // --------------------------------

    function _fetch_preferences()
    {

        $prefs = array(
            'mage' => "",
            'run_code' => "",
            'run_type' => "",
            'exclude' => array(),
            'blocks' => array(
                'head' => 1,      
                'after_body_start' => 1,      
                'global_notices' => 1,      
                'header' => 1,      
                'global_messages' => 1,      
                'left' => 1,      
                'right' => 1,      
                'footer' => 1,      
                'before_body_end' => 1
            ),      
            'custom' => ''
        );
                
        if(isset($this->settings[$this->site_id]))
        {
            return array_merge($prefs,$this->settings[$this->site_id]);
        }

        return $prefs;

    }
    // END _fetch_preferences

}
// END CLASS