<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Geofinder Module
 *
 * @package		Geofinder
 * @category	Modules
 * @author		Natural Logic, Jason Ferrell
 * @link		http://natural-logic.com
 */

include_once(APPPATH.'modules/channel/mod.channel.php');

class Geofinder extends Channel{

  	var $return_data    		= '';     	// Final data
	var $query;
  	var $TYPE;
	var $default_limit = 20;
	var $default_radius = 15;
	var $default_status = 'open';
	var $default_member_group = 'members';
	var $default_distance_mode = 'miles';
	var $google_geo_url = 'http://maps.googleapis.com/maps/api/geocode/xml?sensor=false';
	var $channel_ids = array();
	var $gmaps_country_code = '';

    // -------------------------------------
    //  Constructor
    // -------------------------------------

	function Geofinder()
	{
		parent::Channel();
		$this->EE =& get_instance();
		$this->EE->lang->loadfile('geofinder');
	}

	function simple_form()
	{

		$res = '';

		/** ----------------------------------------
		/**  Create form
		/** ----------------------------------------*/

		$result_page = ( ! $this->EE->TMPL->fetch_param('result_page')) ? 'geofinder/results' : $this->EE->TMPL->fetch_param('result_page');

		$data['hidden_fields'] = array(
			'ACT'					=> $this->EE->functions->fetch_action_id('Geofinder', 'find_locations'),
			'XID'					=> '',
			'RP'					=> $result_page,
			'user_lat'				=> '',
			'user_lng'				=> ''
			);

		if ($this->EE->TMPL->fetch_param('id') !== FALSE &&
			preg_match('#^[a-zA-Z0-9_\-]+$#i', $this->EE->TMPL->fetch_param('id')))
		{
			$data['id'] = $this->EE->TMPL->fetch_param('id');
		}

		$res  = $this->EE->functions->form_declaration($data);

		$res .= stripslashes($this->EE->TMPL->tagdata);

		$res .= '</form>';

		return $res;
	}

	/** ----------------------------------------
    /**  Find Locations
    /** ----------------------------------------*/

	function find_locations()
	{

		$default	= array('geoquery', 'radius', 'categories', 'user_lat', 'user_lng');

		foreach ($default as $val)
		{
			if ( ! isset($_POST[$val]))
			{
				$_POST[$val] = '';
			}
		}

		/** ----------------------------------------
		/**  Fetch the Geofinder language file
		/** ----------------------------------------*/

		$this->EE->lang->loadfile('geofinder');

		/** ----------------------------------------
		/**  Validate our fields
		/** ----------------------------------------*/

		if (($_POST['geoquery'] == '' && $_POST['user_lat'] == '' && $_POST['user_lng'] == '') || $_POST['radius'] == '')
		{
			return show_error($this->EE->lang->line('geofinder_validation_error'));
		}

		$geo_query = urlencode($this->EE->security->xss_clean($_POST['geoquery']));
		$geo_user_lat = urlencode($this->EE->security->xss_clean($_POST['user_lat']));
		$geo_user_lng = urlencode($this->EE->security->xss_clean($_POST['user_lng']));
		$geo_radius = $this->EE->security->xss_clean($_POST['radius']);
		$categories = $this->EE->security->xss_clean($_POST['categories']);
		$cat_separator = ($this->EE->config->item('word_separator') == 'dash') ? '-' : '_';

		if ($geo_query) //check for geoquery, it trumps user location.
		{
			$path = $this->EE->functions->create_url(trim_slashes($_POST['RP'])).'/'.$geo_query.'/'.$geo_radius.'/'.((! $categories) ? '' : implode($cat_separator, $categories));

			return $this->EE->functions->redirect($path);
		}
		else
		{
			$path = $this->EE->functions->create_url(trim_slashes($_POST['RP'])).'/'.$geo_user_lat.','.$geo_user_lng.'/'.$geo_radius.'/'.((! $categories) ? '' : implode($cat_separator, $categories));

			return $this->EE->functions->redirect($path);
		}
	}

