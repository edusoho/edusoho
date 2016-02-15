/*!
 * ====================================================
 * Kity Formula - v1.0.0 - 2014-06-23
 * https://github.com/kitygraph/formula
 * GitHub: https://github.com/kitygraph/formula.git 
 * Copyright (c) 2014 Baidu Kity Group; Licensed MIT
 * ====================================================
 */

(function () {
/**
 * cmd 内部定义
 * build用
 */

// 模块存储
var _modules = {};

function define ( id, deps, factory ) {

    _modules[ id ] = {

        exports: {},
        value: null,
        factory: null

    };

    if ( arguments.length === 2 ) {

        factory = deps;

    }

    if ( _modules.toString.call( factory ) === '[object Object]' ) {

        _modules[ id ][ 'value' ] = factory;

    } else if ( typeof factory === 'function' ) {

        _modules[ id ][ 'factory' ] = factory;

    } else {

        throw new Error( 'define函数未定义的行为' );

    }

}

function require ( id ) {

    var module = _modules[ id ],
        exports = null;

    if ( !module ) {

        return null;

    }

    if ( module.value ) {

        return module.value;

    }

    exports = module.factory.call( null, require, module.exports, module );

    // return 值不为空， 则以return值为最终值
    if ( exports ) {

        module.exports = exports;

    }

    module.value = module.exports;

    return module.value;

}

function use ( id ) {

    return require( id );

}
define("base/canvg", [], function(require) {
    function RGBColor(color_string) {
        this.ok = false;
        if (color_string.charAt(0) == "#") {
            color_string = color_string.substr(1, 6);
        }
        color_string = color_string.replace(/ /g, "");
        color_string = color_string.toLowerCase();
        var simple_colors = {
            aliceblue: "f0f8ff",
            antiquewhite: "faebd7",
            aqua: "00ffff",
            aquamarine: "7fffd4",
            azure: "f0ffff",
            beige: "f5f5dc",
            bisque: "ffe4c4",
            black: "000000",
            blanchedalmond: "ffebcd",
            blue: "0000ff",
            blueviolet: "8a2be2",
            brown: "a52a2a",
            burlywood: "deb887",
            cadetblue: "5f9ea0",
            chartreuse: "7fff00",
            chocolate: "d2691e",
            coral: "ff7f50",
            cornflowerblue: "6495ed",
            cornsilk: "fff8dc",
            crimson: "dc143c",
            cyan: "00ffff",
            darkblue: "00008b",
            darkcyan: "008b8b",
            darkgoldenrod: "b8860b",
            darkgray: "a9a9a9",
            darkgreen: "006400",
            darkkhaki: "bdb76b",
            darkmagenta: "8b008b",
            darkolivegreen: "556b2f",
            darkorange: "ff8c00",
            darkorchid: "9932cc",
            darkred: "8b0000",
            darksalmon: "e9967a",
            darkseagreen: "8fbc8f",
            darkslateblue: "483d8b",
            darkslategray: "2f4f4f",
            darkturquoise: "00ced1",
            darkviolet: "9400d3",
            deeppink: "ff1493",
            deepskyblue: "00bfff",
            dimgray: "696969",
            dodgerblue: "1e90ff",
            feldspar: "d19275",
            firebrick: "b22222",
            floralwhite: "fffaf0",
            forestgreen: "228b22",
            fuchsia: "ff00ff",
            gainsboro: "dcdcdc",
            ghostwhite: "f8f8ff",
            gold: "ffd700",
            goldenrod: "daa520",
            gray: "808080",
            green: "008000",
            greenyellow: "adff2f",
            honeydew: "f0fff0",
            hotpink: "ff69b4",
            indianred: "cd5c5c",
            indigo: "4b0082",
            ivory: "fffff0",
            khaki: "f0e68c",
            lavender: "e6e6fa",
            lavenderblush: "fff0f5",
            lawngreen: "7cfc00",
            lemonchiffon: "fffacd",
            lightblue: "add8e6",
            lightcoral: "f08080",
            lightcyan: "e0ffff",
            lightgoldenrodyellow: "fafad2",
            lightgrey: "d3d3d3",
            lightgreen: "90ee90",
            lightpink: "ffb6c1",
            lightsalmon: "ffa07a",
            lightseagreen: "20b2aa",
            lightskyblue: "87cefa",
            lightslateblue: "8470ff",
            lightslategray: "778899",
            lightsteelblue: "b0c4de",
            lightyellow: "ffffe0",
            lime: "00ff00",
            limegreen: "32cd32",
            linen: "faf0e6",
            magenta: "ff00ff",
            maroon: "800000",
            mediumaquamarine: "66cdaa",
            mediumblue: "0000cd",
            mediumorchid: "ba55d3",
            mediumpurple: "9370d8",
            mediumseagreen: "3cb371",
            mediumslateblue: "7b68ee",
            mediumspringgreen: "00fa9a",
            mediumturquoise: "48d1cc",
            mediumvioletred: "c71585",
            midnightblue: "191970",
            mintcream: "f5fffa",
            mistyrose: "ffe4e1",
            moccasin: "ffe4b5",
            navajowhite: "ffdead",
            navy: "000080",
            oldlace: "fdf5e6",
            olive: "808000",
            olivedrab: "6b8e23",
            orange: "ffa500",
            orangered: "ff4500",
            orchid: "da70d6",
            palegoldenrod: "eee8aa",
            palegreen: "98fb98",
            paleturquoise: "afeeee",
            palevioletred: "d87093",
            papayawhip: "ffefd5",
            peachpuff: "ffdab9",
            peru: "cd853f",
            pink: "ffc0cb",
            plum: "dda0dd",
            powderblue: "b0e0e6",
            purple: "800080",
            red: "ff0000",
            rosybrown: "bc8f8f",
            royalblue: "4169e1",
            saddlebrown: "8b4513",
            salmon: "fa8072",
            sandybrown: "f4a460",
            seagreen: "2e8b57",
            seashell: "fff5ee",
            sienna: "a0522d",
            silver: "c0c0c0",
            skyblue: "87ceeb",
            slateblue: "6a5acd",
            slategray: "708090",
            snow: "fffafa",
            springgreen: "00ff7f",
            steelblue: "4682b4",
            tan: "d2b48c",
            teal: "008080",
            thistle: "d8bfd8",
            tomato: "ff6347",
            turquoise: "40e0d0",
            violet: "ee82ee",
            violetred: "d02090",
            wheat: "f5deb3",
            white: "ffffff",
            whitesmoke: "f5f5f5",
            yellow: "ffff00",
            yellowgreen: "9acd32"
        };
        for (var key in simple_colors) {
            if (color_string == key) {
                color_string = simple_colors[key];
            }
        }
        var color_defs = [ {
            re: /^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/,
            example: [ "rgb(123, 234, 45)", "rgb(255,234,245)" ],
            process: function(bits) {
                return [ parseInt(bits[1]), parseInt(bits[2]), parseInt(bits[3]) ];
            }
        }, {
            re: /^(\w{2})(\w{2})(\w{2})$/,
            example: [ "#00ff00", "336699" ],
            process: function(bits) {
                return [ parseInt(bits[1], 16), parseInt(bits[2], 16), parseInt(bits[3], 16) ];
            }
        }, {
            re: /^(\w{1})(\w{1})(\w{1})$/,
            example: [ "#fb0", "f0f" ],
            process: function(bits) {
                return [ parseInt(bits[1] + bits[1], 16), parseInt(bits[2] + bits[2], 16), parseInt(bits[3] + bits[3], 16) ];
            }
        } ];
        for (var i = 0; i < color_defs.length; i++) {
            var re = color_defs[i].re;
            var processor = color_defs[i].process;
            var bits = re.exec(color_string);
            if (bits) {
                channels = processor(bits);
                this.r = channels[0];
                this.g = channels[1];
                this.b = channels[2];
                this.ok = true;
            }
        }
        this.r = this.r < 0 || isNaN(this.r) ? 0 : this.r > 255 ? 255 : this.r;
        this.g = this.g < 0 || isNaN(this.g) ? 0 : this.g > 255 ? 255 : this.g;
        this.b = this.b < 0 || isNaN(this.b) ? 0 : this.b > 255 ? 255 : this.b;
        this.toRGB = function() {
            return "rgb(" + this.r + ", " + this.g + ", " + this.b + ")";
        };
        this.toHex = function() {
            var r = this.r.toString(16);
            var g = this.g.toString(16);
            var b = this.b.toString(16);
            if (r.length == 1) r = "0" + r;
            if (g.length == 1) g = "0" + g;
            if (b.length == 1) b = "0" + b;
            return "#" + r + g + b;
        };
        this.getHelpXML = function() {
            var examples = new Array();
            for (var i = 0; i < color_defs.length; i++) {
                var example = color_defs[i].example;
                for (var j = 0; j < example.length; j++) {
                    examples[examples.length] = example[j];
                }
            }
            for (var sc in simple_colors) {
                examples[examples.length] = sc;
            }
            var xml = document.createElement("ul");
            xml.setAttribute("id", "rgbcolor-examples");
            for (var i = 0; i < examples.length; i++) {
                try {
                    var list_item = document.createElement("li");
                    var list_color = new RGBColor(examples[i]);
                    var example_div = document.createElement("div");
                    example_div.style.cssText = "margin: 3px; " + "border: 1px solid black; " + "background:" + list_color.toHex() + "; " + "color:" + list_color.toHex();
                    example_div.appendChild(document.createTextNode("test"));
                    var list_item_value = document.createTextNode(" " + examples[i] + " -> " + list_color.toRGB() + " -> " + list_color.toHex());
                    list_item.appendChild(example_div);
                    list_item.appendChild(list_item_value);
                    xml.appendChild(list_item);
                } catch (e) {}
            }
            return xml;
        };
    }
    var mul_table = [ 512, 512, 456, 512, 328, 456, 335, 512, 405, 328, 271, 456, 388, 335, 292, 512, 454, 405, 364, 328, 298, 271, 496, 456, 420, 388, 360, 335, 312, 292, 273, 512, 482, 454, 428, 405, 383, 364, 345, 328, 312, 298, 284, 271, 259, 496, 475, 456, 437, 420, 404, 388, 374, 360, 347, 335, 323, 312, 302, 292, 282, 273, 265, 512, 497, 482, 468, 454, 441, 428, 417, 405, 394, 383, 373, 364, 354, 345, 337, 328, 320, 312, 305, 298, 291, 284, 278, 271, 265, 259, 507, 496, 485, 475, 465, 456, 446, 437, 428, 420, 412, 404, 396, 388, 381, 374, 367, 360, 354, 347, 341, 335, 329, 323, 318, 312, 307, 302, 297, 292, 287, 282, 278, 273, 269, 265, 261, 512, 505, 497, 489, 482, 475, 468, 461, 454, 447, 441, 435, 428, 422, 417, 411, 405, 399, 394, 389, 383, 378, 373, 368, 364, 359, 354, 350, 345, 341, 337, 332, 328, 324, 320, 316, 312, 309, 305, 301, 298, 294, 291, 287, 284, 281, 278, 274, 271, 268, 265, 262, 259, 257, 507, 501, 496, 491, 485, 480, 475, 470, 465, 460, 456, 451, 446, 442, 437, 433, 428, 424, 420, 416, 412, 408, 404, 400, 396, 392, 388, 385, 381, 377, 374, 370, 367, 363, 360, 357, 354, 350, 347, 344, 341, 338, 335, 332, 329, 326, 323, 320, 318, 315, 312, 310, 307, 304, 302, 299, 297, 294, 292, 289, 287, 285, 282, 280, 278, 275, 273, 271, 269, 267, 265, 263, 261, 259 ];
    var shg_table = [ 9, 11, 12, 13, 13, 14, 14, 15, 15, 15, 15, 16, 16, 16, 16, 17, 17, 17, 17, 17, 17, 17, 18, 18, 18, 18, 18, 18, 18, 18, 18, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24 ];
    function stackBlurImage(imageID, canvasID, radius, blurAlphaChannel) {
        var img = document.getElementById(imageID);
        var w = img.naturalWidth;
        var h = img.naturalHeight;
        var canvas = document.getElementById(canvasID);
        canvas.style.width = w + "px";
        canvas.style.height = h + "px";
        canvas.width = w;
        canvas.height = h;
        var context = canvas.getContext("2d");
        context.clearRect(0, 0, w, h);
        context.drawImage(img, 0, 0);
        if (isNaN(radius) || radius < 1) return;
        if (blurAlphaChannel) stackBlurCanvasRGBA(canvasID, 0, 0, w, h, radius); else stackBlurCanvasRGB(canvasID, 0, 0, w, h, radius);
    }
    function stackBlurCanvasRGBA(id, top_x, top_y, width, height, radius) {
        if (isNaN(radius) || radius < 1) return;
        radius |= 0;
        var canvas = document.getElementById(id);
        var context = canvas.getContext("2d");
        var imageData;
        try {
            try {
                imageData = context.getImageData(top_x, top_y, width, height);
            } catch (e) {
                try {
                    netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");
                    imageData = context.getImageData(top_x, top_y, width, height);
                } catch (e) {
                    alert("Cannot access local image");
                    throw new Error("unable to access local image data: " + e);
                    return;
                }
            }
        } catch (e) {
            alert("Cannot access image");
            throw new Error("unable to access image data: " + e);
        }
        var pixels = imageData.data;
        var x, y, i, p, yp, yi, yw, r_sum, g_sum, b_sum, a_sum, r_out_sum, g_out_sum, b_out_sum, a_out_sum, r_in_sum, g_in_sum, b_in_sum, a_in_sum, pr, pg, pb, pa, rbs;
        var div = radius + radius + 1;
        var w4 = width << 2;
        var widthMinus1 = width - 1;
        var heightMinus1 = height - 1;
        var radiusPlus1 = radius + 1;
        var sumFactor = radiusPlus1 * (radiusPlus1 + 1) / 2;
        var stackStart = new BlurStack();
        var stack = stackStart;
        for (i = 1; i < div; i++) {
            stack = stack.next = new BlurStack();
            if (i == radiusPlus1) var stackEnd = stack;
        }
        stack.next = stackStart;
        var stackIn = null;
        var stackOut = null;
        yw = yi = 0;
        var mul_sum = mul_table[radius];
        var shg_sum = shg_table[radius];
        for (y = 0; y < height; y++) {
            r_in_sum = g_in_sum = b_in_sum = a_in_sum = r_sum = g_sum = b_sum = a_sum = 0;
            r_out_sum = radiusPlus1 * (pr = pixels[yi]);
            g_out_sum = radiusPlus1 * (pg = pixels[yi + 1]);
            b_out_sum = radiusPlus1 * (pb = pixels[yi + 2]);
            a_out_sum = radiusPlus1 * (pa = pixels[yi + 3]);
            r_sum += sumFactor * pr;
            g_sum += sumFactor * pg;
            b_sum += sumFactor * pb;
            a_sum += sumFactor * pa;
            stack = stackStart;
            for (i = 0; i < radiusPlus1; i++) {
                stack.r = pr;
                stack.g = pg;
                stack.b = pb;
                stack.a = pa;
                stack = stack.next;
            }
            for (i = 1; i < radiusPlus1; i++) {
                p = yi + ((widthMinus1 < i ? widthMinus1 : i) << 2);
                r_sum += (stack.r = pr = pixels[p]) * (rbs = radiusPlus1 - i);
                g_sum += (stack.g = pg = pixels[p + 1]) * rbs;
                b_sum += (stack.b = pb = pixels[p + 2]) * rbs;
                a_sum += (stack.a = pa = pixels[p + 3]) * rbs;
                r_in_sum += pr;
                g_in_sum += pg;
                b_in_sum += pb;
                a_in_sum += pa;
                stack = stack.next;
            }
            stackIn = stackStart;
            stackOut = stackEnd;
            for (x = 0; x < width; x++) {
                pixels[yi + 3] = pa = a_sum * mul_sum >> shg_sum;
                if (pa != 0) {
                    pa = 255 / pa;
                    pixels[yi] = (r_sum * mul_sum >> shg_sum) * pa;
                    pixels[yi + 1] = (g_sum * mul_sum >> shg_sum) * pa;
                    pixels[yi + 2] = (b_sum * mul_sum >> shg_sum) * pa;
                } else {
                    pixels[yi] = pixels[yi + 1] = pixels[yi + 2] = 0;
                }
                r_sum -= r_out_sum;
                g_sum -= g_out_sum;
                b_sum -= b_out_sum;
                a_sum -= a_out_sum;
                r_out_sum -= stackIn.r;
                g_out_sum -= stackIn.g;
                b_out_sum -= stackIn.b;
                a_out_sum -= stackIn.a;
                p = yw + ((p = x + radius + 1) < widthMinus1 ? p : widthMinus1) << 2;
                r_in_sum += stackIn.r = pixels[p];
                g_in_sum += stackIn.g = pixels[p + 1];
                b_in_sum += stackIn.b = pixels[p + 2];
                a_in_sum += stackIn.a = pixels[p + 3];
                r_sum += r_in_sum;
                g_sum += g_in_sum;
                b_sum += b_in_sum;
                a_sum += a_in_sum;
                stackIn = stackIn.next;
                r_out_sum += pr = stackOut.r;
                g_out_sum += pg = stackOut.g;
                b_out_sum += pb = stackOut.b;
                a_out_sum += pa = stackOut.a;
                r_in_sum -= pr;
                g_in_sum -= pg;
                b_in_sum -= pb;
                a_in_sum -= pa;
                stackOut = stackOut.next;
                yi += 4;
            }
            yw += width;
        }
        for (x = 0; x < width; x++) {
            g_in_sum = b_in_sum = a_in_sum = r_in_sum = g_sum = b_sum = a_sum = r_sum = 0;
            yi = x << 2;
            r_out_sum = radiusPlus1 * (pr = pixels[yi]);
            g_out_sum = radiusPlus1 * (pg = pixels[yi + 1]);
            b_out_sum = radiusPlus1 * (pb = pixels[yi + 2]);
            a_out_sum = radiusPlus1 * (pa = pixels[yi + 3]);
            r_sum += sumFactor * pr;
            g_sum += sumFactor * pg;
            b_sum += sumFactor * pb;
            a_sum += sumFactor * pa;
            stack = stackStart;
            for (i = 0; i < radiusPlus1; i++) {
                stack.r = pr;
                stack.g = pg;
                stack.b = pb;
                stack.a = pa;
                stack = stack.next;
            }
            yp = width;
            for (i = 1; i <= radius; i++) {
                yi = yp + x << 2;
                r_sum += (stack.r = pr = pixels[yi]) * (rbs = radiusPlus1 - i);
                g_sum += (stack.g = pg = pixels[yi + 1]) * rbs;
                b_sum += (stack.b = pb = pixels[yi + 2]) * rbs;
                a_sum += (stack.a = pa = pixels[yi + 3]) * rbs;
                r_in_sum += pr;
                g_in_sum += pg;
                b_in_sum += pb;
                a_in_sum += pa;
                stack = stack.next;
                if (i < heightMinus1) {
                    yp += width;
                }
            }
            yi = x;
            stackIn = stackStart;
            stackOut = stackEnd;
            for (y = 0; y < height; y++) {
                p = yi << 2;
                pixels[p + 3] = pa = a_sum * mul_sum >> shg_sum;
                if (pa > 0) {
                    pa = 255 / pa;
                    pixels[p] = (r_sum * mul_sum >> shg_sum) * pa;
                    pixels[p + 1] = (g_sum * mul_sum >> shg_sum) * pa;
                    pixels[p + 2] = (b_sum * mul_sum >> shg_sum) * pa;
                } else {
                    pixels[p] = pixels[p + 1] = pixels[p + 2] = 0;
                }
                r_sum -= r_out_sum;
                g_sum -= g_out_sum;
                b_sum -= b_out_sum;
                a_sum -= a_out_sum;
                r_out_sum -= stackIn.r;
                g_out_sum -= stackIn.g;
                b_out_sum -= stackIn.b;
                a_out_sum -= stackIn.a;
                p = x + ((p = y + radiusPlus1) < heightMinus1 ? p : heightMinus1) * width << 2;
                r_sum += r_in_sum += stackIn.r = pixels[p];
                g_sum += g_in_sum += stackIn.g = pixels[p + 1];
                b_sum += b_in_sum += stackIn.b = pixels[p + 2];
                a_sum += a_in_sum += stackIn.a = pixels[p + 3];
                stackIn = stackIn.next;
                r_out_sum += pr = stackOut.r;
                g_out_sum += pg = stackOut.g;
                b_out_sum += pb = stackOut.b;
                a_out_sum += pa = stackOut.a;
                r_in_sum -= pr;
                g_in_sum -= pg;
                b_in_sum -= pb;
                a_in_sum -= pa;
                stackOut = stackOut.next;
                yi += width;
            }
        }
        context.putImageData(imageData, top_x, top_y);
    }
    function stackBlurCanvasRGB(id, top_x, top_y, width, height, radius) {
        if (isNaN(radius) || radius < 1) return;
        radius |= 0;
        var canvas = document.getElementById(id);
        var context = canvas.getContext("2d");
        var imageData;
        try {
            try {
                imageData = context.getImageData(top_x, top_y, width, height);
            } catch (e) {
                try {
                    netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");
                    imageData = context.getImageData(top_x, top_y, width, height);
                } catch (e) {
                    alert("Cannot access local image");
                    throw new Error("unable to access local image data: " + e);
                    return;
                }
            }
        } catch (e) {
            alert("Cannot access image");
            throw new Error("unable to access image data: " + e);
        }
        var pixels = imageData.data;
        var x, y, i, p, yp, yi, yw, r_sum, g_sum, b_sum, r_out_sum, g_out_sum, b_out_sum, r_in_sum, g_in_sum, b_in_sum, pr, pg, pb, rbs;
        var div = radius + radius + 1;
        var w4 = width << 2;
        var widthMinus1 = width - 1;
        var heightMinus1 = height - 1;
        var radiusPlus1 = radius + 1;
        var sumFactor = radiusPlus1 * (radiusPlus1 + 1) / 2;
        var stackStart = new BlurStack();
        var stack = stackStart;
        for (i = 1; i < div; i++) {
            stack = stack.next = new BlurStack();
            if (i == radiusPlus1) var stackEnd = stack;
        }
        stack.next = stackStart;
        var stackIn = null;
        var stackOut = null;
        yw = yi = 0;
        var mul_sum = mul_table[radius];
        var shg_sum = shg_table[radius];
        for (y = 0; y < height; y++) {
            r_in_sum = g_in_sum = b_in_sum = r_sum = g_sum = b_sum = 0;
            r_out_sum = radiusPlus1 * (pr = pixels[yi]);
            g_out_sum = radiusPlus1 * (pg = pixels[yi + 1]);
            b_out_sum = radiusPlus1 * (pb = pixels[yi + 2]);
            r_sum += sumFactor * pr;
            g_sum += sumFactor * pg;
            b_sum += sumFactor * pb;
            stack = stackStart;
            for (i = 0; i < radiusPlus1; i++) {
                stack.r = pr;
                stack.g = pg;
                stack.b = pb;
                stack = stack.next;
            }
            for (i = 1; i < radiusPlus1; i++) {
                p = yi + ((widthMinus1 < i ? widthMinus1 : i) << 2);
                r_sum += (stack.r = pr = pixels[p]) * (rbs = radiusPlus1 - i);
                g_sum += (stack.g = pg = pixels[p + 1]) * rbs;
                b_sum += (stack.b = pb = pixels[p + 2]) * rbs;
                r_in_sum += pr;
                g_in_sum += pg;
                b_in_sum += pb;
                stack = stack.next;
            }
            stackIn = stackStart;
            stackOut = stackEnd;
            for (x = 0; x < width; x++) {
                pixels[yi] = r_sum * mul_sum >> shg_sum;
                pixels[yi + 1] = g_sum * mul_sum >> shg_sum;
                pixels[yi + 2] = b_sum * mul_sum >> shg_sum;
                r_sum -= r_out_sum;
                g_sum -= g_out_sum;
                b_sum -= b_out_sum;
                r_out_sum -= stackIn.r;
                g_out_sum -= stackIn.g;
                b_out_sum -= stackIn.b;
                p = yw + ((p = x + radius + 1) < widthMinus1 ? p : widthMinus1) << 2;
                r_in_sum += stackIn.r = pixels[p];
                g_in_sum += stackIn.g = pixels[p + 1];
                b_in_sum += stackIn.b = pixels[p + 2];
                r_sum += r_in_sum;
                g_sum += g_in_sum;
                b_sum += b_in_sum;
                stackIn = stackIn.next;
                r_out_sum += pr = stackOut.r;
                g_out_sum += pg = stackOut.g;
                b_out_sum += pb = stackOut.b;
                r_in_sum -= pr;
                g_in_sum -= pg;
                b_in_sum -= pb;
                stackOut = stackOut.next;
                yi += 4;
            }
            yw += width;
        }
        for (x = 0; x < width; x++) {
            g_in_sum = b_in_sum = r_in_sum = g_sum = b_sum = r_sum = 0;
            yi = x << 2;
            r_out_sum = radiusPlus1 * (pr = pixels[yi]);
            g_out_sum = radiusPlus1 * (pg = pixels[yi + 1]);
            b_out_sum = radiusPlus1 * (pb = pixels[yi + 2]);
            r_sum += sumFactor * pr;
            g_sum += sumFactor * pg;
            b_sum += sumFactor * pb;
            stack = stackStart;
            for (i = 0; i < radiusPlus1; i++) {
                stack.r = pr;
                stack.g = pg;
                stack.b = pb;
                stack = stack.next;
            }
            yp = width;
            for (i = 1; i <= radius; i++) {
                yi = yp + x << 2;
                r_sum += (stack.r = pr = pixels[yi]) * (rbs = radiusPlus1 - i);
                g_sum += (stack.g = pg = pixels[yi + 1]) * rbs;
                b_sum += (stack.b = pb = pixels[yi + 2]) * rbs;
                r_in_sum += pr;
                g_in_sum += pg;
                b_in_sum += pb;
                stack = stack.next;
                if (i < heightMinus1) {
                    yp += width;
                }
            }
            yi = x;
            stackIn = stackStart;
            stackOut = stackEnd;
            for (y = 0; y < height; y++) {
                p = yi << 2;
                pixels[p] = r_sum * mul_sum >> shg_sum;
                pixels[p + 1] = g_sum * mul_sum >> shg_sum;
                pixels[p + 2] = b_sum * mul_sum >> shg_sum;
                r_sum -= r_out_sum;
                g_sum -= g_out_sum;
                b_sum -= b_out_sum;
                r_out_sum -= stackIn.r;
                g_out_sum -= stackIn.g;
                b_out_sum -= stackIn.b;
                p = x + ((p = y + radiusPlus1) < heightMinus1 ? p : heightMinus1) * width << 2;
                r_sum += r_in_sum += stackIn.r = pixels[p];
                g_sum += g_in_sum += stackIn.g = pixels[p + 1];
                b_sum += b_in_sum += stackIn.b = pixels[p + 2];
                stackIn = stackIn.next;
                r_out_sum += pr = stackOut.r;
                g_out_sum += pg = stackOut.g;
                b_out_sum += pb = stackOut.b;
                r_in_sum -= pr;
                g_in_sum -= pg;
                b_in_sum -= pb;
                stackOut = stackOut.next;
                yi += width;
            }
        }
        context.putImageData(imageData, top_x, top_y);
    }
    function BlurStack() {
        this.r = 0;
        this.g = 0;
        this.b = 0;
        this.a = 0;
        this.next = null;
    }
    (function() {
        this.canvg = function(target, s, opts) {
            if (target == null && s == null && opts == null) {
                var svgTags = document.getElementsByTagName("svg");
                for (var i = 0; i < svgTags.length; i++) {
                    var svgTag = svgTags[i];
                    var c = document.createElement("canvas");
                    c.width = svgTag.clientWidth;
                    c.height = svgTag.clientHeight;
                    svgTag.parentNode.insertBefore(c, svgTag);
                    svgTag.parentNode.removeChild(svgTag);
                    var div = document.createElement("div");
                    div.appendChild(svgTag);
                    canvg(c, div.innerHTML);
                }
                return;
            }
            opts = opts || {};
            if (typeof target == "string") {
                target = document.getElementById(target);
            }
            if (target.svg != null) target.svg.stop();
            var svg = build();
            if (!(target.childNodes.length == 1 && target.childNodes[0].nodeName == "OBJECT")) target.svg = svg;
            svg.opts = opts;
            var ctx = target.getContext("2d");
            if (typeof s.documentElement != "undefined") {
                svg.loadXmlDoc(ctx, s);
            } else if (s.substr(0, 1) == "<") {
                svg.loadXml(ctx, s);
            } else {
                svg.load(ctx, s);
            }
        };
        function build() {
            var svg = {};
            svg.FRAMERATE = 30;
            svg.MAX_VIRTUAL_PIXELS = 3e4;
            svg.init = function(ctx) {
                var uniqueId = 0;
                svg.UniqueId = function() {
                    uniqueId++;
                    return "canvg" + uniqueId;
                };
                svg.Definitions = {};
                svg.Styles = {};
                svg.Animations = [];
                svg.Images = [];
                svg.ctx = ctx;
                svg.ViewPort = new function() {
                    this.viewPorts = [];
                    this.Clear = function() {
                        this.viewPorts = [];
                    };
                    this.SetCurrent = function(width, height) {
                        this.viewPorts.push({
                            width: width,
                            height: height
                        });
                    };
                    this.RemoveCurrent = function() {
                        this.viewPorts.pop();
                    };
                    this.Current = function() {
                        return this.viewPorts[this.viewPorts.length - 1];
                    };
                    this.width = function() {
                        return this.Current().width;
                    };
                    this.height = function() {
                        return this.Current().height;
                    };
                    this.ComputeSize = function(d) {
                        if (d != null && typeof d == "number") return d;
                        if (d == "x") return this.width();
                        if (d == "y") return this.height();
                        return Math.sqrt(Math.pow(this.width(), 2) + Math.pow(this.height(), 2)) / Math.sqrt(2);
                    };
                }();
            };
            svg.init();
            svg.ImagesLoaded = function() {
                for (var i = 0; i < svg.Images.length; i++) {
                    if (!svg.Images[i].loaded) return false;
                }
                return true;
            };
            svg.trim = function(s) {
                return s.replace(/^\s+|\s+$/g, "");
            };
            svg.compressSpaces = function(s) {
                return s.replace(/[\s\r\t\n]+/gm, " ");
            };
            svg.ajax = function(url) {
                var AJAX;
                if (window.XMLHttpRequest) {
                    AJAX = new XMLHttpRequest();
                } else {
                    AJAX = new ActiveXObject("Microsoft.XMLHTTP");
                }
                if (AJAX) {
                    AJAX.open("GET", url, false);
                    AJAX.send(null);
                    return AJAX.responseText;
                }
                return null;
            };
            svg.parseXml = function(xml) {
                if (window.DOMParser) {
                    var parser = new DOMParser();
                    return parser.parseFromString(xml, "text/xml");
                } else {
                    xml = xml.replace(/<!DOCTYPE svg[^>]*>/, "");
                    var xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
                    xmlDoc.async = "false";
                    xmlDoc.loadXML(xml);
                    return xmlDoc;
                }
            };
            svg.Property = function(name, value) {
                this.name = name;
                this.value = value;
            };
            svg.Property.prototype.getValue = function() {
                return this.value;
            };
            svg.Property.prototype.hasValue = function() {
                return this.value != null && this.value !== "";
            };
            svg.Property.prototype.numValue = function() {
                if (!this.hasValue()) return 0;
                var n = parseFloat(this.value);
                if ((this.value + "").match(/%$/)) {
                    n = n / 100;
                }
                return n;
            };
            svg.Property.prototype.valueOrDefault = function(def) {
                if (this.hasValue()) return this.value;
                return def;
            };
            svg.Property.prototype.numValueOrDefault = function(def) {
                if (this.hasValue()) return this.numValue();
                return def;
            };
            svg.Property.prototype.addOpacity = function(opacity) {
                var newValue = this.value;
                if (opacity != null && opacity != "" && typeof this.value == "string") {
                    var color = new RGBColor(this.value);
                    if (color.ok) {
                        newValue = "rgba(" + color.r + ", " + color.g + ", " + color.b + ", " + opacity + ")";
                    }
                }
                return new svg.Property(this.name, newValue);
            };
            svg.Property.prototype.getDefinition = function() {
                var name = this.value.match(/#([^\)'"]+)/);
                if (name) {
                    name = name[1];
                }
                if (!name) {
                    name = this.value;
                }
                return svg.Definitions[name];
            };
            svg.Property.prototype.isUrlDefinition = function() {
                return this.value.indexOf("url(") == 0;
            };
            svg.Property.prototype.getFillStyleDefinition = function(e, opacityProp) {
                var def = this.getDefinition();
                if (def != null && def.createGradient) {
                    return def.createGradient(svg.ctx, e, opacityProp);
                }
                if (def != null && def.createPattern) {
                    if (def.getHrefAttribute().hasValue()) {
                        var pt = def.attribute("patternTransform");
                        def = def.getHrefAttribute().getDefinition();
                        if (pt.hasValue()) {
                            def.attribute("patternTransform", true).value = pt.value;
                        }
                    }
                    return def.createPattern(svg.ctx, e);
                }
                return null;
            };
            svg.Property.prototype.getDPI = function(viewPort) {
                return 96;
            };
            svg.Property.prototype.getEM = function(viewPort) {
                var em = 12;
                var fontSize = new svg.Property("fontSize", svg.Font.Parse(svg.ctx.font).fontSize);
                if (fontSize.hasValue()) em = fontSize.toPixels(viewPort);
                return em;
            };
            svg.Property.prototype.getUnits = function() {
                var s = this.value + "";
                return s.replace(/[0-9\.\-]/g, "");
            };
            svg.Property.prototype.toPixels = function(viewPort, processPercent) {
                if (!this.hasValue()) return 0;
                var s = this.value + "";
                if (s.match(/em$/)) return this.numValue() * this.getEM(viewPort);
                if (s.match(/ex$/)) return this.numValue() * this.getEM(viewPort) / 2;
                if (s.match(/px$/)) return this.numValue();
                if (s.match(/pt$/)) return this.numValue() * this.getDPI(viewPort) * (1 / 72);
                if (s.match(/pc$/)) return this.numValue() * 15;
                if (s.match(/cm$/)) return this.numValue() * this.getDPI(viewPort) / 2.54;
                if (s.match(/mm$/)) return this.numValue() * this.getDPI(viewPort) / 25.4;
                if (s.match(/in$/)) return this.numValue() * this.getDPI(viewPort);
                if (s.match(/%$/)) return this.numValue() * svg.ViewPort.ComputeSize(viewPort);
                var n = this.numValue();
                if (processPercent && n < 1) return n * svg.ViewPort.ComputeSize(viewPort);
                return n;
            };
            svg.Property.prototype.toMilliseconds = function() {
                if (!this.hasValue()) return 0;
                var s = this.value + "";
                if (s.match(/s$/)) return this.numValue() * 1e3;
                if (s.match(/ms$/)) return this.numValue();
                return this.numValue();
            };
            svg.Property.prototype.toRadians = function() {
                if (!this.hasValue()) return 0;
                var s = this.value + "";
                if (s.match(/deg$/)) return this.numValue() * (Math.PI / 180);
                if (s.match(/grad$/)) return this.numValue() * (Math.PI / 200);
                if (s.match(/rad$/)) return this.numValue();
                return this.numValue() * (Math.PI / 180);
            };
            svg.Font = new function() {
                this.Styles = "normal|italic|oblique|inherit";
                this.Variants = "normal|small-caps|inherit";
                this.Weights = "normal|bold|bolder|lighter|100|200|300|400|500|600|700|800|900|inherit";
                this.CreateFont = function(fontStyle, fontVariant, fontWeight, fontSize, fontFamily, inherit) {
                    var f = inherit != null ? this.Parse(inherit) : this.CreateFont("", "", "", "", "", svg.ctx.font);
                    return {
                        fontFamily: fontFamily || f.fontFamily,
                        fontSize: fontSize || f.fontSize,
                        fontStyle: fontStyle || f.fontStyle,
                        fontWeight: fontWeight || f.fontWeight,
                        fontVariant: fontVariant || f.fontVariant,
                        toString: function() {
                            return [ this.fontStyle, this.fontVariant, this.fontWeight, this.fontSize, this.fontFamily ].join(" ");
                        }
                    };
                };
                var that = this;
                this.Parse = function(s) {
                    var f = {};
                    var d = svg.trim(svg.compressSpaces(s || "")).split(" ");
                    var set = {
                        fontSize: false,
                        fontStyle: false,
                        fontWeight: false,
                        fontVariant: false
                    };
                    var ff = "";
                    for (var i = 0; i < d.length; i++) {
                        if (!set.fontStyle && that.Styles.indexOf(d[i]) != -1) {
                            if (d[i] != "inherit") f.fontStyle = d[i];
                            set.fontStyle = true;
                        } else if (!set.fontVariant && that.Variants.indexOf(d[i]) != -1) {
                            if (d[i] != "inherit") f.fontVariant = d[i];
                            set.fontStyle = set.fontVariant = true;
                        } else if (!set.fontWeight && that.Weights.indexOf(d[i]) != -1) {
                            if (d[i] != "inherit") f.fontWeight = d[i];
                            set.fontStyle = set.fontVariant = set.fontWeight = true;
                        } else if (!set.fontSize) {
                            if (d[i] != "inherit") f.fontSize = d[i].split("/")[0];
                            set.fontStyle = set.fontVariant = set.fontWeight = set.fontSize = true;
                        } else {
                            if (d[i] != "inherit") ff += d[i];
                        }
                    }
                    if (ff != "") f.fontFamily = ff;
                    return f;
                };
            }();
            svg.ToNumberArray = function(s) {
                var a = svg.trim(svg.compressSpaces((s || "").replace(/,/g, " "))).split(" ");
                for (var i = 0; i < a.length; i++) {
                    a[i] = parseFloat(a[i]);
                }
                return a;
            };
            svg.Point = function(x, y) {
                this.x = x;
                this.y = y;
            };
            svg.Point.prototype.angleTo = function(p) {
                return Math.atan2(p.y - this.y, p.x - this.x);
            };
            svg.Point.prototype.applyTransform = function(v) {
                var xp = this.x * v[0] + this.y * v[2] + v[4];
                var yp = this.x * v[1] + this.y * v[3] + v[5];
                this.x = xp;
                this.y = yp;
            };
            svg.CreatePoint = function(s) {
                var a = svg.ToNumberArray(s);
                return new svg.Point(a[0], a[1]);
            };
            svg.CreatePath = function(s) {
                var a = svg.ToNumberArray(s);
                var path = [];
                for (var i = 0; i < a.length; i += 2) {
                    path.push(new svg.Point(a[i], a[i + 1]));
                }
                return path;
            };
            svg.BoundingBox = function(x1, y1, x2, y2) {
                this.x1 = Number.NaN;
                this.y1 = Number.NaN;
                this.x2 = Number.NaN;
                this.y2 = Number.NaN;
                this.x = function() {
                    return this.x1;
                };
                this.y = function() {
                    return this.y1;
                };
                this.width = function() {
                    return this.x2 - this.x1;
                };
                this.height = function() {
                    return this.y2 - this.y1;
                };
                this.addPoint = function(x, y) {
                    if (x != null) {
                        if (isNaN(this.x1) || isNaN(this.x2)) {
                            this.x1 = x;
                            this.x2 = x;
                        }
                        if (x < this.x1) this.x1 = x;
                        if (x > this.x2) this.x2 = x;
                    }
                    if (y != null) {
                        if (isNaN(this.y1) || isNaN(this.y2)) {
                            this.y1 = y;
                            this.y2 = y;
                        }
                        if (y < this.y1) this.y1 = y;
                        if (y > this.y2) this.y2 = y;
                    }
                };
                this.addX = function(x) {
                    this.addPoint(x, null);
                };
                this.addY = function(y) {
                    this.addPoint(null, y);
                };
                this.addBoundingBox = function(bb) {
                    this.addPoint(bb.x1, bb.y1);
                    this.addPoint(bb.x2, bb.y2);
                };
                this.addQuadraticCurve = function(p0x, p0y, p1x, p1y, p2x, p2y) {
                    var cp1x = p0x + 2 / 3 * (p1x - p0x);
                    var cp1y = p0y + 2 / 3 * (p1y - p0y);
                    var cp2x = cp1x + 1 / 3 * (p2x - p0x);
                    var cp2y = cp1y + 1 / 3 * (p2y - p0y);
                    this.addBezierCurve(p0x, p0y, cp1x, cp2x, cp1y, cp2y, p2x, p2y);
                };
                this.addBezierCurve = function(p0x, p0y, p1x, p1y, p2x, p2y, p3x, p3y) {
                    var p0 = [ p0x, p0y ], p1 = [ p1x, p1y ], p2 = [ p2x, p2y ], p3 = [ p3x, p3y ];
                    this.addPoint(p0[0], p0[1]);
                    this.addPoint(p3[0], p3[1]);
                    for (i = 0; i <= 1; i++) {
                        var f = function(t) {
                            return Math.pow(1 - t, 3) * p0[i] + 3 * Math.pow(1 - t, 2) * t * p1[i] + 3 * (1 - t) * Math.pow(t, 2) * p2[i] + Math.pow(t, 3) * p3[i];
                        };
                        var b = 6 * p0[i] - 12 * p1[i] + 6 * p2[i];
                        var a = -3 * p0[i] + 9 * p1[i] - 9 * p2[i] + 3 * p3[i];
                        var c = 3 * p1[i] - 3 * p0[i];
                        if (a == 0) {
                            if (b == 0) continue;
                            var t = -c / b;
                            if (0 < t && t < 1) {
                                if (i == 0) this.addX(f(t));
                                if (i == 1) this.addY(f(t));
                            }
                            continue;
                        }
                        var b2ac = Math.pow(b, 2) - 4 * c * a;
                        if (b2ac < 0) continue;
                        var t1 = (-b + Math.sqrt(b2ac)) / (2 * a);
                        if (0 < t1 && t1 < 1) {
                            if (i == 0) this.addX(f(t1));
                            if (i == 1) this.addY(f(t1));
                        }
                        var t2 = (-b - Math.sqrt(b2ac)) / (2 * a);
                        if (0 < t2 && t2 < 1) {
                            if (i == 0) this.addX(f(t2));
                            if (i == 1) this.addY(f(t2));
                        }
                    }
                };
                this.isPointInBox = function(x, y) {
                    return this.x1 <= x && x <= this.x2 && this.y1 <= y && y <= this.y2;
                };
                this.addPoint(x1, y1);
                this.addPoint(x2, y2);
            };
            svg.Transform = function(v) {
                var that = this;
                this.Type = {};
                this.Type.translate = function(s) {
                    this.p = svg.CreatePoint(s);
                    this.apply = function(ctx) {
                        ctx.translate(this.p.x || 0, this.p.y || 0);
                    };
                    this.unapply = function(ctx) {
                        ctx.translate(-1 * this.p.x || 0, -1 * this.p.y || 0);
                    };
                    this.applyToPoint = function(p) {
                        p.applyTransform([ 1, 0, 0, 1, this.p.x || 0, this.p.y || 0 ]);
                    };
                };
                this.Type.rotate = function(s) {
                    var a = svg.ToNumberArray(s);
                    this.angle = new svg.Property("angle", a[0]);
                    this.cx = a[1] || 0;
                    this.cy = a[2] || 0;
                    this.apply = function(ctx) {
                        ctx.translate(this.cx, this.cy);
                        ctx.rotate(this.angle.toRadians());
                        ctx.translate(-this.cx, -this.cy);
                    };
                    this.unapply = function(ctx) {
                        ctx.translate(this.cx, this.cy);
                        ctx.rotate(-1 * this.angle.toRadians());
                        ctx.translate(-this.cx, -this.cy);
                    };
                    this.applyToPoint = function(p) {
                        var a = this.angle.toRadians();
                        p.applyTransform([ 1, 0, 0, 1, this.p.x || 0, this.p.y || 0 ]);
                        p.applyTransform([ Math.cos(a), Math.sin(a), -Math.sin(a), Math.cos(a), 0, 0 ]);
                        p.applyTransform([ 1, 0, 0, 1, -this.p.x || 0, -this.p.y || 0 ]);
                    };
                };
                this.Type.scale = function(s) {
                    this.p = svg.CreatePoint(s);
                    this.apply = function(ctx) {
                        ctx.scale(this.p.x || 1, this.p.y || this.p.x || 1);
                    };
                    this.unapply = function(ctx) {
                        ctx.scale(1 / this.p.x || 1, 1 / this.p.y || this.p.x || 1);
                    };
                    this.applyToPoint = function(p) {
                        p.applyTransform([ this.p.x || 0, 0, 0, this.p.y || 0, 0, 0 ]);
                    };
                };
                this.Type.matrix = function(s) {
                    this.m = svg.ToNumberArray(s);
                    this.apply = function(ctx) {
                        ctx.transform(this.m[0], this.m[1], this.m[2], this.m[3], this.m[4], this.m[5]);
                    };
                    this.applyToPoint = function(p) {
                        p.applyTransform(this.m);
                    };
                };
                this.Type.SkewBase = function(s) {
                    this.base = that.Type.matrix;
                    this.base(s);
                    this.angle = new svg.Property("angle", s);
                };
                this.Type.SkewBase.prototype = new this.Type.matrix();
                this.Type.skewX = function(s) {
                    this.base = that.Type.SkewBase;
                    this.base(s);
                    this.m = [ 1, 0, Math.tan(this.angle.toRadians()), 1, 0, 0 ];
                };
                this.Type.skewX.prototype = new this.Type.SkewBase();
                this.Type.skewY = function(s) {
                    this.base = that.Type.SkewBase;
                    this.base(s);
                    this.m = [ 1, Math.tan(this.angle.toRadians()), 0, 1, 0, 0 ];
                };
                this.Type.skewY.prototype = new this.Type.SkewBase();
                this.transforms = [];
                this.apply = function(ctx) {
                    for (var i = 0; i < this.transforms.length; i++) {
                        this.transforms[i].apply(ctx);
                    }
                };
                this.unapply = function(ctx) {
                    for (var i = this.transforms.length - 1; i >= 0; i--) {
                        this.transforms[i].unapply(ctx);
                    }
                };
                this.applyToPoint = function(p) {
                    for (var i = 0; i < this.transforms.length; i++) {
                        this.transforms[i].applyToPoint(p);
                    }
                };
                var data = svg.trim(svg.compressSpaces(v)).replace(/\)(\s?,\s?)/g, ") ").split(/\s(?=[a-z])/);
                for (var i = 0; i < data.length; i++) {
                    var type = svg.trim(data[i].split("(")[0]);
                    var s = data[i].split("(")[1].replace(")", "");
                    var transform = new this.Type[type](s);
                    transform.type = type;
                    this.transforms.push(transform);
                }
            };
            svg.AspectRatio = function(ctx, aspectRatio, width, desiredWidth, height, desiredHeight, minX, minY, refX, refY) {
                aspectRatio = svg.compressSpaces(aspectRatio);
                aspectRatio = aspectRatio.replace(/^defer\s/, "");
                var align = aspectRatio.split(" ")[0] || "xMidYMid";
                var meetOrSlice = aspectRatio.split(" ")[1] || "meet";
                var scaleX = width / desiredWidth;
                var scaleY = height / desiredHeight;
                var scaleMin = Math.min(scaleX, scaleY);
                var scaleMax = Math.max(scaleX, scaleY);
                if (meetOrSlice == "meet") {
                    desiredWidth *= scaleMin;
                    desiredHeight *= scaleMin;
                }
                if (meetOrSlice == "slice") {
                    desiredWidth *= scaleMax;
                    desiredHeight *= scaleMax;
                }
                refX = new svg.Property("refX", refX);
                refY = new svg.Property("refY", refY);
                if (refX.hasValue() && refY.hasValue()) {
                    ctx.translate(-scaleMin * refX.toPixels("x"), -scaleMin * refY.toPixels("y"));
                } else {
                    if (align.match(/^xMid/) && (meetOrSlice == "meet" && scaleMin == scaleY || meetOrSlice == "slice" && scaleMax == scaleY)) ctx.translate(width / 2 - desiredWidth / 2, 0);
                    if (align.match(/YMid$/) && (meetOrSlice == "meet" && scaleMin == scaleX || meetOrSlice == "slice" && scaleMax == scaleX)) ctx.translate(0, height / 2 - desiredHeight / 2);
                    if (align.match(/^xMax/) && (meetOrSlice == "meet" && scaleMin == scaleY || meetOrSlice == "slice" && scaleMax == scaleY)) ctx.translate(width - desiredWidth, 0);
                    if (align.match(/YMax$/) && (meetOrSlice == "meet" && scaleMin == scaleX || meetOrSlice == "slice" && scaleMax == scaleX)) ctx.translate(0, height - desiredHeight);
                }
                if (align == "none") ctx.scale(scaleX, scaleY); else if (meetOrSlice == "meet") ctx.scale(scaleMin, scaleMin); else if (meetOrSlice == "slice") ctx.scale(scaleMax, scaleMax);
                ctx.translate(minX == null ? 0 : -minX, minY == null ? 0 : -minY);
            };
            svg.Element = {};
            svg.EmptyProperty = new svg.Property("EMPTY", "");
            svg.Element.ElementBase = function(node) {
                this.attributes = {};
                this.styles = {};
                this.children = [];
                this.attribute = function(name, createIfNotExists) {
                    var a = this.attributes[name];
                    if (a != null) return a;
                    if (createIfNotExists == true) {
                        a = new svg.Property(name, "");
                        this.attributes[name] = a;
                    }
                    return a || svg.EmptyProperty;
                };
                this.getHrefAttribute = function() {
                    for (var a in this.attributes) {
                        if (a.match(/:href$/)) {
                            return this.attributes[a];
                        }
                    }
                    return svg.EmptyProperty;
                };
                this.style = function(name, createIfNotExists) {
                    var s = this.styles[name];
                    if (s != null) return s;
                    var a = this.attribute(name);
                    if (a != null && a.hasValue()) {
                        this.styles[name] = a;
                        return a;
                    }
                    var p = this.parent;
                    if (p != null) {
                        var ps = p.style(name);
                        if (ps != null && ps.hasValue()) {
                            return ps;
                        }
                    }
                    if (createIfNotExists == true) {
                        s = new svg.Property(name, "");
                        this.styles[name] = s;
                    }
                    return s || svg.EmptyProperty;
                };
                this.render = function(ctx) {
                    if (this.style("display").value == "none") return;
                    if (this.attribute("visibility").value == "hidden") return;
                    ctx.save();
                    if (this.attribute("mask").hasValue()) {
                        var mask = this.attribute("mask").getDefinition();
                        if (mask != null) mask.apply(ctx, this);
                    } else if (this.style("filter").hasValue()) {
                        var filter = this.style("filter").getDefinition();
                        if (filter != null) filter.apply(ctx, this);
                    } else {
                        this.setContext(ctx);
                        this.renderChildren(ctx);
                        this.clearContext(ctx);
                    }
                    ctx.restore();
                };
                this.setContext = function(ctx) {};
                this.clearContext = function(ctx) {};
                this.renderChildren = function(ctx) {
                    for (var i = 0; i < this.children.length; i++) {
                        this.children[i].render(ctx);
                    }
                };
                this.addChild = function(childNode, create) {
                    var child = childNode;
                    if (create) child = svg.CreateElement(childNode);
                    child.parent = this;
                    this.children.push(child);
                };
                if (node != null && node.nodeType == 1) {
                    for (var i = 0; i < node.childNodes.length; i++) {
                        var childNode = node.childNodes[i];
                        if (childNode.nodeType == 1) this.addChild(childNode, true);
                        if (this.captureTextNodes && childNode.nodeType == 3) {
                            var text = childNode.nodeValue || childNode.text || "";
                            if (svg.trim(svg.compressSpaces(text)) != "") {
                                this.addChild(new svg.Element.tspan(childNode), false);
                            }
                        }
                    }
                    for (var i = 0; i < node.attributes.length; i++) {
                        var attribute = node.attributes[i];
                        this.attributes[attribute.nodeName] = new svg.Property(attribute.nodeName, attribute.nodeValue);
                    }
                    var styles = svg.Styles[node.nodeName];
                    if (styles != null) {
                        for (var name in styles) {
                            this.styles[name] = styles[name];
                        }
                    }
                    if (this.attribute("class").hasValue()) {
                        var classes = svg.compressSpaces(this.attribute("class").value).split(" ");
                        for (var j = 0; j < classes.length; j++) {
                            styles = svg.Styles["." + classes[j]];
                            if (styles != null) {
                                for (var name in styles) {
                                    this.styles[name] = styles[name];
                                }
                            }
                            styles = svg.Styles[node.nodeName + "." + classes[j]];
                            if (styles != null) {
                                for (var name in styles) {
                                    this.styles[name] = styles[name];
                                }
                            }
                        }
                    }
                    if (this.attribute("id").hasValue()) {
                        var styles = svg.Styles["#" + this.attribute("id").value];
                        if (styles != null) {
                            for (var name in styles) {
                                this.styles[name] = styles[name];
                            }
                        }
                    }
                    if (this.attribute("style").hasValue()) {
                        var styles = this.attribute("style").value.split(";");
                        for (var i = 0; i < styles.length; i++) {
                            if (svg.trim(styles[i]) != "") {
                                var style = styles[i].split(":");
                                var name = svg.trim(style[0]);
                                var value = svg.trim(style[1]);
                                this.styles[name] = new svg.Property(name, value);
                            }
                        }
                    }
                    if (this.attribute("id").hasValue()) {
                        if (svg.Definitions[this.attribute("id").value] == null) {
                            svg.Definitions[this.attribute("id").value] = this;
                        }
                    }
                }
            };
            svg.Element.RenderedElementBase = function(node) {
                this.base = svg.Element.ElementBase;
                this.base(node);
                this.setContext = function(ctx) {
                    if (this.style("fill").isUrlDefinition()) {
                        var fs = this.style("fill").getFillStyleDefinition(this, this.style("fill-opacity"));
                        if (fs != null) ctx.fillStyle = fs;
                    } else if (this.style("fill").hasValue()) {
                        var fillStyle = this.style("fill");
                        if (fillStyle.value == "currentColor") fillStyle.value = this.style("color").value;
                        ctx.fillStyle = fillStyle.value == "none" ? "rgba(0,0,0,0)" : fillStyle.value;
                    }
                    if (this.style("fill-opacity").hasValue()) {
                        var fillStyle = new svg.Property("fill", ctx.fillStyle);
                        fillStyle = fillStyle.addOpacity(this.style("fill-opacity").value);
                        ctx.fillStyle = fillStyle.value;
                    }
                    if (this.style("stroke").isUrlDefinition()) {
                        var fs = this.style("stroke").getFillStyleDefinition(this, this.style("stroke-opacity"));
                        if (fs != null) ctx.strokeStyle = fs;
                    } else if (this.style("stroke").hasValue()) {
                        var strokeStyle = this.style("stroke");
                        if (strokeStyle.value == "currentColor") strokeStyle.value = this.style("color").value;
                        ctx.strokeStyle = strokeStyle.value == "none" ? "rgba(0,0,0,0)" : strokeStyle.value;
                    }
                    if (this.style("stroke-opacity").hasValue()) {
                        var strokeStyle = new svg.Property("stroke", ctx.strokeStyle);
                        strokeStyle = strokeStyle.addOpacity(this.style("stroke-opacity").value);
                        ctx.strokeStyle = strokeStyle.value;
                    }
                    if (this.style("stroke-width").hasValue()) {
                        var newLineWidth = this.style("stroke-width").toPixels();
                        ctx.lineWidth = newLineWidth == 0 ? .001 : newLineWidth;
                    }
                    if (this.style("stroke-linecap").hasValue()) ctx.lineCap = this.style("stroke-linecap").value;
                    if (this.style("stroke-linejoin").hasValue()) ctx.lineJoin = this.style("stroke-linejoin").value;
                    if (this.style("stroke-miterlimit").hasValue()) ctx.miterLimit = this.style("stroke-miterlimit").value;
                    if (this.style("stroke-dasharray").hasValue()) {
                        var gaps = svg.ToNumberArray(this.style("stroke-dasharray").value);
                        if (typeof ctx.setLineDash != "undefined") {
                            ctx.setLineDash(gaps);
                        } else if (typeof ctx.webkitLineDash != "undefined") {
                            ctx.webkitLineDash = gaps;
                        } else if (typeof ctx.mozDash != "undefined") {
                            ctx.mozDash = gaps;
                        }
                        var offset = this.style("stroke-dashoffset").numValueOrDefault(1);
                        if (typeof ctx.lineDashOffset != "undefined") {
                            ctx.lineDashOffset = offset;
                        } else if (typeof ctx.webkitLineDashOffset != "undefined") {
                            ctx.webkitLineDashOffset = offset;
                        } else if (typeof ctx.mozDashOffset != "undefined") {
                            ctx.mozDashOffset = offset;
                        }
                    }
                    if (typeof ctx.font != "undefined") {
                        ctx.font = svg.Font.CreateFont(this.style("font-style").value, this.style("font-variant").value, this.style("font-weight").value, this.style("font-size").hasValue() ? this.style("font-size").toPixels() + "px" : "", this.style("font-family").value).toString();
                    }
                    if (this.attribute("transform").hasValue()) {
                        var transform = new svg.Transform(this.attribute("transform").value);
                        transform.apply(ctx);
                    }
                    if (this.style("clip-path").hasValue()) {
                        var clip = this.style("clip-path").getDefinition();
                        if (clip != null) clip.apply(ctx);
                    }
                    if (this.style("opacity").hasValue()) {
                        ctx.globalAlpha = this.style("opacity").numValue();
                    }
                };
            };
            svg.Element.RenderedElementBase.prototype = new svg.Element.ElementBase();
            svg.Element.PathElementBase = function(node) {
                this.base = svg.Element.RenderedElementBase;
                this.base(node);
                this.path = function(ctx) {
                    if (ctx != null) ctx.beginPath();
                    return new svg.BoundingBox();
                };
                this.renderChildren = function(ctx) {
                    this.path(ctx);
                    svg.Mouse.checkPath(this, ctx);
                    if (ctx.fillStyle != "") {
                        if (this.attribute("fill-rule").hasValue()) {
                            ctx.fill(this.attribute("fill-rule").value);
                        } else {
                            ctx.fill();
                        }
                    }
                    if (ctx.strokeStyle != "") ctx.stroke();
                    var markers = this.getMarkers();
                    if (markers != null) {
                        if (this.style("marker-start").isUrlDefinition()) {
                            var marker = this.style("marker-start").getDefinition();
                            marker.render(ctx, markers[0][0], markers[0][1]);
                        }
                        if (this.style("marker-mid").isUrlDefinition()) {
                            var marker = this.style("marker-mid").getDefinition();
                            for (var i = 1; i < markers.length - 1; i++) {
                                marker.render(ctx, markers[i][0], markers[i][1]);
                            }
                        }
                        if (this.style("marker-end").isUrlDefinition()) {
                            var marker = this.style("marker-end").getDefinition();
                            marker.render(ctx, markers[markers.length - 1][0], markers[markers.length - 1][1]);
                        }
                    }
                };
                this.getBoundingBox = function() {
                    return this.path();
                };
                this.getMarkers = function() {
                    return null;
                };
            };
            svg.Element.PathElementBase.prototype = new svg.Element.RenderedElementBase();
            svg.Element.svg = function(node) {
                this.base = svg.Element.RenderedElementBase;
                this.base(node);
                this.baseClearContext = this.clearContext;
                this.clearContext = function(ctx) {
                    this.baseClearContext(ctx);
                    svg.ViewPort.RemoveCurrent();
                };
                this.baseSetContext = this.setContext;
                this.setContext = function(ctx) {
                    ctx.strokeStyle = "rgba(0,0,0,0)";
                    ctx.lineCap = "butt";
                    ctx.lineJoin = "miter";
                    ctx.miterLimit = 4;
                    this.baseSetContext(ctx);
                    if (!this.attribute("x").hasValue()) this.attribute("x", true).value = 0;
                    if (!this.attribute("y").hasValue()) this.attribute("y", true).value = 0;
                    ctx.translate(this.attribute("x").toPixels("x"), this.attribute("y").toPixels("y"));
                    var width = svg.ViewPort.width();
                    var height = svg.ViewPort.height();
                    if (!this.attribute("width").hasValue()) this.attribute("width", true).value = "100%";
                    if (!this.attribute("height").hasValue()) this.attribute("height", true).value = "100%";
                    if (typeof this.root == "undefined") {
                        width = this.attribute("width").toPixels("x");
                        height = this.attribute("height").toPixels("y");
                        var x = 0;
                        var y = 0;
                        if (this.attribute("refX").hasValue() && this.attribute("refY").hasValue()) {
                            x = -this.attribute("refX").toPixels("x");
                            y = -this.attribute("refY").toPixels("y");
                        }
                        ctx.beginPath();
                        ctx.moveTo(x, y);
                        ctx.lineTo(width, y);
                        ctx.lineTo(width, height);
                        ctx.lineTo(x, height);
                        ctx.closePath();
                        ctx.clip();
                    }
                    svg.ViewPort.SetCurrent(width, height);
                    if (this.attribute("viewBox").hasValue()) {
                        var viewBox = svg.ToNumberArray(this.attribute("viewBox").value);
                        var minX = viewBox[0];
                        var minY = viewBox[1];
                        width = viewBox[2];
                        height = viewBox[3];
                        svg.AspectRatio(ctx, this.attribute("preserveAspectRatio").value, svg.ViewPort.width(), width, svg.ViewPort.height(), height, minX, minY, this.attribute("refX").value, this.attribute("refY").value);
                        svg.ViewPort.RemoveCurrent();
                        svg.ViewPort.SetCurrent(viewBox[2], viewBox[3]);
                    }
                };
            };
            svg.Element.svg.prototype = new svg.Element.RenderedElementBase();
            svg.Element.rect = function(node) {
                this.base = svg.Element.PathElementBase;
                this.base(node);
                this.path = function(ctx) {
                    var x = this.attribute("x").toPixels("x");
                    var y = this.attribute("y").toPixels("y");
                    var width = this.attribute("width").toPixels("x");
                    var height = this.attribute("height").toPixels("y");
                    var rx = this.attribute("rx").toPixels("x");
                    var ry = this.attribute("ry").toPixels("y");
                    if (this.attribute("rx").hasValue() && !this.attribute("ry").hasValue()) ry = rx;
                    if (this.attribute("ry").hasValue() && !this.attribute("rx").hasValue()) rx = ry;
                    rx = Math.min(rx, width / 2);
                    ry = Math.min(ry, height / 2);
                    if (ctx != null) {
                        ctx.beginPath();
                        ctx.moveTo(x + rx, y);
                        ctx.lineTo(x + width - rx, y);
                        ctx.quadraticCurveTo(x + width, y, x + width, y + ry);
                        ctx.lineTo(x + width, y + height - ry);
                        ctx.quadraticCurveTo(x + width, y + height, x + width - rx, y + height);
                        ctx.lineTo(x + rx, y + height);
                        ctx.quadraticCurveTo(x, y + height, x, y + height - ry);
                        ctx.lineTo(x, y + ry);
                        ctx.quadraticCurveTo(x, y, x + rx, y);
                        ctx.closePath();
                    }
                    return new svg.BoundingBox(x, y, x + width, y + height);
                };
            };
            svg.Element.rect.prototype = new svg.Element.PathElementBase();
            svg.Element.circle = function(node) {
                this.base = svg.Element.PathElementBase;
                this.base(node);
                this.path = function(ctx) {
                    var cx = this.attribute("cx").toPixels("x");
                    var cy = this.attribute("cy").toPixels("y");
                    var r = this.attribute("r").toPixels();
                    if (ctx != null) {
                        ctx.beginPath();
                        ctx.arc(cx, cy, r, 0, Math.PI * 2, true);
                        ctx.closePath();
                    }
                    return new svg.BoundingBox(cx - r, cy - r, cx + r, cy + r);
                };
            };
            svg.Element.circle.prototype = new svg.Element.PathElementBase();
            svg.Element.ellipse = function(node) {
                this.base = svg.Element.PathElementBase;
                this.base(node);
                this.path = function(ctx) {
                    var KAPPA = 4 * ((Math.sqrt(2) - 1) / 3);
                    var rx = this.attribute("rx").toPixels("x");
                    var ry = this.attribute("ry").toPixels("y");
                    var cx = this.attribute("cx").toPixels("x");
                    var cy = this.attribute("cy").toPixels("y");
                    if (ctx != null) {
                        ctx.beginPath();
                        ctx.moveTo(cx, cy - ry);
                        ctx.bezierCurveTo(cx + KAPPA * rx, cy - ry, cx + rx, cy - KAPPA * ry, cx + rx, cy);
                        ctx.bezierCurveTo(cx + rx, cy + KAPPA * ry, cx + KAPPA * rx, cy + ry, cx, cy + ry);
                        ctx.bezierCurveTo(cx - KAPPA * rx, cy + ry, cx - rx, cy + KAPPA * ry, cx - rx, cy);
                        ctx.bezierCurveTo(cx - rx, cy - KAPPA * ry, cx - KAPPA * rx, cy - ry, cx, cy - ry);
                        ctx.closePath();
                    }
                    return new svg.BoundingBox(cx - rx, cy - ry, cx + rx, cy + ry);
                };
            };
            svg.Element.ellipse.prototype = new svg.Element.PathElementBase();
            svg.Element.line = function(node) {
                this.base = svg.Element.PathElementBase;
                this.base(node);
                this.getPoints = function() {
                    return [ new svg.Point(this.attribute("x1").toPixels("x"), this.attribute("y1").toPixels("y")), new svg.Point(this.attribute("x2").toPixels("x"), this.attribute("y2").toPixels("y")) ];
                };
                this.path = function(ctx) {
                    var points = this.getPoints();
                    if (ctx != null) {
                        ctx.beginPath();
                        ctx.moveTo(points[0].x, points[0].y);
                        ctx.lineTo(points[1].x, points[1].y);
                    }
                    return new svg.BoundingBox(points[0].x, points[0].y, points[1].x, points[1].y);
                };
                this.getMarkers = function() {
                    var points = this.getPoints();
                    var a = points[0].angleTo(points[1]);
                    return [ [ points[0], a ], [ points[1], a ] ];
                };
            };
            svg.Element.line.prototype = new svg.Element.PathElementBase();
            svg.Element.polyline = function(node) {
                this.base = svg.Element.PathElementBase;
                this.base(node);
                this.points = svg.CreatePath(this.attribute("points").value);
                this.path = function(ctx) {
                    var bb = new svg.BoundingBox(this.points[0].x, this.points[0].y);
                    if (ctx != null) {
                        ctx.beginPath();
                        ctx.moveTo(this.points[0].x, this.points[0].y);
                    }
                    for (var i = 1; i < this.points.length; i++) {
                        bb.addPoint(this.points[i].x, this.points[i].y);
                        if (ctx != null) ctx.lineTo(this.points[i].x, this.points[i].y);
                    }
                    return bb;
                };
                this.getMarkers = function() {
                    var markers = [];
                    for (var i = 0; i < this.points.length - 1; i++) {
                        markers.push([ this.points[i], this.points[i].angleTo(this.points[i + 1]) ]);
                    }
                    markers.push([ this.points[this.points.length - 1], markers[markers.length - 1][1] ]);
                    return markers;
                };
            };
            svg.Element.polyline.prototype = new svg.Element.PathElementBase();
            svg.Element.polygon = function(node) {
                this.base = svg.Element.polyline;
                this.base(node);
                this.basePath = this.path;
                this.path = function(ctx) {
                    var bb = this.basePath(ctx);
                    if (ctx != null) {
                        ctx.lineTo(this.points[0].x, this.points[0].y);
                        ctx.closePath();
                    }
                    return bb;
                };
            };
            svg.Element.polygon.prototype = new svg.Element.polyline();
            svg.Element.path = function(node) {
                this.base = svg.Element.PathElementBase;
                this.base(node);
                var d = this.attribute("d").value;
                d = d.replace(/,/gm, " ");
                d = d.replace(/([MmZzLlHhVvCcSsQqTtAa])([MmZzLlHhVvCcSsQqTtAa])/gm, "$1 $2");
                d = d.replace(/([MmZzLlHhVvCcSsQqTtAa])([MmZzLlHhVvCcSsQqTtAa])/gm, "$1 $2");
                d = d.replace(/([MmZzLlHhVvCcSsQqTtAa])([^\s])/gm, "$1 $2");
                d = d.replace(/([^\s])([MmZzLlHhVvCcSsQqTtAa])/gm, "$1 $2");
                d = d.replace(/([0-9])([+\-])/gm, "$1 $2");
                d = d.replace(/(\.[0-9]*)(\.)/gm, "$1 $2");
                d = d.replace(/([Aa](\s+[0-9]+){3})\s+([01])\s*([01])/gm, "$1 $3 $4 ");
                d = svg.compressSpaces(d);
                d = svg.trim(d);
                this.PathParser = new function(d) {
                    this.tokens = d.split(" ");
                    this.reset = function() {
                        this.i = -1;
                        this.command = "";
                        this.previousCommand = "";
                        this.start = new svg.Point(0, 0);
                        this.control = new svg.Point(0, 0);
                        this.current = new svg.Point(0, 0);
                        this.points = [];
                        this.angles = [];
                    };
                    this.isEnd = function() {
                        return this.i >= this.tokens.length - 1;
                    };
                    this.isCommandOrEnd = function() {
                        if (this.isEnd()) return true;
                        return this.tokens[this.i + 1].match(/^[A-Za-z]$/) != null;
                    };
                    this.isRelativeCommand = function() {
                        switch (this.command) {
                          case "m":
                          case "l":
                          case "h":
                          case "v":
                          case "c":
                          case "s":
                          case "q":
                          case "t":
                          case "a":
                          case "z":
                            return true;
                            break;
                        }
                        return false;
                    };
                    this.getToken = function() {
                        this.i++;
                        return this.tokens[this.i];
                    };
                    this.getScalar = function() {
                        return parseFloat(this.getToken());
                    };
                    this.nextCommand = function() {
                        this.previousCommand = this.command;
                        this.command = this.getToken();
                    };
                    this.getPoint = function() {
                        var p = new svg.Point(this.getScalar(), this.getScalar());
                        return this.makeAbsolute(p);
                    };
                    this.getAsControlPoint = function() {
                        var p = this.getPoint();
                        this.control = p;
                        return p;
                    };
                    this.getAsCurrentPoint = function() {
                        var p = this.getPoint();
                        this.current = p;
                        return p;
                    };
                    this.getReflectedControlPoint = function() {
                        if (this.previousCommand.toLowerCase() != "c" && this.previousCommand.toLowerCase() != "s" && this.previousCommand.toLowerCase() != "q" && this.previousCommand.toLowerCase() != "t") {
                            return this.current;
                        }
                        var p = new svg.Point(2 * this.current.x - this.control.x, 2 * this.current.y - this.control.y);
                        return p;
                    };
                    this.makeAbsolute = function(p) {
                        if (this.isRelativeCommand()) {
                            p.x += this.current.x;
                            p.y += this.current.y;
                        }
                        return p;
                    };
                    this.addMarker = function(p, from, priorTo) {
                        if (priorTo != null && this.angles.length > 0 && this.angles[this.angles.length - 1] == null) {
                            this.angles[this.angles.length - 1] = this.points[this.points.length - 1].angleTo(priorTo);
                        }
                        this.addMarkerAngle(p, from == null ? null : from.angleTo(p));
                    };
                    this.addMarkerAngle = function(p, a) {
                        this.points.push(p);
                        this.angles.push(a);
                    };
                    this.getMarkerPoints = function() {
                        return this.points;
                    };
                    this.getMarkerAngles = function() {
                        for (var i = 0; i < this.angles.length; i++) {
                            if (this.angles[i] == null) {
                                for (var j = i + 1; j < this.angles.length; j++) {
                                    if (this.angles[j] != null) {
                                        this.angles[i] = this.angles[j];
                                        break;
                                    }
                                }
                            }
                        }
                        return this.angles;
                    };
                }(d);
                this.path = function(ctx) {
                    var pp = this.PathParser;
                    pp.reset();
                    var bb = new svg.BoundingBox();
                    if (ctx != null) ctx.beginPath();
                    while (!pp.isEnd()) {
                        pp.nextCommand();
                        switch (pp.command) {
                          case "M":
                          case "m":
                            var p = pp.getAsCurrentPoint();
                            pp.addMarker(p);
                            bb.addPoint(p.x, p.y);
                            if (ctx != null) ctx.moveTo(p.x, p.y);
                            pp.start = pp.current;
                            while (!pp.isCommandOrEnd()) {
                                var p = pp.getAsCurrentPoint();
                                pp.addMarker(p, pp.start);
                                bb.addPoint(p.x, p.y);
                                if (ctx != null) ctx.lineTo(p.x, p.y);
                            }
                            break;

                          case "L":
                          case "l":
                            while (!pp.isCommandOrEnd()) {
                                var c = pp.current;
                                var p = pp.getAsCurrentPoint();
                                pp.addMarker(p, c);
                                bb.addPoint(p.x, p.y);
                                if (ctx != null) ctx.lineTo(p.x, p.y);
                            }
                            break;

                          case "H":
                          case "h":
                            while (!pp.isCommandOrEnd()) {
                                var newP = new svg.Point((pp.isRelativeCommand() ? pp.current.x : 0) + pp.getScalar(), pp.current.y);
                                pp.addMarker(newP, pp.current);
                                pp.current = newP;
                                bb.addPoint(pp.current.x, pp.current.y);
                                if (ctx != null) ctx.lineTo(pp.current.x, pp.current.y);
                            }
                            break;

                          case "V":
                          case "v":
                            while (!pp.isCommandOrEnd()) {
                                var newP = new svg.Point(pp.current.x, (pp.isRelativeCommand() ? pp.current.y : 0) + pp.getScalar());
                                pp.addMarker(newP, pp.current);
                                pp.current = newP;
                                bb.addPoint(pp.current.x, pp.current.y);
                                if (ctx != null) ctx.lineTo(pp.current.x, pp.current.y);
                            }
                            break;

                          case "C":
                          case "c":
                            while (!pp.isCommandOrEnd()) {
                                var curr = pp.current;
                                var p1 = pp.getPoint();
                                var cntrl = pp.getAsControlPoint();
                                var cp = pp.getAsCurrentPoint();
                                pp.addMarker(cp, cntrl, p1);
                                bb.addBezierCurve(curr.x, curr.y, p1.x, p1.y, cntrl.x, cntrl.y, cp.x, cp.y);
                                if (ctx != null) ctx.bezierCurveTo(p1.x, p1.y, cntrl.x, cntrl.y, cp.x, cp.y);
                            }
                            break;

                          case "S":
                          case "s":
                            while (!pp.isCommandOrEnd()) {
                                var curr = pp.current;
                                var p1 = pp.getReflectedControlPoint();
                                var cntrl = pp.getAsControlPoint();
                                var cp = pp.getAsCurrentPoint();
                                pp.addMarker(cp, cntrl, p1);
                                bb.addBezierCurve(curr.x, curr.y, p1.x, p1.y, cntrl.x, cntrl.y, cp.x, cp.y);
                                if (ctx != null) ctx.bezierCurveTo(p1.x, p1.y, cntrl.x, cntrl.y, cp.x, cp.y);
                            }
                            break;

                          case "Q":
                          case "q":
                            while (!pp.isCommandOrEnd()) {
                                var curr = pp.current;
                                var cntrl = pp.getAsControlPoint();
                                var cp = pp.getAsCurrentPoint();
                                pp.addMarker(cp, cntrl, cntrl);
                                bb.addQuadraticCurve(curr.x, curr.y, cntrl.x, cntrl.y, cp.x, cp.y);
                                if (ctx != null) ctx.quadraticCurveTo(cntrl.x, cntrl.y, cp.x, cp.y);
                            }
                            break;

                          case "T":
                          case "t":
                            while (!pp.isCommandOrEnd()) {
                                var curr = pp.current;
                                var cntrl = pp.getReflectedControlPoint();
                                pp.control = cntrl;
                                var cp = pp.getAsCurrentPoint();
                                pp.addMarker(cp, cntrl, cntrl);
                                bb.addQuadraticCurve(curr.x, curr.y, cntrl.x, cntrl.y, cp.x, cp.y);
                                if (ctx != null) ctx.quadraticCurveTo(cntrl.x, cntrl.y, cp.x, cp.y);
                            }
                            break;

                          case "A":
                          case "a":
                            while (!pp.isCommandOrEnd()) {
                                var curr = pp.current;
                                var rx = pp.getScalar();
                                var ry = pp.getScalar();
                                var xAxisRotation = pp.getScalar() * (Math.PI / 180);
                                var largeArcFlag = pp.getScalar();
                                var sweepFlag = pp.getScalar();
                                var cp = pp.getAsCurrentPoint();
                                var currp = new svg.Point(Math.cos(xAxisRotation) * (curr.x - cp.x) / 2 + Math.sin(xAxisRotation) * (curr.y - cp.y) / 2, -Math.sin(xAxisRotation) * (curr.x - cp.x) / 2 + Math.cos(xAxisRotation) * (curr.y - cp.y) / 2);
                                var l = Math.pow(currp.x, 2) / Math.pow(rx, 2) + Math.pow(currp.y, 2) / Math.pow(ry, 2);
                                if (l > 1) {
                                    rx *= Math.sqrt(l);
                                    ry *= Math.sqrt(l);
                                }
                                var s = (largeArcFlag == sweepFlag ? -1 : 1) * Math.sqrt((Math.pow(rx, 2) * Math.pow(ry, 2) - Math.pow(rx, 2) * Math.pow(currp.y, 2) - Math.pow(ry, 2) * Math.pow(currp.x, 2)) / (Math.pow(rx, 2) * Math.pow(currp.y, 2) + Math.pow(ry, 2) * Math.pow(currp.x, 2)));
                                if (isNaN(s)) s = 0;
                                var cpp = new svg.Point(s * rx * currp.y / ry, s * -ry * currp.x / rx);
                                var centp = new svg.Point((curr.x + cp.x) / 2 + Math.cos(xAxisRotation) * cpp.x - Math.sin(xAxisRotation) * cpp.y, (curr.y + cp.y) / 2 + Math.sin(xAxisRotation) * cpp.x + Math.cos(xAxisRotation) * cpp.y);
                                var m = function(v) {
                                    return Math.sqrt(Math.pow(v[0], 2) + Math.pow(v[1], 2));
                                };
                                var r = function(u, v) {
                                    return (u[0] * v[0] + u[1] * v[1]) / (m(u) * m(v));
                                };
                                var a = function(u, v) {
                                    return (u[0] * v[1] < u[1] * v[0] ? -1 : 1) * Math.acos(r(u, v));
                                };
                                var a1 = a([ 1, 0 ], [ (currp.x - cpp.x) / rx, (currp.y - cpp.y) / ry ]);
                                var u = [ (currp.x - cpp.x) / rx, (currp.y - cpp.y) / ry ];
                                var v = [ (-currp.x - cpp.x) / rx, (-currp.y - cpp.y) / ry ];
                                var ad = a(u, v);
                                if (r(u, v) <= -1) ad = Math.PI;
                                if (r(u, v) >= 1) ad = 0;
                                var dir = 1 - sweepFlag ? 1 : -1;
                                var ah = a1 + dir * (ad / 2);
                                var halfWay = new svg.Point(centp.x + rx * Math.cos(ah), centp.y + ry * Math.sin(ah));
                                pp.addMarkerAngle(halfWay, ah - dir * Math.PI / 2);
                                pp.addMarkerAngle(cp, ah - dir * Math.PI);
                                bb.addPoint(cp.x, cp.y);
                                if (ctx != null) {
                                    var r = rx > ry ? rx : ry;
                                    var sx = rx > ry ? 1 : rx / ry;
                                    var sy = rx > ry ? ry / rx : 1;
                                    ctx.translate(centp.x, centp.y);
                                    ctx.rotate(xAxisRotation);
                                    ctx.scale(sx, sy);
                                    ctx.arc(0, 0, r, a1, a1 + ad, 1 - sweepFlag);
                                    ctx.scale(1 / sx, 1 / sy);
                                    ctx.rotate(-xAxisRotation);
                                    ctx.translate(-centp.x, -centp.y);
                                }
                            }
                            break;

                          case "Z":
                          case "z":
                            if (ctx != null) ctx.closePath();
                            pp.current = pp.start;
                        }
                    }
                    return bb;
                };
                this.getMarkers = function() {
                    var points = this.PathParser.getMarkerPoints();
                    var angles = this.PathParser.getMarkerAngles();
                    var markers = [];
                    for (var i = 0; i < points.length; i++) {
                        markers.push([ points[i], angles[i] ]);
                    }
                    return markers;
                };
            };
            svg.Element.path.prototype = new svg.Element.PathElementBase();
            svg.Element.pattern = function(node) {
                this.base = svg.Element.ElementBase;
                this.base(node);
                this.createPattern = function(ctx, element) {
                    var width = this.attribute("width").toPixels("x", true);
                    var height = this.attribute("height").toPixels("y", true);
                    var tempSvg = new svg.Element.svg();
                    tempSvg.attributes["viewBox"] = new svg.Property("viewBox", this.attribute("viewBox").value);
                    tempSvg.attributes["width"] = new svg.Property("width", width + "px");
                    tempSvg.attributes["height"] = new svg.Property("height", height + "px");
                    tempSvg.attributes["transform"] = new svg.Property("transform", this.attribute("patternTransform").value);
                    tempSvg.children = this.children;
                    var c = document.createElement("canvas");
                    c.width = width;
                    c.height = height;
                    var cctx = c.getContext("2d");
                    if (this.attribute("x").hasValue() && this.attribute("y").hasValue()) {
                        cctx.translate(this.attribute("x").toPixels("x", true), this.attribute("y").toPixels("y", true));
                    }
                    for (var x = -1; x <= 1; x++) {
                        for (var y = -1; y <= 1; y++) {
                            cctx.save();
                            cctx.translate(x * c.width, y * c.height);
                            tempSvg.render(cctx);
                            cctx.restore();
                        }
                    }
                    var pattern = ctx.createPattern(c, "repeat");
                    return pattern;
                };
            };
            svg.Element.pattern.prototype = new svg.Element.ElementBase();
            svg.Element.marker = function(node) {
                this.base = svg.Element.ElementBase;
                this.base(node);
                this.baseRender = this.render;
                this.render = function(ctx, point, angle) {
                    ctx.translate(point.x, point.y);
                    if (this.attribute("orient").valueOrDefault("auto") == "auto") ctx.rotate(angle);
                    if (this.attribute("markerUnits").valueOrDefault("strokeWidth") == "strokeWidth") ctx.scale(ctx.lineWidth, ctx.lineWidth);
                    ctx.save();
                    var tempSvg = new svg.Element.svg();
                    tempSvg.attributes["viewBox"] = new svg.Property("viewBox", this.attribute("viewBox").value);
                    tempSvg.attributes["refX"] = new svg.Property("refX", this.attribute("refX").value);
                    tempSvg.attributes["refY"] = new svg.Property("refY", this.attribute("refY").value);
                    tempSvg.attributes["width"] = new svg.Property("width", this.attribute("markerWidth").value);
                    tempSvg.attributes["height"] = new svg.Property("height", this.attribute("markerHeight").value);
                    tempSvg.attributes["fill"] = new svg.Property("fill", this.attribute("fill").valueOrDefault("black"));
                    tempSvg.attributes["stroke"] = new svg.Property("stroke", this.attribute("stroke").valueOrDefault("none"));
                    tempSvg.children = this.children;
                    tempSvg.render(ctx);
                    ctx.restore();
                    if (this.attribute("markerUnits").valueOrDefault("strokeWidth") == "strokeWidth") ctx.scale(1 / ctx.lineWidth, 1 / ctx.lineWidth);
                    if (this.attribute("orient").valueOrDefault("auto") == "auto") ctx.rotate(-angle);
                    ctx.translate(-point.x, -point.y);
                };
            };
            svg.Element.marker.prototype = new svg.Element.ElementBase();
            svg.Element.defs = function(node) {
                this.base = svg.Element.ElementBase;
                this.base(node);
                this.render = function(ctx) {};
            };
            svg.Element.defs.prototype = new svg.Element.ElementBase();
            svg.Element.GradientBase = function(node) {
                this.base = svg.Element.ElementBase;
                this.base(node);
                this.gradientUnits = this.attribute("gradientUnits").valueOrDefault("objectBoundingBox");
                this.stops = [];
                for (var i = 0; i < this.children.length; i++) {
                    var child = this.children[i];
                    if (child.type == "stop") this.stops.push(child);
                }
                this.getGradient = function() {};
                this.createGradient = function(ctx, element, parentOpacityProp) {
                    var stopsContainer = this;
                    if (this.getHrefAttribute().hasValue()) {
                        stopsContainer = this.getHrefAttribute().getDefinition();
                    }
                    var addParentOpacity = function(color) {
                        if (parentOpacityProp.hasValue()) {
                            var p = new svg.Property("color", color);
                            return p.addOpacity(parentOpacityProp.value).value;
                        }
                        return color;
                    };
                    var g = this.getGradient(ctx, element);
                    if (g == null) return addParentOpacity(stopsContainer.stops[stopsContainer.stops.length - 1].color);
                    for (var i = 0; i < stopsContainer.stops.length; i++) {
                        g.addColorStop(stopsContainer.stops[i].offset, addParentOpacity(stopsContainer.stops[i].color));
                    }
                    if (this.attribute("gradientTransform").hasValue()) {
                        var rootView = svg.ViewPort.viewPorts[0];
                        var rect = new svg.Element.rect();
                        rect.attributes["x"] = new svg.Property("x", -svg.MAX_VIRTUAL_PIXELS / 3);
                        rect.attributes["y"] = new svg.Property("y", -svg.MAX_VIRTUAL_PIXELS / 3);
                        rect.attributes["width"] = new svg.Property("width", svg.MAX_VIRTUAL_PIXELS);
                        rect.attributes["height"] = new svg.Property("height", svg.MAX_VIRTUAL_PIXELS);
                        var group = new svg.Element.g();
                        group.attributes["transform"] = new svg.Property("transform", this.attribute("gradientTransform").value);
                        group.children = [ rect ];
                        var tempSvg = new svg.Element.svg();
                        tempSvg.attributes["x"] = new svg.Property("x", 0);
                        tempSvg.attributes["y"] = new svg.Property("y", 0);
                        tempSvg.attributes["width"] = new svg.Property("width", rootView.width);
                        tempSvg.attributes["height"] = new svg.Property("height", rootView.height);
                        tempSvg.children = [ group ];
                        var c = document.createElement("canvas");
                        c.width = rootView.width;
                        c.height = rootView.height;
                        var tempCtx = c.getContext("2d");
                        tempCtx.fillStyle = g;
                        tempSvg.render(tempCtx);
                        return tempCtx.createPattern(c, "no-repeat");
                    }
                    return g;
                };
            };
            svg.Element.GradientBase.prototype = new svg.Element.ElementBase();
            svg.Element.linearGradient = function(node) {
                this.base = svg.Element.GradientBase;
                this.base(node);
                this.getGradient = function(ctx, element) {
                    var bb = element.getBoundingBox();
                    if (!this.attribute("x1").hasValue() && !this.attribute("y1").hasValue() && !this.attribute("x2").hasValue() && !this.attribute("y2").hasValue()) {
                        this.attribute("x1", true).value = 0;
                        this.attribute("y1", true).value = 0;
                        this.attribute("x2", true).value = 1;
                        this.attribute("y2", true).value = 0;
                    }
                    var x1 = this.gradientUnits == "objectBoundingBox" ? bb.x() + bb.width() * this.attribute("x1").numValue() : this.attribute("x1").toPixels("x");
                    var y1 = this.gradientUnits == "objectBoundingBox" ? bb.y() + bb.height() * this.attribute("y1").numValue() : this.attribute("y1").toPixels("y");
                    var x2 = this.gradientUnits == "objectBoundingBox" ? bb.x() + bb.width() * this.attribute("x2").numValue() : this.attribute("x2").toPixels("x");
                    var y2 = this.gradientUnits == "objectBoundingBox" ? bb.y() + bb.height() * this.attribute("y2").numValue() : this.attribute("y2").toPixels("y");
                    if (x1 == x2 && y1 == y2) return null;
                    return ctx.createLinearGradient(x1, y1, x2, y2);
                };
            };
            svg.Element.linearGradient.prototype = new svg.Element.GradientBase();
            svg.Element.radialGradient = function(node) {
                this.base = svg.Element.GradientBase;
                this.base(node);
                this.getGradient = function(ctx, element) {
                    var bb = element.getBoundingBox();
                    if (!this.attribute("cx").hasValue()) this.attribute("cx", true).value = "50%";
                    if (!this.attribute("cy").hasValue()) this.attribute("cy", true).value = "50%";
                    if (!this.attribute("r").hasValue()) this.attribute("r", true).value = "50%";
                    var cx = this.gradientUnits == "objectBoundingBox" ? bb.x() + bb.width() * this.attribute("cx").numValue() : this.attribute("cx").toPixels("x");
                    var cy = this.gradientUnits == "objectBoundingBox" ? bb.y() + bb.height() * this.attribute("cy").numValue() : this.attribute("cy").toPixels("y");
                    var fx = cx;
                    var fy = cy;
                    if (this.attribute("fx").hasValue()) {
                        fx = this.gradientUnits == "objectBoundingBox" ? bb.x() + bb.width() * this.attribute("fx").numValue() : this.attribute("fx").toPixels("x");
                    }
                    if (this.attribute("fy").hasValue()) {
                        fy = this.gradientUnits == "objectBoundingBox" ? bb.y() + bb.height() * this.attribute("fy").numValue() : this.attribute("fy").toPixels("y");
                    }
                    var r = this.gradientUnits == "objectBoundingBox" ? (bb.width() + bb.height()) / 2 * this.attribute("r").numValue() : this.attribute("r").toPixels();
                    return ctx.createRadialGradient(fx, fy, 0, cx, cy, r);
                };
            };
            svg.Element.radialGradient.prototype = new svg.Element.GradientBase();
            svg.Element.stop = function(node) {
                this.base = svg.Element.ElementBase;
                this.base(node);
                this.offset = this.attribute("offset").numValue();
                if (this.offset < 0) this.offset = 0;
                if (this.offset > 1) this.offset = 1;
                var stopColor = this.style("stop-color");
                if (this.style("stop-opacity").hasValue()) stopColor = stopColor.addOpacity(this.style("stop-opacity").value);
                this.color = stopColor.value;
            };
            svg.Element.stop.prototype = new svg.Element.ElementBase();
            svg.Element.AnimateBase = function(node) {
                this.base = svg.Element.ElementBase;
                this.base(node);
                svg.Animations.push(this);
                this.duration = 0;
                this.begin = this.attribute("begin").toMilliseconds();
                this.maxDuration = this.begin + this.attribute("dur").toMilliseconds();
                this.getProperty = function() {
                    var attributeType = this.attribute("attributeType").value;
                    var attributeName = this.attribute("attributeName").value;
                    if (attributeType == "CSS") {
                        return this.parent.style(attributeName, true);
                    }
                    return this.parent.attribute(attributeName, true);
                };
                this.initialValue = null;
                this.initialUnits = "";
                this.removed = false;
                this.calcValue = function() {
                    return "";
                };
                this.update = function(delta) {
                    if (this.initialValue == null) {
                        this.initialValue = this.getProperty().value;
                        this.initialUnits = this.getProperty().getUnits();
                    }
                    if (this.duration > this.maxDuration) {
                        if (this.attribute("repeatCount").value == "indefinite" || this.attribute("repeatDur").value == "indefinite") {
                            this.duration = 0;
                        } else if (this.attribute("fill").valueOrDefault("remove") == "remove" && !this.removed) {
                            this.removed = true;
                            this.getProperty().value = this.initialValue;
                            return true;
                        } else {
                            return false;
                        }
                    }
                    this.duration = this.duration + delta;
                    var updated = false;
                    if (this.begin < this.duration) {
                        var newValue = this.calcValue();
                        if (this.attribute("type").hasValue()) {
                            var type = this.attribute("type").value;
                            newValue = type + "(" + newValue + ")";
                        }
                        this.getProperty().value = newValue;
                        updated = true;
                    }
                    return updated;
                };
                this.from = this.attribute("from");
                this.to = this.attribute("to");
                this.values = this.attribute("values");
                if (this.values.hasValue()) this.values.value = this.values.value.split(";");
                this.progress = function() {
                    var ret = {
                        progress: (this.duration - this.begin) / (this.maxDuration - this.begin)
                    };
                    if (this.values.hasValue()) {
                        var p = ret.progress * (this.values.value.length - 1);
                        var lb = Math.floor(p), ub = Math.ceil(p);
                        ret.from = new svg.Property("from", parseFloat(this.values.value[lb]));
                        ret.to = new svg.Property("to", parseFloat(this.values.value[ub]));
                        ret.progress = (p - lb) / (ub - lb);
                    } else {
                        ret.from = this.from;
                        ret.to = this.to;
                    }
                    return ret;
                };
            };
            svg.Element.AnimateBase.prototype = new svg.Element.ElementBase();
            svg.Element.animate = function(node) {
                this.base = svg.Element.AnimateBase;
                this.base(node);
                this.calcValue = function() {
                    var p = this.progress();
                    var newValue = p.from.numValue() + (p.to.numValue() - p.from.numValue()) * p.progress;
                    return newValue + this.initialUnits;
                };
            };
            svg.Element.animate.prototype = new svg.Element.AnimateBase();
            svg.Element.animateColor = function(node) {
                this.base = svg.Element.AnimateBase;
                this.base(node);
                this.calcValue = function() {
                    var p = this.progress();
                    var from = new RGBColor(p.from.value);
                    var to = new RGBColor(p.to.value);
                    if (from.ok && to.ok) {
                        var r = from.r + (to.r - from.r) * p.progress;
                        var g = from.g + (to.g - from.g) * p.progress;
                        var b = from.b + (to.b - from.b) * p.progress;
                        return "rgb(" + parseInt(r, 10) + "," + parseInt(g, 10) + "," + parseInt(b, 10) + ")";
                    }
                    return this.attribute("from").value;
                };
            };
            svg.Element.animateColor.prototype = new svg.Element.AnimateBase();
            svg.Element.animateTransform = function(node) {
                this.base = svg.Element.AnimateBase;
                this.base(node);
                this.calcValue = function() {
                    var p = this.progress();
                    var from = svg.ToNumberArray(p.from.value);
                    var to = svg.ToNumberArray(p.to.value);
                    var newValue = "";
                    for (var i = 0; i < from.length; i++) {
                        newValue += from[i] + (to[i] - from[i]) * p.progress + " ";
                    }
                    return newValue;
                };
            };
            svg.Element.animateTransform.prototype = new svg.Element.animate();
            svg.Element.font = function(node) {
                this.base = svg.Element.ElementBase;
                this.base(node);
                this.horizAdvX = this.attribute("horiz-adv-x").numValue();
                this.isRTL = false;
                this.isArabic = false;
                this.fontFace = null;
                this.missingGlyph = null;
                this.glyphs = [];
                for (var i = 0; i < this.children.length; i++) {
                    var child = this.children[i];
                    if (child.type == "font-face") {
                        this.fontFace = child;
                        if (child.style("font-family").hasValue()) {
                            svg.Definitions[child.style("font-family").value] = this;
                        }
                    } else if (child.type == "missing-glyph") this.missingGlyph = child; else if (child.type == "glyph") {
                        if (child.arabicForm != "") {
                            this.isRTL = true;
                            this.isArabic = true;
                            if (typeof this.glyphs[child.unicode] == "undefined") this.glyphs[child.unicode] = [];
                            this.glyphs[child.unicode][child.arabicForm] = child;
                        } else {
                            this.glyphs[child.unicode] = child;
                        }
                    }
                }
            };
            svg.Element.font.prototype = new svg.Element.ElementBase();
            svg.Element.fontface = function(node) {
                this.base = svg.Element.ElementBase;
                this.base(node);
                this.ascent = this.attribute("ascent").value;
                this.descent = this.attribute("descent").value;
                this.unitsPerEm = this.attribute("units-per-em").numValue();
            };
            svg.Element.fontface.prototype = new svg.Element.ElementBase();
            svg.Element.missingglyph = function(node) {
                this.base = svg.Element.path;
                this.base(node);
                this.horizAdvX = 0;
            };
            svg.Element.missingglyph.prototype = new svg.Element.path();
            svg.Element.glyph = function(node) {
                this.base = svg.Element.path;
                this.base(node);
                this.horizAdvX = this.attribute("horiz-adv-x").numValue();
                this.unicode = this.attribute("unicode").value;
                this.arabicForm = this.attribute("arabic-form").value;
            };
            svg.Element.glyph.prototype = new svg.Element.path();
            svg.Element.text = function(node) {
                this.captureTextNodes = true;
                this.base = svg.Element.RenderedElementBase;
                this.base(node);
                this.baseSetContext = this.setContext;
                this.setContext = function(ctx) {
                    this.baseSetContext(ctx);
                    if (this.style("dominant-baseline").hasValue()) ctx.textBaseline = this.style("dominant-baseline").value;
                    if (this.style("alignment-baseline").hasValue()) ctx.textBaseline = this.style("alignment-baseline").value;
                };
                this.getBoundingBox = function() {
                    return new svg.BoundingBox(this.attribute("x").toPixels("x"), this.attribute("y").toPixels("y"), 0, 0);
                };
                this.renderChildren = function(ctx) {
                    this.x = this.attribute("x").toPixels("x");
                    this.y = this.attribute("y").toPixels("y");
                    this.x += this.getAnchorDelta(ctx, this, 0);
                    for (var i = 0; i < this.children.length; i++) {
                        this.renderChild(ctx, this, i);
                    }
                };
                this.getAnchorDelta = function(ctx, parent, startI) {
                    var textAnchor = this.style("text-anchor").valueOrDefault("start");
                    if (textAnchor != "start") {
                        var width = 0;
                        for (var i = startI; i < parent.children.length; i++) {
                            var child = parent.children[i];
                            if (i > startI && child.attribute("x").hasValue()) break;
                            width += child.measureTextRecursive(ctx);
                        }
                        return -1 * (textAnchor == "end" ? width : width / 2);
                    }
                    return 0;
                };
                this.renderChild = function(ctx, parent, i) {
                    var child = parent.children[i];
                    if (child.attribute("x").hasValue()) {
                        child.x = child.attribute("x").toPixels("x") + this.getAnchorDelta(ctx, parent, i);
                    } else {
                        if (this.attribute("dx").hasValue()) this.x += this.attribute("dx").toPixels("x");
                        if (child.attribute("dx").hasValue()) this.x += child.attribute("dx").toPixels("x");
                        child.x = this.x;
                    }
                    this.x = child.x + child.measureText(ctx);
                    if (child.attribute("y").hasValue()) {
                        child.y = child.attribute("y").toPixels("y");
                    } else {
                        if (this.attribute("dy").hasValue()) this.y += this.attribute("dy").toPixels("y");
                        if (child.attribute("dy").hasValue()) this.y += child.attribute("dy").toPixels("y");
                        child.y = this.y;
                    }
                    this.y = child.y;
                    child.render(ctx);
                    for (var i = 0; i < child.children.length; i++) {
                        this.renderChild(ctx, child, i);
                    }
                };
            };
            svg.Element.text.prototype = new svg.Element.RenderedElementBase();
            svg.Element.TextElementBase = function(node) {
                this.base = svg.Element.RenderedElementBase;
                this.base(node);
                this.getGlyph = function(font, text, i) {
                    var c = text[i];
                    var glyph = null;
                    if (font.isArabic) {
                        var arabicForm = "isolated";
                        if ((i == 0 || text[i - 1] == " ") && i < text.length - 2 && text[i + 1] != " ") arabicForm = "terminal";
                        if (i > 0 && text[i - 1] != " " && i < text.length - 2 && text[i + 1] != " ") arabicForm = "medial";
                        if (i > 0 && text[i - 1] != " " && (i == text.length - 1 || text[i + 1] == " ")) arabicForm = "initial";
                        if (typeof font.glyphs[c] != "undefined") {
                            glyph = font.glyphs[c][arabicForm];
                            if (glyph == null && font.glyphs[c].type == "glyph") glyph = font.glyphs[c];
                        }
                    } else {
                        glyph = font.glyphs[c];
                    }
                    if (glyph == null) glyph = font.missingGlyph;
                    return glyph;
                };
                this.renderChildren = function(ctx) {
                    var customFont = this.parent.style("font-family").getDefinition();
                    if (customFont != null) {
                        var fontSize = this.parent.style("font-size").numValueOrDefault(svg.Font.Parse(svg.ctx.font).fontSize);
                        var fontStyle = this.parent.style("font-style").valueOrDefault(svg.Font.Parse(svg.ctx.font).fontStyle);
                        var text = this.getText();
                        if (customFont.isRTL) text = text.split("").reverse().join("");
                        var dx = svg.ToNumberArray(this.parent.attribute("dx").value);
                        for (var i = 0; i < text.length; i++) {
                            var glyph = this.getGlyph(customFont, text, i);
                            var scale = fontSize / customFont.fontFace.unitsPerEm;
                            ctx.translate(this.x, this.y);
                            ctx.scale(scale, -scale);
                            var lw = ctx.lineWidth;
                            ctx.lineWidth = ctx.lineWidth * customFont.fontFace.unitsPerEm / fontSize;
                            if (fontStyle == "italic") ctx.transform(1, 0, .4, 1, 0, 0);
                            glyph.render(ctx);
                            if (fontStyle == "italic") ctx.transform(1, 0, -.4, 1, 0, 0);
                            ctx.lineWidth = lw;
                            ctx.scale(1 / scale, -1 / scale);
                            ctx.translate(-this.x, -this.y);
                            this.x += fontSize * (glyph.horizAdvX || customFont.horizAdvX) / customFont.fontFace.unitsPerEm;
                            if (typeof dx[i] != "undefined" && !isNaN(dx[i])) {
                                this.x += dx[i];
                            }
                        }
                        return;
                    }
                    if (ctx.fillStyle != "") ctx.fillText(svg.compressSpaces(this.getText()), this.x, this.y);
                    if (ctx.strokeStyle != "") ctx.strokeText(svg.compressSpaces(this.getText()), this.x, this.y);
                };
                this.getText = function() {};
                this.measureTextRecursive = function(ctx) {
                    var width = this.measureText(ctx);
                    for (var i = 0; i < this.children.length; i++) {
                        width += this.children[i].measureTextRecursive(ctx);
                    }
                    return width;
                };
                this.measureText = function(ctx) {
                    var customFont = this.parent.style("font-family").getDefinition();
                    if (customFont != null) {
                        var fontSize = this.parent.style("font-size").numValueOrDefault(svg.Font.Parse(svg.ctx.font).fontSize);
                        var measure = 0;
                        var text = this.getText();
                        if (customFont.isRTL) text = text.split("").reverse().join("");
                        var dx = svg.ToNumberArray(this.parent.attribute("dx").value);
                        for (var i = 0; i < text.length; i++) {
                            var glyph = this.getGlyph(customFont, text, i);
                            measure += (glyph.horizAdvX || customFont.horizAdvX) * fontSize / customFont.fontFace.unitsPerEm;
                            if (typeof dx[i] != "undefined" && !isNaN(dx[i])) {
                                measure += dx[i];
                            }
                        }
                        return measure;
                    }
                    var textToMeasure = svg.compressSpaces(this.getText());
                    if (!ctx.measureText) return textToMeasure.length * 10;
                    ctx.save();
                    this.setContext(ctx);
                    var width = ctx.measureText(textToMeasure).width;
                    ctx.restore();
                    return width;
                };
            };
            svg.Element.TextElementBase.prototype = new svg.Element.RenderedElementBase();
            svg.Element.tspan = function(node) {
                this.captureTextNodes = true;
                this.base = svg.Element.TextElementBase;
                this.base(node);
                this.text = node.nodeValue || node.text || "";
                this.getText = function() {
                    return this.text;
                };
            };
            svg.Element.tspan.prototype = new svg.Element.TextElementBase();
            svg.Element.tref = function(node) {
                this.base = svg.Element.TextElementBase;
                this.base(node);
                this.getText = function() {
                    var element = this.getHrefAttribute().getDefinition();
                    if (element != null) return element.children[0].getText();
                };
            };
            svg.Element.tref.prototype = new svg.Element.TextElementBase();
            svg.Element.a = function(node) {
                this.base = svg.Element.TextElementBase;
                this.base(node);
                this.hasText = true;
                for (var i = 0; i < node.childNodes.length; i++) {
                    if (node.childNodes[i].nodeType != 3) this.hasText = false;
                }
                this.text = this.hasText ? node.childNodes[0].nodeValue : "";
                this.getText = function() {
                    return this.text;
                };
                this.baseRenderChildren = this.renderChildren;
                this.renderChildren = function(ctx) {
                    if (this.hasText) {
                        this.baseRenderChildren(ctx);
                        var fontSize = new svg.Property("fontSize", svg.Font.Parse(svg.ctx.font).fontSize);
                        svg.Mouse.checkBoundingBox(this, new svg.BoundingBox(this.x, this.y - fontSize.toPixels("y"), this.x + this.measureText(ctx), this.y));
                    } else {
                        var g = new svg.Element.g();
                        g.children = this.children;
                        g.parent = this;
                        g.render(ctx);
                    }
                };
                this.onclick = function() {
                    window.open(this.getHrefAttribute().value);
                };
                this.onmousemove = function() {
                    svg.ctx.canvas.style.cursor = "pointer";
                };
            };
            svg.Element.a.prototype = new svg.Element.TextElementBase();
            svg.Element.image = function(node) {
                this.base = svg.Element.RenderedElementBase;
                this.base(node);
                var href = this.getHrefAttribute().value;
                var isSvg = href.match(/\.svg$/);
                svg.Images.push(this);
                this.loaded = false;
                if (!isSvg) {
                    this.img = document.createElement("img");
                    var self = this;
                    this.img.onload = function() {
                        self.loaded = true;
                    };
                    this.img.onerror = function() {
                        if (typeof console != "undefined") {
                            console.log('ERROR: image "' + href + '" not found');
                            self.loaded = true;
                        }
                    };
                    this.img.src = href;
                } else {
                    this.img = svg.ajax(href);
                    this.loaded = true;
                }
                this.renderChildren = function(ctx) {
                    var x = this.attribute("x").toPixels("x");
                    var y = this.attribute("y").toPixels("y");
                    var width = this.attribute("width").toPixels("x");
                    var height = this.attribute("height").toPixels("y");
                    if (width == 0 || height == 0) return;
                    ctx.save();
                    if (isSvg) {
                        ctx.drawSvg(this.img, x, y, width, height);
                    } else {
                        ctx.translate(x, y);
                        svg.AspectRatio(ctx, this.attribute("preserveAspectRatio").value, width, this.img.width, height, this.img.height, 0, 0);
                        ctx.drawImage(this.img, 0, 0);
                    }
                    ctx.restore();
                };
                this.getBoundingBox = function() {
                    var x = this.attribute("x").toPixels("x");
                    var y = this.attribute("y").toPixels("y");
                    var width = this.attribute("width").toPixels("x");
                    var height = this.attribute("height").toPixels("y");
                    return new svg.BoundingBox(x, y, x + width, y + height);
                };
            };
            svg.Element.image.prototype = new svg.Element.RenderedElementBase();
            svg.Element.g = function(node) {
                this.base = svg.Element.RenderedElementBase;
                this.base(node);
                this.getBoundingBox = function() {
                    var bb = new svg.BoundingBox();
                    for (var i = 0; i < this.children.length; i++) {
                        bb.addBoundingBox(this.children[i].getBoundingBox());
                    }
                    return bb;
                };
            };
            svg.Element.g.prototype = new svg.Element.RenderedElementBase();
            svg.Element.symbol = function(node) {
                this.base = svg.Element.RenderedElementBase;
                this.base(node);
                this.baseSetContext = this.setContext;
                this.setContext = function(ctx) {
                    this.baseSetContext(ctx);
                    if (this.attribute("viewBox").hasValue()) {
                        var viewBox = svg.ToNumberArray(this.attribute("viewBox").value);
                        var minX = viewBox[0];
                        var minY = viewBox[1];
                        width = viewBox[2];
                        height = viewBox[3];
                        svg.AspectRatio(ctx, this.attribute("preserveAspectRatio").value, this.attribute("width").toPixels("x"), width, this.attribute("height").toPixels("y"), height, minX, minY);
                        svg.ViewPort.SetCurrent(viewBox[2], viewBox[3]);
                    }
                };
            };
            svg.Element.symbol.prototype = new svg.Element.RenderedElementBase();
            svg.Element.style = function(node) {
                this.base = svg.Element.ElementBase;
                this.base(node);
                var css = "";
                for (var i = 0; i < node.childNodes.length; i++) {
                    css += node.childNodes[i].nodeValue;
                }
                css = css.replace(/(\/\*([^*]|[\r\n]|(\*+([^*\/]|[\r\n])))*\*+\/)|(^[\s]*\/\/.*)/gm, "");
                css = svg.compressSpaces(css);
                var cssDefs = css.split("}");
                for (var i = 0; i < cssDefs.length; i++) {
                    if (svg.trim(cssDefs[i]) != "") {
                        var cssDef = cssDefs[i].split("{");
                        var cssClasses = cssDef[0].split(",");
                        var cssProps = cssDef[1].split(";");
                        for (var j = 0; j < cssClasses.length; j++) {
                            var cssClass = svg.trim(cssClasses[j]);
                            if (cssClass != "") {
                                var props = {};
                                for (var k = 0; k < cssProps.length; k++) {
                                    var prop = cssProps[k].indexOf(":");
                                    var name = cssProps[k].substr(0, prop);
                                    var value = cssProps[k].substr(prop + 1, cssProps[k].length - prop);
                                    if (name != null && value != null) {
                                        props[svg.trim(name)] = new svg.Property(svg.trim(name), svg.trim(value));
                                    }
                                }
                                svg.Styles[cssClass] = props;
                                if (cssClass == "@font-face") {
                                    var fontFamily = props["font-family"].value.replace(/"/g, "");
                                    var srcs = props["src"].value.split(",");
                                    for (var s = 0; s < srcs.length; s++) {
                                        if (srcs[s].indexOf('format("svg")') > 0) {
                                            var urlStart = srcs[s].indexOf("url");
                                            var urlEnd = srcs[s].indexOf(")", urlStart);
                                            var url = srcs[s].substr(urlStart + 5, urlEnd - urlStart - 6);
                                            var doc = svg.parseXml(svg.ajax(url));
                                            var fonts = doc.getElementsByTagName("font");
                                            for (var f = 0; f < fonts.length; f++) {
                                                var font = svg.CreateElement(fonts[f]);
                                                svg.Definitions[fontFamily] = font;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            };
            svg.Element.style.prototype = new svg.Element.ElementBase();
            svg.Element.use = function(node) {
                this.base = svg.Element.RenderedElementBase;
                this.base(node);
                this.baseSetContext = this.setContext;
                this.setContext = function(ctx) {
                    this.baseSetContext(ctx);
                    if (this.attribute("x").hasValue()) ctx.translate(this.attribute("x").toPixels("x"), 0);
                    if (this.attribute("y").hasValue()) ctx.translate(0, this.attribute("y").toPixels("y"));
                };
                this.getDefinition = function() {
                    var element = this.getHrefAttribute().getDefinition();
                    if (this.attribute("width").hasValue()) element.attribute("width", true).value = this.attribute("width").value;
                    if (this.attribute("height").hasValue()) element.attribute("height", true).value = this.attribute("height").value;
                    return element;
                };
                this.path = function(ctx) {
                    var element = this.getDefinition();
                    if (element != null) element.path(ctx);
                };
                this.getBoundingBox = function() {
                    var element = this.getDefinition();
                    if (element != null) return element.getBoundingBox();
                };
                this.renderChildren = function(ctx) {
                    var element = this.getDefinition();
                    if (element != null) {
                        var oldParent = element.parent;
                        element.parent = null;
                        element.render(ctx);
                        element.parent = oldParent;
                    }
                };
            };
            svg.Element.use.prototype = new svg.Element.RenderedElementBase();
            svg.Element.mask = function(node) {
                this.base = svg.Element.ElementBase;
                this.base(node);
                this.apply = function(ctx, element) {
                    var x = this.attribute("x").toPixels("x");
                    var y = this.attribute("y").toPixels("y");
                    var width = this.attribute("width").toPixels("x");
                    var height = this.attribute("height").toPixels("y");
                    if (width == 0 && height == 0) {
                        var bb = new svg.BoundingBox();
                        for (var i = 0; i < this.children.length; i++) {
                            bb.addBoundingBox(this.children[i].getBoundingBox());
                        }
                        var x = Math.floor(bb.x1);
                        var y = Math.floor(bb.y1);
                        var width = Math.floor(bb.width());
                        var height = Math.floor(bb.height());
                    }
                    var mask = element.attribute("mask").value;
                    element.attribute("mask").value = "";
                    var cMask = document.createElement("canvas");
                    cMask.width = x + width;
                    cMask.height = y + height;
                    var maskCtx = cMask.getContext("2d");
                    this.renderChildren(maskCtx);
                    var c = document.createElement("canvas");
                    c.width = x + width;
                    c.height = y + height;
                    var tempCtx = c.getContext("2d");
                    element.render(tempCtx);
                    tempCtx.globalCompositeOperation = "destination-in";
                    tempCtx.fillStyle = maskCtx.createPattern(cMask, "no-repeat");
                    tempCtx.fillRect(0, 0, x + width, y + height);
                    ctx.fillStyle = tempCtx.createPattern(c, "no-repeat");
                    ctx.fillRect(0, 0, x + width, y + height);
                    element.attribute("mask").value = mask;
                };
                this.render = function(ctx) {};
            };
            svg.Element.mask.prototype = new svg.Element.ElementBase();
            svg.Element.clipPath = function(node) {
                this.base = svg.Element.ElementBase;
                this.base(node);
                this.apply = function(ctx) {
                    for (var i = 0; i < this.children.length; i++) {
                        var child = this.children[i];
                        if (typeof child.path != "undefined") {
                            var transform = null;
                            if (child.attribute("transform").hasValue()) {
                                transform = new svg.Transform(child.attribute("transform").value);
                                transform.apply(ctx);
                            }
                            child.path(ctx);
                            ctx.clip();
                            if (transform) {
                                transform.unapply(ctx);
                            }
                        }
                    }
                };
                this.render = function(ctx) {};
            };
            svg.Element.clipPath.prototype = new svg.Element.ElementBase();
            svg.Element.filter = function(node) {
                this.base = svg.Element.ElementBase;
                this.base(node);
                this.apply = function(ctx, element) {
                    var bb = element.getBoundingBox();
                    var x = Math.floor(bb.x1);
                    var y = Math.floor(bb.y1);
                    var width = Math.floor(bb.width());
                    var height = Math.floor(bb.height());
                    var filter = element.style("filter").value;
                    element.style("filter").value = "";
                    var px = 0, py = 0;
                    for (var i = 0; i < this.children.length; i++) {
                        var efd = this.children[i].extraFilterDistance || 0;
                        px = Math.max(px, efd);
                        py = Math.max(py, efd);
                    }
                    var c = document.createElement("canvas");
                    c.width = width + 2 * px;
                    c.height = height + 2 * py;
                    var tempCtx = c.getContext("2d");
                    tempCtx.translate(-x + px, -y + py);
                    element.render(tempCtx);
                    for (var i = 0; i < this.children.length; i++) {
                        this.children[i].apply(tempCtx, 0, 0, width + 2 * px, height + 2 * py);
                    }
                    ctx.drawImage(c, 0, 0, width + 2 * px, height + 2 * py, x - px, y - py, width + 2 * px, height + 2 * py);
                    element.style("filter", true).value = filter;
                };
                this.render = function(ctx) {};
            };
            svg.Element.filter.prototype = new svg.Element.ElementBase();
            svg.Element.feMorphology = function(node) {
                this.base = svg.Element.ElementBase;
                this.base(node);
                this.apply = function(ctx, x, y, width, height) {};
            };
            svg.Element.feMorphology.prototype = new svg.Element.ElementBase();
            svg.Element.feColorMatrix = function(node) {
                this.base = svg.Element.ElementBase;
                this.base(node);
                function imGet(img, x, y, width, height, rgba) {
                    return img[y * width * 4 + x * 4 + rgba];
                }
                function imSet(img, x, y, width, height, rgba, val) {
                    img[y * width * 4 + x * 4 + rgba] = val;
                }
                this.apply = function(ctx, x, y, width, height) {
                    var srcData = ctx.getImageData(0, 0, width, height);
                    for (var y = 0; y < height; y++) {
                        for (var x = 0; x < width; x++) {
                            var r = imGet(srcData.data, x, y, width, height, 0);
                            var g = imGet(srcData.data, x, y, width, height, 1);
                            var b = imGet(srcData.data, x, y, width, height, 2);
                            var gray = (r + g + b) / 3;
                            imSet(srcData.data, x, y, width, height, 0, gray);
                            imSet(srcData.data, x, y, width, height, 1, gray);
                            imSet(srcData.data, x, y, width, height, 2, gray);
                        }
                    }
                    ctx.clearRect(0, 0, width, height);
                    ctx.putImageData(srcData, 0, 0);
                };
            };
            svg.Element.feColorMatrix.prototype = new svg.Element.ElementBase();
            svg.Element.feGaussianBlur = function(node) {
                this.base = svg.Element.ElementBase;
                this.base(node);
                this.blurRadius = Math.floor(this.attribute("stdDeviation").numValue());
                this.extraFilterDistance = this.blurRadius;
                this.apply = function(ctx, x, y, width, height) {
                    if (typeof stackBlurCanvasRGBA == "undefined") {
                        if (typeof console != "undefined") {
                            console.log("ERROR: StackBlur.js must be included for blur to work");
                        }
                        return;
                    }
                    ctx.canvas.id = svg.UniqueId();
                    ctx.canvas.style.display = "none";
                    document.body.appendChild(ctx.canvas);
                    stackBlurCanvasRGBA(ctx.canvas.id, x, y, width, height, this.blurRadius);
                    document.body.removeChild(ctx.canvas);
                };
            };
            svg.Element.feGaussianBlur.prototype = new svg.Element.ElementBase();
            svg.Element.title = function(node) {};
            svg.Element.title.prototype = new svg.Element.ElementBase();
            svg.Element.desc = function(node) {};
            svg.Element.desc.prototype = new svg.Element.ElementBase();
            svg.Element.MISSING = function(node) {
                if (typeof console != "undefined") {
                    console.log("ERROR: Element '" + node.nodeName + "' not yet implemented.");
                }
            };
            svg.Element.MISSING.prototype = new svg.Element.ElementBase();
            svg.CreateElement = function(node) {
                var className = node.nodeName.replace(/^[^:]+:/, "");
                className = className.replace(/\-/g, "");
                var e = null;
                if (typeof svg.Element[className] != "undefined") {
                    e = new svg.Element[className](node);
                } else {
                    e = new svg.Element.MISSING(node);
                }
                e.type = node.nodeName;
                return e;
            };
            svg.load = function(ctx, url) {
                svg.loadXml(ctx, svg.ajax(url));
            };
            svg.loadXml = function(ctx, xml) {
                svg.loadXmlDoc(ctx, svg.parseXml(xml));
            };
            svg.loadXmlDoc = function(ctx, dom) {
                svg.init(ctx);
                var mapXY = function(p) {
                    var e = ctx.canvas;
                    while (e) {
                        p.x -= e.offsetLeft;
                        p.y -= e.offsetTop;
                        e = e.offsetParent;
                    }
                    if (window.scrollX) p.x += window.scrollX;
                    if (window.scrollY) p.y += window.scrollY;
                    return p;
                };
                if (svg.opts["ignoreMouse"] != true) {
                    ctx.canvas.onclick = function(e) {
                        var p = mapXY(new svg.Point(e != null ? e.clientX : event.clientX, e != null ? e.clientY : event.clientY));
                        svg.Mouse.onclick(p.x, p.y);
                    };
                    ctx.canvas.onmousemove = function(e) {
                        var p = mapXY(new svg.Point(e != null ? e.clientX : event.clientX, e != null ? e.clientY : event.clientY));
                        svg.Mouse.onmousemove(p.x, p.y);
                    };
                }
                var e = svg.CreateElement(dom.documentElement);
                e.root = true;
                var isFirstRender = true;
                var draw = function() {
                    svg.ViewPort.Clear();
                    if (ctx.canvas.parentNode) svg.ViewPort.SetCurrent(ctx.canvas.parentNode.clientWidth, ctx.canvas.parentNode.clientHeight);
                    if (svg.opts["ignoreDimensions"] != true) {
                        if (e.style("width").hasValue()) {
                            ctx.canvas.width = e.style("width").toPixels("x");
                            ctx.canvas.style.width = ctx.canvas.width + "px";
                        }
                        if (e.style("height").hasValue()) {
                            ctx.canvas.height = e.style("height").toPixels("y");
                            ctx.canvas.style.height = ctx.canvas.height + "px";
                        }
                    }
                    var cWidth = ctx.canvas.clientWidth || ctx.canvas.width;
                    var cHeight = ctx.canvas.clientHeight || ctx.canvas.height;
                    if (svg.opts["ignoreDimensions"] == true && e.style("width").hasValue() && e.style("height").hasValue()) {
                        cWidth = e.style("width").toPixels("x");
                        cHeight = e.style("height").toPixels("y");
                    }
                    svg.ViewPort.SetCurrent(cWidth, cHeight);
                    if (svg.opts["offsetX"] != null) e.attribute("x", true).value = svg.opts["offsetX"];
                    if (svg.opts["offsetY"] != null) e.attribute("y", true).value = svg.opts["offsetY"];
                    if (svg.opts["scaleWidth"] != null && svg.opts["scaleHeight"] != null) {
                        var xRatio = 1, yRatio = 1, viewBox = svg.ToNumberArray(e.attribute("viewBox").value);
                        if (e.attribute("width").hasValue()) xRatio = e.attribute("width").toPixels("x") / svg.opts["scaleWidth"]; else if (!isNaN(viewBox[2])) xRatio = viewBox[2] / svg.opts["scaleWidth"];
                        if (e.attribute("height").hasValue()) yRatio = e.attribute("height").toPixels("y") / svg.opts["scaleHeight"]; else if (!isNaN(viewBox[3])) yRatio = viewBox[3] / svg.opts["scaleHeight"];
                        e.attribute("width", true).value = svg.opts["scaleWidth"];
                        e.attribute("height", true).value = svg.opts["scaleHeight"];
                        e.attribute("viewBox", true).value = "0 0 " + cWidth * xRatio + " " + cHeight * yRatio;
                        e.attribute("preserveAspectRatio", true).value = "none";
                    }
                    if (svg.opts["ignoreClear"] != true) {
                        ctx.clearRect(0, 0, cWidth, cHeight);
                    }
                    e.render(ctx);
                    if (isFirstRender) {
                        isFirstRender = false;
                        if (typeof svg.opts["renderCallback"] == "function") svg.opts["renderCallback"](dom);
                    }
                };
                var waitingForImages = true;
                if (svg.ImagesLoaded()) {
                    waitingForImages = false;
                    draw();
                }
                svg.intervalID = setInterval(function() {
                    var needUpdate = false;
                    if (waitingForImages && svg.ImagesLoaded()) {
                        waitingForImages = false;
                        needUpdate = true;
                    }
                    if (svg.opts["ignoreMouse"] != true) {
                        needUpdate = needUpdate | svg.Mouse.hasEvents();
                    }
                    if (svg.opts["ignoreAnimation"] != true) {
                        for (var i = 0; i < svg.Animations.length; i++) {
                            needUpdate = needUpdate | svg.Animations[i].update(1e3 / svg.FRAMERATE);
                        }
                    }
                    if (typeof svg.opts["forceRedraw"] == "function") {
                        if (svg.opts["forceRedraw"]() == true) needUpdate = true;
                    }
                    if (needUpdate) {
                        draw();
                        svg.Mouse.runEvents();
                    }
                }, 1e3 / svg.FRAMERATE);
            };
            svg.stop = function() {
                if (svg.intervalID) {
                    clearInterval(svg.intervalID);
                }
            };
            svg.Mouse = new function() {
                this.events = [];
                this.hasEvents = function() {
                    return this.events.length != 0;
                };
                this.onclick = function(x, y) {
                    this.events.push({
                        type: "onclick",
                        x: x,
                        y: y,
                        run: function(e) {
                            if (e.onclick) e.onclick();
                        }
                    });
                };
                this.onmousemove = function(x, y) {
                    this.events.push({
                        type: "onmousemove",
                        x: x,
                        y: y,
                        run: function(e) {
                            if (e.onmousemove) e.onmousemove();
                        }
                    });
                };
                this.eventElements = [];
                this.checkPath = function(element, ctx) {
                    for (var i = 0; i < this.events.length; i++) {
                        var e = this.events[i];
                        if (ctx.isPointInPath && ctx.isPointInPath(e.x, e.y)) this.eventElements[i] = element;
                    }
                };
                this.checkBoundingBox = function(element, bb) {
                    for (var i = 0; i < this.events.length; i++) {
                        var e = this.events[i];
                        if (bb.isPointInBox(e.x, e.y)) this.eventElements[i] = element;
                    }
                };
                this.runEvents = function() {
                    svg.ctx.canvas.style.cursor = "";
                    for (var i = 0; i < this.events.length; i++) {
                        var e = this.events[i];
                        var element = this.eventElements[i];
                        while (element) {
                            e.run(element);
                            element = element.parent;
                        }
                    }
                    this.events = [];
                    this.eventElements = [];
                };
            }();
            return svg;
        }
    })();
    if (typeof CanvasRenderingContext2D != "undefined") {
        CanvasRenderingContext2D.prototype.drawSvg = function(s, dx, dy, dw, dh) {
            canvg(this.canvas, s, {
                ignoreMouse: true,
                ignoreAnimation: true,
                ignoreDimensions: true,
                ignoreClear: true,
                offsetX: dx,
                offsetY: dy,
                scaleWidth: dw,
                scaleHeight: dh
            });
        };
    }
    return canvg;
});
define("base/output", [ "kity", "base/canvg" ], function(require) {
    var kity = require("kity"), canvg = require("base/canvg");
    return kity.createClass("Output", {
        constructor: function(formula) {
            this.formula = formula;
        },
        toJPG: function(cb) {
            toImage(this.formula, "image/jpeg", cb);
        },
        toPNG: function(cb) {
            toImage(this.formula, "image/png", cb);
        }
    });
    function toImage(formula, type, cb) {
        var rectSpace = formula.container.getRenderBox();
        return getBase64DataURL(formula.node.ownerDocument, {
            width: rectSpace.width,
            height: rectSpace.height,
            content: getSVGContent(formula.node)
        }, type, cb);
    }
    function getBase64DataURL(doc, data, type, cb) {
        var canvas = null, args = arguments, ctx = null;
        if (!isChromeCore()) {
            drawToCanvas.apply(null, args);
        } else {
            canvas = getImageCanvas(doc, data.width, data.height, type);
            ctx = canvas.getContext("2d");
            var image = new Image();
            image.onload = function() {
                try {
                    ctx.drawImage(image, 0, 0);
                    cb(canvas.toDataURL(type));
                } catch (e) {
                    drawToCanvas.apply(null, args);
                }
            };
            image.src = getSVGDataURL(data.content);
        }
    }
    function getSVGContent(svgNode) {
        var tmp = svgNode.ownerDocument.createElement("div"), start = [ '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="', svgNode.getAttribute("width"), '" height="', svgNode.getAttribute("height"), '">' ];
        tmp.appendChild(svgNode.cloneNode(true));
        return tmp.innerHTML.replace(/<svg[^>]+?>/i, start.join("")).replace(/&nbsp;/g, "");
    }
    function getSVGDataURL(data) {
        return "data:image/svg+xml;base64," + window.btoa(unescape(encodeURIComponent(data)));
    }
    function getImageCanvas(doc, width, height, type) {
        var canvas = doc.createElement("canvas"), ctx = canvas.getContext("2d");
        canvas.width = width;
        canvas.height = height;
        if (type !== "image/png") {
            ctx.fillStyle = "white";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }
        return canvas;
    }
    function drawToCanvas(doc, data, type, cb) {
        var canvas = getImageCanvas(doc, data.width, data.height, type);
        canvas.style.cssText = "position: absolute; top: 0; left: 100000px; z-index: -1;";
        doc.body.appendChild(canvas);
        canvg(canvas, data.content);
        doc.body.removeChild(canvas);
        window.setTimeout(function() {
            cb(canvas.toDataURL(type));
        }, 50);
    }
    function isChromeCore() {
        return window.navigator.userAgent.indexOf("Chrome") !== -1;
    }
});
define("char/char", [ "kity", "signgroup", "def/gtype" ], function(require, exports, module) {
    var kity = require("kity");
    return kity.createClass("Char", {
        base: require("signgroup"),
        constructor: function(value, type) {
            var currentData;
            type = type || "std";
            currentData = CHAR_DATA[type][value];
            if (!currentData) {
                currentData = CHAR_DATA["std"][value];
            }
            if (!currentData) {
                throw new Error("invalid character: " + value);
            }
            this.callBase();
            this.value = value;
            this.contentShape = new kity.Group();
            this.box = new kity.Rect(currentData.size[0] + currentData.offset.x * 2, currentData.size[1]).fill("transparent");
            this.char = new kity.Path(currentData.path).fill("black");
            this.char.translate(currentData.offset.x, currentData.offset.y);
            this.contentShape.addShape(this.box);
            this.contentShape.addShape(this.char);
            this.addShape(this.contentShape);
        },
        getBaseWidth: function() {
            return this.char.getWidth();
        },
        getBaseHeight: function() {
            return this.char.getHeight();
        },
        getBoxWidth: function() {
            return this.box.getWidth();
        }
    });
});
define("char/conf", [], function(require) {
    return {
        defaultFont: "KF AMS MAIN"
    };
});
define("char/map", [], function(require) {
    return {
        Alpha: "\u0391",
        Beta: "\u0392",
        Gamma: "\u0393",
        Delta: "\u0394",
        Epsilon: "\u0395",
        Zeta: "\u0396",
        Eta: "\u0397",
        Theta: "\u0398",
        Iota: "\u0399",
        Kappa: "\u039a",
        Lambda: "\u039b",
        Mu: "\u039c",
        Nu: "\u039d",
        Xi: "\u039e",
        Omicron: "\u039f",
        Pi: "\u03a0",
        Rho: "\u03a1",
        Sigma: "\u03a3",
        Tau: "\u03a4",
        Upsilon: "\u03a5",
        Phi: "\u03a6",
        Chi: "\u03a7",
        Psi: "\u03a8",
        Omega: "\u03a9",
        alpha: "\u03b1",
        beta: "\u03b2",
        gamma: "\u03b3",
        delta: "\u03b4",
        epsilon: "\u03b5",
        varepsilon: "\u03b5",
        zeta: "\u03b6",
        eta: "\u03b7",
        theta: "\u03b8",
        iota: "\u03b9",
        kappa: "\u03ba",
        lambda: "\u03bb",
        mu: "\u03bc",
        nu: "\u03bd",
        xi: "\u03be",
        omicron: "\u03bf",
        pi: "\u03c0",
        rho: "\u03c1",
        sigma: "\u03c3",
        tau: "\u03c4",
        upsilon: "\u03c5",
        phi: "\u03c6",
        varphi: "\u03c6",
        chi: "\u03c7",
        psi: "\u03c8",
        omega: "\u03c9",
        doublecap: "\u22d2",
        Cap: "\u22d2",
        dobulecup: "\u22d3",
        Cup: "\u22d3",
        ast: "\u2217",
        divideontimes: "\u22c7",
        rightthreetimes: "\u22cc",
        leftthreetimes: "\u22cb",
        cdot: "\xb7",
        dotplus: "\u2214",
        rtimes: "\u22ca",
        ltimes: "\u22c9",
        centerdot: "\u25aa",
        doublebarwedge: "\u2480",
        setminus: "\u2481",
        amalg: "\u2210",
        circ: "\u25e6",
        bigcirc: "\xa9",
        gtrdot: "\u22d7",
        lessdot: "\u22d6",
        smallsetminus: "\u2485",
        circledast: "\u229b",
        circledcirc: "\u229a",
        intercal: "\u22ba",
        sqcap: "\u2293",
        sqcup: "\u2294",
        barwedge: "\u22bc",
        circleddash: "\u229d",
        star: "\u2486",
        bigtriangledown: "\u25bd",
        bigtriangleup: "\u25b3",
        cup: "\x92a",
        cap: "\x929",
        times: "\xd7",
        mp: "\u2213",
        pm: "\xb1",
        triangleleft: "\u22b2",
        triangleright: "\u22b3",
        boxdot: "\u22a1",
        curlyvee: "\u22cf",
        curlywedge: "\u22ce",
        boxminus: "\u229f",
        ominus: "\u2296",
        oplus: "\u2295",
        oslash: "\u2298",
        otimes: "\u2297",
        uplus: "\u228e",
        boxplus: "\u229e",
        dagger: "\u2020",
        ddagger: "\u2021",
        vee: "\u2228",
        lor: "\u2228",
        veebar: "\u22bb",
        bullet: "\u2022",
        diamond: "\u22c4",
        wedge: "\u2227",
        land: "\u2227",
        div: "\xf7",
        wr: "\u2240",
        geqq: "\u2267",
        lll: "\u2488",
        llless: "\u2488",
        ggg: "\u2489",
        gggtr: "\u2489",
        preccurlyeq: "\u248a",
        geqslant: "\u248b",
        lnapprox: "\u2268",
        preceq: "\u227c",
        gg: "\u226b",
        lneq: "\u2490",
        precnapprox: "\u2492",
        approx: "\u2248",
        lneqq: "\u2493",
        precneqq: "\u2494",
        approxeq: "\u24a5",
        gnapprox: "\u2269",
        lnsim: "\u22e6",
        precnsim: "\u22e8",
        asymp: "\u224d",
        gneq: "\u2491",
        lvertneqq: "\u2496",
        precsim: "\u227e",
        backsim: "\u223d",
        gneqq: "\u2498",
        ncong: "\u2247",
        risingdotseq: "\u2253",
        backsimeq: "\u22cd",
        gnsim: "\u22e7",
        sim: "\u223c",
        simeq: "\u2243",
        bumpeq: "\u2499",
        gtrapprox: "\u249b",
        ngeq: "\u2271",
        Bumpeq: "\u249a",
        gtreqless: "\u22db",
        ngeqq: "\u24e0",
        succ: "\u227b",
        circeq: "\u249c",
        gtreqqless: "\u24e4",
        ngeqslant: "\u24e6",
        succapprox: "\u249d",
        cong: "\u24a1",
        gtrless: "\u2277",
        ngtr: "\u226f",
        succcurlyeq: "\u248d",
        curlyeqprec: "\u24a2",
        gtrsim: "\u2273",
        nleq: "\u2270",
        succeq: "\u227d",
        curlyeqsucc: "\u24a3",
        gvertneqq: "\u2497",
        nleqq: "\u24e1",
        succnapprox: "\u24a4",
        doteq: "\u249f",
        leq: "\u2264",
        le: "\u2264",
        nleqslant: "\u24e5",
        succneqq: "\u2495",
        doteqdot: "\u2251",
        Doteq: "\u2251",
        leqq: "\u2266",
        nless: "\u226e",
        succnsim: "\u22e9",
        leqslant: "\u248c",
        nprec: "\u2280",
        succsim: "\u227f",
        eqsim: "\u2242",
        lessapprox: "\u24a6",
        npreceq: "\u22e0",
        eqslantgtr: "\u22dd",
        lesseqgtr: "\u22da",
        nsim: "\u2241",
        eqslantless: "\u24a9",
        lesseqqgtr: "\u24e4",
        nsucc: "\u2281",
        triangleq: "\u225c",
        eqcirc: "\u2256",
        equiv: "\u2261",
        lessgtr: "\u2276",
        nsucceq: "\u22e1",
        fallingdotseq: "\u2252",
        lesssim: "\u2272",
        prec: "\u227a",
        geq: "\u2265",
        ge: "\u2265",
        ll: "\u226a",
        precapprox: "\u249e",
        uparrow: "\u2191",
        downarrow: "\u2193",
        updownarrow: "\u2195",
        Uparrow: "\u21d1",
        Downarrow: "\u21d3",
        Updownarrow: "\u21d5",
        circlearrowleft: "\u21ba",
        circlearrowright: "\u21bb",
        curvearrowleft: "\u21b6",
        curvearrowright: "\u21b7",
        downdownarrows: "\u21ca",
        downharpoonleft: "\u21c3",
        downharpoonright: "\u21c2",
        leftarrow: "\u2190",
        gets: "\u2190",
        Leftarrow: "\u21d0",
        leftarrowtail: "\u21a2",
        leftharpoondown: "\u24ac",
        leftharpoonup: "\u24aa",
        leftleftarrows: "\u24ae",
        leftrightarrow: "\u2194",
        Leftrightarrow: "\u21d4",
        leftrightarrows: "\u21c4",
        leftrightharpoons: "\u21cb",
        leftrightsquigarrow: "\u21ad",
        Llfetarrow: "\u21da",
        looparrowleft: "\u21ab",
        looparrowright: "\u21ac",
        multimap: "\u22b8",
        nLeftarrow: "\u21cd",
        nRightarrow: "\u21cf",
        nLeftrightarrow: "\u21ce",
        nearrow: "\u2197",
        nleftarrow: "\u24b0",
        nleftrightarrow: "\u21ae",
        nrightarrow: "\u24b1",
        nwarrow: "\u2196",
        rightarrow: "\u2192",
        to: "\u2192",
        Rightarrow: "\u21d2",
        rightarrowtail: "\u21a3",
        rightharpoondown: "\u24ad",
        rightharpoonup: "\u24ab",
        rightleftarrows: "\u21c6",
        rightleftharpoons: "\u21cc",
        rigtrightarrows: "\u24af",
        rightsquigarrow: "\u21dd",
        Rightarrow: "\u21db",
        searrow: "\u2198",
        swarrow: "\u2199",
        twoheadleftarrow: "\u219e",
        twoheadrightarrow: "\u21a0",
        upharpoonleft: "\u21bf",
        upharpoonright: "\u21be",
        restriction: "be",
        upuparrows: "\u21c8",
        backepsilon: "\u2108",
        because: "\u2235",
        therefore: "\u2234",
        between: "\u226c",
        blacktriangleleft: "\u25c0",
        blacktriangleright: "\u25b8",
        dashv: "\u22a3",
        frown: "\u2322",
        "in": "\u2208",
        mid: "\u24cc",
        parallel: "d0",
        models: "\u22a8",
        ni: "\u220b",
        owns: "\u220b",
        nmid: "\u2224",
        nparallel: "\u2226",
        nshortmid: "\u24b5",
        nshortparallel: "\u24b6",
        nsubseteq: "\u2288",
        nsubseteqq: "\u24b7",
        nsupseteq: "\u2289",
        nsupseteqq: "\u24b8",
        ntriangleleft: "\u22ea",
        ntrianglelefteq: "\u22ec",
        ntriangleright: "\u22eb",
        ntrianglerighteq: "\u22ed",
        nvdash: "\u22ac",
        nVdash: "\u24c0",
        nvDash: "\u24c1",
        nVDash: "\u22af",
        perp: "\u22a5",
        pitchfork: "\u22d4",
        propto: "\u221d",
        shortmid: "\u2483",
        shortparallel: "\u2484",
        smile: "\u2323",
        sqsubset: "\u228f",
        sqsubseteq: "\u2291",
        sqsupset: "\u2290",
        sqsupseteq: "\u2292",
        subset: "\u2282",
        Subset: "\u22d0",
        subseteq: "\u2286",
        subseteqq: "\u24bd",
        subsetneq: "\u228a",
        subsetneqq: "\u24b9",
        supset: "\u2283",
        Supset: "\u22d1",
        supseteq: "\u2287",
        supseteqq: "\u24be",
        supsetneq: "\u228b",
        supsetneqq: "\u24ba",
        trianglelefteq: "\u22b4",
        trianglerighteq: "\u22b5",
        varpropto: "\u24b2",
        varsubsetneq: "\u24b3",
        varsubsetneqq: "\u24bb",
        varsupsetneq: "\u24b4",
        varsupsetneqq: "\u24bc",
        vdash: "\u22a2",
        Vdash: "\u22a9",
        vDash: "\u22a8",
        Vvdash: "\u22aa",
        vert: "|",
        Vert: "\u01c1",
        "|": "\u01c1",
        backslash: "\u01c2",
        langle: "\u3008",
        rangle: "\u3009",
        lceil: "\u2308",
        rceil: "\u2309",
        lbrace: "{",
        rbrace: "}",
        lfloor: "\u230a",
        rfllor: "\u230b",
        colon: "\u01c4",
        "#": "#",
        bot: "\u22a5"
    };
});
define("char/text-factory", [ "kity" ], function(require) {
    var kity = require("kity"), divNode = document.createElement("div"), NAMESPACE = "http://www.w3.org/XML/1998/namespace";
    function createText(content) {
        var text = new kity.Text();
        if ("innerHTML" in text.node) {
            text.node.setAttributeNS(NAMESPACE, "xml:space", "preserve");
        } else {
            if (content.indexOf(" ") != -1) {
                content = convertContent(content);
            }
        }
        text.setContent(content);
        return text;
    }
    function convertContent(content) {
        divNode.innerHTML = '<svg><text gg="asfdas">' + content.replace(/\s/gi, "&nbsp;") + "</text></svg>";
        return divNode.firstChild.firstChild.textContent;
    }
    return {
        create: function(content) {
            return createText(content);
        }
    };
});
define("char/text", [ "kity", "sysconf", "font/map/kf-ams-main", "font/map/kf-ams-cal", "font/map/kf-ams-frak", "font/map/kf-ams-bb", "font/map/kf-ams-roman", "font/manager", "char/text-factory", "signgroup", "def/gtype" ], function(require, exports, module) {
    var kity = require("kity"), FONT_CONF = require("sysconf").font, FontManager = require("font/manager"), TextFactory = require("char/text-factory");
    return kity.createClass("Text", {
        base: require("signgroup"),
        constructor: function(content, fontFamily) {
            this.callBase();
            this.fontFamily = fontFamily;
            this.fontSize = 50;
            this.content = content || "";
            this.box.remove();
            this.translationContent = this.translation(this.content);
            this.contentShape = new kity.Group();
            this.contentNode = this.createContent();
            this.contentShape.addShape(this.contentNode);
            this.addShape(this.contentShape);
        },
        createContent: function() {
            var contentNode = TextFactory.create(this.translationContent);
            contentNode.setAttr({
                "font-family": this.fontFamily,
                "font-size": 50,
                x: 0,
                y: FONT_CONF.offset
            });
            return contentNode;
        },
        setFamily: function(fontFamily) {
            this.fontFamily = fontFamily;
            this.contentNode.setAttr("font-family", fontFamily);
        },
        setFontSize: function(fontSize) {
            this.fontSize = fontSize;
            this.contentNode.setAttr("font-size", fontSize + "px");
            this.contentNode.setAttr("y", fontSize / 50 * FONT_CONF.offset);
        },
        getBaseHeight: function() {
            var chars = this.contentShape.getItems(), currentChar = null, index = 0, height = 0;
            while (currentChar = chars[index]) {
                height = Math.max(height, currentChar.getHeight());
                index++;
            }
            return height;
        },
        translation: function(content) {
            var fontFamily = this.fontFamily;
            return content.replace(/``/g, "\u201c").replace(/\\([a-zA-Z,]+)\\/g, function(match, input) {
                if (input === ",") {
                    return " ";
                }
                var data = FontManager.getCharacterValue(input, fontFamily);
                if (!data) {
                    console.error(input + "\u4e22\u5931");
                    return "";
                }
                return data;
            });
        }
    });
});
define("def/gtype", [], function() {
    return {
        UNKNOWN: -1,
        EXP: 0,
        COMPOUND_EXP: 1,
        OP: 2
    };
});
define("def/script-type", [], function() {
    return {
        SIDE: "side",
        FOLLOW: "follow"
    };
});
define("expression/compound-exp/binary-exp/subscript", [ "kity", "expression/compound-exp/script", "operator/script", "expression/compound" ], function(require, exports, modules) {
    var kity = require("kity");
    return kity.createClass("SubscriptExpression", {
        base: require("expression/compound-exp/script"),
        constructor: function(operand, subscript) {
            this.callBase(operand, null, subscript);
            this.setFlag("Subscript");
        }
    });
});
define("expression/compound-exp/binary-exp/superscript", [ "kity", "expression/compound-exp/script", "operator/script", "expression/compound" ], function(require, exports, modules) {
    var kity = require("kity");
    return kity.createClass("SuperscriptExpression", {
        base: require("expression/compound-exp/script"),
        constructor: function(operand, superscript) {
            this.callBase(operand, superscript, null);
            this.setFlag("Superscript");
        }
    });
});
define("expression/compound-exp/binary", [ "kity", "expression/compound", "def/gtype", "expression/expression" ], function(require, exports, modules) {
    var kity = require("kity");
    return kity.createClass("BinaryExpression", {
        base: require("expression/compound"),
        constructor: function(firstOperand, lastOperand) {
            this.callBase();
            this.setFirstOperand(firstOperand);
            this.setLastOperand(lastOperand);
        },
        setFirstOperand: function(operand) {
            return this.setOperand(operand, 0);
        },
        getFirstOperand: function() {
            return this.getOperand(0);
        },
        setLastOperand: function(operand) {
            return this.setOperand(operand, 1);
        },
        getLastOperand: function() {
            return this.getOperand(1);
        }
    });
});
define("expression/compound-exp/brackets", [ "kity", "operator/brackets", "char/text", "font/manager", "operator/operator", "expression/compound", "def/gtype", "expression/expression" ], function(require, exports, modules) {
    var kity = require("kity"), BracketsOperator = require("operator/brackets");
    return kity.createClass("BracketsExpression", {
        base: require("expression/compound"),
        constructor: function(left, right, exp) {
            this.callBase();
            this.setFlag("Brackets");
            if (arguments.length === 2) {
                exp = right;
                right = left;
            }
            this.leftSymbol = left;
            this.rightSymbol = right;
            this.setOperator(new BracketsOperator());
            this.setOperand(exp, 0);
        },
        getLeftSymbol: function() {
            return this.leftSymbol;
        },
        getRightSymbol: function() {
            return this.rightSymbol;
        }
    });
});
define("expression/compound-exp/combination", [ "kity", "sysconf", "font/map/kf-ams-main", "font/map/kf-ams-cal", "font/map/kf-ams-frak", "font/map/kf-ams-bb", "font/map/kf-ams-roman", "operator/combination", "operator/operator", "expression/compound", "def/gtype", "expression/expression" ], function(require, exports, modules) {
    var kity = require("kity"), FONT_CONF = require("sysconf").font, CombinationOperator = require("operator/combination");
    return kity.createClass("CombinationExpression", {
        base: require("expression/compound"),
        constructor: function(abc) {
            this.callBase();
            this.setFlag("Combination");
            this.setOperator(new CombinationOperator());
            kity.Utils.each(arguments, function(operand, index) {
                this.setOperand(operand, index);
            }, this);
        },
        getRenderBox: function(refer) {
            var rectBox = this.callBase(refer);
            if (this.getOperands().length === 0) {
                rectBox.height = FONT_CONF.spaceHeight;
            }
            return rectBox;
        },
        getBaseline: function(refer) {
            var maxBaseline = 0, operands = this.getOperands();
            if (operands.length === 0) {
                return this.callBase(refer);
            }
            kity.Utils.each(operands, function(operand) {
                maxBaseline = Math.max(operand.getBaseline(refer), maxBaseline);
            });
            return maxBaseline;
        },
        getMeanline: function(refer) {
            var minMeanline = 1e7, operands = this.getOperands();
            if (operands.length === 0) {
                return this.callBase(refer);
            }
            kity.Utils.each(operands, function(operand) {
                minMeanline = Math.min(operand.getMeanline(refer), minMeanline);
            });
            return minMeanline;
        }
    });
});
define("expression/compound-exp/fraction", [ "kity", "operator/fraction", "sysconf", "operator/operator", "expression/compound-exp/binary", "expression/compound" ], function(require, exports, modules) {
    var kity = require("kity"), FractionOperator = require("operator/fraction");
    return kity.createClass("FractionExpression", {
        base: require("expression/compound-exp/binary"),
        constructor: function(upOperand, downOperand) {
            this.callBase(upOperand, downOperand);
            this.setFlag("Fraction");
            this.setOperator(new FractionOperator());
        },
        getBaseline: function(refer) {
            var downOperand = this.getOperand(1), rectBox = downOperand.getRenderBox(refer);
            return rectBox.y + downOperand.getBaselineProportion() * rectBox.height;
        },
        getMeanline: function(refer) {
            var upOperand = this.getOperand(0), rectBox = upOperand.getRenderBox(refer);
            return upOperand.getMeanlineProportion() * rectBox.height;
        }
    });
});
define("expression/compound-exp/func", [ "kity", "sysconf", "font/map/kf-ams-main", "font/map/kf-ams-cal", "font/map/kf-ams-frak", "font/map/kf-ams-bb", "font/map/kf-ams-roman", "operator/func", "char/text", "operator/common/script-controller", "operator/operator", "expression/compound", "def/gtype", "expression/expression" ], function(require, exports, modules) {
    var kity = require("kity"), FUNC_CONF = require("sysconf").func, FunctionOperator = require("operator/func");
    return kity.createClass("FunctionExpression", {
        base: require("expression/compound"),
        constructor: function(funcName, expr, sup, sub) {
            this.callBase();
            this.setFlag("Func");
            this.funcName = funcName;
            this.setOperator(new FunctionOperator(funcName));
            this.setExpr(expr);
            this.setSuperscript(sup);
            this.setSubscript(sub);
        },
        isSideScript: function() {
            return !FUNC_CONF["ud-script"][this.funcName];
        },
        setExpr: function(expr) {
            return this.setOperand(expr, 0);
        },
        setSuperscript: function(sub) {
            return this.setOperand(sub, 1);
        },
        setSubscript: function(sub) {
            return this.setOperand(sub, 2);
        }
    });
});
define("expression/compound-exp/integration", [ "kity", "operator/integration", "operator/common/script-controller", "operator/operator", "expression/compound", "def/gtype", "expression/expression" ], function(require, exports, modules) {
    var kity = require("kity"), IntegrationOperator = require("operator/integration"), IntegrationExpression = kity.createClass("IntegrationExpression", {
        base: require("expression/compound"),
        constructor: function(integrand, superscript, subscript) {
            this.callBase();
            this.setFlag("Integration");
            this.setOperator(new IntegrationOperator());
            this.setIntegrand(integrand);
            this.setSuperscript(superscript);
            this.setSubscript(subscript);
        },
        setType: function(type) {
            this.getOperator().setType(type);
            return this;
        },
        resetType: function() {
            this.getOperator().resetType();
            return this;
        },
        setIntegrand: function(integrand) {
            this.setOperand(integrand, 0);
        },
        setSuperscript: function(sup) {
            this.setOperand(sup, 1);
        },
        setSubscript: function(sub) {
            this.setOperand(sub, 2);
        }
    });
    return IntegrationExpression;
});
define("expression/compound-exp/radical", [ "kity", "operator/radical", "operator/operator", "expression/compound-exp/binary", "expression/compound" ], function(require, exports, modules) {
    var kity = require("kity"), RadicalOperator = require("operator/radical");
    return kity.createClass("RadicalExpression", {
        base: require("expression/compound-exp/binary"),
        constructor: function(radicand, exponent) {
            this.callBase(radicand, exponent);
            this.setFlag("Radicand");
            this.setOperator(new RadicalOperator());
        },
        setRadicand: function(operand) {
            return this.setFirstOperand(operand);
        },
        getRadicand: function() {
            return this.getFirstOperand();
        },
        setExponent: function(operand) {
            return this.setLastOperand(operand);
        },
        getExponent: function() {
            return this.getLastOperand();
        }
    });
});
define("expression/compound-exp/script", [ "kity", "operator/script", "operator/common/script-controller", "operator/operator", "expression/compound", "def/gtype", "expression/expression" ], function(require, exports, modules) {
    var kity = require("kity"), ScriptOperator = require("operator/script");
    return kity.createClass("ScriptExpression", {
        base: require("expression/compound"),
        constructor: function(operand, superscript, subscript) {
            this.callBase();
            this.setFlag("Script");
            this.setOperator(new ScriptOperator());
            this.setOpd(operand);
            this.setSuperscript(superscript);
            this.setSubscript(subscript);
        },
        setOpd: function(operand) {
            this.setOperand(operand, 0);
        },
        setSuperscript: function(sup) {
            this.setOperand(sup, 1);
        },
        setSubscript: function(sub) {
            this.setOperand(sub, 2);
        }
    });
});
define("expression/compound-exp/summation", [ "kity", "operator/summation", "operator/common/script-controller", "operator/operator", "expression/compound", "def/gtype", "expression/expression" ], function(require, exports, modules) {
    var kity = require("kity"), SummationOperator = require("operator/summation");
    return kity.createClass("SummationExpression", {
        base: require("expression/compound"),
        constructor: function(expr, superscript, subscript) {
            this.callBase();
            this.setFlag("Summation");
            this.setOperator(new SummationOperator());
            this.setExpr(expr);
            this.setSuperscript(superscript);
            this.setSubscript(subscript);
        },
        setExpr: function(expr) {
            this.setOperand(expr, 0);
        },
        setSuperscript: function(sup) {
            this.setOperand(sup, 1);
        },
        setSubscript: function(sub) {
            this.setOperand(sub, 2);
        }
    });
});
define("expression/compound", [ "kity", "def/gtype", "expression/expression", "sysconf", "signgroup" ], function(require, exports, modules) {
    var kity = require("kity"), GTYPE = require("def/gtype"), Expression = require("expression/expression");
    return kity.createClass("CompoundExpression", {
        base: require("expression/expression"),
        constructor: function() {
            this.callBase();
            this.type = GTYPE.COMPOUND_EXP;
            this.operands = [];
            this.operator = null;
            this.operatorBox = new kity.Group();
            this.operatorBox.setAttr("data-type", "kf-editor-exp-op-box");
            this.operandBox = new kity.Group();
            this.operandBox.setAttr("data-type", "kf-editor-exp-operand-box");
            this.setChildren(0, this.operatorBox);
            this.setChildren(1, this.operandBox);
        },
        setOperator: function(operator) {
            if (operator === undefined) {
                return this;
            }
            if (this.operator) {
                this.operator.remove();
            }
            this.operatorBox.addShape(operator);
            this.operator = operator;
            this.operator.setParentExpression(this);
            operator.expression = this;
            return this;
        },
        getOperator: function() {
            return this.operator;
        },
        setOperand: function(operand, index, isWrap) {
            if (isWrap === false) {
                this.operands[index] = operand;
                return this;
            }
            operand = Expression.wrap(operand);
            if (this.operands[index]) {
                this.operands[index].remove();
            }
            this.operands[index] = operand;
            this.operandBox.addShape(operand);
            return this;
        },
        getOperand: function(index) {
            return this.operands[index];
        },
        getOperands: function() {
            return this.operands;
        },
        addedCall: function() {
            this.operator.applyOperand.apply(this.operator, this.operands);
            return this;
        }
    });
});
define("expression/empty", [ "kity", "sysconf", "font/map/kf-ams-main", "font/map/kf-ams-cal", "font/map/kf-ams-frak", "font/map/kf-ams-bb", "font/map/kf-ams-roman", "expression/expression", "def/gtype", "signgroup" ], function(require, exports, module) {
    var kity = require("kity"), FONT_CONF = require("sysconf").font, Expression = require("expression/expression"), EmptyExpression = kity.createClass("EmptyExpression", {
        base: Expression,
        constructor: function() {
            this.callBase();
            this.setFlag("Empty");
        },
        getRenderBox: function() {
            return {
                width: 0,
                height: FONT_CONF.spaceHeight,
                x: 0,
                y: 0
            };
        }
    });
    EmptyExpression.isEmpty = function(target) {
        return target instanceof EmptyExpression;
    };
    Expression.registerWrap("empty", function(operand) {
        if (operand === null || operand === undefined) {
            return new EmptyExpression();
        }
    });
    return EmptyExpression;
});
define("expression/expression", [ "kity", "def/gtype", "sysconf", "font/map/kf-ams-main", "font/map/kf-ams-cal", "font/map/kf-ams-frak", "font/map/kf-ams-bb", "font/map/kf-ams-roman", "signgroup" ], function(require, exports, module) {
    var kity = require("kity"), GTYPE = require("def/gtype"), FONT_CONF = require("sysconf").font, WRAP_FN = [], WRAP_FN_INDEX = {}, Expression = kity.createClass("Expression", {
        base: require("signgroup"),
        constructor: function() {
            this.callBase();
            this.type = GTYPE.EXP;
            this._offset = {
                top: 0,
                bottom: 0
            };
            this.children = [];
            this.box.fill("transparent").setAttr("data-type", "kf-editor-exp-box");
            this.box.setAttr("data-type", "kf-editor-exp-bg-box");
            this.expContent = new kity.Group();
            this.expContent.setAttr("data-type", "kf-editor-exp-content-box");
            this.addShape(this.expContent);
        },
        getChildren: function() {
            return this.children;
        },
        getChild: function(index) {
            return this.children[index] || null;
        },
        getTopOffset: function() {
            return this._offset.top;
        },
        getBottomOffset: function() {
            return this._offset.bottom;
        },
        getOffset: function() {
            return this._offset;
        },
        setTopOffset: function(val) {
            this._offset.top = val;
        },
        setBottomOffset: function(val) {
            this._offset.bottom = val;
        },
        setOffset: function(top, bottom) {
            this._offset.top = top;
            this._offset.bottom = bottom;
        },
        setFlag: function(flag) {
            this.setAttr("data-flag", flag || "Expression");
        },
        setChildren: function(index, exp) {
            if (this.children[index]) {
                this.children[index].remove();
            }
            this.children[index] = exp;
            this.expContent.addShape(exp);
        },
        getBaselineProportion: function() {
            return FONT_CONF.baselinePosition;
        },
        getMeanlineProportion: function() {
            return FONT_CONF.meanlinePosition;
        },
        getBaseline: function(refer) {
            return this.getRenderBox(refer).height * FONT_CONF.baselinePosition - 3;
        },
        getMeanline: function(refer) {
            return this.getRenderBox(refer).height * FONT_CONF.meanlinePosition - 1;
        },
        getAscenderline: function() {
            return this.getFixRenderBox().height * FONT_CONF.ascenderPosition;
        },
        getDescenderline: function() {
            return this.getFixRenderBox().height * FONT_CONF.descenderPosition;
        },
        translateElement: function(x, y) {
            this.expContent.translate(x, y);
        },
        expand: function(width, height) {
            var renderBox = this.getFixRenderBox();
            this.setBoxSize(renderBox.width + width, renderBox.height + height);
        },
        getBaseWidth: function() {
            return this.getWidth();
        },
        getBaseHeight: function() {
            return this.getHeight();
        },
        updateBoxSize: function() {
            var renderBox = this.expContent.getFixRenderBox();
            this.setBoxSize(renderBox.width, renderBox.height);
        },
        getBox: function() {
            return this.box;
        }
    });
    kity.Utils.extend(Expression, {
        registerWrap: function(name, fn) {
            WRAP_FN_INDEX[name] = WRAP_FN.length;
            WRAP_FN.push(fn);
        },
        revokeWrap: function(name) {
            var fn = null;
            if (name in WRAP_FN_INDEX) {
                fn = WRAP_FN[WRAP_FN_INDEX[name]];
                WRAP_FN[WRAP_FN_INDEX[name]] = null;
                delete WRAP_FN_INDEX[name];
            }
            return fn;
        },
        wrap: function(operand) {
            var result = undefined;
            kity.Utils.each(WRAP_FN, function(fn) {
                if (!fn) {
                    return;
                }
                result = fn(operand);
                if (result) {
                    return false;
                }
            });
            return result;
        }
    });
    return Expression;
});
define("expression/text", [ "char/text", "kity", "sysconf", "font/manager", "char/text-factory", "signgroup", "char/conf", "expression/expression", "def/gtype" ], function(require, exports, module) {
    var Text = require("char/text"), kity = require("kity"), FONT_CONF = require("char/conf"), Expression = require("expression/expression"), TextExpression = kity.createClass("TextExpression", {
        base: require("expression/expression"),
        constructor: function(content, fontFamily) {
            this.callBase();
            this.fontFamily = fontFamily || FONT_CONF.defaultFont;
            this.setFlag("Text");
            this.content = content + "";
            this.textContent = new Text(this.content, this.fontFamily);
            this.setChildren(0, this.textContent);
            this.setChildren(1, new kity.Rect(0, 0, 0, 0).fill("transparent"));
        },
        setFamily: function(fontFamily) {
            this.textContent.setFamily(fontFamily);
        },
        setFontSize: function(fontSize) {
            this.textContent.setFontSize(fontSize);
        },
        addedCall: function() {
            var box = this.textContent.getFixRenderBox();
            this.getChild(1).setSize(box.width, box.height);
            this.updateBoxSize();
            return this;
        }
    });
    Expression.registerWrap("text", function(operand) {
        var operandType = typeof operand;
        if (operandType === "number" || operandType === "string") {
            operand = new TextExpression(operand);
        }
        return operand;
    });
    return TextExpression;
});
define("font/checker-tpl", [], function(require) {
    return [ '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">', '<text id="abcd" font-family="KF AMS MAIN" font-size="50" x="0" y="0">x</text>', "</svg>" ];
});
define("font/installer", [ "kity", "font/manager", "sysconf", "jquery", "font/map/kf-ams-main", "font/map/kf-ams-cal", "font/map/kf-ams-frak", "font/map/kf-ams-bb", "font/map/kf-ams-roman", "font/checker-tpl" ], function(require) {
    var kity = require("kity"), FontManager = require("font/manager"), $ = require("jquery"), FONT_CONF = require("sysconf").font, NODE_LIST = [];
    return kity.createClass("FontInstaller", {
        constructor: function(doc, resource) {
            this.callBase();
            this.resource = resource || "../src/resource/";
            this.doc = doc;
        },
        mount: function(callback) {
            var fontList = FontManager.getFontList(), count = 0, _self = this;
            kity.Utils.each(fontList, function(fontInfo) {
                count++;
                fontInfo.meta.src = _self.resource + fontInfo.meta.src;
                _self.createFontStyle(fontInfo);
                preload(_self.doc, fontInfo, function() {
                    count--;
                    if (count === 0) {
                        complete(_self.doc, callback);
                    }
                });
            });
        },
        createFontStyle: function(fontInfo) {
            var stylesheet = this.doc.createElement("style"), tpl = '@font-face{\nfont-family: "${fontFamily}";\nsrc: url("${src}");\n}';
            stylesheet.setAttribute("type", "text/css");
            stylesheet.innerHTML = tpl.replace("${fontFamily}", fontInfo.meta.fontFamily).replace("${src}", fontInfo.meta.src);
            this.doc.head.appendChild(stylesheet);
        }
    });
    function preload(doc, fontInfo, callback) {
        $.get(fontInfo.meta.src, function(data, state) {
            if (state === "success") {
                applyFonts(doc, fontInfo);
            }
            callback();
        });
    }
    function complete(doc, callback) {
        window.setTimeout(function() {
            initFontSystemInfo(doc);
            removeTmpNode();
            callback();
        }, 100);
    }
    function applyFonts(doc, fontInfo) {
        var node = document.createElement("div"), fontFamily = fontInfo.meta.fontFamily, strs = [];
        node.style.cssText = "position: absolute; top: 0; left: -100000px;";
        kity.Utils.each(fontInfo.data, function(v, key) {
            strs.push(key);
        });
        node.style.fontFamily = fontFamily;
        node.innerHTML = strs.join("");
        doc.body.appendChild(node);
        NODE_LIST.push(node);
    }
    function initFontSystemInfo(doc) {
        var tmpNode = doc.createElement("div");
        tmpNode.style.cssText = "position: absolute; top: 0; left: -100000px;";
        tmpNode.innerHTML = require("font/checker-tpl").join("");
        doc.body.appendChild(tmpNode);
        var rectBox = tmpNode.getElementsByTagName("text")[0].getBBox();
        FONT_CONF.spaceHeight = rectBox.height;
        FONT_CONF.topSpace = -rectBox.y - FONT_CONF.baseline;
        FONT_CONF.bottomSpace = FONT_CONF.spaceHeight - FONT_CONF.topSpace - FONT_CONF.baseHeight;
        FONT_CONF.offset = FONT_CONF.baseline + FONT_CONF.topSpace;
        FONT_CONF.baselinePosition = (FONT_CONF.topSpace + FONT_CONF.baseline) / FONT_CONF.spaceHeight;
        FONT_CONF.meanlinePosition = (FONT_CONF.topSpace + FONT_CONF.meanline) / FONT_CONF.spaceHeight;
        FONT_CONF.ascenderPosition = FONT_CONF.topSpace / FONT_CONF.spaceHeight;
        FONT_CONF.descenderPosition = (FONT_CONF.topSpace + FONT_CONF.baseHeight) / FONT_CONF.spaceHeight;
        doc.body.removeChild(tmpNode);
    }
    function removeTmpNode() {
        kity.Utils.each(NODE_LIST, function(node) {
            node.parentNode.removeChild(node);
        });
        NODE_LIST = [];
    }
});
define("font/manager", [ "kity", "sysconf", "font/map/kf-ams-main", "font/map/kf-ams-cal", "font/map/kf-ams-frak", "font/map/kf-ams-bb", "font/map/kf-ams-roman" ], function(require) {
    var FONT_LIST = {}, kity = require("kity"), CONF = require("sysconf").font.list;
    (function() {
        kity.Utils.each(CONF, function(fontData) {
            FONT_LIST[fontData.meta.fontFamily] = fontData;
        });
    })();
    return {
        getFontList: function() {
            return FONT_LIST;
        },
        getCharacterValue: function(key, fontFamily) {
            if (!FONT_LIST[fontFamily]) {
                return null;
            }
            return FONT_LIST[fontFamily].map[key] || null;
        }
    };
});
define("font/map/kf-ams-bb", [], function(require) {
    return {
        meta: {
            fontFamily: "KF AMS BB",
            src: "KF_AMS_BB.woff"
        },
        data: {
            A: {
                x: 786,
                d: "M400 682l255 -581c20 -45 37 -62 59 -66c9 -1 22 -3 22 -17c0 -18 -16 -18 -34 -18h-261c-18 0 -33 0 -33 18c0 9 7 16 17 17c11 1 39 3 63 27c-1 21 -2 37 -37 135h-220c-7 -32 -14 -64 -14 -97c0 -60 25 -64 39 -65c7 -1 21 -2 21 -17c0 -18 -15 -18 -33 -18h-160\nc-18 0 -34 0 -34 18c0 15 16 17 21 17c27 3 48 18 60 50l228 598c5 12 10 21 21 21s13 -5 20 -22zM241 232h196c-17 46 -77 190 -108 257c-33 -84 -65 -169 -88 -257zM182 120l-17 -43c-6 -16 -10 -28 -23 -42h54c-14 23 -14 61 -14 65c0 6 0 7 1 9v10zM344 543l49 -112\nc43 -100 130 -303 130 -364c0 -12 -3 -26 -13 -32h142c-14 18 -25 42 -29 52l-243 553z"
            },
            B: {
                x: 712,
                d: "M181 594v-503c0 -13 0 -38 -9 -56h109c-13 16 -13 36 -13 51v513c0 15 0 35 13 51h-109c9 -18 9 -43 9 -56zM453 384c66 14 136 66 136 130c0 99 -71 124 -140 132c23 -24 45 -67 45 -136c0 -43 -8 -94 -41 -126zM303 596v-224c99 2 156 26 156 138\nc0 105 -55 140 -99 140c-24 0 -39 -10 -43 -12c-14 -11 -14 -22 -14 -42zM513 367c73 -20 149 -76 149 -180c0 -139 -158 -187 -281 -187h-297c-18 0 -34 0 -34 18c0 17 18 17 29 17c63 0 67 8 67 58v499c0 48 -3 58 -63 58c-16 0 -33 0 -33 17c0 18 16 18 34 18h303\nc122 0 237 -32 237 -171c0 -68 -55 -118 -111 -147zM303 89c0 -19 0 -54 59 -54c125 0 128 113 128 152c0 32 -3 97 -42 127c-31 23 -65 23 -145 23v-248zM481 47c86 20 146 64 146 140c0 87 -65 139 -157 154c40 -33 55 -85 55 -154c0 -45 -7 -98 -44 -140z"
            },
            C: {
                x: 748,
                d: "M670 671v-167c0 -17 0 -33 -17 -33c-13 0 -16 10 -19 19c-34 106 -154 179 -229 179c-107 0 -179 -131 -179 -328c0 -155 39 -312 194 -312c89 0 178 40 236 106c10 11 14 14 21 14c6 0 21 -6 21 -20s-51 -64 -97 -93c-73 -44 -144 -55 -197 -55\nc-173 0 -354 107 -354 362c0 256 185 361 352 361c16 0 59 0 110 -22c20 -9 55 -24 90 -24c7 0 30 0 33 28c2 18 17 18 18 18c17 0 17 -16 17 -33zM635 568v62c-8 -5 -21 -7 -32 -7c-5 0 -13 0 -18 1c25 -26 38 -39 50 -56zM280 646v1c-94 -32 -195 -121 -195 -305\nc0 -122 47 -257 211 -310c-90 72 -105 210 -105 309c0 196 66 282 89 305z"
            },
            D: {
                x: 775,
                d: "M84 685h289c162 0 352 -91 352 -330c0 -246 -178 -355 -353 -355h-288c-18 0 -34 0 -34 18c0 17 18 17 28 17c51 0 55 8 55 57v501c0 49 -4 57 -56 57c-8 0 -27 0 -27 17c0 18 16 18 34 18zM495 630h-1c51 -54 90 -133 90 -277c0 -116 -20 -227 -85 -295\nc99 35 191 127 191 297c0 119 -52 229 -195 275zM160 35h114c-13 16 -13 36 -13 51v513c0 14 0 36 13 51h-114c8 -18 8 -42 8 -55v-505c0 -13 0 -37 -8 -55zM296 596v-507c0 -19 0 -54 59 -54c168 0 194 191 194 318c0 223 -103 297 -187 297c-21 0 -39 -5 -48 -12\nc-18 -11 -18 -20 -18 -42z"
            },
            E: {
                x: 669,
                d: "M295 336v-234c0 -67 19 -67 43 -67h55c55 0 106 14 158 45c73 43 83 85 89 111c0 1 4 14 18 14c8 0 17 -7 17 -18c0 -5 -28 -172 -29 -173c-3 -14 -13 -14 -32 -14h-530c-18 0 -34 0 -34 18c0 17 17 17 32 17c53 0 56 10 56 57v501c0 49 -4 57 -61 57c-8 0 -27 0 -27 17\nc0 18 16 18 34 18h512c31 0 33 -2 33 -33v-130c0 -17 0 -33 -17 -33c-16 0 -17 15 -18 23c-6 69 -73 138 -208 138h-48c-24 0 -43 0 -43 -67v-211c52 1 154 8 160 137c0 8 1 23 18 23s17 -16 17 -32v-271c0 -18 0 -32 -17 -32s-17 13 -18 27c0 6 -4 60 -52 91\nc-35 21 -83 21 -108 21zM594 650h-57c21 -13 40 -29 57 -48v48zM399 357v-1c1 0 32 -13 56 -40v84c-18 -21 -40 -36 -56 -43zM173 595v-505c0 -12 0 -37 -8 -55h106c-4 9 -11 29 -11 65v485c0 9 0 42 11 65h-106c8 -18 8 -43 8 -55zM545 35h70c1 8 8 52 9 56\nc-20 -18 -47 -39 -79 -55v-1z"
            },
            F: {
                x: 669,
                d: "M295 336v-241c0 -53 4 -60 81 -60c13 0 30 0 30 -17c0 -18 -15 -18 -33 -18h-289c-18 0 -34 0 -34 18c0 17 17 17 32 17c53 0 56 10 56 57v501c0 49 -4 57 -61 57c-8 0 -27 0 -27 17c0 18 16 18 34 18h502c31 0 33 -2 33 -33v-130c0 -17 0 -33 -17 -33\nc-16 0 -17 15 -18 23c-6 69 -71 138 -199 138h-47c-24 0 -43 0 -43 -67v-211c73 3 143 23 149 139c1 11 5 21 18 21c17 0 17 -16 17 -32v-271c0 -18 0 -32 -17 -32c-16 0 -17 13 -18 25c-8 112 -108 114 -149 114zM584 650h-54c23 -14 40 -32 54 -47v47zM444 317v81\nc-14 -17 -34 -33 -53 -42c12 -5 38 -20 53 -39zM173 595v-505c0 -12 0 -37 -8 -55h105c-10 19 -10 43 -10 58v492c0 9 0 42 11 65h-106c8 -18 8 -43 8 -55z"
            },
            G: {
                x: 811,
                d: "M670 255h-143c33 -30 33 -61 33 -135c0 -27 0 -66 -4 -82c39 11 74 27 88 34v102c0 15 0 49 26 81zM408 -19c-192 0 -358 120 -358 362c0 256 185 361 352 361c45 0 78 -8 116 -25c14 -6 49 -21 84 -21c20 0 30 11 32 28c2 18 17 18 18 18c17 0 17 -16 17 -33v-167\nc0 -17 0 -33 -17 -33c-13 0 -16 8 -21 27c-24 78 -136 171 -226 171c-106 0 -179 -128 -179 -329c0 -77 9 -153 32 -212c41 -104 111 -112 152 -112c9 0 55 0 86 11c29 11 29 26 29 55v76c0 29 0 89 -83 96c-14 1 -19 9 -19 18c0 18 16 18 36 18h267c18 0 35 0 35 -18\nc0 -3 -1 -16 -19 -18c-36 -3 -63 -28 -63 -82v-92c0 -28 -1 -29 -26 -42c-69 -35 -158 -57 -245 -57zM634 568v62c-8 -5 -21 -7 -32 -7c-5 0 -12 0 -18 1c15 -13 34 -34 50 -56zM268 42c-69 83 -77 220 -77 298c0 193 63 280 89 306v1c-94 -32 -195 -121 -195 -305\nc0 -128 51 -245 183 -300z"
            },
            H: {
                x: 844,
                d: "M706 593v-501c0 -48 3 -57 60 -57c11 0 28 0 28 -17c0 -18 -15 -18 -33 -18h-272c-18 0 -34 0 -34 18c0 17 17 17 32 17c53 0 56 10 56 57v232h-242v-232c0 -48 3 -57 60 -57c11 0 28 0 28 -17c0 -18 -15 -18 -33 -18h-272c-18 0 -34 0 -34 18c0 17 17 17 32 17\nc53 0 56 10 56 57v501c0 49 -4 57 -61 57c-8 0 -27 0 -27 17c0 18 16 18 34 18h272c18 0 33 0 33 -18c0 -17 -17 -17 -26 -17c-58 0 -62 -7 -62 -57v-223h242v223c0 49 -4 57 -61 57c-8 0 -27 0 -27 17c0 18 16 18 34 18h272c18 0 33 0 33 -18c0 -17 -17 -17 -26 -17\nc-58 0 -62 -7 -62 -57zM173 595v-505c0 -12 0 -37 -8 -55h109c-8 18 -8 43 -8 55v505c0 12 0 37 8 55h-109c8 -18 8 -43 8 -55zM578 595v-505c0 -12 0 -37 -8 -55h109c-8 18 -8 43 -8 55v505c0 12 0 37 8 55h-109c8 -18 8 -43 8 -55z"
            },
            I: {
                x: 449,
                d: "M306 593v-501c0 -49 4 -57 65 -57c11 0 28 0 28 -17c0 -18 -15 -18 -33 -18h-282c-18 0 -34 0 -34 18c0 17 18 17 31 17c59 0 62 9 62 57v501c0 47 -3 57 -60 57c-16 0 -33 0 -33 17c0 18 16 18 34 18h282c18 0 33 0 33 -18c0 -17 -16 -17 -31 -17c-59 0 -62 -10 -62 -57\nzM178 595v-505c0 -15 0 -37 -9 -55h111c-9 18 -9 40 -9 55v505c0 15 0 37 9 55h-111c9 -18 9 -40 9 -55z"
            },
            J: {
                x: 570,
                d: "M201 685h286c18 0 33 0 33 -18c0 -9 -8 -17 -17 -17c-57 -1 -71 -1 -71 -46v-420c0 -32 0 -88 -1 -97c-15 -123 -109 -162 -220 -162c-97 0 -161 48 -161 111c0 41 34 70 72 70s72 -29 72 -70c0 -15 -5 -34 -18 -48c-5 -5 -9 -9 -9 -17c0 -11 30 -11 37 -11\nc24 0 40 11 48 18c17 17 17 36 17 53v561c0 51 -4 58 -73 58c-11 0 -29 0 -29 17c0 18 16 18 34 18zM131 -24c3 19 12 29 16 33c4 3 12 12 12 27c0 23 -20 35 -37 35c-15 0 -37 -10 -37 -36c0 -37 36 -54 46 -59zM304 594v-565c0 -18 0 -37 -15 -60c76 20 99 66 107 117\nc1 10 1 60 1 90v403c0 34 0 53 8 71h-110c9 -18 9 -45 9 -56z"
            },
            K: {
                x: 845,
                d: "M439 416l289 -346c5 -6 12 -13 21 -22c8 -7 12 -11 27 -13c7 -1 19 -4 19 -17c0 -18 -15 -18 -33 -18h-223c-18 0 -33 0 -33 18c0 13 6 14 30 18c4 1 6 3 12 4c-5 13 -5 15 -16 29l-26 31l-177 211l-28 -26v-193c0 -48 3 -57 60 -57c11 0 28 0 28 -17\nc0 -18 -15 -18 -33 -18h-272c-18 0 -34 0 -34 18c0 17 17 17 32 17c53 0 56 10 56 57v501c0 49 -4 57 -61 57c-8 0 -27 0 -27 17c0 18 16 18 34 18h272c18 0 33 0 33 -18c0 -17 -17 -17 -26 -17c-58 0 -62 -7 -62 -57v-250c24 21 81 78 104 100l109 103c9 9 25 26 25 54\nc0 22 -11 46 -38 50c-7 1 -21 2 -21 17c0 18 15 18 33 18h211c18 0 33 0 33 -18c0 -15 -14 -16 -22 -17c-62 -6 -122 -65 -231 -171zM643 650h-82c13 -21 13 -40 13 -55c36 32 38 32 69 55zM165 35h109c-8 18 -8 43 -8 55v505c0 12 0 37 8 55h-109c8 -18 8 -43 8 -55v-505\nc0 -12 0 -37 -8 -55zM414 393l-59 -58c10 -11 79 -94 99 -119c23 -27 80 -94 102 -122c26 -32 26 -42 27 -59h129z"
            },
            L: {
                x: 680,
                d: "M297 593v-491c0 -67 19 -67 43 -67h52c33 0 84 4 148 45c83 52 94 101 100 130c2 8 10 14 18 14s17 -7 17 -18c0 -4 -28 -190 -29 -193c-4 -13 -13 -13 -32 -13h-530c-18 0 -34 0 -34 18c0 17 17 17 32 17c53 0 56 10 56 57v501c0 49 -4 57 -61 57c-8 0 -27 0 -27 17\nc0 18 16 18 34 18h263c18 0 33 0 33 -18c0 -17 -17 -17 -28 -17c-52 0 -55 -8 -55 -57zM173 595v-505c0 -12 0 -37 -8 -55h108c-4 9 -11 29 -11 65v495c0 13 0 37 8 55h-105c8 -18 8 -43 8 -55zM614 35l10 68c-17 -17 -43 -42 -88 -67v-1h78z"
            },
            M: {
                x: 1008,
                d: "M499 210l190 454c9 21 11 21 37 21h193c18 0 33 0 33 -18c0 -11 -9 -16 -19 -17c-68 -6 -68 -50 -68 -71v-473c0 -23 0 -65 77 -71c8 -1 16 -8 16 -17c0 -18 -15 -18 -33 -18h-267c-18 0 -34 0 -34 18c0 1 0 15 19 17c68 6 68 50 68 71v498l-1 1l-245 -585\nc-5 -12 -8 -20 -20 -20c-6 0 -13 3 -21 21l-244 584l-1 -1v-498c0 -23 0 -65 77 -71c8 -1 16 -8 16 -17c0 -18 -15 -18 -33 -18h-155c-18 0 -34 0 -34 18c0 1 0 15 19 17c68 6 68 50 68 71v534c-22 8 -49 10 -61 10c-5 0 -23 0 -23 17c0 18 15 18 33 18h186\nc27 0 28 0 36 -20zM469 192l-193 458h-87c14 -12 22 -29 27 -42l228 -544h2l44 106c-12 4 -13 5 -21 22zM167 35l-9 11c-1 -1 -1 -2 -2 -3c-2 -2 -4 -5 -6 -8h17zM746 650v-546c0 -18 0 -44 -22 -69h129c-23 25 -23 51 -23 69v477c0 18 0 44 22 69h-106z"
            },
            N: {
                x: 777,
                d: "M137 595c0 14 0 16 2 24c-10 12 -29 28 -56 31c-6 0 -21 2 -21 17c0 18 15 18 33 18h174c23 0 26 0 36 -14l302 -408v313c0 53 -8 73 -64 74c-11 0 -29 0 -29 17c0 18 16 18 34 18h146c18 0 33 0 33 -18c0 -16 -17 -17 -23 -17c-46 -1 -55 -15 -55 -71v-565\nc0 -17 0 -33 -17 -33c-7 0 -12 3 -21 15l-432 571v-458c0 -52 8 -73 63 -74c14 0 30 -1 30 -17c0 -18 -15 -18 -33 -18h-155c-18 0 -34 0 -34 18c0 16 17 17 28 17c51 1 59 19 59 72v488zM614 51v144l-337 455h-119c11 -10 11 -12 21 -24z"
            },
            O: {
                x: 812,
                d: "M762 342c0 -246 -173 -361 -356 -361c-179 0 -356 110 -356 362c0 246 173 361 356 361c179 0 356 -110 356 -362zM268 641l-1 1c-58 -22 -182 -96 -182 -300c0 -194 115 -274 184 -300c-66 78 -78 203 -78 301c0 110 16 227 77 298zM545 642l-1 -1\nc65 -76 77 -201 77 -299c0 -110 -16 -227 -78 -300c60 23 184 97 184 301c0 194 -115 274 -182 299zM406 16c160 0 180 207 180 327c0 114 -18 326 -180 326c-160 0 -180 -207 -180 -327c0 -114 18 -326 180 -326z"
            },
            P: {
                x: 683,
                d: "M296 308v-216c0 -49 4 -57 61 -57c11 0 28 0 28 -17c0 -18 -15 -18 -33 -18h-268c-18 0 -34 0 -34 18c0 17 18 17 28 17c51 0 55 8 55 57v501c0 49 -4 57 -56 57c-8 0 -27 0 -27 17c0 18 16 18 34 18h292c129 0 257 -46 257 -188c0 -95 -73 -190 -276 -190h-34\nc-2 1 -23 1 -27 1zM461 353c92 23 137 74 137 144c0 101 -78 131 -133 144c26 -37 31 -90 31 -143c0 -40 -3 -103 -35 -145zM296 596v-253h50c62 0 115 12 115 155c0 125 -33 152 -105 152c-60 0 -60 -34 -60 -54zM160 35h109c-8 18 -8 45 -8 55v509c0 15 0 35 13 51h-114\nc8 -18 8 -42 8 -55v-505c0 -13 0 -37 -8 -55z"
            },
            Q: {
                x: 812,
                d: "M495 -9c23 -50 80 -108 172 -108c16 0 27 0 55 7c7 2 8 2 10 2c8 0 17 -6 17 -18s-10 -16 -28 -23c-52 -20 -107 -30 -157 -30c-124 0 -223 72 -268 175c-118 31 -246 133 -246 347c0 246 173 361 356 361c179 0 356 -110 356 -362c0 -210 -126 -322 -267 -351zM268 641\nl-1 1c-58 -22 -182 -96 -182 -300c0 -194 115 -274 184 -300c-66 78 -78 203 -78 301c0 110 16 227 77 298zM545 642l-1 -1c65 -76 77 -201 77 -299c0 -110 -16 -227 -78 -300c60 23 184 97 184 301c0 194 -115 274 -182 299zM406 16c160 0 180 207 180 327\nc0 114 -18 326 -180 326c-160 0 -180 -207 -180 -327c0 -114 18 -326 180 -326zM340 -14c52 -90 139 -130 224 -130c13 0 31 0 31 2c0 1 -1 1 -11 5c-50 20 -97 58 -124 121c-28 -3 -49 -3 -54 -3c-1 0 -38 0 -66 5z"
            },
            R: {
                x: 787,
                d: "M302 314v-222c0 -49 4 -57 61 -57c11 0 28 0 28 -17c0 -18 -15 -18 -33 -18h-274c-18 0 -34 0 -34 18c0 17 19 17 27 17c57 0 62 7 62 57v501c0 50 -5 57 -62 57c-8 0 -27 0 -27 17c0 18 16 18 34 18h314c118 0 265 -48 265 -187c0 -119 -86 -157 -175 -173\nc67 -108 117 -174 124 -184c24 -32 73 -98 110 -106c9 -2 15 -9 15 -17c0 -18 -15 -18 -33 -18h-138c-23 0 -26 0 -33 12c-11 15 -35 54 -45 70l-150 232h-36zM495 363c103 19 133 70 133 135c0 101 -99 132 -140 141c32 -41 38 -94 38 -142c0 -44 -3 -97 -31 -134zM302 596\nv-247c158 0 189 19 189 148c0 118 -42 153 -127 153c-62 0 -62 -33 -62 -54zM166 35h109c-8 18 -8 45 -8 55v509c0 15 0 35 13 51h-114c8 -18 8 -45 8 -55v-505c0 -9 0 -37 -8 -55zM379 314l180 -279h97c-83 83 -191 261 -206 285c-15 -2 -37 -4 -52 -4c-8 0 -12 -1 -19 -2z\n"
            },
            S: {
                x: 601,
                d: "M93 105v-64c15 8 32 10 47 10c-31 30 -46 54 -46 54h-1zM231 306c-154 65 -181 133 -181 198c0 133 91 200 227 200c30 0 53 -4 85 -16c12 -4 35 -12 60 -12s39 11 48 20c4 3 8 8 16 8c17 0 17 -16 17 -33v-152c0 -17 0 -33 -17 -33c-14 0 -16 10 -19 24\nc-19 84 -116 159 -188 159c-71 0 -110 -47 -110 -102c0 -70 99 -109 188 -145c143 -56 194 -146 194 -230c0 -131 -123 -204 -267 -204c-16 0 -45 0 -86 15c-9 4 -33 13 -59 13c-13 0 -31 -3 -48 -19c-5 -6 -8 -9 -15 -9c-18 0 -18 16 -18 33v186c0 17 0 33 18 33\nc9 0 17 -8 17 -17c0 -22 2 -76 61 -136c35 -36 79 -64 131 -64c99 0 130 41 130 112c0 92 -106 137 -184 171zM468 591v60c-1 -1 -17 -9 -44 -10c28 -29 37 -39 44 -50zM435 59c30 16 81 55 81 133c0 54 -23 92 -55 127c-39 40 -90 60 -127 75c-90 36 -200 82 -200 173\nc0 8 1 42 19 71l-1 1c-54 -31 -67 -88 -67 -135c0 -94 89 -136 183 -176c78 -33 182 -88 182 -193c0 -7 -1 -47 -15 -76z"
            },
            T: {
                x: 702,
                d: "M432 649v-556c0 -51 4 -58 69 -58c11 0 28 0 28 -17c0 -18 -15 -18 -33 -18h-290c-18 0 -34 0 -34 18c0 17 18 17 30 17c63 0 67 8 67 58v556c-105 -6 -180 -66 -184 -149c0 -2 -1 -20 -18 -20s-17 16 -17 32v141c0 30 2 32 33 32h535c30 0 34 -1 34 -36v-133\nc0 -19 0 -36 -18 -36c-16 0 -17 15 -18 30c-4 47 -53 132 -184 139zM85 650v-52c14 17 35 37 60 52h-60zM617 650h-61c27 -17 50 -39 61 -53v53zM295 35h111c-9 18 -9 43 -9 56v559h-93v-559c0 -13 0 -38 -9 -56z"
            },
            U: {
                x: 792,
                d: "M558 685h151c18 0 33 0 33 -18c0 -15 -13 -16 -22 -17c-66 -8 -66 -108 -66 -170v-205c0 -76 0 -294 -260 -294c-67 0 -136 14 -187 58c-74 63 -74 153 -74 200v369c0 41 -16 41 -61 42c-8 0 -22 3 -22 17c0 18 16 18 34 18h268c18 0 33 0 33 -18c0 -17 -16 -17 -31 -17\nc-59 0 -62 -10 -62 -57v-375c0 -173 67 -195 132 -195c146 0 188 110 188 227v265c0 67 -19 129 -63 135c-11 1 -25 3 -25 17c0 18 16 18 34 18zM654 650h-42c10 -14 15 -23 21 -39c6 16 11 25 21 39zM257 216v379c0 15 0 37 9 55h-106c5 -11 6 -24 7 -25c1 -13 1 -49 1 -73\nv-279c0 -25 0 -63 1 -75c7 -83 46 -151 145 -173c-42 41 -57 113 -57 191z"
            },
            V: {
                x: 819,
                d: "M686 599l-249 -597c-6 -16 -9 -21 -20 -21c-9 0 -14 6 -21 21l-269 593c-2 3 -23 50 -56 55c-8 1 -21 3 -21 17c0 18 15 18 33 18h262c18 0 33 0 33 -18c0 -5 -3 -16 -19 -17c-37 -3 -60 -24 -60 -31c0 -65 153 -397 171 -434c73 183 73 185 80 205c17 48 49 143 49 202\nc0 52 -21 56 -36 58c-6 1 -21 2 -21 17c0 18 15 18 33 18h161c18 0 33 0 33 -18c0 -15 -15 -17 -21 -17c-42 -4 -57 -39 -62 -51zM676 650h-54c11 -18 12 -45 12 -58c0 -9 -1 -22 -2 -31c5 11 16 37 20 48c7 16 12 28 24 41zM163 601l254 -559l37 91\nc-63 139 -191 419 -191 487c0 21 8 26 13 30h-142c2 -2 20 -30 29 -49z"
            },
            W: {
                x: 1084,
                d: "M976 603l-214 -599c-5 -15 -8 -23 -20 -23s-15 7 -21 24l-166 438l-193 -442c-6 -13 -9 -20 -20 -20c-12 0 -15 8 -20 23l-214 601c-4 11 -16 42 -39 45c-5 1 -19 3 -19 17c0 18 15 18 33 18h212c18 0 34 0 34 -18c0 -15 -16 -17 -19 -17c-17 -2 -37 -15 -37 -26\nc0 -35 39 -159 62 -227l71 -204l130 299c-12 34 -25 70 -38 100c-21 53 -39 55 -57 58c-14 3 -16 14 -16 17c0 18 16 18 34 18h241c18 0 33 0 33 -18c0 -15 -16 -17 -21 -17c-25 -2 -44 -19 -48 -24c3 -85 123 -391 131 -409c50 146 94 275 94 361c0 68 -24 70 -47 72\nc-9 1 -16 8 -16 17c0 18 15 18 33 18h142c18 0 33 0 33 -18c0 -7 -4 -15 -17 -17c-19 -2 -29 -13 -41 -47zM959 650h-50c15 -26 15 -49 15 -86c11 29 25 70 35 86zM533 601l209 -552c8 26 22 64 38 108c-17 42 -151 385 -151 463c0 5 0 22 10 30h-132c11 -15 19 -32 26 -49z\nM145 604l199 -557l42 97c-27 74 -148 408 -148 481c0 16 3 20 6 25h-119c10 -17 17 -36 20 -46z"
            },
            X: {
                x: 786,
                d: "M490 685h200c18 0 33 0 33 -18c0 -15 -14 -16 -24 -17c-81 -7 -133 -73 -183 -139l-50 -65c-13 -18 -24 -32 -26 -35l136 -201c41 -61 105 -153 119 -166c3 -2 8 -6 25 -9c8 -2 16 -7 16 -17c0 -18 -15 -18 -33 -18h-267c-18 0 -33 0 -33 18c0 17 17 17 26 17\nc20 0 44 2 62 14c-8 23 -17 50 -127 217c-38 -42 -77 -85 -113 -128c-6 -8 -22 -26 -22 -53c0 -1 0 -44 37 -50c8 -1 22 -3 22 -17c0 -18 -16 -18 -34 -18h-171c-18 0 -33 0 -33 18c0 13 11 15 21 17c40 8 86 60 160 143l108 124c-20 31 -162 245 -219 317\nc-17 22 -21 27 -51 31c-15 3 -15 16 -15 17c0 18 15 18 33 18h257c18 0 33 0 33 -18c0 -17 -17 -17 -24 -17c-11 0 -33 -2 -49 -11c7 -21 13 -39 111 -190c16 19 38 48 56 73c28 37 40 52 40 80c0 4 0 44 -35 48c-9 1 -19 5 -19 17c0 18 15 18 33 18zM599 650h-65\nc9 -14 12 -31 12 -47c19 18 35 34 53 47zM193 84l-50 -49h65c-3 4 -9 15 -13 35c0 2 -2 13 -2 14zM268 650h-128c81 -101 295 -428 336 -494c40 -68 51 -95 51 -110c0 -4 -1 -8 -2 -11h132c-72 91 -291 425 -327 481c-62 101 -62 114 -62 134z"
            },
            Y: {
                x: 784,
                d: "M453 335l129 208c10 16 13 32 13 41c0 15 -9 60 -59 66c-7 1 -22 2 -22 17c0 18 16 18 34 18h153c18 0 33 0 33 -18c0 -13 -12 -16 -19 -17c-30 -7 -70 -71 -111 -136l-130 -210v-199c0 -59 7 -70 63 -70c13 0 30 0 30 -17c0 -18 -15 -18 -33 -18h-282\nc-18 0 -34 0 -34 18c0 17 19 17 26 17c59 1 67 7 67 70v158l-200 355c-15 26 -17 31 -38 32c-6 0 -23 1 -23 17c0 18 16 18 34 18h221c18 0 33 0 33 -18c0 -17 -17 -17 -25 -17c-14 0 -16 -1 -28 -5c4 -16 4 -18 13 -33c4 -8 15 -28 20 -35zM656 650h-50\nc9 -10 16 -25 20 -38c5 7 26 33 30 38zM335 35h115c-11 22 -11 56 -11 68v187c-19 31 -116 205 -134 237c-8 15 -30 52 -38 67c-17 34 -17 41 -16 56h-117l212 -378v-169c0 -11 0 -46 -11 -68z"
            },
            Z: {
                x: 708,
                d: "M618 649l-376 -614h142c142 0 220 96 237 178c3 20 5 30 20 30c8 0 17 -7 17 -18l-27 -197c-4 -26 -4 -28 -34 -28h-513c-18 0 -34 0 -34 18c0 3 0 5 9 18l376 614h-81c-34 0 -104 -3 -161 -40c-61 -38 -70 -82 -74 -106c-2 -8 -8 -15 -17 -15c-7 0 -18 5 -18 19l2 13\nl15 135c3 26 3 29 35 29h458c18 0 33 0 33 -18c0 -3 0 -5 -9 -18zM135 650l-5 -45c12 11 31 29 58 44v1h-53zM476 650l-377 -615h102l377 615h-102zM529 36v-1h68c5 36 7 47 9 67c-23 -28 -49 -50 -77 -66z"
            }
        }
    };
});
define("font/map/kf-ams-cal", [], function(require) {
    return {
        meta: {
            fontFamily: "KF AMS CAL",
            src: "KF_AMS_CAL.woff"
        },
        data: {
            A: {
                x: 871,
                d: "M618 165h-272c-22 -35 -137 -215 -206 -215c-47 0 -90 44 -90 84c0 28 22 76 37 76c4 0 6 -6 7 -8c9 -36 40 -60 77 -60c54 0 158 163 217 254c52 81 126 202 205 371l7 11c18 27 59 44 73 44c11 0 11 -4 11 -20c0 -24 -1 -50 -1 -74c0 -102 4 -204 11 -306\nc8 -137 17 -177 24 -210c12 -60 18 -87 46 -87c2 0 4 0 11 4c3 2 24 13 37 13c5 0 9 -2 9 -7c0 -20 -77 -65 -122 -65c-43 0 -48 22 -59 67c-5 19 -17 65 -22 128zM598 599h-1c-13 -25 -40 -83 -90 -172c-6 -11 -64 -115 -134 -223c26 16 41 16 57 16h183\nc-5 52 -9 127 -10 147c-3 58 -5 116 -5 174v58z"
            },
            B: {
                x: 735,
                d: "M300 679l-20 -132c121 150 249 159 283 159c74 0 122 -43 122 -101c0 -94 -117 -166 -197 -200c102 -19 158 -82 158 -166c0 -161 -207 -261 -348 -261c-108 0 -147 66 -147 69c0 18 51 49 73 49c8 0 9 -2 13 -7c13 -14 50 -56 127 -56c91 0 197 40 197 164\nc0 95 -82 155 -183 155c-21 0 -44 -4 -48 -4c-3 0 -10 1 -10 7c0 25 69 49 81 53c104 35 199 67 199 155c0 53 -49 88 -103 88c-63 0 -105 -30 -151 -95c-71 -103 -109 -234 -131 -320c-30 -116 -68 -189 -81 -212c-16 -28 -58 -46 -74 -46c-2 0 -10 0 -10 7c0 1 0 3 5 12\nc49 97 68 158 105 328c18 86 42 214 54 312c-19 -9 -45 -23 -57 -23c-4 0 -11 0 -11 6c0 18 38 37 84 60c20 10 50 25 61 25c9 0 11 -3 11 -10c0 -3 0 -5 -2 -16z"
            },
            C: {
                x: 622,
                d: "M534 157c0 -25 -133 -181 -294 -181c-116 0 -190 81 -190 227c0 52 12 207 131 351c46 56 165 151 310 151c36 0 81 -8 81 -63c0 -51 -58 -149 -60 -153c-12 -16 -49 -38 -69 -38c-6 0 -11 0 -11 7c0 2 0 4 6 15c12 20 49 91 49 126c0 37 -24 51 -62 51\nc-103 0 -157 -52 -201 -116c-53 -79 -89 -198 -89 -288c0 -150 81 -215 171 -215c65 0 109 34 140 81c12 17 16 24 36 37c1 0 25 15 42 15c5 0 10 -1 10 -7z"
            },
            D: {
                x: 845,
                d: "M233 0h-103c-22 0 -23 1 -23 7c0 10 24 33 61 46c70 175 121 366 137 575c-126 -6 -154 -41 -168 -81c-6 -19 -9 -25 -30 -39c-8 -5 -31 -19 -47 -19c-6 0 -10 2 -10 8c0 8 19 91 152 150c84 36 143 36 229 36c95 0 187 0 273 -56c49 -31 91 -87 91 -176\nc0 -273 -329 -451 -562 -451zM238 55h54c242 0 418 157 418 353c0 220 -256 220 -323 220c-14 -120 -35 -294 -149 -573z"
            },
            E: {
                x: 637,
                d: "M261 363c-49 17 -94 53 -94 112c0 122 170 230 306 230c45 0 114 -9 114 -68c0 -57 -62 -96 -92 -96c-3 0 -10 0 -10 7c0 3 3 8 5 11c4 6 12 20 12 36c0 52 -76 55 -95 55c-140 0 -155 -109 -155 -132c0 -40 28 -109 169 -115c5 0 10 -1 10 -7c0 -11 -35 -46 -82 -48\nc-158 -6 -214 -154 -214 -203c0 -75 79 -112 150 -112c89 0 131 58 152 87c23 31 61 44 73 44c5 0 10 -1 10 -7c0 -21 -134 -179 -301 -179c-95 0 -169 48 -169 124c0 24 11 153 211 261z"
            },
            F: {
                x: 913,
                d: "M863 645c0 -27 -54 -57 -78 -57c-3 0 -6 1 -8 3c-3 5 0 15 -2 21c-18 22 -88 17 -113 17h-132c-18 -92 -52 -180 -78 -270h240c12 0 19 1 19 -6c0 -31 -57 -59 -79 -59c-4 0 -10 3 -10 7c0 1 1 2 2 3h-193c-26 -60 -92 -224 -125 -262c-32 -37 -91 -74 -142 -74\nc-49 0 -94 27 -112 74c-1 2 -2 4 -2 6c0 17 55 49 78 49c7 0 7 -4 10 -10c18 -39 51 -63 95 -63c14 12 68 137 74 150c44 98 103 253 142 436c0 1 4 19 4 19h-62c-31 0 -61 -2 -92 -2c-14 -15 -44 -33 -66 -33c-6 0 -8 1 -10 7c16 54 105 83 155 83h377c31 0 108 7 108 -39z\n"
            },
            G: {
                x: 657,
                d: "M442 159c-66 -53 -143 -91 -218 -91c-129 0 -174 103 -174 204c0 235 203 433 429 433c14 0 48 0 85 -12c12 -4 43 -14 43 -55c0 -34 -40 -90 -48 -101c-27 -37 -60 -64 -89 -64c-6 0 -11 0 -11 7c0 4 3 8 7 12c4 6 56 70 56 104c0 32 -25 40 -36 43c-25 8 -54 11 -73 11\nc-113 0 -162 -53 -189 -85c-58 -70 -89 -173 -89 -250c0 -113 57 -192 154 -192c124 0 183 148 193 182c7 25 8 30 31 45c21 14 38 19 47 19s11 -3 11 -9c0 -4 -21 -84 -41 -153c-3 -12 -20 -69 -46 -125c-75 -163 -193 -201 -265 -201c-93 0 -161 41 -161 50\nc0 11 35 46 80 48c59 -40 130 -43 147 -43c34 0 55 2 94 70c10 16 36 62 63 153z"
            },
            H: {
                x: 880,
                d: "M360 335h268c30 102 61 203 101 301c4 9 10 14 11 16c19 17 48 31 63 31c4 0 10 0 10 -7c0 -2 0 -4 -3 -11c-86 -228 -135 -416 -152 -527c-3 -22 -10 -77 -10 -89c0 -31 23 -43 41 -43c1 0 20 1 31 3c19 3 20 4 24 12c15 39 68 53 76 53c5 0 10 -2 10 -8\nc0 -32 -74 -115 -207 -115c-43 0 -60 26 -60 55c0 79 41 242 52 284c-16 -10 -35 -10 -46 -10h-225c-26 -92 -56 -182 -91 -270c-4 -10 -6 -12 -11 -18c-20 -20 -51 -33 -65 -33c-2 0 -10 0 -10 7c0 1 0 3 9 26c46 122 74 218 95 288h-72c-22 0 -23 1 -23 7\nc0 15 46 47 74 48h36c21 86 24 104 29 132c12 69 15 116 15 117c0 16 -7 44 -48 44c-90 0 -119 -38 -146 -89c-21 -38 -68 -50 -76 -50c-3 0 -10 0 -10 7c0 5 26 72 107 127c47 32 115 60 191 60c12 0 67 0 67 -56c0 -12 -7 -79 -19 -146c-10 -48 -23 -99 -36 -146z"
            },
            I: {
                x: 759,
                d: "M429 683h257c22 0 23 -1 23 -7c0 -12 -23 -28 -32 -33c-26 -15 -35 -15 -55 -15h-85c-20 0 -21 -1 -27 -10c-41 -61 -65 -158 -92 -267c-34 -136 -64 -235 -135 -296h191c50 0 53 6 60 27c10 25 57 48 74 48c3 0 11 0 11 -7c0 -36 -87 -123 -204 -123h-342\nc-22 0 -23 1 -23 7c0 10 22 27 33 33c25 15 36 15 55 15h64c27 0 28 1 38 12c41 53 64 131 93 249c36 144 65 251 128 312h-91c-34 0 -67 -2 -101 -6c-67 -9 -70 -18 -82 -56c-8 -22 -56 -47 -74 -47c-6 0 -11 1 -11 8c0 13 23 81 125 124c24 9 78 32 202 32z"
            },
            J: {
                x: 893,
                d: "M618 683h202c22 0 23 -1 23 -7c0 -11 -34 -45 -79 -48c-23 -2 -27 -5 -43 -23c-75 -84 -123 -266 -160 -411c-18 -71 -36 -144 -128 -223c-49 -42 -130 -90 -215 -90c-98 0 -168 60 -168 152c0 47 11 57 23 66c28 21 51 26 57 26c9 0 10 -6 10 -9c0 -4 -5 -21 -5 -41\nc0 -87 71 -139 149 -139c101 0 162 100 186 193c66 260 112 411 222 499h-133c-98 0 -170 -43 -203 -149c-6 -18 -51 -46 -74 -46c-4 0 -10 0 -10 7c0 2 12 86 121 164c46 33 129 79 225 79z"
            },
            K: {
                x: 803,
                d: "M216 638c-48 -24 -50 -24 -59 -24c-4 0 -11 0 -11 6c0 18 38 37 84 60c20 10 50 25 61 25s11 -4 11 -16c0 -4 -2 -45 -7 -85c-30 -254 -160 -581 -165 -588c-16 -19 -52 -38 -70 -38c-2 0 -10 0 -10 7c0 1 0 3 5 15c59 156 150 420 161 638zM747 127c0 -3 -4 -28 -30 -60\nc-38 -48 -119 -89 -197 -89c-30 0 -51 13 -86 47c-72 73 -173 289 -173 375c0 62 107 138 149 169c79 56 217 136 285 136c37 0 58 -26 58 -60c0 -49 -48 -52 -50 -52c-9 0 -12 5 -12 11c0 4 2 9 2 16c0 26 -15 42 -36 42c-47 0 -224 -102 -298 -175\nc-35 -36 -38 -46 -38 -62c0 -90 141 -404 238 -404c2 0 100 0 127 76c5 16 7 19 19 27c14 11 25 12 30 12c12 0 12 -8 12 -9z"
            },
            L: {
                x: 803,
                d: "M199 93c13 3 27 4 40 4c50 0 105 -16 133 -24c66 -18 116 -33 164 -33c15 0 16 0 24 12c4 6 10 14 15 30c6 17 8 23 35 40c17 12 41 23 57 23c1 0 7 0 7 -7c0 -3 -6 -28 -26 -55c-32 -44 -114 -105 -195 -105c-51 0 -102 15 -153 29c-63 18 -98 27 -148 28\nc-23 -24 -25 -26 -42 -36c-26 -17 -47 -21 -51 -21c-7 0 -9 4 -9 6c0 5 10 23 37 48c14 13 40 57 47 69c30 60 38 91 56 160c19 75 66 255 146 337c98 99 186 107 218 107c56 0 84 -41 84 -95c0 -41 -9 -51 -27 -65c-25 -18 -52 -29 -68 -29c-1 0 -7 0 -7 7c0 5 5 19 5 37\nc0 14 -2 83 -71 83c-32 0 -40 -9 -52 -22c-62 -69 -100 -189 -135 -327c-13 -53 -30 -117 -84 -201z"
            },
            M: {
                x: 1189,
                d: "M408 559c-28 -144 -84 -302 -131 -415c-20 -51 -79 -194 -141 -194c-5 0 -29 1 -54 14c-32 17 -32 29 -32 38c0 28 22 73 36 73c2 0 4 0 9 -5c22 -20 59 -28 72 -28c19 0 41 0 121 213c70 191 90 323 102 407c3 21 45 43 63 43c3 0 8 0 11 -5c0 -2 5 -29 8 -43\nc17 -97 34 -180 63 -293c35 -140 53 -178 78 -231c99 84 391 434 485 548c19 22 20 23 24 23c7 0 8 -11 8 -15c0 -8 -1 -10 -2 -17c-29 -124 -73 -427 -73 -538c0 -4 0 -34 3 -66c2 -23 4 -39 28 -42c11 7 31 16 44 16c3 0 9 -1 9 -7c0 -20 -76 -64 -118 -64s-46 29 -47 40\nc-3 27 -4 53 -4 80c0 117 36 342 50 427l-219 -255c-70 -80 -122 -138 -198 -211c-18 -18 -20 -18 -24 -18c-10 0 -21 22 -43 65c-57 116 -101 330 -128 460z"
            },
            N: {
                x: 1107,
                d: "M385 574c-19 -107 -50 -256 -115 -437c-16 -46 -67 -187 -131 -187c-28 0 -89 20 -89 51c0 24 21 74 37 74c1 0 3 0 8 -4c26 -24 65 -29 75 -29c20 0 32 18 40 33c27 48 121 311 149 577c1 11 2 20 12 29c29 24 49 24 51 24c9 0 10 -1 16 -21c27 -90 59 -194 112 -336\nc48 -129 79 -208 134 -291c31 129 63 259 98 387c46 167 70 240 98 276c12 13 57 55 157 55c14 0 20 0 20 -16c0 -22 -19 -75 -39 -76c-123 -2 -143 -40 -144 -42c-20 -46 -96 -320 -156 -583c-11 -49 -12 -50 -26 -63c-11 -8 -32 -20 -46 -20c-9 0 -10 1 -24 22\nc-61 95 -89 157 -152 327c-45 123 -67 198 -85 250z"
            },
            O: {
                x: 820,
                d: "M770 482c0 -308 -286 -504 -481 -504c-166 0 -239 128 -239 268c0 300 296 459 331 459c2 0 10 0 10 -7c0 -13 -30 -31 -40 -37c-146 -86 -216 -236 -216 -372c0 -143 78 -256 220 -256c185 0 330 202 330 406c0 131 -62 211 -159 211c-40 0 -60 -15 -84 -42\nc-37 -42 -60 -91 -69 -111c-11 -23 -12 -26 -30 -39c-15 -11 -31 -21 -50 -21c-5 0 -10 1 -10 7c0 2 30 92 110 171c7 6 95 90 199 90c122 0 178 -96 178 -223z"
            },
            P: {
                x: 812,
                d: "M377 683h134c116 0 251 -45 251 -159c0 -165 -236 -319 -418 -319c-13 0 -19 0 -19 7c0 6 8 17 26 29c28 18 39 18 60 19c194 6 266 146 266 221c0 100 -117 147 -239 147h-52c-30 -278 -112 -508 -164 -630c-11 -26 -59 -48 -75 -48c-7 0 -10 4 -10 7c0 1 0 3 6 18\nc84 209 144 428 162 653c-126 -6 -154 -41 -168 -81c-6 -19 -9 -25 -30 -39c-8 -5 -31 -19 -47 -19c-6 0 -10 2 -10 8c0 2 3 31 38 71c41 47 148 115 289 115z"
            },
            Q: {
                x: 774,
                d: "M160 28h29c164 0 237 34 302 93c95 84 136 197 136 296c0 133 -76 233 -202 233c-177 0 -290 -195 -290 -320c0 -103 65 -173 155 -173c10 0 52 1 93 24c14 7 31 17 45 17c4 0 11 0 11 -6c0 -24 -114 -90 -215 -90c-111 0 -174 80 -174 186c0 208 237 417 441 417\nc147 0 221 -110 221 -246c0 -241 -218 -414 -391 -463c144 -49 189 -65 255 -65c27 0 48 3 62 43c5 13 7 20 30 35c16 11 37 19 47 19c5 0 9 -3 9 -8c0 -41 -97 -144 -214 -144c-79 0 -150 25 -219 50c-100 35 -141 44 -208 47c-5 0 -10 0 -10 7c0 12 24 28 32 33\nc25 15 36 15 55 15z"
            },
            R: {
                x: 916,
                d: "M434 628h-47c-31 -320 -158 -602 -160 -607c-11 -19 -53 -43 -73 -43c-2 0 -10 0 -10 7c0 1 0 3 5 14c44 107 81 217 108 329c34 146 43 239 48 300c-126 -6 -154 -41 -168 -81c-6 -19 -9 -25 -30 -39c-8 -5 -31 -19 -47 -19c-6 0 -10 2 -10 8c0 8 18 91 152 150\nc84 36 140 36 240 36c151 0 319 0 319 -125c0 -104 -98 -209 -243 -262c23 -27 41 -64 62 -114c34 -78 65 -149 116 -149c49 0 71 31 87 54c21 29 60 43 72 43c3 0 11 0 11 -7c0 -22 -115 -145 -236 -145c-59 0 -85 46 -131 151c-52 118 -72 138 -114 145c-1 0 -5 0 -5 6\nc0 11 34 46 83 48c165 6 213 126 213 187c0 66 -50 113 -242 113z"
            },
            S: {
                x: 803,
                d: "M147 219c-7 -16 -12 -34 -12 -52c0 -68 82 -134 184 -134c152 0 201 84 201 151c0 84 -91 130 -162 162c-48 21 -135 61 -135 145c0 114 160 214 291 214c14 0 60 0 107 -22c27 -12 53 -26 53 -75c0 -39 -11 -52 -24 -62c-20 -17 -44 -27 -57 -27c-4 0 -10 0 -10 7\nc0 3 1 7 2 10c3 11 4 16 4 30c0 43 -26 55 -45 64c-43 19 -85 20 -96 20c-14 0 -70 -3 -100 -29c-31 -27 -40 -66 -40 -87c0 -57 46 -98 116 -130c81 -36 181 -82 181 -177c0 -136 -189 -249 -352 -249c-122 0 -203 74 -203 146c0 100 98 143 132 143c6 0 12 0 12 -7\nc0 -14 -35 -36 -47 -41z"
            },
            T: {
                x: 900,
                d: "M502 621l-112 -446c-21 -83 -65 -185 -71 -195c-15 -21 -64 -48 -84 -48c-5 0 -9 1 -9 7c0 2 0 4 6 16c29 65 50 128 62 177l114 453c2 10 6 25 23 36h-199c-44 0 -52 -8 -62 -20c-20 -26 -22 -45 -23 -62c-2 -24 -65 -57 -89 -57c-8 0 -8 6 -8 12c0 82 139 189 263 189\nh437c19 0 21 0 29 6c25 18 51 28 62 28c5 0 9 -2 9 -7c0 -13 -50 -89 -182 -89h-166z"
            },
            U: {
                x: 805,
                d: "M560 296l-1 1c-43 -58 -88 -118 -157 -183c-102 -95 -193 -136 -255 -136c-71 0 -97 54 -97 120c0 58 21 122 87 272c42 96 78 178 78 222c0 22 -9 36 -31 36c-27 0 -43 -10 -50 -15c-16 -11 -34 -19 -46 -19c-4 0 -11 0 -11 6c0 21 96 83 173 83c18 0 50 -1 50 -48\nc0 -45 -30 -116 -66 -198c-60 -137 -99 -227 -99 -297c0 -66 30 -107 78 -107c71 0 169 116 200 151c99 116 198 281 231 376c12 37 14 42 27 77c8 19 53 46 74 46c4 0 10 0 10 -7c0 -8 -13 -49 -25 -77c-29 -74 -143 -424 -143 -546c0 -14 7 -24 20 -26c11 6 29 15 41 15\nc5 0 10 -2 10 -7c0 -20 -75 -63 -115 -63c-24 0 -41 12 -41 39c0 77 35 201 58 285z"
            },
            V: {
                x: 726,
                d: "M286 86c172 134 346 339 346 420c0 24 -12 77 -79 85c-12 1 -17 1 -17 16c0 13 14 76 43 76c41 0 97 -31 97 -116c0 -67 -38 -156 -89 -234c-127 -193 -369 -378 -380 -378c-7 0 -7 12 -7 17c0 4 0 6 2 18c12 67 25 187 25 288c0 113 -12 342 -158 355c-14 1 -19 1 -19 8\nc0 14 35 42 69 42c182 0 182 -298 182 -369c0 -15 0 -105 -15 -228z"
            },
            W: {
                x: 1102,
                d: "M257 129h1c166 209 256 378 317 493c-2 5 -6 14 -6 18c0 18 43 43 62 43c10 0 11 -4 14 -11c41 -110 99 -339 110 -554c1 -23 1 -24 2 -24c7 0 251 308 251 422c0 66 -46 73 -60 75c-7 1 -12 2 -12 16c0 15 15 76 42 76c31 0 74 -26 74 -106c0 -173 -214 -464 -338 -603\nc-16 -18 -17 -19 -22 -19c-3 0 -6 4 -6 8c-1 3 -2 49 -2 53c-1 53 -6 114 -12 167c-19 163 -53 301 -83 394c-4 -7 -30 -59 -69 -128c-109 -190 -220 -342 -331 -474c-16 -19 -17 -20 -22 -20c-7 0 -7 12 -7 16c0 9 1 13 4 26c6 23 41 197 41 348c0 43 -3 119 -24 182\nc-27 81 -70 102 -113 106c-13 1 -18 1 -18 8c0 14 35 42 69 42c33 0 87 -11 126 -90c34 -72 34 -184 34 -213c0 -85 -9 -169 -22 -251z"
            },
            X: {
                x: 858,
                d: "M479 551l15 -153c69 42 111 68 141 88c85 58 88 73 88 98c0 39 -24 43 -32 44c-1 0 -7 1 -7 7c0 14 46 48 77 48c29 0 47 -22 47 -56c0 -42 0 -77 -311 -263c10 -104 13 -130 16 -162c8 -84 14 -147 71 -147c2 0 7 0 10 1c19 34 63 47 73 47c6 0 11 0 11 -7\nc0 -28 -65 -96 -160 -96c-74 0 -81 66 -89 145c-2 17 -17 167 -17 169c-52 -33 -110 -65 -171 -105c-106 -71 -106 -83 -106 -110c0 -39 24 -43 32 -44c4 -1 7 -2 7 -7c0 -15 -46 -48 -76 -48s-48 22 -48 56c0 29 2 59 116 140c72 50 207 131 243 151l-14 147\nc-10 103 -23 134 -78 134l-2 -1c-18 -34 -62 -47 -73 -47c-5 0 -11 0 -11 7c0 28 65 96 160 96c70 0 80 -57 88 -132z"
            },
            Y: {
                x: 786,
                d: "M427 97c222 216 269 394 269 425c0 29 -15 76 -74 83c-15 2 -19 3 -19 18c0 10 9 60 38 60c38 0 95 -31 95 -116c0 -52 -35 -224 -235 -454c-87 -101 -252 -248 -328 -248c-85 0 -123 73 -123 114c0 25 19 59 31 59c6 0 7 -6 9 -13c19 -83 100 -83 108 -83\nc49 0 149 84 151 86c2 1 2 3 3 11c1 13 5 82 5 142c0 91 -7 180 -26 260c-30 126 -87 191 -175 192c-22 -29 -53 -34 -59 -34c-2 0 -10 0 -10 8c0 15 61 76 130 76c209 0 214 -361 214 -466c0 -28 -1 -82 -4 -120z"
            },
            Z: {
                x: 830,
                d: "M481 351l-290 -265c19 4 43 4 49 4c53 0 128 -10 178 -16c92 -12 127 -12 157 -12c31 35 41 69 47 88c9 29 69 58 88 58c5 0 9 -1 9 -7s-18 -77 -79 -131c-32 -29 -91 -70 -164 -70c-49 0 -116 8 -164 15c-46 5 -111 13 -156 13c-16 0 -33 0 -51 -10\nc-13 -8 -34 -18 -47 -18c-7 0 -8 4 -8 7c0 7 19 24 32 35c83 70 155 135 157 137c130 117 177 164 184 172h-91c-17 0 -26 0 -26 9c0 4 9 34 62 34h100c87 89 133 140 186 202c-11 -3 -23 -3 -35 -3c-36 0 -77 8 -127 17c-60 11 -88 11 -97 11c-68 0 -81 -22 -94 -61\nc-9 -27 -69 -56 -88 -56c-8 0 -9 5 -9 8c0 1 8 59 98 116c53 32 113 55 177 55c39 0 75 -7 130 -17c58 -11 83 -11 101 -11c29 22 55 28 62 28s8 -4 8 -7c0 -10 -133 -160 -228 -255c-8 -9 -24 -24 -24 -26c0 -1 14 -2 16 -2c23 -1 65 -6 65 -31c0 -21 -31 -38 -47 -38\nc-5 0 -13 0 -13 10c-1 2 -2 6 -3 7c-6 5 -25 9 -65 10z"
            }
        }
    };
});
define("font/map/kf-ams-frak", [], function(require) {
    return {
        meta: {
            fontFamily: "KF AMS FRAK",
            src: "KF_AMS_FRAK.woff"
        },
        data: {
            A: {
                x: 787,
                d: "M428 319l-178 -164c40 0 65 -21 87 -43l48 -48c12 1 94 53 144 85v325c0 42 -3 88 -5 127l127 96l16 -11c-15 -13 -36 -32 -42 -42c-20 -26 -16 -166 -16 -266c0 -61 2 -121 5 -181c1 -11 2 -23 5 -32c10 -26 34 -89 52 -89c16 0 43 16 60 25l6 -16l-144 -112\nc-31 49 -47 98 -63 150l-203 -150c-33 55 -77 104 -146 104c-37 0 -69 -17 -105 -40l-16 11l112 85c65 50 141 105 181 158c34 44 43 107 43 161c0 97 -72 181 -171 181c-52 0 -111 -38 -111 -95c0 -45 95 -92 95 -150c0 -59 -64 -107 -117 -133l-16 11c30 17 64 47 64 80\nc0 10 -5 19 -11 27c-31 45 -79 74 -79 133c0 40 29 70 57 98c24 24 113 82 193 82c97 0 176 -73 176 -170c0 -68 -18 -136 -48 -197z"
            },
            B: {
                x: 866,
                d: "M539 -27c-107 0 -208 75 -287 75c-51 0 -101 -22 -143 -48l-12 21l124 73c125 73 144 130 147 158c3 32 7 103 7 150c0 162 -95 226 -158 226c-57 0 -97 -27 -97 -87c0 -33 89 -105 89 -152c0 -72 -90 -118 -123 -134l-15 11c27 15 64 48 64 81c0 28 -85 92 -85 160\nc0 40 23 70 51 98c24 24 90 86 183 86c58 0 117 -56 144 -111c51 53 126 106 202 106c23 0 47 -4 64 -21c30 -30 15 -160 78 -160c15 2 30 6 44 11v-22c-37 -15 -71 -25 -103 -48l-89 -63c110 0 176 -65 176 -159c0 -43 -7 -63 -22 -101c-118 -99 -179 -150 -239 -150z\nM457 350c98 35 143 46 228 105c-24 11 -34 55 -38 85c-10 67 -34 94 -76 94c-71 0 -105 -57 -122 -111c6 -25 10 -81 10 -124c0 -14 -1 -35 -2 -49zM422 207l-117 -90c108 0 220 -80 298 -80c75 0 112 74 112 138c0 99 -89 166 -181 166c-27 0 -54 -5 -79 -16\nc-4 -41 -14 -81 -33 -118z"
            },
            C: {
                x: 654,
                d: "M258 601l32 -16c63 45 135 74 207 101l28 -81c6 -14 9 -23 29 -23c16 0 38 5 50 9v-23c-35 -17 -78 -36 -117 -41c-27 16 -57 64 -80 101c-45 -14 -49 -58 -49 -68c0 -18 6 -48 13 -68c5 -16 20 -62 20 -93c0 -106 -89 -147 -174 -160l-12 16c28 7 53 22 74 43\nc16 16 16 45 16 69c0 41 -30 114 -30 167c0 10 1 20 3 30c-105 -3 -138 -116 -138 -202c0 -84 20 -169 80 -229c59 -60 142 -87 225 -87c33 0 58 6 72 13l86 47v-20c-42 -35 -96 -71 -144 -102c-27 -6 -54 -8 -82 -8c-88 0 -176 28 -238 89c-63 62 -79 166 -79 254\nc0 63 15 125 43 181c51 39 106 75 165 101z"
            },
            D: {
                x: 819,
                d: "M391 559l16 -11c-26 -17 -85 -68 -85 -106c0 -40 90 -117 90 -165c0 -72 -87 -120 -148 -146c43 -9 92 -25 134 -40c44 -16 117 -43 137 -43c64 0 83 34 117 85c35 53 42 127 42 192c0 71 -17 145 -69 197c-67 67 -162 90 -256 90c-83 0 -172 -16 -231 -76\nc-44 -44 -64 -110 -67 -169l-21 11c0 82 26 157 85 215c66 65 170 97 261 97c61 0 113 -2 179 -20c112 -32 194 -142 194 -260c0 -96 -21 -174 -55 -263c-63 -69 -163 -174 -222 -174c-21 0 -79 22 -112 35c-63 25 -139 56 -202 56c-46 0 -83 -42 -107 -80l-16 16\nc26 51 63 96 107 133c65 0 160 29 160 96c0 28 -14 53 -32 74c-7 8 -54 67 -54 85c0 77 86 135 155 171z"
            },
            E: {
                x: 655,
                d: "M257 601l32 -16c64 45 136 74 208 101l28 -81c7 -16 11 -23 32 -23c16 0 36 4 48 8v-22c-36 -17 -80 -36 -119 -41c-26 15 -56 63 -79 100c-22 -8 -48 -36 -49 -61c-1 -20 3 -42 9 -60c15 -45 23 -69 23 -137l102 62c21 -29 47 -55 77 -75v-9l-67 -38l-59 42\nc-5 3 -10 5 -15 5c-14 0 -27 -6 -38 -15c-18 -81 -101 -112 -175 -123l-16 16c29 5 55 20 80 37c16 18 17 48 17 73c0 64 -32 127 -32 188c0 10 1 21 4 32c-105 -3 -139 -116 -139 -202c0 -84 20 -169 80 -229s143 -87 226 -87c56 0 116 34 158 60v-20\nc-45 -37 -94 -70 -144 -102c-27 -5 -54 -8 -82 -8c-88 0 -176 27 -233 84c-67 66 -84 167 -84 259c0 63 15 125 42 181c52 39 106 75 165 101z"
            },
            F: {
                x: 710,
                d: "M407 293l122 117c23 -24 43 -49 54 -80l-43 -59c-12 21 -28 43 -53 43c-17 0 -49 -22 -71 -40l87 -112c16 -23 21 -59 21 -88c0 -129 -140 -229 -266 -229c-86 0 -116 78 -116 147c0 33 7 65 7 100c0 72 -22 70 -35 70c-24 0 -48 -12 -59 -18l-5 21c58 39 105 54 131 54\nc36 0 51 -46 51 -85c0 -42 -14 -84 -14 -129c0 -54 17 -78 29 -90c17 -18 39 -35 64 -35c86 0 133 79 133 157c0 50 -53 123 -90 176c-33 47 -80 108 -80 154c0 70 84 137 149 176c-56 34 -128 74 -187 74c-76 0 -108 -87 -122 -154l-16 11c11 110 72 212 197 212\nc96 0 193 -106 272 -106c24 0 60 37 84 63l9 -14c-37 -50 -93 -129 -136 -129c-23 0 -50 11 -73 25c-57 -18 -103 -55 -103 -110c0 -40 33 -87 59 -122z"
            },
            G: {
                x: 745,
                d: "M656 606l-176 -154c16 2 30 3 45 3c57 0 97 -23 114 -37c33 -27 56 -70 56 -115c0 -79 -37 -152 -93 -207l-154 -104c-31 -11 -51 -17 -83 -17c-86 0 -179 33 -231 85c-68 68 -84 164 -84 259c0 65 16 124 44 183c50 36 102 70 162 99l32 -16l112 75l10 -11\nc-27 -23 -53 -53 -53 -90c0 -26 16 -96 26 -137l51 27c33 22 78 57 107 86c-47 16 -94 53 -103 93l68 64c20 -41 58 -85 101 -85c14 0 30 7 45 22zM219 213l-16 16c51 12 92 59 92 119c0 68 -30 126 -30 185c0 10 1 20 3 31c-103 -6 -138 -111 -138 -197\nc0 -88 21 -177 83 -239c52 -52 113 -89 186 -89c51 0 95 7 140 44c52 43 69 113 69 172c0 86 -91 147 -172 147c-16 0 -33 -2 -50 -6c0 -44 -5 -93 -37 -125c-36 -35 -82 -50 -130 -58z"
            },
            H: {
                x: 753,
                d: "M426 -107l48 64c11 -22 33 -37 58 -37c88 0 85 172 85 271c0 149 -48 213 -138 213c-61 0 -113 -47 -155 -97c21 -52 28 -98 28 -122c0 -17 -1 -39 -10 -51c-31 -42 -107 -135 -125 -150c-26 24 -60 52 -89 52s-52 -25 -66 -40l-12 14c29 52 78 106 119 106\nc32 0 69 -30 89 -54c20 25 22 44 24 69c0 86 -101 198 -101 300c0 88 114 178 197 236c34 -34 78 -60 128 -60c22 0 33 13 86 54l13 -15c-43 -42 -110 -108 -142 -108c-49 0 -105 28 -152 52c-28 -28 -49 -60 -49 -111c0 -39 30 -103 52 -149c53 55 115 100 181 138\nc64 0 129 -14 181 -53c16 -57 27 -116 27 -176c0 -87 -7 -175 -43 -255c-51 -47 -127 -117 -165 -117c-26 0 -51 8 -69 26z"
            },
            I: {
                x: 605,
                d: "M544 671l11 -16c-47 -44 -98 -82 -155 -112c-81 30 -143 58 -186 58c-78 0 -122 -77 -147 -145l-17 11c50 134 92 219 186 219c42 0 104 -29 154 -53c17 -8 34 -16 53 -16c43 0 77 32 101 54zM65 -16l-5 16c32 44 109 124 114 128c27 -58 32 -68 45 -81\nc17 -17 45 -29 69 -29c88 0 112 109 112 189c0 90 -18 159 -18 222c0 12 1 23 2 34c40 44 89 90 140 122l12 -7c-33 -30 -65 -69 -65 -114c0 -88 15 -147 15 -219c0 -62 -14 -126 -59 -171c-57 -57 -131 -101 -213 -101c-37 0 -69 24 -96 48z"
            },
            J: {
                x: 640,
                d: "M137 496l-15 10c30 77 91 180 170 180c71 0 130 -106 197 -106c36 0 69 16 101 32v-21c-52 -27 -129 -65 -160 -75c-65 13 -104 86 -170 86c-61 0 -97 -58 -123 -106zM55 60l-5 13c40 33 90 71 136 71c58 0 63 -43 63 -91c0 -94 28 -156 96 -156c75 0 107 73 107 140\nc0 112 -55 218 -55 322c0 10 1 20 2 29c39 54 86 102 138 144l16 -10c-38 -38 -69 -73 -69 -139c0 -86 42 -243 42 -298c0 -113 -108 -224 -218 -224c-36 0 -76 6 -101 32c-22 22 -29 55 -32 85c-7 62 -13 102 -53 102c-20 0 -50 -9 -67 -20z"
            },
            K: {
                x: 774,
                d: "M668 348l-168 -76v-1c15 0 33 -4 46 -16c36 -35 27 -202 101 -202c24 0 54 9 74 24l3 -17c-38 -38 -92 -87 -136 -87c-56 0 -69 64 -75 112c-10 86 -25 144 -101 144c-31 0 -62 -9 -90 -22c0 -26 -1 -53 -5 -79c-44 -67 -104 -130 -176 -153c-39 3 -70 27 -91 57l57 66\nh10c13 -28 36 -62 69 -62c61 0 67 58 67 108c0 99 -59 211 -59 283c0 13 5 28 6 30c50 126 166 224 303 224c55 0 150 -21 150 -82c0 -33 -7 -67 -17 -99l-16 -5c0 96 -88 139 -176 139c-91 0 -169 -55 -169 -144c0 -34 7 -83 20 -123c56 59 134 133 208 133\nc48 0 64 -43 82 -91c6 -16 19 -50 56 -50c5 0 15 2 21 5zM323 230c39 18 75 32 119 40c33 13 68 28 100 44c-22 13 -32 46 -39 69c-10 33 -41 53 -66 53c-62 0 -106 -59 -136 -109c11 -28 17 -66 22 -97z"
            },
            L: {
                x: 713,
                d: "M647 197l16 -11c-35 -188 -83 -213 -171 -213c-91 0 -192 41 -282 80c-20 9 -42 16 -64 16c-30 0 -60 -43 -84 -84l-12 15c22 53 49 133 111 143h31c38 12 79 47 79 84c0 65 -93 153 -93 241c0 22 9 44 19 63c57 100 159 155 269 155c61 0 124 -20 124 -115\nc0 -26 -4 -71 -4 -88c0 -28 36 -31 67 -31v-18l-120 -30c-14 0 -40 21 -40 46c0 14 7 51 7 92c0 72 -50 102 -106 102c-69 0 -136 -44 -136 -122c0 -20 4 -37 10 -55c19 -60 75 -139 75 -200c0 -34 -107 -129 -116 -135c80 -34 171 -68 255 -68c42 0 86 6 117 37\nc26 26 38 61 48 96z"
            },
            M: {
                x: 1122,
                d: "M444 569c39 50 100 91 165 91c56 0 83 -36 100 -83c43 51 121 115 193 115c86 0 52 -132 125 -132c16 0 31 4 45 9v-15c-41 -21 -79 -48 -111 -80c-30 -59 -32 -132 -32 -197c0 -47 0 -107 10 -139c7 -22 29 -58 54 -58c6 0 37 10 58 21v-19l-144 -109l-57 126l-1 172\nc0 43 7 86 16 128c22 34 49 64 80 91c-18 2 -27 20 -43 69c-16 47 -25 69 -59 69c-57 0 -97 -37 -125 -82c6 -31 14 -79 14 -110c0 -106 -23 -211 -64 -308l-123 -150c-18 17 -50 43 -69 43c-29 0 -41 -14 -59 -35l-13 14c35 50 78 104 104 104c19 0 43 -14 79 -38\nc56 56 54 192 54 301c0 97 0 234 -101 234c-46 0 -74 -35 -93 -72c7 -28 10 -75 10 -82c0 -113 -13 -227 -72 -325l-170 -149c-22 23 -57 53 -85 53c-29 0 -52 -20 -69 -42l-11 16c26 44 71 106 117 106c32 0 66 -30 97 -58c17 13 34 28 52 46c26 26 35 69 43 104\nc12 56 17 112 17 169v41c0 111 -63 222 -158 222c-68 0 -99 -35 -99 -66c0 -81 91 -112 91 -175c0 -64 -64 -104 -123 -134l-15 11c31 17 64 49 64 81c0 10 -7 19 -13 27c-31 44 -72 80 -72 133c0 40 23 70 51 98c24 24 97 86 183 86c69 0 127 -69 152 -130c3 2 5 5 7 8z\n"
            },
            N: {
                x: 901,
                d: "M443 533c13 15 46 61 52 71c30 48 93 82 149 82c75 0 93 -27 107 -74c14 -49 24 -58 48 -58c15 0 32 4 45 11v-14c-30 -21 -61 -45 -88 -67c-37 -31 -37 -143 -37 -207c0 -63 10 -201 69 -201c22 0 46 13 63 22v-22l-144 -105l-64 133l1 167c0 40 0 96 6 117\nc9 33 48 68 75 96c-18 4 -26 15 -32 23c-18 24 -25 121 -86 121c-73 0 -124 -75 -153 -127c7 -42 15 -107 15 -150c0 -78 -7 -159 -43 -229c-5 -9 -13 -24 -16 -26c-48 -43 -134 -118 -179 -118c-35 0 -68 30 -86 58l56 65h12c9 -24 32 -61 71 -61c28 0 49 20 68 39\nc38 38 42 124 42 187c0 121 -28 241 -59 284c-27 38 -71 72 -118 72c-53 0 -100 -14 -100 -71c0 -62 92 -109 92 -168c0 -64 -64 -104 -123 -134l-15 11c31 17 64 49 64 81c0 10 -7 19 -13 27c-31 44 -72 80 -72 133c0 40 23 70 51 98c24 24 90 86 183 86\nc74 0 133 -73 159 -152z"
            },
            O: {
                x: 835,
                d: "M471 596l-86 -3c-31 0 -79 12 -79 46c0 40 45 59 69 90h16c-5 -7 -10 -17 -10 -26c0 -32 68 -33 164 -41c192 -17 240 -131 240 -263c0 -100 -17 -224 -91 -287c-73 -67 -172 -139 -266 -139c-55 0 -110 11 -160 32c-33 13 -69 32 -106 32c-39 0 -74 -21 -96 -53l-16 16\nc28 37 58 73 91 106c44 15 85 51 85 101c0 34 -24 67 -43 96c-38 56 -64 82 -64 107c0 75 68 141 134 181l21 -11c-30 -22 -70 -59 -70 -96c0 -65 96 -155 96 -223c0 -60 -55 -110 -101 -149c12 0 28 -4 39 -8c88 -28 165 -68 261 -68c150 0 195 149 195 294\nc0 217 -96 266 -223 266z"
            },
            P: {
                x: 898,
                d: "M454 509v-387h1c144 -47 167 -60 208 -60c44 0 81 28 81 76c0 66 -98 140 -98 208c0 46 50 87 88 118c-36 8 -54 37 -56 90c-2 52 -16 79 -64 79c-76 0 -124 -68 -160 -124zM442 540c48 72 147 152 241 152c114 0 45 -162 123 -162c12 0 25 4 37 8l5 -16\nc-50 -26 -128 -82 -128 -123c0 -61 75 -111 75 -186c0 -106 -94 -224 -192 -224c-10 0 -21 3 -31 6l-118 42c0 -62 3 -124 11 -186c-6 -5 -64 -47 -96 -70l-21 11c21 51 27 180 27 271c-33 10 -63 17 -80 17c-56 0 -91 -54 -123 -96l-10 21c34 68 79 149 165 149\nc13 0 30 -3 48 -8v250c0 113 -58 226 -158 226c-59 0 -102 -20 -102 -71c0 -65 94 -106 94 -168c0 -64 -64 -104 -123 -134l-15 11c31 17 64 49 64 81c0 10 -7 19 -13 27c-31 44 -72 80 -72 133c0 40 23 70 51 98c24 24 89 86 183 86c73 0 135 -77 158 -145z"
            },
            Q: {
                x: 872,
                d: "M471 596l-86 -3c-31 0 -79 12 -79 46c0 8 0 21 11 32l58 58h16c-5 -7 -10 -17 -10 -26c0 -31 71 -33 164 -41c192 -17 240 -131 240 -263c0 -103 -22 -225 -91 -287c-17 -15 -34 -30 -52 -44c29 -32 67 -74 106 -74c27 0 52 12 74 27v-27l-117 -63\nc-53 16 -113 41 -153 79l-2 -1c-38 -20 -79 -36 -122 -36c-55 0 -109 11 -160 32c-33 13 -69 32 -106 32c-39 0 -74 -21 -96 -53l-16 16c28 37 58 73 91 106c44 15 85 51 85 101c0 34 -24 67 -43 96c-38 56 -64 82 -64 107c0 75 68 141 134 181l21 -11\nc-30 -22 -70 -59 -70 -96c0 -65 96 -155 96 -223c0 -59 -57 -114 -102 -148c12 0 29 -5 40 -9c87 -27 163 -67 257 -67c74 0 138 41 169 109c26 57 30 122 30 184c0 217 -96 266 -223 266z"
            },
            R: {
                x: 899,
                d: "M455 348c21 11 70 33 115 40l129 75c-13 1 -27 6 -37 16c-22 22 -1 149 -74 149c-61 0 -99 -54 -135 -116c1 -5 5 -48 5 -61c0 -33 0 -69 -3 -103zM441 548l4 5c53 67 118 133 207 133c55 0 74 -44 80 -95c3 -26 -13 -78 52 -78c13 0 26 2 38 4l7 -13\nc-64 -24 -142 -62 -210 -116c51 0 76 -15 102 -37c3 -66 7 -170 16 -197c10 -31 29 -85 53 -85c5 0 39 13 59 21v-16l-133 -101l-64 117c1 31 3 60 3 89c0 98 -17 117 -29 129c-21 21 -54 35 -83 35c-23 0 -65 -11 -92 -19c-10 -76 -28 -144 -68 -205\nc-56 -45 -113 -100 -168 -146c-22 22 -60 53 -81 53c-31 0 -55 -18 -73 -42l-11 16c24 38 66 106 117 106c34 0 70 -32 94 -58c107 73 116 188 116 319c0 47 -1 126 -40 184c-26 39 -71 72 -118 72c-56 0 -97 -26 -97 -87c0 -41 89 -96 89 -152c0 -64 -64 -104 -123 -134\nl-15 11c31 17 64 49 64 81c0 10 -7 19 -13 27c-31 44 -72 80 -72 133c0 40 23 70 51 98c24 24 90 86 183 86c71 0 131 -73 155 -138z"
            },
            S: {
                x: 800,
                d: "M333 508l13 -13c-33 -10 -48 -39 -48 -69c0 -39 27 -59 64 -59c59 0 174 43 245 43c74 0 138 -51 138 -133c0 -209 -200 -304 -378 -304c-76 0 -155 20 -210 75c-68 68 -107 164 -107 261s39 186 108 255c67 68 168 106 261 125c97 -52 175 -98 230 -98c50 0 70 38 82 75\nl19 -11c-20 -79 -78 -117 -149 -144c-78 7 -181 117 -271 117c-118 0 -197 -134 -197 -250c0 -182 118 -352 316 -352c64 0 129 5 175 51c32 32 47 84 47 130c0 71 -43 134 -123 134c-70 0 -152 -32 -229 -32c-51 0 -96 28 -96 79c0 61 56 105 110 120z"
            },
            T: {
                x: 752,
                d: "M633 133l11 -16c-50 -57 -106 -113 -170 -144c-78 10 -139 75 -213 75c-42 0 -88 -32 -123 -59l-10 16c38 40 79 77 122 112c10 1 20 1 30 1c19 0 37 -1 55 -6c35 20 75 57 75 95c0 56 -111 132 -123 197c17 74 69 140 123 192c-52 18 -106 27 -160 27\nc-66 0 -150 -23 -150 -107c0 -39 29 -71 65 -85l-64 -64c-32 25 -51 58 -51 98c0 55 41 118 82 156c49 46 110 65 177 65c98 0 197 -37 287 -37c42 0 79 23 106 54v-22l-69 -90c-21 -13 -45 -16 -69 -16c-38 0 -91 5 -128 14c-30 -23 -74 -72 -74 -126\nc0 -59 117 -130 117 -208c0 -55 -69 -113 -116 -152c43 -20 98 -45 137 -45c47 0 97 39 133 75z"
            },
            U: {
                x: 792,
                d: "M531 120l-114 -126c-17 -13 -37 -21 -58 -21c-89 0 -166 64 -245 64c-19 0 -41 -25 -54 -43l-10 17l80 90c24 2 50 28 69 48c26 27 37 64 37 101c0 71 -101 175 -101 256c0 56 88 150 160 191l10 -16c-41 -30 -85 -74 -85 -127c0 -76 85 -168 85 -261\nc0 -26 -4 -52 -15 -75l-101 -112c66 0 151 -58 202 -58s124 75 138 117c5 16 5 56 5 85c0 116 -2 231 -10 346l127 90l16 -10c-59 -59 -58 -139 -58 -213v-202c0 -16 6 -182 73 -182c24 0 50 15 60 22v-21l-144 -107z"
            },
            V: {
                x: 897,
                d: "M399 224l-93 -73c39 -3 69 -22 131 -61c66 -41 114 -58 143 -58c97 0 102 94 102 175c0 54 -11 122 -11 187c0 23 4 47 11 69l74 85c-62 0 -104 69 -170 69c-29 0 -66 -36 -66 -36c-28 -27 -50 -59 -66 -95c0 -100 -14 -182 -55 -262zM501 601c35 40 95 85 143 85\nc36 0 67 -17 96 -37c37 -27 65 -32 81 -32c9 0 18 2 26 6v-16c-57 -39 -101 -100 -101 -171c0 -73 26 -167 26 -218c0 -52 -23 -117 -64 -147l-117 -85c-15 -11 -35 -14 -53 -13c-30 0 -76 9 -149 53c-20 12 -110 64 -154 64c-49 0 -92 -27 -123 -64l-11 16\nc38 43 80 82 128 112c118 0 146 126 146 249c-2 110 -59 220 -158 220c-56 0 -100 -17 -100 -71c0 -65 92 -107 92 -168c0 -64 -64 -104 -123 -134l-15 11c31 17 64 49 64 81c0 10 -7 19 -13 27c-31 44 -72 80 -72 133c0 40 23 70 51 98c24 24 90 86 183 86\nc78 0 137 -80 162 -160c13 22 28 44 55 75z"
            },
            W: {
                x: 1134,
                d: "M924 485l59 63c-43 0 -96 69 -160 69c-49 0 -94 -51 -119 -87c2 -33 4 -68 4 -103c0 -79 -8 -156 -29 -227c-14 -19 -56 -75 -79 -99c61 0 159 -69 223 -69c32 0 58 15 74 42c11 18 18 45 18 112c0 70 -12 137 -12 205c0 58 8 77 21 94zM381 191\nc-42 -34 -81 -59 -118 -81c45 -35 103 -68 160 -68c60 0 113 28 155 70c52 51 58 164 58 250c0 89 -27 206 -111 243c-28 -25 -57 -51 -78 -80c1 -9 7 -38 7 -49c0 -37 -10 -190 -73 -285zM438 551l12 13c42 46 89 85 138 122c53 -30 92 -96 107 -127c50 53 121 127 181 127\nc66 0 111 -66 171 -66c12 0 25 3 37 8l-6 -21c-58 -43 -95 -84 -95 -173c0 -74 15 -147 15 -221c0 -25 -2 -51 -9 -74c-19 -58 -145 -164 -226 -164c-82 0 -159 62 -238 62c-41 -19 -81 -39 -118 -64c-5 0 -11 -1 -16 -1c-45 0 -90 16 -127 39c-27 17 -62 37 -86 37\nc-32 0 -76 -34 -103 -59l-14 16c37 43 78 82 123 117h3c147 0 188 145 188 280c-2 110 -59 221 -158 221c-55 0 -100 -16 -100 -69c0 -72 92 -108 92 -170c0 -64 -64 -104 -123 -134l-15 11c31 17 64 49 64 81c0 10 -7 19 -13 27c-31 44 -72 80 -72 133c0 40 23 70 51 98\nc24 24 90 86 183 86c70 0 130 -71 154 -135z"
            },
            X: {
                x: 782,
                d: "M101 527l148 159c44 -2 89 -16 120 -47c30 -30 42 -76 53 -117c0 -2 1 -4 1 -6c39 81 108 140 188 173c21 -40 36 -101 78 -101c11 0 29 9 43 16v-19l-102 -58c-35 16 -84 59 -95 89c-76 -51 -107 -157 -107 -249h176l-37 -42c-46 6 -93 7 -139 8c0 -97 26 -280 165 -280\nc39 0 74 21 106 43v-22l-111 -90c-16 -5 -37 -11 -48 -11c-95 0 -143 97 -160 181l-42 -42c-33 -33 -68 -65 -107 -91c-28 -19 -64 -43 -96 -43c-36 0 -66 24 -85 54l59 69c15 -31 44 -53 79 -53c38 0 73 21 99 47c59 59 72 151 72 238c-59 0 -117 0 -176 -8l37 42h139\nc0 36 0 71 -5 107c-8 53 -50 120 -107 120c-58 0 -97 -41 -129 -82z"
            },
            Y: {
                x: 815,
                d: "M377 397c0 110 -57 226 -158 226c-65 0 -101 -24 -101 -72c0 -61 93 -107 93 -167c0 -64 -64 -104 -123 -134l-15 11c31 17 64 49 64 81c0 10 -7 19 -13 27c-31 44 -72 80 -72 133c0 40 23 70 51 98c24 24 89 86 183 86c74 0 137 -78 159 -149l5 5c55 55 121 111 192 144\nc29 -36 68 -64 107 -90c16 -80 16 -170 16 -255c0 -121 -11 -253 -65 -357c-68 -104 -201 -203 -314 -203c-51 0 -113 19 -133 70l67 71h10c10 -50 56 -93 110 -93c62 0 95 21 135 60c99 96 110 270 110 425c0 72 -4 144 -27 213c-26 29 -54 59 -90 74\nc-8 -3 -76 -35 -116 -89c3 -14 4 -30 4 -44c-2 -91 -18 -183 -70 -261l-116 -92c55 -16 102 -57 154 -57c34 0 63 23 85 48l9 -16c-33 -43 -81 -111 -124 -111c-30 0 -76 23 -107 41c-37 21 -76 34 -100 34c-46 0 -90 -32 -126 -58l-11 15c37 39 82 78 122 113\nc22 0 52 0 68 -2c100 55 137 160 137 275z"
            },
            Z: {
                x: 622,
                d: "M220 309l43 63c81 5 160 88 160 171c0 39 -41 58 -75 58c-48 0 -126 -10 -149 -10c-28 0 -69 5 -69 42l69 96h16c-4 -7 -8 -16 -8 -24c0 -34 50 -34 83 -34c23 0 72 5 106 5c65 0 112 -24 112 -80c0 -100 -92 -173 -172 -222l5 -2c16 3 40 6 55 6c61 0 125 -17 160 -69\nc11 -37 16 -74 16 -112c0 -88 -31 -172 -80 -245c-69 -57 -155 -91 -245 -91c-32 0 -64 3 -96 11c0 71 5 112 -37 112c-11 0 -36 -14 -52 -26l-12 15c40 33 90 64 138 64c42 0 48 -12 48 -80c0 -50 20 -66 62 -66c138 0 183 155 183 268c0 120 -74 160 -159 160\nc-32 0 -63 -7 -91 -21z"
            },
            a: {
                x: 532,
                d: "M314 91l-175 -127c-30 32 -59 66 -87 101c-2 30 -2 61 -2 91c0 70 5 141 20 209l169 106c28 -19 60 -33 93 -40l67 34l8 -6c-10 -67 -15 -135 -15 -203c0 -46 0 -91 1 -137l36 -45c21 7 32 13 46 22l7 -13l-123 -110l-45 73v45zM314 119l-1 246c-47 0 -96 13 -142 27\nc-41 -42 -41 -130 -41 -191c0 -27 1 -53 3 -80c9 -11 51 -66 67 -66c15 0 54 23 114 64z"
            },
            b: {
                x: 458,
                d: "M99 599l120 87l17 -13c-1 0 -20 -17 -30 -26c-78 -67 -65 -183 -65 -282l7 -2c45 38 92 77 140 107c38 -22 77 -43 117 -61c2 -32 3 -64 3 -96c0 -72 -5 -145 -20 -216c-73 -47 -148 -92 -228 -128c-37 33 -86 77 -110 102c14 73 14 157 14 236c0 121 -1 241 -7 361l17 4\nl17 -77zM142 332v-209c32 -33 80 -81 107 -81c67 0 78 118 78 185c0 41 0 72 -4 113c-9 12 -32 24 -85 46c-31 -4 -69 -33 -96 -54z"
            },
            c: {
                x: 387,
                d: "M337 92l-172 -121c-40 34 -76 73 -110 113c-3 45 -5 89 -5 134c0 29 0 89 12 145l165 103c39 -3 75 -22 108 -44l-52 -79l-9 -3c-33 29 -83 54 -120 54c-18 -30 -21 -99 -21 -148c0 -35 2 -69 7 -99c18 -29 53 -61 84 -89c32 10 71 31 105 51z"
            },
            d: {
                x: 517,
                d: "M438 109l-187 -143c-50 34 -99 69 -138 115c-2 30 -3 57 -3 85c0 44 2 93 6 169c42 40 88 83 132 112l19 -4c-46 -50 -57 -77 -65 -97c-9 -23 -13 -56 -13 -92c0 -39 4 -83 7 -119c13 -16 29 -29 45 -41c19 -14 62 -45 80 -45c5 0 11 3 15 10c39 62 50 136 50 208\nc0 44 -4 88 -12 132c-63 66 -154 147 -241 147c-8 0 -21 0 -24 -1l-46 -18l-13 16c43 24 93 48 138 69c90 -12 192 -88 276 -157c1 -18 3 -75 3 -127c0 -89 -2 -153 -29 -219z"
            },
            e: {
                x: 385,
                d: "M64 366l162 101c33 -42 74 -78 118 -109c-69 -66 -155 -146 -213 -192c0 -9 2 -18 5 -26c3 -14 60 -61 100 -79c4 1 18 1 23 3l74 39l5 -16c-54 -42 -110 -82 -169 -118c-40 37 -80 74 -114 116c-4 34 -5 69 -5 103c0 60 5 119 14 178zM128 193c46 34 98 78 136 119\nc-38 24 -76 53 -108 84c0 1 -1 1 -2 1c-24 0 -26 -116 -26 -204z"
            },
            f: {
                x: 325,
                d: "M226 351l-4 -206c0 -129 -7 -209 -56 -295l-50 -88l-19 7l26 90c16 54 17 137 17 269c0 15 0 147 -1 223h-89l15 35h73c0 20 -27 108 -27 142c0 63 51 116 101 151c42 0 92 -7 124 -40l-47 -92l-10 -1c-15 32 -43 69 -78 69c-31 0 -51 -16 -51 -47c0 -17 12 -42 21 -59\nl50 -95c5 -8 7 -17 9 -26h114l-12 -37h-106z"
            },
            g: {
                x: 539,
                d: "M50 -131c45 31 89 68 134 107l-72 89c-2 34 -4 69 -4 103c0 67 5 134 15 200c53 37 107 70 162 102c34 -23 74 -36 114 -45l72 40l6 -6c-30 -62 -22 -184 -22 -309c0 -14 2 -28 5 -41l29 -126c-51 -64 -104 -127 -163 -184c-12 -4 -28 -8 -36 -8c-71 0 -140 40 -194 84\nl-35 -19zM376 97c-67 -47 -133 -99 -195 -152c41 -41 92 -80 148 -80c48 0 74 44 74 90c0 22 -16 95 -27 142zM262 41l112 82c0 76 2 153 9 228c-58 8 -128 21 -176 43c-16 -40 -18 -90 -18 -134c0 -45 1 -91 5 -136c15 -29 43 -60 68 -83z"
            },
            h: {
                x: 459,
                d: "M202 -184l64 55c68 49 64 218 64 322c0 28 -6 107 -10 161c-26 19 -55 36 -88 36c-31 0 -69 -29 -94 -49l-5 -215l54 -77l-82 -75l-55 91c7 122 11 244 11 366c0 81 -1 163 -6 244l12 2l24 -76c40 30 120 88 121 88l17 -11c-84 -77 -90 -142 -90 -197l-1 -110\nc33 26 98 72 148 107c33 -30 74 -48 116 -61c4 -57 7 -114 7 -171c0 -93 -2 -193 -27 -277c-45 -66 -102 -119 -164 -167z"
            },
            i: {
                x: 354,
                d: "M63 369l-13 13c34 28 71 61 105 93c19 -20 38 -39 60 -55c-7 -60 -8 -120 -8 -181c0 -66 -6 -121 37 -164c15 0 44 20 53 27l7 -19c-39 -37 -81 -72 -123 -104l-54 76c3 53 3 93 3 139c0 58 -1 114 -4 173l-21 24c-11 -4 -29 -12 -42 -22zM223 616l-57 -60\nc-21 19 -40 40 -58 61c20 20 42 37 60 58c18 -21 40 -47 55 -59z"
            },
            j: {
                x: 305,
                d: "M227 -44c-45 -55 -98 -107 -161 -158l-16 18c29 22 58 45 83 71c42 42 44 184 44 316c0 50 0 119 -10 149c-6 17 -25 37 -32 37c-2 0 -7 -2 -8 -3l-29 -19l-14 13l106 95c22 -16 43 -34 65 -53c0 -52 -1 -268 -6 -396c-1 -25 -5 -51 -22 -70zM242 615l-58 -60\nc-18 15 -37 40 -54 61l61 57c16 -21 32 -40 51 -58z"
            },
            k: {
                x: 439,
                d: "M376 110l13 -15c-43 -37 -111 -82 -168 -121l-107 95c4 19 6 40 6 58v181h-70l9 35h62c0 227 -1 262 -7 326l17 4c8 -24 18 -79 18 -80c41 32 86 68 130 93l16 -10c-78 -72 -90 -110 -96 -178c42 32 83 57 127 87c28 -27 60 -66 60 -102c0 -18 -10 -36 -23 -49l-86 -91\nh95l-14 -35h-159v-180c24 -24 54 -48 82 -68c32 16 66 29 95 50zM199 343c55 0 115 46 115 103c0 28 -26 54 -47 72c-75 -37 -68 -100 -68 -175z"
            },
            l: {
                x: 280,
                d: "M214 686l16 -13c-92 -80 -95 -123 -95 -282c0 -90 1 -179 2 -269c9 -14 26 -29 42 -41c14 4 29 14 40 23l10 -17l-128 -107l-51 76c6 108 8 217 8 326c0 96 -1 191 -6 286l17 4l17 -78z"
            },
            m: {
                x: 850,
                d: "M211 376l127 99c38 -23 78 -43 120 -57v-39c44 30 88 61 129 95c39 -21 85 -46 121 -56c-4 -57 -4 -114 -4 -171v-121c11 -16 23 -33 37 -47c20 7 28 10 47 26l12 -17l-126 -107c-10 19 -39 54 -59 79c6 42 11 75 11 214c0 26 -1 50 -3 68c-4 27 -70 41 -100 49\nc-23 -12 -44 -24 -65 -38v-218c0 -18 6 -31 50 -88l-86 -73c-13 26 -36 52 -54 72c9 39 14 81 14 202c0 33 -1 66 -5 99c-25 22 -71 35 -105 44l-61 -42v-197c0 -47 -2 -40 49 -104l-83 -70l-49 80c5 40 7 80 7 119c0 63 -1 125 -4 188l-26 26l-43 -24l-12 12l111 93\nc17 -17 50 -51 50 -52v-44z"
            },
            n: {
                x: 603,
                d: "M208 367l144 108c35 -24 73 -43 113 -58c-4 -43 -8 -102 -8 -164l4 -126c9 -15 22 -45 39 -45c1 0 7 3 10 5l31 22l12 -16l-127 -112l-55 84c6 35 8 81 8 130c0 54 -2 111 -5 163c-29 15 -68 24 -101 27l-65 -44l-4 -207c0 -10 4 -24 7 -28l40 -54l-87 -75\nc-14 25 -30 48 -49 69c8 46 11 104 11 165c0 52 -2 105 -4 154l-24 23l-35 -20l-13 12c34 30 103 90 104 90c12 -13 35 -31 54 -45v-58z"
            },
            o: {
                x: 447,
                d: "M149 -28c-35 30 -71 62 -99 96c0 89 5 198 18 296c61 35 119 75 176 117c45 -30 94 -55 148 -66c3 -38 5 -75 5 -113c0 -71 -6 -141 -22 -210c-72 -46 -147 -88 -226 -120zM252 44c6 0 17 13 22 19c49 59 44 150 44 293c-45 3 -104 25 -149 45c-20 -34 -38 -95 -38 -190\nc0 -33 2 -64 6 -88c30 -37 70 -73 115 -79z"
            },
            p: {
                x: 518,
                d: "M207 -170l-80 -44l-11 7c4 83 9 199 9 254c-23 0 -46 -7 -64 -23l-11 14c25 23 62 57 76 67v247c-34 47 -46 61 -46 70c0 29 53 87 83 116l14 -9c-10 -12 -18 -26 -18 -42c0 -29 24 -59 46 -81c0 -1 -1 -22 -2 -34c49 33 95 69 142 106c35 -28 76 -48 118 -64\nc3 -44 5 -88 5 -132c0 -61 -1 -133 -23 -193l-134 -107c-32 26 -68 48 -108 55c0 -69 1 -138 4 -207zM204 347v-241c40 0 108 -30 157 -54c16 30 26 83 26 178c0 41 -3 82 -6 123c-25 13 -66 28 -96 38c-29 -11 -54 -27 -81 -44z"
            },
            q: {
                x: 459,
                d: "M397 -179l-83 -45l-11 10c4 98 8 208 8 307c-56 -46 -113 -84 -174 -122l-84 94c-2 29 -3 57 -3 86c0 73 6 147 23 218c57 31 115 63 165 104c29 -18 60 -34 93 -41l70 48l8 -7c-15 -85 -19 -220 -19 -332c0 -107 0 -215 7 -320zM209 50c24 13 70 48 101 71\nc0 80 1 166 5 246c-51 3 -101 16 -150 31c-31 -55 -36 -132 -36 -198c0 -19 0 -53 1 -56c0 -20 52 -70 79 -94z"
            },
            r: {
                x: 480,
                d: "M217 382l109 87c15 -27 42 -71 66 -71c11 0 29 10 38 15l-1 -13l-59 -55c-9 -8 -21 -10 -33 -10c-27 0 -60 27 -83 49c-16 -12 -31 -25 -37 -34v-198c0 -32 42 -69 73 -92l89 42l2 -19l-146 -104c-37 29 -70 62 -103 96c6 59 6 109 6 170c0 60 -3 128 -9 120\nc-9 8 -19 15 -29 22l-38 -16l-12 10l101 93c21 -21 43 -41 66 -60v-32z"
            },
            s: {
                x: 535,
                d: "M270 244l123 56c22 -7 39 -22 56 -38c2 -26 3 -52 3 -77c0 -46 -4 -91 -16 -135c-54 -24 -109 -49 -160 -80c-34 33 -86 77 -128 77c-30 0 -57 -16 -89 -44l-9 9c35 37 79 99 141 99c25 0 50 -8 72 -22l81 -54c1 -1 2 -1 4 -1c23 0 26 74 26 106c0 76 -27 96 -52 96\nc-14 0 -23 -3 -35 -10l-102 -58c-15 19 -31 45 -43 67c0 49 1 97 6 142l152 102l81 -46c8 -4 16 -6 26 -6c23 0 46 13 72 30l6 -16l-92 -78c-72 4 -126 27 -152 52c-20 -19 -20 -56 -20 -79c0 -33 7 -94 32 -94c6 0 12 0 17 2z"
            },
            t: {
                x: 423,
                d: "M252 641l14 -14l-27 -26c-5 -5 -8 -12 -9 -19c-11 -64 -12 -131 -12 -196h108l-17 -35h-90l1 -228c26 -23 60 -55 80 -55c15 0 36 9 70 32l3 -22l-137 -99l-97 83c0 1 0 192 1 289h-90l16 35h73v172z"
            },
            u: {
                x: 606,
                d: "M463 468l7 -6c-10 -79 -13 -158 -13 -237c0 -50 -1 -142 40 -142c10 0 43 15 53 20l6 -15c-42 -34 -83 -69 -122 -106l-8 2c-19 32 -39 67 -51 103c-68 -45 -153 -113 -153 -113c-26 17 -88 62 -125 74c30 52 42 143 42 216c0 45 0 101 -36 132l-38 -20l-15 10l111 88\nc16 -19 32 -38 52 -53c0 -22 1 -43 1 -65c0 -86 -3 -170 -26 -252l101 -48l88 59c3 50 4 100 4 149c0 52 -1 105 -3 160z"
            },
            v: {
                x: 488,
                d: "M95 231c0 31 -2 69 -4 114c0 23 -41 41 -41 73c0 31 50 85 80 115l14 -4c-9 -14 -16 -29 -16 -46c0 -23 22 -48 46 -75v-34l143 103c36 -24 74 -45 115 -61c6 -66 6 -100 6 -171c0 -37 -15 -119 -32 -159l-215 -114c-31 33 -67 65 -103 94c5 55 7 101 7 165zM174 348\nl-3 -193c-1 -25 14 -47 34 -62c11 0 42 -44 79 -44c60 0 74 126 74 194c0 38 -3 77 -8 115c-23 21 -54 32 -85 38c-28 -2 -63 -27 -91 -48z"
            },
            w: {
                x: 749,
                d: "M424 368l142 103c41 -24 83 -46 126 -66c5 -28 7 -92 7 -137c0 -93 -19 -133 -41 -196l-187 -100c-44 27 -89 52 -134 74c5 59 8 118 8 176c0 40 -1 80 -5 119c-42 29 -76 51 -94 51c-21 0 -53 -30 -74 -44c0 -34 -1 -126 -1 -190c0 -41 22 -79 47 -110l-75 -75\nc-17 29 -35 57 -59 79c8 51 10 103 10 155c0 46 1 92 -3 138c-3 28 -41 41 -41 73c0 31 50 85 80 115l14 -4c-9 -14 -16 -29 -16 -46c0 -23 22 -48 46 -75v-32c43 30 86 61 123 97c41 -26 84 -48 128 -68zM504 386c-22 0 -63 -33 -80 -45c-1 -37 -2 -71 -2 -106\nc0 -26 1 -53 3 -81c2 -48 67 -110 129 -110c60 0 62 145 62 220c0 28 -4 53 -12 84c-29 18 -68 33 -100 38z"
            },
            x: {
                x: 460,
                d: "M232 376l109 89c17 -28 42 -48 69 -67l-67 -59c-28 14 -47 42 -59 51c-15 -9 -42 -33 -49 -43c-7 -30 -5 -62 -5 -87c0 -47 2 -96 5 -143c18 -19 57 -58 76 -58c5 1 13 0 22 5l63 34l3 -18l-149 -105c-25 28 -54 52 -84 74c-28 -26 -61 -64 -61 -103\nc0 -59 70 -80 122 -85v-9l-64 -40c-46 11 -113 36 -113 90c0 51 55 113 94 157c6 42 7 91 7 133c0 53 1 108 -3 161c-14 15 -32 33 -40 33c-7 0 -36 -18 -41 -18l-13 9c33 34 68 65 104 96c25 -22 63 -57 76 -66z"
            },
            y: {
                x: 492,
                d: "M94 255c-1 31 1 35 -3 81c-3 28 -41 39 -41 74c0 29 51 85 80 114l14 -4c-9 -14 -16 -29 -16 -46c0 -23 22 -48 46 -75v-32l142 106c37 -22 74 -44 115 -59c7 -60 11 -130 11 -186c0 -80 -8 -174 -30 -251c-13 -46 -117 -145 -185 -196l-38 17c34 17 67 36 94 62\nc73 72 72 216 72 329c0 56 -5 112 -11 167c-24 15 -53 30 -80 35c-15 0 -59 -28 -89 -51c-1 -64 -3 -81 -3 -188c0 -38 15 -66 45 -104l-75 -75c-17 29 -39 61 -59 79c10 61 11 126 11 203z"
            },
            z: {
                x: 421,
                d: "M347 -99l-137 -116c-64 0 -149 33 -160 99c7 27 54 91 87 129c42 49 82 89 127 134c-35 16 -71 30 -110 34v11c1 0 48 44 73 67c16 15 34 33 34 56c0 39 -45 73 -73 73c-19 0 -62 -35 -84 -53l-11 12c45 45 93 87 146 124c43 -22 102 -59 102 -101c0 -13 -6 -27 -15 -36\nl-81 -87c37 -13 79 -29 112 -49c11 -43 14 -89 14 -134c0 -55 -10 -110 -24 -163zM282 130c-64 -55 -153 -133 -153 -203c0 -42 63 -81 111 -81c41 0 57 100 57 174c0 37 -4 74 -15 110z"
            }
        }
    };
});
define("font/map/kf-ams-main", [], function(require) {
    return {
        meta: {
            fontFamily: "KF AMS MAIN",
            src: "KF_AMS_MAIN.woff"
        },
        data: {
            "0": {
                x: 482,
                d: "M432 321c0 -73 -4 -154 -34 -223c-41 -95 -111 -114 -157 -114c-39 0 -116 14 -159 117c-31 76 -32 163 -32 220c0 78 4 171 44 246c38 72 99 94 147 94c52 0 111 -25 148 -95c42 -80 43 -184 43 -245zM241 0c35 0 94 19 115 128c12 59 12 139 12 204\nc0 80 -1 147 -14 204c-19 82 -72 109 -113 109c-39 0 -95 -26 -114 -111c-13 -61 -13 -125 -13 -202c0 -56 0 -142 11 -201c14 -76 52 -131 116 -131z"
            },
            "1": {
                x: 397,
                d: "M229 639v-566c0 -36 2 -47 88 -47h30v-26c-48 1 -98 2 -146 2s-98 -1 -146 -2v26h30c86 0 88 12 88 47v527c-40 -24 -92 -30 -123 -30c0 21 0 23 1 25s6 2 9 2c56 2 115 17 154 64c14 0 15 -1 15 -22z"
            },
            "2": {
                x: 476,
                d: "M426 155l-24 -155h-352v23l199 225c57 65 108 140 108 227c0 91 -57 160 -144 160c-61 0 -119 -42 -138 -127c4 2 10 3 16 3c14 0 40 -9 40 -40c0 -35 -30 -41 -40 -41c-12 0 -41 5 -41 44c0 90 67 187 177 187c105 0 199 -68 199 -186c0 -99 -71 -167 -132 -225\nc-85 -82 -155 -153 -191 -192h192c14 0 86 0 91 8c4 6 12 18 22 89h18z"
            },
            "3": {
                x: 492,
                d: "M238 340h-49c-20 0 -21 1 -21 8s4 8 11 9c4 0 27 2 32 2c33 3 67 5 97 44c34 44 38 94 38 122c0 96 -66 113 -101 113s-114 -12 -143 -78c4 2 9 2 13 2c21 0 39 -13 39 -39s-18 -39 -39 -39c-13 0 -39 6 -39 41c0 80 80 136 171 136c90 0 169 -57 169 -135\nc0 -91 -73 -159 -149 -175c95 -11 175 -81 175 -178c0 -102 -85 -189 -196 -189c-106 0 -196 72 -196 164c0 37 26 45 43 45c20 0 42 -13 42 -42c0 -26 -18 -42 -42 -42c-5 0 -10 0 -15 2c27 -77 113 -103 166 -103c58 0 122 46 122 166c0 89 -39 166 -128 166z"
            },
            "4": {
                x: 518,
                d: "M366 647v-451h102v-26h-102v-98c0 -36 2 -46 72 -46h19v-26c-32 2 -86 2 -120 2s-88 0 -120 -2v26h19c70 0 72 10 72 46v98h-258v26l297 473c18 0 19 -1 19 -22zM311 581l-241 -385h241v385z"
            },
            "5": {
                x: 476,
                d: "M123 585v-225c34 36 77 55 128 55c97 0 175 -93 175 -213c0 -129 -98 -218 -201 -218c-106 0 -175 91 -175 178c0 28 15 42 38 42c19 0 38 -11 38 -38c0 -31 -25 -38 -38 -38c-4 0 -12 2 -16 3c18 -77 85 -123 151 -123c43 0 82 21 107 62c30 46 30 104 30 136\nc0 171 -75 193 -110 193c-50 0 -94 -19 -126 -64c-5 -7 -7 -9 -12 -9c-10 0 -10 5 -10 23v290c0 20 1 21 6 21c1 0 3 0 14 -5c49 -22 94 -26 124 -26c50 0 91 12 126 27c8 4 10 4 11 4c6 0 6 -5 6 -13c-29 -29 -80 -79 -173 -79c-42 0 -83 13 -93 17z"
            },
            "6": {
                x: 482,
                d: "M118 345v-34c25 70 70 110 131 110c102 0 183 -91 183 -216c0 -134 -92 -221 -189 -221c-80 0 -193 60 -193 332c0 221 125 345 244 345c69 0 119 -36 119 -99c0 -34 -27 -38 -36 -38c-14 0 -36 9 -36 36c0 35 30 35 46 35c-20 39 -71 43 -92 43\nc-65 0 -177 -54 -177 -293zM243 8c46 0 78 23 100 64c21 40 21 80 21 134c0 50 0 91 -18 130c-33 69 -82 69 -99 69c-93 0 -127 -104 -127 -176c0 -37 0 -221 123 -221z"
            },
            "7": {
                x: 505,
                d: "M455 623l-138 -205c-46 -69 -74 -177 -74 -341v-46c0 -13 0 -47 -36 -47s-36 34 -36 46c0 131 51 281 125 392l111 165h-220c-17 0 -91 0 -97 -8c-12 -19 -19 -70 -22 -89h-18l29 187h18c4 -19 6 -32 123 -32h235v-22z"
            },
            "8": {
                x: 492,
                d: "M289 360l52 -37c40 -27 101 -69 101 -160c0 -102 -90 -179 -197 -179c-97 0 -195 64 -195 167c0 110 105 165 150 188c-27 19 -69 49 -79 59c-39 38 -45 81 -45 106c0 91 80 157 171 157c81 0 169 -53 169 -144c0 -77 -64 -125 -127 -157zM158 448l105 -71\nc8 -6 9 -6 10 -6c5 0 109 53 109 146c0 65 -58 121 -137 121c-72 0 -135 -46 -135 -107c0 -47 40 -78 48 -83zM342 243l-125 85c-69 -31 -130 -97 -130 -176s69 -144 160 -144c84 0 158 56 158 130c0 61 -48 94 -63 105z"
            },
            "9": {
                x: 482,
                d: "M364 296v39c-10 -33 -47 -111 -131 -111c-102 0 -183 92 -183 216c0 134 95 221 194 221c76 0 188 -57 188 -331c0 -220 -117 -346 -232 -346c-72 0 -131 31 -131 99c0 34 27 38 36 38c14 0 36 -9 36 -36c0 -22 -15 -36 -36 -36c-3 0 -6 1 -9 1c23 -39 79 -42 102 -42\nc75 0 166 68 166 288zM235 240c95 0 127 106 127 177c0 40 0 221 -118 221c-58 0 -84 -34 -98 -55c-28 -44 -28 -85 -28 -144c0 -49 0 -95 21 -134c31 -60 69 -65 96 -65z"
            },
            A: {
                x: 772,
                d: "M186 111l350 586c9 15 15 17 25 17c11 0 15 -2 17 -22l61 -624c3 -30 4 -39 59 -39c15 0 24 0 24 -10c0 -19 -11 -19 -18 -19c-1 0 -45 2 -106 2h-57c-17 0 -37 -2 -54 -2c-6 0 -15 0 -15 11c0 18 11 18 22 18c16 0 67 2 67 32c0 19 -12 123 -15 149c-1 9 -1 17 -2 26\nh-255l-76 -126c-13 -22 -19 -33 -19 -47c0 -24 19 -32 42 -34c6 0 14 -1 14 -10c0 -19 -12 -19 -18 -19c-28 0 -60 2 -89 2c-18 0 -63 -2 -81 -2c-7 0 -12 4 -12 10c0 18 8 18 19 19c48 3 82 23 117 82zM306 265h235l-34 336z"
            },
            B: {
                x: 795,
                d: "M368 615l-62 -250h144c132 0 213 103 213 184c0 9 0 105 -113 105h-130c-39 0 -43 -2 -52 -39zM537 357c81 -8 153 -58 153 -140c0 -101 -118 -217 -271 -217h-343c-20 0 -26 0 -26 11c0 18 10 18 29 18c70 0 72 10 81 45l135 538c4 15 4 21 4 23c0 11 0 19 -61 19\nc-15 0 -24 0 -24 10c0 19 9 19 28 19h321c113 0 182 -60 182 -138c0 -92 -99 -168 -208 -188zM395 29c124 0 211 102 211 198c0 8 0 118 -118 118h-188l-70 -282c-2 -9 -4 -15 -4 -22c0 -8 1 -10 12 -11c6 -1 8 -1 22 -1h135z"
            },
            C: {
                x: 797,
                d: "M747 695l-63 -255c-4 -18 -5 -19 -16 -19c-3 0 -13 0 -13 9c0 4 3 26 3 52c0 102 -49 193 -159 193c-95 0 -189 -56 -244 -120c-113 -131 -123 -305 -123 -337c0 -142 92 -210 198 -210c100 0 235 75 281 231c2 4 3 10 12 10c3 0 11 0 11 -9c0 -8 -26 -105 -113 -178\nc-58 -49 -128 -83 -208 -83c-146 0 -263 104 -263 271c0 235 224 454 440 454c75 0 130 -34 165 -94l69 84c8 10 12 10 14 10s9 0 9 -9z"
            },
            D: {
                x: 842,
                d: "M160 74l135 538c4 15 4 21 4 23c0 11 0 19 -61 19c-15 0 -24 0 -24 10c0 19 9 19 28 19h321c143 0 229 -107 229 -251c0 -223 -197 -432 -399 -432h-317c-20 0 -26 0 -26 11c0 18 10 18 29 18c70 0 72 10 81 45zM371 615l-138 -552c-2 -9 -4 -15 -4 -22\nc0 -8 1 -10 12 -11c6 -1 8 -1 22 -1h114c115 0 191 69 219 102c97 110 117 284 117 334c0 138 -86 189 -180 189h-110c-39 0 -43 -2 -52 -39z"
            },
            E: {
                x: 807,
                d: "M699 232l-91 -215c-7 -16 -8 -17 -30 -17h-502c-20 0 -26 0 -26 11c0 18 10 18 29 18c70 0 72 10 81 45l135 540c3 11 4 15 4 20c0 11 0 18 -62 18c-17 0 -24 0 -24 11c0 18 10 18 29 18h488c18 0 27 0 27 -11c0 -2 -2 -12 -2 -15l-21 -175c-2 -18 -6 -22 -13 -22\nc-6 0 -11 3 -11 11c0 2 1 8 2 12c4 33 4 61 4 62c0 76 -25 109 -148 109h-143c-41 0 -43 -3 -52 -38l-62 -246h95c89 0 109 22 129 99c4 14 5 18 14 18c6 0 10 -5 10 -10l-57 -232c-4 -14 -5 -21 -14 -21c-6 0 -11 3 -11 10c0 3 1 7 3 11c7 30 7 39 7 49c0 30 -6 47 -84 47\nh-99l-69 -276c-2 -9 -4 -15 -4 -22c0 -8 1 -10 12 -11c6 -1 8 -1 22 -1h146c160 0 203 60 262 200c9 20 9 22 11 23c3 3 5 4 9 4c6 0 10 -5 10 -10c0 -4 -3 -11 -4 -14z"
            },
            F: {
                x: 793,
                d: "M301 326l-64 -255c-2 -7 -4 -15 -4 -20c0 -15 2 -22 81 -22c22 0 28 0 28 -11c0 -18 -12 -18 -21 -18c-22 0 -46 2 -68 2h-130c-19 0 -39 -2 -58 -2c-6 0 -15 0 -15 11c0 18 10 18 29 18c70 0 72 10 81 45l135 540c3 11 4 15 4 20c0 11 0 18 -62 18c-17 0 -24 0 -24 11\nc0 18 10 18 29 18h474c18 0 27 0 27 -11c0 -2 -2 -12 -2 -15l-21 -175c-2 -18 -6 -22 -13 -22c-6 0 -11 3 -11 11c0 4 2 17 3 21c3 26 3 48 3 54c0 71 -18 108 -143 108h-134c-41 0 -43 -3 -52 -38l-65 -259h91c87 0 107 21 128 96c4 17 5 21 14 21c7 0 10 -6 10 -10\nl-58 -233c-4 -16 -5 -20 -13 -20c-7 0 -11 4 -11 11c0 2 1 7 3 11c5 22 7 36 7 49c0 29 -6 46 -82 46h-96z"
            },
            G: {
                x: 796,
                d: "M746 695l-63 -255c-4 -18 -5 -19 -16 -19c-3 0 -13 0 -13 9c0 14 4 36 4 52c0 92 -42 193 -160 193c-88 0 -181 -49 -244 -121c-97 -113 -121 -266 -121 -333c0 -173 125 -213 202 -213c59 0 138 23 176 79c19 30 38 131 38 132c0 15 0 23 -79 23h-25c0 1 -8 1 -8 11\nc0 18 12 18 21 18c41 0 85 -2 127 -2h54c18 0 37 2 54 2c3 0 13 0 13 -10c0 -19 -12 -19 -16 -19c-55 -1 -57 -4 -67 -42c-7 -30 -11 -44 -19 -77l-16 -64c-5 -18 -13 -53 -14 -55c-1 0 -2 -3 -7 -3c-7 0 -29 27 -40 65c-50 -63 -141 -87 -213 -87c-150 0 -264 106 -264 271\nc0 236 225 454 439 454c15 0 58 0 101 -27c39 -23 59 -58 64 -66l69 83c8 10 12 10 14 10s9 0 9 -9z"
            },
            H: {
                x: 912,
                d: "M752 610l-137 -547c-2 -8 -2 -15 -2 -16c0 -10 0 -18 61 -18c15 0 24 0 24 -10c0 -19 -11 -19 -19 -19c-19 0 -40 2 -59 2h-118c-19 0 -40 -2 -58 -2c-3 0 -14 0 -14 10c0 19 9 19 21 19c59 1 75 1 86 31c2 6 53 215 70 281h-303l-69 -278c-2 -8 -2 -15 -2 -16\nc0 -10 0 -18 61 -18c15 0 24 0 24 -10c0 -19 -11 -19 -19 -19c-19 0 -40 2 -59 2h-118c-19 0 -40 -2 -58 -2c-3 0 -14 0 -14 10c0 19 7 19 30 19c69 0 71 10 80 45l135 538c4 15 4 21 4 23c0 11 0 19 -60 19c-19 0 -26 0 -26 10c0 19 12 19 19 19c19 0 40 -2 59 -2h118\nc19 0 40 2 58 2c6 0 15 0 15 -11c0 -18 -10 -18 -28 -18c-71 0 -73 -10 -82 -44l-60 -240h302l61 242c4 15 4 21 4 23c0 11 0 19 -60 19c-19 0 -26 0 -26 10c0 19 12 19 19 19c19 0 40 -2 59 -2h118c19 0 40 2 58 2c6 0 15 0 15 -11c0 -18 -10 -18 -28 -18\nc-71 0 -73 -10 -82 -44z"
            },
            I: {
                x: 541,
                d: "M377 609l-135 -537c-4 -16 -4 -21 -4 -23c0 -12 0 -20 62 -20c22 0 28 0 28 -11c0 -18 -12 -18 -20 -18c-20 0 -42 2 -62 2h-123c-18 0 -40 -2 -58 -2c-5 0 -15 0 -15 10c0 19 9 19 31 19c73 0 75 10 84 45l135 539c2 9 4 15 4 22c0 11 0 19 -62 19c-20 0 -28 0 -28 10\nc0 19 11 19 19 19c20 0 43 -2 63 -2h123c18 0 40 2 58 2c3 0 14 0 14 -10c0 -19 -8 -19 -30 -19c-73 0 -75 -10 -84 -45z"
            },
            J: {
                x: 640,
                d: "M505 614l-118 -471c-24 -95 -122 -164 -213 -164c-72 0 -124 44 -124 107c0 65 42 75 60 75c30 0 40 -21 40 -37c0 -29 -26 -58 -59 -58c-4 0 -9 1 -13 2c12 -53 60 -69 94 -69c48 0 115 49 141 151l115 457c4 16 4 23 4 24c0 15 0 23 -80 23h-25c0 1 -8 1 -8 11\nc0 18 12 18 21 18c42 0 86 -2 128 -2h55c18 0 37 2 54 2c4 0 13 0 13 -11c0 -18 -10 -18 -26 -18c-49 0 -51 -10 -59 -40z"
            },
            K: {
                x: 928,
                d: "M504 404l139 -330c15 -36 28 -44 65 -45c10 0 19 0 19 -10c0 -19 -11 -19 -17 -19c-15 0 -32 2 -47 2h-48c-35 0 -72 -2 -107 -2c-3 0 -14 0 -14 10c0 19 10 19 19 19s49 1 49 31c0 7 -2 13 -5 19c-41 98 -94 226 -123 290l-152 -119l-39 -155c-4 -16 -10 -41 -10 -48\nc0 -10 0 -18 61 -18c15 0 24 0 24 -10c0 -19 -11 -19 -19 -19c-19 0 -40 2 -59 2h-117c-19 0 -39 -2 -58 -2c-6 0 -15 0 -15 11c0 18 10 18 29 18c70 0 72 10 81 45l135 538c4 15 4 21 4 23c0 11 0 19 -61 19c-15 0 -24 0 -24 10c0 19 11 19 19 19c18 0 40 -2 58 -2h118\nc19 0 40 2 58 2c5 0 15 0 15 -10c0 -19 -9 -19 -28 -19c-71 0 -73 -10 -82 -44l-83 -330l392 305c8 6 38 30 38 50c0 14 -13 18 -24 19c-6 0 -13 1 -13 11c0 18 12 18 19 18c30 0 63 -2 94 -2h38c11 0 23 2 34 2c8 0 11 -5 11 -11c0 -17 -10 -17 -17 -18\nc-69 -6 -113 -41 -247 -145l-97 -76c-17 -13 -18 -14 -18 -15c0 -3 4 -11 5 -14z"
            },
            L: {
                x: 685,
                d: "M371 606l-136 -543c-2 -9 -4 -15 -4 -22c0 -8 1 -10 12 -11c6 -1 8 -1 22 -1h96c169 0 214 113 246 201c8 19 8 21 10 22c2 4 7 4 8 4c5 0 10 -4 10 -10c0 -3 -2 -9 -4 -14l-77 -213c-7 -18 -8 -19 -30 -19h-448c-20 0 -26 0 -26 11c0 18 10 18 29 18c70 0 72 10 81 45\nl135 538c4 15 4 21 4 23c0 11 0 19 -61 19c-15 0 -24 0 -24 10c0 19 11 19 19 19c19 0 41 -2 60 -2h134c20 0 43 2 63 2c4 0 15 0 15 -10c0 -19 -7 -19 -34 -19c-88 0 -91 -10 -100 -48z"
            },
            M: {
                x: 1069,
                d: "M909 610l-137 -547c-2 -8 -2 -15 -2 -16c0 -10 0 -18 61 -18c15 0 24 0 24 -10c0 -19 -11 -19 -19 -19c-17 0 -38 2 -55 2h-118c-17 0 -37 -2 -54 -2c-6 0 -15 0 -15 11c0 18 10 18 29 18c70 0 72 10 81 45l145 579h-1l-402 -636c-5 -9 -10 -17 -21 -17\nc-9 0 -10 3 -13 27l-83 621h-1l-138 -551c-4 -14 -4 -15 -4 -25c0 -36 32 -42 65 -43c4 0 14 -1 14 -10c0 -19 -11 -19 -18 -19c-14 0 -31 2 -45 2h-48c-20 0 -71 -2 -91 -2c-4 0 -13 0 -13 11c0 17 12 18 15 18c67 3 90 25 102 73l128 510c4 15 4 21 4 23c0 11 0 19 -61 19\nc-15 0 -24 0 -24 10c0 19 9 19 28 19h124c26 0 27 0 30 -22l75 -572l365 577c11 16 12 17 37 17h120c19 0 26 0 26 -10c0 -19 -9 -19 -28 -19c-71 0 -73 -10 -82 -44z"
            },
            N: {
                x: 912,
                d: "M744 578l-140 -558c-4 -17 -5 -20 -15 -20c-9 0 -11 6 -16 18l-238 593c-4 9 -4 11 -10 21l-135 -535c-2 -8 -4 -17 -4 -25c0 -36 32 -42 65 -43c4 0 14 -1 14 -10c0 -19 -11 -19 -18 -19c-14 0 -31 2 -45 2h-48c-20 0 -71 -2 -91 -2c-3 0 -13 0 -13 10c0 18 10 19 15 19\nc66 3 90 24 102 73l133 530c1 3 3 11 3 13c0 9 -59 9 -64 9c-19 0 -26 0 -26 10c0 19 9 19 29 19h122c22 0 23 -1 30 -17l214 -532l113 451c3 14 4 17 4 26c0 18 -4 41 -64 43c-7 0 -15 0 -15 10c0 19 12 19 18 19c14 0 32 -2 46 -2h48c20 0 71 2 91 2c4 0 13 0 13 -11\nc0 -17 -12 -18 -15 -18c-76 -3 -92 -33 -103 -76z"
            },
            O: {
                x: 778,
                d: "M728 438c0 -240 -219 -459 -429 -459c-145 0 -249 107 -249 264c0 231 216 461 430 461c140 0 248 -101 248 -266zM305 2c74 0 165 47 238 149c67 95 105 234 105 323c0 144 -85 208 -173 208c-67 0 -151 -36 -224 -124c-82 -100 -115 -249 -115 -340\nc0 -156 87 -216 169 -216z"
            },
            P: {
                x: 793,
                d: "M299 318l-64 -255c-1 -5 -2 -12 -2 -17c0 -10 1 -17 61 -17c15 0 24 0 24 -10c0 -19 -11 -19 -19 -19c-19 0 -40 2 -59 2h-117c-19 0 -39 -2 -58 -2c-6 0 -15 0 -15 11c0 18 10 18 29 18c70 0 72 10 81 45l135 538c4 15 4 21 4 23c0 11 0 19 -61 19c-15 0 -24 0 -24 10\nc0 19 9 19 28 19h309c126 0 192 -71 192 -149c0 -114 -138 -216 -276 -216h-168zM371 615l-68 -273h141c91 0 139 38 156 56c46 46 59 131 59 157c0 75 -62 99 -137 99h-99c-39 0 -43 -2 -52 -39z"
            },
            Q: {
                x: 778,
                d: "M428 6c5 -77 18 -112 69 -112c44 0 96 36 118 99c3 11 5 16 11 16c5 0 9 -4 9 -10s-44 -193 -163 -193c-80 0 -80 75 -80 104c0 19 0 24 6 84c-32 -10 -65 -15 -99 -15c-145 0 -249 107 -249 264c0 231 216 461 430 461c140 0 248 -101 248 -266\nc0 -181 -128 -363 -300 -432zM248 11c-4 7 -8 22 -8 33c0 49 46 103 100 103c66 0 80 -60 85 -111c188 110 223 358 223 432c0 130 -70 214 -174 214c-78 0 -177 -51 -251 -165c-66 -101 -92 -234 -92 -304c0 -92 38 -175 117 -202zM400 23c1 6 1 14 1 20\nc0 50 -11 84 -61 84c-45 0 -80 -46 -80 -82c0 -44 35 -44 45 -44c28 0 59 6 95 22z"
            },
            R: {
                x: 793,
                d: "M371 615l-66 -263h113c201 0 227 147 227 198c0 78 -64 104 -150 104h-72c-39 0 -43 -2 -52 -39zM510 340c38 -12 97 -43 97 -116c0 -8 0 -12 -5 -49c-1 -5 -13 -96 -13 -120c0 -45 16 -56 41 -56s66 18 90 89c2 8 4 14 12 14c6 0 11 -3 11 -10c0 -14 -35 -113 -116 -113\nc-67 0 -125 33 -125 104c0 19 3 30 14 76c7 26 17 65 17 80c0 33 -18 93 -113 93h-120l-67 -269c-2 -8 -2 -15 -2 -16c0 -10 0 -18 61 -18c15 0 24 0 24 -10c0 -19 -11 -19 -19 -19c-18 0 -39 2 -57 2h-118c-19 0 -40 -2 -58 -2c-3 0 -14 0 -14 10c0 19 7 19 30 19\nc69 0 71 10 80 45l135 538c4 15 4 21 4 23c0 11 0 19 -60 19c-19 0 -26 0 -26 10c0 19 9 19 29 19h271c139 0 218 -72 218 -150c0 -86 -97 -166 -221 -193z"
            },
            S: {
                x: 684,
                d: "M634 695l-54 -220c-4 -16 -5 -19 -14 -19c-3 0 -11 0 -11 9c0 4 5 31 5 57c0 95 -46 155 -144 155c-95 0 -180 -86 -180 -170c0 -34 13 -58 30 -75c18 -16 27 -19 94 -37c92 -24 110 -29 131 -50c38 -37 48 -74 48 -116c0 -125 -118 -250 -247 -250\nc-93 0 -145 40 -169 76l-46 -60c-12 -15 -13 -16 -17 -16c-2 0 -10 0 -10 9c0 1 4 14 4 15l51 205c4 17 5 19 14 19c8 0 10 -5 10 -9c0 -2 -1 -7 -1 -10c-3 -10 -7 -34 -7 -53c0 -112 91 -147 173 -147c100 0 186 96 186 189c0 47 -20 70 -24 75c-21 22 -34 26 -85 40\nc-30 7 -89 23 -100 27c-49 17 -94 61 -94 136c0 113 115 229 242 229c42 0 108 -11 141 -76c1 1 42 53 48 60c12 15 13 16 17 16c2 0 9 0 9 -9z"
            },
            T: {
                x: 768,
                d: "M443 610l-134 -533c-4 -16 -4 -23 -4 -24c0 -15 0 -24 90 -24c26 0 33 0 33 -10c0 -19 -10 -19 -21 -19c-25 0 -52 2 -77 2h-156c-25 0 -51 -2 -76 -2c-5 0 -16 0 -16 10c0 19 7 19 32 19c106 0 108 10 118 49l134 537c5 18 5 20 5 23c0 11 -10 11 -34 11h-67\nc-133 0 -154 -56 -196 -177c-4 -13 -6 -17 -14 -17c-6 0 -10 5 -10 10c0 1 3 12 4 14l61 180c6 18 7 19 30 19h547c20 0 26 0 26 -11c0 -3 0 -5 -2 -14l-29 -177c-3 -16 -3 -21 -13 -21c-6 0 -10 6 -10 10l1 12c6 37 10 75 10 90c0 78 -47 82 -147 82c-21 0 -54 0 -62 -2\nc-15 -4 -17 -13 -23 -37z"
            },
            U: {
                x: 771,
                d: "M488 230l89 355c4 14 4 17 4 26c0 18 -4 41 -64 43c-7 0 -15 0 -15 10c0 19 12 19 18 19c1 0 33 -2 95 -2h47c14 0 32 2 46 2c4 0 13 0 13 -11c0 -9 -4 -17 -12 -18c-88 -4 -96 -37 -109 -89l-38 -150c-7 -26 -28 -114 -38 -150c-6 -27 -15 -62 -22 -79\nc-51 -127 -163 -207 -264 -207c-109 0 -188 79 -188 189c0 27 6 54 9 65l87 350c5 17 12 46 12 52c0 11 0 19 -60 19c-19 0 -26 0 -26 10c0 19 12 19 19 19c19 0 40 -2 59 -2h118c19 0 40 2 58 2c6 0 15 0 15 -11c0 -18 -10 -18 -28 -18c-71 0 -73 -10 -82 -44l-98 -393\nc-11 -44 -11 -74 -11 -78c0 -83 49 -131 121 -131c94 0 210 82 245 222z"
            },
            V: {
                x: 799,
                d: "M615 572l-361 -574c-10 -16 -15 -19 -27 -19c-14 0 -14 3 -17 24l-80 617c-3 25 -6 34 -57 34c-16 0 -23 0 -23 11c0 18 12 18 18 18c1 0 31 -2 104 -2h56c17 0 37 2 54 2c3 0 14 0 14 -10c0 -19 -9 -19 -24 -19c-11 0 -63 -2 -63 -31l70 -539l314 498c1 1 15 24 15 39\nc0 12 -4 30 -41 33c-4 0 -13 1 -13 10c0 19 10 19 18 19c28 0 58 -2 87 -2c18 0 61 2 79 2c9 0 11 -6 11 -10c0 -18 -8 -18 -18 -19c-51 -4 -82 -28 -116 -82z"
            },
            W: {
                x: 1073,
                d: "M900 572l-329 -574c-8 -14 -11 -19 -21 -19c-12 0 -13 6 -14 23l-37 517l-298 -521c-9 -15 -11 -19 -21 -19c-12 0 -13 6 -14 23l-44 614c-2 32 -3 38 -50 38c-14 0 -22 0 -22 10c0 19 12 19 18 19c15 0 33 -2 48 -2h52c36 0 73 2 108 2c6 0 15 0 15 -11\nc0 -18 -11 -18 -22 -18c-68 -1 -68 -26 -68 -37l37 -516l259 455l-3 36c-4 61 -4 62 -50 62c-17 0 -24 0 -24 10c0 19 12 19 18 19c15 0 33 -2 48 -2h52c36 0 73 2 108 2c5 0 15 0 15 -10c0 -19 -10 -19 -23 -19c-56 -1 -67 -20 -67 -32l36 -521l273 478c10 17 14 24 14 37\nc0 31 -32 37 -52 38c-6 0 -14 1 -14 10c0 19 12 19 18 19c29 0 60 -2 90 -2c66 0 73 2 75 2c3 0 12 0 12 -11c0 -16 -9 -17 -18 -18c-60 -6 -82 -43 -105 -82z"
            },
            X: {
                x: 913,
                d: "M500 406l175 188c11 12 13 22 14 29l1 2c0 19 -17 27 -33 29c-6 0 -13 1 -13 11c0 18 12 18 19 18c31 0 66 -2 98 -2h46c14 0 29 2 43 2c4 0 13 0 13 -11c0 -17 -10 -17 -20 -18c-81 -5 -121 -46 -164 -91c-9 -10 -32 -35 -42 -45l-127 -136l132 -310\nc17 -37 19 -42 79 -43c9 0 19 0 19 -10c0 -19 -11 -19 -18 -19c-17 0 -36 2 -54 2h-110c-17 0 -37 -2 -54 -2c-5 0 -15 0 -15 10c0 18 11 19 17 19c32 2 54 20 54 29c0 1 -4 11 -5 13l-105 246c-22 -22 -75 -80 -97 -103l-89 -97c-29 -30 -41 -42 -41 -59\nc0 -16 13 -27 33 -29c6 0 13 -1 13 -11c0 -18 -12 -18 -19 -18c-31 0 -66 2 -98 2h-47c-14 0 -29 -2 -43 -2c-11 0 -12 7 -12 10c0 18 8 18 20 19c74 4 117 42 153 81l217 232l-119 278c-10 24 -14 34 -72 34c-13 0 -22 0 -22 10c0 19 10 19 18 19c1 0 45 -2 106 -2h58\nc17 0 37 2 54 2c6 0 15 0 15 -11c0 -17 -11 -18 -17 -18c-12 -1 -42 -5 -55 -29z"
            },
            Y: {
                x: 814,
                d: "M605 572l-251 -289c-9 -10 -9 -12 -15 -33l-36 -144c-5 -20 -13 -52 -13 -59c0 -11 0 -18 62 -18c17 0 24 0 24 -11c0 -18 -12 -18 -19 -18c-18 0 -39 2 -57 2h-118c-19 0 -40 -2 -58 -2c-3 0 -14 0 -14 10c0 19 8 19 25 19c64 1 74 6 82 33c3 10 52 207 52 210\ns-3 14 -4 17l-124 333c-9 23 -18 32 -67 32c-15 0 -24 0 -24 10c0 19 11 19 18 19c17 0 36 -2 54 -2h111c17 0 37 2 54 2c6 0 15 0 15 -11c0 -18 -11 -18 -23 -18s-53 -2 -53 -23c0 -2 0 -4 6 -18l113 -304l238 273c20 23 28 38 28 49c0 6 0 21 -30 23c-3 0 -13 1 -13 10\nc0 19 11 19 18 19c28 0 59 -2 88 -2h41c12 0 25 2 37 2c2 0 12 0 12 -10c0 -17 -9 -18 -20 -19c-35 -3 -72 -14 -116 -59z"
            },
            Z: {
                x: 754,
                d: "M693 652l-549 -621h173c174 0 218 78 260 207c8 25 9 26 17 26c6 0 10 -5 10 -10c0 -4 -2 -11 -3 -14l-70 -221c-6 -18 -7 -19 -30 -19h-427c-19 0 -24 0 -24 8c0 11 4 15 13 26l548 620h-164c-158 0 -207 -69 -244 -177c-4 -13 -6 -17 -14 -17c-7 0 -10 6 -10 10\nc0 2 0 4 3 14l55 180c6 18 7 19 30 19h413c23 0 24 -1 24 -8c0 -10 -5 -16 -11 -23z"
            },
            a: {
                x: 545,
                d: "M311 119l49 196c1 2 4 15 4 16c0 8 -13 90 -79 90c-39 0 -86 -36 -120 -106c-19 -42 -50 -154 -50 -209c0 -59 23 -96 64 -96c47 0 91 43 113 71c14 19 14 21 19 38zM375 375c2 12 10 47 42 47c15 0 26 -11 26 -25c0 -6 -9 -42 -46 -192c-8 -29 -13 -51 -20 -76\nc-9 -39 -12 -50 -12 -73c0 -20 2 -46 31 -46c41 0 60 63 75 122c4 15 5 20 14 20c6 0 10 -4 10 -9c0 -4 -14 -65 -30 -99c-18 -36 -42 -54 -72 -54s-77 17 -87 76c-28 -33 -75 -76 -130 -76c-68 0 -126 58 -126 157c0 148 122 294 235 294c52 0 79 -40 90 -66z"
            },
            b: {
                x: 458,
                d: "M236 669l-73 -291c19 20 66 63 118 63c75 0 127 -65 127 -157c0 -145 -120 -294 -236 -294c-65 0 -122 53 -122 155c0 13 0 30 5 55c4 18 61 245 71 285l24 96c4 18 12 47 12 54c0 10 0 19 -51 19c-10 0 -20 0 -20 10c0 18 7 19 31 21c18 1 33 3 50 4c18 2 54 5 55 5\nc2 0 12 0 12 -10c0 -5 -2 -11 -3 -15zM173 10c46 0 92 48 117 102c25 52 53 165 53 213c0 52 -20 96 -64 96c-68 0 -127 -89 -129 -97c-4 -13 -20 -77 -23 -90c-18 -70 -21 -89 -21 -122c0 -78 35 -102 67 -102z"
            },
            c: {
                x: 478,
                d: "M401 376c-20 43 -75 45 -88 45c-63 0 -117 -54 -143 -101c-36 -68 -53 -165 -53 -200c0 -50 22 -110 91 -110c38 0 133 15 197 100c9 9 10 10 13 10c4 0 10 -5 10 -11c0 -13 -86 -119 -222 -119c-102 0 -156 79 -156 166c0 142 131 285 262 285c69 0 115 -37 115 -85\nc0 -40 -27 -60 -51 -60c-19 0 -34 12 -34 32c0 16 11 31 17 36c14 12 23 12 42 12z"
            },
            d: {
                x: 566,
                d: "M513 669l-141 -560c-7 -28 -7 -49 -7 -53c0 -20 2 -46 31 -46c41 0 60 63 75 122c4 15 5 20 14 20c6 0 10 -4 10 -9c0 -4 -14 -65 -30 -99c-18 -36 -42 -54 -72 -54s-77 17 -87 76c-28 -33 -75 -76 -130 -76c-68 0 -126 58 -126 157c0 148 122 294 235 294\nc52 0 79 -40 90 -66l61 243c1 3 3 11 3 17c0 10 -1 19 -50 19c-14 0 -22 0 -22 10c0 18 7 19 32 21c18 1 33 3 50 4c5 1 54 5 55 5c2 0 12 0 12 -10c0 -5 -2 -11 -3 -15zM311 119l49 196c1 2 4 15 4 16c0 8 -13 90 -79 90c-39 0 -86 -36 -120 -106\nc-19 -42 -50 -154 -50 -209c0 -59 23 -96 64 -96c47 0 91 43 113 71c14 19 14 21 19 38z"
            },
            e: {
                x: 473,
                d: "M184 232h-53c-15 -64 -15 -89 -15 -103c0 -74 30 -119 87 -119c38 0 133 15 197 100c9 9 10 10 13 10c4 0 10 -5 10 -11c0 -13 -86 -119 -222 -119c-91 0 -151 73 -151 178c0 178 151 273 256 273c66 0 104 -40 104 -84c0 -18 -7 -76 -83 -105c-48 -18 -116 -20 -143 -20\nzM136 252h42c42 0 202 0 202 105c0 38 -30 64 -74 64c-32 0 -127 -15 -170 -169z"
            },
            f: {
                x: 595,
                d: "M445 402h-85c-5 -30 -54 -296 -72 -373c-11 -50 -53 -233 -155 -233c-39 0 -83 22 -83 65c0 38 30 55 50 55c19 0 34 -12 34 -32c0 -1 0 -45 -51 -49c18 -19 47 -19 50 -19c50 0 65 73 82 160c23 107 64 333 82 426h-64c-20 0 -26 0 -26 11c0 18 10 18 29 18h67\nc23 131 33 167 51 202c20 39 62 71 105 71c44 0 86 -23 86 -65c0 -38 -30 -55 -50 -55c-19 0 -34 12 -34 32c0 17 12 46 51 50c-10 9 -31 18 -52 18c-26 0 -47 -23 -53 -47c-8 -32 -22 -103 -41 -206h81c18 0 26 0 26 -10c0 -19 -8 -19 -28 -19z"
            },
            g: {
                x: 549,
                d: "M370 127l47 192l3 14c0 6 -13 88 -79 88c-42 0 -87 -40 -116 -96c-22 -45 -52 -157 -52 -210c0 -69 31 -95 64 -95c31 0 73 19 114 72c13 15 15 19 19 35zM432 375c3 27 17 47 41 47c15 0 26 -11 26 -25c0 -2 0 -4 -3 -16l-111 -448c-13 -54 -80 -137 -209 -137\nc-85 0 -126 16 -126 57c0 33 26 53 50 53c22 0 34 -16 34 -32c0 -11 -8 -39 -40 -48c25 -8 50 -10 80 -10c22 0 60 0 101 41c36 35 42 60 56 115l22 90c-18 -20 -65 -62 -118 -62c-64 0 -127 51 -127 155c0 149 124 286 233 286c50 0 78 -37 91 -66z"
            },
            h: {
                x: 580,
                d: "M276 669l-77 -307c41 52 91 79 148 79c76 0 111 -45 111 -105c0 -58 -45 -176 -63 -224c-4 -11 -16 -43 -16 -69c0 -32 16 -33 26 -33c40 0 77 42 100 122c5 17 6 20 15 20c6 0 10 -4 10 -9c0 -6 -35 -153 -128 -153c-48 0 -79 36 -79 81c0 18 5 31 14 55\nc22 60 61 169 61 225c0 40 -14 70 -54 70c-77 0 -122 -60 -141 -89s-19 -30 -30 -72c-5 -22 -11 -43 -16 -65l-45 -181c-5 -11 -18 -24 -35 -24c-10 0 -27 4 -27 26c0 6 0 8 4 23l145 579c1 3 3 11 3 17c0 10 -1 19 -50 19c-14 0 -22 0 -22 10c0 18 7 19 32 21\nc18 1 33 3 50 4c5 1 54 5 55 5c2 0 12 0 12 -10c0 -5 -2 -11 -3 -15z"
            },
            i: {
                x: 356,
                d: "M306 143c0 -7 -36 -153 -128 -153c-48 0 -79 36 -79 81c0 18 6 34 12 50l70 186c8 22 21 55 21 81c0 32 -17 33 -26 33c-39 0 -77 -40 -101 -124c-4 -14 -5 -18 -14 -18c-7 0 -11 5 -11 9c0 8 37 153 129 153c49 0 79 -38 79 -80c0 -19 -7 -37 -10 -45l-70 -186\nc-11 -30 -23 -60 -23 -87c0 -30 14 -33 26 -33c33 0 74 31 100 122c5 17 6 20 15 20c6 0 10 -4 10 -9zM298 624c0 -22 -23 -48 -50 -48c-12 0 -33 8 -33 33c0 26 26 48 49 48c22 0 34 -18 34 -33z"
            },
            j: {
                x: 504,
                d: "M413 317l-93 -370c-25 -100 -116 -151 -185 -151c-41 0 -85 17 -85 58c0 30 24 52 50 52c22 0 34 -16 34 -32c0 -24 -19 -43 -43 -48c19 -10 40 -10 44 -10c55 0 102 56 121 130l95 377c4 18 7 28 7 52c0 35 -11 46 -32 46c-38 0 -88 -31 -128 -128\nc-5 -10 -6 -14 -14 -14c-6 0 -10 4 -10 9c0 7 54 153 155 153c45 0 88 -32 88 -90c0 -4 0 -18 -4 -34zM454 624c0 -26 -26 -48 -49 -48c-22 0 -34 18 -34 33c0 22 23 48 50 48c12 0 33 -8 33 -33z"
            },
            k: {
                x: 546,
                d: "M276 669l-103 -411c37 14 74 52 84 64c71 80 116 119 171 119c45 0 68 -30 68 -59c0 -32 -24 -55 -51 -55c-14 0 -33 8 -33 32c0 20 14 46 51 50c-7 6 -13 12 -36 12c-46 0 -85 -32 -142 -96c-14 -15 -50 -54 -86 -76c92 -12 145 -45 145 -104c0 -12 0 -13 -4 -31\nc-4 -15 -8 -37 -8 -56c0 -38 13 -48 33 -48c51 0 72 70 86 122c4 16 5 20 14 20c6 0 10 -4 10 -9c0 -4 -13 -60 -34 -97c-8 -14 -32 -56 -78 -56c-51 0 -89 39 -89 97c0 5 0 17 4 32c3 15 3 19 3 25c0 48 -43 78 -115 86l-47 -187c-8 -31 -14 -53 -42 -53\nc-10 0 -27 4 -27 26c0 6 0 8 4 23l143 572c5 18 5 20 5 24c0 10 0 19 -51 19c-10 0 -20 0 -20 10c0 18 7 19 31 21c18 1 33 3 50 4c18 2 54 5 55 5c2 0 12 0 12 -10c0 -5 -2 -11 -3 -15z"
            },
            l: {
                x: 311,
                d: "M258 669l-141 -560c-7 -28 -7 -49 -7 -53c0 -14 0 -46 30 -46c40 0 58 53 76 122c4 15 5 20 14 20c6 0 10 -4 10 -9c0 -4 -14 -67 -31 -100c-16 -31 -38 -53 -71 -53c-51 0 -88 40 -88 90c0 16 2 24 5 35l124 496c5 18 5 20 5 24c0 10 0 19 -51 19c-10 0 -20 0 -20 10\nc0 18 7 19 31 21c18 1 33 3 50 4c18 2 54 5 55 5c2 0 12 0 12 -10c0 -5 -2 -11 -3 -15z"
            },
            m: {
                x: 900,
                d: "M229 293l-33 -132l-22 -90c-5 -20 -14 -55 -16 -60c-8 -15 -22 -21 -34 -21c-15 0 -26 11 -26 25c0 5 11 52 18 78c5 18 16 63 20 82l28 108c7 31 17 70 17 92c0 30 -8 46 -31 46c-40 0 -59 -59 -74 -118c-6 -23 -7 -24 -15 -24c-7 0 -11 5 -11 9s14 65 32 101\nc19 38 42 52 71 52c44 0 85 -31 88 -88c18 25 65 88 154 88c75 0 108 -43 111 -97c40 59 94 97 161 97c76 0 111 -45 111 -105c0 -58 -45 -176 -63 -224c-4 -11 -16 -43 -16 -69c0 -32 16 -33 26 -33c40 0 77 42 100 122c5 17 6 20 15 20c6 0 10 -4 10 -9\nc0 -6 -35 -153 -128 -153c-48 0 -79 36 -79 81c0 18 5 31 14 55c22 60 61 169 61 225c0 40 -14 70 -54 70c-110 0 -162 -125 -164 -133l-60 -239c-9 -36 -14 -59 -44 -59c-15 0 -26 11 -26 25c0 4 6 30 10 45c2 11 21 85 29 115l25 103c12 47 12 67 12 73\nc0 40 -14 70 -54 70c-39 0 -73 -16 -99 -40c-37 -32 -62 -82 -64 -88z"
            },
            n: {
                x: 628,
                d: "M229 293l-33 -132l-22 -90c-5 -20 -14 -55 -16 -60c-8 -15 -22 -21 -34 -21c-15 0 -26 11 -26 25c0 5 11 52 18 78c5 18 16 63 20 82l28 108c7 31 17 70 17 92c0 30 -8 46 -31 46c-40 0 -58 -57 -74 -118c-6 -23 -7 -24 -15 -24c-7 0 -11 5 -11 9s14 64 32 100\nc17 34 39 53 71 53c44 0 85 -31 88 -88c18 25 65 88 154 88c76 0 111 -45 111 -105c0 -57 -42 -169 -62 -224c-6 -14 -17 -44 -17 -69c0 -32 16 -33 26 -33c39 0 77 40 100 122c5 17 6 20 15 20c6 0 10 -4 10 -9c0 -6 -35 -153 -128 -153c-48 0 -79 36 -79 81\nc0 18 5 31 14 55c20 54 61 168 61 225c0 40 -14 70 -54 70c-39 0 -73 -16 -99 -40c-37 -32 -62 -82 -64 -88z"
            },
            o: {
                x: 515,
                d: "M465 275c0 -141 -129 -285 -262 -285c-91 0 -153 71 -153 166c0 141 130 285 262 285c91 0 153 -71 153 -166zM204 10c39 0 94 26 135 93c38 61 59 168 59 208c0 70 -38 110 -87 110c-41 0 -97 -26 -142 -103c-28 -50 -52 -152 -52 -198c0 -70 37 -110 87 -110z"
            },
            p: {
                x: 609,
                d: "M124 -127l113 450c4 18 7 28 7 52c0 30 -8 46 -31 46c-40 0 -59 -59 -74 -118c-6 -23 -7 -24 -15 -24c-7 0 -11 5 -11 9s14 65 32 101c19 38 42 52 71 52c40 0 79 -26 87 -76c11 14 65 76 129 76c75 0 127 -65 127 -157c0 -147 -122 -294 -235 -294c-52 0 -79 39 -90 67\nc-6 -23 -51 -195 -51 -207c0 -8 3 -15 49 -15c17 0 24 0 24 -11c0 -18 -12 -18 -18 -18c-21 0 -72 2 -93 2h-43c-13 0 -27 -2 -40 -2c-11 0 -12 8 -12 10c0 19 10 19 22 19c41 0 45 8 52 38zM297 312l-49 -196c-1 -5 -3 -13 -3 -16c0 -4 11 -90 79 -90c46 0 92 48 117 102\nc25 52 53 165 53 213c0 52 -20 96 -64 96c-38 0 -71 -28 -82 -38c-16 -14 -44 -43 -51 -71z"
            },
            q: {
                x: 502,
                d: "M452 431l-139 -562c-3 -13 -4 -14 -4 -19c0 -8 3 -15 50 -15c15 0 23 0 23 -10c0 -19 -12 -19 -18 -19c-15 0 -32 2 -47 2h-102c-14 0 -32 -2 -46 -2c-7 0 -13 3 -13 10c0 19 10 19 21 19c47 1 62 1 71 31l9 36l38 150l-1 1c-21 -23 -65 -63 -118 -63\nc-68 0 -126 58 -126 157c0 148 122 294 235 294c43 0 74 -27 92 -72c19 35 58 71 66 71c5 0 9 -4 9 -9zM311 119l49 196c1 2 4 15 4 16c0 8 -13 90 -79 90c-39 0 -86 -36 -120 -106c-19 -42 -50 -154 -50 -209c0 -59 23 -96 64 -96c47 0 91 43 113 71c14 19 14 21 19 38z\n"
            },
            r: {
                x: 499,
                d: "M412 409c-13 9 -29 12 -45 12c-41 0 -67 -23 -79 -34c-25 -22 -52 -71 -55 -80c-3 -13 -7 -29 -11 -43c-5 -22 -11 -43 -16 -65c-6 -23 -43 -174 -46 -182c-6 -20 -25 -27 -36 -27c-15 0 -26 11 -26 25c0 5 11 52 18 78c5 18 16 63 20 82l28 108c7 31 17 70 17 92\nc0 29 -7 46 -31 46c-40 0 -59 -58 -74 -118c-6 -23 -7 -24 -15 -24c-7 0 -11 5 -11 9c0 5 15 65 32 101c12 22 32 52 71 52c40 0 80 -26 87 -79c42 61 88 79 128 79c50 0 81 -28 81 -60c0 -30 -23 -54 -51 -54c-14 0 -33 8 -33 32c0 22 16 44 47 50z"
            },
            s: {
                x: 462,
                d: "M227 200c-23 5 -97 21 -97 97c0 41 35 144 166 144c78 0 116 -43 116 -85s-28 -53 -42 -53c-9 0 -28 6 -28 27c0 1 2 39 46 39c-10 36 -51 52 -93 52c-95 0 -113 -72 -113 -92c0 -45 43 -54 70 -60c54 -11 78 -16 103 -40c32 -32 32 -60 32 -75c0 -52 -45 -164 -197 -164\nc-80 0 -140 37 -140 96c0 45 29 62 51 62c20 0 34 -11 34 -32c0 -20 -18 -49 -52 -49c-3 0 -5 1 -8 1c20 -55 95 -58 116 -58c116 0 144 81 144 112c0 48 -45 64 -58 68c-6 1 -33 7 -50 10z"
            },
            t: {
                x: 400,
                d: "M229 402l-78 -313c-3 -12 -3 -31 -3 -32c0 -22 2 -47 32 -47c58 0 104 70 125 120c7 16 8 17 9 18c2 4 6 4 8 4c6 0 10 -3 10 -9c0 -7 -54 -153 -155 -153c-56 0 -89 43 -89 90c0 13 3 23 11 58l66 264h-89c-19 0 -26 0 -26 10c0 19 9 19 29 19h94l39 159\nc8 32 31 35 39 35c13 0 26 -9 26 -25c0 -7 0 -9 -4 -23l-37 -146h88c20 0 26 0 26 -11c0 -18 -10 -18 -29 -18h-92z"
            },
            u: {
                x: 601,
                d: "M364 58c-20 -24 -55 -68 -119 -68c-41 0 -118 16 -118 118c0 30 5 68 49 187c15 38 26 67 26 93c0 32 -16 33 -26 33c-38 0 -77 -38 -101 -124c-4 -14 -5 -18 -14 -18c-7 0 -11 5 -11 9c0 8 37 153 129 153c48 0 79 -37 79 -80c0 -20 -5 -33 -17 -65\nc-26 -67 -54 -147 -54 -204c0 -37 10 -82 62 -82c73 0 112 82 113 82c7 35 61 250 71 289c6 25 13 50 42 50c15 0 27 -10 27 -25c0 -3 0 -5 -7 -30l-41 -163c-6 -28 -13 -55 -20 -82c-10 -42 -13 -52 -13 -75c0 -13 0 -46 30 -46c40 0 58 53 76 122c4 15 5 20 14 20\nc6 0 10 -4 10 -9c0 -3 -13 -62 -30 -98c-16 -34 -40 -55 -72 -55c-24 0 -72 12 -85 68z"
            },
            v: {
                x: 530,
                d: "M480 374c0 -57 -57 -384 -223 -384c-9 0 -59 0 -95 31c-31 28 -34 69 -34 91c0 24 0 55 48 183c15 38 26 67 26 93c0 33 -17 33 -26 33c-38 0 -77 -38 -101 -124c-4 -14 -5 -18 -14 -18c-7 0 -11 5 -11 9c0 8 37 153 129 153c49 0 79 -38 79 -80c0 -20 -4 -30 -20 -71\nc-27 -72 -50 -142 -50 -192c0 -37 12 -88 73 -88c120 0 180 234 180 276c0 52 -26 79 -36 88c-4 4 -11 11 -11 23c0 20 22 44 46 44c8 0 40 -4 40 -67z"
            },
            w: {
                x: 748,
                d: "M367 61c-40 -71 -85 -71 -102 -71c-51 0 -137 19 -137 124c0 38 10 79 57 204c17 44 17 65 17 70c0 32 -16 33 -26 33c-37 0 -76 -36 -101 -124c-4 -14 -5 -18 -14 -18c-7 0 -11 5 -11 9c0 8 37 153 129 153c49 0 79 -38 79 -80c0 -20 -5 -33 -17 -64\nc-45 -119 -52 -163 -52 -195c0 -17 0 -92 79 -92c31 0 47 17 58 28c1 2 33 40 33 64c0 22 0 33 4 50c4 22 46 191 56 230c6 24 13 49 42 49c15 0 27 -10 27 -25c0 -4 -9 -39 -14 -59l-42 -168c-4 -17 -13 -52 -13 -81c0 -46 18 -88 74 -88c52 0 89 40 117 101\nc21 47 49 141 49 175c0 52 -26 79 -35 88c-5 4 -12 11 -12 23c0 20 22 44 46 44c9 0 40 -6 40 -67c0 -59 -37 -191 -60 -249c-28 -69 -73 -135 -149 -135c-38 0 -100 11 -122 71z"
            },
            x: {
                x: 586,
                d: "M496 408c-21 13 -49 13 -51 13c-46 0 -80 -44 -95 -104l-35 -136c-16 -66 -20 -82 -20 -104c0 -41 19 -67 54 -67c45 0 103 41 130 129c3 8 4 13 13 13c6 0 10 -4 10 -9c0 -22 -54 -153 -156 -153c-37 0 -87 20 -103 79c-21 -41 -55 -79 -103 -79c-43 0 -90 19 -90 61\nc0 32 26 53 50 53c19 0 34 -12 34 -32c0 -12 -8 -42 -44 -49c21 -13 46 -13 51 -13c53 0 82 55 97 115l34 137c12 48 18 71 18 93c0 24 -9 66 -54 66c-29 0 -96 -21 -130 -132c-2 -4 -4 -10 -12 -10c-7 0 -11 5 -11 9c0 22 54 153 156 153c18 0 80 -4 103 -79\nc9 19 43 79 104 79c42 0 90 -19 90 -61c0 -27 -21 -53 -51 -53c-14 0 -33 8 -33 32c0 19 12 43 44 49z"
            },
            y: {
                x: 551,
                d: "M286 -112c35 49 45 88 60 147l-1 1c-35 -37 -71 -46 -100 -46c-50 0 -118 22 -118 117c0 24 0 57 49 188c15 38 26 67 26 93c0 33 -17 33 -26 33c-39 0 -77 -40 -101 -124c-4 -14 -5 -18 -14 -18c-7 0 -11 5 -11 9c0 8 37 153 129 153c49 0 79 -38 79 -80\nc0 -20 -6 -34 -17 -63c-27 -72 -54 -149 -54 -206c0 -27 5 -82 61 -82c48 0 81 34 101 62c8 11 8 13 13 31l74 294c8 30 29 34 38 34c15 0 27 -10 27 -25c0 -6 -2 -12 -3 -17l-98 -390c-28 -111 -129 -203 -229 -203c-57 0 -100 35 -100 86c0 49 35 61 51 61\nc15 0 34 -8 34 -31s-20 -49 -50 -49c-2 0 -7 0 -10 1c15 -46 63 -48 75 -48c49 0 88 34 115 72z"
            },
            z: {
                x: 517,
                d: "M134 81c9 4 20 4 30 4c13 0 24 0 64 -16c21 -9 41 -16 62 -16c39 0 98 24 120 83c3 10 4 14 12 14c10 0 11 -6 11 -10c0 -23 -56 -150 -154 -150c-32 0 -49 21 -68 43c-20 25 -30 32 -51 32c-37 0 -69 -37 -85 -62c-7 -12 -8 -13 -14 -13s-11 2 -11 8c0 7 37 66 98 128\nl101 93c45 41 99 89 132 128c-5 -1 -9 -1 -16 -1c-10 0 -26 0 -64 16c-21 9 -41 16 -62 16c-19 0 -67 -7 -84 -47c-3 -7 -4 -11 -12 -11c-9 0 -10 6 -10 9c0 21 44 112 117 112c29 0 46 -17 67 -42c19 -24 30 -33 51 -33c26 0 50 21 75 64c5 7 7 11 14 11c2 0 10 0 10 -8\ns-35 -66 -105 -135c-14 -15 -40 -40 -100 -92c-50 -45 -85 -76 -128 -125z"
            },
            "&#x237;": {
                x: 467,
                d: "M413 317l-93 -370c-24 -95 -105 -151 -177 -151c-43 0 -93 15 -93 58c0 30 24 52 50 52c22 0 34 -16 34 -32c0 -7 -5 -39 -41 -48c22 -10 45 -10 50 -10c58 0 96 64 113 130l95 377c4 18 7 28 7 52c0 35 -11 46 -32 46c-36 0 -87 -28 -128 -128c-5 -10 -6 -14 -14 -14\nc-6 0 -10 4 -10 9c0 7 54 153 155 153c45 0 88 -32 88 -90c0 -4 0 -18 -4 -34z"
            },
            "&#x131;": {
                x: 356,
                d: "M306 143c0 -7 -36 -153 -128 -153c-48 0 -79 36 -79 81c0 18 6 34 12 50l70 186c8 22 21 55 21 81c0 32 -17 33 -26 33c-39 0 -77 -40 -101 -124c-4 -14 -5 -18 -14 -18c-7 0 -11 5 -11 9c0 8 37 153 129 153c49 0 79 -38 79 -80c0 -19 -7 -37 -10 -45l-70 -186\nc-11 -30 -23 -60 -23 -87c0 -30 14 -33 26 -33c33 0 74 31 100 122c5 17 6 20 15 20c6 0 10 -4 10 -9z"
            },
            "&#x3b1;": {
                x: 649,
                d: "M473 253v-85c62 82 88 160 101 204c4 18 5 21 14 21c3 0 11 0 11 -9c0 -1 -24 -127 -126 -249c0 -61 0 -125 31 -125c24 0 47 22 56 47c3 8 4 11 12 11c3 0 10 0 10 -9c0 -13 -27 -69 -81 -69c-43 0 -74 28 -88 84c-71 -60 -145 -84 -207 -84c-102 0 -156 79 -156 166\nc0 142 131 285 262 285c106 0 161 -91 161 -188zM410 98c-1 11 -2 16 -2 36c0 25 1 51 1 76c0 84 0 211 -97 211c-45 0 -101 -31 -142 -101c-31 -55 -53 -157 -53 -200c0 -47 20 -110 91 -110c35 0 114 9 202 88z"
            },
            "&#x3b2;": {
                x: 637,
                d: "M587 582c0 -79 -44 -140 -116 -178c16 -12 31 -26 43 -45c21 -32 30 -70 30 -108c0 -149 -135 -261 -278 -261c-59 0 -114 46 -124 102l-70 -280c-1 -5 -6 -6 -13 -6c-5 0 -11 2 -9 10l158 627c26 104 108 262 237 262c91 0 142 -63 142 -123zM413 405c-14 4 -32 6 -50 6\nc-21 0 -36 -2 -46 -9c14 -4 25 -5 35 -5h10c19 0 35 2 51 8zM535 591c0 44 -20 94 -92 94c-97 0 -181 -118 -214 -249l-65 -263c-4 -15 -4 -28 -4 -37c0 -68 38 -126 111 -126c69 0 147 48 180 122c19 43 35 90 35 148c0 50 -14 84 -46 110c-24 -7 -47 -13 -73 -13h-12\nc-23 0 -60 2 -60 24c0 28 48 30 73 30h8c22 0 48 -5 67 -12c65 37 92 117 92 172z"
            },
            "&#x3b3;": {
                x: 613,
                d: "M409 122c33 88 53 133 68 167c30 68 65 136 67 138c2 4 7 4 9 4c9 0 10 -7 10 -8c0 -3 -14 -30 -21 -46c-79 -157 -136 -322 -139 -346c-5 -40 -11 -84 -35 -172c-11 -40 -20 -73 -35 -73c-8 0 -11 9 -11 17c0 28 33 152 52 214c6 20 13 41 13 104c0 99 -20 254 -159 254\nc-69 0 -133 -49 -154 -111c-5 -13 -5 -15 -14 -15c-3 0 -10 0 -10 9c0 26 67 183 190 183c73 0 107 -53 127 -103c38 -90 40 -167 42 -216z"
            },
            "&#x3b4;": {
                x: 504,
                d: "M270 436c-34 66 -59 123 -59 172c0 102 98 102 115 102s31 0 78 -10c35 -8 50 -11 50 -30c0 -16 -15 -37 -36 -37c-13 0 -36 13 -45 19c-30 17 -49 28 -80 28c-44 0 -57 -30 -57 -47c0 -48 60 -125 97 -172c25 -33 67 -88 67 -178c0 -133 -80 -295 -198 -295\nc-80 0 -152 60 -152 167c0 117 92 250 220 281zM281 417c-136 -36 -173 -225 -173 -290c0 -93 56 -119 95 -119c89 0 131 157 131 235c0 65 -15 95 -53 174z"
            },
            "&#x3b5;": {
                x: 421,
                d: "M295 227h-166c-9 -40 -11 -66 -11 -85c0 -97 56 -132 109 -132c30 0 67 12 105 37c5 4 7 5 10 5c6 0 9 -6 9 -12c0 -10 -63 -50 -127 -50c-99 0 -174 75 -174 186c0 157 135 255 258 255h34c18 0 29 0 29 -13c0 -16 -15 -16 -32 -16h-29c-89 0 -148 -55 -173 -146h162\nc18 0 28 0 28 -13c0 -16 -17 -16 -32 -16z"
            },
            "&#x3b6;": {
                x: 521,
                d: "M194 49l92 -32c37 -12 95 -32 95 -102c0 -52 -44 -119 -108 -119c-50 0 -93 38 -93 48c0 2 2 10 10 10c5 0 7 -2 11 -6c25 -25 52 -32 72 -32c37 0 58 39 58 68c0 48 -39 61 -80 75c-13 5 -45 16 -58 20c-52 18 -143 50 -143 187c0 187 146 374 262 434\nc-12 21 -12 42 -12 51c0 8 0 45 16 45c5 0 10 -4 10 -10c0 -3 -4 -17 -4 -35c0 -15 3 -29 9 -42c26 11 43 11 63 11c32 0 77 -1 77 -25c0 -29 -61 -29 -86 -29c-19 0 -42 0 -60 17c-123 -71 -224 -254 -224 -391c0 -75 34 -120 93 -143zM347 593c11 -7 18 -7 39 -7\nc42 0 50 3 62 8c-20 5 -23 6 -55 6c-17 0 -31 0 -46 -7z"
            },
            "&#x3b7;": {
                x: 556,
                d: "M498 277l-115 -458c-8 -30 -29 -34 -38 -34c-15 0 -27 10 -27 25c0 6 2 12 3 17l116 460c5 19 9 35 9 64c0 40 -14 70 -54 70c-39 0 -73 -16 -99 -40c-37 -32 -62 -82 -64 -88l-33 -132l-22 -90c-5 -20 -14 -55 -16 -60c-8 -15 -22 -21 -34 -21c-15 0 -26 11 -26 25\nc0 5 11 52 18 78c5 18 16 63 20 82l28 108c7 31 17 70 17 92c0 30 -8 46 -31 46c-40 0 -59 -59 -74 -118c-6 -23 -7 -24 -15 -24c-7 0 -11 5 -11 9s14 65 32 101c19 38 42 52 71 52c44 0 85 -31 88 -88c18 25 65 88 154 88c76 0 111 -45 111 -105c0 -27 -3 -39 -8 -59z"
            },
            "&#x3b8;": {
                x: 503,
                d: "M453 503c0 -227 -152 -513 -288 -513c-105 0 -115 155 -115 201c0 220 149 513 289 513c82 0 114 -99 114 -201zM150 362h211c29 112 32 162 32 198c0 96 -22 124 -55 124c-44 0 -79 -48 -112 -108c-40 -71 -61 -155 -76 -214zM354 332h-212c-16 -66 -32 -134 -32 -199\nc0 -99 25 -123 56 -123c42 0 77 47 106 98c36 63 57 126 82 224z"
            },
            "&#x3b9;": {
                x: 361,
                d: "M311 143c0 -33 -69 -153 -180 -153c-56 0 -81 42 -81 81c0 34 23 73 32 108c7 25 6 17 13 43c4 12 29 103 30 107c6 19 16 67 23 86c6 16 20 26 36 26c13 0 26 -9 26 -25c0 -8 -38 -169 -88 -305c-5 -14 -16 -44 -16 -68c0 -31 15 -33 27 -33c42 0 123 32 155 129\nc3 9 5 13 13 13c6 0 10 -4 10 -9z"
            },
            "&#x3ba;": {
                x: 580,
                d: "M208 250c66 -3 192 -12 192 -103c0 -11 -3 -23 -5 -33c-4 -15 -8 -37 -8 -56c0 -35 11 -48 33 -48c51 0 73 74 86 122c4 16 5 20 14 20c6 0 10 -4 10 -9c0 -4 -13 -59 -34 -98c-15 -26 -38 -55 -79 -55c-49 0 -88 37 -88 97c0 6 0 17 4 36c3 11 3 14 3 21\nc0 49 -43 81 -170 87c-2 -6 -43 -173 -49 -194c-5 -21 -11 -47 -41 -47c-15 0 -26 11 -26 25c0 6 0 8 3 20l93 371c8 32 30 35 39 35c13 0 26 -9 26 -25l-38 -159c41 13 70 35 128 86c44 37 103 88 163 88c35 0 37 -26 37 -32c0 -19 -18 -49 -50 -49c-10 0 -33 5 -33 32\nc0 7 1 15 5 21c-38 -15 -60 -31 -120 -83c-25 -21 -62 -52 -95 -70z"
            },
            "&#x3bb;": {
                x: 582,
                d: "M306 623l201 -575c4 -12 11 -30 20 -41c4 -4 5 -5 5 -9c0 -8 -7 -8 -11 -8h-19c-28 0 -30 0 -40 9c-14 13 -19 27 -24 42c-31 84 -63 176 -89 254c-91 -108 -234 -283 -250 -298c-6 -6 -17 -8 -21 -8c-16 0 -28 12 -28 26c0 13 8 21 20 33l260 263c8 8 9 9 9 10\nc0 2 -103 301 -113 320c-16 29 -28 31 -43 33c-5 1 -11 2 -11 10c0 10 11 10 17 10c13 0 92 0 117 -71z"
            },
            "&#x3bc;": {
                x: 632,
                d: "M166 22l-47 -186c-7 -28 -13 -51 -42 -51c-15 0 -27 10 -27 25c0 6 2 12 3 17l145 579c8 32 30 35 39 35c13 0 26 -9 26 -25c0 -6 -8 -38 -13 -57l-45 -180c-4 -17 -13 -52 -13 -81c0 -47 18 -88 74 -88c63 0 102 54 114 71c13 16 13 18 18 38l70 278c8 30 29 34 38 34\nc15 0 27 -10 27 -25c0 -4 -11 -47 -17 -71c-5 -18 -16 -63 -20 -82l-28 -108c-7 -30 -16 -66 -16 -89c0 -20 2 -46 31 -46c41 0 60 63 75 122c4 15 5 20 14 20c6 0 10 -4 10 -9c0 -4 -14 -65 -30 -99c-18 -36 -42 -54 -72 -54c-35 0 -77 21 -87 74c-23 -31 -67 -74 -131 -74\nc-34 0 -71 9 -96 32z"
            },
            "&#x3bd;": {
                x: 561,
                d: "M217 431l-99 -401c133 44 270 168 329 377c2 7 9 34 38 34c13 0 26 -9 26 -25c0 -4 -18 -127 -147 -260c-117 -120 -269 -156 -283 -156h-21c-7 0 -10 6 -10 10l88 353c1 4 3 16 3 20c0 11 -3 18 -50 18c-14 0 -22 0 -22 10c0 18 10 19 21 20c15 1 109 10 116 10\nc8 0 11 -6 11 -10z"
            },
            "&#x3be;": {
                x: 517,
                d: "M289 -5l60 -24c32 -14 49 -39 49 -70c0 -43 -39 -105 -103 -105c-54 0 -106 37 -106 48c0 6 5 10 10 10c4 0 5 -1 12 -7c36 -27 69 -31 84 -31c35 0 51 34 51 55c0 16 -8 28 -18 35c-7 4 -48 21 -73 30l-90 36c-36 15 -115 46 -115 133c0 54 44 156 147 212\nc-27 15 -67 48 -67 108c0 65 53 150 173 185c-6 13 -7 27 -7 41c0 8 0 45 16 45c5 0 10 -4 10 -10c0 -3 -4 -17 -4 -35c0 -22 1 -24 6 -36c28 5 43 5 63 5c32 0 80 0 80 -25c0 -29 -61 -29 -86 -29c-21 0 -45 0 -63 21c-79 -37 -115 -115 -115 -170c0 -30 10 -62 34 -82\nc36 12 63 12 83 12c43 0 79 -3 79 -25c0 -29 -59 -29 -90 -29c-18 0 -44 0 -71 8c-3 1 -12 3 -14 3c-21 0 -126 -87 -126 -173c0 -60 60 -84 86 -94zM340 595c11 -9 23 -9 42 -9c42 0 50 3 62 8c-20 5 -23 6 -56 6c-13 0 -31 0 -48 -5zM261 321c18 -8 28 -8 52 -8\nc43 0 52 3 63 8c-22 5 -24 6 -57 6c-12 0 -34 0 -58 -6z"
            },
            "&#x3bf;": {
                x: 515,
                d: "M465 275c0 -141 -129 -285 -262 -285c-91 0 -153 71 -153 166c0 141 130 285 262 285c91 0 153 -71 153 -166zM204 10c39 0 94 26 135 93c38 61 59 168 59 208c0 70 -38 110 -87 110c-41 0 -97 -26 -142 -103c-28 -50 -52 -152 -52 -198c0 -70 37 -110 87 -110z"
            },
            "&#x3c0;": {
                x: 627,
                d: "M283 377l-58 -226c-10 -40 -10 -42 -26 -93c-12 -42 -20 -68 -49 -68c-9 0 -27 5 -27 26c0 6 0 8 8 24c82 175 107 262 129 337h-57c-25 0 -77 0 -129 -82c-5 -6 -6 -9 -13 -9c-6 0 -11 2 -11 8s32 59 58 88c46 49 80 49 103 49h329c18 0 37 0 37 -22\nc0 -32 -33 -32 -46 -32h-112c-21 -90 -21 -161 -21 -167c0 -7 0 -93 27 -162c7 -15 7 -17 7 -22c0 -17 -18 -36 -39 -36c-41 0 -41 108 -41 122c0 80 17 153 44 265h-113z"
            },
            "&#x3c1;": {
                x: 563,
                d: "M53 -173l98 390c33 133 140 224 228 224c69 0 134 -53 134 -160c0 -151 -128 -291 -241 -291c-39 0 -76 21 -96 71c-32 -123 -60 -243 -64 -252c-6 -14 -20 -24 -35 -24s-27 10 -27 25c0 6 2 12 3 17zM271 10c51 0 103 50 133 119c17 42 44 141 44 192\nc0 59 -25 100 -70 100c-15 0 -113 -5 -164 -204c-3 -15 -28 -110 -28 -116c0 -12 17 -91 85 -91z"
            },
            "&#x3c2;": {
                x: 470,
                d: "M240 25l-97 55c-30 17 -93 52 -93 135c0 112 123 226 270 226c40 0 100 -9 100 -30c0 -6 -6 -15 -15 -15c-2 0 -4 0 -11 4c-24 11 -48 21 -79 21c-108 0 -216 -83 -216 -176c0 -63 38 -84 94 -116l56 -31c10 -6 38 -21 48 -28c11 -6 41 -27 41 -71\nc0 -46 -41 -106 -101 -106c-16 0 -58 8 -58 22c0 5 5 9 10 9c2 0 8 -2 10 -3c17 -7 35 -8 38 -8c34 0 48 34 48 55c0 31 -20 42 -45 57z"
            },
            "&#x3c3;": {
                x: 616,
                d: "M520 377h-111c24 -33 33 -71 33 -110c0 -150 -129 -277 -247 -277c-90 0 -145 71 -145 156c0 118 108 285 258 285h221c18 0 37 0 37 -22c0 -32 -33 -32 -46 -32zM196 10c45 0 99 33 135 92c35 58 51 142 51 177c0 77 -48 98 -91 98c-147 0 -183 -199 -183 -259\nc0 -69 37 -108 88 -108z"
            },
            "&#x3c4;": {
                x: 573,
                d: "M311 377l-68 -346c-3 -15 -8 -42 -39 -42c-20 0 -27 14 -27 25c0 3 0 5 5 20l104 343h-83c-25 0 -77 0 -129 -82c-5 -6 -6 -9 -13 -9c-6 0 -11 2 -11 8s32 59 58 88c46 49 80 49 103 49h275c18 0 37 0 37 -22c0 -32 -33 -32 -46 -32h-166z"
            },
            "&#x3c5;": {
                x: 585,
                d: "M535 374c0 -32 -17 -173 -88 -277c-26 -38 -80 -107 -163 -107c-74 0 -155 29 -155 129c0 26 0 52 56 199c17 44 17 65 17 70c0 32 -16 33 -26 33c-37 0 -76 -36 -101 -124c-4 -14 -5 -18 -14 -18c-7 0 -11 5 -11 9c0 8 37 153 129 153c49 0 79 -38 79 -80\nc0 -19 -5 -32 -17 -65c-39 -101 -51 -149 -51 -188c0 -29 7 -54 25 -73c26 -25 66 -25 72 -25c128 0 209 209 209 276c0 52 -26 79 -35 88c-5 4 -12 11 -12 23c0 20 22 44 46 44c9 0 40 -6 40 -67z"
            },
            "&#x3c6;": {
                x: 611,
                d: "M433 685l-59 -242c111 -7 187 -79 187 -180c0 -126 -130 -266 -301 -275l-37 -149c-4 -13 -10 -37 -12 -39c-2 -4 -6 -4 -9 -4c-10 0 -10 6 -10 8c0 4 8 37 22 90c8 31 15 63 23 94c-104 6 -187 72 -187 180c0 125 126 260 290 274c2 0 9 1 10 3c1 1 2 2 5 15l54 215\nc4 17 5 19 14 19c3 0 10 0 10 -9zM368 423l-103 -414c156 12 235 162 235 275c0 98 -66 135 -132 139zM242 8l104 414c-144 -9 -235 -149 -235 -275c0 -110 82 -136 131 -139z"
            },
            "&#x3c7;": {
                x: 651,
                d: "M349 161l226 258c10 11 13 11 16 11c5 0 10 -3 10 -10c0 -4 -1 -5 -9 -14l-237 -270c17 -62 40 -142 66 -209c44 -111 60 -111 73 -111c16 0 42 14 53 42c3 8 4 12 12 12c5 0 11 -2 11 -9c0 -14 -26 -65 -83 -65c-85 0 -106 49 -122 87c-15 37 -15 39 -64 193l-116 -132\nc-26 -30 -88 -104 -116 -132c-3 -3 -6 -5 -10 -5s-9 2 -9 10c0 1 0 5 9 16l236 268c-27 97 -39 139 -63 200c-18 48 -46 120 -75 120c-9 0 -40 -6 -55 -45c-1 -4 -3 -9 -11 -9c-6 0 -11 3 -11 9c0 13 27 65 83 65c35 0 69 -12 87 -28c14 -14 22 -21 53 -107\nc25 -70 19 -62 46 -145z"
            },
            "&#x3c8;": {
                x: 693,
                d: "M493 670l-164 -660c30 0 107 0 191 94c64 72 84 146 84 182c0 52 -27 79 -36 88c-4 4 -11 11 -11 23c0 20 22 44 46 44c10 0 40 -7 40 -67c0 -51 -24 -140 -34 -166c-17 -43 -47 -89 -80 -125c-90 -93 -169 -93 -206 -93c-4 -18 -45 -186 -49 -190c-2 -4 -6 -4 -8 -4\nc-3 0 -11 0 -11 9c0 4 7 32 23 92c8 31 15 63 23 94c-90 7 -170 44 -170 140c0 23 0 45 54 187c17 44 17 66 17 70c0 32 -17 33 -26 33c-37 0 -77 -37 -101 -124c-4 -14 -5 -18 -14 -18c-7 0 -11 5 -11 9c0 8 37 153 129 153c47 0 79 -36 79 -81c0 -18 -5 -31 -18 -67\nc-47 -124 -47 -147 -47 -169c0 -60 31 -102 114 -112l165 662c4 15 5 20 14 20c3 0 11 0 11 -9c0 -1 -4 -14 -4 -15z"
            },
            "&#x3c9;": {
                x: 682,
                d: "M632 376c0 -55 -31 -180 -57 -238c-36 -81 -84 -148 -160 -148c-73 0 -103 53 -111 109c-21 -38 -73 -109 -156 -109c-75 0 -98 74 -98 136c0 96 36 207 87 286c8 12 13 25 29 25c9 0 13 -11 13 -18c0 -2 -7 -12 -17 -27c-18 -25 -83 -116 -83 -220\nc0 -57 18 -116 85 -116c64 0 118 62 137 99c-1 21 12 100 26 122c5 8 13 12 20 12c13 0 17 -9 17 -20c0 -26 -23 -91 -34 -117c11 -56 35 -96 98 -96c52 0 98 47 126 102c16 31 38 88 38 124c0 47 -19 74 -34 91c-8 9 -12 15 -12 24c0 20 23 45 43 45c35 0 43 -37 43 -66z\n"
            },
            "&#x3d1;": {
                x: 620,
                d: "M520 356l28 -7c15 -3 22 -4 22 -11c0 -4 -3 -11 -9 -11c-5 0 -23 4 -46 9c-40 -156 -144 -346 -259 -346c-16 0 -61 1 -94 25c-36 28 -36 73 -36 87c0 13 0 27 8 62l18 73c4 17 13 51 18 73c7 28 11 43 11 65c0 30 -8 46 -31 46c-40 0 -59 -59 -74 -118\nc-6 -23 -7 -24 -15 -24c-7 0 -11 5 -11 9s14 65 32 101c19 38 42 52 71 52c41 0 87 -29 87 -90c0 -16 -5 -36 -10 -54c-8 -35 -3 -14 -12 -48c-7 -30 -13 -52 -19 -77c-8 -35 -13 -59 -13 -82c0 -20 1 -80 73 -80c40 0 74 37 116 118c28 57 49 115 75 227\nc-61 20 -160 68 -160 167c0 74 62 182 144 182c91 0 106 -126 106 -195c0 -34 -2 -76 -20 -153zM456 375c18 80 29 132 29 190c0 56 -5 119 -53 119c-67 0 -120 -96 -120 -161c0 -67 51 -117 144 -148z"
            },
            "&#x3d5;": {
                x: 658,
                d: "M302 44c153 0 281 125 281 239c0 55 -30 104 -95 104c-87 0 -163 -92 -195 -220c-6 -22 -23 -116 -23 -118c0 -5 15 -5 32 -5zM259 -8c-4 -20 -32 -175 -35 -184c-8 -20 -26 -24 -36 -24c-2 0 -26 1 -26 25c0 4 0 6 5 21l52 169c-81 18 -169 79 -169 191\nc0 95 76 241 103 241c2 0 10 0 10 -8c0 -2 0 -4 -11 -17c-58 -72 -79 -179 -79 -206c0 -67 48 -128 163 -150c56 188 117 391 261 391c84 0 111 -74 111 -135c0 -158 -151 -316 -315 -316c-18 0 -28 1 -34 2z"
            },
            "&#x3d6;": {
                x: 872,
                d: "M211 431h574c18 0 37 0 37 -22c0 -14 -10 -26 -22 -30c-6 -2 -33 -2 -50 -2c7 -24 11 -50 11 -75c0 -102 -89 -312 -230 -312c-78 0 -101 60 -101 132c0 12 1 27 2 36l-1 1c-49 -96 -127 -169 -214 -169c-64 0 -85 51 -85 112c0 136 77 236 107 275c-65 0 -75 0 -102 -17\nc-35 -21 -53 -48 -63 -64c-5 -8 -6 -10 -13 -10c-6 0 -11 2 -11 8s32 59 58 88c46 49 80 49 103 49zM727 377h-461c-93 -112 -107 -199 -107 -242c0 -32 7 -91 67 -91c72 0 177 62 228 239c2 5 4 10 12 10c7 0 11 -5 11 -9c0 -1 0 -3 -6 -28c-5 -20 -12 -62 -12 -98\nc0 -80 28 -114 81 -114c110 0 199 148 199 258c0 20 -4 52 -12 75z"
            },
            "&#x3de;": {
                x: 756,
                d: "M519 270l-382 -254c-11 -7 -31 -21 -55 -21c-3 0 -32 1 -32 26c0 15 8 20 25 32l66 44c35 22 35 23 40 26c28 19 98 170 98 224c0 3 0 12 -3 18c-68 0 -115 -23 -123 -41c-4 -7 -6 -12 -14 -12s-12 6 -12 12c0 32 65 108 155 108c28 0 28 -21 28 -45\nc0 -74 -45 -178 -74 -227l383 255c12 8 31 21 55 21c10 0 32 -5 32 -26c0 -10 -6 -16 -7 -18c-5 -5 -61 -42 -99 -68c-31 -21 -32 -22 -52 -52c-49 -78 -72 -172 -72 -188c0 -7 2 -13 4 -18c68 1 113 21 124 41c3 8 5 12 13 12c4 0 12 -2 12 -12c0 -34 -67 -108 -155 -108\nc-16 0 -29 4 -29 45c0 26 10 111 74 226z"
            },
            "&#x3dc;": {
                x: 764,
                d: "M230 251l-78 -309c-5 -18 -6 -19 -17 -22c-14 -3 -32 -3 -36 -3c-7 0 -49 0 -49 12c0 4 1 10 2 14l164 654c16 7 28 8 54 8h390c12 0 28 0 44 -4c13 -3 4 -3 10 -6c-6 -3 3 -3 -10 -6c-16 -4 -32 -4 -44 -4h-347l-78 -314h182c12 0 28 0 44 -4c13 -3 4 -3 10 -6\nc-6 -3 3 -3 -10 -6c-16 -4 -32 -4 -44 -4h-187z"
            },
            "&#x3f5;": {
                x: 496,
                d: "M163 228c-65 -34 -90 -85 -90 -119c0 -71 82 -87 144 -87c119 0 145 40 156 59c4 6 6 9 11 9s9 -4 9 -9c0 -14 -53 -102 -185 -102c-96 0 -158 55 -158 124c0 49 34 103 92 136c-17 12 -41 35 -41 72c0 74 106 141 218 141c69 0 127 -38 127 -56c0 -12 -13 -25 -25 -25\nc-7 0 -10 3 -16 7c-42 31 -78 31 -95 31c-89 0 -187 -36 -187 -98c0 -22 12 -45 43 -60c35 15 65 18 90 18c51 0 70 -7 70 -25c0 -29 -52 -29 -78 -29c-19 0 -53 0 -85 13zM194 240c20 -5 36 -5 54 -5c36 0 38 0 55 8c-16 4 -21 6 -49 6c-7 0 -33 0 -60 -9z"
            },
            "&#x3f1;": {
                x: 518,
                d: "M101 102c-9 -37 -24 -108 -24 -154c0 -59 23 -69 134 -69c97 0 141 0 141 -47c0 -26 -8 -26 -14 -26s-10 3 -10 10c-1 0 0 3 -1 5c-4 8 -25 8 -46 8c-34 0 -61 -4 -96 -4c-135 0 -135 63 -135 97c0 51 15 133 55 291c38 148 149 228 229 228c69 0 134 -53 134 -160\nc0 -149 -126 -291 -242 -291c-55 0 -106 34 -125 112zM227 10c46 0 100 46 132 119c17 42 44 141 44 192c0 59 -25 100 -70 100c-49 0 -100 -49 -127 -103c-36 -70 -53 -168 -53 -203c0 -49 20 -105 74 -105z"
            },
            "&#x3f9;": {
                x: 470,
                d: "M240 25l-97 55c-30 17 -93 52 -93 135c0 112 123 226 270 226c40 0 100 -9 100 -30c0 -6 -6 -15 -15 -15c-2 0 -4 0 -11 4c-24 11 -48 21 -79 21c-108 0 -216 -83 -216 -176c0 -63 38 -84 94 -116l56 -31c10 -6 38 -21 48 -28c11 -6 41 -27 41 -71\nc0 -46 -41 -106 -101 -106c-16 0 -58 8 -58 22c0 5 5 9 10 9c2 0 8 -2 10 -3c17 -7 35 -8 38 -8c34 0 48 34 48 55c0 31 -20 42 -45 57z"
            },
            "&#x211c;": {
                x: 768,
                d: "M577 701l134 -241c1 -1 7 -13 7 -15c0 -5 -2 -7 -5 -10c-2 -1 -111 -65 -167 -83c1 -14 5 -94 7 -114c1 -31 6 -83 6 -112c0 -11 -4 -14 -4 -33c0 -27 12 -60 46 -82c45 37 81 66 86 66c3 0 19 -6 19 -14c0 -4 -1 -5 -13 -15l-72 -56c-19 -14 -21 -14 -34 -14\nc-67 0 -116 55 -116 115c0 19 4 26 4 33l-13 219h-115v-73c0 -203 -79 -294 -150 -294c-35 0 -86 20 -118 107c-3 -2 -15 -6 -18 -6c-5 0 -11 6 -11 11c0 8 15 14 21 15c-2 3 -4 9 -4 13c0 5 7 13 13 13c7 0 12 -11 13 -17c4 2 17 7 21 7c3 0 11 -5 11 -11\nc0 -9 -20 -16 -23 -16c21 -63 58 -94 95 -94c65 0 65 184 65 193v295c0 117 -40 195 -92 195s-81 -53 -81 -96c0 -22 11 -37 39 -62c68 -63 70 -101 70 -131c0 -23 0 -151 -81 -151c-49 0 -58 60 -58 78c0 4 0 15 12 15s13 -8 13 -16c1 -18 6 -55 33 -55c43 0 49 60 49 119\nc0 37 0 53 -56 109c-28 29 -51 54 -51 92c0 57 44 120 112 120c76 0 125 -73 140 -102c52 85 174 113 243 113c14 0 15 -1 23 -15zM634 427l-133 240c-1 1 -7 11 -9 11c-1 0 -130 -18 -165 -114c11 -31 20 -70 20 -139v-49h145c29 0 50 0 142 51z"
            },
            "&#x2135;": {
                x: 600,
                d: "M465 112l-283 323c-32 -68 -37 -149 -37 -188c0 -31 3 -45 44 -94c23 -28 46 -56 46 -94c0 -57 -75 -59 -102 -59h-56c-18 0 -27 0 -27 11s7 11 17 11c71 2 71 28 71 39c0 21 -9 46 -25 75c-26 49 -35 72 -35 105c0 66 29 138 81 220l-59 68c-50 61 -50 81 -50 100\nc0 52 21 64 26 64c4 0 9 -3 10 -10c1 -18 4 -50 49 -101l278 -317c4 37 5 45 21 134c12 67 14 94 16 123c-57 55 -67 76 -67 107c0 52 21 64 26 64c6 0 8 -5 10 -10c7 -18 53 -98 77 -119c46 -42 54 -56 54 -90c0 -52 -21 -64 -26 -64c-9 0 -10 9 -10 13\nc-3 36 -31 67 -34 70l-39 -260l59 -68c50 -61 50 -81 50 -100c0 -52 -21 -64 -26 -64c-4 0 -9 3 -10 10c-1 18 -4 50 -49 101z"
            },
            "&#x2111;": {
                x: 738,
                d: "M282 336c0 -11 -11 -11 -17 -11c-92 0 -215 67 -215 190c0 106 96 190 217 190c42 0 184 -10 273 -176c16 -29 22 -32 36 -32c2 0 73 0 81 47c1 9 2 14 15 14c6 0 16 0 16 -13c0 -38 -44 -70 -114 -70c-86 0 -134 61 -179 118c-43 54 -75 90 -132 90\nc-91 0 -182 -68 -182 -168c0 -59 40 -159 191 -168c4 -1 10 -4 10 -11zM77 183h40c70 0 108 -29 181 -98c4 -3 32 -29 50 -42c32 -23 60 -31 66 -31c18 0 121 33 121 126c0 21 -3 37 -40 79c-30 34 -53 59 -53 99c0 63 58 115 130 115c71 0 116 -48 116 -91\nc0 -9 -3 -14 -16 -14s-14 7 -15 13c-9 70 -82 70 -83 70c-37 0 -48 -47 -48 -90c0 -23 3 -40 42 -84c37 -40 51 -61 51 -97c0 -51 -29 -91 -64 -115c-49 -34 -91 -34 -143 -34c-80 0 -102 0 -208 97c-78 70 -110 73 -145 75c-9 1 -9 9 -9 11c0 11 9 11 27 11z"
            },
            "&#x2127;": {
                x: 733,
                d: "M653 662l27 -110c1 -4 3 -11 3 -15c0 -10 -8 -12 -13 -12s-9 2 -11 8c-2 3 -2 5 -6 22c-10 40 -14 51 -19 58c-5 9 -48 9 -59 9h-82c13 -57 40 -100 90 -174c47 -71 88 -140 88 -219c0 -137 -133 -251 -305 -251c-169 0 -304 112 -304 251c0 79 41 148 88 219\nc51 75 77 118 90 174h-82c-17 0 -54 0 -60 -10c-4 -8 -8 -18 -18 -59c-7 -27 -8 -28 -17 -28c-5 0 -13 2 -13 12c0 5 2 11 3 15l27 109c5 21 6 22 31 22h130c24 0 27 0 27 -20c0 -72 -33 -158 -55 -216c-26 -68 -54 -145 -54 -219c0 -151 105 -228 208 -228\nc98 0 207 74 207 228c0 74 -28 149 -55 221c-21 56 -54 143 -54 214c0 20 3 20 28 20h130c24 0 25 -1 30 -21z"
            },
            "&#x2136;": {
                x: 778,
                d: "M666 604v-500h40c11 -1 22 -7 22 -20c0 -10 -4 -14 -15 -24l-68 -68c-12 -12 -13 -12 -36 -12h-524c-17 0 -35 0 -35 20c0 8 1 9 17 25l67 68c12 11 13 11 37 11h455v449c0 18 0 25 -10 32c-14 11 -21 11 -61 11h-324c-79 0 -119 0 -119 121c0 34 0 46 20 46\nc14 0 17 -11 19 -16c12 -33 36 -33 57 -33h367c53 0 91 -15 91 -110z"
            },
            "&#x2137;": {
                x: 507,
                d: "M208 714h170c79 0 79 -53 79 -59c0 -12 -7 -46 -49 -57c2 -34 1 -64 4 -98c29 -351 29 -353 29 -425c0 -49 0 -58 -5 -75c-2 -7 -14 -42 -35 -42c-17 0 -20 15 -23 31c-4 22 -5 35 -7 49c-67 -66 -39 -40 -53 -52c-6 -5 -7 -6 -29 -6h-204c-17 0 -35 0 -35 20\nc0 8 1 9 17 25l67 68c12 11 13 11 37 11h197v492h-165c-79 0 -91 43 -91 121c0 34 0 46 20 46c14 0 17 -11 19 -16c12 -33 36 -33 57 -33z"
            },
            "&#x2138;": {
                x: 668,
                d: "M529 596h-388c-79 0 -91 43 -91 121c0 34 0 46 20 46c14 0 17 -11 19 -16c12 -33 36 -33 57 -33h393c79 0 79 -53 79 -59c0 -12 -7 -46 -49 -57c2 -34 1 -64 4 -98c29 -351 29 -353 29 -425c0 -49 0 -58 -5 -75c-2 -7 -14 -42 -35 -42c-18 0 -20 16 -27 54\nc-6 41 -6 72 -6 78v506z"
            },
            "&#xf0;": {
                x: 571,
                d: "M284 6c82 0 147 73 147 216c1 7 1 17 1 24c0 88 -50 189 -154 189c-21 0 -78 0 -119 -75c-23 -41 -23 -88 -23 -140c0 -60 0 -106 30 -151c31 -45 69 -63 118 -63zM320 615l-198 -84c-15 -7 -17 -7 -29 -7c-28 0 -28 5 -28 28v46c0 21 0 29 28 29s28 -10 28 -18v-48\nl171 74c-80 52 -164 55 -193 56c-9 0 -15 1 -15 13s6 12 20 12c58 0 137 -13 230 -63l109 47c17 7 23 10 35 10c28 0 28 -7 28 -29v-46c0 -22 0 -28 -28 -28s-28 8 -28 20v45c-29 -13 -58 -26 -87 -38c59 -41 94 -90 113 -123c45 -77 45 -145 45 -172\nc0 -269 -119 -361 -238 -361c-90 0 -136 42 -169 75c-64 66 -64 139 -64 166c0 67 16 120 75 178c48 47 99 60 152 60c106 0 147 -93 157 -117l1 1c0 164 -65 233 -115 274z"
            },
            "&#x210f;": {
                x: 591,
                d: "M242 519l-38 -153c57 69 117 76 148 76c69 0 115 -35 115 -108c0 -56 -45 -176 -62 -220c-6 -17 -17 -45 -17 -70c0 -21 6 -33 25 -33c27 0 72 22 101 124c5 15 5 18 15 18c3 0 12 0 12 -10c0 -6 -35 -154 -131 -154c-45 0 -82 31 -82 83c0 19 5 33 11 49\nc18 46 64 168 64 230c0 33 -10 69 -54 69c-100 0 -154 -105 -162 -122c-1 -3 -8 -34 -13 -50c-3 -15 -13 -53 -16 -68l-23 -89c-6 -25 -17 -70 -19 -75c-4 -13 -18 -27 -37 -27c-12 0 -29 6 -29 28c0 6 0 8 4 23l114 456c-9 -3 -69 -22 -74 -22c-1 0 -12 0 -12 12\nc0 9 7 11 17 15c16 5 55 16 77 23c12 49 26 108 26 111c0 10 -2 17 -50 17c-14 0 -24 0 -24 11c0 19 11 20 19 21c24 2 103 10 122 10c8 0 14 -3 14 -12l-34 -135l220 69c11 3 13 3 14 3c7 0 12 -5 12 -12c0 -9 -7 -11 -19 -15z"
            },
            "&#x2141;": {
                x: 636,
                d: "M224 269h-134v-206c0 -24 0 -33 53 -40c36 -5 83 -5 97 -5c47 0 142 0 225 97c61 72 81 155 81 227c0 166 -116 323 -287 323c-47 0 -107 -11 -160 -40c-8 -4 -15 -8 -21 -8c-11 0 -20 10 -20 20c0 14 11 19 30 28c59 28 116 40 171 40c192 0 327 -172 327 -364\nc0 -115 -49 -212 -104 -267c-96 -96 -188 -96 -242 -96c-190 0 -190 40 -190 81v215c0 32 4 35 35 35h139c17 0 36 0 36 -20s-19 -20 -36 -20z"
            },
            "&#x210e;": {
                x: 591,
                d: "M250 550l-46 -184c57 69 117 76 148 76c69 0 115 -35 115 -108c0 -56 -45 -176 -62 -220c-6 -17 -17 -45 -17 -70c0 -21 6 -33 25 -33c27 0 72 22 101 124c5 15 5 18 15 18c3 0 12 0 12 -10c0 -6 -35 -154 -131 -154c-45 0 -82 31 -82 83c0 19 5 33 11 49\nc18 46 64 168 64 230c0 33 -10 69 -54 69c-100 0 -154 -105 -162 -122c-1 -3 -8 -34 -13 -50c-3 -15 -13 -53 -16 -68l-23 -89c-6 -25 -17 -70 -19 -75c-4 -13 -18 -27 -37 -27c-12 0 -29 6 -29 28c0 6 0 8 4 23l128 510h-53c-19 0 -28 0 -28 12c0 13 11 13 31 13h56\nc5 20 14 53 14 60c0 10 -2 17 -50 17c-14 0 -24 0 -24 11c0 19 11 20 19 21c24 2 103 10 122 10c8 0 14 -3 14 -12c0 -2 -9 -37 -14 -55c-6 -25 -6 -27 -13 -52h185c19 0 31 0 31 -13c0 -12 -11 -12 -28 -12h-194z"
            },
            "&#x2202;": {
                x: 615,
                d: "M466 334h1c10 37 29 111 29 175c0 112 -56 182 -144 182c-27 0 -103 -7 -144 -81c13 0 44 0 44 -31c0 -17 -15 -49 -50 -49c-28 0 -34 22 -34 31c0 27 43 154 188 154c128 0 209 -111 209 -255c0 -137 -101 -481 -337 -481c-136 0 -178 115 -178 176\nc0 159 144 302 285 302c90 0 125 -67 131 -123zM231 6c154 0 219 224 219 298c0 54 -21 133 -112 133c-104 0 -161 -101 -174 -131c-15 -37 -43 -140 -43 -184c0 -42 16 -116 110 -116z"
            },
            "&#x2118;": {
                x: 630,
                d: "M139 121c13 -18 47 -62 61 -79c34 -46 43 -58 43 -94c0 -77 -64 -163 -130 -163c-47 0 -63 36 -63 77c0 31 14 116 49 216c-31 41 -42 56 -42 108c0 128 90 266 161 266c11 0 11 -7 11 -8c0 -10 -6 -11 -12 -12c-69 -14 -117 -143 -117 -218c0 -14 0 -46 24 -73\nc50 97 92 146 130 180c70 65 138 88 199 88c85 0 127 -73 127 -148c0 -136 -115 -271 -225 -271c-45 0 -88 27 -88 82c0 10 0 41 25 41c9 0 17 -6 17 -17c0 -9 -6 -19 -20 -22c0 -55 46 -64 66 -64c47 0 91 43 116 88c32 58 52 151 52 194c0 55 -22 97 -72 97\nc-133 0 -250 -126 -312 -268zM114 57c-14 -40 -42 -148 -42 -194c0 -38 13 -58 41 -58c42 0 86 57 86 115c0 14 -1 26 -12 42z"
            },
            "&#x214c;": {
                x: 618,
                d: "M368 463h147c18 0 33 0 33 -18c0 -14 -13 -16 -20 -17c-26 -4 -59 -23 -118 -73c-13 -11 -67 -60 -72 -65c7 -10 175 -224 200 -248c4 -5 6 -5 14 -7c3 -1 16 -4 16 -17c0 -18 -16 -18 -34 -18h-177c-18 0 -33 0 -33 18c0 16 17 17 31 18c9 1 4 3 12 4\nc-6 17 -6 19 -26 42c-21 28 -76 96 -97 123l-11 -9v-99c0 -19 1 -57 28 -62c4 -1 17 -3 17 -17c0 -18 -15 -18 -33 -18h-162c-18 0 -33 0 -33 18c0 9 7 15 15 17c14 2 31 6 31 79v457c0 34 -2 75 -28 79c-3 0 -18 3 -18 17c0 18 15 18 33 18h116c30 0 34 -1 34 -36v-403\nc22 20 77 68 98 88c22 19 41 37 41 66c0 2 0 25 -19 28c-4 0 -19 3 -19 17c0 18 16 18 34 18zM407 398l39 30h-44c4 -9 5 -21 5 -30zM131 573v-461c0 -28 -2 -55 -12 -77h90c-11 23 -11 48 -11 60v555h-79c10 -22 12 -49 12 -77zM270 228l115 -144c17 -25 17 -34 17 -49h93\nl-184 231z"
            },
            "&#x2132;": {
                x: 543,
                d: "M493 659v-624c0 -32 -4 -35 -35 -35h-373c-17 0 -35 0 -35 20s18 20 35 20h368v287h-218c-17 0 -35 0 -35 20s18 20 35 20h218v291c0 17 0 36 20 36s20 -18 20 -35z"
            },
            "&#x2201;": {
                x: 488,
                d: "M438 218v-96c0 -114 -119 -144 -194 -144c-74 0 -194 29 -194 144v581c0 114 119 144 194 144c74 0 194 -29 194 -144v-96c0 -13 0 -51 -40 -51s-40 41 -40 56v84c0 22 0 40 -38 57c-27 11 -55 14 -76 14c-23 0 -54 -3 -81 -16c-33 -17 -33 -33 -33 -55v-567\nc0 -22 0 -40 38 -57c27 -11 55 -14 76 -14c23 0 54 3 81 16c33 17 33 33 33 55v84c0 15 0 56 40 56s40 -38 40 -51z"
            },
            "&#x2113;": {
                x: 481,
                d: "M129 170v19c0 122 52 261 56 271c14 38 95 244 188 244c43 0 58 -35 58 -75c0 -177 -185 -368 -244 -429c-7 -8 -7 -50 -7 -60c0 -54 7 -132 61 -132c40 0 84 35 119 69c4 4 19 19 23 19c5 0 10 -6 10 -11c0 -9 -48 -50 -61 -60c-16 -12 -52 -37 -92 -37\nc-78 0 -103 87 -109 154c-8 -9 -65 -64 -70 -64c-7 0 -11 7 -11 11s16 19 79 81zM189 232c57 60 97 110 123 147c87 126 97 216 97 251c0 22 -2 54 -36 54c-20 0 -37 -17 -54 -44c-84 -140 -129 -402 -130 -408z"
            },
            "&#x24c8;": {
                x: 986,
                d: "M936 267c0 -246 -201 -443 -443 -443c-241 0 -443 197 -443 443c0 243 196 443 443 443s443 -201 443 -443zM493 -136c222 0 403 181 403 403c0 220 -178 403 -403 403s-403 -183 -403 -403c0 -222 181 -403 403 -403zM501 226c-92 21 -112 25 -142 55\nc-12 12 -44 45 -44 101c0 78 66 146 157 146c41 0 87 -12 125 -49c1 1 27 38 29 40c4 6 6 9 12 9c10 0 10 -7 10 -26v-132c0 -21 0 -26 -13 -26c-11 0 -11 6 -13 13c-4 32 -22 149 -149 149c-67 0 -107 -49 -107 -95c0 -30 16 -69 66 -89c6 -2 41 -10 62 -15\nc85 -20 101 -23 138 -61c0 -1 39 -41 39 -104c0 -79 -62 -156 -159 -156c-65 0 -117 23 -146 49c-1 -1 -27 -38 -29 -40c-4 -6 -6 -9 -12 -9c-10 0 -10 7 -10 26v131c0 21 0 27 13 27c3 0 11 -1 12 -9c1 -27 4 -76 49 -111c43 -35 97 -39 123 -39c69 0 108 54 108 104\nc0 44 -27 71 -32 75c-23 21 -33 23 -87 36z"
            },
            "(": {
                x: 316,
                d: "M266 -194c0 -4 -2 -6 -7 -6c-12 0 -94 73 -142 177c-60 129 -67 251 -67 323c0 96 14 204 64 315c49 110 134 184 145 184c4 0 7 -2 7 -5s-2 -5 -2 -6c-49 -50 -170 -177 -170 -489s123 -440 169 -487c3 -3 3 -5 3 -6z"
            },
            ")": {
                x: 316,
                d: "M266 299c0 -96 -14 -204 -64 -315c-49 -110 -134 -184 -145 -184c-3 0 -7 1 -7 6c0 2 1 3 2 5c49 50 170 177 170 489c0 311 -122 438 -170 488c-1 2 -2 3 -2 5c0 5 4 6 7 6c12 0 94 -73 142 -177c60 -129 67 -251 67 -323z"
            },
            "&#x393;": {
                x: 591,
                d: "M517 681l24 -221h-18c-15 125 -23 195 -175 195h-110c-40 0 -42 -5 -42 -39v-544c0 -36 2 -46 78 -46h22v-26c-41 2 -89 2 -130 2c-31 0 -87 0 -116 -2v26c69 0 80 0 80 45v539c0 45 -11 45 -80 45v26h467z"
            },
            "&#x394;": {
                x: 797,
                d: "M419 690l323 -673c1 -2 5 -11 5 -12c0 -4 -1 -5 -19 -5h-659c-18 0 -19 1 -19 5c0 1 4 10 5 12l324 674c7 14 8 16 20 16c8 0 12 0 20 -17zM374 631l-278 -575h555z"
            },
            "&#x395;": {
                x: 656,
                d: "M606 253l-36 -253h-520v26c69 0 80 0 80 45v539c0 45 -11 45 -80 45v26h506l24 -221h-18c-14 133 -32 195 -187 195h-137c-40 0 -42 -5 -42 -39v-249h94c94 0 103 34 103 117h18v-260h-18c0 83 -9 117 -103 117h-94v-276c0 -34 2 -39 42 -39h139c176 0 189 80 211 227h18\nz"
            },
            "&#x396;": {
                x: 575,
                d: "M520 663l-394 -635h174c191 0 198 102 207 233h18l-14 -261h-439c-20 0 -22 0 -22 13c0 7 0 8 7 19l387 625h-165c-162 0 -192 -81 -198 -195h-18l10 221h425c21 0 22 -1 22 -20z"
            },
            "&#x397;": {
                x: 690,
                d: "M560 612v-541c0 -45 11 -45 80 -45v-26c-29 2 -82 2 -113 2s-84 0 -113 -2v26c69 0 80 0 80 45v271h-298v-271c0 -45 11 -45 80 -45v-26c-29 2 -82 2 -113 2s-84 0 -113 -2v26c69 0 80 0 80 45v541c0 45 -11 45 -80 45v26c29 -2 82 -2 113 -2s84 0 113 2v-26\nc-69 0 -80 0 -80 -45v-244h298v244c0 45 -11 45 -80 45v26c29 -2 82 -2 113 -2s84 0 113 2v-26c-69 0 -80 0 -80 -45z"
            },
            "&#x398;": {
                x: 727,
                d: "M677 340c0 -200 -143 -356 -314 -356c-167 0 -313 153 -313 356s144 359 314 359c166 0 313 -154 313 -359zM364 1c110 0 240 105 240 339c0 235 -128 343 -241 343c-109 0 -240 -105 -240 -343c0 -231 127 -339 241 -339zM551 404v-124h-18v34h-339v-34h-18v124h18v-34\nh339v34h18z"
            },
            "&#x399;": {
                x: 334,
                d: "M200 612v-541c0 -45 12 -45 84 -45v-26c-32 2 -83 2 -117 2s-85 0 -117 -2v26c72 0 84 0 84 45v541c0 45 -12 45 -84 45v26c32 -2 83 -2 117 -2s85 0 117 2v-26c-72 0 -84 0 -84 -45z"
            },
            "&#x39a;": {
                x: 734,
                d: "M368 419l223 -341c30 -46 45 -52 93 -52v-26c-23 2 -64 2 -88 2c-33 0 -79 0 -111 -2v26c13 0 41 0 41 26c0 10 -7 23 -13 33l-189 290l-128 -127v-177c0 -45 11 -45 80 -45v-26c-29 2 -82 2 -113 2s-84 0 -113 -2v26c69 0 80 0 80 45v541c0 45 -11 45 -80 45v26\nc29 -2 82 -2 113 -2s84 0 113 2v-26c-69 0 -80 0 -80 -45v-339l332 333c4 6 8 17 8 24s-4 25 -30 27v26c26 -2 73 -2 100 -2c20 0 45 1 65 2v-26c-56 -2 -94 -30 -130 -65z"
            },
            "&#x39b;": {
                x: 694,
                d: "M363 690l207 -629c12 -35 30 -35 74 -35v-26c-23 2 -69 2 -94 2c-31 0 -82 0 -111 -2v26c21 0 63 0 63 28c0 3 0 5 -5 19l-174 528l-166 -505c-3 -10 -5 -16 -5 -23s3 -45 56 -47v-26c-24 2 -63 2 -88 2c-18 0 -53 -1 -70 -2v26c33 1 66 13 84 66l197 598\nc5 16 6 17 16 17s11 -1 16 -17z"
            },
            "&#x39c;": {
                x: 843,
                d: "M206 667l216 -586l216 586c6 15 7 16 28 16h127v-26c-69 0 -80 0 -80 -45v-541c0 -45 11 -45 80 -45v-26c-27 2 -82 2 -111 2s-83 0 -110 -2v26c69 0 80 0 80 45v587h-1l-237 -642c-4 -10 -6 -16 -14 -16s-10 6 -14 16l-235 637h-1v-555c0 -25 0 -72 80 -72v-26\nc-23 2 -65 2 -90 2s-67 0 -90 -2v26c80 0 80 47 80 72v514c0 45 -11 45 -80 45v26h128c21 0 22 -1 28 -16z"
            },
            "&#x39d;": {
                x: 690,
                d: "M204 671l336 -549v463c0 25 0 72 -80 72v26c23 -2 65 -2 90 -2s67 0 90 2v-26c-80 0 -80 -47 -80 -72v-563c0 -19 0 -22 -10 -22c-5 0 -8 0 -15 12l-371 607c-7 10 -7 12 -14 18v-539c0 -25 0 -72 80 -72v-26c-23 2 -65 2 -90 2s-67 0 -90 -2v26c80 0 80 47 80 72v553\nc-3 1 -21 6 -61 6h-19v26h127c19 0 20 -1 27 -12z"
            },
            "&#x39e;": {
                x: 648,
                d: "M586 680l1 -19c1 -29 4 -88 6 -119h-18c-1 19 -3 60 -10 76c-6 10 -51 10 -75 10h-332c-10 0 -67 0 -73 -9c-9 -12 -11 -59 -12 -77h-18l1 19c1 29 4 88 6 119h524zM57 0l-7 149h18c2 -40 4 -70 10 -84c6 -13 43 -13 79 -13h334c26 0 72 0 78 12c7 14 8 40 11 85h18\nl-7 -149h-534zM506 420v-132h-18v40h-328v-40h-18v132h18v-40h328v40h18z"
            },
            "&#x39f;": {
                x: 727,
                d: "M677 340c0 -200 -143 -356 -314 -356c-167 0 -313 153 -313 356s144 359 314 359c166 0 313 -154 313 -359zM364 2c110 0 237 110 237 351c0 233 -132 328 -238 328c-101 0 -237 -92 -237 -328c0 -237 124 -351 238 -351z"
            },
            "&#x3a0;": {
                x: 690,
                d: "M560 610v-539c0 -45 11 -45 80 -45v-26c-29 2 -82 2 -113 2s-84 0 -113 -2v26c69 0 80 0 80 45v584h-298v-584c0 -45 11 -45 80 -45v-26c-29 2 -82 2 -113 2s-84 0 -113 -2v26c69 0 80 0 80 45v539c0 45 -11 45 -80 45v26h590v-26c-69 0 -80 0 -80 -45z"
            },
            "&#x3a1;": {
                x: 629,
                d: "M196 321v-250c0 -45 11 -45 80 -45v-26c-29 2 -82 2 -113 2s-84 0 -113 -2v26c69 0 80 0 80 45v541c0 45 -11 45 -80 45v26h306c131 0 223 -88 223 -183s-94 -179 -223 -179h-160zM194 342h143c122 0 166 67 166 158c0 83 -37 157 -166 157h-101c-40 0 -42 -5 -42 -39\nv-276z"
            },
            "&#x3a3;": {
                x: 675,
                d: "M327 329l-231 -295h276c162 0 219 33 235 195h18l-24 -229h-529c-15 0 -21 0 -21 7c0 2 0 3 7 12l223 286l-231 356c0 21 1 22 22 22h529l24 -221h-18c-17 159 -67 195 -233 195h-242l197 -303c1 -2 5 -8 5 -12c0 -3 0 -5 -7 -13z"
            },
            "&#x3a4;": {
                x: 711,
                d: "M644 680l17 -221h-18c-13 166 -28 195 -181 195c-18 0 -47 0 -55 -1c-18 -4 -18 -16 -18 -38v-542c0 -36 3 -47 86 -47h28v-26c-48 1 -98 2 -147 2s-99 -1 -147 -2v26h28c83 0 86 11 86 47v542c0 23 0 35 -19 38c-8 1 -37 1 -55 1c-154 0 -168 -29 -181 -195h-18l17 221\nh577z"
            },
            "&#x3a5;": {
                x: 727,
                d: "M397 353v-281c0 -36 2 -46 78 -46h22v-26c-42 2 -91 2 -133 2s-91 0 -133 -2v26h22c76 0 78 10 78 46v281c0 60 -10 289 -160 289c-32 0 -91 -17 -100 -91c-1 -8 -1 -11 -10 -11c-10 0 -11 3 -11 13c0 52 34 146 122 146c152 0 184 -193 192 -245h1c7 51 38 245 190 245\nc87 0 122 -93 122 -146c0 -10 -1 -13 -11 -13c-9 0 -9 3 -10 11c-9 73 -68 91 -100 91c-54 0 -98 -30 -126 -95c-27 -65 -33 -150 -33 -194z"
            },
            "&#x3a6;": {
                x: 675,
                d: "M368 129v-57c0 -36 2 -46 78 -46h22v-26c-41 2 -89 2 -131 2s-91 0 -132 -2v26h22c76 0 78 10 78 46v57c-153 14 -255 110 -255 213c0 99 98 198 255 212v57c0 36 -2 46 -78 46h-22v26c41 -2 90 -2 132 -2s90 0 131 2v-26h-22c-76 0 -78 -10 -78 -46v-57\nc158 -14 257 -112 257 -213c0 -97 -95 -198 -257 -212zM305 146v391c-87 -9 -181 -59 -181 -196c0 -133 91 -185 181 -195zM368 537v-391c75 7 183 49 183 196c0 143 -104 188 -183 195z"
            },
            "&#x3a7;": {
                x: 766,
                d: "M402 379l220 -320c20 -28 30 -33 94 -33v-26c-24 2 -74 2 -100 2c-33 0 -82 0 -114 -2v26c35 2 44 19 44 27c0 3 0 6 -8 17l-174 254l-160 -232c-5 -7 -10 -14 -10 -27c0 -16 9 -36 40 -39v-26c-25 2 -72 2 -99 2c-24 0 -62 0 -85 -2v26c19 0 84 1 127 63l174 253\nl-193 282c-22 31 -40 33 -95 33v26c24 -2 74 -2 100 -2c33 0 82 0 114 2v-26c-33 -1 -44 -18 -44 -27c0 -3 1 -6 8 -17l148 -216l132 191c7 10 12 18 12 30c0 16 -8 36 -40 39v26c25 -2 66 -2 99 -2c24 0 62 0 85 2v-26c-82 -1 -112 -44 -127 -65z"
            },
            "&#x3a8;": {
                x: 726,
                d: "M393 611v-464c118 25 146 148 147 254c1 103 22 155 88 155h26c17 0 22 0 22 -8c0 -6 -2 -6 -12 -8c-51 -10 -61 -75 -61 -121c0 -57 -2 -267 -210 -289v-58c0 -36 2 -46 78 -46h22v-26c-41 2 -89 2 -131 2s-91 0 -132 -2v26h22c76 0 78 10 78 46v58\nc-174 20 -207 173 -208 266c-1 134 -39 140 -67 145c-5 0 -5 6 -5 7c0 8 5 8 21 8h26c62 0 87 -49 88 -144c0 -52 2 -236 145 -265v464c0 36 -2 46 -78 46h-22v26c41 -2 90 -2 132 -2s90 0 131 2v-26h-22c-76 0 -78 -10 -78 -46z"
            },
            "&#x3a9;": {
                x: 695,
                d: "M645 146l-29 -146h-151c-21 0 -22 0 -22 16c0 70 36 161 52 202c37 91 64 160 64 237c0 146 -103 228 -212 228c-104 0 -211 -79 -211 -228c0 -78 30 -154 62 -232c17 -43 54 -136 54 -207c0 -15 -1 -16 -22 -16h-151l-29 146h18c10 -46 12 -59 18 -75\nc5 -14 8 -22 65 -22h81c-11 58 -35 101 -79 172c-49 79 -93 150 -93 233c0 134 125 245 288 245c159 0 287 -110 287 -245c0 -83 -43 -152 -95 -237c-42 -67 -66 -110 -77 -168h81c58 0 61 8 66 23c5 16 8 28 17 74h18z"
            },
            "&#x391;": {
                x: 746,
                d: "M390 691l222 -628c13 -37 31 -37 84 -37v-26c-24 2 -74 2 -100 2c-31 0 -83 0 -112 -2v26c19 0 62 0 62 27c0 4 0 6 -5 18l-60 170h-262l-53 -149c-2 -6 -4 -11 -4 -20c0 -12 7 -44 54 -46v-26c-24 2 -64 2 -89 2c-19 0 -59 0 -77 -2v26c35 0 75 11 94 65l212 600\nc5 13 7 16 17 16s12 -3 17 -16zM350 611l-122 -344h244z"
            },
            "&#x392;": {
                x: 655,
                d: "M50 683h318c129 0 211 -85 211 -168c0 -76 -67 -140 -163 -159c107 -7 189 -84 189 -174c0 -91 -83 -182 -211 -182h-344v26c69 0 80 0 80 45v541c0 45 -11 45 -80 45v26zM193 363h144c108 0 169 76 169 152c0 62 -44 142 -143 142h-128c-40 0 -42 -5 -42 -39v-255z\nM235 26h130c109 0 166 84 166 157s-50 164 -153 164h-185v-282c0 -34 2 -39 42 -39z"
            },
            "#": {
                x: 779,
                d: "M489 143l-80 -316c-2 -8 -5 -22 -19 -22c-10 0 -18 8 -18 18c0 3 1 8 4 18l77 302h-181l-80 -316c-2 -8 -5 -22 -19 -22c-10 0 -18 8 -18 18c0 3 1 8 4 18l77 302h-152c-17 0 -34 0 -34 18c0 17 15 17 30 17h166l34 143h-200c-15 0 -30 0 -30 17c0 18 17 18 34 18h206\nl80 316c2 8 5 22 19 22c10 0 18 -8 18 -18c0 -3 -1 -8 -4 -18l-77 -302h181l80 316c2 8 5 22 19 22c10 0 18 -8 18 -18c0 -3 -1 -8 -4 -18l-77 -302h152c17 0 34 0 34 -18c0 -17 -15 -17 -30 -17h-166l-34 -143h200c15 0 30 0 30 -17c0 -18 -17 -18 -34 -18h-206zM282 178\nh181l34 143h-181z"
            },
            "!": {
                x: 182,
                d: "M132 670l-31 -483c-1 -13 -1 -18 -10 -18s-9 5 -10 18l-31 483c0 29 24 40 41 40s41 -11 41 -40zM132 41c0 -23 -19 -41 -41 -41c-23 0 -41 19 -41 41c0 23 19 41 41 41c23 0 41 -19 41 -41z"
            },
            $: {
                x: 466,
                d: "M246 676v-285c45 -11 84 -21 121 -64c45 -53 49 -110 49 -136c0 -99 -66 -189 -170 -198v-48h-26v47c-109 6 -170 86 -170 186c0 33 19 41 36 41c13 0 36 -8 36 -36c0 -25 -19 -36 -36 -36c-5 0 -12 1 -16 3c18 -101 95 -129 150 -132v312c-58 16 -85 23 -122 62\nc-16 18 -48 60 -48 126c0 95 71 175 170 184v47h26v-47c120 -7 170 -90 170 -172c0 -29 -15 -41 -36 -41c-17 0 -36 11 -36 36c0 28 23 36 36 36c5 0 11 -1 15 -3c-19 93 -94 115 -149 118zM220 399v276c-85 -8 -130 -77 -130 -137c0 -37 15 -69 39 -95\nc28 -29 64 -38 91 -44zM246 323v-304c81 8 130 81 130 151c0 53 -26 90 -40 106c-29 31 -58 40 -90 47z"
            },
            "%": {
                x: 779,
                d: "M729 146c0 -117 -57 -201 -124 -201c-69 0 -137 79 -137 201s69 201 137 201c67 0 124 -85 124 -201zM606 -39c55 0 105 79 105 185s-50 185 -105 185c-26 0 -91 -23 -91 -185c0 -161 65 -185 91 -185zM642 714l-476 -753c-7 -11 -10 -16 -19 -16s-18 7 -18 18\nc0 3 0 5 9 19l432 684l-1 1c-25 -17 -71 -41 -135 -41c-26 0 -86 5 -147 44c3 -6 24 -53 24 -122c0 -117 -57 -201 -124 -201c-69 0 -137 79 -137 201s69 201 137 201c34 0 53 -19 66 -31c19 -18 80 -76 180 -76c41 0 121 9 179 92c7 8 11 15 21 15c8 0 17 -6 17 -18\nc0 -5 -1 -6 -8 -17zM188 363c55 0 105 79 105 185s-50 185 -105 185c-26 0 -91 -23 -91 -185c0 -161 65 -185 91 -185z"
            },
            "&#x26;": {
                x: 745,
                d: "M84 214l124 144c-30 87 -38 150 -38 201c0 97 61 151 120 151c65 0 78 -82 78 -122c0 -32 -11 -77 -114 -201c49 -129 148 -256 163 -275c35 39 70 98 85 124c43 72 66 111 66 127c0 27 -27 41 -60 41v26c29 -2 76 -2 106 -2c20 0 62 0 81 2v-26c-50 -1 -73 -18 -87 -30\nc-15 -13 -43 -63 -62 -96c-66 -119 -98 -158 -117 -181c24 -28 73 -87 132 -87c44 0 109 33 111 111h18c-2 -83 -62 -137 -133 -137c-80 0 -135 52 -162 77c-20 -19 -80 -77 -174 -77s-171 62 -171 144c0 33 12 61 34 86zM248 406c84 98 102 148 102 183\nc0 15 -3 105 -59 105c-32 0 -73 -28 -73 -116c0 -54 11 -120 30 -172zM215 341l-62 -71c-26 -30 -39 -71 -39 -112c0 -65 35 -148 116 -148c49 0 103 19 152 65c-47 54 -118 155 -167 266z"
            },
            "&#x2220;": {
                x: 709,
                d: "M648 653l-534 -613h510c17 0 35 0 35 -20s-18 -20 -35 -20h-538c-17 0 -36 0 -36 20c0 8 3 11 12 21l555 639c10 11 16 14 22 14c11 0 20 -9 20 -20c0 -4 0 -9 -11 -21z"
            },
            "&#x2032;": {
                x: 333,
                d: "M274 475l-177 -414c-6 -13 -7 -16 -13 -16c-8 0 -34 7 -34 18c0 1 4 12 4 14l119 437c7 26 25 45 53 45c31 0 57 -24 57 -53c0 -10 -4 -19 -9 -31z"
            },
            "&#x2035;": {
                x: 333,
                d: "M160 511l119 -434c0 -2 4 -13 4 -14c0 -8 -4 -9 -14 -13c-16 -5 -18 -5 -20 -5c-6 0 -7 2 -13 16l-179 417c-7 16 -7 28 -7 29c0 30 28 52 56 52c34 0 47 -23 54 -48z"
            },
            "&#x2605;": {
                x: 944,
                d: "M881 369l-239 -174l92 -281c1 -3 3 -10 3 -14c0 -1 0 -11 -11 -11c-5 0 -12 5 -15 9l-239 173l-239 -173c-11 -9 -13 -9 -15 -9c-11 0 -11 10 -11 11s4 13 4 14l91 281l-240 174c-9 7 -12 9 -12 15c0 11 9 11 28 11h289l90 278c4 13 7 20 15 20s9 -4 14 -18l91 -280h289\nc19 0 28 0 28 -11c0 -4 -1 -5 -13 -15z"
            },
            "&#x25c6;": {
                x: 654,
                d: "M595 272l-246 -388c-6 -9 -11 -17 -22 -17s-16 8 -22 17l-248 390c0 1 -7 11 -7 17c0 5 0 7 9 20l246 388c6 9 11 17 22 17s16 -8 22 -17l248 -390c0 -1 7 -11 7 -17c0 -5 0 -7 -9 -20z"
            },
            "&#x25a0;": {
                x: 765,
                d: "M715 652v-617c0 -32 -3 -35 -36 -35h-593c-33 0 -36 4 -36 36v616c0 32 4 35 35 35h595c32 0 35 -4 35 -35z"
            },
            "&#x25b2;": {
                x: 653,
                d: "M348 556l250 -540c4 -8 5 -12 5 -16c0 -20 -19 -20 -35 -20h-483c-16 0 -35 0 -35 20c0 4 0 6 6 18l248 538c8 18 16 20 23 20c12 0 15 -8 21 -20z"
            },
            "&#x25bc;": {
                x: 653,
                d: "M597 538l-255 -552c-4 -4 -9 -6 -15 -6c-7 0 -15 2 -23 20l-249 540c-4 8 -5 12 -5 16c0 20 19 20 35 20h483c16 0 35 0 35 -20c0 -4 0 -6 -6 -18z"
            },
            "&#x22a4;": {
                x: 768,
                d: "M404 628v-592c0 -18 0 -36 -20 -36s-20 21 -20 36v592h-278c-15 0 -36 0 -36 20s21 20 36 20h597c17 0 35 0 35 -20s-18 -20 -35 -20h-279z"
            },
            "&#x22a5;": {
                x: 768,
                d: "M404 632v-592h279c17 0 35 0 35 -20s-18 -20 -35 -20h-597c-15 0 -36 0 -36 20s21 20 36 20h278v592c0 15 0 36 20 36s20 -18 20 -36z"
            },
            "&#x2663;": {
                x: 822,
                d: "M424 -130h-26c-17 0 -35 0 -35 20c0 2 0 4 3 12c15 53 24 102 25 168h-50c-4 -83 -94 -92 -120 -92c-97 0 -171 89 -171 196c0 97 52 194 146 194c36 0 68 -14 92 -43l22 46c-50 40 -76 100 -76 161c0 104 76 195 177 195s177 -90 177 -195c0 -61 -25 -120 -76 -161\nl22 -46c24 29 56 43 92 43c94 0 146 -97 146 -194c0 -107 -75 -196 -171 -196c-27 0 -116 9 -120 92h-50c0 -50 7 -105 24 -164c4 -11 4 -13 4 -16c0 -20 -18 -20 -35 -20z"
            },
            "&#x2660;": {
                x: 768,
                d: "M397 -130h-26c-17 0 -35 0 -35 20c0 2 0 4 3 12c15 53 24 102 25 168h-50c-3 -82 -99 -92 -130 -92c-118 0 -134 150 -134 225c0 114 65 195 182 299c76 67 114 162 132 208c2 6 6 17 20 17c8 0 16 -5 18 -13c25 -61 59 -146 135 -214c113 -100 181 -180 181 -297\nc0 -65 -12 -225 -134 -225c-18 0 -127 4 -130 92h-50c0 -50 7 -105 24 -164c4 -11 4 -13 4 -16c0 -20 -18 -20 -35 -20z"
            },
            "&#x2662;": {
                x: 768,
                d: "M55 295l24 23c137 128 230 282 282 387c8 16 11 22 23 22s17 -9 19 -14c89 -182 206 -324 298 -406c13 -13 17 -16 17 -25s-4 -13 -14 -22c-137 -123 -238 -282 -297 -401c-8 -16 -11 -22 -23 -22s-17 9 -19 14c-56 114 -154 274 -298 406c-11 11 -17 16 -17 25\nc0 5 3 10 5 13zM384 -98c31 57 100 184 214 308c22 25 47 52 70 72c-22 20 -46 45 -67 69c-108 116 -173 229 -217 311c-31 -57 -100 -184 -214 -308c-22 -25 -47 -52 -70 -72c22 -20 46 -45 67 -69c108 -116 173 -229 217 -311z"
            },
            "&#x2661;": {
                x: 768,
                d: "M384 605c21 44 76 111 167 111c115 0 167 -98 167 -227c0 -150 -109 -255 -218 -359c-31 -29 -62 -61 -96 -146c-2 -6 -6 -17 -20 -17c-13 0 -17 9 -22 22c-18 46 -37 79 -65 112c-8 10 -41 41 -63 61c-146 139 -184 234 -184 327c0 125 50 227 167 227\nc91 0 147 -68 167 -111zM384 39c32 67 61 94 125 154c154 145 169 236 169 296c0 13 0 78 -27 130c-23 43 -62 57 -100 57c-46 0 -124 -27 -146 -139c-2 -9 -5 -23 -21 -23c-2 0 -16 1 -20 20c-26 130 -119 142 -147 142c-113 0 -127 -123 -127 -187c0 -85 35 -176 208 -331\nc43 -39 73 -91 86 -119z"
            },
            "&#x2203;": {
                x: 545,
                d: "M495 658v-622c0 -33 -3 -36 -35 -36h-375c-17 0 -35 0 -35 20s18 20 35 20h370v287h-356c-17 0 -35 0 -35 20s18 20 35 20h356v287h-370c-17 0 -35 0 -35 20s18 20 35 20h375c32 0 35 -3 35 -36z"
            },
            "&#x2204;": {
                x: 543,
                d: "M398 819l-28 -125h88c32 0 35 -4 35 -35v-624c0 -32 -4 -35 -35 -35h-244c-15 -70 -17 -77 -32 -142c-2 -10 -5 -24 -21 -24c-5 0 -20 3 -20 20c0 4 10 48 15 72c11 45 6 28 17 74h-88c-17 0 -35 0 -35 20s18 20 35 20h97l64 287h-147c-17 0 -35 0 -35 20s18 20 35 20\nh156l65 287h-235c-17 0 -35 0 -35 20s18 20 35 20h244l31 140c2 10 6 26 22 26c5 0 20 -3 20 -20c0 -4 -1 -7 -4 -21zM297 367h156v287h-92zM223 40h230v287h-165z"
            },
            "&#x266d;": {
                x: 372,
                d: "M72 724v-310c27 30 74 45 117 45c71 0 133 -57 133 -138c0 -127 -95 -222 -178 -286c-16 -12 -72 -56 -83 -56s-11 6 -11 26v718c0 18 0 27 11 27s11 -6 11 -26zM72 344v-332c48 43 176 159 176 309c0 23 -5 105 -70 105c-21 0 -54 -5 -77 -21c-29 -19 -29 -38 -29 -61z\n"
            },
            "&#x266e;": {
                x: 320,
                d: "M270 479v-668c0 -18 0 -27 -12 -27c-10 0 -10 7 -10 26v185l-171 -63c-14 -6 -16 -6 -17 -6c-10 0 -10 7 -10 26v746c0 17 0 27 12 27c10 0 10 -9 10 -26v-263l171 63c14 6 16 6 17 6c10 0 10 -7 10 -26zM72 5l176 65v356l-176 -65v-356z"
            },
            "&#x266f;": {
                x: 372,
                d: "M256 -17l-140 -41v-131c0 -19 0 -26 -12 -26c-10 0 -10 9 -10 26v125c-7 -2 -29 -10 -34 -10c-10 0 -10 7 -10 26v21c0 22 0 24 13 29c2 0 27 7 31 8v357c-7 -2 -29 -10 -34 -10c-10 0 -10 7 -10 26v21c0 22 0 24 13 29c2 0 27 7 31 8v200c0 19 0 27 12 27\nc10 0 10 -7 10 -26v-194l140 41v200c0 19 0 26 12 26c10 0 10 -9 10 -26v-194c7 2 29 10 34 10c10 0 10 -7 10 -26v-22c0 -22 -1 -23 -13 -28c-26 -8 -5 -1 -31 -8v-357c7 2 29 10 34 10c10 0 10 -7 10 -26v-22c0 -22 -1 -23 -13 -28c-26 -8 -5 -1 -31 -8v-131\nc0 -19 0 -27 -12 -27c-10 0 -10 7 -10 26v125zM256 414l-140 -41v-356l140 41v356z"
            },
            "&#x2200;": {
                x: 656,
                d: "M601 656l-251 -657c-4 -10 -8 -21 -22 -21c-13 0 -18 9 -23 23l-249 655c-6 14 -6 16 -6 18c0 10 10 20 20 20c13 0 18 -9 23 -23l83 -220h304l83 220c4 11 10 23 23 23c12 0 20 -10 20 -20c0 -5 0 -7 -5 -18zM192 411l136 -356l136 356h-272z"
            },
            "&#x221e;": {
                x: 989,
                d: "M503 271c49 80 132 171 247 171s189 -111 189 -226c0 -117 -76 -227 -193 -227c-51 0 -107 18 -163 63c-31 25 -43 40 -97 108c-49 -80 -132 -171 -247 -171s-189 111 -189 226c0 117 76 227 193 227c51 0 107 -18 163 -63c31 -25 43 -40 97 -108zM529 237\nc61 -79 84 -110 109 -137c21 -21 66 -63 124 -63c88 0 155 81 155 179c0 90 -54 194 -160 194c-113 0 -187 -101 -228 -173zM460 194c-61 79 -84 110 -109 137c-21 21 -66 63 -124 63c-88 0 -155 -81 -155 -179c0 -90 54 -194 160 -194c113 0 187 101 228 173z"
            },
            "&#x2221;": {
                x: 709,
                d: "M648 673l-325 -385c62 -75 96 -153 106 -248h195c17 0 35 0 35 -20s-18 -20 -35 -20h-193c-2 -17 -15 -20 -20 -20c-7 0 -18 4 -20 20h-305c-17 0 -36 0 -36 20c0 7 2 9 12 21l555 659c10 12 17 14 22 14c11 0 20 -9 20 -20c0 -4 0 -8 -11 -21zM113 40h276\nc-4 42 -23 137 -93 216z"
            },
            "&#x2207;": {
                x: 838,
                d: "M783 661l-337 -675c-7 -13 -10 -19 -27 -19s-20 6 -27 19l-337 675c-2 3 -5 10 -5 14c0 7 1 8 24 8h690c23 0 24 -1 24 -8c0 -4 -3 -11 -5 -14zM174 611l275 -550l274 550h-549z"
            },
            "&#xac;": {
                x: 656,
                d: "M606 320v-195c0 -18 0 -36 -20 -36s-20 18 -20 36v191h-481c-17 0 -35 0 -35 20s18 20 35 20h486c32 0 35 -3 35 -36z"
            },
            "&#x2222;": {
                x: 709,
                d: "M638 508l-118 -58c28 -63 42 -131 42 -200s-14 -137 -42 -200l116 -58c16 -7 23 -11 23 -23s-9 -20 -20 -20c-3 0 -5 0 -18 7l-551 272c-12 6 -20 10 -20 22s9 17 19 22l552 272c13 7 15 7 18 7c11 0 20 -8 20 -20s-9 -17 -21 -23zM115 250l369 -182c26 61 38 120 38 182\nc0 57 -10 117 -38 182z"
            },
            "&#x221a;": {
                x: 880,
                d: "M366 -95l421 874c8 16 15 21 23 21c12 0 20 -10 20 -20c0 -2 0 -6 -7 -20l-454 -940c-7 -14 -10 -20 -25 -20c-9 0 -14 0 -21 16l-197 433c-11 -8 -26 -19 -32 -24c-11 -8 -28 -22 -34 -22c-7 0 -10 6 -10 11c0 3 0 6 13 16l95 72c11 8 16 8 17 8c3 0 8 0 15 -16z"
            },
            "&#x25b3;": {
                x: 870,
                d: "M457 697l357 -661c4 -8 6 -11 6 -16c0 -20 -18 -20 -35 -20h-699c-15 0 -36 0 -36 20c0 4 0 6 8 19l355 657c8 16 11 20 22 20c12 0 15 -7 22 -19zM435 654l-332 -614h664z"
            },
            "&#x25bd;": {
                x: 870,
                d: "M812 461l-355 -657c-8 -16 -11 -20 -22 -20c-12 0 -15 7 -22 19l-356 660c-6 12 -7 12 -7 17c0 20 21 20 36 20h699c17 0 35 0 35 -20c0 -4 0 -6 -8 -19zM103 460l331 -613h2l331 613h-664z"
            },
            "&#x2205;": {
                x: 765,
                d: "M701 543l-73 -64c-4 -4 -10 -9 -11 -10c19 -27 59 -85 59 -178c0 -155 -124 -293 -294 -293c-124 0 -198 75 -207 85l-78 -68c-18 -17 -20 -17 -27 -17c-12 0 -20 8 -20 20c0 9 2 11 22 29c59 51 60 52 76 67c-20 29 -59 84 -59 178c0 166 134 293 294 293\nc112 0 186 -63 207 -85l77 67c18 16 21 18 28 18c2 0 20 -1 20 -20c0 -4 0 -10 -14 -22zM178 140l381 333c-10 11 -70 72 -177 72c-138 0 -253 -110 -253 -254c0 -22 0 -82 49 -151zM586 443l-380 -333c10 -11 70 -72 177 -72c138 0 253 110 253 254c0 57 -17 107 -50 151z\n"
            },
            "&#xf8;": {
                x: 505,
                d: "M391 752l-23 -84c87 -83 87 -249 87 -323c0 -72 -3 -155 -29 -225c-18 -48 -62 -142 -174 -142c-27 0 -57 8 -81 21c-11 -40 -1 -5 -13 -45c-6 -22 -9 -32 -24 -32c-12 0 -20 9 -20 20c0 3 0 5 23 82c-87 78 -87 258 -87 321c0 93 5 183 44 264c37 77 102 107 159 107\nc37 0 69 -15 81 -21c11 40 1 5 13 45c6 21 9 32 24 32c12 0 20 -9 20 -20zM150 70l175 596c-26 22 -52 28 -72 28c-45 0 -103 -30 -122 -123c-14 -64 -14 -133 -14 -214c0 -95 0 -218 33 -287zM355 626l-174 -597c24 -21 48 -29 71 -29c53 0 106 38 124 143\nc12 67 12 141 12 214c0 95 0 211 -33 269z"
            },
            "&#x25c7;": {
                x: 654,
                d: "M595 272l-246 -388c-6 -9 -11 -17 -22 -17s-16 8 -22 17l-248 390c0 1 -7 11 -7 17c0 5 0 7 9 20l246 388c6 9 11 17 22 17s16 -8 22 -17l248 -390c0 -1 7 -11 7 -17c0 -5 0 -7 -9 -20zM94 291l232 -366h2l232 367l-233 367z"
            },
            "&#x25c0;": {
                x: 711,
                d: "M70 272l553 261c8 4 14 6 18 6c6 0 11 -2 14 -6s5 -8 5 -12s1 -10 1 -17v-509c0 -7 -1 -13 -1 -17s-2 -8 -5 -12s-8 -6 -14 -6c-3 0 -9 2 -18 7l-553 260c-13 6 -20 13 -20 22s7 17 20 23z"
            },
            "&#x25b8;": {
                x: 711,
                d: "M641 227l-553 -260c-9 -5 -15 -7 -18 -7c-6 0 -11 2 -14 6s-6 8 -6 12v17v508v18s3 8 6 12s8 6 15 6c3 0 8 -2 17 -7l553 -260c13 -6 20 -14 20 -23s-7 -16 -20 -22z"
            },
            "[": {
                x: 229,
                d: "M179 -249h-129v998h129v-35h-94v-928h94v-35z"
            },
            "]": {
                x: 229,
                d: "M179 749v-998h-129v35h94v928h-94v35h129z"
            },
            "{": {
                x: 455,
                d: "M261 617v-240c0 -58 -36 -104 -118 -127c28 -8 57 -18 78 -39c32 -32 40 -49 40 -95v-210c0 -36 0 -38 3 -47c13 -51 62 -83 125 -87c10 -1 16 -1 16 -11c0 -11 -8 -11 -19 -11c-39 0 -105 11 -144 41c-48 38 -48 70 -48 112v198c0 36 0 38 -3 49c-11 49 -59 85 -125 89\nc-10 1 -16 1 -16 11c0 6 4 10 9 11c22 1 60 3 95 32c40 33 40 65 40 104v228c1 74 92 125 192 125c11 0 19 0 19 -11c0 -6 -4 -10 -9 -11c-109 -7 -135 -71 -135 -111z"
            },
            "}": {
                x: 455,
                d: "M194 -117v240c0 58 36 104 118 127c-28 8 -57 18 -78 39c-32 32 -40 49 -40 95v210c0 36 0 38 -3 47c-13 51 -62 83 -125 87c-10 1 -16 1 -16 11c0 11 11 11 20 11c34 0 103 -10 143 -41c48 -38 48 -70 48 -112v-198c0 -36 0 -38 3 -49c11 -49 59 -85 125 -89\nc10 -1 16 -1 16 -11c0 -6 -4 -10 -9 -11c-22 -1 -60 -3 -95 -32c-40 -33 -40 -65 -40 -104v-228c-1 -76 -94 -125 -191 -125c-9 0 -20 0 -20 11c0 6 4 10 9 11c109 7 135 71 135 111z"
            },
            "&#x3008;": {
                x: 323,
                d: "M268 712l-177 -462l177 -464c5 -11 5 -13 5 -16c0 -11 -9 -20 -20 -20c-14 0 -19 13 -23 24l-175 459c-5 12 -5 14 -5 17c0 6 3 12 5 18l175 459c7 18 14 23 23 23c11 0 20 -9 20 -20c0 -5 0 -7 -5 -18z"
            },
            "&#x3009;": {
                x: 323,
                d: "M268 233l-177 -463c-4 -10 -8 -20 -21 -20c-11 0 -20 9 -20 20c0 5 0 7 5 18l177 462l-177 464c-5 11 -5 13 -5 16c0 11 9 20 20 20s17 -6 23 -23l175 -460c5 -12 5 -14 5 -17s0 -5 -5 -17z"
            },
            "&#x3f0;": {
                x: 756,
                d: "M519 270l-382 -254c-11 -7 -31 -21 -55 -21c-3 0 -32 1 -32 26c0 15 8 20 25 32l66 44c35 22 35 23 40 26c28 19 98 170 98 224c0 3 0 12 -3 18c-68 0 -115 -23 -123 -41c-4 -7 -6 -12 -14 -12s-12 6 -12 12c0 32 65 108 155 108c28 0 28 -21 28 -45\nc0 -74 -45 -178 -74 -227l383 255c12 8 31 21 55 21c10 0 32 -5 32 -26c0 -10 -6 -16 -7 -18c-5 -5 -61 -42 -99 -68c-31 -21 -32 -22 -52 -52c-49 -78 -72 -172 -72 -188c0 -7 2 -13 4 -18c68 1 113 21 124 41c3 8 5 12 13 12c4 0 12 -2 12 -12c0 -34 -67 -108 -155 -108\nc-16 0 -29 4 -29 45c0 26 10 111 74 226z"
            },
            ",": {
                x: 208,
                d: "M158 -4c0 -35 -7 -67 -20 -98s-27 -54 -40 -69s-21 -22 -25 -22c-7 0 -10 3 -10 10c0 3 4 8 11 16c43 48 64 102 64 163c0 13 -1 19 -2 19s-3 -1 -4 -2c-9 -9 -20 -13 -33 -13c-16 0 -29 5 -37 15s-12 21 -12 34c0 12 4 23 13 33s20 15 35 15c20 0 35 -9 45 -27\ns15 -43 15 -74z"
            },
            ".": {
                x: 197,
                d: "M147 48c0 -13 -5 -24 -14 -34s-21 -14 -35 -14c-13 0 -24 5 -34 14s-14 21 -14 35c0 13 5 23 14 33s21 15 35 15c13 0 23 -5 33 -14s15 -21 15 -35z"
            },
            "/": {
                x: 481,
                d: "M425 713l-335 -941c-5 -15 -12 -22 -22 -22c-5 0 -8 2 -12 6s-6 8 -6 13c0 1 1 4 3 9l3 9l335 941c5 15 13 22 22 22c5 0 9 -2 13 -6s5 -8 5 -13c0 -1 -1 -4 -3 -9z"
            },
            ":": {
                x: 182,
                d: "M132 389c0 -12 -4 -21 -12 -29s-18 -12 -29 -12s-21 4 -29 12s-12 17 -12 29s4 21 12 29s18 12 29 12s21 -4 29 -12s12 -17 12 -29zM132 41c0 -12 -4 -21 -12 -29s-18 -12 -29 -12s-21 4 -29 12s-12 17 -12 29s4 21 12 29s18 12 29 12s21 -4 29 -12s12 -17 12 -29z"
            },
            ";": {
                x: 185,
                d: "M132 389c0 -12 -4 -21 -12 -29s-18 -12 -29 -12s-21 4 -29 12s-12 17 -12 29s4 21 12 29s18 12 29 12s21 -4 29 -12s12 -17 12 -29zM119 -11v21c-7 -7 -16 -10 -28 -10c-11 0 -21 4 -29 11s-12 17 -12 30s4 23 12 30s18 11 29 11c29 0 44 -30 44 -91\nc0 -35 -6 -67 -17 -98s-22 -53 -32 -67s-16 -20 -19 -20c-5 0 -8 3 -8 9c0 2 2 5 6 10c36 51 54 105 54 164z"
            },
            "?": {
                x: 439,
                d: "M215 225v-35c0 -7 -1 -12 -1 -14s-1 -4 -2 -5s-3 -2 -6 -2c-4 0 -7 1 -8 3s-1 9 -1 18v39c0 94 30 173 90 238c8 9 15 16 19 22s8 17 12 31s7 30 7 49c0 11 0 20 -1 27s-3 18 -7 30s-9 22 -17 30s-20 15 -36 21s-34 9 -56 9c-29 0 -57 -7 -82 -22s-42 -37 -52 -66\nc4 1 8 2 12 2c9 0 18 -4 25 -10s11 -15 11 -26c0 -13 -4 -22 -12 -28s-16 -8 -24 -8c-3 0 -7 0 -11 1s-9 4 -15 11s-10 16 -10 27c0 35 14 67 44 94s69 41 116 41c57 0 101 -13 132 -38s47 -58 47 -100c0 -43 -18 -78 -54 -105c-37 -29 -67 -65 -88 -105s-32 -83 -32 -129z\nM247 41c0 -11 -4 -21 -12 -29s-18 -12 -29 -12s-21 4 -29 12s-12 18 -12 29s4 21 12 29s18 12 29 12s21 -4 29 -12s12 -18 12 -29z"
            },
            "\\": {
                x: 489,
                d: "M396 -226l-341 940c-3 8 -5 13 -5 16c0 5 2 10 6 14s9 6 14 6c6 0 10 -2 13 -5s6 -10 9 -19l342 -940c3 -8 5 -13 5 -16c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-9 0 -17 8 -23 24z"
            },
            "&#x22ee;": {
                x: 216,
                d: "M107 674c31 0 59 -28 59 -58c0 -31 -28 -58 -60 -58c-29 0 -56 28 -56 58s27 58 57 58zM107 329c31 0 59 -28 59 -58c0 -31 -28 -58 -60 -58c-29 0 -56 28 -56 58s27 58 57 58zM107 -26c31 0 59 -28 59 -58c0 -31 -28 -58 -60 -58c-29 0 -56 28 -56 58s27 58 57 58z"
            },
            "&#x22ef;": {
                x: 882,
                d: "M440 329c31 0 59 -28 59 -58c0 -31 -28 -58 -60 -58c-29 0 -56 28 -56 58s27 58 57 58zM107 329c31 0 59 -28 59 -58c0 -31 -28 -58 -60 -58c-29 0 -56 28 -56 58s27 58 57 58zM773 329c31 0 59 -28 59 -58c0 -31 -28 -58 -60 -58c-29 0 -56 28 -56 58s27 58 57 58z"
            },
            "&#x22f0;": {
                x: 685,
                d: "M383 318c22 -22 22 -62 1 -83c-22 -22 -61 -21 -83 1c-21 21 -20 60 1 81s60 22 81 1zM148 553c22 -22 22 -61 1 -83c-22 -21 -61 -21 -84 2c-20 20 -20 59 2 80c21 22 60 22 81 1zM619 82c22 -22 22 -61 0 -82c-21 -22 -60 -22 -83 1c-20 20 -20 59 1 81\nc22 21 61 21 82 0z"
            },
            "&#x2026;": {
                x: 647,
                d: "M538 111c31 0 59 -28 59 -58c0 -31 -28 -58 -60 -58c-29 0 -56 28 -56 58s27 58 57 58zM322 111c31 0 59 -28 59 -58c0 -31 -28 -58 -60 -58c-29 0 -56 28 -56 58s27 58 57 58zM107 111c31 0 59 -28 59 -58c0 -31 -28 -58 -60 -58c-29 0 -56 28 -56 58s27 58 57 58z"
            },
            "@": {
                x: 727,
                d: "M551 457v-242c0 -27 4 -68 36 -68c70 0 72 108 72 183c0 95 -25 190 -89 260c-64 67 -135 96 -207 96c-159 0 -295 -149 -295 -339c0 -178 123 -339 300 -339c130 0 227 46 263 63c5 3 7 3 24 3c20 0 21 -1 21 -6c0 -8 -144 -76 -307 -76c-186 0 -319 164 -319 355\nc0 204 146 355 314 355c139 0 313 -117 313 -368c0 -107 -11 -203 -94 -203c-39 0 -85 26 -86 82c-18 -31 -65 -82 -134 -82c-95 0 -187 88 -187 216s91 216 187 216c38 0 90 -16 132 -78c5 -7 6 -8 7 -8h28c20 0 21 -1 21 -20zM498 271v152c0 33 -20 62 -44 85\nc-16 15 -48 39 -90 39c-74 0 -135 -89 -135 -200s61 -200 135 -200c43 0 91 26 122 83c12 21 12 22 12 41z"
            },
            "&#x22;": {
                x: 425,
                d: "M148 611c-4 -6 -18 -23 -45 -23c-36 0 -53 27 -53 53s18 53 53 53c46 0 68 -50 68 -105c0 -118 -85 -194 -96 -194c-6 0 -11 5 -11 11c0 2 0 4 5 9c19 18 80 74 80 175c0 6 0 15 -1 21zM352 611c-4 -6 -18 -23 -45 -23c-36 0 -53 27 -53 53s18 53 53 53\nc46 0 68 -50 68 -105c0 -118 -85 -194 -96 -194c-6 0 -11 5 -11 11c0 2 0 4 5 9c19 18 80 74 80 175c0 6 0 15 -1 21z"
            },
            "'": {
                x: 216,
                d: "M160 669l-88 -179c-8 3 -12 4 -22 9l44 184c5 21 8 32 21 39c17 8 41 -1 49 -16s-1 -30 -4 -37z"
            },
            "|": {
                x: 140,
                d: "M90 714v-928c0 -18 0 -36 -20 -36s-20 18 -20 36v928c0 18 0 36 20 36s20 -18 20 -36z"
            },
            "^": {
                x: 346,
                d: "M173 694l123 -136l-12 -12l-111 108l-111 -108l-12 12z"
            },
            "`": {
                x: 221,
                d: "M73 478c4 6 18 23 45 23c36 0 53 -27 53 -53s-18 -53 -53 -53c-46 0 -68 50 -68 105c0 118 85 194 96 194c6 0 11 -5 11 -11c0 -2 0 -4 -5 -9c-19 -18 -80 -74 -80 -175c0 -6 0 -15 1 -21z"
            },
            "&#x201c;": {
                x: 425,
                d: "M277 478c4 6 18 23 45 23c36 0 53 -27 53 -53s-18 -53 -53 -53c-46 0 -68 50 -68 105c0 118 85 194 96 194c6 0 11 -5 11 -11c0 -2 0 -4 -5 -9c-19 -18 -80 -74 -80 -175c0 -6 0 -15 1 -21zM73 478c4 6 18 23 45 23c36 0 53 -27 53 -53s-18 -53 -53 -53\nc-46 0 -68 50 -68 105c0 118 85 194 96 194c6 0 11 -5 11 -11c0 -2 0 -4 -5 -9c-19 -18 -80 -74 -80 -175c0 -6 0 -15 1 -21z"
            },
            _: {
                x: 381,
                d: "M332 0h-282v44h282v-44z"
            },
            "*": {
                x: 669,
                d: "M360 427l-14 -156l112 81c25 19 27 19 34 19c14 0 27 -13 27 -28s-8 -19 -24 -27l-138 -67c79 -37 81 -37 141 -67c12 -6 21 -11 21 -26c0 -14 -13 -28 -27 -28c-7 0 -9 2 -18 7l-128 93l15 -168c0 -19 -15 -26 -27 -26c-8 0 -26 5 -26 26l15 168l-112 -81\nc-25 -19 -27 -19 -34 -19c-14 0 -27 13 -27 28s8 19 24 27l138 67l-142 68c-10 5 -20 9 -20 25c0 14 13 28 27 28c7 0 9 -2 18 -7l128 -93l-14 155v13c-3 13 10 26 26 26c22 0 25 -16 25 -30v-8z"
            },
            "+": {
                x: 927,
                d: "M481 232v-265c0 -15 0 -31 -18 -31c-17 0 -17 17 -17 31v265h-265c-15 0 -31 0 -31 18c0 17 17 17 31 17h265v265c0 15 0 31 18 31c17 0 17 -17 17 -31v-265h265c15 0 31 0 31 -18c0 -17 -17 -17 -31 -17h-265z"
            },
            "-": {
                x: 967,
                d: "M726 230h-541c-17 0 -35 0 -35 20s18 20 35 20h541c17 0 35 0 35 -20s-18 -20 -35 -20z"
            },
            "&#x2210;": {
                x: 979,
                d: "M726 605v-527c0 -40 0 -47 72 -47c25 0 31 0 31 -16c0 -15 -7 -15 -28 -15h-623c-21 0 -28 0 -28 15c0 16 7 16 26 16c77 0 77 6 77 47v527c0 40 0 47 -72 47c-25 0 -31 0 -31 16c0 15 10 15 17 15c21 0 43 -2 64 -2s43 -1 64 -1c28 0 100 3 128 3c7 0 17 0 17 -15\nc0 -16 -7 -16 -26 -16c-77 0 -77 -6 -77 -47v-574h305v574c0 40 0 47 -72 47c-25 0 -31 0 -31 16c0 15 10 15 17 15c21 0 43 -2 64 -2s43 -1 64 -1c28 0 100 3 128 3c7 0 17 0 17 -15c0 -16 -7 -16 -26 -16c-77 0 -77 -6 -77 -47z"
            },
            "&#x22bc;": {
                x: 798,
                d: "M612 676h-426c-17 0 -36 0 -36 20s18 20 35 20h428c17 0 35 0 35 -20s-19 -20 -36 -20zM421 503l220 -465c7 -13 7 -15 7 -18c0 -11 -8 -20 -20 -20s-17 9 -22 19l-207 437l-207 -436c-6 -12 -9 -20 -22 -20c-11 0 -20 9 -20 20c0 2 0 6 7 20l220 462c5 11 9 20 22 20\nc11 0 16 -7 22 -19z"
            },
            "&#x22bb;": {
                x: 798,
                d: "M641 676l-220 -462c-5 -11 -9 -20 -22 -20c-12 0 -17 9 -22 19l-221 465c-2 5 -6 12 -6 18c0 11 9 20 20 20s17 -7 22 -19l207 -437l207 436c6 12 9 20 22 20c11 0 20 -9 20 -20c0 -1 0 -6 -7 -20zM186 40h427c17 0 35 0 35 -20s-18 -20 -35 -20h-427c-17 0 -36 0 -36 20\ns19 20 36 20z"
            },
            "&#x25ef;": {
                x: 1189,
                d: "M1039 250c0 -259 -200 -466 -445 -466c-242 0 -444 205 -444 466c0 259 200 466 445 466c242 0 444 -205 444 -466zM595 -176c221 0 404 188 404 426c0 236 -181 426 -405 426c-221 0 -404 -188 -404 -426c0 -236 181 -426 405 -426z"
            },
            "&#x22a1;": {
                x: 965,
                d: "M815 652v-617c0 -32 -3 -35 -36 -35h-593c-33 0 -36 4 -36 36v616c0 32 4 35 35 35h595c32 0 35 -4 35 -35zM190 647v-607h585v607h-585zM536 344c0 -29 -24 -53 -53 -53s-53 24 -53 53s24 53 53 53s53 -24 53 -53z"
            },
            "&#x229f;": {
                x: 965,
                d: "M815 652v-617c0 -32 -3 -35 -36 -35h-593c-33 0 -36 4 -36 36v616c0 32 4 35 35 35h595c32 0 35 -4 35 -35zM190 364h585v283h-585v-283zM190 40h585v284h-585v-284z"
            },
            "&#x229e;": {
                x: 965,
                d: "M815 652v-617c0 -32 -3 -35 -36 -35h-593c-33 0 -36 4 -36 36v616c0 32 4 35 35 35h595c32 0 35 -4 35 -35zM190 364h273v283h-273v-283zM775 647h-272v-283h272v283zM190 40h273v284h-273v-284zM775 324h-272v-284h272v284z"
            },
            "&#x22a0;": {
                x: 965,
                d: "M815 652v-617c0 -32 -4 -35 -35 -35h-594c-33 0 -36 4 -36 36v616c0 32 4 35 35 35h595c32 0 35 -4 35 -35zM217 647l266 -274l265 274h-531zM190 70l264 273l-264 274v-547zM775 617l-264 -273l264 -274v547zM748 40l-266 274l-265 -274h531z"
            },
            "&#x2022;": {
                x: 689,
                d: "M539 250c0 -107 -89 -194 -194 -194c-108 0 -195 88 -195 194c0 105 87 194 195 194c105 0 194 -87 194 -194z"
            },
            "&#x2229;": {
                x: 856,
                d: "M706 380v-366c0 -18 0 -36 -20 -36s-20 18 -20 36v361c0 27 0 91 -77 140c-53 34 -116 43 -161 43c-75 0 -238 -32 -238 -182v-362c0 -18 0 -36 -20 -36s-20 18 -20 36v367c0 147 148 217 278 217c125 0 278 -66 278 -218z"
            },
            "&#x222a;": {
                x: 856,
                d: "M706 562v-367c0 -147 -148 -217 -278 -217c-125 0 -278 66 -278 218v366c0 18 0 36 20 36s20 -18 20 -36v-361c0 -27 0 -91 77 -140c53 -34 116 -43 161 -43c75 0 238 32 238 182v362c0 18 0 36 20 36s20 -18 20 -36z"
            },
            "&#x22d2;": {
                x: 854,
                d: "M550 320v-307c0 -17 0 -35 -20 -35s-20 18 -20 35v304c0 22 0 41 -23 63c-19 19 -45 24 -60 24c-17 0 -42 -5 -61 -25c-22 -22 -22 -41 -22 -62v-304c0 -17 0 -35 -20 -35s-20 18 -20 35v307c0 99 84 124 123 124c38 0 123 -24 123 -124zM704 379v-366\nc0 -17 0 -35 -20 -35s-20 18 -20 35v363c0 25 0 86 -73 136c-47 31 -108 46 -164 46c-86 0 -237 -40 -237 -182v-363c0 -17 0 -35 -20 -35s-20 18 -20 35v366c0 153 154 219 277 219s277 -65 277 -219z"
            },
            "&#x22d3;": {
                x: 854,
                d: "M550 563v-307c0 -99 -84 -124 -123 -124c-38 0 -123 24 -123 124v307c0 17 0 35 20 35s20 -18 20 -35v-304c0 -22 0 -41 23 -63c19 -19 45 -24 60 -24c17 0 42 5 61 25c22 22 22 41 22 62v304c0 17 0 35 20 35s20 -18 20 -35zM704 563v-366c0 -153 -154 -219 -277 -219\ns-277 65 -277 219v366c0 17 0 35 20 35s20 -18 20 -35v-363c0 -25 0 -86 73 -136c47 -31 108 -46 164 -46c86 0 237 40 237 182v363c0 17 0 35 20 35s20 -18 20 -35z"
            },
            "&#x22d0;": {
                x: 967,
                d: "M470 155h256c17 0 35 0 35 -20s-18 -20 -35 -20h-107c-59 0 -119 -1 -178 -1c-78 0 -137 62 -137 136c0 73 59 136 137 136c10 0 21 -1 32 -1h253c17 0 35 0 35 -20s-18 -20 -35 -20h-256c-10 0 -20 1 -29 1c-56 0 -97 -45 -97 -96s41 -96 97 -96c9 0 19 1 29 1zM459 0\nh267c17 0 35 0 35 -20s-18 -20 -35 -20h-270c-167 0 -306 126 -306 290s139 290 306 290h270c17 0 35 0 35 -20s-18 -20 -35 -20h-267c-157 0 -269 -115 -269 -250s113 -250 269 -250z"
            },
            "&#x22d1;": {
                x: 967,
                d: "M441 345h-256c-17 0 -35 0 -35 20s18 20 35 20h253c11 0 22 1 32 1c78 0 137 -62 137 -136c0 -73 -59 -136 -137 -136c-10 0 -21 1 -32 1h-253c-17 0 -35 0 -35 20s18 20 35 20h256c10 0 20 -1 29 -1c56 0 97 45 97 96s-41 96 -97 96c-9 0 -19 -1 -29 -1zM452 500h-267\nc-17 0 -35 0 -35 20s18 20 35 20h270c167 0 306 -126 306 -290s-139 -290 -306 -290h-270c-17 0 -35 0 -35 20s18 20 35 20h267c157 0 269 115 269 250s-113 250 -269 250z"
            },
            "&#xb7;": {
                x: 406,
                d: "M256 250c0 -29 -24 -53 -53 -53s-53 24 -53 53s24 53 53 53s53 -24 53 -53z"
            },
            "&#x25aa;": {
                x: 465,
                d: "M315 152v-117c0 -32 -4 -35 -35 -35h-94c-33 0 -36 4 -36 36v116c0 32 4 35 35 35h95c32 0 35 -4 35 -35z"
            },
            "&#x25e6;": {
                x: 689,
                d: "M539 250c0 -107 -89 -194 -194 -194c-108 0 -195 88 -195 194c0 105 87 194 195 194c105 0 194 -87 194 -194zM345 96c83 0 154 68 154 154s-71 154 -154 154c-86 0 -155 -70 -155 -154s68 -154 155 -154z"
            },
            "&#x229b;": {
                x: 966,
                d: "M816 250c0 -183 -149 -333 -333 -333c-183 0 -333 149 -333 333c0 183 149 333 333 333c183 0 333 -149 333 -333zM483 -58c170 0 308 138 308 308s-138 308 -308 308s-308 -138 -308 -308s138 -308 308 -308zM604 150l-109 79l5 -54c1 -20 5 -59 5 -60c0 -4 5 -51 5 -54\nc0 -18 -16 -26 -27 -26s-27 8 -27 26l15 168l-111 -80c-11 -8 -27 -20 -34 -20c-15 0 -27 14 -27 28c0 15 7 18 21 26l140 67l-141 68c-10 5 -20 10 -20 25c0 14 12 28 27 28c8 0 24 -13 36 -21l109 -79l-14 156v13c-3 13 11 26 26 26c23 0 26 -18 26 -32v-7l-14 -157\nl127 94c3 2 9 7 18 7c15 0 27 -14 27 -28c0 -15 -7 -18 -21 -26l-140 -67c63 -32 125 -60 138 -67c16 -8 23 -11 23 -26c0 -14 -12 -28 -27 -28c-8 0 -24 13 -36 21z"
            },
            "&#x229a;": {
                x: 966,
                d: "M816 250c0 -183 -149 -333 -333 -333c-183 0 -333 149 -333 333c0 183 149 333 333 333c183 0 333 -149 333 -333zM483 -58c170 0 308 138 308 308s-138 308 -308 308s-308 -138 -308 -308s138 -308 308 -308zM622 250c0 -76 -63 -139 -139 -139s-139 62 -139 139\nc0 76 63 139 139 139s139 -62 139 -139zM483 151c55 0 99 45 99 99s-45 99 -99 99c-55 0 -99 -45 -99 -99s45 -99 99 -99z"
            },
            "&#x2296;": {
                x: 967,
                d: "M817 250c0 -182 -148 -333 -334 -333c-183 0 -333 149 -333 333c0 182 148 333 334 333c183 0 333 -149 333 -333zM175 263h617c-10 176 -152 295 -309 295c-153 0 -298 -117 -308 -295zM792 238h-617c10 -179 154 -296 309 -296c151 0 298 114 308 296z"
            },
            "&#x2299;": {
                x: 967,
                d: "M817 250c0 -182 -148 -333 -334 -333c-183 0 -333 149 -333 333c0 182 148 333 334 333c183 0 333 -149 333 -333zM484 -58c168 0 308 136 308 308c0 169 -137 308 -309 308c-168 0 -308 -136 -308 -308c0 -169 137 -308 309 -308zM552 250c0 -36 -29 -69 -69 -69\nc-37 0 -68 31 -68 69c0 36 29 69 69 69c37 0 68 -31 68 -69z"
            },
            "&#x229d;": {
                x: 966,
                d: "M816 250c0 -183 -149 -333 -333 -333c-183 0 -333 149 -333 333c0 183 149 333 333 333c183 0 333 -149 333 -333zM483 -58c170 0 308 138 308 308s-138 308 -308 308s-308 -138 -308 -308s138 -308 308 -308zM346 263h274c19 0 29 0 29 -13c0 -12 -11 -12 -29 -12h-274\nc-19 0 -29 0 -29 13c0 12 11 12 29 12z"
            },
            "&#x2295;": {
                x: 967,
                d: "M817 250c0 -182 -148 -333 -334 -333c-183 0 -333 149 -333 333c0 182 148 333 334 333c183 0 333 -149 333 -333zM175 263h296v295c-156 -8 -286 -127 -296 -295zM496 558v-295h296c-10 167 -139 287 -296 295zM471 -58v296h-296c10 -171 141 -288 296 -296zM792 238\nh-296v-296c154 8 286 125 296 296z"
            },
            "&#x2297;": {
                x: 967,
                d: "M817 250c0 -182 -148 -333 -334 -333c-183 0 -333 149 -333 333c0 182 148 333 334 333c183 0 333 -149 333 -333zM285 465l199 -198l208 209c-82 77 -174 82 -209 82c-126 0 -207 -78 -207 -82c0 -2 7 -9 9 -11zM256 41l209 209l-209 209c-52 -59 -81 -132 -81 -209\nc0 -64 20 -141 81 -209zM710 459l-208 -209l209 -209c52 59 81 132 81 209c0 82 -34 158 -82 209zM682 35l-199 198l-208 -209c82 -77 174 -82 209 -82c126 0 207 78 207 82c0 2 -7 9 -9 11z"
            },
            "&#x2298;": {
                x: 967,
                d: "M817 250c0 -182 -148 -333 -334 -333c-183 0 -333 149 -333 333c0 182 148 333 334 333c183 0 333 -149 333 -333zM266 51l426 425c-82 77 -174 82 -209 82c-168 0 -308 -136 -308 -308c0 -127 79 -207 82 -207c1 0 1 1 9 8zM710 459l-435 -435c82 -77 174 -82 209 -82\nc168 0 308 136 308 308c0 82 -34 158 -82 209z"
            },
            "&#xb1;": {
                x: 967,
                d: "M504 313v-273h278c17 0 35 0 35 -20s-18 -20 -35 -20h-597c-17 0 -35 0 -35 20s18 20 35 20h279v273h-279c-17 0 -35 0 -35 20s18 20 35 20h279v279c0 16 0 34 20 34s20 -21 20 -37v-276h278c17 0 35 0 35 -20s-18 -20 -35 -20h-278z"
            },
            "&#x2213;": {
                x: 967,
                d: "M504 147v-276c0 -15 0 -37 -20 -37s-20 18 -20 34v279h-279c-17 0 -35 0 -35 20s18 20 35 20h279v273h-279c-17 0 -35 0 -35 20s18 20 35 20h597c17 0 35 0 35 -20s-18 -20 -35 -20h-278v-273h278c17 0 35 0 35 -20s-18 -20 -35 -20h-278z"
            },
            "&#x22cf;": {
                x: 891,
                d: "M446 251c21 105 54 182 101 237c73 86 170 90 173 90c17 0 21 -13 21 -20c0 -17 -14 -19 -27 -21c-94 -15 -165 -75 -209 -213c-25 -80 -38 -197 -39 -295c0 -33 0 -34 -1 -35c0 -3 -2 -6 -4 -8c-2 -3 -7 -8 -15 -8c-20 0 -20 20 -20 24c-1 117 -9 234 -47 345\nc-57 168 -168 185 -212 191c-10 2 -17 10 -17 20c0 7 4 20 21 20c13 0 111 -11 175 -91c55 -67 84 -151 100 -236z"
            },
            "&#x22ce;": {
                x: 891,
                d: "M446 305c-16 -82 -45 -168 -94 -229c-76 -96 -180 -98 -181 -98c-17 0 -21 13 -21 20c0 17 16 20 22 21c91 13 166 62 214 210c27 85 39 207 40 298c0 33 0 34 1 35c0 3 2 6 4 8c2 3 7 8 15 8c20 0 20 -20 20 -24c1 -100 8 -228 44 -335c60 -178 165 -194 214 -201\nc10 -2 17 -10 17 -20c0 -7 -4 -20 -21 -20c-2 0 -54 4 -104 32c-87 51 -140 145 -170 295z"
            },
            "&#x2020;": {
                x: 633,
                d: "M327 420c1 -20 2 -33 12 -85c11 -57 11 -68 11 -117c0 -110 -8 -244 -9 -262c-2 -48 -6 -108 -11 -154c-2 -13 -2 -18 -14 -18c-11 0 -12 6 -13 20c-8 87 -20 256 -20 414c0 48 0 60 11 118c10 52 11 63 12 84c-15 -2 -25 -3 -60 -12c-32 -9 -51 -11 -59 -11\nc-29 0 -37 20 -37 34c0 9 5 33 37 33c21 0 52 -8 73 -14c23 -7 32 -7 46 -9c0 19 -1 40 -10 99c-4 27 -13 88 -13 123c0 10 0 42 34 42c33 0 33 -33 33 -42c0 -36 -7 -81 -12 -118c-10 -60 -11 -87 -11 -104c15 2 25 3 60 12c32 9 51 11 59 11c29 0 37 -20 37 -34\nc0 -9 -5 -33 -37 -33c-21 0 -52 8 -73 14c-23 7 -32 7 -46 9z"
            },
            "&#x2021;": {
                x: 633,
                d: "M327 467c1 -23 2 -34 12 -92c9 -44 11 -71 11 -85c0 -12 -3 -40 -34 -40c-18 0 -33 11 -33 40c0 27 8 74 13 100c8 44 9 56 10 77c-15 -2 -25 -3 -60 -12c-32 -9 -51 -11 -59 -11c-29 0 -37 20 -37 34c0 9 5 33 37 33c21 0 52 -8 73 -14c23 -7 32 -7 46 -9\nc-1 23 -2 34 -12 92c-9 44 -11 71 -11 85c0 12 3 40 34 40c18 0 33 -11 33 -40c0 -27 -8 -74 -13 -100c-8 -44 -9 -56 -10 -77c15 2 25 3 60 12c32 9 51 11 59 11c29 0 37 -20 37 -34c0 -9 -5 -33 -37 -33c-21 0 -52 8 -73 14c-23 7 -32 7 -46 9zM327 12c1 -23 2 -36 13 -99\nc8 -42 10 -68 10 -78c0 -12 -3 -40 -34 -40c-18 0 -33 11 -33 40c0 13 2 38 12 89c9 53 10 68 11 88c-19 -2 -21 -2 -46 -10c-43 -11 -60 -13 -76 -13c-24 0 -34 19 -34 33s9 33 34 33c18 0 36 -3 64 -11c6 -1 36 -10 58 -12c-1 23 -2 36 -13 99c-8 42 -10 68 -10 78\nc0 30 16 40 33 40c34 0 34 -32 34 -40c0 -13 -2 -38 -12 -89c-9 -53 -10 -68 -11 -88c19 2 21 2 46 10c43 11 60 13 76 13c24 0 34 -19 34 -33s-9 -33 -34 -33c-18 0 -36 3 -64 11c-6 1 -36 10 -58 12z"
            },
            "&#x22c4;": {
                x: 776,
                d: "M410 474l203 -203c10 -10 13 -13 13 -21s-4 -12 -5 -13l-24 -25l-171 -171l-25 -24c-5 -5 -11 -5 -13 -5c-6 0 -11 4 -12 4c-2 3 -16 17 -25 25l-142 143c-18 18 -52 52 -55 54c-2 4 -4 8 -4 12c0 7 2 9 13 20l205 205c11 11 13 13 20 13c8 0 10 -2 22 -14zM388 440\nl-189 -190l190 -190l189 190z"
            },
            "&#xf7;": {
                x: 968,
                d: "M548 466c0 -32 -26 -63 -64 -63c-37 0 -64 31 -64 63s26 63 64 63c37 0 64 -31 64 -63zM548 34c0 -32 -26 -63 -64 -63c-37 0 -64 31 -64 63s26 63 64 63c37 0 64 -31 64 -63zM185 270h598c17 0 35 0 35 -20s-18 -20 -35 -20h-598c-17 0 -35 0 -35 20s18 20 35 20z"
            },
            "&#x22c7;": {
                x: 965,
                d: "M545 466c0 -34 -28 -62 -62 -62s-62 28 -62 62s28 62 62 62s62 -28 62 -62zM545 34c0 -34 -28 -62 -62 -62s-62 28 -62 62s28 62 62 62s62 -28 62 -62zM710 449l-178 -179h248c17 0 35 0 35 -20s-18 -20 -35 -20h-248l178 -179c12 -12 14 -14 14 -22\nc0 -11 -8 -20 -20 -20c-6 0 -13 4 -14 6c-13 12 -159 159 -208 207l-199 -199c-12 -12 -14 -14 -22 -14c-10 0 -20 9 -20 20c0 6 3 12 14 23l178 178h-248c-17 0 -35 0 -35 20s18 20 35 20h248l-178 179c-10 10 -14 14 -14 22c0 11 10 20 20 20c8 0 10 -2 22 -14l200 -199\nl199 199c10 10 14 14 22 14c11 0 20 -9 20 -20c0 -8 -2 -10 -14 -22z"
            },
            "&#x2214;": {
                x: 966,
                d: "M503 230v-289c0 -17 0 -35 -20 -35s-20 18 -20 35v289h-278c-17 0 -35 0 -35 20s18 20 35 20h278v289c0 17 0 35 20 35s20 -18 20 -35v-289h278c17 0 35 0 35 -20s-18 -20 -35 -20h-278zM533 716c0 -29 -24 -50 -50 -50s-50 20 -50 50c0 29 23 50 50 50s50 -21 50 -50z\n"
            },
            "&#x232d;": {
                x: 798,
                d: "M612 773h-426c-17 0 -36 0 -36 20s18 20 35 20h428c17 0 35 0 35 -20s-19 -20 -36 -20zM421 406l221 -465c2 -5 6 -12 6 -18c0 -11 -9 -20 -20 -20s-17 7 -22 19l-207 437l-207 -436c-6 -12 -9 -20 -22 -20c-11 0 -20 9 -20 20c0 1 0 6 7 20l220 462c5 11 9 20 22 20\nc12 0 17 -9 22 -19zM185 619h428c17 0 35 0 35 -20s-19 -20 -36 -20h-426c-17 0 -36 0 -36 20s18 20 35 20z"
            },
            "&#x22d7;": {
                x: 967,
                d: "M376 250c0 -37 -30 -63 -63 -63c-36 0 -64 29 -64 63s28 63 64 63c33 0 63 -26 63 -63zM188 533l553 -261c12 -6 20 -9 20 -22c0 -11 -7 -16 -19 -22l-556 -262c-7 -3 -11 -5 -16 -5c-10 0 -20 9 -20 19c0 8 3 15 20 23l524 247l-524 247c-17 8 -20 15 -20 23\nc0 10 10 19 20 19c4 0 7 -1 18 -6z"
            },
            "&#x22d6;": {
                x: 967,
                d: "M662 250c0 -34 -28 -63 -64 -63c-33 0 -63 26 -63 63s30 63 63 63c36 0 64 -29 64 -63zM741 497l-524 -247l524 -248c13 -6 20 -9 20 -21c0 -14 -12 -20 -20 -20c-4 0 -7 1 -18 6l-553 261c-12 6 -20 9 -20 22c0 11 7 16 19 22l556 262c7 3 11 5 16 5c8 0 20 -6 20 -20\nc0 -8 -4 -15 -20 -22z"
            },
            "&#x22c9;": {
                x: 783,
                d: "M619 449l-198 -199c52 -53 201 -201 207 -208c5 -6 5 -11 5 -13c0 -11 -8 -20 -20 -20c-6 0 -13 4 -14 6c-13 12 -159 159 -208 207l-199 -199c-12 -12 -14 -14 -22 -14c-20 0 -20 19 -20 36v410c0 17 0 36 20 36c8 0 10 -2 22 -14l200 -199l199 199c10 10 14 14 22 14\nc11 0 20 -9 20 -20c0 -8 -2 -10 -14 -22zM190 77l172 173l-172 172v-345z"
            },
            "&#x22ca;": {
                x: 783,
                d: "M392 278l199 199c10 10 14 14 22 14c20 0 20 -18 20 -35v-412c0 -17 0 -35 -20 -35c-6 0 -13 4 -14 6c-13 12 -159 159 -208 207l-199 -199c-12 -12 -14 -14 -22 -14c-10 0 -20 9 -20 20c0 6 3 12 14 23l198 198l-198 199c-10 10 -14 14 -14 22c0 11 10 20 20 20\nc8 0 10 -2 22 -14zM421 250l172 -172v345z"
            },
            "&#x22cb;": {
                x: 965,
                d: "M455 336l-293 316c-11 12 -12 16 -12 22c0 12 9 20 20 20c3 0 9 0 22 -14l611 -660c11 -12 12 -16 12 -22c0 -12 -9 -20 -20 -20c-4 0 -10 1 -21 13l-292 315l-291 -315c-11 -12 -17 -13 -21 -13c-11 0 -20 8 -20 20c0 6 1 10 12 22z"
            },
            "&#x22cc;": {
                x: 965,
                d: "M510 336l293 -316c11 -12 12 -16 12 -22c0 -12 -9 -20 -20 -20c-4 0 -10 1 -21 13l-292 315l-291 -315c-11 -12 -17 -13 -21 -13c-11 0 -20 8 -20 20c0 6 1 10 12 22l611 660c13 14 19 14 22 14c11 0 20 -8 20 -20c0 -6 -1 -10 -12 -22z"
            },
            "&#x2293;": {
                x: 844,
                d: "M694 562v-526c0 -18 0 -36 -20 -36s-20 21 -20 36v522h-464v-522c0 -18 0 -36 -20 -36s-20 21 -20 36v526c0 33 3 36 35 36h474c32 0 35 -3 35 -36z"
            },
            "&#x2294;": {
                x: 844,
                d: "M694 562v-526c0 -33 -3 -36 -36 -36h-472c-32 0 -36 4 -36 36v526c0 18 0 36 20 36s20 -18 20 -36v-522h464v522c0 18 0 36 20 36s20 -18 20 -36z"
            },
            "&#x2291;": {
                x: 931,
                d: "M746 596h-545v-499h544c17 0 36 0 36 -20s-18 -20 -35 -20h-550c-32 0 -35 3 -35 36v507c0 32 4 36 36 36h549c17 0 35 0 35 -20s-18 -20 -35 -20zM746 -137h-561c-17 0 -35 0 -35 20s20 20 36 20h559c15 0 36 0 36 -20s-18 -20 -35 -20z"
            },
            "&#x2292;": {
                x: 931,
                d: "M770 600v-507c0 -33 -3 -36 -35 -36h-550c-17 0 -35 0 -35 20s20 20 36 20h544v499h-545c-17 0 -35 0 -35 20s18 20 35 20h550c32 0 35 -3 35 -36zM186 -97h559c17 0 36 0 36 -20s-18 -20 -35 -20h-561c-17 0 -35 0 -35 20s21 20 36 20z"
            },
            "&#x228f;": {
                x: 967,
                d: "M726 499h-536v-499h535c17 0 36 0 36 -20s-18 -20 -35 -20h-541c-32 0 -35 4 -35 35v508c0 33 4 36 36 36h540c17 0 35 0 35 -20s-18 -20 -35 -20z"
            },
            "&#x2290;": {
                x: 951,
                d: "M801 504v-509c0 -32 -4 -35 -35 -35h-581c-17 0 -35 0 -35 20s19 20 36 20h575v499h-576c-17 0 -35 0 -35 20s18 20 35 20h581c32 0 35 -4 35 -35z"
            },
            "&#x22c6;": {
                x: 789,
                d: "M394 172l-130 -144c-9 -10 -13 -10 -15 -10c-9 0 -9 8 -9 9s0 2 7 14l96 169l-180 81c-8 4 -13 6 -13 12c0 5 4 9 10 9l202 -41l22 192c2 15 2 20 11 20c8 0 8 -5 10 -19l22 -193l202 41c6 0 10 -4 10 -9c0 -6 -2 -7 -14 -12l-179 -81l98 -171c2 -3 5 -9 5 -12\nc0 -1 0 -9 -9 -9c-4 0 -7 2 -10 5z"
            },
            "&#xd7;": {
                x: 783,
                d: "M392 278l198 198c11 11 15 15 23 15c12 0 20 -9 20 -20c0 -7 -4 -11 -5 -13c-5 -6 -20 -19 -25 -25l-183 -183c50 -51 209 -208 210 -210c3 -5 3 -9 3 -11c0 -11 -8 -20 -20 -20c-6 0 -13 5 -15 6l-207 207l-199 -199c-12 -12 -14 -14 -22 -14c-10 0 -20 9 -20 20\nc0 7 2 9 13 20l200 201l-200 201c-11 11 -13 13 -13 20c0 11 10 20 20 20c8 0 10 -2 22 -14z"
            },
            "&#x22b3;": {
                x: 967,
                d: "M741 227l-553 -260c-13 -7 -15 -7 -18 -7c-20 0 -20 18 -20 35v508c0 18 0 36 21 36c2 0 4 0 17 -7l553 -260c17 -8 20 -15 20 -23s-4 -15 -20 -22zM694 250l-504 238v-477z"
            },
            "&#x22b2;": {
                x: 967,
                d: "M170 272l553 261c4 2 13 6 18 6c20 0 20 -18 20 -35v-509c0 -17 0 -35 -20 -35c-3 0 -5 0 -18 7l-553 260c-16 7 -20 14 -20 22s3 15 20 23zM217 249l504 -238v477z"
            },
            "&#x22b5;": {
                x: 967,
                d: "M741 324l-553 -260c-13 -7 -15 -7 -18 -7c-20 0 -20 18 -20 35v508c0 18 0 36 21 36c2 0 4 0 17 -7l553 -260c17 -8 20 -15 20 -23s-4 -15 -20 -22zM694 347l-504 238v-477zM726 -137h-541c-17 0 -35 0 -35 20s19 20 36 20h539c17 0 36 0 36 -20s-18 -20 -35 -20z"
            },
            "&#x22b4;": {
                x: 967,
                d: "M170 369l553 261c4 2 13 6 18 6c20 0 20 -18 20 -35v-509c0 -17 0 -35 -20 -35c-3 0 -5 0 -18 7l-553 260c-16 7 -20 14 -20 22s3 15 20 23zM217 346l504 -238v477zM186 -97h539c17 0 36 0 36 -20s-18 -20 -35 -20h-541c-17 0 -35 0 -35 20s19 20 36 20z"
            },
            "&#x228e;": {
                x: 856,
                d: "M448 285v-142c0 -15 0 -35 -20 -35s-20 19 -20 35v142h-142c-15 0 -36 0 -36 20s21 20 36 20h142v143c0 17 0 35 20 35s20 -18 20 -35v-143h142c15 0 36 0 36 -20s-21 -20 -36 -20h-142zM706 562v-367c0 -147 -148 -217 -278 -217c-125 0 -278 66 -278 218v366\nc0 18 0 36 20 36s20 -18 20 -36v-361c0 -27 0 -91 77 -140c53 -34 116 -43 161 -43c75 0 238 32 238 182v362c0 18 0 36 20 36s20 -18 20 -36z"
            },
            "&#x2228;": {
                x: 856,
                d: "M699 558l-249 -559c-6 -14 -9 -21 -22 -21c-9 0 -15 4 -23 21l-249 563c-6 12 -6 14 -6 16c0 11 9 20 20 20c7 0 15 -2 23 -21l235 -530l235 529c7 15 13 22 23 22c11 0 20 -9 20 -20c0 -5 -1 -7 -7 -20z"
            },
            "&#x2227;": {
                x: 856,
                d: "M451 577l249 -563c6 -12 6 -14 6 -16c0 -11 -9 -20 -20 -20c-4 0 -14 0 -23 21l-235 530l-235 -530c-8 -19 -16 -21 -23 -21c-11 0 -20 9 -20 20c0 5 1 7 7 20l249 559c6 14 9 21 22 21c9 0 15 -4 23 -21z"
            },
            "&#x2240;": {
                x: 467,
                d: "M316 -69c0 -9 -10 -14 -29 -14c-44 0 -77 17 -101 51s-36 75 -36 121c0 19 3 39 9 59s12 38 19 52s17 34 32 60c34 62 51 112 51 151c0 35 -8 67 -23 97s-35 45 -60 47c-18 2 -27 7 -27 14c0 9 10 14 30 14c43 0 77 -17 101 -51s35 -74 35 -121c0 -19 -3 -39 -9 -59\ns-12 -38 -19 -52s-17 -34 -32 -60c-34 -62 -51 -112 -51 -151c0 -35 7 -67 21 -95c17 -31 39 -48 68 -49c14 -2 21 -7 21 -14z"
            },
            "&#x3c;": {
                x: 941,
                d: "M772 498l-553 -248l553 -248c1 0 1 0 2 -1c11 -5 17 -12 17 -20c0 -7 -3 -12 -7 -15s-8 -5 -13 -5c-3 0 -9 2 -17 5l-585 262c-13 6 -19 13 -19 22c0 8 7 15 20 22l600 268c14 -1 21 -8 21 -21c0 -1 -1 -3 -1 -5v-4s-2 -1 -3 -2s-3 -2 -3 -3s0 -1 -2 -2s-4 -1 -4 -1\ns-1 -1 -3 -2z"
            },
            "=": {
                x: 927,
                d: "M747 321h-567c-20 0 -30 6 -30 17c0 12 11 18 34 18h559c23 0 34 -6 34 -18c0 -11 -10 -17 -30 -17zM743 143h-559c-23 0 -34 6 -34 18c0 11 10 17 30 17h567c20 0 30 -6 30 -17c0 -12 -11 -18 -34 -18z"
            },
            "&#x3e;": {
                x: 941,
                d: "M771 228l-600 -268c-7 1 -11 4 -15 8s-6 8 -6 12c0 8 6 15 19 22l553 248l-553 248c-13 7 -19 14 -19 22c0 5 2 9 6 13s9 6 14 6c3 0 9 -2 17 -5l585 -262c13 -6 19 -13 19 -22c0 -8 -7 -15 -20 -22z"
            },
            "&#x2248;": {
                x: 967,
                d: "M817 452c0 -46 -17 -85 -49 -116s-71 -47 -118 -47c-20 0 -42 4 -64 12s-40 16 -53 24s-32 20 -56 36c-32 22 -61 38 -85 49s-49 17 -75 17c-19 0 -39 -4 -58 -11s-37 -19 -54 -37s-26 -40 -27 -66c0 -3 -1 -8 -4 -14s-6 -9 -10 -9c-9 0 -14 10 -14 30c0 46 16 85 48 116\ns72 47 119 47c20 0 41 -4 63 -12s41 -16 54 -24s32 -20 56 -36c32 -22 60 -38 84 -49s50 -17 76 -17c35 0 66 11 94 31s43 46 45 79c1 18 5 27 14 27s14 -10 14 -30zM817 218c0 -45 -16 -82 -48 -114s-72 -48 -119 -48c-20 0 -42 4 -64 12s-40 16 -53 24s-32 20 -56 36\nc-32 22 -61 39 -85 50s-49 16 -75 16c-19 0 -39 -3 -58 -10s-37 -20 -54 -38s-26 -40 -27 -66c0 -3 -1 -8 -4 -14s-6 -9 -10 -9c-9 0 -14 10 -14 30c0 46 16 85 48 116s72 47 119 47c20 0 41 -4 63 -12s41 -16 54 -24s32 -20 56 -36c32 -22 60 -39 84 -50s50 -16 76 -16\nc35 0 66 10 94 30s43 47 45 80c1 18 5 27 14 27s14 -10 14 -31z"
            },
            "&#x2247;": {
                x: 965,
                d: "M477 201l-88 -154h389c8 0 14 -1 18 -1s8 -2 12 -5s7 -8 7 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-414l-80 -140c-9 -16 -17 -24 -25 -24c-6 0 -11 2 -15 6s-5 9 -5 14c0 2 2 8 6 17c12 19 36 62 73 127h-135c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14\ns9 5 13 5s10 1 18 1h156l88 154h-246c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14s9 5 13 5s10 1 18 1h267l98 172c-14 7 -27 14 -38 21s-24 15 -37 24s-25 16 -35 22c-47 30 -89 45 -125 45c-19 0 -39 -4 -58 -11s-37 -19 -54 -37s-26 -40 -27 -66\nc0 -15 -5 -23 -14 -23s-14 10 -14 30c0 46 16 85 48 116s72 47 119 47c20 0 41 -5 63 -13s41 -16 55 -24s33 -21 57 -37c47 -32 76 -48 85 -48c1 0 4 4 9 12l96 167c7 13 14 19 22 19c6 0 10 -2 14 -6s6 -9 6 -14c0 -3 -3 -10 -8 -20l-98 -171c9 -2 19 -3 31 -3\nc35 0 67 10 94 31s42 47 44 79c1 18 5 27 14 27s14 -10 14 -31c0 -44 -16 -82 -47 -114s-71 -48 -119 -48c-21 0 -41 3 -60 10l-89 -156h278c8 0 14 -1 18 -1s8 -2 12 -5s7 -8 7 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-303z"
            },
            "&#x224d;": {
                x: 967,
                d: "M817 464c0 -5 -2 -10 -5 -13l-35 -28c-98 -71 -196 -107 -294 -107c-59 0 -120 14 -181 41c-15 7 -31 14 -46 23s-28 18 -40 26s-23 14 -32 21s-16 12 -21 17l-8 6c-3 4 -5 9 -5 14s2 10 6 14s8 6 14 6c4 0 11 -4 22 -13c99 -77 197 -115 292 -115s191 37 287 112\nc12 11 21 16 26 16c6 0 11 -2 15 -6s5 -9 5 -14zM817 36c0 -5 -1 -10 -5 -14s-9 -6 -15 -6c-4 0 -11 4 -22 13c-100 77 -197 115 -292 115s-191 -37 -287 -112c-12 -11 -21 -16 -26 -16c-6 0 -10 2 -14 6s-6 9 -6 14s2 10 5 13l35 28c98 71 196 107 294 107\nc59 0 120 -14 181 -41c15 -7 31 -14 46 -23s29 -18 41 -26s21 -14 30 -21s17 -12 22 -17l8 -6c3 -4 5 -9 5 -14z"
            },
            "&#x2252;": {
                x: 1031,
                d: "M256 541c0 -15 -6 -27 -16 -37s-22 -16 -37 -16s-27 6 -37 16s-16 22 -16 37s6 27 16 37s22 16 37 16s27 -6 37 -16s16 -22 16 -37zM881 -41c0 -15 -5 -27 -15 -37s-23 -16 -38 -16s-28 6 -38 16s-15 22 -15 37s5 27 15 37s23 16 38 16s28 -6 38 -16s15 -22 15 -37z\nM219 173h593c8 0 14 -1 18 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-595c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14c0 13 12 20 36 20zM218 367h595c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -18 -1h-593\nc-24 0 -36 7 -36 20c0 6 2 11 6 14s8 5 12 5s10 1 17 1z"
            },
            "&#x2253;": {
                x: 1031,
                d: "M881 541c0 -15 -5 -27 -15 -37s-23 -16 -38 -16s-28 6 -38 16s-15 22 -15 37s5 27 15 37s23 16 38 16s28 -6 38 -16s15 -22 15 -37zM256 -41c0 -15 -6 -27 -16 -37s-22 -16 -37 -16s-27 6 -37 16s-16 22 -16 37s6 27 16 37s22 16 37 16s27 -6 37 -16s16 -22 16 -37z\nM813 133h-595c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14c0 13 12 20 36 20h593c8 0 14 -1 18 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1zM812 327h-593c-24 0 -36 7 -36 20c0 6 2 11 6 14s8 5 12 5s10 1 17 1h595c7 0 13 -1 17 -1s8 -2 12 -5\ns6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -18 -1z"
            },
            "&#x224a;": {
                x: 977,
                d: "M821 549c0 -46 -17 -85 -49 -116s-71 -47 -117 -47c-20 0 -41 4 -62 12s-38 14 -50 21s-28 17 -48 30c-29 19 -50 33 -64 41s-31 16 -51 23s-39 11 -58 11s-38 -3 -57 -10s-37 -20 -54 -38s-26 -40 -27 -66c0 -15 -5 -23 -14 -23s-14 10 -14 30c0 46 16 85 48 116\ns72 47 118 47c20 0 41 -4 62 -12s38 -14 50 -21s28 -17 48 -30c29 -19 50 -33 64 -41s31 -16 51 -23s39 -11 58 -11c34 0 65 10 93 30s43 47 45 80c2 18 7 27 14 27c9 0 14 -10 14 -30zM186 7h605c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-607\nc-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14c0 13 12 20 36 20zM821 315c0 -45 -16 -83 -48 -115s-71 -47 -118 -47c-20 0 -41 3 -62 11s-38 15 -50 22s-28 17 -48 30c-29 19 -50 33 -64 41s-31 16 -51 23s-39 11 -58 11s-38 -4 -57 -11s-37 -19 -54 -37s-26 -40 -27 -66\nc0 -15 -5 -23 -14 -23s-14 10 -14 30c0 46 16 85 48 116s72 47 118 47c20 0 41 -3 62 -11s38 -15 50 -22s28 -17 48 -30c29 -19 50 -33 64 -41s31 -16 51 -23s39 -11 58 -11c35 0 66 10 93 31s42 45 45 74c1 21 6 32 14 32c9 0 14 -10 14 -31z"
            },
            "&#x223d;": {
                x: 966,
                d: "M816 166c0 -21 -5 -32 -14 -32c-7 0 -12 11 -14 33c-3 41 -17 76 -44 103s-58 41 -94 41c-20 0 -40 -5 -60 -14s-38 -19 -50 -29s-29 -24 -51 -44c-23 -21 -42 -36 -55 -46s-31 -20 -53 -30s-44 -15 -65 -15c-49 0 -89 21 -120 62s-46 88 -46 139c0 21 5 32 14 32\nc8 0 13 -11 14 -33c3 -41 18 -75 44 -103s57 -41 94 -41c20 0 40 5 60 14s38 19 50 29s29 24 51 44c23 21 42 36 55 46s31 20 53 30s44 15 65 15c49 0 89 -21 120 -62s46 -88 46 -139z"
            },
            "&#x2241;": {
                x: 965,
                d: "M478 214l-140 -167c-8 -10 -15 -15 -22 -15c-6 0 -10 2 -14 6s-6 9 -6 14s4 13 12 22l138 165c-47 35 -90 53 -129 53c-37 0 -69 -13 -96 -38c-25 -24 -38 -54 -39 -90c-2 -20 -7 -30 -16 -30c-11 0 -16 13 -16 40c0 52 16 98 47 136s71 57 120 57c20 0 41 -4 63 -13\ns39 -19 52 -28s32 -22 55 -40l140 167c9 10 16 15 22 15s11 -2 15 -6s5 -9 5 -14s-4 -13 -12 -22l-138 -165c47 -35 90 -53 129 -53c37 0 69 13 96 38c25 24 38 54 39 90c1 20 7 30 16 30c11 0 16 -13 16 -40c0 -52 -16 -98 -47 -136s-71 -57 -120 -57c-20 0 -40 4 -62 13\ns-40 19 -53 28s-32 22 -55 40z"
            },
            "&#x2242;": {
                x: 965,
                d: "M780 424h-595h-17s-8 3 -12 6s-6 8 -6 14s2 11 6 14s8 6 12 6h17h595h17s8 -3 12 -6s6 -8 6 -14s-2 -11 -6 -14s-8 -6 -12 -6h-17zM815 226c0 -49 -16 -93 -47 -132s-71 -58 -120 -58c-21 0 -42 5 -64 14s-38 19 -51 28s-29 21 -49 38c-25 21 -45 36 -58 46\ns-30 18 -50 27s-40 13 -59 13c-33 0 -64 -12 -93 -36s-44 -59 -46 -106c0 -3 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 11 -14 32c0 49 15 92 46 131s71 58 121 58c21 0 41 -5 63 -14s39 -19 52 -28s29 -21 49 -38c25 -21 45 -36 58 -46s30 -18 50 -27s40 -13 59 -13\nc33 0 63 12 92 36s45 59 47 106c0 4 1 8 3 14s6 9 11 9c9 0 14 -10 14 -31z"
            },
            "&#x2243;": {
                x: 967,
                d: "M817 432c0 -49 -15 -92 -47 -128s-72 -54 -120 -54c-21 0 -42 5 -64 14s-40 18 -54 28s-32 23 -55 41c-21 17 -39 30 -53 39s-30 16 -50 24s-38 12 -57 12c-20 0 -40 -4 -59 -12s-38 -23 -54 -44s-25 -47 -26 -78c0 -3 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 10 -14 31\nc0 49 16 92 48 128s71 54 119 54c21 0 42 -5 64 -14s40 -18 54 -28s32 -23 55 -41c21 -17 38 -30 52 -39s30 -16 50 -24s39 -12 58 -12c35 0 66 12 94 36s43 55 45 94c1 18 5 27 14 27s14 -10 14 -31zM186 76h595c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17\nh-597h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20z"
            },
            "&#x22cd;": {
                x: 966,
                d: "M816 282c0 -21 -5 -31 -14 -31c-8 0 -13 11 -14 33c-3 33 -17 62 -43 87s-58 37 -96 37c-41 0 -91 -23 -149 -68c-28 -22 -49 -38 -64 -48s-34 -19 -56 -28s-42 -14 -63 -14c-49 0 -90 19 -121 56s-46 78 -46 125c0 21 5 32 14 32c8 0 13 -11 14 -33c2 -33 16 -61 42 -86\ns59 -38 97 -38c41 0 91 23 149 68c28 22 49 38 64 48s34 19 56 28s42 14 63 14c49 0 89 -18 120 -55s47 -79 47 -127zM186 76h594c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17h-596h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20z"
            },
            "&#x224f;": {
                x: 965,
                d: "M626 367h154c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -18 -1h-156c-16 0 -26 2 -29 5s-6 12 -7 26c-1 23 -11 44 -31 64s-45 30 -74 30c-28 0 -52 -10 -72 -29s-31 -41 -32 -66c-1 -14 -4 -22 -8 -25s-13 -5 -28 -5h-156\nc-24 0 -36 7 -36 20c0 6 2 11 6 14s8 5 12 5s10 1 17 1h154c6 39 23 70 51 92s59 33 93 33c33 0 63 -10 91 -32s45 -53 52 -93zM186 173h593c8 0 14 -1 18 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-595c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14\nc0 13 12 20 36 20z"
            },
            "&#x224e;": {
                x: 965,
                d: "M626 367h154c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -18 -1h-156c-16 0 -26 2 -29 5s-6 12 -7 26c-1 23 -11 44 -31 64s-45 30 -74 30c-28 0 -52 -10 -72 -29s-31 -41 -32 -66c-1 -14 -4 -22 -8 -25s-13 -5 -28 -5h-156\nc-24 0 -36 7 -36 20c0 6 2 11 6 14s8 5 12 5s10 1 17 1h154c6 39 23 70 51 92s59 33 93 33c33 0 63 -10 91 -32s45 -53 52 -93zM623 173h156c8 0 14 -1 18 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-154c-6 -39 -23 -70 -51 -92s-59 -33 -93 -33\nc-33 0 -63 10 -91 32s-45 53 -52 93h-154c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14c0 13 12 20 36 20h156c16 0 26 -2 29 -5s6 -12 7 -26c1 -23 12 -44 32 -64s44 -30 73 -30c28 0 52 10 72 29s31 41 32 66c1 14 3 22 7 25s14 5 29 5z"
            },
            "&#x2257;": {
                x: 965,
                d: "M592 611c0 -29 -11 -55 -32 -77s-47 -32 -78 -32c-30 0 -56 10 -77 32s-32 48 -32 77c0 31 11 57 33 78s47 32 77 32c29 0 54 -10 76 -31s33 -48 33 -79zM483 542c25 0 42 5 53 16s16 28 16 53s-5 42 -15 53s-28 17 -55 17s-46 -6 -55 -17s-14 -29 -14 -53\nc0 -26 6 -44 17 -54s28 -15 53 -15zM186 173h593c8 0 14 -1 18 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-595c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14c0 13 12 20 36 20zM185 367h595c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14\ns-8 -5 -12 -5s-10 -1 -18 -1h-593c-24 0 -36 7 -36 20c0 6 2 11 6 14s8 5 12 5s10 1 17 1z"
            },
            "&#x2245;": {
                x: 965,
                d: "M815 305c0 -32 -6 -63 -19 -93s-32 -55 -58 -76s-56 -31 -89 -31c-17 0 -33 2 -48 7s-31 12 -47 23s-30 21 -40 29s-24 20 -42 36c-20 18 -36 31 -48 40s-28 19 -48 28s-40 14 -60 14c-15 0 -30 -3 -44 -8s-28 -13 -42 -24s-27 -27 -36 -48s-15 -46 -16 -73\nc0 -4 -2 -9 -4 -15s-5 -8 -10 -8c-9 0 -14 11 -14 32c0 32 6 63 19 94s32 57 58 78s56 31 89 31h6c14 0 29 -2 42 -6c15 -5 32 -12 48 -23s28 -21 38 -29s24 -20 43 -37c1 -1 4 -3 7 -6c19 -17 35 -30 46 -39s26 -18 46 -27s38 -13 57 -13c37 0 68 13 95 41s41 63 43 105\nc0 20 5 30 14 30s14 -11 14 -32zM753 -174h-541h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h539c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17zM753 20h-541h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h539c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14\ns-8 -6 -12 -6h-17z"
            },
            "&#x22de;": {
                x: 967,
                d: "M650 712c0 -3 -1 -9 -2 -18s-4 -23 -10 -42s-15 -36 -26 -53c-24 -37 -57 -69 -98 -95s-84 -44 -129 -54s-83 -16 -113 -20s-57 -6 -82 -6h-22s-8 3 -12 6s-6 8 -6 14c0 13 9 20 26 20c47 0 92 4 136 12s88 21 132 38s80 42 110 75s48 73 55 118c3 17 10 26 21 26\nc5 0 10 -1 14 -5s6 -9 6 -16zM188 310h29c26 0 64 2 115 6c49 5 92 12 132 21s72 19 98 29s49 24 69 39s35 29 45 41s19 27 26 44s12 29 14 38s4 21 6 35c0 3 2 6 5 10s8 6 14 6c7 0 12 -3 15 -7s5 -9 5 -14c0 -33 -12 -70 -36 -110c-47 -78 -143 -131 -288 -159\nc98 -16 177 -49 238 -98c23 -20 43 -45 57 -73s22 -49 25 -64s4 -26 4 -33c0 -6 -3 -11 -7 -15s-8 -6 -13 -6c-11 0 -18 9 -21 26c-15 107 -95 179 -240 214c-81 19 -167 29 -256 29c-35 0 -54 0 -59 1c-10 5 -15 12 -15 20c0 13 13 20 38 20z"
            },
            "&#x22df;": {
                x: 967,
                d: "M761 444c0 -9 -3 -15 -9 -17s-16 -3 -31 -3c-28 0 -58 2 -91 6s-72 13 -116 24s-87 30 -126 56s-70 57 -92 94c-23 39 -35 75 -35 108c0 7 3 12 7 16s8 5 13 5c11 0 18 -8 21 -25c5 -38 19 -72 41 -101s48 -53 77 -70s63 -32 101 -43s73 -20 107 -24s68 -6 103 -6\nc20 0 30 -7 30 -20zM471 289v1c-96 16 -174 49 -235 98c-23 20 -42 44 -56 72s-23 49 -26 64s-4 26 -4 34c0 14 7 21 20 21c11 0 18 -9 21 -26c17 -108 97 -179 240 -214c81 -19 169 -29 263 -29h29c25 0 38 -7 38 -20c0 -7 -3 -11 -7 -14s-9 -5 -14 -6s-14 -1 -27 -1\nc-115 0 -211 -10 -289 -31c-48 -13 -88 -28 -120 -47s-55 -39 -70 -61s-26 -42 -31 -57s-10 -34 -13 -55c-2 -12 -9 -18 -20 -18c-5 0 -10 2 -14 6s-6 9 -6 15c0 33 12 70 36 110c47 79 142 131 285 158z"
            },
            "&#x2250;": {
                x: 965,
                d: "M537 541c0 -16 -6 -29 -17 -39s-23 -16 -38 -16c-13 0 -26 5 -37 15s-17 23 -17 40c0 16 5 29 16 39s24 16 39 16c13 0 26 -5 37 -15s17 -23 17 -40zM186 173h593c8 0 14 -1 18 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-595\nc-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14c0 13 12 20 36 20zM185 367h595c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -18 -1h-593c-24 0 -36 7 -36 20c0 6 2 11 6 14s8 5 12 5s10 1 17 1z"
            },
            "&#x2251;": {
                x: 965,
                d: "M537 541c0 -16 -6 -29 -17 -39s-23 -16 -38 -16c-13 0 -26 5 -37 15s-17 23 -17 40c0 16 5 29 16 39s24 16 39 16c13 0 26 -5 37 -15s17 -23 17 -40zM537 -41c0 -17 -6 -30 -17 -40s-24 -15 -37 -15c-15 0 -28 6 -39 16s-16 23 -16 39s5 29 16 39s24 16 39 16\nc13 0 26 -5 37 -15s17 -23 17 -40zM186 173h593c8 0 14 -1 18 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-595c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14c0 13 12 20 36 20zM185 367h595c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14\ns-8 -5 -12 -5s-10 -1 -18 -1h-593c-24 0 -36 7 -36 20c0 6 2 11 6 14s8 5 12 5s10 1 17 1z"
            },
            "&#x2256;": {
                x: 965,
                d: "M559 173h220c8 0 14 -1 18 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-595c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14c0 13 12 20 36 20h220c-21 21 -32 47 -32 77c0 27 11 52 32 77h-220c-24 0 -36 7 -36 20c0 6 2 11 6 14s8 5 12 5s10 1 17 1\nh595c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -18 -1h-220c21 -21 32 -47 32 -77c0 -27 -11 -52 -32 -77zM502 327h-39c-8 0 -13 0 -17 -1s-9 -3 -14 -6s-11 -9 -18 -17c-11 -15 -17 -33 -17 -53c0 -21 6 -39 19 -54c9 -11 16 -18 22 -20\ns14 -3 25 -3h39c8 0 14 0 18 1s8 3 13 6s11 9 18 17c11 15 17 33 17 53c0 21 -6 39 -19 54c-9 11 -16 18 -22 20s-14 3 -25 3z"
            },
            "&#x2a96;": {
                x: 967,
                d: "M723 333l-553 261c-1 0 -1 0 -2 1c-12 6 -18 13 -18 21c0 5 2 10 6 14s9 6 14 6c4 0 11 -2 20 -7l551 -261c13 -6 20 -13 20 -21c0 -7 -3 -12 -7 -15s-8 -5 -13 -5c-3 0 -9 2 -18 6zM741 130l-553 -260c-9 -5 -15 -7 -18 -7c-5 0 -10 2 -14 6s-6 9 -6 14c0 9 6 16 19 22\nl525 248l-524 247c-1 0 -1 0 -2 1c-12 6 -18 13 -18 21c0 6 2 10 6 14s9 6 14 6c4 0 11 -2 20 -7l551 -260c13 -6 20 -14 20 -23s-7 -16 -20 -22z"
            },
            "&#x2a95;": {
                x: 967,
                d: "M742 594l-556 -262c-8 -3 -13 -5 -16 -5c-5 0 -10 2 -14 6s-6 8 -6 13c0 9 7 17 20 23l553 261c8 4 14 6 18 6c5 0 10 -2 14 -6s6 -9 6 -14c0 -8 -6 -15 -19 -22zM723 -130l-553 260c-13 6 -20 13 -20 22s7 17 20 23l553 261c8 4 14 6 18 6c5 0 9 -2 13 -6s7 -8 7 -14\nc0 -8 -6 -15 -19 -22l-525 -248l524 -247c13 -6 20 -13 20 -22c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -9 2 -18 7z"
            },
            "&#x2261;": {
                x: 967,
                d: "M781 424h-595c-24 0 -36 7 -36 20c0 6 2 11 6 14s8 6 12 6h17h597h17s8 -3 12 -6s6 -8 6 -14c0 -13 -12 -20 -36 -20zM782 36h-597h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h595c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17zM782 230h-597h-17\ns-8 3 -12 6s-6 8 -6 14s2 11 6 14s8 6 12 6h17h597h17s8 -3 12 -6s6 -8 6 -14s-2 -11 -6 -14s-8 -6 -12 -6h-17z"
            },
            "&#x2265;": {
                x: 967,
                d: "M741 324l-553 -260c-9 -5 -15 -7 -18 -7c-5 0 -10 2 -14 6s-6 9 -6 14c0 9 7 17 21 23l523 247l-525 247c-13 6 -19 13 -19 22c0 5 2 10 6 14s8 6 14 6c4 0 11 -2 20 -7l551 -260c13 -6 20 -14 20 -23s-7 -16 -20 -22zM726 -137h-541c-7 0 -13 1 -17 1s-8 2 -12 5\ns-6 8 -6 14c0 13 12 20 36 20h539c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1z"
            },
            "&#x2264;": {
                x: 967,
                d: "M742 594l-525 -248l523 -246c14 -7 21 -14 21 -23c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -9 2 -18 7l-553 260c-13 6 -20 13 -20 22s7 17 20 23l553 261c8 4 14 6 18 6c6 0 11 -2 15 -6s5 -9 5 -14c0 -9 -6 -16 -19 -22zM726 -137h-541c-7 0 -13 1 -17 1s-8 2 -12 5\ns-6 8 -6 14c0 13 12 20 36 20h539c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1z"
            },
            "&#x2266;": {
                x: 967,
                d: "M742 711l-525 -248l524 -247c13 -6 20 -13 20 -22c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -9 2 -18 7l-553 260c-13 6 -20 13 -20 22s7 17 20 23l553 261c8 4 14 6 18 6c5 0 10 -2 14 -6s6 -9 6 -14c0 -9 -6 -16 -19 -22zM726 -174h-541h-17s-8 3 -12 6s-6 8 -6 14\nc0 13 12 20 36 20h539c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17zM726 20h-541h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h539c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17z"
            },
            "&#x2267;": {
                x: 967,
                d: "M741 441l-553 -260c-9 -5 -15 -7 -18 -7c-5 0 -10 2 -14 6s-6 9 -6 14c0 9 6 16 19 22l525 248l-524 247c-1 0 -1 0 -2 1c-12 6 -18 13 -18 21c0 5 2 10 6 14s9 6 14 6c4 0 11 -2 20 -7l551 -260c13 -7 20 -14 20 -23s-7 -16 -20 -22zM726 -174h-541h-17s-8 3 -12 6\ns-6 8 -6 14c0 13 12 20 36 20h539c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17zM726 20h-541h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h539c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17z"
            },
            "&#x2a7e;": {
                x: 967,
                d: "M741 324l-553 -260c-9 -5 -15 -7 -18 -7c-5 0 -10 2 -14 6s-6 9 -6 14c0 9 6 16 19 22l525 248l-524 247c-1 0 -1 0 -2 1c-12 6 -18 13 -18 21c0 5 2 10 6 14s9 6 14 6c4 0 11 -2 20 -7l551 -261c13 -6 20 -13 20 -22s-7 -16 -20 -22zM741 130l-553 -260\nc-9 -5 -15 -7 -18 -7c-5 0 -10 2 -14 6s-6 9 -6 14c0 9 6 16 19 22l566 267c17 0 26 -7 26 -20c0 -9 -7 -16 -20 -22z"
            },
            "&#x2a7d;": {
                x: 967,
                d: "M742 594l-525 -248l524 -247c13 -6 20 -13 20 -22c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -9 2 -18 7l-553 260c-13 6 -20 13 -20 22s7 17 20 23l553 261c8 4 14 6 18 6c5 0 10 -2 14 -6s6 -9 6 -14c0 -8 -6 -15 -19 -22zM723 -130l-553 260c-13 6 -20 13 -20 22\nc0 5 2 10 6 14s8 6 14 6c4 0 10 -2 19 -7l552 -260c13 -6 20 -13 20 -22c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -9 2 -18 7z"
            },
            "&#x226b;": {
                x: 1189,
                d: "M715 228l-539 -294c-17 0 -26 7 -26 20c0 7 7 14 20 22l502 274l-502 274c-13 8 -20 15 -20 22s2 12 6 15s9 5 14 5c4 0 10 -2 18 -6l527 -288c11 -7 17 -12 18 -16c1 -2 1 -4 1 -6c0 -7 -5 -14 -16 -20c-1 -1 -2 -1 -3 -2zM1020 228l-539 -294c-17 0 -26 7 -26 20\nc0 7 7 14 20 22l502 274l-502 274c-13 8 -20 15 -20 22s2 12 6 15s9 5 14 5c4 0 10 -2 18 -6l527 -288c11 -7 17 -12 18 -16c1 -2 1 -4 1 -6c0 -7 -5 -14 -16 -20c-1 -1 -2 -1 -3 -2z"
            },
            "&#x226a;": {
                x: 1189,
                d: "M714 524l-502 -274l502 -274c13 -8 20 -15 20 -22c0 -13 -7 -20 -20 -20c-5 0 -11 2 -19 7l-526 287c-11 7 -17 12 -18 16c-1 2 -1 4 -1 6c0 7 5 14 16 20c1 1 2 1 3 2l527 288c8 4 14 6 18 6c5 0 10 -2 14 -5s6 -8 6 -15s-7 -14 -20 -22zM1019 524l-502 -274l502 -274\nc13 -8 20 -15 20 -22c0 -13 -7 -20 -20 -20c-5 0 -11 2 -19 7l-526 287c-11 7 -17 12 -18 16c-1 2 -1 4 -1 6c0 7 5 14 16 20c1 1 2 1 3 2l527 288c8 4 14 6 18 6c5 0 10 -2 14 -5s6 -8 6 -15s-7 -14 -20 -22z"
            },
            "&#x2268;": {
                x: 967,
                d: "M742 711l-525 -248l524 -247c13 -6 20 -13 20 -22c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -9 2 -18 7l-553 260c-13 6 -20 13 -20 22s7 17 20 23l553 261c8 4 14 6 18 6c5 0 10 -2 14 -6s6 -9 6 -14c0 -9 -6 -16 -19 -22zM557 20l-147 -154h315c24 0 36 -7 36 -20\nc0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17h-355c-38 -41 -65 -69 -80 -84c-13 -12 -21 -18 -26 -18s-10 2 -14 6s-6 8 -6 13s3 11 10 18c1 1 4 4 7 8s6 7 7 8c17 19 32 36 47 49h-131h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h168l147 154h-316h-17s-8 3 -12 6s-6 8 -6 14\nc0 13 12 20 36 20h354c38 41 65 69 80 84c13 12 21 18 26 18s10 -2 14 -6s6 -8 6 -13s-3 -11 -10 -18c-1 -1 -3 -4 -7 -8s-6 -7 -7 -8c-17 -19 -32 -36 -47 -49h130c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17h-169z"
            },
            "&#x2269;": {
                x: 967,
                d: "M741 441l-553 -260c-9 -5 -15 -7 -18 -7c-5 0 -10 2 -14 6s-6 9 -6 14c0 9 6 16 19 22l525 248l-524 247c-1 0 -1 0 -2 1c-12 6 -18 13 -18 21c0 5 2 10 6 14s9 6 14 6c4 0 11 -2 20 -7l551 -260c13 -7 20 -14 20 -23s-7 -16 -20 -22zM557 20l-147 -154h315\nc24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17h-355c-38 -41 -65 -69 -80 -84c-13 -12 -21 -18 -26 -18s-10 2 -14 6s-6 8 -6 13s3 11 10 18c1 1 4 4 7 8s6 7 7 8c17 19 32 36 47 49h-131h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h168l147 154h-316h-17\ns-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h354c38 41 65 69 80 84c13 12 21 18 26 18s10 -2 14 -6s6 -8 6 -13s-3 -11 -10 -18c-1 -1 -3 -4 -7 -8s-6 -7 -7 -8c-17 -19 -32 -36 -47 -49h130c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17h-169z"
            },
            "&#x22d8;": {
                x: 1520,
                d: "M712 524l-500 -274l500 -274c13 -7 20 -15 20 -22c0 -13 -7 -20 -20 -20c-5 0 -11 2 -19 7l-523 287c-9 5 -14 8 -16 10s-4 7 -4 12c0 7 5 14 16 20c1 1 2 1 3 2l525 287c9 5 15 7 18 7c5 0 10 -2 14 -6s6 -8 6 -14c0 -7 -7 -15 -20 -22zM1350 524l-500 -274l500 -274\nc13 -7 20 -15 20 -22c0 -13 -7 -20 -20 -20c-5 0 -11 2 -19 7l-523 287c-9 5 -14 8 -16 10s-4 7 -4 12c0 7 5 14 16 20c1 1 2 1 3 2l525 287c9 5 15 7 18 7c5 0 10 -2 14 -6s6 -8 6 -14c0 -7 -7 -15 -20 -22zM1031 524l-500 -274l500 -274c13 -7 20 -15 20 -22\nc0 -13 -7 -20 -20 -20c-5 0 -11 2 -19 7l-523 287c-9 5 -15 8 -17 10s-3 7 -3 12c0 7 5 14 16 20c1 1 2 1 3 2l525 287c9 5 15 7 18 7c5 0 9 -2 13 -6s7 -8 7 -14c0 -7 -7 -15 -20 -22z"
            },
            "&#x22d9;": {
                x: 1520,
                d: "M733 228l-544 -287c-9 -5 -15 -7 -19 -7c-13 0 -20 7 -20 20c0 7 7 15 20 22l520 274l-520 274c-13 7 -20 15 -20 22c0 6 2 10 6 14s9 6 14 6c2 0 8 -2 18 -6l545 -288c13 -7 19 -14 19 -22s-6 -15 -19 -22zM1351 228l-544 -287c-9 -5 -15 -7 -19 -7c-13 0 -20 7 -20 20\nc0 3 0 5 1 7s3 4 5 6l4 4s4 2 8 4l521 275l-520 275c-5 3 -7 4 -9 5s-4 2 -6 4s-4 4 -4 6v6c0 6 2 11 5 14s7 4 9 5s4 1 6 1s8 -2 18 -6l544 -288c12 -7 18 -12 19 -16c1 -2 1 -4 1 -6c0 -8 -6 -15 -19 -22zM1042 228l-544 -287c-9 -5 -15 -7 -19 -7c-13 0 -20 7 -20 20\nc0 5 1 9 4 12s8 6 16 10l519 274l-521 275c-12 6 -18 13 -18 21c0 7 3 12 7 15s8 5 13 5c2 0 8 -2 18 -6l544 -288c9 -4 15 -7 17 -10s3 -7 3 -12c0 -8 -6 -15 -19 -22z"
            },
            "&#x2a87;": {
                x: 967,
                d: "M742 594l-525 -248l524 -247c13 -6 20 -13 20 -22c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -9 2 -18 7l-553 260c-13 6 -20 13 -20 22s7 17 20 23l553 261c8 4 14 6 18 6c5 0 10 -2 14 -6s6 -9 6 -14c0 -8 -6 -15 -19 -22zM726 -137h-262l-81 -81c-9 -11 -17 -16 -24 -16\nc-13 0 -20 7 -20 20c0 5 4 12 13 22c19 18 37 36 55 55h-222c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14c0 13 12 20 36 20h262l80 81c11 11 19 16 24 16s9 -1 12 -4s5 -5 6 -8s2 -6 2 -8c0 -6 -5 -14 -15 -24l-52 -53h220c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -5 -12 -5\ns-10 -1 -17 -1z"
            },
            "&#x2a88;": {
                x: 967,
                d: "M741 324l-553 -260c-9 -5 -15 -7 -18 -7c-5 0 -10 2 -14 6s-6 9 -6 14c0 9 6 16 19 22l525 248l-524 247c-1 0 -1 0 -2 1c-12 6 -18 13 -18 21c0 5 2 10 6 14s9 6 14 6c4 0 11 -2 20 -7l551 -261c13 -6 20 -13 20 -22s-7 -16 -20 -22zM726 -137h-262l-81 -81\nc-9 -11 -17 -16 -24 -16c-13 0 -20 7 -20 20c0 5 4 12 13 22c19 18 37 36 55 55h-222c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14c0 13 12 20 36 20h262l80 81c11 11 19 16 24 16s9 -1 12 -4s5 -5 6 -8s2 -6 2 -8c0 -6 -5 -14 -15 -24l-52 -53h220c24 0 36 -7 36 -20\nc0 -6 -2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1z"
            },
            "&#x2a89;": {
                x: 965,
                d: "M765 719l-516 -217l518 -217l8 -4c1 -1 3 -2 6 -4s4 -4 5 -6s2 -5 2 -8c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -10 2 -19 6l-550 231c-15 7 -22 14 -22 22c0 9 7 17 22 23l551 231c11 4 17 6 18 6c5 0 10 -2 14 -6s6 -9 6 -14c0 -6 -2 -11 -5 -14s-9 -6 -18 -9z\nM445 -214l-60 -145c-5 -13 -13 -20 -22 -20c-5 0 -9 1 -13 5s-7 9 -7 14c0 2 25 62 74 181c-33 20 -67 30 -101 30c-32 0 -63 -10 -92 -29s-44 -48 -46 -85c0 -4 -2 -9 -4 -15s-5 -8 -10 -8c-9 0 -14 10 -14 30c0 45 16 84 48 116s72 47 118 47c35 0 70 -10 105 -30\nc8 -5 13 -7 14 -7s3 4 8 13l51 123c-46 30 -80 51 -103 62s-48 16 -75 16c-32 0 -63 -10 -92 -29s-44 -48 -46 -85c0 -4 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 10 -14 30c0 45 16 83 48 115s72 48 118 48c43 0 95 -20 156 -60c23 -17 37 -26 40 -26c2 0 5 4 8 13l60 145\nc5 13 13 20 22 20c5 0 10 -2 14 -6s6 -8 6 -13c0 -2 -25 -62 -74 -181c33 -20 67 -30 101 -30c36 0 67 11 94 32s41 45 44 73c1 21 6 32 14 32c9 0 14 -10 14 -31c0 -45 -16 -82 -48 -114s-71 -48 -118 -48c-35 0 -70 10 -105 30c-8 5 -13 7 -14 7s-4 -4 -8 -13l-51 -123\nc46 -30 80 -50 103 -61s48 -17 75 -17c36 0 67 10 94 31s41 46 44 74c1 21 6 32 14 32c9 0 14 -10 14 -31c0 -45 -16 -83 -48 -115s-71 -47 -118 -47c-43 0 -95 20 -156 60c-23 17 -37 26 -40 26c-2 0 -5 -4 -8 -13z"
            },
            "&#x2a8a;": {
                x: 965,
                d: "M766 480l-550 -231c-9 -4 -16 -6 -19 -6c-5 0 -10 2 -14 6s-6 9 -6 14c0 4 1 8 3 11s6 5 8 6l12 6l516 217l-518 217c-14 6 -21 13 -21 22c0 5 2 10 6 14s9 6 14 6c1 0 7 -2 18 -6l551 -231c15 -6 22 -14 22 -23c0 -8 -7 -15 -22 -22zM445 -214l-60 -145\nc-5 -13 -13 -20 -22 -20c-5 0 -9 1 -13 5s-7 9 -7 14c0 2 25 62 74 181c-33 20 -67 30 -101 30c-32 0 -63 -10 -92 -29s-44 -48 -46 -85c0 -4 -2 -9 -4 -15s-5 -8 -10 -8c-9 0 -14 10 -14 30c0 45 16 84 48 116s72 47 118 47c35 0 70 -10 105 -30c8 -5 13 -7 14 -7s3 4 8 13\nl51 123c-46 30 -80 51 -103 62s-48 16 -75 16c-32 0 -63 -10 -92 -29s-44 -48 -46 -85c0 -4 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 10 -14 30c0 45 16 83 48 115s72 48 118 48c43 0 95 -20 156 -60c23 -17 37 -26 40 -26c2 0 5 4 8 13l60 145c5 13 13 20 22 20\nc5 0 10 -2 14 -6s6 -8 6 -13c0 -2 -25 -62 -74 -181c33 -20 67 -30 101 -30c36 0 67 11 94 32s41 45 44 73c1 21 6 32 14 32c9 0 14 -10 14 -31c0 -45 -16 -82 -48 -114s-71 -48 -118 -48c-35 0 -70 10 -105 30c-8 5 -13 7 -14 7s-4 -4 -8 -13l-51 -123\nc46 -30 80 -50 103 -61s48 -17 75 -17c36 0 67 10 94 31s41 46 44 74c1 21 6 32 14 32c9 0 14 -10 14 -31c0 -45 -16 -83 -48 -115s-71 -47 -118 -47c-43 0 -95 20 -156 60c-23 17 -37 26 -40 26c-2 0 -5 -4 -8 -13z"
            },
            "&#x22e7;": {
                x: 965,
                d: "M768 420l-553 -260c-9 -5 -15 -7 -18 -7c-5 0 -10 2 -14 6s-6 9 -6 14c0 9 6 16 19 22l525 248l-524 247c-1 0 -1 0 -2 1c-12 6 -18 13 -18 21c0 5 2 10 6 14s9 6 14 6c4 0 11 -2 20 -7l551 -260c13 -7 20 -14 20 -23s-7 -16 -20 -22zM486 -144l-97 -177\nc-7 -12 -11 -19 -13 -22s-7 -4 -13 -4c-13 0 -20 7 -20 20c0 4 3 11 9 21l104 190c-53 45 -99 67 -140 67c-20 0 -39 -4 -58 -13s-38 -26 -54 -50s-25 -54 -26 -90c0 -4 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 11 -14 32c0 51 15 97 46 138s71 62 120 62c29 0 54 -7 78 -20\ns52 -34 85 -62l97 177c7 12 12 19 14 22s6 4 12 4c13 0 20 -7 20 -20c0 -4 -3 -11 -9 -21l-104 -190c53 -45 99 -67 140 -67c37 0 68 14 95 42s41 62 43 104c0 20 5 30 14 30s14 -11 14 -32c0 -51 -15 -97 -46 -138s-71 -62 -120 -62c-29 0 -55 7 -79 20s-51 34 -84 62z"
            },
            "&#x22e6;": {
                x: 965,
                d: "M769 690l-525 -248l524 -247c13 -6 20 -13 20 -22c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -9 2 -18 7l-553 260c-13 6 -20 13 -20 22s7 17 20 23l553 261c8 4 14 6 18 6c5 0 10 -2 14 -6s6 -9 6 -14c0 -8 -6 -15 -19 -22zM486 -144l-97 -177c-7 -12 -11 -19 -13 -22\ns-7 -4 -13 -4c-13 0 -20 7 -20 20c0 4 3 11 9 21l104 190c-53 45 -99 67 -140 67c-20 0 -39 -4 -58 -13s-38 -26 -54 -50s-25 -54 -26 -90c0 -4 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 11 -14 32c0 51 15 97 46 138s71 62 120 62c29 0 54 -7 78 -20s52 -34 85 -62l97 177\nc7 12 12 19 14 22s6 4 12 4c13 0 20 -7 20 -20c0 -4 -3 -11 -9 -21l-104 -190c53 -45 99 -67 140 -67c37 0 68 14 95 42s41 62 43 104c0 20 5 30 14 30s14 -11 14 -32c0 -51 -15 -97 -46 -138s-71 -62 -120 -62c-29 0 -55 7 -79 20s-51 34 -84 62z"
            },
            "&#x2a86;": {
                x: 965,
                d: "M766 480l-550 -231c-9 -4 -16 -6 -19 -6c-5 0 -10 2 -14 6s-6 9 -6 14c0 4 1 8 3 11s6 5 8 6l12 6l516 217l-518 217c-14 6 -21 13 -21 22c0 5 2 10 6 14s9 6 14 6c1 0 7 -2 18 -6l551 -231c15 -6 22 -14 22 -23c0 -8 -7 -15 -22 -22zM815 -125c0 -45 -16 -83 -48 -115\ns-71 -47 -118 -47c-20 0 -41 3 -62 11s-38 15 -50 22s-28 17 -48 30c-29 19 -50 33 -64 41s-31 16 -51 23s-39 11 -58 11c-32 0 -63 -10 -92 -29s-44 -48 -46 -85c0 -4 -2 -9 -4 -15s-5 -8 -10 -8c-9 0 -14 10 -14 30c0 45 16 84 48 116s72 47 118 47c20 0 41 -3 62 -11\ns38 -15 50 -22s28 -17 48 -30c29 -19 50 -33 64 -41s31 -16 51 -23s39 -11 58 -11c36 0 67 10 94 31s41 46 44 74c1 21 6 32 14 32c9 0 14 -10 14 -31zM815 108c0 -45 -16 -82 -48 -114s-71 -48 -118 -48c-20 0 -41 4 -62 12s-38 14 -50 21s-28 17 -48 30\nc-29 19 -50 33 -64 41s-31 16 -51 23s-39 11 -58 11c-32 0 -63 -10 -92 -29s-44 -48 -46 -85c0 -4 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 10 -14 30c0 45 16 83 48 115s72 48 118 48c20 0 41 -4 62 -12s38 -14 50 -21s28 -17 48 -30c29 -19 50 -33 64 -41s31 -16 51 -23\ns39 -11 58 -11c36 0 67 11 94 32s41 45 44 73c1 21 6 32 14 32c9 0 14 -10 14 -31z"
            },
            "&#x2a85;": {
                x: 965,
                d: "M765 719l-516 -217l518 -217l8 -4c1 -1 3 -2 6 -4s4 -4 5 -6s2 -5 2 -8c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -10 2 -19 6l-550 231c-15 7 -22 14 -22 22c0 9 7 17 22 23l551 231c11 4 17 6 18 6c5 0 10 -2 14 -6s6 -9 6 -14c0 -6 -2 -11 -5 -14s-9 -6 -18 -9z\nM815 -125c0 -45 -16 -83 -48 -115s-71 -47 -118 -47c-20 0 -41 3 -62 11s-38 15 -50 22s-28 17 -48 30c-29 19 -50 33 -64 41s-31 16 -51 23s-39 11 -58 11c-32 0 -63 -10 -92 -29s-44 -48 -46 -85c0 -4 -2 -9 -4 -15s-5 -8 -10 -8c-9 0 -14 10 -14 30c0 45 16 84 48 116\ns72 47 118 47c20 0 41 -3 62 -11s38 -15 50 -22s28 -17 48 -30c29 -19 50 -33 64 -41s31 -16 51 -23s39 -11 58 -11c36 0 67 10 94 31s41 46 44 74c1 21 6 32 14 32c9 0 14 -10 14 -31zM815 108c0 -45 -16 -82 -48 -114s-71 -48 -118 -48c-20 0 -41 4 -62 12s-38 14 -50 21\ns-28 17 -48 30c-29 19 -50 33 -64 41s-31 16 -51 23s-39 11 -58 11c-32 0 -63 -10 -92 -29s-44 -48 -46 -85c0 -4 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 10 -14 30c0 45 16 83 48 115s72 48 118 48c20 0 41 -4 62 -12s38 -14 50 -21s28 -17 48 -30c29 -19 50 -33 64 -41\ns31 -16 51 -23s39 -11 58 -11c36 0 67 11 94 32s41 45 44 73c1 21 6 32 14 32c9 0 14 -10 14 -31z"
            },
            "&#x22db;": {
                x: 891,
                d: "M718 632l-529 -202c-9 -4 -16 -6 -19 -6c-5 0 -10 2 -14 6s-6 9 -6 14c0 9 8 17 23 23l492 188l-494 189c-14 5 -21 13 -21 22c0 5 2 10 6 14s9 6 14 6c3 0 9 -2 18 -6l532 -203c14 -5 21 -13 21 -22c0 -10 -8 -18 -23 -23zM718 33l-492 -188l494 -189\nc14 -6 21 -13 21 -22c0 -6 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -10 2 -19 6l-531 203c-14 5 -21 13 -21 22s8 17 24 23l529 202c9 4 15 6 18 6c5 0 10 -2 14 -6s6 -9 6 -14c0 -10 -8 -18 -23 -23zM706 230h-521h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h519\nc24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17z"
            },
            "&#x22da;": {
                x: 891,
                d: "M718 843l-492 -188l494 -189c14 -5 21 -13 21 -22c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -10 2 -19 6l-531 203c-14 5 -21 13 -21 22s8 17 23 23l530 202c9 4 15 6 18 6c5 0 10 -2 14 -6s6 -9 6 -14c0 -10 -8 -18 -23 -23zM718 -178l-529 -202c-9 -4 -16 -6 -19 -6\nc-5 0 -10 2 -14 6s-6 8 -6 14c0 9 8 17 24 23l491 188l-494 189c-14 5 -21 13 -21 22c0 5 2 10 6 14s9 6 14 6c3 0 9 -2 18 -6l532 -203c14 -6 21 -13 21 -22c0 -10 -8 -18 -23 -23zM706 230h-521h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h519c24 0 36 -7 36 -20\nc0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17z"
            },
            "&#x2a8b;": {
                x: 967,
                d: "M737 960l-509 -188l512 -189c14 -5 21 -13 21 -22c0 -6 -3 -11 -7 -15s-8 -5 -13 -5c-3 0 -9 2 -18 6l-552 203c-14 5 -21 13 -21 22s8 17 23 23l549 202c10 4 16 6 19 6c5 0 10 -2 14 -6s6 -9 6 -14c0 -10 -8 -18 -24 -23zM174 -420l509 188l-512 189\nc-14 5 -21 13 -21 22c0 6 2 11 6 15s9 5 14 5c3 0 9 -2 18 -6l552 -203c14 -6 21 -13 21 -22s-8 -17 -23 -23l-549 -202c-10 -4 -16 -6 -19 -6c-5 0 -10 2 -14 6s-6 9 -6 14c0 10 8 18 24 23zM185 193h541c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14\ns-8 -5 -12 -5s-10 -1 -17 -1h-541c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14s8 5 12 5s10 1 17 1zM185 387h541c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-541c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14\ns8 5 12 5s10 1 17 1z"
            },
            "&#x2a8c;": {
                x: 967,
                d: "M738 749l-550 -202c-9 -4 -15 -6 -18 -6c-5 0 -10 1 -14 5s-6 9 -6 15c0 9 8 17 23 23l510 188l-512 189c-14 5 -21 13 -21 22c0 5 2 10 6 14s9 6 14 6c3 0 9 -2 19 -6l551 -203c14 -5 21 -13 21 -22c0 -10 -8 -18 -23 -23zM738 -44l-510 -188l512 -189\nc14 -5 21 -13 21 -22c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -9 2 -19 6l-551 203c-14 5 -21 13 -21 22c0 10 8 18 24 23l549 202c9 4 15 6 18 6c5 0 9 -1 13 -5s7 -9 7 -15c0 -9 -8 -17 -23 -23zM726 153h-541c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14c0 13 12 20 36 20\nh539c8 0 14 -1 18 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1zM725 347h-539c-24 0 -36 7 -36 20c0 6 2 11 6 14s8 5 12 5s10 1 17 1h541c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -18 -1z"
            },
            "&#x2277;": {
                x: 967,
                d: "M739 399l-550 -231c-9 -4 -16 -6 -19 -6c-5 0 -10 2 -14 6s-6 9 -6 14c0 4 2 7 4 10s4 6 6 7s7 3 13 6l516 217l-518 217c-14 6 -21 13 -21 22c0 5 2 10 6 14s9 6 14 6c1 0 7 -2 18 -6l551 -231c15 -6 22 -14 22 -23c0 -8 -7 -15 -22 -22zM728 -246l-557 233\nc-14 6 -21 13 -21 22c0 6 2 10 5 13s10 6 19 10l547 230c9 4 16 6 20 6c5 0 10 -2 14 -6s6 -9 6 -14c0 -8 -7 -15 -22 -22l-517 -217l517 -217c15 -6 22 -14 22 -23c0 -5 -2 -9 -6 -13s-9 -6 -14 -6c-2 0 -6 1 -13 4z"
            },
            "&#x2276;": {
                x: 990,
                d: "M816 639l-589 -218l589 -217c7 -3 12 -5 14 -6s4 -3 6 -6s4 -6 4 -10c0 -5 -2 -10 -6 -14s-9 -6 -15 -6c-2 0 -8 2 -18 6l-631 232c-13 5 -20 12 -20 22c0 9 7 17 21 22l630 231c10 4 16 5 18 5c6 0 11 -2 15 -6s6 -8 6 -13c0 -4 -2 -8 -4 -11s-4 -5 -6 -6s-7 -2 -14 -5z\nM762 9l-592 218c-13 5 -20 12 -20 21c0 13 6 20 19 20c3 0 10 -2 20 -6l627 -231c7 -3 12 -4 14 -5s4 -3 6 -6s4 -7 4 -11s-2 -8 -4 -11s-4 -5 -6 -6s-7 -2 -14 -5l-632 -233c-7 -3 -12 -4 -15 -4c-5 0 -9 2 -13 6s-6 8 -6 14c0 9 7 17 21 22z"
            },
            "&#x2273;": {
                x: 965,
                d: "M768 420l-553 -260c-9 -5 -15 -7 -18 -7c-5 0 -10 2 -14 6s-6 9 -6 14c0 9 6 16 19 22l525 248l-524 247c-1 0 -1 0 -2 1c-12 6 -18 13 -18 21c0 5 2 10 6 14s9 6 14 6c4 0 11 -2 20 -7l551 -260c13 -7 20 -14 20 -23s-7 -16 -20 -22zM815 -26c0 -51 -15 -97 -46 -138\ns-71 -62 -120 -62c-22 0 -45 6 -67 16s-40 20 -54 31s-32 27 -56 48c-20 18 -36 32 -48 41s-28 18 -48 27s-40 14 -60 14s-39 -4 -58 -13s-38 -26 -54 -50s-25 -54 -26 -90c0 -4 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 11 -14 32c0 51 15 97 46 138s71 62 120 62\nc22 0 44 -5 66 -15s40 -21 54 -32s33 -27 57 -48c20 -18 35 -31 47 -40s28 -19 48 -28s41 -14 61 -14c37 0 68 14 95 42s41 62 43 104c0 20 5 30 14 30s14 -11 14 -32z"
            },
            "&#x2272;": {
                x: 965,
                d: "M769 690l-525 -248l524 -247c13 -6 20 -13 20 -22c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -9 2 -18 7l-553 260c-13 6 -20 13 -20 22s7 17 20 23l553 261c8 4 14 6 18 6c5 0 10 -2 14 -6s6 -9 6 -14c0 -8 -6 -15 -19 -22zM815 -26c0 -51 -15 -97 -46 -138\ns-71 -62 -120 -62c-22 0 -45 6 -67 16s-40 20 -54 31s-32 27 -56 48c-20 18 -36 32 -48 41s-28 18 -48 27s-40 14 -60 14s-39 -4 -58 -13s-38 -26 -54 -50s-25 -54 -26 -90c0 -4 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 11 -14 32c0 51 15 97 46 138s71 62 120 62\nc22 0 44 -5 66 -15s40 -21 54 -32s33 -27 57 -48c20 -18 35 -31 47 -40s28 -19 48 -28s41 -14 61 -14c37 0 68 14 95 42s41 62 43 104c0 20 5 30 14 30s14 -11 14 -32z"
            },
            "&#x232e;": {
                x: 967,
                d: "M742 711l-525 -248l524 -247c13 -6 20 -13 20 -22c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -9 2 -18 7l-553 260c-13 6 -20 13 -20 22s7 17 20 23l553 261c8 4 14 6 18 6c5 0 10 -2 14 -6s6 -9 6 -14c0 -9 -6 -16 -19 -22zM726 -174h-250v-64c0 -25 -7 -38 -20 -38\nc-6 0 -11 2 -14 6s-6 6 -6 10v17v69h-251h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h250v154h-251h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h250v69v17s3 6 6 10s8 6 14 6c13 0 20 -13 20 -38v-64h249c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17h-250\nv-154h249c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17z"
            },
            "&#x232f;": {
                x: 967,
                d: "M741 441l-553 -260c-9 -5 -15 -7 -18 -7c-5 0 -10 2 -14 6s-6 9 -6 14c0 9 6 16 19 22l525 248l-524 247c-1 0 -1 0 -2 1c-12 6 -18 13 -18 21c0 5 2 10 6 14s9 6 14 6c4 0 11 -2 20 -7l551 -260c13 -7 20 -14 20 -23s-7 -16 -20 -22zM726 -174h-250v-64\nc0 -25 -7 -38 -20 -38c-6 0 -11 2 -14 6s-6 6 -6 10v17v69h-251h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h250v154h-251h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h250v69v17s3 6 6 10s8 6 14 6c13 0 20 -13 20 -38v-64h249c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14\ns-8 -6 -12 -6h-17h-250v-154h249c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17z"
            },
            "&#x226f;": {
                x: 967,
                d: "M742 228l-362 -171l-123 -241c-5 -9 -9 -15 -12 -18s-7 -4 -11 -4c-5 0 -10 2 -14 6s-6 8 -6 14c0 10 36 82 107 215l-81 -37c-5 -3 -17 -9 -36 -18s-31 -13 -34 -13c-5 0 -10 2 -14 6s-6 8 -6 13c0 9 7 17 20 23l181 85l133 261l-314 148c-13 6 -20 14 -20 23\nc0 5 2 9 6 13s9 6 14 6c4 0 10 -2 18 -6l314 -148l154 302c7 13 14 19 21 19c5 0 10 -2 14 -6s6 -8 6 -14c0 -4 -3 -11 -8 -20l-151 -298l203 -96c1 0 1 0 2 -1c12 -6 18 -13 18 -21c0 -9 -6 -17 -19 -22zM520 332l-111 -216l285 134z"
            },
            "&#x2271;": {
                x: 967,
                d: "M451 188l-118 -285h393c8 0 14 -1 18 -1s8 -2 12 -5s5 -8 5 -14c0 -13 -12 -20 -37 -20h-408l-59 -142c-7 -16 -14 -24 -23 -24c-6 0 -10 2 -14 6s-6 9 -6 14s20 54 59 146h-86c-25 0 -37 7 -37 20c0 6 2 11 6 14s7 5 11 5s10 1 18 1h104l109 260l-212 -100\nc-8 -4 -13 -6 -16 -6c-5 0 -10 2 -14 6s-6 9 -6 14c0 9 7 17 20 23l250 118l90 216l-338 160c-6 3 -10 4 -13 6s-5 4 -7 6s-2 6 -2 10c0 6 2 10 6 14s9 6 14 6s12 -2 21 -7l335 -158l136 326c4 4 9 6 15 6s11 -2 15 -6s5 -9 5 -14c0 -3 -2 -10 -6 -19l-129 -311l180 -84\nc13 -6 19 -13 19 -22c0 -6 -2 -10 -5 -13s-8 -6 -17 -10zM546 417l-72 -174l220 104z"
            },
            "&#x2270;": {
                x: 967,
                d: "M452 191l-119 -288h393c8 0 14 -1 18 -1s8 -2 12 -5s5 -8 5 -14c0 -13 -12 -20 -37 -20h-408l-59 -142c-7 -16 -14 -24 -23 -24c-6 0 -10 2 -14 6s-6 9 -6 14s20 54 59 146h-86c-25 0 -37 7 -37 20c0 6 2 11 6 14s7 5 11 5s10 1 18 1h104l128 305l-246 116\nc-14 7 -21 14 -21 23s7 16 21 23l389 183l102 244c4 4 9 6 15 6s11 -2 15 -6s5 -9 5 -14c0 -3 -2 -10 -6 -19l-77 -186l108 52c7 4 13 6 19 6c5 0 10 -2 14 -6s6 -8 6 -14c0 -5 -1 -10 -4 -12s-9 -5 -18 -10l-148 -71l-123 -295l271 -127c5 -3 9 -4 10 -5c2 -2 2 -2 6 -5\nc3 -2 4 -4 5 -6s1 -5 1 -8c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -49 21 -137 63zM537 498l-320 -151l215 -102z"
            },
            "&#x226e;": {
                x: 967,
                d: "M560 412l-132 -261c14 -7 48 -23 100 -48s101 -47 148 -69s72 -34 75 -36c7 -7 10 -12 10 -17c0 -7 -3 -12 -7 -15s-8 -5 -13 -5c-4 0 -12 3 -25 9l-36 17l-271 128c-101 -203 -155 -307 -161 -314c-3 -5 -8 -7 -14 -7c-5 0 -10 2 -14 6s-6 8 -6 14c0 5 5 16 14 33\nl145 285l-203 96c-1 0 -1 0 -2 1c-12 6 -18 13 -18 21s6 15 19 22l362 170l125 245c7 13 14 19 21 19c5 0 10 -2 14 -6s6 -8 6 -14c0 -4 -3 -11 -8 -20l-99 -196l135 64c8 3 13 5 16 5c5 0 9 -2 13 -5s7 -8 7 -15c0 -9 -7 -16 -20 -22zM502 384l-285 -134l174 -82z"
            },
            "&#x2331;": {
                x: 967,
                d: "M739 441l-235 -111l-92 -270h314h18s8 -3 12 -6s5 -8 5 -14c0 -13 -12 -20 -37 -20h-326l-52 -154h380h18s8 -3 12 -6s5 -8 5 -14c0 -13 -12 -20 -37 -20h-392l-76 -222c-5 -16 -12 -24 -22 -24c-6 0 -10 2 -14 6s-6 9 -6 14c0 3 2 10 5 20l71 206h-103\nc-25 0 -37 7 -37 20c0 6 2 11 6 14s7 6 11 6h18h118l53 154h-169c-25 0 -37 7 -37 20c0 6 2 11 6 14s7 6 11 6h18h185l84 246l-268 -126c-8 -4 -13 -6 -16 -6c-5 0 -10 2 -14 6s-6 9 -6 14c0 9 7 17 20 23l302 142l61 181l-361 171c-6 3 -10 5 -13 7s-5 4 -7 6s-2 5 -2 9\nc0 6 2 11 6 15s9 5 14 5s12 -2 21 -7l355 -168l109 318c5 16 12 24 22 24c6 0 11 -2 15 -6s5 -9 5 -14c0 -3 -2 -10 -5 -20l-109 -319l159 -75c13 -7 19 -14 19 -22c0 -6 -2 -10 -5 -13s-8 -6 -17 -10zM694 464l-124 59l-48 -141z"
            },
            "&#x2330;": {
                x: 967,
                d: "M613 651l-110 -323l127 -59l124 -59c5 -9 7 -15 7 -16c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -22 8 -57 25l-62 30c-14 7 -27 13 -40 19s-22 10 -30 14s-15 7 -22 10s-12 6 -16 8s-7 3 -10 4s-4 2 -6 3s-3 1 -4 2s-3 1 -4 2l-78 -231h314h18s8 -3 12 -6s5 -8 5 -14\nc0 -13 -12 -20 -37 -20h-326l-52 -154h380h18s8 -3 12 -6s5 -8 5 -14c0 -13 -12 -20 -37 -20h-392l-76 -222c-5 -16 -12 -24 -22 -24c-6 0 -10 2 -14 6s-6 9 -6 14c0 3 2 10 5 20l71 206h-103c-25 0 -37 7 -37 20c0 6 2 11 6 14s7 6 11 6h18h118l53 154h-169\nc-25 0 -37 7 -37 20c0 6 2 11 6 14s7 6 11 6h18h185l84 248l-283 133c-14 7 -21 14 -21 23s7 16 21 23l410 193l74 216c5 16 12 24 22 24c6 0 11 -2 15 -6s5 -9 5 -14c0 -3 -2 -10 -5 -20l-60 -176c68 33 104 49 109 49s10 -1 14 -5s6 -9 6 -15c0 -5 -1 -9 -4 -11\ns-9 -6 -18 -11zM217 464l250 -118l96 281z"
            },
            "&#x2332;": {
                x: 967,
                d: "M591 523l-123 -295l271 -127c5 -3 9 -4 10 -5c2 -2 2 -2 6 -5c3 -2 4 -4 5 -6s1 -5 1 -8c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -49 21 -137 63l-152 71l-51 -125l340 -160c13 -6 20 -14 20 -23c0 -5 -2 -10 -6 -14s-9 -6 -14 -6c-3 0 -13 4 -30 13l-250 118\nc-2 1 -9 4 -22 10s-24 11 -35 16s-16 8 -17 8c-2 0 -5 -4 -8 -13l-130 -312c-4 -4 -9 -6 -15 -6s-10 2 -14 6s-6 9 -6 14c0 3 2 10 6 19l129 311l-172 81c-9 4 -14 6 -17 8s-5 4 -7 7s-3 6 -3 10c0 5 2 10 6 14s9 6 14 6c1 0 16 -7 45 -20c4 -2 13 -7 27 -13s22 -10 26 -11\nl97 -46l52 125l-246 116c-14 7 -21 14 -21 23s7 16 21 23l389 183l102 244c4 4 9 6 15 6s11 -2 15 -6s5 -9 5 -14c0 -3 -2 -10 -6 -19l-77 -186l108 52c7 4 13 6 19 6c5 0 10 -2 14 -6s6 -8 6 -14c0 -5 -1 -10 -4 -12s-9 -5 -18 -10zM217 347l215 -102l105 253z"
            },
            "&#x2333;": {
                x: 967,
                d: "M739 130l-388 -183l-102 -244c-4 -4 -9 -6 -15 -6s-10 2 -14 6s-6 9 -6 14c0 3 2 10 6 19l77 185c-81 -39 -124 -58 -127 -58c-5 0 -10 2 -14 6s-6 9 -6 14c0 6 1 11 4 13s11 7 25 14l140 65c1 2 7 17 19 45s23 56 36 86s20 49 23 56l-211 -99c-8 -4 -13 -6 -16 -6\nc-5 0 -10 2 -14 6s-6 9 -6 14c0 9 7 17 20 23l250 118l90 216l-338 160c-6 3 -10 4 -13 6s-5 4 -7 6s-2 6 -2 10c0 6 2 10 6 14s9 6 14 6s12 -2 21 -7l335 -158l136 326c4 4 9 6 15 6s11 -2 15 -6s5 -9 5 -14c0 -3 -2 -10 -6 -19l-129 -311l180 -84c13 -6 19 -13 19 -22\nc0 -6 -2 -10 -5 -13s-8 -6 -17 -10l-288 -136l-77 -186l86 40l100 48l142 67c23 11 36 16 39 16c5 0 10 -2 14 -6s6 -9 6 -14c0 -6 -2 -10 -5 -13s-8 -6 -17 -10zM546 417l-72 -174l220 104z"
            },
            "&#x226c;": {
                x: 651,
                d: "M346 -129c33 -35 72 -66 118 -93c9 -5 14 -10 14 -15c0 -9 -4 -13 -12 -13c-3 0 -12 4 -26 12s-31 20 -53 37s-43 34 -62 52c-18 -18 -38 -35 -60 -51s-40 -30 -54 -38s-23 -12 -26 -12c-8 0 -12 4 -12 13c0 4 4 9 12 14c49 29 89 61 120 94c-17 20 -32 39 -44 55\ns-28 41 -48 74s-36 71 -47 114s-16 89 -16 136s5 93 16 136s27 81 47 114s36 58 48 74s27 35 44 55c-33 35 -72 66 -118 93c-9 5 -14 10 -14 15c0 9 4 13 12 13c3 0 11 -4 25 -12s32 -20 54 -37s43 -34 62 -52c18 18 38 35 60 51s40 30 54 38s23 12 26 12c8 0 12 -4 12 -13\nc0 -5 -4 -9 -12 -14c-49 -29 -89 -61 -120 -94c17 -20 32 -39 44 -55s28 -41 48 -74s35 -71 46 -114s17 -89 17 -136s-6 -93 -17 -136s-26 -81 -46 -114s-36 -58 -48 -74s-27 -35 -44 -55zM326 -107c19 22 37 47 54 74s33 67 50 120s26 107 26 163c0 45 -5 88 -15 130\ns-23 78 -39 108s-29 54 -41 72s-24 34 -36 47c-19 -22 -37 -47 -54 -74s-34 -67 -51 -120s-25 -107 -25 -163c0 -45 5 -88 15 -130s22 -78 38 -108s30 -54 42 -72s24 -34 36 -47z"
            },
            "&#x2280;": {
                x: 967,
                d: "M455 206l-206 -406c-4 -4 -9 -6 -15 -6s-10 2 -14 6s-6 9 -6 14c0 4 3 11 8 20l192 379c-68 11 -141 17 -220 17h-24s-10 3 -14 6s-6 7 -6 14c0 6 2 11 6 14s8 5 12 5s10 1 18 1c120 0 209 8 268 24c1 1 4 6 8 13l200 393c4 4 9 6 15 6s11 -2 15 -6s5 -9 5 -14\nc0 -4 -3 -11 -8 -20l-181 -358c36 11 67 24 94 40s48 30 62 44s25 31 34 50s14 32 16 42s5 22 7 36c2 13 9 19 20 19c13 0 20 -7 20 -21c0 -25 -8 -55 -25 -89c-22 -44 -54 -79 -95 -105s-94 -48 -159 -65l-4 -8l-3 -6c-1 -1 -1 -2 -1 -3c39 -8 74 -19 106 -33\ns58 -28 77 -43s37 -32 51 -50s24 -34 30 -48s12 -28 16 -43s6 -25 6 -30s1 -10 1 -13c0 -7 -3 -12 -7 -16s-8 -6 -13 -6c-11 0 -18 9 -21 28c-17 111 -106 184 -265 218z"
            },
            "&#x2281;": {
                x: 967,
                d: "M455 206l-206 -406c-4 -4 -9 -6 -15 -6s-10 2 -14 6s-6 9 -6 14c0 1 6 14 17 39l67 131c5 10 4 10 18 37c9 18 17 33 24 47s11 21 12 22l51 102c-36 -11 -67 -24 -94 -40s-47 -30 -61 -44s-25 -31 -34 -50s-16 -33 -18 -43s-4 -22 -6 -37c-2 -12 -9 -18 -20 -18\nc-5 0 -10 2 -14 6s-6 9 -6 16c0 26 8 56 25 89c23 45 55 81 97 107s94 47 157 63l4 8l3 6c1 1 1 2 1 3c-39 8 -75 19 -107 33s-57 28 -76 43s-36 32 -50 50s-26 34 -32 48s-11 28 -15 43s-7 25 -7 30v13c0 14 7 21 20 21c5 0 9 -2 12 -5s5 -5 6 -8s2 -8 3 -14\nc17 -112 106 -185 265 -218l206 406c4 4 9 6 15 6s11 -2 15 -6s5 -9 5 -14c0 -4 -3 -11 -8 -20l-192 -379c75 -11 149 -17 220 -17h25s9 -3 13 -6s6 -7 6 -14c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-18c-103 0 -193 -8 -270 -24z"
            },
            "&#x22e0;": {
                x: 967,
                d: "M495 293l-162 -390h393c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-410l-59 -142c-7 -16 -14 -24 -23 -24c-6 0 -10 2 -14 6s-6 9 -6 14s20 54 59 146h-88c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14s8 5 12 5s10 1 17 1\nh104l167 400c-75 16 -161 24 -258 24c-13 0 -23 0 -28 1s-10 2 -14 5s-6 7 -6 14c0 9 4 15 10 17s19 3 38 3c115 0 214 11 299 34l165 396c4 4 9 6 15 6s11 -2 15 -6s5 -9 5 -14c0 -3 -2 -10 -6 -19l-144 -346c35 14 65 30 89 48s41 36 52 56s19 38 23 50s7 27 10 45\nc2 13 9 19 20 19c13 0 20 -7 20 -21c0 -27 -9 -59 -28 -96c-35 -69 -104 -119 -207 -150l-15 -39c77 -22 138 -55 183 -100c23 -24 39 -51 50 -82s17 -54 17 -69c0 -7 -3 -13 -7 -17s-8 -5 -13 -5c-11 0 -18 9 -21 28c-16 103 -91 172 -225 208zM442 346l30 -6l5 15l-35 -8\nv-1z"
            },
            "&#x22e1;": {
                x: 967,
                d: "M502 311l-169 -408h393c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-410l-59 -142c-7 -16 -14 -24 -23 -24c-6 0 -10 2 -14 6s-6 9 -6 14s20 54 59 146h-88c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14s8 5 12 5s10 1 17 1\nh104l166 399c-77 -14 -139 -40 -186 -79c-43 -35 -70 -84 -79 -148c-2 -12 -9 -18 -20 -18c-5 0 -10 1 -14 5s-6 10 -6 17c0 25 8 54 24 87c21 43 54 80 99 110s110 54 195 70v1c-143 29 -239 83 -286 164c-9 15 -16 31 -21 49s-8 31 -9 39s-2 13 -2 16c0 14 7 21 20 21\nc5 0 9 -1 12 -4s5 -7 6 -10s2 -7 3 -13c9 -61 38 -109 87 -145s120 -63 212 -79l172 412c4 4 9 6 15 6s11 -2 15 -6s5 -9 5 -14c0 -3 -2 -10 -6 -19l-160 -385c40 -5 70 -8 91 -9l109 -3c20 0 30 -7 30 -20c0 -9 -3 -15 -9 -17s-17 -3 -32 -3c-86 0 -159 -5 -218 -16z"
            },
            "&#x227a;": {
                x: 967,
                d: "M439 249c97 -19 172 -49 225 -90c27 -21 49 -47 65 -77s25 -52 28 -66s4 -26 4 -34c0 -15 -7 -22 -20 -22c-11 0 -18 7 -21 22c-2 14 -4 26 -6 35s-9 23 -18 42s-20 35 -34 49s-33 28 -60 44s-58 29 -94 40c-81 23 -183 36 -308 37c-15 0 -24 0 -30 1s-10 2 -14 5\ns-6 7 -6 14c0 13 10 20 29 20c164 1 292 20 383 57s143 98 157 184c3 19 10 29 22 29c5 0 10 -2 14 -6s6 -9 6 -16s-2 -21 -7 -40s-11 -37 -20 -54c-48 -88 -146 -146 -295 -174z"
            },
            "&#x227b;": {
                x: 967,
                d: "M472 249c-57 11 -105 26 -146 45s-72 38 -93 58s-38 42 -53 67c-9 15 -16 33 -22 53s-8 36 -8 45c0 7 2 12 6 16s9 6 14 6c11 0 18 -8 21 -24c9 -53 30 -96 63 -129c77 -77 230 -116 457 -117c15 0 25 0 31 -1s10 -2 14 -5s5 -7 5 -14c0 -13 -10 -20 -29 -20\nc-144 -1 -256 -14 -335 -39c-35 -11 -65 -24 -91 -40s-46 -30 -59 -44s-24 -29 -33 -48s-15 -33 -17 -42s-4 -21 -7 -36c-1 -13 -8 -20 -20 -20c-13 0 -20 7 -20 22c0 7 1 18 4 32s12 37 28 67s37 56 64 77c52 41 127 71 226 91z"
            },
            "&#x227c;": {
                x: 967,
                d: "M188 310h29c26 0 64 2 115 6c49 5 92 12 132 21s72 19 98 29s49 24 69 39s35 29 45 41s19 27 26 44s12 29 14 38s4 21 6 35c0 3 2 6 5 10s8 6 14 6c7 0 12 -3 15 -7s5 -9 5 -14c0 -33 -12 -70 -36 -110c-47 -78 -143 -131 -288 -159c98 -16 177 -49 238 -98\nc23 -20 43 -45 57 -73s22 -49 25 -64s4 -26 4 -33c0 -6 -3 -11 -7 -15s-8 -6 -13 -6c-11 0 -18 9 -21 26c-15 107 -95 179 -240 214c-81 19 -167 29 -256 29c-35 0 -54 0 -59 1c-10 5 -15 12 -15 20c0 13 13 20 38 20zM650 -133c0 -7 -2 -11 -6 -15s-9 -6 -14 -6\nc-11 0 -18 8 -21 25c-7 47 -25 87 -55 120s-68 58 -112 75s-88 30 -131 38s-86 11 -131 11c-20 0 -30 7 -30 20c0 9 3 15 9 17s16 3 31 3c123 0 228 -26 317 -77c26 -15 49 -34 68 -54s34 -38 43 -54s17 -34 22 -52s8 -29 9 -36s1 -12 1 -15z"
            },
            "&#x227d;": {
                x: 967,
                d: "M694 310h29c25 0 38 -7 38 -20c0 -7 -3 -11 -7 -14s-9 -5 -14 -6s-14 -1 -27 -1c-115 0 -211 -10 -289 -31c-48 -13 -88 -28 -120 -47s-55 -39 -70 -61s-26 -42 -31 -57s-10 -34 -13 -55c-2 -12 -9 -18 -20 -18c-5 0 -10 2 -14 6s-6 9 -6 15c0 33 12 70 36 110\nc47 79 142 131 285 158v1c-96 16 -174 49 -235 98c-23 20 -42 44 -56 72s-23 49 -26 64s-4 26 -4 34c0 14 7 21 20 21c11 0 18 -9 21 -26c17 -108 97 -179 240 -214c81 -19 169 -29 263 -29zM761 135c0 -13 -9 -20 -26 -20c-37 0 -74 -2 -109 -7s-72 -12 -109 -24\ns-70 -27 -99 -44s-55 -41 -76 -70s-34 -61 -40 -98c-3 -17 -10 -26 -21 -26c-5 0 -9 2 -13 6s-7 8 -7 15c0 4 0 9 1 15s4 19 10 38s12 37 22 54s26 35 48 56s47 40 76 56c87 46 188 69 303 69c11 0 19 -1 23 -1s8 -1 12 -4s5 -8 5 -15z"
            },
            "&#x227e;": {
                x: 965,
                d: "M815 -26c0 -51 -15 -97 -46 -138s-71 -62 -120 -62c-22 0 -45 6 -67 16s-40 20 -54 31s-32 27 -56 48c-20 18 -36 32 -48 41s-28 18 -48 27s-40 14 -60 14s-39 -4 -58 -13s-38 -26 -54 -50s-25 -54 -26 -90c0 -4 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 11 -14 32\nc0 51 15 97 46 138s71 62 120 62c22 0 44 -5 66 -15s40 -21 54 -32s33 -27 57 -48c20 -18 35 -31 47 -40s28 -19 48 -28s41 -14 61 -14c37 0 68 14 95 42s41 62 43 104c0 20 5 30 14 30s14 -11 14 -32zM467 442c150 -29 248 -87 294 -173c18 -35 27 -66 27 -95\nc0 -14 -7 -21 -20 -21c-11 0 -18 9 -21 26c-7 45 -26 86 -57 121s-83 64 -156 85c-81 23 -183 36 -307 37c-22 0 -33 0 -34 1c-3 0 -5 1 -8 4c-5 3 -8 8 -8 15c0 13 10 20 29 20c127 0 228 10 305 31c39 10 72 22 101 37s51 29 67 43s29 30 39 49s18 34 21 47s6 28 9 45\nc3 12 9 18 20 18c5 0 10 -2 14 -6s6 -9 6 -15c0 -27 -8 -57 -24 -88c-22 -43 -55 -81 -100 -111s-111 -53 -197 -70z"
            },
            "&#x227f;": {
                x: 965,
                d: "M815 -26c0 -51 -15 -97 -46 -138s-71 -62 -120 -62c-22 0 -45 6 -67 16s-40 20 -54 31s-32 27 -56 48c-20 18 -36 32 -48 41s-28 18 -48 27s-40 14 -60 14s-39 -4 -58 -13s-38 -26 -54 -50s-25 -54 -26 -90c0 -4 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 11 -14 32\nc0 51 15 97 46 138s71 62 120 62c22 0 44 -5 66 -15s40 -21 54 -32s33 -27 57 -48c20 -18 35 -31 47 -40s28 -19 48 -28s41 -14 61 -14c37 0 68 14 95 42s41 62 43 104c0 20 5 30 14 30s14 -11 14 -32zM740 422c-61 0 -119 -3 -172 -10s-106 -18 -158 -34s-94 -40 -128 -72\ns-55 -71 -62 -117c-2 -14 -4 -24 -7 -29s-8 -7 -16 -7c-13 0 -20 7 -20 21c0 34 12 71 37 111c47 77 141 129 281 156v1c-95 19 -170 49 -223 92c-27 21 -48 47 -64 78s-26 54 -28 68s-3 24 -3 31c0 6 2 11 6 15s9 6 14 6c11 0 18 -8 21 -23c9 -67 43 -120 100 -159\ns142 -65 256 -78c53 -7 108 -10 166 -10h13h17s8 -3 12 -6s6 -8 6 -14s-2 -11 -6 -14s-8 -6 -12 -6h-17h-13z"
            },
            "&#x2282;": {
                x: 967,
                d: "M726 500h-267c-77 0 -141 -25 -192 -74s-77 -108 -77 -176s26 -127 77 -176s115 -74 192 -74h267h17s8 -3 12 -6s6 -8 6 -14s-2 -11 -6 -14s-8 -6 -12 -6h-17h-269c-85 0 -157 28 -217 85s-90 125 -90 205s30 148 90 205s132 85 217 85h269h17s8 -3 12 -6s6 -8 6 -14\ns-2 -11 -6 -14s-8 -6 -12 -6h-17z"
            },
            "&#x2283;": {
                x: 967,
                d: "M452 500h-267h-17s-8 3 -12 6s-6 8 -6 14s2 11 6 14s8 6 12 6h17h269c85 0 158 -28 218 -85s89 -125 89 -205s-29 -148 -89 -205s-133 -85 -218 -85h-269h-17s-8 3 -12 6s-6 8 -6 14s2 11 6 14s8 6 12 6h17h267c77 0 141 25 192 74s77 108 77 176s-26 127 -77 176\ns-115 74 -192 74z"
            },
            "&#x2288;": {
                x: 967,
                d: "M621 596l-206 -496c13 -2 28 -3 45 -3h266c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-269c-21 0 -41 2 -58 5l-66 -159h393c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-410l-59 -142\nc-7 -16 -14 -24 -23 -24c-6 0 -10 2 -14 6s-6 9 -6 14s20 54 59 146h-68c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14s8 5 12 5s10 1 17 1h84l71 169c-57 16 -107 48 -148 98s-62 109 -62 177c0 81 30 149 90 205s131 84 216 84h138l68 161c4 4 9 6 15 6s11 -2 15 -6\ns5 -9 5 -14c0 -3 -2 -10 -6 -19l-53 -128h88h17s8 -3 12 -6s6 -8 6 -14s-2 -11 -6 -14s-8 -6 -12 -6h-17h-105zM578 596h-119c-77 0 -141 -25 -192 -74s-77 -107 -77 -176c0 -57 18 -107 53 -150s79 -72 132 -87z"
            },
            "&#x2289;": {
                x: 967,
                d: "M691 764l-72 -173c39 -23 73 -56 101 -99s41 -91 41 -146c0 -81 -29 -149 -89 -205s-132 -84 -217 -84h-58l-64 -154h373c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-390l-59 -142c-7 -16 -14 -24 -23 -24c-6 0 -10 2 -14 6\ns-6 9 -6 14s20 54 59 146h-88c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14s8 5 12 5s10 1 17 1h104l65 154h-169c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14s8 5 12 5s10 1 17 1h185l198 476c-37 15 -76 23 -117 23h-266h-17s-8 3 -12 6s-6 8 -6 14\ns2 11 6 14s8 6 12 6h17h269c48 0 91 -9 130 -27l78 188c4 4 9 6 15 6s11 -2 15 -6s5 -9 5 -14c0 -3 -2 -10 -6 -19zM603 554l-189 -457c31 0 56 1 74 2s41 7 69 17s54 26 77 46c58 49 87 111 87 185c0 38 -10 76 -29 113s-48 68 -89 94z"
            },
            "&#x2286;": {
                x: 967,
                d: "M726 596h-267c-77 0 -141 -25 -192 -74s-77 -107 -77 -176c0 -68 25 -127 76 -176s116 -73 193 -73h267c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-269c-85 0 -157 29 -217 85s-90 124 -90 205c0 79 30 147 90 204\ns132 85 217 85h269h17s8 -3 12 -6s6 -8 6 -14s-2 -11 -6 -14s-8 -6 -12 -6h-17zM205 -97h521c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-521c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14s8 5 12 5s10 1 17 1z"
            },
            "&#x2287;": {
                x: 967,
                d: "M452 596h-267h-17s-8 3 -12 6s-6 8 -6 14s2 11 6 14s8 6 12 6h17h269c85 0 157 -28 217 -84s90 -125 90 -206c0 -79 -29 -147 -89 -204s-133 -85 -218 -85h-269c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14s8 5 12 5s10 1 17 1h267c77 0 141 24 192 73\ns77 108 77 177c0 68 -26 126 -77 175s-115 74 -192 74zM706 -137h-521c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14s8 5 12 5s10 1 17 1h521c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1z"
            },
            "&#x228a;": {
                x: 967,
                d: "M726 596h-267c-77 0 -141 -25 -192 -74s-77 -107 -77 -176c0 -67 25 -126 76 -175s115 -74 194 -74h266c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-269c-85 0 -157 28 -217 84s-90 125 -90 206s30 149 90 205s131 84 216 84h270\nh17s8 -3 12 -6s6 -8 6 -14s-2 -11 -6 -14s-8 -6 -12 -6h-17zM726 -137h-263l-85 -78c-14 -13 -24 -19 -29 -19c-13 0 -20 7 -20 20c0 5 5 12 14 23c1 1 5 4 12 10s15 13 25 23s18 17 23 21h-198c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14s8 5 12 5s10 1 17 1h243\nl85 78c14 13 24 19 29 19s9 -1 12 -4s5 -5 6 -8s2 -6 2 -8c0 -6 -5 -14 -14 -23c-1 -1 -5 -4 -12 -10s-16 -13 -26 -23s-17 -17 -22 -21h218c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1z"
            },
            "&#x228b;": {
                x: 967,
                d: "M451 596h-266h-17s-8 3 -12 6s-6 8 -6 14s2 11 6 14s8 6 12 6h17h269c85 0 158 -28 218 -84s89 -125 89 -206s-29 -149 -89 -205s-132 -84 -217 -84h-270c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14s8 5 12 5s10 1 17 1h267c77 0 141 24 192 73s77 108 77 177\nc0 67 -25 126 -76 175s-115 74 -194 74zM706 -137h-242c-45 -51 -68 -77 -70 -79c-11 -12 -20 -18 -25 -18c-13 0 -20 7 -20 20c0 4 4 11 12 21c3 5 11 13 23 27s21 24 26 29h-225c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14s8 5 12 5s10 1 17 1h262\nc31 35 54 61 70 78c12 13 20 19 25 19s9 -1 12 -4s5 -5 6 -8s2 -6 2 -8c0 -6 -4 -13 -12 -21l-49 -56h205c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1z"
            },
            "&#x2ab7;": {
                x: 965,
                d: "M467 442c150 -29 248 -87 294 -173c18 -35 27 -66 27 -95c0 -14 -7 -21 -20 -21c-11 0 -18 9 -21 26c-7 45 -26 86 -57 121s-83 64 -156 85c-81 23 -183 36 -307 37c-22 0 -33 0 -34 1c-3 0 -5 1 -8 4c-5 3 -8 8 -8 15c0 13 10 20 29 20c127 0 228 10 305 31\nc39 10 72 22 101 37s51 29 67 43s29 30 39 49s18 34 21 47s6 28 9 45c3 12 9 18 20 18c5 0 10 -2 14 -6s6 -9 6 -15c0 -27 -8 -57 -24 -88c-22 -43 -55 -81 -100 -111s-111 -53 -197 -70zM815 -125c0 -45 -16 -83 -48 -115s-71 -47 -118 -47c-20 0 -41 3 -62 11\ns-38 15 -50 22s-28 17 -48 30c-29 19 -50 33 -64 41s-31 16 -51 23s-39 11 -58 11c-32 0 -63 -10 -92 -29s-44 -48 -46 -85c0 -4 -2 -9 -4 -15s-5 -8 -10 -8c-9 0 -14 10 -14 30c0 45 16 84 48 116s72 47 118 47c20 0 41 -3 62 -11s38 -15 50 -22s28 -17 48 -30\nc29 -19 50 -33 64 -41s31 -16 51 -23s39 -11 58 -11c36 0 67 10 94 31s41 46 44 74c1 21 6 32 14 32c9 0 14 -10 14 -31zM815 108c0 -45 -16 -82 -48 -114s-71 -48 -118 -48c-20 0 -41 4 -62 12s-38 14 -50 21s-28 17 -48 30c-29 19 -50 33 -64 41s-31 16 -51 23\ns-39 11 -58 11c-32 0 -63 -10 -92 -29s-44 -48 -46 -85c0 -4 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 10 -14 30c0 45 16 83 48 115s72 48 118 48c20 0 41 -4 62 -12s38 -14 50 -21s28 -17 48 -30c29 -19 50 -33 64 -41s31 -16 51 -23s39 -11 58 -11c36 0 67 11 94 32\ns41 45 44 73c1 21 6 32 14 32c9 0 14 -10 14 -31z"
            },
            "&#x2ab8;": {
                x: 965,
                d: "M740 422c-61 0 -119 -3 -172 -10s-106 -18 -158 -34s-94 -40 -128 -72s-55 -71 -62 -117c-2 -14 -4 -24 -7 -29s-8 -7 -16 -7c-13 0 -20 7 -20 21c0 34 12 71 37 111c47 77 141 129 281 156v1c-95 19 -170 49 -223 92c-27 21 -48 47 -64 78s-26 54 -28 68s-3 24 -3 31\nc0 6 2 11 6 15s9 6 14 6c11 0 18 -8 21 -23c9 -67 43 -120 100 -159s142 -65 256 -78c53 -7 108 -10 166 -10h13h17s8 -3 12 -6s6 -8 6 -14s-2 -11 -6 -14s-8 -6 -12 -6h-17h-13zM815 -125c0 -45 -16 -83 -48 -115s-71 -47 -118 -47c-20 0 -41 3 -62 11s-38 15 -50 22\ns-28 17 -48 30c-29 19 -50 33 -64 41s-31 16 -51 23s-39 11 -58 11c-32 0 -63 -10 -92 -29s-44 -48 -46 -85c0 -4 -2 -9 -4 -15s-5 -8 -10 -8c-9 0 -14 10 -14 30c0 45 16 84 48 116s72 47 118 47c20 0 41 -3 62 -11s38 -15 50 -22s28 -17 48 -30c29 -19 50 -33 64 -41\ns31 -16 51 -23s39 -11 58 -11c36 0 67 10 94 31s41 46 44 74c1 21 6 32 14 32c9 0 14 -10 14 -31zM815 108c0 -45 -16 -82 -48 -114s-71 -48 -118 -48c-20 0 -41 4 -62 12s-38 14 -50 21s-28 17 -48 30c-29 19 -50 33 -64 41s-31 16 -51 23s-39 11 -58 11\nc-32 0 -63 -10 -92 -29s-44 -48 -46 -85c0 -4 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 10 -14 30c0 45 16 83 48 115s72 48 118 48c20 0 41 -4 62 -12s38 -14 50 -21s28 -17 48 -30c29 -19 50 -33 64 -41s31 -16 51 -23s39 -11 58 -11c36 0 67 11 94 32s41 45 44 73\nc1 21 6 32 14 32c9 0 14 -10 14 -31z"
            },
            "&#x2aaf;": {
                x: 967,
                d: "M439 346c97 -19 172 -49 225 -90c27 -21 49 -47 65 -77s25 -53 28 -67s4 -25 4 -33c0 -15 -7 -22 -20 -22c-11 0 -18 7 -21 22c-2 13 -4 25 -6 35s-8 24 -17 42s-21 34 -35 48s-33 30 -60 46s-58 28 -94 39c-81 23 -183 36 -308 37c-15 0 -24 0 -30 1s-10 2 -14 5\ns-6 7 -6 14c0 13 10 20 29 20c164 1 292 20 383 57s143 98 157 184c3 19 10 29 22 29c5 0 10 -2 14 -6s6 -9 6 -16s-2 -21 -7 -40s-11 -37 -20 -54c-48 -88 -146 -146 -295 -174zM185 -97h541c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5\ns-10 -1 -17 -1h-541c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14s8 5 12 5s10 1 17 1z"
            },
            "&#x2ab0;": {
                x: 967,
                d: "M472 346c-57 11 -105 25 -146 44s-72 38 -93 58s-38 43 -53 68c-9 15 -16 34 -22 54s-8 35 -8 44c0 7 2 12 6 16s9 6 14 6c11 0 18 -8 21 -24c9 -53 30 -96 63 -129c77 -77 230 -116 457 -117c15 0 25 0 31 -1s10 -2 14 -5s5 -7 5 -14c0 -13 -10 -20 -29 -20\nc-144 -1 -256 -14 -335 -39c-34 -11 -63 -24 -89 -39s-46 -30 -60 -44s-25 -28 -34 -46s-14 -32 -16 -42s-5 -21 -7 -34c-2 -17 -9 -25 -21 -25c-13 0 -20 7 -20 22c0 7 1 19 4 33s12 36 28 66s37 56 64 77c52 41 127 71 226 91zM185 -97h541c7 0 13 -1 17 -1s8 -2 12 -5\ns6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1h-541c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14s2 11 6 14s8 5 12 5s10 1 17 1z"
            },
            "&#x2ab9;": {
                x: 965,
                d: "M439 -114l60 117c-45 30 -79 51 -105 63s-52 18 -78 18c-19 0 -38 -3 -57 -10s-37 -20 -54 -38s-26 -40 -27 -66c0 -15 -5 -23 -14 -23s-14 10 -14 30c0 46 16 85 48 116s72 47 118 47c33 0 71 -11 114 -34c10 -5 24 -14 41 -26s34 -22 50 -32c5 9 16 29 31 60\ns24 48 27 52c6 11 10 17 12 19s6 3 11 3c13 0 20 -7 20 -20c0 -4 -5 -16 -16 -35c-5 -10 -10 -21 -16 -34s-13 -25 -20 -39s-12 -23 -14 -27c32 -17 63 -25 93 -25c35 0 66 10 93 31s42 45 45 74c1 21 6 32 14 32c9 0 14 -10 14 -31c0 -45 -16 -82 -48 -114\ns-71 -48 -118 -48c-33 0 -68 10 -105 29c-5 3 -8 5 -10 5l-8 -13l-60 -117c45 -30 80 -51 106 -63s51 -18 77 -18c35 0 66 10 93 31s42 45 45 74c1 21 6 32 14 32c9 0 14 -10 14 -31c0 -45 -16 -83 -48 -115s-71 -47 -118 -47c-33 0 -71 11 -114 34c-10 5 -24 13 -41 25\ns-34 23 -50 33c-5 -9 -16 -29 -31 -60s-24 -48 -27 -52c-6 -11 -10 -17 -12 -19s-6 -3 -11 -3c-13 0 -20 7 -20 20c0 4 5 16 16 35c5 10 11 21 17 34s13 26 20 40l13 26c-32 17 -63 25 -93 25c-19 0 -38 -4 -57 -11s-37 -19 -54 -37s-26 -40 -27 -66c0 -15 -5 -23 -14 -23\ns-14 10 -14 30c0 46 16 85 48 116s72 47 118 47c33 0 68 -10 105 -29c5 -3 8 -5 10 -5zM507 502c88 -15 157 -44 207 -89c21 -19 38 -41 50 -67s19 -44 21 -56s3 -21 3 -26c0 -14 -7 -21 -20 -21c-7 0 -11 2 -14 6s-5 8 -5 11c-3 15 -5 27 -8 36s-8 23 -17 41s-21 33 -34 45\ns-31 24 -56 38s-55 25 -88 33c-82 19 -173 29 -274 29h-57c-25 0 -38 7 -38 20c0 7 3 12 7 15s9 5 14 5s12 1 22 1c137 0 239 8 307 24c37 9 70 19 97 33s48 28 62 40s26 26 36 44s16 32 18 42s5 23 8 38c2 12 9 18 20 18c5 0 10 -2 14 -6s6 -9 6 -15s-2 -16 -4 -29\ns-9 -32 -22 -58s-29 -48 -50 -65c-49 -42 -118 -71 -205 -87z"
            },
            "&#x2aba;": {
                x: 965,
                d: "M439 -114l60 117c-45 30 -79 51 -105 63s-52 18 -78 18c-19 0 -38 -3 -57 -10s-37 -20 -54 -38s-26 -40 -27 -66c0 -15 -5 -23 -14 -23s-14 10 -14 30c0 46 16 85 48 116s72 47 118 47c33 0 71 -11 114 -34c10 -5 24 -14 41 -26s34 -22 50 -32c5 9 16 29 31 60\ns24 48 27 52c6 11 10 17 12 19s6 3 11 3c13 0 20 -7 20 -20c0 -4 -5 -16 -16 -35c-5 -10 -10 -21 -16 -34s-13 -25 -20 -39s-12 -23 -14 -27c32 -17 63 -25 93 -25c35 0 66 10 93 31s42 45 45 74c1 21 6 32 14 32c9 0 14 -10 14 -31c0 -45 -16 -82 -48 -114\ns-71 -48 -118 -48c-33 0 -68 10 -105 29c-5 3 -8 5 -10 5l-8 -13l-60 -117c45 -30 80 -51 106 -63s51 -18 77 -18c35 0 66 10 93 31s42 45 45 74c1 21 6 32 14 32c9 0 14 -10 14 -31c0 -45 -16 -83 -48 -115s-71 -47 -118 -47c-33 0 -71 11 -114 34c-10 5 -24 13 -41 25\ns-34 23 -50 33c-5 -9 -16 -29 -31 -60s-24 -48 -27 -52c-6 -11 -10 -17 -12 -19s-6 -3 -11 -3c-13 0 -20 7 -20 20c0 4 5 16 16 35c5 10 11 21 17 34s13 26 20 40l13 26c-32 17 -63 25 -93 25c-19 0 -38 -4 -57 -11s-37 -19 -54 -37s-26 -40 -27 -66c0 -15 -5 -23 -14 -23\ns-14 10 -14 30c0 46 16 85 48 116s72 47 118 47c33 0 68 -10 105 -29c5 -3 8 -5 10 -5zM693 482c-101 0 -187 -8 -259 -25c-36 -9 -67 -20 -94 -33s-48 -26 -62 -38s-26 -28 -35 -46s-15 -32 -18 -42s-5 -22 -8 -38c-3 -11 -9 -17 -20 -17c-13 0 -20 7 -20 21c0 6 1 16 3 29\ns10 33 23 59s29 47 50 64c49 42 117 71 202 86v1c-86 15 -154 44 -204 89c-21 19 -37 40 -49 66s-20 45 -22 57s-3 21 -3 26c0 6 2 11 6 15s9 6 14 6c11 0 17 -6 19 -17c5 -25 11 -47 18 -64s20 -37 42 -59s50 -39 84 -52s80 -24 142 -33s135 -14 219 -14c30 0 47 -1 52 -2\nc10 -2 15 -8 15 -19c0 -13 -13 -20 -38 -20h-57z"
            },
            "&#x2ab5;": {
                x: 967,
                d: "M217 443h-29c-25 0 -38 7 -38 20c0 7 2 12 6 15s10 4 15 5s14 1 27 1c115 0 211 10 289 31c48 13 88 28 120 47s56 40 71 62s25 41 30 56s10 34 13 55c2 12 9 18 20 18c5 0 10 -2 14 -6s6 -9 6 -15c0 -33 -12 -70 -36 -110c-47 -78 -143 -131 -288 -159\nc98 -16 177 -49 238 -98c23 -20 43 -44 57 -72s22 -49 25 -64s4 -26 4 -34c0 -14 -7 -21 -20 -21c-11 0 -18 9 -21 26c-17 108 -97 179 -240 214c-81 19 -169 29 -263 29zM557 20l-147 -154h315c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17h-355\nc-38 -41 -65 -69 -80 -84c-13 -12 -21 -18 -26 -18s-10 2 -14 6s-6 8 -6 13s3 11 10 18c1 1 4 4 7 8s6 7 7 8c17 19 32 36 47 49h-131h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h168l147 154h-316h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h354c38 41 65 69 80 84\nc13 12 21 18 26 18s10 -2 14 -6s6 -8 6 -13s-3 -11 -10 -18c-1 -1 -3 -4 -7 -8s-6 -7 -7 -8c-17 -19 -32 -36 -47 -49h130c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17h-169z"
            },
            "&#x2ab6;": {
                x: 967,
                d: "M723 443h-29c-26 0 -64 -2 -115 -6c-119 -11 -211 -35 -275 -73s-102 -91 -113 -162c-3 -19 -10 -28 -21 -28c-13 0 -20 7 -20 21c0 33 12 70 36 110c47 79 142 131 285 158v1c-96 16 -174 49 -235 98c-23 20 -42 44 -56 72s-23 49 -26 64s-4 26 -4 34c0 6 2 11 6 15\ns9 6 14 6c11 0 18 -9 21 -26c17 -108 97 -179 240 -214c85 -19 173 -29 262 -29c31 0 49 -1 53 -2c10 -1 15 -8 15 -19c0 -13 -13 -20 -38 -20zM557 20l-147 -154h315c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17h-355c-38 -41 -65 -69 -80 -84\nc-13 -12 -21 -18 -26 -18s-10 2 -14 6s-6 8 -6 13s3 11 10 18c1 1 4 4 7 8s6 7 7 8c17 19 32 36 47 49h-131h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h168l147 154h-316h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h354c38 41 65 69 80 84c13 12 21 18 26 18\ns10 -2 14 -6s6 -8 6 -13s-3 -11 -10 -18c-1 -1 -3 -4 -7 -8s-6 -7 -7 -8c-17 -19 -32 -36 -47 -49h130c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17h-169z"
            },
            "&#x22e8;": {
                x: 965,
                d: "M486 -144l-97 -177c-7 -12 -11 -19 -13 -22s-7 -4 -13 -4c-13 0 -20 7 -20 20c0 4 3 11 9 21l104 190c-53 45 -99 67 -140 67c-20 0 -39 -4 -58 -13s-38 -26 -54 -50s-25 -54 -26 -90c0 -4 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 11 -14 32c0 51 15 97 46 138s71 62 120 62\nc29 0 54 -7 78 -20s52 -34 85 -62l97 177c7 12 12 19 14 22s6 4 12 4c13 0 20 -7 20 -20c0 -4 -3 -11 -9 -21l-104 -190c53 -45 99 -67 140 -67c37 0 68 14 95 42s41 62 43 104c0 20 5 30 14 30s14 -11 14 -32c0 -51 -15 -97 -46 -138s-71 -62 -120 -62c-29 0 -55 7 -79 20\ns-51 34 -84 62zM467 442c150 -29 248 -87 294 -173c18 -35 27 -66 27 -95c0 -14 -7 -21 -20 -21c-11 0 -18 9 -21 26c-7 45 -26 86 -57 121s-83 64 -156 85c-81 23 -183 36 -307 37c-22 0 -33 0 -34 1c-3 0 -5 1 -8 4c-5 3 -8 8 -8 15c0 13 10 20 29 20c127 0 228 10 305 31\nc39 10 72 22 101 37s51 29 67 43s29 30 39 49s18 34 21 47s6 28 9 45c3 12 9 18 20 18c5 0 10 -2 14 -6s6 -9 6 -15c0 -27 -8 -57 -24 -88c-22 -43 -55 -81 -100 -111s-111 -53 -197 -70z"
            },
            "&#x22e9;": {
                x: 965,
                d: "M486 -144l-97 -177c-7 -12 -11 -19 -13 -22s-7 -4 -13 -4c-13 0 -20 7 -20 20c0 4 3 11 9 21l104 190c-53 45 -99 67 -140 67c-20 0 -39 -4 -58 -13s-38 -26 -54 -50s-25 -54 -26 -90c0 -4 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 11 -14 32c0 51 15 97 46 138s71 62 120 62\nc29 0 54 -7 78 -20s52 -34 85 -62l97 177c7 12 12 19 14 22s6 4 12 4c13 0 20 -7 20 -20c0 -4 -3 -11 -9 -21l-104 -190c53 -45 99 -67 140 -67c37 0 68 14 95 42s41 62 43 104c0 20 5 30 14 30s14 -11 14 -32c0 -51 -15 -97 -46 -138s-71 -62 -120 -62c-29 0 -55 7 -79 20\ns-51 34 -84 62zM498 442c-97 19 -172 49 -226 92c-27 21 -48 47 -64 78s-26 54 -28 68s-3 24 -3 31c0 6 2 11 6 15s9 6 14 6c11 0 18 -8 21 -23c11 -73 49 -129 114 -168s160 -64 286 -74c43 -3 83 -5 120 -5c22 0 33 0 34 -1c3 0 5 -1 8 -4c5 -3 8 -8 8 -15\nc0 -13 -10 -20 -29 -20c-335 -2 -514 -80 -539 -233c-2 -14 -4 -24 -7 -29s-8 -7 -16 -7c-13 0 -20 7 -20 21c0 34 12 71 37 111c47 77 141 130 284 157z"
            },
            "&#x223c;": {
                x: 967,
                d: "M817 334c0 -51 -15 -96 -46 -138s-71 -63 -122 -63c-21 0 -43 5 -65 15s-40 20 -53 31s-31 26 -53 46c-22 19 -39 34 -51 43s-28 18 -49 28s-41 15 -60 15c-35 0 -67 -13 -95 -39s-43 -64 -45 -115c0 -3 -2 -8 -4 -14s-5 -9 -10 -9c-9 0 -14 11 -14 32c0 51 15 96 46 138\ns71 63 122 63c21 0 43 -5 65 -15s40 -20 53 -31s31 -26 53 -46c22 -19 39 -34 51 -43s28 -18 49 -28s41 -15 60 -15c37 0 69 14 96 41s42 64 44 110c1 17 5 26 14 26s14 -11 14 -32z"
            },
            "&#x225c;": {
                x: 966,
                d: "M505 842l192 -334c0 -12 -2 -19 -7 -22s-15 -4 -29 -4h-356h-18s-8 3 -12 6s-6 8 -6 14c0 5 3 12 8 21l184 319c7 11 14 17 22 17s15 -6 22 -17zM483 799l-160 -277h320zM186 173h594c8 0 14 -1 18 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1\nh-596c-7 0 -13 1 -17 1s-8 2 -12 5s-6 8 -6 14c0 13 12 20 36 20zM185 367h596c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -18 -1h-594c-24 0 -36 7 -36 20c0 6 2 11 6 14s8 5 12 5s10 1 17 1z"
            },
            "&#x21b6;": {
                x: 1235,
                d: "M350 88c68 85 143 105 144 105c11 0 11 -12 11 -15c0 -12 -1 -12 -17 -20c-69 -30 -115 -76 -146 -140c-7 -15 -8 -17 -15 -17s-9 5 -12 10c-35 77 -85 121 -155 151c-9 3 -10 7 -10 15c0 5 0 16 11 16c8 0 92 -33 147 -110c28 249 212 379 388 379\nc245 0 389 -234 389 -426c0 -17 0 -34 -20 -34c-19 0 -20 17 -20 44c-3 154 -123 376 -349 376c-185 0 -329 -152 -346 -334z"
            },
            "&#x21b7;": {
                x: 1236,
                d: "M928 83c55 78 139 110 147 110c11 0 11 -11 11 -16c0 -11 -2 -12 -15 -17c-102 -45 -136 -118 -152 -153c-2 -4 -6 -6 -10 -6c-7 0 -9 4 -13 12c-12 26 -49 103 -145 144c-19 9 -20 9 -20 21c0 3 0 15 11 15c3 0 77 -21 144 -105c-16 180 -159 334 -347 334\nc-200 0 -346 -184 -349 -390c0 -9 0 -30 -20 -30s-20 17 -20 34c0 194 146 426 389 426c190 0 365 -146 389 -379z"
            },
            "&#x21ba;": {
                x: 965,
                d: "M622 393l-30 9c-9 50 -31 98 -73 139c-16 16 -17 16 -30 17c-7 0 -19 1 -19 12c0 13 13 13 21 13c16 0 44 -4 47 -4s69 14 127 76c7 -2 24 -7 29 -8c-45 -52 -105 -78 -109 -80c159 -56 230 -194 230 -317c0 -182 -148 -333 -333 -333c-181 0 -332 148 -332 333\nc0 206 176 301 194 301c11 0 12 -10 12 -12c0 -7 -4 -10 -14 -15c-139 -76 -167 -202 -167 -274c0 -169 137 -308 308 -308c167 0 307 137 307 308c0 130 -84 254 -220 295l-5 -20c37 -50 51 -97 57 -132z"
            },
            "&#x21bb;": {
                x: 965,
                d: "M400 525l-5 20c-136 -41 -220 -164 -220 -295c0 -169 137 -308 308 -308c167 0 307 137 307 308c0 69 -26 197 -168 275c-8 4 -13 7 -13 14c0 2 1 12 12 12c18 0 194 -96 194 -301c0 -182 -148 -333 -333 -333c-181 0 -332 148 -332 333c0 122 70 261 230 317\nc-4 2 -64 28 -109 80c5 1 22 6 29 8c57 -62 124 -76 127 -76s31 4 47 4c8 0 21 0 21 -13c0 -11 -11 -12 -21 -12c-9 0 -14 -1 -36 -24c-40 -44 -58 -91 -65 -132l-30 -9c6 37 21 84 57 132z"
            },
            "&#x21be;": {
                x: 487,
                d: "M190 608v-767c0 -17 0 -35 -20 -35s-20 17 -20 35v853h20c27 -55 81 -119 167 -151v-31c-25 8 -86 27 -147 96z"
            },
            "&#x21bf;": {
                x: 487,
                d: "M337 694v-853c0 -17 0 -35 -20 -35s-20 17 -20 35v767c-61 -69 -122 -88 -147 -96v31c85 32 140 95 167 151h20z"
            },
            "&#x21c2;": {
                x: 487,
                d: "M190 659v-767c61 69 122 88 147 96v-31c-85 -32 -140 -95 -167 -151h-20v853c0 17 0 35 20 35s20 -17 20 -35z"
            },
            "&#x21c3;": {
                x: 487,
                d: "M337 659v-853h-20c-27 55 -81 119 -167 151v31c25 -8 86 -27 147 -96v767c0 17 0 35 20 35s20 -17 20 -35z"
            },
            "&#x21c4;": {
                x: 1187,
                d: "M1002 480h-771c9 -8 66 -57 101 -147h-31c-16 45 -56 116 -150 167c94 50 133 121 150 167h31c-35 -90 -92 -139 -101 -147h771c17 0 35 0 35 -20s-17 -20 -35 -20zM956 147h-771c-18 0 -35 0 -35 20s17 20 36 20h770c-17 15 -71 68 -101 146h31c30 -79 91 -136 150 -166\nc-68 -37 -122 -91 -150 -167h-31c9 23 34 87 101 147z"
            },
            "&#x21c6;": {
                x: 1187,
                d: "M956 480h-771c-17 0 -35 0 -35 20s17 20 35 20h771c-45 40 -79 91 -101 147h31c16 -45 56 -116 150 -167c-94 -50 -133 -121 -150 -167h-31c35 90 92 139 101 147zM1002 147h-771c67 -60 92 -123 101 -147h-31c-28 74 -80 129 -150 167c61 31 121 88 150 166h31\nc-28 -75 -79 -127 -101 -146h770c19 0 36 0 36 -20s-17 -20 -35 -20z"
            },
            "&#x21c8;": {
                x: 966,
                d: "M670 613v-770c0 -19 0 -37 -20 -37s-20 16 -20 34v774c-22 -23 -69 -73 -147 -102c-78 29 -125 79 -147 102v-774c0 -18 0 -34 -20 -34s-20 18 -20 37v770c-40 -45 -90 -79 -146 -101v31c67 25 127 75 167 151c33 -70 95 -124 166 -151c76 29 134 85 167 151\nc46 -92 120 -134 166 -151v-31c-29 12 -90 38 -146 101z"
            },
            "&#x21ca;": {
                x: 966,
                d: "M670 657v-770c40 45 90 79 146 101v-31c-50 -19 -122 -63 -166 -151h-1c-33 70 -95 124 -166 151c-76 -29 -134 -85 -167 -151c-46 92 -120 134 -166 151v31c29 -12 90 -38 146 -101v770c0 19 0 37 20 37s20 -16 20 -34v-774c22 23 69 73 147 102\nc78 -29 125 -79 147 -102v774c0 18 0 34 20 34s20 -18 20 -37z"
            },
            "&#x21cb;": {
                x: 1187,
                d: "M1002 327h-852v20c55 27 119 81 151 167h31c-8 -25 -27 -86 -96 -147h766c17 0 35 0 35 -20s-17 -20 -35 -20zM951 133h-766c-17 0 -35 0 -35 20s17 20 35 20h852v-20c-55 -27 -119 -81 -151 -167h-31c8 25 27 86 96 147z"
            },
            "&#x21cc;": {
                x: 1187,
                d: "M1037 327h-852c-17 0 -35 0 -35 20s17 20 35 20h766c-69 61 -88 122 -96 147h31c32 -85 95 -140 151 -167v-20zM1002 133h-766c69 -61 88 -122 96 -147h-31c-32 85 -95 140 -151 167v20h852c17 0 35 0 35 -20s-17 -20 -35 -20z"
            },
            "&#x21cd;": {
                x: 1186,
                d: "M745 327l-65 -154h320c18 0 36 0 36 -20s-18 -20 -35 -20h-338l-62 -147c-4 -9 -8 -19 -21 -19c-8 0 -20 6 -20 20c0 7 52 127 60 146h-207c81 -89 100 -144 100 -146c0 -12 -11 -12 -20 -12c-6 0 -11 0 -15 3c-2 1 -2 3 -13 22c-49 91 -139 192 -299 237\nc-11 3 -16 5 -16 13s5 10 17 13c158 45 250 146 301 243c10 18 10 19 25 19c9 0 20 0 20 -12c0 -3 -19 -58 -100 -146h306l61 144c6 15 10 22 22 22c8 0 20 -6 20 -20c0 -3 -1 -5 -6 -18l-54 -128h239c17 0 35 0 35 -20s-18 -20 -36 -20h-255zM702 327h-314\nc-18 0 -19 -1 -21 -2c-1 0 -9 -6 -19 -14c-45 -33 -103 -59 -107 -61c4 -2 58 -25 114 -67c13 -10 15 -10 33 -10h249z"
            },
            "&#x21ce;": {
                x: 1229,
                d: "M713 497l-41 -130h186c-59 70 -86 141 -86 146c0 12 13 12 19 12c15 0 16 -1 24 -19c61 -138 163 -207 257 -246c1 0 7 -2 7 -10c0 -6 -4 -9 -6 -10c-49 -20 -183 -77 -258 -247c-8 -17 -8 -18 -24 -18c-6 0 -19 0 -19 12c0 5 28 77 86 146h-260\nc-14 -48 -30 -97 -45 -145c-4 -10 -7 -21 -22 -21c-5 0 -20 4 -20 20c0 6 18 60 22 71c7 21 18 59 23 75h-185c59 -70 86 -141 86 -146c0 -12 -13 -12 -19 -12c-15 0 -16 1 -24 19c-61 138 -163 207 -257 246c-1 0 -7 2 -7 10c0 6 4 9 6 10c49 20 183 77 258 247\nc8 17 8 18 24 18c6 0 19 0 19 -12c0 -5 -28 -77 -86 -146h260c14 48 30 97 45 145c4 10 7 21 22 21c5 0 20 -4 20 -20c0 -1 -4 -14 -5 -16zM331 173h238l49 154h-287c-33 -33 -75 -60 -105 -77c30 -17 72 -44 105 -77zM660 327l-49 -154h287c33 33 75 60 105 77\nc-30 17 -72 44 -105 77h-238z"
            },
            "&#x21cf;": {
                x: 1186,
                d: "M620 495l-54 -128h207c-81 89 -100 144 -100 146c0 12 11 12 20 12c6 0 11 0 15 -3c2 -1 2 -3 13 -22c49 -91 139 -192 299 -237c11 -3 16 -5 16 -13s-5 -10 -17 -13c-158 -45 -250 -146 -301 -243c-10 -18 -10 -19 -25 -19c-9 0 -20 0 -20 12c0 3 19 58 100 146h-306\nl-62 -147c-4 -9 -8 -19 -21 -19c-8 0 -20 6 -20 20c0 7 52 127 60 146h-239c-17 0 -35 0 -35 20s19 20 36 20h255l65 154h-320c-17 0 -36 0 -36 20s18 20 35 20h338l61 144c6 15 10 22 22 22c8 0 20 -6 20 -20c0 -3 -1 -5 -6 -18zM484 173h314c18 0 19 1 21 2c1 0 9 6 19 14\nc45 33 103 59 107 61c-4 2 -58 25 -114 67c-13 10 -15 10 -33 10h-249z"
            },
            "&#x21d0;": {
                x: 1188,
                d: "M1003 133h-591c49 -47 100 -134 100 -147c0 -11 -13 -11 -19 -11c-14 0 -15 1 -26 21c-56 107 -156 198 -291 238c-19 6 -20 7 -22 8l-3 2c-1 2 -1 4 -1 6s0 5 2 6c2 2 3 3 20 9c52 15 119 42 188 103c65 58 93 111 114 149c4 8 11 8 19 8c6 0 19 0 19 -11\nc0 -13 -52 -101 -100 -147h591c17 0 35 0 35 -20s-18 -20 -36 -20h-635c-41 -35 -90 -61 -126 -77c33 -15 84 -41 126 -77h635c18 0 36 0 36 -20s-18 -20 -35 -20z"
            },
            "&#x21d1;": {
                x: 848,
                d: "M541 430v-588c0 -18 0 -36 -20 -36s-20 19 -20 36v634c-38 45 -65 99 -77 126c-11 -25 -38 -80 -77 -126v-634c0 -18 0 -36 -20 -36s-20 18 -20 36v588c-34 -31 -48 -42 -69 -57c-31 -21 -71 -43 -77 -43c-11 0 -11 11 -11 20c0 11 0 16 8 20c152 78 220 192 255 312\nc1 4 3 11 11 11c4 0 9 -2 10 -8c31 -106 92 -231 252 -313c12 -6 12 -8 12 -22c0 -9 0 -20 -11 -20c-6 0 -44 21 -77 44c-16 11 -30 20 -69 56z"
            },
            "&#x21d2;": {
                x: 1188,
                d: "M821 327h-635c-17 0 -36 0 -36 20s18 20 35 20h591c-49 47 -100 134 -100 147c0 11 13 11 19 11c14 0 15 -1 26 -21c56 -107 156 -198 291 -238c19 -6 20 -7 22 -8l3 -2c1 -2 1 -4 1 -6s0 -5 -2 -6c-2 -2 -3 -3 -20 -9c-52 -15 -119 -42 -188 -103\nc-65 -58 -93 -111 -114 -149c-4 -8 -11 -8 -19 -8c-6 0 -19 0 -19 11c0 13 52 101 100 147h-591c-17 0 -35 0 -35 20s19 20 36 20h635c41 35 90 61 126 77c-33 15 -84 41 -126 77z"
            },
            "&#x21d3;": {
                x: 848,
                d: "M541 658v-588c34 31 48 42 69 57c31 21 71 43 77 43c11 0 11 -11 11 -20c0 -11 0 -16 -8 -20c-143 -74 -217 -181 -255 -312c-1 -4 -3 -11 -11 -11c-4 0 -9 2 -10 8c-32 112 -97 233 -252 313c-12 6 -12 8 -12 22c0 9 0 20 11 20c6 0 44 -21 77 -44c16 -11 30 -20 69 -56\nv588c0 18 0 36 20 36s20 -18 20 -36v-634c38 -45 65 -99 77 -126c11 25 38 80 77 126v634c0 17 0 36 20 36s20 -18 20 -36z"
            },
            "&#x21d4;": {
                x: 1231,
                d: "M370 367h491c-57 65 -87 138 -87 146c0 12 11 12 20 12c12 0 16 -1 20 -10c51 -116 133 -201 243 -247c20 -9 21 -10 23 -12c1 -2 1 -4 1 -6c0 -7 -3 -8 -17 -14c-131 -56 -205 -148 -246 -240c-9 -20 -9 -21 -24 -21c-9 0 -20 0 -20 12c0 8 30 81 87 146h-491\nc57 -65 87 -138 87 -146c0 -12 -11 -12 -20 -12c-12 0 -16 1 -20 10c-51 116 -133 201 -243 247c-20 9 -21 10 -23 12c-1 2 -1 4 -1 6c0 7 3 8 17 14c131 56 205 148 246 240c9 20 9 21 24 21c9 0 20 0 20 -12c0 -8 -30 -81 -87 -146zM331 173h569c20 20 50 45 105 77\nc-41 24 -75 48 -105 77h-569c-20 -20 -50 -45 -105 -77c41 -24 75 -48 105 -77z"
            },
            "&#x21d5;": {
                x: 848,
                d: "M541 550v-600c67 56 140 86 146 86c11 0 11 -10 11 -19c0 -12 0 -14 -3 -16c-1 -1 -2 -2 -20 -11c-68 -30 -175 -98 -237 -246c-5 -11 -7 -15 -14 -15s-9 3 -14 16c-57 133 -145 205 -251 251c-9 4 -9 7 -9 21c0 9 0 19 11 19c6 0 79 -29 146 -86v600\nc-67 -56 -140 -86 -146 -86c-11 0 -11 10 -11 19c0 12 0 14 3 16c1 1 2 2 20 11c68 30 175 98 237 246c5 11 7 15 14 15s9 -3 14 -16c57 -133 145 -205 251 -251c9 -4 9 -7 9 -21c0 -9 0 -19 -11 -19c-6 0 -79 29 -146 86zM347 590v-680c33 -34 59 -72 77 -105\nc18 33 44 71 77 105v680c-33 34 -59 72 -77 105c-18 -33 -44 -71 -77 -105z"
            },
            "&#x21da;": {
                x: 1166,
                d: "M981 230h-738c84 -57 131 -116 154 -144c8 -9 9 -10 28 -10h555c17 0 36 0 36 -20s-18 -20 -35 -20h-547c34 -55 58 -107 72 -147h-47c-57 160 -166 282 -309 361c151 83 255 209 309 361h47c-12 -35 -36 -88 -72 -147h547c17 0 35 0 35 -20s-19 -20 -36 -20h-555\nc-19 0 -20 -1 -28 -11c-21 -25 -70 -86 -154 -143h738c17 0 35 0 35 -20s-17 -20 -35 -20z"
            },
            "&#x21db;": {
                x: 1166,
                d: "M741 424h-556c-17 0 -35 0 -35 20s17 20 35 20h547c-34 55 -58 107 -72 147h47c57 -160 166 -282 309 -361c-151 -83 -255 -209 -309 -361h-47c12 35 36 88 72 147h-547c-17 0 -35 0 -35 20s17 20 35 20h556c19 0 20 1 28 11c21 25 70 86 154 143h-738\nc-20 0 -35 0 -35 20s15 20 35 20h738c-84 57 -131 116 -154 144c-8 9 -9 10 -28 10z"
            },
            "&#x21dd;": {
                x: 1187,
                d: "M284 342l164 -148c22 19 162 146 165 148c11 9 13 11 20 11s9 0 27 -16c23 -20 34 -31 73 -67h224c-9 8 -66 57 -101 147h31c16 -45 56 -116 150 -167c-94 -50 -133 -121 -150 -167h-31c35 90 92 139 101 147h-216c-21 0 -23 0 -34 10l-74 66l-75 -68l-79 -71\nc-22 -20 -24 -20 -31 -20s-9 0 -23 14c-5 4 -23 20 -25 21l-137 124c-27 -22 -47 -42 -68 -60c-17 -16 -19 -16 -25 -16c-10 0 -20 9 -20 20c0 9 4 13 16 24l68 60c20 19 22 19 29 19c3 0 9 0 21 -11z"
            },
            "&#x21ab;": {
                x: 1206,
                d: "M755 230v-237c0 -17 0 -35 -20 -35s-20 18 -20 35v237h-485c9 -8 66 -57 101 -147h-31c-16 45 -56 116 -150 167c94 50 133 121 150 167h31c-35 -90 -92 -139 -101 -147h485v60c0 153 29 246 171 246c108 0 170 -51 170 -172c0 -135 -80 -174 -239 -174h-62zM755 270h59\nc160 0 202 37 202 134c0 96 -41 132 -130 132c-99 0 -131 -45 -131 -209v-57z"
            },
            "&#x21ac;": {
                x: 1206,
                d: "M491 230v-237c0 -17 0 -35 -20 -35s-20 19 -20 36v236h-62c-160 0 -239 39 -239 174c0 110 51 172 170 172c141 0 171 -92 171 -246v-60h485c-9 8 -66 57 -101 147h31c16 -45 56 -116 150 -167c-94 -50 -133 -121 -150 -167h-31c35 90 92 139 101 147h-485zM392 270h59\nv57c0 164 -33 209 -131 209c-89 0 -130 -36 -130 -132c0 -97 42 -134 202 -134z"
            },
            "&#x21ad;": {
                x: 1574,
                d: "M1344 230h-134c-25 0 -26 1 -55 32l-42 42l-78 -79c-16 -17 -55 -60 -73 -74c-5 -4 -10 -4 -12 -4c-9 0 -12 4 -22 13c-23 24 -53 53 -69 71c-23 25 -47 49 -72 73c-24 -23 -48 -47 -71 -72l-40 -40c-7 -8 -26 -27 -33 -35c-7 -6 -11 -10 -19 -10c-2 0 -8 0 -13 4\nc-15 14 -51 52 -65 67c-29 28 -73 72 -85 86c-60 -58 -48 -50 -65 -65c-9 -8 -10 -9 -32 -9h-134c9 -8 66 -57 101 -147h-31c-16 45 -56 116 -150 167c94 50 133 121 150 167h31c-35 -90 -92 -139 -101 -147h141l54 55c27 27 28 28 36 28s11 -2 22 -14l141 -143l150 153\nc1 0 5 4 13 4c6 0 10 -1 22 -13l142 -144c17 18 145 149 149 152c6 5 11 5 13 5c8 0 9 -1 46 -37l44 -46h141c-45 40 -79 91 -101 147h31c29 -77 83 -131 150 -167c-59 -30 -119 -85 -150 -167h-31c33 84 84 132 101 147z"
            },
            "&#x21ae;": {
                x: 1185,
                d: "M603 230l-100 -151c-10 -15 -17 -15 -22 -15c-10 0 -19 8 -19 20c0 6 10 22 17 33l76 113h-321c75 -55 108 -138 108 -146c0 -9 -8 -12 -15 -12c-11 0 -13 5 -17 14c-27 58 -65 112 -148 151c-7 4 -12 6 -12 13s3 8 14 14c100 47 132 119 147 154c2 4 4 10 16 10\nc7 0 15 -3 15 -12c0 -8 -33 -91 -108 -146h348l100 151c10 15 17 15 22 15c10 0 19 -8 19 -20c0 -6 -10 -22 -17 -33l-76 -113h321c-75 55 -108 138 -108 146c0 9 8 12 15 12c11 0 13 -5 17 -14c41 -92 99 -129 153 -154c1 0 7 -3 7 -10s-2 -8 -14 -14\nc-100 -47 -132 -120 -147 -154c-2 -4 -4 -10 -16 -10c-7 0 -15 3 -15 12c0 8 33 91 108 146h-348z"
            },
            "&#x2190;": {
                x: 1188,
                d: "M1002 230h-743c43 -32 72 -67 92 -97c42 -67 51 -130 51 -132c0 -12 -12 -12 -20 -12c-17 0 -18 3 -21 15c-25 112 -92 190 -196 233c-10 4 -15 6 -15 13s4 9 15 13c113 47 172 130 195 228c4 18 5 20 22 20c8 0 20 0 20 -12c0 -1 -8 -64 -53 -133\nc-8 -13 -35 -54 -90 -96h743c18 0 36 0 36 -20s-18 -20 -36 -20z"
            },
            "&#x2191;": {
                x: 766,
                d: "M403 555v-713c0 -18 0 -36 -20 -36s-20 18 -20 36v713c-22 -34 -49 -64 -82 -89c-57 -41 -116 -52 -119 -52c-12 0 -12 11 -12 19c0 17 2 17 17 22c110 31 181 109 204 220c2 12 3 18 12 18c6 0 10 -4 11 -9c6 -32 18 -92 69 -147c23 -25 62 -61 130 -80\nc22 -7 23 -8 23 -24c0 -8 0 -19 -12 -19c-2 0 -63 10 -124 56c-25 19 -53 47 -77 85z"
            },
            "&#x2192;": {
                x: 1188,
                d: "M929 230h-743c-18 0 -36 0 -36 20s18 20 36 20h743c-43 32 -72 67 -92 97c-42 67 -51 130 -51 132c0 12 12 12 20 12c17 0 18 -3 21 -15c38 -168 159 -218 203 -235c2 -1 8 -4 8 -11s-3 -9 -15 -13c-113 -46 -172 -130 -195 -228c-4 -18 -5 -20 -22 -20\nc-8 0 -20 0 -20 12c0 1 8 64 53 133c8 13 35 54 90 96z"
            },
            "&#x2193;": {
                x: 766,
                d: "M403 658v-713c22 34 49 64 82 89c57 41 116 52 119 52c12 0 12 -11 12 -19c0 -17 -2 -17 -17 -22c-110 -31 -182 -109 -204 -222c-2 -9 -3 -16 -12 -16c-7 0 -10 5 -11 10c-6 31 -18 91 -69 146c-23 25 -62 61 -130 80c-22 7 -23 8 -23 24c0 8 0 19 12 19\nc2 0 63 -10 124 -56c25 -19 53 -47 77 -85v713c0 18 0 36 20 36s20 -18 20 -36z"
            },
            "&#x2194;": {
                x: 1187,
                d: "M259 270h669c-43 32 -72 67 -92 97c-42 67 -51 130 -51 132c0 12 12 12 20 12c17 0 18 -3 21 -15c38 -168 159 -218 203 -235c2 -1 8 -4 8 -11s-3 -9 -15 -13c-113 -46 -172 -130 -195 -228c-4 -18 -5 -20 -22 -20c-8 0 -20 0 -20 12c0 1 8 64 53 133c8 13 35 54 90 96\nh-669c43 -32 72 -67 92 -97c42 -67 51 -130 51 -132c0 -12 -12 -12 -20 -12c-17 0 -18 3 -21 15c-25 112 -92 190 -196 233c-10 4 -15 6 -15 13s4 9 15 13c113 47 172 130 195 228c4 18 5 20 22 20c8 0 20 0 20 -12c0 -1 -8 -64 -53 -133c-8 -13 -35 -54 -90 -96z"
            },
            "&#x2195;": {
                x: 766,
                d: "M403 633v-766c22 34 49 64 82 89c57 41 116 52 119 52c12 0 12 -11 12 -19c0 -17 -2 -17 -17 -22c-109 -31 -182 -109 -204 -222c-2 -9 -3 -16 -12 -16c-7 0 -10 5 -11 10c-6 31 -18 91 -69 146c-36 39 -79 66 -130 80c-22 7 -23 8 -23 24c0 8 0 19 12 19\nc1 0 63 -10 124 -56c25 -19 53 -47 77 -85v766c-22 -34 -49 -64 -82 -89c-57 -41 -116 -52 -119 -52c-12 0 -12 11 -12 19c0 10 0 17 10 20c37 10 85 24 141 81c9 10 52 54 70 141c2 12 3 18 12 18c6 0 10 -4 11 -9c6 -33 17 -90 68 -147c36 -38 80 -66 131 -80\nc22 -7 23 -8 23 -24c0 -8 0 -19 -12 -19c-1 0 -63 10 -124 56c-25 19 -53 47 -77 85z"
            },
            "&#x2196;": {
                x: 1214,
                d: "M270 628l779 -780c13 -13 15 -15 15 -23c0 -10 -8 -20 -20 -20c-8 0 -10 2 -22 14l-781 780c5 -25 5 -26 5 -58c0 -23 -2 -71 -23 -127c-8 -19 -36 -81 -50 -81c-7 0 -23 16 -23 23c0 4 1 6 5 13c46 71 52 131 52 173c0 58 -15 102 -28 129c-3 8 -3 10 -3 12\nc0 6 4 10 10 10c3 0 6 -1 12 -4c23 -11 68 -27 129 -27c25 0 94 0 173 52c6 4 9 5 13 5c7 0 23 -16 23 -23c0 -16 -74 -47 -81 -50c-58 -21 -107 -23 -127 -23c-18 0 -41 2 -58 5z"
            },
            "&#x2197;": {
                x: 1214,
                d: "M973 599l-781 -780c-12 -12 -14 -14 -22 -14c-11 0 -20 10 -20 20c0 8 2 10 15 23l779 780c-25 -5 -26 -5 -58 -5c-18 0 -67 1 -125 23c-21 8 -83 36 -83 50c0 7 16 23 23 23c4 0 7 -1 13 -5c77 -51 144 -52 173 -52c58 0 102 15 129 28c8 3 10 3 12 3c4 0 10 -2 10 -10\nc0 -3 -1 -6 -4 -12c-11 -23 -27 -68 -27 -129c0 -35 3 -97 52 -173c4 -7 5 -9 5 -13c0 -7 -16 -23 -23 -23c-15 0 -45 69 -50 81c-21 58 -23 107 -23 127c0 18 2 41 5 58z"
            },
            "&#x2198;": {
                x: 1214,
                d: "M944 -128l-779 780c-13 13 -15 15 -15 23c0 10 8 20 20 20c8 0 10 -2 22 -14l781 -780c-5 25 -5 26 -5 58c0 23 2 71 23 127c8 19 36 81 50 81c7 0 23 -16 23 -23c0 -4 -1 -6 -5 -13c-46 -71 -52 -131 -52 -173c0 -59 15 -102 28 -129c3 -8 3 -10 3 -12\nc0 -8 -6 -10 -10 -10c-3 0 -6 1 -12 4c-23 11 -68 27 -129 27c-25 0 -94 0 -173 -52c-6 -4 -9 -5 -13 -5c-7 0 -23 16 -23 23c0 16 74 47 81 50c58 21 107 23 127 23c18 0 41 -2 58 -5z"
            },
            "&#x2199;": {
                x: 1214,
                d: "M1049 652l-779 -780c25 5 26 5 58 5c18 0 67 -1 125 -23c21 -8 83 -36 83 -50c0 -7 -16 -23 -23 -23c-4 0 -7 1 -13 5c-77 51 -144 52 -173 52c-58 0 -102 -15 -129 -28c-8 -3 -10 -3 -12 -3c-6 0 -10 4 -10 10c0 3 1 6 4 12c11 23 27 68 27 129c0 35 -3 97 -52 173\nc-4 7 -5 9 -5 13c0 7 16 23 23 23c15 0 45 -69 50 -81c21 -58 23 -107 23 -127c0 -18 -2 -41 -5 -58l781 780c12 12 14 14 22 14c11 0 20 -10 20 -20c0 -8 -2 -10 -15 -23z"
            },
            "&#x219e;": {
                x: 1186,
                d: "M1001 230h-589c10 -8 69 -60 101 -147h-31c-28 75 -80 123 -116 147h-136c9 -8 66 -57 101 -147h-31c-16 45 -56 116 -150 167c94 50 133 121 150 167h31c-35 -90 -92 -139 -101 -147h136c38 26 89 73 116 147h31c-27 -74 -74 -124 -101 -147h589c17 0 35 0 35 -20\ns-17 -20 -35 -20z"
            },
            "&#x21a0;": {
                x: 1186,
                d: "M820 270h136c-9 8 -66 57 -101 147h31c16 -45 56 -116 150 -167c-94 -50 -133 -121 -150 -167h-31c35 90 92 139 101 147h-136c-38 -26 -89 -73 -116 -147h-31c27 74 74 124 101 147h-589c-17 0 -35 0 -35 20s17 20 35 20h589c-10 8 -69 60 -101 147h31\nc28 -75 80 -123 116 -147z"
            },
            "&#x21a2;": {
                x: 1283,
                d: "M986 230h-756c30 -27 72 -74 101 -147h-31c-30 80 -89 136 -150 167c60 31 120 86 150 167h31c-27 -70 -67 -117 -101 -147h756c36 24 85 68 116 147h31c-28 -77 -78 -127 -102 -147v-40c23 -20 74 -70 102 -147h-31c-31 80 -81 124 -116 147z"
            },
            "&#x21a3;": {
                x: 1283,
                d: "M297 270h756c-30 27 -72 74 -101 147h31c30 -80 89 -136 150 -167c-60 -31 -120 -86 -150 -167h-31c27 70 67 117 101 147h-756c-36 -24 -85 -68 -116 -147h-31c28 77 78 127 102 147v40c-23 20 -74 70 -102 147h31c31 -80 81 -124 116 -147z"
            },
            "&#x21b0;": {
                x: 686,
                d: "M536 540v-505c0 -17 0 -35 -20 -35s-20 17 -20 35v500h-266c23 -20 73 -72 101 -146h-31c-29 77 -88 135 -150 166c64 34 121 89 150 167h31c-12 -29 -37 -90 -101 -147h271c32 0 35 -3 35 -35z"
            },
            "&#x21b1;": {
                x: 686,
                d: "M456 535h-266v-500c0 -17 0 -35 -20 -35s-20 17 -20 35v505c0 32 3 35 35 35h271c-58 52 -86 107 -101 147h31c22 -60 68 -124 150 -167c-63 -32 -121 -90 -150 -166h-31c27 71 74 122 101 146z"
            },
            "&#x22a2;": {
                x: 800,
                d: "M615 327h-425v-291v-18s-3 -8 -6 -12s-8 -6 -14 -6c-13 0 -20 12 -20 36v622c0 24 7 36 20 36c6 0 11 -2 14 -6s6 -8 6 -12v-18v-291h425c7 0 13 -1 17 -1s8 -2 12 -5s6 -8 6 -14s-2 -11 -6 -14s-8 -5 -12 -5s-10 -1 -17 -1z"
            },
            "&#x22a3;": {
                x: 800,
                d: "M650 658v-622v-18s-3 -8 -6 -12s-8 -6 -14 -6c-13 0 -20 12 -20 36v291h-424c-24 0 -36 7 -36 20s12 20 36 20h424v291c0 24 7 36 20 36c6 0 11 -2 14 -6s6 -8 6 -12v-18z"
            },
            "&#x22a8;": {
                x: 798,
                d: "M613 230h-423v-195c0 -17 0 -35 -20 -35s-20 19 -20 36v622c0 17 0 36 20 36s20 -18 20 -35v-195h423c17 0 35 0 35 -20s-19 -20 -36 -20h-422v-154h422c17 0 36 0 36 -20s-18 -20 -35 -20z"
            },
            "&#x22a9;": {
                x: 909,
                d: "M190 659v-624c0 -17 0 -35 -20 -35s-20 19 -20 36v622c0 17 0 36 20 36s20 -18 20 -35zM384 327v-292c0 -17 0 -35 -20 -35s-20 19 -20 36v622c0 17 0 36 20 36s20 -18 20 -35v-292h340c17 0 35 0 35 -20s-18 -20 -35 -20h-340z"
            },
            "&#x22aa;": {
                x: 1076,
                d: "M190 659v-624c0 -17 0 -35 -20 -35s-20 19 -20 36v622c0 17 0 36 20 36s20 -18 20 -35zM552 327v-292c0 -17 0 -35 -20 -35s-20 19 -20 36v622c0 17 0 36 20 36s20 -18 20 -35v-292h339c17 0 35 0 35 -20s-18 -20 -35 -20h-339zM371 659v-624c0 -17 0 -35 -20 -35\ns-20 19 -20 36v622c0 17 0 36 20 36s20 -18 20 -35z"
            },
            "&#x22ad;": {
                x: 909,
                d: "M548 424l-134 -154h309c17 0 36 0 36 -20s-18 -20 -35 -20h-345l-78 -89v-106c0 -17 0 -35 -20 -35s-20 19 -20 36v58l-66 -75c-12 -14 -16 -19 -26 -19s-19 9 -19 20c0 3 1 9 4 14c4 4 14 14 17 19c55 63 55 65 90 102v503c0 17 0 36 20 36s20 -18 20 -35v-195h229\nl187 214c12 14 14 16 22 16c11 0 20 -8 20 -20c0 -8 -3 -12 -12 -22l-164 -188h141c17 0 35 0 35 -20s-19 -20 -36 -20h-175zM301 424v-154h60l134 154h-194zM325 230h-24v-28z"
            },
            "&#x22af;": {
                x: 1020,
                d: "M619 424l-124 -119v-35h339c17 0 36 0 36 -20s-18 -20 -35 -20h-340v-195c0 -17 0 -35 -20 -35s-20 19 -20 36v230l-154 -148v-83c0 -17 0 -35 -20 -35s-20 19 -20 36v44l-45 -44c-31 -30 -37 -36 -46 -36c-14 0 -20 11 -20 20c0 11 13 22 31 39l73 70c7 6 7 8 7 26v503\nc0 17 0 36 20 36s20 -18 20 -35v-485l154 148v336c0 17 0 36 20 36s20 -18 20 -35v-195h108c16 14 47 44 70 67c16 17 58 56 75 72l76 73c15 15 18 18 26 18c11 0 20 -8 20 -20c0 -9 -2 -11 -13 -21l-196 -189h174c17 0 35 0 35 -20s-19 -20 -36 -20h-215zM561 424h-66v-63z\n"
            },
            "&#x22b8;": {
                x: 1298,
                d: "M832 230h-647c-17 0 -35 0 -35 20s17 20 35 20h647c11 83 80 139 158 139c86 0 158 -69 158 -159c0 -87 -69 -159 -159 -159c-74 0 -145 53 -157 139zM990 131c64 0 118 52 118 119c0 64 -51 119 -119 119c-64 0 -118 -52 -118 -119c0 -64 51 -119 119 -119z"
            },
            "&#x22ba;": {
                x: 744,
                d: "M412 351v-511c0 -14 0 -53 -40 -53s-40 39 -40 53v511h-129c-14 0 -53 0 -53 40s39 40 53 40h338c14 0 53 0 53 -40s-39 -40 -53 -40h-129z"
            },
            "&#x22d4;": {
                x: 855,
                d: "M705 332v-319c0 -17 0 -35 -20 -35s-20 18 -20 35v316c0 25 0 72 -59 113c-35 23 -87 42 -158 45v-474c0 -17 0 -35 -20 -35s-20 18 -20 35v474c-85 -3 -135 -30 -152 -41c-66 -42 -66 -90 -66 -117v-316c0 -17 0 -35 -20 -35s-20 18 -20 35v319c0 172 209 194 258 196\nv173c0 17 0 35 20 35s20 -18 20 -35v-173c35 -1 257 -18 257 -196z"
            },
            "&#x22ea;": {
                x: 967,
                d: "M516 435l111 250c6 12 11 21 23 21c10 0 20 -9 20 -20c0 -4 -1 -6 -7 -19l-91 -206l150 72c5 3 12 6 19 6c20 0 20 -17 20 -35v-508c0 -16 0 -36 -20 -36c-5 0 -49 21 -74 33l-251 118c-45 -97 -135 -304 -139 -310c-2 -1 -7 -7 -16 -7c-10 0 -20 9 -20 20\nc0 4 18 44 28 66l111 249l-209 98c-11 5 -21 10 -21 23c0 11 8 17 21 23zM491 379l-274 -129l179 -85zM547 406l-115 -258l289 -136v476z"
            },
            "&#x22eb;": {
                x: 967,
                d: "M663 667l-132 -296l211 -99c10 -5 19 -11 19 -22c0 -13 -8 -17 -18 -21l-315 -149l-30 -14c-2 -2 -3 -3 -11 -18l-50 -114c-8 -16 -55 -124 -58 -129c-3 -6 -9 -11 -18 -11c-10 0 -20 9 -20 20c0 4 14 35 22 52l76 172c-21 -10 -161 -78 -169 -78c-20 0 -20 20 -20 36\nv508c0 17 0 35 20 35c7 0 14 -3 19 -6l306 -145l132 297c6 12 11 21 23 21c10 0 20 -9 20 -20c0 -4 -1 -6 -7 -19zM419 120l275 130l-179 84zM364 94l115 258l-289 136v-476z"
            },
            "&#x22ec;": {
                x: 967,
                d: "M419 207l-248 117c-11 5 -21 10 -21 23c0 11 8 17 21 23l370 174l87 236c6 15 8 23 22 23c10 0 20 -9 20 -20c0 -1 0 -5 -5 -17l-72 -198l129 62c5 3 12 6 19 6c20 0 20 -17 20 -35v-508c0 -16 0 -36 -20 -36c-3 0 -5 0 -30 13l-256 120l-105 -287h376c18 0 35 0 35 -20\ns-20 -20 -37 -20h-388l-52 -142c-5 -14 -9 -24 -23 -24c-10 0 -20 9 -20 20c0 4 17 49 26 74c4 12 11 31 26 72h-106c-17 0 -37 0 -37 20s17 20 35 20h123zM522 491l-305 -144l215 -102zM469 228l252 -119v476l-148 -70z"
            },
            "&#x22ed;": {
                x: 967,
                d: "M739 324l-284 -134l-105 -287h376c18 0 35 0 35 -20s-20 -20 -37 -20h-388l-52 -142c-5 -14 -9 -24 -23 -24c-10 0 -20 9 -20 20c0 4 17 49 26 74c4 12 11 31 26 72h-106c-17 0 -37 0 -37 20s17 20 35 20h123l95 262l-217 -102c-10 -5 -12 -6 -16 -6c-20 0 -20 20 -20 36\nv508c0 17 0 35 20 35c7 0 15 -4 21 -7l325 -154l112 305c6 15 8 23 22 23c10 0 20 -9 20 -20c0 -1 0 -4 -5 -17l-113 -308l190 -89c10 -5 19 -11 19 -22c0 -13 -7 -16 -22 -23zM694 347l-155 73l-65 -177zM190 109l233 110l80 218l-313 148v-476z"
            },
            "&#x2308;": {
                x: 548,
                d: "M362 710h-172v-924c0 -18 0 -36 -20 -36s-20 21 -20 36v928c0 32 4 36 36 36h176c15 0 36 0 36 -20s-21 -20 -36 -20z"
            },
            "&#x2309;": {
                x: 548,
                d: "M398 714v-928c0 -18 0 -36 -20 -36s-20 21 -20 36v924h-172c-15 0 -36 0 -36 20s21 20 36 20h177c32 0 35 -3 35 -36z"
            },
            "&#x230a;": {
                x: 548,
                d: "M362 -250h-176c-32 0 -36 4 -36 36v928c0 15 0 36 20 36s20 -18 20 -36v-924h172c15 0 36 0 36 -20s-21 -20 -36 -20z"
            },
            "&#x230b;": {
                x: 548,
                d: "M398 714v-928c0 -33 -3 -36 -35 -36h-177c-15 0 -36 0 -36 20s21 20 36 20h172v924c0 15 0 36 20 36s20 -18 20 -36z"
            },
            "&#x2acb;": {
                x: 967,
                d: "M460 246h266c17 0 35 0 35 -20s-18 -20 -35 -20h-269c-169 0 -307 127 -307 290c0 162 138 289 306 289h270c17 0 35 0 35 -20s-18 -20 -35 -20h-267c-155 0 -269 -114 -269 -250c0 -131 109 -249 270 -249zM548 -52l-132 -154h310c17 0 35 0 35 -20s-18 -20 -35 -20\nh-345l-89 -105c-21 -23 -22 -24 -31 -24c-10 0 -20 9 -20 20c0 7 0 9 19 30c16 18 59 68 68 79h-143c-17 0 -35 0 -35 20s18 20 35 20h178l132 154h-310c-17 0 -35 0 -35 20s18 20 35 20h345l89 105c21 23 22 24 31 24c10 0 20 -9 20 -20c0 -7 0 -9 -19 -30\nc-20 -23 -59 -68 -68 -79h143c17 0 35 0 35 -20s-18 -20 -35 -20h-178z"
            },
            "&#x2acc;": {
                x: 967,
                d: "M451 745h-266c-17 0 -35 0 -35 20s18 20 35 20h269c170 0 307 -127 307 -290c0 -162 -138 -289 -306 -289h-270c-17 0 -35 0 -35 20s18 20 35 20h267c156 0 269 114 269 250c0 131 -109 249 -270 249zM548 -52l-132 -154h310c17 0 35 0 35 -20s-18 -20 -35 -20h-345\nl-89 -105c-21 -23 -22 -24 -31 -24c-10 0 -20 9 -20 20c0 7 0 9 19 30c16 18 59 68 68 79h-143c-17 0 -35 0 -35 20s18 20 35 20h178l132 154h-310c-17 0 -35 0 -35 20s18 20 35 20h345l89 105c21 23 22 24 31 24c10 0 20 -9 20 -20c0 -7 0 -9 -19 -30\nc-20 -23 -59 -68 -68 -79h143c17 0 35 0 35 -20s-18 -20 -35 -20h-178z"
            },
            "&#x2ac5;": {
                x: 967,
                d: "M460 214h266c17 0 35 0 35 -20s-18 -20 -35 -20h-269c-169 0 -307 127 -307 290c0 162 138 289 306 289h270c17 0 35 0 35 -20s-18 -20 -35 -20h-267c-155 0 -269 -114 -269 -250c0 -131 109 -249 270 -249zM185 -174h541c17 0 35 0 35 -20s-18 -20 -35 -20h-541\nc-17 0 -35 0 -35 20s18 20 35 20zM185 20h541c17 0 35 0 35 -20s-18 -20 -35 -20h-541c-17 0 -35 0 -35 20s18 20 35 20z"
            },
            "&#x2ac6;": {
                x: 967,
                d: "M451 713h-266c-17 0 -35 0 -35 20s18 20 35 20h269c170 0 307 -127 307 -290c0 -162 -138 -289 -306 -289h-270c-17 0 -35 0 -35 20s18 20 35 20h267c156 0 269 114 269 250c0 131 -109 249 -270 249zM726 -214h-541c-17 0 -35 0 -35 20s18 20 35 20h541\nc17 0 35 0 35 -20s-18 -20 -35 -20zM726 -20h-541c-17 0 -35 0 -35 20s18 20 35 20h541c17 0 35 0 35 -20s-18 -20 -35 -20z"
            },
            "&#x2208;": {
                x: 800,
                d: "M615 230h-424c12 -134 127 -230 268 -230h156c17 0 35 0 35 -20s-18 -20 -35 -20h-158c-171 0 -307 130 -307 290s136 290 307 290h158c17 0 35 0 35 -20s-18 -20 -35 -20h-156c-141 0 -256 -96 -268 -230h424c17 0 35 0 35 -20s-18 -20 -35 -20z"
            },
            "&#x220b;": {
                x: 800,
                d: "M341 500h-156c-17 0 -35 0 -35 20s18 20 35 20h158c171 0 307 -130 307 -290s-136 -290 -307 -290h-158c-17 0 -35 0 -35 20s18 20 35 20h156c141 0 256 96 268 230h-424c-17 0 -35 0 -35 20s18 20 35 20h424c-12 134 -127 230 -268 230z"
            },
            "&#x221d;": {
                x: 966,
                d: "M816 32v-39c-9 -2 -25 -4 -37 -4c-25 0 -80 5 -141 58c-37 31 -51 53 -91 114c-24 -50 -98 -172 -225 -172c-110 0 -172 114 -172 226c0 115 64 227 175 227c25 0 80 -5 141 -58c37 -31 51 -53 91 -114c24 50 98 172 225 172c9 0 24 -1 31 -3c2 -2 3 -3 3 -29\nc-11 3 -20 3 -27 3c-119 0 -186 -124 -210 -178c11 -17 38 -58 48 -75c34 -52 87 -130 166 -130c3 0 19 2 23 2zM525 196c-11 17 -38 58 -48 75c-34 52 -87 130 -166 130c-83 0 -139 -87 -139 -186c0 -91 46 -197 143 -197c119 0 186 124 210 178z"
            },
            "&#x2224;": {
                x: 617,
                d: "M453 478l-124 -111v-582c0 -17 0 -35 -20 -35s-20 18 -20 35v545l-89 -80c-20 -20 -22 -20 -30 -20c-10 0 -20 9 -20 20c0 9 4 12 15 22l124 112v331c0 17 0 35 20 35s20 -18 20 -35v-294l89 79c15 15 20 20 29 20c11 0 20 -9 20 -20c0 -7 -2 -11 -14 -22z"
            },
            "&#x2226;": {
                x: 839,
                d: "M675 478l-138 -138v-552c0 -18 0 -38 -20 -38s-20 17 -20 34v516l-155 -157v-359c0 -17 0 -34 -20 -34s-20 20 -20 38v315l-104 -103c-19 -19 -20 -20 -28 -20c-10 0 -20 9 -20 20c0 6 3 12 14 23l138 137v552c0 18 0 38 20 38s20 -17 20 -34v-516l155 157v359\nc0 17 0 34 20 34s20 -20 20 -38v-315l100 100c23 22 24 23 32 23c11 0 20 -9 20 -20c0 -8 -2 -10 -14 -22z"
            },
            "&#x2234;": {
                x: 906,
                d: "M506 411c0 -30 -25 -53 -53 -53c-29 0 -53 24 -53 53s24 53 53 53s53 -24 53 -53zM756 -22c0 -29 -24 -53 -53 -53s-53 24 -53 53s24 53 53 53s53 -24 53 -53zM256 -22c0 -29 -24 -53 -53 -53s-53 24 -53 53s24 53 53 53s53 -24 53 -53z"
            },
            "&#x2235;": {
                x: 906,
                d: "M256 411c0 -30 -25 -53 -53 -53c-29 0 -53 24 -53 53s24 53 53 53s53 -24 53 -53zM506 -22c0 -29 -24 -53 -53 -53s-53 24 -53 53s24 53 53 53s53 -24 53 -53zM756 411c0 -30 -25 -53 -53 -53c-29 0 -53 24 -53 53s24 53 53 53s53 -24 53 -53z"
            },
            "&#x220d;": {
                x: 629,
                d: "M385 174h-162c-11 0 -19 1 -23 2s-7 5 -7 12c0 11 11 17 34 17h166c7 31 11 59 11 84c0 47 -12 81 -34 101s-47 30 -74 30c-33 0 -69 -13 -108 -39c-3 -3 -7 -4 -10 -4c-7 0 -10 5 -10 14c0 4 6 10 18 18s30 16 51 23s42 10 61 10c53 0 96 -18 130 -53s51 -81 51 -136\nc0 -73 -27 -133 -81 -181s-115 -72 -183 -72h-35c-11 0 -20 1 -24 2s-6 5 -6 12s3 12 8 14s13 3 25 3h30c45 0 82 13 111 39s49 61 61 104z"
            },
            "&#x22c8;": {
                x: 783,
                d: "M619 449l-198 -199c52 -53 201 -201 207 -208c5 -6 5 -11 5 -13c0 -11 -8 -20 -20 -20c-6 0 -13 4 -14 6c-13 12 -159 159 -208 207l-199 -199c-12 -12 -14 -14 -22 -14c-20 0 -20 19 -20 36v410c0 17 0 36 20 36c8 0 10 -2 22 -14l200 -199l199 199c10 10 14 14 22 14\nc11 0 20 -9 20 -20c0 -8 -2 -10 -14 -22zM190 77l172 173l-172 172v-345zM392 278l199 199c10 10 14 14 22 14c20 0 20 -18 20 -35v-412c0 -17 0 -35 -20 -35c-6 0 -13 4 -14 6c-13 12 -159 159 -208 207l-199 -199c-12 -12 -14 -14 -22 -14c-10 0 -20 9 -20 20\nc0 6 3 12 14 23l198 198l-198 199c-10 10 -14 14 -14 22c0 11 10 20 20 20c8 0 10 -2 22 -14zM421 250l172 -172v345z"
            },
            "&#x2322;": {
                x: 1168,
                d: "M1018 142c0 -6 -4 -10 -10 -10s-8 3 -13 9c-20 28 -69 98 -173 146c-96 44 -185 50 -238 50c-124 0 -215 -38 -250 -56c-98 -51 -137 -106 -162 -141c-4 -5 -6 -8 -12 -8s-10 4 -10 10c0 12 143 237 434 237s434 -225 434 -237z"
            },
            "&#x2323;": {
                x: 1168,
                d: "M1018 358c0 -12 -142 -224 -434 -224s-434 213 -434 224c0 6 4 10 10 10c3 0 6 0 10 -5c21 -28 64 -85 154 -131c71 -35 163 -56 260 -56c110 0 293 25 412 184c3 5 6 8 12 8s10 -4 10 -10z"
            },
            "&#x2223;": {
                x: 340,
                d: "M190 714v-928c0 -18 0 -36 -20 -36s-20 18 -20 36v928c0 18 0 36 20 36s20 -18 20 -36z"
            },
            "&#x2225;": {
                x: 535,
                d: "M190 716v-932c0 -16 0 -34 -20 -34s-20 21 -20 37v926c0 15 0 37 20 37s20 -18 20 -34zM385 713v-926c0 -15 0 -37 -20 -37s-20 18 -20 34v932c0 16 0 34 20 34s20 -21 20 -37z"
            },
            "&#x23d0;": {
                x: 340,
                d: "M190 396v-383v-17s-3 -8 -6 -12s-8 -6 -14 -6s-11 2 -14 6s-6 8 -6 12v17v382c0 24 7 36 20 36c6 0 11 -2 14 -6s6 -8 6 -12v-17z"
            },
            "&#x23d1;": {
                x: 576,
                d: "M190 393v-377c0 -25 -7 -38 -20 -38c-6 0 -11 2 -14 6s-6 7 -6 11v17v385v17s3 8 6 12s8 5 14 5c13 0 20 -13 20 -38zM426 397v-385v-17s-3 -7 -6 -11s-8 -6 -14 -6c-13 0 -20 13 -20 38v377c0 25 7 38 20 38c6 0 11 -1 14 -5s6 -8 6 -12v-17z"
            },
            "&#x23d2;": {
                x: 561,
                d: "M301 243v-230c0 -7 -1 -13 -1 -17s-2 -8 -5 -12s-8 -6 -14 -6s-11 2 -14 6s-5 8 -5 12s-1 10 -1 17v187c-9 -9 -23 -23 -41 -42l-27 -28c-10 -9 -18 -14 -23 -14c-6 0 -10 2 -14 6s-6 9 -6 14s5 12 14 22l22 23l75 77v137c0 24 7 36 20 36c6 0 11 -2 14 -6s5 -8 5 -12\ns1 -10 1 -17v-95l23 23c10 10 19 20 28 29l15 15c10 11 18 16 24 16s11 -2 15 -6s5 -9 5 -14s-4 -12 -13 -22l-22 -22c-30 -33 -55 -58 -75 -77z"
            },
            "&#x23d3;": {
                x: 728,
                d: "M266 215l196 116v62c0 25 7 38 20 38c6 0 11 -1 14 -5s6 -8 6 -12v-17v-42c18 10 27 15 27 16c14 9 24 13 29 13c6 0 10 -2 14 -6s6 -9 6 -14c0 -6 -4 -12 -13 -19l-63 -37v-296v-17s-3 -7 -6 -11s-8 -6 -14 -6c-13 0 -20 13 -20 38v269l-196 -116v-153\nc0 -25 -7 -38 -20 -38c-6 0 -11 2 -14 6s-6 7 -6 11v17v133c-18 -10 -27 -15 -27 -16c-14 -9 -24 -13 -29 -13c-6 0 -10 2 -14 6s-6 9 -6 14c0 6 4 12 13 19l63 37v205v17s3 8 6 12s8 5 14 5c13 0 20 -13 20 -38v-178z"
            },
            "&#x2ac7;": {
                x: 967,
                d: "M684 713l-223 -499h265c17 0 35 0 35 -20s-18 -20 -35 -20h-283l-68 -154h351c17 0 35 0 35 -20s-18 -20 -35 -20h-369l-69 -154h438c17 0 35 0 35 -20s-18 -20 -35 -20h-456c-14 -33 -29 -65 -44 -97c-3 -7 -7 -17 -20 -17c-8 0 -20 6 -20 20c0 4 13 33 20 49\nc4 8 18 41 20 45h-41c-17 0 -35 0 -35 20s18 20 35 20h59l69 154h-128c-17 0 -35 0 -35 20s18 20 35 20h146l71 159c-153 26 -252 148 -252 285c0 162 138 289 306 289h203c7 17 9 23 19 43c12 26 14 32 27 32c8 0 20 -6 20 -20c0 -6 -8 -22 -23 -55h40c11 -1 19 -10 19 -20\nc0 -20 -18 -20 -35 -20h-42zM641 713h-182c-155 0 -269 -114 -269 -250c0 -100 61 -178 134 -217c46 -24 88 -29 92 -29c3 0 3 1 5 5l3 8z"
            },
            "&#x2ac8;": {
                x: 967,
                d: "M418 214l199 448c-56 39 -111 51 -166 51h-266c-17 0 -35 0 -35 20s18 20 35 20h269c64 0 127 -19 181 -54l47 108c5 13 12 21 23 21c8 0 20 -6 20 -20c0 -4 -4 -13 -6 -18l-52 -118c43 -36 94 -111 94 -209c0 -136 -107 -289 -318 -289l-68 -154h351c17 0 35 0 35 -20\ns-18 -20 -35 -20h-369l-69 -154h438c17 0 35 0 35 -20s-18 -20 -35 -20h-456c-14 -33 -29 -65 -44 -97c-3 -7 -7 -17 -20 -17c-8 0 -20 6 -20 20c0 4 13 33 20 49c4 8 18 41 20 45h-41c-17 0 -35 0 -35 20s18 20 35 20h59l69 154h-128c-17 0 -35 0 -35 20s18 20 35 20h146\nl69 154h-215c-17 0 -35 0 -35 20s18 20 35 20h233zM649 634l-188 -420c168 8 260 130 260 250c0 64 -27 125 -72 170z"
            },
            "&#x22ae;": {
                x: 1020,
                d: "M857 653l-297 -286h275c17 0 35 0 35 -20s-18 -20 -35 -20h-317c-8 -7 -16 -14 -23 -22v-270c0 -17 0 -35 -20 -35s-20 19 -20 36v230l-154 -148v-83c0 -17 0 -35 -20 -35s-20 19 -20 36v44l-45 -44c-31 -30 -37 -36 -46 -36c-14 0 -20 11 -20 20c0 11 13 22 31 39l73 70\nc7 6 7 8 7 26v503c0 17 0 36 20 36s20 -18 20 -35v-485l154 148v336c0 17 0 36 20 36s20 -18 20 -35v-292c2 1 7 1 9 2l321 309c13 13 18 16 25 16c11 0 20 -8 20 -20c0 -9 -2 -11 -13 -21z"
            },
            "&#x22ac;": {
                x: 909,
                d: "M464 327l-163 -187v-105c0 -17 0 -35 -20 -35s-20 19 -20 36v58l-66 -75c-12 -14 -16 -19 -26 -19s-19 9 -19 20c0 3 1 9 4 14c4 4 14 14 17 19c55 63 55 65 90 102v503c0 17 0 36 20 36s20 -18 20 -35v-292h144l272 311c12 14 14 16 22 16c11 0 20 -8 20 -20\nc0 -8 -3 -12 -12 -22l-248 -285h225c17 0 35 0 35 -20s-18 -20 -35 -20h-260zM410 327h-109v-125z"
            },
            "&#x2ac9;": {
                x: 965,
                d: "M584 316c29 101 128 156 205 156c6 0 26 0 26 -20c0 -19 -16 -19 -27 -20c-103 -6 -174 -89 -174 -182c0 -69 45 -175 183 -182c8 -1 18 -7 18 -20c0 -20 -20 -20 -26 -20c-72 0 -172 49 -206 156c-30 -98 -121 -156 -211 -156c-118 0 -222 94 -222 222s104 222 222 222\nc84 0 177 -51 212 -156zM372 68c94 0 181 75 181 182s-87 182 -181 182c-97 0 -182 -77 -182 -182s85 -182 182 -182z"
            },
            "&#x23d4;": {
                x: 967,
                d: "M546 57l-129 -154h309c17 0 35 0 35 -20s-18 -20 -35 -20h-343l-74 -90c-18 -20 -19 -21 -28 -21c-14 0 -20 13 -20 20s4 12 13 22c11 14 43 51 57 69h-126c-17 0 -35 0 -35 20s18 20 35 20h160l128 154c-69 0 -155 0 -243 75c-61 52 -100 129 -100 215\nc0 162 138 289 306 289h270c17 0 35 0 35 -20s-18 -20 -35 -20h-267c-155 0 -269 -114 -269 -250c0 -131 109 -249 270 -249h68l74 90c18 20 19 21 28 21c14 0 20 -13 20 -20s-4 -12 -13 -22c-11 -14 -43 -51 -57 -69h146c17 0 35 0 35 -20s-18 -20 -35 -20h-180z"
            },
            "&#x23d5;": {
                x: 967,
                d: "M706 -137h-452l-60 -91c-10 -15 -13 -20 -24 -20s-20 9 -20 20c0 6 3 11 12 25l44 66h-37c-11 1 -19 10 -19 20c0 20 18 20 35 20h49l102 154h-151c-17 0 -35 0 -35 20s18 20 35 20h178l61 91c10 15 13 20 24 20c15 0 20 -13 20 -20c0 -5 0 -7 -13 -25l-44 -66h41\nc156 0 269 114 269 250c0 131 -109 249 -270 249h-266c-17 0 -35 0 -35 20s18 20 35 20h269c170 0 307 -127 307 -290c0 -162 -138 -289 -306 -289h-71l-102 -154h424c17 0 35 0 35 -20s-18 -20 -35 -20z"
            },
            "&#x23d6;": {
                x: 967,
                d: "M645 174l-99 -154h180c17 0 35 0 35 -20s-18 -20 -35 -20h-206l-99 -154h305c17 0 35 0 35 -20s-18 -20 -35 -20h-331l-59 -91c-9 -14 -12 -20 -23 -20c-12 0 -20 9 -20 20c0 5 0 7 13 27l41 64h-162c-17 0 -35 0 -35 20s18 20 35 20h188l99 154h-287c-17 0 -35 0 -35 20\ns18 20 35 20h313l99 154h-140c-169 0 -307 127 -307 290c0 162 138 289 306 289h270c17 0 35 0 35 -20s-18 -20 -35 -20h-267c-155 0 -269 -114 -269 -250c0 -131 109 -249 270 -249h163l58 91c11 16 13 20 24 20s20 -9 20 -20c0 -6 0 -7 -13 -27c-14 -21 -28 -42 -41 -64\nh55c17 0 35 0 35 -20s-18 -20 -35 -20h-81z"
            },
            "&#x23d7;": {
                x: 967,
                d: "M423 174l-63 -154h366c17 0 35 0 35 -20s-18 -20 -35 -20h-382l-63 -154h445c17 0 35 0 35 -20s-18 -20 -35 -20h-461l-32 -79c-12 -27 -14 -32 -27 -32c-11 0 -20 9 -20 20c0 4 11 30 17 44l18 47h-46c-5 0 -25 0 -25 20s18 20 35 20h53l63 154h-116c-17 0 -35 0 -35 20\ns18 20 35 20h132l63 154h-195c-17 0 -35 0 -35 20s18 20 35 20h212c16 41 16 43 38 92c3 9 7 19 20 19s20 -10 20 -20c0 -4 -10 -30 -16 -45l-19 -46c38 0 122 0 199 69c49 43 82 106 82 181c0 131 -109 249 -270 249h-266c-17 0 -35 0 -35 20s18 20 35 20h269\nc170 0 307 -127 307 -290c0 -102 -56 -177 -97 -211c-92 -78 -173 -78 -241 -78z"
            },
            "&#x21c7;": {
                x: 1187,
                d: "M1004 397h-774c22 -21 74 -70 102 -147c-28 -77 -80 -126 -102 -147h774c18 0 33 0 33 -20s-19 -20 -38 -20h-768c23 -22 71 -67 101 -146h-31c-29 74 -75 126 -151 167c52 24 116 76 151 166c-33 84 -93 137 -151 167c74 37 122 93 151 166h31\nc-30 -79 -78 -124 -101 -146h768c19 0 38 0 38 -20s-15 -20 -33 -20z"
            },
            "&#x21c9;": {
                x: 1187,
                d: "M957 397h-774c-18 0 -33 0 -33 20s19 20 38 20h768c-23 22 -71 67 -101 146h31c29 -74 75 -126 151 -167c-52 -24 -116 -76 -151 -166c33 -84 93 -137 151 -167c-74 -37 -122 -93 -151 -166h-31c30 79 78 124 101 146h-768c-19 0 -38 0 -38 20s15 20 33 20h774\nc-22 21 -74 70 -102 147c28 77 80 126 102 147z"
            },
            "&#x21bc;": {
                x: 1188,
                d: "M236 196h766c17 0 36 0 36 -20s-18 -20 -36 -20h-852v20c55 27 118 82 150 168h32c-8 -25 -27 -87 -96 -148z"
            },
            "&#x21bd;": {
                x: 1188,
                d: "M150 344h854c17 0 34 0 34 -20s-16 -20 -34 -20h-768c69 -61 88 -123 96 -148h-30c-32 85 -96 141 -152 168v20z"
            },
            "&#x21c0;": {
                x: 1188,
                d: "M184 196h768c-69 61 -88 123 -96 148h30c32 -85 96 -141 152 -168v-20h-854c-17 0 -34 0 -34 20s16 20 34 20z"
            },
            "&#x21c1;": {
                x: 1188,
                d: "M186 344h852v-20c-55 -27 -118 -82 -150 -168h-32c8 25 27 87 96 148h-766c-17 0 -36 0 -36 20s18 20 36 20z"
            },
            "&#x219a;": {
                x: 1186,
                d: "M603 230l-100 -151c-10 -15 -17 -15 -22 -15c-10 0 -19 8 -19 20c0 6 10 22 17 33l76 113h-321c75 -55 108 -138 108 -146c0 -9 -8 -12 -15 -12c-11 0 -13 5 -17 14c-27 58 -65 112 -148 151c-7 4 -12 6 -12 13s3 8 14 14c100 47 132 119 147 154c2 4 4 10 16 10\nc7 0 15 -3 15 -12c0 -8 -33 -91 -108 -146h348l100 151c10 15 17 15 22 15c10 0 19 -8 19 -20c0 -6 -10 -22 -17 -33l-76 -113h371c17 0 35 0 35 -20s-18 -20 -35 -20h-398z"
            },
            "&#x219b;": {
                x: 1186,
                d: "M604 230l-100 -151c-10 -15 -17 -15 -22 -15c-10 0 -19 8 -19 20c0 6 10 22 17 33l76 113h-371c-17 0 -35 0 -35 20s18 20 35 20h398l100 151c10 15 17 15 22 15c10 0 19 -8 19 -20c0 -6 -10 -22 -17 -33l-76 -113h321c-75 55 -108 138 -108 146c0 9 8 12 15 12\nc11 0 13 -5 17 -14c41 -92 99 -129 153 -154c1 0 7 -3 7 -10s-2 -8 -14 -14c-100 -47 -132 -120 -147 -154c-2 -4 -4 -10 -16 -10c-7 0 -15 3 -15 12c0 8 33 91 108 146h-348z"
            },
            "&#x27f5;": {
                x: 1544,
                d: "M259 282h1102c18 0 21 0 27 -6c4 -4 6 -9 6 -14s-2 -10 -6 -14c-6 -6 -8 -6 -25 -6l-1104 -1c21 -14 21 -14 44 -37c16 -16 49 -52 74 -106c6 -16 24 -63 24 -84c0 -4 0 -7 -2 -9s-9 -3 -16 -3s-15 1 -17 3c-3 3 -3 5 -5 13c-18 83 -56 129 -86 159\nc-41 41 -83 62 -111 72c-8 4 -9 5 -10 6c-2 2 -4 5 -4 7s2 5 4 7s5 4 11 6c24 8 67 29 110 72c18 18 67 66 86 159c1 7 2 10 5 13c2 2 10 3 17 3s14 -1 16 -3s2 -6 2 -10c0 -23 -22 -77 -24 -83c-26 -56 -60 -92 -74 -106c-13 -13 -30 -28 -44 -38z"
            },
            "&#x27f6;": {
                x: 1544,
                d: "M1285 241l-1102 1c-18 0 -21 0 -27 6c-4 4 -6 9 -6 14s2 10 6 14c6 6 9 6 26 6l1103 1c-21 14 -21 14 -44 37c-16 16 -49 52 -74 106c-6 16 -24 63 -24 84c0 4 0 7 2 9s10 3 17 3s14 -1 16 -3c3 -3 4 -5 6 -13c18 -83 55 -129 85 -159c41 -41 83 -62 111 -72\nc8 -4 10 -5 11 -6c2 -2 3 -5 3 -7s-1 -5 -3 -7s-6 -4 -12 -6c-24 -8 -67 -29 -110 -72c-18 -18 -66 -66 -85 -159c-1 -7 -3 -10 -6 -13c-2 -2 -9 -3 -16 -3s-15 1 -17 3s-2 6 -2 10c0 23 22 77 24 83c26 56 60 92 74 106c13 13 30 27 44 37z"
            },
            "&#x27f7;": {
                x: 1824,
                d: "M259 270h1306c-43 32 -72 67 -92 97c-42 67 -51 130 -51 132c0 12 12 12 20 12c17 0 18 -3 21 -15c38 -168 159 -218 203 -235c2 -1 8 -4 8 -11s-3 -9 -15 -13c-113 -46 -172 -130 -195 -228c-4 -18 -5 -20 -22 -20c-8 0 -20 0 -20 12c0 1 8 64 53 133c8 13 35 54 90 96\nh-1306c43 -32 72 -67 92 -97c42 -67 51 -130 51 -132c0 -12 -12 -12 -20 -12c-17 0 -18 3 -21 15c-25 112 -92 190 -196 233c-10 4 -15 6 -15 13s4 9 15 13c113 47 172 130 195 228c4 18 5 20 22 20c8 0 20 0 20 -12c0 -1 -8 -64 -53 -133c-8 -13 -35 -54 -90 -96z"
            },
            "&#x27f9;": {
                x: 1594,
                d: "M185 367h997c-49 47 -100 134 -100 147c0 11 13 11 19 11c14 0 15 -1 26 -21c56 -107 156 -198 291 -238c19 -6 20 -7 22 -8l3 -2c1 -2 1 -4 1 -6s0 -5 -2 -6c-2 -2 -3 -3 -20 -9c-52 -15 -119 -42 -188 -103c-65 -58 -93 -111 -114 -149c-4 -8 -11 -8 -19 -8\nc-6 0 -19 0 -19 11c0 13 52 101 100 147h-997c-17 0 -35 0 -35 20s18 20 36 20h1041c41 35 90 61 126 77c-33 15 -84 41 -126 77h-1041c-18 0 -36 0 -36 20s18 20 35 20z"
            },
            "&#x27f8;": {
                x: 1594,
                d: "M1409 133h-997c49 -47 100 -134 100 -147c0 -11 -13 -11 -19 -11c-14 0 -15 1 -26 21c-56 107 -156 198 -291 238c-19 6 -20 7 -22 8l-3 2c-1 2 -1 4 -1 6s0 5 2 6c2 2 3 3 20 9c52 15 119 42 188 103c65 58 93 111 114 149c4 8 11 8 19 8c6 0 19 0 19 -11\nc0 -13 -52 -101 -100 -147h997c17 0 35 0 35 -20s-18 -20 -36 -20h-1041c-41 -35 -90 -61 -126 -77c33 -15 84 -41 126 -77h1041c18 0 36 0 36 -20s-18 -20 -35 -20z"
            },
            "&#x27fa;": {
                x: 1694,
                d: "M370 367h954c-57 65 -87 138 -87 146c0 12 11 12 20 12c12 0 16 -1 20 -10c51 -116 133 -201 243 -247c20 -9 21 -10 23 -12c1 -2 1 -4 1 -6c0 -7 -3 -8 -17 -14c-131 -56 -205 -148 -246 -240c-9 -20 -9 -21 -24 -21c-9 0 -20 0 -20 12c0 8 30 81 87 146h-954\nc57 -65 87 -138 87 -146c0 -12 -11 -12 -20 -12c-12 0 -16 1 -20 10c-51 116 -133 201 -243 247c-20 9 -21 10 -23 12c-1 2 -1 4 -1 6c0 7 3 8 17 14c131 56 205 148 246 240c9 20 9 21 24 21c9 0 20 0 20 -12c0 -8 -30 -81 -87 -146zM331 173h1032c20 20 50 45 105 77\nc-41 24 -75 48 -105 77h-1032c-20 -20 -50 -45 -105 -77c41 -24 75 -48 105 -77z"
            },
            "&#x2262;": {
                x: 967,
                d: "M751 648l-492 -791c-8 -13 -10 -16 -19 -17c-14 -2 -22 10 -23 17s3 12 9 22l494 793c8 11 10 14 19 15c10 1 20 -5 22 -16c1 -8 -2 -12 -10 -23zM781 424h-595c-24 0 -36 7 -36 20c0 6 2 11 6 14s8 6 12 6h17h597h17s8 -3 12 -6s6 -8 6 -14c0 -13 -12 -20 -36 -20z\nM782 36h-597h-17s-8 3 -12 6s-6 8 -6 14c0 13 12 20 36 20h595c24 0 36 -7 36 -20c0 -6 -2 -11 -6 -14s-8 -6 -12 -6h-17zM782 230h-597h-17s-8 3 -12 6s-6 8 -6 14s2 11 6 14s8 6 12 6h17h597h17s8 -3 12 -6s6 -8 6 -14s-2 -11 -6 -14s-8 -6 -12 -6h-17z"
            },
            "&#x2260;": {
                x: 927,
                d: "M228 -50l409 646c8 13 11 16 20 17c14 2 21 -11 22 -18s-3 -12 -9 -22l-411 -648c-9 -11 -10 -14 -19 -15c-10 -1 -21 6 -22 17c-1 8 1 12 10 23zM747 321h-567c-20 0 -30 6 -30 17c0 12 11 18 34 18h559c23 0 34 -6 34 -18c0 -11 -10 -17 -30 -17zM743 143h-559\nc-23 0 -34 6 -34 18c0 11 10 17 30 17h567c20 0 30 -6 30 -17c0 -12 -11 -18 -34 -18z"
            },
            "&#x2209;": {
                x: 800,
                d: "M261 -181l319 929c5 15 6 21 18 23c13 2 22 -9 23 -17c0 -3 1 -5 -5 -20l-318 -929c-5 -15 -7 -21 -19 -23c-14 -2 -22 10 -23 17c0 3 -1 5 5 20zM615 230h-424c12 -134 127 -230 268 -230h156c17 0 35 0 35 -20s-18 -20 -35 -20h-158c-171 0 -307 130 -307 290\ns136 290 307 290h158c17 0 35 0 35 -20s-18 -20 -35 -20h-156c-141 0 -256 -96 -268 -230h424c17 0 35 0 35 -20s-18 -20 -35 -20z"
            }
        },
        map: {
            Alpha: "\u0391",
            Beta: "\u0392",
            Gamma: "\u0393",
            Delta: "\u0394",
            Epsilon: "\u0395",
            Zeta: "\u0396",
            Eta: "\u0397",
            Theta: "\u0398",
            Iota: "\u0399",
            Kappa: "\u039a",
            Lambda: "\u039b",
            Mu: "\u039c",
            Nu: "\u039d",
            Xi: "\u039e",
            Omicron: "\u039f",
            Pi: "\u03a0",
            Rho: "\u03a1",
            Sigma: "\u03a3",
            Tau: "\u03a4",
            Upsilon: "\u03a5",
            Phi: "\u03a6",
            Chi: "\u03a7",
            Psi: "\u03a8",
            Omega: "\u03a9",
            alpha: "\u03b1",
            beta: "\u03b2",
            gamma: "\u03b3",
            delta: "\u03b4",
            epsilon: "\u03b5",
            varepsilon: "\u03b5",
            zeta: "\u03b6",
            eta: "\u03b7",
            theta: "\u03b8",
            iota: "\u03b9",
            kappa: "\u03ba",
            lambda: "\u03bb",
            mu: "\u03bc",
            nu: "\u03bd",
            xi: "\u03be",
            omicron: "\u03bf",
            pi: "\u03c0",
            rho: "\u03c1",
            sigma: "\u03c3",
            tau: "\u03c4",
            upsilon: "\u03c5",
            phi: "\u03c6",
            varkappa: "\u03f0",
            chi: "\u03c7",
            psi: "\u03c8",
            omega: "\u03c9",
            digamma: "\u03dc",
            varepsilon: "\u03f5",
            varrho: "\u03f1",
            varphi: "\u03d5",
            vartheta: "\u03d1",
            varpi: "\u03d6",
            varsigma: "\u03f9",
            aleph: "\u2135",
            beth: "\u2136",
            daleth: "\u2138",
            gimel: "\u2137",
            eth: "\xf0",
            hbar: "\u210e",
            hslash: "\u210f",
            mho: "\u2127",
            partial: "\u2202",
            wp: "\u2118",
            Game: "\u2141",
            Bbbk: "\u214c",
            Finv: "\u2132",
            Im: "\u2111",
            Re: "\u211c",
            complement: "\u2201",
            ell: "\u2113",
            circledS: "\u24c8",
            imath: "\u0131",
            jmath: "\u0237",
            doublecap: "\u22d2",
            Cap: "\u22d2",
            doublecup: "\u22d3",
            Cup: "\u22d3",
            ast: "*",
            divideontimes: "\u22c7",
            rightthreetimes: "\u22cc",
            leftthreetimes: "\u22cb",
            cdot: "\xb7",
            odot: "\u2299",
            dotplus: "\u2214",
            rtimes: "\u22ca",
            ltimes: "\u22c9",
            centerdot: "\u25aa",
            doublebarwedge: "\u232d",
            setminus: "\u2481",
            amalg: "\u2210",
            circ: "\u25e6",
            bigcirc: "\u25ef",
            gtrdot: "\u22d7",
            lessdot: "\u22d6",
            smallsetminus: "\u2485",
            circledast: "\u229b",
            circledcirc: "\u229a",
            sqcap: "\u2293",
            sqcup: "\u2294",
            barwedge: "\u22bc",
            circleddash: "\u229d",
            star: "\u22c6",
            bigtriangledown: "\u25bd",
            bigtriangleup: "\u25b3",
            cup: "\u222a",
            cap: "\u2229",
            times: "\xd7",
            mp: "\u2213",
            pm: "\xb1",
            triangleleft: "\u22b2",
            triangleright: "\u22b3",
            boxdot: "\u22a1",
            curlyvee: "\u22cf",
            curlywedge: "\u22ce",
            boxminus: "\u229f",
            boxtimes: "\u22a0",
            ominus: "\u2296",
            oplus: "\u2295",
            oslash: "\u2298",
            otimes: "\u2297",
            uplus: "\u228e",
            boxplus: "\u229e",
            dagger: "\u2020",
            ddagger: "\u2021",
            vee: "\u2228",
            lor: "\u2228",
            veebar: "\u22bb",
            bullet: "\u2022",
            diamond: "\u22c4",
            wedge: "\u2227",
            land: "\u2227",
            div: "\xf7",
            wr: "\u2240",
            geqq: "\u2267",
            lll: "\u22d8",
            llless: "\u22d8",
            ggg: "\u22d9",
            gggtr: "\u22d9",
            preccurlyeq: "\u227c",
            geqslant: "\u2a7e",
            lnapprox: "\u2a89",
            preceq: "\u2aaf",
            gg: "\u226b",
            lneq: "\u2a87",
            precnapprox: "\u2ab9",
            approx: "\u2248",
            lneqq: "\u2268",
            precneqq: "\u2ab5",
            approxeq: "\u224a",
            gnapprox: "\u2a8a",
            lnsim: "\u22e6",
            precnsim: "\u22e8",
            asymp: "\u224d",
            gneq: "\u2a88",
            lvertneqq: "\u232e",
            precsim: "\u227e",
            backsim: "\u223d",
            gneqq: "\u2269",
            ncong: "\u2247",
            risingdotseq: "\u2253",
            backsimeq: "\u22cd",
            gnsim: "\u22e7",
            sim: "\u223c",
            simeq: "\u2243",
            bumpeq: "\u224f",
            gtrapprox: "\u2a86",
            ngeq: "\u2271",
            Bumpeq: "\u224e",
            gtreqless: "\u22db",
            ngeqq: "\u2331",
            succ: "\u227b",
            circeq: "\u2257",
            gtreqqless: "\u2a8c",
            ngeqslant: "\u2333",
            succapprox: "\u2ab8",
            cong: "\u2245",
            gtrless: "\u2277",
            ngtr: "\u226f",
            succcurlyeq: "\u227d",
            curlyeqprec: "\u22de",
            gtrsim: "\u2273",
            nleq: "\u2270",
            succeq: "\u2ab0",
            curlyeqsucc: "\u22df",
            gvertneqq: "\u232f",
            neq: "\u2260",
            ne: "\u2260",
            nequiv: "\u2262",
            nleqq: "\u2330",
            succnapprox: "\u2aba",
            doteq: "\u2250",
            leq: "\u2264",
            le: "\u2264",
            nleqslant: "\u2332",
            succneqq: "\u2ab6",
            doteqdot: "\u2251",
            Doteq: "\u2251",
            leqq: "\u2266",
            nless: "\u226e",
            succnsim: "\u22e9",
            leqslant: "\u2a7d",
            nprec: "\u2280",
            succsim: "\u227f",
            eqsim: "\u2242",
            lessapprox: "\u2a85",
            npreceq: "\u22e0",
            eqslantgtr: "\u2a96",
            lesseqgtr: "\u22da",
            nsim: "\u2241",
            eqslantless: "\u2a95",
            lesseqqgtr: "\u2a8b",
            nsucc: "\u2281",
            triangleq: "\u225c",
            eqcirc: "\u2256",
            equiv: "\u2261",
            lessgtr: "\u2276",
            nsucceq: "\u22e1",
            fallingdotseq: "\u2252",
            lesssim: "\u2272",
            prec: "\u227a",
            geq: "\u2265",
            ge: "\u2265",
            ll: "\u226a",
            precapprox: "\u2ab7",
            uparrow: "\u2191",
            downarrow: "\u2193",
            updownarrow: "\u2195",
            Uparrow: "\u21d1",
            Downarrow: "\u21d3",
            Updownarrow: "\u21d5",
            circlearrowleft: "\u21ba",
            circlearrowright: "\u21bb",
            curvearrowleft: "\u21b6",
            curvearrowright: "\u21b7",
            downdownarrows: "\u21ca",
            downharpoonleft: "\u21c3",
            downharpoonright: "\u21c2",
            leftarrow: "\u2190",
            gets: "\u2190",
            Leftarrow: "\u21d0",
            leftarrowtail: "\u21a2",
            leftharpoondown: "\u21bd",
            leftharpoonup: "\u21bc",
            leftleftarrows: "\u21c7",
            leftrightarrow: "\u2194",
            Leftrightarrow: "\u21d4",
            leftrightarrows: "\u21c4",
            leftrightharpoons: "\u21cb",
            leftrightsquigarrow: "\u21ad",
            Lleftarrow: "\u21da",
            looparrowleft: "\u21ab",
            looparrowright: "\u21ac",
            multimap: "\u22b8",
            nLeftarrow: "\u21cd",
            nRightarrow: "\u21cf",
            nLeftrightarrow: "\u21ce",
            nearrow: "\u2197",
            nleftarrow: "\u219a",
            nleftrightarrow: "\u21ae",
            nrightarrow: "\u219b",
            nwarrow: "\u2196",
            rightarrow: "\u2192",
            to: "\u2192",
            Rightarrow: "\u21d2",
            rightarrowtail: "\u21a3",
            rightharpoondown: "\u21c1",
            rightharpoonup: "\u21c0",
            rightleftarrows: "\u21c6",
            rightleftharpoons: "\u21cc",
            rightrightarrows: "\u21c9",
            rightsquigarrow: "\u21dd",
            Rrightarrow: "\u21db",
            searrow: "\u2198",
            swarrow: "\u2199",
            twoheadleftarrow: "\u219e",
            twoheadrightarrow: "\u21a0",
            upharpoonleft: "\u21bf",
            upharpoonright: "\u21be",
            restriction: "\u21be",
            upuparrows: "\u21c8",
            Lsh: "\u21b0",
            Rsh: "\u21b1",
            longleftarrow: "\u27f5",
            longrightarrow: "\u27f6",
            Longleftarrow: "\u27f8",
            Longrightarrow: "\u27f9",
            implies: "\u27f9",
            longleftrightarrow: "\u27f7",
            Longleftrightarrow: "\u27fa",
            backepsilon: "\u220d",
            because: "\u2235",
            therefore: "\u2234",
            between: "\u226c",
            blacktriangleleft: "\u25c0",
            blacktriangleright: "\u25b8",
            dashv: "\u22a3",
            bowtie: "\u22c8",
            frown: "\u2322",
            "in": "\u2208",
            notin: "\u2209",
            mid: "\u2223",
            parallel: "\u2225",
            models: "\u22a8",
            ni: "\u220b",
            owns: "\u220b",
            nmid: "\u2224",
            nparallel: "\u2226",
            nshortmid: "\u23d2",
            nshortparallel: "\u23d3",
            nsubseteq: "\u2288",
            nsubseteqq: "\u2ac7",
            nsupseteq: "\u2289",
            nsupseteqq: "\u2ac8",
            ntriangleleft: "\u22ea",
            ntrianglelefteq: "\u22ec",
            ntriangleright: "\u22eb",
            ntrianglerighteq: "\u22ed",
            nvdash: "\u22ac",
            nVdash: "\u22ae",
            nvDash: "\u22ad",
            nVDash: "\u22af",
            perp: "\u22a5",
            pitchfork: "\u22d4",
            propto: "\u221d",
            shortmid: "\u23d0",
            shortparallel: "\u23d1",
            smile: "\u2323",
            sqsubset: "\u228f",
            sqsubseteq: "\u2291",
            sqsupset: "\u2290",
            sqsupseteq: "\u2292",
            subset: "\u2282",
            Subset: "\u22d0",
            subseteq: "\u2286",
            subseteqq: "\u2ac5",
            subsetneq: "\u228a",
            subsetneqq: "\u2acb",
            supset: "\u2283",
            Supset: "\u22d1",
            supseteq: "\u2287",
            supseteqq: "\u2ac6",
            supsetneq: "\u228b",
            supsetneqq: "\u2acc",
            trianglelefteq: "\u22b4",
            trianglerighteq: "\u22b5",
            varpropto: "\u2ac9",
            varsubsetneq: "\u23d4",
            varsubsetneqq: "\u23d6",
            varsupsetneq: "\u23d5",
            varsupsetneqq: "\u23d7",
            vdash: "\u22a2",
            Vdash: "\u22a9",
            vDash: "\u22a8",
            Vvdash: "\u22aa",
            vert: "|",
            Vert: "\u01c1",
            "|": "\u01c1",
            "{": "{",
            "}": "}",
            backslash: "\\",
            langle: "\u3008",
            rangle: "\u3009",
            lceil: "\u2308",
            rceil: "\u2309",
            lbrace: "{",
            rbrace: "}",
            lfloor: "\u230a",
            rfloor: "\u230b",
            cdots: "\u22ef",
            ddots: "\u22f0",
            vdots: "\u22ee",
            dots: "\u2026",
            ldots: "\u2026",
            "#": "#",
            bot: "\u22a5",
            angle: "\u2220",
            backprime: "\u2035",
            bigstar: "\u2605",
            blacklozenge: "\u25c6",
            blacksquare: "\u25a0",
            blacktriangle: "\u25b2",
            blacktriangledown: "\u25bc",
            clubsuit: "\u2663",
            diagdown: "\u2481",
            diagup: "\u2482",
            diamondsuit: "\u2662",
            emptyset: "\xf8",
            exists: "\u2203",
            flat: "\u266d",
            forall: "\u2200",
            heartsuit: "\u2661",
            infty: "\u221e",
            lozenge: "\u25c7",
            measuredangle: "\u2221",
            nabla: "\u2207",
            natural: "\u266e",
            neg: "\xac",
            lnot: "\xac",
            nexists: "\u2204",
            prime: "\u2032",
            sharp: "\u266f",
            spadesuit: "\u2660",
            sphericalangle: "\u2222",
            surd: "\u221a",
            top: "\u22a4",
            varnothing: "\u2205",
            triangle: "\u25b3",
            triangledown: "\u25bd"
        }
    };
});
define("font/map/kf-ams-roman", [], function(require) {
    return {
        meta: {
            fontFamily: "KF AMS ROMAN",
            src: "KF_AMS_ROMAN.woff"
        },
        data: {
            A: {
                x: 746,
                d: "M390 691l222 -628c13 -37 31 -37 84 -37v-26c-24 2 -74 2 -100 2c-31 0 -83 0 -112 -2v26c19 0 62 0 62 27c0 4 0 6 -5 18l-60 170h-262l-53 -149c-2 -6 -4 -11 -4 -20c0 -12 7 -44 54 -46v-26c-24 2 -64 2 -89 2c-19 0 -59 0 -77 -2v26c35 0 75 11 94 65l212 600\nc5 13 7 16 17 16s12 -3 17 -16zM350 611l-122 -344h244z"
            },
            B: {
                x: 655,
                d: "M50 683h318c129 0 211 -85 211 -168c0 -76 -67 -140 -163 -159c107 -7 189 -84 189 -174c0 -91 -83 -182 -211 -182h-344v26c69 0 80 0 80 45v541c0 45 -11 45 -80 45v26zM193 363h144c108 0 169 76 169 152c0 62 -44 142 -143 142h-128c-40 0 -42 -5 -42 -39v-255z\nM235 26h130c109 0 166 84 166 157s-50 164 -153 164h-185v-282c0 -34 2 -39 42 -39z"
            },
            C: {
                x: 675,
                d: "M625 679v-237c0 -18 0 -20 -11 -20c-9 0 -9 2 -11 14c-19 139 -101 237 -214 237c-97 0 -263 -70 -263 -331c0 -260 163 -332 265 -332c108 0 208 86 216 226c1 9 1 12 9 12c9 0 9 -4 9 -14c0 -115 -95 -250 -248 -250c-172 0 -327 150 -327 358c0 206 155 357 326 357\nc77 0 137 -36 186 -98l44 84c7 12 8 13 12 13c6 0 7 -1 7 -19z"
            },
            D: {
                x: 708,
                d: "M50 683h318c163 0 290 -158 290 -347c0 -188 -129 -336 -290 -336h-318v26c69 0 80 0 80 45v541c0 45 -11 45 -80 45v26zM236 26h112c95 0 152 54 176 85c19 27 60 84 60 225c0 272 -153 321 -236 321h-112c-40 0 -42 -5 -42 -39v-553c0 -34 2 -39 42 -39z"
            },
            E: {
                x: 656,
                d: "M606 253l-36 -253h-520v26c69 0 80 0 80 45v539c0 45 -11 45 -80 45v26h506l24 -221h-18c-14 133 -32 195 -187 195h-137c-40 0 -42 -5 -42 -39v-249h94c94 0 103 34 103 117h18v-260h-18c0 83 -9 117 -103 117h-94v-276c0 -34 2 -39 42 -39h139c176 0 189 80 211 227h18\nz"
            },
            F: {
                x: 617,
                d: "M543 681l24 -221h-18c-14 133 -31 195 -182 195h-129c-40 0 -42 -5 -42 -39v-262h90c93 0 102 33 102 117h18v-260h-18c0 84 -9 117 -102 117h-90v-256c0 -36 2 -46 78 -46h22v-26c-41 2 -89 2 -130 2c-31 0 -87 0 -116 -2v26c69 0 80 0 80 45v539c0 45 -11 45 -80 45v26\nh493z"
            },
            G: {
                x: 740,
                d: "M625 199v-179c0 -18 -1 -19 -6 -19s-33 28 -50 69c-31 -57 -110 -86 -191 -86c-176 0 -328 153 -328 358c0 206 155 357 326 357c77 0 137 -36 186 -98l44 84c7 12 8 13 12 13c6 0 7 -1 7 -19v-237c0 -18 0 -20 -11 -20c-9 0 -9 2 -11 14c-19 139 -101 237 -214 237\nc-97 0 -263 -70 -263 -331s169 -332 272 -332c31 0 161 9 161 119v66c0 36 -2 47 -88 47h-30v26c38 -1 116 -2 144 -2c27 0 81 0 105 2v-26c-62 0 -65 -5 -65 -43z"
            },
            H: {
                x: 690,
                d: "M560 612v-541c0 -45 11 -45 80 -45v-26c-29 2 -82 2 -113 2s-84 0 -113 -2v26c69 0 80 0 80 45v271h-298v-271c0 -45 11 -45 80 -45v-26c-29 2 -82 2 -113 2s-84 0 -113 -2v26c69 0 80 0 80 45v541c0 45 -11 45 -80 45v26c29 -2 82 -2 113 -2s84 0 113 2v-26\nc-69 0 -80 0 -80 -45v-244h298v244c0 45 -11 45 -80 45v26c29 -2 82 -2 113 -2s84 0 113 2v-26c-69 0 -80 0 -80 -45z"
            },
            I: {
                x: 334,
                d: "M200 612v-541c0 -45 12 -45 84 -45v-26c-32 2 -83 2 -117 2s-85 0 -117 -2v26c72 0 84 0 84 45v541c0 45 -12 45 -84 45v26c32 -2 83 -2 117 -2s85 0 117 2v-26c-72 0 -84 0 -84 -45z"
            },
            J: {
                x: 466,
                d: "M292 147v463c0 36 -2 47 -80 47h-24v26c42 -2 88 -2 130 -2c25 0 96 2 98 2v-26c-31 0 -54 0 -58 -19c-2 -6 -2 -34 -2 -51v-420c0 -34 0 -36 -1 -43c-12 -84 -84 -140 -162 -140c-82 0 -143 61 -143 128c0 29 19 44 43 44c25 0 42 -18 42 -42c0 -30 -24 -43 -43 -43\nc-4 0 -9 1 -13 2c26 -61 86 -73 112 -73c51 0 101 55 101 147z"
            },
            K: {
                x: 734,
                d: "M368 419l223 -341c30 -46 45 -52 93 -52v-26c-23 2 -64 2 -88 2c-33 0 -79 0 -111 -2v26c13 0 41 0 41 26c0 10 -7 23 -13 33l-189 290l-128 -127v-177c0 -45 11 -45 80 -45v-26c-29 2 -82 2 -113 2s-84 0 -113 -2v26c69 0 80 0 80 45v541c0 45 -11 45 -80 45v26\nc29 -2 82 -2 113 -2s84 0 113 2v-26c-69 0 -80 0 -80 -45v-339l332 333c4 6 8 17 8 24s-4 25 -30 27v26c26 -2 73 -2 100 -2c20 0 45 1 65 2v-26c-56 -2 -94 -30 -130 -65z"
            },
            L: {
                x: 591,
                d: "M541 253l-24 -253h-467v26c69 0 80 0 80 45v541c0 45 -11 45 -80 45v26c29 -2 85 -2 116 -2c41 0 89 0 130 2v-26h-22c-76 0 -78 -10 -78 -46v-546c0 -34 2 -39 42 -39h93c171 0 184 128 192 227h18z"
            },
            M: {
                x: 843,
                d: "M206 667l216 -586l216 586c6 15 7 16 28 16h127v-26c-69 0 -80 0 -80 -45v-541c0 -45 11 -45 80 -45v-26c-27 2 -82 2 -111 2s-83 0 -110 -2v26c69 0 80 0 80 45v587h-1l-237 -642c-4 -10 -6 -16 -14 -16s-10 6 -14 16l-235 637h-1v-555c0 -25 0 -72 80 -72v-26\nc-23 2 -65 2 -90 2s-67 0 -90 -2v26c80 0 80 47 80 72v514c0 45 -11 45 -80 45v26h128c21 0 22 -1 28 -16z"
            },
            N: {
                x: 690,
                d: "M204 671l336 -549v463c0 25 0 72 -80 72v26c23 -2 65 -2 90 -2s67 0 90 2v-26c-80 0 -80 -47 -80 -72v-563c0 -19 0 -22 -10 -22c-5 0 -8 0 -15 12l-371 607c-7 10 -7 12 -14 18v-539c0 -25 0 -72 80 -72v-26c-23 2 -65 2 -90 2s-67 0 -90 -2v26c80 0 80 47 80 72v553\nc-3 1 -21 6 -61 6h-19v26h127c19 0 20 -1 27 -12z"
            },
            O: {
                x: 727,
                d: "M677 340c0 -200 -143 -356 -314 -356c-167 0 -313 153 -313 356s144 359 314 359c166 0 313 -154 313 -359zM364 2c110 0 237 110 237 351c0 233 -132 328 -238 328c-101 0 -237 -92 -237 -328c0 -237 124 -351 238 -351z"
            },
            P: {
                x: 629,
                d: "M196 321v-250c0 -45 11 -45 80 -45v-26c-29 2 -82 2 -113 2s-84 0 -113 -2v26c69 0 80 0 80 45v541c0 45 -11 45 -80 45v26h306c131 0 223 -88 223 -183s-94 -179 -223 -179h-160zM194 342h143c122 0 166 67 166 158c0 83 -37 157 -166 157h-101c-40 0 -42 -5 -42 -39\nv-276z"
            },
            Q: {
                x: 732,
                d: "M477 9c19 -75 40 -128 99 -128c32 0 86 20 93 116c0 2 1 9 6 9c7 0 7 -7 7 -17c0 -28 -5 -184 -111 -184c-86 0 -97 73 -114 195c-38 -13 -70 -16 -93 -16c-170 0 -314 156 -314 356c0 203 144 359 314 359c166 0 313 -154 313 -359c0 -150 -81 -280 -200 -331zM310 10\nc-11 13 -20 32 -20 55c0 38 27 80 75 80c65 0 89 -60 104 -111c67 43 134 137 134 306c0 234 -128 341 -240 341c-108 0 -239 -103 -239 -341c0 -186 82 -297 186 -330zM453 25c-8 47 -24 104 -88 104c-38 0 -59 -33 -59 -64c0 -22 13 -63 58 -63c17 0 49 2 89 23z"
            },
            R: {
                x: 730,
                d: "M390 341c119 -36 127 -109 133 -159c2 -22 4 -37 7 -58c6 -61 12 -124 67 -124c31 0 60 22 65 87c0 4 1 10 9 10c9 0 9 -7 9 -13c0 -17 -11 -100 -85 -100c-22 0 -73 4 -111 45c-30 34 -30 68 -30 135c0 68 0 95 -40 134c-14 13 -46 36 -103 36h-117v-263\nc0 -45 11 -45 80 -45v-26c-29 2 -81 2 -112 2s-83 0 -112 -2v26c69 0 80 0 80 45v541c0 45 -11 45 -80 45v26h269c140 0 246 -85 246 -179c0 -80 -80 -144 -175 -163zM308 350c75 0 181 25 181 154c0 124 -97 153 -180 153h-73c-40 0 -42 -5 -42 -39v-268h114z"
            },
            S: {
                x: 518,
                d: "M442 679v-203c0 -17 0 -20 -9 -20c-7 0 -8 1 -11 19c-15 125 -81 200 -187 200c-85 0 -137 -71 -137 -136c0 -32 10 -64 38 -95c25 -27 48 -34 102 -48c56 -14 58 -14 65 -17c48 -12 93 -24 135 -88c11 -18 30 -58 30 -110c0 -106 -77 -197 -186 -197\nc-48 0 -132 14 -182 80c-19 -38 -19 -40 -20 -43c-14 -31 -16 -36 -23 -36c-6 0 -7 1 -7 19v202c0 20 1 21 9 21c7 0 8 -1 9 -17c7 -140 107 -200 214 -200c87 0 138 77 138 149c0 56 -30 116 -87 140c-10 4 -60 17 -90 25c-80 21 -115 29 -152 75c-34 42 -41 86 -41 117\nc0 102 82 183 184 183c63 0 121 -25 159 -80l30 66c4 9 6 13 12 13s7 -1 7 -19z"
            },
            T: {
                x: 711,
                d: "M644 680l17 -221h-18c-13 166 -28 195 -181 195c-18 0 -47 0 -55 -1c-18 -4 -18 -16 -18 -38v-542c0 -36 3 -47 86 -47h28v-26c-48 1 -98 2 -147 2s-99 -1 -147 -2v26h28c83 0 86 11 86 47v542c0 23 0 35 -19 38c-8 1 -37 1 -55 1c-154 0 -168 -29 -181 -195h-18l17 221\nh577z"
            },
            U: {
                x: 690,
                d: "M539 229v356c0 25 0 72 -80 72v26c23 -2 66 -2 91 -2s67 0 90 2v-26c-29 0 -80 -6 -80 -62v-380c0 -98 -75 -231 -205 -231c-116 0 -225 101 -225 245v383c0 45 -11 45 -80 45v26c29 -2 82 -2 113 -2s84 0 113 2v-26c-69 0 -80 0 -80 -45v-388c0 -35 4 -97 36 -145\nc28 -42 74 -69 125 -69c95 0 182 85 182 219z"
            },
            V: {
                x: 772,
                d: "M630 591l-227 -591c-5 -14 -6 -16 -17 -16s-12 2 -17 16l-238 621c-12 32 -23 36 -81 36v26c24 -2 71 -2 97 -2c33 0 80 0 112 2v-26c-21 0 -61 0 -61 -27c0 -5 2 -10 5 -17l206 -539l197 513c3 8 6 15 6 26c0 6 -2 41 -52 44v26c23 -2 64 -2 88 -2c25 0 51 0 74 2v-26\nc-67 -1 -83 -44 -92 -66z"
            },
            W: {
                x: 1036,
                d: "M904 593l-187 -592c-5 -15 -5 -17 -14 -17c-8 0 -11 2 -15 16l-170 542l-170 -542c-4 -14 -7 -16 -15 -16c-9 0 -9 2 -14 17l-195 618c-10 32 -14 38 -74 38v26c24 -2 68 -2 94 -2c31 0 81 0 110 2v-26c-20 0 -64 0 -64 -28c0 -2 0 -4 5 -18l163 -519l147 467\nc1 3 3 9 3 13c0 3 -16 55 -20 64c-10 18 -22 21 -68 21v26c23 -2 68 -2 93 -2c31 0 81 0 110 2v-26c-19 0 -63 0 -63 -28c0 -4 1 -8 4 -18l163 -520l155 494c4 11 5 15 5 22c0 24 -19 49 -64 50v26c24 -2 68 -2 93 -2c18 0 53 1 70 2v-26c-58 -2 -73 -37 -82 -64z"
            },
            X: {
                x: 766,
                d: "M402 379l220 -320c20 -28 30 -33 94 -33v-26c-24 2 -74 2 -100 2c-33 0 -82 0 -114 -2v26c35 2 44 19 44 27c0 3 0 6 -8 17l-174 254l-160 -232c-5 -7 -10 -14 -10 -27c0 -16 9 -36 40 -39v-26c-25 2 -72 2 -99 2c-24 0 -62 0 -85 -2v26c19 0 84 1 127 63l174 253\nl-193 282c-22 31 -40 33 -95 33v26c24 -2 74 -2 100 -2c33 0 82 0 114 2v-26c-33 -1 -44 -18 -44 -27c0 -3 1 -6 8 -17l148 -216l132 191c7 10 12 18 12 30c0 16 -8 36 -40 39v26c25 -2 66 -2 99 -2c24 0 62 0 85 2v-26c-82 -1 -112 -44 -127 -65z"
            },
            Y: {
                x: 788,
                d: "M627 594l-201 -321v-198c0 -49 9 -49 81 -49v-26c-29 2 -82 2 -113 2s-83 0 -112 -2v26c69 0 80 0 80 45v202l-221 352c-18 29 -34 32 -91 32v26c24 -2 74 -2 100 -2c33 0 82 0 114 2v-26c-14 0 -49 0 -49 -20c0 -7 1 -8 8 -20l197 -315l180 289c8 13 13 21 13 33\nc0 19 -13 32 -38 33v26c24 -2 64 -2 89 -2s51 0 74 2v-26c-18 0 -72 -1 -111 -63z"
            },
            Z: {
                x: 575,
                d: "M520 663l-394 -635h174c191 0 198 102 207 233h18l-14 -261h-439c-20 0 -22 0 -22 13c0 7 0 8 7 19l387 625h-165c-162 0 -192 -81 -198 -195h-18l10 221h425c21 0 22 -1 22 -20z"
            },
            a: {
                x: 519,
                d: "M370 259v-144c0 -44 0 -96 41 -96c13 0 40 8 40 69v57h18v-56c0 -79 -53 -93 -74 -93c-44 0 -72 40 -74 84c-22 -57 -73 -88 -128 -88c-53 0 -143 22 -143 99c0 38 18 88 81 123c56 30 127 38 186 40v44c0 84 -55 128 -105 128c-35 0 -89 -18 -110 -81c3 1 8 2 12 2\nc17 0 36 -11 36 -36c0 -28 -23 -36 -36 -36c-6 0 -36 2 -36 39c0 66 57 128 136 128c34 0 77 -10 114 -43c42 -39 42 -74 42 -140zM317 139v100c-32 -2 -87 -6 -135 -33c-58 -32 -71 -85 -71 -115c0 -46 39 -83 89 -83c55 0 117 44 117 131z"
            },
            b: {
                x: 546,
                d: "M169 694v-328c30 39 74 72 136 72c101 0 191 -94 191 -222c0 -136 -101 -224 -201 -224c-58 0 -101 32 -130 79l-29 -71h-18v603c0 48 -9 54 -68 54v26zM171 315v-198c0 -18 0 -20 11 -39c32 -58 77 -70 108 -70c24 0 142 11 142 209c0 189 -103 205 -133 205\nc-19 0 -75 -5 -114 -65c-14 -21 -14 -24 -14 -42z"
            },
            c: {
                x: 451,
                d: "M366 350c-22 55 -80 74 -119 74c-59 0 -133 -55 -133 -207c0 -148 77 -207 141 -207c43 0 100 20 126 101c4 13 5 14 12 14s8 -3 8 -7c0 -11 -31 -126 -155 -126c-102 0 -196 91 -196 224c0 128 90 226 196 226c77 0 145 -54 145 -125c0 -35 -29 -38 -36 -38\nc-13 0 -36 8 -36 36c0 35 28 35 47 35z"
            },
            d: {
                x: 546,
                d: "M309 683l119 11v-614c0 -48 9 -54 68 -54v-26l-121 -8v73c-5 -7 -49 -73 -134 -73c-98 0 -191 92 -191 223c0 132 99 223 201 223c81 0 122 -64 126 -69v234c0 48 -9 54 -68 54v26zM375 120v198c0 18 0 21 -13 42c-23 36 -60 62 -106 62c-26 0 -142 -12 -142 -208\nc0 -191 104 -206 133 -206c19 0 53 5 87 35c23 20 41 47 41 77z"
            },
            e: {
                x: 458,
                d: "M404 235h-290c0 -47 0 -106 31 -157c27 -44 70 -68 113 -68c52 0 107 34 129 102c4 12 5 14 12 14c2 0 8 0 8 -7c0 -21 -44 -127 -156 -127c-106 0 -201 96 -201 226c0 123 84 224 190 224c114 0 168 -89 168 -191c0 -10 0 -12 -4 -16zM115 250h239\nc-1 112 -47 176 -115 176c-40 0 -117 -32 -124 -176z"
            },
            f: {
                x: 391,
                d: "M181 404v-333c0 -45 12 -45 84 -45v-26c-27 2 -78 2 -107 2c-26 0 -72 0 -96 -2v26c64 0 68 5 68 43v335h-80v26h80v119c0 106 78 153 135 153c40 0 76 -23 76 -61c0 -26 -20 -34 -34 -34s-34 8 -34 34c0 25 21 32 29 34c-10 7 -25 11 -38 11c-43 0 -85 -52 -85 -135\nv-121h116v-26h-114z"
            },
            g: {
                x: 531,
                d: "M141 180c-19 -22 -19 -44 -19 -53c0 -35 21 -63 52 -68c5 -1 46 -1 69 -1c77 0 225 0 225 -136c0 -75 -99 -126 -209 -126c-114 0 -209 53 -209 125c0 50 41 90 93 103c-34 21 -44 59 -44 85c0 5 0 46 30 81c-10 10 -43 46 -43 103c0 83 68 145 147 145\nc32 0 68 -10 98 -37c28 26 63 45 103 45c32 0 47 -20 47 -39c0 -13 -8 -23 -23 -23c-13 0 -22 9 -22 22c0 16 9 20 13 21c-5 3 -11 3 -15 3c-23 0 -64 -10 -92 -40c30 -31 38 -71 38 -97c0 -83 -68 -145 -147 -145c-40 0 -73 17 -92 32zM233 165c88 0 88 108 88 128\ns0 128 -88 128s-88 -108 -88 -128s0 -128 88 -128zM259 -187c100 0 169 53 169 108c0 93 -114 93 -196 93c-68 0 -78 0 -103 -17c-22 -16 -39 -45 -39 -76c0 -55 69 -108 169 -108z"
            },
            h: {
                x: 550,
                d: "M432 304v-235c0 -39 4 -43 68 -43v-26c-24 2 -69 2 -95 2s-70 0 -94 -2v26c64 0 68 5 68 43v239c0 48 -8 114 -82 114c-70 0 -126 -69 -126 -161v-192c0 -39 4 -43 68 -43v-26c-24 2 -69 2 -95 2s-70 0 -94 -2v26c64 0 68 5 68 43v534c0 48 -9 54 -68 54v26l119 11v-355\nh1c12 33 52 99 134 99c118 0 128 -83 128 -134z"
            },
            i: {
                x: 280,
                d: "M171 616c0 -23 -19 -41 -41 -41c-23 0 -41 19 -41 41c0 23 19 41 41 41c23 0 41 -19 41 -41zM54 427l115 11v-370c0 -36 2 -42 61 -42v-26c-23 2 -64 2 -88 2c-25 0 -68 0 -92 -2v26c64 0 68 5 68 43v278c0 48 -8 54 -64 54v26z"
            },
            j: {
                x: 315,
                d: "M265 616c0 -23 -19 -41 -41 -41c-23 0 -41 19 -41 41c0 23 19 41 41 41c23 0 41 -19 41 -41zM140 427l125 11v-493c0 -85 -51 -148 -120 -148c-51 0 -95 26 -95 66c0 22 14 37 36 37c23 0 36 -17 36 -36c0 -28 -25 -35 -32 -36c22 -14 49 -15 56 -15c59 0 68 78 68 128\nv405c0 48 -8 55 -74 55v26z"
            },
            k: {
                x: 544,
                d: "M280 264l126 -185c30 -44 41 -53 88 -53v-26c-17 1 -54 2 -72 2c-25 0 -68 0 -92 -2v26c12 0 30 3 30 21c0 13 -10 27 -22 45l-101 150l-69 -63v-110c0 -39 4 -43 68 -43v-26c-24 2 -68 2 -93 2s-69 0 -93 -2v26c64 0 68 5 68 43v534c0 48 -9 54 -68 54v26l119 11v-490\nl151 138c1 0 21 19 21 39c0 14 -10 22 -24 23v26c27 -2 70 -2 98 -2l49 1c1 1 3 1 9 1v-26c-26 -1 -62 -6 -119 -54c-9 -8 -80 -73 -80 -75c0 -3 5 -9 6 -11z"
            },
            l: {
                x: 287,
                d: "M169 694v-625c0 -39 4 -43 68 -43v-26c-24 2 -68 2 -94 2c-25 0 -69 0 -93 -2v26c64 0 68 5 68 43v534c0 48 -9 54 -68 54v26z"
            },
            m: {
                x: 811,
                d: "M693 304v-235c0 -39 4 -43 68 -43v-26c-24 2 -69 2 -95 2s-70 0 -94 -2v26c64 0 68 5 68 43v239c0 48 -8 114 -82 114c-70 0 -126 -69 -126 -161v-192c0 -39 4 -43 68 -43v-26c-24 2 -69 2 -95 2s-70 0 -94 -2v26c64 0 68 5 68 43v239c0 48 -8 114 -82 114\nc-70 0 -126 -69 -126 -161v-192c0 -39 4 -43 68 -43v-26c-24 2 -69 2 -95 2s-70 0 -94 -2v26c64 0 68 5 68 43v278c0 48 -9 54 -68 54v26l118 11v-101h1c15 40 56 101 135 101c56 0 115 -18 127 -100h1c18 54 65 100 133 100c119 0 128 -84 128 -134z"
            },
            n: {
                x: 550,
                d: "M432 304v-235c0 -39 4 -43 68 -43v-26c-24 2 -69 2 -95 2s-70 0 -94 -2v26c64 0 68 5 68 43v239c0 48 -8 114 -82 114c-70 0 -126 -69 -126 -161v-192c0 -39 4 -43 68 -43v-26c-24 2 -69 2 -95 2s-70 0 -94 -2v26c64 0 68 5 68 43v278c0 48 -9 54 -68 54v26l118 11v-101\nh1c15 40 56 101 135 101c118 0 128 -83 128 -134z"
            },
            o: {
                x: 504,
                d: "M454 214c0 -126 -93 -222 -202 -222s-202 96 -202 222c0 128 93 228 202 228s202 -100 202 -228zM252 10c38 0 81 19 109 65c27 48 29 108 29 147c0 31 0 97 -31 144c-24 35 -62 60 -107 60c-51 0 -90 -32 -110 -65c-26 -45 -28 -97 -28 -139c0 -44 3 -100 28 -145\nc23 -39 63 -67 110 -67z"
            },
            p: {
                x: 546,
                d: "M239 -169v-26c-24 2 -69 2 -95 2s-70 0 -94 -2v26c64 0 68 5 68 43v473c0 48 -9 54 -68 54v26l119 11v-73c28 40 77 73 137 73c103 0 190 -96 190 -222c0 -136 -101 -224 -201 -224c-53 0 -95 27 -124 69v-187c0 -39 4 -43 68 -43zM171 314v-198c0 -16 0 -22 13 -44\nc30 -47 69 -64 105 -64c79 0 143 93 143 207s-60 205 -133 205c-50 0 -84 -30 -89 -34c-39 -37 -39 -58 -39 -72z"
            },
            q: {
                x: 546,
                d: "M428 438v-564c0 -39 4 -43 68 -43v-26c-24 2 -69 2 -95 2s-70 0 -94 -2v26c64 0 68 5 68 43v199c-13 -22 -56 -81 -133 -81c-100 0 -192 92 -192 223c0 132 98 223 200 223c67 0 109 -46 132 -99l30 99h16zM377 137v140c0 52 -40 143 -120 143c-77 0 -143 -89 -143 -206\nc0 -111 58 -206 133 -206c24 0 60 8 91 43c3 3 39 43 39 86z"
            },
            r: {
                x: 402,
                d: "M169 236v-165c0 -45 12 -45 84 -45v-26c-27 2 -78 2 -107 2c-26 0 -72 0 -96 -2v26c64 0 68 5 68 43v278c0 48 -9 54 -68 54v26l116 11v-109h1c10 35 43 109 119 109c34 0 66 -20 66 -52c0 -28 -22 -35 -34 -35c-15 0 -34 10 -34 34c0 26 22 33 22 33c-7 3 -14 4 -21 4\nc-75 0 -116 -90 -116 -186z"
            },
            s: {
                x: 408,
                d: "M334 422v-112c0 -17 0 -20 -9 -20c-3 0 -8 1 -9 6c-2 32 -8 132 -115 132c-101 0 -115 -55 -115 -81c0 -61 70 -75 125 -87c42 -8 77 -15 107 -46c13 -12 40 -39 40 -90c0 -78 -53 -132 -152 -132c-56 0 -91 26 -111 53c-6 -10 -21 -34 -27 -43c-5 -8 -6 -9 -11 -9\nc-6 0 -7 1 -7 19v144c0 20 1 21 9 21s8 -1 11 -15c18 -88 49 -154 136 -154c92 0 116 54 116 96c0 36 -22 58 -35 70c-21 18 -43 22 -106 35c-29 6 -131 27 -131 117c0 58 40 116 151 116c17 0 62 -1 94 -35c3 4 12 14 15 18c12 15 13 16 17 16c6 0 7 -1 7 -19z"
            },
            t: {
                x: 396,
                d: "M190 404v-283c0 -91 40 -111 69 -111c42 0 69 46 69 115v56h18v-57c0 -74 -34 -132 -93 -132c-116 0 -116 112 -116 131v281h-87v16c85 2 120 92 122 195h18v-185h140v-26h-140z"
            },
            u: {
                x: 550,
                d: "M311 427l121 11v-358c0 -48 9 -54 68 -54v-26l-119 -8v93h-1c-8 -21 -41 -93 -126 -93c-72 0 -97 26 -110 39c-26 28 -26 71 -26 138v192c-1 40 -28 40 -68 40v26l121 11v-329c0 -52 7 -101 89 -101c74 0 119 73 119 157v182c0 48 -9 54 -68 54v26z"
            },
            v: {
                x: 564,
                d: "M431 340l-133 -331c-6 -15 -7 -17 -16 -17s-10 2 -16 17l-145 364c-12 31 -33 31 -71 31v26c22 -2 53 -2 80 -2c25 0 69 0 93 2v-26c-15 0 -48 0 -48 -24c0 -4 0 -6 6 -19l119 -300l110 278c6 14 6 16 6 25c0 26 -14 38 -39 40v26c20 -2 53 -2 74 -2c19 0 44 1 63 2v-26\nc-58 -2 -74 -42 -83 -64z"
            },
            w: {
                x: 747,
                d: "M622 342l-114 -334c-4 -13 -7 -16 -15 -16c-7 0 -11 2 -16 17l-104 302l-103 -302c-5 -15 -9 -17 -16 -17c-8 0 -11 3 -15 16l-123 360c-11 33 -22 36 -66 36v26c21 -2 55 -2 77 -2c25 0 67 0 91 2v-26c-16 0 -50 0 -50 -26c0 -3 0 -5 5 -18l99 -289l92 268\nc-21 63 -21 65 -76 65v26c23 -2 49 -2 74 -2c24 0 66 0 89 2v-26c-16 0 -50 0 -50 -26c0 -4 3 -12 5 -19l102 -295l94 272c2 7 4 16 4 23c0 25 -17 43 -48 45v26c17 -2 60 -2 79 -2c20 0 40 1 60 2v-26c-15 -1 -55 -3 -75 -62z"
            },
            x: {
                x: 582,
                d: "M309 233l134 -174c24 -31 39 -33 89 -33v-26c-22 2 -54 2 -81 2c-25 0 -68 0 -92 -2v26c16 1 28 9 28 23c0 7 -12 22 -20 33l-87 113l-83 -102c-9 -12 -17 -23 -17 -41c0 -23 16 -25 22 -26v-26c-18 2 -62 2 -82 2c-18 0 -53 -1 -70 -2v26c27 1 74 5 119 59\nc11 14 98 121 98 125c0 3 -5 9 -7 11l-115 149c-24 30 -36 34 -90 34v26c22 -2 54 -2 81 -2c25 0 68 0 92 2v-26c-17 -1 -27 -11 -27 -23c0 -5 0 -7 9 -18l88 -115l74 94c5 7 15 18 15 36c0 14 -8 24 -22 26v26c23 -2 54 -2 81 -2c18 0 53 1 70 2v-26\nc-44 -1 -82 -17 -115 -55c-28 -33 -62 -77 -92 -116z"
            },
            y: {
                x: 564,
                d: "M430 342l-178 -439c-20 -50 -56 -106 -114 -106c-45 0 -86 29 -86 69c0 18 11 33 33 33c20 0 32 -15 32 -32s-10 -31 -33 -33c18 -18 43 -21 54 -21c63 0 89 69 134 187l-149 367c-13 32 -19 37 -73 37v26c22 -2 54 -2 81 -2c25 0 68 0 92 2v-26c-24 0 -47 -2 -47 -24\nc0 -1 0 -8 5 -20l119 -292l109 270c5 12 7 17 7 27c0 8 -1 36 -39 39v26c20 -2 53 -2 74 -2c19 0 44 1 63 2v-26c-14 0 -59 -1 -84 -62z"
            },
            z: {
                x: 452,
                d: "M391 407l-278 -389h128c122 0 135 51 143 162h18l-14 -180h-316c-21 0 -22 1 -22 15l286 399h-122c-115 0 -126 -42 -133 -139h-18l10 155h304c18 0 22 0 22 -9c0 -2 0 -4 -8 -14z"
            }
        }
    };
});
define("formula", [ "kity", "def/gtype", "font/manager", "sysconf", "font/installer", "jquery", "font/checker-tpl", "base/output", "base/canvg", "fpaper" ], function(require, exports, module) {
    var kity = require("kity"), GTYPE = require("def/gtype"), FontManager = require("font/manager"), FontInstaller = require("font/installer"), DEFAULT_OPTIONS = {
        fontsize: 50,
        autoresize: true,
        padding: [ 0 ]
    }, Output = require("base/output"), EXPRESSION_INTERVAL = 10, ExpressionWrap = kity.createClass("ExpressionWrap", {
        constructor: function(exp, config) {
            this.wrap = new kity.Group();
            this.bg = new kity.Rect(0, 0, 0, 0).fill("transparent");
            this.exp = exp;
            this.config = config;
            this.wrap.setAttr("data-type", "kf-exp-wrap");
            this.bg.setAttr("data-type", "kf-exp-wrap-bg");
            this.wrap.addShape(this.bg);
            this.wrap.addShape(this.exp);
        },
        getWrapShape: function() {
            return this.wrap;
        },
        getExpression: function() {
            return this.exp;
        },
        getBackground: function() {
            return this.bg;
        },
        resize: function() {
            var padding = this.config.padding, expBox = this.exp.getFixRenderBox();
            if (padding.length === 1) {
                padding[1] = padding[0];
            }
            this.bg.setSize(padding[1] * 2 + expBox.width, padding[0] * 2 + expBox.height);
            this.exp.translate(padding[1], padding[0]);
        }
    }), Formula = kity.createClass("Formula", {
        base: require("fpaper"),
        constructor: function(container, config) {
            this.callBase(container);
            this.expressions = [];
            this.fontInstaller = new FontInstaller(this);
            this.config = kity.Utils.extend({}, DEFAULT_OPTIONS, config);
            this.initEnvironment();
            this.initInnerFont();
        },
        getContentContainer: function() {
            return this.container;
        },
        initEnvironment: function() {
            this.zoom = this.config.fontsize / 50;
            if ("width" in this.config) {
                this.setWidth(this.config.width);
            }
            if ("height" in this.config) {
                this.setHeight(this.config.height);
            }
            this.node.setAttribute("font-size", DEFAULT_OPTIONS.fontsize);
        },
        initInnerFont: function() {
            var fontList = FontManager.getFontList(), _self = this;
            kity.Utils.each(fontList, function(fontInfo) {
                createFontStyle(fontInfo);
            });
            function createFontStyle(fontInfo) {
                var stylesheet = _self.doc.createElement("style"), tpl = '@font-face{font-family: "${fontFamily}";font-style: normal;src: url("${src}") format("woff");}';
                stylesheet.setAttribute("type", "text/css");
                stylesheet.innerHTML = tpl.replace("${fontFamily}", fontInfo.meta.fontFamily).replace("${src}", fontInfo.meta.src);
                _self.resourceNode.appendChild(stylesheet);
            }
        },
        insertExpression: function(expression, index) {
            var expWrap = this.wrap(expression);
            this.container.clearTransform();
            this.expressions.splice(index, 0, expWrap.getWrapShape());
            this.addShape(expWrap.getWrapShape());
            notifyExpression.call(this, expWrap.getExpression());
            expWrap.resize();
            correctOffset.call(this);
            this.resetZoom();
            this.config.autoresize && this.resize();
        },
        appendExpression: function(expression) {
            this.insertExpression(expression, this.expressions.length);
        },
        resize: function() {
            var renderBox = this.container.getFixRenderBox();
            this.node.setAttribute("width", renderBox.width);
            this.node.setAttribute("height", renderBox.height);
        },
        resetZoom: function() {
            var zoomLevel = this.zoom / this.getBaseZoom();
            if (zoomLevel !== 0) {
                this.container.scale(zoomLevel);
            }
        },
        wrap: function(exp) {
            return new ExpressionWrap(exp, this.config);
        },
        clear: function() {
            this.callBase();
            this.expressions = [];
        },
        clearExpressions: function() {
            kity.Utils.each(this.expressions, function(exp, i) {
                exp.remove();
            });
            this.expressions = [];
        },
        toJPG: function(cb) {
            new Output(this).toJPG(cb);
        },
        toPNG: function(cb) {
            new Output(this).toPNG(cb);
        }
    });
    kity.Utils.extend(Formula, {
        registerFont: function(fontData) {
            FontManager.registerFont(fontData);
        }
    });
    function correctOffset() {
        var exprOffset = 0;
        kity.Utils.each(this.expressions, function(expr) {
            var box = null;
            if (!expr) {
                return;
            }
            expr.setMatrix(new kity.Matrix(1, 0, 0, 1, 0, 0));
            box = expr.getFixRenderBox();
            expr.translate(0 - box.x, exprOffset);
            exprOffset += box.height + EXPRESSION_INTERVAL;
        });
        return this;
    }
    function notifyExpression(expression) {
        var len = 0, childGroup = null;
        if (!expression) {
            return;
        }
        if (expression.getType() === GTYPE.EXP) {
            for (var i = 0, len = expression.getChildren().length; i < len; i++) {
                notifyExpression(expression.getChild(i));
            }
        } else if (expression.getType() === GTYPE.COMPOUND_EXP) {
            for (var i = 0, len = expression.getOperands().length; i < len; i++) {
                notifyExpression(expression.getOperand(i));
            }
            notifyExpression(expression.getOperator());
        }
        expression.addedCall && expression.addedCall();
    }
    return Formula;
});
define("fpaper", [ "kity" ], function(require, exports, module) {
    var kity = require("kity");
    return kity.createClass("FPaper", {
        base: kity.Paper,
        constructor: function(container) {
            this.callBase(container);
            this.doc = container.ownerDocument;
            this.container = new kity.Group();
            this.container.setAttr("data-type", "kf-container");
            this.background = new kity.Group();
            this.background.setAttr("data-type", "kf-bg");
            this.baseZoom = 1;
            this.zoom = 1;
            this.base("addShape", this.background);
            this.base("addShape", this.container);
        },
        getZoom: function() {
            return this.zoom;
        },
        getBaseZoom: function() {
            return this.baseZoom;
        },
        addShape: function(shape, pos) {
            return this.container.addShape(shape, pos);
        },
        getBackground: function() {
            return this.background;
        },
        removeShape: function(pos) {
            return this.container.removeShape(pos);
        },
        clear: function() {
            return this.container.clear();
        }
    });
});
define("jquery", [], function(require, exports, module) {
    if (!window.jQuery) {
        throw new Error("Missing jQuery");
    }
    return window.jQuery;
});
define("kity", [], function(require, exports, module) {
    if (!window.kity) {
        throw new Error("Missing Kity Graphic Lib");
    }
    return window.kity;
});
define("operator/brackets", [ "kity", "char/text", "sysconf", "font/manager", "char/text-factory", "signgroup", "operator/operator", "def/gtype" ], function(require, exports, modules) {
    var kity = require("kity"), Text = require("char/text"), FontManager = require("font/manager");
    return kity.createClass("BracketsOperator", {
        base: require("operator/operator"),
        constructor: function() {
            this.callBase("Brackets");
        },
        applyOperand: function(exp) {
            generate.call(this, exp);
        }
    });
    function generate(exp) {
        var left = this.getParentExpression().getLeftSymbol(), right = this.getParentExpression().getRightSymbol(), fontSize = exp.getFixRenderBox().height, group = new kity.Group(), offset = 0, leftOp = new Text(left, "KF AMS MAIN").fill("black"), rightOp = new Text(right, "KF AMS MAIN").fill("black");
        leftOp.setFontSize(fontSize);
        rightOp.setFontSize(fontSize);
        this.addOperatorShape(group.addShape(leftOp).addShape(rightOp));
        offset += leftOp.getFixRenderBox().width;
        exp.translate(offset, 0);
        offset += exp.getFixRenderBox().width;
        rightOp.translate(offset, 0);
    }
});
define("operator/combination", [ "kity", "operator/operator", "def/gtype", "signgroup" ], function(require, exports, modules) {
    var kity = require("kity");
    return kity.createClass("CombinationOperator", {
        base: require("operator/operator"),
        constructor: function() {
            this.callBase("Combination");
        },
        applyOperand: function() {
            var offsetX = 0, offsetY = 0, operands = arguments, maxHeight = 0, maxOffsetTop = 0, maxOffsetBottom = 0, cached = [], offsets = [];
            kity.Utils.each(operands, function(operand) {
                var box = operand.getFixRenderBox(), offsetY = operand.getOffset();
                box.height -= offsetY.top + offsetY.bottom;
                cached.push(box);
                offsets.push(offsetY);
                maxOffsetTop = Math.max(offsetY.top, maxOffsetTop);
                maxOffsetBottom = Math.max(offsetY.bottom, maxOffsetBottom);
                maxHeight = Math.max(box.height, maxHeight);
            });
            kity.Utils.each(operands, function(operand, index) {
                var box = cached[index];
                operand.translate(offsetX - box.x, (maxHeight - (box.y + box.height)) / 2 + maxOffsetBottom - offsets[index].bottom);
                offsetX += box.width;
            });
            this.parentExpression.setOffset(maxOffsetTop, maxOffsetBottom);
            this.parentExpression.updateBoxSize();
        }
    });
});
define("operator/common/script-controller", [ "kity", "expression/empty", "sysconf", "expression/expression" ], function(require) {
    var kity = require("kity"), EmptyExpression = require("expression/empty"), defaultOptions = {
        subOffset: 0,
        supOffset: 0,
        zoom: .66
    };
    return kity.createClass("ScriptController", {
        constructor: function(opObj, target, sup, sub, options) {
            this.observer = opObj.getParentExpression();
            this.target = target;
            this.sup = sup;
            this.sub = sub;
            this.options = kity.Utils.extend({}, defaultOptions, options);
        },
        applyUpDown: function() {
            var target = this.target, sup = this.sup, sub = this.sub, options = this.options;
            sup.scale(options.zoom);
            sub.scale(options.zoom);
            var targetBox = target.getFixRenderBox();
            if (EmptyExpression.isEmpty(sup) && EmptyExpression.isEmpty(sub)) {
                return {
                    width: targetBox.width,
                    height: targetBox.height,
                    top: 0,
                    bottom: 0
                };
            } else {
                if (!EmptyExpression.isEmpty(sup) && EmptyExpression.isEmpty(sub)) {
                    return this.applyUp(target, sup);
                } else if (EmptyExpression.isEmpty(sup) && !EmptyExpression.isEmpty(sub)) {
                    return this.applyDown(target, sub);
                } else {
                    return this.applyUpDownScript(target, sup, sub);
                }
            }
        },
        applySide: function() {
            var target = this.target, sup = this.sup, sub = this.sub;
            if (EmptyExpression.isEmpty(sup) && EmptyExpression.isEmpty(sub)) {
                var targetRectBox = target.getRenderBox(this.observer);
                return {
                    width: targetRectBox.width,
                    height: targetRectBox.height,
                    top: 0,
                    bottom: 0
                };
            } else {
                if (EmptyExpression.isEmpty(sup) && !EmptyExpression.isEmpty(sub)) {
                    return this.applySideSub(target, sub);
                } else if (!EmptyExpression.isEmpty(sup) && EmptyExpression.isEmpty(sub)) {
                    return this.applySideSuper(target, sup);
                } else {
                    return this.applySideScript(target, sup, sub);
                }
            }
        },
        applySideSuper: function(target, sup) {
            sup.scale(this.options.zoom);
            var targetRectBox = target.getRenderBox(this.observer), supRectBox = sup.getRenderBox(this.observer), targetMeanline = target.getMeanline(this.observer), supBaseline = sup.getBaseline(this.observer), positionline = targetMeanline, diff = supBaseline - positionline, space = {
                top: 0,
                bottom: 0,
                width: targetRectBox.width + supRectBox.width,
                height: targetRectBox.height
            };
            sup.translate(targetRectBox.width, 0);
            if (this.options.supOffset) {
                sup.translate(this.options.supOffset, 0);
            }
            if (diff > 0) {
                target.translate(0, diff);
                space.bottom = diff;
                space.height += diff;
            } else {
                sup.translate(0, -diff);
            }
            return space;
        },
        applySideSub: function(target, sub) {
            sub.scale(this.options.zoom);
            var targetRectBox = target.getRenderBox(this.observer), subRectBox = sub.getRenderBox(this.observer), subOffset = sub.getOffset(), targetBaseline = target.getBaseline(this.observer), subPosition = (subRectBox.height + subOffset.top + subOffset.bottom) / 2, diff = targetRectBox.height - targetBaseline - subPosition, space = {
                top: 0,
                bottom: 0,
                width: targetRectBox.width + subRectBox.width,
                height: targetRectBox.height
            };
            sub.translate(targetRectBox.width, subOffset.top + targetBaseline - subPosition);
            if (this.options.subOffset) {
                sub.translate(this.options.subOffset, 0);
            }
            if (diff < 0) {
                space.top = -diff;
                space.height -= diff;
            }
            return space;
        },
        applySideScript: function(target, sup, sub) {
            sup.scale(this.options.zoom);
            sub.scale(this.options.zoom);
            var targetRectBox = target.getRenderBox(this.observer), subRectBox = sub.getRenderBox(this.observer), supRectBox = sup.getRenderBox(this.observer), targetMeanline = target.getMeanline(this.observer), targetBaseline = target.getBaseline(this.observer), supBaseline = sup.getBaseline(this.observer), subAscenderline = sub.getAscenderline(this.observer), supPosition = targetMeanline, subPosition = targetMeanline + (targetBaseline - targetMeanline) * 2 / 3, topDiff = supPosition - supBaseline, bottomDiff = targetRectBox.height - subPosition - (subRectBox.height - subAscenderline), space = {
                top: 0,
                bottom: 0,
                width: targetRectBox.width + Math.max(subRectBox.width, supRectBox.width),
                height: targetRectBox.height
            };
            sup.translate(targetRectBox.width, topDiff);
            sub.translate(targetRectBox.width, subPosition - subAscenderline);
            if (this.options.supOffset) {
                sup.translate(this.options.supOffset, 0);
            }
            if (this.options.subOffset) {
                sub.translate(this.options.subOffset, 0);
            }
            if (topDiff > 0) {
                if (bottomDiff < 0) {
                    targetRectBox.height -= bottomDiff;
                    space.top = -bottomDiff;
                }
            } else {
                target.translate(0, -topDiff);
                sup.translate(0, -topDiff);
                sub.translate(0, -topDiff);
                space.height -= topDiff;
                if (bottomDiff > 0) {
                    space.bottom = -topDiff;
                } else {
                    space.height -= bottomDiff;
                    topDiff = -topDiff;
                    bottomDiff = -bottomDiff;
                    if (topDiff > bottomDiff) {
                        space.bottom = topDiff - bottomDiff;
                    } else {
                        space.top = bottomDiff - topDiff;
                    }
                }
            }
            return space;
        },
        applyUp: function(target, sup) {
            var supBox = sup.getFixRenderBox(), targetBox = target.getFixRenderBox(), space = {
                width: Math.max(targetBox.width, supBox.width),
                height: supBox.height + targetBox.height,
                top: 0,
                bottom: supBox.height
            };
            sup.translate((space.width - supBox.width) / 2, 0);
            target.translate((space.width - targetBox.width) / 2, supBox.height);
            return space;
        },
        applyDown: function(target, sub) {
            var subBox = sub.getFixRenderBox(), targetBox = target.getFixRenderBox(), space = {
                width: Math.max(targetBox.width, subBox.width),
                height: subBox.height + targetBox.height,
                top: subBox.height,
                bottom: 0
            };
            sub.translate((space.width - subBox.width) / 2, targetBox.height);
            target.translate((space.width - targetBox.width) / 2, 0);
            return space;
        }
    });
});
define("operator/fraction", [ "kity", "sysconf", "font/map/kf-ams-main", "font/map/kf-ams-cal", "font/map/kf-ams-frak", "font/map/kf-ams-bb", "font/map/kf-ams-roman", "operator/operator", "def/gtype", "signgroup" ], function(require, exports, modules) {
    var kity = require("kity"), ZOOM = require("sysconf").zoom;
    return kity.createClass("FractionOperator", {
        base: require("operator/operator"),
        constructor: function() {
            this.callBase("Fraction");
        },
        applyOperand: function(upOperand, downOperand) {
            upOperand.scale(ZOOM);
            downOperand.scale(ZOOM);
            var upWidth = Math.ceil(upOperand.getWidth()), downWidth = Math.ceil(downOperand.getWidth()), upHeight = Math.ceil(upOperand.getHeight()), downHeight = Math.ceil(downOperand.getHeight()), overflow = 3, padding = 1, maxWidth = Math.max(upWidth, downWidth), maxHeight = Math.max(upHeight, downHeight), operatorShape = generateOperator(maxWidth, overflow);
            this.addOperatorShape(operatorShape);
            upOperand.translate((maxWidth - upWidth) / 2 + overflow, 0);
            operatorShape.translate(0, upHeight + 1);
            downOperand.translate((maxWidth - downWidth) / 2 + overflow, upHeight + operatorShape.getHeight() + 1 * 2);
            this.parentExpression.setOffset(maxHeight - upHeight, maxHeight - downHeight);
            this.parentExpression.expand(padding * 2, padding * 2);
            this.parentExpression.translateElement(padding, padding);
        }
    });
    function generateOperator(width, overflow) {
        return new kity.Rect(width + overflow * 2, 1).fill("black");
    }
});
define("operator/func", [ "kity", "char/text", "sysconf", "font/manager", "char/text-factory", "signgroup", "operator/common/script-controller", "expression/empty", "operator/operator", "def/gtype" ], function(require, exports, modules) {
    var kity = require("kity"), Text = require("char/text"), ScriptController = require("operator/common/script-controller");
    return kity.createClass("FunctionOperator", {
        base: require("operator/operator"),
        constructor: function(funcName) {
            this.callBase("Function: " + funcName);
            this.funcName = funcName;
        },
        applyOperand: function(expr, sup, sub) {
            var opShape = generateOperator.call(this), expBox = expr.getFixRenderBox(), scriptHanlder = this.parentExpression.isSideScript() ? "applySide" : "applyUpDown", space = new ScriptController(this, opShape, sup, sub, {
                zoom: .5
            })[scriptHanlder](), padding = 5, diff = (space.height + space.top + space.bottom - expBox.height) / 2;
            opShape.translate(0, space.top);
            sup.translate(0, space.top);
            sub.translate(0, space.top);
            if (diff >= 0) {
                expr.translate(space.width + padding, diff);
            } else {
                diff = -diff;
                opShape.translate(0, diff);
                sup.translate(0, diff);
                sub.translate(0, diff);
                expr.translate(space.width + padding, 0);
            }
            this.parentExpression.expand(padding, padding * 2);
            this.parentExpression.translateElement(padding, padding);
        }
    });
    function generateOperator() {
        var opShape = new Text(this.funcName, "KF AMS ROMAN");
        this.addOperatorShape(opShape);
        return opShape;
    }
});
define("operator/integration", [ "kity", "operator/common/script-controller", "expression/empty", "operator/operator", "def/gtype", "signgroup" ], function(require, exports, modules) {
    var kity = require("kity"), ScriptController = require("operator/common/script-controller");
    return kity.createClass("IntegrationOperator", {
        base: require("operator/operator"),
        constructor: function(type) {
            this.callBase("Integration");
            this.opType = type || 1;
        },
        setType: function(type) {
            this.opType = type | 0;
        },
        resetType: function() {
            this.opType = 1;
        },
        applyOperand: function(exp, sup, sub) {
            var opShape = this.getOperatorShape(), padding = 3, expBox = exp.getFixRenderBox(), space = new ScriptController(this, opShape, sup, sub, {
                supOffset: 3,
                subOffset: -15
            }).applySide(), diff = (space.height + space.top - expBox.height) / 2;
            opShape.translate(0, space.top);
            sup.translate(0, space.top);
            sub.translate(0, space.top);
            if (diff >= 0) {
                exp.translate(space.width + padding, diff);
            } else {
                diff = -diff;
                opShape.translate(0, diff);
                sup.translate(0, diff);
                sub.translate(0, diff);
                exp.translate(space.width + padding, 0);
            }
            this.parentExpression.expand(padding, padding * 2);
            this.parentExpression.translateElement(padding, padding);
        },
        getOperatorShape: function() {
            var pathData = "M1.318,48.226c0,0,0.044,0.066,0.134,0.134c0.292,0.313,0.626,0.447,1.006,0.447c0.246,0.022,0.358-0.044,0.604-0.268   c0.782-0.782,1.497-2.838,2.324-6.727c0.514-2.369,0.938-4.693,1.586-8.448C8.559,24.068,9.9,17.878,11.978,9.52   c0.917-3.553,1.922-7.576,3.866-8.983C16.247,0.246,16.739,0,17.274,0c1.564,0,2.503,1.162,2.592,2.57   c0,0.827-0.424,1.386-1.273,1.386c-0.671,0-1.229-0.514-1.229-1.251c0-0.805,0.514-1.095,1.185-1.274   c0.022,0-0.291-0.29-0.425-0.379c-0.201-0.134-0.514-0.224-0.737-0.224c-0.067,0-0.112,0-0.157,0.022   c-0.469,0.134-0.983,0.939-1.453,2.234c-0.537,1.475-0.961,3.174-1.631,6.548c-0.424,2.101-0.693,3.464-1.229,6.727   c-1.608,9.185-2.949,15.487-5.006,23.756c-0.514,2.034-0.849,3.24-1.207,4.335c-0.559,1.698-1.162,2.95-1.811,3.799   c-0.514,0.715-1.385,1.408-2.436,1.408c-1.363,0-2.391-1.185-2.458-2.592c0-0.804,0.447-1.363,1.273-1.363   c0.671,0,1.229,0.514,1.229,1.251C2.503,47.757,1.989,48.047,1.318,48.226z", group = new kity.Group(), opGroup = new kity.Group(), opShape = new kity.Path(pathData).fill("black"), opBox = new kity.Rect(0, 0, 0, 0).fill("transparent"), tmpShape = null;
            opGroup.addShape(opShape);
            group.addShape(opBox);
            group.addShape(opGroup);
            this.addOperatorShape(group);
            for (var i = 1; i < this.opType; i++) {
                tmpShape = new kity.Use(opShape).translate(opShape.getWidth() / 2 * i, 0);
                opGroup.addShape(tmpShape);
            }
            opGroup.scale(1.6);
            tmpShape = null;
            group.getBaseline = function() {
                return opGroup.getFixRenderBox().height;
            };
            group.getMeanline = function() {
                return 10;
            };
            return group;
        }
    });
});
define("operator/operator", [ "kity", "def/gtype", "signgroup" ], function(require, exports, modules) {
    var kity = require("kity"), GTYPE = require("def/gtype");
    return kity.createClass("Operator", {
        base: require("signgroup"),
        constructor: function(operatorName) {
            this.callBase();
            this.type = GTYPE.OP;
            this.parentExpression = null;
            this.operatorName = operatorName;
            this.operatorShape = new kity.Group();
            this.addShape(this.operatorShape);
        },
        applyOperand: function() {
            throw new Error("applyOperand is abstract");
        },
        setParentExpression: function(exp) {
            this.parentExpression = exp;
        },
        getParentExpression: function() {
            return this.parentExpression;
        },
        clearParentExpression: function() {
            this.parentExpression = null;
        },
        addOperatorShape: function(shpae) {
            return this.operatorShape.addShape(shpae);
        },
        getOperatorShape: function() {
            return this.operatorShape;
        }
    });
});
define("operator/radical", [ "kity", "operator/operator", "def/gtype", "signgroup" ], function(require, exports, modules) {
    var kity = require("kity"), SHAPE_DATA_WIDTH = 1, radians = 2 * Math.PI / 360, sin15 = Math.sin(15 * radians), cos15 = Math.cos(15 * radians), tan15 = Math.tan(15 * radians);
    return kity.createClass("RadicalOperator", {
        base: require("operator/operator"),
        constructor: function() {
            this.callBase("Radical");
        },
        applyOperand: function(radicand, exponent) {
            generateOperator.call(this, radicand, exponent);
        }
    });
    function generateOperator(radicand, exponent) {
        var decoration = generateDecoration(radicand), vLine = generateVLine(radicand), padding = 5, hLine = generateHLine(radicand);
        this.addOperatorShape(decoration);
        this.addOperatorShape(vLine);
        this.addOperatorShape(hLine);
        adjustmentPosition.call(this, mergeShape(decoration, vLine, hLine), this.operatorShape, radicand, exponent);
        this.parentExpression.expand(0, padding * 2);
        this.parentExpression.translateElement(0, padding);
    }
    function generateDecoration(radicand) {
        var shape = new kity.Path(), a = SHAPE_DATA_WIDTH, h = radicand.getHeight() / 3, drawer = shape.getDrawer();
        drawer.moveTo(0, cos15 * a * 6);
        drawer.lineBy(sin15 * a, cos15 * a);
        drawer.lineBy(cos15 * a * 3, -sin15 * a * 3);
        drawer.lineBy(tan15 * h, h);
        drawer.lineBy(sin15 * a * 3, -cos15 * a * 3);
        drawer.lineBy(-sin15 * h, -h);
        drawer.close();
        return shape.fill("black");
    }
    function generateVLine(operand) {
        var shape = new kity.Path(), h = operand.getHeight() * .9, drawer = shape.getDrawer();
        drawer.moveTo(tan15 * h, 0);
        drawer.lineTo(0, h);
        drawer.lineBy(sin15 * SHAPE_DATA_WIDTH * 3, cos15 * SHAPE_DATA_WIDTH * 3);
        drawer.lineBy(tan15 * h + sin15 * SHAPE_DATA_WIDTH * 3, -(h + 3 * SHAPE_DATA_WIDTH * cos15));
        drawer.close();
        return shape.fill("black");
    }
    function generateHLine(operand) {
        var w = operand.getWidth() + 2 * SHAPE_DATA_WIDTH;
        return new kity.Rect(w, 2 * SHAPE_DATA_WIDTH).fill("black");
    }
    function mergeShape(decoration, vLine, hLine) {
        var decoBox = decoration.getFixRenderBox(), vLineBox = vLine.getFixRenderBox();
        vLine.translate(decoBox.width - sin15 * SHAPE_DATA_WIDTH * 3, 0);
        decoration.translate(0, vLineBox.height - decoBox.height);
        vLineBox = vLine.getFixRenderBox();
        hLine.translate(vLineBox.x + vLineBox.width - SHAPE_DATA_WIDTH / cos15, 0);
        return {
            x: vLineBox.x + vLineBox.width - SHAPE_DATA_WIDTH / cos15,
            y: 0
        };
    }
    function adjustmentPosition(position, operator, radicand, exponent) {
        var exponentBox = null, opOffset = {
            x: 0,
            y: 0
        }, opBox = operator.getFixRenderBox();
        exponent.scale(.66);
        exponentBox = exponent.getFixRenderBox();
        if (exponentBox.width > 0 && exponentBox.height > 0) {
            opOffset.y = exponentBox.height - opBox.height / 2;
            if (opOffset.y < 0) {
                exponent.translate(0, -opOffset.y);
                opOffset.y = 0;
            }
            opOffset.x = exponentBox.width + opBox.height / 2 * tan15 - position.x;
        }
        operator.translate(opOffset.x, opOffset.y);
        radicand.translate(opOffset.x + position.x + SHAPE_DATA_WIDTH, opOffset.y + 2 * SHAPE_DATA_WIDTH);
    }
});
define("operator/script", [ "kity", "operator/common/script-controller", "expression/empty", "operator/operator", "def/gtype", "signgroup" ], function(require, exports, module) {
    var kity = require("kity"), ScriptController = require("operator/common/script-controller");
    return kity.createClass("ScriptOperator", {
        base: require("operator/operator"),
        constructor: function(operatorName) {
            this.callBase(operatorName || "Script");
        },
        applyOperand: function(operand, sup, sub) {
            var opShape = this.getOperatorShape(), padding = 1, parent = this.parentExpression, space = new ScriptController(this, operand, sup, sub).applySide();
            space && parent.setOffset(space.top, space.bottom);
            parent.expand(4, padding * 2);
            parent.translateElement(2, padding);
        }
    });
});
define("operator/summation", [ "kity", "operator/common/script-controller", "expression/empty", "operator/operator", "def/gtype", "signgroup" ], function(require, exports, modules) {
    var kity = require("kity"), ScriptController = require("operator/common/script-controller");
    return kity.createClass("SummationOperator", {
        base: require("operator/operator"),
        constructor: function() {
            this.callBase("Summation");
            this.displayType = "equation";
        },
        applyOperand: function(expr, sup, sub) {
            var opShape = this.getOperatorShape(), expBox = expr.getFixRenderBox(), padding = 0, space = new ScriptController(this, opShape, sup, sub).applyUpDown(), diff = (space.height - space.top - space.bottom - expBox.height) / 2;
            if (diff >= 0) {
                expr.translate(space.width + padding, diff + space.bottom);
            } else {
                diff = -diff;
                opShape.translate(0, diff);
                sup.translate(0, diff);
                sub.translate(0, diff);
                expr.translate(space.width + padding, space.bottom);
            }
            this.parentExpression.setOffset(space.top, space.bottom);
            this.parentExpression.expand(padding, padding * 2);
            this.parentExpression.translateElement(padding, padding);
        },
        getOperatorShape: function() {
            var pathData = "M0.672,33.603c-0.432,0-0.648,0-0.648-0.264c0-0.024,0-0.144,0.24-0.432l12.433-14.569L0,0.96c0-0.264,0-0.72,0.024-0.792   C0.096,0.024,0.12,0,0.672,0h28.371l2.904,6.745h-0.6C30.531,4.8,28.898,3.72,28.298,3.336c-1.896-1.2-3.984-1.608-5.28-1.8   c-0.216-0.048-2.4-0.384-5.617-0.384H4.248l11.185,15.289c0.168,0.24,0.168,0.312,0.168,0.36c0,0.12-0.048,0.192-0.216,0.384   L3.168,31.515h14.474c4.608,0,6.96-0.624,7.464-0.744c2.76-0.72,5.305-2.352,6.241-4.848h0.6l-2.904,7.681H0.672z", operatorShape = new kity.Path(pathData).fill("black"), opBgShape = new kity.Rect(0, 0, 0, 0).fill("transparent"), group = new kity.Group(), opRenderBox = null;
            group.addShape(opBgShape);
            group.addShape(operatorShape);
            operatorShape.scale(1.6);
            this.addOperatorShape(group);
            opRenderBox = operatorShape.getFixRenderBox();
            if (this.displayType === "inline") {
                operatorShape.translate(5, 15);
                opBgShape.setSize(opRenderBox.width + 10, opRenderBox.height + 25);
            } else {
                operatorShape.translate(2, 5);
                opBgShape.setSize(opRenderBox.width + 4, opRenderBox.height + 8);
            }
            return group;
        }
    });
});
define("resource-manager", [ "kity", "sysconf", "font/map/kf-ams-main", "font/map/kf-ams-cal", "font/map/kf-ams-frak", "font/map/kf-ams-bb", "font/map/kf-ams-roman", "font/installer", "font/manager", "jquery", "font/checker-tpl", "formula", "def/gtype", "base/output", "fpaper" ], function(require) {
    var kity = require("kity"), cbList = [], RES_CONF = require("sysconf").resource, FontInstall = require("font/installer"), Formula = require("formula"), __readyState = false, inited = false;
    return {
        ready: function(cb, options) {
            if (!inited) {
                inited = true;
                init(options);
            }
            if (__readyState) {
                window.setTimeout(function() {
                    cb(Formula);
                }, 0);
            } else {
                cbList.push(cb);
            }
        }
    };
    function init(options) {
        var options = kity.Utils.extend({}, RES_CONF, options);
        new FontInstall(document, options.path).mount(complete);
    }
    function complete() {
        kity.Utils.each(cbList, function(cb) {
            cb(Formula);
        });
    }
});
define("signgroup", [ "kity", "def/gtype" ], function(require, exports, module) {
    var kity = require("kity"), GTYPE = require("def/gtype");
    return kity.createClass("SignGroup", {
        base: kity.Group,
        constructor: function() {
            this.callBase();
            this.box = new kity.Rect(0, 0, 0, 0);
            this.type = GTYPE.UNKNOWN;
            this.addShape(this.box);
            this.zoom = 1;
        },
        setZoom: function(zoom) {
            this.zoom = zoom;
        },
        getZoom: function() {
            return this.zoom;
        },
        setBoxSize: function(w, h) {
            return this.box.setSize(w, h);
        },
        setBoxWidth: function(w) {
            return this.box.setWidth(w);
        },
        setBoxHeight: function(h) {
            return this.box.setHeight(h);
        },
        getType: function() {
            return this.type;
        },
        getBaseHeight: function() {
            return this.getHeight();
        },
        getBaseWidth: function() {
            return this.getWidth();
        },
        addedCall: function() {}
    });
});
define("sysconf", [ "font/map/kf-ams-main", "font/map/kf-ams-cal", "font/map/kf-ams-frak", "font/map/kf-ams-bb", "font/map/kf-ams-roman" ], function(require) {
    return {
        zoom: .66,
        font: {
            meanline: Math.round(380 / 1e3 * 50),
            baseline: Math.round(800 / 1e3 * 50),
            baseHeight: 50,
            list: [ require("font/map/kf-ams-main"), require("font/map/kf-ams-cal"), require("font/map/kf-ams-frak"), require("font/map/kf-ams-bb"), require("font/map/kf-ams-roman") ]
        },
        resource: {
            path: "src/resource/"
        },
        func: {
            "ud-script": {
                limit: true
            }
        }
    };
});

/**
 * 模块暴露
 */

( function ( global ) {

    var oldGetRenderBox = kity.Shape.getRenderBox;

    kity.extendClass(kity.Shape, {
        getFixRenderBox: function () {
            return this.getRenderBox( this.container.container );
        },

        getTranslate: function () {
            return this.transform.translate;
        }
    });

    define( 'kf.start', function ( require ) {

        global.kf = {

            // base
            ResourceManager: require( "resource-manager" ),
            Operator: require( "operator/operator" ),

            // expression
            Expression: require( "expression/expression" ),
            CompoundExpression: require( "expression/compound" ),
            TextExpression: require( "expression/text" ),
            EmptyExpression: require( "expression/empty" ),
            CombinationExpression: require( "expression/compound-exp/combination" ),
            FunctionExpression: require( "expression/compound-exp/func" ),

            FractionExpression: require( "expression/compound-exp/fraction" ),
            IntegrationExpression: require( "expression/compound-exp/integration" ),
            RadicalExpression: require( "expression/compound-exp/radical" ),
            ScriptExpression: require( "expression/compound-exp/script" ),
            SuperscriptExpression: require( "expression/compound-exp/binary-exp/superscript" ),
            SubscriptExpression: require( "expression/compound-exp/binary-exp/subscript" ),
            SummationExpression: require( "expression/compound-exp/summation" ),

            // Brackets expressoin
            BracketsExpression: require( "expression/compound-exp/brackets" )

        };

    } );

    // build环境中才含有use
    try {
        use( 'kf.start' );
    } catch ( e ) {
    }

} )( this );
})();
