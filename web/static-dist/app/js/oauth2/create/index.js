!function(e){function t(t){for(var a,i,o=t[0],d=t[1],c=t[2],u=0,h=[];u<o.length;u++)i=o[u],Object.prototype.hasOwnProperty.call(n,i)&&n[i]&&h.push(n[i][0]),n[i]=0;for(a in d)Object.prototype.hasOwnProperty.call(d,a)&&(e[a]=d[a]);for(l&&l(t);h.length;)h.shift()();return s.push.apply(s,c||[]),r()}function r(){for(var e,t=0;t<s.length;t++){for(var r=s[t],a=!0,o=1;o<r.length;o++){var d=r[o];0!==n[d]&&(a=!1)}a&&(s.splice(t--,1),e=i(i.s=r[0]))}return e}var a={},n={280:0},s=[];function i(t){if(a[t])return a[t].exports;var r=a[t]={i:t,l:!1,exports:{}};return e[t].call(r.exports,r,r.exports,i),r.l=!0,r.exports}i.m=e,i.c=a,i.d=function(e,t,r){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(e,t){if(1&t&&(e=i(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(i.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var a in e)i.d(r,a,function(t){return e[t]}.bind(null,a));return r},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p="/static-dist/";var o=window.webpackJsonp=window.webpackJsonp||[],d=o.push.bind(o);o.push=t,o=o.slice();for(var c=0;c<o.length;c++)t(o[c]);var l=d;s.push([1224,0]),r()}({1224:function(e,t,r){"use strict";r.r(t);var a=r(2),n=r.n(a),s=r(3),i=r.n(s),o=r(198),d=r(8),c=$(".js-time-left"),l=$(".js-sms-send"),u=$(".js-fetch-btn-text"),h=function e(){var t=c.text();c.html(t-1),t-1>0?(l.attr("disabled",!0),setTimeout(e,1e3)):(c.html(""),u.html(Translator.trans("oauth.send.validate_message")),l.removeAttr("disabled"))},g=r(44),m=r(86);new(function(){function e(){n()(this,e),this.$form=$("#third-party-create-account-form"),this.$btn=$(".js-submit-btn"),this.validator=null,this.dragCaptchaToken="",this.smsToken=null,this.$sendBtn=$(".js-sms-send"),this.drag=!!$("#drag-btn").length&&new m.a($("#drag-btn"),$(".js-jigsaw"),{limitType:"bind_register"}),this.init()}return i()(e,[{key:"init",value:function(){this.setValidateRule(),this.initValidator(),this.smsSend(),this.submitForm(),this.removeSmsErrorTip(),this.dragEvent(),this.initRegisterVisitIdField()}},{key:"setValidateRule",value:function(){$.validator.addMethod("spaceNoSupport",(function(e,t){return e.indexOf(" ")<0}),$.validator.format(Translator.trans("validate.have_spaces")))}},{key:"dragEvent",value:function(){var e=this;this.drag&&this.drag.on("success",(function(t){e.$sendBtn.attr("disabled",!1),e.dragCaptchaToken=t.token})),$(".js-drag-jigsaw").hasClass("hidden")||this.addDragCaptchaRules()}},{key:"initValidator",value:function(){$.validator.addMethod("sms_code_required",(function(e,t){return!(!$("#originalMobileAccount").val()||!e)}),$.validator.format(Translator.trans("auth.mobile_captcha_required_error_hint"))),$.validator.addMethod("account_password",(function(e,t){return!(!$("#originalEmailAccount").val()||!e)}),$.validator.format(Translator.trans("auth.login.password_required_error_hint"))),this.rules={username:{required:!0,byte_minlength:4,byte_maxlength:18,nickname:!0,chinese_alphanumeric:!0,es_remote:{type:"get"}},invitedCode:{required:!1,reg_inviteCode:!0,es_remote:{type:"get"}},password:{required:!0,minlength:5,maxlength:20,spaceNoSupport:!0},confirmPassword:{required:!0,equalTo:"#password"},sms_code:{required:!0,unsigned_integer:!0,rangelength:[6,6]},agree_policy:{required:!0},originalMobileAccount:{required:!1,phone:!0,es_remote:{type:"get",callback:function(e){e?$(".js-sms-send").removeAttr("disabled"):$(".js-sms-send").attr("disabled","true")}}},originalEmailAccount:{required:!1,email:!0,es_remote:{type:"get"}},originalAccountPassword:{required:!1,account_password:!0},accountSmsCode:{required:!1,sms_code_required:!0,unsigned_integer:!0,rangelength:[6,6]}},this.validator=this.$form.validate({rules:this.rules,messages:{sms_code:{required:Translator.trans("site.captcha_code.required"),rangelength:Translator.trans("validate.sms_code.message")},agree_policy:{required:Translator.trans("validate.valid_policy_input.message")}}})}},{key:"smsSend",value:function(){var e=this,t=this,r=$("#captcha_code");this.$sendBtn.length&&this.$sendBtn.click((function(a){t.smsSended||($.ajaxSetup({global:!1}),t.smsSended=!0),t.$sendBtn.attr("disabled",!0);var n=$(a.currentTarget).data("type"),s={type:n,unique:"register"===n,mobile:"register"===n?$(".js-account").text():$("#originalMobileAccount").val(),dragCaptchaToken:e.dragCaptchaToken,phrase:r.val()};g.a.sms.send({data:s}).then((function(t){var r;$.ajaxSetup({global:!0}),e.smsToken=t.smsToken,r=120,c.html(r),u.html(Translator.trans("site.data.get_sms_code_again_btn")),Object(d.a)("success",Translator.trans("site.data.get_sms_code_success_hint")),h()})).catch((function(e){t.drag&&($.ajaxSetup({global:!0}),t.addDragCaptchaRules(),t.drag.initDragCaptcha(),$(".js-drag-jigsaw").removeClass("hidden"))}))}))}},{key:"submitForm",value:function(){var e=this;this.$btn.click((function(t){var r=$(t.target);if(e.validator.form()){r.button("loading");var a={nickname:$("#username").val(),password:$("#password").val(),mobile:$(".js-account").html(),smsToken:e.smsToken,smsCode:$("#sms-code").val(),captchaToken:e.captchaToken,phrase:$("#captcha_code").val(),dragCaptchaToken:$('[name="dragCaptchaToken"]').val(),invitedCode:$("#invitedCode").length>0?$("#invitedCode").val():"",registerVisitId:$('[name="registerVisitId"]').val(),originalMobileAccount:$('[name="originalMobileAccount"]').val(),accountSmsCode:$('[name="accountSmsCode"]').val(),originalEmailAccount:$('[name="originalEmailAccount"]').val(),originalAccountPassword:$('[name="originalAccountPassword"]').val()},n=Translator.trans("oauth.send.sms_code_error_tip");$.post(r.data("url"),a,(function(e){r.button("reset"),1===e.success?window.location.href=e.url:$(".js-password-error").length||r.prev().addClass("has-error").append('<p id="password-error" class="form-error-message js-password-error">'.concat(n,"</p>"))})).error((function(e){r.button("reset")}))}})),Object(o.a)(this.$form,this.$btn)}},{key:"addDragCaptchaRules",value:function(){$('[name="dragCaptchaToken"]').rules("add",{required:!0,messages:{required:Translator.trans("auth.register.drag_captcha_tips")}})}},{key:"removeSmsErrorTip",value:function(){$("#sms-code").focus((function(){var e=$(".js-password-error");e.length&&e.remove()}))}},{key:"initRegisterVisitIdField",value:function(){$(document).ready((function(){"undefined"!==window._VISITOR_ID&&$('[name="registerVisitId"]').val(window._VISITOR_ID)}))}}]),e}())},198:function(e,t,r){"use strict";r.d(t,"a",(function(){return a}));var a=function(e,t){e.keypress((function(e){13==e.which&&(t.trigger("click"),e.preventDefault())}))}},22:function(e,t){e.exports=jQuery}});