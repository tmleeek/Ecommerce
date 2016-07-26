<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Gmaps Module File
 *
 * @package             Gmaps for EE2
 * @author              Rein de Vries (info@reinos.nl)
 * @copyright           Copyright (c) 2013 Rein de Vries
 * @license  			http://reinos.nl/add-ons/commercial-license
 * @link                http://reinos.nl/add-ons/gmaps
 */

require_once(PATH_THIRD.'gmaps/config.php');

class Gmaps {
		 
	private $site_id;
	private $libraries = array();
	private $default_js_array;
	private $default_zoom = 15;
	
	public $return_data = '';

	/**
	 * Constructor
	 * 
	 * @return unknown_type
	 */
	function __construct()
	{		
		//Load the gmaps lib	
		ee()->load->library('gmaps_library', null, 'gmaps');
		ee()->load->library('gmaps_twitter_search', null, 'twitter');
		ee()->load->library('gmaps_api');
		
		//require the settings and the actions
		require PATH_THIRD.'gmaps/settings.php';
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:init}
	 * 
	 * Init function to place all files
	 * 
	 * 
	 * @return unknown_type
	 */
	function init()
	{
		//license check
		if(!ee()->gmaps->license_check())
		{
			gmaps_helper::log('Your Gmaps license key appear to be invalid. Please fill in the license in the Gmaps CP.', 1);
		}

		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, true);

		//set session cache
        ee()->gmaps->set_cache(GMAPS_MAP.'_init', true);
        ee()->gmaps->set_cache(GMAPS_MAP.'_caller', 0);

		//set lang
		$this->lang = ee()->gmaps->get_from_tagdata('lang', ''); // lang list https://spreadsheets.google.com/pub?key=p9pdwsai2hDMsLkXsoM05KQ&gid=1

