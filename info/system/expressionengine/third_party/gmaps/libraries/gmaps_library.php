<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  GMAPS lib
 *
 * @package		Gmaps
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @link        http://reinos.nl/add-ons/gmaps
 * @copyright 	Copyright (c) 2012 Reinos.nl Internet Media
 */

require_once(PATH_THIRD.'gmaps/config.php');
require_once(PATH_THIRD.'gmaps/libraries/gmaps_helper.php');


//reset the session for the init cache
//this must be outside the class, because it must be rest at once
@chmod(session_save_path(), 0755);
if (session_id() == "") {session_start();}
unset($_SESSION[GMAPS_MAP.'_init']); // deze wel resetten per request, deze moet dan ook maar 1 keer aangeroepen worden
//unset($_SESSION[GMAPS_MAP.'_caller']); //niet resetten omdat dit dan per request, ook ajax, wordt gereset

class Gmaps_library
{
	private $default_settings;
	private $settings;
	public $EE;

	public $debug = array();
	public $act;

	private $adapter;
	private $geocoder = null;
	private $providers = array();

	//format
	public $address_format = '[streetName] [streetNumber], [city], [country]';

	//api keys
	public $google_maps_key;
	public $bing_maps_key;
	public $map_quest_key;
	public $tomtom_key;

	public $errors = array();

	public function __construct()
	{						
		//get the action id
		$this->act = $this->fetch_action_id('Gmaps', 'gmaps_act');

		//load the helper
		//ee()->load->helper(GMAPS_MAP.'_helper');
		
		//needed for stash
		$this->EE = get_instance();
		
		//load logger
		ee()->load->library('logger');

		//load the channel data
		ee()->load->driver('channel_data');

		//load model
		ee()->load->model(GMAPS_MAP.'_model');

		//load the config
		ee()->load->library(GMAPS_MAP.'_settings');
		
		//require the default settings
		require PATH_THIRD.GMAPS_MAP.'/settings.php';
	}

	// ----------------------------------------------------------------------
	// CUSTOM FUNCTIONS
	// ----------------------------------------------------------------------

	/**
	 * [_load_geocoder description]
	 * 
	 * @return [type] 
	 */
	private function _load_geocoder($group = 'geocoding')
	{
		//geocoder not loaded? Create the object
		if($this->geocoder == null)
		{
			//load the files
			require_once 'vendor/autoload.php';

			//Create instances
			if(ee()->gmaps_settings->item('data_transfer') == 'curl' && gmaps_helper::is_curl_loaded())
			{
				$this->adapter  = new \Ivory\HttpAdapter\CurlHttpAdapter();
				gmaps_helper::log('Using cURL to load the results from the geocoder', 3);
				//$this->debug[] = 'Use cURL to load the results from the geocoder';
			}
			else
			{
				$this->adapter  = new \Ivory\HttpAdapter\BuzzHttpAdapter();
				gmaps_helper::log('Using HTTP BUZZ to load the results from the geocoder', 3);
				//$this->debug[] = 'Use HTTP BUZZ to load the results from the geocoder';
			}
			$this->geocoder = new \Geocoder\ProviderAggregator();
			
			//empty provider array
			$this->providers = array();

			//which group
			if($group == 'geocoding')
			{
				//the geocoders
				$geocoding_providers = ee()->gmaps_settings->item('geocoding_providers');
				
				// Google Maps - Address-Based geocoding and reverse geocoding provider;
				$this->providers['google_maps'] = new \Geocoder\Provider\GoogleMaps($this->adapter, $this->google_maps_key);

				//Bing maps - Address-Based geocoding and reverse geocoding provider;
				if(in_array('bing_maps', $geocoding_providers) && isset($this->bing_maps_key) && $this->bing_maps_key) {
					$this->providers['bing_maps'] = new \Geocoder\Provider\bing_maps($this->adapter, $this->bing_maps_key);
				}

				// Openstreetmap - Address-Based geocoding and reverse geocoding provider;
				if(in_array('openstreetmap', $geocoding_providers))
				{
					$this->providers['openstreetmap'] = new \Geocoder\Provider\OpenStreetMap($this->adapter);
				}

				// MapQuest - Address-Based geocoding and reverse geocoding provider;
				if(in_array('mapquest', $geocoding_providers) && isset($this->map_quest_key) && $this->map_quest_key) {
					$this->providers['mapquest'] = new \Geocoder\Provider\MapQuest($this->adapter, $this->map_quest_key);
				}

				// Yandex - Address-Based geocoding and reverse geocoding provider;
				if(in_array('yandex', $geocoding_providers))
				{
					$this->providers['yandex'] = new \Geocoder\Provider\Yandex($this->adapter);
				}

				// TOMTOM - as Address-Based geocoding and reverse geocoding provider;
				if(in_array('tomtom', $geocoding_providers) && isset($this->tomtom_key) && $this->tomtom_key) {
					$this->providers['tomtom'] = new \Geocoder\Provider\TomTom($this->adapter, $this->tomtom_key);
				}

				// Nominatim - as Address-Based geocoding and reverse geocoding provider;
				if(in_array('nominatim', $geocoding_providers))
				{
					$this->providers['nominatim'] = new \Geocoder\Provider\Nominatim($this->adapter, 'http://nominatim.openstreetmap.org/');
				}

			}

			// Register IP-Based providers
			else if($group == 'ip')
			{
				//GeoIp provider is an extension of PHP.
				//Rarly used but we will support this
				//http://nl3.php.net/manual/en/book.geoip.php
				try 
				{
					$this->providers['GeoipProvider'] = new \Geocoder\Provider\Geoip($this->adapter);
				}
				catch (Exception $e) 
				{
					gmaps_helper::log('Unable to use the GeoIp Provider', 2);
					//$this->debug[] = 'Unable to use the GeoIp Provider';
				}

				//normal GeoIP providers
				$this->providers['FreeGeoIpProvider'] = new \Geocoder\Provider\FreeGeoIp($this->adapter);
				$this->providers['HostIpProvider'] = new \Geocoder\Provider\HostIp($this->adapter);
				
			}

			//register the providers
			$this->geocoder->registerProviders($this->providers);
		}
	}

