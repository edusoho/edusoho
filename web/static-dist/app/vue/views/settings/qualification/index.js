!function(e){function t(t){for(var n,o,i=t[0],s=t[1],p=t[2],l=0,d=[];l<i.length;l++)o=i[l],Object.prototype.hasOwnProperty.call(a,o)&&a[o]&&d.push(a[o][0]),a[o]=0;for(n in s)Object.prototype.hasOwnProperty.call(s,n)&&(e[n]=s[n]);for(c&&c(t);d.length;)d.shift()();return u.push.apply(u,p||[]),r()}function r(){for(var e,t=0;t<u.length;t++){for(var r=u[t],n=!0,o=1;o<r.length;o++){var s=r[o];0!==a[s]&&(n=!1)}n&&(u.splice(t--,1),e=i(i.s=r[0]))}return e}var n={},o={422:0},a={422:0},u=[];function i(t){if(n[t])return n[t].exports;var r=n[t]={i:t,l:!1,exports:{}};return e[t].call(r.exports,r,r.exports,i),r.l=!0,r.exports}i.e=function(e){var t=[];o[e]?t.push(o[e]):0!==o[e]&&{5:1,410:1}[e]&&t.push(o[e]=new Promise((function(t,r){for(var n=e+".css",a=i.p+n,u=document.getElementsByTagName("link"),s=0;s<u.length;s++){var p=(c=u[s]).getAttribute("data-href")||c.getAttribute("href");if("stylesheet"===c.rel&&(p===n||p===a))return t()}var l=document.getElementsByTagName("style");for(s=0;s<l.length;s++){var c;if((p=(c=l[s]).getAttribute("data-href"))===n||p===a)return t()}var d=document.createElement("link");d.rel="stylesheet",d.type="text/css",d.onload=t,d.onerror=function(t){var n=t&&t.target&&t.target.src||a,u=new Error("Loading CSS chunk "+e+" failed.\n("+n+")");u.code="CSS_CHUNK_LOAD_FAILED",u.request=n,delete o[e],d.parentNode.removeChild(d),r(u)},d.href=a,document.getElementsByTagName("head")[0].appendChild(d)})).then((function(){o[e]=0})));var r=a[e];if(0!==r)if(r)t.push(r[2]);else{var n=new Promise((function(t,n){r=a[e]=[t,n]}));t.push(r[2]=n);var u,s=document.createElement("script");s.charset="utf-8",s.timeout=120,i.nc&&s.setAttribute("nonce",i.nc),s.src=function(e){return i.p+""+({1:"vendors~app/vue/dist/Assistant~app/vue/dist/ClassroomManageWrongQuestion~app/vue/dist/CourseManage~a~1cb25e45",2:"app/vue/dist/Assistant~app/vue/dist/ClassroomManageWrongQuestion~app/vue/dist/CourseManage~app/vue/d~11025591",5:"vendors~app/vue/dist/CreateCourse~app/vue/dist/Settings~app/vue/dist/Teacher",410:"app/vue/dist/Settings"}[e]||e)+".js"}(e);var p=new Error;u=function(t){s.onerror=s.onload=null,clearTimeout(l);var r=a[e];if(0!==r){if(r){var n=t&&("load"===t.type?"missing":t.type),o=t&&t.target&&t.target.src;p.message="Loading chunk "+e+" failed.\n("+n+": "+o+")",p.name="ChunkLoadError",p.type=n,p.request=o,r[1](p)}a[e]=void 0}};var l=setTimeout((function(){u({type:"timeout",target:s})}),12e4);s.onerror=s.onload=u,document.head.appendChild(s)}return Promise.all(t)},i.m=e,i.c=n,i.d=function(e,t,r){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(e,t){if(1&t&&(e=i(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(i.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)i.d(r,n,function(t){return e[t]}.bind(null,n));return r},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p="/static-dist/",i.oe=function(e){throw console.error(e),e};var s=window.webpackJsonp=window.webpackJsonp||[],p=s.push.bind(s);s.push=t,s=s.slice();for(var l=0;l<s.length;l++)t(s[l]);var c=p;u.push([1188,0]),r()}({1188:function(e,t,r){"use strict";r.r(t);var n=r(60),o=r(62),a=r(63),u=[{path:"/",name:"SettingsQualification",component:function(){return Promise.all([r.e(1),r.e(5),r.e(2),r.e(410)]).then(r.bind(null,1294))}}],i=new o.a({mode:"hash",routes:u});new n.a({el:"#app",components:{AntConfigProvider:a.a},router:i,template:"<ant-config-provider />"})}});