	function location_results()
	{
		// we must always turn dynamic="off"
		$this->EE->TMPL->tagparams['dynamic'] = 'off';
		$geo_limit = $this->EE->TMPL->fetch_param('limit');

		// // grab any categories that were supplied as part of the search
		$categories = $this->EE->security->xss_clean($this->EE->TMPL->fetch_param('category'));
		$search_category_request = FALSE;

		// make sure specified categories segment doesn't hold paging info
		if ($categories)
		{
			if (substr($categories, 0, 1) == 'P')
			{
				$categories = FALSE;
				$this->EE->TMPL->tagparams['category'] = '';
			}
		}

		$search_categories = array();
		$cat_ids = 0;

		if ($categories)
		{  // replace word_separator with pipe character so category tag works like standard exp:channel:entries
			$cat_separator = ($this->EE->config->item('word_separator') == 'dash') ? '-' : '_';
			// if categories contain pipe they are hardcoded, no need to replace
			if (strstr($categories,'|'))
			{
				$cat_separator = '|';
			}

			$this->EE->TMPL->tagparams['category'] = str_replace($cat_separator, '|', $categories);
			$cat_ids = str_replace($cat_separator, ',', $categories);

			$cat_sql = 'SELECT cat_name FROM exp_categories WHERE cat_id IN ('.$cat_ids.')';
			$cat_query = $this->EE->db->query($cat_sql);

			foreach($cat_query->result_array() as $row)
			{
				array_push($search_categories, $row['cat_name']);
			}
		}
		if (version_compare(APP_VER, '2.6.0', '<')) {
			$this->EE->TMPL->tagdata = $this->EE->TMPL->assign_relationship_data($this->EE->TMPL->tagdata);
			$this->EE->TMPL->var_single = array_merge($this->EE->TMPL->var_single, $this->EE->TMPL->related_markers);
		}
		$channel = new Channel();
		$channel->initialize();

		$channel->uri = ($this->query_string != '') ? $this->query_string : 'index.php';

		// custom fields must always be enabled so we override here
		$channel->enable['custom_fields'] = TRUE;

		// trackbacks aren't needed since we are searching, not showing single entries
		$channel->enable['trackbacks'] = FALSE;

		if ($channel->enable['custom_fields'] == TRUE)
		{
			$channel->fetch_custom_channel_fields();
		}

		if ($channel->enable['member_data'] == TRUE)
		{
			$channel->fetch_custom_member_fields();
		}

		//only render if older ee
		if ($channel->enable['pagination'] == TRUE && version_compare(APP_VER, '2.4', '<'))
		{
			$channel->fetch_pagination_data();
		}

        $save_cache = FALSE;

		if ($this->EE->config->item('enable_sql_caching') == 'y')
		{
			if (FALSE == ($channel->sql = $channel->fetch_cache()))
			{
				$save_cache = TRUE;
			}
			else
			{
				if ($this->EE->TMPL->fetch_param('dynamic') != 'off')
				{
					if (preg_match('#(^|\/)C(\d+)#', $channel->QSTR, $match) OR in_array($channel->reserved_cat_segment, explode('/', $channel->QSTR)))
					{
						$channel->cat_request = TRUE;
					}
				}
			}

			if (FALSE !== ($cache = $channel->fetch_cache('pagination_count')))
			{
				if (FALSE !== ($channel->fetch_cache('field_pagination')))
				{
					if (FALSE !== ($pg_query = $channel->fetch_cache('pagination_query')))
					{
						$channel->paginate = TRUE;
						$channel->field_pagination = TRUE;
						$channel->create_pagination(trim($cache), $this->EE->db->query(trim($pg_query)));
					}
				}
				else
				{
					$channel->create_pagination(trim($cache));
				}
			}
		}

		$geoquery = $this->EE->security->xss_clean($this->EE->TMPL->fetch_param('geoquery'));
		$geoquery = urldecode($geoquery);

		// is gmaps_country_code specified?
		$this->gmaps_country_code = $this->EE->security->xss_clean($this->EE->TMPL->fetch_param('gmaps_country_code'));

		// check if geoquery contains lat/lng coordinates
		$geoquery_check = explode(',', $geoquery);
		$is_user_coord = FALSE;

		if (sizeof($geoquery_check) == 2)
		{
			foreach ($geoquery_check as &$value)
			{
				if (is_numeric($value))
				{
					$is_user_coord = TRUE;
				}
			}
		}

		$radius = $this->EE->security->xss_clean($this->EE->TMPL->fetch_param('radius'));
		if ($radius == '') $radius = $this->default_radius;

		$distance_mode = $this->EE->security->xss_clean($this->EE->TMPL->fetch_param('distance_mode'));
		if ($distance_mode == '') $distance_mode = $this->default_distance_mode;

		$channel_param = $this->EE->security->xss_clean($this->EE->TMPL->fetch_param('channel'));

		if ($channel_param != '')
		{
			$channel_array = explode("|", $channel_param);
			foreach($channel_array as $c)
			{
				$channel_param_sql = "SELECT channel_id FROM exp_channels WHERE channel_name = '".$c."'";
				$channel_param_query = $this->EE->db->query($channel_param_sql);
				if ($channel_param_query->num_rows() > 0)
				{
					foreach($channel_param_query->result_array() as $row)
					{
						array_push($this->channel_ids, $row['channel_id']);
					}
				}
			}
		}

		// get total number of records in specified channels
		$this->EE->db->where_in('channel_id', implode(',',$this->channel_ids));
		$this->EE->TMPL->tagparams['limit'] = $this->EE->db->count_all_results('channel_titles');

		$status = $this->EE->security->xss_clean($this->EE->TMPL->fetch_param('status'));
		if ($status == '')
		{
			$status = $this->default_status;
		}

		$channel->build_sql_query();

		if ($channel->sql == '')
		{
			return $this->EE->TMPL->no_results();
		}

		$lat;
		$lng;

		if ($is_user_coord)
		{
			$geoquery = implode(', ', $geoquery_check);
			$lat = $geoquery_check[0];
			$lng = $geoquery_check[1];
		}
		else
		{
			// check if proxy param specified
			$proxy = $this->EE->security->xss_clean($this->EE->TMPL->fetch_param('proxy'));

			if ($proxy) { // replace google url with proxy
				$this->google_geo_url = $proxy;
			}

			$coords = $this->_geocode($geoquery);

			if (!$coords)
			{
				return $this->EE->TMPL->no_results();
			}

			$lat = $coords[1];
			$lng = $coords[0];
		}

		/** ----------------------------------------
		/**  Get the latitude field id from the latitude param name
		/** ----------------------------------------*/
		$lat_field_name = $this->EE->security->xss_clean($this->EE->TMPL->fetch_param('latitude'));
		$lat_field_query = $this->EE->db->query("SELECT field_id FROM exp_channel_fields WHERE field_name = '".trim($lat_field_name,'{}')."' LIMIT 1");

		if ($lat_field_query->num_rows() === 0)
		{
			return  show_error($this->EE->lang->line('latitude_field_error'));
		}

		$lat_field_id = $lat_field_query->row()->field_id;

		/** ----------------------------------------
		/**  Get the longitude field id from the longitude param name
		/** ----------------------------------------*/
		$lng_field_name = $this->EE->security->xss_clean($this->EE->TMPL->fetch_param('longitude'));
		$lng_field_query = $this->EE->db->query("SELECT field_id FROM exp_channel_fields WHERE field_name = '".trim($lng_field_name,'{}')."' LIMIT 1");

		if ($lng_field_query->num_rows() === 0)
		{
			return show_error($this->EE->lang->line('longitude_field_error'));
		}

		$lng_field_id = $lng_field_query->row()->field_id;

		$offset = 0;
		$last_segment = strrchr(rtrim($this->EE->uri->uri_string, '/'), '/');

		if (preg_match('([P][0-9])',$last_segment))
		{
			$paging_uri = TRUE;
			$offset = substr($last_segment, 2, strlen($last_segment));
		}else
		{
			$paging_uri = FALSE;
		}

		$limit = $geo_limit;
		if ( ! $limit) $limit = $this->default_limit;

		$channel->sql = $this->_build_locations_query($lat, $lng, $radius, $lat_field_id, $lng_field_id, $distance_mode, $channel->sql, $offset, $limit, $cat_ids, $status, $channel->cfields);

		if ($save_cache == TRUE)
		{
			$this->save_cache($channel->sql);
		}

		$total_sql = substr($channel->sql, 0, strpos($channel->sql, 'LIMIT'));
		$total_query = $this->EE->db->query($total_sql);
		$total = $total_query->num_rows();

		$this->EE->TMPL->tagdata = $this->EE->TMPL->swap_var_single('total_results', $total, $this->EE->TMPL->tagdata);

		// handle paging
		if ($geo_limit)
		{
			$this->EE->TMPL->tagparams['limit'] = $limit;
			$channel->total_rows = $total;
			$channel->p_limit = $geo_limit;
			$channel->total_pages = ceil($total/$geo_limit);
			$channel->p_page = $offset;
		}

		$channel->query = $this->EE->db->query($channel->sql);

		if ($channel->query->num_rows() == 0)
		{
			return $this->EE->TMPL->no_results();
		}

		$this->EE->load->library('typography');
		$this->EE->typography->initialize();

		if ($channel->enable['categories'] == TRUE)
		{
			$channel->fetch_categories();
		}

		$channel->parse_channel_entries();

		//only rendering if older ee
		if ($channel->enable['pagination'] == TRUE && version_compare(APP_VER, '2.4', '<'))
		{
			$channel->create_pagination($total, $channel->sql);
			$channel->add_pagination_data();
		}

		$search_cat_name = '';
		$backspace = FALSE;

		if ($categories)
		{
	    if (preg_match_all('/'.LD.'search_categories(.*?)'.RD.'(.*?)'.LD.'\/'.'search_categories'.RD.'/s', $this->EE->TMPL->tagdata, $matches))
	    {
			$search_cat_name = $matches['2'][0];
	    }

			// handle search_categories var pair
			foreach ($this->EE->TMPL->var_pair as $key => $val)
			{
				if (strncmp('search_categories', $key, 17) == 0)
				{
					if (is_array($val) AND isset($val['backspace']))
					{
						$backspace = $val['backspace'];
					}
					$channel->return_data = $this->EE->TMPL->swap_var_pairs($key, 'search_categories', $channel->return_data);
				}
			}

			$temp = '';
			$str = '';

			foreach ($search_categories as $val)
			{
				$temp .= str_replace(LD.'search_category_name'.RD, $val, $search_cat_name);
			}

			if ($backspace)
			{
				$str = $temp;
				$str = substr($str, 0, - $backspace);
			}

			$channel->return_data = str_replace($search_cat_name, $str, $channel->return_data);

		}else
		{
	    if (preg_match_all('/'.LD.'search_categories(.*?)'.RD.'(.*?)'.LD.'\/'.'search_categories'.RD.'/s', $this->EE->TMPL->tagdata, $matches))
	    {
				$search_cat_name = $matches['0'][0];
	    }
			$channel->return_data = str_replace($search_cat_name, '', $channel->return_data);
		}

		if ($categories)
		{
			$cond = array();
			$cond['search_category_request'] = TRUE;
			$channel->return_data = $this->EE->functions->prep_conditionals($channel->return_data, $cond);
		}

		foreach ($this->EE->TMPL->var_single as $key => $val)
		{
			if ($key == 'geoquery')
	    {
	        $channel->return_data = $this->EE->TMPL->swap_var_single($val, $geoquery, $channel->return_data);
	    }
			if ($key == 'radius')
	    {
	        $channel->return_data = $this->EE->TMPL->swap_var_single($val, $radius, $channel->return_data);
	    }
			if ($key == 'distance_mode')
	    {
	        $channel->return_data = $this->EE->TMPL->swap_var_single($val, $distance_mode, $channel->return_data);
	    }
			if ($key == 'query_lat')
	    {
	        $channel->return_data = $this->EE->TMPL->swap_var_single($val, $lat, $channel->return_data);
	    }
			if ($key == 'query_lng')
	    {
	        $channel->return_data = $this->EE->TMPL->swap_var_single($val, $lng, $channel->return_data);
	    }
		}

		if (count($this->EE->TMPL->related_data) > 0 AND count($channel->related_entries) > 0)
		{
			$channel->parse_related_entries();
		}

		if (count($this->EE->TMPL->reverse_related_data) > 0 AND count($channel->reverse_related_entries) > 0)
		{
			$channel->parse_reverse_related_entries();
		}

		if (version_compare(APP_VER, '2.4', '<'))
		{
			return $channel->return_data;
		} else {
			return $this->render_with_page_fix($channel, $limit, $offset);
		}
	}

