/*!
 * jQuery UI Effects Bounce @VERSION
 *
 * Copyright 2012, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Bounce
 *
 * Depends:
 *	jquery.effects.core.js
 */
!function(e,t){e.effects.bounce=function(t){return this.queue(function(){
// Create element
var o=e(this),i=["position","top","bottom","left","right"],s=e.effects.setMode(o,t.options.mode||"effect"),a=t.options.direction||"up",n=t.options.distance||20,p=t.options.times||5,c=t.duration||250;// Default speed per bounce
/show|hide/.test(s)&&i.push("opacity"),// Avoid touching opacity to prevent clearType and PNG issues in IE
// Adjust
e.effects.save(o,i),o.show(),// Save & Show
e.effects.createWrapper(o);// Create Wrapper
var f="up"==a||"down"==a?"top":"left",r="up"==a||"left"==a?"pos":"neg",n=t.options.distance||("top"==f?o.outerHeight({margin:!0})/3:o.outerWidth({margin:!0})/3);
// Animate
if("show"==s&&o.css("opacity",0).css(f,"pos"==r?-n:n),// Shift
"hide"==s&&(n/=2*p),"hide"!=s&&p--,"show"==s){// Show Bounce
var u={opacity:1};u[f]=("pos"==r?"+=":"-=")+n,o.animate(u,c/2,t.options.easing),n/=2,p--}for(var h=0;p>h;h++){// Bounces
var d={},l={};d[f]=("pos"==r?"-=":"+=")+n,l[f]=("pos"==r?"+=":"-=")+n,o.animate(d,c/2,t.options.easing).animate(l,c/2,t.options.easing),n="hide"==s?2*n:n/2}if("hide"==s){// Last Bounce
var u={opacity:0};u[f]=("pos"==r?"-=":"+=")+n,o.animate(u,c/2,t.options.easing,function(){o.hide(),// Hide
e.effects.restore(o,i),e.effects.removeWrapper(o),// Restore
t.callback&&t.callback.apply(this,arguments)})}else{var d={},l={};d[f]=("pos"==r?"-=":"+=")+n,l[f]=("pos"==r?"+=":"-=")+n,o.animate(d,c/2,t.options.easing).animate(l,c/2,t.options.easing,function(){e.effects.restore(o,i),e.effects.removeWrapper(o),// Restore
t.callback&&t.callback.apply(this,arguments)})}o.queue("fx",function(){o.dequeue()}),o.dequeue()})}}(jQuery);