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
 * Video Service API Interface
 * [Extension] <---> [Connector model] <---> [<Service name> API Model (implements this)]
 */
interface AW_Vidtest_Model_Api_Interface {
    /*     * ******************** */
    /* Authorization block */
    /*     * ******************** */

    public function logIn($loginData);

    public function logOut($token = null);

    public function isLoggedIn($token = null);

    public function refreshSecret($token = null);

    /*     * ************************* */
    /* Get videos & information */
    /*     * ************************* */

    public function getPublicVideoData($apiVideoId);

    public function getVideoData($apiVideoId);

    public function getThumbnailUrl($apiVideoId);

    public function getVideoUrl($apiVideoId);

    public function getViews($apiVideoId);

    public function getTime($apiVideoId);

    /*     * ***** */
    /* Show */
    /*     * ***** */

    public function getRenderedPlayer($url, $options = null);

    public function getRenderedForm($options = null);

    public function getRenderedCopyright($options = null);

    /*     * ********************** */
    /* Upload & update video */
    /*     * ********************** */

    public function uploadVideo($file, $videoData);

    public function updateVideo($apiVideoId, $videoData);

    public function resumeUpload($apiVideoId, $resumeData);

    /*     * ************* */
    /* Delete video */
    /*     * ************* */

    public function deleteVideo($apiVideoId);

    /*     * **************** */
    /* Data validation */
    /*     * **************** */

    public function validateLogInData($loginData);
}