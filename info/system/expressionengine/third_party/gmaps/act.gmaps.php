<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Gmaps action File
 *
 * @package             Gmaps for EE2
 * @author              Rein de Vries (info@reinos.nl)
 * @copyright           Copyright (c) 2013 Rein de Vries
 * @license  			http://reinos.nl/add-ons/commercial-license
 * @link                http://reinos.nl/add-ons/gmaps
 */

require_once(PATH_THIRD.'gmaps/config.php');

class Gmaps_ACT
{
	private $EE; 

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

		//load the helper
		//ee()->load->helper(GMAPS_MAP.'_helper');
		
		//require the settings and the actions
		require PATH_THIRD.'gmaps/settings.php';
	}

	// ----------------------------------------------------------------------------------
	
	/**
	 * dispatch the actions via ajax or so.
	 * 
	 * @return unknown_type
	 */
	function init ()
	{
		//get the method
		$method = ee()->input->get_post('method');

		//call the method if exists
		if(method_exists($this, $method))
		{
			echo $this->{$method}();
            
            die();
		}

        echo 'no_method';
		exit;
	}

    // ----------------------------------------------------------------------------------

    /**
     * The API action
     *
     * @return unknown_type
     */
    public function api()
    {
        //needed in some cases
        header('Access-Control-Allow-Origin: *');

        //no input
        if(ee()->input->post('input') == '')
        {
            echo 'no_post_value';
            die();
        }

        //no method
        if(!isset($_GET['type']) || $_GET['type'] == '')
        {
            echo 'no_method';
            die();
        }

        //input value
        $input = explode('|', ee()->input->post('input'));

        //result var
        $result = '';

        switch($_GET['type'])
        {
            case 'address':
                $result = ee()->gmaps->geocode_address($input, 'php', 'all');
                break;
            case 'latlng':
                $result = ee()->gmaps->geocode_latlng($input, 'php', 'all');
                break;
            case 'ip':
                $result = ee()->gmaps->geocode_ip($input, 'php', 'all');
                break;
        }
        //echo a json object
        if($result != '')
        {
            echo json_encode($result);
        }
        else
        {
            echo 'no_result';
        }

        exit;
    }

	// ----------------------------------------------------------------------------------

	/**
	 * Create a new map for all the fieldtypes
	 * 
	 * @return unknown_type
	 */
	function gmaps_fieldtype ()
	{
		//load stuff
		ee()->load->helper('form');

        //weird session bug
        $user_vars	= array(
            'member_id', 'group_id', 'group_description', 'group_title', 'username', 'screen_name',
            'email', 'ip_address', 'location', 'total_entries',
            'total_comments', 'private_messages', 'total_forum_posts', 'total_forum_topics', 'total_forum_replies'
        );
        foreach ($user_vars as $user_var)
        {
            if(!isset(ee()->session->userdata[$user_var]))
            {
                ee()->session->userdata[$user_var] = '';
            }
        }

		//save data
        $data = trim(ee()->input->get_post('data'));
        //$zoom = ee()->input->get_post('zoom');
        $max_markers = ee()->input->get_post('max_markers');
        $zoom_level = ee()->input->get_post('zoom_level');
        $show_map_tools = ee()->input->get_post('show_map_tools');
        $show_search_tools = ee()->input->get_post('show_search_tools');
        $show_marker_icon = ee()->input->get_post('show_marker_icon');
        $show_circle_icon = ee()->input->get_post('show_circle_icon');
        $show_polygon_icon = ee()->input->get_post('show_polygon_icon');
        $show_polyline_icon = ee()->input->get_post('show_polyline_icon');
        $show_rectangle_icon = ee()->input->get_post('show_rectangle_icon');
        $location = ee()->input->get_post('location');
        $scroll = ee()->input->get_post('scroll');
        $icon_url = ee()->input->get_post('icon_url');
        $icon_dir = ee()->input->get_post('icon_dir');
        $field_name = ee()->input->get_post('fieldname');
        $field_name_input = ee()->input->get_post('fieldname_input');
        $height = ee()->input->get_post('height') != '' ? ee()->input->get_post('height') : '500px';

        //the tag
        $tag_gmaps = '
            {exp:gmaps:empty_map}
                {width}100%{/width}
                {address}heerde{/address}
                {height}'.$height.'{/height}
            {/exp:gmaps:empty_map}
        ';

        //parse the gmaps
        $parsed_gmaps = ee()->gmaps->parse_channel_data($tag_gmaps);
        $return = '';
        
        //data-zoom="'.$zoom.'"
        $return .= '
        <div  
            id="gmaps_ft_'.ee()->gmaps->get_cache(GMAPS_MAP.'_caller').'"
            class="gmap_holder ee_gmap_'.ee()->gmaps->get_cache(GMAPS_MAP.'_caller').' gmaps_ft_'.ee()->gmaps->get_cache(GMAPS_MAP.'_caller').'"
            data-mapid="ee_gmap_'.ee()->gmaps->get_cache(GMAPS_MAP.'_caller').'" 
            data-gmaps-number="'.ee()->gmaps->get_cache(GMAPS_MAP.'_caller').'" 
            data-fieldname="'.$field_name.'"
            data-fieldname_input="'.$field_name_input.'"
            data-location="'.$location.'"
            data-max-markers="'.$max_markers.'"
            data-zoom-level="'.$zoom_level.'"
            data-show-map-tools="'.$show_map_tools.'"
            data-show-search-tools="'.$show_search_tools.'"
            data-show-marker-icon="'.$show_marker_icon.'"
            data-show-circle-icon="'.$show_circle_icon.'"
            data-show-polygon-icon="'.$show_polygon_icon.'"
            data-show-polyline-icon="'.$show_polyline_icon.'"
            data-show-rectangle-icon="'.$show_rectangle_icon.'"
            data-scroll="'.$scroll.'"
        >

            <div class="alert alert-info alert-block">
                <a class="close" data-dismiss="alert" href="javascript:;">×</a>
                <i class="icon-info-sign"></i> <strong class="txt"></strong>
            </div>
        ';

        if($show_map_tools == 1)
        {
            $return .= '
                <div class="gmaps_controls markers_holder">
                    <ul class="markers">
                        <li class="refresh_map_wrapper">Refresh Map <i class="refresh_map fa fa-refresh"></i></li>
                        <li class="reset_map_wrapper">Reset Map <i class="reset_map fa fa-trash"></i></li>
                        <li class="edit_map_wrapper">Map Settings <i class="edit_map fa fa-edit"></i></li>
                    </ul>
                </div>
            ';
        }

        if($show_search_tools == 1)
        {
            $return .= '   <div class="search_holder"> 
                    <input placeholder="Search location" type="text" class="search_address_input" name="address_'.ee()->gmaps->get_cache(GMAPS_MAP.'_caller').'"/>
                    <button class="submit search_address">Search</button>
                    <div class="markers_holder">
                        <ul class="markers"></ul>
                    </div>
                </div>
                <br />
            ';
        }

        $return .= '  <div style="display:none;" class="markers_holder markers_on_map">
                <h3>Markers</h3>
                <ul class="selected_markers markers sortable"></ul>
            </div>

            <div style="display:none;" class="markers_holder polylines_on_map">
                <h3>Polylines</h3>
                <ul class="selected_polylines polylines"></ul>
            </div>

            <div style="display:none;" class="markers_holder polygons_on_map">
                <h3>Polygons</h3>
                <ul class="selected_polygons polygons"></ul>
            </div>

             <div style="display:none;" class="markers_holder circles_on_map">
                <h3>Circles</h3>
                <ul class="selected_circles circles"></ul>
            </div>

            <div style="display:none;" class="markers_holder rectangles_on_map">
                <h3>Rectangles</h3>
                <ul class="selected_rectangles rectangles"></ul>
            </div>


            <div style="display:none;" id="edit_map_'.ee()->gmaps->get_cache(GMAPS_MAP.'_caller').'" class="dialog " title="Edit Map">
                <div class="form-elem">
                    <label>Available Map Types :</label> 
                    <div class="input map_types" data-name="map_types" data-type="checkbox">
                        hybrid <input checked type="checkbox" value="hybrid" name="map_types[]"/>
                        roadmap <input checked type="checkbox" value="roadmap" name="map_types[]"/>
                        satellite <input checked type="checkbox" value="satellite" name="map_types[]"/>
                        terrain <input checked type="checkbox" value="terrain" name="map_types[]"/>
                    </div>
                </div>
                <div class="form-elem">
                    <label>Default Map Types :</label> 
                    <div class="input map_type" data-name="map_type" data-type="normal">
                        <select class="value" name="map_type">
                            <option value="hybrid">hybrid</option>
                            <option SELECTED value="roadmap">roadmap</option>
                            <option value="satellite">satellite</option>
                            <option value="terrain">terrain</option>
                        </select>
                    </div>
                </div>
                <div class="form-elem">
                    <label>Zoom level :</label> 
                    <div class="input zoom_level" data-name="zoom_level" data-type="normal">
                        <select class="value" name="zoom_level">
                            <option value="0">0</option>
                            <option SELECTED value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                            <option value="13">13</option>
                            <option value="14">14</option>
                            <option value="15">15</option>
                            <option value="16">16</option>
                            <option value="17">17</option>
                            <option value="18">18</option>
                            <option value="19">19</option>
                            <option value="20">20</option>
                            <option value="21">21</option>
                            <option value="22">22</option>
                        </select>
                    </div>
                </div>

                 <div class="form-elem">
                    <label>Scrollwheel :</label> 
                    <div class="input scroll_wheel" data-name="scroll_wheel" data-type="normal">
                        <select class="value" name="scroll_wheel">
                            <option SELECTED value="true">Enabled</option>
                            <option value="false">Disabled</option>
                        </select>
                    </div>
                </div>

                <div class="form-elem">
                    <label>ZoomControl :</label> 
                    <div class="input zoom_control" data-name="zoom_control" data-type="normal">
                        <select class="value" name="zoom_control">
                            <option SELECTED value="true">Enabled</option>
                            <option value="false">Disabled</option>
                        </select>
                    </div>
                </div>
                <div class="form-elem">
                    <label>ZoomControl Style :</label> 
                    <div class="input zoom_control_style" data-name="zoom_control_style" data-type="normal">
                        <select class="value" name="zoom_control_style">
                            <option SELECTED value="LARGE">Large</option>
                            <option value="SMALL">Small</option>
                        </select>
                    </div>
                </div>
                <div class="form-elem">
                    <label>ZoomControl Position :</label> 
                    <div class="input zoom_control_position" data-name="zoom_control_position" data-type="normal">
                        <select class="value" name="zoom_control_position">
                            <option value="TOP_CENTER">top center</option>
                            <option SELECTED value="TOP_LEFT">top left</option>
                            <option value="TOP_RIGHT">top right</option>
                            <option value="LEFT_TOP">left top</option>
                            <option value="RIGHT_TOP">right top</option>
                            <option value="LEFT_CENTER">left center</option>
                            <option value="RIGHT_CENTER">right center</option>
                            <option value="LEFT_BOTTOM">left bottom</option>
                            <option value="RIGHT_BOTTOM">right bottom</option>
                            <option value="BOTTOM_CENTER">bottom center</option>
                            <option value="BOTTOM_LEFT">bottom left</option>
                            <option value="BOTTOM_RIGHT">bottom right</option>
                        </select>
                    </div>
                </div>

                <div class="form-elem">
                    <label>PanControl :</label> 
                    <div class="input pan_control" data-name="pan_control" data-type="normal">
                        <select class="value" name="pan_control">
                            <option SELECTED value="true">Enabled</option>
                            <option value="false">Disabled</option>
                        </select>
                    </div>
                </div>
                <div class="form-elem">
                    <label>PanControl Position :</label> 
                    <div class="input pan_control_position" data-name="pan_control_position" data-type="normal">
                        <select class="value" name="pan_control_position">
                            <option value="TOP_CENTER">top center</option>
                            <option SELECTED value="TOP_LEFT">top left</option>
                            <option value="TOP_RIGHT">top right</option>
                            <option value="LEFT_TOP">left top</option>
                            <option value="RIGHT_TOP">right top</option>
                            <option value="LEFT_CENTER">left center</option>
                            <option value="RIGHT_CENTER">right center</option>
                            <option value="LEFT_BOTTOM">left bottom</option>
                            <option value="RIGHT_BOTTOM">right bottom</option>
                            <option value="BOTTOM_CENTER">bottom center</option>
                            <option value="BOTTOM_LEFT">bottom left</option>
                            <option value="BOTTOM_RIGHT">bottom right</option>
                        </select>
                    </div>
                </div>

                <div class="form-elem">
                    <label>MapTypeControl :</label> 
                    <div class="input map_type_control" data-name="map_type_control" data-type="normal">
                        <select class="value" name="map_type_control">
                            <option SELECTED value="true">Enabled</option>
                            <option value="false">Disabled</option>
                        </select>
                    </div>
                </div>
                <div class="form-elem">
                    <label>MapTypeControl Style :</label> 
                    <div class="input map_type_control_style" data-name="map_type_control_style" data-type="normal">
                        <select class="value" name="map_type_control_style">
                            <option SELECTED value="DEFAULT">Default</option>
                            <option value="DROPDOWN_MENU">Dropdown menu</option>
                            <option value="HORIZONTAL_BAR">Horizontal bar</option>
                        </select>
                    </div>
                </div>
                <div class="form-elem">
                    <label>MapTypeControl Position :</label> 
                    <div class="input map_type_control_position" data-name="map_type_control_position" data-type="normal">
                        <select class="value" name="map_type_control_position">
                            <option value="TOP_CENTER">top center</option>
                            <option value="TOP_LEFT">top left</option>
                            <option SELECTED value="TOP_RIGHT">top right</option>
                            <option value="LEFT_TOP">left top</option>
                            <option value="RIGHT_TOP">right top</option>
                            <option value="LEFT_CENTER">left center</option>
                            <option value="RIGHT_CENTER">right center</option>
                            <option value="LEFT_BOTTOM">left bottom</option>
                            <option value="RIGHT_BOTTOM">right bottom</option>
                            <option value="BOTTOM_CENTER">bottom center</option>
                            <option value="BOTTOM_LEFT">bottom left</option>
                            <option value="BOTTOM_RIGHT">bottom right</option>
                        </select>
                    </div>
                </div>

                <div class="form-elem">
                    <label>StreetViewControl :</label> 
                    <div class="input street_view_control" data-name="street_view_control" data-type="normal">
                        <select class="value" name="street_view_control">
                            <option SELECTED value="true">Enabled</option>
                            <option value="false">Disabled</option>
                        </select>
                    </div>
                </div>
                <div class="form-elem">
                    <label>StreetViewControl Position :</label> 
                    <div class="input street_view_control_position" data-name="street_view_control_position" data-type="normal">
                        <select class="value" name="street_view_control_position">
                            <option value="TOP_CENTER">top center</option>
                            <option SELECTED value="TOP_LEFT">top left</option>
                            <option value="TOP_RIGHT">top right</option>
                            <option value="LEFT_TOP">left top</option>
                            <option value="RIGHT_TOP">right top</option>
                            <option value="LEFT_CENTER">left center</option>
                            <option value="RIGHT_CENTER">right center</option>
                            <option value="LEFT_BOTTOM">left bottom</option>
                            <option value="RIGHT_BOTTOM">right bottom</option>
                            <option value="BOTTOM_CENTER">bottom center</option>
                            <option value="BOTTOM_LEFT">bottom left</option>
                            <option value="BOTTOM_RIGHT">bottom right</option>
                        </select>
                    </div>
                </div>


                <div class="form-elem">
                    <label>Google Overlay :</label> <div class="input google_overlay_html" data-name="google_overlay_html" data-type="textarea"><textarea name="google_overlay_html" class="value"></textarea></div>
                </div>
                <div class="form-elem">
                    <label>Overlay position :</label> 
                    <div class="input google_overlay_position" data-name="google_overlay_position" data-type="normal">
                        <select class="value" name="google_overlay_position">
                            <option value="right">Right</option>
                            <option SELECTED value="left">Left</option>
                        </select>
                    </div>
                </div>
            </div>

            <div style="display:none;" id="edit_marker_'.ee()->gmaps->get_cache(GMAPS_MAP.'_caller').'" class="dialog " title="Edit Marker">
               
                <div class="form-elem">
                    <label>Title :</label> <div class="input"><input type="text" name="marker_title"/></div>
                </div>
                <div class="form-elem">
                    <label>Icon :</label> <div class="input">'.ee()->gmaps->set_icon_options($icon_url, $icon_dir).'</div>
                </div>
                <!--<div class="circle">
                    <div class="form-elem">
                        <label>Circle :</label> <div class="input"><input type="checkbox" value="yes" name="circle"/></div>
                    </div>
                    <div class="form-elem hidden options">
                        <label>Circle Radius :</label> <div class="input"><input type="text" name="circle_radius"/></div>
                    </div>
                </div>-->
                <div class="form-elem">
                    <label>Text :</label> <div class="input"><textarea name="marker_infowindow"></textarea></div>
                </div>
            </div>

            <div style="display:none;" id="edit_polyline_'.ee()->gmaps->get_cache(GMAPS_MAP.'_caller').'" class="dialog " title="Edit Polyline">
                <div class="form-elem">
                    <label>Stroke Color :</label> <div class="input"><input type="text" name="polyline_strokecolor"/></div>
                </div>
                <div class="form-elem">
                    <label>Stroke Opacity :</label> <div class="input"><input type="text" name="polyline_opacity"/></div>
                </div>
                <div class="form-elem">
                    <label>Stroke Weight :</label> <div class="input"><input type="text" name="polyline_weight"/></div>
                </div>
            </div>

            <div style="display:none;" id="edit_polygon_'.ee()->gmaps->get_cache(GMAPS_MAP.'_caller').'" class="dialog " title="Edit Polygon">
                <div class="form-elem">
                    <label>Stroke Color :</label> <div class="input"><input type="text" name="polygon_strokecolor"/></div>
                </div>
                <div class="form-elem">
                    <label>Stroke Opacity :</label> <div class="input"><input type="text" name="polygon_opacity"/></div>
                </div>
                <div class="form-elem">
                    <label>Stroke Weight :</label> <div class="input"><input type="text" name="polygon_weight"/></div>
                </div>
                <div class="form-elem">
                    <label>Fill Color :</label> <div class="input"><input type="text" name="polygon_fillcolor"/></div>
                </div>
                <div class="form-elem">
                    <label>Fill Opacity :</label> <div class="input"><input type="text" name="polygon_fillopacity"/></div>
                </div>
            </div>

            <div style="display:none;" id="edit_circle_'.ee()->gmaps->get_cache(GMAPS_MAP.'_caller').'" class="dialog " title="Edit Circle">
               <div class="form-elem">
                    <label>Stroke Color :</label> <div class="input"><input type="text" name="circle_strokecolor"/></div>
                </div>
                <div class="form-elem">
                    <label>Stroke Opacity :</label> <div class="input"><input type="text" name="circle_opacity"/></div>
                </div>
                <div class="form-elem">
                    <label>Stroke Weight :</label> <div class="input"><input type="text" name="circle_weight"/></div>
                </div>
                <div class="form-elem">
                    <label>Fill Color :</label> <div class="input"><input type="text" name="circle_fillcolor"/></div>
                </div>
                <div class="form-elem">
                    <label>Fill Opacity :</label> <div class="input"><input type="text" name="circle_fillopacity"/></div>
                </div>
                <div class="form-elem">
                    <label>Radius :</label> <div class="input"><input type="text" name="circle_radius"/></div>
                </div>
            </div>

            <div style="display:none;" id="edit_rectangle_'.ee()->gmaps->get_cache(GMAPS_MAP.'_caller').'" class="dialog " title="Edit Rectangle">
                <div class="form-elem">
                    <label>Stroke Color :</label> <div class="input"><input type="text" name="rectangle_strokecolor"/></div>
                </div>
                <div class="form-elem">
                    <label>Stroke Opacity :</label> <div class="input"><input type="text" name="rectangle_opacity"/></div>
                </div>
                <div class="form-elem">
                    <label>Stroke Weight :</label> <div class="input"><input type="text" name="rectangle_weight"/></div>
                </div>
                <div class="form-elem">
                    <label>Fill Color :</label> <div class="input"><input type="text" name="rectangle_fillcolor"/></div>
                </div>
                <div class="form-elem">
                    <label>Fill Opacity :</label> <div class="input"><input type="text" name="rectangle_fillopacity"/></div>
                </div>
            </div>

        '.$parsed_gmaps.'
        '.form_input($field_name_input, $data, 'id="'.$field_name.'" style="display: none;"').'
        </div>
        ';

        //init the js
        //ee()->cp->add_to_foot(' <script>$(window).load(function(){init_gmaps('.ee()->session->userdata(GMAPS_MAP.'_caller').');});</script>');

        //return data
        return base64_encode(json_encode(array('map' => $return, 'map_nr' => ee()->gmaps->get_cache(GMAPS_MAP.'_caller'))));
	}

	// ----------------------------------------------------------------------------------
}