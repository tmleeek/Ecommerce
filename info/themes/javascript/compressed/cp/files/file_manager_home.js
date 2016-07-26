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
/*jslint browser: true, onevar: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, regexp: false, strict: true, newcap: true, immed: true */
/*global $, jQuery, EE, window, document, console, alert */
//"use strict";
$.ee_filemanager=$.filemanager||{},$(document).ready(function(){
// Load the functionality needed for this page
$.ee_filemanager.file_uploader(),$.ee_filemanager.datatables(),$.ee_filemanager.image_overlay(),$.ee_filemanager.date_range(),$.ee_filemanager.directory_change(),
// Hide first and previous pagination
$(".paginationLinks .first").hide(),$(".paginationLinks .previous").hide()}),$.ee_filemanager.file_uploader=function(){$.ee_fileuploader({type:"filemanager",load:function(){$.template("filemanager_row",$("#filemanager_row").remove())},open:function(e){$.ee_fileuploader.set_directory_id($("#dir_id").val())},after_upload:function(e,a){if(
// if we're replacing remove any visible files with the same ID
1==a.replace&&$(".mainTable tbody tr:has(td:contains("+a.file_id+")):has(td:contains("+a.file_name+"))").remove(),
// Build actions
a.actions="",$.each(EE.fileuploader.actions,function(e,t){var n=t.replace("[file_id]",a.file_id).replace("[upload_dir]",a.upload_directory_prefs.id);
// Add the edit action only if it's an image
"delete"==e?a.action_delete=n:("image"!=e||a.is_image)&&(a.actions+=n+"&nbsp;&nbsp;")}),"undefined"==typeof a.title&&(a.title=a.name),a.is_image){var t=new Date,n=$("<a>",{id:"",href:a.upload_directory_prefs.url+a.file_name+"?v="+t.getTime(),title:a.file_name,text:a.title,rel:"#overlay","class":"less_important_link overlay"});
// I realize how foolish this looks, but in order to pass the html
// to jQuery templates, we need the html and jQuery in its infinite
// wisdom has no method to get the full html of an object, it only
// has a method to get the inner html, completely missing the actual
// anchor link, seems worthless to me too.
a.link=n.wrap("<div>").parent().html()}else a.link=a.title;
// Send it all to the jQuery Template
$(".mainTable tbody").prepend($.tmpl("filemanager_row",a)),$("td.dataTables_empty").size()&&$("td.dataTables_empty").parent().remove(),1!=a.replace&&
// Change modal's top
$("#file_uploader").dialog("option","position","center"),
// If there were no files previously, the table might be hidden
$(".mainTable").show(),
// Ensure new file appears on subsequent filtering
$(".mainTable").table("clear_cache")},trigger:"#action_nav a.upload_file"})},$.ee_filemanager.directory_change=function(){
// Change the submenus
// Gets passed the directory id
function e(e){void 0===a[e]&&(e=0),jQuery.each(a[e],function(e,a){$("select#cat_id").empty().append(a)})}var a=EE.file.directoryInfo,t=new RegExp("!-!","g");
// We prep our magic arrays as soons as we can, basically
// converting everything into option elements
$.each(a,function(e,n){
// Go through each of the individual settings and build a proper dom element
$.each(n,function(n,i){var o=new String;
// Add the new option fields
$.each(i,function(e,a){o+='<option value="'+a[0]+'">'+a[1].replace(t,String.fromCharCode(160))+"</option>"}),
// Set the new values
a[e][n]=o})}),$("#dir_id").change(function(){e(this.value)})},$.ee_filemanager.date_range=function(){function e(){"yyyy-mm-dd"!=$("#custom_date_start").val()&&"yyyy-mm-dd"!=$("#custom_date_end").val()&&(
// populate dropdown box
focus_number=$("#date_range").children().length,$("#date_range").append('<option id="custom_date_option">'+$("#custom_date_start").val()+" to "+$("#custom_date_end").val()+"</option>"),document.getElementById("date_range").options[focus_number].selected=!0,
// hide custom date picker again
$("#custom_date_picker").slideUp("fast"),
// Trigger change to update filter
$("#date_range").change())}$("#custom_date_start_span").datepicker({dateFormat:"yy-mm-dd",prevText:"<<",nextText:">>",onSelect:function(a){$("#custom_date_start").val(a),e()}}),$("#custom_date_end_span").datepicker({dateFormat:"yy-mm-dd",prevText:"<<",nextText:">>",onSelect:function(a){$("#custom_date_end").val(a),e()}}),$("#custom_date_start, #custom_date_end").focus(function(){"yyyy-mm-dd"==$(this).val()&&$(this).val("")}),$("#custom_date_start, #custom_date_end").keypress(function(){$(this).val().length>=9&&e()}),$("#date_range").change(function(){"custom_date"==$("#date_range").val()?(
// clear any current dates, remove any custom options
$("#custom_date_start").val("yyyy-mm-dd"),$("#custom_date_end").val("yyyy-mm-dd"),$("#custom_date_option").remove(),
// drop it down
$("#custom_date_picker").slideDown("fast")):$("#custom_date_picker").hide()})},$.ee_filemanager.image_overlay=function(){function e(){// start loading
// Prevent default click event
// Destroy any existing overlay
// Launch overlay once image finishes loading
return $("#overlay").hide().removeData("overlay"),$("#overlay .contentWrap img").remove(),$("<img />").appendTo("#overlay .contentWrap").load(function(){
// We need to scale very large images down just a bit. To do that we
// need a reference element that we can set to visible very briefly
// or we won't get a proper width / height
var e=$(this).clone().appendTo(document.body).show(),a=e.width(),t=e.height(),n=.8*$(window).width(),// 10% margin
i=.8*$(window).height(),o=n/a,// ratios
r=i/t,d=o>r?r:o;// use the smaller
e.remove(),
// We only scale down - up would be silly
1>d&&(t*=d,a*=d,$(this).height(t).width(a)),$("#overlay").overlay({load:!0,speed:100,top:"center"})}).attr("src",$(this).attr("href")),!1}
// Set up image viewer (overlay)
$("a.overlay").live("click",e),$("#overlay").css("cursor","pointer").click(function(){$(this).fadeOut(100)})},$.ee_filemanager.datatables=function(){$(".mainTable").table("add_filter",$("#filterform"))};