	// ----------------------------------------------------------------------------------

	/**
	 * _geocode_latlng
	 * 
	 * @param  array  $addresses 
	 * @return [type]            
	 */
	public function geocode_latlng($latlng = array(), $return_type = 'array', $show = 'restricted')
	{
		//init the geococer includes
		$this->_load_geocoder('geocoding');

		//default vars
		$address = array();

		//query results
		foreach($latlng as $key => $ll)
		{
			//is latlng?
			if(!$this->_is_latlng($ll))
			{
				gmaps_helper::log('Not an latlng coordinates: '.$ll, 2);
				//$this->debug[] = 'Not an latlng coordinates: '.$ll;
				unset($latlng[$key]);
				continue;
			}

			$found_latlng = false;
			$ll = explode(',', $ll);

			//search the address in the db
			//ee()->db->select('lat,lng,date,geocoder');
			ee()->db->where('lat = '.$ll[0].' AND lng = '.$ll[1]);
			$query = ee()->db->get('gmaps_cache', 1, 0);
			
			//found in DB
			if($query->num_rows() > 0)
			{
				$result = $query->row();
				
				// When te result is coming from an other geocoder, update it with the google maps result
				// Or when we need to refresh the address due the cache time
				if(($result->geocoder != 'google_maps') || ((time() - $result->date) > $this->cache_time))
				{
					//delete the record
					ee()->db->delete('gmaps_cache', array('cache_id' => $result->cache_id));
					$found_address = false;

					//log
					gmaps_helper::log('refresh cache for latlng: '.$ll[0].', '.$ll[1], 3);
					//$this->debug[] = 'refresh cache for latlng: '.$ll[0].', '.$ll[1];	
				}
				else
				{
					//set latlng and address
					//$latlng[] = $result->lat.','.$result->lng;
					//prepare data
					if($show == 'all')
					{
						$address[] = $this->_prepare_all_address(unserialize($result->result_object));
					}
					else
					{
						$address[] = $this->_prepare_address(unserialize($result->result_object));
					}

					$found_latlng = true;

					//log
					gmaps_helper::log('Latlng found in cache: '.$ll[0].', '.$ll[1], 3);
					//$this->debug[] = 'Latlng found in cache: '.$ll[0].', '.$ll[1];		
				}					
			}

			//get the address from the geocoder		
			if(!$found_latlng)
			{
				$providers = $this->geocoder->getProviders();

				$i = 1;
				foreach($providers as $provider=>$v)
				{
					//use an provider
					$this->geocoder->using($provider);

					//get the latlng
					try
					{
						$result = $this->geocoder->reverse($ll[0], $ll[1]);
					}
					catch (Exception $e) 
					{
						gmaps_helper::log($e->getMessage(), 2);
						//$this->debug[] = $e->getMessage();
						return array('address'=>'','latlng'=>'', 'raw_latlng' => '', 'raw_address' => '');
					}

					//we got result
					if($result->count() > 0)
					{
						$result = $result->first();

						//add the result to the cache DB
						ee()->db->insert('gmaps_cache', array(
							'address' 		=> trim($this->_prepare_address($result)),
							'lat'			=> $result->getLatitude(),
							'lng'			=> $result->getLongitude(),
							'date'			=> time(),
							'geocoder'		=> $provider,
							'result_object'	=> serialize($result)
						));
						//also add the searches latlng to the cache with the address
						ee()->db->insert('gmaps_cache', array(
							'address' 		=> trim($this->_prepare_address($result)),
							'lat'			=> $ll[0],
							'lng'			=> $ll[1],
							'date'			=> time(),
							'geocoder'		=> $provider,
							'result_object'	=> serialize($result)
						));
						//$latlng[] = $result->getLatitude().','.$result->getLongitude();
						//$address[] = $this->_prepare_address($result);
						if($show == 'all')
						{
							$address[] = $this->_prepare_all_address($result);
						}
						else
						{
							$address[] = $this->_prepare_address($result);
						}

						//log
						gmaps_helper::log('Save this latlng coordinates to the cache: '.$ll[0].', '.$ll[1] .' ('.$provider.')', 3);
						//$this->debug[] = 'Save this latlng coordinates to the cache: '.$ll[0].', '.$ll[1] .' ('.$provider.')';	

						break;
					}

					//not found
					if($i == count($this->providers))
					{
						gmaps_helper::log('Latlng not found: '.$ll, 2);
						//$this->debug[] = 'Latlng not found: '.$ll;
					}
					$i++;
				}
			}	
		}

		$return = array(
			'latlng'		=> '',
			'address'		=> '',
			'raw_latlng' 	=> '', 
			'raw_address' 	=> ''
		);

		if($show == 'all') 
		{
			return $address;
		}
		else
		{
			$return['raw_latlng'] = $latlng;
			$return['raw_address'] = $address;
			$return['latlng'] = $return_type == 'array' ? (implode('|', $latlng)) : $latlng[0];
			$return['address'] = $return_type == 'array' ? (implode('|', $address)) : $address[0];
			return $return;
		}
	}

