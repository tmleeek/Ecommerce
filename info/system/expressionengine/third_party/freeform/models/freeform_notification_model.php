<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Freeform - Notification Model
 *
 * @package		Solspace:Freeform
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2015, Solspace, Inc.
 * @link		https://solspace.com/docs/freeform
 * @license		https://solspace.com/software/license-agreement
 * @filesource	freeform/models/freeform_notification_model.php
 */

if ( ! class_exists('Freeform_Model'))
{
	require_once 'freeform_model.php';
}

class Freeform_notification_model extends Freeform_Model 
{
	//nonstandard name
	public $_table = 'freeform_notification_templates';
}
//END Freeform_preference_model