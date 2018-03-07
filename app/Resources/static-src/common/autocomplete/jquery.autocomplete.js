/**
 * @preserve jQuery Autocomplete plugin v1.2.6
 * @homepage http://xdsoft.net/jqplugins/autocomplete/
 * @license MIT - MIT-LICENSE.txt
 * (c) 2014, Chupurnov Valeriy <chupurnov@gmail.com>
 */
(function ($) {
  'use strict';
  var	ARROWLEFT = 37,
    ARROWRIGHT = 39,
    ARROWUP = 38,
    ARROWDOWN = 40,
    TAB = 9,
    CTRLKEY = 17,
    SHIFTKEY = 16,
    DEL = 46,
    ENTER = 13,
    ESC = 27,
    BACKSPACE = 8,
    AKEY = 65,
    CKEY = 67,
    VKEY = 86,
    ZKEY = 90,
    YKEY = 89,
    defaultSetting = {},
    //currentInput = false,
    ctrlDown = false,
    shiftDown = false,
    interval_for_visibility,
    publics = {},
    accent_map = {
      'ẚ':'a','Á':'a','á':'a','À':'a','à':'a','Ă':'a','ă':'a','Ắ':'a','ắ':'a','Ằ':'a','ằ':'a','Ẵ':'a','ẵ':'a','Ẳ':'a',
      'Ẫ':'a','ẫ':'a','Ẩ':'a','ẩ':'a','Ǎ':'a','ǎ':'a','Å':'a','å':'a','Ǻ':'a','ǻ':'a','Ä':'a','ä':'a','Ǟ':'a','ǟ':'a',
      'Ã':'a','ã':'a','Ȧ':'a','ȧ':'a','Ǡ':'a','ǡ':'a','Ą':'a','ą':'a','Ā':'a','ā':'a','Ả':'a','ả':'a','Ȁ':'a','ȁ':'a',
      'Ȃ':'a','ȃ':'a','Ạ':'a','ạ':'a','Ặ':'a','ặ':'a','Ậ':'a','ậ':'a','Ḁ':'a','ḁ':'a','Ⱥ':'a','ⱥ':'a','Ǽ':'a','ǽ':'a',
      'Ǣ':'a','ǣ':'a','Ḃ':'b','ḃ':'b','Ḅ':'b','ḅ':'b','Ḇ':'b','ḇ':'b','Ƀ':'b','ƀ':'b','ᵬ':'b','Ɓ':'b','ɓ':'b','Ƃ':'b',
      'ƃ':'b','Ć':'c','ć':'c','Ĉ':'c','ĉ':'c','Č':'c','č':'c','Ċ':'c','ċ':'c','Ç':'c','ç':'c','Ḉ':'c','ḉ':'c','Ȼ':'c',
      'ȼ':'c','Ƈ':'c','ƈ':'c','ɕ':'c','Ď':'d','ď':'d','Ḋ':'d','ḋ':'d','Ḑ':'d','ḑ':'d','Ḍ':'d','ḍ':'d','Ḓ':'d','ḓ':'d',
      'Ḏ':'d','ḏ':'d','Đ':'d','đ':'d','ᵭ':'d','Ɖ':'d','ɖ':'d','Ɗ':'d','ɗ':'d','Ƌ':'d','ƌ':'d','ȡ':'d','ð':'d','É':'e',
      'Ə':'e','Ǝ':'e','ǝ':'e','é':'e','È':'e','è':'e','Ĕ':'e','ĕ':'e','Ê':'e','ê':'e','Ế':'e','ế':'e','Ề':'e','ề':'e',
      'Ễ':'e','ễ':'e','Ể':'e','ể':'e','Ě':'e','ě':'e','Ë':'e','ë':'e','Ẽ':'e','ẽ':'e','Ė':'e','ė':'e','Ȩ':'e','ȩ':'e',
      'Ḝ':'e','ḝ':'e','Ę':'e','ę':'e','Ē':'e','ē':'e','Ḗ':'e','ḗ':'e','Ḕ':'e','ḕ':'e','Ẻ':'e','ẻ':'e','Ȅ':'e','ȅ':'e',
      'Ȇ':'e','ȇ':'e','Ẹ':'e','ẹ':'e','Ệ':'e','ệ':'e','Ḙ':'e','ḙ':'e','Ḛ':'e','ḛ':'e','Ɇ':'e','ɇ':'e','ɚ':'e','ɝ':'e',
      'Ḟ':'f','ḟ':'f','ᵮ':'f','Ƒ':'f','ƒ':'f','Ǵ':'g','ǵ':'g','Ğ':'g','ğ':'g','Ĝ':'g','ĝ':'g','Ǧ':'g','ǧ':'g','Ġ':'g',
      'ġ':'g','Ģ':'g','ģ':'g','Ḡ':'g','ḡ':'g','Ǥ':'g','ǥ':'g','Ɠ':'g','ɠ':'g','Ĥ':'h','ĥ':'h','Ȟ':'h','ȟ':'h','Ḧ':'h',
      'ḧ':'h','Ḣ':'h','ḣ':'h','Ḩ':'h','ḩ':'h','Ḥ':'h','ḥ':'h','Ḫ':'h','ḫ':'h','H':'h','̱':'h','ẖ':'h','Ħ':'h','ħ':'h',
      'Ⱨ':'h','ⱨ':'h','Í':'i','í':'i','Ì':'i','ì':'i','Ĭ':'i','ĭ':'i','Î':'i','î':'i','Ǐ':'i','ǐ':'i','Ï':'i','ï':'i',
      'Ḯ':'i','ḯ':'i','Ĩ':'i','ĩ':'i','İ':'i','i':'i','Į':'i','į':'i','Ī':'i','ī':'i','Ỉ':'i','ỉ':'i','Ȉ':'i','ȉ':'i',
      'Ȋ':'i','ȋ':'i','Ị':'i','ị':'i','Ḭ':'i','ḭ':'i','I':'i','ı':'i','Ɨ':'i','ɨ':'i','Ĵ':'j','ĵ':'j','J':'j','̌':'j',
      'ǰ':'j','ȷ':'j','Ɉ':'j','ɉ':'j','ʝ':'j','ɟ':'j','ʄ':'j','Ḱ':'k','ḱ':'k','Ǩ':'k','ǩ':'k','Ķ':'k','ķ':'k','Ḳ':'k',
      'ḳ':'k','Ḵ':'k','ḵ':'k','Ƙ':'k','ƙ':'k','Ⱪ':'k','ⱪ':'k','Ĺ':'a','ĺ':'l','Ľ':'l','ľ':'l','Ļ':'l','ļ':'l','Ḷ':'l',
      'ḷ':'l','Ḹ':'l','ḹ':'l','Ḽ':'l','ḽ':'l','Ḻ':'l','ḻ':'l','Ł':'l','ł':'l','̣':'l','Ŀ':'l',
      'ŀ':'l','Ƚ':'l','ƚ':'l','Ⱡ':'l','ⱡ':'l','Ɫ':'l','ɫ':'l','ɬ':'l','ɭ':'l','ȴ':'l','Ḿ':'m','ḿ':'m','Ṁ':'m','ṁ':'m',
      'Ṃ':'m','ṃ':'m','ɱ':'m','Ń':'n','ń':'n','Ǹ':'n','ǹ':'n','Ň':'n','ň':'n','Ñ':'n','ñ':'n','Ṅ':'n','ṅ':'n','Ņ':'n',
      'ņ':'n','Ṇ':'n','ṇ':'n','Ṋ':'n','ṋ':'n','Ṉ':'n','ṉ':'n','Ɲ':'n','ɲ':'n','Ƞ':'n','ƞ':'n','ɳ':'n','ȵ':'n','N':'n',
      '̈':'n','n':'n','Ó':'o','ó':'o','Ò':'o','ò':'o','Ŏ':'o','ŏ':'o','Ô':'o','ô':'o','Ố':'o','ố':'o','Ồ':'o',
      'ồ':'o','Ỗ':'o','ỗ':'o','Ổ':'o','ổ':'o','Ǒ':'o','ǒ':'o','Ö':'o','ö':'o','Ȫ':'o','ȫ':'o','Ő':'o','ő':'o','Õ':'o',
      'õ':'o','Ṍ':'o','ṍ':'o','Ṏ':'o','ṏ':'o','Ȭ':'o','ȭ':'o','Ȯ':'o','ȯ':'o','Ȱ':'o','ȱ':'o','Ø':'o','ø':'o','Ǿ':'o',
      'ǿ':'o','Ǫ':'o','ǫ':'o','Ǭ':'o','ǭ':'o','Ō':'o','ō':'o','Ṓ':'o','ṓ':'o','Ṑ':'o','ṑ':'o','Ỏ':'o','ỏ':'o','Ȍ':'o',
      'ȍ':'o','Ȏ':'o','ȏ':'o','Ơ':'o','ơ':'o','Ớ':'o','ớ':'o','Ờ':'o','ờ':'o','Ỡ':'o','ỡ':'o','Ở':'o','ở':'o','Ợ':'o',
      'ợ':'o','Ọ':'o','ọ':'o','Ộ':'o','ộ':'o','Ɵ':'o','ɵ':'o','Ṕ':'p','ṕ':'p','Ṗ':'p','ṗ':'p','Ᵽ':'p','Ƥ':'p','ƥ':'p',
      'P':'p','̃':'p','p':'p','ʠ':'q','Ɋ':'q','ɋ':'q','Ŕ':'r','ŕ':'r','Ř':'r','ř':'r','Ṙ':'r','ṙ':'r','Ŗ':'r',
      'ŗ':'r','Ȑ':'r','ȑ':'r','Ȓ':'r','ȓ':'r','Ṛ':'r','ṛ':'r','Ṝ':'r','ṝ':'r','Ṟ':'r','ṟ':'r','Ɍ':'r','ɍ':'r','ᵲ':'r',
      'ɼ':'r','Ɽ':'r','ɽ':'r','ɾ':'r','ᵳ':'r','ß':'s','Ś':'s','ś':'s','Ṥ':'s','ṥ':'s','Ŝ':'s','ŝ':'s','Š':'s','š':'s',
      'Ṧ':'s','ṧ':'s','Ṡ':'s','ṡ':'s','ẛ':'s','Ş':'s','ş':'s','Ṣ':'s','ṣ':'s','Ṩ':'s','ṩ':'s','Ș':'s','ș':'s','ʂ':'s',
      'S':'s','̩':'s','s':'s','Þ':'t','þ':'t','Ť':'t','ť':'t','T':'t','ẗ':'t','Ṫ':'t','ṫ':'t','Ţ':'t','ţ':'t','Ṭ':'t',
      'ṭ':'t','Ț':'t','ț':'t','Ṱ':'t','ṱ':'t','Ṯ':'t','ṯ':'t','Ŧ':'t','ŧ':'t','Ⱦ':'t','ⱦ':'t','ᵵ':'t',
      'ƫ':'t','Ƭ':'t','ƭ':'t','Ʈ':'t','ʈ':'t','ȶ':'t','Ú':'u','ú':'u','Ù':'u','ù':'u','Ŭ':'u','ŭ':'u','Û':'u','û':'u',
      'Ǔ':'u','ǔ':'u','Ů':'u','ů':'u','Ü':'u','ü':'u','Ǘ':'u','ǘ':'u','Ǜ':'u','ǜ':'u','Ǚ':'u','ǚ':'u','Ǖ':'u','ǖ':'u',
      'Ű':'u','ű':'u','Ũ':'u','ũ':'u','Ṹ':'u','ṹ':'u','Ų':'u','ų':'u','Ū':'u','ū':'u','Ṻ':'u','ṻ':'u','Ủ':'u','ủ':'u',
      'Ȕ':'u','ȕ':'u','Ȗ':'u','ȗ':'u','Ư':'u','ư':'u','Ứ':'u','ứ':'u','Ừ':'u','ừ':'u','Ữ':'u','ữ':'u','Ử':'u','ử':'u',
      'Ự':'u','ự':'u','Ụ':'u','ụ':'u','Ṳ':'u','ṳ':'u','Ṷ':'u','ṷ':'u','Ṵ':'u','ṵ':'u','Ʉ':'u','ʉ':'u','Ṽ':'v','ṽ':'v',
      'Ṿ':'v','ṿ':'v','Ʋ':'v','ʋ':'v','Ẃ':'w','ẃ':'w','Ẁ':'w','ẁ':'w','Ŵ':'w','ŵ':'w','W':'w','̊':'w','ẘ':'w','Ẅ':'w',
      'ẅ':'w','Ẇ':'w','ẇ':'w','Ẉ':'w','ẉ':'w','Ẍ':'x','ẍ':'x','Ẋ':'x','ẋ':'x','Ý':'y','ý':'y','Ỳ':'y','ỳ':'y','Ŷ':'y',
      'ŷ':'y','Y':'y','ẙ':'y','Ÿ':'y','ÿ':'y','Ỹ':'y','ỹ':'y','Ẏ':'y','ẏ':'y','Ȳ':'y','ȳ':'y','Ỷ':'y','ỷ':'y',
      'Ỵ':'y','ỵ':'y','ʏ':'y','Ɏ':'y','ɏ':'y','Ƴ':'y','ƴ':'y','Ź':'z','ź':'z','Ẑ':'z','ẑ':'z','Ž':'z','ž':'z','Ż':'z',
      'ż':'z','Ẓ':'z','ẓ':'z','Ẕ':'z','ẕ':'z','Ƶ':'z','ƶ':'z','Ȥ':'z','ȥ':'z','ʐ':'z','ʑ':'z','Ⱬ':'z','ⱬ':'z','Ǯ':'z',
      'ǯ':'z','ƺ':'z','２':'2','６':'6','Ｂ':'B','Ｆ':'F','Ｊ':'J','Ｎ':'N','Ｒ':'R','Ｖ':'V','Ｚ':'Z','ｂ':'b','ｆ':'f','ｊ':'j',
      'ｎ':'n','ｒ':'r','ｖ':'v','ｚ':'z','１':'1','５':'5','９':'9','Ａ':'A','Ｅ':'E','Ｉ':'I','Ｍ':'M','Ｑ':'Q','Ｕ':'U','Ｙ':'Y',
      'ａ':'a','ｅ':'e','ｉ':'i','ｍ':'m','ｑ':'q','ｕ':'u','ｙ':'y','０':'0','４':'4','８':'8','Ｄ':'D','Ｈ':'H','Ｌ':'L','Ｐ':'P',
      'Ｔ':'T','Ｘ':'X','ｄ':'d','ｈ':'h','ｌ':'l','ｐ':'p','ｔ':'t','ｘ':'x','３':'3','７':'7','Ｃ':'C','Ｇ':'G','Ｋ':'K','Ｏ':'O',
      'Ｓ':'S','Ｗ':'W','ｃ':'c','ｇ':'g','ｋ':'k','ｏ':'o','ｓ':'s','ｗ':'w','ẳ':'a','Â':'a','â':'a','Ấ':'a','ấ':'a','Ầ':'a','ầ':'a'
    };

  if (window.getComputedStyle === undefined) {
    window.getComputedStyle = (function () {
      function getPixelSize(element, style, property, fontSize) {
        var	sizeWithSuffix = style[property],
          size = parseFloat(sizeWithSuffix),
          suffix = sizeWithSuffix.split(/\d/)[0],
          rootSize;

        fontSize = fontSize !== null ? fontSize : /%|em/.test(suffix) && element.parentElement ? getPixelSize(element.parentElement, element.parentElement.currentStyle, 'fontSize', null) : 16;
        rootSize = property === 'fontSize' ? fontSize : /width/i.test(property) ? element.clientWidth : element.clientHeight;

        return (suffix === 'em') ? size * fontSize : (suffix === 'in') ? size * 96 : (suffix === 'pt') ? size * 96 / 72 : (suffix === '%') ? size / 100 * rootSize : size;
      }

      function setShortStyleProperty(style, property) {
        var	borderSuffix = property === 'border' ? 'Width' : '',
          t = property + 'Top' + borderSuffix,
          r = property + 'Right' + borderSuffix,
          b = property + 'Bottom' + borderSuffix,
          l = property + 'Left' + borderSuffix;

        style[property] = (style[t] === style[r] === style[b] === style[l] ? [style[t]]
          : style[t] === style[b] && style[l] === style[r] ? [style[t], style[r]]
            : style[l] === style[r] ? [style[t], style[r], style[b]]
              : [style[t], style[r], style[b], style[l]]).join(' ');
      }

      function CSSStyleDeclaration(element) {
        var	currentStyle = element.currentStyle,
          style = this,
          property,
          fontSize = getPixelSize(element, currentStyle, 'fontSize', null);
				
        for (property in currentStyle) {
          if (Object.prototype.hasOwnProperty.call(currentStyle, property)) {
            if (/width|height|margin.|padding.|border.+W/.test(property) && style[property] !== 'auto') {
              style[property] = getPixelSize(element, currentStyle, property, fontSize) + 'px';
            } else if (property === 'styleFloat') {
              style.float = currentStyle[property];
            } else {
              style[property] = currentStyle[property];
            }
          }
        }

        setShortStyleProperty(style, 'margin');
        setShortStyleProperty(style, 'padding');
        setShortStyleProperty(style, 'border');

        style.fontSize = fontSize + 'px';

        return style;
      }

      CSSStyleDeclaration.prototype = {
        constructor: CSSStyleDeclaration,
        getPropertyPriority: function () {},
        getPropertyValue: function (prop) {
          return this[prop] || '';
        },
        item: function () {},
        removeProperty: function () {},
        setProperty: function () {},
        getPropertyCSSValue: function () {}
      };

      function getComputedStyle(element) {
        return new CSSStyleDeclaration(element);
      }

      return getComputedStyle;
    }(this));
  }


  $(document)
    .on('keydown.xdsoftctrl', function (e) {
      if (e.keyCode === CTRLKEY) {
        ctrlDown = true;
      }
      if (e.keyCode === SHIFTKEY) {
        ctrlDown = true;
      }
    })
    .on('keyup.xdsoftctrl', function (e) {
      if (e.keyCode === CTRLKEY) {
        ctrlDown = false;
      }
      if (e.keyCode === SHIFTKEY) {
        ctrlDown = false;
      }
    });
	
  function accentReplace (s) {
    if (!s) { return ''; }
    var ret = '',i;
    for (i=0; i < s.length; i+=1) {
      ret += accent_map[s.charAt(i)] || s.charAt(i);
    }
    return ret;
  }
	
  function escapeRegExp (str) {
    return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&');
  }
	
  function getCaretPosition(input) {
    if (!input) {
      return;
    }
    if (input.selectionStart) {
      return input.selectionStart;
    }
    if (document.selection) {
      input.focus();
      var sel = document.selection.createRange(),
        selLen = document.selection.createRange().text.length;
      sel.moveStart('character', -input.value.length);
      return sel.text.length - selLen;
    }
  }

  function setCaretPosition(input, pos) {
    if (input.setSelectionRange) {
      input.focus();
      input.setSelectionRange(pos, pos);
    } else if (input.createTextRange) {
      var range = input.createTextRange();
      range.collapse(true);
      range.moveEnd('character', pos);
      range.moveStart('character', pos);
      range.select();
    }
  }

  function isset(value) {
    return value !== undefined;
  }

  function safe_call(callback, args, callback2, defaultValue) {
    if (isset(callback) && !$.isArray(callback)) {
      return $.isFunction(callback) ? callback.apply(this,args):defaultValue;
    }
    if(isset(callback2)) {
      return safe_call.call(this,callback2,args);
    }
    return defaultValue;
  }

  function __safe( callbackName,source,args,defaultValue ){
    var undefinedVar;
    return safe_call.call( this, (isset(this.source[source])&&
			Object.prototype.hasOwnProperty.call(this.source[source], callbackName)) ? this.source[source][callbackName] : undefinedVar, args, function(){
      return safe_call.call(this,
        isset(this[callbackName][source])?
          this[callbackName][source]:(
            isset(this[callbackName][0])?
              this[callbackName][0]:(
                Object.prototype.hasOwnProperty.call(this, callbackName)?
                  this[callbackName]:
                  undefinedVar
              )
          ),
        args,
        defaultSetting[callbackName][source]||defaultSetting[callbackName][0]||defaultSetting[callbackName],
        defaultValue
      );
    },defaultValue);
  }

  function __get( property,source ){
    if(!isset(source))
      source = 0;
		
    if( $.isArray(this.source) && isset(this.source[source]) && isset(this.source[source][property]))
      return this.source[source][property];
			
    if( isset(this[property]) ){
      if( $.isArray(this[property]) ){
        if( isset(this[property][source]) )
          return this[property][source];
        if( isset(this[property][0]) )
          return this[property][0];
        return null;
      }
      return this[property];
    }
		
    return null;
  }

  function loadRemote( url,sourceObject,done,debug ){
		 if (sourceObject.xhr) {
      sourceObject.xhr.abort();
		 }
		 sourceObject.xhr = $.ajax($.extend(true,{
      url : url,
      type  : 'GET' ,
      async:true,
      cache :false,
      dataType : 'json'
		 },sourceObject.ajax))
		 
		 .done(function( data ){
        done&&done.apply(this,$.makeArray(arguments));
		 })
		 
		 .fail(function( jqXHR, textStatus ){
        if( debug )
          console.log('Request failed: ' + textStatus);
		 });
  }


  function findRight( data,query ){
    var right = false,source;
		
    for (source = 0;source < data.length;source += 1) {
      if( right = __safe.call(this,'findRight',source,[data[source],query,source]) ){
        return {right:right,source:source};
      }
    }
		
    return false;
  }

  function processData( data,query ){
    var source;
    preparseData
      .call( this,data,query );
		
    for (source = 0;source < data.length;source += 1) {
      data[source] = __safe.call(this,
        'filter',
        source,
        [data[source], query, source],
        data[source]
      );
    }
  }


  function collectData( query,datasource,callback ){
    var options = this,source;
		
    if( $.isFunction(options.source) ){
      options.source.apply(options,[query,function(items){
        datasource = [items];
        safe_call.call(options,callback,[query]);
      },datasource,0]);
    }else{
      for (source = 0;source < options.source.length;source += 1) {
        if ($.isArray(options.source[source])) {
          datasource[source] = options.source[source];
        } else if ($.isFunction(options.source[source])) {
          (function (source) {
            options.source[source].apply(options,[query, function(items){
              if (!datasource[source]) {
                datasource[source] = [];
              }
								
              if (items && $.isArray(items)) {
                switch (options.appendMethod) {
                case 'replace':
                  datasource[source] = items;
                  break;
                default:
                  datasource[source] = datasource[source].concat(items);
                }
              }
								
              safe_call.call(options,callback,[query]);
            }, datasource,source]);
          }(source));
        } else {
          switch (options.source[source].type) {
          case 'remote':
            if (isset(options.source[source].url)) {
              if (!isset(options.source[source].minLength) || query.length >= options.source[source].minLength){
                var url = __safe.call(options,'replace',source,[options.source[source].url,query],'');
                if (!datasource[source]) {
                  datasource[source] = [];
                }
                (function (source) {
                  loadRemote(url,options.source[source], function(resp){
                    datasource[source] = resp;
                    safe_call.call(options,callback,[query]);
                  },options.debug);
                }(source));
              }
            }
            break;
          default:
            if( isset(options.source[source]['data']) ){
              datasource[source] = options.source[source]['data'];
            }else{
              datasource[source] = options.source[source];
            }
          }
        }
      }
    }
    safe_call.call(options,callback,[query]);
  }

  function preparseData( data,query ){
    for( var source=0;source<data.length;source++ ){
      data[source] = __safe.call(this,
        'preparse',
        source,
        [data[source],query],
        data[source]
      );
    }
  }

  function renderData( data,query ){
    var  source, i, $div, $divs = [];
		
    for (source = 0;source < data.length;source += 1) {
      for (i = 0;i < data[source].length;i += 1) {
        if( $divs.length>=this.limit )
          break;
					
        $div = $(__safe.call(this,
          'render',source,
          [data[source][i],source,i,query],
          ''
        ));
				
        $div.data('source',source);
        $div.data('pid',i);
        $div.data('item',data[source][i]);
				
        $divs.push($div);
      }
    }
		
    return $divs;
  }

  function getItem( $div,dataset ){
    if( isset($div.data('source')) && 
			isset($div.data('pid')) && 
			isset(dataset[$div.data('source')]) && 
			isset(dataset[$div.data('source')][$div.data('pid')]) 
    ){
      return dataset[$div.data('source')][$div.data('pid')];
    }
    return false;
  }

  function getValue( $div,dataset ){
    var item = getItem($div,dataset);
		
    if( item ){
      return __safe.call(this,
        'getValue',$div.data('source'),
        [item,$div.data('source')]
      );
    }else{
      if( isset($div.data('value')) ){
        return decodeURIComponent($div.data('value'));
      }else{
        return $div.html();
      }
    }
  }

  defaultSetting = {
    minLength: 0,
    valueKey: 'value',
    titleKey: 'title',
    highlight: true,

    showHint: true,

    dropdownWidth: '100%',
    dropdownStyle: {},
    itemStyle: {},
    hintStyle: false,
    style: false,

    debug: true,
    openOnFocus: false,
    closeOnBlur: true,

    autoselect: false,
		
    accents: true,
    replaceAccentsForRemote: true,
		
    limit: 20,
    visibleLimit: 20,
    visibleHeight: 0,
    defaultHeightItem: 30,

    timeoutUpdate: 10,

    get: function (property, source) {
      return __get.call(this,property,source);
    },
		
    replace: [
      function (url, query) {
        if (this.replaceAccentsForRemote) {
          query = accentReplace(query);
        }
        return url.replace('%QUERY%',encodeURIComponent(query));
      }
    ],
		
    equal:function( value,query ){
      return query.toLowerCase()==value.substr(0,query.length).toLowerCase();
    },
		
    findRight:[
      function(items,query,source){
        var results = [],value = '',i;
        if (items) {
          for (i = 0;i < items.length;i += 1) {
            value = __safe.call(this,'getValue',source,[items[i],source]);
            if (__safe.call(this, 'equal', source, [value,query,source], false)) {
              return items[i];
            }
          }				
        }
        return false;
      }
    ],
		
    valid:[
      function (value, query) {
        if (this.accents) {
          value = accentReplace(value);
          query = accentReplace(query);
        }
        return value.toLowerCase().indexOf(query.toLowerCase())!=-1;
				
      }
    ],
		
    filter:[
      function (items, query, source) {
        var results = [], value = '',i;
        if (items) {					
          for (i = 0;i < items.length;i += 1) {
            value = isset(items[i][this.get('valueKey', source)]) ? items[i][this.get('valueKey', source)] : items[i].toString();
            if (__safe.call(this, 'valid', source, [value, query])) {
              results.push(items[i]); 
            }
          }
        }
        return results;
      }
    ],
		
    preparse:function(items){
      return items;
    },
		
    getValue: [
      function (item, source) {
        return isset(item[this.get('valueKey',source)])?item[this.get('valueKey',source)]:item.toString();
      }
    ],
		
    getTitle: [
      function (item, source) {
        return isset(item[this.get('titleKey',source)])?item[this.get('titleKey',source)]:item.toString();
      }
    ],
		
    render: [
      function (item, source, pid, query) {
        var value = __safe.call(this, 'getValue', source, [item, source], defaultSetting.getValue[0].call(this, item, source)),
          title = __safe.call(this, 'getTitle', source, [item, source], defaultSetting.getTitle[0].call(this, item, source)),
          _value = '',
          _query = '',
          _title = '',
          hilite_hints = '',
          highlighted = '',
          c, h, i,
          spos = 0;
					
        if (this.highlight) {
          if (!this.accents) {
            title = title.replace(new RegExp('('+escapeRegExp(query)+')','i'),'<b>$1</b>');
          }else{
            _title = accentReplace(title).toLowerCase().replace(/[<>]+/g, ''),
            _query = accentReplace(query).toLowerCase().replace(/[<>]+/g, '');
						
            hilite_hints = _title.replace(new RegExp(escapeRegExp(_query), 'g'), '<'+_query+'>');
            for (i=0;i < hilite_hints.length;i += 1) {
              c = title.charAt(spos);
              h = hilite_hints.charAt(i);
              if (h === '<') {
                highlighted += '<b>';
              } else if (h === '>') {
                highlighted += '</b>';
              } else {
                spos += 1;
                highlighted += c;
              }
            }
            title = highlighted;
          }
        }
					
        return '<div '+(value==query?'class="active"':'')+' data-value="'+encodeURIComponent(value)+'">'
							+title+
						'</div>';
      }
    ],
    appendMethod: 'concat', // supported merge and replace 
    source:[]
  };
  function init( that,options ){
    if( $(that).hasClass('xdsoft_input') )
      return;
		
    var $box = $('<div class="xdsoft_autocomplete"></div>'),
      $dropdown = $('<div class="xdsoft_autocomplete_dropdown"></div>'),
      $hint = $('<input readonly class="xdsoft_autocomplete_hint"/>'),
      $input = $(that),
      timer1 = 0,
      dataset = [],
      iOpen	= false,
      value = '',
      currentValue = '',
      currentSelect = '',
      active = null,
      pos = 0;
		
    //it can be used to access settings
    $input.data('autocomplete_options', options);
		
    $dropdown
      .on('mousedown', function(e) {
        e.preventDefault();
        e.stopPropagation();
      })
      .on('updatescroll.xdsoft', function() {
        var _act = $dropdown.find('.active');
        if (!_act.length) {
          return;
        }
				
        var top = _act.position().top,
          actHght = _act.outerHeight(true),
          scrlTop = $dropdown.scrollTop(),
          hght = $dropdown.height();
					
        if (top <0) {
          $dropdown.scrollTop(scrlTop-Math.abs(top));
        } else if (top+actHght>hght) {
          $dropdown.scrollTop(scrlTop+top+actHght-hght);
        }
      });
		
    $box
      .css({
        'display':$input.css('display'),
        'width':$input.css('width')
      });
		
    if( options.style )
      $box.css(options.style);
			
    $input
      .addClass('xdsoft_input')
      .attr('autocomplete','off');
		
    $dropdown
      .on('mousemove','div',function(){
        if( $(this).hasClass('active') )
          return true;
        $dropdown.find('div').removeClass('active');
        $(this).addClass('active');
      })
      .on('mousedown touchstart','div',function(){
        $dropdown.find('div').removeClass('active');
        $(this).addClass('active');
        $input.trigger('pick.xdsoft');
      });

    function manageData(){
      if ($input.val()!=currentValue){
        currentValue = $input.val();
      } else {
        return;
      }
      if (currentValue.length < options.minLength) {
        $input.trigger('close.xdsoft');
        return;
      }
      collectData.call(options,currentValue,dataset,function( query ){
        if (query != currentValue) {
          return;
        }
        var right;	
        processData.call(options, dataset,query);

        $input.trigger('updateContent.xdsoft');

        if (options.showHint && currentValue.length && currentValue.length<=$input.prop('size') && (right = findRight.call(options,dataset,currentValue))) {
          var title 	=  __safe.call(options,'getTitle',right.source,[right.right,right.source]);
          title = query + title.substr(query.length);
          $hint.val(title);
        } else {
          $hint.val('');
        }
      });

      return;
    }

    function manageKey (event) {
      var key = event.keyCode, right;
			
      switch( key ){
      case AKEY: case CKEY: case VKEY: case ZKEY: case YKEY:
        if (event.shiftKey || event.ctrlKey) {
          return true;
        }
        break;
      case SHIFTKEY:	
      case CTRLKEY:
        return true;
        break;
      case ARROWRIGHT:	
      case ARROWLEFT:
        if (ctrlDown || shiftDown || event.shiftKey || event.ctrlKey) {
          return true;
        }
        value = $input.val();
        pos = getCaretPosition($input[0]);
        if (key === ARROWRIGHT && pos === value.length) {
          if (right = findRight.call(options, dataset, value)){
            $input.trigger('pick.xdsoft', [
              __safe.call(options,
                'getValue', right.source,
                [right.right, right.source]
              )
            ]);
          } else {
            $input.trigger('pick.xdsoft');
          }
          event.preventDefault();
          return false;
        }
        return true;
      case TAB:
        return true;
      case ENTER:
        if (iOpen) {
          $input.trigger('pick.xdsoft');
          event.preventDefault();
          return false;
        } else {
          return true;
        }
        break;
      case ESC:
        $input
          .val(currentValue)
          .trigger('close.xdsoft');
        event.preventDefault();
        return false;
      case ARROWDOWN:
      case ARROWUP:
        if (!iOpen) {
          $input.trigger('open.xdsoft');
          $input.trigger('updateContent.xdsoft');
          event.preventDefault();
          return false;
        }
					
        active = $dropdown.find('div.active');
					
        var next = key==ARROWDOWN?'next':'prev', timepick = true;
					
        if( active.length ){
          active.removeClass('active');
          if( active[next]().length ){
            active[next]().addClass('active');
          }else{
            $input.val(currentValue);
            timepick = false;
          }
        }else{
          $dropdown.children().eq(key==ARROWDOWN?0:-1).addClass('active');
        }
					
        if( timepick ){
          $input.trigger('timepick.xdsoft');
        }
					
        $dropdown
          .trigger('updatescroll.xdsoft');
					
        event.preventDefault();
        return false;	
      }
      return;
    }
		
    $input
      .data('xdsoft_autocomplete',dataset)
      .after($box)
      .on('pick.xdsoft', function( event,_value ){

        $input
          .trigger('timepick.xdsoft',_value);
				
        currentSelect = currentValue = $input.val();
				
        $input
          .trigger('close.xdsoft');
				
        //currentInput = false;
				
        active = $dropdown.find('div.active').eq(0);
							
        if( !active.length )
          active = $dropdown.children().first();
					
        $input.trigger('selected.xdsoft',[getItem(active,dataset)]);
      })
      .on('timepick.xdsoft', function( event,_value ){
        active = $dropdown.find('div.active');
							
        if( !active.length )
          active = $dropdown.children().first();
				
        if( active.length ){
          if( !isset(_value) ){
            $input.val(getValue.call(options,active,dataset));
          }else{
            $input.val(_value);
          }
          $input.trigger('autocompleted.xdsoft',[getItem(active,dataset)]);
          $hint.val('');
          setCaretPosition($input[0],$input.val().length);
        }
      })
      .on('keydown.xdsoft input.xdsoft cut.xdsoft paste.xdsoft', function( event ){
        var ret = manageKey(event);
				
        if (ret === false || ret === true) {
          return ret;
        }
				
        setTimeout(function(){
          manageData();
        },1);
				
        manageData();
      })
      .on('change.xdsoft', function( event ){
        currentValue = $input.val();
      });
		
    currentValue = $input.val();
		
    collectData.call(options, $input.val(),dataset,function( query ){
      processData.call(options,dataset,query);
    });
		
    if( options.openOnFocus ){
      $input.on('focusin.xdsoft',function(){
        $input.trigger('open.xdsoft');
        $input.trigger('updateContent.xdsoft');
      });
    }
		
    if( options.closeOnBlur )
      $input.on('focusout.xdsoft',function(){
        $input.trigger('close.xdsoft');
      });
			
    $box
      .append($input)
      .append($dropdown);


    var olderBackground = false,
      timerUpdate = 0;
		
    $input
      .on('updateHelperPosition.xdsoft',function(){
        clearTimeout(timerUpdate);
        timerUpdate = setTimeout(function(){
          $box.css({
            'display':$input.css('display'),
            'width':$input.css('width')
          });
          $dropdown.css($.extend(true,{
            left:$input.position().left,
            top:$input.position().top + parseInt($input.css('marginTop'))+parseInt($input[0].offsetHeight),
            marginLeft:$input.css('marginLeft'),
            marginRight:$input.css('marginRight'),
            width:options.dropdownWidth=='100%'?$input[0].offsetWidth:options.dropdownWidth
          },options.dropdownStyle));
					
          if (options.showHint) {
            var style = getComputedStyle($input[0], '');
						
            $hint[0].style.cssText = style.cssText;
						
            $hint.css({
              'box-sizing':style.boxSizing,
              borderStyle:'solid',
              borderCollapse:style.borderCollapse,
              borderLeftWidth:style.borderLeftWidth,
              borderRightWidth:style.borderRightWidth,
              borderTopWidth:style.borderTopWidth,
              borderBottomWidth:style.borderBottomWidth,
              paddingBottom:style.paddingBottom,
              marginBottom:style.marginBottom,
              paddingTop:style.paddingTop,
              marginTop:style.marginTop,
              paddingLeft:style.paddingLeft,
              marginLeft:style.marginLeft,
              paddingRight:style.paddingRight,
              marginRight:style.marginRight,
              maxHeight:style.maxHeight,
              minHeight:style.minHeight,
              maxWidth:style.maxWidth,
              minWidth:style.minWidth,
              width:style.width,
              letterSpacing:style.letterSpacing,
              lineHeight:style.lineHeight,
              outlineWidth:style.outlineWidth,
              fontFamily:style.fontFamily,
              fontVariant:style.fontVariant,
              fontStyle:$input.css('fontStyle'),
              fontSize:$input.css('fontSize'),
              fontWeight:$input.css('fontWeight'),
              flex:style.flex,
              justifyContent:style.justifyContent,
              borderRadius:style.borderRadius,
              '-webkit-box-shadow':'none',
              'box-shadow':'none'
            });
						
            $input.css('font-size',$input.css('fontSize'));// fix bug with em font size
						
            $hint.innerHeight($input.innerHeight());
						
            $hint.css($.extend(true,{
              position:'absolute',
              zIndex:'1',
              borderColor:'transparent',
              outlineColor:'transparent',
              left:$input.position().left,
              top:$input.position().top,
              background:$input.css('background')
            },options.hintStyle));
						
						
            if( olderBackground!==false ){
              $hint.css('background',olderBackground);
            }else{
              olderBackground = $input.css('background');
            }
						
            try{
              $input[0].style.setProperty('background', 'transparent', 'important');
            } catch(e) {
              $input.css('background','transparent');
            }

            $box
              .append($hint);
          }
        }, options.timeoutUpdate||1);
      });
		
    if ($input.is(':visible')) {
      $input
        .trigger('updateHelperPosition.xdsoft');
    } else {
      interval_for_visibility = setInterval(function () {
        if ($input.is(':visible')) {
          $input
            .trigger('updateHelperPosition.xdsoft');
          clearInterval(interval_for_visibility);
        }
      },100);
    }
		
    $(window).on('resize',function () {
      $box.css({
        'width':'auto'
      });
      $input
        .trigger('updateHelperPosition.xdsoft');
    });
		
    $input	
      .on('close.xdsoft',function(){
        if (!iOpen) {
          return;
        }

        $dropdown
          .hide();

        $hint
          .val('');	

        if (!options.autoselect) {
          $input.val(currentValue);
        }

        iOpen = false;

        //currentInput = false;
      })
			
      .on('updateContent.xdsoft',function(){
        var out = renderData.call(options,dataset,$input.val()),
          hght = 10;
				
        if (out.length) {
          $input.trigger('open.xdsoft');
        } else {
          $input.trigger('close.xdsoft');
          return;
        }

        $(out).each(function(){
          this.css($.extend(true,{
            paddingLeft:$input.css('paddingLeft'),
            paddingRight:$input.css('paddingRight')
          },options.itemStyle));
        });

        $dropdown
          .html(out);
					
        if (options.visibleHeight){
          hght = options.visibleHeight;
        } else {
          hght = options.visibleLimit * ((out[0] ? out[0].outerHeight(true) : 0) || options.defaultHeightItem) + 5;
        }
				
        $dropdown
          .css('maxHeight', hght+'px');
      })
			
      .on('open.xdsoft',function(){
        if( iOpen )
          return;
				
        $dropdown
          .show();

        iOpen = true;
					
        //currentInput = $input;
      })
      .on('destroy.xdsoft',function(){
        $input.removeClass('xdsoft');
        $box.after($input);
        $box.remove();
        clearTimeout(timer1);
        //currentInput = false;
        $input.data('xdsoft_autocomplete',null);
        $input
          .off('.xdsoft');
      });
  }
	
  publics = {
    destroy: function () {
      return this.trigger('destroy.xdsoft');
    },
    update: function () {
      return this.trigger('updateHelperPosition.xdsoft');	
    },
    options: function (_options) {
      if (this.data('autocomplete_options') && $.isPlainObject(_options)) {
        this.data('autocomplete_options', $.extend(true, this.data('autocomplete_options'), _options));
      }
      return this;
    },
    setSource: function (_newsource, id) {
      if(this.data('autocomplete_options') && ($.isPlainObject(_newsource) || $.isFunction(_newsource) || $.isArray(_newsource))) {
        var options = this.data('autocomplete_options'), 
          dataset = this.data('xdsoft_autocomplete'),
          source 	= options.source;
        if (id!==undefined && !isNaN(id)) {
          if ($.isPlainObject(_newsource) || $.isArray(_newsource)) {
            source[id] =  $.extend(true,$.isArray(_newsource) ? [] : {}, _newsource);
          } else {
            source[id] =  _newsource;
          }
        } else {
          if ($.isFunction(_newsource)) {
            this.data('autocomplete_options').source = _newsource;
          } else {
            $.extend(true, source, _newsource);
          }
        }
				
        collectData.call(options, this.val(), dataset,function( query ){
          processData.call(options,dataset,query);
        });
      }
      return this;
    },
    getSource: function (id) {
      if (this.data('autocomplete_options')) {
        var source = this.data('autocomplete_options').source;
        if (id!==undefined && !isNaN(id) &&source[id]) {
          return source[id];
        } else {
          return source;
        }
      }
      return null;
    } 
  };
	
  $.fn.autocomplete = function(_options, _second, _third){
    if ($.type(_options) === 'string' && publics[_options]) {
      return publics[_options].call(this, _second, _third);
    }
    return this.each(function () {
      var options = $.extend(true, {}, defaultSetting, _options);
      init(this, options);
    });
  };
}(jQuery));
