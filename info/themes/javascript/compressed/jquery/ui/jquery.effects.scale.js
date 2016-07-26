/*!
 * jQuery UI Effects Scale @VERSION
 *
 * Copyright 2012, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Scale
 *
 * Depends:
 *	jquery.effects.core.js
 */
!function(t,o){t.effects.puff=function(o){return this.queue(function(){var e=t(this),i=t.effects.setMode(e,o.options.mode||"hide"),f=parseInt(o.options.percent,10)||150,r=f/100,h={height:e.height(),width:e.width()};t.extend(o.options,{fade:!0,mode:i,percent:"hide"==i?f:100,from:"hide"==i?h:{height:h.height*r,width:h.width*r}}),e.effect("scale",o.options,o.duration,o.callback),e.dequeue()})},t.effects.scale=function(o){return this.queue(function(){
// Create element
var e=t(this),i=t.extend(!0,{},o.options),f=t.effects.setMode(e,o.options.mode||"effect"),r=parseInt(o.options.percent,10)||(0==parseInt(o.options.percent,10)?0:"hide"==f?0:100),h=o.options.direction||"both",n=o.options.origin;"effect"!=f&&(i.origin=n||["middle","center"],i.restore=!0);var s={height:e.height(),width:e.width()};// Save original
e.from=o.options.from||("show"==f?{height:0,width:0}:s);// Default from state
// Adjust
var c={// Set scaling factor
y:"horizontal"!=h?r/100:1,x:"vertical"!=h?r/100:1};e.to={height:s.height*c.y,width:s.width*c.x},// Set to state
o.options.fade&&(// Fade option to support puff
"show"==f&&(e.from.opacity=0,e.to.opacity=1),"hide"==f&&(e.from.opacity=1,e.to.opacity=0)),
// Animation
i.from=e.from,i.to=e.to,i.mode=f,
// Animate
e.effect("size",i,o.duration,o.callback),e.dequeue()})},t.effects.size=function(o){return this.queue(function(){
// Create element
var e=t(this),i=["position","top","bottom","left","right","width","height","overflow","opacity"],f=["position","top","bottom","left","right","overflow","opacity"],r=["width","height","overflow"],h=["fontSize"],n=["borderTopWidth","borderBottomWidth","paddingTop","paddingBottom"],s=["borderLeftWidth","borderRightWidth","paddingLeft","paddingRight"],c=t.effects.setMode(e,o.options.mode||"effect"),a=o.options.restore||!1,d=o.options.scale||"both",m=o.options.origin,p={height:e.height(),width:e.width()};// Default to state
// Adjust
if(// Save original
e.from=o.options.from||p,// Default from state
e.to=o.options.to||p,m){// Calculate baseline shifts
var g=t.effects.getBaseline(m,p);e.from.top=(p.height-e.from.height)*g.y,e.from.left=(p.width-e.from.width)*g.x,e.to.top=(p.height-e.to.height)*g.y,e.to.left=(p.width-e.to.width)*g.x}var w={// Set scaling factor
from:{y:e.from.height/p.height,x:e.from.width/p.width},to:{y:e.to.height/p.height,x:e.to.width/p.width}};"box"!=d&&"both"!=d||(// Scale the css box
w.from.y!=w.to.y&&(i=i.concat(n),e.from=t.effects.setTransition(e,n,w.from.y,e.from),e.to=t.effects.setTransition(e,n,w.to.y,e.to)),w.from.x!=w.to.x&&(i=i.concat(s),e.from=t.effects.setTransition(e,s,w.from.x,e.from),e.to=t.effects.setTransition(e,s,w.to.x,e.to))),"content"!=d&&"both"!=d||// Scale the content
w.from.y!=w.to.y&&(i=i.concat(h),e.from=t.effects.setTransition(e,h,w.from.y,e.from),e.to=t.effects.setTransition(e,h,w.to.y,e.to)),t.effects.save(e,a?i:f),e.show(),// Save & Show
t.effects.createWrapper(e),// Create Wrapper
e.css("overflow","hidden").css(e.from),// Shift
// Animate
"content"!=d&&"both"!=d||(n=n.concat(["marginTop","marginBottom"]).concat(h),s=s.concat(["marginLeft","marginRight"]),r=i.concat(n).concat(s),e.find("*[width]").each(function(){var e=t(this);a&&t.effects.save(e,r);var i={height:e.height(),width:e.width()};e.from={height:i.height*w.from.y,width:i.width*w.from.x},e.to={height:i.height*w.to.y,width:i.width*w.to.x},w.from.y!=w.to.y&&(e.from=t.effects.setTransition(e,n,w.from.y,e.from),e.to=t.effects.setTransition(e,n,w.to.y,e.to)),w.from.x!=w.to.x&&(e.from=t.effects.setTransition(e,s,w.from.x,e.from),e.to=t.effects.setTransition(e,s,w.to.x,e.to)),e.css(e.from),e.animate(e.to,o.duration,o.options.easing,function(){a&&t.effects.restore(e,r)})})),
// Animate
e.animate(e.to,{queue:!1,duration:o.duration,easing:o.options.easing,complete:function(){0===e.to.opacity&&e.css("opacity",e.from.opacity),"hide"==c&&e.hide(),// Hide
t.effects.restore(e,a?i:f),t.effects.removeWrapper(e),// Restore
o.callback&&o.callback.apply(this,arguments),// Callback
e.dequeue()}})})}}(jQuery);