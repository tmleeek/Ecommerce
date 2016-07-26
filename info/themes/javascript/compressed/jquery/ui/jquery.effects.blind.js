/*!
 * jQuery UI Effects Blind @VERSION
 *
 * Copyright 2012, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Blind
 *
 * Depends:
 *	jquery.effects.core.js
 */
!function(e,t){e.effects.blind=function(t){return this.queue(function(){
// Create element
var i=e(this),o=["position","top","bottom","left","right"],s=e.effects.setMode(i,t.options.mode||"hide"),c=t.options.direction||"vertical";// Default direction
// Adjust
e.effects.save(i,o),i.show();// Save & Show
var r=e.effects.createWrapper(i).css({overflow:"hidden"}),a="vertical"==c?"height":"width",n="vertical"==c?r.height():r.width();"show"==s&&r.css(a,0);// Shift
// Animation
var f={};f[a]="show"==s?n:0,
// Animate
r.animate(f,t.duration,t.options.easing,function(){"hide"==s&&i.hide(),// Hide
e.effects.restore(i,o),e.effects.removeWrapper(i),// Restore
t.callback&&t.callback.apply(i[0],arguments),// Callback
i.dequeue()})})}}(jQuery);