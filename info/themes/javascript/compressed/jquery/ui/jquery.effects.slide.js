/*!
 * jQuery UI Effects Slide @VERSION
 *
 * Copyright 2012, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Slide
 *
 * Depends:
 *	jquery.effects.core.js
 */
!function(e,t){e.effects.slide=function(t){return this.queue(function(){
// Create element
var o=e(this),s=["position","top","bottom","left","right"],i=e.effects.setMode(o,t.options.mode||"show"),n=t.options.direction||"left";// Default Direction
// Adjust
e.effects.save(o,s),o.show(),// Save & Show
e.effects.createWrapper(o).css({overflow:"hidden"});// Create Wrapper
var r="up"==n||"down"==n?"top":"left",a="up"==n||"left"==n?"pos":"neg",f=t.options.distance||("top"==r?o.outerHeight({margin:!0}):o.outerWidth({margin:!0}));"show"==i&&o.css(r,"pos"==a?isNaN(f)?"-"+f:-f:f);// Shift
// Animation
var p={};p[r]=("show"==i?"pos"==a?"+=":"-=":"pos"==a?"-=":"+=")+f,
// Animate
o.animate(p,{queue:!1,duration:t.duration,easing:t.options.easing,complete:function(){"hide"==i&&o.hide(),// Hide
e.effects.restore(o,s),e.effects.removeWrapper(o),// Restore
t.callback&&t.callback.apply(this,arguments),// Callback
o.dequeue()}})})}}(jQuery);