!function(i){function t(t){for(var e,n,r=t[0],o=t[1],c=t[2],a=0,u=[];a<r.length;a++)n=r[a],Object.prototype.hasOwnProperty.call(s,n)&&s[n]&&u.push(s[n][0]),s[n]=0;for(e in o)Object.prototype.hasOwnProperty.call(o,e)&&(i[e]=o[e]);for(p&&p(t);u.length;)u.shift()();return f.push.apply(f,c||[]),l()}function l(){for(var t,e=0;e<f.length;e++){for(var n=f[e],r=!0,o=1;o<n.length;o++){var c=n[o];0!==s[c]&&(r=!1)}r&&(f.splice(e--,1),t=a(a.s=n[0]))}return t}var n={},s={250:0},f=[];function a(t){if(n[t])return n[t].exports;var e=n[t]={i:t,l:!1,exports:{}};return i[t].call(e.exports,e,e.exports,a),e.l=!0,e.exports}a.m=i,a.c=n,a.d=function(t,e,n){a.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},a.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},a.t=function(e,t){if(1&t&&(e=a(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(a.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)a.d(n,r,function(t){return e[t]}.bind(null,r));return n},a.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return a.d(e,"a",e),e},a.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},a.p="/static-dist/";var e=window.webpackJsonp=window.webpackJsonp||[],r=e.push.bind(e);e.push=t,e=e.slice();for(var o=0;o<e.length;o++)t(e[o]);var p=r;f.push([724,0]),l()}({15:function(t,e){t.exports=jQuery},724:function(t,e,n){"use strict";n.r(e);var r=n(3);$("#orders-table").on("click",".js-cancel-refund",function(){var t=$(this);cd.confirm({title:Translator.trans("user.account.refund_cancel_title"),content:Translator.trans("user.account.refund_cancel_hint"),okText:Translator.trans("site.confirm"),cancelText:Translator.trans("site.close")}).on("ok",function(){$.post(t.data("url"),function(){Object(r.a)("success",Translator.trans("user.account.refund_cancel_success_hint")),window.location.reload()})})})}});