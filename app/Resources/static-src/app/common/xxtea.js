/**********************************************************\
|                                                          |
| xxtea.js                                                 |
|                                                          |
| XXTEA encryption algorithm library for JavaScript.       |
|                                                          |
| Encryption Algorithm Authors:                            |
|      David J. Wheeler                                    |
|      Roger M. Needham                                    |
|                                                          |
| Code Author: Ma Bingyao <mabingyao@gmail.com>            |
| LastModified: Oct 4, 2016                                |
|                                                          |
\**********************************************************/

(function (global) {
  'use strict';

  if (typeof(global.btoa) == 'undefined') {
    global.btoa = function() {
      var base64EncodeChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'.split('');
      return function(str) {
        var buf, i, j, len, r, l, c;
        i = j = 0;
        len = str.length;
        r = len % 3;
        len = len - r;
        l = (len / 3) << 2;
        if (r > 0) {
          l += 4;
        }
        buf = new Array(l);

        while (i < len) {
          c = str.charCodeAt(i++) << 16 |
                        str.charCodeAt(i++) << 8  |
                        str.charCodeAt(i++);
          buf[j++] = base64EncodeChars[c >> 18] +
                               base64EncodeChars[c >> 12 & 0x3f] +
                               base64EncodeChars[c >> 6  & 0x3f] +
                               base64EncodeChars[c & 0x3f] ;
        }
        if (r == 1) {
          c = str.charCodeAt(i++);
          buf[j++] = base64EncodeChars[c >> 2] +
                               base64EncodeChars[(c & 0x03) << 4] +
                               '==';
        }
        else if (r == 2) {
          c = str.charCodeAt(i++) << 8 |
                        str.charCodeAt(i++);
          buf[j++] = base64EncodeChars[c >> 10] +
                               base64EncodeChars[c >> 4 & 0x3f] +
                               base64EncodeChars[(c & 0x0f) << 2] +
                               '=';
        }
        return buf.join('');
      };
    }();
  }

  if (typeof(global.atob) == 'undefined') {
    global.atob = function() {
      var base64DecodeChars = [
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 62, -1, -1, -1, 63,
        52, 53, 54, 55, 56, 57, 58, 59, 60, 61, -1, -1, -1, -1, -1, -1,
        -1,  0,  1,  2,  3,  4,  5,  6,  7,  8,  9, 10, 11, 12, 13, 14,
        15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, -1, -1, -1, -1, -1,
        -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
        41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1, -1, -1
      ];
      return function(str) {
        var c1, c2, c3, c4;
        var i, j, len, r, l, out;

        len = str.length;
        if (len % 4 !== 0) {
          return '';
        }
        if (/[^ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789\+\/\=]/.test(str)) {
          return '';
        }
        if (str.charAt(len - 2) == '=') {
          r = 1;
        }
        else if (str.charAt(len - 1) == '=') {
          r = 2;
        }
        else {
          r = 0;
        }
        l = len;
        if (r > 0) {
          l -= 4;
        }
        l = (l >> 2) * 3 + r;
        out = new Array(l);

        i = j = 0;
        while (i < len) {
          // c1
          c1 = base64DecodeChars[str.charCodeAt(i++)];
          if (c1 == -1) break;

          // c2
          c2 = base64DecodeChars[str.charCodeAt(i++)];
          if (c2 == -1) break;

          out[j++] = String.fromCharCode((c1 << 2) | ((c2 & 0x30) >> 4));

          // c3
          c3 = base64DecodeChars[str.charCodeAt(i++)];
          if (c3 == -1) break;

          out[j++] = String.fromCharCode(((c2 & 0x0f) << 4) | ((c3 & 0x3c) >> 2));

          // c4
          c4 = base64DecodeChars[str.charCodeAt(i++)];
          if (c4 == -1) break;

          out[j++] = String.fromCharCode(((c3 & 0x03) << 6) | c4);
        }
        return out.join('');
      };
    }();
  }

  var DELTA = 0x9E3779B9;

  function toBinaryString(v, includeLength) {
    var length = v.length;
    var n = length << 2;
    if (includeLength) {
      var m = v[length - 1];
      n -= 4;
      if ((m < n - 3) || (m > n)) {
        return null;
      }
      n = m;
    }
    for (var i = 0; i < length; i++) {
      v[i] = String.fromCharCode(
        v[i] & 0xFF,
        v[i] >>> 8 & 0xFF,
        v[i] >>> 16 & 0xFF,
        v[i] >>> 24 & 0xFF
      );
    }
    var result = v.join('');
    if (includeLength) {
      return result.substring(0, n);
    }
    return result;
  }

  function toUint32Array(bs, includeLength) {
    var length = bs.length;
    var n = length >> 2;
    if ((length & 3) !== 0) {
      ++n;
    }
    var v;
    if (includeLength) {
      v = new Array(n + 1);
      v[n] = length;
    }
    else {
      v = new Array(n);
    }
    for (var i = 0; i < length; ++i) {
      v[i >> 2] |= bs.charCodeAt(i) << ((i & 3) << 3);
    }
    return v;
  }

  function int32(i) {
    return i & 0xFFFFFFFF;
  }

  function mx(sum, y, z, p, e, k) {
    return ((z >>> 5 ^ y << 2) + (y >>> 3 ^ z << 4)) ^ ((sum ^ y) + (k[p & 3 ^ e] ^ z));
  }

  function fixk(k) {
    if (k.length < 4) k.length = 4;
    return k;
  }

  function encryptUint32Array(v, k) {
    var length = v.length;
    var n = length - 1;
    var y, z, sum, e, p, q;
    z = v[n];
    sum = 0;
    for (q = Math.floor(6 + 52/length) | 0; q > 0; --q) {
      sum = int32(sum + DELTA);
      e = sum >>> 2 & 3;
      for (p = 0; p < n; ++p) {
        y = v[p + 1];
        z = v[p] = int32(v[p] + mx(sum, y, z, p, e, k));
      }
      y = v[0];
      z = v[n] = int32(v[n] + mx(sum, y, z, n, e, k));
    }
    return v;
  }

  function decryptUint32Array(v, k) {
    var length = v.length;
    var n = length - 1;
    var y, z, sum, e, p, q;
    y = v[0];
    q = Math.floor(6 + 52/length);
    for (sum = int32(q * DELTA); sum !== 0; sum = int32(sum - DELTA)) {
      e = sum >>> 2 & 3;
      for (p = n; p > 0; --p) {
        z = v[p - 1];
        y = v[p] = int32(v[p] - mx(sum, y, z, p, e, k));
      }
      z = v[n];
      y = v[0] = int32(v[0] - mx(sum, y, z, 0, e, k));
    }
    return v;
  }

  function utf8Encode(str) {
    if (/^[\x00-\x7f]*$/.test(str)) {
      return str;
    }
    var buf = [];
    var n = str.length;
    for (var i = 0, j = 0; i < n; ++i, ++j) {
      var codeUnit = str.charCodeAt(i);
      if (codeUnit < 0x80) {
        buf[j] = str.charAt(i);
      }
      else if (codeUnit < 0x800) {
        buf[j] = String.fromCharCode(0xC0 | (codeUnit >> 6),
          0x80 | (codeUnit & 0x3F));
      }
      else if (codeUnit < 0xD800 || codeUnit > 0xDFFF) {
        buf[j] = String.fromCharCode(0xE0 | (codeUnit >> 12),
          0x80 | ((codeUnit >> 6) & 0x3F),
          0x80 | (codeUnit & 0x3F));
      }
      else {
        if (i + 1 < n) {
          var nextCodeUnit = str.charCodeAt(i + 1);
          if (codeUnit < 0xDC00 && 0xDC00 <= nextCodeUnit && nextCodeUnit <= 0xDFFF) {
            var rune = (((codeUnit & 0x03FF) << 10) | (nextCodeUnit & 0x03FF)) + 0x010000;
            buf[j] = String.fromCharCode(0xF0 | ((rune >> 18) &0x3F),
              0x80 | ((rune >> 12) & 0x3F),
              0x80 | ((rune >> 6) & 0x3F),
              0x80 | (rune & 0x3F));
            ++i;
            continue;
          }
        }
        throw new Error('Malformed string');
      }
    }
    return buf.join('');
  }

  function utf8DecodeShortString(bs, n) {
    var charCodes = new Array(n);
    var i = 0, off = 0;
    for (var len = bs.length; i < n && off < len; i++) {
      var unit = bs.charCodeAt(off++);
      switch (unit >> 4) {
      case 0:
      case 1:
      case 2:
      case 3:
      case 4:
      case 5:
      case 6:
      case 7:
        charCodes[i] = unit;
        break;
      case 12:
      case 13:
        if (off < len) {
          charCodes[i] = ((unit & 0x1F) << 6) |
                                    (bs.charCodeAt(off++) & 0x3F);
        }
        else {
          throw new Error('Unfinished UTF-8 octet sequence');
        }
        break;
      case 14:
        if (off + 1 < len) {
          charCodes[i] = ((unit & 0x0F) << 12) |
                                   ((bs.charCodeAt(off++) & 0x3F) << 6) |
                                   (bs.charCodeAt(off++) & 0x3F);
        }
        else {
          throw new Error('Unfinished UTF-8 octet sequence');
        }
        break;
      case 15:
        if (off + 2 < len) {
          var rune = (((unit & 0x07) << 18) |
                                ((bs.charCodeAt(off++) & 0x3F) << 12) |
                                ((bs.charCodeAt(off++) & 0x3F) << 6) |
                                (bs.charCodeAt(off++) & 0x3F)) - 0x10000;
          if (0 <= rune && rune <= 0xFFFFF) {
            charCodes[i++] = (((rune >> 10) & 0x03FF) | 0xD800);
            charCodes[i] = ((rune & 0x03FF) | 0xDC00);
          }
          else {
            throw new Error('Character outside valid Unicode range: 0x' + rune.toString(16));
          }
        }
        else {
          throw new Error('Unfinished UTF-8 octet sequence');
        }
        break;
      default:
        throw new Error('Bad UTF-8 encoding 0x' + unit.toString(16));
      }
    }
    if (i < n) {
      charCodes.length = i;
    }
    return String.fromCharCode.apply(String, charCodes);
  }

  function utf8DecodeLongString(bs, n) {
    var buf = [];
    var charCodes = new Array(0x8000);
    var i = 0, off = 0;
    for (var len = bs.length; i < n && off < len; i++) {
      var unit = bs.charCodeAt(off++);
      switch (unit >> 4) {
      case 0:
      case 1:
      case 2:
      case 3:
      case 4:
      case 5:
      case 6:
      case 7:
        charCodes[i] = unit;
        break;
      case 12:
      case 13:
        if (off < len) {
          charCodes[i] = ((unit & 0x1F) << 6) |
                                    (bs.charCodeAt(off++) & 0x3F);
        }
        else {
          throw new Error('Unfinished UTF-8 octet sequence');
        }
        break;
      case 14:
        if (off + 1 < len) {
          charCodes[i] = ((unit & 0x0F) << 12) |
                                   ((bs.charCodeAt(off++) & 0x3F) << 6) |
                                   (bs.charCodeAt(off++) & 0x3F);
        }
        else {
          throw new Error('Unfinished UTF-8 octet sequence');
        }
        break;
      case 15:
        if (off + 2 < len) {
          var rune = (((unit & 0x07) << 18) |
                                ((bs.charCodeAt(off++) & 0x3F) << 12) |
                                ((bs.charCodeAt(off++) & 0x3F) << 6) |
                                (bs.charCodeAt(off++) & 0x3F)) - 0x10000;
          if (0 <= rune && rune <= 0xFFFFF) {
            charCodes[i++] = (((rune >> 10) & 0x03FF) | 0xD800);
            charCodes[i] = ((rune & 0x03FF) | 0xDC00);
          }
          else {
            throw new Error('Character outside valid Unicode range: 0x' + rune.toString(16));
          }
        }
        else {
          throw new Error('Unfinished UTF-8 octet sequence');
        }
        break;
      default:
        throw new Error('Bad UTF-8 encoding 0x' + unit.toString(16));
      }
      if (i >= 0x7FFF - 1) {
        var size = i + 1;
        charCodes.length = size;
        buf[buf.length] = String.fromCharCode.apply(String, charCodes);
        n -= size;
        i = -1;
      }
    }
    if (i > 0) {
      charCodes.length = i;
      buf[buf.length] = String.fromCharCode.apply(String, charCodes);
    }
    return buf.join('');
  }

  // n is UTF16 length
  function utf8Decode(bs, n) {
    if (n === undefined || n === null || (n < 0)) n = bs.length;
    if (n === 0) return '';
    if (/^[\x00-\x7f]*$/.test(bs) || !(/^[\x00-\xff]*$/.test(bs))) {
      if (n === bs.length) return bs;
      return bs.substr(0, n);
    }
    return ((n < 0xFFFF) ?
      utf8DecodeShortString(bs, n) :
      utf8DecodeLongString(bs, n));
  }

  function encrypt(data, key) {
    if (data === undefined || data === null || data.length === 0) {
      return data;
    }
    data = utf8Encode(data);
    key = utf8Encode(key);
    return toBinaryString(encryptUint32Array(toUint32Array(data, true), fixk(toUint32Array(key, false))), false);
  }

  function encryptToBase64(data, key) {
    return global.btoa(encrypt(data, key));
  }

  function decrypt(data, key) {
    if (data === undefined || data === null || data.length === 0) {
      return data;
    }
    key = utf8Encode(key);
    return utf8Decode(toBinaryString(decryptUint32Array(toUint32Array(data, false), fixk(toUint32Array(key, false))), true));
  }

  function decryptFromBase64(data, key) {
    if (data === undefined || data === null || data.length === 0) {
      return data;
    }
    return decrypt(global.atob(data), key);
  }

  global.XXTEA = {
    utf8Encode: utf8Encode,
    utf8Decode: utf8Decode,
    encrypt: encrypt,
    encryptToBase64: encryptToBase64,
    decrypt: decrypt,
    decryptFromBase64: decryptFromBase64
  };
})(window);