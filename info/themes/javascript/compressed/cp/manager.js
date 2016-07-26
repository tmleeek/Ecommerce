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
/* This file exposes three callback functions:
 *
 * EE.manager.showPrefsRow and EE.manager.hidePrefsRow and
 * EE.manager.refreshPrefs
 */
/*jslint browser: true, onevar: true, undef: true, nomen: true, eqeqeq: true, plusplus: false, bitwise: true, regexp: false, strict: true, newcap: true, immed: true */
/*global $, jQuery, EE, window, document, console, alert */
"use strict";function refresh_prefs_ajax(e){$.ajax({type:"GET",url:EE.template_prefs_url,data:"is_ajax=TRUE&group_id="+e,dataType:"json",success:function(e){EE.pref_json=e}})}function access_edit_ajax(e){var t,a,n,i=[];
// We may be changing permissions for multiple element at a time
// if they selected a Select All option
e.each(function(e,r){var r=$(r);
// Handle template bounce setting
"no_auth_bounce"===r.attr("name").substr(0,14)?(t=r.attr("name").substr(15)?r.attr("name").substr(15):$("input:hidden[name=template_id]").val(),i.push({template_id:t,no_auth_bounce:r.val()})):"enable_http_auth"===r.attr("name").substr(0,16)?(t=r.attr("name").substr(17)?r.attr("name").substr(17):$("input:hidden[name=template_id]").val(),i.push({template_id:t,enable_http_auth:r.val()})):"template_route"===r.attr("name").substr(0,14)?(t=r.attr("name").substr(15)?r.attr("name").substr(15):$("input:hidden[name=template_id]").val(),i.push({template_id:t,template_route:r.val()})):"route_required"===r.attr("name").substr(0,14)?(t=r.attr("name").substr(15)?r.attr("name").substr(15):$("input:hidden[name=template_id]").val(),i.push({template_id:t,route_required:r.val()})):(a=r.attr("name").replace("access_","").split("_"),t=a.length<2?$("input:hidden[name=template_id]").val():a[1],n=$(r).closest(".accessTable").length?$(r).closest(".accessTable").find(".no_auth_bounce").val():$(".no_auth_bounce").val(),r.attr("checked","checked"),i.push({template_id:t,member_group_id:a[0],new_status:r.val(),no_auth_bounce:n}))}),$.ajax({type:"POST",url:EE.access_edit_url,data:{is_ajax:"TRUE",XID:EE.XID,payload:i},success:function(e){""!==e&&$.ee_notice(e,{duration:3e3,type:"success"})},error:function(e,t){""!==e.responseText&&$.ee_notice(e.responseText,{duration:3e3,type:"error"})}})}function template_edit_ajax(){var e,t,a,n,i,r,s,d,c,l,_,o,p,u=$(this).closest(".accessRowHeader");return u.length<1&&(u=$(this).closest(".templateEditorTable")),(e=u.data("ajax_ids"))?(t=e.id,a=e.group_id,n=u.find(".template_name").val(),i=u.find("select[name^=template_type]").val(),r=u.find("select[name^=cache]").val(),s=u.find(".refresh").val(),d=u.find("select[name^=allow_php]").val(),c=u.find("select[name^=php_parse_location]").val(),l=u.find(".hits").val(),_=u.find(".template_size").val(),o=u.find("select[name^=protect_javascript]").val(),p=jQuery.param({template_id:t,group_id:a,template_name:n,template_type:i,cache:r,refresh:s,hits:l,allow_php:d,php_parse_location:c,template_size:_,protect_javascript:o}),void $.ajax({type:"POST",url:EE.template_edit_url,data:"is_ajax=TRUE&XID="+EE.XID+"&"+p,success:function(e){var a,i=$("#templateId_"+t);i.text(n),i.closest(".templateName").length?(a=i.closest(".templateName").next().find("a"),a.length&&(a=a.get(0),a.href=a.href.replace(/\/[^\/]*$/,"/"+n))):$("#templateViewLink a.submit").length&&(a=$("#templateViewLink a.submit"),a.length&&(a=a.get(0),a.href=a.href.replace(/\/[^\/]*$/,"/"+n))),$("#template_data").attr("rows",_),$("#hitsId_"+t).text(l),""!==e&&$.ee_notice(e,{duration:3e3,type:"success"})},error:function(e,t){""!==e.responseText&&$.ee_notice(e.responseText,{duration:3e3,type:"error"})}})):$(this).hasClass("ignore_radio")?!1:access_edit_ajax($(this))}function hideSubRows(e,t){return t?void($(e).data(t)&&$(e).data(t).hide()):(hideSubRows(e,"prefsRow"),void hideSubRows(e,"accessRow"))}function hideRow(e,t){if(e.hasClass("highlightRow")&&e.removeClass("highlightRow"),e.data(t)){var a=e.data(t).is(":visible");return hideSubRows(e),a||(e.addClass("highlightRow"),e.data(t).show()),!0}return hideSubRows(e),!1}function set_radio_buttons(e,t){e.find("input:radio").each(function(){var e,a,n;e=$(this).attr("id").split("_"),a=e.slice(0,-1).join("_"),n=e.slice(-1)[0],$(this).attr({id:a+"_"+t+"_"+n,name:a+"_"+t})})}function bind_prefs_events(){$(".templateTable .accessTable").find("input:text").unbind("blur.manager_updated").bind("blur.manager_updated",template_edit_ajax),$(".templateTable .accessTable").find("input:radio").unbind("click.manager_updated").bind("click.manager_updated",template_edit_ajax),$(".templateTable .accessTable").find("select").unbind("change.manager_updated").bind("change.manager_updated",template_edit_ajax)}!function(e){var t,a;e(document).ready(function(){function n(t,a){var n="input:radio[id$=_";a&&(n="input:radio[id$=_"+a+"_"),t.find(".ignore_radio").click(function(){return"y"!==this.value&&"n"!==this.value||access_edit_ajax(t.find(n+this.value+"]").filter(":not(.ignore_radio)")),e(this).attr("checked",!1),!1})}function i(t,i,r){var s=e('<tr class="accessRowHeader"><td colspan="6">'+a+"</td></tr>");
// no_auth_bounce field
s.find(".no_auth_bounce").val(r.no_auth_bounce),s.find(".no_auth_bounce").attr({id:"no_auth_bounce_"+t,name:"no_auth_bounce_"+t}),
// http auth
s.find(".enable_http_auth").val(r.enable_http_auth),s.find(".enable_http_auth").attr({id:"enable_http_auth_"+t,name:"enable_http_auth_"+t}),
// template route
s.find(".template_route").val(r.template_route),s.find(".template_route").attr({id:"template_route_"+t,name:"template_route_"+t}),
// template route required
s.find(".route_required").val(r.route_required),s.find(".route_required").attr({id:"route_required_"+t,name:"route_required_"+t}),
// Set data, ids, and names
// Radio Buttons
set_radio_buttons(s,t),e.each(r.access,function(e,a){var n=s.find("#access_"+e+"_"+t+"_y"),i=s.find("#access_"+e+"_"+t+"_n");a.access===!0?(n.attr("checked","checked"),i.attr("checked",!1)):(i.attr("checked","checked"),n.attr("checked",!1))}),n(s,t),e(i).addClass("highlightRow"),e(i).after(s),
// Restripe!
s.find(".accessTable").tablesorter({widgets:["zebra"]}),i.data("accessRow",s)}function r(a,n){var i=e('<tr class="accessRowHeader"><td colspan="6">'+t+"</td></tr>");
// Set data, ids, and names
// Dropdowns
i.find("select").each(function(){var t=e(this);switch(this.name){case"template_type":t.val(n.type);break;case"cache":t.val(n.cache);break;case"allow_php":t.val(n.allow_php);break;case"php_parse_location":t.val(n.php_parsing);break;case"protect_javascript":t.val(n.protect_javascript)}t.attr("name",this.name+"_"+n.id)}),
// Name field
i.find(".template_name").val(n.name),"index"===n.name&&i.find(".template_name").attr({readonly:"readonly"}),
// Refresh Interval
i.find(".refresh").val(n.refresh),
// Hit Count
i.find(".hits").val(n.hits),
// Entry id and group id
i.data("ajax_ids",{id:n.id,group_id:n.group_id}),a.data("prefsRow",i),e(a).addClass("highlightRow"),e(a).after(i)}var s,d,c;
// Template editor page?
// Expose the three click callback functions - events bound in the controller
// Template editor page?
return t=e("#prefRowTemplate").html(),a=e("#accessRowTemplate").html(),EE.manager={refreshPrefs:function(e){refresh_prefs_ajax(e)},showPrefsRow:function(t,a){var n=e(a).parent().parent();return hideRow(n,"prefsRow")||(r(n,t),bind_prefs_events()),!1},showAccessRow:function(t,a,n){var r=e(n).parent().parent();return hideRow(r,"accessRow")||(i(t,r,a),bind_prefs_events(),r.trigger("applyWidgets")),!1}},t&&a?void e("#prefRowTemplate, #accessRowTemplate").remove():(s=e("#templateAccess, #templatePreferences"),d=e("input:hidden[name=template_id]").val(),c=e("input:hidden[name=group_id]").val(),e("#templatePreferences").data("ajax_ids",{id:d,group_id:c}),n(e("#templateAccess")),s.find("input:text").unbind("blur.manager_updated").bind("blur.manager_updated",template_edit_ajax),s.find("input:radio").unbind("click.manager_updated").bind("click.manager_updated",template_edit_ajax),void s.find("select").unbind("change.manager_updated").bind("change.manager_updated",template_edit_ajax))}),e(".last_edit").css("opacity",0).show(),e("#template_details").hover(function(){e(".last_edit").animate({opacity:1},50)},function(){e(".last_edit").animate({opacity:0},50)}),
// Template search reset
e("#template_keywords_reset").click(function(){e("#template_keywords").val(""),e(".search form").submit()})}(jQuery);