	// ----------------------------------------------------------------------------------

	/**
	 * _geocode_latlng
	 * 
	 * @param  array  $addresses 
	 * @return [type]            
	 */
	public function geocode_ip($ips = array())
	{
		//init the geococer includes
		$this->_load_geocoder('ip');

		//default vars
		$address = array();

		//query results
		foreach($ips as $key => $ip)
		{
			//CURRENT_IP?
			if(strtolower($ip) == 'current_ip')
			{
				$ip = $_SERVER['REMOTE_ADDR'];
			}

			//SERVER_IP
			if(strtolower($ip) == 'server_ip')
			{
				$ip = $_SERVER['SERVER_ADDR'];
			}

			//is is valid IP?
			if(!filter_var($ip, FILTER_VALIDATE_IP))
			{
				gmaps_helper::log('Not an valid IP: '.$ip, 2);
				//$this->debug[] = 'Not an valid IP: '.$ip;
				unset($ips[$key]);
				continue;
			}
			
			$providers = $this->geocoder->getProviders();

			$i = 1;
			foreach($providers as $provider=>$v)
			{
				//use an provider
				$this->geocoder->using($provider);

				//geocode the IP
				try
				{
					$result = $this->geocoder->geocode($ip);
				}
				catch (Exception $e) 
				{
					gmaps_helper::log($e->getMessage(), 2);
					//$this->debug[] = $e->getMessage();
					//$this->debug[] = 'Curl must be enable to use the Gmaps module';
					return array('address'=>'','latlng'=>'', 'raw_latlng' => '', 'raw_address' => '');
				}

				//we got result
				if($result->count() > 0)
				{
					$result = $result->first();

					$address[] = $this->_prepare_all_address($result);
					
					//log
					gmaps_helper::log('IP found: '.$ip.' ('.$provider.')', 3);
					//$this->debug[] = 'IP found: '.$ip.' ('.$provider.')';	

					break;
				}

				//not found
				if($i == count($this->providers))
				{
					gmaps_helper::log('IP not found: '.$ip, 2);
					//$this->debug[] = 'IP not found: '.$ip;
				}
				$i++;
				
			}	
		}

		//return result
		return $address;
	}

	// ----------------------------------------------------------------------------------
	
