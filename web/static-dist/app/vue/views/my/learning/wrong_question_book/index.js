!function(e){function t(t){for(var r,o,i=t[0],s=t[1],l=t[2],c=0,d=[];c<i.length;c++)o=i[c],Object.prototype.hasOwnProperty.call(a,o)&&a[o]&&d.push(a[o][0]),a[o]=0;for(r in s)Object.prototype.hasOwnProperty.call(s,r)&&(e[r]=s[r]);for(p&&p(t);d.length;)d.shift()();return u.push.apply(u,l||[]),n()}function n(){for(var e,t=0;t<u.length;t++){for(var n=u[t],r=!0,o=1;o<n.length;o++){var s=n[o];0!==a[s]&&(r=!1)}r&&(u.splice(t--,1),e=i(i.s=n[0]))}return e}var r={},o={419:0},a={419:0},u=[];function i(t){if(r[t])return r[t].exports;var n=r[t]={i:t,l:!1,exports:{}};return e[t].call(n.exports,n,n.exports,i),n.l=!0,n.exports}i.e=function(e){var t=[];o[e]?t.push(o[e]):0!==o[e]&&{413:1}[e]&&t.push(o[e]=new Promise((function(t,n){for(var r=e+".css",a=i.p+r,u=document.getElementsByTagName("link"),s=0;s<u.length;s++){var l=(p=u[s]).getAttribute("data-href")||p.getAttribute("href");if("stylesheet"===p.rel&&(l===r||l===a))return t()}var c=document.getElementsByTagName("style");for(s=0;s<c.length;s++){var p;if((l=(p=c[s]).getAttribute("data-href"))===r||l===a)return t()}var d=document.createElement("link");d.rel="stylesheet",d.type="text/css",d.onload=t,d.onerror=function(t){var r=t&&t.target&&t.target.src||a,u=new Error("Loading CSS chunk "+e+" failed.\n("+r+")");u.code="CSS_CHUNK_LOAD_FAILED",u.request=r,delete o[e],d.parentNode.removeChild(d),n(u)},d.href=a,document.getElementsByTagName("head")[0].appendChild(d)})).then((function(){o[e]=0})));var n=a[e];if(0!==n)if(n)t.push(n[2]);else{var r=new Promise((function(t,r){n=a[e]=[t,r]}));t.push(n[2]=r);var u,s=document.createElement("script");s.charset="utf-8",s.timeout=120,i.nc&&s.setAttribute("nonce",i.nc),s.src=function(e){return i.p+""+({1:"vendors~app/vue/dist/Assistant~app/vue/dist/ClassroomManageWrongQuestion~app/vue/dist/CourseManage~a~1cb25e45",2:"app/vue/dist/Assistant~app/vue/dist/ClassroomManageWrongQuestion~app/vue/dist/CourseManage~app/vue/d~11025591",413:"app/vue/dist/WrongQuestionBook"}[e]||e)+".js"}(e);var l=new Error;u=function(t){s.onerror=s.onload=null,clearTimeout(c);var n=a[e];if(0!==n){if(n){var r=t&&("load"===t.type?"missing":t.type),o=t&&t.target&&t.target.src;l.message="Loading chunk "+e+" failed.\n("+r+": "+o+")",l.name="ChunkLoadError",l.type=r,l.request=o,n[1](l)}a[e]=void 0}};var c=setTimeout((function(){u({type:"timeout",target:s})}),12e4);s.onerror=s.onload=u,document.head.appendChild(s)}return Promise.all(t)},i.m=e,i.c=r,i.d=function(e,t,n){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(e,t){if(1&t&&(e=i(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(i.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)i.d(n,r,function(t){return e[t]}.bind(null,r));return n},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p="/static-dist/",i.oe=function(e){throw console.error(e),e};var s=window.webpackJsonp=window.webpackJsonp||[],l=s.push.bind(s);s.push=t,s=s.slice();for(var c=0;c<s.length;c++)t(s[c]);var p=l;u.push([1185,0]),n()}({1185:function(e,t,n){"use strict";n.r(t);var r=n(60),o=n(62),a=n(63),u=[{path:"/",component:function(){return Promise.all([n.e(1),n.e(2),n.e(413)]).then(n.bind(null,1298))},children:[{path:"",name:"CourseWrongQuestion",meta:{current:"course"},component:function(){return Promise.all([n.e(1),n.e(2),n.e(413)]).then(n.bind(null,1299))}},{path:"classroom",name:"ClassroomWrongQuestion",meta:{current:"classroom"},component:function(){return Promise.all([n.e(1),n.e(2),n.e(413)]).then(n.bind(null,1295))}},{path:"question_bank",name:"QuestionBankWrongQuestion",meta:{current:"question-bank"},component:function(){return Promise.all([n.e(1),n.e(2),n.e(413)]).then(n.bind(null,1301))}}]}],i=new o.a({mode:"hash",routes:u});new r.a({el:"#app",components:{AntConfigProvider:a.a},router:i,template:"<ant-config-provider />"})}});