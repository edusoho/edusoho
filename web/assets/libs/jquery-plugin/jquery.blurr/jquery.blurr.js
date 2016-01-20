/*
 *  jQuery Blurr
 *  --
 *  Written by Tom Hallam @ Freshnode
 *  --
 *  Released under the MIT Licence
 * 
    Copyright (c) 2014 Tom Hallam

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
 */
define("jquery-plugin/jquery.blurr/jquery.blurr", [ "$-debug" ], function(require) {
    var jQuery = require("$-debug");
;(function($, window, document, undefined) {


    // Create the defaults once
    var pluginName = "blurr",
            defaults = {
                offsetX: 0,
                offsetY: 0, 
                sharpness: 40,
                height: 300,
                divBG: '#000',
                callback: function() {},
                unsupportedCallback: function() {}
            };

    // The actual plugin constructor
    function Blurr(element, options, elementIndex) {
        
        this.$el = $(element);
        // jQuery has an extend method which merges the contents of two or
        // more objects, storing the result in the first object. The first object
        // is generally empty as we don't want to alter the default options for
        // future instances of the plugin
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this.cssPrefix = null;
        
        // Store the template
        this.tpl = '<svg><defs><filter id="blrIMG{{i}}"><feGaussianBlur id="filter_1" stdDeviation="{{sharpness}}" data-filterid="1"></feGaussianBlur><feComponentTransfer><feFuncR type="linear" slope="0.8"></feFuncR><feFuncG type="linear" slope="0.8"></feFuncG><feFuncB type="linear" slope="0.8"></feFuncB></feComponentTransfer></filter></defs><image x="{{offsetX}}" y="{{offsetY}}" width="100%" height="100%" xlink:href="{{href}}" filter="url(#blrIMG{{i}})" preserveAspectRatio="xMidYMid slice"></image></svg>';
        
        // Element counter
        this.elementCount = elementIndex;
         
        // Does this browser support SVG filtering?
        this.supportsFilter = (typeof SVGFEColorMatrixElement !== 'undefined' && SVGFEColorMatrixElement.SVG_FECOLORMATRIX_TYPE_SATURATE === 2);
        this.supportsFilter = (window.location.hash.length > 0);
        
        var _browserPrefixes = ' -webkit- -moz- -o- -ms- '.split(' ');
        var _cssPrefixString = {};
        
        /*jshint -W030 */
        _cssPrefix = function(property) {
          if (_cssPrefixString[property] || _cssPrefixString[property] === '') return _cssPrefixString[property] + property;
          var e = document.createElement('div');
          var prefixes = ['', 'Moz', 'Webkit', 'O', 'ms', 'Khtml']; // Various supports...
          for (var i in prefixes) {
            if (typeof e.style[prefixes[i] + property] !== 'undefined') {
              _cssPrefixString[property] = prefixes[i];
              return prefixes[i] + property;
            } 
          }
          return property.toLowerCase();
        };
        
        // https://github.com/Modernizr/Modernizr/blob/master/feature-detects/css-filters.js
        /*jshint -W030 */
        this.support = {
          cssfilters: function() {
            var el = document.createElement('div');
            el.style.cssText = _browserPrefixes.join('filter' + ':blur(2px); ');
            return !!el.style.length && ((document.documentMode === undefined || document.documentMode > 9));
          }(),

          // https://github.com/Modernizr/Modernizr/blob/master/feature-detects/svg-filters.js
          svgfilters: function() {
            var result = false;
            try {
              result = typeof SVGFEColorMatrixElement !== 'undefined' && SVGFEColorMatrixElement.SVG_FECOLORMATRIX_TYPE_SATURATE == 2;
            } catch (e) {}
            return result;
          }()
        };
                
        // Immediately hand off to the unsupported callback if there's no support
        if(!this.support.cssfilters && !this.support.svgfilters) {
            if(typeof this.settings.unsupportedCallback === 'function') {
                return this.settings.unsupportedCallback.call(this);
            }
        }
                
        // What CSS Vendor Prefix?
        /*jshint -W030 */
        this.cssPrefix = _cssPrefix('filter');
        
        // Apply the fix for "scrolling lines bug"
        var bodyEl = document.getElementsByTagName('body')[0];
        window.onscroll = function(e) {
            bodyEl.style.visibility = 'hidden';
            bodyEl.offsetHeight;
            bodyEl.style.visibility = 'visible';
        };
        
        // Initialise the plugin
        this.init();
        
    }

    // Avoid Plugin.prototype conflicts
    $.extend(Blurr.prototype, {
        init: function() {
                        
            // Import options from the data-attributes of the element
            var href, offsetX, offsetY, sharpness, callback, height;
            
            // Assign from the options, if available - [data-] attributes override below
            href      = this.settings.href;
            offsetX   = this.settings.offsetX;
            offsetY   = this.settings.offsetY;
            sharpness = this.settings.sharpness;
            callback  = this.settings.callback;
            height    = this.settings.height;
            
            if(this.$el.data('image')) {
                href = this.$el.data('image');
            }
            
            if(this.$el.data('href')) {
                href = this.$el.data('href');
            }
            
            if(this.$el.data('offsetx')) {
                offsetX = this.$el.data('offsetx');
            }
            
            if(this.$el.data('offsety')) {
                offsetY = this.$el.data('offsety');
            }
            
            if(this.$el.data('sharpness')) {
                sharpness = this.$el.data('sharpness');
            }
            
            // Normalise the options
            if(typeof offsetX === 'undefined') {
                offsetX = 0;
            }

            if(typeof offsetY === 'undefined') {
                offsetY = 0;
            }
            
            if(typeof sharpness === 'undefined' || sharpness.length === 0 || sharpness < 0 || sharpness > 100) {
                sharpness = (sharpness > 100 ? 100 : 40);
            }
            else {
                sharpness = 100 - sharpness;
            }
            
            if(typeof height === 'undefined' || sharpness.length === 0 || sharpness < 0) {
                height = 300;
            }
                        
            // Add the blurstretch CSS class
            this.$el.addClass('has-blurr');
                        
            // Parse, render and callback
            if(this.support.svgfilters && !this.support.cssfilters) {
                return this.renderSVG(href, offsetX, offsetY, sharpness, height, callback);
            }
            else {
                return this.renderCSSFilter(href, offsetX, offsetY, sharpness, height, callback);
            }
            

        },
        renderSVG: function(href, offsetX, offsetY, sharpness, height, callback) {
            
            // Parse the template and replace values
            var _tpl = this.tpl;
            _tpl = _tpl.replace('{{href}}', href);
            _tpl = _tpl.replace('{{offsetX}}', offsetX);
            _tpl = _tpl.replace('{{offsetY}}', offsetY);
            _tpl = _tpl.replace('{{sharpness}}', sharpness);
            _tpl = _tpl.replace(/{{i}}/g, this.elementCount);

            // Prepend the template to the wrapper
            $(_tpl).appendTo(this.$el);
            
            // Format the target div
            this.$el.css({
                'height': height,
                'overflow': 'hidden',
                'background': '#000'
            });
            
            // Format the SVG with some tweaks to make it look grand.
            this.$el.find('svg').css({
                'min-width': '100%',
                'min-height': '100%',
                '-webkit-transform': 'translate3d(-50px, 0px, 75px) scale(1.25)',
                'transform': 'translate3d(-50px, 0px, 75px) scale(1.25)',
                'position': 'relative',
                'right': 0,
                'left': 0
            });
            

            // Format the inner div with some styles to make sure it shows
            this.$el.find('div:first').css({
                'position': 'absolute',
                'left': 0,
                'right': 0,
                'z-index': 1
            });
            
            // Call the callback
            if(typeof callback === 'function') {
                callback.call(this, href, offsetX, offsetY, sharpness);
            }
            
        },
        renderCSSFilter: function(href, offsetX, offsetY, sharpness, height, callback) {
            
            // Format the target div
            this.$el.css({
                'height': height,
                'overflow': 'hidden',
                'position': 'relative',
                // 'background': '#000'
            });
            
            // Create a background position string
            var bgPosition;
            if(offsetX && offsetY) {
                bgPosition = offsetX + 'px ' + offsetY + 'px';
            }
            else if(offsetX && !offsetY) {
                bgPosition = offsetX + 'px center';
            }
            else if(!offsetX && offsetY) {
                bgPosition = 'center ' + offsetY + 'px';
            }
            else {
                bgPosition = 'center center';
            }
            
            var bgDiv = $('<div class="blurr-bg"></div>').css({
                'background': 'url(' + href + ')',
                'left': 0,
                'right': 0,
                'top': -50,
                'bottom': -50,
                width: this.$el.width(),
                'background-size': '150% auto',
                'background-position': bgPosition,
                '-webkit-filter': 'blur(' + sharpness + 'px)',
                '-webkit-transform': 'translateZ(0)',
                // 'z-index': 50,
                'position': 'absolute'
            }).prependTo(this.$el);
            
            //
            var prefix = this.cssPrefix;
            var p2 = prefix;
            
            // Apply the fallback style for old browsers
            if(this.support.cssfilters) {
                bgDiv[0].style[prefix] =  'blur(' + sharpness + 'px)';
            }
            else {
                
                bgDiv[0].style[prefix] =  'progid:DXImageTransform.Microsoft.Blur(PixelRadius="100")';
                bgDiv.css({
                    'top': -250,
                    'left': -200,
                    'opacity': 0.8
                });
                this.$el.css({
                    'background': '#fff'
                });
                
            }
            
            // Format the inner div with some styles to make sure it shows
            this.$el.find('> div').not('.blurr-bg').css({
                'position': 'absolute',
                'left': 0,
                'right': 0,
                'z-index': 1
            });
            
        }
    });

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[ pluginName ] = function(options) {
        var self = this;
        this.each(function(i) {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Blurr(this, options, i));
            }
        });

        // chain jQuery functions
        return this;
    };

})(jQuery, window, document);

});