	/**
	 * _geocode_address
	 * 
	 * @param  array  $addresses 
	 * @return [type]            
	 */
	public function geocode_address($addresses = array(), $return_type = 'array', $show = 'restricted')
	{
		//$addresses = array_map("utf8_decode", $addresses );

		//init the geococer includes
		$this->_load_geocoder('geocoding');

		//default vars
		$latlng = array();
		$address_new = array();

		//query results
		foreach($addresses as $address )
		{
			//convert
			$address = $this->transliterate_string($address);

			$found_address = false;

			//clean address
			$address = preg_replace('/\`\~\!\@\#\$\%\^\&\*\(\)\_\+\=\{\[\}\}\\\|:\;\"\'\<\,\>\.\?\//si','---',$address);
			//$address = preg_replace ("/([^A-Za-z0-9\+_\-,\.\s]+)/", "", $address);

			//search the address in the db
			//ee()->db->select('lat,lng,date,geocoder');
			ee()->db->where('address', $address);
			$query = ee()->db->get('gmaps_cache', 1, 0);
			
			//found in DB
			if($query->num_rows() > 0)
			{
				$result = $query->row();
				
				// When te result is coming from an other geocoder, update it with the google maps result
				// Or when we need to refresh the address due the cache time
				if(($result->geocoder != 'google_maps') || ((time() - $result->date) > $this->cache_time))
				{
					//delete the record
					ee()->db->delete('gmaps_cache', array('cache_id' => $result->cache_id));
					$found_address = false;

					//log
					gmaps_helper::log('Refresh cache for address: '.$address, 3);
					//$this->debug[] = 'Refresh cache for address: '.$address;
				}	
				else
				{
					//set latlng and address
					$latlng[] = $result->lat.','.$result->lng;
;
					//prepare data
					if($show == 'all')
					{
						$address_new[] = $this->_prepare_all_address(unserialize($result->result_object));
					}
					else
					{
						$address_new[] = $this->_prepare_address(unserialize($result->result_object));
					}
					
					$found_address = true;	

					//log
					gmaps_helper::log('Address found in cache: '.$address, 3);
					//$this->debug[] = 'Address found in cache: '.$address;	
				}								
			}

			//get the address from the geocoder		
			if(!$found_address)
			{
				$providers = $this->geocoder->getProviders();

				$i = 1;
				foreach($providers as $provider=>$v)
				{
					//use an provider
					$this->geocoder->using($provider);

					//get the address
					try
					{
						$result = $this->geocoder->limit(1)->geocode($address);
					}
					catch (Exception $e) 
					{
						gmaps_helper::log($e->getMessage(), 2);
						//$this->debug[] = $e->getMessage();
						//return array('address'=>'','latlng'=>'', 'raw_latlng' => '', 'raw_address' => '');
						continue;
					}

					//we got result
					if($result->count() > 0)
					{
						$result = $result->first();

						//add the result to the cache DB
						ee()->db->insert('gmaps_cache', array(
							'address' 		=> trim($address),
							'lat'			=> $result->getLatitude(),
							'lng'			=> $result->getLongitude(),
							'date'			=> time(),
							'geocoder'		=> $provider,
							'result_object'	=> serialize($result)
						));
						$latlng[] = $result->getLatitude().','.$result->getLongitude();
						//$address_new[] = $this->_prepare_address($result);
						if($show == 'all')
						{
							$address_new[] = $this->_prepare_all_address($result);
						}
						else
						{
							$address_new[] = $this->_prepare_address($result);
						}

						//log
						gmaps_helper::log('Save this address to the cache: '.$address .' ('.$provider.')', 3);
						//$this->debug[] = 'Save this address to the cache: '.$address .' ('.$provider.')';

						break;
					}

					//not found
					if($i == count($this->providers))
					{
						gmaps_helper::log('Address not found: '.$address, 2);
						//$this->debug[] = 'Address not found: '.$address;
					}
					$i++;
				}
			}	
		}

		$return = array(
			'latlng'		=> '',
			'address'		=> '',
			'raw_latlng'	=> '',
			'raw_address'	=> ''
		);

		if($show == 'all') 
		{
			return $address_new;
		}
		else
		{
			$return['raw_latlng'] = $latlng;
			$return['raw_address'] = $address;
			$return['latlng'] = $return_type == 'array' ? (implode('|', $latlng)) : $latlng[0];
			$return['address'] = $return_type == 'array' ? (implode('|', $address_new)) : $address_new[0];
			return $return;
		}
	}

	// ----------------------------------------------------------------------------------
	
	/**
	 * _prepare_address
	 * @param  [type] $serialize_address_object 
	 * @return [type]                           
	 */
	public function _prepare_address($result_object, $serialize = true)
	{
		$address_placehoders = array('[streetName]','[streetNumber]','[city]','[country]', '[countryCode]', '[zipCode]');
		$address_data = array(
			$result_object->getStreetName(),
			$result_object->getStreetNumber(),
			$result_object->getLocality(),
			$result_object->getCountry(),
			$result_object->getCountryCode(),
			$result_object->getPostalCode()
		);
		$return = str_replace($address_placehoders, $address_data, $this->address_format);
		//remove trailing comma
		$return = preg_replace('/,/', '', $return,1);
		//remove empty values
		$return = gmaps_helper::remove_empty_array_values(explode(',', $return));

		if(count($return) < 2)
		{
			return array_shift($return);
		}
		else
		{
			return implode(',', $return);
		}		
	}

	// ----------------------------------------------------------------------------------
	
	/**
	 * _prepare_all_address
	 *
	 * Names are in EE format because we give this right back to the template parser
	 * 
	 * @param  [type] $serialize_address_object 
	 * @return [type]                           
	 */
	public function _prepare_all_address($result_object)
	{
		//default value for the bounds
		if($result_object->getBounds() != '')
		{
			$bounds = $result_object->getBounds()->toArray();
		}
		else
		{
			$bounds = array(
				'south'=>'',
				'west'=>'',
				'north'=>'',
				'east'=>'',
			);
		}
		
		//return values
		return array(
			'latitude' => $result_object->getLatitude(),
			'longitude'=> $result_object->getLongitude(),
			'bounds' => array($bounds),
			'street_name' => $result_object->getStreetName(),
			'street_number' => $result_object->getStreetNumber(),
			'city' => $result_object->getLocality(),
			'zipcode' => $result_object->getPostalCode(),
			'city_district' => $result_object->getSubLocality(),
			'country' => $result_object->getCountry(),
			'country_code' => $result_object->getCountryCode(),
			'timezone' => $result_object->getTimezone(),
		);
				
	}

	// ----------------------------------------------------------------------------------

	//is latlng
	private function _is_latlng($latlng)
	{
		if(preg_match('/([0-9.-]+).+?([0-9.-]+)/', $latlng, $matches))
		{
			return true;
		}
		return false;
	}

	// ----------------------------------------------------------------------------------