	function render_with_page_fix($channel, $limit, $offset) {
		if ($channel->enable['pagination'] == TRUE)
		{

			if (version_compare(APP_VER, '2.8', '>=')) {
        $this->EE->load->library('pagination');
        $pagination = $this->EE->pagination->create();
        $this->EE->TMPL->tagdata = $pagination->prepare($this->EE->TMPL->tagdata);
        $total_items = $channel->total_rows;
        $per_page = $this->EE->TMPL->fetch_param('limit');
        $pagination->build($total_items, $per_page);
        if ($pagination->paginate === TRUE)
        {
          $pagination->build($total_items, $per_page);
        }

        return $pagination->render($channel->return_data);
      } else {
				$channel->pagination->EE->pagination->total_rows = $channel->total_rows;
				$channel->pagination->get_template();
				$channel->return_data = preg_replace(
					"/".LD."paginate".RD.".+?".LD.'\/'."paginate".RD."/s",
					"",
					$channel->return_data
				);
				$channel->pagination->build($channel->total_pages);

				$config = array();
				$config['first_url'] 	= rtrim($channel->pagination->basepath, '/');
				$config['base_url']		= $channel->pagination->basepath;
				$config['prefix']		= 'P';
				$config['total_rows'] 	= $channel->total_rows;
				$config['per_page']	= $limit;
				// cur_page uses the offset because P45 (or similar) is a page
				$config['cur_page']	= $offset;
				$config['first_link'] 	= lang('pag_first_link');
				$config['last_link'] 	= lang('pag_last_link');
				$config['uri_segment']	= 0; // Allows $config['cur_page'] to override

				$channel->pagination->EE->pagination->initialize($config);
				$channel->pagination->page_links = $channel->pagination->EE->pagination->create_links();
				$channel->pagination->EE->pagination->initialize($config); // Re-initialize to reset config
				$channel->pagination->page_array = $channel->pagination->EE->pagination->create_link_array();

				$channel->pagination->current_page = floor(($offset / $limit) + 1);
				$channel->pagination->total_pages = $channel->total_pages;

				return $channel->pagination->render($channel->return_data);
			}
		} else {
			return $channel->return_data;
		}
	}

