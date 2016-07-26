/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Vidtest
 * @copyright  Copyright (c) 2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */

var isIE  = (navigator.appVersion.indexOf("MSIE") != -1) ? true : false;
var isWin = (navigator.appVersion.toLowerCase().indexOf("win") != -1) ? true : false;
var isOpera = (navigator.userAgent.indexOf("Opera") != -1) ? true : false;
 
var motionLock = false;
var runMotionLock = false;
var motionQuery = new Array;
var run = '';
var lastClickTime = 0;

var checkTime = function() { 
    timestamp = Number(new Date());
    if (lastClickTime == 0) {
        lastClickTime = timestamp;
        return true;
    }
    result = (((timestamp - lastClickTime) > (2 * vidtest.speed)) );
    lastClickTime = timestamp;
    return result;
}

 
hasClass = function(element, className) {
    var pattern = new RegExp('(^|\\s)' + className + '(\\s|$)'); //use this regexp
    return pattern.test(element.className); //to check for the class
}

addClass = function(element, className) {
    if (!hasClass(element, className)) { //if the class isn't there already
        element.className += (' ' + className); //append it to the end of the class list
    }
}

removeClass = function(element, className) {
    var pattern = new RegExp('(^|\\s)' + className + '(\\s|$)'); //use this regexp
    element.className = element.className.replace(pattern, ' '); //to make a search and replace by a blank space
}

