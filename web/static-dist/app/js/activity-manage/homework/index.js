!function(t){function e(e){for(var i,a,s=e[0],l=e[1],d=e[2],c=0,h=[];c<s.length;c++)a=s[c],Object.prototype.hasOwnProperty.call(o,a)&&o[a]&&h.push(o[a][0]),o[a]=0;for(i in l)Object.prototype.hasOwnProperty.call(l,i)&&(t[i]=l[i]);for(u&&u(e);h.length;)h.shift()();return r.push.apply(r,d||[]),n()}function n(){for(var t,e=0;e<r.length;e++){for(var n=r[e],i=!0,s=1;s<n.length;s++){var l=n[s];0!==o[l]&&(i=!1)}i&&(r.splice(e--,1),t=a(a.s=n[0]))}return t}var i={},o={38:0},r=[];function a(e){if(i[e])return i[e].exports;var n=i[e]={i:e,l:!1,exports:{}};return t[e].call(n.exports,n,n.exports,a),n.l=!0,n.exports}a.m=t,a.c=i,a.d=function(t,e,n){a.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},a.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},a.t=function(t,e){if(1&e&&(t=a(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(a.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var i in t)a.d(n,i,function(e){return t[e]}.bind(null,i));return n},a.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return a.d(e,"a",e),e},a.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},a.p="/static-dist/";var s=window.webpackJsonp=window.webpackJsonp||[],l=s.push.bind(s);s.push=e,s=s.slice();for(var d=0;d<s.length;d++)e(s[d]);var u=l;r.push([1352,0]),n()}({1352:function(t,e,n){"use strict";n.r(e);var i=n(176),o=n(2),r=n.n(o),a=n(3),s=n.n(a),l=n(8),d=n(385),u=function(){function t(e,n){r()(this,t),this.$form=e,this.$modal=n,this.initEvent()}return s()(t,[{key:"initEvent",value:function(){var t=this;this.$form.on("click",'[data-role="item-delete-btn"]',(function(e){return t.deleteQuestion(e)})),this.$form.on("click",'[data-role="replace-item"]',(function(e){return t.replaceQuestion(e)})),this.$form.on("click",'[data-role="preview-btn"]',(function(e){return t.previewQuestion(e)})),this.$form.on("click",'[data-role="batch-delete-btn"]',(function(e){return t.batchDelete(e)})),this.initSortList()}},{key:"initSortList",value:function(){var t,e=this,n=this.$form.find("tbody"),i=n.hasClass("js-homework-table")?"":"<td></td>",o='<tr class="question-placehoder js-placehoder"><td></td><td></td><td></td><td></td><td></td><td></td><td></td>'.concat(i,"</tr>");n.sortable({containerPath:"> tr",containerSelector:"tbody",itemSelector:"tr.is-question",placeholder:o,exclude:".notMoveHandle",onDragStart:function(e,n,i){e.hasClass("have-sub-questions")||$(".js-have-sub").removeClass("is-question");var o=e.offset(),r=n.rootGroup.pointer;t={left:r.left-o.left,top:r.top-o.top},i(e,n)},onDrag:function(e,n){var i=e.height();e.css({left:n.left-t.left,top:n.top-t.top}),$(".js-placehoder").css({height:i})},onDrop:function(t,n,i){if(i(t,n),t.hasClass("have-sub-questions")){var o=t.parents("tbody");o.find("tr.is-question").each((function(){var t=$(this);o.find("[data-parent-id="+t.data("id")+"]").detach().insertAfter(t)}))}else $(".js-have-sub").addClass("is-question");e.refreshSeqs()}})}},{key:"replaceQuestion",value:function(t){var e=this,n=$(t.currentTarget),i=[],o=this.$form.find("tbody:visible");o.find('[name="questionIds[]"]').each((function(){i.push($(this).val())})),this.$modal.data("manager",this).modal(),$.get(n.data("url"),{excludeIds:i.join(","),type:o.data("type")},(function(t){e.$modal.html(t)}))}},{key:"deleteQuestion",value:function(t){t.stopPropagation();var e=$(t.currentTarget),n=e.closest("tr").data("id"),i=e.closest("tbody");i.find('[data-parent-id="'+n+'"]').remove(),e.closest("tr").remove(),Object(d.a)(this.$form),i.trigger("lengthChange"),this.refreshSeqs()}},{key:"batchDelete",value:function(t){if(0==this.$form.find('[data-role="batch-item"]:checked').length){var e=this.$form.find(".js-help-redmine");e?(e.text(Translator.trans("activity.testpaper_manage.question_required_error_hint")).show(),setTimeout((function(){e.slideUp()}),3e3)):Object(l.a)("danger",Translator.trans("activity.testpaper_manage.question_required_error_hint"))}var n=this;this.$form.find('[data-role="batch-item"]:checked').each((function(t,e){var i=$(this).val();"material"==$(this).closest("tr").data("type")&&n.$form.find('[data-parent-id="'+i+'"]').remove(),$(this).closest("tr").remove()})),this.refreshSeqs(),Object(d.a)(this.$form)}},{key:"previewQuestion",value:function(t){t.preventDefault(),window.open($(t.currentTarget).data("url"),"_blank","directories=0,height=580,width=820,scrollbars=1,toolbar=0,status=0,menubar=0,location=0")}},{key:"refreshSeqs",value:function(){var t=1;this.$form.find("tbody tr").each((function(){var e=$(this);e.hasClass("have-sub-questions")||(e.find("td.seq").html(t),t++)})),this.$form.find('[name="questionLength"]').val(t-1>0?t-1:null)}}]),t}(),c=function(){function t(e){r()(this,t),this.$homeworkModal=$("#modal",window.parent.document),this.$questionPickedModal=$("#attachment-modal",window.parent.document),this.$element=e,this.$step2_form=this.$element.find("#step2-form"),this.$step3_form=this.$element.find("#step3-form"),this.validator2=null,this.init()}return s()(t,[{key:"init",value:function(){this.initEvent(),this.setValidateRule(),this.inItStep2form()}},{key:"initEvent",value:function(){var t=this;this.$element.on("click",'[data-role="pick-item"]',(function(e){return t.showPickQuestion(e)})),this.$questionPickedModal.on("shown.bs.modal",(function(){t.$homeworkModal.hide()})),this.$questionPickedModal.on("hidden.bs.modal",(function(){t.$homeworkModal.show(),t.$questionPickedModal.html(""),t.validator2&&t.validator2.form()}))}},{key:"initCkeditor",value:function(t){var e=CKEDITOR.replace("homework-about-field",{toolbar:"Task",fileSingleSizeLimit:app.fileSingleSizeLimit,filebrowserImageUploadUrl:$("#homework-about-field").data("imageUploadUrl")});e.on("change",(function(){$("#homework-about-field").val(e.getData())})),e.on("blur",(function(){t.form()}))}},{key:"showPickQuestion",value:function(t){var e=this;t.preventDefault();var n=$(t.currentTarget),i=[];$("#question-table-tbody").find('[name="questionIds[]"]').each((function(){i.push($(this).val())})),this.$questionPickedModal.modal().data("manager",this),$.get(n.data("url"),{excludeIds:i.join(",")},(function(t){e.$questionPickedModal.html(t)}))}},{key:"inItStep2form",value:function(){var t=this.$step2_form.validate({onkeyup:!1,rules:{title:{required:!0,maxlength:50,trim:!0,course_title:!0},description:{required:!0},content:"required",questionLength:{required:!0}},messages:{description:Translator.trans("activity.homework_manage.question_homework_hint"),questionLength:Translator.trans("activity.homework_manage.question_required_error_hint")}});this.validator2=t,this.initCkeditor(t),this.$step2_form.data("validator",t)}},{key:"setValidateRule",value:function(){$.validator.addMethod("arithmeticFloat",(function(t,e){return this.optional(e)||/^[0-9]+(\.[0-9]?)?$/.test(t)}),$.validator.format(Translator.trans("activity.homework_manage.arithmetic_float_error_hint"))),$.validator.addMethod("positiveInteger",(function(t,e){return this.optional(e)||/^[1-9]\d*$/.test(t)}),$.validator.format(Translator.trans("activity.homework_manage.positive_integer_error_hint"))),$.validator.addMethod("DateAndTime",(function(t,e){return this.optional(e)||/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) ([0-1]{1}[0-9]{1})|(2[0-4]{1}):[0-5]{1}[0-9]{1}$/.test(t)}),$.validator.format(Translator.trans("activity.homework_manage.date_and_time_error_hint:mm")))}}]),t}(),h=$("#step2-form");new c($("#iframe-content")),new i.a(h),new u(h,$("#attachment-modal",window.parent.document))},22:function(t,e){t.exports=jQuery},385:function(t,e,n){"use strict";n.d(e,"a",(function(){return i}));var i=function(t){var e=!1,n="",i=$("#task-create-content-iframe").contents().find(".js-subjective-remask");t.find("tbody tr").each((function(){var t=$(this).data("type");console.log(t),"essay"==t&&(e=!0)})),console.log(e),e||0==t.find("tbody tr").length?i.html(""):(console.log(i),n="homework"==i.data("type")?Translator.trans("activity.homework_manage.objective_question_hint"):Translator.trans("activity.homework_manage.pass_objective_question_hint"),i.html(n).removeClass("hidden"))}}});