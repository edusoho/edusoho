!function(l){function t(t){for(var e,r,n=t[0],o=t[1],a=t[2],i=0,u=[];i<n.length;i++)r=n[i],Object.prototype.hasOwnProperty.call(p,r)&&p[r]&&u.push(p[r][0]),p[r]=0;for(e in o)Object.prototype.hasOwnProperty.call(o,e)&&(l[e]=o[e]);for(s&&s(t);u.length;)u.shift()();return f.push.apply(f,a||[]),c()}function c(){for(var t,e=0;e<f.length;e++){for(var r=f[e],n=!0,o=1;o<r.length;o++){var a=r[o];0!==p[a]&&(n=!1)}n&&(f.splice(e--,1),t=i(i.s=r[0]))}return t}var r={},p={52:0},f=[];function i(t){if(r[t])return r[t].exports;var e=r[t]={i:t,l:!1,exports:{}};return l[t].call(e.exports,e,e.exports,i),e.l=!0,e.exports}i.m=l,i.c=r,i.d=function(t,e,r){i.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:r})},i.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},i.t=function(e,t){if(1&t&&(e=i(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(i.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)i.d(r,n,function(t){return e[t]}.bind(null,n));return r},i.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return i.d(e,"a",e),e},i.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},i.p="/static-dist/";var e=window.webpackJsonp=window.webpackJsonp||[],n=e.push.bind(e);e.push=t,e=e.slice();for(var o=0;o<e.length;o++)t(e[o]);var s=n;f.push([540,0]),c()}({15:function(t,e){t.exports=jQuery},540:function(t,e,r){"use strict";r.r(e);var n=r(4);$(".js-operator-plumber").length&&$("body").on("click",".js-operator-plumber",function(){$(this).attr("disabled",!0);var e=$(this).data("action");Object(n.a)("warning",Translator.trans("admin_v2.developer.plumber_operate_hint")),$.post($("#plumber-info").data("url"),{action:e,_csrf_token:$("meta[name=csrf-token]").attr("content")},function(t){"stop"!=e?(function e(){setTimeout(function(){$.get($("#plumber-info").data("url"),function(t){console.log(t),t.length?$("#plumber-info").html(t):e()})},1e3)}(),$(this).removeAttr("disabled")):window.location.reload()})})}});