	function member_form()
	{

		$res = '';

		/** ----------------------------------------
		/**  Create form
		/** ----------------------------------------*/

		$result_page = ( ! $this->EE->TMPL->fetch_param('result_page')) ? 'geofinder/results' : $this->EE->TMPL->fetch_param('result_page');

		$data['hidden_fields'] = array(
			'ACT'					=> $this->EE->functions->fetch_action_id('Geofinder', 'find_members'),
			'XID'					=> '',
			'RP'					=> $result_page
			);

		if ($this->EE->TMPL->fetch_param('id') !== FALSE && preg_match('#^[a-zA-Z0-9_\-]+$#i', $this->EE->TMPL->fetch_param('id')))
		{
			$data['id'] = $this->EE->TMPL->fetch_param('id');
		}

		$res  = $this->EE->functions->form_declaration($data);

		$res .= stripslashes($this->EE->TMPL->tagdata);

		$res .= '</form>';

		return $res;
	}

	/** ----------------------------------------
    /**  Find Members
    /** ----------------------------------------*/

    function find_members()
    {

	    $default	= array('geoquery', 'radius');

	    foreach ($default as $val)
	    {
			if ( ! isset($_POST[$val]))
			{
				$_POST[$val] = '';
			}
	    }

		/** ----------------------------------------
		/**  Fetch the Geofinder language file
		/** ----------------------------------------*/

		$this->EE->lang->loadfile('geofinder');

		/** ----------------------------------------
		/**  Validate our fields
		/** ----------------------------------------*/

		if ($_POST['geoquery'] == '' || $_POST['radius'] == '')
		{
			return  show_error($this->EE->lang->line('geofinder_validation_error'));
		}

		$geo_query = urlencode($this->EE->security->xss_clean($_POST['geoquery']));
		$geo_radius = $this->EE->security->xss_clean($_POST['radius']);

		$path = $this->EE->functions->create_url(trim_slashes($_POST['RP'])).'/'.$geo_query.'/'.$geo_radius.'/';

		return $this->EE->functions->redirect($path);
	}

