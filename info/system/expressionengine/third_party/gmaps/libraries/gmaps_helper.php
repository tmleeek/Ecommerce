<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Default  helper
 *
 * @package		Module name
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl/add-ons/add-ons
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */

class Gmaps_helper
{
    /**
     * Logging levels
     */
    private static $_levels = array(
        1 => 'ERROR',
        2 => 'DEBUG',
        3 => 'INFO'
    );


    /**
     * History of logging for EE Debug Toolbar
     */
    private static $_log = array();


    /**
     * Flag for whether to 'flash' our toolbar tab
     */
    private static $_log_has_error = FALSE;


	/**
	 * Remove the double slashes
	 */
	public static function remove_double_slashes($str)
    {
        return preg_replace("#(^|[^:])//+#", "\\1/", $str);
    }

	// ----------------------------------------------------------------------

	/**
	 * Check if Submitted String is a Yes value
	 *
	 * If the value is 'y', 'yes', 'true', or 'on', then returns TRUE, otherwise FALSE
	 *
	 */
	public static function check_yes($which, $string = false)
	{
	    if (is_string($which))
	    {
	        $which = strtolower(trim($which));
	    }

	    $result = in_array($which, array('yes', 'y', 'true', 'on'), TRUE);

	    if($string)
	    {
	       return $result ? 'true' : 'false' ; 
	    }

	    return $result;
	}

	// ------------------------------------------------------------------------

	/**
	 * Log an array to a file
	 *
	 */
	public static function log_array($array)
    {
		@file_put_contents(__DIR__.'/print.txt', print_r($array, true));
    }

	// ----------------------------------------------------------------------------------

