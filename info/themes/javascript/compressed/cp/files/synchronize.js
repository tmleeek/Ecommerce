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
EE.file_manager=EE.file_manager||{},EE.file_manager.sync_files=EE.file_manager.sync_files||{},EE.file_manager.sync_db=0,EE.file_manager.sync_running=0,EE.file_manager.sync_errors=[],EE.file_manager.resize_ids=[],$(document).ready(function(){$.template("sync_complete_template",$("#sync_complete_template")),EE.file_manager.sync_listen()}),EE.file_manager.sync_listen=function(){$(".tableSubmit input").click(function(e){e.preventDefault(),
// Hide button
$(this).hide(),
// Show progress bar
EE.file_manager.update_progress(),
// Get array of files
EE.file_manager.sync_files=_.toArray(EE.file_manager.sync_files);
// Get upload directory
var n=_.keys(EE.file_manager.sync_sizes)[0];EE.file_manager.update_progress(0),
// Send ajax requests
// Note- testing didn't show async made much improvement on time
setTimeout(function(){EE.file_manager.sync(n)},15)})},EE.file_manager.resize_ids=function(){var e=[];return $('input[name="toggle[]"]:checked').each(function(){e.push($(this).val())}),e},/**
 * Fire off the Ajax request, which then listens for the finish and then fires off the next Ajax request and so on
 *
 * @param {Number} upload_directory_id The id of the upload directory to pass to the controller method
 */
EE.file_manager.sync=function(e){
// If no files are left, check if db sync has run- if so, get outta here
if(EE.file_manager.sync_files.length<=0){if("y"==EE.file_manager.db_sync)return;EE.file_manager.db_sync="y"}
// There should only be one place we're splicing the files array and THIS is it
var n=EE.file_manager.sync_files.splice(0,5);$.ajax({url:EE.BASE+"&C=content_files&M=do_sync_files",type:"POST",dataType:"json",data:{XID:EE.XID,upload_directory_id:e,sizes:EE.file_manager.sync_sizes,files:n,resize_ids:EE.file_manager.resize_ids(),db_sync:EE.file_manager.db_sync},beforeSend:function(e,n){
// Increment the running count
EE.file_manager.sync_running+=1},complete:function(n,r){
// Decrement the running count
EE.file_manager.sync_running-=1,
// Fire off another Ajax request
EE.file_manager.sync(e);
// Update the progress bar
var a=EE.file_manager.sync_file_count,s=EE.file_manager.sync_files.length,i=a-s;EE.file_manager.update_progress(Math.round(i/a*100)),EE.file_manager.finish_sync(e)},success:function(e,n,r){if("success"!=e.message_type)if("undefined"!=typeof e.errors)for(var a in e.errors)EE.file_manager.sync_errors.push("<b>"+a+"</b>: "+e.errors[a]);else EE.file_manager.sync_errors.push("<b>Undefined errors</b>")}})},EE.file_manager.get_directory_name=function(e){return $("#sync table:first tr[data-id="+e+"] td:first").text()},/**
 * Show the sync complete summary
 *
 * This should contain the number of files processed, the number of errors and the errors themselves
 */
EE.file_manager.finish_sync=function(e){if(0==EE.file_manager.sync_running){$("#progress").hide();var n={directory_name:EE.file_manager.get_directory_name(e),files_processed:EE.file_manager.sync_file_count-EE.file_manager.sync_errors.length,errors:EE.file_manager.sync_errors,error_count:EE.file_manager.sync_errors.length};$.tmpl("sync_complete_template",n).appendTo("#sync"),
// You can't have a conditional template in a table because Firefox ignores anything in a table that's untablelike
0==n.error_count?$("#sync_complete ul").hide():$("#sync_complete span").hide()}},/**
 * Update the progress bar
 *
 * @param {Number} progress_percentage The percentage of progress, represented as an integer (e.g. 56 = 56%)
 */
EE.file_manager.update_progress=function(e){var n=$("#progress"),r=$("#progress_bar");n.is(":not(:visible)")&&n.show(),r.progressbar({value:e})};