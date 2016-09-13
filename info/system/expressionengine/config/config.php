<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| ExpressionEngine Config Items
|--------------------------------------------------------------------------
|
| The following items are for use with ExpressionEngine.  The rest of
| the config items are for use with CodeIgniter, some of which are not
| observed by ExpressionEngine, e.g. 'permitted_uri_chars'
|
*/

$config['app_version'] = '2.11.1';
$config['license_contact'] = 'jarod@unleadedgroup.com';
$config['license_number'] = '5686-7722-4860-9889';
$config['debug'] = '1';
$config['cp_url'] = 'http://lunddev.build.moe/info/admin.php';
$config['doc_url'] = 'http://ellislab.com/expressionengine/user-guide/';
$config['is_system_on'] = 'y';
$config['allow_extensions'] = 'y';
$config['cache_driver'] = 'file';
$config['cookie_prefix'] = '';
$config['cookie_httponly'] = 'y';

$protocol                         = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https://" : "http://";
$base_url                         = $protocol . $_SERVER['SERVER_NAME'] . "/info";
$base_path                        = $_SERVER['DOCUMENT_ROOT'] . "/info";
/*
Replace current $base_url & $base_path lines with these lines if EE is installed in a subfolder. Change /info to correct subfolder name, if different.

$base_url                         = $protocol . $_SERVER['SERVER_NAME'] . "/info";
$base_path                        = $_SERVER['DOCUMENT_ROOT'] . "/info";
*/
$system_folder                    = "system";
$images_folder                    = "images";
$images_path                      = $base_path . "/" . $images_folder;
$images_url                       = $base_url . "/" . $images_folder;
$user_agent                       = $_SERVER['HTTP_USER_AGENT'];

$config['app_version'] = '2.11.1';
$config['license_contact'] = 'jarod@unleadedgroup.com';
$config['license_number'] = '5686-7722-4860-9889';
$config['debug'] = '1';
$config['site_url'] = $base_url;
$config['cp_url'] = $base_url . "/admin.php";
$config['doc_url'] = 'http://ellislab.com/expressionengine/user-guide/';
$config['is_system_on'] = 'y';
$config['allow_extensions'] = 'y';
$config['cookie_prefix'] = '';
$config['cookie_httponly'] = 'y';

$config['save_tmpl_files']            = "y";
$config['site_404']                   = "site/404";
$config['strict_urls']                = "y";
$config['tmpl_file_basepath']         = $base_path . "/templates/";
$config['hidden_template_indicator']  = ".";

$config['theme_folder_url']    = $base_url . "/themes/";
$config['theme_folder_path']    = $base_path . "/themes/";
$config['emoticon_path']        = $images_url . "/smileys/";
$config['captcha_path']         = $images_path . "/captchas/";
$config['captcha_url']          = $images_url . "/captchas/";
$config['avatar_path']          = $images_path . "/avatars/";
$config['avatar_url']           = $images_url . "/avatars/";
$config['photo_path']           = $images_path . "/member_photos/";
$config['photo_url']            = $images_url . "/member_photos/";
$config['sig_img_path']         = $images_path . "/signature_attachments/";
$config['sig_img_url']          = $images_url . "/signature_attachments/";
$config['prv_msg_upload_path']  = $images_path . "/pm_attachments/";

$config['index_page'] = '';

$config['upload_preferences'] = array(
    1 => array(                                                            	// ID of upload destination
        'name'        => 'Content',                          				// Display name in control panel
        'server_path' => $base_path . "/uploads/", 				// Server path to upload directory
        'url'         => $base_url . "/uploads/"      				// URL of upload directory
    ),
     2 => array(                                                            	// ID of upload destination
        'name'        => 'Guides',                          				// Display name in control panel
        'server_path' => $base_path . "/guides/", 				// Server path to upload directory
        'url'         => $base_url . "/guides/"      				// URL of upload directory
    ),
    3 => array(                                                            	// ID of upload destination
        'name'        => 'Content',                          				// Display name in control panel
        'server_path' => $base_path . "/uploads/", 				// Server path to upload directory
        'url'         => $base_url . "/uploads/"      				// URL of upload directory
    ),
    4 => array(                                                            	// ID of upload destination
        'name'        => 'Guides',                          				// Display name in control panel
        'server_path' => $base_path . "/guides/", 				// Server path to upload directory
        'url'         => $base_url . "/guides/"      				// URL of upload directory
    ),
    5 => array(                                                            	// ID of upload destination
        'name'        => 'Content',                          				// Display name in control panel
        'server_path' => $base_path . "/uploads/", 				// Server path to upload directory
        'url'         => $base_url . "/uploads/"      				// URL of upload directory
    ),
    6 => array(                                                            	// ID of upload destination
        'name'        => 'Guides',                          				// Display name in control panel
        'server_path' => $base_path . "/guides/", 				// Server path to upload directory
        'url'         => $base_url . "/guides/"      				// URL of upload directory
    )
);


