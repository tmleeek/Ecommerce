/*!
 * jQuery UI Effects Drop @VERSION
 *
 * Copyright 2012, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Drop
 *
 * Depends:
 *	jquery.effects.core.js
 */
!function(e,t){e.effects.drop=function(t){return this.queue(function(){
// Create element
var o=e(this),i=["position","top","bottom","left","right","opacity"],s=e.effects.setMode(o,t.options.mode||"hide"),n=t.options.direction||"left";// Default Direction
// Adjust
e.effects.save(o,i),o.show(),// Save & Show
e.effects.createWrapper(o);// Create Wrapper
var p="up"==n||"down"==n?"top":"left",a="up"==n||"left"==n?"pos":"neg",c=t.options.distance||("top"==p?o.outerHeight({margin:!0})/2:o.outerWidth({margin:!0})/2);"show"==s&&o.css("opacity",0).css(p,"pos"==a?-c:c);// Shift
// Animation
var r={opacity:"show"==s?1:0};r[p]=("show"==s?"pos"==a?"+=":"-=":"pos"==a?"-=":"+=")+c,
// Animate
o.animate(r,{queue:!1,duration:t.duration,easing:t.options.easing,complete:function(){"hide"==s&&o.hide(),// Hide
e.effects.restore(o,i),e.effects.removeWrapper(o),// Restore
t.callback&&t.callback.apply(this,arguments),// Callback
o.dequeue()}})})}}(jQuery);