(window.webpackJsonp=window.webpackJsonp||[]).push([["goods~old-goods"],{2164:function(t,e,s){"use strict";s("e17f");var i=s("2241");e.a={methods:{copyPcUrl:function(t){var e=this,s="移动端暂不支持此类课程学习。请移步至电脑「".concat(t,"」完成课程。");i.a.alert({title:"暂不支持",message:s,messageAlign:"left",confirmButtonText:"复制链接"}).then((function(){e.$copyText(t).then((function(t){}))}))}}}},"228a":function(t,e,s){"use strict";s("8e6e"),s("ac6a"),s("456d"),s("6b54"),s("c5f6");var i=s("bd86"),n={name:"SwiperDirectory",props:{item:{type:Array,default:function(){return[]}},slideIndex:{type:Number,default:0},hasChapter:{type:Boolean,default:!0}},data:function(){return{current:this.slideIndex||0}},watch:{slideIndex:function(t,e){t!=e&&(this.current=t||0)}},methods:{changeChapter:function(t){this.current=t,this.$emit("changeChapter",t)},handleChapter:function(t){this.$refs.chapterSwipe.swipeTo(t)}}},r=s("a6c2"),a=Object(r.a)(n,(function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"swiper-directory goods-swiper-directory"},[s("van-swipe",{ref:"chapterSwipe",attrs:{"show-indicators":!1,loop:!1,touchable:!0,width:265,"initial-swipe":t.slideIndex},on:{change:t.changeChapter}},t._l(t.item,(function(e,i){return s("van-swipe-item",{key:i},[0==e.isExist?s("div",{staticClass:"chapter nochapter",class:"swiper-directory-active",staticStyle:{margin:"0 4vw 0 0"},style:i==t.item.length-1&&"margin-right: 0;",on:{click:function(e){return t.handleChapter(i)}}},[s("i",{staticClass:"iconfont icon-wuzhangjieliang"}),t._v("\n        无章节\n      ")]):t._e(),1==e.isExist?s("div",{staticClass:"chapter haschapter",class:[t.current===i?"swiper-directory-active":""],staticStyle:{margin:"0 4vw 0 0"},style:i==t.item.length-1&&"margin-right: 0;",on:{click:function(e){return t.handleChapter(i)}}},[s("p",{staticClass:"chapter-title text-overflow"},[t._v("\n          第"+t._s(e.number)+t._s(t.hasChapter?"章":"节")+"："+t._s(e.title)+"\n        ")]),s("p",{staticClass:"chapter-des text-overflow"},[t._v("\n          "+t._s(t.hasChapter?"节("+e.unitNum+")":"")+" 课时("+t._s(e.lessonNum)+") 学习任务("+t._s(e.tasksNum)+")\n        ")])]):t._e()])})),1)],1)}),[],!1,null,null,null).exports,c={name:"UtilDirectory",props:{util:{type:Object,default:function(){}}}},o=Object(r.a)(c,(function(){var t=this.$createElement,e=this._self._c||t;return 1==this.util.isExist?e("div",{staticClass:"util-directory text-overflow"},[this._v("\n  第"+this._s(this.util.number)+"节："+this._s(this.util.title)+"\n")]):this._e()}),[],!1,null,null,null).exports,u=(s("e7e5"),s("d399")),l=s("d863"),d=s("2164"),p=s("2f62"),h=s("faa5");function f(t,e){var s=Object.keys(t);if(Object.getOwnPropertySymbols){var i=Object.getOwnPropertySymbols(t);e&&(i=i.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),s.push.apply(s,i)}return s}function m(t){for(var e=1;e<arguments.length;e++){var s=null!=arguments[e]?arguments[e]:{};e%2?f(Object(s),!0).forEach((function(e){Object(i.a)(t,e,s[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(s)):f(Object(s)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(s,e))}))}return t}var v={name:"LessonDirectory",mixins:[l.a,d.a],props:{lesson:{type:Array,default:function(){return[]}},errorMsg:{type:String,default:""},taskId:{type:Number,default:-1},taskNumber:{type:Number,default:-1},unitNum:{type:Number,default:-1}},data:function(){return{currentTask:""}},watch:{taskId:{handler:"getTaskId",immediate:!0}},computed:m(m({},Object(p.e)("course",{details:function(t){return t.details},joinStatus:function(t){return t.joinStatus},selectedPlanId:function(t){return t.selectedPlanId}})),{},{hasLesson:function(){return this.lesson.length>0},isNoData:function(){return 0===this.taskNumber&&0===this.unitNum}}),mounted:function(){if(Object.keys(this.$route.query).length){var t=this.$route.query,e=t.sourceType,s=t.taskId;this.setSourceType({sourceType:e,taskId:s})}},methods:m(m({},Object(p.d)("course",{setSourceType:h.x})),{},{getCurrentStatus:function(t){return Number(t.isFree)?"is-free":Number(this.details.tryLookable)&&"video"===t.type&&t.activity.mediaStorage?"is-tryLook":""},filterTaskStatus:function(t){return this.details.member||"is-free"!==t.tagStatus?this.details.member||"is-tryLook"!==t.tagStatus?"":"试看":"免费"},getTaskId:function(){this.currentTask=this.taskId},doubleLine:function(t){if(t.type)return"live"===t.type},showTask:function(t,e){var s=!0;return null==t.mode&&0==e&&(s=!1),"lesson"==t.mode&&(s=!1),s},lessonCellClick:function(t,e,s){if(this.$store.commit(h.y,""),this.errorMsg)this.$emit("showDialog");else if(t.lock)Object(u.a)("需要解锁上一个任务");else if("create"!==t.status&&"published"===t.status){var i={id:t.id};this.$store.commit("course/".concat(h.h),{nextTask:i}),!this.details.allowAnonymousPreview&&this.$router.push({name:"login",query:{redirect:this.redirect}}),this.joinStatus&&this.showTypeDetail(t)}else Object(u.a)("敬请期待")},showTypeDetail:function(t){if("published"===t.status)switch(t.type){case"video":"self"===t.mediaSource?this.setSourceType({sourceType:"video",taskId:t.id}):Object(u.a)("暂不支持此类型");break;case"audio":this.setSourceType({sourceType:"audio",taskId:t.id});break;case"text":case"ppt":case"doc":this.$router.push({name:"course_web",query:{courseId:this.selectedPlanId,taskId:t.id,type:t.type}});break;case"live":var e=!1;if(new Date>new Date(1e3*t.endTime)){if("videoGenerated"===t.activity.replayStatus){"self"===t.mediaSource?this.setSourceType({sourceType:"video",taskId:t.id}):Object(u.a)("暂不支持此类型");break}if("ungenerated"===t.activity.replayStatus)return void Object(u.a)("暂无回放");e=!0}this.$router.push({name:"live",query:{courseId:this.selectedPlanId,taskId:t.id,type:t.type,title:t.title,replay:e}});break;case"testpaper":var s=t.activity.testpaperInfo.testpaperId;this.$router.push({name:"testpaperIntro",query:{testId:s,targetId:t.id}});break;case"homework":this.$router.push({name:"homeworkIntro",query:{courseId:this.$route.params.id,taskId:t.id}});break;case"exercise":this.$router.push({name:"exerciseIntro",query:{courseId:this.$route.params.id,taskId:t.id}});break;default:this.setSourceType({sourceType:"img",taskId:t.id}),this.copyPcUrl(t.courseUrl)}else Object(u.a)("敬请期待")},iconfont:function(t){switch(t.type){case"audio":return"icon-yinpin";case"doc":return"icon-wendang";case"exercise":return"icon-lianxi";case"flash":return"icon-flash";case"homework":return"icon-zuoye";case"live":return"icon-zhibo";case"ppt":return"icon-ppt";case"discuss":return"icon-taolun";case"testpaper":return"icon-kaoshi";case"text":return"icon-tuwen";case"video":return"icon-shipin";case"download":return"icon-xiazai";default:return""}},studyStatus:function(t){if(t.lock)return"icon-suo";if(null==t.result)return"icon-weixuexi";switch(t.result.status){case"finish":return"icon-yiwanchengliang";case"start":return"icon-weiwancheng";default:return""}},liveClass:function(t){return"published"!==t.status||"live"!==t.type?"nopublished":(new Date).getTime()>new Date(1e3*t.endTime)?"ungenerated"===t.activity.replayStatus?"end":"back":"play"}})};function _(t,e){var s=Object.keys(t);if(Object.getOwnPropertySymbols){var i=Object.getOwnPropertySymbols(t);e&&(i=i.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),s.push.apply(s,i)}return s}function y(t){for(var e=1;e<arguments.length;e++){var s=null!=arguments[e]?arguments[e]:{};e%2?_(Object(s),!0).forEach((function(e){Object(i.a)(t,e,s[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(s)):_(Object(s)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(s,e))}))}return t}var g={name:"AfterjoinDirectory",components:{swiperDirectory:a,utilDirectory:o,lessonDirectory:Object(r.a)(v,(function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",[t._l(t.lesson,(function(e,i){return s("div",{key:i,staticClass:"lesson-directory",staticStyle:{"margin-left":"0",width:"100%","box-sizing":"border-box"}},[s("div",{staticClass:"lesson-title",class:{"zb-ks":t.doubleLine(e.tasks[e.index])},attrs:{id:e.tasks[e.index].id},on:{click:function(s){return t.lessonCellClick(e.tasks[e.index],i,e.index)}}},[s("div",{staticClass:"lesson-title-r"},[s("div",{staticClass:"lesson-title-des"},[t.doubleLine(e.tasks[e.index])?t._e():s("div",{staticClass:"bl l22"},[s("span",{staticClass:"text-overflow ks",class:{lessonactive:t.currentTask==e.tasks[e.index].id}},[s("i",{staticClass:"iconfont",class:t.iconfont(e.tasks[e.index])}),t._v("\n              "+t._s(Number(e.tasks[e.index].isOptional)?"选修 ":"课时")+t._s(Number(e.tasks[e.index].isOptional)?" ":e.tasks[e.index].number+":"+e.title)+"\n            ")])]),t.doubleLine(e.tasks[e.index])?s("div",{staticClass:"bl"},[s("div",{staticClass:"block-inline"},[s("span",{staticClass:"bl text-overflow ks",class:{lessonactive:t.currentTask==e.tasks[e.index].id}},[s("i",{staticClass:"iconfont",class:t.iconfont(e.tasks[e.index])}),t._v("\n                "+t._s(Number(e.tasks[e.index].isOptional)?"选修 ":"课时")+t._s(Number(e.tasks[e.index].isOptional)?" ":e.tasks[e.index].number+":"+e.title)+"\n              ")]),s("span",{staticClass:"bl zbtime"},[s("span",{class:[t.liveClass(e.tasks[e.index])]},[t._v(t._s(t._f("filterTaskTime")(e.tasks[e.index])))])])])]):t._e()])]),s("div",{staticClass:"lesson-title-l"},["live"!=e.tasks[e.index].type?s("span",[t._v(t._s(t._f("filterTaskTime")(e.tasks[e.index])))]):t._e(),s("i",{staticClass:"iconfont",class:t.studyStatus(e.tasks[e.index])})])]),e.tasks.length>1?s("div",{staticClass:"lesson-items"},t._l(e.tasks,(function(e,n){return s("div",{key:n,staticClass:"litem",attrs:{id:e.id},on:{click:function(s){return t.lessonCellClick(e,i,n)}}},[t.showTask(e,n)?s("div",[s("div",{staticClass:"litem-r text-overflow",class:{lessonactive:t.currentTask==Number(e.id)}},[s("i",{staticClass:"iconfont",class:t.iconfont(e)}),t._v("\n            "+t._s(Number(e.isOptional)?"选修 ":"课时")+t._s(Number(e.isOptional)?" ":e.number+":"+e.title)+"\n          ")]),s("div",{staticClass:"litem-l clearfix"},[s("span",{class:[t.liveClass(e),"text-overflow"]},[t._v(t._s(t._f("filterTaskTime")(e)))]),s("i",{staticClass:"iconfont",class:t.studyStatus(e)})])]):t._e()])})),0):t._e()])})),t.isNoData?s("div",{staticClass:"noneItem"},[s("img",{staticClass:"notask",attrs:{src:"static/images/none.png"}}),s("p",[t._v("暂时还没有课时哦...")])]):t._e()],2)}),[],!1,null,null,null).exports},data:function(){return{scroll:"",item:[],level:3,chapterNum:0,unitNum:0,lessonNum:0,currentChapter:0,currentUnit:0,currentLesson:0,slideIndex:0,taskId:null,nodata:!1,allTask:{},allTaskId:[]}},computed:y(y({},Object(p.e)("course",{nextStudy:function(t){return t.nextStudy},selectedPlanId:function(t){return t.selectedPlanId},OptimizationCourseLessons:function(t){return t.OptimizationCourseLessons},details:function(t){return t.details},taskStatus:function(t){return t.taskStatus}})),{},{hasChapter:function(){return this.chapterNum>0}}),watch:{nextStudy:{handler:"getNextStudy",immediate:!0},selectedPlanId:{handler:"processItem",immediate:!0,deep:!0},taskStatus:{handler:"changeTaskStatus",immediate:!1}},methods:y(y({},Object(p.c)("course",["getCourseLessons"])),{},{getNextStudy:function(){if(this.nextStudy.nextTask){this.taskId=Number(this.nextStudy.nextTask.id);var t=this.allTask[this.taskId];if(!t)return;this.hasChapter?this.slideIndex=t.chapterIndex:this.slideIndex=t.unitIndex}},processItem:function(t){var e=this.OptimizationCourseLessons;this.resetData(),e.length?(this.nodata=!1,this.setItems(e),this.mapChild(this.item),this.startScroll(),this.$store.commit("course/".concat(h.l),this.allTask)):this.nodata=!0},resetData:function(){this.chapterNum=0,this.unitNum=0,this.lessonNum=0},setItems:function(t){this.level=1===t.length&&0==t[0].isExist?2:3,1===t.length?this.item=2===this.level?t[0].children:t:t.length>1&&(this.item=t)},mapChild:function(t){var e=this;t.map((function(s,i){"chapter"===s.type?e.formatChapter(s,t,i):"unit"===s.type?e.formatUnit(s,t,i):"lesson"===s.type?e.formatLesson(s,t,i):e.formatTask(s,t,i)}))},startScroll:function(){var t=this;this.$nextTick((function(){if(t.taskId){var e=document.getElementById("progress-bar"),s=document.getElementById("swiper-directory"),i=document.getElementById(t.taskId);e&&e.offsetHeight,s&&s.offsetHeight;i&&i.offsetTop,document.documentElement.clientWidth}}))},formatChapter:function(t,e,s){this.currentChapter=s,t.chapterNum=0,t.unitNum=0,t.lessonNum=0,t.tasksNum=0,t.isExist&&(this.chapterNum+=1,this.computedNum(1,"chapterNum")),Array.isArray(t.children)&&t.children.length>0&&this.mapChild(t.children)},formatUnit:function(t,e,s){this.currentUnit=s,t.unitNum=0,t.lessonNum=0,t.tasksNum=0,t.isExist&&(this.unitNum+=1,this.computedNum(1,"unitNum")),Array.isArray(t.children)&&t.children.length>0&&(this.computedNum(t.children.length,"lessonNum"),this.mapChild(t.children))},formatLesson:function(t,e,s){this.currentLesson=s,t.isExist&&(this.lessonNum+=1),t.tasks&&(this.computedNum(t.tasks.length-1,"tasksNum"),this.mapChild(t.tasks))},formatTask:function(t,e,s){Number(t.id)===this.taskId&&(this.slideIndex=3==this.level?this.currentChapter:this.currentUnit),this.getLessonIndex(t,s),t.chapterIndex=this.currentChapter,t.unitIndex=this.currentUnit,t.LessonIndex=this.currentLesson,t.level=this.level,t.taskIndex=s,this.allTask[t.id]=y({},t),this.allTaskId.push(t.id)},getLessonIndex:function(t,e){t.mode?"lesson"===t.mode&&this.getMainTask(t,e):this.getMainTask(t,0)},getMainTask:function(t,e){3===this.level?this.$set(this.item[this.currentChapter].children[this.currentUnit].children[this.currentLesson],"index",e):this.$set(this.item[this.currentUnit].children[this.currentLesson],"index",e)},computedNum:function(t,e){var s=3===this.level?this.currentChapter:this.currentUnit,i=this.item[s][e]+t;this.$set(this.item[s],e,i)},changeChapter:function(t){this.slideIndex=t},changeTaskStatus:function(t){if(t){"finish"===t&&this.changeLockStatus();var e=this.allTask[this.taskId],s={};if(2===e.level){var i=this.item[e.unitIndex].children[e.LessonIndex].tasks[e.taskIndex];i.result?i.result.status=t:(s.status=t,i.result=s)}else{var n=this.item[e.chapterIndex].children[e.unitIndex].children[e.LessonIndex].tasks[e.taskIndex];n.result?n.result.status=t:(s.status=t,n.result=s)}}},changeLockStatus:function(){if("lockMode"===this.details.learnMode){var t=this.allTaskId.indexOf(this.taskId.toString());if(t<this.allTaskId.length-1){var e=this.allTaskId[t+1],s=this.allTask[e];2===s.level?this.item[s.unitIndex].children[s.LessonIndex].tasks[s.taskIndex].lock=!1:this.item[s.chapterIndex].children[s.unitIndex].children[s.LessonIndex].tasks[s.taskIndex].lock=!1}}}})},k=Object(r.a)(g,(function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"afterjoin-directory"},[t.lessonNum>0?s("div",[t.chapterNum>0||t.unitNum>0?s("swiper-directory",{attrs:{id:"swiper-directory",item:t.item,"slide-index":t.slideIndex,"has-chapter":t.hasChapter},on:{changeChapter:t.changeChapter}}):t._e(),t.item.length>0?s("div",{attrs:{id:"lesson-directory"}},[t.chapterNum>0?t._l(t.item[t.slideIndex].children,(function(e,i){return s("div",{key:i,staticClass:"pd-bo"},[s("util-directory",{attrs:{util:e}}),s("lesson-directory",t._g(t._b({attrs:{lesson:e.children,"task-id":t.taskId,"task-number":t.item[t.slideIndex].lessonNum,"unit-num":t.item[t.slideIndex].unitNum}},"lesson-directory",t.$attrs,!1),t.$listeners))],1)})):s("div",{staticClass:"pd-bo"},[s("lesson-directory",t._g(t._b({attrs:{lesson:t.item[t.slideIndex].children,"task-id":t.taskId,"task-number":t.item[t.slideIndex].lessonNum,"unit-num":t.item[t.slideIndex].unitNum}},"lesson-directory",t.$attrs,!1),t.$listeners))],1)],2):t._e()],1):t._e(),t.nodata&&0==t.lessonNum?s("div",{staticClass:"noneItem"},[s("img",{staticClass:"nodata",attrs:{src:"static/images/none.png"}}),s("p",[t._v("暂时还没有课程哦...")])]):t._e()])}),[],!1,null,null,null);e.a=k.exports},"51a9":function(t,e,s){"use strict";s("8e6e"),s("ac6a"),s("456d");var i=s("bd86"),n=(s("3b2b"),s("a481"),s("2f62"));function r(t,e){var s=Object.keys(t);if(Object.getOwnPropertySymbols){var i=Object.getOwnPropertySymbols(t);e&&(i=i.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),s.push.apply(s,i)}return s}function a(t){for(var e=1;e<arguments.length;e++){var s=null!=arguments[e]?arguments[e]:{};e%2?r(Object(s),!0).forEach((function(e){Object(i.a)(t,e,s[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(s)):r(Object(s)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(s,e))}))}return t}var c={data:function(){return{show:!1}},props:{goods:{type:Object,default:function(){}},currentSku:{type:Object,default:function(){}}},methods:{gotoVip:function(){this.$router.push({path:"/vip",query:{id:this.currentSku.vipLevelInfo.id}})},showPopup:function(){this.goods&&1==this.goods.specs.length||(this.show=!0)},onClose:function(){this.show=!1},handleClick:function(t){this.$emit("changeSku",t.targetId),this.show=!1},formatDate:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"yyyy-MM-dd";t*=1e3;var s=new Date(t);/(y+)/.test(e)&&(e=e.replace(RegExp.$1,(s.getFullYear()+"").substr(4-RegExp.$1.length)));var i={"M+":s.getMonth()+1,"d+":s.getDate(),"h+":s.getHours(),"m+":s.getMinutes(),"s+":s.getSeconds()};for(var n in i)if(new RegExp("(".concat(n,")")).test(e)){var r=i[n]+"";e=e.replace(RegExp.$1,1===RegExp.$1.length?r:("00"+r).substr(r.length))}return e}},computed:a(a({},Object(n.e)(["vipSwitch"])),{},{buyableModeHtml:function(){var t=this.goods.member;if(t)return"forever"==this.currentSku.usageMode?"长期有效":0!=t.deadline?t.deadline.slice(0,10)+"之前可学习":"长期有效";switch(this.currentSku.usageMode){case"forever":return"长期有效";case"end_date":return this.formatDate(this.currentSku.usageEndTime.slice(0,10))+"&nbsp;之前可学习";case"days":return this.currentSku.usageDays+"天内可学习";case"date":return this.formatDate(this.currentSku.usageStartTime.slice(0,10))+"&nbsp;~&nbsp;"+this.formatDate(this.currentSku.usageEndTime.slice(0,10));default:return""}}})},o=s("a6c2"),u=Object(o.a)(c,(function(){var t=this,e=t.$createElement,s=t._self._c||e;return t.goods.id?s("div",{staticClass:"detail-plan"},["course"===t.goods.type&&t.goods.specs.length>1?s("div",{staticClass:"detail-plan__plan clearfix",on:{click:t.showPopup}},[s("div",{staticClass:"pull-left plan-left"},[t._v("教学计划")]),s("div",{staticClass:"pull-left plan-right"},[t._v("\n        "+t._s(t.currentSku.title)+"\n        "),t.goods.specs.length>1?s("i",{staticClass:"iconfont icon-arrow-right plan-right__icon"}):t._e()])]):t._e(),s("van-popup",{staticClass:"detail-plan__popup plan-popup",attrs:{round:"",position:"bottom"},on:{close:t.onClose},model:{value:t.show,callback:function(e){t.show=e},expression:"show"}},[s("div",{staticClass:"plan-popup__title"},[s("span"),t._v("\n        选择教学计划\n      ")]),s("div",{staticClass:"plan-popup__type"},t._l(t.goods.specs,(function(e){return s("span",{key:e.id,staticClass:"plan-popup__type__item",class:{active:e.active},on:{click:function(s){return t.handleClick(e)}}},[t._v(t._s(e.title))])})),0),s("div",{staticClass:"plan-popup__other"},[s("div",{staticClass:"popup-other clearfix"},[s("div",{staticClass:"pull-left popup-other__left"},[t._v("学习有效期")]),s("div",{staticClass:"pull-left popup-other__right",domProps:{innerHTML:t._s(t.buyableModeHtml)}})]),t.currentSku.services.length?s("div",{staticClass:"popup-other clearfix"},[s("div",{staticClass:"pull-left popup-other__left"},[t._v("承诺服务")]),s("div",{staticClass:"pull-left popup-other__right"},t._l(t.currentSku.services,(function(e,i){return s("span",{key:i,staticClass:"popup-other__right__promise"},[t._v("练")])})),0)]):t._e()])]),t.currentSku.vipLevelInfo&&t.vipSwitch?s("div",{staticClass:"detail-plan__plan clearfix"},[s("div",{staticClass:"pull-left plan-left"},[t._v("会员免费")]),s("div",{staticClass:"pull-left plan-right"},[s("img",{staticClass:"vip-icon",attrs:{src:t.currentSku.vipLevelInfo.icon,alt:""}}),s("router-link",{staticClass:"color-primary",attrs:{to:{path:"/vip",query:{id:this.currentSku.vipLevelInfo.id}}}},[t._v("\n          "+t._s(t.currentSku.vipLevelInfo.name)+"免费学")])],1)]):t._e(),s("div",{staticClass:"detail-plan__plan clearfix"},[s("div",{staticClass:"pull-left plan-left"},[t._v("学习有效期")]),s("div",{staticClass:"pull-left plan-right",domProps:{innerHTML:t._s(t.buyableModeHtml)}})]),t.currentSku.services.length>0?s("div",{staticClass:"detail-plan__plan clearfix"},[s("div",{staticClass:"pull-left plan-left"},[t._v("承诺服务")]),s("div",{staticClass:"pull-left plan-right"},t._l(t.currentSku.services,(function(e,i){return s("span",{key:i,staticClass:"plan-right__promise"},[t._v(t._s(e.shortName))])})),0)]):t._e()],1):t._e()}),[],!1,null,null,null);e.a=u.exports},7067:function(t,e,s){"use strict";s("e17f");var i=s("2241"),n=(s("e7e5"),s("d399")),r=s("a026"),a=s("3ce7"),c=/micromessenger/.test(navigator.userAgent.toLowerCase());e.a=function(t,e){var s=arguments.length>2&&void 0!==arguments[2]&&arguments[2];if(!t||s&&!e)n.a.fail("缺少分享参数");else{var o={domainUri:location.origin,itemUri:"",source:"h5"};a.a.marketingActivities({query:{activityId:t},data:o}).then((function(t){var a=-1!==t.url.indexOf("?")?"&":"?",o=s?"".concat(e).concat(a,"ticket=").concat(t.ticket):t.url;c?window.location.href=o:i.a.confirm({message:"去微信完成活动",confirmButtonText:"复制链接",title:""}).then((function(){try{r.default.prototype.$copyText(o).then((function(){n.a.success("复制成功")}),(function(){n.a.fail("请更换浏览器复制")}))}catch(t){n.a.fail("请更换浏览器复制")}})).catch((function(){}))})).catch((function(t){n.a.fail(t.message)}))}}},"84e1":function(t,e,s){"use strict";var i={methods:{backToTop:function(){document.documentElement.scrollTop=document.body.scrollTop=0}}},n=s("a6c2"),r=Object(n.a)(i,(function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"back-to-top",on:{click:this.backToTop}},[e("i",{staticClass:"iconfont icon-top"}),e("span",[this._v("顶部")])])}),[],!1,null,null,null);e.a=r.exports},"92d0":function(t,e,s){"use strict";var i={data:function(){return{time:1432111,timeData:{days:0,hours:0,minutes:0,seconds:0}}},props:{goods:{type:Object,default:function(){}},currentSku:{type:Object,default:function(){}}},methods:{onChange:function(t){this.timeData=t},onFinish:function(){}},filters:{formatPrice:function(t){return(Math.round(100*t)/100).toFixed(2)},checkTime:function(t){return t<10&&t>=0&&(t="0".concat(t)),t}},created:function(){var t=this.goods.discount;this.time=1e3*t.endTime-Date.now()}},n=s("a6c2"),r=Object(n.a)(i,(function(){var t=this,e=t.$createElement,s=t._self._c||e;return 0!=t.currentSku.displayPrice?s("div",{staticClass:"detail-discount clearfix"},[s("div",{staticClass:"pull-left detail-discount__left"},[s("span",{staticClass:"text"},[t._v("限时优惠")]),"RMB"===t.currentSku.displayPriceObj.currency?s("span",{staticClass:"price"},[t._v(t._s(t._f("formatPrice")(t.currentSku.displayPriceObj.amount))+"元\n        ")]):t._e(),"coin"===t.currentSku.displayPriceObj.currency?s("span",{staticClass:"price"},[t._v(t._s(t._f("formatPrice")(t.currentSku.displayPriceObj.coinAmount))+"\n    ")]):t._e(),s("s",{staticClass:"original-price"},["RMB"===t.currentSku.priceObj.currency?s("span",[t._v(t._s(t._f("formatPrice")(t.currentSku.priceObj.amount))+"元\n        ")]):t._e(),"coin"===t.currentSku.priceObj.currency?s("span",[t._v(t._s(t._f("formatPrice")(t.currentSku.priceObj.coinAmount))+"\n      ")]):t._e()])]),s("div",{staticClass:"pull-left detail-discount__right"},[s("p",{staticClass:"text"},[t._v("距离结束还剩")]),s("div",{staticClass:"count-down"},[s("van-count-down",{attrs:{"use-slot":"",time:t.time},on:{finish:t.onFinish,change:t.onChange}},[s("span",{staticClass:"day"},[t._v(t._s(t.timeData.days))]),t._v("天 "),s("span",{staticClass:"item"},[t._v(t._s(t._f("checkTime")(t.timeData.hours)))]),t._v(": "),s("span",{staticClass:"item"},[t._v(t._s(t._f("checkTime")(t.timeData.minutes)))]),t._v(":\n        "),s("span",{staticClass:"item"},[t._v(t._s(t._f("checkTime")(t.timeData.seconds)))])])],1)])]):t._e()}),[],!1,null,null,null);e.a=r.exports},9498:function(t,e,s){"use strict";var i={data:function(){return{page:1,page_count:"",courses:[]}},props:{classroomCourses:{type:Array,default:function(){return[]}}},methods:{gotoCourse:function(t){this.$router.push({path:"/course/".concat(t.id)})},loadMore:function(){var t=5*this.page;this.courses=this.courses.concat(this.classroomCourses.slice(t,t+5)),this.page+=1}},watch:{classroomCourses:{immediate:!0,handler:function(t){this.page_count=Math.ceil(t.length/5),this.courses=t.slice(0,5)}}}},n=s("a6c2"),r=Object(n.a)(i,(function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"class-catalog"},[t._l(t.courses,(function(e){return s("div",{key:e.id,staticClass:"class-catalog__item clearfix",on:{click:function(s){return t.gotoCourse(e)}}},[s("div",{staticClass:"item-img pull-left"},[s("img",{attrs:{src:e.courseSet.cover.small,alt:""}})]),s("div",{staticClass:"item-info pull-left"},[s("p",{staticClass:"item-info__title text-overflow"},[t._v("\n        "+t._s(e.courseSet.title)+"\n      ")]),"coin"===e.originPrice2.currency?s("p",{staticClass:"item-info__price"},[t._v(t._s(e.originPrice2.coinAmount)+t._s(e.originPrice2.coinName))]):t._e(),"RMB"===e.originPrice2.currency?s("p",{staticClass:"item-info__price"},[t._v("￥"+t._s(e.originPrice2.amount))]):t._e(),s("p",{staticClass:"item-info__plan clearfix"},[s("span",{staticClass:"pull-left item-info__plan-mw text-overflow"},[t._v(t._s(e.title))]),s("span",{staticClass:"pull-right"},[t._v("共"+t._s(e.compulsoryTaskNum)+"课时")])])])])})),t.page<t.page_count?s("div",{staticClass:"load-more__footer",on:{click:t.loadMore}},[t._v("\n    点击查看更多\n  ")]):t._e(),t.page>=t.page_count?s("div",{staticClass:"load-more__footer"},[t._v("\n    没有更多了\n  ")]):t._e()],2)}),[],!1,null,null,null);e.a=r.exports},b576:function(t,e,s){"use strict";s("8e6e"),s("ac6a"),s("456d"),s("c5f6");var i=s("bd86"),n=s("3ce7"),r=s("2f62");function a(t,e){var s=Object.keys(t);if(Object.getOwnPropertySymbols){var i=Object.getOwnPropertySymbols(t);e&&(i=i.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),s.push.apply(s,i)}return s}function c(t){for(var e=1;e<arguments.length;e++){var s=null!=arguments[e]?arguments[e]:{};e%2?a(Object(s),!0).forEach((function(e){Object(i.a)(t,e,s[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(s)):a(Object(s)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(s,e))}))}return t}var o={data:function(){return{redirect:""}},props:{isFavorite:{type:Boolean,default:!1},goods:{type:Object,default:function(){}},currentSku:{type:Object,default:function(){}}},methods:c(c({},Object(r.c)("course",["joinCourse"])),{},{handleJoin:function(){var t=this;if("member.member_exist"!==this.currentSku.access.code){var e=this.vipAccessToJoin;(this.accessToJoin||e)&&(this.$store.state.token?(Number(this.currentSku.buyable)||e)&&(+this.currentSku.displayPrice&&!e?this.getOrder():("course"===this.goods.type&&this.joinCourse({id:this.currentSku.targetId}).then((function(e){0!==Object.keys(e).length||t.getOrder(),t.$router.push({path:"/course/".concat(t.currentSku.targetId)})})).catch((function(t){})),"classroom"===this.goods.type&&n.a.joinClass({query:{classroomId:this.currentSku.targetId}}).then((function(e){t.$router.push({path:"/classroom/".concat(t.currentSku.targetId)})})).catch((function(t){})))):this.$router.push({name:"login",query:{redirect:this.redirect}}))}else this.$router.push({path:"/".concat(this.goods.type,"/").concat(this.currentSku.targetId)})},addFavorite:function(){n.a.addFavorite({data:{targetType:"goods",targetId:this.$route.params.id}}).then((function(t){}))},removeFavorite:function(){n.a.removeFavorite({data:{targetType:"goods",targetId:this.$route.params.id}}).then((function(t){}))},onFavorite:function(){this.$store.state.token?this.isFavorite?(this.removeFavorite(),this.$emit("update-data",!1)):(this.addFavorite(),this.$emit("update-data",!0)):this.$router.push({name:"login",query:{redirect:this.redirect}})},getOrder:function(){this.$router.push({name:"order",params:{id:this.currentSku.targetId},query:{expiryScope:this.buyableModeHtml,targetType:"".concat(this.goods.type)}})}}),computed:c(c({vipAccessToJoin:function(){var t=!1;return!(!this.currentSku.vipLevelInfo||!this.currentSku.vipUser)&&(this.currentSku.vipLevelInfo.seq<=this.currentSku.vipUser.level.seq&&(t=!(1e3*parseInt(this.currentSku.vipUser.deadline)<(new Date).getTime())),t)},accessToJoin:function(){return"success"===this.currentSku.access.code||"user.not_login"===this.currentSku.access.code||"member.member_exist"===this.currentSku.access.code}},Object(r.e)(["vipSwitch"])),{},{buyableModeHtml:function(){var t=this.goods.member;if(t)return"forever"===this.currentSku.usageMode?"长期有效":0!=t.deadline?t.deadline.slice(0,10)+"之前可学习":"长期有效";switch(this.currentSku.usageMode){case"forever":return"长期有效";case"end_date":return this.formatDate(this.currentSku.usageEndTime.slice(0,10))+"&nbsp;之前可学习";case"days":return this.currentSku.usageDays+"天内可学习";case"date":return this.formatDate(this.currentSku.usageStartTime.slice(0,10))+"&nbsp;~&nbsp;"+this.formatDate(this.currentSku.usageEndTime.slice(0,10));default:return""}}}),created:function(){this.redirect=decodeURIComponent(this.$route.fullPath)}},u=s("a6c2"),l=Object(u.a)(o,(function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"info-buy"},[s("div",{staticClass:"info-buy__collection",on:{click:t.onFavorite}},[t.isFavorite?[s("i",{staticClass:"iconfont icon-aixin1",staticStyle:{color:"#FF7E56"}}),s("span",{staticStyle:{color:"#FF7E56"}},[t._v("已收藏")])]:[s("i",{staticClass:"iconfont icon-aixin"}),s("span",[t._v("收藏")])]],2),t.currentSku.isMember?s("div",{staticClass:"info-buy__btn",on:{click:t.handleJoin}},[t._v("\n    去学习\n  ")]):0!=t.currentSku.displayPrice?s("div",{staticClass:"info-buy__btn",class:t.accessToJoin?"":"disabled",on:{click:t.handleJoin}},[t._v("\n    "+t._s(t._f("filterGoodsBuyStatus")(t.currentSku.access.code,t.goods.type,t.vipAccessToJoin))+"\n  ")]):s("div",{staticClass:"info-buy__btn",class:t.accessToJoin?"":"disabled",on:{click:t.handleJoin}},[t.accessToJoin?s("span",[t._v("免费加入")]):s("span",[t._v(t._s(t._f("filterGoodsBuyStatus")(t.currentSku.access.code,t.goods.type,t.vipAccessToJoin)))])])])}),[],!1,null,null,null);e.a=l.exports},d218:function(t,e,s){"use strict";var i={props:{recommendGoods:{type:Array,default:function(){return[]}},goods:{type:Object,default:function(){}}},methods:{onJump:function(t){t!=this.$route.params.id&&this.$router.push({path:"/goods/".concat(t,"/show")})},onMore:function(){this.$router.push({name:"course"===this.goods.type?"more_course":"more_class"})}},filters:{formatPrice:function(t){return(Math.round(100*t)/100).toFixed(2)}}},n=s("a6c2"),r=Object(n.a)(i,(function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"info-learn"},[s("div",{staticClass:"info-learn__header clearfix"},[t._t("title"),s("span",{staticClass:"header-more pull-right",on:{click:t.onMore}},[t._v("更多"),s("i",{staticClass:"iconfont icon-About"})])],2),s("div",{staticClass:"info-learn__body"},[t.recommendGoods.length?t._l(t.recommendGoods,(function(e){return s("div",{key:e.id,staticClass:"body-item",on:{click:function(s){return t.onJump(e.id)}}},[s("div",{staticClass:"body-item__img"},[s("img",{attrs:{src:e.images.large,alt:""}})]),s("div",{staticClass:"body-item__content"},[s("p",{staticClass:"content-title text-overflow"},[t._v(t._s(e.title))]),e.minDisplayPriceObj.amount==e.maxDisplayPriceObj.amount?s("p",{staticClass:"content-price text-overflow",class:{"is-free":0==Number(e.minDisplayPriceObj.amount)}},[t._v("\n            "+t._s(0==Number(e.maxDisplayPriceObj.amount)?"免费":"RMB"===e.minDisplayPriceObj.currency?"¥"+e.maxDisplayPriceObj.amount:e.minDisplayPriceObj.coinAmount+e.minDisplayPriceObj.coinName)+"\n          ")]):s("p",{staticClass:"content-price text-overflow"},["RMB"===e.minDisplayPriceObj.currency?s("span",{staticClass:"price"},[t._v(t._s(t._f("formatPrice")(e.minDisplayPriceObj.amount))+"元")]):t._e(),"coin"===e.minDisplayPriceObj.currency?s("span",{staticClass:"price"},[t._v(t._s(t._f("formatPrice")(e.minDisplayPriceObj.coinAmount))+"\n            ")]):t._e(),s("span",{staticClass:"detail-right__price__unit"},[t._v("\n              "+t._s(e.minDisplayPriceObj.coinName)+"\n            ")])])])])})):s("div",[t._v("暂时还没有推荐商品哦...")])],2)])}),[],!1,null,null,null);e.a=r.exports},d863:function(t,e,s){"use strict";s("a481");var i=s("7067");e.a={data:function(){return{redirect:""}},created:function(){this.redirect=decodeURIComponent(this.$route.fullPath)},methods:{afterLogin:function(){var t=this,e=this.$route.query.redirect?decodeURIComponent(this.$route.query.redirect):"/",s=this.$route.query.skipUrl?decodeURIComponent(this.$route.query.skipUrl):"",n=this.$route.query.callbackType,r=this.$route.query.activityId,a=decodeURIComponent(this.$route.query.callback);setTimeout((function(){if(n)switch(n){case"marketing":Object(i.a)(r,a)}else s?t.$router.replace({path:e,query:{backUrl:s}}):t.$router.replace({path:e})}),2e3)}}}},e57e:function(t,e,s){"use strict";var i={props:{teachers:{type:Array,default:function(){return[]}}}},n=s("a6c2"),r=Object(n.a)(i,(function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"info-teacher"},[t.teachers.length?t._l(t.teachers,(function(e){return s("div",{key:e.id,staticClass:"info-teacher__item clearfix"},[s("div",{staticClass:"pull-left teacher-img"},[s("img",{attrs:{src:e.avatar.small,alt:""}})]),s("div",{staticClass:"pull-left teacher-info"},[s("p",{staticClass:"teacher-info__name"},[t._v(t._s(e.nickname))]),s("p",{staticClass:"teacher-info__describe text-overflow"},[t._v("\n          "+t._s(e.title)+"\n        ")])])])})):s("div",{staticClass:"info-teacher__item"},[t._v("\n    暂无教师~\n  ")])],2)}),[],!1,null,null,null);e.a=r.exports},e90f:function(t,e,s){"use strict";var i={props:{goods:{type:Object,default:function(){}},currentSku:{type:Object,default:function(){}}},data:function(){return{isShare:!1}},filters:{formatPrice:function(t){return(Math.round(100*t)/100).toFixed(2)}},methods:{onShare:function(){}}},n=s("a6c2"),r=Object(n.a)(i,(function(){var t=this,e=t.$createElement,s=t._self._c||e;return t.goods.id?s("div",{staticClass:"detail-info"},[s("p",{staticClass:"detail-info__title",class:t.isShare&&"detail-info__title--pr"},[t.currentSku.hasCertificate?s("span",{staticClass:"certificate-icon"},[t._v("证")]):t._e(),t._v(t._s(t.goods.title)+"\n    "),t.isShare?s("i",{staticClass:"iconfont icon-fenxiang goods-share",on:{click:t.onShare}}):t._e()]),t.goods.discount&&0!=t.currentSku.displayPrice?s("div",{staticClass:"detail-info__price"},[s("div",{staticClass:"clearfix"},[s("div",{staticClass:"pull-left"},[t._v("\n        优惠价\n        "),"RMB"===t.currentSku.displayPriceObj.currency?s("span",{staticClass:"price"},[t._v(t._s(t._f("formatPrice")(t.currentSku.displayPriceObj.amount))+"元\n        ")]):t._e(),"coin"===t.currentSku.displayPriceObj.currency?s("span",{staticClass:"price"},[t._v(t._s(t._f("formatPrice")(t.currentSku.displayPriceObj.coinAmount))),s("span",{staticClass:"detail-right__price__unit"},[t._v(t._s(t.currentSku.displayPriceObj.coinName))])]):t._e()]),s("div",{staticClass:"pull-right study-num"},[s("i",{staticClass:"iconfont icon-renqi"}),t._v("\n        "+t._s(t.goods.product.target.studentNum)+"人\n      ")])])]):t._e(),t.goods.discount&&0!=t.currentSku.displayPrice?t._e():s("div",{staticClass:"detail-info__price"},[s("div",{staticClass:"clearfix"},[s("div",{staticClass:"pull-left"},[t._v("\n        价格\n        "),"RMB"===t.currentSku.displayPriceObj.currency?s("span",{staticClass:"price"},[t._v(t._s(t._f("formatPrice")(t.currentSku.displayPriceObj.amount))+"元\n        ")]):t._e(),"coin"===t.currentSku.displayPriceObj.currency?s("span",{staticClass:"price"},[t._v(t._s(t._f("formatPrice")(t.currentSku.displayPriceObj.coinAmount))),s("span",{staticClass:"detail-right__price__unit"},[t._v(t._s(t.currentSku.displayPriceObj.coinName))])]):t._e()]),s("div",{staticClass:"pull-right study-num"},[s("i",{staticClass:"iconfont icon-renqi"}),t._v("\n        "+t._s(t.goods.product.target.studentNum)+"人\n      ")])])])]):t._e()}),[],!1,null,null,null);e.a=r.exports}}]);