	/**
	 * get the twitter feeds
	 *
	 * @param none
	 * @return void
	 */
	public function get_twitter_feed_for_map($raw_latlng, $page = 1, $counter = 0)
	{	
		//twitter
		$twitter_search_about = ee()->TMPL->fetch_param('twitter_search:about', '');
		$twitter_search_hash = ee()->TMPL->fetch_param('twitter_search:hash', '');
		$twitter_search_to = ee()->TMPL->fetch_param('twitter_search:to', '');
		$twitter_search_from = ee()->TMPL->fetch_param('twitter_search:from', '');
		$twitter_search_contains = ee()->TMPL->fetch_param('twitter_search:contains', '');
		$twitter_radius = ee()->TMPL->fetch_param('twitter_radius', '25');
		$twitter_limit = ee()->TMPL->fetch_param('twitter_limit', '25');
		$twitter_format = ee()->TMPL->fetch_param('twitter_format', "<h2>@[user]</h2><b></b><img style='float:left;margin:0 5px 5px 0;' src='[image_url]' alt='[user]'/> <b>[post_date format='%D, %F %d, %Y - %g:%i:%s']:</b> [text]");
		//$twitter_create_links = ee()->TMPL->fetch_param('twitter_create_links') == 'no' ? false : true;

		//are we looking for tweets?
		if($twitter_search_about != '' || $twitter_search_hash != '' || $twitter_search_to != '' || $twitter_search_from != '' || $twitter_search_contains != '')
		{
			//vars			
			$twitter_latlng = array();
			$twitter_address = array();
			$twitter_text = array();
			$twitter_date = array();
			$raw_latlng_search = explode(',', $raw_latlng[0]);	

			//get twitter results
			ee()->twitter
			->geocode($raw_latlng_search[0], $raw_latlng_search[1], $twitter_radius, 'km')
			->include_entities()->rpp($twitter_limit)
			->page($page)
			->result_type('recent');

			//extend the query
			if($twitter_search_about != '')
			{
				ee()->twitter->about($twitter_search_about);
			}
			if($twitter_search_hash != '')
			{
				ee()->twitter->with($twitter_search_hash);
			}
			if($twitter_search_to != '')
			{
				ee()->twitter->to($twitter_search_to);
			}
			if($twitter_search_from != '')
			{
				ee()->twitter->from($twitter_search_from);
			}
			if($twitter_search_contains != '')
			{
				ee()->twitter->contains($twitter_search_contains);
			}

			//get the results
			$twitter_results = ee()->twitter->results();		

			//log url
			gmaps_helper::log('Fetch twitter url: '.ee()->twitter->url, 3);
			//$this->debug[] = 'Fetch twitter url: '.ee()->twitter->url;	
			
			//curl disabled?
			if($twitter_results == 'curl_not_loaded')
			{
				gmaps_helper::log('Curl must be enable to use the Twitter function', 3);
				//$this->debug[] = 'Curl must be enable to use the Twitter function';
				return false;
			}

			//no result
			if(empty($twitter_results) || $twitter_results == '')
			{
				gmaps_helper::log('No twitter result founded', 3);
				return 'no_result';
			}

			//loop over the results
			foreach($twitter_results as $val)
			{
				//is there any geo or location data
				if(!empty($val->geo) || !empty($val->location))
				{
					//set the text
					$_text = gmaps_helper::create_links_from_string(gmaps_helper::twitter_clean_text($val->text));
					//date
					$twitter_format_tmp = str_replace('[post_date]', strtotime($val->created_at), $twitter_format);
			   		if (strpos($twitter_format_tmp, '[post_date') !== FALSE AND preg_match_all("/\[post_date\s+format=([\"\'])([^\\1]*?)\\1\]/", $twitter_format_tmp, $matches))
			   		{	
						for ($j = 0; $j < count($matches['0']); $j++)
						{
							$twitter_format_tmp = preg_replace("/".preg_quote($matches['0'][$j], '/')."/", ee()->localize->decode_date($matches['2'][$j], strtotime($val->created_at)), $twitter_format_tmp, 1);				
						}
					}

					//real geocoding coordinates results
					if(!empty($val->geo))
					{
						//calculate the distance
						$distance = gmaps_helper::distance($raw_latlng[0], $val->geo->coordinates[0].','.$val->geo->coordinates[1]);
						
						if($distance['kilometers'] <= $twitter_radius && $distance['kilometers'] != 0)
						{
							//latlng
							$twitter_latlng[] = $val->geo->coordinates[0].','.$val->geo->coordinates[1];
							//format	
							$twitter_text[] = str_replace(array('[user]', '[text]', '[image_url]'), array($val->from_user, $_text, $val->profile_image_url), $twitter_format_tmp);

							//address
							if(isset($val->location))
							{
								$twitter_address[] = $val->location;
							}
							else 
							{
								$result_geocoding_twitter = $this->geocode_latlng(array($val->geo->coordinates[0].','.$val->geo->coordinates[1]));
								if(isset($result_geocoding_twitter['raw_address'][0]))
								{
									$twitter_address[] = $result_geocoding_twitter['raw_address'][0];
								}
							}
						}
					}

					//location results
					else if(!empty($val->location)) 
					{
						$result_geocoding_twitter = $this->geocode_address(array($val->location));
						if(isset($result_geocoding_twitter['raw_latlng'][0]))
						{
							//calculate the distance
							$distance = gmaps_helper::distance($raw_latlng[0], $result_geocoding_twitter['raw_latlng'][0]);
							
							if($distance['kilometers'] <= $twitter_radius)
							{
								//latlng
								$raw_latlng_twitter = explode(',', $result_geocoding_twitter['raw_latlng'][0]);	
								$twitter_latlng[] = $raw_latlng_twitter[0].','.$raw_latlng_twitter[1];
								$twitter_address[] = $val->location;
								//format	
								$twitter_text[] = str_replace(array('[user]', '[text]', '[image_url]'), array($val->from_user, $_text, $val->profile_image_url), $twitter_format_tmp);
							}	
						}	
					}
				}
			}

			//avoid double values #1
			$twitter_latlng = gmaps_helper::avoid_double_latlng($twitter_latlng, 0.5);

			$twitter_final_result = array(
				'latlng' => $twitter_latlng,
				'address' => $twitter_address,
				'marker_html' => $twitter_text
			);

			$counter = $counter + count($twitter_latlng);

			//did we got the limit?
			if($counter < $twitter_limit)
			{
				$page_result = $this->get_twitter_feed_for_map($raw_latlng, $page+1, $counter);
				if($page_result != 'no_result' && is_array($page_result))
				{
					$twitter_final_result = @array_merge_recursive($twitter_final_result, $page_result);
				}
			}

			if($page == 1)
			{
				//avoid double values #2
				$twitter_final_result['latlng'] = gmaps_helper::avoid_double_latlng($twitter_final_result['latlng'], 0.5);

				//build the js array
				$latlng = (implode('|', $twitter_final_result['latlng']));
				$address = (implode('|', $twitter_final_result['address']));
				$marker_html = gmaps_helper::build_js_array(implode('|', $twitter_final_result['marker_html']));

				//format the result
				return array(
					'latlng' => $latlng,
					'address' => $address,
					'marker_html' => $marker_html
				);
			}

			//return result
			return $twitter_final_result;
		}

		return array();
	}

