!function(l){function i(i){for(var e,s,t=i[0],n=i[1],o=i[2],r=0,a=[];r<t.length;r++)s=t[r],Object.prototype.hasOwnProperty.call(d,s)&&d[s]&&a.push(d[s][0]),d[s]=0;for(e in n)Object.prototype.hasOwnProperty.call(n,e)&&(l[e]=n[e]);for(h&&h(i);a.length;)a.shift()();return c.push.apply(c,o||[]),u()}function u(){for(var i,e=0;e<c.length;e++){for(var s=c[e],t=!0,n=1;n<s.length;n++){var o=s[n];0!==d[o]&&(t=!1)}t&&(c.splice(e--,1),i=r(r.s=s[0]))}return i}var s={},d={221:0},c=[];function r(i){if(s[i])return s[i].exports;var e=s[i]={i:i,l:!1,exports:{}};return l[i].call(e.exports,e,e.exports,r),e.l=!0,e.exports}r.m=l,r.c=s,r.d=function(i,e,s){r.o(i,e)||Object.defineProperty(i,e,{enumerable:!0,get:s})},r.r=function(i){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(i,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(i,"__esModule",{value:!0})},r.t=function(e,i){if(1&i&&(e=r(e)),8&i)return e;if(4&i&&"object"==typeof e&&e&&e.__esModule)return e;var s=Object.create(null);if(r.r(s),Object.defineProperty(s,"default",{enumerable:!0,value:e}),2&i&&"string"!=typeof e)for(var t in e)r.d(s,t,function(i){return e[i]}.bind(null,t));return s},r.n=function(i){var e=i&&i.__esModule?function(){return i.default}:function(){return i};return r.d(e,"a",e),e},r.o=function(i,e){return Object.prototype.hasOwnProperty.call(i,e)},r.p="/static-dist/";var e=window.webpackJsonp=window.webpackJsonp||[],t=e.push.bind(e);e.push=i,e=e.slice();for(var n=0;n<e.length;n++)i(e[n]);var h=t;c.push([694,0]),u()}({283:function(_,T,E){var x;
/*!
 * UAParser.js v0.7.21
 * Lightweight JavaScript-based User-Agent string parser
 * https://github.com/faisalman/ua-parser-js
 *
 * Copyright © 2012-2019 Faisal Salman <f@faisalman.com>
 * Licensed under MIT License
 */
!function(n,c){"use strict";var h="function",i="undefined",e="model",s="name",t="type",o="vendor",r="version",a="architecture",l="console",u="mobile",d="tablet",w="smarttv",v="wearable",m={extend:function(i,e){var s={};for(var t in i)e[t]&&e[t].length%2==0?s[t]=e[t].concat(i[t]):s[t]=i[t];return s},has:function(i,e){return"string"==typeof i&&-1!==e.toLowerCase().indexOf(i.toLowerCase())},lowerize:function(i){return i.toLowerCase()},major:function(i){return"string"==typeof i?i.replace(/[^\d\.]/g,"").split(".")[0]:c},trim:function(i){return i.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,"")}},p={rgx:function(i,e){for(var s,t,n,o,r,a=0;a<e.length&&!o;){for(var l=e[a],u=e[a+1],d=s=0;d<l.length&&!o;)if(o=l[d++].exec(i))for(t=0;t<u.length;t++)r=o[++s],"object"==typeof(n=u[t])&&0<n.length?2==n.length?typeof n[1]==h?this[n[0]]=n[1].call(this,r):this[n[0]]=n[1]:3==n.length?typeof n[1]!=h||n[1].exec&&n[1].test?this[n[0]]=r?r.replace(n[1],n[2]):c:this[n[0]]=r?n[1].call(this,r,n[2]):c:4==n.length&&(this[n[0]]=r?n[3].call(this,r.replace(n[1],n[2])):c):this[n]=r||c;a+=2}},str:function(i,e){for(var s in e)if("object"==typeof e[s]&&0<e[s].length){for(var t=0;t<e[s].length;t++)if(m.has(e[s][t],i))return"?"===s?c:s}else if(m.has(e[s],i))return"?"===s?c:s;return i}},f={browser:{oldsafari:{version:{"1.0":"/8",1.2:"/1",1.3:"/3","2.0":"/412","2.0.2":"/416","2.0.3":"/417","2.0.4":"/419","?":"/"}}},device:{amazon:{model:{"Fire Phone":["SD","KF"]}},sprint:{model:{"Evo Shift 4G":"7373KT"},vendor:{HTC:"APA",Sprint:"Sprint"}}},os:{windows:{version:{ME:"4.90","NT 3.11":"NT3.51","NT 4.0":"NT4.0",2e3:"NT 5.0",XP:["NT 5.1","NT 5.2"],Vista:"NT 6.0",7:"NT 6.1",8:"NT 6.2",8.1:"NT 6.3",10:["NT 6.4","NT 10.0"],RT:"ARM"}}}},b={browser:[[/(opera\smini)\/([\w\.-]+)/i,/(opera\s[mobiletab]+).+version\/([\w\.-]+)/i,/(opera).+version\/([\w\.]+)/i,/(opera)[\/\s]+([\w\.]+)/i],[s,r],[/(opios)[\/\s]+([\w\.]+)/i],[[s,"Opera Mini"],r],[/\s(opr)\/([\w\.]+)/i],[[s,"Opera"],r],[/(kindle)\/([\w\.]+)/i,/(lunascape|maxthon|netfront|jasmine|blazer)[\/\s]?([\w\.]*)/i,/(avant\s|iemobile|slim)(?:browser)?[\/\s]?([\w\.]*)/i,/(bidubrowser|baidubrowser)[\/\s]?([\w\.]+)/i,/(?:ms|\()(ie)\s([\w\.]+)/i,/(rekonq)\/([\w\.]*)/i,/(chromium|flock|rockmelt|midori|epiphany|silk|skyfire|ovibrowser|bolt|iron|vivaldi|iridium|phantomjs|bowser|quark|qupzilla|falkon)\/([\w\.-]+)/i],[s,r],[/(konqueror)\/([\w\.]+)/i],[[s,"Konqueror"],r],[/(trident).+rv[:\s]([\w\.]+).+like\sgecko/i],[[s,"IE"],r],[/(edge|edgios|edga|edg)\/((\d+)?[\w\.]+)/i],[[s,"Edge"],r],[/(yabrowser)\/([\w\.]+)/i],[[s,"Yandex"],r],[/(Avast)\/([\w\.]+)/i],[[s,"Avast Secure Browser"],r],[/(AVG)\/([\w\.]+)/i],[[s,"AVG Secure Browser"],r],[/(puffin)\/([\w\.]+)/i],[[s,"Puffin"],r],[/(focus)\/([\w\.]+)/i],[[s,"Firefox Focus"],r],[/(opt)\/([\w\.]+)/i],[[s,"Opera Touch"],r],[/((?:[\s\/])uc?\s?browser|(?:juc.+)ucweb)[\/\s]?([\w\.]+)/i],[[s,"UCBrowser"],r],[/(comodo_dragon)\/([\w\.]+)/i],[[s,/_/g," "],r],[/(windowswechat qbcore)\/([\w\.]+)/i],[[s,"WeChat(Win) Desktop"],r],[/(micromessenger)\/([\w\.]+)/i],[[s,"WeChat"],r],[/(brave)\/([\w\.]+)/i],[[s,"Brave"],r],[/(qqbrowserlite)\/([\w\.]+)/i],[s,r],[/(QQ)\/([\d\.]+)/i],[s,r],[/m?(qqbrowser)[\/\s]?([\w\.]+)/i],[s,r],[/(baiduboxapp)[\/\s]?([\w\.]+)/i],[s,r],[/(2345Explorer)[\/\s]?([\w\.]+)/i],[s,r],[/(MetaSr)[\/\s]?([\w\.]+)/i],[s],[/(LBBROWSER)/i],[s],[/xiaomi\/miuibrowser\/([\w\.]+)/i],[r,[s,"MIUI Browser"]],[/;fbav\/([\w\.]+);/i],[r,[s,"Facebook"]],[/safari\s(line)\/([\w\.]+)/i,/android.+(line)\/([\w\.]+)\/iab/i],[s,r],[/headlesschrome(?:\/([\w\.]+)|\s)/i],[r,[s,"Chrome Headless"]],[/\swv\).+(chrome)\/([\w\.]+)/i],[[s,/(.+)/,"$1 WebView"],r],[/((?:oculus|samsung)browser)\/([\w\.]+)/i],[[s,/(.+(?:g|us))(.+)/,"$1 $2"],r],[/android.+version\/([\w\.]+)\s+(?:mobile\s?safari|safari)*/i],[r,[s,"Android Browser"]],[/(sailfishbrowser)\/([\w\.]+)/i],[[s,"Sailfish Browser"],r],[/(chrome|omniweb|arora|[tizenoka]{5}\s?browser)\/v?([\w\.]+)/i],[s,r],[/(dolfin)\/([\w\.]+)/i],[[s,"Dolphin"],r],[/(qihu|qhbrowser|qihoobrowser|360browser)/i],[[s,"360 Browser"]],[/((?:android.+)crmo|crios)\/([\w\.]+)/i],[[s,"Chrome"],r],[/(coast)\/([\w\.]+)/i],[[s,"Opera Coast"],r],[/fxios\/([\w\.-]+)/i],[r,[s,"Firefox"]],[/version\/([\w\.]+).+?mobile\/\w+\s(safari)/i],[r,[s,"Mobile Safari"]],[/version\/([\w\.]+).+?(mobile\s?safari|safari)/i],[r,s],[/webkit.+?(gsa)\/([\w\.]+).+?(mobile\s?safari|safari)(\/[\w\.]+)/i],[[s,"GSA"],r],[/webkit.+?(mobile\s?safari|safari)(\/[\w\.]+)/i],[s,[r,p.str,f.browser.oldsafari.version]],[/(webkit|khtml)\/([\w\.]+)/i],[s,r],[/(navigator|netscape)\/([\w\.-]+)/i],[[s,"Netscape"],r],[/(swiftfox)/i,/(icedragon|iceweasel|camino|chimera|fennec|maemo\sbrowser|minimo|conkeror)[\/\s]?([\w\.\+]+)/i,/(firefox|seamonkey|k-meleon|icecat|iceape|firebird|phoenix|palemoon|basilisk|waterfox)\/([\w\.-]+)$/i,/(mozilla)\/([\w\.]+).+rv\:.+gecko\/\d+/i,/(polaris|lynx|dillo|icab|doris|amaya|w3m|netsurf|sleipnir)[\/\s]?([\w\.]+)/i,/(links)\s\(([\w\.]+)/i,/(gobrowser)\/?([\w\.]*)/i,/(ice\s?browser)\/v?([\w\._]+)/i,/(mosaic)[\/\s]([\w\.]+)/i],[s,r]],cpu:[[/(?:(amd|x(?:(?:86|64)[_-])?|wow|win)64)[;\)]/i],[[a,"amd64"]],[/(ia32(?=;))/i],[[a,m.lowerize]],[/((?:i[346]|x)86)[;\)]/i],[[a,"ia32"]],[/windows\s(ce|mobile);\sppc;/i],[[a,"arm"]],[/((?:ppc|powerpc)(?:64)?)(?:\smac|;|\))/i],[[a,/ower/,"",m.lowerize]],[/(sun4\w)[;\)]/i],[[a,"sparc"]],[/((?:avr32|ia64(?=;))|68k(?=\))|arm(?:64|(?=v\d+[;l]))|(?=atmel\s)avr|(?:irix|mips|sparc)(?:64)?(?=;)|pa-risc)/i],[[a,m.lowerize]]],device:[[/\((ipad|playbook);[\w\s\),;-]+(rim|apple)/i],[e,o,[t,d]],[/applecoremedia\/[\w\.]+ \((ipad)/],[e,[o,"Apple"],[t,d]],[/(apple\s{0,1}tv)/i],[[e,"Apple TV"],[o,"Apple"],[t,w]],[/(archos)\s(gamepad2?)/i,/(hp).+(touchpad)/i,/(hp).+(tablet)/i,/(kindle)\/([\w\.]+)/i,/\s(nook)[\w\s]+build\/(\w+)/i,/(dell)\s(strea[kpr\s\d]*[\dko])/i],[o,e,[t,d]],[/(kf[A-z]+)\sbuild\/.+silk\//i],[e,[o,"Amazon"],[t,d]],[/(sd|kf)[0349hijorstuw]+\sbuild\/.+silk\//i],[[e,p.str,f.device.amazon.model],[o,"Amazon"],[t,u]],[/android.+aft([bms])\sbuild/i],[e,[o,"Amazon"],[t,w]],[/\((ip[honed|\s\w*]+);.+(apple)/i],[e,o,[t,u]],[/\((ip[honed|\s\w*]+);/i],[e,[o,"Apple"],[t,u]],[/(blackberry)[\s-]?(\w+)/i,/(blackberry|benq|palm(?=\-)|sonyericsson|acer|asus|dell|meizu|motorola|polytron)[\s_-]?([\w-]*)/i,/(hp)\s([\w\s]+\w)/i,/(asus)-?(\w+)/i],[o,e,[t,u]],[/\(bb10;\s(\w+)/i],[e,[o,"BlackBerry"],[t,u]],[/android.+(transfo[prime\s]{4,10}\s\w+|eeepc|slider\s\w+|nexus 7|padfone|p00c)/i],[e,[o,"Asus"],[t,d]],[/(sony)\s(tablet\s[ps])\sbuild\//i,/(sony)?(?:sgp.+)\sbuild\//i],[[o,"Sony"],[e,"Xperia Tablet"],[t,d]],[/android.+\s([c-g]\d{4}|so[-l]\w+)(?=\sbuild\/|\).+chrome\/(?![1-6]{0,1}\d\.))/i],[e,[o,"Sony"],[t,u]],[/\s(ouya)\s/i,/(nintendo)\s([wids3u]+)/i],[o,e,[t,l]],[/android.+;\s(shield)\sbuild/i],[e,[o,"Nvidia"],[t,l]],[/(playstation\s[34portablevi]+)/i],[e,[o,"Sony"],[t,l]],[/(sprint\s(\w+))/i],[[o,p.str,f.device.sprint.vendor],[e,p.str,f.device.sprint.model],[t,u]],[/(htc)[;_\s-]+([\w\s]+(?=\)|\sbuild)|\w+)/i,/(zte)-(\w*)/i,/(alcatel|geeksphone|nexian|panasonic|(?=;\s)sony)[_\s-]?([\w-]*)/i],[o,[e,/_/g," "],[t,u]],[/(nexus\s9)/i],[e,[o,"HTC"],[t,d]],[/d\/huawei([\w\s-]+)[;\)]/i,/(nexus\s6p|vog-l29|ane-lx1|eml-l29)/i],[e,[o,"Huawei"],[t,u]],[/android.+(bah2?-a?[lw]\d{2})/i],[e,[o,"Huawei"],[t,d]],[/(microsoft);\s(lumia[\s\w]+)/i],[o,e,[t,u]],[/[\s\(;](xbox(?:\sone)?)[\s\);]/i],[e,[o,"Microsoft"],[t,l]],[/(kin\.[onetw]{3})/i],[[e,/\./g," "],[o,"Microsoft"],[t,u]],[/\s(milestone|droid(?:[2-4x]|\s(?:bionic|x2|pro|razr))?:?(\s4g)?)[\w\s]+build\//i,/mot[\s-]?(\w*)/i,/(XT\d{3,4}) build\//i,/(nexus\s6)/i],[e,[o,"Motorola"],[t,u]],[/android.+\s(mz60\d|xoom[\s2]{0,2})\sbuild\//i],[e,[o,"Motorola"],[t,d]],[/hbbtv\/\d+\.\d+\.\d+\s+\([\w\s]*;\s*(\w[^;]*);([^;]*)/i],[[o,m.trim],[e,m.trim],[t,w]],[/hbbtv.+maple;(\d+)/i],[[e,/^/,"SmartTV"],[o,"Samsung"],[t,w]],[/\(dtv[\);].+(aquos)/i],[e,[o,"Sharp"],[t,w]],[/android.+((sch-i[89]0\d|shw-m380s|gt-p\d{4}|gt-n\d+|sgh-t8[56]9|nexus 10))/i,/((SM-T\w+))/i],[[o,"Samsung"],e,[t,d]],[/smart-tv.+(samsung)/i],[o,[t,w],e],[/((s[cgp]h-\w+|gt-\w+|galaxy\snexus|sm-\w[\w\d]+))/i,/(sam[sung]*)[\s-]*(\w+-?[\w-]*)/i,/sec-((sgh\w+))/i],[[o,"Samsung"],e,[t,u]],[/sie-(\w*)/i],[e,[o,"Siemens"],[t,u]],[/(maemo|nokia).*(n900|lumia\s\d+)/i,/(nokia)[\s_-]?([\w-]*)/i],[[o,"Nokia"],e,[t,u]],[/android[x\d\.\s;]+\s([ab][1-7]\-?[0178a]\d\d?)/i],[e,[o,"Acer"],[t,d]],[/android.+([vl]k\-?\d{3})\s+build/i],[e,[o,"LG"],[t,d]],[/android\s3\.[\s\w;-]{10}(lg?)-([06cv9]{3,4})/i],[[o,"LG"],e,[t,d]],[/(lg) netcast\.tv/i],[o,e,[t,w]],[/(nexus\s[45])/i,/lg[e;\s\/-]+(\w*)/i,/android.+lg(\-?[\d\w]+)\s+build/i],[e,[o,"LG"],[t,u]],[/(lenovo)\s?(s(?:5000|6000)(?:[\w-]+)|tab(?:[\s\w]+))/i],[o,e,[t,d]],[/android.+(ideatab[a-z0-9\-\s]+)/i],[e,[o,"Lenovo"],[t,d]],[/(lenovo)[_\s-]?([\w-]+)/i],[o,e,[t,u]],[/linux;.+((jolla));/i],[o,e,[t,u]],[/((pebble))app\/[\d\.]+\s/i],[o,e,[t,v]],[/android.+;\s(oppo)\s?([\w\s]+)\sbuild/i],[o,e,[t,u]],[/crkey/i],[[e,"Chromecast"],[o,"Google"],[t,w]],[/android.+;\s(glass)\s\d/i],[e,[o,"Google"],[t,v]],[/android.+;\s(pixel c)[\s)]/i],[e,[o,"Google"],[t,d]],[/android.+;\s(pixel( [23])?( xl)?)[\s)]/i],[e,[o,"Google"],[t,u]],[/android.+;\s(\w+)\s+build\/hm\1/i,/android.+(hm[\s\-_]*note?[\s_]*(?:\d\w)?)\s+build/i,/android.+(mi[\s\-_]*(?:a\d|one|one[\s_]plus|note lte)?[\s_]*(?:\d?\w?)[\s_]*(?:plus)?)\s+build/i,/android.+(redmi[\s\-_]*(?:note)?(?:[\s_]*[\w\s]+))\s+build/i],[[e,/_/g," "],[o,"Xiaomi"],[t,u]],[/android.+(mi[\s\-_]*(?:pad)(?:[\s_]*[\w\s]+))\s+build/i],[[e,/_/g," "],[o,"Xiaomi"],[t,d]],[/android.+;\s(m[1-5]\snote)\sbuild/i],[e,[o,"Meizu"],[t,u]],[/(mz)-([\w-]{2,})/i],[[o,"Meizu"],e,[t,u]],[/android.+a000(1)\s+build/i,/android.+oneplus\s(a\d{4})[\s)]/i],[e,[o,"OnePlus"],[t,u]],[/android.+[;\/]\s*(RCT[\d\w]+)\s+build/i],[e,[o,"RCA"],[t,d]],[/android.+[;\/\s]+(Venue[\d\s]{2,7})\s+build/i],[e,[o,"Dell"],[t,d]],[/android.+[;\/]\s*(Q[T|M][\d\w]+)\s+build/i],[e,[o,"Verizon"],[t,d]],[/android.+[;\/]\s+(Barnes[&\s]+Noble\s+|BN[RT])(V?.*)\s+build/i],[[o,"Barnes & Noble"],e,[t,d]],[/android.+[;\/]\s+(TM\d{3}.*\b)\s+build/i],[e,[o,"NuVision"],[t,d]],[/android.+;\s(k88)\sbuild/i],[e,[o,"ZTE"],[t,d]],[/android.+[;\/]\s*(gen\d{3})\s+build.*49h/i],[e,[o,"Swiss"],[t,u]],[/android.+[;\/]\s*(zur\d{3})\s+build/i],[e,[o,"Swiss"],[t,d]],[/android.+[;\/]\s*((Zeki)?TB.*\b)\s+build/i],[e,[o,"Zeki"],[t,d]],[/(android).+[;\/]\s+([YR]\d{2})\s+build/i,/android.+[;\/]\s+(Dragon[\-\s]+Touch\s+|DT)(\w{5})\sbuild/i],[[o,"Dragon Touch"],e,[t,d]],[/android.+[;\/]\s*(NS-?\w{0,9})\sbuild/i],[e,[o,"Insignia"],[t,d]],[/android.+[;\/]\s*((NX|Next)-?\w{0,9})\s+build/i],[e,[o,"NextBook"],[t,d]],[/android.+[;\/]\s*(Xtreme\_)?(V(1[045]|2[015]|30|40|60|7[05]|90))\s+build/i],[[o,"Voice"],e,[t,u]],[/android.+[;\/]\s*(LVTEL\-)?(V1[12])\s+build/i],[[o,"LvTel"],e,[t,u]],[/android.+;\s(PH-1)\s/i],[e,[o,"Essential"],[t,u]],[/android.+[;\/]\s*(V(100MD|700NA|7011|917G).*\b)\s+build/i],[e,[o,"Envizen"],[t,d]],[/android.+[;\/]\s*(Le[\s\-]+Pan)[\s\-]+(\w{1,9})\s+build/i],[o,e,[t,d]],[/android.+[;\/]\s*(Trio[\s\-]*.*)\s+build/i],[e,[o,"MachSpeed"],[t,d]],[/android.+[;\/]\s*(Trinity)[\-\s]*(T\d{3})\s+build/i],[o,e,[t,d]],[/android.+[;\/]\s*TU_(1491)\s+build/i],[e,[o,"Rotor"],[t,d]],[/android.+(KS(.+))\s+build/i],[e,[o,"Amazon"],[t,d]],[/android.+(Gigaset)[\s\-]+(Q\w{1,9})\s+build/i],[o,e,[t,d]],[/\s(tablet|tab)[;\/]/i,/\s(mobile)(?:[;\/]|\ssafari)/i],[[t,m.lowerize],o,e],[/[\s\/\(](smart-?tv)[;\)]/i],[[t,w]],[/(android[\w\.\s\-]{0,9});.+build/i],[e,[o,"Generic"]]],engine:[[/windows.+\sedge\/([\w\.]+)/i],[r,[s,"EdgeHTML"]],[/webkit\/537\.36.+chrome\/(?!27)([\w\.]+)/i],[r,[s,"Blink"]],[/(presto)\/([\w\.]+)/i,/(webkit|trident|netfront|netsurf|amaya|lynx|w3m|goanna)\/([\w\.]+)/i,/(khtml|tasman|links)[\/\s]\(?([\w\.]+)/i,/(icab)[\/\s]([23]\.[\d\.]+)/i],[s,r],[/rv\:([\w\.]{1,9}).+(gecko)/i],[r,s]],os:[[/microsoft\s(windows)\s(vista|xp)/i],[s,r],[/(windows)\snt\s6\.2;\s(arm)/i,/(windows\sphone(?:\sos)*)[\s\/]?([\d\.\s\w]*)/i,/(windows\smobile|windows)[\s\/]?([ntce\d\.\s]+\w)/i],[s,[r,p.str,f.os.windows.version]],[/(win(?=3|9|n)|win\s9x\s)([nt\d\.]+)/i],[[s,"Windows"],[r,p.str,f.os.windows.version]],[/\((bb)(10);/i],[[s,"BlackBerry"],r],[/(blackberry)\w*\/?([\w\.]*)/i,/(tizen|kaios)[\/\s]([\w\.]+)/i,/(android|webos|palm\sos|qnx|bada|rim\stablet\sos|meego|sailfish|contiki)[\/\s-]?([\w\.]*)/i],[s,r],[/(symbian\s?os|symbos|s60(?=;))[\/\s-]?([\w\.]*)/i],[[s,"Symbian"],r],[/\((series40);/i],[s],[/mozilla.+\(mobile;.+gecko.+firefox/i],[[s,"Firefox OS"],r],[/(nintendo|playstation)\s([wids34portablevu]+)/i,/(mint)[\/\s\(]?(\w*)/i,/(mageia|vectorlinux)[;\s]/i,/(joli|[kxln]?ubuntu|debian|suse|opensuse|gentoo|(?=\s)arch|slackware|fedora|mandriva|centos|pclinuxos|redhat|zenwalk|linpus)[\/\s-]?(?!chrom)([\w\.-]*)/i,/(hurd|linux)\s?([\w\.]*)/i,/(gnu)\s?([\w\.]*)/i],[s,r],[/(cros)\s[\w]+\s([\w\.]+\w)/i],[[s,"Chromium OS"],r],[/(sunos)\s?([\w\.\d]*)/i],[[s,"Solaris"],r],[/\s([frentopc-]{0,4}bsd|dragonfly)\s?([\w\.]*)/i],[s,r],[/(haiku)\s(\w+)/i],[s,r],[/cfnetwork\/.+darwin/i,/ip[honead]{2,4}(?:.*os\s([\w]+)\slike\smac|;\sopera)/i],[[r,/_/g,"."],[s,"iOS"]],[/(mac\sos\sx)\s?([\w\s\.]*)/i,/(macintosh|mac(?=_powerpc)\s)/i],[[s,"Mac OS"],[r,/_/g,"."]],[/((?:open)?solaris)[\/\s-]?([\w\.]*)/i,/(aix)\s((\d)(?=\.|\)|\s)[\w\.])*/i,/(plan\s9|minix|beos|os\/2|amigaos|morphos|risc\sos|openvms|fuchsia)/i,/(unix)\s?([\w\.]*)/i],[s,r]]},g=function(i,e){if("object"==typeof i&&(e=i,i=c),!(this instanceof g))return new g(i,e).getResult();var s=i||(n&&n.navigator&&n.navigator.userAgent?n.navigator.userAgent:""),t=e?m.extend(b,e):b;return this.getBrowser=function(){var i={name:c,version:c};return p.rgx.call(i,s,t.browser),i.major=m.major(i.version),i},this.getCPU=function(){var i={architecture:c};return p.rgx.call(i,s,t.cpu),i},this.getDevice=function(){var i={vendor:c,model:c,type:c};return p.rgx.call(i,s,t.device),i},this.getEngine=function(){var i={name:c,version:c};return p.rgx.call(i,s,t.engine),i},this.getOS=function(){var i={name:c,version:c};return p.rgx.call(i,s,t.os),i},this.getResult=function(){return{ua:this.getUA(),browser:this.getBrowser(),engine:this.getEngine(),os:this.getOS(),device:this.getDevice(),cpu:this.getCPU()}},this.getUA=function(){return s},this.setUA=function(i){return s=i,this},this};g.VERSION="0.7.21",g.BROWSER={NAME:s,MAJOR:"major",VERSION:r},g.CPU={ARCHITECTURE:a},g.DEVICE={MODEL:e,VENDOR:o,TYPE:t,CONSOLE:l,MOBILE:u,SMARTTV:w,TABLET:d,WEARABLE:v,EMBEDDED:"embedded"},g.ENGINE={NAME:s,VERSION:r},g.OS={NAME:s,VERSION:r},typeof T!=i?(typeof _!=i&&_.exports&&(T=_.exports=g),T.UAParser=g):(x=function(){return g}.call(T,E,T,_))===c||(_.exports=x);var k,y=n&&(n.jQuery||n.Zepto);y&&!y.ua&&(k=new g,y.ua=k.getResult(),y.ua.get=function(){return k.getUA()},y.ua.set=function(i){k.setUA(i);var e=k.getResult();for(var s in e)y.ua[s]=e[s]})}("object"==typeof window?window:this)},286:function(i,e,s){"use strict";s.d(e,"a",function(){return c});var t=s(0),n=s.n(t),o=s(1),r=s.n(o),a=s(26),l=s.n(a),u=(s(292),s(293),function(){function e(){var i=0<arguments.length&&void 0!==arguments[0]?arguments[0]:null;n()(this,e),this.$element=null===i?$(".all-wrapper"):i,this.mask='\n            <div class="out-focus-mask">\n                <div class="content">\n                    <div class="tips"></div>\n                    <div class="continue-studying">\n                        <button class="btn btn-primary js-continue-studying">'.concat(Translator.trans("course.task.out_focus_mask.continue_studying"),"</button>\n                    </div>\n                </div>\n            </div>"),this.mask1='\n            <div class="out-focus-mask">\n                <div class="content">\n                    <div class="tips"></div>\n                </div>\n            </div>',this.initEvent()}return r()(e,[{key:"initEvent",value:function(){this._registerChannel()}},{key:"validateMask",value:function(){return 0<this.$element.find(".out-focus-mask").length}},{key:"initLearStopTips",value:function(){this.validateMask()||(this.$element.append(this.mask),this.$element.find(".out-focus-mask .content .tips").html(Translator.trans("course.task.out_focus_mask.stop.tips")),this.popAfter())}},{key:"initAntiBrushTips",value:function(){this.validateMask()&&this.destroyMask(),this.$element.append(this.mask),this.$element.find(".out-focus-mask .content .tips").html(Translator.trans("course.task.out_focus_mask.anti_brush.tips")),this.popAfter()}},{key:"initBanTips",value:function(){this.validateMask()&&this.destroyMask(),this.$element.append(this.mask1),this.$element.find(".out-focus-mask .content .tips").html(Translator.trans("course.task.out_focus_mask.anti_brush.tips")),this.popAfter()}},{key:"continueStudying",value:function(){this.destroyMask(),this._publishResponse("play")}},{key:"destroyMask",value:function(){this.$element.find(".out-focus-mask").remove()}},{key:"popAfter",value:function(){this._publishResponse("pause")}},{key:"_registerChannel",value:function(){return l.a.instanceId("task"),l.a.fedx.addFilter([{channel:"task-events",topic:"monitoringEvent",direction:"out"}]),this}},{key:"_publishResponse",value:function(i){l.a.publish({channel:"task-events",topic:"monitoringEvent",data:i})}}]),e}()),d=s(14),c=function(){function e(i){n()(this,e),this.maskElement=i.maskElement||null,this.OutFocusMask=new u(this.maskElement),this.activityTimer=null,this.ACTIVITY_TIME=1200,this.eventMaskElement=null,this.eventMaskTimer=null,this.EVENT_MASK_TIME=30,this.videoPlayRule=i.videoPlayRule,this.taskType=i.taskType,this.taskPipe=i.taskPipe,this.initEvent()}return r()(e,[{key:"initEvent",value:function(){var i=this;$("body").off("click",".js-continue-studying"),$("body").on("click",".js-continue-studying",function(){i.OutFocusMask.continueStudying(),i.taskPipe._flush({reActive:1}),i.taskPipe.absorbedChange(0)}),Object(d.f)()||"auto_pause"===this.videoPlayRule&&"video"===this.taskType&&(this.initMaskElement(),this.initVisibilitychange(),this.initActivity())}},{key:"initMaskElement",value:function(){$("body").append('\n      <div class="monitor-event-mask" style="position: fixed; left: 0; right: 0; top: 0; bottom: 0; opacity: 0; display: none;"></div>\n    '),this.eventMaskElement=$(".monitor-event-mask"),this.maskElementShow()}},{key:"ineffectiveEvent",value:function(){this.OutFocusMask.initLearStopTips(),this.taskPipe.absorbedChange(1),this.taskPipe._flush()}},{key:"triggerEvent",value:function(i){this.taskPipe.absorbedChange(1),"reject_current"!==i?"kick_previous"!==i||this.OutFocusMask.initAntiBrushTips():this.OutFocusMask.initBanTips()}},{key:"initVisibilitychange",value:function(){var i=this;document.addEventListener("visibilitychange",function(){"hidden"===document.visibilityState&&i.ineffectiveEvent()})}},{key:"initActivity",value:function(){this.afterActivity(),document.onmousedown=this.afterActivity.bind(this),document.onscroll=this.afterActivity.bind(this),document.onkeypress=this.afterActivity.bind(this),document.onmousemove=this.afterActivity.bind(this)}},{key:"afterActivity",value:function(){var i=this;this.maskElementHide(),clearTimeout(this.activityTimer),this.activityTimer=null,this.activityTimer=setTimeout(function(){i.ineffectiveEvent()},1e3*this.ACTIVITY_TIME)}},{key:"maskElementShow",value:function(){var i=this;clearTimeout(this.eventMaskTimer),this.eventMaskTimer=null,this.eventMaskTimer=setTimeout(function(){i.eventMaskElement.show()},1e3*this.EVENT_MASK_TIME)}},{key:"maskElementHide",value:function(){this.eventMaskElement.hide(),this.maskElementShow()}}]),e}()},57:function(i,e,s){"use strict";var t=s(10),o=s.n(t),n=s(0),r=s.n(n),a=s(1),l=s.n(a),u=(s(94),function(){function i(){r()(this,i)}return l()(i,null,[{key:"set",value:function(i,e,s){var t=store.get("durations",{});t&&t instanceof Array||(t=new Array);var n=i+"-"+e+":"+s;0<t.length&&-1<t.slice(t.length-1,t.length)[0].indexOf(i+"-"+e)&&t.splice(t.length-1,t.length),20<=t.length&&t.shift(),t.push(n),store.set("durations",t)}},{key:"get",value:function(i,e){var s=store.get("durations",{});if(s)for(var t=0;t<s.length;t++){if(-1<s[t].indexOf(i+"-"+e)){var n=s[t];return o()(n.split(":")[1])}}return 0}},{key:"del",value:function(i,e){var s=store.get("durations");if(s){for(var t=0;t<s.length;t++){-1<s[t].indexOf(i+"-"+e)&&s.splice(t,1)}store.set("durations",s)}}}]),i}());e.a=u},694:function(i,e,s){"use strict";s.r(e);var t=s(12),o=s.n(t),n=s(0),r=s.n(n),a=s(1),l=s.n(a),u=s(283),d=s.n(u),c=s(32),h=s(57),w=s(286),v=s(14);new(function(){function i(){r()(this,i),this.taskId=$("#entry").data("taskId"),this.courseId=$("#entry").data("courseId"),this.taskPipeCounter=0,this.pushing=!1,this.sign="",this.absorbed=0,this.TASK_PIPE_INTERNAL=60,this.intervalId=null,this.lastTimestamp=0,this.init()}return l()(i,[{key:"init",value:function(){var e=this;this.isLiveRoomOpened=!1;var s=0,t=1,i=$("#entry").data("directUrl");i?this.entryRoom(i):s=setInterval(function(){return 10<t?(clearInterval(s),void $("#entry").html(Translator.trans("course_set.live_room.entry_error_hint"))):void $.ajax({url:$("#entry").data("url"),success:function(i){if(i.error)return clearInterval(s),void $("#entry").html(Translator.trans("course_set.live_room.entry_error_with_message",{message:i.error}));i.roomUrl&&(e.entryRoom(i.roomUrl),clearInterval(s)),t++},error:function(){$("#entry").html(Translator.trans("course_set.live_room.entry_error_hint"))}})},3e3),this.triggerLiveEvent()}},{key:"entryRoom",value:function(i){var e=$("#entry").data("provider"),s=$("#entry").data("role"),t=new d.a(navigator.userAgent),n=t.getBrowser(),o=t.getOS();"http:"===document.location.protocol&&"student"===s&&(8===e||9===e)&&"Android"!==o.name&&"Chrome"===n.name&&60<=n.major&&(window.location.href=i),this.isLiveRoomOpened=!0;var r='<iframe name="classroom" src="'+i+'" style="position:absolute; left:0; top:0; height:100%; width:100%; border:0px;" scrolling="no" allowfullscreen="true" allow="microphone; camera"></iframe>';$("body").html(r)}},{key:"triggerLiveEvent",value:function(){Date.parse(new Date).toString().substr(0,10);this._initInterval(),v.a.safari&&!Object(v.f)()&&this.safariVisibilitychange()}},{key:"_clearInterval",value:function(){clearInterval(this.intervalId)}},{key:"_initInterval",value:function(){var i=this;this._flush(),window.onbeforeunload=function(){i._clearInterval(),i._flush(),0<i.sign.length&&localStorage.setItem("flowSign",i.sign)},this._clearInterval(),this.intervalId=setInterval(function(){return i._addPipeCounter()},1e3)}},{key:"_flush",value:function(i){var e,s,t=this,n=0<arguments.length&&void 0!==i?i:{};this.pushing||(""===this.sign?(e={},(s=localStorage.getItem("flowSign"))&&(this.lastSign=s,e.lastSign=s,localStorage.removeItem("flowSign")),c.a.courseTaskEvent.pushEvent({params:{courseId:this.courseId,taskId:this.taskId,eventName:"start"},data:o()({client:"pc"},e)}).then(function(i){if(t.MonitoringEvents=new w.a({videoPlayRule:t.videoPlayRule,taskType:"live",taskPipe:t,maskElement:$("body")}),i.learnControl.allowLearn||"kick_previous"!==i.learnControl.denyReason){if(!i.learnControl.allowLearn&&"reject_current"===i.learnControl.denyReason)return t.MonitoringEvents.triggerEvent("reject_current"),t._clearInterval(),void $("[name=classroom]").attr("src","");t.sign=i.record.flowSign,t.record=i.record,t._doing(n)}else t.MonitoringEvents.triggerEvent("kick_previous")})):this._doing(n))}},{key:"_doing",value:function(i){var e,s,t=this,n=0<arguments.length&&void 0!==i?i:{};0!==this.sign.length&&(s={client:"pc",sign:this.sign,duration:this.taskPipeCounter,status:this.absorbed,lastLearnTime:h.a.get(this.userId,this.fileId)},n.watchTime&&(e={watchData:{duration:n.watchTime}},s=o()(s,e)),n.reActive&&(s.reActive=n.reActive),this.pushing=!0,c.a.courseTaskEvent.pushEvent({params:{courseId:this.courseId,taskId:this.taskId,eventName:"doing"},data:s}).then(function(i){t.pushing=!1,t.record=i.record,t.taskPipeCounter=0,t.lastTimestamp=(new Date).getTime(),i.learnControl.allowLearn||"kick_previous"!==i.learnControl.denyReason?i.learnControl.allowLearn||"reject_current"!==i.learnControl.denyReason||t.MonitoringEvents.triggerEvent("reject_current"):t.MonitoringEvents.triggerEvent("kick_previous")}).catch(function(i){t.pushing=!1,t._clearInterval(),cd.message({type:"danger",message:Translator.trans("task_show.user_login_protect_tip")})}))}},{key:"absorbedChange",value:function(i){this.absorbed=i}},{key:"_addPipeCounter",value:function(){this.taskPipeCounter++,this.taskPipeCounter>=this.TASK_PIPE_INTERNAL&&this._flush()}},{key:"safariVisibilitychange",value:function(){var e=this;document.addEventListener("visibilitychange",function(){var i=document.visibilityState;"hidden"===i?e._clearInterval():"visible"===i&&(e.taskPipeCounter=Math.round(((new Date).getTime()-e.lastTimestamp)/1e3),e.intervalId=setInterval(function(){return e._addPipeCounter()},1e3))})}}]),i}())}});