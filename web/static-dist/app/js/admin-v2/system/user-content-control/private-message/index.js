!function(e){var t={};function n(r){if(t[r])return t[r].exports;var a=t[r]={i:r,l:!1,exports:{}};return e[r].call(a.exports,a,a.exports,n),a.l=!0,a.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var a in e)n.d(r,a,function(t){return e[t]}.bind(null,a));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/static-dist/",n(n.s=750)}({750:function(e,t){var n=$(".js-private-message-setting-save-btn");$("#user_content_control_private_message").validate({rules:{},ajax:!0,submitSuccess:function(e){cd.message({type:"success",message:Translator.trans("site.save_success_hint")}),n.button("reset")}});n.on("click",(function(e){$(e.currentTarget).button("loading"),$("#user_content_control_private_message").submit()})),$('input[name="enable_private_message"]').change((function(e){var t=$(e.currentTarget),n=$(".js-sub-management");"0"===t.val()?n.addClass("hidden"):"1"===t.val()&&n.removeClass("hidden")}))}});