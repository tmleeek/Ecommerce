/*!
 * jQuery UI Mouse @VERSION
 *
 * Copyright 2012, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Mouse
 *
 * Depends:
 *	jquery.ui.widget.js
 */
!function(e,t){var s=!1;e(document).mouseup(function(e){s=!1}),e.widget("ui.mouse",{options:{cancel:":input,option",distance:1,delay:0},_mouseInit:function(){var t=this;this.element.bind("mousedown."+this.widgetName,function(e){return t._mouseDown(e)}).bind("click."+this.widgetName,function(s){return!0===e.data(s.target,t.widgetName+".preventClickEvent")?(e.removeData(s.target,t.widgetName+".preventClickEvent"),s.stopImmediatePropagation(),!1):void 0}),this.started=!1},
// TODO: make sure destroying one instance of mouse doesn't mess with
// other instances of mouse
_mouseDestroy:function(){this.element.unbind("."+this.widgetName),e(document).unbind("mousemove."+this.widgetName,this._mouseMoveDelegate).unbind("mouseup."+this.widgetName,this._mouseUpDelegate)},_mouseDown:function(t){
// don't let more than one widget handle mouseStart
if(!s){
// we may have missed mouseup (out of window)
this._mouseStarted&&this._mouseUp(t),this._mouseDownEvent=t;var o=this,i=1==t.which,
// event.target.nodeName works around a bug in IE 8 with
// disabled inputs (#7620)
n="string"==typeof this.options.cancel&&t.target.nodeName?e(t.target).closest(this.options.cancel).length:!1;
// Click event may never have fired (Gecko & Opera)
// these delegates are required to keep context
return i&&!n&&this._mouseCapture(t)?(this.mouseDelayMet=!this.options.delay,this.mouseDelayMet||(this._mouseDelayTimer=setTimeout(function(){o.mouseDelayMet=!0},this.options.delay)),this._mouseDistanceMet(t)&&this._mouseDelayMet(t)&&(this._mouseStarted=this._mouseStart(t)!==!1,!this._mouseStarted)?(t.preventDefault(),!0):(!0===e.data(t.target,this.widgetName+".preventClickEvent")&&e.removeData(t.target,this.widgetName+".preventClickEvent"),this._mouseMoveDelegate=function(e){return o._mouseMove(e)},this._mouseUpDelegate=function(e){return o._mouseUp(e)},e(document).bind("mousemove."+this.widgetName,this._mouseMoveDelegate).bind("mouseup."+this.widgetName,this._mouseUpDelegate),t.preventDefault(),s=!0,!0)):!0}},_mouseMove:function(t){
// IE mouseup check - mouseup happened when mouse was out of window
// IE mouseup check - mouseup happened when mouse was out of window
return!e.browser.msie||document.documentMode>=9||t.button?this._mouseStarted?(this._mouseDrag(t),t.preventDefault()):(this._mouseDistanceMet(t)&&this._mouseDelayMet(t)&&(this._mouseStarted=this._mouseStart(this._mouseDownEvent,t)!==!1,this._mouseStarted?this._mouseDrag(t):this._mouseUp(t)),!this._mouseStarted):this._mouseUp(t)},_mouseUp:function(t){return e(document).unbind("mousemove."+this.widgetName,this._mouseMoveDelegate).unbind("mouseup."+this.widgetName,this._mouseUpDelegate),this._mouseStarted&&(this._mouseStarted=!1,t.target==this._mouseDownEvent.target&&e.data(t.target,this.widgetName+".preventClickEvent",!0),this._mouseStop(t)),!1},_mouseDistanceMet:function(e){return Math.max(Math.abs(this._mouseDownEvent.pageX-e.pageX),Math.abs(this._mouseDownEvent.pageY-e.pageY))>=this.options.distance},_mouseDelayMet:function(e){return this.mouseDelayMet},
// These are placeholder methods, to be overriden by extending plugin
_mouseStart:function(e){},_mouseDrag:function(e){},_mouseStop:function(e){},_mouseCapture:function(e){return!0}})}(jQuery);