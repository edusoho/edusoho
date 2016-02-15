/**
 * LavaLamp - A menu plugin for jQuery with cool hover effects.
 * @requires jQuery v1.1.3.1 or above
 *
 * http://gmarwaha.com/blog/?p=7
 *
 * Copyright (c) 2007 Ganeshji Marwaha (gmarwaha.com)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 * Version: 0.2.0
 * Requires Jquery 1.2.1 from version 0.2.0 onwards. 
 * For jquery 1.1.x, use version 0.1.0 of lavalamp
 */
define(function(require, exports, module){
    require('jquery.easing');
    (function($){$.fn.lavaLamp=function(o){o=$.extend({fx:"easein",speed:200,click:function(){}},o||{});return this.each(function(){var b=$(this),noop=function(){},$back=$('<li class="highlight"></li>').appendTo(b),$li=$("li",this),curr=$("li.active",this)[0]||$($li[0]).addClass("active")[0];$li.not(".highlight").hover(function(){move(this)},noop);$(this).hover(noop,function(){move(curr)});$li.click(function(e){setCurr(this);return o.click.apply(this,[e,this])});setCurr(curr);function setCurr(a){$back.css({"left":a.offsetLeft+"px","width":a.offsetWidth+"px"});curr=a};function move(a){$back.each(function(){$(this).dequeue()}).animate({width:a.offsetWidth,left:a.offsetLeft},o.speed,o.fx)}})}})(jQuery);
});