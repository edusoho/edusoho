!function(l){function e(e){for(var t,n,r=e[0],o=e[1],i=e[2],a=0,s=[];a<r.length;a++)n=r[a],Object.prototype.hasOwnProperty.call(c,n)&&c[n]&&s.push(c[n][0]),c[n]=0;for(t in o)Object.prototype.hasOwnProperty.call(o,t)&&(l[t]=o[t]);for(f&&f(e);s.length;)s.shift()();return d.push.apply(d,i||[]),u()}function u(){for(var e,t=0;t<d.length;t++){for(var n=d[t],r=!0,o=1;o<n.length;o++){var i=n[o];0!==c[i]&&(r=!1)}r&&(d.splice(t--,1),e=a(a.s=n[0]))}return e}var n={},c={216:0},d=[];function a(e){if(n[e])return n[e].exports;var t=n[e]={i:e,l:!1,exports:{}};return l[e].call(t.exports,t,t.exports,a),t.l=!0,t.exports}a.m=l,a.c=n,a.d=function(e,t,n){a.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},a.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},a.t=function(t,e){if(1&e&&(t=a(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(a.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)a.d(n,r,function(e){return t[e]}.bind(null,r));return n},a.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return a.d(t,"a",t),t},a.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},a.p="/static-dist/";var t=window.webpackJsonp=window.webpackJsonp||[],r=t.push.bind(t);t.push=e,t=t.slice();for(var o=0;o<t.length;o++)e(t[o]);var f=r;d.push([686,0]),u()}({15:function(e,t){e.exports=jQuery},686:function(e,t,n){"use strict";n.r(t);var r=n(0),o=n.n(r),i=n(1),a=n.n(i),s=n(4);new(function(){function e(){o()(this,e),this.$form=$("#module-form"),this.validate=this.initValidate(),this.init()}return a()(e,[{key:"init",value:function(){var t=this;$(".js-submit-btn").on("click",function(e){return t.submit(e)}),$(".js-delete-module").on("click",function(e){return t.deleteModule(e)})}},{key:"submit",value:function(){this.validate.form()&&$.post(this.$form.attr("action"),this.$form.serialize(),function(e){window.location.reload()}).error(function(e){Object(s.a)("danger",e.error.message)})}},{key:"deleteModule",value:function(e){var t=this,n=$(e.currentTarget);$.get(n.data("checkUrl"),function(e){1!=e.moduleCount?0<e.assessmentCount?cd.confirm({title:Translator.trans("item_bank_exercise.assessment_module.module_delete.title"),content:Translator.trans("item_bank_exercise.assessment_module.module_delete.has_assessment_hint"),okText:Translator.trans("site.confirm"),cancelText:Translator.trans("site.close")}).on("ok",function(){t.submitDeleteModule(n)}):cd.confirm({title:Translator.trans("item_bank_exercise.assessment_module.module_delete.title"),content:Translator.trans("item_bank_exercise.assessment_module.module_delete"),okText:Translator.trans("site.confirm"),cancelText:Translator.trans("site.close")}).on("ok",function(){t.submitDeleteModule(n)}):Object(s.a)("danger",Translator.trans("item_bank_exercise.assessment_module.module_delete.least_module_count_hint"))})}},{key:"submitDeleteModule",value:function(e){$.post(e.data("url"),function(e){window.location.reload()})}},{key:"initValidate",value:function(){return this.$form.validate({rules:{title:{required:!0,maxlength:6,chinese_alphanumeric:!0}}})}}]),e}())}});