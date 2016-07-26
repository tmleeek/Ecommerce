/*
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
$(document).ready(function(){
// Change the submenus
// Gets passed the channel id
function e(e){void 0===a[e]&&(e=0),jQuery.each(a[e],function(e,t){switch(e){case"categories":$("select#f_cat_id").empty().append(t);break;case"statuses":$("select#f_status").empty().append(t)}})}function t(){"yyyy-mm-dd"!=$("#custom_date_start").val()&&"yyyy-mm-dd"!=$("#custom_date_end").val()&&(
// populate dropdown box
focus_number=$("#date_range").children().length,$("#date_range").append('<option id="custom_date_option">'+$("#custom_date_start").val()+" to "+$("#custom_date_end").val()+"</option>"),document.getElementById("date_range").options[focus_number].selected=!0,
// hide custom date picker again
$("#custom_date_picker").slideUp("fast"),
// Trigger change to update filter
$("#date_range").change())}$(".paginationLinks .first").hide(),$(".paginationLinks .previous").hide(),$("#custom_date_start_span").datepicker({dateFormat:"yy-mm-dd",prevText:"<<",nextText:">>",onSelect:function(e){$("#custom_date_start").val(e),t()}}),$("#custom_date_end_span").datepicker({dateFormat:"yy-mm-dd",prevText:"<<",nextText:">>",onSelect:function(e){$("#custom_date_end").val(e),t()}}),$("#custom_date_start, #custom_date_end").focus(function(){"yyyy-mm-dd"==$(this).val()&&$(this).val("")}),$("#custom_date_start, #custom_date_end").keypress(function(){$(this).val().length>=9&&t()});
// The oracle knows everything.
var a=EE.edit.channelInfo,n=new RegExp("!-!","g");(new Date).getTime();
// We prep our magic arrays as soons as we can, basically
// converting everything into option elements
!function(){jQuery.each(a,function(e,t){
// Go through each of the individual settings and build a proper dom element
jQuery.each(t,function(t,d){var o=new String;
// Add the new option fields
jQuery.each(d,function(e,t){o+='<option value="'+t[0]+'">'+t[1].replace(n,String.fromCharCode(160))+"</option>"}),
// Set the new values
a[e][t]=o})})}(),$("#f_channel_id").change(function(){e(this.value)}),$("#date_range").change(function(){"custom_date"==$("#date_range").val()?(
// clear any current dates, remove any custom options
$("#custom_date_start").val("yyyy-mm-dd"),$("#custom_date_end").val("yyyy-mm-dd"),$("#custom_date_option").remove(),
// drop it down
$("#custom_date_picker").slideDown("fast")):$("#custom_date_picker").hide()}),
// Require at least one comment checked to submit
$("#entries_form").submit(function(){return $("input:checkbox",this).is(":checked")?void 0:($.ee_notice(EE.lang.selection_required,{type:"error"}),!1)});
// Keyword filter
var d=$(".searchIndicator");$("table").table("add_filter",$("#keywords").closest("form")).bind("tableload",function(){d.css("visibility","")}).bind("tableupdate",function(){d.css("visibility","hidden")})});