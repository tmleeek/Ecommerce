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
 * Api Xml Request Class
 */
class AW_Vidtest_Model_Api_Xml_Request extends Zend_Http_Client {

    /**
     * Retrives requested object
     * @param string $method Request method (GET, POST etc...)
     * @return Object
     */
    public function request($method = parent::POST) {
        $response = parent::request($method);
        if ($response->isSuccessful()) {
            $res = new AW_Vidtest_Model_Api_Xml_Response($response->getBody());
            return $res->getData();
        } else {
            throw new Exception('Error of Api Xml Response');
        }
    }

}