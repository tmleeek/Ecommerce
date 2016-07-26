/*
 * EE_GMaps.js
 * http://reinos.nl
 *
 * Map types (Toner, Terrain and Watercolor) are Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under CC BY SA
 *
 * @package            Gmaps for EE2
 * @author             Rein de Vries (info@reinos.nl)
 * @copyright          Copyright (c) 2013 Rein de Vries
 * @license 		   http://reinos.nl/commercial-license
 * @link               http://reinos.nl/add-ons/gmaps
 */
;
var EE_GMAPS = EE_GMAPS || {};


//check if jQuery is loaded
EE_GMAPS.jqueryLoaded = function(){
    if (typeof jQuery == 'undefined') {
        console.info('GMAPS ERROR: jQuery is not loaded. Make sure Jquery is loaded before Gmaps is called');
    }
}();

(function ($) {

    //default lat lng values
    EE_GMAPS.def = {};
    EE_GMAPS.vars = {}; //default vars, dynamic created by this file
    EE_GMAPS.def.lat = EE_GMAPS.def.Lat = -12.043333;
    EE_GMAPS.def.lng = EE_GMAPS.def.Lng = -77.028333;
    EE_GMAPS.def.circle = {
        'fit_circle': true,
        'stroke_color': '#BBD8E9',
        'stroke_opacity': 1,
        'stroke_weight': 3,
        'fill_color': '#BBD8E9',
        'fill_opacity': 0.6,
        'radius': 1000
    };

    //create the diff types of arrays
    var arrayTypes = ['polylines', 'polygons', 'circles', 'rectangles', 'markers'];
    $.each(arrayTypes, function (k, v) {
        EE_GMAPS[v] = [];
    });

    //marker holder
    EE_GMAPS.markers_address_based = {};
    EE_GMAPS.markers_key_based = {};

    //latlng holder
    EE_GMAPS.latlngs = [];

    //the map
    EE_GMAPS._map_ = [];

    //fitMap default to false
    EE_GMAPS.fitTheMap = false;

    //ready function, when this file is totally ready
    var funcList = [];
    EE_GMAPS.runAll = function () {
        var len = funcList.length,
            index = 0;

        for (; index < len; index++)
            funcList[index].call(); // you can pass in a "this" parameter here.
    };
    EE_GMAPS.ready = function (inFunc) {
        funcList.push(inFunc);
    };

    EE_GMAPS.cacheLists = function(lists) {
        $.each(lists, function(i, name) {
            var list = google.maps[name];
            var iList = {};
            $.each(list, function(k, v) {
                iList[v] = k;
            });

            EE_GMAPS[name] = list;
            EE_GMAPS[name + 'I'] = iList;
        });
    };

    //cache the Google maps options and also reverse them
    EE_GMAPS.cacheLists(['MapTypeControlStyle', 'ControlPosition']);

    //get latlong based on address
    EE_GMAPS.setGeocoding = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'map_type': '',
            'map_types': [],
            'input_address': [],
            'address': [],
            'latlng': [],
            'keys': [],
            'zoom': '',
            'zoom_override': false,
            'center': '',
            'width': '',
            'height': '',
            'loader': '',
            'marker': [],
            'static': true,
            'scroll_wheel': true,
            'zoom_control': true,
            'zoom_control_style' : '',
            'zoom_control_position' : '',
            'pan_control': true,
            'pan_control_position' : '',
            'map_type_control': true,
            'map_type_control_style' : '',
            'map_type_control_position' : '',
            'scale_control': true,
            'street_view_control': true,
            'street_view_control_position' : '',
            'show_elevation': false,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            //circle specific
            'circle': {
                'circle': [],
                'fit_circle': true,
                'stroke_color': options.stroke_color,
                'stroke_opacity': options.stroke_opacity,
                'stroke_weight': options.stroke_weight,
                'fill_color': options.fill_color,
                'fill_opacity': options.fill_opacity,
                'radius': options.radius
            },
            //end circle
            'hidden_div': '',
            'enable_new_style': true,
            'overlay_html' : '',
            'overlay_position' : ''
        }, options);

        //turn back the address, input_address and latlng
        options.address = EE_GMAPS.parseToJsArray(options.address);
        options.input_address = EE_GMAPS.parseToJsArray(options.input_address);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng);
        options.keys = EE_GMAPS.parseToJsArray(options.keys);
        options.center = EE_GMAPS.parseToJsArray(options.center);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //is this a static map?
        if (options.static) {
            EE_GMAPS.setStaticMap({
                'selector': options.selector,
                'latlng': options.latlng,
                'map_type': options.map_type,
                'width': options.width.match('%') ? '' : options.width,
                'height': options.height.match('%') ? '' : options.height,
                'zoom': options.zoom,
                'marker': options.marker.show
            });
            return true;
        }

        var circle = [];
        var circleBounds = new google.maps.LatLngBounds();

        //create latlng object
        var latlng = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.latlng));

        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            zoom: options.zoom,
            lat: latlng[0].lat(),
            lng: latlng[0].lng(),
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //todo add examples/overlay_map_types.html

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map, {
            tag: options.panoramio_tag
        });

        //set the marker
        var marker_icon,
            marker_title;
        //var address;

        //loop through the address
        $.each(latlng, function (k, v) {

            var address = options.address[k] ? options.address[k] : '';
            var location = options.address[k] ? options.address[k] : v.toString().replace('(', '').replace(')', '');

            //set the title
            if (options.marker.show_title) {
                marker_title = options.marker.title[k] == undefined ? location : options.marker.title[k];
            } else {
                marker_title = null;
            }

            //place marker
            if (options.marker.show) {

                marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon, options.marker.icon_default, k);
                marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow, options.marker.icon_default_shadow, k);
                marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape, options.marker.icon_shape_default, k);

                //set the custom marker
                /*if(options.marker_icon.length > 0) {
					marker_icon = options.marker_icon[k] ? options.marker_icon[k] : options.marker_icon_default;
				} else {
					marker_icon = options.marker_icon_default;
				}*/

                //get the elevation
                if (options.show_elevation) {
                    map.getElevations({
                        locations: EE_GMAPS.createlatLngArray([v]),
                        callback: function (result, status) {
                            if (status == google.maps.ElevationStatus.OK) {

                                //tmp var for the content
                                var tmp_content;

                                //custom marker
                                if (options.marker.custom_html.length > 0) {

                                    //set the content
                                    tmp_content = EE_GMAPS.setInfowindowContent(options.marker.custom_html[k], {
                                        'elevation': result[0].elevation.toFixed(2),
                                        'location': location
                                    }, v);
                                    //tmp_content = options.marker.custom_html[k] ? options.marker.custom_html[k].replace('[elevation]', result[0].elevation.toFixed(2)).replace('[location]', location) : '';

                                    //draw overlay
                                    map.drawOverlay({
                                        lat: v.lat(),
                                        lng: v.lng(),
                                        content: tmp_content,
                                        verticalAlign: options.marker.custom_html_vertical_align,
                                        horizontalAlign: options.marker.custom_html_vertical_align
                                    });

                                    //aslo add marker
                                    if(options.marker.custom_html_show_marker) {
                                        map.addMarker(EE_GMAPS.cleanObject({
                                            lat: v.lat(),
                                            lng: v.lng(),
                                            title: marker_title,
                                            label: options.marker.label[k] != undefined ? options.marker.label[k] : null,
                                            icon: marker_icon,
                                            shadow: marker_icon_shadow,
                                            shape: marker_icon_shape,
                                            animation: options.marker.animation ? google.maps.Animation.DROP : null
                                        }));
                                    }
                                } else {

                                    //set the content
                                    if (options.marker.html.length > 0) {

                                        tmp_marker_html_nr = options.marker.html[k] ? k : 0;

                                        //address set?
                                        if (options.address[k]) {

                                            tmp_content = EE_GMAPS.setInfowindowContent(options.marker.html[tmp_marker_html_nr], {
                                                'elevation': result[0].elevation.toFixed(2),
                                                'location': location
                                            }, v);

                                            //check if there is a default html content
                                            if(tmp_content === null) {
                                                tmp_content = EE_GMAPS.setInfowindowContent(options.marker.html_default, {
                                                    'elevation': result[0].elevation.toFixed(2),
                                                    'location': location
                                                }, v);
                                            }

                                            //tmp_content = options.marker.html[k] ? 
                                            //options.marker.html[k].replace('[elevation]', result[0].elevation.toFixed(2)).replace('[location]', location) : 
                                            //options.marker.html[0].replace('[elevation]', result[0].elevation.toFixed(2)).replace('[location]', location);
                                        } else {

                                            tmp_content = EE_GMAPS.setInfowindowContent(options.marker.html[tmp_marker_html_nr], {
                                                'elevation': result[0].elevation.toFixed(2)
                                            }, v);

                                            //check if there is a default html content
                                            if(tmp_content === null) {
                                                tmp_content = EE_GMAPS.setInfowindowContent(options.marker.html_default, {
                                                    'elevation': result[0].elevation.toFixed(2)
                                                }, v);
                                            }

                                            //tmp_content = options.marker.html[k] ? 
                                            //options.marker.html[k].replace('[elevation]', result[0].elevation.toFixed(2)) : 
                                            //options.marker.html[0].replace('[elevation]', result[0].elevation.toFixed(2));
                                        }
                                    }

                                    //Add marker
                                    map.addMarker(EE_GMAPS.cleanObject({
                                        lat: v.lat(),
                                        lng: v.lng(),
                                        title: marker_title,
                                        label: options.marker.label[k] != undefined ? options.marker.label[k] : null,
                                        icon: marker_icon,
                                        shadow: marker_icon_shadow,
                                        shape: marker_icon_shape,
                                        animation: options.marker.animation ? google.maps.Animation.DROP : null,
                                        infoWindow: {
                                            content: tmp_content
                                        }
                                    }));

                                    //set the infobox
                                    EE_GMAPS.addInfobox(options, map, map.markers[k], k);
                                }

                                //open first marker infowindow when there is one marker
                                if (latlng.length == 1 && options.marker.open_by_default) {
                                    google.maps.event.trigger(map.markers[0], 'click');
                                }
                            }
                        }
                    });
                } else {
                    //custom marker
                    if (options.marker.custom_html.length > 0) {
                        map.drawOverlay({
                            lat: v.lat(),
                            lng: v.lng(),
                            content: EE_GMAPS.setInfowindowContent(options.marker.custom_html[k], {
                                'location': location
                            }, v),
                            //options.marker.custom_html[k] ? options.marker.custom_html[k].replace('[location]', location) : '',
                            verticalAlign: options.marker.custom_html_vertical_align,
                            horizontalAlign: options.marker.custom_html_vertical_align
                        });

                        //also add marker
                        if(options.marker.custom_html_show_marker) {
                            map.addMarker(EE_GMAPS.cleanObject({
                                lat: v.lat(),
                                lng: v.lng(),
                                title: marker_title,
                                label: options.marker.label[k] != undefined ? options.marker.label[k] : null,
                                icon: marker_icon,
                                shadow: marker_icon_shadow,
                                shape: marker_icon_shape,
                                animation: options.marker.animation ? google.maps.Animation.DROP : null
                            }));
                        }
                    } else {

                        //set the html content
                        var html_content = EE_GMAPS.setInfowindowContent(options.marker.html[k], {
                            'location': location
                        }, v);

                        //check if there is a default html content
                        if(html_content === null) {
                            html_content = EE_GMAPS.setInfowindowContent(options.marker.html_default, {
                                'location': location
                            }, v);
                        }

                        map.addMarker(EE_GMAPS.cleanObject({
                            lat: v.lat(),
                            lng: v.lng(),
                            title: marker_title,
                            label: options.marker.label[k] != undefined ? options.marker.label[k] : null,
                            icon: marker_icon,
                            shadow: marker_icon_shadow,
                            shape: marker_icon_shape,
                            animation: options.marker.animation ? google.maps.Animation.DROP : null,
                            infoWindow: {
                                content: html_content
                            }
                        }));

                        //set the infobox
                        EE_GMAPS.addInfobox(options, map, map.markers[k], k);

                        //open first marker infowindow when there is one marker
                        if (latlng.length == 1 && options.marker.open_by_default) {
                            google.maps.event.trigger(map.markers[0], 'click');
                        }
                    }
                }
            }

            //set the circle
            if (options.circle.circle[k] || options.circle.circle[0] == 'all') {

                //create the circle
                circle[k] = map.drawCircle({
                    strokeColor: options.circle.stroke_color[k] ? options.circle.stroke_color[k] : EE_GMAPS.def.circle.stroke_color,
                    strokeOpacity: options.circle.stroke_opacity[k] ? options.circle.stroke_opacity[k] : EE_GMAPS.def.circle.stroke_opacity,
                    strokeWeight: options.circle.stroke_weight[k] ? options.circle.stroke_weight[k] : EE_GMAPS.def.circle.stroke_weight,
                    fillColor: options.circle.fill_color[k] ? options.circle.fill_color[k] : EE_GMAPS.def.circle.fill_color,
                    fillOpacity: options.circle.fill_opacity[k] ? options.circle.fill_opacity[k] : EE_GMAPS.def.circle.fill_opacity,
                    radius: options.circle.radius[k] ? parseInt(options.circle.radius[k]) : EE_GMAPS.def.circle.radius,
                    lat: v.lat(),
                    lng: v.lng()
                });

                //set the bounds for the circle
                circleBounds.union(circle[k].getBounds());
            }
        });

        // Clustering
        // @todo build it with the gmaps.js /examples/marker_clusterer.html
        if (options.marker.show_cluster) {
            var markerCluster = new MarkerClusterer(map.map, map.markers, {
                gridSize: options.marker.cluster_grid_size,
                maxZoom: 10,
                styles: options.marker.cluster_style,
                imagePath: EE_GMAPS.theme_path+'images/cluster/m'
            });
        }

        //fit the map
        if (circle.length > 0) {
            //fit the map
            if (options.circle.fit_circle) {
                map.fitBounds(circleBounds);
            }
        } else {
            //override center by setting the center or zoom level
            if (options.center != undefined && options.center != '') {
                options.center = options.center.toString().split(',');

                var center = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray([options.center]));

                map.setCenter(center[0].lat(), center[0].lng());
                map.setZoom(options.zoom);

                //zoom override
            } else if (options.zoom_override) {
                map.setZoom(options.zoom);

                //default
            } else if (latlng.length > 1) {
                map.fitLatLngBounds(latlng);
            }
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers, options.input_address, options.keys);
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setGeolocation = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'width': '',
            'zoom': '',
            'map_type': '',
            'map_types': [],
            'height': '',
            'marker': [],
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            zoom: 1,
            lat: EE_GMAPS.def.lat,
            lng: EE_GMAPS.def.lng,
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        var marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon);
        var marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow);
        var marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape);

        GMaps.geolocate({
            success: function (position) {
                map.setCenter(position.coords.latitude, position.coords.longitude);
                map.setZoom(options.zoom);

                //reverse geocode
                GMaps.geocode({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    callback: function (e) {
                        //place marker
                        if (options.marker.show) {
                            if (options.marker.custom_html != '') {
                                map.drawOverlay({
                                    lat: position.coords.latitude,
                                    lng: position.coords.longitude,
                                    content: EE_GMAPS.setInfowindowContent(options.marker.custom_html, {
                                        'location': e[0].formatted_address
                                    }, e[0].geometry.location),
                                    //options.marker.custom_html ? options.marker.custom_html.replace('[location]', e[0].formatted_address) : '',
                                    verticalAlign: options.marker.custom_html_vertical_align,
                                    horizontalAlign: options.marker.custom_html_vertical_align
                                });

                                //also add a marker
                                if(options.marker.custom_html_show_marker) {
                                    map.addMarker(EE_GMAPS.cleanObject({
                                        lat: position.coords.latitude,
                                        lng: position.coords.longitude,
                                        icon: marker_icon,
                                        shadow: marker_icon_shadow,
                                        shape: marker_icon_shape,
                                        animation: options.marker.animation ? google.maps.Animation.DROP : null,
                                        title: options.marker.show_title ? e[0].formatted_address : null
                                    }));
                                }

                            } else {

                                //set the html content
                                var html_content = EE_GMAPS.setInfowindowContent(options.marker.html, {
                                    'location': e[0].formatted_address
                                }, e[0].geometry.location);

                                //check if there is a default html content
                                if(html_content === null) {
                                    html_content = EE_GMAPS.setInfowindowContent(options.marker.html_default, {
                                        'location': e[0].formatted_address
                                    }, e[0].geometry.location);
                                }

                                map.addMarker(EE_GMAPS.cleanObject({
                                    lat: position.coords.latitude,
                                    lng: position.coords.longitude,
                                    icon: marker_icon,
                                    shadow: marker_icon_shadow,
                                    shape: marker_icon_shape,
                                    animation: options.marker.animation ? google.maps.Animation.DROP : null,
                                    title: options.marker.show_title ? e[0].formatted_address : null,
                                    infoWindow: {
                                        content: html_content
                                    }
                                }));

                                //set the infobox
                                EE_GMAPS.addInfobox(options, map, map.markers[0], 0);

                                //open the popup by default
                                if (options.marker.open_by_default) {
                                    google.maps.event.trigger(map.markers[0], 'click');
                                }
                            }
                        }
                    }
                });
            },
            error: function (error) {
                alert('Geolocation failed: ' + error.message);
            },
            not_supported: function () {
                alert("Your browser does not support geolocation");
            },
            always: function () {
                //alert("Done!");
            }
        });

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //Route address
    EE_GMAPS.setRoute = function (options) {

        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'from_address': '',
            'from_latlng': '',
            'to_address': '',
            'to_latlng': '',
            'stops_addresses': '',
            'stops_latlng': '',
            'map_type': '',
            'map_types': [],
            'travel_mode': "driving",
            'departure_time': new Date(),
            'arrival_time': null,
            'stroke_color': "#131540",
            'stroke_opacity': 0.6,
            'stroke_weight': 6,
            'marker': [],
            'width': '',
            'height': '',
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'show_details': false,
            'show_details_per_step': false,
            'details_per_step_template': '',
            'details_template': '',
            'show_elevation': false,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.from_address = EE_GMAPS.parseToJsArray(options.from_address, false);
        options.from_latlng = EE_GMAPS.parseToJsArray(options.from_latlng, false);
        options.to_address = EE_GMAPS.parseToJsArray(options.to_address, false);
        options.to_latlng = EE_GMAPS.parseToJsArray(options.to_latlng, false);
        options.stops_addresses = EE_GMAPS.parseToJsArray(options.stops_addresses);
        options.stops_latlng = EE_GMAPS.parseToJsArray(options.stops_latlng);

        //set map 
        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            lat: EE_GMAPS.def.lat,
            lng: EE_GMAPS.def.lng,
            zoom: 1,
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        //create the latlng object
        var from = EE_GMAPS.stringToLatLng(options.from_latlng);
        var stops = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.stops_latlng));
        var to = EE_GMAPS.stringToLatLng(options.to_latlng);

        var latlng = [from].concat(stops);
        latlng.push(to);
        var address_object = [options.from_address].concat(options.stops_addresses);
        address_object.push(options.to_address);

        //cache the locations for pinPoints purpose
        var markers = latlng.gmaps_clone();

        //create waypoints
        var waypoints = EE_GMAPS.createWaypoints(stops);

        //Transit?
        var transitOptions = {
            departureTime: options.departure_time,
            arrivalTime: options.arrival_time
        }

        //set the icons
        var marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon, options.marker.icon_default, 0);
        var marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow, options.marker.icon_default_shadow, 0);
        var marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape, options.marker.icon_shape_default, 0);

        //set the icons
        //var marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon);
        //var marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow);
        //var marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape);

        //draw route
        map.drawSteppedRoute({
            origin: [from.lat(), from.lng()],
            destination: [to.lat(), to.lng()],
            waypoints: waypoints,
            travelMode: options.travel_mode,
            transitOptions: transitOptions,
            strokeColor: options.stroke_color,
            strokeOpacity: options.stroke_opacity,
            strokeWeight: options.stroke_weight,
            start: function (e) {
                //show elevation by chart
                if (options.show_elevation) {

                    google.load("visualization", "1", {
                        packages: ["columnchart"],
                        callback: function () {
                            // Create a new chart in the elevation_chart DIV.
                            var _selector = options.selector + '_chart';
                            $(_selector).width(options.width);
                            chart = new google.visualization.ColumnChart(document.getElementById(_selector.replace('#', '')));

                            var tmp_locations = EE_GMAPS.createlatLngArray(e.overview_path);
                            map.getElevations({
                                path: true,
                                locations: tmp_locations,
                                callback: function (results, status) {
                                    if (status == google.maps.ElevationStatus.OK) {
                                        elevations = results;
                                        // Extract the data from which to populate the chart.
                                        // Because the samples are equidistant, the 'Sample'
                                        // column here does double duty as distance along the
                                        // X axis.
                                        var data = new google.visualization.DataTable();
                                        data.addColumn('string', 'Sample');
                                        data.addColumn('number', 'Elevation');
                                        for (var i = 0; i < results.length; i++) {
                                            data.addRow(['', elevations[i].elevation]);
                                        }
                                        // Draw the chart using the data within its DIV.
                                        chart.draw(data, {
                                            width: options.width,
                                            height: options.height / 2,
                                            legend: 'none',
                                            titleY: 'Elevation (m)',
                                            colors: [options.stroke_color]
                                        });

                                        //add a mouseover to set the new marker on the screen
                                        var mousemarker;
                                        google.visualization.events.addListener(chart, 'onmouseover', function (e) {
                                            if (mousemarker == null) {
                                                if (tmp_locations[e.row] != undefined) {
                                                    mousemarker = map.addMarker(EE_GMAPS.cleanObject({
                                                        lat: tmp_locations[e.row].lat(),
                                                        lng: tmp_locations[e.row].lng()
                                                        //icon : marker_icon,
                                                        //shadow : marker_icon_shadow,
                                                        //shape : marker_icon_shape
                                                    }));
                                                }
                                            } else {
                                                mousemarker.setPosition(elevations[e.row].location);
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    });
                }
            },
            step: function (e, total_steps) {
                
                //show the details
                if (options.show_details) {
                    //set the template
                    var details_per_step_template = options.details_per_step_template;
                    details_per_step_template = details_per_step_template
                        .replace('[instructions]', e.instructions)
                        .replace('[distance]', e.distance.text)
                        .replace('[duration]', e.duration.text);

                    if (e.transit) {
                        //default transit vars
                        details_per_step_template = details_per_step_template
                            .replace('[arrival_stop]', e.transit.arrival_stop)
                            .replace('[arrival_time]', e.transit.arrival_time)
                            .replace('[departure_stop]', e.transit.departure_stop)
                            .replace('[departure_time]', e.transit.departure_time)
                            .replace('[headsign]', e.transit.headsign)
                            .replace('[num_stops]', e.transit.num_stops)
                            .replace('[name]', e.transit.line.name)
                            .replace('[vehicle_icon]', e.transit.line.vehicle.icon)
                            .replace('[vehicle_name]', e.transit.line.vehicle.name)
                            .replace('[vehicle_type]', e.transit.line.vehicle.type);
                    }

                    $(options.selector + '_details_per_step').append('<li style="cursor:pointer;" rel="' + e.step_number + '">' + details_per_step_template + '</li>');
                    if (options.show_details_per_step) {
                        $(options.selector + '_details_per_step li[rel="' + e.step_number + '"]').click(function () {
                            //add active class
                            $(options.selector + '_details_per_step li').removeClass('active');
                            $(this).addClass('active');
                            //center map by fitbounds
                            map.fitLatLngBounds(e.lat_lngs);
                            //remove old markers
                            map.removeMarkers();
                            //add new markers
                            map.addMarker(EE_GMAPS.cleanObject({
                                lat: e.lat_lngs[0].lat(),
                                lng: e.lat_lngs[0].lng(),
                                //icon : marker_icon
                                //shadow : marker_icon_shadow,
                                //shape : marker_icon_shape,
                                animation: options.marker.animation ? google.maps.Animation.DROP : null
                            }));
                            map.addMarker(EE_GMAPS.cleanObject({
                                lat: e.lat_lngs[e.lat_lngs.length - 1].lat(),
                                lng: e.lat_lngs[e.lat_lngs.length - 1].lng(),
                                //icon : marker_icon,
                                //shadow : marker_icon_shadow,
                                //shape : marker_icon_shape,
                                animation: options.marker.animation ? google.maps.Animation.DROP : null
                            }));
                        });
                    }
                }
            },
            end: function (e) {
                //fit the map
                map.fitLatLngBounds(e.overview_path);

                //set the overal information
                if (options.show_details && e.legs[0]) {
                    var details_template = options.details_template;
                    details_template = details_template
                        .replace('[distance]', e.legs[0].distance.text)
                        .replace('[duration]', e.legs[0].duration.text)
                        .replace('[end_address]', e.legs[0].end_address)
                        .replace('[start_address]', e.legs[0].start_address);
                    $(options.selector + '_details').append(details_template);
                }
            },
            error: function(e) {
                console.log('Route cannot be generated');
            }
        });

        //fit the map, this is for the short time. In the end callback is the best fitBounds
        map.fitLatLngBounds(markers);

        //place the markers
        if (options.marker.show) {
            $.each(markers, function (k, v) {

                marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon, options.marker.icon_default, k);
                marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow, options.marker.icon_default_shadow, k);
                marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape, options.marker.icon_shape_default, k);
                //console.log(marker_icon_test, marker_icon_shadow_test, marker_icon_shape_test);

                var location = address_object[k] ? address_object[k] : v.toString().replace('(', '').replace(')', '');
                map.addMarker(EE_GMAPS.cleanObject({
                    lat: v.lat(),
                    lng: v.lng(),
                    title: options.marker.show_title ? location : null,
                    icon: marker_icon,
                    shadow: marker_icon_shadow,
                    shape: marker_icon_shape,
                    animation: options.marker.animation ? google.maps.Animation.DROP : null
                }));
            });
        }

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setPolygon = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'address': '',
            'latlng': '',
            'json': '',
            'map_type': '',
            'map_types': [],
            'width': '',
            'height': '',
            'stroke_color': '',
            'stroke_opacity': '',
            'stroke_weight': '',
            'fill_color': '',
            'fill_opacity': '',
            'marker': [],
            'static': true,
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng);

        //is this a static map?
        if (options.static) {
            EE_GMAPS.setStaticMap({
                'selector': options.selector,
                'latlng': options.latlng,
                'map_type': options.map_type,
                'width': options.width,
                'height': options.height,
                'zoom': options.zoom,
                'marker': options.marker,
                'polygon': true,
                'stroke_color': options.stroke_color,
                'stroke_opacity': options.stroke_opacity,
                'stroke_weight': options.stroke_weight,
                'fill_color': options.fill_color,
                'fill_opacity': options.fill_opacity
            });
            return true;
        }

        //set the icons
        var marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon);
        var marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow);
        var marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape);


        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            lat: EE_GMAPS.def.lat,
            lng: EE_GMAPS.def.lng,
            zoom: 1,
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        //create the latlng
        var latlng = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.latlng));

        //is the latlng filled?
        if (latlng.length > 0) {
            var is_json = false;
            var _polygon = [];
            $.each(latlng, function (k, v) {
                _polygon.push([v.lat(), v.lng()]);
            });

            //We got json?
        } else if (options.json != '') {
            var is_json = true;
            var _polygon = JSON.parse(options.json);
        }

        //create the polygon
        var polygon = map.drawPolygon({
            paths: _polygon, // pre-defined polygon shape
            useGeoJSON: is_json,
            strokeColor: options.stroke_color,
            strokeOpacity: options.stroke_opacity,
            strokeWeight: options.stroke_weight,
            fillColor: options.fill_color,
            fillOpacity: options.fill_opacity
        });

        //fit the map
        //map.fitLatLngBounds(EE_GMAPS.flatten_polygon_result(polygon.getPaths()));
        map.fitBounds(polygon.getBounds());

        //place marker
        if (options.marker.show) {
            $.each(latlng, function (k, v) {
                var location = options.address[k] ? options.address[k] : v.toString().replace('(', '').replace(')', '');
                map.addMarker(EE_GMAPS.cleanObject({
                    lat: v.lat(),
                    lng: v.lng(),
                    title: options.marker.show_title ? location : null,
                    icon: marker_icon,
                    shadow: marker_icon_shadow,
                    shape: marker_icon_shape,
                    animation: options.marker.animation ? google.maps.Animation.DROP : null
                }));
            });
        }

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setPolyline = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'address': '',
            'latlng': '',
            'json': '',
            'map_type': '',
            'map_types': [],
            'width': '',
            'height': '',
            'stroke_color': '',
            'stroke_opacity': '',
            'stroke_weight': '',
            'marker': [],
            'static': true,
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng);

        //is this a static map?
        if (options.static) {
            EE_GMAPS.setStaticMap({
                'selector': options.selector,
                'latlng': options.latlng,
                'map_type': options.map_type,
                'width': options.width,
                'height': options.height,
                'zoom': options.zoom,
                'marker': options.marker,
                'polygon': true,
                'stroke_color': options.stroke_color,
                'stroke_opacity': options.stroke_opacity,
                'stroke_weight': options.stroke_weight,
                'fill_color': options.fill_color,
                'fill_opacity': options.fill_opacity
            });
            return true;
        }

        //set the icons
        var marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon);
        var marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow);
        var marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape);

        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            lat: EE_GMAPS.def.lat,
            lng: EE_GMAPS.def.lng,
            zoom: 1,
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        //create the latlng
        var latlng = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.latlng));

        //set the polyline
        var _polyline = [];
        $.each(latlng, function (k, v) {
            _polyline.push([v.lat(), v.lng()]);
        });

        //create the polyline
        var polyline = map.drawPolyline({
            path: _polyline, // pre-defined polyline shape
            strokeColor: options.stroke_color,
            strokeOpacity: options.stroke_opacity,
            strokeWeight: options.stroke_weight
        });

        //fit the map
        //map.fitLatLngBounds(EE_GMAPS.flatten_polygon_result(polygon.getPaths()));
        map.fitBounds(polyline.getBounds());

        //place marker
        if (options.marker.show) {
            $.each(latlng, function (k, v) {
                var location = options.address[k] ? options.address[k] : v.toString().replace('(', '').replace(')', '');
                map.addMarker(EE_GMAPS.cleanObject({
                    lat: v.lat(),
                    lng: v.lng(),
                    title: options.marker.show_title ? location : null,
                    icon: marker_icon,
                    shadow: marker_icon_shadow,
                    shape: marker_icon_shape,
                    animation: options.marker.animation ? google.maps.Animation.DROP : null
                }));
            });
        }

        //set some vars
        EE_GMAPS.vars.polylineLenght = google.maps.geometry.spherical.computeLength(polyline.getPath());

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setCircle = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'address': '',
            'latlng': '',
            'map_type': '',
            'map_types': [],
            'width': '',
            'height': '',
            'marker': [],
            'zoom': '',
            //circle specific
            'fit_circle': '',
            'stroke_color': options.stroke_color,
            'stroke_opacity': options.stroke_opacity,
            'stroke_weight': options.stroke_weight,
            'fill_color': options.fill_color,
            'fill_opacity': options.fill_opacity,
            'radius': options.radius,

            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        options.address = options.address ? options.address : '';

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address, false);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng, false);

        //create latlng object
        var latlng = EE_GMAPS.stringToLatLng(options.latlng);

        //set the icons
        var marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon);
        var marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow);
        var marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape);

        //create the map	
        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            lat: latlng.lat(),
            lng: latlng.lng(),
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            zoom: options.zoom,
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        //create the circle
        var circle = map.drawCircle({
            strokeColor: options.stroke_color,
            strokeOpacity: options.stroke_opacity,
            strokeWeight: options.stroke_weight,
            fillColor: options.fill_color,
            fillOpacity: options.fill_opacity,
            radius: options.radius,
            lat: latlng.lat(),
            lng: latlng.lng()
        });

        //fit the map
        if (options.fit_circle) {
            map.fitBounds(circle.getBounds());
        }

        //place marker 
        if (options.marker.show) {
            var location = options.address ? options.address : latlng.toString().replace('(', '').replace(')', '');
            if (options.marker.custom_html != '') {
                map.drawOverlay({
                    lat: latlng.lat(),
                    lng: latlng.lng(),
                    content: EE_GMAPS.setInfowindowContent(options.marker.custom_html, {
                        'location': location
                    }, latlng),
                    //options.marker.custom_html ? options.marker.custom_html.replace('[location]', location) : '',
                    verticalAlign: options.marker.custom_html_vertical_align,
                    horizontalAlign: options.marker.custom_html_vertical_align
                });

                //also add a marker
                if(options.marker.custom_html_show_marker) {
                    map.addMarker(EE_GMAPS.cleanObject({
                        lat: latlng.lat(),
                        lng: latlng.lng(),
                        icon: marker_icon,
                        shadow: marker_icon_shadow,
                        shape: marker_icon_shape,
                        animation: options.marker.animation ? google.maps.Animation.DROP : null,
                        title: options.marker.show_title ? location : null,
                        label: options.marker.label[0] != undefined ? options.marker.label[0] : null,
                    }));
                }

            } else {

                //set the html content
                var html_content = EE_GMAPS.setInfowindowContent(options.marker.html, {
                    'location': location
                }, latlng);

                //check if there is a default html content
                if(html_content === null) {
                    html_content = EE_GMAPS.setInfowindowContent(options.marker.html_default, {
                        'location': location
                    }, latlng);
                }

                map.addMarker(EE_GMAPS.cleanObject({
                    lat: latlng.lat(),
                    lng: latlng.lng(),
                    icon: marker_icon,
                    shadow: marker_icon_shadow,
                    shape: marker_icon_shape,
                    animation: options.marker.animation ? google.maps.Animation.DROP : null,
                    title: options.marker.show_title ? location : null,
                    label: options.marker.label[0] != undefined ? options.marker.label[0] : null,
                    infoWindow: {
                        content: html_content
                    }
                }));

                //set the infobox
                EE_GMAPS.addInfobox(options, map, map.markers[0], 0);

                //open the popup by default
                if (options.marker.open_by_default) {
                    google.maps.event.trigger(map.markers[0], 'click');
                }
            }
        }

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setRectangle = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'address': '',
            'latlng': '',
            'map_type': '',
            'map_types': [],
            'width': '',
            'height': '',
            'zoom': '',
            'stroke_color': options.stroke_color,
            'stroke_opacity': options.stroke_opacity,
            'stroke_weight': options.stroke_weight,
            'fill_color': options.fill_color,
            'fill_opacity': options.fill_opacity,
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng);

        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            lat: EE_GMAPS.def.lat,
            lng: EE_GMAPS.def.lng,
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            zoom: 1,
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //create latlng object
        var latlng = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.latlng));

        //create rectangle
        var rectangle = map.drawRectangle({
            bounds: [
                [latlng[0].lat(), latlng[0].lng()],
                [latlng[1].lat(), latlng[1].lng()]
            ],
            strokeColor: options.stroke_color,
            strokeOpacity: options.stroke_opacity,
            strokeWeight: options.stroke_weight,
            fillColor: options.fill_color,
            fillOpacity: options.fill_opacity
        });

        //fit the map
        map.fitBounds(rectangle.getBounds());

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setPlaces = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'address': '',
            'latlng': '',
            'map_type': '',
            'map_types': [],
            'width': '',
            'height': '',
            'marker': [],
            'zoom': '',
            'radius': options.radius,
            'type': 'search', //radar_search also an option
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address, false);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng, false);

        //create latlng object
        var latlng = EE_GMAPS.stringToLatLng(options.latlng);

        var marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon);
        var marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow);
        var marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape);

        //create the map
        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            lat: latlng.lat(),
            lng: latlng.lng(),
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            zoom: options.zoom,
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        //set the typ
        var type = 'search';
        if (options.type == 'radar_search') {
            type = 'radarSearch';
        }
        var search_options = {
            location: latlng,
            radius: options.radius,
            types: options.search_types,
            keyword: options.search_keyword
        };

        search_options[type] = function (results, status) {
            var bounds = [];
            if (status == google.maps.places.PlacesServiceStatus.OK) {
                for (var i = 0; i < results.length; i++) {
                    var place = results[i];
                    bounds.push(place.geometry.location);
                    map.addMarker(EE_GMAPS.cleanObject({
                        lat: place.geometry.location.lat(),
                        lng: place.geometry.location.lng(),
                        title: place.name,
                        icon: marker_icon,
                        shadow: marker_icon_shadow,
                        shape: marker_icon_shape,
                        animation: options.marker.animation ? google.maps.Animation.DROP : null,
                        infoWindow: {
                            content: '<h2>' + place.name + '</h2><p>' + (place.vicinity ? place.vicinity : place.formatted_address) + '</p><img src="' + place.icon + '"" width="100"/>'
                        }
                    }));

                    /*placesLayer.getDetails({
						reference : place.reference
					}, function (place_detail, status){
						map.createMarker(place_detail);
					});*/
                }
            }

            //fit the map
            if (bounds.length > 1) {
                map.fitLatLngBounds(bounds);
            } else if (bounds.length == 1) {
                map.setCenter(bounds[0].lat(), bounds[0].lng());
            }
        }

        var placesLayer = map.addLayer('places', search_options);

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //Get kml data in a map (BETA)
    EE_GMAPS.setKml = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'kml_url': '',
            'map_type': '',
            'map_types': [],
            'width': '',
            'height': '',
            'zoom': '',
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        if (options.latlng != '' && options.zoom != 0) {
            options.address = EE_GMAPS.parseToJsArray(options.address);
            options.latlng = EE_GMAPS.parseToJsArray(options.latlng);

            //create latlng object
            var latlng = EE_GMAPS.stringToLatLng(options.latlng[0]);
            var lat = latlng.lat(),
                lng = latlng.lng();
        } else {
            var lat = EE_GMAPS.def.lat,
                lng = EE_GMAPS.def.lng;
        }

        infoWindow = new google.maps.InfoWindow({});
        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            lat: lat,
            lng: lng,
            zoom: options.zoom,
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        map.loadFromKML({
            url: options.kml_url,
            suppressInfoWindows: true,
            preserveViewport: options.zoom != 0 ? true : false,
            events: {
                click: function (point) {
                    infoWindow.setContent(point.featureData.infoWindowHtml);
                    infoWindow.setPosition(point.latLng);
                    infoWindow.open(map.map);
                }
            }
        });

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //Get a fusion table (BETA)
    EE_GMAPS.setFusionTable = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'table_id': '',
            'address': '',
            'latlng': '',
            'styles': [],
            'heatmap': false,
            'map_type': '',
            'map_types': [],
            'width': '',
            'height': '',
            'zoom': '',
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address, false);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng, false);

        infoWindow = new google.maps.InfoWindow({});

        //create latlng object
        var latlng = EE_GMAPS.stringToLatLng(options.latlng);

        //create the map			
        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            lat: latlng.lat(),
            lng: latlng.lng(),
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            zoom: options.zoom,
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        var fusion = map.loadFromFusionTables({
            query: {
                from: options.table_id
            },
            styles: options.styles,
            suppressInfoWindows: true,
            events: {
                click: function (point) {
                    infoWindow.setContent(point.infoWindowHtml);
                    infoWindow.setPosition(point.latLng);
                    infoWindow.open(map.map);
                }
            },
            heatmap: {
                enabled: options.heatmap
            }
        });

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setStreetViewPanorama = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'address': '',
            'latlng': '',
            'width': '',
            'height': '',
            'address_control': true,
            'click_to_go': true,
            'disable_double_click_zoom': false,
            'enable_close_button': true,
            'image_date_control': true,
            'links_control': true,
            'pan_control': true,
            'scroll_wheel': true,
            'zoom_control': true,
            'checkaround' : 50,
            'visible': true,
            'pov': {},
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng);

        //create latlng object
        var latlng = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.latlng));

        var map;
        EE_GMAPS._map_[options.selector] = map = GMaps.createPanorama({
            el: options.selector,
            lat: latlng[0].lat(),
            lng: latlng[0].lng(),
            addressControl: options.address_control,
            clickToGo: options.click_to_go,
            disableDoubleClickZoom: options.disable_double_click_zoom,
            enableCloseButton: options.enable_close_button,
            imageDateControl: options.image_date_control,
            linksControl: options.links_control,
            panControl: options.pan_control,
            pov: options.pov,
            scrollwheel: options.scroll_wheel,
            visible: options.visible,
            zoomControl: options.zoom_control,
            enableNewStyle: options.enable_new_style,
            checkaround: options.checkaround
        });

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setEmptyMap = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'width': '',
            'latlng': '',
            'address': '',
            'zoom': '',
            'map_type': '',
            'map_types': [],
            'height': '',
            'marker': [],
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng);

        //create latlng object
        var latlng = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.latlng));

        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            zoom: 1,
            lat: latlng[0].lat(),
            lng: latlng[0].lng(),
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        if (latlng.length > 1) {
            map.fitLatLngBounds(latlng);
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setMap = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'width': '',
            'zoom': '',
            'map_type': '',
            'map_types': [],
            'height': '',
            'marker': [],
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true,
            'marker_cluster': false
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //map options
        var map_options = {
            el: options.selector,
            zoom: 1,
            lat: EE_GMAPS.def.lat,
            lng: EE_GMAPS.def.lng,
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style,

        };

        //marker cluster
        if (options.marker_cluster) {
            map_options.markerClusterer = function (map) {
                return new MarkerClusterer(map, [], {
                    gridSize: 60,
                    maxZoom: 10,
                    styles: options.marker_cluster_style,
                    imagePath: EE_GMAPS.theme_path+'images/cluster/m'
                });
            };
        };

        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps(map_options);

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        //EE_GMAPS.saveMarkers(options.selector, map.markers);
        //callback function when all things is ready
        EE_GMAPS.runAll();
    };

    //----------------------------------------------------------------------------------------------------------//
    // Private functions //
    //----------------------------------------------------------------------------------------------------------//

    //get latlong based on address
    EE_GMAPS.setStaticMap = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'latlng': '',
            'map_type': '',
            'width': '',
            'height': '',
            'zoom': '',
            'marker': true,
            'polygon': false,
            'stroke_color': '',
            'stroke_opacity': '',
            'stroke_weight': '',
            'fill_color': '',
            'fill_opacity': ''
        }, options);

        // create latlng object
        var latlng = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.latlng));
        var _polygon_latlng = [];
        var _markers = [];
        var bounds = new google.maps.LatLngBounds();

        //set the bounds and the polygon latlng
        $.each(latlng, function (k, v) {
            bounds.extend(v);

            _polygon_latlng.push([v.lat(), v.lng()]);

            if (options.marker.show) {
                _markers.push({
                    lat: v.lat(),
                    lng: v.lng()
                });
            }
        });

        //get the center
        var center = bounds.getCenter();

        //size
        if (options.width == '' || options.height) {
            options.width = '630';
            options.height = '300';
        }

        if (options.polygon) {

            //var center = EE_GMAPS.getCenterLatLng(latlng);
            //create map
            var url = GMaps.staticMapURL({
                size: [options.width, options.height],
                lat: center[0],
                lng: center[1],
                zoom: EE_GMAPS.getZoom(options.width, latlng),
                maptype: options.map_type,
                polyline: {
                    path: _polygon_latlng,
                    strokeColor: options.stroke_color,
                    strokeOpacity: options.stroke_opacity,
                    strokeWeight: options.stroke_weight,
                    fillColor: options.fill_color
                },
                markers: _markers
            });

            //geocoding
        } else {
            //create map
            var url = GMaps.staticMapURL({
                size: [options.width, options.height],
                lat: center.lat(),
                lng: center.lng(),
                zoom: options.zoom,
                maptype: options.map_type,
                markers: _markers
            });
        }

        //place the image
        $(options.selector).html('<img src="' + url + '" alt="Gmaps map from ' + options.address + '" title="Static Gmaps" width="' + options.width + '" height="' + options.height + '" />');
    };

    //add a google like overlay like the iframe
    EE_GMAPS.addGoogleOverlay = function(map, options, direct){
 
        var latlng, marker_object;

        if( options.latlng != undefined && options.latlng[0] != undefined) {
            latlng = options.latlng[0].split(',');
            marker_object = new google.maps.LatLng(latlng[0], latlng[1]);

             options.overlay_html = options.overlay_html.gmaps_replaceAll('[route_to_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'route_to'));
             options.overlay_html = options.overlay_html.gmaps_replaceAll('[route_from_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'route_from'));
             options.overlay_html = options.overlay_html.gmaps_replaceAll('[map_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'map'));
        }
     
        if(options.overlay_html != '') {
            if(direct) {
                if($(options.selector).find('#custom_gmaps_overlay').length == 0) {
                    var style = "margin: 10px; padding: 1px; -webkit-box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; border-top-left-radius: 2px; border-top-right-radius: 2px; border-bottom-right-radius: 2px; border-bottom-left-radius: 2px; background-color: white;";
                    $(options.selector).find('.gm-style').append('<div class="google-like-overlay-position" style="position: absolute; '+options.overlay_position+': 0px; top: 0px;"><div style="'+style+'" id="custom_gmaps_overlay"><div style="padding:5px;" class="place-card google-like-overlay-content place-card-large">'+options.overlay_html+'</div></div></div>');                         
                }
            }

            map.on('tilesloaded', function(){
                if($(options.selector).find('#custom_gmaps_overlay').length == 0) {
                    var style = "margin: 10px; padding: 1px; -webkit-box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; border-top-left-radius: 2px; border-top-right-radius: 2px; border-bottom-right-radius: 2px; border-bottom-left-radius: 2px; background-color: white;";
                    $(options.selector).find('.gm-style').append('<div class="google-like-overlay-position" style="position: absolute; '+options.overlay_position+': 0px; top: 0px;"><div style="'+style+'" id="custom_gmaps_overlay"><div style="padding:5px;" class="place-card google-like-overlay-content place-card-large">'+options.overlay_html+'</div></div></div>');                         
                }
            });
        }
    };

    //remove a google like overlay like the iframe
    EE_GMAPS.removeGoogleOverlay = function(selector){
        $(selector).find('.google-like-overlay-position').remove();
    }

    //update a google like overlay like the iframe
    EE_GMAPS.updateGoogleOverlay = function(map, options){
        //no overlay?
        if($(options.selector).find('.google-like-overlay-position').length == 0) {
            EE_GMAPS.addGoogleOverlay(map, options, true);
        }

        if(options.overlay_html != undefined) {
            if(options.overlay_html == '') {
                EE_GMAPS.removeGoogleOverlay(options.selector);
            } else {
                $(options.selector).find('.google-like-overlay-content').html(options.overlay_html);
            }
            
        }

        if(options.overlay_position != undefined) {
            $(options.selector).find('.google-like-overlay-position').css('left', '').css('right', '');
            $(options.selector).find('.google-like-overlay-position').css(options.overlay_position, '0px');
        }
    }

    //add mapTypes
    EE_GMAPS.addCustomMapTypes = function (map, map_types) {
        $.each(map_types, function (k, v) {
            switch (v) {
                //Openstreetmap
            case 'osm':
                map.addMapType("osm", {
                    getTileUrl: function (coord, zoom) {
                        return "http://tile.openstreetmap.org/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
                    },
                    tileSize: new google.maps.Size(256, 256),
                    name: "OpenStreetMap",
                    maxZoom: 18
                });
                break;

                //Cloudmade
            case 'cloudmade':
                map.addMapType("cloudmade", {
                    getTileUrl: function (coord, zoom) {
                        return "http://b.tile.cloudmade.com/8ee2a50541944fb9bcedded5165f09d9/1/256/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
                    },
                    tileSize: new google.maps.Size(256, 256),
                    name: "CloudMade",
                    maxZoom: 18
                });
                break;

                //StamenMapType: Toner
            case 'toner':
                map.map.mapTypes.set("toner", new google.maps.StamenMapType("toner"));
                break;

                //StamenMapType: watercolor
            case 'watercolor':
                map.map.mapTypes.set("watercolor", new google.maps.StamenMapType("watercolor"));
                break;
            };
        });
    };

    //Set custom maps
    EE_GMAPS.addCustomMapType = function (map, map_type) {
        switch (map_type) {
            //Openstreetmap
        case 'osm':
            map.setMapTypeId("osm");
            break;

            //Cloudmade
        case 'cloudmade':
            map.setMapTypeId("cloudmade");
            break;

            //StamenMapType: Toner
        case 'toner':
            map.setMapTypeId("toner");
            break;

            //StamenMapType: watercolor
        case 'watercolor':
            map.setMapTypeId("watercolor");
            break;
        };
    };

    //get latlong based on address
    EE_GMAPS.latLongAddress = function (addresses, callback) {

        var latLongAdresses = new Array();
        var latLongObject = new Array();

        $.each(addresses, function (key, val) {
            GMaps.geocode({
                address: val,
                callback: function (results, status) {
                    //is there any result
                    if (status == "OK") {
                        latLongAdresses[key] = results[0].geometry.location;
                        latLongObject[key] = results[0];
                    }

                    //return the results
                    if (key == (addresses.length - 1)) {
                        if (callback && typeof (callback) === "function") {
                            //settimeout because the load error
                            setTimeout(function () {
                                callback(latLongAdresses, latLongObject);
                            }, 200);
                        }
                    }
                }
            });
        });
    };

    //flatten an polygon result
    EE_GMAPS.flatten_polygon_result = function (polygon) {
        var new_array = [];

        polygon.getArray().forEach(function (v) {
            new_array.push(v.getArray());
        });

        return _.flatten(new_array);
    };

    //retrun good waypoints array based on some latlong positions
    EE_GMAPS.getCenterLatLng = function (latlong) {
        var lat = [];
        var lng = [];

        $.each(latlong, function (key, val) {
            lat.push(val.lat());
            lng.push(val.lng());
        });

        //set the center x and y points
        var center_lat = lat.gmaps_min() + ((lat.gmaps_max() - lat.gmaps_min()) / 2);
        var center_lng = lng.gmaps_min() + ((lng.gmaps_max() - lng.gmaps_min()) / 2);

        return [center_lat, center_lng];
    };

    //set the styled maps for a map
    EE_GMAPS.setStyledMap = function (styledArray, map) {
        if (Object.keys(styledArray).length > 0) {
            map.addStyle({
                styledMapName: "Styled Map",
                styles: styledArray,
                mapTypeId: "map_style"
            });
            map.setStyle("map_style");
        };
    };

    //set the traffic layer
    EE_GMAPS.setTraffic = function (show_layer, map) {
        if (show_layer) {
            map.addLayer('traffic');
        };
    };

    //set the Transit layer
    EE_GMAPS.setTransit = function (show_layer, map) {
        if (show_layer) {
            map.addLayer('transit');
        };
    };

    //set the Bicycling layer
    EE_GMAPS.setBicycling = function (show_layer, map) {
        if (show_layer) {
            map.addLayer('bicycling');
        };
    };

    //set the Weather layer
    EE_GMAPS.setWeather = function (show_layer, map) {
        if (show_layer) {
            map.addLayer('clouds');
            map.addLayer('weather');
        };
    };

    //set the Panoramio layer
    EE_GMAPS.setPaoramio = function (show_layer, map, options) {
        if (show_layer) {
            map.addLayer('panoramio', {
                filter: options.tag
            });
        };
    };

    //calculate the zoom
    EE_GMAPS.getZoom = function (map_width, latlong) {
        map_width = map_width / 1.74;
        var lat = [];
        var lng = [];

        $.each(latlong, function (key, val) {
            lat.push(val.lat());
            lng.push(val.lng());
        });

        //calculate the distance
        var dist = (6371 * Math.acos(Math.sin(lat.gmaps_min() / 57.2958) * Math.sin(lat.gmaps_max() / 57.2958) +
            (Math.cos(lat.gmaps_min() / 57.2958) * Math.cos(lat.gmaps_max() / 57.2958) * Math.cos((lng.gmaps_max() / 57.2958) - (lng.gmaps_min() / 57.2958)))));

        //calculate the zoom
        var zoom = Math.floor(8 - Math.log(1.6446 * dist / Math.sqrt(2 * (map_width * map_width))) / Math.log(2)) - 1;

        return zoom;
    };

    //retrun good waypoints array based on some latlong positions
    EE_GMAPS.createWaypoints = function (waypoints) {
        var points = [];
        $.each(waypoints, function (key, val) {
            points.push({
                location: val,
                stopover: false
            });
        });
        return points;
    };

    //retrun good waypoints array based on some latlong positions
    EE_GMAPS.createlatLngArray = function (latlng) {
        var points = [];
        $.each(latlng, function (key, val) {
            points.push([val.lat(), val.lng()]);
        });

        return points;
    };

    //reparse an array that whas generated by php
    EE_GMAPS.reParseLatLngArray = function (array) {
        var points = [];
        $.each(array, function (key, val) {
            points.push([parseFloat(val[0]), parseFloat(val[1])]);
        });
        return points;
    };

    //convert array to latlng 
    EE_GMAPS.arrayToLatLng = function (coords) {
        var new_coords = [];
        $.each(coords, function (key, val) {
            if (typeof val == 'string') {
                val = val.split(',');
            }
            new_coords.push(new google.maps.LatLng(parseFloat($.trim(val[0])), parseFloat($.trim(val[1]))));
        });
        return new_coords;
    };

    //convert string to latlng
    EE_GMAPS.stringToLatLng = function (coords) {
        var val = coords.split(',');
        var new_coords = new google.maps.LatLng(parseFloat($.trim(val[0])), parseFloat($.trim(val[1])));
        return new_coords;
    };

    //remove empty values
    EE_GMAPS.cleanArray = function (arr) {
        return $.grep(arr, function (n) {
            return (n);
        });
    };

    //Parse base64 string to js array
    EE_GMAPS.parseToJsArray = function (string, split) {
        string = base64_decode(string);
        if (typeof string == 'string') {

            //empty?
            if(string == '[]') {
                 return '';
            };

            string = decodeURIComponent(escape(string));
            if (split || split == undefined) {
                return string.split('|');
            } else {
                return string;
            };
        };
        return '';
    };

    //set the marker Icon 
    EE_GMAPS.setMarkerIcon = function (marker_icon, marker_icon_default, k) {

        //set vars
        var new_marker_icon, url, size, origin, anchor;

        //array of values, mostly geocoding
        if (typeof marker_icon.url == 'object' && marker_icon.url.length > 0) {
            url = marker_icon.url[k] != undefined ? marker_icon.url[k] : marker_icon_default.url;
            size = marker_icon.size[k] != undefined ? marker_icon.size[k] : marker_icon_default.size;
            size = size.split(',');
            origin = marker_icon.origin[k] != undefined ? marker_icon.origin[k] : marker_icon_default.origin;
            origin = origin.split(',');
            anchor = marker_icon.anchor[k] != undefined ? marker_icon.anchor[k] : marker_icon_default.anchor;
            anchor = anchor.split(',');

            //set the object
            new_marker_icon = {};
            if (url != '') {
                new_marker_icon.url = url;
                if (size != '') {
                    new_marker_icon.size = new google.maps.Size(parseInt(size[0]), parseInt(size[1]));
                }
                if (origin != '') {
                    new_marker_icon.origin = new google.maps.Point(parseInt(origin[0]), parseInt(origin[1]));
                }
                if (anchor != '') {
                    new_marker_icon.anchor = new google.maps.Point(parseInt(anchor[0]), parseInt(anchor[1]));
                }
            } else {
                new_marker_icon = '';
            }

            //default, all others beside geocoding
        } else if (marker_icon_default == undefined) {
            url = marker_icon.url;
            size = marker_icon.size;
            size = size.split(',');
            origin = marker_icon.origin;
            origin = origin.split(',');
            anchor = marker_icon.anchor;
            anchor = anchor.split(',');

            //set the object
            new_marker_icon = {};
            if (url != '') {
                new_marker_icon.url = url;
                if (size != '') {
                    new_marker_icon.size = new google.maps.Size(parseInt(size[0]), parseInt(size[1]));
                }
                if (origin != '') {
                    new_marker_icon.origin = new google.maps.Point(parseInt(origin[0]), parseInt(origin[1]));
                }
                if (anchor != '') {
                    new_marker_icon.anchor = new google.maps.Point(parseInt(anchor[0]), parseInt(anchor[1]));
                }
            } else {
                new_marker_icon = '';
            }

            //default marker icon, mostly geocoding
        } else {
            if (marker_icon_default.url != '') {
                url = marker_icon_default.url;
                size = marker_icon_default.size;
                size = size.split(',');
                origin = marker_icon_default.origin;
                origin = origin.split(',');
                anchor = marker_icon_default.anchor;
                anchor = anchor.split(',');

                //set the object
                new_marker_icon = {};
                if (url != '') {
                    new_marker_icon.url = url;
                    if (size != '') {
                        new_marker_icon.size = new google.maps.Size(parseInt(size[0]), parseInt(size[1]));
                    }
                    if (origin != '') {
                        new_marker_icon.origin = new google.maps.Point(parseInt(origin[0]), parseInt(origin[1]));
                    }
                    if (anchor != '') {
                        new_marker_icon.anchor = new google.maps.Point(parseInt(anchor[0]), parseInt(anchor[1]));
                    }
                } else {
                    new_marker_icon = '';
                }

                //no marker set? just empty
            } else {
                new_marker_icon = '';
            }
        }
        return new_marker_icon;
    };

    //set the marker shape 
    EE_GMAPS.setMarkerShape = function (marker_icon_shape, marker_icon_shape_default, k) {

        //set vars
        var new_marker_icon_shape, coord, type;

        //array of values, mostly geocoding
        if (typeof marker_icon_shape.coord == 'object' && marker_icon_shape.coord.length > 0) {
            coord = marker_icon_shape.coord[k] != undefined ? marker_icon_shape.coord[k] : marker_icon_shape_default.coord;
            type = marker_icon_shape.type[k] != undefined ? marker_icon_shape.type[k] : marker_icon_shape_default.type;

            //set the object
            new_marker_icon_shape = {};
            if (type != '') {
                new_marker_icon_shape.type = type;
            }
            if (coord != '') {
                new_marker_icon_shape.coord = coord.split(',');
            } else {
                new_marker_icon_shape = '';
            }

            //default, all others beside geocoding
        } else if (marker_icon_shape_default == undefined) {
            coord = marker_icon_shape.coord;
            type = marker_icon_shape.type;

            //set the object
            new_marker_icon_shape = {};
            if (type != '') {
                new_marker_icon_shape.type = type;
            }
            if (coord != '') {
                new_marker_icon_shape.coord = coord.split(',');
            } else {
                new_marker_icon_shape = '';
            }

            //default shape, mostly geocoding
        } else {
            if (marker_icon_shape_default.url != '') {
                coord = marker_icon_shape_default.coord;
                type = marker_icon_shape_default.type;

                //set the object
                new_marker_icon_shape = {};
                if (type != '') {
                    new_marker_icon_shape.type = type;
                }
                if (coord != '') {
                    new_marker_icon_shape.coord = coord.split(',');
                } else {
                    new_marker_icon_shape = '';
                }

                //no marker set? just empty
            } else {
                new_marker_icon_shape = '';
            }
        }
        return new_marker_icon_shape;
    };

    //set the infowindow content
    //and replace the tokens
    EE_GMAPS.setInfowindowContent = function (content, tokens, marker_object) {
        var content = content || '';

        if (content != undefined || content) {
            $.each(tokens, function (k, v) {
                content = content.gmaps_replaceAll('[' + k + ']', v);
            });

            //try creating the urls
            content = content.gmaps_replaceAll('[route_to_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'route_to'));
            content = content.gmaps_replaceAll('[route_from_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'route_from'));
            content = content.gmaps_replaceAll('[map_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'map'));
        }

        //set content to null when empty
        content = content != '' ? content : null;

        return content;
    };

    //remove empty properties from an object
    EE_GMAPS.cleanObject = function (object) {
        var object = object || {};

        object = gmaps_remove_empty_values(object);

        return object;
    };

    //create the infobox
    EE_GMAPS.addInfobox = function(options, map, marker, marker_number){
        if(options.marker.infobox.content !== '') {
            var content = options.marker.infobox.content.split('|');
            var location = options.address[marker_number] ? options.address[marker_number] : marker.position.toString().replace('(', '').replace(')', '');
            //remove the hash
            var selector = options.selector.replace('#', '');

            //set the content
            content = EE_GMAPS.setInfowindowContent(content[marker_number], {
                'location': location
            }, marker.position);

            marker.infobox_options = {
                boxClass: options.marker.infobox.box_class,
                maxWidth: options.marker.infobox.max_width,
                zIndex: options.marker.infobox.z_index,
                content: content,
                pixelOffset: new google.maps.Size(parseInt(options.marker.infobox.pixel_offset.width), parseInt(options.marker.infobox.pixel_offset.height)),
                boxStyle: options.marker.infobox.box_style,
                closeBoxMargin: "10px 2px 2px 2px",
                closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif"
            };

            //create a infobox
            marker.infoBox = new InfoBox(marker.infobox_options);

            //save the marker
            EE_GMAPS.saveMarker(selector, marker);

            marker = EE_GMAPS.searchMarker(selector, marker_number);

            google.maps.event.addListener(marker, "click", function () {
                $.each(EE_GMAPS.markers[selector], function(i, _marker){
                    _marker.marker.infoBox.close();
                });

                marker.infoBox.open(map.map, marker);
            });
        }

    };

    //set the googlemaps url e.g. route or place
    EE_GMAPS.setInfowindowUrl = function (marker_object, type) {
        var url = '';
        if (marker_object != undefined) {
            switch (type) {
            case 'route_to':
                url = 'https://maps.google.com/maps?daddr=' + marker_object.lat() + ',' + marker_object.lng();
                //http://maps.google.com/maps?saddr=start&daddr=end
                break;

            case 'route_from':
                url = 'https://maps.google.com/maps?saddr=' + marker_object.lat() + ',' + marker_object.lng();
                //http://maps.google.com/maps?saddr=start&daddr=end
                break;

            default:
            case 'map':
                url = 'https://maps.google.com/maps?q=' + marker_object.lat() + ',' + marker_object.lng();
                //https://maps.google.com/maps?q=
                break;
            }
        }
        return url;
    };

    //set the markers to the arrays
    EE_GMAPS.saveMarkers = function (mapID, markers, address_based, keys) {

        //set mapID
        mapID = mapID.replace('#', '');
        //set vars
        var markerNumbers = [];
        var newMarkerData = [];

        if (markers.length > 0) {

            //save all to a latlng array
            $.each(markers, function (k, v) {
                //set the marker number
                v.markerNumber = k;
                //set the uuuid
                v.markerUUID = createUUID(),

                markerNumbers.push(v.markerNumber);

                //set the arrays
                newMarkerData[k] = [];
                newMarkerData[k]['marker'] = v;
                newMarkerData[k]['keys'] = [k, v.markerUUID, v.getPosition().lat() + ',' + v.getPosition().lng()];

                //save marker to array
                //EE_GMAPS.markers[k]['index'] = [v];

                //save all to a latlng array
                EE_GMAPS.latlngs.push(v.position.lat() + ',' + v.position.lng());
            });

            //create address based array
            if (typeof address_based == 'object') {
                $.each(address_based, function (k, v) {
                    if (newMarkerData[k] != undefined && newMarkerData[k]['keys'] != undefined) {
                        v = $.trim(v);
                        newMarkerData[k]['keys'].push(v);
                        newMarkerData[k]['keys'].push(v.toLowerCase());
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '_'));
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '_').toLowerCase());
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '-'));
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '-').toLowerCase());
                        //remove duplicated
                        newMarkerData[k]['keys'] = _.uniq(newMarkerData[k]['keys']);
                    }
                });
            };

            //create the custom keys for the marker
            if (typeof keys == 'object') {
                $.each(keys, function (k, v) {
                    if (newMarkerData[k] != undefined && newMarkerData[k]['keys'] != undefined) {
                        v = $.trim(v);
                        newMarkerData[k]['keys'].push(v);
                        newMarkerData[k]['keys'].push(v.toLowerCase());
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '_'));
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '_').toLowerCase());
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '-'));
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '-').toLowerCase());
                        //remove duplicated
                        newMarkerData[k]['keys'] = _.uniq(newMarkerData[k]['keys']);
                    }
                });
            };

            //save the marker data
            EE_GMAPS.markers[mapID] = newMarkerData;
        };

        //callback function when all things is ready
        EE_GMAPS.runAll();

        return markerNumbers.length == 1 ? markerNumbers[0] : markerNumbers;
    };

    //save single marker
    EE_GMAPS.saveMarker = function (mapID, marker) {
        //set the map array
        if (EE_GMAPS.markers[mapID] == undefined) {
            EE_GMAPS.markers[mapID] = [];
        };
        //get the index
        var index = EE_GMAPS.markers[mapID].length;
        //set markerNumber
        marker.markerNumber = index;
        //set the uuuid
        marker.markerUUID = createUUID();
        //set the arrays
        EE_GMAPS.markers[mapID][index] = [];
        EE_GMAPS.markers[mapID][index]['marker'] = marker;
        EE_GMAPS.markers[mapID][index]['keys'] = [index, marker.markerUUID];
        //update lnglngs array
        EE_GMAPS.latlngs.push(marker.position.lat() + ',' + marker.position.lng());

        return marker.markerNumber;
    };

    //set the markers to the arrays
    EE_GMAPS.searchMarker = function (mapID, marker_name) {

        var marker;

        //loop over the markers
        if(EE_GMAPS.markers[mapID] != undefined) {
            $.each(EE_GMAPS.markers[mapID], function (key, val) {
                //search the array
                if (val['keys'] != undefined) {
                    if (jQuery.inArray(marker_name, val['keys']) != -1) {
                        marker = EE_GMAPS.markers[mapID][key]['marker'];
                    }
                }
            });
        }

        return marker;
    };

    //remove single marker
    EE_GMAPS.removeMarker = function (mapID, marker_name) {

        var index;

        //loop over the markers
        $.each(EE_GMAPS.markers[mapID], function (key, val) {
            //search the array
            if (val['keys'] != undefined && index == undefined) {
                if (jQuery.inArray(marker_name, val['keys']) != -1) {
                    //set the index
                    index = key;
                }
            }
        });

        //remove marker
        if (index != undefined) {
            EE_GMAPS.markers[mapID].gmaps_remove(index);
        }

        //remove latlng from array
        $.each(EE_GMAPS.latlngs, function (k, v) {
            if (k == index) {
                EE_GMAPS.latlngs.gmaps_remove(k);
                //delete EE_GMAPS.latlngs[k];
            }
        });

        //update markerNumber
        $.each(EE_GMAPS.markers[mapID], function (key, val) {
            val['marker'].markerNumber = key;
            val['keys'][0] = key;
        });
    };

    //update the marker cache with the new markers
    //new_order is an array with the key/index as new number, and the value the uuid
    EE_GMAPS.updateMarkerCache = function (mapID, new_order) {
        if ($.isArray(new_order)) {
            var new_cache = [];
            $.each(new_order, function (k, v) {
                var marker = EE_GMAPS.searchMarker(mapID, v);
                if (marker != undefined) {
                    var old_markerNumber = marker.markerNumber
                    //set the new marker
                    marker.markerNumber = k;
                    EE_GMAPS.markers[mapID][old_markerNumber].keys[0] = k;
                    //save to new cache
                    new_cache.push(EE_GMAPS.markers[mapID][old_markerNumber]);
                }
            });

            //set the new cache
            EE_GMAPS.markers[mapID] = new_cache;
        }
    };

    //save polyline or polygon
    EE_GMAPS.saveArtOverlay = function (mapID, object, type) {

        //set the type array, always with s like polylines or circles
        var type_array = type + 's';

        if (type != undefined && EE_GMAPS[type_array] != undefined && object != undefined) {
            //set the map array
            if (EE_GMAPS[type_array][mapID] == undefined) {
                EE_GMAPS[type_array][mapID] = [];
            }
            //get the index
            var index = EE_GMAPS[type_array][mapID].length;
            //set markerNumber
            object.objectNumber = index;
            //set the uuuid
            object.objectUUID = createUUID();
            //set the arrays
            EE_GMAPS[type_array][mapID][index] = [];
            EE_GMAPS[type_array][mapID][index]['object'] = object;
            EE_GMAPS[type_array][mapID][index]['keys'] = [index, object.objectUUID];

            //return number
            return object.objectNumber;
        }
    };

    //set the poly to the arrays
    EE_GMAPS.searchArtOverlay = function (mapID, object_name, type) {

        //set the type array, always with s like polylines or circles
        var type_array = type + 's';

        if (type != undefined && EE_GMAPS[type_array] != undefined) {
            var object;

            //loop over the markers
            $.each(EE_GMAPS[type_array][mapID], function (key, val) {
                //search the array
                if (val['keys'] != undefined) {
                    if (jQuery.inArray(object_name, val['keys']) != -1) {
                        object = EE_GMAPS[type_array][mapID][key]['object'];
                    }
                }
            });

            //return
            return object;
        }
    };

    //remove single poly
    EE_GMAPS.removeArtOverlay = function (mapID, object_name, type) {

        //set the type array, always with s like polylines or circles
        var type_array = type + 's';
        var index;

        if (type != undefined && EE_GMAPS[type_array] != undefined) {
            //loop over the markers
            $.each(EE_GMAPS[type_array][mapID], function (key, val) {
                //search the array
                if (val['keys'] != undefined && typeof (index) == 'undefined') {
                    if (jQuery.inArray(object_name, val['keys']) != -1) {
                        //set the index
                        index = key;
                    }
                }
            });

            //remove marker
            if (index != undefined) {
                EE_GMAPS[type_array][mapID].gmaps_remove(index);
            }

            //update markerNumber
            $.each(EE_GMAPS[type_array][mapID], function (key, val) {
                val['object'].objectNumber = key;
                val['keys'][0] = key;
            });
        }
    };

    //update the poly cache with the new polylines
    //new_order is an array with the key/index as new number, and the value the uuid
    EE_GMAPS.updateArtOverlayCache = function (mapID, new_order, type) {

        //set the type array, always with s like polylines or circles
        var type_array = type + 's';

        if (type != undefined && EE_GMAPS[type_array] != undefined) {
            if ($.isArray(new_order)) {
                var new_cache = [];
                $.each(new_order, function (k, v) {
                    var object = EE_GMAPS.searchArtOverlay(mapID, v, type);
                    if (object != undefined) {
                        var old_objectNumber = object.objectNumber
                        //set the new poly
                        object.objectNumber = k;
                        EE_GMAPS[type_array][mapID][old_objectNumber].keys[0] = k;
                        //save to new cache
                        new_cache.push(EE_GMAPS[type_array][mapID][old_objectNumber]);
                    }
                });
                //set the new cache
                EE_GMAPS[type_array][mapID] = new_cache;
            }
        }
    };

    //get the map
    EE_GMAPS.getMap = function (id) {
        if (EE_GMAPS._map_['#' + id] != undefined) {
            return EE_GMAPS._map_['#' + id];
        }
        return false;
    };

    //get the map
    EE_GMAPS.fitMap = function (key) {
        if (EE_GMAPS.markers[key] != undefined) {
            //console.log(EE_GMAPS.markers[key]);
            //EE_GMAPS['#'_key].fitLatLngBounds(latlng);
        }
    };

    //simple Geolocation wrapper
    EE_GMAPS.geolocate = function(callback){
        GMaps.geolocate({
            success: function (position) {
                if (typeof callback === "function") {
                    // Call it, since we have confirmed it is callable​
                    callback(position);
                }
            },
            error: function (error) {
                console.log('Geolocation failed: ' + error.message);
            },
            not_supported: function () {
                console.log("Your browser does not support geolocation");
            }
        });
    };




    //----------------------------------------------------------------------------------------------------------//
    // Public functions //
    //----------------------------------------------------------------------------------------------------------//

    ///create an onclick event wrapper for an marker
    EE_GMAPS.api = EE_GMAPS.triggerEvent = function (type, options) { 
        //no type
        if (type == '') {
            return false;
        }

        //options 
        options = $.extend({
            mapID: '',
            key: ''
        }, options);

        //set the vars
        var mapID, map, latlng = [];
        var marker;

        //set the mapID
        if (options.mapID != '') {
            //set the mapID
            mapID = options.mapID;
            delete options.mapID;

            //get the map
            map = EE_GMAPS.getMap(mapID);
        }

        //what do we do
        switch (type) {
            //marker click
        case 'markerClick':
            //get the marker
            marker = EE_GMAPS.searchMarker(mapID, options.key);

            //trigger the click
            if (marker != undefined) {
                google.maps.event.trigger(marker, 'click');
                //is there a map
                if (map) {
                    map.setCenter(marker.position.lat(), marker.position.lng());
                }
            }
            break;

        //callback for the marker click (added 2.14)
        case 'markerClickCallback':
            //get the marker
            marker = EE_GMAPS.searchMarker(mapID, options.key);

            //trigger the click
            if (marker != undefined && typeof options.callback == 'function') {
                google.maps.event.addListener(marker, "click", function () {
                    //assign marker and map object
                    options.marker = marker;
                    options.map = map;
                    //call the callback
                    options.callback(map);
                });
            }
            break;

            //close infowindow
        case 'infowindowClose':
            //get the marker
            marker = EE_GMAPS.searchMarker(mapID, options.key);
            //close infoWindow
            if (marker != undefined) {
                marker.infoWindow.close();
            }
            break;

        //Get the marker array
        case 'getAllMarkers':
            return (EE_GMAPS.markers);
        break;

            //Get the marker array
        case 'getMarkers':
            return (EE_GMAPS.markers[mapID]);
            break;

            //close infowindow
        case 'refresh':
            //is there a map
            if (map) {
                map.refresh();
            }
            break;

            //refresh all maps
        case 'refreshAll':
            var maps = _.values(EE_GMAPS._map_);
            _.each(maps, function(v){
                v.refresh();

                //fitzoom
                if(typeof options.center == 'boolean' && options.center === true) {
                    v.fitZoom();
                }
            });
            break;

        // set Zoom level
        case 'setZoom':
            //is there a map
            if (map) {
                map.setZoom(options.zoomLevel);
            }
            break;

        // set Zoom level
        case 'fitZoom':
            //is there a map
            if (map) {
                map.fitZoom();

                //set the zoom manually
                if (options.zoomLevel != undefined) {
                    map.setZoom(options.zoomLevel);
                }
            }
            break;

        // set Zoom level (added 2.9)
        case 'center':
            //is there a map
            if (map) {
                map.setCenter(options.lat, options.lng);
            }
            break;

            // add Marker, and return the marker numbers
        case 'addMarker':
            //is there a map
            if (map) {
                var ids = [];
                //multiple
                if (options.multi != undefined && _.isArray(options.multi)) {
                    ids = [];
                    $.each(options.multi, function (k, v) {
                        var new_marker = map.addMarker(v);
                        ids.push(EE_GMAPS.saveMarker(mapID, new_marker));

                        //callback
                        if ((k + 1) == options.multi.length) {

                            //fit map
                            if (options.fitTheMap) {
                                map.fitZoom();
                            }

                            //callback
                            if (options.callback && typeof (options.callback) == 'function') {
                                setTimeout(function () {
                                    options.callback()
                                }, 200);
                            }
                        }
                    });

                    //single marker
                } else {
                    var new_marker = map.addMarker(options);
                    ids = EE_GMAPS.saveMarker(mapID, new_marker);

                    //fit map
                    if (options.fitTheMap) {
                        map.fitZoom();
                    }

                    //callback
                    if (options.callback && typeof (options.callback) == 'function') {
                        setTimeout(function () {
                            options.callback()
                        }, 200);
                    }
                }

                return ids;
            }
            break;

            //remove marker
        case 'removeMarker':
            //get the marker
            marker = EE_GMAPS.searchMarker(mapID, options.key);
            //is there a map
            if (map && marker != undefined) {
                //remove from the gmaps.js
                map.removeMarker(marker);
                //remove from the cache
                EE_GMAPS.removeMarker(mapID, options.key);
            }
            break;

            // hide existing Marker
        case 'hideMarker':
            //get the marker
            marker = EE_GMAPS.searchMarker(mapID, options.key);
            //is there a map
            if (map && marker != undefined) {
                //remove from map
                marker.setVisible(false);
            }
            break;

            // show existing Marker
        case 'showMarker':
            //get the marker
            marker = EE_GMAPS.searchMarker(mapID, options.key);
            //is there a map
            if (map && marker != undefined) {
                //remove from map
                marker.setVisible(true);
            }
            break;

            // show existing Marker (added 2.9)
        case 'updateMarker':
            //get the marker
            marker = EE_GMAPS.searchMarker(mapID, options.key);
            //is there a map
            if (map && marker != undefined) {
                //remove key
                delete options.key;
                //set infowindow if needed
                if (options.infoWindow != undefined && options.infoWindow.content != undefined) {
                    if (marker.infoWindow != undefined) {
                        marker.infoWindow.setContent(options.infoWindow.content);
                    }
                    delete options.infoWindow;
                }
                //set the new options
                marker.setOptions(options);
                //refresh the map
                map.refresh();
            }
            break;

            // Get the marker
        case 'getMarker':
            //get the marker
            marker = EE_GMAPS.searchMarker(mapID, options.key);
            //is there a map
            if (map && marker != undefined) {
                //remove from map
                return marker;
            }
            break;

            // Remove all markers
        case 'removeMarkers':
            //is there a map
            if (map) {
                map.removeMarkers();
                //reset marker cache
                EE_GMAPS.markers = [];
            }
            break;

            // Hide all markers (added 2.12.9))
        case 'hideMarkers':
            //is there a map
            if (map && EE_GMAPS.markers[mapID] != undefined) {
                $.each(EE_GMAPS.markers[mapID], function (k, v) {
                    //remove from map
                    v.marker.setVisible(false);
                });
            }
            break;

            // Show all markers (added 2.12.9)
        case 'showMarkers':
            //is there a map
            if (map && EE_GMAPS.markers[mapID] != undefined) {
                $.each(EE_GMAPS.markers[mapID], function (k, v) {
                    //remove from map
                    v.marker.setVisible(true);
                });
            }
            break;

            // create the context menu (added 2.9)
        case 'contextMenu':
            //is there a map
            if (map) {
                map.setContextMenu(options);
            }
            break;

            // Get the map object (added 2.9)
        case 'getMap':
            //is there a map
            if (map) {
                return map;
            }
            break;

            // Add a layer
        case 'addLayer':
            //is there a map
            if (map) {
                if (options.layerName != undefined) {
                    map.addLayer(options.layerName);
                }
            }
            break;

            // Remove a layer
        case 'removeLayer':
            //is there a map
            if (map) {
                if (options.layerName != undefined) {
                    map.removeLayer(options.layerName);
                }
            }
            break;

            // Create a circle
        case 'addCircle':
            //is there a map
            if (map) {
                //multiple
                if (options.multi != undefined && _.isArray(options.multi)) {
                    var ids = [];
                    $.each(options.multi, function (k, v) {
                        var new_circle = map.drawCircle(v);
                        ids.push(EE_GMAPS.saveArtOverlay(mapID, new_circle, 'circle'));
                    });
                    return ids;

                    //single polygon
                } else {
                    var new_circle = map.drawCircle(options);
                    return EE_GMAPS.saveArtOverlay(mapID, new_circle, 'circle');
                }
            }
            break;

            // Get the polygon (added 2.11.1)
        case 'getCircle':
            //get the marker
            circle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'circle');
            //is there a map
            if (map && circle != undefined) {
                //remove from map
                return circle;
            }
            break;

            // update a polygon (added 2.11.1)
        case 'updateCircle':
            //get the marker
            circle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'circle');
            //is there a map
            if (map && circle != undefined) {
                //remove key
                delete options.key;
                //set the new options
                circle.setOptions(options);
                //refresh the map
                map.refresh();
            }
            break;

            //remove polygon (added 2.11.1)
        case 'removeCircle':
            //get the marker
            circle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'circle');
            //is there a map
            if (map && circle != undefined) {
                //remove from gmaps.js
                circle.setMap(null);
                //remove from the cache
                EE_GMAPS.removeArtOverlay(mapID, options.key, 'circle');
            }
            break;

            // Create a circle
        case 'addRectangle':
            //is there a map
            if (map) {
                //multiple
                if (options.multi != undefined && _.isArray(options.multi)) {
                    var ids = [];
                    $.each(options.multi, function (k, v) {
                        var new_rectangle = map.drawRectangle(v);
                        ids.push(EE_GMAPS.saveArtOverlay(mapID, new_rectangle, 'rectangle'));
                    });
                    return ids;

                    //single polygon
                } else {
                    var new_rectangle = map.drawRectangle(options);
                    return EE_GMAPS.saveArtOverlay(mapID, new_rectangle, 'rectangle');
                }
            }
            break;

            // Get the polygon (added 2.11.1)
        case 'getRectangle':
            //get the marker
            rectangle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'rectangle');
            //is there a map
            if (map && rectangle != undefined) {
                //remove from map
                return rectangle;
            }
            break;

            // update a polygon (added 2.11.1)
        case 'updateRectangle':
            //get the marker
            rectangle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'rectangle');
            //is there a map
            if (map && rectangle != undefined) {
                //remove key
                delete options.key;
                //set the new options
                rectangle.setOptions(options);
                //refresh the map
                map.refresh();
            }
            break;

            //remove polygon (added 2.11.1)
        case 'removeRectangle':
            //get the marker
            rectangle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'rectangle');
            //is there a map
            if (map && rectangle != undefined) {
                //remove from gmaps.js
                rectangle.setMap(null);
                //remove from the cache
                EE_GMAPS.removeArtOverlay(mapID, options.key, 'rectangle');
            }
            break;

            // Create a Polygon
        case 'addPolygon':
            //is there a map
            if (map) {
                //multiple
                if (options.multi != undefined && _.isArray(options.multi)) {
                    var ids = [];
                    $.each(options.multi, function (k, v) {
                        var new_polygon = map.drawPolygon(v);
                        ids.push(EE_GMAPS.saveArtOverlay(mapID, new_polygon, 'polygon'));
                    });
                    return ids;

                    //single polygon
                } else {
                    var new_polygon = map.drawPolygon(options);
                    return EE_GMAPS.saveArtOverlay(mapID, new_polygon, 'polygon');
                }
            }
            break;

            // Get the polygon (added 2.11.1)
        case 'getPolygon':
            //get the marker
            polygon = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polygon');
            //is there a map
            if (map && polygon != undefined) {
                //remove from map
                return polygon;
            }
            break;

            // update a polygon (added 2.11.1)
        case 'updatePolygon':
            //get the marker
            polygon = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polygon');
            //is there a map
            if (map && polygon != undefined) {
                //remove key
                delete options.key;
                //set the new options
                polygon.setOptions(options);
                //refresh the map
                map.refresh();
            }
            break;

            //remove polygon (added 2.11.1)
        case 'removePolygon':
            //get the marker
            polygon = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polygon');
            //is there a map
            if (map && polygon != undefined) {
                //remove from gmaps.js
                map.removePolygon(polygon);
                //remove from the cache
                EE_GMAPS.removeArtOverlay(mapID, options.key, 'polygon');
            }
            break;

            // Create a Polyline
        case 'addPolyline':
            //is there a map
            if (map) {
                //multiple
                if (options.multi != undefined && _.isArray(options.multi)) {
                    var ids = [];
                    $.each(options.multi, function (k, v) {
                        var new_polyline = map.drawPolyline(v);
                        ids.push(EE_GMAPS.saveArtOverlay(mapID, new_polyline, 'polyline'));
                    });
                    return ids;

                    //single polyline
                } else {
                    var new_polyline = map.drawPolyline(options);
                    return EE_GMAPS.saveArtOverlay(mapID, new_polyline, 'polyline');
                }
            }
            break;

            // Get the polyline (added 2.11.1)
        case 'getPolyline':
            //get the marker
            polyline = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polyline');
            //is there a map
            if (map && polyline != undefined) {
                //remove from map
                return polyline;
            }
            break;

            // Get the polyline (added 2.11.1)
        case 'updatePolyline':
            //get the marker
            polyline = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polyline');
            //is there a map
            if (map && polyline != undefined) {
                //remove key
                delete options.key;
                //set the new options
                polyline.setOptions(options);
                //refresh the map
                map.refresh();
            }
            break;

            //remove marker (added 2.11.1)
        case 'removePolyline':
            //get the marker
            polyline = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polyline');
            //is there a map
            if (map && polyline != undefined) {
                //remove from gmaps.js
                map.removePolyline(polyline);
                //remove from the cache
                EE_GMAPS.removeArtOverlay(mapID, options.key, 'polyline');
            }
            break;

            // Update a map with new settings
        case 'updateMap':
            //is there a map
            if (map) {
                if (options.setMapTypeId != undefined) {
                    map.setMapTypeId(google.maps.MapTypeId[options.setMapTypeId.toUpperCase()]);
                    delete options.setMapTypeId;
                }
                map.setOptions(options);
            }
            break;

            // Update a map with new settings
        case 'fitMap':
            //is there a map
            if (map) {
                //EE_GMAPS.fitMap(key);
            }
            break;

            //add the google map like overlay (added 3.0)
        case 'addGoogleOverlay':

            //is there a map
            if (map) {
                var new_options = {
                    overlay_html : options.html || '',
                    selector : '#'+mapID.replace('#', ''),
                    overlay_position : options.position || 'left'  
                };
               
                EE_GMAPS.addGoogleOverlay(map, new_options, true);
            }
        break;

         //add the google map like overlay (added 3.0)
        case 'updateGoogleOverlay':

            //is there a map
            if (map) {
                var new_options = {
                    overlay_html : options.html || '',
                    selector : '#'+mapID.replace('#', ''),
                    overlay_position : options.position || 'left'  
                };
               
                EE_GMAPS.updateGoogleOverlay(map, new_options, true);
            }
        break;

           //add the google map like overlay (added 3.0)
        case 'removeGoogleOverlay':

            //is there a map
            if (map) {
                EE_GMAPS.removeGoogleOverlay('#'+mapID.replace('#', ''));
            }
        break;

        case 'geolocation' :
            GMaps.geolocate({
                success: function (position) {
                    if (typeof options.callback === "function") {
                        // Call it, since we have confirmed it is callable​
                        options.callback(position);
                    }
                },
                error: function (error) {
                    console.log('Geolocation failed: ' + error.message);
                },
                not_supported: function () {
                    console.log("Your browser does not support geolocation");
                }
            });
        break;

            // Geocode using the API way to cache all addresses
        case 'geocode':

            var sessionKey = createUUID();

            //latlng reverse geocoding
            if (options.latlng != undefined) {
                $.post(EE_GMAPS.api_path+'&type=latlng', {
                    input: options.latlng
                }, function (result) {
                    if (options.callback && typeof (options.callback) === "function") {
                        options.callback(result, 'latlng', sessionKey);
                    }
                });
            }

            //address geocoding
            if (options.address != undefined) {
                $.post(EE_GMAPS.api_path+'&type=address', {
                    input: options.address
                }, function (result) {
                    if (options.callback && typeof (options.callback) === "function") {
                        options.callback(result, 'address', sessionKey);
                    }
                });
            }

            //ip geocoding
            if (options.ip != undefined) {
                $.post(EE_GMAPS.api_path+'&type=ip', {
                    input: options.ip
                }, function (result) {
                    if (options.callback && typeof (options.callback) === "function") {
                        options.callback(result, 'ip', sessionKey);
                    }
                });
            }
            break;
        }
    };

    //create a show trigger 
    $.each(["show", "toggle", "toggleClass", "addClass", "removeClass"], function () {
        var _oldFn = $.fn[this];
        $.fn[this] = function () {
            var hidden = this.find(":hidden").add(this.filter(":hidden"));
            var result = _oldFn.apply(this, arguments);
            hidden.filter(":visible").each(function () {
                $(this).triggerHandler("show"); //No bubbling
            });
            return result;
        };
    });

}(jQuery));