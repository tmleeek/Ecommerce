<?php
/**
 * the settings for the Gmaps module
 * 
 * @package             Gmaps for EE2
 * @author              Rein de Vries (info@reinos.nl)
 * @copyright           Copyright (c) 2012 Rein de Vries
 * @license  			http://reinos.nl/add-ons/commercial-license
 * @link                http://reinos.nl/add-ons/gmaps
 */

//updates
$this->updates = array(
	'2.4',
	'2.6',
	'2.9.2',
	'2.9.4',
	'2.11.1',
	'2.13',
	'3.0',
	'3.0.2',
	'3.2.1',
	'3.2.2',
	'4.2.6',
	'4.3.0',
);

//Default Post
$this->default_post = array(
	'license_key' => '',
	'report_date' => time(),
	'report_stats' => true,
	'files_info' => '',
	'data_transfer' => 'curl',
	'geocoding_providers' => 'a:1:{i:0;s:11:"google_maps";}',
	'dev_mode' => 0

);

$this->def_geocoding_providers = array(
	'google_maps' => array('Google Maps', 'The default provider'),
	'bing_maps' => array('Bing Maps', 'Bing maps can only be used with the param bing_maps_key="".'),
	'openstreetmap' => array('Openstreetmap'),
	'mapquest' => array('Mapquest', 'Mapquest can only be used with the param mapquest_maps_key="".'),
	'yandex' => array('Yandex'),
	'tomtom' => array('TomTom', 'TomTom can only be used with the param tomtom_maps_key="".'),
	'nominatim' => array('Nominatim')
);

//overrides
$this->overide_settings = array(
	'gmaps_icon_dir' => '[theme_dir]images/icons/',
	'gmaps_icon_url' => '[theme_url]images/icons/',
);

//cache date in hour
$this->cache_time = 168; // one week (7 days)

// Backwards-compatibility with pre-2.6 Localize class
$this->format_date_fn = (version_compare(APP_VER, '2.6', '>=')) ? 'format_date' : 'decode_date';

//mcp veld header
$this->table_headers = array(
	GMAPS_MAP.'_address' => array('data' => lang(GMAPS_MAP.'_address'), 'style' => 'width:10%;'),
	GMAPS_MAP.'_lat' => array('data' => lang(GMAPS_MAP.'_lat'), 'style' => 'width:40%;'),
	GMAPS_MAP.'_lng' => array('data' => lang(GMAPS_MAP.'_lng'), 'style' => 'width:40%;'),
	GMAPS_MAP.'_date' => array('data' => lang(GMAPS_MAP.'_date'), 'style' => 'width:40%;'),
	//'actions' => array('data' => '', 'style' => 'width:10%;')
);

/* End of file settings.gmaps.php */
/* Location: /system/expressionengine/third_party/gmaps/settings.gmaps.php */