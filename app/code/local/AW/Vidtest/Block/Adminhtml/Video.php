<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento enterprise edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Vidtest
 * @version    1.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


/**
 * Manage Videos
 */
class AW_Vidtest_Block_Adminhtml_Video extends Mage_Adminhtml_Block_Widget_Grid_Container {

    /**
     * Class constructor
     * @param boolean $pending Is Pending grid flag
     */
    public function __construct($pending = null) {
        $this->_controller = 'adminhtml_video';
        $this->_blockGroup = 'vidtest';
        $this->_addButtonLabel = Mage::helper('vidtest')->__('Add video');

        if ($pending) {
            $this->_headerText = Mage::helper('vidtest')->__('Pending video');
            $this->setPending($pending);
        } else {
            $this->_headerText = Mage::helper('vidtest')->__('All video');
        }
        parent::__construct();
        if ($pending) {
            $this->_removeButton('add');
        }
    }

}
