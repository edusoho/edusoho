!function(s){function t(t){for(var e,n,r=t[0],a=t[1],i=t[2],o=0,c=[];o<r.length;o++)n=r[o],Object.prototype.hasOwnProperty.call(d,n)&&d[n]&&c.push(d[n][0]),d[n]=0;for(e in a)Object.prototype.hasOwnProperty.call(a,e)&&(s[e]=a[e]);for(h&&h(t);c.length;)c.shift()();return l.push.apply(l,i||[]),u()}function u(){for(var t,e=0;e<l.length;e++){for(var n=l[e],r=!0,a=1;a<n.length;a++){var i=n[a];0!==d[i]&&(r=!1)}r&&(l.splice(e--,1),t=o(o.s=n[0]))}return t}var n={},d={99:0},l=[];function o(t){if(n[t])return n[t].exports;var e=n[t]={i:t,l:!1,exports:{}};return s[t].call(e.exports,e,e.exports,o),e.l=!0,e.exports}o.m=s,o.c=n,o.d=function(t,e,n){o.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},o.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},o.t=function(e,t){if(1&t&&(e=o(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(o.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)o.d(n,r,function(t){return e[t]}.bind(null,r));return n},o.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return o.d(e,"a",e),e},o.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},o.p="/static-dist/";var e=window.webpackJsonp=window.webpackJsonp||[],r=e.push.bind(e);e.push=t,e=e.slice();for(var a=0;a<e.length;a++)t(e[a]);var h=r;l.push([793,0]),u()}({16:function(t,e){t.exports=jQuery},348:function(t,e,n){"use strict";n.d(e,"a",function(){return u});var r=n(11),c=n.n(r),a=n(0),i=n.n(a),o=n(1),s=n.n(o),u=function(){function e(t){i()(this,e),this.$container=t.$coinContainer,this.cashierForm=t.cashierForm,this.$form=t.$form,this.priceType=this.$container.data("priceType"),this.coinRate=this.$container.data("coinRate"),this.maxCoinInput=this.$container.data("maxAllowCoin")>this.$container.data("coinBalance")?this.$container.data("coinBalance"):this.$container.data("maxAllowCoin"),this.init()}return s()(e,[{key:"init",value:function(){this.initEvent()}},{key:"initEvent",value:function(){var e=this;this.$form.on("change",".js-coin-amount",function(t){return e.changeAmount(t)})}},{key:"changeAmount",value:function(t){var e=$(t.currentTarget),n=e.val();if(c()(n)>c()(this.maxCoinInput)&&(n=this.maxCoinInput),isNaN(c()(n))||c()(n)<=0)return n=0,e.val(""),this.removePasswordValidate(),this.$form.trigger("removePriceItem",["coin-price"]),$(".js-no-payment").length&&($(".js-no-payment").attr("disabled","disabled"),$(".js-no-payment").addClass("cd-btn-default"),$(".js-no-payment").removeClass("cd-btn-primary")),void this.cashierForm.calcPayPrice(n);e.val(c()(n).toFixed(2)),this.addPasswordValidate();var r,a,i=this.$form.data("coin-name"),o=0;"coin"===this.priceType?(o=c()(n).toFixed(2)+" "+i,r=c()(this.$container.data("maxAllowCoin")),a=c()(r-n).toFixed(2)+" "+i,this.$form.trigger("changeCoinPrice",[a])):o="￥"+c()(n/this.coinRate).toFixed(2),this.$form.trigger("addPriceItem",["coin-price",i+Translator.trans("order.create.minus"),o]),$(".js-no-payment").length&&($(".js-no-payment").attr("disabled","disabled"),$(".js-no-payment").addClass("cd-btn-default"),$(".js-no-payment").removeClass("cd-btn-primary")),this.cashierForm.calcPayPrice(n)}},{key:"addPasswordValidate",value:function(){this.$container.find('[name="payPassword"]').rules("add","required es_remote")}},{key:"removePasswordValidate",value:function(){this.$container.find('[name="payPassword"]').rules("remove","required es_remote")}}]),e}()},388:function(t,e){!function(n){"use strict";var s,l;void 0===n.btoa&&(n.btoa=(s="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/".split(""),function(t){var e,n,r,a=n=0,i=t.length,o=i%3,c=(i-=o)/3<<2;for(0<o&&(c+=4),e=new Array(c);a<i;)r=t.charCodeAt(a++)<<16|t.charCodeAt(a++)<<8|t.charCodeAt(a++),e[n++]=s[r>>18]+s[r>>12&63]+s[r>>6&63]+s[63&r];return 1==o?(r=t.charCodeAt(a++),e[n++]=s[r>>2]+s[(3&r)<<4]+"=="):2==o&&(r=t.charCodeAt(a++)<<8|t.charCodeAt(a++),e[n++]=s[r>>10]+s[r>>4&63]+s[(15&r)<<2]+"="),e.join("")})),void 0===n.atob&&(n.atob=(l=[-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,62,-1,-1,-1,63,52,53,54,55,56,57,58,59,60,61,-1,-1,-1,-1,-1,-1,-1,0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,-1,-1,-1,-1,-1,-1,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,-1,-1,-1,-1,-1],function(t){var e,n,r,a,i,o,c,s,u,d=t.length;if(d%4!=0)return"";if(/[^ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789\+\/\=]/.test(t))return"";for(s=d,0<(c="="==t.charAt(d-2)?1:"="==t.charAt(d-1)?2:0)&&(s-=4),s=3*(s>>2)+c,u=new Array(s),i=o=0;i<d&&-1!=(e=l[t.charCodeAt(i++)])&&-1!=(n=l[t.charCodeAt(i++)])&&(u[o++]=String.fromCharCode(e<<2|(48&n)>>4),-1!=(r=l[t.charCodeAt(i++)]))&&(u[o++]=String.fromCharCode((15&n)<<4|(60&r)>>2),-1!=(a=l[t.charCodeAt(i++)]));)u[o++]=String.fromCharCode((3&r)<<6|a);return u.join("")}));var d=2654435769;function r(t,e){var n=t.length,r=n<<2;if(e){var a=t[n-1];if(a<(r-=4)-3||r<a)return null;r=a}for(var i=0;i<n;i++)t[i]=String.fromCharCode(255&t[i],t[i]>>>8&255,t[i]>>>16&255,t[i]>>>24&255);var o=t.join("");return e?o.substring(0,r):o}function a(t,e){var n,r=t.length,a=r>>2;0!=(3&r)&&++a,e?(n=new Array(a+1))[a]=r:n=new Array(a);for(var i=0;i<r;++i)n[i>>2]|=t.charCodeAt(i)<<((3&i)<<3);return n}function h(t){return 4294967295&t}function f(t,e,n,r,a,i){return(n>>>5^e<<2)+(e>>>3^n<<4)^(t^e)+(i[3&r^a]^n)}function i(t){return t.length<4&&(t.length=4),t}function o(t){if(/^[\x00-\x7f]*$/.test(t))return t;for(var e=[],n=t.length,r=0,a=0;r<n;++r,++a){var i=t.charCodeAt(r);if(i<128)e[a]=t.charAt(r);else if(i<2048)e[a]=String.fromCharCode(192|i>>6,128|63&i);else{if(!(i<55296||57343<i)){if(r+1<n){var o=t.charCodeAt(r+1);if(i<56320&&56320<=o&&o<=57343){var c=65536+((1023&i)<<10|1023&o);e[a]=String.fromCharCode(240|c>>18&63,128|c>>12&63,128|c>>6&63,128|63&c),++r;continue}}throw new Error("Malformed string")}e[a]=String.fromCharCode(224|i>>12,128|i>>6&63,128|63&i)}}return e.join("")}function c(t,e){return(null==e||e<0)&&(e=t.length),0===e?"":/^[\x00-\x7f]*$/.test(t)||!/^[\x00-\xff]*$/.test(t)?e===t.length?t:t.substr(0,e):(e<65535?function(t,e){for(var n=new Array(e),r=0,a=0,i=t.length;r<e&&a<i;r++){var o=t.charCodeAt(a++);switch(o>>4){case 0:case 1:case 2:case 3:case 4:case 5:case 6:case 7:n[r]=o;break;case 12:case 13:if(!(a<i))throw new Error("Unfinished UTF-8 octet sequence");n[r]=(31&o)<<6|63&t.charCodeAt(a++);break;case 14:if(!(a+1<i))throw new Error("Unfinished UTF-8 octet sequence");n[r]=(15&o)<<12|(63&t.charCodeAt(a++))<<6|63&t.charCodeAt(a++);break;case 15:if(!(a+2<i))throw new Error("Unfinished UTF-8 octet sequence");var c=((7&o)<<18|(63&t.charCodeAt(a++))<<12|(63&t.charCodeAt(a++))<<6|63&t.charCodeAt(a++))-65536;if(!(0<=c&&c<=1048575))throw new Error("Character outside valid Unicode range: 0x"+c.toString(16));n[r++]=c>>10&1023|55296,n[r]=1023&c|56320;break;default:throw new Error("Bad UTF-8 encoding 0x"+o.toString(16))}}return r<e&&(n.length=r),String.fromCharCode.apply(String,n)}:function(t,e){for(var n=[],r=new Array(32768),a=0,i=0,o=t.length;a<e&&i<o;a++){var c,s=t.charCodeAt(i++);switch(s>>4){case 0:case 1:case 2:case 3:case 4:case 5:case 6:case 7:r[a]=s;break;case 12:case 13:if(!(i<o))throw new Error("Unfinished UTF-8 octet sequence");r[a]=(31&s)<<6|63&t.charCodeAt(i++);break;case 14:if(!(i+1<o))throw new Error("Unfinished UTF-8 octet sequence");r[a]=(15&s)<<12|(63&t.charCodeAt(i++))<<6|63&t.charCodeAt(i++);break;case 15:if(!(i+2<o))throw new Error("Unfinished UTF-8 octet sequence");var u=((7&s)<<18|(63&t.charCodeAt(i++))<<12|(63&t.charCodeAt(i++))<<6|63&t.charCodeAt(i++))-65536;if(!(0<=u&&u<=1048575))throw new Error("Character outside valid Unicode range: 0x"+u.toString(16));r[a++]=u>>10&1023|55296,r[a]=1023&u|56320;break;default:throw new Error("Bad UTF-8 encoding 0x"+s.toString(16))}32766<=a&&(c=a+1,r.length=c,n[n.length]=String.fromCharCode.apply(String,r),e-=c,a=-1)}return 0<a&&(r.length=a,n[n.length]=String.fromCharCode.apply(String,r)),n.join("")})(t,e)}function u(t,e){return null==t||0===t.length?t:(t=o(t),e=o(e),r(function(t,e){for(var n,r,a,i=t.length,o=i-1,c=t[o],s=0,u=0|Math.floor(6+52/i);0<u;--u){for(r=(s=h(s+d))>>>2&3,a=0;a<o;++a)n=t[a+1],c=t[a]=h(t[a]+f(s,n,c,a,r,e));n=t[0],c=t[o]=h(t[o]+f(s,n,c,o,r,e))}return t}(a(t,!0),i(a(e,!1))),!1))}function p(t,e){return null==t||0===t.length?t:(e=o(e),c(r(function(t,e){for(var n,r,a,i=t.length,o=i-1,c=t[0],s=h(Math.floor(6+52/i)*d);0!==s;s=h(s-d)){for(r=s>>>2&3,a=o;0<a;--a)n=t[a-1],c=t[a]=h(t[a]-f(s,c,n,a,r,e));n=t[o],c=t[0]=h(t[0]-f(s,c,n,0,r,e))}return t}(a(t,!1),i(a(e,!1))),!0)))}n.XXTEA={utf8Encode:o,utf8Decode:c,encrypt:u,encryptToBase64:function(t,e){return n.btoa(u(t,e))},decrypt:p,decryptFromBase64:function(t,e){return null==t||0===t.length?t:p(n.atob(t),e)}}}(window)},793:function(t,e,n){"use strict";n.r(e);var r=n(0),a=n.n(r),i=n(1),o=n.n(i),c=n(348),s=n(13),u=n.n(s),d=n(2),l=n.n(d),h=n(8),f=n.n(h),p=n(9),y=n.n(p),v=n(5),m=n.n(v),g=n(21),w=n.n(g),b=n(29),k=n.n(b),C=n(33),P=(n(140),n(4)),S=function(){function e(){a()(this,e),this.$container=$("body"),this.modalID="cashier-confirm-modal",this.tradeSn="";var t='\n      <div id="'.concat(this.modalID,'" class="modal">\n        <div class="modal-dialog cd-modal-dialog">\n          <div class="modal-content">\n            <div class="modal-header">\n              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">\n                <i class="cd-icon cd-icon-close"></i>\n              </button>\n              <h4 class="modal-title">').concat(Translator.trans("cashier.confirm.title"),'</h4>\n            </div>\n            <div class="modal-body">\n              <p>\n              ').concat(Translator.trans("cashier.confirm.desc"),'\n              </p>\n            </div>\n            <div class="modal-footer">\n              <a class="btn cd-btn cd-btn-flat-default cd-btn-lg" data-dismiss="modal">').concat(Translator.trans("cashier.confirm.pick_again"),'</a>\n              <a class="btn cd-btn cd-btn-primary cd-btn-lg js-confirm-btn">').concat(Translator.trans("cashier.confirm.success"),"</a>\n            </div>\n          </div>\n        <div>  \n      </div>\n    ");0===this.$container.find("#"+this.modalID).length&&this.$container.append(t),$("body").on("click",".js-confirm-btn",this.checkIsPaid.bind(this))}return o()(e,[{key:"checkIsPaid",value:function(){var e=this;A.getTrade(this.tradeSn).then(function(t){t.isPaid?location.href=t.paidSuccessUrl:(Object(P.a)("danger",Translator.trans("cashier.confirm.fail_message")),$("#"+e.modalID).modal("hide"))})}},{key:"show",value:function(t){$("#"+this.modalID).modal("show"),this.tradeSn=t}}]),e}();n(388);var A=function(){function n(){a()(this,n)}return o()(n,[{key:"setOptions",value:function(t){this.options=t}},{key:"getOptions",value:function(){return this.options}},{key:"showConfirmModal",value:function(t){this.confirmModal||(this.confirmModal=new S),this.confirmModal.show(t)}},{key:"pay",value:function(t){var e=this.createTrade(t);null!=e&&(e.paidSuccessUrl?location.href=e.paidSuccessUrl:(store.set("trade_"+this.getURLParameter("sn"),e.tradeSn),this.afterTradeCreated(e)))}},{key:"afterTradeCreated",value:function(){}},{key:"customParams",value:function(t){return t}},{key:"checkOrderStatus",value:function(){this.startInterval()&&(window.intervalCheckOrderId=setInterval(this.checkIsPaid.bind(this),2e3))}},{key:"cancelCheckOrder",value:function(){clearInterval(window.intervalCheckOrderId)}},{key:"startInterval",value:function(){return!1}},{key:"checkIsPaid",value:function(){var e=this,t=store.get("trade_"+this.getURLParameter("sn"));n.getTrade(t).then(function(t){t.isPaid&&(store.remove("payment_gateway"),store.remove("trade_"+e.getURLParameter("sn")),location.href=t.paidSuccessUrl)})}},{key:"getURLParameter",value:function(t){return decodeURIComponent((new RegExp("[?|&]"+t+"=([^&;]+?)(&|#|;|$)").exec(location.search)||[null,""])[1].replace(/\+/g,"%20"))||null}},{key:"filterParams",value:function(t){var e={gateway:t.gateway,type:t.type,orderSn:t.orderSn,coinAmount:t.coinAmount,amount:t.amount,openid:t.openid,payPassword:window.XXTEA.encryptToBase64(t.payPassword,"EduSoho")},e=this.customParams(e);return k()(e).forEach(function(t){return!e[t]&&void 0!==e[t]&&delete e[t]}),e}},{key:"createTrade",value:function(t){var e=this.filterParams(t),n=null;return C.a.trade.create({data:e,async:!1,promise:!1}).done(function(t){n=t}).error(function(t){var e=JSON.parse(t.responseText);2==e.error.code?Object(P.a)("danger",e.error.message):Object(P.a)("danger",Translator.trans("cashier.pay.error_message"))}),n}}],[{key:"getTrade",value:function(t,e){var n=1<arguments.length&&void 0!==e?e:"",r={};return null==t||""==t?new w.a(function(t,e){t({isPaid:!1})}):(t&&(r.tradeSn=t),n&&(r.orderSn=n),C.a.trade.get({params:r}))}}]),n}();function x(r){var a=function(){if("undefined"==typeof Reflect||!l.a)return!1;if(l.a.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(l()(Date,[],function(){})),!0}catch(t){return!1}}();return function(){var t,e,n=m()(r);return e=a?(t=m()(this).constructor,l()(n,arguments,t)):n.apply(this,arguments),y()(this,e)}}var j=function(t){f()(r,t);var n=x(r);function r(){var t;a()(this,r),(t=n.call(this)).$container=$("body"),t.modalID="wechat-qrcode-modal";var e='\n      <div id="'.concat(t.modalID,'" class="modal">\n        <div class="modal-dialog cd-modal-dialog cd-modal-dialog-sm">\n          <div class="modal-content">\n          \n            <div class="modal-header">\n              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">\n                <i class="cd-icon cd-icon-close"></i>\n              </button>\n              <h4 class="modal-title">').concat(Translator.trans("cashier.wechat_pay"),'</h4>\n            </div>\n            \n            <div class="modal-body text-center">\n              <div style="height: 270px; width: 270px; margin: 0 auto;">\n                <img class="cd-mb16 js-qrcode-img" src="">\n              </div>\n              <div class="cd-mb16">\n                ').concat(Translator.trans("cashier.wechat_pay.scan_qcode_pay_tips"),'\n              </div>\n              <div class="cd-text-danger cd-mb32 js-pay-amount" style="font-size:16px;"></div>\n            </div>\n            \n          </div>\n        </div>\n      </div>\n    ');return 0===t.$container.find("#"+t.modalID).length&&t.$container.append(e),t.$container.find("#"+t.modalID).on("hidden.bs.modal",function(){clearInterval(window.intervalWechatId)}),t}return o()(r,[{key:"afterTradeCreated",value:function(t){this.checkOrderStatus();var e=this.$container.find("#"+this.modalID);e.find(".js-qrcode-img").attr("src",t.qrcodeUrl),e.find(".js-pay-amount").text("￥"+t.cash_amount),e.modal("show")}},{key:"startInterval",value:function(){return!0}}]),r}(A);function T(r){var a=function(){if("undefined"==typeof Reflect||!l.a)return!1;if(l.a.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(l()(Date,[],function(){})),!0}catch(t){return!1}}();return function(){var t,e,n=m()(r);return e=a?(t=m()(this).constructor,l()(n,arguments,t)):n.apply(this,arguments),y()(this,e)}}var _=function(t){f()(n,t);var e=T(n);function n(){return a()(this,n),e.apply(this,arguments)}return o()(n,[{key:"afterTradeCreated",value:function(t){this.checkOrderStatus(),this.getOptions().showConfirmModal?(window.open(t.payUrl,"_blank"),this.showConfirmModal(t.tradeSn)):location.href=t.payUrl}}]),n}(A);function I(r){var a=function(){if("undefined"==typeof Reflect||!l.a)return!1;if(l.a.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(l()(Date,[],function(){})),!0}catch(t){return!1}}();return function(){var t,e,n=m()(r);return e=a?(t=m()(this).constructor,l()(n,arguments,t)):n.apply(this,arguments),y()(this,e)}}var O=function(t){f()(n,t);var e=I(n);function n(){return a()(this,n),e.apply(this,arguments)}return o()(n,[{key:"afterTradeCreated",value:function(t){location.href=t.payUrl}},{key:"customParams",value:function(t){return this.isQQBuildInBrowser()||(t.app_pay="Y",t.wap_pay=!0),t}},{key:"isQQBuildInBrowser",value:function(){return!!navigator.userAgent.match(/QQ\//i)}}]),n}(A);function U(r){var a=function(){if("undefined"==typeof Reflect||!l.a)return!1;if(l.a.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(l()(Date,[],function(){})),!0}catch(t){return!1}}();return function(){var t,e,n=m()(r);return e=a?(t=m()(this).constructor,l()(n,arguments,t)):n.apply(this,arguments),y()(this,e)}}var D=function(t){f()(n,t);var e=U(n);function n(){return a()(this,n),e.apply(this,arguments)}return n}(O);function E(r){var a=function(){if("undefined"==typeof Reflect||!l.a)return!1;if(l.a.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(l()(Date,[],function(){})),!0}catch(t){return!1}}();return function(){var t,e,n=m()(r);return e=a?(t=m()(this).constructor,l()(n,arguments,t)):n.apply(this,arguments),y()(this,e)}}var F=function(t){f()(n,t);var e=E(n);function n(){return a()(this,n),e.apply(this,arguments)}return n}(_);function M(r){var a=function(){if("undefined"==typeof Reflect||!l.a)return!1;if(l.a.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(l()(Date,[],function(){})),!0}catch(t){return!1}}();return function(){var t,e,n=m()(r);return e=a?(t=m()(this).constructor,l()(n,arguments,t)):n.apply(this,arguments),y()(this,e)}}var R=function(t){f()(n,t);var e=M(n);function n(){return a()(this,n),e.apply(this,arguments)}return o()(n,[{key:"pay",value:function(t){location.href="/pay/center/wxpay?"+$.param(t)}}]),n}(A);function W(r){var a=function(){if("undefined"==typeof Reflect||!l.a)return!1;if(l.a.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(l()(Date,[],function(){})),!0}catch(t){return!1}}();return function(){var t,e,n=m()(r);return e=a?(t=m()(this).constructor,l()(n,arguments,t)):n.apply(this,arguments),y()(this,e)}}var L=function(t){f()(n,t);var e=W(n);function n(){return a()(this,n),e.apply(this,arguments)}return o()(n,[{key:"afterTradeCreated",value:function(t){this.checkOrderStatus(),location.href=t.mwebUrl}},{key:"startInterval",value:function(){return!0}}]),n}(A);function q(r){var a=function(){if("undefined"==typeof Reflect||!l.a)return!1;if(l.a.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(l()(Date,[],function(){})),!0}catch(t){return!1}}();return function(){var t,e,n=m()(r);return e=a?(t=m()(this).constructor,l()(n,arguments,t)):n.apply(this,arguments),y()(this,e)}}var B=function(t){f()(n,t);var e=q(n);function n(){return a()(this,n),e.apply(this,arguments)}return o()(n,[{key:"afterTradeCreated",value:function(t){location.href=t.payUrl}}]),n}(A),Q=function(){function t(){a()(this,t)}return o()(t,[{key:"pay",value:function(t,e){var n=1<arguments.length&&void 0!==e?e:{},r=this.getGateway(t.payment,t.isMobile,t.isWechat);t.gateway=r;var a=this.initPaySdk(r);return a.options=u()({showConfirmModal:1},n),a.pay(t),a}},{key:"checkOrderStatus",value:function(){var t=this.initPaySdk();null!=t&&t.checkOrderStatus()}},{key:"cancelCheckOrder",value:function(){var t=this.initPaySdk();null!=t&&t.cancelCheckOrder()}},{key:"initPaySdk",value:function(t){var e=null;switch(t=void 0===t?store.get("payment_gateway"):t){case"WechatPay_Native":e=this.wpn?this.wpn:this.wpn=new j;break;case"WechatPay_MWeb":e=this.wpm?this.wpm:this.wpm=new L;break;case"WechatPay_Js":e=this.wpj?this.wpj:this.wpj=new R;break;case"Alipay_LegacyExpress":e=this.ale?this.ale:this.ale=new _;break;case"Alipay_LegacyWap":e=this.alw?this.alw:this.alw=new O;break;case"Lianlian_Wap":e=this.llwp?this.llwp:this.llwp=new D;break;case"Lianlian_Web":e=this.llwb?this.llwb:this.llwb=new F;break;case"Coin":e=this.coin?this.coin:this.coin=new B}return e}},{key:"getGateway",value:function(t,e,n){var r="";switch(t){case"wechat":r=n?"WechatPay_Js":e?"WechatPay_MWeb":"WechatPay_Native";break;case"alipay":r=e?"Alipay_LegacyWap":"Alipay_LegacyExpress";break;case"lianlianpay":r=e?"Lianlian_Wap":"Lianlian_Web";break;case"coin":r="Coin"}return store.set("payment_gateway",r),r}}]),t}();new(function(){function e(t){a()(this,e),this.$form=$(t.element),this.$priceList=this.$form.find("#order-center-price-list"),this.validator=this.$form.validate(),this.initEvent(),this.initCoin(),this.paySdk=new Q,this.paySdk.checkOrderStatus()}return o()(e,[{key:"initCoin",value:function(){var t=$("#coin-use-section");0<t.length&&(this.coin=new c.a({$coinContainer:t,cashierForm:this,$form:this.$form}))}},{key:"initEvent",value:function(){var a=this,t=this.$form;t.on("click",".js-pay-type",function(t){return a.switchPayType(t)}),t.on("click",".js-pay-btn",function(t){return a.payOrder(t)}),t.on("addPriceItem",function(t,e,n,r){return a.addPriceItem(t,e,n,r)}),t.on("removePriceItem",function(t,e){return a.removePriceItem(t,e)}),t.on("changeCoinPrice",function(t,e){return a.changeCoinPrice(t,e)})}},{key:"payOrder",value:function(t){var e,n,r=this.$form;r.valid()&&((e=$(t.currentTarget)).button("loading"),(n=this.formDataToObject(r)).payAmount=r.find(".js-pay-price").text(),this.paySdk.cancelCheckOrder(),this.paySdk.pay(n),e.button("reset"))}},{key:"switchPayType",value:function(t){var e=$(t.currentTarget);e.hasClass("active")||(e.addClass("active").siblings().removeClass("active"),$("input[name='payment']").val(e.attr("id")))}},{key:"calcPayPrice",value:function(t){var e=this;$.post(this.$form.data("priceUrl"),{coinAmount:t}).done(function(t){return e.$form.find(".js-pay-price").text(t.data),$(".js-no-payment").length?void("￥0.00"==t.data&&($(".js-no-payment").removeAttr("disabled"),$(".js-no-payment").removeClass("cd-btn-default"),$(".js-no-payment").addClass("cd-btn-primary"))):null})}},{key:"formDataToObject",value:function(t){var e={},n=t.serializeArray();for(var r in n)e[n[r].name]=n[r].value;return e}},{key:"hasPriceItem",value:function(t,e){return!!$("#".concat(e)).length}},{key:"addPriceItem",value:function(t,e,n,r){var a=$("#".concat(e));this.hasPriceItem(t,e)&&a.remove();var i='\n      <div class="order-center-price" id="'.concat(e,'">\n        <div class="order-center-price__title">').concat(n,'</div>\n        <div class="order-center-price__content">-').concat(r,"</div>\n      </div>\n    ");this.$priceList.append(i)}},{key:"removePriceItem",value:function(t,e){var n=$("#".concat(e));this.hasPriceItem(t,e)&&n.remove()}},{key:"changeCoinPrice",value:function(t,e){this.$form.find(".js-pay-coin").text(e)}}]),e}())({element:"#cashier-form"})}});