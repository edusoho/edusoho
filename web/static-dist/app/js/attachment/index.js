!function(u){function e(e){for(var t,n,a=e[0],r=e[1],o=e[2],i=0,l=[];i<a.length;i++)n=a[i],Object.prototype.hasOwnProperty.call(s,n)&&s[n]&&l.push(s[n][0]),s[n]=0;for(t in r)Object.prototype.hasOwnProperty.call(r,t)&&(u[t]=r[t]);for(p&&p(e);l.length;)l.shift()();return c.push.apply(c,o||[]),d()}function d(){for(var e,t=0;t<c.length;t++){for(var n=c[t],a=!0,r=1;r<n.length;r++){var o=n[r];0!==s[o]&&(a=!1)}a&&(c.splice(t--,1),e=i(i.s=n[0]))}return e}var n={},s={95:0},c=[];function i(e){if(n[e])return n[e].exports;var t=n[e]={i:e,l:!1,exports:{}};return u[e].call(t.exports,t,t.exports,i),t.l=!0,t.exports}i.m=u,i.c=n,i.d=function(e,t,n){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(t,e){if(1&e&&(t=i(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(i.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var a in t)i.d(n,a,function(e){return t[e]}.bind(null,a));return n},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p="/static-dist/";var t=window.webpackJsonp=window.webpackJsonp||[],a=t.push.bind(t);t.push=e,t=t.slice();for(var r=0;r<t.length;r++)e(t[r]);var p=a;c.push([578,0]),d()}({15:function(e,t){e.exports=jQuery},578:function(e,t,n){"use strict";n.r(t);var a=n(6),u=n.n(a),r=n(3),d=$("#attachment-modal"),o=d.find("#uploader-container"),i=new UploaderSDK({id:o.attr("id"),sdkBaseUri:app.cloudSdkBaseUri,disableDataUpload:app.cloudDisableLogReport,disableSentry:app.cloudDisableLogReport,initUrl:o.data("initUrl"),finishUrl:o.data("finishUrl"),accept:o.data("accept"),process:{document:{type:"html"}},fileSingleSizeLimit:o.data("fileSingleSizeLimit"),ui:"single",locale:document.documentElement.lang});i.on("error",function(e){Object(r.a)("danger",e.message)}),i.on("file.finish",function(t){var e,n;t.length&&0<t.length&&(e=u()(t.length/60),n=Math.round(t.length%60),$("#minute").val(e),$("#second").val(n),$("#length").val(60*e+n));var a=$('[data-role="metas"]'),r=a.data("currentTarget"),o=$("."+a.data("idsClass")),i=$("."+a.data("listClass"));""!=r&&(o=$("[data-role="+r+"]").find("."+a.data("idsClass")),i=$("[data-role="+r+"]").find("."+a.data("listClass")));var l=$('input[name="module"]').val();$.get("/attachment/file/"+t.no+"/show",{module:l},function(e){i.append(e),o.val(t.no),d.modal("hide"),i.siblings(".js-upload-file").hide()})}),d.one("hide.bs.modal",function(e){i.destroy(),i=null})}});