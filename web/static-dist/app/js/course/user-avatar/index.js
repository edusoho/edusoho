!function(c){function e(e){for(var t,r,n=e[0],o=e[1],u=e[2],i=0,a=[];i<n.length;i++)r=n[i],Object.prototype.hasOwnProperty.call(f,r)&&f[r]&&a.push(f[r][0]),f[r]=0;for(t in o)Object.prototype.hasOwnProperty.call(o,t)&&(c[t]=o[t]);for(s&&s(e);a.length;)a.shift()();return p.push.apply(p,u||[]),l()}function l(){for(var e,t=0;t<p.length;t++){for(var r=p[t],n=!0,o=1;o<r.length;o++){var u=r[o];0!==f[u]&&(n=!1)}n&&(p.splice(t--,1),e=i(i.s=r[0]))}return e}var r={},f={176:0},p=[];function i(e){if(r[e])return r[e].exports;var t=r[e]={i:e,l:!1,exports:{}};return c[e].call(t.exports,t,t.exports,i),t.l=!0,t.exports}i.m=c,i.c=r,i.d=function(e,t,r){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(t,e){if(1&e&&(t=i(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var r=Object.create(null);if(i.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var n in t)i.d(r,n,function(e){return t[e]}.bind(null,n));return r},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p="/static-dist/";var t=window.webpackJsonp=window.webpackJsonp||[],n=t.push.bind(t);t.push=e,t=t.slice();for(var o=0;o<t.length;o++)e(t[o]);var s=n;p.push([656,0]),l()}({656:function(e,t,r){"use strict";r.r(t);r(98);$(".js-course-avatar").on("click",function(){store.set("COURSE-GUEST-PAGE-URL",window.location.href),this.href=$(this).data("url")})}});