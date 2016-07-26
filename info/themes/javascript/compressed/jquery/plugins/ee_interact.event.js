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
// ------------------------------------------------------------------------
/*!
 * ExpressionEngine Custom Interact jQuery Event
 *
 * @package		ExpressionEngine
 * @subpackage	Control Panel
 * @category	Control Panel
 * @author		EllisLab Dev Team
 * @link		http://ellislab.com
 */
/* Usage Notes:
 *
 * This file adds a custom event to jquery. The interact event
 * can be thought of as a more responsive change event. On text
 * inputs and textareas, the original change event does not fire
 * until after you blur the element.
 *
 * In the past our solution has been to bind keyup on text inputs
 * and textareas, but this gets clunky quickly when trying to
 * observe events on a complex form. This custom event fills that
 * void. It can be bound on a form, a single input, or a jquery
 * object of input events. It also fires on cut and paste events,
 * which could not be supported with the keyup method.
 *
 * Usage:
 *
 * $(form).bind('interact', callback);
 * $(input).bind('interact', callback);
 *
 */
!function(t){/* Helper method to iterate over all
 * elements inside a form
 */
function e(e,n){return t(e).map(function(){return this.elements?t.makeArray(this.elements):this}).filter(function(){return this.name}).map(n)}/* Helper method to figure out if something
 * is a text input. Does not do all html5,
 * but most of what people use.
 */
function n(t){if(jQuery.nodeName(t,"textarea"))return!0;if(!jQuery.nodeName(t,"input"))return!1;var e=t.type;return e?"text"==e||"password"==e||"search"==e||"url"==e||"email"==e||"tel"==e:!0}/* Helper method to propagate
 * the event with a check if data changed
 * and support for delayed firing (copy, paste, etc don't update the value right away)
 */
function i(e,n,i){i=i||0,setTimeout(function(){var i=t.data(e,"_interact_cache"),a=e.value;i!==a&&(t.event.trigger("interact",n,e),t.data(e,"_interact_cache",a))},i)}t.event.special.interact={/* jQuery Event Bind
	 *
 	 * Bind our special interact event.
	 */
setup:function(a,c){
// for forms we need to bind on the kids instead
// for forms we need to bind on the kids instead
// text inputs don't fire a sensible change event,
// for live filtering we need to know when something
// is changed as soon as the user releases the key.
// store old value so we don't fire uselessly
// this is consistent with other element change events
// keyup
// cut, paste, and IE's oninput
// and a change event for all other elements as well
// as browsers that don't recognize cut and paste events
return t.nodeName(this,"form")?void e(this,function(){t.event.special.interact.setup.call(this,a,c)}):n(this)?(t.data(this,"_interact_cache",this.value),t.event.add(this,"keyup.specialInteract change.specialInteract",function(){i(this,a)}),void t.event.add(this,"input.specialInteract cut.specialInteract paste.specialInteract",function(){i(this,a,25)})):void t.event.add(this,"change.specialInteract",function(){t.event.trigger("interact",a,this)})},/* jQuery Event Unbind
	 *
 	 * Remove all helper events we added
	 */
teardown:function(e){t(this).unbind(".specialInteract")}}}(jQuery);