		//catch output JS
		$catch_output_js = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('catch_output_js', 'no'));
		gmaps_helper::set_ee_cache('catch_output_js', $catch_output_js);

		//Load jQuery if its not here
		$load_jquery = gmaps_helper::check_yes(ee()->TMPL->fetch_param('load_jquery', 'yes'));
		if($load_jquery)
		{
			$this->return_data .= '
				<script type="text/javascript">
					if(!window.jQuery || window.jQuery === undefined){
					   document.write(unescape("%3Cscript src=\'' . ee()->gmaps_settings->get_setting('theme_url') . 'js/jquery.js\' type=\'text/javascript\'%3E%3C/script%3E"));
					}
				</script>
			';
		}

		//add libraries
		/*$this->_add_library('weather', $this->show_weather);
		$this->_add_library('panoramio', $this->show_panoramio);
		if(ee()->TMPL->tag_data[0]['method'] == 'places')
		{
			$this->_add_library('places', 'true');
		}*/

		/*
		The libraries are always loaded, not good. 
		@todo load based on the need
		 */
		$this->_add_library('weather', 'true');
		$this->_add_library('panoramio', 'true');
		$this->_add_library('places', 'true');
		$this->_add_library('geometry', 'true');
		$this->_add_library('drawing', 'true');
		
		//create library string
		if(!empty($this->libraries))
		{
			$this->libraries = '&libraries='.implode(',', $this->libraries);
		} 
		else
		{
			$this->libraries = '';
		}

		//get the key
		$key = ee()->gmaps->get_from_tagdata('key', '');
		
		//google api url
		$google_api_url = 'https://maps.googleapis.com/maps/api/js?key='.$key.'&v=3'.$this->libraries.'&language='.$this->lang;

		if (gmaps_helper::is_ssl() == TRUE)
		{
			$google_api_url = str_replace('http://', 'https://', $google_api_url);
		}

		//Google api
		$this->return_data .= '<script type="text/javascript" src="'.$google_api_url.'"></script>';

		$this->return_data .= '
			<script type="text/javascript">
				var EE_GMAPS = {
					version : "'.GMAPS_VERSION.'",
					base_path : "'.ee()->gmaps_settings->item('site_url').'",
					act_path : "'.ee()->gmaps_settings->item('site_url').'?ACT='.ee()->gmaps->fetch_action_id('Gmaps', 'gmaps_act').'",
					api_path : "'.ee()->gmaps_settings->item('site_url').'?ACT='.ee()->gmaps->fetch_action_id('Gmaps', 'gmaps_act').'&method=api",
					theme_path: "'.ee()->gmaps_settings->item('theme_url').'",
				}
			</script>
		';

		//minify css on DEV only
		if ( ee()->gmaps_settings->item('dev_mode'))
		{
			//add js
			$this->return_data .= '
				<script type="text/javascript" src="' . ee()->gmaps_settings->get_setting('theme_url') . 'js/gmaps.js" ></script>
			';
		}
		else
		{
			//add js
			$this->return_data .= '
				<script type="text/javascript" src="' . ee()->gmaps_settings->get_setting('theme_url') . 'js/gmaps.min.js" ></script>
			';
		}

		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, false);

		if($catch_output_js)
		{
			gmaps_helper::set_ee_cache('init_js', $this->return_data);
		}
		else
		{
			return $this->_minify_html_output($this->return_data, true);
		}


	}

	// ----------------------------------------------------------------------------------
	
	/**
	 * this init function is called by almost every method
	 * this method add all the default handlers and logic
	 * 
	 * @return unknown_type
	 */
	private function _init($method = '', $get_other_pars = true)
	{
		//errors in the {exp:gmaps:init}
		if(gmaps_helper::log_has_error())
		{
			//{errors}{error}{/errors}
			return ee()->gmaps->parse_errors();
		}
		else
		{
			if(preg_match_all("/".LD."errors".RD."(.*?)".LD."\/errors".RD."/s", ee()->TMPL->tagdata, $all_matches))
			{
				ee()->TMPL->tagdata = str_replace($all_matches[0][0], '', ee()->TMPL->tagdata);
			}
		}

		//report stats
		gmaps_helper::stats();

		//EDT Benchmark
		ee()->gmaps->benchmark($method, true);

		//set the caller times
		$this->_add_call_timer();

		//set the selector
		$this->div_id = ee()->gmaps->get_from_tagdata('div_id', 'ee_gmap').'_'.$this->caller_id;
		$this->div_class = ee()->gmaps->get_from_tagdata('div_class', '').' ee_gmap ee_gmaps ee_gmap_'.$this->caller_id.' ee_gmaps_'.$this->caller_id;
		$this->selector = '#'.$this->div_id;

		//catch output JS
		$this->catch_output_js = gmaps_helper::get_ee_cache('catch_output_js');

		//cache param
		$this->cache_time = ee()->gmaps->cache_time = (ee()->gmaps->get_from_tagdata('cache_time', $this->cache_time)) * 60 * 60;
		//$this->cache_time = $this->cache_time * 60 *60;

		//width and heigh
		$this->width = ee()->gmaps->get_from_tagdata('width', 700);
		$this->height = ee()->gmaps->get_from_tagdata('height', 400);

		//get the overlay
		$this->overlay_html = ee()->gmaps->get_from_tagdata('overlay:html', '');
		$this->overlay_position = ee()->gmaps->get_from_tagdata('overlay:position', 'left');

		//new/old style
		$this->enable_new_style = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('enable_new_style', 'yes'), true);

		//set the default location based on the current location
		$this->focus_current_location = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('focus_current_location', 'no'), true);

		//set the default vars
		$this->map_type = ee()->gmaps->get_from_tagdata('map_type', 'roadmap');
		$this->map_types = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('map_types', 'hybrid|roadmap|satellite|terrain'), true);
		
		if($get_other_pars)
		{
			//marker
			$this->marker = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('marker', 'yes'), true);
			$this->marker_show_title = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('marker:show_title', 'yes'), true);
			$this->marker_title = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('marker:title', ''), true);
			$this->marker_label = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('marker:label', ''), true);
			$this->marker_animation =gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('marker:animation', 'no'), true);
			//marker icon
			//$this->marker_icon_array = ee()->gmaps->get_from_tagdata('marker:icon', ''); //@deprecated v7.2.3
			$this->marker_icon_url_array = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('marker:icon:url', ''), false, false, false);
			$this->marker_icon_size_array = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('marker:icon:size', ''), false, false, false);
			$this->marker_icon_origin_array = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('marker:icon:origin', ''), false, false, false);
			$this->marker_icon_anchor_array = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('marker:icon:anchor', ''), false, false, false);
			//$this->marker_icon = ee()->gmaps->get_from_tagdata('marker:icon', ''); //@deprecated v7.2.3
			$this->marker_icon_url = ee()->gmaps->get_from_tagdata('marker:icon:url', '');
			$this->marker_icon_size = ee()->gmaps->get_from_tagdata('marker:icon:size', '');
			$this->marker_icon_origin = ee()->gmaps->get_from_tagdata('marker:icon:origin', '');
			$this->marker_icon_anchor = ee()->gmaps->get_from_tagdata('marker:icon:anchor', '');
			//$this->marker_icon_default = ee()->gmaps->get_from_tagdata('marker:icon_default', ''); //@deprecated v7.2.3 //geocoding only
			$this->marker_icon_default_url = ee()->gmaps->get_from_tagdata('marker:icon_default:url', '');
			$this->marker_icon_default_size = ee()->gmaps->get_from_tagdata('marker:icon_default:size', '');
			$this->marker_icon_default_origin = ee()->gmaps->get_from_tagdata('marker:icon_default:origin', '');
			$this->marker_icon_default_anchor = ee()->gmaps->get_from_tagdata('marker:icon_default:anchor', '');
			//shadow - fully @deprecated v2.3 (https://developers.google.com/maps/documentation/javascript/overlays#ComplexIcons)
			//$this->marker_shadow_array = ee()->gmaps->get_from_tagdata('marker:shadow', ''); //@deprecated v7.2.3
			$this->marker_shadow_url_array = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('marker:shadow:url', ''), false, false, false);
			$this->marker_shadow_size_array = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('marker:shadow:size', ''), false, false, false);
			$this->marker_shadow_origin_array = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('marker:shadow:origin', ''), false, false, false);
			$this->marker_shadow_anchor_array = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('marker:shadow:anchor', ''), false, false, false);
			//$this->marker_shadow = ee()->gmaps->get_from_tagdata('marker:shadow', ''); //@deprecated v7.2.3
			$this->marker_shadow_url = ee()->gmaps->get_from_tagdata('marker:shadow:url', '');
			$this->marker_shadow_size = ee()->gmaps->get_from_tagdata('marker:shadow:size', '');
			$this->marker_shadow_origin = ee()->gmaps->get_from_tagdata('marker:shadow:origin', '');
			$this->marker_shadow_anchor = ee()->gmaps->get_from_tagdata('marker:shadow:anchor', '');
			//$this->marker_shadow_default = ee()->gmaps->get_from_tagdata('marker:shadow_default', ''); //@deprecated v7.2.3 //geocoding only
			$this->marker_shadow_default_url = ee()->gmaps->get_from_tagdata('marker:shadow_default:url', '');
			$this->marker_shadow_default_size = ee()->gmaps->get_from_tagdata('marker:shadow_default:size', '');
			$this->marker_shadow_default_origin = ee()->gmaps->get_from_tagdata('marker:shadow_default:origin', '');
			$this->marker_shadow_default_anchor = ee()->gmaps->get_from_tagdata('marker:shadow_default:anchor', '');
			//shape
			$this->marker_shape_coord_array = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('marker:shape:coord', ''), false, false, false);
			$this->marker_shape_type_array = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('marker:shape:type', ''), false, false, false);
			$this->marker_shape_coord = ee()->gmaps->get_from_tagdata('marker:shape:coord', '');
			$this->marker_shape_type = ee()->gmaps->get_from_tagdata('marker:shape:type', '');
			$this->marker_shape_default_coord = ee()->gmaps->get_from_tagdata('marker:shape_default:coord', '');
			$this->marker_shape_default_type = ee()->gmaps->get_from_tagdata('marker:shape_default:type', '');
		}

		//others
		//(https://developers.google.com/maps/documentation/javascript/controls?hl=nl#ControlPositioning)
		$this->scroll_wheel = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('scroll_wheel', 'yes'), true);
		$this->zoom_control = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('zoom_control', 'yes'), true);
		$this->zoom_control_style = ee()->gmaps->get_from_tagdata('zoom_control_style', ''); //large, small
		$this->zoom_control_position = ee()->gmaps->get_from_tagdata('zoom_control_position', '');
		$this->pan_control = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('pan_control', 'yes'), true);
		$this->pan_control_position = ee()->gmaps->get_from_tagdata('pan_control_position', '');
		$this->map_type_control = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('map_type_control', 'yes'), true);
		$this->map_type_control_style = ee()->gmaps->get_from_tagdata('map_type_control_style', '');
		$this->map_type_control_position = ee()->gmaps->get_from_tagdata('map_type_control_position', '');
		$this->scale_control = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('scale_control', 'yes'), true);
		$this->street_view_control = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('street_view_control', 'yes'), true);
		$this->street_view_control_position = ee()->gmaps->get_from_tagdata('street_view_control_position', '');
		$this->hidden_div = ee()->gmaps->get_from_tagdata('hidden_div', '');

		//get the styled map settings and set it correct
		$this->styled_map = $this->_set_styled_map();

		//layers
		$this->show_traffic = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('show_traffic', 'no'), true) ;
		$this->show_transit = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('show_transit', 'no'), true) ;
		$this->show_bicycling = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('show_bicycling', 'no'), true) ;
		$this->show_panoramio = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('show_panoramio', 'no'), true) ;
		$this->panoramio_tag = ee()->gmaps->get_from_tagdata('panoramio_tag', '');
		
		//set the libraries vars
		$this->show_weather = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('show_weather', 'no'), true);

		//The Provider keys
		$this->bing_maps_key = ee()->gmaps->bing_maps_key = ee()->gmaps->get_from_tagdata('bing_maps_key', '');
		$this->google_maps_key = ee()->gmaps->google_maps_key = ee()->gmaps->get_from_tagdata('google_maps_key', '');
		$this->map_quest_key = ee()->gmaps->map_quest_key = ee()->gmaps->get_from_tagdata('map_quest_key', '');
		$this->tomtom_key = ee()->gmaps->tomtom_key = ee()->gmaps->get_from_tagdata('tomtom_key', '');

		//format for the adress object
		$this->address_format = ee()->gmaps->address_format = ee()->gmaps->get_from_tagdata('address_format', '[streetName] [streetNumber], [city], [country]');

		//are we using channel data? On yes we have to parse it early
		//$this->channel_data = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('channel_data', 'yes'), true);

		//build the default array for the js functions
		$this->default_js_array = '
			selector : "'.$this->selector.'",
			map_type : "'.$this->map_type.'",
			map_types : '.$this->map_types.',
			width : "'.$this->width.'",
			height : "'.$this->height.'",
			scroll_wheel : '.$this->scroll_wheel.',
			zoom_control : '.$this->zoom_control.',
			zoom_control_style : "'.strtoupper($this->zoom_control_style).'",
			zoom_control_position : "'.strtoupper($this->zoom_control_position).'",
			pan_control : '.$this->pan_control.',
			pan_control_position : "'.strtoupper($this->pan_control_position).'",
			map_type_control : '.$this->map_type_control.',
			map_type_control_style : "'.strtoupper($this->map_type_control_style).'",
			map_type_control_position : "'.strtoupper($this->map_type_control_position).'",
			scale_control : '.$this->scale_control.',
			street_view_control : '.$this->street_view_control.',
			street_view_control_position : "'.strtoupper($this->street_view_control_position).'",
			styled_map : '.$this->styled_map.',
			show_traffic : '.$this->show_traffic.',
			show_transit : '.$this->show_transit.',
			show_bicycling : '.$this->show_bicycling.',
			show_weather : '.$this->show_weather.',
			show_panoramio : '.$this->show_panoramio.',
			panoramio_tag : "'.$this->panoramio_tag.'",
			hidden_div : "'.$this->hidden_div.'",
			enable_new_style : '.$this->enable_new_style.',
			overlay_html : "'.$this->overlay_html.'",
			overlay_position : "'.$this->overlay_position.'",
			focus_current_location : '.$this->focus_current_location.'
		';

		//icon
		if($get_other_pars)
		{
				$this->default_js_icon_array = array(
				'icon' => '
					{
						url : "'.$this->marker_icon_url.'",
						size : "'.$this->marker_icon_size.'",
						origin : "'.$this->marker_icon_origin.'",
						anchor : "'.$this->marker_icon_anchor.'"
					}
				',
				'icon_default' => '
					{
						url : "'.$this->marker_icon_default_url.'",
						size : "'.$this->marker_icon_default_size.'",
						origin : "'.$this->marker_icon_default_origin.'",
						anchor : "'.$this->marker_icon_default_anchor.'"
					}
				',
				'icon_geocoding' => '
					{
						url : '.$this->marker_icon_url_array.',
						size : '.$this->marker_icon_size_array.',
						origin : '.$this->marker_icon_origin_array.',
						anchor : '.$this->marker_icon_anchor_array.'
					}
				',
				'shadow' => '
					{
						url : "'.$this->marker_shadow_url.'",
						size : "'.$this->marker_shadow_size.'",
						origin : "'.$this->marker_shadow_origin.'",
						anchor : "'.$this->marker_shadow_anchor.'"
					}
				',
				'shadow_default' => '
					{
						url : "'.$this->marker_shadow_default_url.'",
						size : "'.$this->marker_shadow_default_size.'",
						origin : "'.$this->marker_shadow_default_origin.'",
						anchor : "'.$this->marker_shadow_default_anchor.'"
					}
				',
				
				'shadow_geocoding' => '
					{
						url : '.$this->marker_shadow_url_array.',
						size : '.$this->marker_shadow_size_array.',
						origin : '.$this->marker_shadow_origin_array.',
						anchor : '.$this->marker_shadow_anchor_array.'
					}
				',
				'shape' => '
					{
						coord : "'.$this->marker_shape_coord.'",
						type : "'.$this->marker_shape_type.'"
					}
				',
				'shape_default' => '
					{
						coord : "'.$this->marker_shape_default_coord.'",
						type : "'.$this->marker_shape_default_type.'"
					}
				',
				'shape_geocoding' => '
					{
						coord : '.$this->marker_shape_coord_array.',
						type : '.$this->marker_shape_type_array.'
					}
				',
			); 
		}
		
		
		/* -------------------------------------------
		/* 'gmaps_init' hook.
		/*  - Added: 2.3
		*/
		if (ee()->extensions->active_hook('gmaps_init') === TRUE)
		{
			ee()->extensions->call('gmaps_init', '');
		}
		// -------------------------------------------
	}

	
	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:geocoding}
	 * 
	 * This is the geocoding method according to http://reinos.nl/add-ons/gmaps/docs#Geocoding
	 *
	 * @return unknown_type
	 */
	function geocoding()
	{
		//call the init function to init some default values
		$error = $this->_init(__FUNCTION__);
		if(gmaps_helper::log_has_error())
		{
			return $error;
		}

		//set the specific vars
		$address = ee()->gmaps->get_from_tagdata('address');
		$latlng = ee()->gmaps->get_from_tagdata('latlng');
		$address_center = ee()->gmaps->get_from_tagdata('address:center');
		$latlng_center = ee()->gmaps->get_from_tagdata('latlng:center');
		//$address = ee()->gmaps->get_from_tagdata('address', '');
		//$latlng = ee()->gmaps->get_from_tagdata('latlng', '');
		$zoom = ee()->gmaps->get_from_tagdata('zoom', $this->default_zoom);
		$zoom_override = $zoom == $this->default_zoom ? 'false' : 'true';
		$static = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('static', 'no'), true);
		$show_elevation = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('show_elevation', 'no'), true);
		//$show_streetview = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('show_streetview', 'no'), true);

		//twitter vars for the if statement, in the library we load the rest.
		$twitter_search_about = ee()->gmaps->get_from_tagdata('twitter_search:about', '');
		$twitter_search_hash = ee()->gmaps->get_from_tagdata('twitter_search:hash', '');
		$twitter_search_to = ee()->gmaps->get_from_tagdata('twitter_search:to', '');
		$twitter_search_from = ee()->gmaps->get_from_tagdata('twitter_search:from', '');
		$twitter_search_contains = ee()->gmaps->get_from_tagdata('twitter_search:contains', '');

		//circle specific
		$circle = ee()->gmaps->get_from_tagdata('circle') == 'all' ? '["all"]' : gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('circle', 'no'), false, true);
		$circle_fit_circle = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('circle:fit_circle', 'yes'), true);
		$circle_stroke_color = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('circle:stroke_color', '#BBD8E9'));
		$circle_stroke_opacity = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('circle:stroke_opacity', 1));
		$circle_stroke_weight = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('circle:stroke_weight', 3));
		$circle_fill_color = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('circle:fill_color', '#BBD8E9'));
		$circle_fill_opacity = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('circle:fill_opacity', 0.6));
		$circle_radius = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('circle:radius', 1000));	

		//get the vars from the {marker:collection}
		$marker_collection = $this->extract_marker_collection_data();
		if(is_array($marker_collection))
		{
			if(!empty($marker_collection['address']))
			{
				$address = $marker_collection['address'];
			}
			else if($marker_collection['latlng'])
			{
				$latlng = $marker_collection['latlng'];
			}
		}

		//if address empty return a error.
		// fill in by the user
		if($address == '' && $latlng == '') 
		{
			gmaps_helper::log('You forgot to fill in an address or latlng', 1);
			//ee()->gmaps->errors[] = 'You forgot to fill in an address or latlng';
			return ee()->gmaps->parse_errors();	
		}

		//get the adresses via the geocoder
		if($address != '')
		{
			//parse the keys for js use
			$parsed_data = ee()->gmaps->parse_param_keys(ee()->gmaps->explode($address));
			//build a js array
			//$input_address = build_js_array($address);
			$input_address = $address;
			//geocode the result
			$result = ee()->gmaps->geocode_address($parsed_data['data']);
			//set the vars
			$address = $result['address'];
			$latlng = $result['latlng'];
			$raw_latlng = $result['raw_latlng'];
		}
		else 
		{
			//parse the keys for js use
			$parsed_data = ee()->gmaps->parse_param_keys(ee()->gmaps->explode($latlng));
			//build a js array
			$latlng = (implode('|', $parsed_data['data']));
			//set the vars
			$address = $latlng;
			$input_address = $latlng;;
		}

		//get the center address
		if($address_center != '')
		{
			$center = ee()->gmaps->geocode_address(array($address_center));
			$center = $center['latlng'];
		}
		else
		{
			$center = $latlng_center;
		}

		//set the js keys for the markers
		$keys = (implode('|', $parsed_data['keys']));

		//if address empty return a error.
		// after geocoding
		if(($address == '[]' || $address == '') && ($latlng == '[]' || $latlng == ''))
		{
			gmaps_helper::log('No result founded', 1);
			//ee()->gmaps->errors[] = 'No result founded';
			return ee()->gmaps->parse_errors().ee()->TMPL->no_results();	
		}
		
		//twitter search function invoke
		$twitter_marker_html = '';
		if($twitter_search_about != '' || $twitter_search_hash != '' || $twitter_search_to != '' || $twitter_search_from != '' || $twitter_search_contains != '')
		{
			$twitter_result = ee()->gmaps->get_twitter_feed_for_map($raw_latlng);	
			if(empty($twitter_result) || $twitter_result == 'no_result')
			{
				gmaps_helper::log('No result founded', 1);
				//ee()->gmaps->errors[] = 'No twitter results founded';
				return ee()->gmaps->parse_errors().ee()->TMPL->no_results();	
			}
			else if($twitter_result['latlng'] != '[]')
			{
				$latlng = $twitter_result['latlng'];
				$address = $twitter_result['address'];
				$twitter_marker_html = $twitter_result['marker_html'];
			}
			else
			{
				gmaps_helper::log('No result founded', 1);
				//ee()->gmaps->errors[] = 'No twitter results founded';
				return ee()->gmaps->parse_errors().ee()->TMPL->no_results();	
			}
		}		
		
		//return a div
		$this->return_data .= '<div data-gmaps-number="'.$this->caller_id.'" class="'.$this->div_class.'" id="'.$this->div_id.'"></div>';

		//return the js 
		$this->return_data .= $this->_output_js('
			jQuery(window).ready(function(){
				EE_GMAPS.setGeocoding({
					input_address : "'.base64_encode($input_address).'",
					address : "'.base64_encode($address).'",
					latlng : "'.base64_encode($latlng).'",
					keys : "'.base64_encode($keys).'",
					zoom : '.$zoom.',
					zoom_override : '.$zoom_override.',
					center : "'.base64_encode($center).'",
					static : '.$static.',
					show_elevation : '.$show_elevation.',
					circle : {
						circle : '.$circle.',
						fit_circle : '.$circle_fit_circle.',
						stroke_color : '.$circle_stroke_color.',
						stroke_opacity : '.$circle_stroke_opacity.',
						stroke_weight : '.$circle_stroke_weight.',
						fill_color : '.$circle_fill_color.',
						fill_opacity : '.$circle_fill_opacity.',
						radius : '.$circle_radius.'
					},
					'.$this->_create_markers_array(__FUNCTION__, $twitter_marker_html).',
					'.$this->default_js_array.'
				});	
			});
		');

		/* -------------------------------------------
		/* 'gmaps_geocoding_end' hook.
		/*  - Added: 2.3
		*/
		if (ee()->extensions->active_hook('gmaps_geocoding_end') === TRUE)
		{
        	ee()->extensions->call('gmaps_geocoding_end', '');
		}
		// -------------------------------------------
	
		//parse {map_id}
		$this->return_data .= ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array(array('map_id'=>$this->caller_id)));

		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, false);

		//return the gmaps
		return $this->_minify_html_output($this->return_data);
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:geolocation}
	 * 
	 * This is the geolocation method according to http://reinos.nl/add-ons/gmaps/docs#Geolocation
	 * 
	 * @return unknown_type
	 */
	function geolocation()
	{
		//call the init function to init some default values
		$error = $this->_init(__FUNCTION__);
		if(gmaps_helper::log_has_error())
		{
			return $error;
		}
		
		//set the specific vars
		$zoom = ee()->gmaps->get_from_tagdata('zoom', $this->default_zoom);

		//return a div
		$this->return_data .= '<div data-gmaps-number="'.$this->caller_id.'" class="'.$this->div_class.'" id="'.$this->div_id.'"></div>';
		
		//return the js 
		$this->return_data .= $this->_output_js('
			jQuery(window).ready(function(){
				EE_GMAPS.setGeolocation({
					zoom : '.$zoom.',
					'.$this->_create_markers_array(__FUNCTION__).',
					'.$this->default_js_array.'					
				});
			});
		');

		/* -------------------------------------------
		/* 'gmaps_geolocation_end' hook.
		/*  - Added: 2.3
		*/
		if (ee()->extensions->active_hook('gmaps_geolocation_end') === TRUE)
		{
			ee()->extensions->call('gmaps_geolocation_end', '');
		}
		// -------------------------------------------
	
		//parse {map_id}
		$this->return_data .= ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array(array('map_id'=>$this->caller_id)));

		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, false);

		//return the gmaps
		return $this->_minify_html_output($this->return_data);
	}

	// ----------------------------------------------------------------------------------
	
	/**
	 * {exp:gmaps:route}
	 * 
	 * This is the route method according to http://reinos.nl/add-ons/gmaps/docs#Route
	 * 
	 * @return unknown_type
	 */
	function route()
	{
		//call the init function to init some default values
		$error = $this->_init(__FUNCTION__);
		if(gmaps_helper::log_has_error())
		{
			return $error;
		}

		//set the specific vars
		$from_address = ee()->gmaps->get_from_tagdata('from_address');
		$from_latlng = ee()->gmaps->get_from_tagdata('from_latlng');
		$to_address = ee()->gmaps->get_from_tagdata('to_address');
		$to_latlng = ee()->gmaps->get_from_tagdata('to_latlng');
		$stops_address = ee()->gmaps->get_from_tagdata('stops_address');
		$stops_latlng = ee()->gmaps->get_from_tagdata('stops_latlng');

		$travel_mode = ee()->gmaps->get_from_tagdata('travel_mode', 'driving');
		$stroke_color = ee()->gmaps->get_from_tagdata('stroke_color', '#131540');
		$stroke_opacity = ee()->gmaps->get_from_tagdata('stroke_opacity', '0.6');
		$stroke_weight = ee()->gmaps->get_from_tagdata('stroke_weight', '6');
		$show_details = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('show_details', 'no'), true);
		$show_details_per_step = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('show_details_per_step', 'no'), true);
		$details_per_step_template = ee()->gmaps->get_from_tagdata('details_per_step_template', "[instructions] ([distance], [duration])");
		$details_template = ee()->gmaps->get_from_tagdata('details_template', "<p>The route goes from <b>[start_address]</b> to <b>[end_address]</b> in a time of <b>[duration]</b> and a distance of <b>[distance]</b></p>");
		$details_template_position = ee()->gmaps->get_from_tagdata('details_template_position', 'top');
		$show_elevation = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('show_elevation', 'no'), true);

		//if address empty return a error.
		if($to_address == '' && $to_latlng == '') 
		{
			gmaps_helper::log('You forgot to fill in an to address/latlng', 1);
			//ee()->gmaps->errors[] = 'You forgot to fill in an to address/latlng';
			return ee()->gmaps->parse_errors();	
		}
		if($from_address == '' && $from_latlng == '') 
		{
			gmaps_helper::log('You forgot to fill in an to address/latlng', 1);
			//ee()->gmaps->errors[] = 'You forgot to fill in an from address/latlng';
			return ee()->gmaps->parse_errors();	
		}

		//return a div
		$this->return_data .= '<div data-gmaps-number="'.$this->caller_id.'" class="'.$this->div_class.'" id="'.$this->div_id.'"></div>';

		//add a div when we have to show the route in display
		if($show_details == 'true') 
		{
			if($details_template_position == 'top') 
			{
				$this->return_data .= '<div id="'.$this->div_id.'_details"></div>';
			}

			$this->return_data .= '<ol id="'.$this->div_id.'_details_per_step"></ol>';

			if($details_template_position == 'bottom') 
			{
				$this->return_data .= '<div id="'.$this->div_id.'_details"></div>';
			}
		}

		//load google api if we have to use the chart api from google
		if($show_elevation == 'true')
		{
			$this->return_data .= '
				<script type="text/javascript" src="https://www.google.com/jsapi"></script>
			';
			$this->return_data .= '<div id="'.$this->div_id.'_chart"></div>';
		}

		//prepare to address
		if($to_address != '')
		{
			$to_address = array_shift(ee()->gmaps->explode($to_address));
			$result = ee()->gmaps->geocode_address(array($to_address), 'string');
			$to_latlng = $result['latlng'];
			$to_address = $result['address'];
		}
		else 
		{
			$to_latlng = array_shift(ee()->gmaps->explode($to_latlng));
			$to_address = '';
		}

		//prepare from address
		if($from_address != '')
		{
			$from_address = array_shift(ee()->gmaps->explode($from_address));
			$result = ee()->gmaps->geocode_address(array($from_address), 'string');
			$from_latlng = $result['latlng'];
			$from_address = $result['address'];
		}
		else 
		{
			$from_latlng = array_shift(ee()->gmaps->explode($from_latlng));
			$from_address = '';
		}

		//prepare stop address
		if($stops_address != '')
		{
			$result = ee()->gmaps->geocode_address(ee()->gmaps->explode($stops_address));
			$stops_latlng = $result['latlng'];
			$stops_address = $result['address'];
		}
		else 
		{
			$stops_latlng = ($stops_latlng);
			$stops_address = '';
		}

		//if address empty return a error.
		// after geocoding
		if(($to_address == '[]' || $to_address == '') && ($to_latlng == '[]' || $to_latlng == '')) 
		{
			gmaps_helper::log('No result founded', 1);
			//ee()->gmaps->errors[] = 'No result founded';
			return ee()->gmaps->parse_errors().ee()->TMPL->no_results();	
		}
		
		//return the js 
		$this->return_data .= $this->_output_js('
			jQuery(window).ready(function(){
				EE_GMAPS.setRoute({		 
					from_address : "'.base64_encode($from_address).'", 
					from_latlng : "'.base64_encode($from_latlng).'", 
					to_address : "'.base64_encode($to_address).'", 	
					to_latlng : "'.base64_encode($to_latlng).'", 
					stops_addresses : "'.base64_encode($stops_address).'",
					stops_latlng : "'.base64_encode($stops_latlng).'",
					travel_mode: "'.$travel_mode.'",
					stroke_color: "'.$stroke_color.'",
					stroke_opacity: '.$stroke_opacity.',
					stroke_weight: '.$stroke_weight.',
					show_details : '.$show_details.',
					show_details_per_step : '.$show_details_per_step.',
					details_per_step_template : "'.$details_per_step_template.'",
					details_template : "'.$details_template.'",
					show_elevation : '.$show_elevation.',
					'.$this->_create_markers_array(__FUNCTION__).',
					'.$this->default_js_array.'					
				});
			});
		');

		/* -------------------------------------------
		/* 'gmaps_route_end' hook.
		/*  - Added: 2.3
		*/
		if (ee()->extensions->active_hook('gmaps_route_end') === TRUE)
		{
			ee()->extensions->call('gmaps_route_end', '');
		}
		// -------------------------------------------
	
		//parse {map_id}
		$this->return_data .= ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array(array('map_id'=>$this->caller_id)));

		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, false);

		//return the gmaps
		return $this->_minify_html_output($this->return_data);
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:polygon}
	 * 
	 * This is the polygon method according to http://reinos.nl/add-ons/gmaps/docs#Polygon
	 * 
	 * @return unknown_type
	 */
	function polygon()
	{
		//call the init function to init some default values
		$error = $this->_init(__FUNCTION__);
		if(gmaps_helper::log_has_error())
		{
			return $error;
		}
		
		//fetch params
		$address = ee()->gmaps->get_from_tagdata('address');
		$latlng = ee()->gmaps->get_from_tagdata('latlng');
		$json = ee()->gmaps->get_from_tagdata('json');

		$zoom = ee()->gmaps->get_from_tagdata('zoom', $this->default_zoom);
		$stroke_color = ee()->gmaps->get_from_tagdata('stroke_color', '#BBD8E9');
		$stroke_opacity = ee()->gmaps->get_from_tagdata('stroke_opacity', 1);
		$stroke_weight = ee()->gmaps->get_from_tagdata('stroke_weight', 3);
		$fill_color = ee()->gmaps->get_from_tagdata('fill_color', '#BBD8E9');
		$fill_opacity = ee()->gmaps->get_from_tagdata('fill_opacity',  0.6);
		$static = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('static', 'no'), true);
		
		//if address empty return a error.
		if($address == '' && $latlng == '' && $json == '') 
		{
			gmaps_helper::log('You forgot to fill in an address or latlng', 1);
			//ee()->gmaps->errors[] = 'You forgot to fill in an address or latlng';
			return ee()->gmaps->parse_errors();	
		}

		//return a div
		$this->return_data .= '<div data-gmaps-number="'.$this->caller_id.'" class="'.$this->div_class.'" id="'.$this->div_id.'"></div>';
		
		//get the adresses via the geocoder
		if($address != '')
		{
			$result = ee()->gmaps->geocode_address(ee()->gmaps->explode($address));
			$address = $result['address'];
			$latlng = $result['latlng'];
			$json = '';
		} 
		//get json file
		//@todo, need to build this function: http://hpneo.github.com/gmaps/examples/geojson_polygon.html
		else if ($json != '')
		{
			if(gmaps_helper::is_json($json))
			{
				$json = json_encode(json_decode($json));
				$address = "[]";
				$latlng = "[]";
			}
			else
			{
				gmaps_helper::log('Given json is not an json string.', 1);
				//ee()->gmaps->errors[] = 'Given json is not an json string.';
				return ee()->gmaps->parse_errors();	
			}
				
		}
		else 
		{
			$latlng = ($latlng);
			$address = "";
			$json = '';
		}

		//return the js 
		$this->return_data .= $this->_output_js('
			jQuery(window).ready(function(){
				EE_GMAPS.setPolygon({
					address : "'.base64_encode($address).'",
					latlng : "'.base64_encode($latlng).'",
					json : "'.$json.'",
					zoom : '.$zoom.',
					stroke_color : "'.$stroke_color.'",
					stroke_opacity : '.$stroke_opacity.',
					stroke_weight : '.$stroke_weight.',
					fill_color : "'.$fill_color.'",
					fill_opacity : '.$fill_opacity.',
					static : '.$static.',
					'.$this->_create_markers_array(__FUNCTION__).',
					'.$this->default_js_array.'
				});
			});
		');

		/* -------------------------------------------
		/* 'gmaps_polygon_end' hook.
		/*  - Added: 2.3
		*/
		if (ee()->extensions->active_hook('gmaps_polygon_end') === TRUE)
		{
			ee()->extensions->call('gmaps_polygon_end', '');
		}
		// -------------------------------------------
	
		//parse {map_id}
		$this->return_data .= ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array(array('map_id'=>$this->caller_id)));

		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, false);

		//return the gmaps
		return $this->_minify_html_output($this->return_data);
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:polyline}
	 * 
	 * This is the polyline method according to http://reinos.nl/add-ons/gmaps/docs#Polyline
	 * 
	 * @return unknown_type
	 */
	function polyline()
	{
		//call the init function to init some default values
		$error = $this->_init(__FUNCTION__);
		if(gmaps_helper::log_has_error())
		{
			return $error;
		}

		//set the specific vars
		$address = ee()->gmaps->get_from_tagdata('address');
		$latlng = ee()->gmaps->get_from_tagdata('latlng');
		
		$zoom = ee()->gmaps->get_from_tagdata('zoom', $this->default_zoom);
		$stroke_color = ee()->gmaps->get_from_tagdata('stroke_color', '#BBD8E9');
		$stroke_opacity = ee()->gmaps->get_from_tagdata('stroke_opacity', 1);
		$stroke_weight = ee()->gmaps->get_from_tagdata('stroke_weight', 3);
		$static = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('static', 'no'), true);
		
		//if address empty return a error.
		if($address == '' && $latlng == '') 
		{
			gmaps_helper::log('You forgot to fill in an address or latlng.', 1);
			//ee()->gmaps->errors[] = 'You forgot to fill in an address or latlng.';
			return ee()->gmaps->parse_errors();	
		}

		//return a div
		$this->return_data .= '<div data-gmaps-number="'.$this->caller_id.'" class="'.$this->div_class.'" id="'.$this->div_id.'"></div>';
		
		//get the adresses via the geocoder
		if($address != '')
		{
			$result = ee()->gmaps->geocode_address(ee()->gmaps->explode($address));
			$address = $result['address'];
			$latlng = $result['latlng'];
		}
		else 
		{
			$latlng = ($latlng);
			$address = "";
		}

		//return the js 
		$this->return_data .= $this->_output_js('
			jQuery(window).ready(function(){
				EE_GMAPS.setPolyline({
					address : "'.base64_encode($address).'",
					latlng : "'.base64_encode($latlng).'",
					zoom : '.$zoom.',
					stroke_color : "'.$stroke_color.'",
					stroke_opacity : '.$stroke_opacity.',
					stroke_weight : '.$stroke_weight.',
					static : '.$static.',
					'.$this->_create_markers_array(__FUNCTION__).',
					'.$this->default_js_array.'
				});
			});
		');

		/* -------------------------------------------
		/* 'gmaps_polygon_end' hook.
		/*  - Added: 2.3
		*/
		if (ee()->extensions->active_hook('gmaps_polygon_end') === TRUE)
		{
			ee()->extensions->call('gmaps_polygon_end', '');
		}
		// -------------------------------------------
	
		//parse {map_id}
		$this->return_data .= ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array(array('map_id'=>$this->caller_id)));

		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, false);

		//return the gmaps
		return $this->_minify_html_output($this->return_data);
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:circle}
	 * 
	 * This is the circle method according to http://reinos.nl/add-ons/gmaps/docs#Circle
	 * 
	 * @return unknown_type
	 */
	function circle()
	{
		//call the init function to init some default values
		$error = $this->_init(__FUNCTION__);
		if(gmaps_helper::log_has_error())
		{
			return $error;
		}

		//set the specific vars
		//$address = build_js_array(ee()->gmaps->get_from_tagdata('address', ''));	
		
		$address = ee()->gmaps->get_from_tagdata('address');
		$latlng = ee()->gmaps->get_from_tagdata('latlng');		
		$zoom = ee()->gmaps->get_from_tagdata('zoom', $this->default_zoom);		
		//circle specific
		$fit_circle = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('fit_circle', 'yes'), true);
		$stroke_color = ee()->gmaps->get_from_tagdata('stroke_color', '#BBD8E9');
		$stroke_opacity = ee()->gmaps->get_from_tagdata('stroke_opacity', 1);
		$stroke_weight = ee()->gmaps->get_from_tagdata('stroke_weight', 3);
		$fill_color = ee()->gmaps->get_from_tagdata('fill_color', '#BBD8E9');
		$fill_opacity = ee()->gmaps->get_from_tagdata('fill_opacity', 0.6);
		$radius = ee()->gmaps->get_from_tagdata('radius', 1000);	

		//if address empty return a error.
		if($address == '' && $latlng == '') 
		{
			gmaps_helper::log('You forgot to fill in an address or latlng.', 1);
			//ee()->gmaps->errors[] = 'You forgot to fill in an address or latlng.';
			return ee()->gmaps->parse_errors();	
		}

		//prepare address
		if($address != '')
		{
			$address = array_shift(ee()->gmaps->explode($address));
			$result = ee()->gmaps->geocode_address(array($address), 'string');
			$address = $result['address'];
			$latlng = $result['latlng'];
		}
		else 
		{
			$latlng = array_shift(ee()->gmaps->explode($latlng));
			$address = '';
		}
		
		//return a div
		$this->return_data .= '<div data-gmaps-number="'.$this->caller_id.'" class="'.$this->div_class.'" id="'.$this->div_id.'"></div>';
		
		//return the js 
		$this->return_data .= $this->_output_js('
			jQuery(window).ready(function(){
				EE_GMAPS.setCircle({
					address : "'.base64_encode($address).'",
					latlng : "'.base64_encode($latlng).'",
					zoom : '.$zoom.',
					fit_circle : '.$fit_circle.',
					stroke_color : "'.$stroke_color.'",
					stroke_opacity : '.$stroke_opacity.',
					stroke_weight : '.$stroke_weight.',
					fill_color : "'.$fill_color.'",
					fill_opacity : '.$fill_opacity.',
					radius : '.$radius.',
					'.$this->_create_markers_array(__FUNCTION__).',
					'.$this->default_js_array.'
				});
			});
		');

		/* -------------------------------------------
		/* 'gmaps_circle_end' hook.
		/*  - Added: 2.3
		*/
		if (ee()->extensions->active_hook('gmaps_circle_end') === TRUE)
		{
			ee()->extensions->call('gmaps_circle_end', '');
		}
		// -------------------------------------------
	
		//parse {map_id}
		$this->return_data .= ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array(array('map_id'=>$this->caller_id)));

		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, false);

		//return the gmaps
		return $this->_minify_html_output($this->return_data);
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:rectangle}
	 * 
	 * This is the rectangle method according to http://reinos.nl/add-ons/gmaps/docs#Rectangle
	 * 
	 * @return unknown_type
	 */
	function rectangle()
	{
		//call the init function to init some default values
		$error = $this->_init(__FUNCTION__);
		if(gmaps_helper::log_has_error())
		{
			return $error;
		}

		//set the specific vars
		//$address = build_js_array(ee()->gmaps->get_from_tagdata('address', ''));	
		
		$address = ee()->gmaps->get_from_tagdata('address');
		$latlng = ee()->gmaps->get_from_tagdata('latlng');
		//$address = ee()->gmaps->get_from_tagdata('address', '');	
		//$latlng = ee()->gmaps->get_from_tagdata('latlng', '');

		$zoom = ee()->gmaps->get_from_tagdata('zoom', $this->default_zoom);
		
		//rectangle specific
		$stroke_color = ee()->gmaps->get_from_tagdata('stroke_color', '#BBD8E9');
		$stroke_opacity = ee()->gmaps->get_from_tagdata('stroke_opacity', 1);
		$stroke_weight = ee()->gmaps->get_from_tagdata('stroke_weight', 3);
		$fill_color = ee()->gmaps->get_from_tagdata('fill_color', '#BBD8E9');
		$fill_opacity = ee()->gmaps->get_from_tagdata('fill_opacity', 0.6);

		//if address empty return a error.
		if($address == '' && $latlng == '') 
		{
			gmaps_helper::log('You forgot to fill in an address or latlng.', 1);
			//ee()->gmaps->errors[] = 'You forgot to fill in an address or latlng.';
			return ee()->gmaps->parse_errors();	
		}

		//if address is lower than two.
		if(gmaps_helper::count_multiple_values(ee()->gmaps->get_from_tagdata('address', '')) != 2 && gmaps_helper::count_multiple_values(ee()->gmaps->get_from_tagdata('latlng', '')) != 2) 
		{
			gmaps_helper::log('This method need only 2 addresses or latlng to create a rectangle', 1);
			//ee()->gmaps->errors[] = 'This method need only 2 addresses or latlng to create a rectangle';
			return ee()->gmaps->parse_errors();	
		}

		//prepare address
		if($address != '')
		{
			$address = ee()->gmaps->explode($address);
			$address = array_slice($address, 0, 2);
			$result = ee()->gmaps->geocode_address($address);
			$address = $result['address'];
			$latlng = $result['latlng'];
		}
		else //if($latlng != '')
		{
			$latlng = ($latlng);
			$address = "";
		}

		//return a div
		$this->return_data .= '<div data-gmaps-number="'.$this->caller_id.'" class="'.$this->div_class.'" id="'.$this->div_id.'"></div>';
		
		//return the js 
		$this->return_data .= $this->_output_js('
			jQuery(window).ready(function(){
				EE_GMAPS.setRectangle({					
					address : "'.base64_encode($address).'",	
					latlng : "'.base64_encode($latlng).'",				
					zoom : '.$zoom.',
					stroke_color : "'.$stroke_color.'",
					stroke_opacity : '.$stroke_opacity.',
					stroke_weight : '.$stroke_weight.',
					fill_color : "'.$fill_color.'",
					fill_opacity : '.$fill_opacity.',
					'.$this->default_js_array.'					
				});
			});
		');

		/* -------------------------------------------
		/* 'gmaps_rectangle_end' hook.
		/*  - Added: 2.3
		*/
		if (ee()->extensions->active_hook('gmaps_rectangle_end') === TRUE)
		{
			ee()->extensions->call('gmaps_rectangle_end', '');
		}
		// -------------------------------------------
	
		//parse {map_id}
		$this->return_data .= ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array(array('map_id'=>$this->caller_id)));

		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, false);

		//return the gmaps
		return $this->_minify_html_output($this->return_data);
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:places}
	 * 
	 * This is the places method according to http://reinos.nl/add-ons/gmaps/docs#Places
	 * 
	 * @return unknown_type
	 */
	function places()
	{
		//call the init function to init some default values
		$error = $this->_init(__FUNCTION__);
		if(gmaps_helper::log_has_error())
		{
			return $error;
		}

		//set the specific vars
		$address = ee()->gmaps->get_from_tagdata('address');
		$latlng = ee()->gmaps->get_from_tagdata('latlng');
		//$address = ee()->gmaps->get_from_tagdata('address', '');	
		//$latlng = ee()->gmaps->get_from_tagdata('latlng', '');

		$zoom = ee()->gmaps->get_from_tagdata('zoom', $this->default_zoom);
		$search_keyword = ee()->gmaps->get_from_tagdata('search_keyword', '');
		$search_types = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('search_types', ''));
		$radius = ee()->gmaps->get_from_tagdata('radius', 1000);	
		$type = ee()->gmaps->get_from_tagdata('type', 'search'); // also radar_search is possible

		//if address empty return a error.
		if($address == '' && $latlng == '') 
		{
			gmaps_helper::log('You forgot to fill in an address or latlng.', 1);
			//ee()->gmaps->errors[] = 'You forgot to fill in an address or latlng.';
			return ee()->gmaps->parse_errors();	
		}

		//if we don`t apply an search than trigger error
		if($search_types == '[]') 
		{
			gmaps_helper::log('You forgot to fill in the search param search_types=""', 1);
			//ee()->gmaps->errors[] = 'You forgot to fill in the search param search_types=""';
			return ee()->gmaps->parse_errors();	
		}

		//prepare address
		if($address != '')
		{
			$address = array_shift(ee()->gmaps->explode($address));
			$result = ee()->gmaps->geocode_address(array($address), 'string');
			$address = $result['address'];
			$latlng = $result['latlng'];
		}
		else
		{
			$latlng = array_shift(ee()->gmaps->explode($latlng));
			$address = '';
		}

		//return a div
		$this->return_data .= '<div data-gmaps-number="'.$this->caller_id.'" class="'.$this->div_class.'" id="'.$this->div_id.'"></div>';
		
		//return the js 
		$this->return_data .= $this->_output_js('
			jQuery(window).ready(function(){
				EE_GMAPS.setPlaces({
					address : "'.base64_encode($address).'",
					latlng : "'.base64_encode($latlng).'",
					search_keyword : "'.$search_keyword.'",
					search_types : '.$search_types.',
					zoom : '.$zoom.',
					'.$this->_create_markers_array(__FUNCTION__).',
					radius : '.$radius.',
					type : "'.$type.'",
					'.$this->default_js_array.'
				});
			});
		');

		/* -------------------------------------------
		/* 'gmaps_places_end' hook.
		/*  - Added: 2.3
		*/
		if (ee()->extensions->active_hook('gmaps_places_end') === TRUE)
		{
			ee()->extensions->call('gmaps_places_end', '');
		}
		// -------------------------------------------
		
		//parse {map_id}
		$this->return_data .= ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array(array('map_id'=>$this->caller_id)));

		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, false);

		//return the gmaps
		return $this->_minify_html_output($this->return_data);
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:kml_georss}
	 * 
	 * This is the kml_georss method according to http://reinos.nl/add-ons/gmaps/docs#Kml_georss
	 *  
	 * @return unknown_type
	 */
	function kml_georss()
	{
		//call the init function to init some default values
		$error = $this->_init(__FUNCTION__);
		if(gmaps_helper::log_has_error())
		{
			return $error;
		}
		
		//set the specific vars
		$zoom = ee()->gmaps->get_from_tagdata('zoom', '0'); //zoom is empty because it is not always
		$kml_url = ee()->gmaps->get_from_tagdata('kml_url', '');

		$address = ee()->gmaps->get_from_tagdata('address');
		$latlng = ee()->gmaps->get_from_tagdata('latlng');
		

		//if address empty return a error.
		if($kml_url == '') 
		{
			gmaps_helper::log('You forgot to fill in an kml_url', 1);
			//ee()->gmaps->errors[] = 'You forgot to fill in an kml_url';
			return ee()->gmaps->parse_errors();	
		}

		//prepare address
		if($address != '')
		{
			$address = ee()->gmaps->explode($address);
			$address = array_slice($address, 0, 2);
			$result = ee()->gmaps->geocode_address($address);
			$address = $result['address'];
			$latlng = $result['latlng'];
		}
		else //if($latlng != '')
		{
			$latlng = ($latlng);
			$address = "";
		}

		//return a div
		$this->return_data .= '<div data-gmaps-number="'.$this->caller_id.'" class="'.$this->div_class.'" id="'.$this->div_id.'"></div>';
		
		//return the js 
		$this->return_data .= $this->_output_js('
			jQuery(window).ready(function(){
				EE_GMAPS.setKml({										
					kml_url : "'.$kml_url.'",					
					zoom : '.$zoom.',
					address : "'.base64_encode($address).'",	
					latlng : "'.base64_encode($latlng).'",	
					'.$this->default_js_array.'					
				});
			});
		');

		/* -------------------------------------------
		/* 'gmaps_kml_georss_end' hook.
		/*  - Added: 2.3
		*/
		if (ee()->extensions->active_hook('gmaps_kml_georss_end') === TRUE)
		{
			ee()->extensions->call('gmaps_kml_georss_end', '');
		}
		// -------------------------------------------
	
		//parse {map_id}
		$this->return_data .= ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array(array('map_id'=>$this->caller_id)));

		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, false);

		//return the gmaps
		return $this->_minify_html_output($this->return_data);
	}


	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:fusion_tables}
	 * 
	 * This is the fusion_tables method according to http://reinos.nl/add-ons/gmaps/docs#fusion_tables
	 * 
	 * @return unknown_type
	 */
	function fusion_tables()
	{
		//call the init function to init some default values
		$error = $this->_init(__FUNCTION__);
		if(gmaps_helper::log_has_error())
		{
			return $error;
		}

		//set the specific vars
		//$address = build_js_array(ee()->gmaps->get_from_tagdata('address', ''));	
		$address = ee()->gmaps->get_from_tagdata('address');
		$latlng = ee()->gmaps->get_from_tagdata('latlng');
		//$address = ee()->gmaps->get_from_tagdata('address', '');	
		//$latlng = ee()->gmaps->get_from_tagdata('latlng', '');	
		
		$table_id = ee()->gmaps->get_from_tagdata('table_id', '');	
		$zoom = ee()->gmaps->get_from_tagdata('zoom', $this->default_zoom);
		$fields = ee()->gmaps->get_from_tagdata('fields', '');	
		$heatmap = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('heatmap', 'no'), true);

		//set the correct values
		$_fields = $this->preg_array_key_exists('/fields:/',ee()->TMPL->tagparams);
		$fields = array();
		$field_options = array();
		if(!empty($_fields))
		{
			// Create a tussen array which can be threated for the js converting
			foreach($_fields as $val)
			{
				$_val = explode(':', $val);
				
				//explode on | by multiple values
				$param = explode('|', ee()->gmaps->get_from_tagdata($val, ''));

				//multiple values?
				if(count($param) > 1)
				{
					$tmp_val = array();
					foreach($param as $v)
					{
						$tmp_val[] = $v;
					}

					$fields[$_val[1]][$_val[2]] = $tmp_val;
					
				}

				//single value
				else
				{
					$fields[$_val[1]][$_val[2]] = $param[0];
				}				
			}

			//create a good array for converting to JS
			foreach($fields as $key=>$val)
			{
				$tmp = array();

				//set default
				$fields['default']['fill_color'] = isset($fields['default']['fill_color']) ? $fields['default']['fill_color'] : '' ;
				$fields['default']['fill_opacity'] = isset($fields['default']['fill_opacity']) ? $fields['default']['fill_opacity'] : '' ;
				$fields['default']['stroke_color'] = isset($fields['default']['stroke_color']) ? $fields['default']['stroke_color'] : '' ;
				$fields['default']['stroke_weight'] = isset($fields['default']['stroke_weight']) ? $fields['default']['stroke_weight'] : '' ;

				if($key == 'default')
				{

					$tmp['fillColor'] =  isset($val['fill_color']) ? $val['fill_color'] : '' ;
					$tmp['fillOpacity'] =  isset($val['fill_opacity']) ? $val['fill_opacity'] : '' ;
					$tmp['strokeWeight'] =  isset($val['stroke_weight']) ? $val['stroke_weight'] : '' ;
					$tmp['strokeColor'] =  isset($val['stroke_color']) ? $val['stroke_color'] : '' ;

					$field_options[] = array(
						'polygonOptions' => $tmp
					);
				}
				else
				{
					//check for multiple values
					if(isset($val['where']) && count($val['where']) > 1)
					{
						foreach($val['where'] as $k=>$v)
						{
							//fill_color
							if(isset($val['fill_color'][$k]) && is_array($val['fill_color']))
							{
								$tmp['fillColor'] = $val['fill_color'][$k];
							}
							else
							{
								if(!isset($val['fill_color']) || !isset($val['fill_color'][$k]))
								{
									$tmp['fillColor'] = isset($fields['default']['fill_color']) ? $fields['default']['fill_color'] : '';
								}
								else if(!is_array($val['fill_color']))
								{
									$tmp['fillColor'] = $val['fill_color'];
								}	
							}

							//fill_opacity
							if(isset($val['fill_opacity'][$k]) && is_array($val['fill_opacity']))
							{
								$tmp['fillOpacity'] = $val['fill_opacity'][$k];
							}
							else
							{
								if(!isset($val['fill_opacity']) || !isset($val['fill_opacity'][$k]))
								{
									$tmp['fillColor'] = isset($fields['default']['fill_opacity']) ? $fields['default']['fill_opacity'] : '';
								}
								else if(!is_array($val['fill_opacity']))
								{
									$tmp['fillOpacity'] = $val['fill_opacity'];
								}
							}

							//stroke_color
							if(isset($val['stroke_color'][$k]) && is_array($val['stroke_color']))
							{
								$tmp['strokeColor'] = $val['stroke_color'][$k];
							}
							else
							{
								if(!isset($val['stroke_color']) || !isset($val['stroke_color'][$k]))
								{
									$tmp['strokeColor'] = isset($fields['default']['stroke_color']) ? $fields['default']['stroke_color'] : '';
								}
								else if(!is_array($val['stroke_color']))
								{
									$tmp['strokeColor'] = $val['stroke_color'];
								}
							}

							//stroke_weight
							if(isset($val['stroke_weight'][$k]) && is_array($val['stroke_weight']))
							{
								$tmp['strokeWeight'] = $val['stroke_weight'][$k];
							}
							else
							{
								if(!isset($val['stroke_weight']) || !isset($val['stroke_weight'][$k]))
								{
									$tmp['strokeWeight'] = isset($fields['default']['stroke_weight']) ? $fields['default']['stroke_weight'] : '';
								}
								else if(!is_array($val['stroke_weight']))
								{
									$tmp['strokeWeight'] = $val['stroke_weight'];
								}
							}

							//get the operator (=,>= etc..)
							$operators = array('>', '<', '>=', '<=', '=');
							$operator_selected = '';

							//get the active operator
							foreach($operators as $op) 
							{
								$founded = stripos($v, $op);
								if($founded !== false) 
								{
									$v = str_replace($op, '', $v);
									$operator_selected = $op;
								}
							}

							//push the values
							$field_options[] = array(
								'where' =>  "'".$key."' ".$operator_selected." '".$v."'",
								'polygonOptions' => $tmp
							);
						}	
					}

					//single values
					else
					{
						$where =  isset($val['where']) ? $key.''.$val['where'] : '' ;
						if($where != '')
						{


							$tmp['fillColor'] =  isset($val['fill_color']) ? $val['fill_color'] : $fields['default']['fill_color'] ;
							$tmp['fillOpacity'] =  isset($val['fill_opacity']) ? $val['fill_opacity'] : $fields['default']['fill_opacity'] ;
							$tmp['strokeColor'] =  isset($val['stroke_color']) ? $val['stroke_color'] : $fields['default']['stroke_color'] ;
							$tmp['strokeWeight'] =  isset($val['stroke_weight']) ? $val['stroke_weight'] : $fields['default']['stroke_weight'] ;
						
							//get the operator (=,>= etc..)
							$operators = array('>', '<', '>=', '<=', '=');
							$operator_selected = '';

							//get the active operator
							foreach($operators as $op) 
							{
								$founded = stripos($where, $op);

								if($founded !== false) 
								{
									$where_ = explode($op, $where);
									$operator_selected = $op;
								}
							}

							//push the values
							$field_options[] = array(
								'where' => "'".$where_[0]."' ".$operator_selected." '".$where_[1]."'",
								'polygonOptions' => $tmp
							);
						}
					}
				}		
			}
		}

		//if address empty return a error.
		if($table_id == '') 
		{
			gmaps_helper::log('You forgot to fill in an table_id', 1);
			//ee()->gmaps->errors[] = 'You forgot to fill in an table_id';
			return ee()->gmaps->parse_errors();	
		}

		//if address empty return a error.
		if($address == '' && $latlng == '') 
		{
			gmaps_helper::log('You forgot to fill in an address or latlng', 1);
			//ee()->gmaps->errors[] = 'You forgot to fill in an address or latlng';
			return ee()->gmaps->parse_errors();	
		}

		//prepare address
		if($address != '')
		{
			$address = array_shift(ee()->gmaps->explode($address));
			$result = ee()->gmaps->geocode_address(array($address), 'string');
			$address = $result['address'];
			$latlng = $result['latlng'];
		}
		else 
		{
			$latlng = array_shift(ee()->gmaps->explode($latlng));
			$address = '';
		}

		//load google api
		$this->return_data .= '
			<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		';

		//return a div
		$this->return_data .= '<div data-gmaps-number="'.$this->caller_id.'" class="'.$this->div_class.'" id="'.$this->div_id.'"></div>';
		
		//return the js 
		$this->return_data .= $this->_output_js('
			jQuery(window).ready(function(){
				EE_GMAPS.setFusionTable({					
					address : "'.base64_encode($address).'",
					latlng : "'.base64_encode($latlng).'",
					table_id : "'.$table_id.'",
					styles : '.$this->convert_array_to_js($field_options).',
					heatmap : '.$heatmap.',					
					zoom : '.$zoom.',
					'.$this->default_js_array.'					
				});
			});
		');

		/* -------------------------------------------
		/* 'gmaps_fusion_table_end' hook.
		/*  - Added: 2.3
		*/
		if (ee()->extensions->active_hook('gmaps_fusion_table_end') === TRUE)
		{
			ee()->extensions->call('gmaps_fusion_table_end', '');
		}
		// -------------------------------------------
		
		//parse {map_id}
		$this->return_data .= ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array(array('map_id'=>$this->caller_id)));

		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, false);

		//return the gmaps
		return $this->_minify_html_output($this->return_data);
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:street_view_panorama}
	 * 
	 * This is the street_view_panorama method according to http://reinos.nl/add-ons/gmaps/docs#Street_view_panorama
	 * 
	 * @return unknown_type
	 */
	function street_view_panorama()
	{
		//call the init function to init some default values
		$error = $this->_init(__FUNCTION__);
		if(gmaps_helper::log_has_error())
		{
			return $error;
		}
		
		//set the specific vars
		$address = ee()->gmaps->get_from_tagdata('address');
		$latlng = ee()->gmaps->get_from_tagdata('latlng');
		//$address = ee()->gmaps->get_from_tagdata('address', '');
		//$latlng = ee()->gmaps->get_from_tagdata('latlng', '');
		
		$pov_heading = ee()->gmaps->get_from_tagdata('pov:heading', 0);
		$pov_pitch = ee()->gmaps->get_from_tagdata('pov:pitch', 0);
		$pov_zoom = ee()->gmaps->get_from_tagdata('pov:zoom', 0);
		$address_control = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('address_control', 'yes'), true);
		$click_to_go = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('click_to_go', 'yes'), true);
		$disable_double_click_zoom = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('disable_double_click_zoom', 'yes'), true);
		$enable_close_button = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('enable_close_button', 'no'), true);
		$image_date_control = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('image_date_control', 'yes'), true);
		$links_control = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('links_control', 'yes'), true);
		$visible = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('visible', 'yes'), true);
		$checkaround = ee()->gmaps->get_from_tagdata('checkaround', '50');

		//if address empty return a error.
		// fill in by the user
		if($address == '' && $latlng == '') 
		{
			gmaps_helper::log('You forgot to fill in an address or latlng', 1);
			//ee()->gmaps->errors[] = 'You forgot to fill in an address or latlng';
			return ee()->gmaps->parse_errors();	
		}

		//get the adresses via the geocoder
		if($address != '')
		{
			$result = ee()->gmaps->geocode_address(explode('|', $address));
			$address = $result['address'];
			$latlng = $result['latlng'];
			$raw_latlng = $result['raw_latlng'];
		}
		else 
		{
			$latlng = ($latlng);
			$address = "";
		}

		//if address empty return a error.
		// after geocoding
		if(($address == '[]' || $address == '') && ($latlng == '[]' || $latlng == ''))
		{
			gmaps_helper::log('No result founded', 1);
			//ee()->gmaps->errors[] = 'No result founded';
			return ee()->gmaps->parse_errors().ee()->TMPL->no_results();	
		}

		//return a div
		$this->return_data .= '<div data-gmaps-number="'.$this->caller_id.'" class="'.$this->div_class.'" id="'.$this->div_id.'"></div>';
		
		//return the js 
		$this->return_data .= $this->_output_js('
			jQuery(window).ready(function(){
				EE_GMAPS.setStreetViewPanorama({
					address : "'.base64_encode($address).'",
					latlng : "'.base64_encode($latlng).'",
					address_control : '.$address_control.',
					click_to_go : '.$click_to_go.',
					disable_double_click_zoom : '.$disable_double_click_zoom.',
					enable_close_button : '.$enable_close_button.',
					image_date_control : '.$image_date_control.',
					links_control : '.$links_control.',
					visible : '.$visible.',
					checkaround : '.$checkaround.',
					pov : {
						heading : '.$pov_heading.',
						pitch : '.$pov_pitch.',
						zoom : '.$pov_zoom.'
					},
					'.$this->default_js_array.'					
				});
			});
		');

		/* -------------------------------------------
		/* 'gmaps_geolocation_end' hook.
		/*  - Added: 2.5
		*/
		if (ee()->extensions->active_hook('gmaps_panorama_end') === TRUE)
		{
			ee()->extensions->call('gmaps_panorama_end', '');
		}
		// -------------------------------------------
		
		//parse {map_id}
		$this->return_data .= ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array(array('map_id'=>$this->caller_id)));

		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, false);

		//return the gmaps
		return $this->_minify_html_output($this->return_data);
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:empty_map}
	 * 
	 * This is the empty_map method according to http://reinos.nl/add-ons/gmaps/docs#Empty_map
	 * 
	 * @return unknown_type
	 */
	function empty_map()
	{
		//call the init function to init some default values
		$error = $this->_init(__FUNCTION__);
		if(gmaps_helper::log_has_error())
		{
			return $error;
		}

		//set the specific vars
		$address = ee()->gmaps->get_from_tagdata('address');
		$latlng = ee()->gmaps->get_from_tagdata('latlng');
		//$address = ee()->gmaps->get_from_tagdata('address', '');
		//$latlng = ee()->gmaps->get_from_tagdata('latlng', '');
		$zoom = ee()->gmaps->get_from_tagdata('zoom', 1);

		if($address != '')
		{
			$result = ee()->gmaps->geocode_address(explode('|', $address));
			$address = $result['address'];
			$latlng = $result['latlng'];
		}
		else 
		{
			$latlng = ($latlng);
			$address = "";
		}

		//return a div
		$this->return_data .= '<div data-gmaps-number="'.$this->caller_id.'" class="'.$this->div_class.'" id="'.$this->div_id.'"></div>';
		
		//return the js 
		$this->return_data .= $this->_output_js('
			jQuery(window).ready(function(){
				EE_GMAPS.setEmptyMap({
					address : "'.base64_encode($address).'",
					latlng : "'.base64_encode($latlng).'",
					zoom : '.$zoom.',
					'.$this->default_js_array.'					
				});
			});
		');

		/* -------------------------------------------
		/* 'gmaps_geolocation_end' hook.
		/*  - Added: 2.6
		*/
		if (ee()->extensions->active_hook('gmaps_empty_map_end') === TRUE)
		{
			ee()->extensions->call('gmaps_empty_map_end', '');
		}
		// -------------------------------------------
		
		//parse {map_id}
		$this->return_data .= ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array(array('map_id'=>$this->caller_id)));
		
		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, false);

		//return the gmaps
		return $this->_minify_html_output($this->return_data);
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:map}
	 * 
	 * This is the map method according to http://reinos.nl/add-ons/gmaps/docs#Empty_map
	 * 
	 * @return unknown_type
	 */
	function map()
	{
		//call the init function to init some default values
		$error = $this->_init(__FUNCTION__, false);
		if(gmaps_helper::log_has_error())
		{
			return $error;
		}

		//set the specific vars
		$zoom = ee()->gmaps->get_from_tagdata('zoom', $this->default_zoom);
		//$static = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('static', 'no'), true);
		$show_elevation = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('show_elevation', 'no'), true);

		//cluster @todo gelijk trekken met de cluster uit de geocoding
		$show_marker_cluster = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('marker:show_marker_cluster', 'no'), true);
		$marker_cluster_style = $this->_set_cluster_style();

		//return a div
		$this->return_data .= '<div data-gmaps-number="'.$this->caller_id.'" class="'.$this->div_class.'" id="'.$this->div_id.'"></div>';
		
		//return the js 
		$this->return_data .= $this->_output_js('
			jQuery(window).ready(function(){
				EE_GMAPS.setMap({
					zoom : '.$zoom.',
					marker_cluster : '.$show_marker_cluster.',
					marker_cluster_stle : '.$marker_cluster_style.',
					show_elevation : '.$show_elevation.',
					'.$this->default_js_array.'					
				});
			});
		');

		/* -------------------------------------------
		/* 'gmaps_geolocation_end' hook.
		/*  - Added: 2.6
		*/
		if (ee()->extensions->active_hook('gmaps_empty_map_end') === TRUE)
		{
			ee()->extensions->call('gmaps_empty_map_end', '');
		}
		// -------------------------------------------
		
		//parse {map_id}
		$this->return_data .= ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array(array('map_id'=>$this->caller_id)));
		$this->return_data = str_replace(LD.'map_id'.RD, $this->caller_id, $this->return_data);
		
		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, false);

		//return the gmaps
		return $this->_minify_html_output($this->return_data);
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:add_marker}
	 * 
	 * This is the geocoder method according to http://reinos.nl/add-ons/gmaps/docs#Geocoder
	 *  
	 * @return unknown_type
	 */
	function add_marker()
	{	
		return $this->_minify_html_output(ee()->gmaps_api->create_marker());
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:add_polyline}
	 * 
	 * This is the geocoder method according to http://reinos.nl/add-ons/gmaps/docs#Geocoder
	 *  
	 * @return unknown_type
	 */
	function add_polyline()
	{	
		return $this->_minify_html_output(ee()->gmaps_api->create_polyline());
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:add_polygon}
	 * 
	 * This is the geocoder method according to http://reinos.nl/add-ons/gmaps/docs#Geocoder
	 *  
	 * @return unknown_type
	 */
	function add_polygon()
	{	
		return $this->_minify_html_output(ee()->gmaps_api->create_polygon());
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:add_circle}
	 * 
	 * This is the geocoder method according to http://reinos.nl/add-ons/gmaps/docs#Geocoder
	 *  
	 * @return unknown_type
	 */
	function add_circle()
	{	
		return $this->_minify_html_output(ee()->gmaps_api->create_circle());
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:add_rectangle}
	 * 
	 * This is the geocoder method according to http://reinos.nl/add-ons/gmaps/docs#Geocoder
	 *  
	 * @return unknown_type
	 */
	function add_rectangle()
	{	
		return $this->_minify_html_output(ee()->gmaps_api->create_rectangle());
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:fit_map}
	 * 
	 * This is the geocoder method according to http://reinos.nl/add-ons/gmaps/docs#Geocoder
	 *  
	 * @return unknown_type
	 */
	function fit_map()
	{	
		return $this->_minify_html_output(ee()->gmaps_api->fit_map());
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:set_center}
	 * 
	 * This is the geocoder method according to http://reinos.nl/add-ons/gmaps/docs#Geocoder
	 *  
	 * @return unknown_type
	 */
	function center()
	{	
		return $this->_minify_html_output(ee()->gmaps_api->set_center());
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:set_center}
	 * 
	 * This is the geocoder method according to http://reinos.nl/add-ons/gmaps/docs#Geocoder
	 *  
	 * @return unknown_type
	 */
	function zoom()
	{	
		//return $this->_minify_html_output(ee()->gmaps_api->set_center());
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:geocoder}
	 * 
	 * This is the geocoder method according to http://reinos.nl/add-ons/gmaps/docs#Geocoder
	 *  
	 * @return unknown_type
	 */
	function geocoder()
	{
		//call the init function to init some default values
		$this->_init('geocoder');
		
		//set the specific vars
		$address = gmaps_helper::remove_empty_array_values(explode('|', ee()->gmaps->get_from_tagdata('address')));	
		$latlng = gmaps_helper::remove_empty_array_values(explode('|', ee()->gmaps->get_from_tagdata('latlng')));	
		$ip = gmaps_helper::remove_empty_array_values(explode('|', ee()->gmaps->get_from_tagdata('ip')));	
		//$address = remove_empty_array_values(explode('|', ee()->gmaps->get_from_tagdata('address', '')));	
		//$latlng = remove_empty_array_values(explode('|', ee()->gmaps->get_from_tagdata('latlng', '')));	
		//$ip = remove_empty_array_values(explode('|', ee()->gmaps->get_from_tagdata('ip', '')));	

		//define var
		$variables = array();

		//switch
		if(!empty($address))
		{
			$result = ee()->gmaps->geocode_address($address, 'array', 'all');
			$variables[0]['address'] = $result;
		}
		else if(!empty($latlng))
		{
			$result = ee()->gmaps->geocode_latlng($latlng, 'array', 'all');
			$variables[0]['address'] = $result;
		}
		else if(!empty($ip))
		{
			$result = ee()->gmaps->geocode_ip($ip);
			$variables[0]['address'] = $result;
		}

		/* -------------------------------------------
		/* 'gmaps_geocoder_end' hook.
		/*  - Added: 2.3
		*/
		if (ee()->extensions->active_hook('gmaps_geocoder_end') === TRUE)
		{
			ee()->extensions->call('gmaps_geocoder_end', '');
		}
		// -------------------------------------------

		//no result?
		if(!isset($variables[0]['address'][0]))
		{
			return false;
		}

		//set the var to a new var
		$variables = array(
			array('result'=> $variables[0]['address'])
		);

		//EDT Benchmark
		ee()->gmaps->benchmark('geocoder', false);

		//return the gmaps
		return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);
	}

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:calculate_distance}
	 *
	 * This is the calculate_distance method according to http://reinos.nl/add-ons/gmaps/docs#calculate_distance
	 *
	 * @return unknown_type
	 */
	function calculate_distance()
	{
		//call the init function to init some default values
		$this->_init('calculate_distance');

		//set the specific vars
		$address_from = ee()->gmaps->get_from_tagdata('address_from');
		$address_to = ee()->gmaps->get_from_tagdata('address_to');
		$latlng_from = ee()->gmaps->get_from_tagdata('latlng_from');
		$latlng_to = ee()->gmaps->get_from_tagdata('latlng_to');

		$direct = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('direct', 'yes'));

		//switch
		if(!empty($address_from) && !empty($address_to))
		{
			$result1 = ee()->gmaps->geocode_address(array($address_from), 'array', 'all');
			$result2 = ee()->gmaps->geocode_address(array($address_to), 'array', 'all');
		}
		else if(!empty($latlng_from) && !empty($latlng_to))
		{
			$result1 = ee()->gmaps->geocode_address(array($latlng_from), 'array', 'all');
			$result2 = ee()->gmaps->geocode_address(array($latlng_to), 'array', 'all');
		}


		//if address empty return a error.
		// after geocoding
		if(($result1 == '[]' || $result1 == '') && ($result2 == '[]' || $result2 == ''))
		{
			gmaps_helper::log('No result founded', 1);
			//ee()->gmaps->errors[] = 'No result founded';
			return ee()->gmaps->parse_errors().ee()->TMPL->no_results();
		}

		/* -------------------------------------------
		/* 'gmaps_geocoder_end' hook.
		/*  - Added: 2.3
		*/
		if (ee()->extensions->active_hook('gmaps_calculate_distance_end') === TRUE)
		{
			ee()->extensions->call('gmaps_calculate_distance_end', '');
		}
		// -------------------------------------------

		$result = array();

		//calculate the distance
		if($direct)
		{
			$result['m'] = $this->haversine_great_circle_distance($result1[0]['latitude'], $result1[0]['longitude'], $result2[0]['latitude'], $result2[0]['longitude']);
			$result['k'] = $result['m'] / 1000;
		}
		else
		{
			$result = $this->get_driving_information($address_from, $address_to);

			//return false?
			if(!$result)
			{
				gmaps_helper::log('No result founded', 1);
				return ee()->gmaps->parse_errors().ee()->TMPL->no_results();
			}

			//convert to meters
			else
			{
				$result = array(
					'k' => $result['distance'],
					'm' => $result['distance'] * 1000
				);
			}
		}

		//set the var to a new var
		$variables = array($result);

		//EDT Benchmark
		ee()->gmaps->benchmark('calculate_distance', false);

		//return the gmaps
		return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);
	}


	// ----------------------------------------------------------------------------------

	/**
	 * the Actions
	 *
	 * @return unknown_type
	 */
	public function gmaps_act()
	{
		//needed in some cases
		header('Access-Control-Allow-Origin: *');

		// Load Library
		if (class_exists('Gmaps_ACT') != TRUE) include 'act.gmaps.php';

		$ACT = new Gmaps_ACT();

		$ACT->init();

		exit;
	}

	// ----------------------------------------------------------------------------------
 
	// Output JS tags
	public function output_js()
	{
		return $this->_minify_html_output(gmaps_helper::get_ee_cache('init_js', $this->return_data)).$this->_minify_html_output('<script>'.gmaps_helper::get_ee_cache('output_js').'</script>');
	}

	// ----------------------------------------------------------------------------------
	// Prvate functions
	// ----------------------------------------------------------------------------------
	
	//add a library
	private function _add_library($library_name, $check)
	{
		if($check == "true")
		{
			$this->libraries[] = $library_name;
		}
	}

	// ----------------------------------------------------------------------------------

	//add call timer
	private function _add_call_timer()
	{
		//caller ID
       /* if(!isset(ee()->session->cache[GMAPS_MAP]['caller']))
        {
            ee()->session->cache[GMAPS_MAP]['caller'] = 1;
        }
        else
        {
        	ee()->session->cache[GMAPS_MAP]['caller'] = ee()->session->cache[GMAPS_MAP]['caller'] + 1;
        }

        //prefix
        $prefix = ee()->gmaps->get_from_tagdata('cache_prefix', '');	
        $suffix = ee()->gmaps->get_from_tagdata('cache_suffix', '');	
		
		//set caller ID
        $this->caller_id = $prefix.ee()->session->cache[GMAPS_MAP]['caller'].$suffix;	
	
        */
        ee()->gmaps->set_cache(GMAPS_MAP.'_caller', (ee()->gmaps->get_cache(GMAPS_MAP.'_caller') + 1));

		//prefix
        $prefix = ee()->gmaps->get_from_tagdata('cache_prefix', '');	
        $suffix = ee()->gmaps->get_from_tagdata('cache_suffix', '');	

		//set caller ID
        $this->caller_id = $prefix.ee()->gmaps->get_cache(GMAPS_MAP.'_caller').$suffix;	

		//set the time of calling inthe session
		/*if(ee()->session->userdata('gmaps_call_times') == '')
		{
			ee()->session->userdata['gmaps_call_times'] = 1;
		}
		else
		{
			ee()->session->userdata['gmaps_call_times'] = ee()->session->userdata('gmaps_call_times') + 1;
		}*/

		//$this->caller_id = ee()->session->userdata('gmaps_call_times');	
		
		//and if the add_base_files="yes" than set this to 1 to avoid problems when the first is not loaded
		/*if($this->add_base_files)
		{
			$this->caller_id = 1;
		}
		else
		{
			$this->caller_id = ee()->session->userdata('gmaps_call_times');	
		}	*/
	}

	// ----------------------------------------------------------------------------------

	//serach array with regex
	private function preg_array_key_exists($pattern, $array) 
	{
		if(!is_array($array))
		{
			return false;
		}
		$keys = array_keys($array);    
		return preg_grep($pattern,$keys);
	}

	// ----------------------------------------------------------------------------------

	/**
	 * is_assoc
	 * @param  [type]  $array 
	 * @return boolean        
	 */
	function is_assoc($array) { 
        foreach ( array_keys ( $array ) as $k => $v ) 
        { 
            if ($k !== $v) 
                return true; 
        } 
        return false; 
    } 

    // ----------------------------------------------------------------------------------

    /**
     * convert_array_to_js
     * @param  [type] $data
     * @return [type]       
     */
    function convert_array_to_js($data) 
    { 
        if (is_null($data)) return 'null'; 
        if (is_string($data)) return '"' . $data . '"'; 
        if (self::is_assoc($data)) 
        { 
            $a=array(); 
            foreach ($data as $key => $val ) 
                $a[]='"' . $key . '" :' .self::convert_array_to_js($val); 
            return "{" . implode ( ', ', $a ) . "}"; 
        } 
        if (is_array($data)) 
        { 
            $a=array(); 
            foreach ($data as $val ) 
                $a[]=self::convert_array_to_js($val); 
            return "[" . implode ( ', ', $a ) . "]"; 
        } 
        return $data; 
    } 

    // ----------------------------------------------------------------------------------

    //set cluster settings
    private function _set_cluster_style()
    {
    	$_fields = $this->preg_array_key_exists('/marker:cluster_style:/',ee()->TMPL->tagparams);
    	$fields = array();
		$field_options = array();
		if(!empty($_fields))
		{
			// Create a tussen array which can be threated for the js converting
			foreach($_fields as $val)
			{
				$_val = explode(':', $val);

				$param = ee()->gmaps->get_from_tagdata($val, '');

				$fields[$_val[1]][$_val[2]] = $param;
				
			}

		    $i = 0;
			foreach($fields as $key=>$val)
			{
				$field_options[$i] = array();

				//size
				if(isset($val['size']))
				{
					$size = explode(',', $val['size']);
					$width = isset($size[0]) ? $size[0] : '';
					$height = isset($size[1]) ? $size[1] : '';
				}
				else
				{
					$width = '';
					$height = '';
				}

				$field_options[$i] = array(
						'url' 			=> isset($val['url']) ? $val['url'] : '',
						'width' 		=> $width,
						'height' 		=> $height,
						'textColor' 	=> isset($val['color']) ? $val['color'] : '',
						'anchor'		=> isset($val['anchor']) ? '['.$val['anchor'].']' : '[]',
						'textSize' 		=> isset($val['text_size']) ? $val['text_size'] : '',
				);

				//remove empty values
				//$field_options[$i] = gmaps_helper::remove_empty_values($field_options[$i]);

				$i++;	
			}

			if(!empty($field_options))
			{
				$final = $this->convert_array_to_js($field_options);

				//create the indivudual objects, the array converter can`t create it.
				$final = str_replace("'url'", "url", $final);
				$final = str_replace("'textColor'", "textColor", $final);
				$final = str_replace("'width'", "width", $final);
				$final = str_replace("'height'", "height", $final);
				$final = str_replace("'anchor' :'[", "anchor :[", $final);
				$final = str_replace("]', 'textSize'", "], textSize", $final);

				return $final;
			}						
		}

		return '[]';
    }

    // ----------------------------------------------------------------------------------

    //set the styled map
    private function _set_styled_map()
    {
		//snazzy?
		$snazzy = ee()->gmaps->get_from_tagdata('map_style:snazzymaps', '');
		if($snazzy != '')
		{
			return $snazzy;
		}

		//default styling
		$_fields = $this->preg_array_key_exists('/map_style:/',ee()->TMPL->tagparams);
		$fields = array();
		$field_options = array();

		//any fields there?
		if(!empty($_fields))
		{
			// Create a tussen array which can be threated for the js converting
			foreach($_fields as $val)
			{
				$_val = explode(':', $val);
				
				//explode on | by multiple values
				$param = explode('|', ee()->gmaps->get_from_tagdata($val, ''));

				//multiple values?
				if(count($param) > 1)
				{
					$tmp_val = array();
					foreach($param as $v)
					{
						$tmp_val[] = $v;
					}

					$fields[$_val[1]][$_val[2]] = $tmp_val;
					
				}

				//single value
				else
				{
					$fields[$_val[1]][$_val[2]] = $param[0];
				}				
			}

		    $i = 0;
			foreach($fields as $key=>$val)
			{
				$field_options[$i] = array();

				if($key != 'default')
				{
					$field_options[$i]['featureType'] = $key;
					$field_options[$i]['elementType'] = isset($val['element_type']) ? $val['element_type'] : '' ;	
				}
				else
				{
					$field_options[$i]['featureType'] = 'all';
				}

				$field_options[$i]['stylers'] = array(array(
						'hue' 				=> isset($val['hue']) ? $val['hue'] : '',
						'lightness' 		=> isset($val['lightness']) ? $val['lightness'] : '',
						'saturation' 		=> isset($val['saturation']) ? $val['saturation'] : '',
						'inverseLightness' 	=> isset($val['inverse_lightness']) ? $val['inverse_lightness'] : '',
						'visibility'		=> isset($val['visibility']) ? $val['visibility'] == "no" ? 'off' : $val['visibility'] : '',
						'color' 			=> isset($val['color']) ? $val['color'] : '',
						'width'			 	=> isset($val['width']) ? $val['width'] : ''
				));

				//remove empty values
				$field_options[$i] = gmaps_helper::remove_empty_values($field_options[$i]);

				//if the stylers are empty return empty array
				if(empty($field_options[$i]['stylers']))
				{
					unset($field_options[$i]);
				}

				$i++;	
			}

			if(!empty($field_options))
			{
				$final = $this->convert_array_to_js($field_options);

				//create the indivudual objects, the array converter can`t create it.
				$final = str_replace(", 'hue'", "}, {'hue'", $final);
				$final = str_replace(", 'lightness'", "}, {'lightness'", $final);
				$final = str_replace(", 'saturation'", "}, {'saturation'", $final);
				$final = str_replace(", 'inverseLightness'", "}, {'inverseLightness'", $final);
				$final = str_replace(", 'visibility'", "}, {'visibility'", $final);
				$final = str_replace(", 'color'", "}, {'color'", $final);
				$final = str_replace(", 'width'", "}, {'width'", $final);

				return $final;
			}						
		}

		return '{}';
    }

    // ----------------------------------------------------------------------------------

	/**
	 * Create the marker array
	 *
	 * @return void
	 */
	private function _create_markers_array($type = '', $twitter_marker_html = '')
	{

		$return = 'marker : {}';

		switch($type)
		{
			//geocoding
			case 'geocoding' :
			case 'route':
				//get the marker html from the marker_data array, when the user used the extract_data_from_tagpair
				if(isset($this->marker_data['marker_html']) && $this->marker_data['marker_html'] != '')
				{
					$marker_html =  gmaps_helper::build_js_array($this->marker_data['marker_html'], false, false, false);
				}
				else
				{
					$marker_html = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('marker:html', ''));
				}

				//the default html for every marker
				$marker_html_default = ee()->gmaps->get_from_tagdata('marker:html_default', '');

				//get the marker html from the marker_data array, when the user used the extract_data_from_tagpair
				if(isset($this->marker_data['marker_custom_html']) && $this->marker_data['marker_custom_html'] != '')
				{
					$marker_custom_html =  gmaps_helper::build_js_array($this->marker_data['marker_custom_html'], false, false, false);
				}
				else
				{
					$marker_custom_html = gmaps_helper::build_js_array(ee()->gmaps->get_from_tagdata('marker:custom_html', ''));
				}		

				$marker_open_by_default = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('marker:open_by_default', 'yes'), true);
				$marker_custom_vertical_align = ee()->gmaps->get_from_tagdata('marker:custom_vertical_align', 'top');
				$marker_custom_horizontal_align = ee()->gmaps->get_from_tagdata('marker:custom_horizontal_align', 'center');
				$marker_custom_show_marker = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('marker:custom_show_marker', 'no'), true);
				
				//cluster
				$marker_show_cluster = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('marker:show_cluster', 'no'), true);
				$marker_cluster_grid_size = ee()->gmaps->get_from_tagdata('marker:cluster:grid_size', '60');
				$marker_cluster_style = $this->_set_cluster_style();

				//twitter html
				if($twitter_marker_html != '')
				{
					$marker_html = $twitter_marker_html;
				}

				//infobox
				$marker_infobox_content = ee()->gmaps->get_from_tagdata('marker:infobox:content', '');
				$marker_infobox_box_style = ee()->gmaps->get_from_tagdata('marker:infobox:box_style', '{}');
				$marker_infobox_close_box_margin = ee()->gmaps->get_from_tagdata('marker:infobox:close_box_margin', '2px');
				$marker_infobox_close_box_url = ee()->gmaps->get_from_tagdata('marker:infobox:close_box_url', 'http://www.google.com/intl/en_us/mapfiles/close.gif');
				$marker_infobox_box_class = ee()->gmaps->get_from_tagdata('marker:infobox:box_class', '');
				$marker_infobox_max_width = ee()->gmaps->get_from_tagdata('marker:infobox:max_width', '');
				$marker_infobox_z_index = ee()->gmaps->get_from_tagdata('marker:infobox:z_index', '');
				$marker_infobox_pixel_offset_left = ee()->gmaps->get_from_tagdata('marker:infobox:pixel_offset_left', '-140');
				$marker_infobox_pixel_offset_top = ee()->gmaps->get_from_tagdata('marker:infobox:pixel_offset_top', '0');

				$return = '
					marker : {
						show : '.$this->marker.',
						show_title : '.$this->marker_show_title.',
						title : '.$this->marker_title.',
						label : '.$this->marker_label.',
						animation : '.$this->marker_animation.',
						icon : '.$this->default_js_icon_array['icon_geocoding'].',
						icon_default : '.$this->default_js_icon_array['icon_default'].',
						icon_shadow : '.$this->default_js_icon_array['shadow_geocoding'].',
						icon_default_shadow : '.$this->default_js_icon_array['shadow_default'].',
						icon_shape : '.$this->default_js_icon_array['shape_geocoding'].',
						icon_shape_default : '.$this->default_js_icon_array['shape_default'].', 
						html : '.$marker_html.',
						html_default : "'.$marker_html_default.'",
						custom_html : '.$marker_custom_html.',
						custom_html_show_marker : '.$marker_custom_show_marker.',
						custom_html_vertical_align : "'.$marker_custom_vertical_align.'",
						custom_html_horizontal_align : "'.$marker_custom_horizontal_align.'",
						infobox: {
							content: "'.str_replace('"', '\\"', $marker_infobox_content).'",
							box_style: '.$marker_infobox_box_style.',
							close_box_margin: "'.$marker_infobox_close_box_margin.'",
							close_box_url: "'.$marker_infobox_close_box_url.'",
							box_class: "'.$marker_infobox_box_class.'",
							max_width: "'.$marker_infobox_max_width.'",
							z_index: "'.$marker_infobox_z_index.'",
							pixel_offset: {
								width: "'.$marker_infobox_pixel_offset_left.'",
								height: "'.$marker_infobox_pixel_offset_top.'"
							}
						},
						open_by_default : '.$marker_open_by_default.',
						show_cluster : '.$marker_show_cluster.',
						cluster_style : '.$marker_cluster_style.',
						cluster_grid_size : '.$marker_cluster_grid_size.'
					}
				';
			break;

			//geolocation
			case 'geolocation' :
			case 'circle' :
				$marker_html = ee()->gmaps->get_from_tagdata('marker:html', '');
				$marker_custom_html = ee()->gmaps->get_from_tagdata('marker:custom_html', '');
				$marker_custom_vertical_align = ee()->gmaps->get_from_tagdata('marker:custom_vertical_align', 'top');
				$marker_custom_horizontal_align = ee()->gmaps->get_from_tagdata('marker:custom_horizontal_align', 'center');
				$marker_custom_show_marker = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('marker:custom_show_marker', 'no'), true);
				$marker_open_by_default = gmaps_helper::check_yes(ee()->gmaps->get_from_tagdata('marker:open_by_default', 'yes'), true);

				//infobox
				$marker_infobox_content = ee()->gmaps->get_from_tagdata('marker:infobox:content', '');
				$marker_infobox_box_style = ee()->gmaps->get_from_tagdata('marker:infobox:box_style', '{}');
				$marker_infobox_close_box_margin = ee()->gmaps->get_from_tagdata('marker:infobox:close_box_margin', '2px');
				$marker_infobox_close_box_url = ee()->gmaps->get_from_tagdata('marker:infobox:close_box_url', 'http://www.google.com/intl/en_us/mapfiles/close.gif');
				$marker_infobox_box_class = ee()->gmaps->get_from_tagdata('marker:infobox:box_class', '');
				$marker_infobox_max_width = ee()->gmaps->get_from_tagdata('marker:infobox:max_width', '');
				$marker_infobox_z_index = ee()->gmaps->get_from_tagdata('marker:infobox:z_index', '');
				$marker_infobox_pixel_offset_left = ee()->gmaps->get_from_tagdata('marker:infobox:pixel_offset_left', '-140');
				$marker_infobox_pixel_offset_top = ee()->gmaps->get_from_tagdata('marker:infobox:pixel_offset_top', '0');
				
				$return = '
					marker : {
						show : '.$this->marker.',
						show_title : '.$this->marker_show_title.',
						title : '.$this->marker_title.',
						label : '.$this->marker_label.',
						animation : '.$this->marker_animation.',
						icon : '.$this->default_js_icon_array['icon'].',
						icon_shadow : '.$this->default_js_icon_array['shadow'].',
						icon_shape : '.$this->default_js_icon_array['shape'].',
						html : "'.$marker_html.'",
						html_default : "'.$marker_html_default.'",
						custom_html : "'.$marker_custom_html.'",
						custom_html_show_marker : '.$marker_custom_show_marker.',
						custom_html_vertical_align : "'.$marker_custom_vertical_align.'",
						custom_html_horizontal_align : "'.$marker_custom_horizontal_align.'",
						infobox: {
							content: "'.str_replace('"', '\\"', $marker_infobox_content).'",
							box_style: '.$marker_infobox_box_style.',
							close_box_margin: "'.$marker_infobox_close_box_margin.'",
							close_box_url: "'.$marker_infobox_close_box_url.'",
							box_class: "'.$marker_infobox_box_class.'",
							max_width: "'.$marker_infobox_max_width.'",
							z_index: "'.$marker_infobox_z_index.'",
							pixel_offset: {
								width: "'.$marker_infobox_pixel_offset_left.'",
								height: "'.$marker_infobox_pixel_offset_top.'"
							}
						},
						open_by_default : '.$marker_open_by_default.'
					}
				';
			break;

			//places
			case 'places' :
				$return = '
					marker : {
						show : '.$this->marker.',
						animation : '.$this->marker_animation.',
						icon : '.$this->default_js_icon_array['icon'].',
						icon_shadow : '.$this->default_js_icon_array['shadow'].',
						icon_shape : '.$this->default_js_icon_array['shape'].'	
					}
				';
			break;

			//default
			default:			
				$return = '
					marker : {
						show : '.$this->marker.',
						show_title : '.$this->marker_show_title.',
						title : '.$this->marker_title.',
						animation : '.$this->marker_animation.',
						icon : '.$this->default_js_icon_array['icon'].',
						icon_shadow : '.$this->default_js_icon_array['shadow'].',
						icon_shape : '.$this->default_js_icon_array['shape'].'
					}
				';
			break;


		}

		return $return;
	}

	// ----------------------------------------------------------------------------------

	/**
	 * Extract markers data from the marker collection tag pair
	 * 
	 * {marker:collection}{address="new york"}{marker:html="this is [location]"} | and more ...{/marker:collection}
	 *
	 * see http://reinos.nl/add-ons/gmaps/docs/marker-collection
	 * 
	 * also do some cleaning with wrong tags like {not_an_var}
	 *
	 * @return void
	 */
	private function extract_marker_collection_data()
	{
		//get the data
		$data = ee()->gmaps->get_from_tagdata('marker:collection', '');

		//create an array
		$_data = explode('|', $data);
		
		$marker_html = array();
		$marker_custom_html = array();
		$address = array();
		$latlng = array();
		
		if(!empty($_data))
		{
			foreach($_data as $val)
			{			
				//get the address
				if (preg_match("/".LD."address=(.*?)".RD."/s", $val, $match))
				{
					$address[] = trim(str_replace(array("'", '"'), "", $match[1]));
				}
				
				//get the latlng
				if (preg_match("/".LD."latlng=(.*?)".RD."/s", $val, $match))
				{
					$latlng[] = trim(str_replace(array("'", '"'), "", $match[1]));
				}
				
				//get the marker:html
				if (preg_match("/".LD."marker:html=(.*?)".RD."/s", $val, $match))
				{
					$marker_html[] = trim(str_replace(array("'", '"'), "", $match[1]));
				}

				//get the marker:custom_html
				if (preg_match("/".LD."marker:custom_html=(.*?)".RD."/s", $val, $match))
				{
					$marker_custom_html[] = trim(str_replace(array("'", '"'), "", $match[1]));
				}
			}	
		}

		//set the meta data
		if(!empty($marker_html))
		{
			$this->marker_data['marker_html'] = implode('|', $marker_html);
		}
		if(!empty($marker_custom_html))
		{
			$this->marker_data['marker_custom_html'] = implode('|', $marker_custom_html);
		}

		//any latlng or address result
		if(!empty($latlng) || !empty($address))
		{
			return array(
				'latlng' => implode('|',$latlng),
				'address' => implode('|',$address),
			);
		}
				
		//return raw data
		return $data;
	}
	
	// ----------------------------------------------------------------------------------

	//compress js
	private function _compress_js($files)
	{
		//load minify class
		require_once 'libraries/Minifier.php';

		//load helper
		ee()->load->helper('file');

		//get the files_info from the settings table
		$files_info = ee()->gmaps_settings->item('files_info');

		//var that holds if we need to minify the file again
		$minify = false;

		//loop over the files and check if they are changed
		foreach($files as $file)
		{
			//get the file info
			$info = get_file_info(ee()->gmaps_settings->get_setting('theme_dir') . $file);

			//check the file date with the one from the database
			if(!isset($files_info[$info['name']]['date']) || $info['date'] != $files_info[$info['name']]['date'])
			{
				$minify = true;
				break;
			}
		}

		//we need to minify
		if($minify)
		{
			//set the file info
			$new_files_info = array();
			foreach($files as $file)
			{
				$info = get_file_info(ee()->gmaps_settings->get_setting('theme_dir') . $file);
				$new_files_info[$info['name']] = $info;
			}
			ee()->gmaps_settings->save_setting('files_info', $new_files_info);


			//get the file path
			$out = ee()->gmaps_settings->get_setting('theme_dir') . 'js/gmaps.min.js';

			$script = '';
			foreach($files as $file)
			{
				$script .= Minifier::minify(file_get_contents(ee()->gmaps_settings->get_setting('theme_url') . $file))."\n";
			}

			file_put_contents($out, $script);	
		}
	}

	// ----------------------------------------------------------------------------------

	//minify the output in the source of the browser
	private function _minify_html_output($data, $need_to_parse = false)
	{
		//license check, if valid we can go futher
		if(ee()->gmaps->license_check() || (!ee()->gmaps->license_check() && $need_to_parse))
		{
			if(!ee()->gmaps_settings->item('dev_mode'))
			{
				return preg_replace('!\s+!smi', ' ', $data);
			}
			
			return $data;
		}	
	}

	// ----------------------------------------------------------------------------------

	//catch the queue code and attach it
	public function _output_js($js_output = '')
	{
		//check if we need to catch the js output?
		if($this->catch_output_js)
		{
			$js_output .= gmaps_helper::get_ee_cache('output_js');
			gmaps_helper::set_ee_cache('output_js', $js_output, true);
			return '';
		}
		else
		{
			return '<script type="text/javascript">'.$js_output.'</script>';
		}
	}

	// ----------------------------------------------------------------------------------


	/**
	 * Calculate distance via Google Maps
	 *
	 * @param $start
	 * @param $finish
	 * @param bool $raw
	 * @return array
	 * @throws Exception
     */
	private function get_driving_information($start, $finish, $raw = false)
	{
		if(strcmp($start, $finish) == 0)
		{
			$time = 0;
			if($raw)
			{
				$time .= ' seconds';
			}

			return array('distance' => 0, 'time' => $time);
		}

		$start  = urlencode($start);
		$finish = urlencode($finish);

		$distance   = 'unknown';
		$time		= 'unknown';

		$url = 'http://maps.googleapis.com/maps/api/directions/xml?origin='.$start.'&destination='.$finish.'&sensor=false';
		if($data = file_get_contents($url))
		{
			$xml = new SimpleXMLElement($data);

			if(isset($xml->route->leg->duration->value) AND (int)$xml->route->leg->duration->value > 0)
			{
				if($raw)
				{
					$distance = (string)$xml->route->leg->distance->text;
					$time	  = (string)$xml->route->leg->duration->text;
				}
				else
				{
					$distance = (int)$xml->route->leg->distance->value / 1000 / 1.609344;
					$time	  = (int)$xml->route->leg->duration->value;
				}
			}
			else
			{
				return false;
				//throw new Exception('Could not find that route');
			}

			return array('distance' => $distance, 'time' => $time);
		}
		else
		{
			return false;
			//throw new Exception('Could not resolve URL');
		}
	}

	// ----------------------------------------------------------------------------------


	/**
	 * Calculates the great-circle distance between two points, with
	 * the Haversine formula.
	 * @param float $latitudeFrom Latitude of start point in [deg decimal]
	 * @param float $longitudeFrom Longitude of start point in [deg decimal]
	 * @param float $latitudeTo Latitude of target point in [deg decimal]
	 * @param float $longitudeTo Longitude of target point in [deg decimal]
	 * @param float $earthRadius Mean earth radius in [m]
	 * @return float Distance between points in [m] (same as earthRadius)
	 */
	function haversine_great_circle_distance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
	{
		// convert from degrees to radians
		$latFrom = deg2rad($latitudeFrom);
		$lonFrom = deg2rad($longitudeFrom);
		$latTo = deg2rad($latitudeTo);
		$lonTo = deg2rad($longitudeTo);

		$latDelta = $latTo - $latFrom;
		$lonDelta = $lonTo - $lonFrom;

		$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
				cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
		return $angle * $earthRadius;
	}
}

/* End of file mod.gmaps.php */
/* Location: /system/expressionengine/third_party/gmaps/mod.gmaps.php */