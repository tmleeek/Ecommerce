;(function(global, $){
    //es5 strict mode
    "use strict";

    var ChannelVideos = global.ChannelVideos = global.ChannelVideos || {};

    var fieldsElem;
    var fields = {};

    var youtubeKey = 'AIzaSyCaVU3uYehPlaQQqgIo66N9FCZhRN1kbNg';
    var youtubeDurationRegex = /P((([0-9]*\.?[0-9]*)Y)?(([0-9]*\.?[0-9]*)M)?(([0-9]*\.?[0-9]*)W)?(([0-9]*\.?[0-9]*)D)?)?(T(([0-9]*\.?[0-9]*)H)?(([0-9]*\.?[0-9]*)M)?(([0-9]*\.?[0-9]*)S)?)?/


    // ----------------------------------------------------------------------

    ChannelVideos.Init = function(){
        fieldsElem = $('.CVField');

        fieldsElem.each(function(i, elem){
            elem = $(elem);
            fields['field_'+elem.data('field_id')] = JSON.parse(elem.find('.jsondata').html());
        });

        fieldsElem.find('.SearchVideos').click(toggleSearchVideos);
        fieldsElem.find('.SearchVideos input[type=text]').keypress(disableEnter);
        fieldsElem.find('.SVWrapper .Button').click(searchForVideos);
        fieldsElem.find('.SubmitVideoUrl').click(submitVideoUrl);

        fieldsElem.delegate('.VideosResults .video .add', 'click', addVideo);
        fieldsElem.delegate('.DelVideo', 'click', delVideo);
        fieldsElem.delegate('.ClearVideoSearch', 'click', clearVideoSearch);

        fieldsElem.find('.AssignedVideos').sortable({
            cursor: 'move', opacity: 0.6, handle: '.MoveVideo', update:syncOrderNumbers,
            helper: function(event, ui){
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            },
            forcePlaceholderSize: true,
            start: function(event, ui){
                ui.placeholder.html('<td colspan="20"></td>');
            },
            placeholder: 'cvideo-reorder-state-highlight'
        });

        fieldsElem.find('.AssignedVideos .CVItem .PlayVideo').colorbox({iframe:true, width: 450, height:375});
    };

    // ----------------------------------------------------------------------

    function toggleSearchVideos(e){
        $(e.target).closest('.CVTable').find('.SVWrapperTR').toggle();
        return false;
    }

    // ----------------------------------------------------------------------

    function disableEnter(e){
        if (e.which == 13)  {
            $(e.target).closest('.CVField').find('.searchbutton').click();
            return false;
        }
    }

    // ----------------------------------------------------------------------

    function syncOrderNumbers(){
        var attr;

        // Loop over all Channel Videos Fields
        fieldsElem.each(function(FieldIndex, VideoField){


            // Loop over all individual Videos
            $(VideoField).find('.CVItem').each(function(VideoIndex, VideoItem){

                // Loop Over all Input Elements of the Relation Item
                $(VideoItem).find('input, textarea, select').each(function(){
                    attr = $(this).attr('name').replace(/\[videos\]\[.*?\]/, '[videos][' + (VideoIndex+1) + ']');
                    $(this).attr('name', attr);
                });
            });

            // Add Zebra
            $(VideoField).find('.CVItem').removeClass('odd');
            $(VideoField).find('.CVItem:odd').addClass('odd');
        });

    }

    // ----------------------------------------------------------------------

    function delVideo(e){

        var VideoID = $(e.target).data('id');

        // Send Ajax
        if (VideoID) {
            $.get(ChannelVideos.ACT_URL, {video_id: VideoID, ajax_method: 'delete_video'}, function(){

            });
        }

        $(e.target).closest('.CVItem').fadeOut('slow', function(){
            $(this).remove(); syncOrderNumbers();
        });

    }

    // ----------------------------------------------------------------------

    function clearVideoSearch(e){

        var customField = jQuery(e.target).closest('.CVField');

        TargetBox.find('.VideosResults .inner').empty();

        return false;
    }

    // ----------------------------------------------------------------------

    function searchForVideos(e){
        e.preventDefault();

        var results = {};
        var params = {};
        var customField = $(e.target).closest('.CVField');
        var videoServices = fields['field_'+customField.data('field_id')].services;

        // Grab all input fields
        customField.find('.SVWrapper .cvsearch').find('input[type=text], input[type=hidden]').each(function(){
            params[$(this).attr('rel')] = jQuery(this).val();
        });

        for (var i = 0; i < videoServices.length; i++) {
            customField.find('.VideosResults .results-'+videoServices[i]).show().find('.LoadingVideos').show().siblings('.inner').empty();

            if (videoServices[i] == 'youtube') {
                youtubeSearchVideos(params, customField);
            } else {
                vimeoSearchVideos(params, customField);
            }
        }
    }

    // ----------------------------------------------------------------------

    function addVideoResults(service, items, customField){
        var Label = (service == 'youtube') ? 'Youtube' : 'Vimeo';
        var html = '';

        if (items.length === 0) {
            html += '<p>No Results Found...</p>';
        }

        for (var i = 0; i < items.length; i++) {
            html += '<div class="video" rel="'+service+'|'+items[i].id+'" id="'+service+'__'+items[i].id+'">';
            html +=     '<img src="' + items[i].img_url + '" width="100px" height="75px">';
            html +=     '<small>'+ items[i].title +'</small>';
            html +=     '<span>';
            html +=         '<a href="' + items[i].vid_url + '" class="play">&nbsp;</a>';
            html +=         '<a href="#" class="add">&nbsp;</a>';
            html +=     '</span>';
            html += '</div>';
        }

        html += '<br clear="all"></div>';

        customField.find('.VideosResults .results-'+service).find('.LoadingVideos').hide().siblings('.inner').show().html(html);
        customField.find('.VideosResults .video .play').colorbox({iframe:true, width: 450, height:375});
    }

    // ----------------------------------------------------------------------

    function submitVideoUrl(e){
        var videoUrl = prompt("Video URL?", "");
        if (videoUrl === null) return false;

        var customField = $(e.target).closest('div.CVField');
        var videoServices = fields['field_'+customField.data('field_id')].services;

        customField.find('.SVWrapperTR').show();

        for (var i = 0; i < videoServices.length; i++) {
            customField.find('.VideosResults .results-'+videoServices[i]).show().find('.LoadingVideos').show().siblings('.inner').empty();

            if (videoServices[i] == 'youtube') {
                youtubeSubmitUrl(videoUrl, customField);
            } else {
                vimeoSubmitUrl(videoUrl, customField);
            }
        }

        return false;
    }

    // ----------------------------------------------------------------------

    function addVideoToTable(video, field_id) {
        var field_data = fields['field_'+field_id];
        var customField = $('#ChannelVideos'+field_id);
        var html = '';

        customField.find('#'+video.service+'__'+video.service_video_id).slideUp();

        var video_date = new Date();
        video_date.setTime( (video.video_date*1000) );

        if (field_data.layout == 'table') {
            html += '<tr class="CVItem">';
            html +=     '<td><a href="'+video.video_url+'" class="PlayVideo"><img src="'+video.video_img_url+'" width="100px" height="75px"></a></td>';
            html +=     '<td>'+video.video_title+'</td>';
            html +=     '<td>'+video.video_author+'</td>';
            html +=     '<td>'+(video.video_duration/60).toFixed(2)+' min</td>';
            html +=     '<td>'+video.video_views+'</td>';
            html +=     '<td>'+video_date.toDateString()+'</td>';
            html +=     '<td>';
            html +=         '<a href="javascript:void(0)" class="MoveVideo">&nbsp;</a>';
            html +=         '<a href="javascript:void(0)" class="DelVideo" data-id="'+video.video_id+'">&nbsp;</a>';

            if (video.video_id > 0) {
                html += '<input name="'+field_data.field_name+'[videos][0][video_id]" type="hidden" value="'+video.video_id+'">';
            } else {
                html += '<textarea name="'+field_data.field_name+'[videos][0][data]" style="display:none">'+JSON.stringify(video)+'</textarea>';
            }

            html +=     '</td>';
            html += '</tr>';
        } else {
            html += '<div class="CVItem VideoTile">';
            html +=     '<a href="'+video.video_url+'" class="PlayVideo"><img src="'+video.video_img_url+'" width="100px" height="75px"></a>';
            html +=     '<small>'+video.video_title+'</small>';
            html +=     '<span>';
            html +=         '<a href="javascript:void(0)" class="MoveVideo">&nbsp;</a>';
            html +=         '<a href="javascript:void(0)" class="DelVideo" data-id="'+video.video_id+'">&nbsp;</a>';

            if (video.video_id > 0) {
                html += '<input name="'+field_data.field_name+'[videos][0][video_id]" type="hidden" value="'+video.video_id+'">';
            } else {
                html += '<textarea name="'+field_data.field_name+'[videos][0][data]" style="display:none">'+JSON.stringify(video)+'</textarea>';
            }
            html +=     '</span>';
            html += '</div>';
        }

        customField.find('.AssignedVideos .NoVideos').hide();
        customField.find('.AssignedVideos').append(html);
        syncOrderNumbers();
        customField.find('.AssignedVideos .CVItem .PlayVideo').colorbox({iframe:true, width: 450, height:375});
    }

    // ----------------------------------------------------------------------

    function addVideo(e){
        var Parent = jQuery(e.target).closest('div.video');
        var customField = jQuery(e.target).closest('div.CVField');
        var field_id = customField.data('field_id');

        jQuery(e.target).addClass('loading');

        var Params = {};
        Params.ajax_method = 'get_video';
        Params.service = Parent.attr('rel').split('|')[0];
        Params.video_id = Parent.attr('rel').split('|')[1];
        Params.field_id = field_id;
        Params.field_name = fields['field_'+field_id].field_name;
        Params.field_layout = fields['field_'+field_id].layout;

        if (Params.service == 'youtube') {
            youtubeGetVideo(Params.video_id, field_id, addVideoToTable);

            return false;
        } else {
            vimeoGetVideo(Params.video_id, field_id, addVideoToTable);
        }

        return false;
    }

    // ----------------------------------------------------------------------

    function youtubeSearchVideos(params, customField){
        var i, entry, video_id;

        if (params.author) params.keywords += ' ' +params.author;

        $.ajax({
            crossDomain: true,
            dataType: 'json', type: 'GET',
            url: 'https://www.googleapis.com/youtube/v3/search',
            data: {q:params.keywords, maxResults:params.limit, type:'video', part:'snippet', key:youtubeKey},
            success: function(rdata){
                var videos = [];

                for (var i = 0; i < rdata.items.length; i++) {
                    var video = rdata.items[i];
                    var videoID = video.id.videoId;

                    videos.push({
                        id: videoID,
                        title: video.snippet.title,
                        img_url: 'https://i.ytimg.com/vi/' + videoID + '/default.jpg',
                        vid_url: 'https://www.youtube.com/embed/'+videoID
                    });

                }
                addVideoResults('youtube', videos, customField);
            }
        });
    }

    // ----------------------------------------------------------------------

    function youtubeSubmitUrl(url, customField){
        var servicebox = customField.find('.VideosResults .results-youtube').show();
        var inner = servicebox.find('.inner');
        var loading = servicebox.find('.LoadingVideos');
        var id = null;
        var parts;

        if (url.indexOf('youtube') === -1 && url.indexOf('youtu.be') === -1) {
            loading.hide();
            inner.html('<p>Not a valid Youtube URL</p>');
            return;
        }

        // http://www.youtube.com/watch?v=9bZkp7q19f0
        if (url.indexOf('youtube.com/watch') > 0) {
            parts = parseUrl(url);
            id = getQueryVariable(parts.query, 'v');
        }

        // http://youtu.be/9bZkp7q19f0
        else if (url.indexOf('youtu.be') > 0) {
            parts = url.split('/');
            id = parts[(parts.length-1)];
        }

        // http://www.youtube.com/embed/9bZkp7q19f0
        else if (url.indexOf('youtube.com/embed') > 0) {
            parts = url.split('/');
            id = parts[(parts.length-1)];
        }

        if (id === null) {
            loading.hide();
            inner.html('<p>Could not parse Youtube ID from that URL</p>');
            return;
        }

        $.ajax({
            crossDomain: true,
            dataType: 'json', type: 'GET',
            url: 'https://www.googleapis.com/youtube/v3/videos',
            data: {id:id, part:'snippet', key:youtubeKey},
            success: function(rdata){
                loading.hide();

                if (typeof rdata.pageInfo === 'undefined' || typeof rdata.pageInfo.totalResults != 1) {
                    inner.html('<p>Youtube could not find the video. (ID: '+id+')</p>');
                }

                var video = rdata.items[0];
                var videoId = video.id;

                addVideoResults('youtube', [{
                    id: videoId,
                    title: video.snippet.title,
                    img_url: 'https://i.ytimg.com/vi/' + videoId + '/default.jpg',
                    vid_url: 'https://www.youtube.com/embed/'+videoId
                }], customField);
            }
        });
    }

    // ----------------------------------------------------------------------

    function youtubeGetVideo(id, field_id, callback){
        $.ajax({
            crossDomain: true,
            dataType: 'json', type: 'GET',
            url: 'https://www.googleapis.com/youtube/v3/videos',
            data: {id:id, part:'snippet,statistics,contentDetails', key:youtubeKey},
            success: function(rdata){
                var videoID = id;
                var video = {};
                var entry = rdata.items[0];

                video.service = 'youtube';
                video.service_video_id = videoID;
                video.video_id = 0;
                video.video_url = 'http://www.youtube.com/embed/' + videoID;
                video.video_img_url = 'http://i.ytimg.com/vi/' + videoID + '/default.jpg';
                video.video_title = entry.snippet.title;
                video.video_desc = entry.snippet.description;
                video.video_username = entry.snippet.channelTitle;
                video.video_author = entry.snippet.channelTitle;
                video.video_author_id = 0;
                video.video_duration = nezasa.iso8601.Period.parseToTotalSeconds(entry.contentDetails.duration);
                video.video_date = (new Date(entry.publishedAt).getTime()/1000);

                video.video_views = 0;

                // When the video has no views, stats will not exists
                if (typeof entry.statistics != 'undefined'
                    && typeof entry.statistics.viewCount != 'undefined') {
                    video.video_views = entry.statistics.viewCount;
                }

                callback(video, field_id);
            }
        });
    }

    // ----------------------------------------------------------------------

    function vimeoSearchVideos(params, customField){
        var i, entry, img_url, thumb;

        var servicebox = customField.find('.VideosResults .results-vimeo').show();
        var inner = servicebox.find('.inner');
        var loading = servicebox.find('.LoadingVideos');

        var urlparams = {};
        urlparams.format = 'jsonp';
        urlparams.method = 'vimeo.videos.search';
        urlparams.query = params.keywords;
        if (params.author) urlparams.user_id = params.author;
        urlparams.per_page = params.limit;
        urlparams.full_response = '1';
        var url = vimeoGetTheSignedUrl($.param(urlparams));
        //url = decodeURIComponent(url.replace(/\+/g,  " "));

        $.ajax({
            crossDomain: true,
            dataType: "jsonp",
            url: url,
            jsonp: false, jsonpCallback: "vimeoCallback",
            cache: true, // Adding the extra cache params breaks it!!
            success: function(rdata){
                if (rdata.stat != 'ok') {
                    loading.hide();
                    inner.html('<p>The vimeo request failed!</p>');
                    return;
                }

                if (rdata.videos.on_this_page === 0) {
                    loading.hide();
                    inner.html('<p>No results found..</p>');
                    return;
                }

                var Videos = [];

                for (var i = 0; i < rdata.videos.video.length; i++) {
                    entry = rdata.videos.video[i];

                    for (var ii = entry.thumbnails.thumbnail.length - 1; ii >= 0; ii--) {
                        thumb = entry.thumbnails.thumbnail[ii];
                        if (thumb.width == '100' || thumb.height == '100') {
                            img_url = thumb._content;
                        }
                    }

                    Videos.push({
                        id: entry.id,
                        title: entry.title,
                        img_url: img_url,
                        vid_url: 'http://player.vimeo.com/video/' + entry.id + '?title=0&byline=0&portrait=0'
                    });

                }

                addVideoResults('vimeo', Videos, customField);
            }
        });

    }

    // ----------------------------------------------------------------------

    function vimeoSubmitUrl(url, customField){
        var servicebox = customField.find('.VideosResults .results-vimeo').show();
        var inner = servicebox.find('.inner');
        var loading = servicebox.find('.LoadingVideos');
        var id = null;
        var parts, entry, thumb, img_url;

        if (url.indexOf('vimeo') === -1) {
            loading.hide();
            inner.html('<p>Not a valid Vimeo URL</p>');
            return;
        }

        // https://vimeo.com/58161697
        if (url.indexOf('vimeo.com/') > 0) {
            parts = url.split('/');
            id = parts[(parts.length-1)];
        }

        if (id === null) {
            loading.hide();
            inner.html('<p>Could not parse Vimeo ID from that URL</p>');
            return;
        }

        var urlparams = {};
        urlparams.format = 'jsonp';
        urlparams.method = 'vimeo.videos.getInfo';
        urlparams.video_id = id;
        urlparams.full_response = '1';

        url = vimeoGetTheSignedUrl($.param(urlparams));
        //url = decodeURIComponent(url.replace(/\+/g,  " "));

        $.ajax({
            crossDomain: true,
            dataType: "jsonp",
            url: url,
            jsonp: false, jsonpCallback: "vimeoCallback",
            cache: true, // Adding the extra cache params breaks it!!
            success: function(rdata){
                if (rdata.stat != 'ok') {
                    loading.hide();
                    inner.html('<p>The vimeo request failed!</p>');
                    return;
                }

                if (rdata.video.length === 0) {
                    loading.hide();
                    inner.html('<p>No results found..</p>');
                    return;
                }

                var Videos = [];

                for (var i = 0; i < rdata.video.length; i++) {
                    entry = rdata.video[i];

                    for (var ii = entry.thumbnails.thumbnail.length - 1; ii >= 0; ii--) {
                        thumb = entry.thumbnails.thumbnail[ii];
                        if (thumb.width == '100' || thumb.height == '100') {
                            img_url = thumb._content;
                        }
                    }

                    Videos.push({
                        id: entry.id,
                        title: entry.title,
                        img_url: img_url,
                        vid_url: 'http://player.vimeo.com/video/' + entry.id + '?title=0&byline=0&portrait=0'
                    });

                }

                addVideoResults('vimeo', Videos, customField);
            }
        });

        return;
    }

    // ----------------------------------------------------------------------

    function vimeoGetVideo(id, field_id, callback){
        var entry, thumb, img_url;

        var urlparams = {};
        urlparams.format = 'jsonp';
        urlparams.method = 'vimeo.videos.getInfo';
        urlparams.video_id = id;
        urlparams.full_response = '1';
        var url = vimeoGetTheSignedUrl($.param(urlparams));
        //url = decodeURIComponent(url.replace(/\+/g,  " "));

        $.ajax({
            crossDomain: true,
            dataType: "jsonp",
            url: url,
            jsonp: false, jsonpCallback: "vimeoCallback",
            cache: true, // Adding the extra cache params breaks it!!
            success: function(rdata){
                if (rdata.stat != 'ok') {
                    loading.hide();
                    inner.html('<p>The vimeo request failed!</p>');
                    return;
                }

                entry = rdata.video[0];

                for (var ii = entry.thumbnails.thumbnail.length - 1; ii >= 0; ii--) {
                    thumb = entry.thumbnails.thumbnail[ii];
                    if (thumb.width == '100' || thumb.height == '100') {
                        img_url = thumb._content;
                    }
                }

                var Video = {};
                Video.service = 'vimeo';
                Video.service_video_id = entry.id;
                Video.video_id = 0;
                Video.video_url = 'http://player.vimeo.com/video/' + entry.id + '?title=0&byline=0&portrait=0';
                Video.video_img_url = img_url;
                Video.video_title = entry.title;
                Video.video_desc = entry.description;
                Video.video_username = entry.owner.username;
                Video.video_author = entry.owner.display_name;
                Video.video_author_id = entry.owner.id;
                Video.video_duration = entry.duration;
                Video.video_views = entry.number_of_plays;
                Video.video_date = (new Date(entry.upload_date.replace(' ', 'T')+'-00:00').getTime()/1000);

                callback(Video, field_id);
            }
        });


    }

    // ----------------------------------------------------------------------

    function vimeoGetTheSignedUrl(params) {
        var recvd_resp = "nothing";
        var oauth = new ChannelVideos.OAuthSimple();

        try {
            var oauthObject = oauth.sign({
                path: 'https://vimeo.com/api/rest/v2',
                parameters: params,
                signatures: {
                    api_key: vimeoConsumerKey,
                    shared_secret: vimeoSharedSecret
                }
            });
            recvd_resp = oauthObject.signed_url;
        }
        catch (e) {
            alert(e);
        }

        return recvd_resp;
    }

    // ----------------------------------------------------------------------

    function parseUrl(str, component) {
        var key = ['source', 'scheme', 'authority', 'userInfo', 'user', 'pass', 'host', 'port',
                'relative', 'path', 'directory', 'file', 'query', 'fragment'],
        ini = {},
        mode = (ini['phpjs.parse_url.mode'] &&
          ini['phpjs.parse_url.mode'].local_value) || 'php',
        parser = {
          php: /^(?:([^:\/?#]+):)?(?:\/\/()(?:(?:()(?:([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?()(?:(()(?:(?:[^?#\/]*\/)*)()(?:[^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
          strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
          loose: /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/\/?)?((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/ // Added one optional slash to post-scheme to catch file:/// (should restrict this)
        };

        var m = parser[mode].exec(str),
        uri = {},
        i = 14;
        while (i--) {
        if (m[i]) {
          uri[key[i]] = m[i];
        }
        }

        if (component) {
        return uri[component.replace('PHP_URL_', '').toLowerCase()];
        }
        if (mode !== 'php') {
        var name = (ini['phpjs.parse_url.queryKey'] &&
            ini['phpjs.parse_url.queryKey'].local_value) || 'queryKey';
        parser = /(?:^|&)([^&=]*)=?([^&]*)/g;
        uri[name] = {};
        uri[key[12]].replace(parser, function ($0, $1, $2) {
          if ($1) {uri[name][$1] = $2;}
        });
        }
        delete uri.source;
        return uri;
    }

    // ----------------------------------------------------------------------

    function getQueryVariable(url, variable) {
        var query = url;
        var vars = query.split('&');
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split('=');
            if (decodeURIComponent(pair[0]) == variable) {
                return decodeURIComponent(pair[1]);
            }
        }
        return null;
    }

    // ----------------------------------------------------------------------

    //OAuthSimple Class :)
    //var vimeoConsumerKey = 'be6f5726995c952d80c62fea3bfdacbd92f477ae';
    //var vimeoSharedSecret = '5e082c746fefa4bbad7217d7ca8aef12dae4dab5';
    var vimeoConsumerKey = '1a8a81eaf6658d0dbb955f0386f484c1f9b55ece';
    var vimeoSharedSecret = '2ffac38d1ee9eac6a2389269aa19429a927ff07b';

    ChannelVideos.OAuthSimple = function (consumer_key, shared_secret) {
        this._secrets = {};


        // General configuration options.
        if (consumer_key != null)
            this._secrets['consumer_key'] = consumer_key;
        if (shared_secret != null)
            this._secrets['shared_secret'] = shared_secret;
        this._default_signature_method = "HMAC-SHA1";
        this._action = "GET";
        this._nonce_chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

        /* set the parameters either from a hash or a string
        *
        * @param {string,object} List of parameters for the call, this can either be a URI string (e.g. "foo=bar&gorp=banana" or an object/hash)
        */

        this.setParameters = function (parameters) {
            if (parameters == null)
                parameters = {};
            if (typeof (parameters) == 'string')
                parameters = this._parseParameterString(parameters);
            this._parameters = parameters;
            if (this._parameters['oauth_nonce'] == null)
                this._getNonce();
            if (this._parameters['oauth_timestamp'] == null)
                this._getTimestamp();
            if (this._parameters['oauth_method'] == null)
                this.setSignatureMethod();
            if (this._parameters['oauth_consumer_key'] == null)
                this._getApiKey();
            if (this._parameters['oauth_token'] == null)
                this._getAccessToken();

            return this;
        };

        /* convienence method for setParameters
        *
        * @param parameters {string,object} See .setParameters
        */
        this.setQueryString = function (parameters) {
            return this.setParameters(parameters);
        };

        /* Set the target URL (does not include the parameters)
        *
        * @param path {string} the fully qualified URI (excluding query arguments) (e.g "http://example.org/foo")
        */
        this.setURL = function (path) {
            if (path == '')
                throw ('No path specified for OAuthSimple.setURL');
            if (path.indexOf(' ') != -1) {
                $("#path").select();
                throw ('Space detected in request path/URL');
            };
            this._path = path;
            return this;
        };

        /* convienence method for setURL
        *
        * @param path {string} see .setURL
        */
        this.setPath = function (path) {
            return this.setURL(path);
        };

        /* set the "action" for the url, (e.g. GET,POST, DELETE, etc.)
        *
        * @param action {string} HTTP Action word.
        */
        this.setAction = function (action) {
            if (action == null)
                action = "GET";
            action = action.toUpperCase();
            if (action.match('[^A-Z]'))
                throw ('Invalid action specified for OAuthSimple.setAction');
            this._action = action;
            return this;
        };

        /* set the signatures (as well as validate the ones you have)
        *
        * @param signatures {object} object/hash of the token/signature pairs {api_key:, shared_secret:, oauth_token: oauth_secret:}
        */
        this.setTokensAndSecrets = function (signatures) {
            if (signatures)
                for (var i in signatures)
                    this._secrets[i] = signatures[i];
            // Aliases
            if (this._secrets['api_key'])
                this._secrets.consumer_key = this._secrets.api_key;
            if (this._secrets['access_token'])
                this._secrets.oauth_token = this._secrets.access_token;
            if (this._secrets['access_secret'])
                this._secrets.oauth_secret = this._secrets.access_secret;
            // Gauntlet
            if (this._secrets.consumer_key == null)
                throw ('Missing required consumer_key in OAuthSimple.setTokensAndSecrets');
            if (this._secrets.shared_secret == null)
                throw ('Missing required shared_secret in OAuthSimple.setTokensAndSecrets');
            if ((this._secrets.oauth_token != null) && (this._secrets.oauth_secret == null))
                throw ('Missing oauth_secret for supplied oauth_token in OAuthSimple.setTokensAndSecrets');
            return this;
        };

        /* set the signature method (currently only Plaintext or SHA-MAC1)
        *
        * @param method {string} Method of signing the transaction (only PLAINTEXT and SHA-MAC1 allowed for now)
        */
        this.setSignatureMethod = function (method) {
            if (method == null)
                method = this._default_signature_method;
            //TODO: accept things other than PlainText or SHA-MAC1
            if (method.toUpperCase().match(/(PLAINTEXT|HMAC-SHA1)/) == null)
                throw ('Unknown signing method specified for OAuthSimple.setSignatureMethod');
            this._parameters['oauth_signature_method'] = method.toUpperCase();
            return this;
        };

        /* sign the request
        *
        * note: all arguments are optional, provided you've set them using the
        * other helper functions.
        *
        * @param args {object} hash of arguments for the call
        * {action:, path:, parameters:, method:, signatures:}
        * all arguments are optional.
        */
        this.sign = function (args) {
            if (args == null)
                args = {};
            // Set any given parameters
            if (args['action'] != null)
                this.setAction(args['action']);
            if (args['path'] != null)
                this.setPath(args['path']);
            if (args['method'] != null)
                this.setSignatureMethod(args['method']);
            this.setTokensAndSecrets(args['signatures']);
            if (args['parameters'] != null)
                this.setParameters(args['parameters']);
            // check the parameters
            var normParams = this._normalizedParameters();
            var sig = this._generateSignature(normParams);
            this._parameters['oauth_signature'] = sig.signature;
            return {
                parameters: this._parameters,
                sig_string: sig.sig_string,
                signature: this._oauthEscape(this._parameters['oauth_signature']),
                signed_url: this._path + '?' + this._normalizedParameters(),
                header: this.getHeaderString()
            };
        };

        /* Return a formatted "header" string
        *
        * NOTE: This doesn't set the "Authorization: " prefix, which is required.
        * I don't set it because various set header functions prefer different
        * ways to do that.
        *
        * @param args {object} see .sign
        */
        this.getHeaderString = function (args) {
            if (this._parameters['oauth_signature'] == null)
                this.sign(args);

            var result = 'OAuth ';
            for (var pName in this._parameters) {
                if (pName.match(/^oauth/) == null)
                    continue;
                if ((this._parameters[pName]) instanceof Array) {
                    var pLength = this._parameters[pName].length;
                    for (var j = 0; j < pLength; j++) {
                        result += pName + '="' + this._oauthEscape(this._parameters[pName][j]) + '" ';
                    }
                }
                else {
                    result += pName + '="' + this._oauthEscape(this._parameters[pName]) + '" ';
                }
            }
            return result;
        };

        // Start Private Methods.

        /* convert the parameter string into a hash of objects.
        *
        */
        this._parseParameterString = function (paramString) {
            var elements = paramString.split('&');
            var result = {};
            for (var element = elements.shift(); element; element = elements.shift()) {
                var keyToken = element.split('=');
                var value;
                if (keyToken[1])
                    value = decodeURIComponent(keyToken[1]);
                if (result[keyToken[0]]) {
                    if (!(result[keyToken[0]] instanceof Array)) {
                        result[keyToken[0]] = Array(result[keyToken[0]], value);
                    }
                    else {
                        result[keyToken[0]].push(value);
                    }
                }
                else {
                    result[keyToken[0]] = value;
                }
            }
            return result;
        };

        this._oauthEscape = function (string) {
            if (string == null)
                return "";
            if (string instanceof Array) {
                throw ('Array passed to _oauthEscape');
            }
            return encodeURIComponent(string).replace(/\!/g, "%21").
            replace(/\*/g, "%2A").
            replace(/'/g, "%27").
            replace(/\(/g, "%28").
            replace(/\)/g, "%29");
        };

        this._getNonce = function (length) {
            if (length == null)
                length = 5;
            var result = "";
            var cLength = this._nonce_chars.length;
            for (var i = 0; i < length; i++) {
                var rnum = Math.floor(Math.random() * cLength);
                result += this._nonce_chars.substring(rnum, rnum + 1);
            }
            this._parameters['oauth_nonce'] = result;
            return result;
        };

        this._getApiKey = function () {
            if (this._secrets.consumer_key == null)
                throw ('No consumer_key set for OAuthSimple.');
            this._parameters['oauth_consumer_key'] = this._secrets.consumer_key;
            return this._parameters.oauth_consumer_key;
        };

        this._getAccessToken = function () {
            if (this._secrets['oauth_secret'] == null)
                return '';
            if (this._secrets['oauth_token'] == null)
                throw ('No oauth_token (access_token) set for OAuthSimple.');
            this._parameters['oauth_token'] = this._secrets.oauth_token;
            return this._parameters.oauth_token;
        };

        this._getTimestamp = function () {
            var d = new Date();
            var ts = Math.floor(d.getTime() / 1000);
            this._parameters['oauth_timestamp'] = ts;
            return ts;
        };

        this._normalizedParameters = function () {
            var elements = new Array();
            var paramNames = [];
            var ra = 0;

            for (var paramName in this._parameters) {
                if (ra++ > 1000)
                    throw ('runaway 1');
                paramNames.unshift(paramName);
            }
            paramNames = paramNames.sort();
            var pLen = paramNames.length;
            for (var i = 0; i < pLen; i++) {
                paramName = paramNames[i];
                //skip secrets.
                if (paramName.match(/\w+_secret/))
                    continue;
                if (this._parameters[paramName] instanceof Array) {
                    var sorted = this._parameters[paramName].sort();
                    var spLen = sorted.length;
                    for (var j = 0; j < spLen; j++) {
                        if (ra++ > 1000)
                            throw ('runaway 1');
                        elements.push(this._oauthEscape(paramName) + '=' +
                                  this._oauthEscape(sorted[j]));
                    }
                    continue;
                }
                elements.push(this._oauthEscape(paramName) + '=' +
                              this._oauthEscape(this._parameters[paramName]));
            }
            return elements.join('&');
        };

        this.b64_hmac_sha1 = function (k, d, _p, _z) {
            // heavily optimized and compressed version of http://pajhome.org.uk/crypt/md5/sha1.js
            // _p = b64pad, _z = character size; not used here but I left them available just in case
            if (!_p) { _p = '='; } if (!_z) { _z = 8; } function _f(t, b, c, d) { if (t < 20) { return (b & c) | ((~b) & d); } if (t < 40) { return b ^ c ^ d; } if (t < 60) { return (b & c) | (b & d) | (c & d); } return b ^ c ^ d; } function _k(t) { return (t < 20) ? 1518500249 : (t < 40) ? 1859775393 : (t < 60) ? -1894007588 : -899497514; } function _s(x, y) { var l = (x & 0xFFFF) + (y & 0xFFFF), m = (x >> 16) + (y >> 16) + (l >> 16); return (m << 16) | (l & 0xFFFF); } function _r(n, c) { return (n << c) | (n >>> (32 - c)); } function _c(x, l) { x[l >> 5] |= 0x80 << (24 - l % 32); x[((l + 64 >> 9) << 4) + 15] = l; var w = [80], a = 1732584193, b = -271733879, c = -1732584194, d = 271733878, e = -1009589776; for (var i = 0; i < x.length; i += 16) { var o = a, p = b, q = c, r = d, s = e; for (var j = 0; j < 80; j++) { if (j < 16) { w[j] = x[i + j]; } else { w[j] = _r(w[j - 3] ^ w[j - 8] ^ w[j - 14] ^ w[j - 16], 1); } var t = _s(_s(_r(a, 5), _f(j, b, c, d)), _s(_s(e, w[j]), _k(j))); e = d; d = c; c = _r(b, 30); b = a; a = t; } a = _s(a, o); b = _s(b, p); c = _s(c, q); d = _s(d, r); e = _s(e, s); } return [a, b, c, d, e]; } function _b(s) { var b = [], m = (1 << _z) - 1; for (var i = 0; i < s.length * _z; i += _z) { b[i >> 5] |= (s.charCodeAt(i / 8) & m) << (32 - _z - i % 32); } return b; } function _h(k, d) { var b = _b(k); if (b.length > 16) { b = _c(b, k.length * _z); } var p = [16], o = [16]; for (var i = 0; i < 16; i++) { p[i] = b[i] ^ 0x36363636; o[i] = b[i] ^ 0x5C5C5C5C; } var h = _c(p.concat(_b(d)), 512 + d.length * _z); return _c(o.concat(h), 512 + 160); } function _n(b) { var t = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/", s = ''; for (var i = 0; i < b.length * 4; i += 3) { var r = (((b[i >> 2] >> 8 * (3 - i % 4)) & 0xFF) << 16) | (((b[i + 1 >> 2] >> 8 * (3 - (i + 1) % 4)) & 0xFF) << 8) | ((b[i + 2 >> 2] >> 8 * (3 - (i + 2) % 4)) & 0xFF); for (var j = 0; j < 4; j++) { if (i * 8 + j * 6 > b.length * 32) { s += _p; } else { s += t.charAt((r >> 6 * (3 - j)) & 0x3F); } } } return s; } function _x(k, d) { return _n(_h(k, d)); } return _x(k, d);
        };

        this._generateSignature = function () {

            var secretKey = this._oauthEscape(this._secrets.shared_secret) + '&' +
                this._oauthEscape(this._secrets.oauth_secret);
            if (this._parameters['oauth_signature_method'] == 'PLAINTEXT') {
                return { sig_string: null, signature: secretKey };
            }
            if (this._parameters['oauth_signature_method'] == 'HMAC-SHA1') {
                var sigString = this._oauthEscape(this._action) + '&' + this._oauthEscape(this._path) + '&' + this._oauthEscape(this._normalizedParameters());
                return { 'sig_string': sigString, 'signature': this.b64_hmac_sha1(secretKey, sigString) };
            }
            return null;
        };

        return this;
    };

    /* --------------------------------End of Simple OAuth Class------------------------------------- */


}(window, jQuery));

//********************************************************************************* //

/*
 * Shared and maintained by [Nezasa](http://www.nezasa.com)
 * Published under [Apache 2.0 license](http://www.apache.org/licenses/LICENSE-2.0.html)
 * © Nezasa, 2012-2013
 *
 * ---
 *
 * Javascript library for parsing of ISO 8601 durations. Supported are durations of
 * the form P3Y6M4DT12H30M17S or PT1S or P1Y4DT1H3S etc.
 *
 * @author Nezasa AG -- https://github.com/nezasa
 * @contributor Jason "Palamedes" Ellis -- https://github.com/palamedes
 */

(function( nezasa, undefined ) {

    // create sub packages
    if (!nezasa.iso8601) nezasa.iso8601 = {};
    if (!nezasa.iso8601.Period) nezasa.iso8601.Period = {};

    //---- public properties

    /**
     * version of the ISO8601 version
     */
    nezasa.iso8601.version = '0.2';

    //---- public methods

    /**
     * Returns an array of the duration per unit. The normalized sum of all array elements
     * represents the total duration.
     *
     * - array[0]: years
     * - array[1]: months
     * - array[2]: weeks
     * - array[3]: days
     * - array[4]: hours
     * - array[5]: minutes
     * - array[6]: seconds
     *
     * @param period iso8601 period string
     * @param distributeOverflow if 'true', the unit overflows are merge into the next higher units. Defaults to 'false'.
     */
    nezasa.iso8601.Period.parse = function(period, distributeOverflow) {
        return parsePeriodString(period, distributeOverflow);
    };

    /**
     * Returns the total duration of the period in seconds.
     */
    nezasa.iso8601.Period.parseToTotalSeconds = function(period) {

        var multiplicators = [31104000 /* year   (360*24*60*60) */,
            2592000  /* month  (30*24*60*60) */,
            604800   /* week   (24*60*60*7) */,
            86400    /* day    (24*60*60) */,
            3600     /* hour   (60*60) */,
            60       /* minute (60) */,
            1        /* second (1) */];
        var durationPerUnit = parsePeriodString(period);
        var durationInSeconds = 0;

        for (var i = 0; i < durationPerUnit.length; i++) {
            durationInSeconds += durationPerUnit[i] * multiplicators[i];
        }

        return durationInSeconds;
    };

    /**
     * Return boolean based on validity of period
     * @param period
     * @return {Boolean}
     */
    nezasa.iso8601.Period.isValid = function(period) {
        try {
            parsePeriodString(period);
            return true;
        } catch(e) {
            return false;
        }
    }

    /**
     * Returns a more readable string representation of the ISO8601 period.
     * @param period the ISO8601 period string
     * @param unitName the names of the time units if there is only one (such as hour or minute).
     *        Defaults to ['year', 'month', 'week', 'day', 'hour', 'minute', 'second'].
     * @param unitNamePlural thenames of the time units if there are several (such as hours or minutes).
     *        Defaults to ['years', 'months', 'weeks', 'days', 'hours', 'minutes', 'seconds'].
     * @param distributeOverflow if 'true', the unit overflows are merge into the next higher units. Defaults to 'false'.
     */
    nezasa.iso8601.Period.parseToString = function(period, unitNames, unitNamesPlural, distributeOverflow) {

        var result = ['', '', '', '', '', '', ''];
        var durationPerUnit = parsePeriodString(period, distributeOverflow);

        // input validation (use english as default)
        if (!unitNames)       unitNames       = ['year', 'month', 'week', 'day', 'hour', 'minute', 'second'];
        if (!unitNamesPlural) unitNamesPlural = ['years', 'months', 'weeks', 'days', 'hours', 'minutes', 'seconds'];

        // assemble string per unit
        for (var i = 0; i < durationPerUnit.length; i++) {
            if (durationPerUnit[i] > 0) {
                if   (durationPerUnit[i] == 1) result[i] = durationPerUnit[i] + " " + unitNames[i];
                else                           result[i] = durationPerUnit[i] + " " + unitNamesPlural[i];
            }
        }

        // trim because of space at very end and because of join(" ")
        // replace double spaces because of join(" ") and empty strings
        // Its actually possible to get more than 2 spaces in a row,
        //   so lets get 2+ spaces and remove them
        return result.join(' ').trim().replace(/[ ]{2,}/g,' ');
    };

    //---- private methods

    /**
     * Parses a ISO8601 period string.
     * @param period iso8601 period string
     * @param _distributeOverflow if 'true', the unit overflows are merge into the next higher units.
     */
    function parsePeriodString(period, _distributeOverflow) {

        // regex splits as follows
        // grp0 omitted as it is equal to the sample
        //
        // | sample            | grp1   | grp2 | grp3 | grp4 | grp5 | grp6       | grp7 | grp8 | grp9 |
        // --------------------------------------------------------------------------------------------
        // | P1Y2M3W           | 1Y2M3W | 1Y   | 2M   | 3W   | 4D   | T12H30M17S | 12H  | 30M  | 17S  |
        // | P3Y6M4DT12H30M17S | 3Y6M4D | 3Y   | 6M   |      | 4D   | T12H30M17S | 12H  | 30M  | 17S  |
        // | P1M               | 1M     |      | 1M   |      |      |            |      |      |      |
        // | PT1M              | 3Y6M4D |      |      |      |      | T1M        |      | 1M   |      |
        // --------------------------------------------------------------------------------------------

        var distributeOverflow = (_distributeOverflow) ? _distributeOverflow : false;
        var valueIndexes       = [2, 3, 4, 5, 7, 8, 9];
        var duration           = [0, 0, 0, 0, 0, 0, 0];
        var overflowLimits     = [0, 12, 4, 7, 24, 60, 60];
        var struct;

        // upcase the string just in case people don't follow the letter of the law
        period = period.toUpperCase();

        // input validation
        if (!period)                         return duration;
        else if (typeof period !== "string") throw new Error("Invalid iso8601 period string '" + period + "'");

        // parse the string
        if (struct = /^P((\d+Y)?(\d+M)?(\d+W)?(\d+D)?)?(T(\d+H)?(\d+M)?(\d+S)?)?$/.exec(period)) {

            // remove letters, replace by 0 if not defined
            for (var i = 0; i < valueIndexes.length; i++) {
                var structIndex = valueIndexes[i];
                duration[i] = struct[structIndex] ? +struct[structIndex].replace(/[A-Za-z]+/g, '') : 0;
            }
        }
        else {
            throw new Error("String '" + period + "' is not a valid ISO8601 period.");
        }

        if (distributeOverflow) {
            // note: stop at 1 to ignore overflow of years
            for (var i = duration.length - 1; i > 0; i--) {
                if (duration[i] >= overflowLimits[i]) {
                    duration[i-1] = duration[i-1] + Math.floor(duration[i]/overflowLimits[i]);
                    duration[i] = duration[i] % overflowLimits[i];
                }
            }
        }

        return duration;
    };


}( window.nezasa = window.nezasa || {} ));

//********************************************************************************* //


$(document).ready(function() {

    ChannelVideos.Init();
});

//********************************************************************************* //
