!function(l){function e(e){for(var t,s,n=e[0],a=e[1],i=e[2],o=0,r=[];o<n.length;o++)s=n[o],Object.prototype.hasOwnProperty.call(u,s)&&u[s]&&r.push(u[s][0]),u[s]=0;for(t in a)Object.prototype.hasOwnProperty.call(a,t)&&(l[t]=a[t]);for(d&&d(e);r.length;)r.shift()();return h.push.apply(h,i||[]),c()}function c(){for(var e,t=0;t<h.length;t++){for(var s=h[t],n=!0,a=1;a<s.length;a++){var i=s[a];0!==u[i]&&(n=!1)}n&&(h.splice(t--,1),e=o(o.s=s[0]))}return e}var s={},u={161:0},h=[];function o(e){if(s[e])return s[e].exports;var t=s[e]={i:e,l:!1,exports:{}};return l[e].call(t.exports,t,t.exports,o),t.l=!0,t.exports}o.m=l,o.c=s,o.d=function(e,t,s){o.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:s})},o.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},o.t=function(t,e){if(1&e&&(t=o(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var s=Object.create(null);if(o.r(s),Object.defineProperty(s,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var n in t)o.d(s,n,function(e){return t[e]}.bind(null,n));return s},o.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return o.d(t,"a",t),t},o.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},o.p="/static-dist/";var t=window.webpackJsonp=window.webpackJsonp||[],n=t.push.bind(t);t.push=e,t=t.slice();for(var a=0;a<t.length;a++)e(t[a]);var d=n;h.push([643,0]),c()}({148:function(e,t,s){"use strict";s.d(t,"b",function(){return a}),s.d(t,"c",function(){return o}),s.d(t,"d",function(){return r}),s.d(t,"a",function(){return l});var n=s(152),a=function(){$("body").on("click",".js-close-course",function(e){var t=$(e.currentTarget);cd.confirm({title:Translator.trans("site.close"),content:Translator.trans("course.manage.close_hint"),okText:Translator.trans("site.confirm"),cancelText:Translator.trans("site.cancel")}).on("ok",function(){$.post(t.data("checkUrl"),function(e){e.warn?cd.confirm({title:Translator.trans("site.close"),content:Translator.trans(e.message),okText:Translator.trans("site.confirm"),cancelText:Translator.trans("site.cancel")}).on("ok",function(){i(t)}):i(t)})})})},i=function(e){$.post(e.data("url"),function(e){e.success?(cd.message({type:"success",message:Translator.trans("course.manage.close_success_hint")}),location.reload()):cd.message({type:"danger",message:Translator.trans("course.manage.close_fail_hint")+":"+e.message})})},o=function(){$("body").on("click",".js-delete-course",function(e){cd.confirm({title:Translator.trans("site.delete"),content:Translator.trans("course.manage.delete_hint"),okText:Translator.trans("site.confirm"),cancelText:Translator.trans("site.cancel")}).on("ok",function(){$.post($(e.currentTarget).data("url"),function(e){e.success?(cd.message({type:"success",message:Translator.trans("site.delete_success_hint")}),e.redirect?window.location.href=e.redirect:location.reload()):cd.message({type:"danger",message:Translator.trans("site.delete_fail_hint")+":"+e.message})})})})},r=function(){Object(n.a)(".js-publish-course",{title:"course.manage.publish_title",hint:"course.manage.publish_hint",success:"course.manage.publish_success_hint",fail:"course.manage.publish_fail_hint"})},l=function(){var t,s=$(".js-task-list-header"),n=$(".js-task-list-header__slot");s.length&&(t=s.offset().top,$(window).scroll(function(e){$(window).scrollTop()>=t?(s.addClass("fixed"),n.removeClass("hidden")):(s.removeClass("fixed"),n.addClass("hidden"))}))}},15:function(e,t){e.exports=jQuery},164:function(e,t,s){"use strict";s.d(t,"b",function(){return n}),s.d(t,"a",function(){return a});var n=function(){cd.onoff({el:".js-switch"}).on("change",function(e){var s=$(".js-switch"),t=s.data("url"),n=s.parent().hasClass("checked")?1:0,a=s.parent().hasClass("checked")?"on":"off";cd.confirm({title:Translator.trans("confirm.oper.tip"),content:Translator.trans("confirm.lesson.hidden.tip."+a),okText:Translator.trans("site.yes"),cancelText:Translator.trans("site.no")}).on("ok",function(){$.post(t,{status:n}).success(function(e){cd.message({type:"success",message:Translator.trans("site.save_success_hint")}),location.reload()}).error(function(e){cd.message({type:"danger",message:e.responseJSON.error.message})})}).on("cancel",function(e,t){s[0].checked=!s[0].checked,s.parent().toggleClass("checked")})})},a=function(){$("body").on("click",".js-lesson-create-btn",function(e){var t=$(e.currentTarget).data("url");$.get(t,{}).success(function(e){$("#modal").html(""),$("#modal").append(e.html),$("#modal").modal({backdrop:"static",show:!0})}).error(function(e){cd.message({type:"danger",message:Translator.trans(e.responseJSON.error.message)})})})}},281:function(e,t,s){"use strict";s.d(t,"a",function(){return c});var n=s(0),a=s.n(n),i=s(1),o=s.n(i),l=s(43),r=s(4),c=function(){function t(e){a()(this,t),this.$element=$(e),this._sort(),this._event()}return o()(t,[{key:"_event",value:function(){var s=this;this.$element.on("addItem",function(e,t){s.addItem(t),s.sortList()}),$("body").on("click","[data-position]",function(e){var t=$(this);s.position=t.data("position"),s.type=t.data("type")}),this._deleteChapter(),this._collapse(),this._publish(),this._createTask(),this._optional(),this._initLessonTaskAction()}},{key:"_collapse",value:function(){var o=['<i class="es-icon es-icon-chevronright cd-mr16"></i>','<i class="es-icon es-icon-keyboardarrowdown cd-mr16"></i>'];this.$element.on("click",".js-toggle-show",function(e){var t=$(e.currentTarget),s=t.closest(".task-manage-item"),n=s.hasClass("js-task-manage-chapter")?".js-task-manage-chapter":".js-task-manage-chapter,.js-task-manage-unit",a=s.nextUntil(n),i=t.hasClass("toogle-hide");t.hasClass("js-toggle-unit")?i?a.removeClass("unit-hide"):a.addClass("unit-hide"):t.hasClass("js-toggle-chapter")&&(a=a.not(".unit-hide")),(a=a.filter(function(e,t){var s="none"===$(t).css("display");return i===s})).stop().animate({height:"toggle",opacity:"toggle"},"fast"),t.toggleClass("toogle-hide").hasClass("toogle-hide")?t.html(o[0]):t.html(o[1])})}},{key:"addItem",value:function(e){var t=$(e),s=$("#"+t.attr("id"));if(0<s.length)return s.replaceWith(t),void this.afterAddItem(t);switch(this.type){case"chapter":var n=this.$element.find("#chapter-"+this.position),a=n.nextUntil(".js-task-manage-chapter").last();0==a.length?n.after(t):a.after(t);break;case"task":this.$element.find("#chapter-"+this.position+" .js-lesson-box").append(t);var i=t.parents(".js-lesson-container");this._triggerAsTaskNumUpdated(i);break;case"lesson":var o=this.$element.find("#chapter-"+this.position),r=o.nextUntil(".js-task-manage-unit,.js-task-manage-chapter").last();0==r.length?o.after(t):r.after(t);break;default:this.$element.append(t)}$('[data-toggle="tooltip"]').tooltip(),this.handleEmptyShow(),this._flushTaskNumber(),this._flushPublishLessonNum(),this.clearPosition(),this.afterAddItem(t)}},{key:"clearPosition",value:function(){this.position="",this.type=""}},{key:"_deleteChapter",value:function(){var a=this;this.$element.on("click",".js-delete",function(e){var t=$(this),s=t.closest(".task-manage-item"),n=a._getDeleteText(t);cd.confirm({title:Translator.trans("site.delete"),content:n,okText:Translator.trans("site.confirm"),cancelText:Translator.trans("site.cancel")}).on("ok",function(){"task"==t.data("type")&&0==s.siblings().length&&s.closest(".js-task-manage-lesson").remove();var e=s.parents(".js-lesson-container");s.remove(),a._triggerAsTaskNumUpdated(e),a.handleEmptyShow(),a._flushTaskNumber(),a._flushPublishLessonNum(),$.post(t.data("url"),function(e){Object(r.a)("success",Translator.trans("site.delete_success_hint")),a.sortList()})})})}},{key:"_getDeleteText",value:function(e){return"task"==e.data("type")?Translator.trans("course.manage.task_delete_hint",{taskName:e.data("name")}):Translator.trans("course.manage.chapter_delete_hint",{name:e.data("name")})}},{key:"_sort",value:function(){var i,o=this,r=null;Object(l.a)({element:o.$element,ajax:!1,group:"nested",placeholder:'<li class="placeholder task-dragged-placeholder"></li>',isValidTarget:function(e,t){return o._sortRules(e,t)},onDragStart:function(e,t,s){var n=e.offset(),a=t.rootGroup.pointer;i={left:a.left-n.left,top:a.top-n.top},r=o.getChildrens(e),s(e,t)},onDrag:function(e,t){var s=e.height();$(".task-dragged-placeholder").css({height:s,"background-color":"#eee"}),e.css({left:t.left-i.left,top:t.top-i.top})},onDrop:function(e,t,s){s(e,t);for(var n=e;"none"===n.next().css("display");)n=n.next();n.after(e),r&&(e.after(r),r=null),o.sortList()}})}},{key:"getChildrens",value:function(e){if(!(0<e.find(".js-toggle-show.toogle-hide").length))return null;var t=null;return e.hasClass("js-task-manage-chapter")?t=e.nextUntil(".js-task-manage-chapter"):e.hasClass("js-task-manage-unit")&&(t=e.nextUntil(".js-task-manage-unit,.js-task-manage-chapter")),t=t&&t.filter(function(e){return"none"===t.eq(e).css("display")})}},{key:"_sortRules",value:function(e,t){return(!e.hasClass("js-task-manage-item")||t.target.closest(".js-task-manage-lesson").attr("id")==e.closest(".js-task-manage-lesson").attr("id"))&&(!((e.hasClass("js-task-manage-unit")||e.hasClass("js-task-manage-chapter"))&&!t.target.hasClass("sortable-list"))&&(!e.hasClass("js-task-manage-lesson")||!t.target.hasClass("js-lesson-box")))}},{key:"handleEmptyShow",value:function(){0===$("#sortable-list").find("li").length?$(".js-task-empty").removeClass("hidden"):$(".js-task-empty").addClass("hidden")}},{key:"sortList",value:function(){var e=[];this.$element.find(".task-manage-item").each(function(){e.push($(this).attr("id"))}),$.post(this.$element.data("sortUrl"),{ids:e},function(e){}),this.sortablelist()}},{key:"setShowNum",value:function(e){0==e.attr("show-num")?e.attr("show-num",1):e.attr("show-num",0)}},{key:"sortablelist",value:function(){for(var e=[".js-task-manage-lesson[show-num=1]",".js-task-manage-chapter",".js-task-manage-item[show-num=1]",".js-task-manage-unit"],t=0;t<e.length;t++)this._sortNumberByClassName(e[t]);this._sortUnitNumber()}},{key:"_sortNumberByClassName",value:function(e){var t=1;this.$element.find(e).each(function(){$(this).find(".number").text(t++)})}},{key:"_sortUnitNumber",value:function(){var t;this.$element.find(".js-task-manage-chapter").each(function(){var e=$(this).nextUntil(".js-task-manage-chapter").filter(".js-task-manage-unit");t=1,e.each(function(){$(this).find(".number").text(t++)})})}},{key:"_publish",value:function(){var s=this,n=this,a={class:".js-publish-item, .js-delete, .js-lesson-unpublish-status",oppositeClas:".js-unpublish-item",isHideUnPublish:$("#isHideUnPublish").hasClass("checked"),flag:!1};this.$element.on("click",".js-unpublish-item",function(e){var t=$(e.target);a.success=Translator.trans("course.manage.task_unpublish_success_hint"),a.danger=Translator.trans("course.manage.task_unpublish_fail_hint")+":",s.toggleOptional(t,n,a)}),this.$element.on("click",".js-publish-item",function(e){var t=$(e.target);a.success=Translator.trans("course.manage.task_publish_success_hint"),a.danger=Translator.trans("course.manage.task_publish_fail_hint")+":",s.toggleOptional(t,n,a)})}},{key:"_flushTaskNumber",value:function(){this.$taskNumber||(this.$taskNumber=$("#task-num"));var e=$(".js-settings-item.active").length;this.$taskNumber.text(e)}},{key:"_flushPublishLessonNum",value:function(){var e=$(".js-settings-item.active").length,t=$(".js-lesson-unpublish-status.hidden").length,s=Translator.trans("course.plan_task.lessons_publish_status",{publishedNum:t,unpublishedNum:e-t});$(".js-lessons-publish-status").attr("data-content",s)}},{key:"_createTask",value:function(){this.$element.on("click",".js-create-task-btn",function(e){var t=$(this).data("url");$.get(t,function(e){e.code?($("#modal").html(""),$("#modal").append(e.html),$("#modal").modal({backdrop:"static",show:!0})):cd.message({type:"danger",message:Translator.trans(e.message)})}).fail(function(e){cd.message({type:"danger",message:e.responseJSON.error.message})})})}},{key:"_optional",value:function(){var s=this,n={class:".js-set-optional",oppositeClas:".js-unset-optional,.js-lesson-option-tag",success:Translator.trans("site.save_success_hint"),danger:Translator.trans("site.save_error_hint")+":",flag:!0};this.$element.on("click",".js-set-optional",function(e){var t=$(e.target);s.toggleOptional(t,s,n)}),this.$element.on("click",".js-unset-optional",function(e){var t=$(e.target);s.toggleOptional(t,s,n)})}},{key:"_initLessonTaskAction",value:function(){var t={"js-lesson-preview-btn":"js-hidden-lesson-preview-btn","js-lesson-edit-btn":"js-hidden-lesson-edit-btn","js-lesson-rename-btn":"js-hidden-lesson-rename-btn"};for(var e in t)!function(e){var a=t[e];$("#sortable-list").on("click","."+e,function(){var e=$(this).parents(".js-lesson-container"),t=e.find(".js-task-manage-item").attr("id").split("-")[1],s=e.find("."+a),n=s.data("url").replace("%7BtaskId%7D",t);s.data("url",n),s.data("toggle")?s.click():window.open(s.data("url"),"_blank")})}(e)}},{key:"toggleOptional",value:function(s,n,a){var i=this,o=s.closest(".task-manage-item"),r=o.find(a.class),l=o.find(a.oppositeClas),c=$("#isHideUnPublish").hasClass("checked");$.post(s.data("url"),function(e){var t=!0;c&&(t=n.checkShouldSetProperty(s,o)),r.toggleClass("hidden"),l.toggleClass("hidden"),(c&&t||!c&&a.flag)&&(o.find(".display-text").toggleClass("hidden"),n.setShowNum(o),n.sortList()),i._flushPublishLessonNum(),cd.message({type:"success",message:a.success})}).fail(function(e){cd.message({type:"danger",message:a.danger+e.responseJSON.error.message})})}},{key:"checkShouldSetProperty",value:function(e,t){var s=t.find(".js-publish-item"),n=t.find(".js-set-optional"),a=s.hasClass("hidden"),i=n.hasClass("hidden"),o=!0;return e.hasClass("js-unpublish-item")||e.hasClass("js-publish-item")?i&&(o=!1):(e.hasClass("js-set-optional")||e.hasClass("js-unset-optional"))&&(a||(o=!1)),o}},{key:"afterAddItem",value:function(){console.log("afterAddItem")}},{key:"_triggerAsTaskNumUpdated",value:function(e){var t=e.find(".js-lesson-box"),s=1<t.find(".js-task-manage-item").length;s?(t.removeClass("hidden"),e.find(".js-display-when-mul-tasks").removeClass("hidden"),e.find(".js-display-when-single-task").addClass("hidden")):(t.addClass("hidden"),e.find(".js-display-when-mul-tasks").addClass("hidden"),e.find(".js-display-when-single-task").removeClass("hidden"),e.find(".js-task-title").html(e.find(".js-lesson-title").html())),this._triggerLessonIconAsTaskNumUpdated(e,s)}},{key:"_triggerLessonIconAsTaskNumUpdated",value:function(e,t){var s=e.find(".js-lesson-icon"),n="";n=t?s[0].classList:0==e.find(".js-lesson-box").find(".es-icon").length?[]:e.find(".js-lesson-box").find(".es-icon")[0].classList;for(var a=0;a<n.length;a++){var i=n[a];i.startsWith("es-icon-")&&(t?s.removeClass(i):s.addClass(i))}}}]),t}()},363:function(e,t,s){"use strict";s.d(t,"a",function(){return u});var n=s(68),c=s.n(n),a=s(0),i=s.n(a),o=s(1),r=s.n(o),l=s(314),u=function(){function t(e){i()(this,t),this.$element=$(e),this.batchOperate={status:"none",permission:[],chosenItems:[]},this._defaultEvent()}return r()(t,[{key:"_defaultEvent",value:function(){this.calcOperatePanelPosition(),this.toggleBatchOperate(),this.singleChooseItem(),this.batchChooseItem(),this.batchDelete(),this.batchCancelPublish(),this.batchPublish()}},{key:"calcOperatePanelPosition",value:function(){var t=$(".cd-main__body"),s=($(".js-task-list-header"),$(".js-batch-operate-panel")),n=$(".js-batch-operate-panel__slot");$(window).on("resize scroll",Object(l.throttle)(function(){var e=window.pageYOffset;document.documentElement.clientHeight+e-t[0].getBoundingClientRect().height-t.offset().top<=0?(s.addClass("fixed"),n.removeClass("hidden")):(s.removeClass("fixed"),n.addClass("hidden"))},300))}},{key:"toggleBatchOperate",value:function(){var t=this,s=$(".js-task-list-header .js-batch-operate-switch");s.on("click",function(e){t.batchOperate.status="none"===t.batchOperate.status?"editing":"none",s.toggleClass("hidden"),"editing"===t.batchOperate.status?t.startBatchOperate():t.endBatchOperate()})}},{key:"startBatchOperate",value:function(){this.$element.find(".js-chapter-operation").removeClass("hidden"),$(".js-batch-operate-panel").removeClass("hidden"),this.batchOperate.chosenItems=[],$(".js-task-list-header").find(".js-lesson-create-btn,.js-batch-add,.js-add-chapter-unit").attr("disabled",!0),$(".js-task-list-header").find(".js-add-chapter-unit .caret").hide()}},{key:"endBatchOperate",value:function(){this.$element.find(".js-chapter-operation").addClass("hidden").removeClass("checked"),$(".js-batch-operate-panel").addClass("hidden"),this.batchOperate.chosenItems=[],$(".js-task-list-header").find(".js-lesson-create-btn,.js-batch-add,.js-add-chapter-unit").attr("disabled",!1),$(".js-task-list-header").find(".js-add-chapter-unit .caret").show()}},{key:"singleChooseItem",value:function(){var r=this;this.$element.on("click",".js-chapter-operation",function(e){e.stopPropagation();var t,s,n,a,i,o=$(e.target);o.hasClass("js-chapter-operation")&&(o.toggleClass("checked"),t=o.data(),s=t.id,n=t.type,a=o.hasClass("checked"),-1<(i=r.batchOperate.chosenItems.findIndex(function(e){return e.id===s}))&&!a?r.batchOperate.chosenItems.splice(i,1):-1===i&&a&&r.batchOperate.chosenItems.push({id:s,type:n}),r.updateBatchBtnStatus())})}},{key:"batchChooseItem",value:function(){var a=this,i=["chapter","unit","lesson"];this.$element.on("click",".js-batch-choose",function(e){var t=$(e.target),s=t.data("types").split(","),n=i.filter(function(e){return-1===s.indexOf(e)});a.toggleChooseAllItemByType(s),t.toggleClass("active"),n.forEach(function(e){return a.cancelChooseAllItemByType(e)}),a.$element.find('.js-batch-choose[data-types="'.concat(n.join(","),'"]')).removeClass("active"),a.updateBatchBtnStatus()})}},{key:"toggleChooseAllItemByType",value:function(e){var n=this,a=!0;this.batchOperate.chosenItems;e.forEach(function(e){var t=n.$element.find(".js-chapter-operation.checked[data-type=".concat(e,"]")),s=n.$element.find(".js-chapter-operation[data-type=".concat(e,"]"));t.length!==s.length&&(a=!1)}),e.forEach(function(e){a?n.cancelChooseAllItemByType(e):n.chooseAllItemByType(e)}),this.updateBatchBtnStatus()}},{key:"chooseAllItemByType",value:function(a){var i=this;this.$element.find(".js-chapter-operation[data-type=".concat(a,"]")).each(function(e,t){var s=$(t),n=s.data().id;-1===i.batchOperate.chosenItems.findIndex(function(e){return e.id===n})&&(i.batchOperate.chosenItems.push({id:n,type:a}),s.addClass("checked"))})}},{key:"cancelChooseAllItemByType",value:function(e){var i=this;this.$element.find(".js-chapter-operation[data-type=".concat(e,"]")).each(function(e,t){var s=$(t),n=s.data().id,a=i.batchOperate.chosenItems.findIndex(function(e){return e.id===n});-1<a&&(i.batchOperate.chosenItems.splice(a,1),s.removeClass("checked"))})}},{key:"batchDelete",value:function(){var o=this,r=$("#course_manage_lesson_batch_delete").val();this.$element.on("click",".js-batch-delete",function(){var e,t=o.batchOperate,s=t.status,n=t.permission,a=$(event.target),i=o.batchOperate.chosenItems;"none"!==s&&-1!==n.indexOf("delete")&&((e=i.every(function(e){return"lesson"===e.type}))&&(i=(i=o.clearDeletedLessons()).filter(function(e){return!o.getPublishStatusById(e.id)})),cd.confirm({title:Translator.trans("site.delete"),content:o.getDeleteText(e,i.length),okText:Translator.trans("site.confirm"),cancelText:Translator.trans("site.cancel"),className:"task-manage-batch-delete"}).on("ok",function(){var e=i.map(function(e){return e.id});a.button("loading"),$.post(r,{lessonIds:e}).then(function(e){c()(e)&&e.forEach(function(e){return $("#chapter-".concat(e)).remove()}),o.clearChosenItems(),a.button("reset"),cd.message({type:"success",message:Translator.trans("site.delete_success_hint")}),setTimeout(function(){return o.updateBatchBtnStatus()})}).catch(function(e){var t=Translator.trans("site.delete_fail_hint: Delete failed")+":";a.button("reset"),cd.message({type:"danger",message:t+":"+e.responseJSON.error.message})})}))})}},{key:"getDeleteText",value:function(e,t){return e?Translator.trans("course.manage.task_batch_delete_hint",{length:t}):Translator.trans("course.manage.chapter_batch_delete_hint",{length:t})}},{key:"clearDeletedLessons",value:function(){var e=this.batchOperate.chosenItems,t=e.filter(function(e){return 0<$("#chapter-".concat(e.id)).length});return t.length!==e.length&&(this.batchOperate.chosenItems=t,this.updateBatchBtnStatus()),this.batchOperate.chosenItems}},{key:"batchPublish",value:function(){var r=this,l=$("#course_manage_lesson_batch_publish").val();this.$element.on("click",".js-batch-publish",function(e){var t,s,n=r.batchOperate,a=n.status,i=n.permission,o=n.chosenItems;"none"!==a&&-1!==i.indexOf("publish")&&(t=o.map(function(e){return e.id}),(s=$(e.target)).button("loading"),$.post(l,{lessonIds:t}).then(function(e){c()(e)&&e.forEach(function(e){var t=$("#chapter-".concat(e));t.find(".js-publish-item, .js-delete, .js-lesson-unpublish-status").addClass("hidden"),t.find(".js-unpublish-item").removeClass("hidden")}),cd.message({type:"success",message:Translator.trans("course.manage.task_publish_success_hint")}),s.button("reset"),setTimeout(function(){return r.updateBatchBtnStatus()})}).catch(function(e){var t=Translator.trans("course.manage.task_unpublish_fail_hint")+":";s.button("reset"),cd.message({type:"danger",message:t+e.responseJSON.error.message})}))})}},{key:"batchCancelPublish",value:function(){var o=this,r=$("#course_manage_lesson_batch_unpublish").val();this.$element.on("click",".js-batch-cancel-publish",function(){var e,s,t=o.batchOperate,n=t.status,a=t.permission,i=t.chosenItems;"none"!==n&&-1!==a.indexOf("cancelPublish")&&(e=i.map(function(e){return e.id}),(s=$(event.target)).button("loading"),$.post(r,{lessonIds:e}).then(function(e){c()(e)&&e.forEach(function(e){var t=$("#chapter-".concat(e));t.find(".js-publish-item, .js-delete, .js-lesson-unpublish-status").removeClass("hidden"),t.find(".js-unpublish-item").addClass("hidden")}),cd.message({type:"success",message:Translator.trans("course.manage.task_unpublish_success_hint")}),s.button("reset"),setTimeout(function(){return o.updateBatchBtnStatus()})}).catch(function(e){var t=Translator.trans("course.manage.task_unpublish_fail_hint")+":";s.button("reset"),cd.message({type:"danger",message:t+e.responseJSON.error.message})}))})}},{key:"updateBatchBtnStatus",value:function(){var e,t,s=this,n=this.$element.find(".js-chosen-number"),a=this.batchOperate.chosenItems,i=-1<a.findIndex(function(e){return"chapter"===e.type}),o=-1<a.findIndex(function(e){return"lesson"===e.type}),r=-1<a.findIndex(function(e){return"unit"===e.type}),l=this.$element.find(".js-batch-publish"),c=this.$element.find(".js-batch-cancel-publish"),u=this.$element.find(".js-batch-delete"),h=!(i||o||r);n.text(a.length),l.attr("disabled",h),c.attr("disabled",h),u.attr("disabled",h),this.batchOperate.permission=h?[]:["publish","cancelPublish","delete"],o&&(i||r)&&(l.attr("disabled",!0),c.attr("disabled",!0),u.attr("disabled",!0),this.batchOperate.permission=[]),o||!i&&!r||(this.batchOperate.permission=["delete"],l.attr("disabled",!0),c.attr("disabled",!0)),!o||i||r||(e=a.every(function(e){var t=e.id;return s.getPublishStatusById(t)}),t=a.every(function(e){var t=e.id;return!s.getPublishStatusById(t)}),e?(u.attr("disabled",!0),l.attr("disabled",!0),this.batchOperate.permission=["cancelPublish"]):t&&(c.attr("disabled",!0),this.batchOperate.permission=["publish","delete"]))}},{key:"clearChosenItems",value:function(){this.batchOperate.chosenItems=[]}},{key:"getPublishStatusById",value:function(e){return 0<$("#chapter-".concat(e)).find(".js-lesson-unpublish-status.hidden").length}}]),t}()},643:function(e,t,s){"use strict";s.r(t);var n=s(2),i=s.n(n),a=s(0),o=s.n(a),r=s(1),l=s.n(r),c=s(8),u=s.n(c),h=s(9),d=s.n(h),p=s(5),f=s.n(p),m=s(164),g=s(281),b=s(363),v=s(148);function k(n){var a=function(){if("undefined"==typeof Reflect||!i.a)return!1;if(i.a.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(i()(Date,[],function(){})),!0}catch(e){return!1}}();return function(){var e,t,s=f()(n);return t=a?(e=f()(this).constructor,i()(s,arguments,e)):s.apply(this,arguments),d()(this,t)}}new(function(e){u()(n,e);var s=k(n);function n(e){var t;return o()(this,n),(t=s.call(this,e))._defaultEvent(),t}return l()(n,[{key:"_defaultEvent",value:function(){this._showLesson()}},{key:"_sortRules",value:function(){return!0}},{key:"_showLesson",value:function(){this.$element.find(".js-task-manage-item").first().addClass("active").find(".js-settings-list").stop().slideDown(500),this.$element.on("click",".js-item-content",function(e){var t=$(e.currentTarget).closest(".js-task-manage-item");t.hasClass("active")?t.removeClass("active").find(".js-settings-list").stop().slideUp(500):(t.addClass("active").find(".js-settings-list").stop().slideDown(500),t.siblings(".js-task-manage-item.active").removeClass("active").find(".js-settings-list").hide())})}},{key:"afterAddItem",value:function(e){0<e.find(".js-item-content").length&&e.find(".js-item-content").trigger("click"),$('[data-toggle="popover"]').popover({html:!0})}}]),n}(g.a))("#sortable-list"),new b.a(".js-lesson-manage"),Object(m.b)(),Object(m.a)(),Object(v.a)()}});