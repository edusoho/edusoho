!function(s){function e(e){for(var t,r,n=e[0],o=e[1],i=e[2],u=0,a=[];u<n.length;u++)r=n[u],Object.prototype.hasOwnProperty.call(l,r)&&l[r]&&a.push(l[r][0]),l[r]=0;for(t in o)Object.prototype.hasOwnProperty.call(o,t)&&(s[t]=o[t]);for(p&&p(e);a.length;)a.shift()();return f.push.apply(f,i||[]),c()}function c(){for(var e,t=0;t<f.length;t++){for(var r=f[t],n=!0,o=1;o<r.length;o++){var i=r[o];0!==l[i]&&(n=!1)}n&&(f.splice(t--,1),e=u(u.s=r[0]))}return e}var r={},l={319:0},f=[];function u(e){if(r[e])return r[e].exports;var t=r[e]={i:e,l:!1,exports:{}};return s[e].call(t.exports,t,t.exports,u),t.l=!0,t.exports}u.m=s,u.c=r,u.d=function(e,t,r){u.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},u.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},u.t=function(t,e){if(1&e&&(t=u(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var r=Object.create(null);if(u.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var n in t)u.d(r,n,function(e){return t[e]}.bind(null,n));return r},u.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return u.d(t,"a",t),t},u.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},u.p="/static-dist/";var t=window.webpackJsonp=window.webpackJsonp||[],n=t.push.bind(t);t.push=e,t=t.slice();for(var o=0;o<t.length;o++)e(t[o]);var p=n;f.push([774,0]),c()}({15:function(e,t){e.exports=jQuery},774:function(e,t,r){"use strict";r.r(t);var n=r(0),o=r.n(n),i=r(1),u=r.n(i),a=r(4);new(function(){function t(e){o()(this,t),this.element=$(e.element),this.currentDom=e.currentDom,this.init()}return u()(t,[{key:"init",value:function(){this.initEvent(),this.validate()}},{key:"validate",value:function(){var e=this.currentDom;return this.element.validate({ajax:!0,currentDom:e,rules:{"form[currentUserLoginPassword]":{required:!0,es_remote:!0},"form[newPayPassword]":{required:!0,maxlength:20,minlength:5},"form[confirmPayPassword]":{required:!0,equalTo:"#form_newPayPassword"}},submitError:function(){Object(a.a)("danger","pay.security.password.save_fail_hint")},submitSuccess:function(e){Object(a.a)("success",e.message),setTimeout(function(){window.location.reload()},1e3)}})}},{key:"initEvent",value:function(){var e=this;$(this.currentDom).on("click",function(){e.validate().form()&&e.element.submit()})}}]),t}())({element:"#settings-pay-password-form",currentDom:".js-submit-form"})}});