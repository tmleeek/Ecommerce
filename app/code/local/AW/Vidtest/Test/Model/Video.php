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


class AW_Vidtest_Test_Model_Video extends EcomDev_PHPUnit_Test_Case {

    public function setup() {
        AW_Vidtest_Test_Model_Mocks_Foreignresetter::dropForeignKeys();
        parent::setup();
    }

    /**
     *  @test
     *  @dataProvider provider__setStore
     * 
     * 
     * 
     */
    public function setStore($data) {

        if (is_string($data['store'])) {
            $data['store'] = new $data['store'];
            $data['store']->setId($data['storeId']);
        }

        $video = Mage::getModel('vidtest/video')->setStore($data['store']);


        foreach ($video->getStores() as $k => $store) {
            $this->assertEquals($data['expected'][$k], $store);
        }
    }

    public function provider__setStore() {

        return array(
            array(array('store' => 'Mage_Core_Model_Store', 'expected' => array(1), 'storeId' => 1)),
            array(array('store' => array(1, 2, 3, 4, 5), 'expected' => array(1, 2, 3, 4, 5)))
        );
    }

}