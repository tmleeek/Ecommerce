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
 * @package    AW_Pquestion2
 * @version    2.1.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Pquestion2_Model_Resource_Notification_Queue extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('aw_pq2/notification_queue', 'entity_id');
    }

    public function getStoredEmail($email, $queueId)
    {
        $queueCollection = Mage::getModel('aw_pq2/notification_queue')->getCollection();
        $queueCollection
            ->addFieldToFilter('recipient_email', $email)
            ->addFieldToFilter('entity_id', $queueId)
        ;
        return $queueCollection->getFirstItem();
    }

    public function removeOldStoredEmails()
    {
        $storeCollection = Mage::getModel('core/store')->getCollection();
        $writeAdapter = $this->_getWriteAdapter();
        foreach ($storeCollection as $storeModel) {
            $storedEmailsLifeTime = Mage::getModel('aw_pq2/source_notification_type')
                ->getStoredEmailsLifetime($storeModel)
            ;

            if (null !== $storedEmailsLifeTime) {
                $finalDay = new Zend_Date();
                $finalDay->subDay($storedEmailsLifeTime);
                $writeAdapter->query(
                    "DELETE FROM `{$this->getTable('aw_pq2/notification_queue')}`"
                    . " WHERE sent_at  <= '" . $finalDay->toString(Varien_Date::DATETIME_INTERNAL_FORMAT) . "'"
                );
            }
        }
        return $this;
    }
}