	function member_results()
	{

		$this->EE->lang->loadfile('geofinder');

		// get the params and values
		$geoquery = $this->EE->security->xss_clean($this->EE->TMPL->fetch_param('geoquery'));

		$radius = $this->EE->security->xss_clean($this->EE->TMPL->fetch_param('radius'));
		if ($radius == '') $radius = $this->default_radius;

		$distance_mode = $this->EE->TMPL->fetch_param('distance_mode');
		if ($distance_mode == '') $distance_mode = $this->default_distance_mode;

		$limit = $this->EE->TMPL->fetch_param('limit');
		if ($limit == '') $limit = $this->default_limit;

		$member_group = $this->EE->TMPL->fetch_param('member_group');
		if ($limit == '') $member_group = $this->default_member_group;

		$coords = $this->_geocode($geoquery);

		if (!$coords)
		{
			return $this->EE->TMPL->no_results();
		}

		$lat = $coords[1];
		$lng = $coords[0];

    /** ----------------------------------------
    /**  Get the member group id from the member_group param name
    /** ----------------------------------------*/
		$group_name = $this->EE->TMPL->fetch_param('member_group');
		$group_query = $this->EE->db->query("SELECT group_id FROM exp_member_groups WHERE group_title = '".$member_group."' LIMIT 1");

		if ($group_query->num_rows() == 0)
		{
			return  show_error($this->EE->lang->line('no_member_group_error'));
		}

		$group_id = $group_query->row()->group_id;

    /** ----------------------------------------
    /**  Get the latitude field id from the latitude param name
    /** ----------------------------------------*/
		$lat_field_name = $this->EE->TMPL->fetch_param('latitude');
		$lat_field_query = $this->EE->db->query("SELECT m_field_id FROM exp_member_fields WHERE m_field_name = '".$lat_field_name."' LIMIT 1");

		if ($lat_field_query->num_rows() === 0)
		{
			return  show_error($this->EE->lang->line('latitude_field_error'));
		}

		$lat_field_id = $lat_field_query->row()->m_field_id;

    /** ----------------------------------------
    /**  Get the longitude field id from the longitude param name
    /** ----------------------------------------*/
		$lng_field_name = $this->EE->TMPL->fetch_param('longitude');
		$lng_field_query = $this->EE->db->query("SELECT m_field_id FROM exp_member_fields WHERE m_field_name = '".$lng_field_name."' LIMIT 1");

		if ($lng_field_query->num_rows() === 0)
		{
			return  show_error($this->EE->lang->line('longitude_field_error'));
		}

		$lng_field_id = $lng_field_query->row()->m_field_id;

    /** ----------------------------------------
    /**  Build the sql query
    /** ----------------------------------------*/

		$this->query = $this->_build_members_query($lat, $lng, $radius, $limit, $lat_field_id, $lng_field_id, $group_id, $distance_mode);

    /** ----------------------------------------
    /**  No query results?
    /** ----------------------------------------*/
 		$item_count = 0;

		if ($this->query->num_rows() == 0)
        {
        	return $this->EE->TMPL->no_results();
        }

		$total_results  = $this->query->num_rows();

    /** ----------------------------------------
    /**  Fetch Member Custom Fields
    /** ----------------------------------------*/
		$custom_fields = $this->EE->db->query('SELECT m_field_id,m_field_name FROM exp_member_fields');
		$custom_field_names = array();

		foreach($custom_fields->result_array() as $row)
    	{
			$custom_field_names[$row['m_field_name']] = $row['m_field_id'];
    	}

		foreach ($this->query->result_array() as $row)
		{
			$item_count++;
			$row['count'] = $item_count;
			$row['total_results'] = $total_results;
			$row['geoquery'] = urldecode($geoquery);
			$row['radius'] = $radius;
			$row['distance_mode'] = $distance_mode;
			$row['query_lat'] = $lat;
			$row['query_lng'] = $lng;

			$tagdata = $this->EE->TMPL->tagdata;
			$cond = $row;
			$tagdata = $this->EE->functions->prep_conditionals($tagdata, $cond);

			foreach ( $this->EE->TMPL->var_single as $key => $val )
			{
				if ( isset( $row[$val] ) )
				{
					$tagdata = $this->EE->TMPL->swap_var_single( $val, $row[$val], $tagdata );
				}else
				{
					if ( isset( $custom_field_names[$val] ))
					{
						$tagdata = $this->EE->TMPL->swap_var_single( $val, $row['m_field_id_'.$custom_field_names[$val]], $tagdata );
					}
				}

				/** ----------------------------------------
				/**  parse {switch} variable
				/** ----------------------------------------*/

				if (preg_match('/^switch\s*=.+/i', $key))
				{
					$sparam = $this->EE->functions->assign_parameters($key);

					$sw = '';

					if (isset($sparam['switch']))
					{
						$sopt = explode('|', $sparam['switch']);

						$sw = $sopt[($item_count + count($sopt)) % count($sopt)];
					}

					$tagdata = $this->EE->TMPL->swap_var_single($key, $sw, $tagdata);
				}
			}
			$this->return_data .= $tagdata;
		}

		return $this->return_data;
	}

