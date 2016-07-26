/*!
 * jQuery UI Effects Fade @VERSION
 *
 * Copyright 2012, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Fade
 *
 * Depends:
 *	jquery.effects.core.js
 */
!function(e,t){e.effects.fade=function(t){return this.queue(function(){var i=e(this),n=e.effects.setMode(i,t.options.mode||"hide");i.animate({opacity:n},{queue:!1,duration:t.duration,easing:t.options.easing,complete:function(){t.callback&&t.callback.apply(this,arguments),i.dequeue()}})})}}(jQuery);