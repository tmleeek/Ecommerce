<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Freeform - File Upload Model
 *
 * @package		Solspace:Freeform
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2015, Solspace, Inc.
 * @link		https://solspace.com/docs/freeform
 * @license		https://solspace.com/software/license-agreement
 * @filesource	freeform/models/freeform_file_upload_model.php
 */

if ( ! class_exists('Freeform_Model'))
{
	require_once 'freeform_model.php';
}

class Freeform_file_upload_model extends Freeform_Model 
{
	//nonstandard id
	public $id = 'file_id';
}
//END Freeform_preference_model