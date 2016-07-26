/*!
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2003 - 2015, EllisLab, Inc.
 * @license		http://ellislab.com/expressionengine/user-guide/license.html
 * @link		http://ellislab.com
 * @since		Version 2.0
 * @filesource
 */
!function(e){"use strict";/**
  * Namespace function that non-destructively creates "namespace" objects (e.g. EE.publish.example)
  *
  * @param {String} namespace_string The namespace string (e.g. EE.publish.example)
  * @returns The object to create
  */
EE.namespace=function(e){var t=e.split("."),i=EE;
// strip redundant leading global
"EE"===t[0]&&(t=t.slice(1));
// @todo disallow 'prototype', duh
// create a property if it doesn't exist
for(var a=0,o=t.length;o>a;a+=1)"undefined"==typeof i[t[a]]&&(i[t[a]]={}),i=i[t[a]];return i},
// Create the base cp namespace
EE.namespace("EE.cp"),/**
 * Hook into jQuery's ajax functionality to build in handling of our
 * csrf tokens and custom response headers.
 *
 * We also add a custom error handler in case the developer does not specify
 * one. This prevents silent failure.
 */
e.ajaxPrefilter(function(t,i,a){var o=EE.CSRF_TOKEN,n=t.type.toUpperCase();
// Throw all errors
_.has(t,"error")||a.error(function(e){_.defer(function(){throw[e.statusText,e.responseText]})}),
// Add CSRF TOKEN to EE POST requests
"POST"==n&&t.crossDomain===!1&&a.setRequestHeader("X-CSRF-TOKEN",o);var s={
// Refresh xids (deprecated)
eexid:function(e){e&&EE.cp.setCsrfToken(e)},
// Refresh csrf tokens
"csrf-token":function(e){e&&EE.cp.setCsrfToken(e)},
// Force redirects (e.g. logout)
"ee-redirect":function(e){window.location=EE.BASE+"&"+e.replace("//","/")},
// Trigger broadcast events
"ee-broadcast":function(t){EE.cp.broadcastEvents[t](),e(window).trigger("broadcast",t)}},c=e.merge(s,i.eeResponseHeaders||{});a.complete(function(e){t.crossDomain===!1&&_.each(c,function(t,i){var a=e.getResponseHeader("X-"+i);a&&t(a)})})}),
// Grid has become a dependency for a few fieldtypes. However, sometimes it's not
// on the page or loaded after the fieldtype. So instead of tryin to always load
// grid or doing weird dependency juggling, we're just going to cache any calls
// to grid.bind for now. Grid will override this definition and replay them if/when
// it becomes available on the page. Long term we need a better solution for js
// dependencies.
EE.grid_cache=[],window.Grid={bind:function(){EE.grid_cache.push(arguments)}},
// Setup Base EE Control Panel
e(document).ready(function(){
// call the input placeholder polyfill early so that we don't get
// weird flashes of content
!1 in document.createElement("input")&&EE.insert_placeholders(),
// External links open in new window
e('a[rel="external"]').click(function(){return window.open(this.href),!1}),
// Notice banners
EE.importantMessage&&EE.cp.showNoticeBanner(),EE.cp.zebra_tables(),EE.cp.show_hide_sidebar(),EE.cp.display_notices(),EE.cp.cleanUrls(),EE.cp.deprecation_meaning(),EE.cp.notepad.init(),EE.cp.accessory_toggle(),EE.cp.control_panel_search(),
// Setup sidebar hover descriptions
e("#quickLinks h4").click(function(){window.location.href=EE.BASE+"&C=myaccount&M=quicklinks"}).add("#notePad").hover(function(){e(".sidebar_hover_desc",this).show()},function(){e(".sidebar_hover_desc",this).hide()}).css("cursor","pointer")}),
// Simple function to deal with csrf tokens
EE.cp.setCsrfToken=function(t,i){e('input[name="XID"]').val(t),e('input[name="csrf_token"]').val(t),EE.XID=t,EE.CSRF_TOKEN=t,i||e(window).trigger("broadcast.setCsrfToken",t)},e(window).bind("broadcast.setCsrfToken",function(e,t){EE.cp.setCsrfToken(t,!0)});
// Simple function to deal with base paths tokens
var t=/[&?](S=[A-Za-z0-9]+)/;EE.cp.setBasePath=function(i,a){var i=i.replace(/&amp;/g,"&"),o=i.match(t)||["",""],n=EE.BASE.match(t)||["",""],s=function(e,t){return t?t.replace(n[1],o[1]):void 0};e("a").attr("href",s),e("form").attr("action",s),
// Since the session id in the current url is no longer correct, a
// refresh will end up on the login page. We will replace the current
// url to avoid that issue. You still cannot use the back button after
// logging back in, but how likely are you to remember what page you
// were on before leaving this one open for 20 minutes anyways?
"function"==typeof window.history.pushState&&window.history.replaceState(null,document.title,window.location.href.replace(n[1],o[1])),
// Set it as the new base
EE.BASE=i,a||e(window).trigger("broadcast.setBasePath",i)},e(window).bind("broadcast.setBasePath",function(e,t){EE.cp.setBasePath(t,!0)}),EE.cp.refreshSessionData=function(t,i){i&&EE.cp.setBasePath(i),
// running the request will return the x-csrf-header, which will trigger
// our prefilter. We still need to replace the base though.
e.getJSON(EE.BASE+"&C=login&M=refresh_csrf_token",function(e){EE.cp.setBasePath(e.base)})},
// Show / hide accessories
EE.cp.accessory_toggle=function(){e("#accessoryTabs li a").click(function(t){t.preventDefault();var i=e(this).parent("li"),a=e("#"+this.className);i.hasClass("current")?(a.slideUp("fast"),i.removeClass("current")):(i.siblings().hasClass("current")?(a.show().siblings(":not(#accessoryTabs)").hide(),i.siblings().removeClass("current")):a.slideDown("fast"),i.addClass("current"))})};var i=/(.*?)[?](.*?&)?(D=cp(?:&C=[^&]+(?:&M=[^&]+)?)?)(?:&(.+))?$/,a=/&?[DCM]=/g,o=/^&+/,n=/&+$/,s=/(^|&)S=0(&|$)/;EE.cp.cleanUrl=function(e,t){t=t||e,t=t||"",t=t.toString().replace(/^(\S*?)S=(\S+?)&(\S*?)$/g,"$1$3&S=$2");var c=i.exec(t);if(c){
// result[1] // index.php
// result[2] // S=49204&
// result[3] // D=cp&C=foo&M=bar
// result[4] // &foobarbaz
var r=c[3].replace(a,"/"),d=c[2]||"",l=c[4]||"",h=c[1]+"?"+r,u=l.replace(s,"")+"&"+d.replace(s,"");return u=u.replace(o,"").replace(n,""),u&&(h+="&"+u),h.replace(n,"")}},EE.cp.cleanUrls=function(){e("a:not([href^=javascript])").attr("href",EE.cp.cleanUrl),e("form").attr("action",EE.cp.cleanUrl)},
// Upgrade and developer log notices
EE.cp.showNoticeBanner=function(){var t,i,a,o;t=EE.importantMessage.state,i=e("#ee_important_message"),a=function(){t=!t,document.cookie="exp_home_msg_state="+(t?"open":"closed")},o=function(){e.ee_notice.show_info(function(){e.ee_notice.hide_info(),i.removeClass("closed").show(),a()})},i.find(".msg_open_close").click(function(){i.hide(),o(),a()}),t||o()},
// Setup Notepad
EE.cp.notepad=function(){var t,i,a,o,n,s;return{init:function(){var c=e("#notePad");t=e("#notepad_form"),i=e("#notePadTextEdit"),a=e("#notePadControls"),o=e("#notePadText"),n=o.text(),s=i.val(),s&&o.html(s.replace(/</gi,"&lt;").replace(/>/gi,"&gt;").replace(/\n/gi,"<br />")),c.click(EE.cp.notepad.show),a.find("a.cancel").click(EE.cp.notepad.hide),t.submit(EE.cp.notepad.submit),a.find("input.submit").click(EE.cp.notepad.submit),i.autoResize()},submit:function(){s=e.trim(i.val());var c=s.replace(/</gi,"&lt;").replace(/>/gi,"&gt;").replace(/\n/gi,"<br />");return i.attr("readonly","readonly").css("opacity",.5),a.find("#notePadSaveIndicator").show(),e.post(t.attr("action"),{notepad:s},function(e){o.html(c||n).show(),i.attr("readonly",!1).css("opacity",1).hide(),a.hide().find("#notePadSaveIndicator").hide()},"json"),!1},show:function(){
// Already showing?
if(a.is(":visible"))return!1;var e="";o.hide().text()!==n&&(e=o.html().replace(/<br>/gi,"\n").replace(/&lt;/gi,"<").replace(/&gt;/gi,">")),a.show(),i.val(e).show().height(0).focus().trigger("keypress")},hide:function(){return o.show(),i.hide(),a.hide(),!1}}}(),
// Ajax for control panel search
EE.cp.control_panel_search=function(){var t,i=e("#search"),a=i.clone(),o=e("#cp_search_form").find(".searchButton");t=function(){var n=e(this).attr("action"),s={cp_search_keywords:e("#cp_search_keywords").val()};return e.ajax({url:n,data:s,type:"POST",dataType:"html",beforeSend:function(){o.toggle()},success:function(n){o.toggle(),i=i.replaceWith(a),a.html(n),e("#cp_reset_search").click(function(){return a=a.replaceWith(i),e("#cp_search_form").submit(t),e("#cp_search_keywords").select(),!1})}}),!1},e("#cp_search_form").submit(t)},
// Hook up show / hide actions for sidebar
EE.cp.show_hide_sidebar=function(){var t,i={revealSidebarLink:"77%",hideSidebarLink:"100%"},a=e("#mainContent"),o=e("#sidebarContent"),n=a.height(),s=o.height();
// Sidebar state
"n"===EE.CP_SIDEBAR_STATE?(a.css("width","100%"),e("#revealSidebarLink").css("display","block"),e("#hideSidebarLink").hide(),o.show(),s=o.height(),o.hide()):(o.hide(),n=a.height(),o.show()),t=s>n?s:n,e("#revealSidebarLink, #hideSidebarLink").click(function(s){var c=e(this),r=c.siblings("a"),d="revealSidebarLink"===this.id;return e.ajax({type:"POST",dataType:"json",url:EE.BASE+"&C=myaccount&M=update_sidebar_status",data:{show:d},success:function(e){"success"===e.messageType}}),s.isTrigger||e(window).trigger("broadcast.sidebar",d),e("#sideBar").css({position:"absolute","float":"",right:"0"}),c.hide(),r.css("display","block"),o.slideToggle(),a.animate({width:i[this.id],height:d?t:n},function(){a.height(""),e("#sideBar").css({position:"","float":"right"})}),!1}),e(window).bind("broadcast.sidebar",function(t,i){var a=i?"#revealSidebarLink":"#hideSidebarLink";e(a).filter(":visible").trigger("click")})},
// Move notices to notification bar for consistency
EE.cp.display_notices=function(){var t=["success","notice","error"];e(".message.js_hide").each(function(){for(var i in t)e(this).hasClass(t[i])&&e.ee_notice(e(this).html(),{type:t[i]})})},
// Fallback for browsers without placeholder= support
EE.insert_placeholders=function(){e('input[type="text"]').each(function(){if(this.placeholder){var t=e(this),i=this.placeholder,a=t.css("color");""==t.val()&&t.data("user_data","n"),t.focus(function(){
// Reset color & remove placeholder text
t.css("color",a),t.val()===i&&(t.val(""),t.data("user_data","y"))}).blur(function(){
// If no user content -> add placeholder text and dim
""!==t.val()&&t.val!==i||(t.val(i).css("color","#888"),t.data("user_data","n"))}).trigger("blur")}})},
// Modal for "What does this mean?" link on deprecation notices
EE.cp.deprecation_meaning=function(){e(".deprecation_meaning").click(function(t){t.preventDefault();var i=e('<div class="alert">'+EE.developer_log.deprecation_meaning+" </div>");i.dialog({height:300,modal:!0,title:EE.developer_log.dev_log_help,width:460})})},EE.cp.zebra_tables=function(t){t=t||e("table"),t.jquery||(t=e(t)),e(t).find("tr").removeClass("even odd").filter(":even").addClass("even").end().filter(":odd").addClass("odd")},/**
 * Handle idle / inaction between windows
 *
 * This code relies heavily on timing. In order to reduce complexity everything is
 * handled in steps (ticks) of 15 seconds. We count for how many ticks we have been
 * in a given state and act accordingly. This gives us reasonable timing information
 * without having to set, cancel, and track multiple timeouts.
 *
 * The conditions currently are as follows:
 *
 * - If an ee tab has focus we call it idle after 20 minutes of no interaction
 * - If no ee tab has focus, we call it idle after 40 minutes of no activity
 * - If they work around the modal (inspector), all request will land on the login page.
 * - Logging out of one tab will show the modal on all other tabs.
 * - Logging into the modal on one tab, will show it on all other tabs.
 *
 * The object returned is one that allows manual triggering of an event. For
 * example, to force the modal to show you could call:
 *
 *     EE.cp.broadcastEvents['modal']();
 *
 * This is used by our ajax filter to allow triggering an event with the
 * X-EE-BROADCAST header
 *
 */
EE.cp.broadcastEvents=function(){
// Define our time limits:
var t=1e3,// Check state every second
i=18e5,// 30 minutes: time before modal if window focused
a=27e5,// 45 minutes: time before modal if no focus
o=3e6,n=e("#idle-modal").dialog({autoOpen:!1,resizable:!1,title:EE.lang.session_idle,modal:!0,closeOnEscape:!1,position:"center",height:"auto",width:354});
// This modal is required, remove the close button in the titlebar.
n.closest(".ui-dialog").find(".ui-dialog-titlebar-close").remove(),
// If the modal hasn't been interacted with in over 10 minutes we'll send a request for
// the current csrf token. It can flip on us during long waits due to the session timeout.
// If the session times out this will get us a cookie based csrf token, which is what you
// would normally log in with, so it's fine.
n.find("form").on("interact",_.throttle(EE.cp.refreshSessionData,6e5)),
// Bind on the modal submission
n.find("form").on("submit",function(){return e.ajax({type:"POST",url:this.action,data:e(this).serialize(),dataType:"json",success:function(t){
// Hide the dialog
// Grab the new token
return"success"!=t.messageType?void alert(t.message):(c.login(),EE.cp.refreshSessionData(null,t.base),void e(window).trigger("broadcast.idleState","login"))},error:function(e){alert(e.message)}}),!1});/**
	 * This object tracks the current state of the page.
	 *
	 * The resolve function is called once per tick. The individual events will
	 * set hasFocus and lastActive time.
	 */
var s={hasFocus:!0,modalActive:!1,pingReceived:!1,lastActive:e.now(),lastRefresh:e.now(),setActiveTime:function(){
// Before we set someone as not idle we need to check if they've
// sneakily been idle for a long time. When you close your laptop
// the timer stops. Reopening it hours later creates a race between
// the tick timer and the non-idle events. When that happens, you're
// way past the threshold and therefore too late.
!this.modalActive&&this.modalThresholdReached()||(
// If they're active on the page for an extend period of time
// without hitting the backend, we can sometimes run past the
// session timeout. To prevent that from happening we'll refresh
// their session last activity in the background.
this.refreshThresholdReached()&&this.doRefresh(),this.lastActive=e.now())},modalThresholdReached:function(){var t=e.now()-this.lastActive,o=this.hasFocus&&t>i||!this.hasFocus&&t>a;return this.modalActive===!1&&o===!0},refreshThresholdReached:function(){var t=e.now()-this.lastRefresh;return t>o},doRefresh:function(){this.lastRefresh=e.now(),EE.cp.refreshSessionData()},resolve:function(){
// Reset
return EE.hasRememberMe?void(this.refreshThresholdReached()&&this.doRefresh()):(this.modalThresholdReached()?(c.modal(),e(window).trigger("broadcast.idleState","modal"),e.get(EE.BASE+"&C=login&M=lock_cp")):this.hasFocus&&this.pingReceived===!1&&e(window).trigger("broadcast.idleState","active"),void(this.pingReceived=!1))}},c={
// received another window's active event, user active
active:function(){s.setActiveTime()},
// user focused, they are active
focus:function(){s.setActiveTime(),s.hasFocus=!0},
// user left, they are idle
blur:function(){s.setActiveTime(),s.hasFocus=!1},
// user typing / mousing, possibly active
interact:function(){s.hasFocus&&s.setActiveTime()},
// received another window's modal event, open it
modal:function(){s.modalActive||(n.dialog("open"),n.on("dialogbeforeclose",e.proxy(this,"logout")),// prevent tampering. If they close it, they go.
s.modalActive=!0),s.setActiveTime()},
// received another window's login event, check and hide modal
login:function(){n.off("dialogbeforeclose"),n.dialog("close"),n.find(":password").val(""),s.setActiveTime(),s.modalActive=!1},
// received another window's logout event, leave page
logout:function(){window.location=EE.BASE+"&C=login&M=logout"}},r={_t:null,init:function(){e(window).trigger("broadcast.setBasePath",EE.BASE),e(window).trigger("broadcast.setCsrfToken",EE.CSRF_TOKEN),e(window).trigger("broadcast.idleState","login"),this._bindEvents(),this.track()},/**
		 * Bind our events
		 *
		 * We keep track of focus, blur, scrolling, clicking, etc.
		 * Some broadcast events can be fired immediately as nothing will stop
		 * them once the tick fires anyways.
		 * We have an extra throttle on interactions to keep the browser happy
		 * and not fill up the queue uselessly.
		 */
_bindEvents:function(){var t=e.proxy(this,"track");
// Bind on the broadcast event
e(window).on("broadcast.idleState",function(e,i){switch(i){case"active":s.pingReceived=!0,t(i);break;case"modal":case"login":case"logout":c[i]()}}),
// Bind on window focus and blur
e(window).bind("blur",_.partial(t,"blur")),e(window).bind("focus",_.partial(t,"focus"));
// Bind on interactions
var i="DOMMouseScroll keydown mousedown mousemove mousewheel touchmove touchstart";e(document).on(i.split(" ").join(".idleState "),// namespace the events
_.throttle(_.partial(t,"interact"),500)),
// Clicking the logout button fires "modal" on all the others
e(".logOutButton").click(function(){e(window).trigger("broadcast.idleState","modal")})},/**
		 * Helper method to record an event
		 */
track:function(i){clearTimeout(this._t),this._t=setTimeout(e.proxy(this,"track"),t),i&&c[i](),s.resolve()}};
// Go go go!
return r.init(),c}()}(jQuery);