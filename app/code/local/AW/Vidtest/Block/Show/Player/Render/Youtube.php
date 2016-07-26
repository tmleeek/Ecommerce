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
 * Youtube player renderer
 */
class AW_Vidtest_Block_Show_Player_Render_Youtube extends AW_Vidtest_Block_Show_Player_Render_Abstract {
    /**
     * Path to player render template
     */
    const YOUTUBE_PLAYER_TEMPLATE = "aw_vidtest/show/player/render/youtube.phtml";

    /**
     * Class constructor
     */
    public function __construct() {
        parent::__construct();
        $this->setTemplate(self::YOUTUBE_PLAYER_TEMPLATE);
    }

}