	function geocode()
  {
		$geoquery = urldecode($this->EE->security->xss_clean($this->EE->TMPL->fetch_param('geoquery')));

		$coords = $this->_geocode($geoquery);


		if ( ! $coords)
		{
			return $this->EE->TMPL->no_results();
		}

		$lat = $coords[1];
		$lng = $coords[0];

		$tagdata = $this->EE->TMPL->tagdata;
		$tagdata = $this->EE->TMPL->swap_var_single('latitude', $lat, $tagdata);
		$tagdata = $this->EE->TMPL->swap_var_single('longitude', $lng, $tagdata);

		$this->return_data = $tagdata;

		return $this->return_data;
	}

	function _geocode($geoquery = '')
	{
    /** ----------------------------------------
    /**  Fetch the Geofinder language file
    /** ----------------------------------------*/
    $this->EE->lang->loadfile('geofinder');

		if ($geoquery == '') return FALSE;

		// query exp_geofinder to see if we have local geocoded value
		$geocode_sql = "SELECT latitude, longitude FROM exp_geofinder WHERE geoquery = '".$geoquery."'";

		if ($this->gmaps_country_code)
		{
			$geocode_sql .= " AND country_code = '".$this->gmaps_country_code."'";
		}

		$geocode_query = $this->EE->db->query($geocode_sql);

		if ($geocode_query->num_rows() == 0 && $geoquery != '') // if we don't, query Google Geocoding Service
		{
			$geo_url = $this->google_geo_url;

			if ($this->gmaps_country_code)
			{
				$geo_url .= '&region='.$this->gmaps_country_code;
			}
			else
			{
				// perform regexp to check for a UK postal code
				$regex_uk = '(GIR 0AA|[A-PR-UWYZ]([0-9]{1,2}|([A-HK-Y][0-9]|[A-HK-Y][0-9]([0-9]|[ABEHMNPRV-Y]))|[0-9][A-HJKS-UW])[ ]*[0-9][ABD-HJLNP-UW-Z]{2})';

				if (preg_match($regex_uk, $geoquery))
				{
					$geo_url .= '&region=gb';
				}
			}

	    // get the coordinates for user entered address
			$geoquery_encoded = urlencode($geoquery);
	    	$request_url = $geo_url.'&address='.$geoquery_encoded;

			if (function_exists('curl_init')) { // use curl
				// perform geocode request
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $request_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				$output = curl_exec($ch);
				curl_close($ch);

				if ($output == '')
				{
					return  show_error($this->EE->lang->line('network_error'));
				}

				$xml = new SimpleXMLElement($output);

			}
			else //use simplexml_load_file
			{
				$xml = simplexml_load_file($request_url) or FALSE;

				if ( ! $xml )
				{
					return  show_error($this->EE->lang->line('network_error'));
				}
			}

			$status = $xml->status;

			if ($status == 'OK') {
				$lat = $xml->result->geometry->location->lat;
				$lng = $xml->result->geometry->location->lng;

				// insert record into db
				$data = array('geoquery' => $this->EE->db->escape_str($geoquery), 'latitude' => $lat, 'longitude' => $lng, 'country_code' => $this->gmaps_country_code);
				$sql = $this->EE->db->insert_string('exp_geofinder', $data);
				$this->EE->db->query($sql);

				return array($lng, $lat);
			}
			elseif($status == "ZERO_RESULTS")
			{
				return FALSE;
			}
			else
			{
				return  show_error($this->EE->lang->line('google_error').$status);
			}
		}
		else // if we do, return lat/lng
		{
			return array($geocode_query->row()->longitude, $geocode_query->row()->latitude);
		}
	}