var AWVidtest = Class.create();
AWVidtest.prototype = {
    initialize: function(width, height, pixScroll, perPage, count, basicSlider) {
          
        this.speed = 210;
        this.formShow = false;
        this.videos = new Array;
        this.playerWidth = width;
        this.playerHeight = height;
        this.pixScroll = pixScroll;
        this.position = 0;
        this.page = 0;
        // this.perPage = perPage;
        this.count = count;
        this.detailTemplate = '';
        this.templateSyntax = /(^|.|\r|\n)({{(\w+)}})/;
        this.template = undefined;
        this.ajaxUrl = '';
        this.rateId = '';
        this.index = 0;
		this.motionLock = false;

        //reinit this.perPage
        if (basicSlider) {
            this.initPerPage();
            this.initScroller();
        }
    },

    initPerPage: function(){
        var items = $$('div.aw-vidtest-videoitem');

        if (items[0]!=undefined){

            var itemWidth = $(items[0]).getWidth();
            var marginRight = parseInt($(items["0"]).getStyle('margin-right').replace('px', ''));
            var marginLeft =  parseInt($(items["0"]).getStyle('margin-left').replace('px', ''));

            var fullItemWidths = itemWidth+marginRight+marginLeft;

            var itemsCount = items.size();

            var scrollFrameWidth =   $('aw-vidtest-scroll-frame-id').getWidth();

            var countOfThumbsForLayout =  Math.round(scrollFrameWidth/fullItemWidths);

            this.perPage = countOfThumbsForLayout;

            var buttons = $$('div.page-button');

            var reduceButtonsCountTill = 1;

            if (this.count>countOfThumbsForLayout){

                reduceButtonsCountTill = Math.ceil(this.count/countOfThumbsForLayout);
            }

            for (var i = reduceButtonsCountTill-1; i < buttons.size(); i++){
                if (i == reduceButtonsCountTill-1 && reduceButtonsCountTill != 1) {
                    $(buttons[i]).addClassName('last');
                }
                else{
                    $(buttons[i]).hide();
                }
            }

            //disables navigation arrows
            if (countOfThumbsForLayout>=itemsCount){              
                $("sbt-right").addClassName('disabled');
                $("sbt-left").addClassName('disabled');
            }
        }
    },

    initScroller: function() {
        var items = $$('div.aw-vidtest-videoitem');
        if (items.length > this.perPage) {
            for (var i = 0; i < this.perPage; i++) {
                $$('div#scroll-line')[0].insert('<div id="' + items[i].id + '" class="aw-vidtest-videoitem">' + items[i].innerHTML + '</div>',{ position:top });
            }
        }
    },
    show: function(element){
        new Effect.Opacity(element, {from: 0, to: 1, duration: this.speed});
    },
    hide: function(element){
        new Effect.Opacity(element, {from: 1, to: 0, duration: this.speed});

    },
    toogleForm: function(){
        
        if (this.count == 0){            
            this.formShow = true;
            $('uploadform-content').style.display = this.formShow ? 'block' : 'none';
            $('player-content').style.display = this.formShow ? 'none' : 'block';
            return false;
        }

        if (this.formShow){
            this.formShow = false;
        } else {
            this.formShow = true;            
        }
        $('uploadform-content').style.display = this.formShow ? 'block' : 'none';
        $('player-content').style.display = this.formShow ? 'none' : 'block';
    },
    addVideo: function(id, p_url, v_url, rate, can_rate, title, details, date, entity_id){
        this.videos[id] = {
            id: id,
            p_url: p_url,
            v_url: v_url,
            rate: rate,
            can_rate: can_rate,
            title: title,
            details: details,
            date: date,
            entity_id: entity_id
        };
    },
    playVideo: function(id){
        if (this.videos[id] == 'undefined'){
            return;
        }
        if (this.formShow){
            this.toogleForm();
        }

        player = this.getPlayerHtml(this.videos[id]['p_url']);
        $('player-content').innerHTML = player;

        if (this.template == undefined){
            this.template = new Template(this.detailTemplate, this.templateSyntax);
        }
        $('details-content').innerHTML = this.template.evaluate(this.videos[id]);

        this.rateId = id;
        if ($('aw-rate-indicator') != undefined){
            $('aw-rate-indicator').style.width = this.videos[this.rateId]['rate'] + '%';
            $('aw-rate-notice').style.display = 'none';
            if (this.videos[this.rateId]['can_rate'] == '1'){
                $('aw-rate-box').removeClassName('disabled');
            } else {
                $('aw-rate-box').addClassName('disabled');
            }
        }
    },
    getPlayerHtml: function(url){
        var objAttrs = {
            height: this.playerHeight + 'px',
            width: this.playerWidth + 'px'
        };
        var params = {
            movie: url,
            allowFullScreen: 'true',
            allowScriptAccess: 'always'
        };
        var embedAttrs = {
            src: url,
            allowFullScreen: 'true',
            allowScriptAccess: 'always',
            height: this.playerHeight + 'px',
            width: this.playerWidth + 'px',
            type: 'application/x-shockwave-flash'
        };

        var str = '';
        str += '<iframe src="' + url + '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';

        return str;
    },

    _setSlideEffectLock: function() {
        this.motionLock = true;
    },

    _resetSlideEffectLock: function() {
		 
        this.motionLock = false;
    },
 
    scrollRight: function(obj) {
  
        if ($(obj).hasClassName('disabled')) { return false; }
  
        if (this.motionLock){  return false; }
 
        if (this.position == (this.count)){

            $('scroll-line').style.left = '0';
            this.position = 0;
            this.index = 0;

        }

        this._setSlideEffectLock();
       
        new Effect.Move($('scroll-line'), {
            x:parseInt(this.index) - parseInt(this.pixScroll),
            mode: 'absolute',
            transition: Effect.Transitions.sinoidal,
            afterFinish: function() { this._resetSlideEffectLock(); }.bind(this)
        });
  
        this.index = parseInt(this.index) - parseInt(this.pixScroll);        
        this.position ++;
        this.updatePager();
    },
    scrollLeft: function(obj){
        
        if ($(obj).hasClassName('disabled')) { return false; }

        if (this.motionLock){           
            return false;
        }
        if (this.position == 0) {
            this.position = (this.count);
            $('scroll-line').style.left = '-' + (this.position * this.pixScroll)  + 'px';
            this.index = '-' + (this.position * this.pixScroll);
        }
       
        this._setSlideEffectLock();
        new Effect.Move($('scroll-line'), {
            x: parseInt(this.index) + parseInt(this.pixScroll),
            mode: 'absolute',
            transition: Effect.Transitions.sinoidal,
            afterFinish: function() { this._resetSlideEffectLock(); }.bind(this)
        });

        this.index = parseInt(this.index) + parseInt(this.pixScroll);       
        this.position --;
        this.updatePager();
    },
    scrollToAnchor: function(id, left) {

        if (this.motionLock) { return false; }
		this._setSlideEffectLock();
         new Effect.Move($('scroll-line'), {
            x: '-' + (left * this.perPage * this.pixScroll),
            mode: 'absolute',
            transition: Effect.Transitions.sinoidal,
            afterFinish:  function() { this._resetSlideEffectLock(); }.bind(this)
        });

        this.index = '-' + (left * this.perPage * this.pixScroll);     

        if (this.currentId == undefined){
            $$('a.first').invoke('removeClassName','active');
        } else {
            $$('a#'+this.currentId).invoke('removeClassName','active');
        }
        this.currentId = id;
        $$('a#'+id).invoke('addClassName','active');
        this.page = left;
        this.position = left * this.perPage;
    },
    updatePager: function(){
        page = (this.position - (this.position % this.perPage)) / this.perPage ;
        if (this.currentId == undefined){
            if (page == 0){
                return false;
            }
        } else {
            if (this.currentId == 'page-button-' + page){
                return false;
            }
        }
        if (this.currentId == undefined){
            $$('a#page-button-0').invoke('removeClassName','active');
        } else {
            $$('a#'+this.currentId).invoke('removeClassName','active');
        }
        this.currentId = 'page-button-' + page;
        $$('a#page-button-' + page).invoke('addClassName','active');
    },
    /* Rates Logic */
    mouseOut: function(obj){
        $('aw-rate-indicator').style.width = this.videos[this.rateId]['rate'] + '%';
    },
    mouseClick: function(num){
        if (this.videos[this.rateId]['can_rate'] == 1){
            //SSL correct work fix
            this.ajaxUrl = this.ajaxUrl.replace(/^http[s]{0,1}/, window.location.href.replace(/:[^:].*$/i, ''));
            var param = 'id/' + this.videos[this.rateId]['entity_id'] + '/';
            param += 'rate/' + num + '/';
            $('aw-rate-loader').style.display = 'block';
            this.videos[this.rateId]['can_rate'] = 0;
            $$('div#aw-rate-box').invoke('addClassName','disabled');
            new Ajax.Request(this.ajaxUrl + param, {
                method: 'get',
                onSuccess: function(transport) {                    
                    if (transport && transport.responseText) {
                        try{
                            response = eval('(' + transport.responseText + ')');
                            if (response.newrate) {                                
                                $('aw-rate-indicator').style.width = response.newrate + '%';
                                $('aw-rate-loader').style.display = 'none';
                                $('aw-rate-notice').style.display = 'block';
                                vidtest.videos[vidtest.rateId]['rate'] = response.newrate;
                            }
                            if (response.error){
                                console.debug(response.error);
                            }
                        }
                        catch (e) {
                            response = {};
                        }
                    }  
                },
                onFailure: function() {
                    $('aw-rate-loader').style.display = 'none';
                }
            });            
        }        
    },
    mouseMove: function(num){
        if (this.videos[this.rateId]['can_rate'] == 1){
            $('aw-rate-indicator').style.width = (num * 20) + '%';
        }
    }
}



;
