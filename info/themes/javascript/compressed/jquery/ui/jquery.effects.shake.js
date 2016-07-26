/*!
 * jQuery UI Effects Shake @VERSION
 *
 * Copyright 2012, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Shake
 *
 * Depends:
 *	jquery.effects.core.js
 */
!function(e,t){e.effects.shake=function(t){return this.queue(function(){
// Create element
var o=e(this),n=["position","top","bottom","left","right"],i=(e.effects.setMode(o,t.options.mode||"effect"),t.options.direction||"left"),s=t.options.distance||20,a=t.options.times||3,f=t.duration||t.options.duration||140;// Default speed per shake
// Adjust
e.effects.save(o,n),o.show(),// Save & Show
e.effects.createWrapper(o);// Create Wrapper
var p="up"==i||"down"==i?"top":"left",c="up"==i||"left"==i?"pos":"neg",r={},u={},d={};r[p]=("pos"==c?"-=":"+=")+s,u[p]=("pos"==c?"+=":"-=")+2*s,d[p]=("pos"==c?"-=":"+=")+2*s,
// Animate
o.animate(r,f,t.options.easing);for(var l=1;a>l;l++)// Shakes
o.animate(u,f,t.options.easing).animate(d,f,t.options.easing);o.animate(u,f,t.options.easing).animate(r,f/2,t.options.easing,function(){// Last shake
e.effects.restore(o,n),e.effects.removeWrapper(o),// Restore
t.callback&&t.callback.apply(this,arguments)}),o.queue("fx",function(){o.dequeue()}),o.dequeue()})}}(jQuery);