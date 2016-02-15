// ug_vk.js (https://github.com/finalfantasia/ug_vk)
// The MIT License (MIT)
// Copyright (c) 2013, 2014 Abdussalam Abdurrahman (abdusalam.abdurahman@gmail.com)
!function(t){"use strict"
function e(t){return"function"==typeof t}function n(t){return"[object Array]"===Object.prototype.toString.call(t)}function a(t,e){var n
for(n=0;n<t.length;n++)if(t[n]===e)return n
return-1}function i(){var t,e,n,i=[]
for(t=k.getElementsByTagName("input"),e=k.getElementsByTagName("textarea"),n=0;n<t.length;n++)a(["text","search"],t[n].type.toLowerCase())>=0&&i.push(t[n])
for(n=0;n<e.length;n++)i.push(e[n])
return i}function r(t,e,n){"addEventListener"in t?(t.removeEventListener(e,n,!1),t.addEventListener(e,n,!1)):(t.detachEvent("on"+e,n),t.attachEvent("on"+e,n))}function o(t,e,n){"removeEventListener"in t?t.removeEventListener(e,n,!1):t.detachEvent("on"+e,n)}function l(){var t,e,n
if(t=i(),E.all)for(n=0;n<t.length;n++)e=t[n],a(E.blacklist,e.name)<0&&(r(e,"keydown",s),r(e,"keypress",h),c(e,f),C[e.name]="ug")
else for(n=0;n<t.length;n++)e=t[n],a(E.whitelist,e.name)>=0&&(r(e,"keydown",s),r(e,"keypress",h),c(e,f),C[e.name]="ug")}function u(){var t,e,n
for(t=i(),n=0;n<t.length;n++)e=t[n],o(e,"keydown",s),o(e,"keypress",h)}function c(t,e){function n(){o(t,"touchmove",a),o(t,"touchend",i),u=null,c=null,s=null,h=null}function a(t){var e
t.touches.length>1?n():(s=t.touches[0].pageX-u,e=t.touches[0].pageY-c,h&&0>h&&s>0||h>0&&0>s||Math.abs(e)>d?n():(h=s,t.preventDefault()))}function i(){var a=Math.abs(s)
n(),a>f&&e(t)}function l(e){1===e.touches.length&&(u=e.touches[0].pageX,c=e.touches[0].pageY,r(t,"touchmove",a),r(t,"touchend",i))}var u,c,s,h,f=50,d=15
r(t,"touchstart",l)}function s(t){var e=t.target||t.srcElement,n="which"in t?t.which:t.keyCode,a=String.fromCharCode(n).toUpperCase(),i=t.ctrlKey||t.metaKey
i&&a in w&&(w[a](e),"preventDefault"in t?(t.preventDefault(),t.stopPropagation()):(t.returnValue=!1,t.cancelBubble=!0))}function h(e){function n(t){var e
return e=0===t.selectionStart?null:t.value[t.selectionStart-1]}var a,i="target"in e?e.target:e.srcElement,r="which"in e?e.which:e.keyCode,o=String.fromCharCode(r),l=/^[A-Za-z]{1}$/.test(o),u=e.ctrlKey||e.metaKey,c=!1
u||"ug"!==C[i.name]||("keyCode"in e&&!("which"in e)?(a=S.getUgChar(o),a&&(e.keyCode=a.charCodeAt(0),c=!0)):(a=S.getUgChar(o,E.smartHamza,n(i)),a&&(v(i,a),c=!0)),c?"preventDefault"in e?(e.preventDefault(),e.stopPropagation(),i.dispatchEvent(new t.Event("input",{bubbles:!0}))):(e.returnValue=!0,e.cancelBubble=!0):l&&("preventDefault"in e?(e.preventDefault(),e.stopPropagation()):(e.returnValue=!1,e.cancelBubble=!0)))}function f(t){var e
for(C[t.name]="ug"===C[t.name]?"en":"ug",e=0;e<L.length;e++)L[e]({target:t,keyboardMode:C[t.name]})}function d(t){t.dir="ltr"===t.dir?"rtl":"ltr"}function v(t,e){var n,a
"selection"in k&&"createRange"in k.selection?k.selection.createRange().text=e:(n=t.selectionStart,t.value=t.value.slice(0,t.selectionStart)+e+t.value.slice(t.selectionEnd),a=n+e.length,t.setSelectionRange(a,a))}function g(){var e={}
return e.all=!!t.attachAll,e.all?e.blacklist="string"==typeof t.bedit_deny&&t.bedit_deny.length>0?t.bedit_deny.split(":"):[]:e.whitelist="string"==typeof t.bedit_allow&&t.bedit_allow.length>0?t.bedit_allow.split(":"):[],e}function m(t){var e
return t.smartHamza=!("smartHamza"in t&&!t.smartHamza),t.all=!!t.all,t.all?"blacklist"in t?e=n(t.blacklist):(t.blacklist=[],e=!0):e=n(t.whitelist)&&t.whitelist.length>0,e}function p(){S.initialize(),w={},w.K=f,w.T=d,w.Y=d,z=!0}function b(){var i={}
z||(i="object"==typeof t.UG_VK_OPTS?t.UG_VK_OPTS:g(),m(i)&&(E=i,p(),l()),t.UG_VK={addInputEventListeners:function(t){t&&(E={all:"all"in t?!!t.all:E.all,whitelist:n(t.whitelist)?t.whitelist:E.whitelist,blacklist:n(t.blacklist)?t.blacklist:E.blacklist,smartHamza:"smartHamza"in t?!!t.smartHamza:E.smartHamza}),z||p(),u(),l()},addKeyboardModeChangeListener:function(t){z&&e(t)&&a(L,t)<0&&L.push(t)}})}function y(e){function n(){o||(o=!0,e())}function a(){"removeEventListener"in k&&k.removeEventListener("DOMContentLoaded",a,!1),n()}function i(){if(!o){try{k.body.doScroll("up")}catch(t){return void setTimeout(i,50)}n()}}function r(){return"attachEvent"in k||"loading"===k.readyState?void(l||(l=!0,"addEventListener"in k?(k.addEventListener("DOMContentLoaded",a,!1),t.addEventListener("load",a,!1)):(k.attachEvent("onload",a),i()))):void n()}var o=!1,l=!1
r()}var w,E,k=t.document,C={},L=[],z=!1,S=function(){function t(){o={a:"ھ",b:"ب",c:"غ",D:"ژ",d:"د",e:"ې",F:"ف",f:"ا",G:"گ",g:"ە",H:"خ",h:"ى",i:"ڭ",J:"ج",j:"ق",K:"ۆ",k:"ك",l:"ل",m:"م",n:"ن",o:"و",p:"پ",q:"چ",r:"ر",s:"س",t:"ت",u:"ۇ",v:"ۈ",w:"ۋ",x:"ش",y:"ي",z:"ز","/":"ئ",";":"؛","?":"؟",",":"،",_:"—","(":")",")":"(","[":"]","]":"[","{":"»","}":"«","<":"›",">":"‹"},l=[o.f,o.g,o.e,o.h,o.o,o.u,o.K,o.v],c=o["/"],u=[o[";"],o["?"],o[","]]}function e(t){var e=t.charCodeAt(0)
return e>=s&&h>e&&a(u,t)<0}function n(t){return e(t)&&a(l,t)>=0}function i(t,a){var i=t
return n(t)&&(a?(!e(a)||n(a))&&(i=c+t):i=c+t),i}function r(t,e,n){var a
return a=o[t],a&&e&&(a=i(a,n)),a}var o,l,u,c,s=1536,h=1791
return{initialize:t,getUgChar:r}}()
y(b)}(window)