  function _build_locations_query($user_lat = '', $user_lng = '', $radius = 15, $lat_field_id = 0, $lng_field_id = 0, $distance_mode = 'miles', $sql, $offset = 0, $limit = 0, $cat_ids = 0, $status = 'open', $cfields)
    {

		$sql_where_pos = strpos($sql, 'WHERE');
		$sql_in_ids = FALSE;

		$sql_where = substr($sql, $sql_where_pos, strlen($sql));
		$sql_in_pos = strpos($sql_where, 'IN') + 3;
		$sql_order_pos = strpos($sql_where, 'ORDER') - 1;
		$sql_in_ids = substr($sql_where, $sql_in_pos, $sql_order_pos - $sql_in_pos);

		// find WHERE and remove
		$sql = substr($sql, 0, $sql_where_pos);

		$distance_calc = ($distance_mode == 'miles') ? 3956 : 6368;

		$geo_sql_select_cats = 'SELECT DISTINCT(t.entry_id), ';

		if (substr_count($sql, 'DISTINCT') > 0)
		{
			$geo_sql_select = $geo_sql_select_cats;
			$geo_sql_replace = $geo_sql_select;
			$sql = str_replace('DISTINCT(t.entry_id), ','', $sql);
		}else
		{
			$geo_sql_select = 'SELECT ';
			$geo_sql_replace = $geo_sql_select;
		}

		$geo_sql_select .= 'ROUND(( '.$distance_calc.' * acos( cos( radians('.$user_lat.') ) * cos( radians( field_id_'.$lat_field_id.' ) ) * ';
		$geo_sql_select .= 'cos( radians( field_id_'.$lng_field_id.' ) - radians('.$user_lng.') ) + sin( radians('.$user_lat.') ) * ';
		$geo_sql_select .= 'sin( radians( field_id_'.$lat_field_id.' ) ) ) ), 1) AS distance, ';

		$geo_sql = str_replace('SELECT', $geo_sql_select, $sql);

		if ($cat_ids != 0)
		{
			$geo_sql_where = 'LEFT JOIN exp_category_posts as cp ON cp.entry_id = t.entry_id WHERE cp.cat_id IN ('.$cat_ids.') HAVING distance <= '.$radius.' ';
		}else
		{
			$geo_sql_where = 'HAVING distance <= '.$radius.' ';
		}

		if (sizeof($this->channel_ids) > 0)
		{
			$geo_sql_where .= 'AND t.channel_id IN ('.implode(',', $this->channel_ids).') ';
		}

		if ($sql_in_ids)
		{
			$geo_sql_where .= 'AND t.entry_id IN '.$sql_in_ids.' ';
		}

		$geo_sql_where .= $this->EE->functions->sql_andor_string($status, 't.status').' ';

		$geo_order = $this->EE->TMPL->fetch_param('orderby');
		$geo_sort = $this->EE->TMPL->fetch_param('sort');
		if ($geo_order)
		{
			// put order into array
			$geo_order_array = explode('|', $geo_order);
			// put sort into array
			$geo_sort_array = explode('|', $geo_sort);
			$geo_sql_order = 'ORDER BY ';
			// base orders
			$base_orders = array('distance', 'random', 'entry_id', 'date', 'title', 'url_title', 'edit_date', 'comment_total', 'username', 'screen_name', 'most_recent_comment', 'expiration_date','view_count_one', 'view_count_two', 'view_count_three', 'view_count_four');
			// loop through order array and generate SQL
			foreach($geo_order_array as $key => $order)
			{
				// get custom field id
				if ( ! in_array($order, $base_orders))
				{
					$order = 'wd.field_id_'.$cfields[$this->EE->config->item('site_id')][$order];
				}
				$sort = (isset($geo_sort_array[$key]) && $geo_sort_array[$key] != '') ? $geo_sort_array[$key] : 'asc';
				$geo_sql_order .= $order.' '.$sort.', ';
			}
			// remove last comma
			$geo_sql_order = substr($geo_sql_order,0,-2).' ';
		}else
		{
			$geo_sql_order = 'ORDER BY distance, t.title ';
		}

		if ($limit != 0)
		{
			$geo_sql_limit = 'LIMIT '.$offset.','.$limit.' ';
			$geo_sql .= $geo_sql_where.$geo_sql_order.$geo_sql_limit;
		}else
		{
			$geo_sql .= $geo_sql_where.$geo_sql_order;
		}

		return $geo_sql;
	}

