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
/*jslint browser: true, devel: true, onevar: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, regexp: true, strict: true, newcap: true, immed: true */
/*global $, jQuery, EE */
"use strict";// @todo - yuck, should be on the EE global
function disable_fields(e){var t=$(".main_tab input, .main_tab textarea, .main_tab select, #submit_button"),i=$("#submit_button"),s=$("#holder").find("a");e?(disabled_fields=t.filter(":disabled"),t.attr("disabled",!0),i.addClass("disabled_field"),s.addClass("admin_mode"),$("#holder div.markItUp, #holder p.spellcheck").each(function(){$(this).before('<div class="cover" style="position:absolute;width:98%;height:50px;z-index:9999;"></div>').css({})}),$(".contents, .publish_field input, .publish_field textarea").css("-webkit-user-select","none")):(t.removeAttr("disabled"),i.removeClass("disabled_field"),s.removeClass("admin_mode"),$(".cover").remove(),disabled_fields.attr("disabled",!0),$(".contents, .publish_field input, .publish_field textarea").css("-webkit-user-select","auto"))}/**
 * Get the percentage width of a field
 *
 * @param {jQuery Object} $element The jQuery object of a field in the publish form (e.g. $('.publish_field'))
 * @returns The percentage width of the field
 * @type Number
 */
EE.publish.get_percentage_width=function(e){var t=/[0-9]+/gi,i=e.attr("data-width");return i&&t.test(i.slice(0,-1))?parseInt(i,10):10*Math.round(e.width()/e.parent().width()*10)},EE.publish.save_layout=function(){var e=0,t={},s={},n=0,l=!1,a="_tab_label",r=$("#tab_menu_tabs li.current").attr("id");if(
// for width() to work, the element cannot be in a parent div that is display:none
$(".main_tab").show(),$("#tab_menu_tabs a:not(.add_tab_link)").each(function(){
// skip list items with no id (ie: new tab)
if($(this).parent("li").attr("id")&&"menu_"==$(this).parent("li").attr("id").substring(0,5)){var i=$(this).parent("li").attr("id").substring(5),r=$(this).parent("li").attr("id").substring(5),o=$(this).parent("li").attr("title");n=0,visible=!0,$(this).parent("li").is(":visible")?(lay_name=i,t[e]={name:lay_name,fields:{}},t[e].fields[a]=o):(l=!0,visible=!1),$("#"+r).find(".publish_field").each(function(){var i,l=$(this),a=this.id.replace(/hold_field_/,""),r=EE.publish.get_percentage_width(l),o=$("#sub_hold_field_"+a+" .markItUp ul li:eq(2)");r>100&&(r=100),o="undefined"!==o.html()&&"none"!==o.css("display"),i={visible:"none"!==$(this).css("display")&&visible!==!1,collapse:"none"===$("#sub_hold_field_"+a).css("display"),htmlbuttons:o,width:r+"%"},visible===!0?(i.index=n,t[e].fields[a]=i,n+=1):s[a]=i}),visible===!0&&e++}}),1==l){
// Add hidden fields to first tab
var o=0,u=t[0].fields;for(i in u)u[i].index>o&&(o=u[i].index);
// Reindex first tab
$.each(s,function(){this.index=++o}),jQuery.extend(t[0].fields,s)}
// @todo not a great solution
EE.tab_focus(r.replace(/menu_/,"")),0===e?$.ee_notice(EE.publish.lang.tab_count_zero,{type:"error"}):0===$("#layout_groups_holder input:checked").length?$.ee_notice(EE.publish.lang.no_member_groups,{type:"error"}):$.ajax({type:"POST",dataType:"json",url:EE.BASE+"&C=content_publish&M=save_layout",data:"json_tab_layout="+encodeURIComponent(JSON.stringify(t))+"&"+$("#layout_groups_holder input").serialize()+"&channel_id="+EE.publish.channel_id,success:function(e){"success"===e.messageType?$.ee_notice(e.message,{type:"success"}):"failure"===e.messageType&&$.ee_notice(e.message,{type:"error"})}})},EE.publish.remove_layout=function(){if(0===$("#layout_groups_holder input:checked").length)return $.ee_notice(EE.publish.lang.no_member_groups,{type:"error"});var e="{}";// empty array will remove everything nicely
return $.ajax({type:"POST",url:EE.BASE+"&C=content_publish&M=save_layout",data:"json_tab_layout="+e+"&"+$("#layout_groups_holder input").serialize()+"&channel_id="+EE.publish.channel_id+"&field_group="+EE.publish.field_group,success:function(e){return $.ee_notice(EE.publish.lang.layout_removed+' <a href="javascript:location=location">'+EE.publish.lang.refresh_layout+"</a>",{duration:0,type:"success"}),!0}}),!1},/**
 * Change the Preview Layout link to use the member group's ID
 * Hits the preview_layout method to create a message
 */