	// ----------------------------------------------------------------------------------

	/**
	 * Get the keys which are presenting in an address="" or latlng="" param
	 *
	 * e.g. address="key:address|key:address"
	 *
	 * @param none
	 * @return void
	 */
	public function parse_param_keys($data = array())
	{
		$keys = array();
		$new_data = array();

		if(!empty($data))
		{
			foreach($data as $val)
			{
				$_val = explode(':', $val);

				//set the val as key when there is only one value
				if(isset($_val[0]) && isset($_val[1]))
				{
					$keys[] = $_val[0];
					$new_data[] = $_val[1]; 
				} 
				else
				{
					$keys[] = $val;
					$new_data[] = $val; 	
				}
			}
		}

		return array('keys' => $keys, 'data' => $new_data);
	}

	// ----------------------------------------------------------------------------------

	/**
	 * Parse only a string
	 *
	 * @param none
	 * @return void
	 */
	public function parse_channel_data($tag = '', $parse = true)
	{
		// do we need to parse, and are there any modules/tags to parse?
		if($parse && (strpos($tag, LD.'exp:') !== FALSE))
		{
			$old_tag = $tag;

			require_once APPPATH.'libraries/Template.php';
			$OLD_TMPL = isset(ee()->TMPL) ? ee()->TMPL : NULL;
			ee()->TMPL = new EE_Template();
			ee()->TMPL->parse($tag, true);
			//$tag = ee()->TMPL->parse_globals($tag);
			$tag = ee()->TMPL->remove_ee_comments($tag);
			ee()->TMPL = $OLD_TMPL;

			//whas the TMPL not yet set, delete this again
			if($OLD_TMPL === NULL)
			{
				unset(ee()->TMPL);
			}
			
		}

		//return the data
		return trim($tag);		
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Get the data from tagdat
	 *
	 * @param none
	 * @return void
	 */
	public function get_from_tagdata($field = 'field', $default_value = '')
	{
		//get the tag pair data
		//can be for example {address}{/address}
		if (preg_match_all("/".LD.$field.RD."(.*?)".LD."\/".$field.RD."/s", ee()->TMPL->tagdata, $tmp)!=0)
        {
          	if(isset($tmp[1][0]))
          	{
          		//trim to one line
         		$tmp[1][0] = gmaps_helper::trim_to_one_line($tmp[1][0]);
				
				//convert double quotes to single quotes
				$tmp[1][0] = str_replace('"', "'", $tmp[1][0]);

				//check for stash
				if (preg_match_all("/".LD."exp:stash:(.*?)".RD."(.*?)".LD."\/exp:stash:(.*?)".RD."/s", $tmp[1][0], $stash_match))
				{
					if ( ! class_exists('Stash'))
					{
						include_once PATH_THIRD . 'stash/mod.stash.php';
					}

					//parse the whole tag
					$stash_result = Stash::parse(array(), $stash_match[0][0]);

					//place the result in the template
					$tmp[1][0] = str_replace($stash_match[0][0], $stash_result, $tmp[1][0]);
				}
				

				//remove the tagdata
				ee()->TMPL->tagdata = str_replace($tmp[0][0], '', ee()->TMPL->tagdata);

				//go to the parser to parse any module tag data, if present
          		$parsed_data = $this->parse_channel_data($tmp[1][0]);

          		//remove from tagdata
          		ee()->TMPL->tagdata = str_replace($tmp[0][0], '', ee()->TMPL->tagdata);
				
          		
          		//return the data
          		return $parsed_data;
          	}
        }

        //get normal tagdata form params
        else
        {
        	return ee()->TMPL->fetch_param($field, $default_value);
        }

        return '';
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Explode and trim
	 *
	 * @param none
	 * @return void
	 */
	public function explode($value, $delimiter = '|')
	{
		if($value != '')
		{
			//explode to an array
			$value = explode($delimiter, $value);
			//trim every value with array_walk
			array_walk($value, create_function('&$val', '$val = trim($val);'));
			//remove empty values
			$value = array_filter($value);
		}

		return $value;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * EDT benchmark
	 * https://github.com/mithra62/ee_debug_toolbar/wiki/Benchmarks
	 *
	 * @param none
	 * @return void
	 */
	public function benchmark($method = '', $start = true)
	{
		if($method != '' && REQ != 'CP')
		{
			$prefix = 'gmaps_';
			$type = $start ? '_start' : '_end';
			ee()->benchmark->mark($prefix.$method.$type);
		}
	}

	// ----------------------------------------------------------------------

    /**
     * set_icon_options
     *
     * @access private
    */
    public function set_icon_options($url = '', $dir = '')
    {
		//get the icons
        $this->icons = get_dir_file_info($dir);

        $return = '<select name="marker_icon">';
        $return .= '<option value="">Default</option>';

        if(!empty($this->icons))
        {
            foreach($this->icons as $val)
            {
                $return .= '<option value="'.$url.$val['name'].'">'.$val['name'].'</option>';
            }
        }

        $return .= '</select>';

        return $return;
    }

    // ----------------------------------------------------------------------

    /**
     * set_icon_options
     *
     * @access private
    */
    public function parse_errors()
    {
    	$variables = array();
    	$errors = gmaps_helper::get_log();

    	if(!empty($errors))
    	{
    		foreach($errors as $error)
    		{
    			$variables[0]['errors'][]['error'] = $error[1];
    		}
    	}

    	//there is some tagdat like {errors}{error}{/errors}
    	if(preg_match_all("/".LD.'errors'.RD."/", ee()->TMPL->tagdata, $tmp_all_matches))
    	{
    		return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);
    	}		
    }   

	// ----------------------------------------------------------------------
	// PRIVATE FUNCTIONS
	// ----------------------------------------------------------------------
	
	// ----------------------------------------------------------------------
	// DEFAULT FUNCTIONS
	// ----------------------------------------------------------------------
	
	/**
	 * Simple license check.
	 *
	 * @access     private
	 * @return     bool
	 */
	public function license_check()
	{
		$is_valid = FALSE;

		$valid_patterns = array(
			'/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/' // devot-ee.com
		);

		foreach ($valid_patterns as $pattern)
		{
			if (preg_match($pattern, ee()->gmaps_settings->item('license_key')))
			{
				$is_valid = TRUE;
				break;
			}
		}

		return $is_valid;
	}

	 // --------------------------------------------------------------------

	/**
	 * Anonymously report EE & PHP versions used to improve the product.
	 */
	public function stats()
	{
		if (function_exists('curl_init'))
		{
			$data = http_build_query(array(
				// anonymous reference generated using one-way hash
				'site' => sha1(ee()->config->item('license_number')),
				'product' => 'store',
				'version' => STORE_VERSION,
				'ee' => APP_VER,
				'php' => PHP_VERSION,
			));
			ee()->load->library('curl');
			ee()->curl->simple_post("http://hello.exp-resso.com/v1", $data);
		}

		// report again in 28 days
		ee()->store_config->set_item('report_date', ee()->localize->now + 28*24*60*60);
		ee()->store_config->save();
		exit('OK');
	}

	// ----------------------------------------------------------------------

    /**
     * set_cache
     *
     * @access private
    */
    public function set_cache($name = '', $value = '')
    {
    	if (session_id() == "") 
		{
			session_start(); 
		}

		$_SESSION[$name] = $value;
    }

    // ----------------------------------------------------------------------

    /**
     * get_cache
     *
     * @access private
    */
    public function get_cache($name = '')
    {
    	// if no active session we start a new one
		if (session_id() == "") 
		{
			session_start(); 
		}
		
		if (isset($_SESSION[$name]))
		{
			return $_SESSION[$name];
		}
		
		else
		{
			return '';
		}
    }

    // ----------------------------------------------------------------------

    /**
     * delete_cache
     *
     * @access private
    */
    public function delete_cache($name = '')
    {
    	// if no active session we start a new one
		if (session_id() == "") 
		{
			session_start(); 
		}
		
		unset($_SESSION[$name]);
    }

    // ----------------------------------------------------------------------

    /**
     * mcp_meta_parser
     *
     * @access private
    */
	function mcp_meta_parser($type='', $file)
	{
		// -----------------------------------------
		// CSS
		// -----------------------------------------
		if ($type == 'css')
		{
			if ( isset(ee()->session->cache[UPLOADIFY_MAP]['CSS'][$file]) == FALSE )
			{
				ee()->cp->add_to_head('<link rel="stylesheet" href="' . ee()->uploadify_settings->get_setting('theme_url') . 'css/' . $file . '" type="text/css" media="print, projection, screen" />');
				ee()->session->cache[UPLOADIFY_MAP]['CSS'][$file] = TRUE;
			}
		}

		// -----------------------------------------
		// CSS Inline
		// -----------------------------------------
		if ($type == 'css_inline')
		{
			ee()->cp->add_to_foot('<style type="text/css">'.$file.'</style>');
			
		}

		// -----------------------------------------
		// Javascript
		// -----------------------------------------
		if ($type == 'js')
		{
			if ( isset(ee()->session->cache[UPLOADIFY_MAP]['JS'][$file]) == FALSE )
			{
				ee()->cp->add_to_foot('<script src="' . ee()->uploadify_settings->get_setting('theme_url') . 'js/' . $file . '" type="text/javascript"></script>');
				ee()->session->cache[UPLOADIFY_MAP]['JS'][$file] = TRUE;
			}
		}

		// -----------------------------------------
		// Javascript Inline
		// -----------------------------------------
		if ($type == 'js_inline')
		{
			ee()->cp->add_to_foot('<script type="text/javascript">'.$file.'</script>');
			
		}
	}

	// ----------------------------------------------------------------------
		
	/**
	 * Include Theme CSS
	 */
	public function include_theme_css($file)
	{
		ee()->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.ee()->gmaps_settings->get_setting('theme_url').'css/'.$file.'" />');
	}

	// ----------------------------------------------------------------------

	/**
	 * Include Theme JS
	 */
	public function include_theme_js($file)
	{
		ee()->cp->add_to_foot('<script type="text/javascript" src="'.ee()->gmaps_settings->get_setting('theme_url').'javascript/'.$file.'"></script>');
	}

	// --------------------------------------------------------------------

	/**
	 * Insert CSS
	 */
	public function insert_css($css)
	{
		ee()->cp->add_to_head('<style type="text/css">'.$css.'</style>');
	}

	// ----------------------------------------------------------------------

	/**
	 * Insert JS
	 */
	public function insert_js($js)
	{
		ee()->cp->add_to_foot('<script type="text/javascript">'.$js.'</script>');
	}
	
	// ----------------------------------------------------------------------
		
	/**
	 * 	Fetch Action IDs
	 *
	 * 	@access public
	 *	@param string
	 * 	@param string
	 *	@return mixed
	 */
	public function fetch_action_id($class = '', $method)
	{
		ee()->db->select('action_id');
		ee()->db->where('class', $class);
		ee()->db->where('method', $method);
		$query = ee()->db->get('actions');
		
		if ($query->num_rows() == 0)
		{
			return FALSE;
		}
		
		return $query->row('action_id');
	}	

	// ----------------------------------------------------------------------
		
	/**
	 * 	Convert special to normal
	 *
	 * 	@access public
	 *	@param string
	 * 	@param string
	 *	@return mixed
	 */
	public function transliterate_string($txt) 
	{
	    $transliterationTable = array('á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'å' => 'a', 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A', 'ä' => 'ae', 'Ä' => 'AE', 'æ' => 'ae', 'Æ' => 'AE', 'ḃ' => 'b', 'Ḃ' => 'B', 'ć' => 'c', 'Ć' => 'C', 'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c', 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C', 'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D', 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh', 'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e', 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E', 'ę' => 'e', 'Ę' => 'E', 'ē' => 'e', 'Ē' => 'E', 'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F', 'ğ' => 'g', 'Ğ' => 'G', 'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G', 'ĥ' => 'h', 'Ĥ' => 'H', 'ħ' => 'h', 'Ħ' => 'H', 'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ï' => 'i', 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I', 'ĵ' => 'j', 'Ĵ' => 'J', 'ķ' => 'k', 'Ķ' => 'K', 'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l', 'Ł' => 'L', 'ṁ' => 'm', 'Ṁ' => 'M', 'ń' => 'n', 'Ń' => 'N', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N', 'ņ' => 'n', 'Ņ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O', 'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O', 'ö' => 'oe', 'Ö' => 'OE', 'ṗ' => 'p', 'Ṗ' => 'P', 'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r', 'Ŗ' => 'R', 'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S', 'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS', 'ť' => 't', 'Ť' => 'T', 'ṫ' => 't', 'Ṫ' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T', 'ú' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u', 'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue', 'Ü' => 'UE', 'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w', 'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W', 'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y', 'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y', 'ź' => 'z', 'Ź' => 'Z', 'ž' => 'z', 'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z', 'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u', 'а' => 'a', 'А' => 'a', 'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v', 'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd', 'е' => 'e', 'Е' => 'e', 'ё' => 'e', 'Ё' => 'e', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z', 'и' => 'i', 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k', 'л' => 'l', 'Л' => 'l', 'м' => 'm', 'М' => 'm', 'н' => 'n', 'Н' => 'n', 'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p', 'р' => 'r', 'Р' => 'r', 'с' => 's', 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u', 'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'c', 'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch', 'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y', 'ь' => '', 'Ь' => '', 'э' => 'e', 'Э' => 'e', 'ю' => 'ju', 'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja');
	    $txt = str_replace(array_keys($transliterationTable), array_values($transliterationTable), $txt);
	    return $txt;
	}
		
	// ----------------------------------------------------------------------
	
} // END CLASS

/* End of file gmaps_library.php  */
/* Location: ./system/expressionengine/third_party/gmaps/libraries/gmaps_library.php */