  function _build_members_query($user_lat = '', $user_lng = '', $radius = 15, $results = 20, $lat_field_id = 0, $lng_field_id = 0, $group_id = 0, $distance_mode = 'miles')
    {

		$distance_calc = ($distance_mode == 'miles') ? 3959 : 6371;

		$geo_sql = 'SELECT * , ';
		$geo_sql .= 'ROUND(( '.$distance_calc.' * acos( cos( radians('.$user_lat.') ) * cos( radians( m_field_id_'.$lat_field_id.' ) ) * ';
		$geo_sql .= 'cos( radians( m_field_id_'.$lng_field_id.' ) - radians('.$user_lng.') ) + sin( radians('.$user_lat.') ) * ';
 		$geo_sql .= 'sin( radians( m_field_id_'.$lat_field_id.' ) ) ) ), 1) AS distance ';
		$geo_sql .= 'FROM exp_members AS m ';
		$geo_sql .= 'INNER JOIN exp_member_data AS md ON m.member_id = md.member_id ';
		$geo_sql .= 'HAVING distance < '.$radius.' ';
		$geo_sql .= 'AND m.group_id = '.$group_id.' ';
		$geo_sql .= 'ORDER BY distance LIMIT 0 , '.$results;

		if ($user_lat != '' || $user_lng != '')
		{
			$query = $this->EE->db->query($geo_sql);
		}

		return $query;
	}
}
// END CLASS

?>