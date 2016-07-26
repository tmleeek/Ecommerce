/*!
 * jQuery UI Sortable @VERSION
 *
 * Copyright 2012, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Sortables
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.mouse.js
 *	jquery.ui.widget.js
 */
!function(t,e){t.widget("ui.sortable",t.ui.mouse,{widgetEventPrefix:"sort",ready:!1,options:{appendTo:"parent",axis:!1,connectWith:!1,containment:!1,cursor:"auto",cursorAt:!1,dropOnEmpty:!0,forcePlaceholderSize:!1,forceHelperSize:!1,grid:!1,handle:!1,helper:"original",items:"> *",opacity:!1,placeholder:!1,revert:!1,scroll:!0,scrollSensitivity:20,scrollSpeed:20,scope:"default",tolerance:"intersect",zIndex:1e3},_create:function(){var t=this.options;this.containerCache={},this.element.addClass("ui-sortable"),
//Get the items
this.refresh(),
//Let's determine if the items are being displayed horizontally
this.floating=this.items.length?"x"===t.axis||/left|right/.test(this.items[0].item.css("float"))||/inline|table-cell/.test(this.items[0].item.css("display")):!1,
//Let's determine the parent's offset
this.offset=this.element.offset(),
//Initialize mouse events for interaction
this._mouseInit(),
//We're ready to go
this.ready=!0},destroy:function(){t.Widget.prototype.destroy.call(this),this.element.removeClass("ui-sortable ui-sortable-disabled"),this._mouseDestroy();for(var e=this.items.length-1;e>=0;e--)this.items[e].item.removeData(this.widgetName+"-item");return this},_setOption:function(e,i){"disabled"===e?(this.options[e]=i,this.widget()[i?"addClass":"removeClass"]("ui-sortable-disabled")):
// Don't call widget base _setOption for disable as it adds ui-state-disabled class
t.Widget.prototype._setOption.apply(this,arguments)},_mouseCapture:function(e,i){var s=this;if(this.reverting)return!1;if(this.options.disabled||"static"==this.options.type)return!1;
//We have to refresh the items data once first
this._refreshItems(e);
//Find out if the clicked node (or one of its parents) is a actual item in this.items
var r=null,o=this;t(e.target).parents().each(function(){return t.data(this,s.widgetName+"-item")==o?(r=t(this),!1):void 0});if(t.data(e.target,s.widgetName+"-item")==o&&(r=t(e.target)),!r)return!1;if(this.options.handle&&!i){var n=!1;if(t(this.options.handle,r).find("*").andSelf().each(function(){this==e.target&&(n=!0)}),!n)return!1}return this.currentItem=r,this._removeCurrentsFromItems(),!0},_mouseStart:function(e,i,s){var r=this.options,o=this;
//Post 'activate' events to possible containers
if(this.currentContainer=this,
//We only need to call refreshPositions, because the refreshItems call has been moved to mouseCapture
this.refreshPositions(),
//Create and append the visible helper
this.helper=this._createHelper(e),
//Cache the helper size
this._cacheHelperProportions(),/*
		 * - Position generation -
		 * This block generates everything position related - it's the core of draggables.
		 */
//Cache the margins of the original element
this._cacheMargins(),
//Get the next scrolling parent
this.scrollParent=this.helper.scrollParent(),
//The element's absolute position on the page minus margins
this.offset=this.currentItem.offset(),this.offset={top:this.offset.top-this.margins.top,left:this.offset.left-this.margins.left},
// Only after we got the offset, we can change the helper's position to absolute
// TODO: Still need to figure out a way to make relative sorting possible
this.helper.css("position","absolute"),this.cssPosition=this.helper.css("position"),t.extend(this.offset,{click:{//Where the click happened, relative to the element
left:e.pageX-this.offset.left,top:e.pageY-this.offset.top},parent:this._getParentOffset(),relative:this._getRelativeOffset()}),
//Generate the original position
this.originalPosition=this._generatePosition(e),this.originalPageX=e.pageX,this.originalPageY=e.pageY,
//Adjust the mouse offset relative to the helper if 'cursorAt' is supplied
r.cursorAt&&this._adjustOffsetFromHelper(r.cursorAt),
//Cache the former DOM position
this.domPosition={prev:this.currentItem.prev()[0],parent:this.currentItem.parent()[0]},
//If the helper is not the original, hide the original so it's not playing any role during the drag, won't cause anything bad this way
this.helper[0]!=this.currentItem[0]&&this.currentItem.hide(),
//Create the placeholder
this._createPlaceholder(),
//Set a containment if given in the options
r.containment&&this._setContainment(),r.cursor&&(// cursor option
t("body").css("cursor")&&(this._storedCursor=t("body").css("cursor")),t("body").css("cursor",r.cursor)),r.opacity&&(// opacity option
this.helper.css("opacity")&&(this._storedOpacity=this.helper.css("opacity")),this.helper.css("opacity",r.opacity)),r.zIndex&&(// zIndex option
this.helper.css("zIndex")&&(this._storedZIndex=this.helper.css("zIndex")),this.helper.css("zIndex",r.zIndex)),
//Prepare scrolling
this.scrollParent[0]!=document&&"HTML"!=this.scrollParent[0].tagName&&(this.overflowOffset=this.scrollParent.offset()),
//Call callbacks
this._trigger("start",e,this._uiHash()),
//Recache the helper size
this._preserveHelperProportions||this._cacheHelperProportions(),!s)for(var n=this.containers.length-1;n>=0;n--)this.containers[n]._trigger("activate",e,o._uiHash(this));//Execute the drag once - this causes the helper not to be visible before getting its correct position
//Prepare possible droppables
return t.ui.ddmanager&&(t.ui.ddmanager.current=this),t.ui.ddmanager&&!r.dropBehaviour&&t.ui.ddmanager.prepareOffsets(this,e),this.dragging=!0,this.helper.addClass("ui-sortable-helper"),this._mouseDrag(e),!0},_mouseDrag:function(e){
//Do scrolling
if(
//Compute the helpers position
this.position=this._generatePosition(e),this.positionAbs=this._convertPositionTo("absolute"),this.lastPositionAbs||(this.lastPositionAbs=this.positionAbs),this.options.scroll){var i=this.options,s=!1;this.scrollParent[0]!=document&&"HTML"!=this.scrollParent[0].tagName?(this.overflowOffset.top+this.scrollParent[0].offsetHeight-e.pageY<i.scrollSensitivity?this.scrollParent[0].scrollTop=s=this.scrollParent[0].scrollTop+i.scrollSpeed:e.pageY-this.overflowOffset.top<i.scrollSensitivity&&(this.scrollParent[0].scrollTop=s=this.scrollParent[0].scrollTop-i.scrollSpeed),this.overflowOffset.left+this.scrollParent[0].offsetWidth-e.pageX<i.scrollSensitivity?this.scrollParent[0].scrollLeft=s=this.scrollParent[0].scrollLeft+i.scrollSpeed:e.pageX-this.overflowOffset.left<i.scrollSensitivity&&(this.scrollParent[0].scrollLeft=s=this.scrollParent[0].scrollLeft-i.scrollSpeed)):(e.pageY-t(document).scrollTop()<i.scrollSensitivity?s=t(document).scrollTop(t(document).scrollTop()-i.scrollSpeed):t(window).height()-(e.pageY-t(document).scrollTop())<i.scrollSensitivity&&(s=t(document).scrollTop(t(document).scrollTop()+i.scrollSpeed)),e.pageX-t(document).scrollLeft()<i.scrollSensitivity?s=t(document).scrollLeft(t(document).scrollLeft()-i.scrollSpeed):t(window).width()-(e.pageX-t(document).scrollLeft())<i.scrollSensitivity&&(s=t(document).scrollLeft(t(document).scrollLeft()+i.scrollSpeed))),s!==!1&&t.ui.ddmanager&&!i.dropBehaviour&&t.ui.ddmanager.prepareOffsets(this,e)}
//Regenerate the absolute position used for position checks
this.positionAbs=this._convertPositionTo("absolute"),
//Set the helper position
this.options.axis&&"y"==this.options.axis||(this.helper[0].style.left=this.position.left+"px"),this.options.axis&&"x"==this.options.axis||(this.helper[0].style.top=this.position.top+"px");
//Rearrange
for(var r=this.items.length-1;r>=0;r--){
//Cache variables and intersection, continue if no intersection
var o=this.items[r],n=o.item[0],h=this._intersectsWithPointer(o);if(h&&n!=this.currentItem[0]&&this.placeholder[1==h?"next":"prev"]()[0]!=n&&!t.ui.contains(this.placeholder[0],n)&&("semi-dynamic"==this.options.type?!t.ui.contains(this.element[0],n):!0)){if(this.direction=1==h?"down":"up","pointer"!=this.options.tolerance&&!this._intersectsWithSides(o))break;this._rearrange(e,o),this._trigger("change",e,this._uiHash());break}}
//Post events to containers
//Interconnect with droppables
//Call callbacks
return this._contactContainers(e),t.ui.ddmanager&&t.ui.ddmanager.drag(this,e),this._trigger("sort",e,this._uiHash()),this.lastPositionAbs=this.positionAbs,!1},_mouseStop:function(e,i){if(e){if(
//If we are using droppables, inform the manager about the drop
t.ui.ddmanager&&!this.options.dropBehaviour&&t.ui.ddmanager.drop(this,e),this.options.revert){var s=this,r=s.placeholder.offset();s.reverting=!0,t(this.helper).animate({left:r.left-this.offset.parent.left-s.margins.left+(this.offsetParent[0]==document.body?0:this.offsetParent[0].scrollLeft),top:r.top-this.offset.parent.top-s.margins.top+(this.offsetParent[0]==document.body?0:this.offsetParent[0].scrollTop)},parseInt(this.options.revert,10)||500,function(){s._clear(e)})}else this._clear(e,i);return!1}},cancel:function(){var e=this;if(this.dragging){this._mouseUp({target:null}),"original"==this.options.helper?this.currentItem.css(this._storedCSS).removeClass("ui-sortable-helper"):this.currentItem.show();
//Post deactivating events to containers
for(var i=this.containers.length-1;i>=0;i--)this.containers[i]._trigger("deactivate",null,e._uiHash(this)),this.containers[i].containerCache.over&&(this.containers[i]._trigger("out",null,e._uiHash(this)),this.containers[i].containerCache.over=0)}
//$(this.placeholder[0]).remove(); would have been the jQuery way - unfortunately, it unbinds ALL events from the original node!
return this.placeholder&&(this.placeholder[0].parentNode&&this.placeholder[0].parentNode.removeChild(this.placeholder[0]),"original"!=this.options.helper&&this.helper&&this.helper[0].parentNode&&this.helper.remove(),t.extend(this,{helper:null,dragging:!1,reverting:!1,_noFinalSort:null}),this.domPosition.prev?t(this.domPosition.prev).after(this.currentItem):t(this.domPosition.parent).prepend(this.currentItem)),this},serialize:function(e){var i=this._getItemsAsjQuery(e&&e.connected),s=[];return e=e||{},t(i).each(function(){var i=(t(e.item||this).attr(e.attribute||"id")||"").match(e.expression||/(.+)[-=_](.+)/);i&&s.push((e.key||i[1]+"[]")+"="+(e.key&&e.expression?i[1]:i[2]))}),!s.length&&e.key&&s.push(e.key+"="),s.join("&")},toArray:function(e){var i=this._getItemsAsjQuery(e&&e.connected),s=[];return e=e||{},i.each(function(){s.push(t(e.item||this).attr(e.attribute||"id")||"")}),s},/* Be careful with the following core functions */
_intersectsWith:function(t){var e=this.positionAbs.left,i=e+this.helperProportions.width,s=this.positionAbs.top,r=s+this.helperProportions.height,o=t.left,n=o+t.width,h=t.top,a=h+t.height,l=this.offset.click.top,c=this.offset.click.left,p=s+l>h&&a>s+l&&e+c>o&&n>e+c;return"pointer"==this.options.tolerance||this.options.forcePointerForContainers||"pointer"!=this.options.tolerance&&this.helperProportions[this.floating?"width":"height"]>t[this.floating?"width":"height"]?p:o<e+this.helperProportions.width/2&&i-this.helperProportions.width/2<n&&h<s+this.helperProportions.height/2&&r-this.helperProportions.height/2<a},_intersectsWithPointer:function(e){var i="x"===this.options.axis||t.ui.isOverAxis(this.positionAbs.top+this.offset.click.top,e.top,e.height),s="y"===this.options.axis||t.ui.isOverAxis(this.positionAbs.left+this.offset.click.left,e.left,e.width),r=i&&s,o=this._getDragVerticalDirection(),n=this._getDragHorizontalDirection();return r?this.floating?n&&"right"==n||"down"==o?2:1:o&&("down"==o?2:1):!1},_intersectsWithSides:function(e){var i=t.ui.isOverAxis(this.positionAbs.top+this.offset.click.top,e.top+e.height/2,e.height),s=t.ui.isOverAxis(this.positionAbs.left+this.offset.click.left,e.left+e.width/2,e.width),r=this._getDragVerticalDirection(),o=this._getDragHorizontalDirection();return this.floating&&o?"right"==o&&s||"left"==o&&!s:r&&("down"==r&&i||"up"==r&&!i)},_getDragVerticalDirection:function(){var t=this.positionAbs.top-this.lastPositionAbs.top;return 0!=t&&(t>0?"down":"up")},_getDragHorizontalDirection:function(){var t=this.positionAbs.left-this.lastPositionAbs.left;return 0!=t&&(t>0?"right":"left")},refresh:function(t){return this._refreshItems(t),this.refreshPositions(),this},_connectWith:function(){var t=this.options;return t.connectWith.constructor==String?[t.connectWith]:t.connectWith},_getItemsAsjQuery:function(e){var i=[],s=[],r=this._connectWith();if(r&&e)for(var o=r.length-1;o>=0;o--)for(var n=t(r[o]),h=n.length-1;h>=0;h--){var a=t.data(n[h],this.widgetName);a&&a!=this&&!a.options.disabled&&s.push([t.isFunction(a.options.items)?a.options.items.call(a.element):t(a.options.items,a.element).not(".ui-sortable-helper").not(".ui-sortable-placeholder"),a])}s.push([t.isFunction(this.options.items)?this.options.items.call(this.element,null,{options:this.options,item:this.currentItem}):t(this.options.items,this.element).not(".ui-sortable-helper").not(".ui-sortable-placeholder"),this]);for(var o=s.length-1;o>=0;o--)s[o][0].each(function(){i.push(this)});return t(i)},_removeCurrentsFromItems:function(){for(var t=this.currentItem.find(":data("+this.widgetName+"-item)"),e=0;e<this.items.length;e++)for(var i=0;i<t.length;i++)t[i]==this.items[e].item[0]&&this.items.splice(e,1)},_refreshItems:function(e){this.items=[],this.containers=[this];var i=this.items,s=[[t.isFunction(this.options.items)?this.options.items.call(this.element[0],e,{item:this.currentItem}):t(this.options.items,this.element),this]],r=this._connectWith();if(r&&this.ready)//Shouldn't be run the first time through due to massive slow-down
for(var o=r.length-1;o>=0;o--)for(var n=t(r[o]),h=n.length-1;h>=0;h--){var a=t.data(n[h],this.widgetName);a&&a!=this&&!a.options.disabled&&(s.push([t.isFunction(a.options.items)?a.options.items.call(a.element[0],e,{item:this.currentItem}):t(a.options.items,a.element),a]),this.containers.push(a))}for(var o=s.length-1;o>=0;o--)for(var l=s[o][1],c=s[o][0],h=0,p=c.length;p>h;h++){var f=t(c[h]);f.data(this.widgetName+"-item",l),// Data for target checking (mouse manager)
i.push({item:f,instance:l,width:0,height:0,left:0,top:0})}},refreshPositions:function(e){
//This has to be redone because due to the item being moved out/into the offsetParent, the offsetParent's position will change
this.offsetParent&&this.helper&&(this.offset.parent=this._getParentOffset());for(var i=this.items.length-1;i>=0;i--){var s=this.items[i];
//We ignore calculating positions of all connected containers when we're not over them
if(s.instance==this.currentContainer||!this.currentContainer||s.item[0]==this.currentItem[0]){var r=this.options.toleranceElement?t(this.options.toleranceElement,s.item):s.item;e||(s.width=r.outerWidth(),s.height=r.outerHeight());var o=r.offset();s.left=o.left,s.top=o.top}}if(this.options.custom&&this.options.custom.refreshContainers)this.options.custom.refreshContainers.call(this);else for(var i=this.containers.length-1;i>=0;i--){var o=this.containers[i].element.offset();this.containers[i].containerCache.left=o.left,this.containers[i].containerCache.top=o.top,this.containers[i].containerCache.width=this.containers[i].element.outerWidth(),this.containers[i].containerCache.height=this.containers[i].element.outerHeight()}return this},_createPlaceholder:function(e){var i=e||this,s=i.options;if(!s.placeholder||s.placeholder.constructor==String){var r=s.placeholder;s.placeholder={element:function(){var e=t(document.createElement(i.currentItem[0].nodeName)).addClass(r||i.currentItem[0].className+" ui-sortable-placeholder").removeClass("ui-sortable-helper")[0];return r||(e.style.visibility="hidden"),e},update:function(t,e){
// 1. If a className is set as 'placeholder option, we don't force sizes - the class is responsible for that
// 2. The option 'forcePlaceholderSize can be enabled to force it even if a class name is specified
r&&!s.forcePlaceholderSize||(
//If the element doesn't have a actual height by itself (without styles coming from a stylesheet), it receives the inline height from the dragged item
e.height()||e.height(i.currentItem.innerHeight()-parseInt(i.currentItem.css("paddingTop")||0,10)-parseInt(i.currentItem.css("paddingBottom")||0,10)),e.width()||e.width(i.currentItem.innerWidth()-parseInt(i.currentItem.css("paddingLeft")||0,10)-parseInt(i.currentItem.css("paddingRight")||0,10)))}}}
//Create the placeholder
i.placeholder=t(s.placeholder.element.call(i.element,i.currentItem)),
//Append it after the actual current item
i.currentItem.after(i.placeholder),
//Update the size of the placeholder (TODO: Logic to fuzzy, see line 316/317)
s.placeholder.update(i,i.placeholder)},_contactContainers:function(e){for(var i=null,s=null,r=this.containers.length-1;r>=0;r--)
// never consider a container that's located within the item itself 
if(!t.ui.contains(this.currentItem[0],this.containers[r].element[0]))if(this._intersectsWith(this.containers[r].containerCache)){
// if we've already found a container and it's more "inner" than this, then continue 
if(i&&t.ui.contains(this.containers[r].element[0],i.element[0]))continue;i=this.containers[r],s=r}else
// container doesn't intersect. trigger "out" event if necessary 
this.containers[r].containerCache.over&&(this.containers[r]._trigger("out",e,this._uiHash(this)),this.containers[r].containerCache.over=0);
// if no intersecting containers found, return 
if(i)
// move the item into the container if it's not there already
if(1===this.containers.length)this.containers[s]._trigger("over",e,this._uiHash(this)),this.containers[s].containerCache.over=1;else if(this.currentContainer!=this.containers[s]){for(var o=1e4,n=null,h=this.positionAbs[this.containers[s].floating?"left":"top"],a=this.items.length-1;a>=0;a--)if(t.ui.contains(this.containers[s].element[0],this.items[a].item[0])){var l=this.items[a][this.containers[s].floating?"left":"top"];Math.abs(l-h)<o&&(o=Math.abs(l-h),n=this.items[a])}if(!n&&!this.options.dropOnEmpty)//Check if dropOnEmpty is enabled 
return;this.currentContainer=this.containers[s],n?this._rearrange(e,n,null,!0):this._rearrange(e,null,this.containers[s].element,!0),this._trigger("change",e,this._uiHash()),this.containers[s]._trigger("change",e,this._uiHash(this)),
//Update the placeholder 
this.options.placeholder.update(this.currentContainer,this.placeholder),this.containers[s]._trigger("over",e,this._uiHash(this)),this.containers[s].containerCache.over=1}},_createHelper:function(e){var i=this.options,s=t.isFunction(i.helper)?t(i.helper.apply(this.element[0],[e,this.currentItem])):"clone"==i.helper?this.currentItem.clone():this.currentItem;//Add the helper to the DOM if that didn't happen already
return s.parents("body").length||t("parent"!=i.appendTo?i.appendTo:this.currentItem[0].parentNode)[0].appendChild(s[0]),s[0]==this.currentItem[0]&&(this._storedCSS={width:this.currentItem[0].style.width,height:this.currentItem[0].style.height,position:this.currentItem.css("position"),top:this.currentItem.css("top"),left:this.currentItem.css("left")}),(""==s[0].style.width||i.forceHelperSize)&&s.width(this.currentItem.width()),(""==s[0].style.height||i.forceHelperSize)&&s.height(this.currentItem.height()),s},_adjustOffsetFromHelper:function(e){"string"==typeof e&&(e=e.split(" ")),t.isArray(e)&&(e={left:+e[0],top:+e[1]||0}),"left"in e&&(this.offset.click.left=e.left+this.margins.left),"right"in e&&(this.offset.click.left=this.helperProportions.width-e.right+this.margins.left),"top"in e&&(this.offset.click.top=e.top+this.margins.top),"bottom"in e&&(this.offset.click.top=this.helperProportions.height-e.bottom+this.margins.top)},_getParentOffset:function(){
//Get the offsetParent and cache its position
this.offsetParent=this.helper.offsetParent();var e=this.offsetParent.offset();
// This is a special case where we need to modify a offset calculated on start, since the following happened:
// 1. The position of the helper is absolute, so it's position is calculated based on the next positioned parent
// 2. The actual offset parent is a child of the scroll parent, and the scroll parent isn't the document, which means that
//    the scroll is included in the initial calculation of the offset of the parent, and never recalculated upon drag
//Ugly IE fix
return"absolute"==this.cssPosition&&this.scrollParent[0]!=document&&t.ui.contains(this.scrollParent[0],this.offsetParent[0])&&(e.left+=this.scrollParent.scrollLeft(),e.top+=this.scrollParent.scrollTop()),(this.offsetParent[0]==document.body||this.offsetParent[0].tagName&&"html"==this.offsetParent[0].tagName.toLowerCase()&&t.browser.msie)&&(e={top:0,left:0}),{top:e.top+(parseInt(this.offsetParent.css("borderTopWidth"),10)||0),left:e.left+(parseInt(this.offsetParent.css("borderLeftWidth"),10)||0)}},_getRelativeOffset:function(){if("relative"==this.cssPosition){var t=this.currentItem.position();return{top:t.top-(parseInt(this.helper.css("top"),10)||0)+this.scrollParent.scrollTop(),left:t.left-(parseInt(this.helper.css("left"),10)||0)+this.scrollParent.scrollLeft()}}return{top:0,left:0}},_cacheMargins:function(){this.margins={left:parseInt(this.currentItem.css("marginLeft"),10)||0,top:parseInt(this.currentItem.css("marginTop"),10)||0}},_cacheHelperProportions:function(){this.helperProportions={width:this.helper.outerWidth(),height:this.helper.outerHeight()}},_setContainment:function(){var e=this.options;if("parent"==e.containment&&(e.containment=this.helper[0].parentNode),"document"!=e.containment&&"window"!=e.containment||(this.containment=[0-this.offset.relative.left-this.offset.parent.left,0-this.offset.relative.top-this.offset.parent.top,t("document"==e.containment?document:window).width()-this.helperProportions.width-this.margins.left,(t("document"==e.containment?document:window).height()||document.body.parentNode.scrollHeight)-this.helperProportions.height-this.margins.top]),!/^(document|window|parent)$/.test(e.containment)){var i=t(e.containment)[0],s=t(e.containment).offset(),r="hidden"!=t(i).css("overflow");this.containment=[s.left+(parseInt(t(i).css("borderLeftWidth"),10)||0)+(parseInt(t(i).css("paddingLeft"),10)||0)-this.margins.left,s.top+(parseInt(t(i).css("borderTopWidth"),10)||0)+(parseInt(t(i).css("paddingTop"),10)||0)-this.margins.top,s.left+(r?Math.max(i.scrollWidth,i.offsetWidth):i.offsetWidth)-(parseInt(t(i).css("borderLeftWidth"),10)||0)-(parseInt(t(i).css("paddingRight"),10)||0)-this.helperProportions.width-this.margins.left,s.top+(r?Math.max(i.scrollHeight,i.offsetHeight):i.offsetHeight)-(parseInt(t(i).css("borderTopWidth"),10)||0)-(parseInt(t(i).css("paddingBottom"),10)||0)-this.helperProportions.height-this.margins.top]}},_convertPositionTo:function(e,i){i||(i=this.position);var s="absolute"==e?1:-1,r=(this.options,"absolute"!=this.cssPosition||this.scrollParent[0]!=document&&t.ui.contains(this.scrollParent[0],this.offsetParent[0])?this.scrollParent:this.offsetParent),o=/(html|body)/i.test(r[0].tagName);return{top:i.top+this.offset.relative.top*s+this.offset.parent.top*s-(t.browser.safari&&"fixed"==this.cssPosition?0:("fixed"==this.cssPosition?-this.scrollParent.scrollTop():o?0:r.scrollTop())*s),left:i.left+this.offset.relative.left*s+this.offset.parent.left*s-(t.browser.safari&&"fixed"==this.cssPosition?0:("fixed"==this.cssPosition?-this.scrollParent.scrollLeft():o?0:r.scrollLeft())*s)}},_generatePosition:function(e){var i=this.options,s="absolute"!=this.cssPosition||this.scrollParent[0]!=document&&t.ui.contains(this.scrollParent[0],this.offsetParent[0])?this.scrollParent:this.offsetParent,r=/(html|body)/i.test(s[0].tagName);
// This is another very weird special case that only happens for relative elements:
// 1. If the css position is relative
// 2. and the scroll parent is the document or similar to the offset parent
// we have to refresh the relative offset during the scroll so there are no jumps
"relative"!=this.cssPosition||this.scrollParent[0]!=document&&this.scrollParent[0]!=this.offsetParent[0]||(this.offset.relative=this._getRelativeOffset());var o=e.pageX,n=e.pageY;/*
		 * - Position constraining -
		 * Constrain the position to a mix of grid, containment.
		 */
if(this.originalPosition&&(//If we are not dragging yet, we won't check for options
this.containment&&(e.pageX-this.offset.click.left<this.containment[0]&&(o=this.containment[0]+this.offset.click.left),e.pageY-this.offset.click.top<this.containment[1]&&(n=this.containment[1]+this.offset.click.top),e.pageX-this.offset.click.left>this.containment[2]&&(o=this.containment[2]+this.offset.click.left),e.pageY-this.offset.click.top>this.containment[3]&&(n=this.containment[3]+this.offset.click.top)),i.grid)){var h=this.originalPageY+Math.round((n-this.originalPageY)/i.grid[1])*i.grid[1];n=this.containment&&(h-this.offset.click.top<this.containment[1]||h-this.offset.click.top>this.containment[3])?h-this.offset.click.top<this.containment[1]?h+i.grid[1]:h-i.grid[1]:h;var a=this.originalPageX+Math.round((o-this.originalPageX)/i.grid[0])*i.grid[0];o=this.containment&&(a-this.offset.click.left<this.containment[0]||a-this.offset.click.left>this.containment[2])?a-this.offset.click.left<this.containment[0]?a+i.grid[0]:a-i.grid[0]:a}return{top:n-this.offset.click.top-this.offset.relative.top-this.offset.parent.top+(t.browser.safari&&"fixed"==this.cssPosition?0:"fixed"==this.cssPosition?-this.scrollParent.scrollTop():r?0:s.scrollTop()),left:o-this.offset.click.left-this.offset.relative.left-this.offset.parent.left+(t.browser.safari&&"fixed"==this.cssPosition?0:"fixed"==this.cssPosition?-this.scrollParent.scrollLeft():r?0:s.scrollLeft())}},_rearrange:function(t,e,i,s){i?i[0].appendChild(this.placeholder[0]):e.item[0].parentNode.insertBefore(this.placeholder[0],"down"==this.direction?e.item[0]:e.item[0].nextSibling),
//Various things done here to improve the performance:
// 1. we create a setTimeout, that calls refreshPositions
// 2. on the instance, we have a counter variable, that get's higher after every append
// 3. on the local scope, we copy the counter variable, and check in the timeout, if it's still the same
// 4. this lets only the last addition to the timeout stack through
this.counter=this.counter?++this.counter:1;var r=this,o=this.counter;window.setTimeout(function(){o==r.counter&&r.refreshPositions(!s)},0)},_clear:function(e,i){this.reverting=!1;
// We delay all events that have to be triggered to after the point where the placeholder has been removed and
// everything else normalized again
var s=[];if(
// We first have to update the dom position of the actual currentItem
// Note: don't do it if the current item is already removed (by a user), or it gets reappended (see #4088)
!this._noFinalSort&&this.currentItem.parent().length&&this.placeholder.before(this.currentItem),this._noFinalSort=null,this.helper[0]==this.currentItem[0]){for(var r in this._storedCSS)"auto"!=this._storedCSS[r]&&"static"!=this._storedCSS[r]||(this._storedCSS[r]="");this.currentItem.css(this._storedCSS).removeClass("ui-sortable-helper")}else this.currentItem.show();//Trigger update callback if the DOM position has changed
if(this.fromOutside&&!i&&s.push(function(t){this._trigger("receive",t,this._uiHash(this.fromOutside))}),!this.fromOutside&&this.domPosition.prev==this.currentItem.prev().not(".ui-sortable-helper")[0]&&this.domPosition.parent==this.currentItem.parent()[0]||i||s.push(function(t){this._trigger("update",t,this._uiHash())}),!t.ui.contains(this.element[0],this.currentItem[0])){//Node was moved out of the current element
i||s.push(function(t){this._trigger("remove",t,this._uiHash())});for(var r=this.containers.length-1;r>=0;r--)t.ui.contains(this.containers[r].element[0],this.currentItem[0])&&!i&&(s.push(function(t){return function(e){t._trigger("receive",e,this._uiHash(this))}}.call(this,this.containers[r])),s.push(function(t){return function(e){t._trigger("update",e,this._uiHash(this))}}.call(this,this.containers[r])))}
//Post events to containers
for(var r=this.containers.length-1;r>=0;r--)i||s.push(function(t){return function(e){t._trigger("deactivate",e,this._uiHash(this))}}.call(this,this.containers[r])),this.containers[r].containerCache.over&&(s.push(function(t){return function(e){t._trigger("out",e,this._uiHash(this))}}.call(this,this.containers[r])),this.containers[r].containerCache.over=0);if(
//Do what was originally in plugins
this._storedCursor&&t("body").css("cursor",this._storedCursor),//Reset cursor
this._storedOpacity&&this.helper.css("opacity",this._storedOpacity),//Reset opacity
this._storedZIndex&&this.helper.css("zIndex","auto"==this._storedZIndex?"":this._storedZIndex),//Reset z-index
this.dragging=!1,this.cancelHelperRemoval){if(!i){this._trigger("beforeStop",e,this._uiHash());for(var r=0;r<s.length;r++)s[r].call(this,e);//Trigger all delayed events
this._trigger("stop",e,this._uiHash())}return!1}if(i||this._trigger("beforeStop",e,this._uiHash()),
//$(this.placeholder[0]).remove(); would have been the jQuery way - unfortunately, it unbinds ALL events from the original node!
this.placeholder[0].parentNode.removeChild(this.placeholder[0]),this.helper[0]!=this.currentItem[0]&&this.helper.remove(),this.helper=null,!i){for(var r=0;r<s.length;r++)s[r].call(this,e);//Trigger all delayed events
this._trigger("stop",e,this._uiHash())}return this.fromOutside=!1,!0},_trigger:function(){t.Widget.prototype._trigger.apply(this,arguments)===!1&&this.cancel()},_uiHash:function(e){var i=e||this;return{helper:i.helper,placeholder:i.placeholder||t([]),position:i.position,originalPosition:i.originalPosition,offset:i.positionAbs,item:i.currentItem,sender:e?e.element:null}}}),t.extend(t.ui.sortable,{version:"@VERSION"})}(jQuery);