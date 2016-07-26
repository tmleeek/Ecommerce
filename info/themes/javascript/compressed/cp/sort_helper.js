/**
 * Fixes an issue in jQuery UI's Sortable implementation of it's
 * tolerance: 'intercect' option not working correctly; this fix
 * ensures once an item overlaps by 50%, the sort happens, and does
 * not depend on the position of the cursor
 */
EE.sortable_sort_helper=function(e,i){
// Get the axis to determine if we're working with heights or widths
var t=0==$(this).sortable("option","axis")?"y":$(this).sortable("option","axis"),r=$(this),o=r.children(".ui-sortable-placeholder:first"),s="y"==t?i.helper.outerHeight():i.helper.outerWidth(),l="y"==t?i.position.top:i.position.left,a=l+s;r.children(":visible").each(function(){var e=$(this);if(!e.hasClass("ui-sortable-helper")&&!e.hasClass("ui-sortable-placeholder")){var i="y"==t?e.outerHeight():e.outerWidth(),h="y"==t?e.position().top:e.position().left,n=h+i,f=Math.min(s,i)/2;if(l>h&&n>l){var p=l-h;if(f>p)return o.insertBefore(e),r.sortable("refreshPositions"),!1}else if(n>a&&a>h){var p=n-a;if(f>p)return o.insertAfter(e),r.sortable("refreshPositions"),!1}}})};