	/**
	* Log all messages
	*
	* @param array $logs The debug messages.
	* @return void
	*/
	public static function log_to_ee( $logs = array(), $name = '')
    {
        if(!empty($logs))
        {
            foreach ($logs as $log)
            {
                ee()->TMPL->log_item('&nbsp;&nbsp;***&nbsp;&nbsp;'.$name.' debug: ' . $log);
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Log method
     *
     * By default will pass message to log_message();
     * Also will log to template if rendering a PAGE.
     *
     *  1 = error
     *  2 = debug
     *  3 = info
     *
     * @access  public
     * @param   string      $message        The log entry message.
     * @param   int         $severity       The log entry 'level'.
     * @return  void
     */
    public static function log($message, $severity = 1)
    {
        // translate our severity number into text
        $severity = (array_key_exists($severity, self::$_levels)) ? self::$_levels[$severity] : self::$_levels[1];

        // save our log for EE Debug Toolbar
        self::$_log[] = array($severity, $message);
        if($severity == 'ERROR')
        {
            self::$_log_has_error = TRUE;
        }

        // basic EE logging
        log_message($severity, GMAPS_NAME . ": {$message}");

        // Can we also log our message to the template debugger?
        if (REQ == 'PAGE' && isset(ee()->TMPL))
        {
            ee()->TMPL->log_item(GMAPS_NAME . " [{$severity}]: {$message}");
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Fetch our static log
     *
     * @return  Array   Array of logs
     */
    public static function get_log()
    {
        return self::$_log;
    }

    // ------------------------------------------------------------------------

    /**
     * Fetch our static log
     *
     * @return  Array   Array of logs
     */
    public static function log_has_error()
    {
        return self::$_log_has_error;
    }

	// ------------------------------------------------------------------------

	/**
	 * Is the string serialized
	 *
	 */
	public static function is_serialized($val)
    {
        if (!is_string($val)){ return false; }
        if (trim($val) == "") { return false; }
        if (preg_match("/^(i|s|a|o|d):(.*);/si",$val)) { return true; }
        return false;
    }

	// ------------------------------------------------------------------------

	/**
	 * Is the string json
	 *
	 */
	public static function is_json($string)
    {
       json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

	// ------------------------------------------------------------------------

	/**
	 * Retrieve site path
	 */
	public static function get_site_path()
    {
        // extract path info
        $site_url_path = parse_url(ee()->functions->fetch_site_index(), PHP_URL_PATH);

        $path_parts = pathinfo($site_url_path);
        $site_path = $path_parts['dirname'];

        $site_path = str_replace("\\", "/", $site_path);

        return $site_path;
    }   

	// ------------------------------------------------------------------------

	/**
	 * remove beginning and ending slashes in a url
	 *
	 * @param  $url
	 * @return void
	 */
	public static function remove_begin_end_slash($url, $slash = '/')
    {
        $url = explode($slash, $url);
        array_pop($url);
        array_shift($url);
        return implode($slash, $url);
    }

	// ----------------------------------------------------------------------

	/**
	 * add slashes for an array
	 *
	 * @param  $arr_r
	 * @return void
	 */
	public static function add_slashes_extended(&$arr_r)
    {
        if(is_array($arr_r))
        {
            foreach ($arr_r as &$val)
                is_array($val) ? self::add_slashes_extended($val):$val=addslashes($val);
            unset($val);
        }
        else
            $arr_r = addslashes($arr_r);
    }

	// ----------------------------------------------------------------

	/**
	 * add a element to a array
	 *
	 * @return  DB object
	 */
	public static function array_unshift_assoc(&$arr, $key, $val)
    {
        $arr = array_reverse($arr, true);
        $arr[$key] = $val;
        $arr = array_reverse($arr, true);
        return $arr;
    }

	// ----------------------------------------------------------------------

	/**
	 * get the memory usage
	 *
	 * @param 
	 * @return void
	 */
	public static function memory_usage()
    {
         $mem_usage = memory_get_usage(true);
       
        if ($mem_usage < 1024)
            return $mem_usage." bytes";
        elseif ($mem_usage < 1048576)
            return round($mem_usage/1024,2)." KB";
        else
            return round($mem_usage/1048576,2)." MB";
    }

    // ------------------------------------------------------------------------

	/**
	 * Remove empty values (BETTER)
	 *
	 */
	public static function remove_empty_array_values($input)
    {
       return array_filter($input, create_function('$a','return trim($a)!="";'));
    }	

    // ------------------------------------------------------------------------

	/**
	 * Remove empty values
	 *
	 */
	public static function remove_empty_values($input)
    {
        // If it is an element, then just return it
        if (!is_array($input)) {
          return $input;
        }
        $non_empty_items = array();

        foreach ($input as $key => $value) {
          // Ignore empty cells
          if($value) {
            // Use recursion to evaluate cells 
            $non_empty_items[$key] = self::remove_empty_values($value);

            if($non_empty_items[$key] == '')
            {
                unset($non_empty_items[$key]);
            }
          }
        }

        if(empty($non_empty_items))
        {
            $non_empty_items = '';
        }

        // Finally return the array without empty items
        return $non_empty_items;
    }

    // ------------------------------------------------------------------------

	/**
	 * Build an js array
	 *
	 */
	public static function build_js_array($addresses, $strtolower = false, $evaluate_yes_no = false, $remove_empty_values = true) 
    {
       $addresses = trim($addresses);

        //lowercase
        if($strtolower) 
        {
            $addresses = strtolower($addresses);
        }

        //empty?
        if($addresses == '' || empty($addresses))
        {
            return '[]';
        }

        $addresses = explode('|',$addresses);

        //do we need to remove empty values
        if($remove_empty_values)
        {
            $addresses = self::remove_empty_values($addresses);
        }

        $_addresses = '[]';

        if(!empty($addresses))
        {
            $_addresses = '';
            
            foreach($addresses as $key => $address)
            {
                //evalutate yes or no
                if($evaluate_yes_no) {
                   $address = $address == 'yes' ? true : false ;
                }

                if($key == 0)
                {
                    $_addresses .= '[';
                }
                
                if(count($addresses) == ($key +1 ))
                {
                    $_addresses .= '"'.$address.'"';
                }
                else
                {
                    $_addresses .= '"'.$address.'",';
                }
                
                if(count($addresses) == ($key +1 ))
                {
                    $_addresses .= ']';
                }
            }
        }
        return $_addresses;
    }

    // ------------------------------------------------------------------------

	/**
	 * Cleanup text for twitter
	 *
	 */
	public static function twitter_clean_text( $str )
    {
        //remove doublequotes
        $str = str_replace(array('“', '”', '"'), array("'", "'", "'"), $str);

        //remove newline
        $str = preg_replace('/\s\s+/', ' ', $str);
        $str = (string)str_replace(array("\r", "\r\n", "\n"), ' ', $str);

        $str = trim($str);

        $str = htmlspecialchars($str);
        return $str;
    }

    // ------------------------------------------------------------------------

	/**
	 * calculate the distance between 2 points
	 *
	 */
	public static function distance( $latlng1, $latlng2 )
    {
        $latlng1 = explode(',', $latlng1);
        $latitude1 = $latlng1[0];
        $longitude1 = $latlng1[1];
        $latlng2 = explode(',', $latlng2);
        $latitude2 = $latlng2[0];
        $longitude2 = $latlng2[1];

        $theta = $longitude1 - $longitude2;
        $miles = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        return compact('miles','feet','yards','kilometers','meters'); 
    }

    // ------------------------------------------------------------------------

	/**
	 * avoid double latlngs
	 */
	public static function avoid_double_latlng( $latlng = array(), $radius = 25)
    {
       $latlng_new = array();

       if(!empty($latlng))
       {
            foreach($latlng as $ll)
            {
                //is there already an ll?
                if(in_array($ll, $latlng_new))
                {
                    $ll_ = explode(',', $ll);

                    $random = self::random_latlng($ll_[0], $ll_[1], $radius);
                    $ll = $random['lat'].','.$random['lng'];
                    $latlng_new[] = $ll;
                }

                //new one
                else
                {
                    $latlng_new[] = $ll;
                }
            }
       }

       return $latlng_new;
    }

    // ------------------------------------------------------------------------

	/**
	 * avoid double latlngs
	 */
	public static function create_links_from_string( $str, $target = '_blank')
    {
       return preg_replace("/(http:\/\/[^\s]+)/", "<a target='".$target."' href='$1'>$1</a>", $str);
    }

    // ------------------------------------------------------------------------

	/**
	 * random latlng in radius (miles)
	 */
	public static function random_latlng( $latitude, $longitude, $radius = 1 )
    {
        $lng_min = $longitude - $radius / abs(cos(deg2rad($latitude)) * 69);
        $lng_max = $longitude + $radius / abs(cos(deg2rad($latitude)) * 69);
        $lat_min = $latitude - ($radius / 69);
        $lat_max = $latitude + ($radius / 69);

        return array(
            'lat' => self::random_float($lat_min, $lat_max),
            'lng' => self::random_float($lng_min, $lng_max)
        );
    }

    // ------------------------------------------------------------------------

	/**
	 * Random float
	 */
	public static function random_float ($min,$max) 
    {
        return ($min+lcg_value()*(abs($max-$min)));
    }

    // ------------------------------------------------------------------------

	/**
	 * is curl loaded
	 */
	public static function is_curl_loaded() 
    {
        if (extension_loaded('curl')) {
            return true;
        }
        return false;
    }

    // ----------------------------------------------------------------------

	/**
	 * add slashes for an array
	 *
	 * @param  $arr_r
	 * @return void
	 */
	public static function addslashesextended(&$arr_r)
	{
		if(is_array($arr_r))
		{
			foreach ($arr_r as &$val)
				is_array($val) ? self::addslashesextended($val):$val=addslashes($val);
			unset($val);
		}
		else
			$arr_r = addslashes($arr_r);
	}

    // ----------------------------------------------------------------------

    /**
     * Trim multi line to one
     *
     * @param  $string
     * @return void
     */
    public static function trim_to_one_line($string)
    {
        $string = str_replace(array("\r\n", "\r"), "\n", $string);
        $lines = explode("\n", $string);
        $new_lines = array();
        foreach ($lines as $i => $line) {
            if(!empty($line))
                $new_lines[] = trim($line);
        }
        return implode($new_lines);
    }

    // ----------------------------------------------------------------------

    /**
     * Trim multi line to one
     *
     * @param  $string
     * @return void
     */
    public static function count_multiple_values($string = '', $delimiter = '|')
    {
        $string = explode($delimiter, $string);
        return count($string);
    }

    // ----------------------------------------------------------------------
     
    /**
     * Anonymously report EE & PHP versions used to improve the product.
     */
    public static function stats($overide = array())
    {
        if (
            ee()->gmaps_settings->item('report_stats') != 0 && 
            function_exists('curl_init') &&
            ee()->gmaps_settings->item('report_date') <  ee()->localize->now)
        {
            $data = http_build_query(array(
                // anonymous reference generated using one-way hash
                'license' => isset($overide['license']) ? $overide['license'] : ee()->gmaps_settings->item('license_key'),
                'product' => isset($overide['product']) ? $overide['product'] : GMAPS_NAME,
                'version' => isset($overide['version']) ? $overide['version'] : GMAPS_VERSION,
                'ee' => APP_VER,
                'php' => PHP_VERSION,
                'time' => ee()->localize->now,
            ));
            ee()->load->library('curl');
            ee()->curl->simple_post(GMAPS_STATS_URL, $data);
            //ee()->curl->debug();

            // report again in 7 days
            ee()->gmaps_settings->save_setting('report_date', ee()->localize->now + 7*24*60*60);
        }
    }

    // ----------------------------------------------------------------------

    /**
     * set_cache
     *
     * @access private
     */
    public static function set_ee_cache($name = '', $value = '', $reset = false)
    {
        if ( isset(ee()->session->cache[GMAPS_MAP][$name]) == FALSE || $reset == true)
        {
            ee()->session->cache[GMAPS_MAP][$name] = $value;
        }
        return ee()->session->cache[GMAPS_MAP][$name];

    }

    // ----------------------------------------------------------------------

    /**
     * get_cache
     *
     * @access private
     */
    public static function get_ee_cache($name = '')
    {
        if ( isset(ee()->session->cache[GMAPS_MAP][$name]) != FALSE )
        {
            return ee()->session->cache[GMAPS_MAP][$name];
        }
        return false;
    }

    // ----------------------------------------------------------------------

    /**
     * set_cache
     *
     * @access private
     */
    public static function set_cache($name = '', $value = '')
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
    public static function get_cache($name = '')
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
    public static function delete_cache($name = '')
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
     * @return bool
     */
    public static function is_ssl()
    {
        $is_SSL = FALSE;

        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
            || $_SERVER['SERVER_PORT'] == 443) {

            $is_SSL = TRUE;
        }


        return $is_SSL;
    }
    
	// ----------------------------------------------------------------------
	
} // END CLASS

/* End of file default_helper.php  */
/* Location: ./system/expressionengine/third_party/default/libraries/default_helper.php */