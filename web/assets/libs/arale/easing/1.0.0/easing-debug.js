define("arale/easing/1.0.0/easing-debug", ["$-debug"], function(require, exports, module) {

    // Based on Easing Equations (c) 2003 Robert Penner, all rights reserved.
    // This work is subject to the terms in
    // http://www.robertpenner.com/easing_terms_of_use.html
    // Preview: http://www.robertpenner.com/Easing/easing_demo.html
    //
    // Thanks to:
    //  - https://github.com/yui/yui3/blob/master/src/anim/js/anim-easing.js
    //  - https://github.com/gilmoreorless/jquery-easing-molecules


    var PI = Math.PI;
    var pow = Math.pow;
    var sin = Math.sin;
    var MAGIC_NUM = 1.70158; // Penner's magic number


    /**
     * 和 YUI 的 Easing 相比，这里的 Easing 进行了归一化处理，参数调整为：
     * @param {Number} t Time value used to compute current value 0 =< t <= 1
     * @param {Number} b Starting value  b = 0
     * @param {Number} c Delta between start and end values  c = 1
     * @param {Number} d Total length of animation d = 1
     */
    var Easing = {

        /**
         * Uniform speed between points.
         */
        easeNone: function(t) {
            return t;
        },

        /**
         * Begins slowly and accelerates towards end. (quadratic)
         */
        easeIn: function(t) {
            return t * t;
        },

        /**
         * Begins quickly and decelerates towards end.  (quadratic)
         */
        easeOut: function(t) {
            return (2 - t) * t;
        },

        /**
         * Begins slowly and decelerates towards end. (quadratic)
         */
        easeBoth: function(t) {
            return (t *= 2) < 1 ?
                    .5 * t * t :
                    .5 * (1 - (--t) * (t - 2));
        },

        /**
         * Begins slowly and accelerates towards end. (quartic)
         */
        easeInStrong: function(t) {
            return t * t * t * t;
        },
        /**
         * Begins quickly and decelerates towards end.  (quartic)
         */
        easeOutStrong: function(t) {
            return 1 - (--t) * t * t * t;
        },

        /**
         * Begins slowly and decelerates towards end. (quartic)
         */
        easeBothStrong: function(t) {
            return (t *= 2) < 1 ?
                    .5 * t * t * t * t :
                    .5 * (2 - (t -= 2) * t * t * t);
        },

        /**
         * Backtracks slightly, then reverses direction and moves to end.
         */
        backIn: function(t) {
            if (t === 1) t -= .001;
            return t * t * ((MAGIC_NUM + 1) * t - MAGIC_NUM);
        },

        /**
         * Overshoots end, then reverses and comes back to end.
         */
        backOut: function(t) {
            return (t -= 1) * t * ((MAGIC_NUM + 1) * t + MAGIC_NUM) + 1;
        },

        /**
         * Backtracks slightly, then reverses direction, overshoots end,
         * then reverses and comes back to end.
         */
        backBoth: function(t) {
            var s = MAGIC_NUM;
            var m = (s *= 1.525) + 1;

            if ((t *= 2 ) < 1) {
                return .5 * (t * t * (m * t - s));
            }
            return .5 * ((t -= 2) * t * (m * t + s) + 2);
        },

        /**
         * Snap in elastic effect.
         */
        elasticIn: function(t) {
            var p = .3, s = p / 4;
            if (t === 0 || t === 1) return t;
            return -(pow(2, 10 * (t -= 1)) * sin((t - s) * (2 * PI) / p));
        },

        /**
         * Snap out elastic effect.
         */
        elasticOut: function(t) {
            var p = .3, s = p / 4;
            if (t === 0 || t === 1) return t;
            return pow(2, -10 * t) * sin((t - s) * (2 * PI) / p) + 1;
        },

        /**
         * Snap both elastic effect.
         */
        elasticBoth: function(t) {
            var p = .45, s = p / 4;
            if (t === 0 || (t *= 2) === 2) return t;

            if (t < 1) {
                return -.5 * (pow(2, 10 * (t -= 1)) *
                        sin((t - s) * (2 * PI) / p));
            }
            return pow(2, -10 * (t -= 1)) *
                    sin((t - s) * (2 * PI) / p) * .5 + 1;
        },

        /**
         * Bounce off of start.
         */
        bounceIn: function(t) {
            return 1 - Easing.bounceOut(1 - t);
        },

        /**
         * Bounces off end.
         */
        bounceOut: function(t) {
            var s = 7.5625, r;

            if (t < (1 / 2.75)) {
                r = s * t * t;
            }
            else if (t < (2 / 2.75)) {
                r = s * (t -= (1.5 / 2.75)) * t + .75;
            }
            else if (t < (2.5 / 2.75)) {
                r = s * (t -= (2.25 / 2.75)) * t + .9375;
            }
            else {
                r = s * (t -= (2.625 / 2.75)) * t + .984375;
            }

            return r;
        },

        /**
         * Bounces off start and end.
         */
        bounceBoth: function(t) {
            if (t < .5) {
                return Easing.bounceIn(t * 2) * .5;
            }
            return Easing.bounceOut(t * 2 - 1) * .5 + .5;
        }
    };

    // 可以通过 require 获取
    module.exports = Easing;


    // 也可以直接通过 jQuery.easing 来使用
    var $ = require('$-debug');
    $.extend($.easing, Easing);

});
