<?php
/**
 * the config for the Gmaps module
 *
 * @package             Gmaps for EE2
 * @author              Rein de Vries (info@reinos.nl)
 * @copyright           Copyright (c) 2013 Rein de Vries
 * @license  			http://reinos.nl/add-ons/commercial-license
 * @link                http://reinos.nl/add-ons/gmaps
 */

if ( ! defined('GMAPS_NAME'))
{
	define('GMAPS_NAME', 'Gmaps');
	define('GMAPS_CLASS', 'Gmaps');
	define('GMAPS_MAP', 'gmaps');
	define('GMAPS_VERSION', '4.3.1');
	define('GMAPS_AUTHOR', 'Reinos.nl');
	define('GMAPS_DESCRIPTION', 'Simplified Google Maps for ExpressionEngine');
	define('GMAPS_DOCS', 'http://reinos.nl/add-ons/gmaps/');
	define('GMAPS_DEVOTEE', 'http://devot-ee.com/add-ons/gmaps');
	define('GMAPS_STATS_URL', 'http://reinos.nl/index.php/module_stats_api/v1'); 
}

$config['name'] = GMAPS_NAME;
$config['version'] = GMAPS_VERSION;

//load compat file
require_once(PATH_THIRD.GMAPS_MAP.'/compat.php');

/* End of file config.gmaps.php */
/* Location: /system/expressionengine/third_party/gmaps/config.gmaps.php */