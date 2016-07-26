/*!
 * jQuery UI Effects Clip @VERSION
 *
 * Copyright 2012, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Clip
 *
 * Depends:
 *	jquery.effects.core.js
 */
!function(e,t){e.effects.clip=function(t){return this.queue(function(){
// Create element
var i=e(this),o=["position","top","bottom","left","right","height","width"],s=e.effects.setMode(i,t.options.mode||"hide"),c=t.options.direction||"vertical";// Default direction
// Adjust
e.effects.save(i,o),i.show();// Save & Show
var a=e.effects.createWrapper(i).css({overflow:"hidden"}),n="IMG"==i[0].tagName?a:i,r={size:"vertical"==c?"height":"width",position:"vertical"==c?"top":"left"},h="vertical"==c?n.height():n.width();"show"==s&&(n.css(r.size,0),n.css(r.position,h/2));// Shift
// Animation
var f={};f[r.size]="show"==s?h:0,f[r.position]="show"==s?0:h/2,
// Animate
n.animate(f,{queue:!1,duration:t.duration,easing:t.options.easing,complete:function(){"hide"==s&&i.hide(),// Hide
e.effects.restore(i,o),e.effects.removeWrapper(i),// Restore
t.callback&&t.callback.apply(i[0],arguments),// Callback
i.dequeue()}})})}}(jQuery);