EE.publish.change_preview_link=function(){$select=$("#layout_preview select"),$link=$("#layout_group_preview"),base=$link.attr("href").split("layout_preview")[0],$link.attr("href",base+"layout_preview="+$select.val()),$.ajax({url:EE.BASE+"&C=content_publish&M=preview_layout",type:"POST",dataType:"json",data:{member_group:$select.find("option:selected").text()}})},file_manager_context="",$(document).ready(function(){var e,t;if($("#layout_group_submit").click(function(){return EE.publish.save_layout(),!1}),$("#layout_group_remove").click(function(){return EE.publish.remove_layout(),!1}),$("#layout_preview select").change(function(){EE.publish.change_preview_link()}),$("a.reveal_formatting_buttons").click(function(){return $(this).parent().parent().children(".close_container").slideDown(),$(this).hide(),!1}),$("#write_mode_header .reveal_formatting_buttons").hide(),$("a.glossary_link").click(function(){return $(this).parent().siblings(".glossary_content").slideToggle("fast"),$(this).parent().siblings(".smileyContent .spellcheck_content").hide(),!1}),EE.publish.smileys===!0&&($("a.smiley_link").toggle(function(){$(this).parent().siblings(".smileyContent").slideDown("fast",function(){$(this).css("display","")})},function(){$(this).parent().siblings(".smileyContent").slideUp("fast")}),$(this).parent().siblings(".glossary_content, .spellcheck_content").hide(),$(".glossary_content a").click(function(){var e=$(this).closest(".publish_field"),t=e.attr("id").replace("hold_field_","field_id_");return e.find("[name="+t+"]").insertAtCursor($(this).attr("title")),!1})),EE.publish.autosave&&EE.publish.autosave.interval){var i=!1;t=function(){i||(i=!0,setTimeout(e,1e3*EE.publish.autosave.interval))},e=function(){var e,s=$("#tools:visible");
// If the sidebar is showing, then form fields are disabled. Skip it.
// If the sidebar is showing, then form fields are disabled. Skip it.
return 1===s.length?void t():(e=$("#publishForm").serialize(),void $.ajax({type:"POST",dataType:"json",url:EE.BASE+"&C=content_publish&M=autosave",data:e,success:function(e){e.error?console.log(e.error):e.success?(e.autosave_entry_id&&$("input[name=autosave_entry_id]").val(e.autosave_entry_id),$("#autosave_notice").text(e.success)):console.log("Autosave Failed"),i=!1}}))};
// Start autosave when something changes
var s=$("textarea, input").not(":password,:checkbox,:radio,:submit,:button,:hidden"),n=$("select, :checkbox, :radio, :file");s.bind("keypress change",t),n.bind("change",t)}
// Pages URI Placeholder
if(EE.publish.pages){var l=$("#pages__pages_uri"),a=EE.publish.pages.pagesUri;l.val()||l.val(a),l.focus(function(){this.value===a&&$(this).val("")}).blur(function(){""===this.value&&$(this).val(a)})}
// @todo if admin bridge
if(void 0!==EE.publish.markitup.fields&&$.each(EE.publish.markitup.fields,function(e,t){$("textarea[name="+e+"]").markItUp(mySettings)}),EE.publish.setup_writemode=function(){var e,t,i,s=$("#write_mode_writer"),n=$("#write_mode_textarea");n.markItUp(myWritemodeSettings),
// the height of this modal depends on the height of the viewport
// we'll dynamically resize as a user may want proper fullscreen
// by changing their browser window once it's open.
$(window).resize(function(){var e=$(this).height()-117;// header + footer + 25px just to be safe
s.css("height",e+"px").find("textarea").css("height",e-67-17+"px")}).triggerHandler("resize"),$(".write_mode_trigger").overlay({
// only exit by clicking an action
closeOnEsc:!1,closeOnClick:!1,top:"center",target:"#write_mode_container",
// Mask to create modal look
mask:{color:"#262626",loadSpeed:200,opacity:.85},
// Event handlers
onBeforeLoad:function(t){var s=this.getTrigger()[0].id;
// regular field (id_#) or named field (id_forum_content_blah)
i=s.match(/^id_\d+$/)?$("textarea[name=field_"+s+"]"):$("#"+s.replace(/id_/,"")),e=i.getSelectedRange(),n.val(i.val())},onLoad:function(t){
// recreate the old cursor position
n.focus(),n.createSelection(e.start,e.end);
// monkey patching the closers so that we can
// standardize srcElement for all browsers
// (looking at you FireFox)
var i=this;i.getClosers().unbind("click").click(function(e){e.srcElement=this,i.close(e)})},onBeforeClose:function(e){var s=$(e.srcElement).closest(".close");s.hasClass("publish_to_field");s.hasClass("publish_to_field")&&(t=n.getSelectedRange(),i.val(n.val()),i.createSelection(t.start,t.end)),i.focus()}})},EE.publish.show_write_mode===!0&&EE.publish.setup_writemode(),
// toggle can not be used here, since it may or may not be visible
// depending on admin customization
$(".hide_field span").click(function(){var e=$(this).parent().parent().attr("id"),t=e.substr(11),i=$("#hold_field_"+t),s=$("#sub_hold_field_"+t);
// We dont want datepicker getting triggered when a field is collapsed/expanded
return"block"==s.css("display")?(s.slideUp(),i.find(".ui-resizable-handle").hide(),i.find(".field_collapse").attr("src",EE.THEME_URL+"images/field_collapse.png")):(s.slideDown(),i.find(".ui-resizable-handle").show(),i.find(".field_collapse").attr("src",EE.THEME_URL+"images/field_expand.png")),!1}),$(".close_upload_bar").toggle(function(){$(this).parent().children(":not(.close_upload_bar)").hide(),$(this).children("img").attr("src",EE.THEME_URL+"publish_plus.png")},function(){$(this).parent().children().show(),$(this).children("img").attr("src",EE.THEME_URL+"publish_minus.gif")}),$(".ping_toggle_all").toggle(function(){$("input.ping_toggle").each(function(){this.checked=!1})},function(){$("input.ping_toggle").each(function(){this.checked=!0})}),EE.user.can_edit_html_buttons&&($(".markItUp ul").append('<li class="btn_plus"><a title="'+EE.lang.add_new_html_button+'" href="'+EE.BASE+"&C=myaccount&M=html_buttons&id="+EE.user_id+'">+</a></li>'),$(".btn_plus a").click(function(){return confirm(EE.lang.confirm_exit,"")})),
// inject the collapse button into the formatting buttons list
$(".markItUpHeader ul").prepend('<li class="close_formatting_buttons"><a href="#"><img width="10" height="10" src="'+EE.THEME_URL+'images/publish_minus.gif" alt="Close Formatting Buttons"/></a></li>'),$(".close_formatting_buttons a").toggle(function(){$(this).parent().parent().children(":not(.close_formatting_buttons)").hide(),$(this).parent().parent().css("height","13px"),$(this).children("img").attr("src",EE.THEME_URL+"images/publish_plus.png")},function(){$(this).parent().parent().children().show(),$(this).parent().parent().css("height","auto"),$(this).children("img").attr("src",EE.THEME_URL+"images/publish_minus.gif")}),
// Apply a class to its companion tab fitting of its position
$(".tab_menu li:first").addClass("current"),1==EE.publish.title_focus&&$("#publishForm input[name=title]").focus(),"new"==EE.publish.which&&$("#publishForm input[name=title]").bind("keyup blur",function(){$("#publishForm input[name=title]").ee_url_title($("#publishForm input[name=url_title]"))}),"n"==EE.publish.versioning_enabled?$("#revision_button").hide():$("#versioning_enabled").click(function(){$(this).attr("checked")?$("#revision_button").show():$("#revision_button").hide()}),EE.publish.hidden_fields){EE._hidden_fields=[];var r=$("input");$.each(EE.publish.hidden_fields,function(e){EE._hidden_fields.push(r.filter("[name="+e+"]")[0])}),$(EE._hidden_fields).after('<p class="hidden_blurb">This module field only shows in certain circumstances. This is a placeholder to let you define it in your layout.</p>')}});