$config['tz_country'] = 'us';

$config['multiple_sites_enabled'] = 'y';

$config['gmap_api_version'] = '3.22';
$config['gmap_api_key'] = 'AIzaSyAi8_RYGduEnXRlUgyksd6HwhEDynSdARM';

// END EE config items



/*
|--------------------------------------------------------------------------
| URI PROTOCOL
|--------------------------------------------------------------------------
|
| This item determines which server global should be used to retrieve the
| URI string.  The default setting of "AUTO" works for most servers.
| If your links do not seem to work, try one of the other delicious flavors:
|
| 'AUTO'			Default - auto detects
| 'PATH_INFO'		Uses the PATH_INFO
| 'QUERY_STRING'	Uses the QUERY_STRING
| 'REQUEST_URI'		Uses the REQUEST_URI
| 'ORIG_PATH_INFO'	Uses the ORIG_PATH_INFO
|
*/
$config['uri_protocol']	= 'AUTO';

/*
|--------------------------------------------------------------------------
| Default Character Set
|--------------------------------------------------------------------------
|
| This determines which character set is used by default in various methods
| that require a character set to be provided.
|
*/
$config['charset'] = 'UTF-8';


/*
|--------------------------------------------------------------------------
| Class Extension Prefix
|--------------------------------------------------------------------------
|
| This item allows you to set the filename/classname prefix when extending
| native libraries.  For more information please see the user guide:
|
| http://codeigniter.com/user_guide/general/core_classes.html
| http://codeigniter.com/user_guide/general/creating_libraries.html
|
*/
$config['subclass_prefix'] = 'EE_';

/*
|--------------------------------------------------------------------------
| Error Logging Threshold
|--------------------------------------------------------------------------
|
| If you have enabled error logging, you can set an error threshold to
| determine what gets logged. Threshold options are:
|
|	0 = Disables logging, Error logging TURNED OFF
|	1 = Error Messages (including PHP errors)
|	2 = Debug Messages
|	3 = Informational Messages
|	4 = All Messages
|
| For a live site you'll usually only enable Errors (1) to be logged otherwise
| your log files will fill up very fast.
|
*/
$config['log_threshold'] = 0;

/*
|--------------------------------------------------------------------------
| Error Logging Directory Path
|--------------------------------------------------------------------------
|
| Leave this BLANK unless you would like to set something other than the
| default system/expressionengine/logs/ directory. Use a full server path
| with trailing slash.
|
| Note: You may need to create this directory if your server does not
| create it automatically.
|
*/
$config['log_path'] = '';

/*
|--------------------------------------------------------------------------
| Date Format for Logs
|--------------------------------------------------------------------------
|
| Each item that is logged has an associated date. You can use PHP date
| codes to set your own date formatting
|
*/
$config['log_date_format'] = 'Y-m-d H:i:s';

/*
|--------------------------------------------------------------------------
| Cache Directory Path
|--------------------------------------------------------------------------
|
| Leave this BLANK unless you would like to set something other than the
| default system/expressionengine/cache/ directory. Use a full server path
| with trailing slash.
|
*/
$config['cache_path'] = '';

/*
|--------------------------------------------------------------------------
| Encryption Key
|--------------------------------------------------------------------------
|
| If you use the Encryption class or the Sessions class with encryption
| enabled you MUST set an encryption key.  See the user guide for info.
|
*/
$config['encryption_key'] = '';


/*
|--------------------------------------------------------------------------
| Rewrite PHP Short Tags
|--------------------------------------------------------------------------
|
| If your PHP installation does not have short tag support enabled CI
| can rewrite the tags on-the-fly, enabling you to utilize that syntax
| in your view files.  Options are TRUE or FALSE (boolean)
|
*/
$config['rewrite_short_tags'] = TRUE;


/* End of file config.php */
/* Location: ./system/expressionengine/config/config.php */