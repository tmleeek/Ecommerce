/*!
 * jQuery UI Position @VERSION
 *
 * Copyright 2012, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Position
 */
!function(t,e){t.ui=t.ui||{};var o=/left|center|right/,i=/top|center|bottom/,n="center",l={},f=t.fn.position,s=t.fn.offset;t.fn.position=function(e){if(!e||!e.of)return f.apply(this,arguments);
// make a copy, we don't want to modify arguments
e=t.extend({},e);var s,r,a,p=t(e.of),h=p[0],c=(e.collision||"flip").split(" "),u=e.offset?e.offset.split(" "):[0,0];
// force left top to allow flipping
// force my and at to have valid horizontal and veritcal positions
// if a value is missing or invalid, it will be converted to center 
// normalize collision option
// normalize offset option
return 9===h.nodeType?(s=p.width(),r=p.height(),a={top:0,left:0}):h.setTimeout?(s=p.width(),r=p.height(),a={top:p.scrollTop(),left:p.scrollLeft()}):h.preventDefault?(e.at="left top",s=r=0,a={top:e.of.pageY,left:e.of.pageX}):(s=p.outerWidth(),r=p.outerHeight(),a=p.offset()),t.each(["my","at"],function(){var t=(e[this]||"").split(" ");1===t.length&&(t=o.test(t[0])?t.concat([n]):i.test(t[0])?[n].concat(t):[n,n]),t[0]=o.test(t[0])?t[0]:n,t[1]=i.test(t[1])?t[1]:n,e[this]=t}),1===c.length&&(c[1]=c[0]),u[0]=parseInt(u[0],10)||0,1===u.length&&(u[1]=u[0]),u[1]=parseInt(u[1],10)||0,"right"===e.at[0]?a.left+=s:e.at[0]===n&&(a.left+=s/2),"bottom"===e.at[1]?a.top+=r:e.at[1]===n&&(a.top+=r/2),a.left+=u[0],a.top+=u[1],this.each(function(){var o,i=t(this),f=i.outerWidth(),p=i.outerHeight(),h=parseInt(t.curCSS(this,"marginLeft",!0))||0,g=parseInt(t.curCSS(this,"marginTop",!0))||0,d=f+h+(parseInt(t.curCSS(this,"marginRight",!0))||0),m=p+g+(parseInt(t.curCSS(this,"marginBottom",!0))||0),y=t.extend({},a);"right"===e.my[0]?y.left-=f:e.my[0]===n&&(y.left-=f/2),"bottom"===e.my[1]?y.top-=p:e.my[1]===n&&(y.top-=p/2),
// prevent fractions if jQuery version doesn't support them (see #5280)
l.fractions||(y.left=Math.round(y.left),y.top=Math.round(y.top)),o={left:y.left-h,top:y.top-g},t.each(["left","top"],function(i,n){t.ui.position[c[i]]&&t.ui.position[c[i]][n](y,{targetWidth:s,targetHeight:r,elemWidth:f,elemHeight:p,collisionPosition:o,collisionWidth:d,collisionHeight:m,offset:u,my:e.my,at:e.at})}),t.fn.bgiframe&&i.bgiframe(),i.offset(t.extend(y,{using:e.using}))})},t.ui.position={fit:{left:function(e,o){var i=t(window),n=o.collisionPosition.left+o.collisionWidth-i.width()-i.scrollLeft();e.left=n>0?e.left-n:Math.max(e.left-o.collisionPosition.left,e.left)},top:function(e,o){var i=t(window),n=o.collisionPosition.top+o.collisionHeight-i.height()-i.scrollTop();e.top=n>0?e.top-n:Math.max(e.top-o.collisionPosition.top,e.top)}},flip:{left:function(e,o){if(o.at[0]!==n){var i=t(window),l=o.collisionPosition.left+o.collisionWidth-i.width()-i.scrollLeft(),f="left"===o.my[0]?-o.elemWidth:"right"===o.my[0]?o.elemWidth:0,s="left"===o.at[0]?o.targetWidth:-o.targetWidth,r=-2*o.offset[0];e.left+=o.collisionPosition.left<0?f+s+r:l>0?f+s+r:0}},top:function(e,o){if(o.at[1]!==n){var i=t(window),l=o.collisionPosition.top+o.collisionHeight-i.height()-i.scrollTop(),f="top"===o.my[1]?-o.elemHeight:"bottom"===o.my[1]?o.elemHeight:0,s="top"===o.at[1]?o.targetHeight:-o.targetHeight,r=-2*o.offset[1];e.top+=o.collisionPosition.top<0?f+s+r:l>0?f+s+r:0}}}},
// offset setter from jQuery 1.4
t.offset.setOffset||(t.offset.setOffset=function(e,o){
// set position first, in-case top/left are set even on static elem
/static/.test(t.curCSS(e,"position"))&&(e.style.position="relative");var i=t(e),n=i.offset(),l=parseInt(t.curCSS(e,"top",!0),10)||0,f=parseInt(t.curCSS(e,"left",!0),10)||0,s={top:o.top-n.top+l,left:o.left-n.left+f};"using"in o?o.using.call(e,s):i.css(s)},t.fn.offset=function(e){var o=this[0];return o&&o.ownerDocument?e?this.each(function(){t.offset.setOffset(this,e)}):s.call(this):null}),
// fraction support test (older versions of jQuery don't support fractions)
function(){var e,o,i,n,f,s=document.getElementsByTagName("body")[0],r=document.createElement("div");e=document.createElement(s?"div":"body"),i={visibility:"hidden",width:0,height:0,border:0,margin:0,background:"none"},s&&t.extend(i,{position:"absolute",left:"-1000px",top:"-1000px"});for(var a in i)e.style[a]=i[a];e.appendChild(r),o=s||document.documentElement,o.insertBefore(e,o.firstChild),r.style.cssText="position: absolute; left: 10.7432222px; top: 10.432325px; height: 30px; width: 201px;",n=t(r).offset(function(t,e){return e}).offset(),e.innerHTML="",o.removeChild(e),f=n.top+n.left+(s?2e3:0),l.fractions=f>21&&22>f}()}(jQuery);