!function(t){var o={};function n(r){if(o[r])return o[r].exports;var e=o[r]={i:r,l:!1,exports:{}};return t[r].call(e.exports,e,e.exports,n),e.l=!0,e.exports}n.m=t,n.c=o,n.d=function(r,e,t){n.o(r,e)||Object.defineProperty(r,e,{enumerable:!0,get:t})},n.r=function(r){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(r,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(r,"__esModule",{value:!0})},n.t=function(e,r){if(1&r&&(e=n(e)),8&r)return e;if(4&r&&"object"==typeof e&&e&&e.__esModule)return e;var t=Object.create(null);if(n.r(t),Object.defineProperty(t,"default",{enumerable:!0,value:e}),2&r&&"string"!=typeof e)for(var o in e)n.d(t,o,function(r){return e[r]}.bind(null,o));return t},n.n=function(r){var e=r&&r.__esModule?function(){return r.default}:function(){return r};return n.d(e,"a",e),e},n.o=function(r,e){return Object.prototype.hasOwnProperty.call(r,e)},n.p="/static-dist/",n(n.s=697)}({697:function(r,e){var n=0,t=1;function o(){var r;3<t?(clearInterval(n),r=Translator.trans("classroom.live_room.entry_error_hint"),$("#classroom-url").html(r)):($.ajax({url:$("#classroom-url").data("url"),success:function(r){var e,t,o;r.error?(clearInterval(n),e=r.error+Translator.trans("，")+Translator.trans("classroom.live_room.retry_or_close"),$("#classroom-url").html(e)):r.url&&(t=r.url,r.param&&(t=t+"?param="+r.param),o='<iframe name="classroom" src="'+t+'" style="position:absolute; left:0; top:0; height:100%; width:100%; border:0px;" scrolling="no" allowfullscreen="true"></iframe>',$("body").html(o),clearInterval(n))},error:function(){}}),t++)}o(),n=setInterval(o,3e3)}});