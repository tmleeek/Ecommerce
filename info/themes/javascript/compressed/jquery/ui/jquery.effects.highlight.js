/*!
 * jQuery UI Effects Highlight @VERSION
 *
 * Copyright 2012, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Highlight
 *
 * Depends:
 *	jquery.effects.core.js
 */
!function(o,e){o.effects.highlight=function(e){return this.queue(function(){var t=o(this),i=["backgroundImage","backgroundColor","opacity"],c=o.effects.setMode(t,e.options.mode||"show"),n={backgroundColor:t.css("backgroundColor")};"hide"==c&&(n.opacity=0),o.effects.save(t,i),t.show().css({backgroundImage:"none",backgroundColor:e.options.color||"#ffff99"}).animate(n,{queue:!1,duration:e.duration,easing:e.options.easing,complete:function(){"hide"==c&&t.hide(),o.effects.restore(t,i),"show"==c&&!o.support.opacity&&this.style.removeAttribute("filter"),e.callback&&e.callback.apply(this,arguments),t.dequeue()}})})}}(jQuery);