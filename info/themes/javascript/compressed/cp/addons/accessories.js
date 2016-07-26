// Za Toggles, zey do nuffink!
function prep_class(t){var s=$(t).parent().parent().attr("class").replace(/even/,"").replace(/odd/,"").replace(/ /,"").substring(0,6);return s.length<6&&(s+="_"),".sub_"+s}function table_stripe(){$("table tbody tr:visible:even").addClass("even"),$("table tbody tr:visible:odd").addClass("odd")}$(".toggle_controllers").toggle(function(){$("input[class=toggle_controller]").each(function(){this.checked=!0})},function(){$("input[class=toggle_controller]").each(function(){this.checked=!1})}),$(".toggle_groups").toggle(function(){$("input[class=toggle_group]").each(function(){this.checked=!0})},function(){$("input[class=toggle_group]").each(function(){this.checked=!1})});
// hide sub controllers
var subs=$(".sub_addons, .sub_admin_, .sub_conten, .sub_tools_").hide();
// add plus sign to parent controllers
$(".addons td:first, .admin td:first, .content td:first, .tools td:first").prepend('<img class="acc_toggle" width="11" height="10" src="'+EE.THEME_URL+'images/publish_plus.png" alt="" style="float:left;position:absolute;" />'),subs.find("td.controller_label").css("padding-left","36px"),$(".acc_toggle").css("cursor","pointer"),// just styling it like a link
// toggle visible and invisible
$(".acc_toggle").toggle(function(){var t=prep_class($(this));$(this).attr("src",EE.THEME_URL+"images/publish_minus.gif"),$(t).each(function(){$(this).show()}),table_stripe()},function(){var t=prep_class($(this));$(this).attr("src",EE.THEME_URL+"images/publish_plus.png"),$(t).each(function(){$(this).hide()}),table_stripe()}),
// toggle checkboxes for groups
// toggle visible and invisible
$(".addons input, .admin input, .content input, .tools input").click(function(){var t=$(this).attr("checked"),s=prep_class($(this))+" input";$(s).each(function(){$(this).attr("checked",t)})}),table_stripe();