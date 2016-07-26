<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mage_integratee_ft extends EE_Fieldtype {

    var $info = array(
        'name'      => 'Magento Categories',
        'version'   => '1.1.0'
    );

    // --------------------------------------------------------------------

    function display_field($data)
    {
        $field_options = $this->_get_field_options($data);

        $field = form_dropdown($this->field_name, $field_options, $data);

        return $field;
    }

    // --------------------------------------------------------------------

    function grid_display_field($data)
    {
        return $this->display_field(form_prep($data));
    }

    // --------------------------------------------------------------------

    function install()
    {

    }

    // --------------------------------------------------------------------

    function _get_field_options($data)
    {

        $site_id = ee()->config->item('site_id');

        $prefs_query = ee()->db->select('settings')->where('class', str_replace("ft", "ext", __CLASS__))->get('extensions');
        foreach($prefs_query->result_array() AS $r) {
            $tmp = unserialize($r['settings']);
            $prefs = $tmp[$site_id];
        }

        //Get Magento Run Codes
        $mageRunCode = $prefs['run_code'];
        $mageRunType = $prefs['run_type'];

        //Set Magento Run Codes
        if(isset( ee()->config->_global_vars['MAGE_INTEGRATEE_RUN_CODE']))
        {
            $mageRunCode = ee()->config->_global_vars['MAGE_INTEGRATEE_RUN_CODE'];
        }

        if(isset( ee()->config->_global_vars['MAGE_INTEGRATEE_RUN_TYPE']))
        {
            $mageRunType = ee()->config->_global_vars['MAGE_INTEGRATEE_RUN_TYPE'];
        }


        //Load and Cache Magento Layout
        if ( ! ee()->session->cache(__CLASS__, 'mage'))
        {

            //Verify Magento path
            if($prefs['mage'] === ""){
                return;
            } else {
              $mage = $prefs['mage'];
            }

            //Set path to /app directory
            if($mage[strlen($mage)-1] !== "/"){
                $mage .= "/app/Mage.php";
            } else {
                $mage .= "app/Mage.php";
            }

        }

        require_once $mage;
        Mage::app($mageRunCode,$mageRunType);

        $field_options = array();

        $field_options = $this->_get_categories();

        return $field_options;
    }

    // --------------------------------------------------------------------

    function replace_tag($data, $params = '', $tagdata = '')
    {

        return ee()->functions->encode_ee_tags($data);

    }

    // --------------------------------------------------------------------

    function replace_url($data, $params = '', $tagdata = '')
    {

        $cache = unserialize(ee()->cache->get('/mage_integratee/categories'));

        return $cache[$data]['url'];
        
    }

    // --------------------------------------------------------------------

    function replace_thumbnail($data, $params = '', $tagdata = '')
    {

        $cache = unserialize(ee()->cache->get('/mage_integratee/categories'));

        return $cache[$data]['thumbnail'];
        
    }

    // --------------------------------------------------------------------

    function replace_name($data, $params = '', $tagdata = '')
    {

        $cache = unserialize(ee()->cache->get('/mage_integratee/categories'));

        return $cache[$data]['name'];
        
    }

    // --------------------------------------------------------------------

    function _get_categories($catid=0)
    {

        if(!$catid):
            $catid = Mage::app()->getStore()->getRootCategoryId();
        endif;

        //Load Cache
        $cache = unserialize(ee()->cache->get('/mage_integratee/categories'));

        if(isset($cache) && $cache != ""):

            //We have cache, load data
            $tree = array();
            $tree[""] = "Select a Category";

            foreach ($cache as $cat):
                $level = $cat['level'] - 1;
                $tree[$cat['id']] = str_repeat("--", $level) . " " . $cat['name'];
            endforeach;

            return $tree;

        else:

            //We don't have cache, grab data from Magento and build cache
            $temp = $this->_get_category_array($catid);

            ee()->cache->save('/mage_integratee/categories', serialize($temp), 60 * 60 * 24 * 365 * 10);

            $tree = array();
            $tree[""] = "Select a Category";

            foreach ($temp as $cat):
                $level = $cat['level'] - 1;
                $tree[$cat['id']] = str_repeat("--", $level) . " " . $cat['name'];
            endforeach;

            return $tree;

        endif;

    }

    // --------------------------------------------------------------------

    function _get_category_array($catid=0)
    {

        $tree = array();

        if(!$catid):
            $catid = Mage::app()->getStore()->getRootCategoryId();
        endif;

        $cats = Mage::getModel('catalog/category')->load($catid)->getChildrenCategories();

        foreach ($cats as $cat):

            $cat = Mage::getModel('catalog/category')->load($cat->getId());

            $tree[$cat->getId()]['id'] = $cat->getId();
            $tree[$cat->getId()]['name'] = $cat->getName();
            $tree[$cat->getId()]['url'] = $cat->getUrl();
            $tree[$cat->getId()]['path'] = $cat->getPath();
            $tree[$cat->getId()]['hide'] = $cat->getHideMainNav();
            if($cat->getThumbnail() != ""):
                $tree[$cat->getId()]['thumbnail'] = Mage::getBaseUrl('media') . 'catalog' . DS . 'category' . DS . $cat->getThumbnail();
            else: 
                $tree[$cat->getId()]['thumbnail'] = Mage::getBaseUrl('media') . 'catalog' . DS . 'category' . DS . 'no-image.jpg';
            endif;
            $tree[$cat->getId()]['level'] = $cat->getLevel() - 1;

            if($cat->hasChildren()):

                $tree = $tree + $this->_get_category_array($cat->getId());

            endif;

        endforeach;

        return $tree;

    }

}
// END Mage_integratee_ft class

/* End of file ft.mage_integratee.php */
/* Location: ./system/expressionengine/third_party/mage_integratee/ft.mage_integratee.php */