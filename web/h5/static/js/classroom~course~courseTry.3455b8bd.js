(window.webpackJsonp=window.webpackJsonp||[]).push([["classroom~course~courseTry"],{4127:function(t,e,s){"use strict";var r=s("d233"),i=s("b313"),n=Object.prototype.hasOwnProperty,a={brackets:function(t){return t+"[]"},comma:"comma",indices:function(t,e){return t+"["+e+"]"},repeat:function(t){return t}},o=Array.isArray,c=Array.prototype.push,l=function(t,e){c.apply(t,o(e)?e:[e])},u=Date.prototype.toISOString,p={addQueryPrefix:!1,allowDots:!1,charset:"utf-8",charsetSentinel:!1,delimiter:"&",encode:!0,encoder:r.encode,encodeValuesOnly:!1,formatter:i.formatters[i.default],indices:!1,serializeDate:function(t){return u.call(t)},skipNulls:!1,strictNullHandling:!1},f=function t(e,s,i,n,a,c,u,f,d,h,y,m,v){var b=e;if("function"==typeof u?b=u(s,b):b instanceof Date?b=h(b):"comma"===i&&o(b)&&(b=b.join(",")),null===b){if(n)return c&&!m?c(s,p.encoder,v):s;b=""}if("string"==typeof b||"number"==typeof b||"boolean"==typeof b||r.isBuffer(b))return c?[y(m?s:c(s,p.encoder,v))+"="+y(c(b,p.encoder,v))]:[y(s)+"="+y(String(b))];var g,k=[];if(void 0===b)return k;if(o(u))g=u;else{var w=Object.keys(b);g=f?w.sort(f):w}for(var _=0;_<g.length;++_){var O=g[_];a&&null===b[O]||(o(b)?l(k,t(b[O],"function"==typeof i?i(s,O):s,i,n,a,c,u,f,d,h,y,m,v)):l(k,t(b[O],s+(d?"."+O:"["+O+"]"),i,n,a,c,u,f,d,h,y,m,v)))}return k};t.exports=function(t,e){var s,r=t,c=function(t){if(!t)return p;if(null!==t.encoder&&void 0!==t.encoder&&"function"!=typeof t.encoder)throw new TypeError("Encoder has to be a function.");var e=t.charset||p.charset;if(void 0!==t.charset&&"utf-8"!==t.charset&&"iso-8859-1"!==t.charset)throw new TypeError("The charset option must be either utf-8, iso-8859-1, or undefined");var s=i.default;if(void 0!==t.format){if(!n.call(i.formatters,t.format))throw new TypeError("Unknown format option provided.");s=t.format}var r=i.formatters[s],a=p.filter;return("function"==typeof t.filter||o(t.filter))&&(a=t.filter),{addQueryPrefix:"boolean"==typeof t.addQueryPrefix?t.addQueryPrefix:p.addQueryPrefix,allowDots:void 0===t.allowDots?p.allowDots:!!t.allowDots,charset:e,charsetSentinel:"boolean"==typeof t.charsetSentinel?t.charsetSentinel:p.charsetSentinel,delimiter:void 0===t.delimiter?p.delimiter:t.delimiter,encode:"boolean"==typeof t.encode?t.encode:p.encode,encoder:"function"==typeof t.encoder?t.encoder:p.encoder,encodeValuesOnly:"boolean"==typeof t.encodeValuesOnly?t.encodeValuesOnly:p.encodeValuesOnly,filter:a,formatter:r,serializeDate:"function"==typeof t.serializeDate?t.serializeDate:p.serializeDate,skipNulls:"boolean"==typeof t.skipNulls?t.skipNulls:p.skipNulls,sort:"function"==typeof t.sort?t.sort:null,strictNullHandling:"boolean"==typeof t.strictNullHandling?t.strictNullHandling:p.strictNullHandling}}(e);"function"==typeof c.filter?r=(0,c.filter)("",r):o(c.filter)&&(s=c.filter);var u,d=[];if("object"!=typeof r||null===r)return"";u=e&&e.arrayFormat in a?e.arrayFormat:e&&"indices"in e?e.indices?"indices":"repeat":"indices";var h=a[u];s||(s=Object.keys(r)),c.sort&&s.sort(c.sort);for(var y=0;y<s.length;++y){var m=s[y];c.skipNulls&&null===r[m]||l(d,f(r[m],m,h,c.strictNullHandling,c.skipNulls,c.encode?c.encoder:null,c.filter,c.sort,c.allowDots,c.serializeDate,c.formatter,c.encodeValuesOnly,c.charset))}var v=d.join(c.delimiter),b=!0===c.addQueryPrefix?"?":"";return c.charsetSentinel&&("iso-8859-1"===c.charset?b+="utf8=%26%2310003%3B&":b+="utf8=%E2%9C%93&"),v.length>0?b+v:""}},4328:function(t,e,s){"use strict";var r=s("4127"),i=s("9e6a"),n=s("b313");t.exports={formats:n,parse:i,stringify:r}},"50ad":function(t,e,s){"use strict";var r=s("8171");s.n(r).a},7067:function(t,e,s){"use strict";s("e17f");var r=s("2241"),i=(s("e7e5"),s("d399")),n=s("a026"),a=s("3ce7"),o=/micromessenger/.test(navigator.userAgent.toLowerCase());e.a=function(t,e){var s=arguments.length>2&&void 0!==arguments[2]&&arguments[2];if(!t||s&&!e)i.a.fail("缺少分享参数");else{var c={domainUri:location.origin,itemUri:"",source:"h5"};a.a.marketingActivities({query:{activityId:t},data:c}).then((function(t){var a=-1!==t.url.indexOf("?")?"&":"?",c=s?"".concat(e).concat(a,"ticket=").concat(t.ticket):t.url;o?window.location.href=c:r.a.confirm({message:"去微信完成活动",confirmButtonText:"复制链接",title:""}).then((function(){try{n.default.prototype.$copyText(c).then((function(){i.a.success("复制成功")}),(function(){i.a.fail("请更换浏览器复制")}))}catch(t){i.a.fail("请更换浏览器复制")}})).catch((function(){}))})).catch((function(t){i.a.fail(t.message)}))}}},8171:function(t,e,s){},"9e6a":function(t,e,s){"use strict";var r=s("d233"),i=Object.prototype.hasOwnProperty,n={allowDots:!1,allowPrototypes:!1,arrayLimit:20,charset:"utf-8",charsetSentinel:!1,comma:!1,decoder:r.decode,delimiter:"&",depth:5,ignoreQueryPrefix:!1,interpretNumericEntities:!1,parameterLimit:1e3,parseArrays:!0,plainObjects:!1,strictNullHandling:!1},a=function(t){return t.replace(/&#(\d+);/g,(function(t,e){return String.fromCharCode(parseInt(e,10))}))},o=function(t,e,s){if(t){var r=s.allowDots?t.replace(/\.([^.[]+)/g,"[$1]"):t,n=/(\[[^[\]]*])/g,a=/(\[[^[\]]*])/.exec(r),o=a?r.slice(0,a.index):r,c=[];if(o){if(!s.plainObjects&&i.call(Object.prototype,o)&&!s.allowPrototypes)return;c.push(o)}for(var l=0;null!==(a=n.exec(r))&&l<s.depth;){if(l+=1,!s.plainObjects&&i.call(Object.prototype,a[1].slice(1,-1))&&!s.allowPrototypes)return;c.push(a[1])}return a&&c.push("["+r.slice(a.index)+"]"),function(t,e,s){for(var r=e,i=t.length-1;i>=0;--i){var n,a=t[i];if("[]"===a&&s.parseArrays)n=[].concat(r);else{n=s.plainObjects?Object.create(null):{};var o="["===a.charAt(0)&&"]"===a.charAt(a.length-1)?a.slice(1,-1):a,c=parseInt(o,10);s.parseArrays||""!==o?!isNaN(c)&&a!==o&&String(c)===o&&c>=0&&s.parseArrays&&c<=s.arrayLimit?(n=[])[c]=r:n[o]=r:n={0:r}}r=n}return r}(c,e,s)}};t.exports=function(t,e){var s=function(t){if(!t)return n;if(null!==t.decoder&&void 0!==t.decoder&&"function"!=typeof t.decoder)throw new TypeError("Decoder has to be a function.");if(void 0!==t.charset&&"utf-8"!==t.charset&&"iso-8859-1"!==t.charset)throw new Error("The charset option must be either utf-8, iso-8859-1, or undefined");var e=void 0===t.charset?n.charset:t.charset;return{allowDots:void 0===t.allowDots?n.allowDots:!!t.allowDots,allowPrototypes:"boolean"==typeof t.allowPrototypes?t.allowPrototypes:n.allowPrototypes,arrayLimit:"number"==typeof t.arrayLimit?t.arrayLimit:n.arrayLimit,charset:e,charsetSentinel:"boolean"==typeof t.charsetSentinel?t.charsetSentinel:n.charsetSentinel,comma:"boolean"==typeof t.comma?t.comma:n.comma,decoder:"function"==typeof t.decoder?t.decoder:n.decoder,delimiter:"string"==typeof t.delimiter||r.isRegExp(t.delimiter)?t.delimiter:n.delimiter,depth:"number"==typeof t.depth?t.depth:n.depth,ignoreQueryPrefix:!0===t.ignoreQueryPrefix,interpretNumericEntities:"boolean"==typeof t.interpretNumericEntities?t.interpretNumericEntities:n.interpretNumericEntities,parameterLimit:"number"==typeof t.parameterLimit?t.parameterLimit:n.parameterLimit,parseArrays:!1!==t.parseArrays,plainObjects:"boolean"==typeof t.plainObjects?t.plainObjects:n.plainObjects,strictNullHandling:"boolean"==typeof t.strictNullHandling?t.strictNullHandling:n.strictNullHandling}}(e);if(""===t||null===t||void 0===t)return s.plainObjects?Object.create(null):{};for(var c="string"==typeof t?function(t,e){var s,o={},c=e.ignoreQueryPrefix?t.replace(/^\?/,""):t,l=e.parameterLimit===1/0?void 0:e.parameterLimit,u=c.split(e.delimiter,l),p=-1,f=e.charset;if(e.charsetSentinel)for(s=0;s<u.length;++s)0===u[s].indexOf("utf8=")&&("utf8=%E2%9C%93"===u[s]?f="utf-8":"utf8=%26%2310003%3B"===u[s]&&(f="iso-8859-1"),p=s,s=u.length);for(s=0;s<u.length;++s)if(s!==p){var d,h,y=u[s],m=y.indexOf("]="),v=-1===m?y.indexOf("="):m+1;-1===v?(d=e.decoder(y,n.decoder,f),h=e.strictNullHandling?null:""):(d=e.decoder(y.slice(0,v),n.decoder,f),h=e.decoder(y.slice(v+1),n.decoder,f)),h&&e.interpretNumericEntities&&"iso-8859-1"===f&&(h=a(h)),h&&e.comma&&h.indexOf(",")>-1&&(h=h.split(",")),i.call(o,d)?o[d]=r.combine(o[d],h):o[d]=h}return o}(t,s):t,l=s.plainObjects?Object.create(null):{},u=Object.keys(c),p=0;p<u.length;++p){var f=u[p],d=o(f,c[f],s);l=r.merge(l,d,s)}return r.compact(l)}},b127:function(t,e,s){"use strict";s("8e6e"),s("456d"),s("e7e5");var r=s("d399"),i=(s("6762"),s("2fdb"),s("ac6a"),s("c5f6"),s("bd86")),n=s("2f62"),a=s("0d25"),o=s("faa5"),c=s("d863");function l(t,e){var s=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),s.push.apply(s,r)}return s}function u(t){for(var e=1;e<arguments.length;e++){var s=null!=arguments[e]?arguments[e]:{};e%2?l(Object(s),!0).forEach((function(e){Object(i.a)(t,e,s[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(s)):l(Object(s)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(s,e))}))}return t}s("3ce7");var p={mixins:[c.a],props:{hiddeTitle:{type:Boolean,default:!1},errorMsg:{type:String,default:""}},data:function(){return{directoryArray:[],chapters:[],tasks:[],unit:[],optionalMap:[],unitShow:{},firstLesson:""}},computed:u(u(u({},Object(n.e)("course",{details:function(t){return t.details},joinStatus:function(t){return t.joinStatus},courseLessons:function(t){return t.courseLessons},selectedPlanId:function(t){return t.selectedPlanId}})),Object(n.e)(["courseSettings","user"])),{},{currentCourseType:function(){return Number(this.details.parentId)?"班级":"课程"},liveClass:function(){return function(t){var e=(new Date).getTime(),s=new Date(1e3*t.startTime),r=new Date(1e3*t.endTime);return e<=s?"grey-medium":e>r?"ungenerated"===t.activity.replayStatus?"live-done":"live-replay":"living"}}}),watch:{selectedPlanId:{deep:!0,immediate:!0,handler:function(t){var e=this;if(this.courseLessons.length){var s=0,r=0,i=0;this.directoryArray=this.courseLessons.map((function(t){if(e.firstLesson||(e.firstLesson=t.type),s++,e.$set(t,"show",!0),"chapter"===t.type&&(i++,s=0),"unit"===t.type&&(r=s-1),"lesson"===t.type){var n="chapter"===e.firstLesson?Math.max(i-1,0):Math.max(i,0),a=r;e.$set(t,"show","".concat(n,"-").concat(a))}return t})),this.getTasks(this.directoryArray)}}}},methods:u(u({},Object(n.d)("course",{setSourceType:o.x})),{},{lessonToggle:function(t,e){var s="".concat(t,"-").concat(e);this.$set(this.unitShow,s,!this.unitShow[s])},filterNumber:function(t,e,s){return s?"":"1"===t.isOptional?"选修":e+1},getTasks:function(t){var e=this,s=[],r=0,i=0;this.chapters=[],this.tasks=[],this.unit=[],this.optionalMap=[],t.forEach((function(t){if("lesson"==t.type&&(i++,r=Number(t.isOptional)?++r:r,e.optionalMap[i]=r,t.tasks.forEach((function(t){t.tagStatus=e.getCurrentStatus(t)}))),"chapter"!==t.type){if("unit"===t.type){var n="chapter"===e.firstLesson?e.chapters.length-1:e.chapters.length,a=s.length;e.$set(e.unitShow,"".concat(n,"-").concat(a),!0),e.unit.push(t)}s.push(t)}else s.length>0?(e.tasks.push([].concat(s)),s=[]):e.chapters.length>0&&e.tasks.push([]),e.chapters.push(t)})),this.unit.length&&"chapter"===this.firstLesson||this.$set(this.unitShow,"".concat(0,"-",0),!0),"chapter"!==t[t.length-1].type&&this.tasks.push(s),"chapter"!==t[0].type&&this.chapters.unshift({show:!0})},getCurrentStatus:function(t){return Number(t.isFree)?"is-free":Number(this.details.tryLookable)&&"video"===t.type&&t.activity.mediaStorage?"is-tryLook":""},filterTaskStatus:function(t){return this.details.member||"is-free"!==t.tagStatus?this.details.member||"is-tryLook"!==t.tagStatus?"":"试看":"免费"},lessonCellClick:function(t){if(this.errorMsg||"create"===t.status)this.$emit("showDialog");else if(!this.details.allowAnonymousPreview&&this.$router.push({name:"login",query:{redirect:this.redirect}}),!this.joinStatus&&["is-tryLook","is-free"].includes(t.tagStatus))switch(t.type){case"video":case"audio":this.$router.push({name:"course_try"}),this.setSourceType({sourceType:t.type,taskId:t.id});break;case"doc":case"text":case"ppt":this.$router.push({name:"course_web",query:{courseId:this.selectedPlanId,taskId:t.id,type:t.type,preview:1}});break;default:return Object(r.a)("请先加入".concat(this.currentCourseType))}else this.joinStatus?this.showTypeDetail(t):Object(r.a)("请先加入".concat(this.currentCourseType))},showTypeDetail:function(t){if("published"===t.status)switch(t.type){case"video":"self"===t.mediaSource?this.setSourceType({sourceType:"video",taskId:t.id}):Object(r.a)("暂不支持此类型");break;case"audio":this.setSourceType({sourceType:"audio",taskId:t.id});break;case"text":case"ppt":case"doc":this.$router.push({name:"course_web",query:{courseId:this.selectedPlanId,taskId:t.id,type:t.type}});break;case"live":var e=new Date,s=new Date(1e3*t.endTime),i=(new Date(1e3*t.startTime),!1);if(e>s){if("videoGenerated"==t.activity.replayStatus)return void("self"===t.mediaSource?this.setSourceType({sourceType:"video",taskId:t.id}):Object(r.a)("暂不支持此类型"));if("ungenerated"==t.activity.replayStatus)return void Object(r.a)("暂无回放");i=!0}this.$router.push({name:"live",query:{courseId:this.selectedPlanId,taskId:t.id,type:t.type,title:t.title,replay:i}});break;default:Object(r.a)("暂不支持此类型")}else Object(r.a)("敬请期待")}}),filters:{liveStatusText:function(t){var e=(new Date).getTime(),s=new Date(1e3*t.startTime),r=new Date(1e3*t.endTime);return e<=s?Object(a.formatCompleteTime)(s):e>r?"ungenerated"===t.activity.replayStatus?"已结束":"回放":"直播中"}}},f=s("a6c2"),d=Object(f.a)(p,(function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("e-panel",{staticClass:"directory",attrs:{"hidde-title":t.hiddeTitle,title:"课程目录"}},[0==t.courseLessons.length?s("div",{staticClass:"empty"},[t._v("暂无学习任务")]):s("div",{staticClass:"directory-list"},t._l(t.chapters,(function(e,r){return s("div",{key:r,staticClass:"directory-list__item"},["chapter"===e.type?s("div",{staticClass:"directory-list__item-chapter",on:{click:function(t){e.show=!e.show}}},[s("span",{staticClass:"text-overflow"},[t._v("第"+t._s(e.number)+t._s(t.courseSettings.chapter_name)+"："+t._s(e.title))]),s("i",{class:[e.show?"icon-packup":"icon-unfold"]})]):t._e(),t._l(t.tasks[r],(function(i,n){return s("div",{key:n,class:["directory-list__item-unit",{"unit-show":e.show||e.show&&"lesson"===t.tasks[r][0].type}]},["unit"===i.type?s("div",{staticClass:"lesson-cell__unit"},[s("span",{staticClass:"lesson-cell__unit-title text-overflow"},[t._v("第"+t._s(i.number)+t._s(t.courseSettings.part_name)+"："+t._s(i.title))]),s("i",{class:[t.unitShow[r+"-"+n]?"icon-packup":"icon-unfold"],on:{click:function(e){return t.lessonToggle(r,n)}}})]):t._e(),"lesson"===i.type?s("div",{staticClass:"lesson-cell__hour text-overflow",class:{"lesson-show":t.unitShow[i.show]||"lesson"===t.tasks[r][0].type}},[i.tasks.length>1?s("div",[s("div",{staticClass:"lesson-cell__lesson text-overflow"},[s("i",{staticClass:"h5-icon h5-icon-dot color-primary text-18"}),s("span",[t._v(t._s(Number(i.isOptional)?"选修 ":"课时 ")+"\n                "+t._s(Number(i.isOptional)?" ":i.number-t.optionalMap[i.number]+"：")+t._s(i.title))])]),t._l(i.tasks,(function(e,r){return s("div",{key:r,class:["box","show-box"]},[s("div",{staticClass:"lesson-cell"},[Number(i.isOptional)?t._e():s("span",{staticClass:"lesson-cell__number pull-left"},[t._v(t._s(t.filterNumber(e,r)))]),s("div",{class:["lesson-cell__content","live"===i.tasks[r].type?"pr10":""],on:{click:function(s){return t.lessonCellClick(e)}}},[s("div",{staticClass:"lesson-cell__text"},[s("span",{staticClass:"text-overflow"},[t._v(t._s(e.title))]),"live"===i.tasks[r].type&&"published"===i.tasks[r].status?s("span",{class:[t.liveClass(i.tasks[r]),"live-text","ml5"]},[t._v(t._s(t._f("liveStatusText")(i.tasks[r])))]):t._e()]),s("span",{staticClass:"lesson-cell-last__text"},[t._v(t._s(t._f("taskType")(e))+t._s(t._f("filterTask")(e)))])]),t.details.member?t._e():s("div",{class:["lesson-cell__status",t.details.member?"":e.tagStatus]},[t._v("\n                  "+t._s(t.filterTaskStatus(e))+"\n                ")])])])}))],2):t._e(),1===i.tasks.length?s("div",[s("div",{staticClass:"lesson-cell__lesson text-overflow",on:{click:function(e){return t.lessonCellClick(i.tasks[0])}}},[s("i",{staticClass:"h5-icon h5-icon-dot color-primary text-18 pull-left"}),s("div",{staticClass:"lesson-cell__text "},[s("span",{staticClass:"pl3 text-overflow"},[t._v(t._s(Number(i.isOptional)?"选修 ":"课时 ")+"\n                  "+t._s(Number(i.isOptional)?" ":i.number-t.optionalMap[i.number]+"：")+t._s(i.tasks[0].title))]),"published"===i.tasks[0].status&&"live"===i.tasks[0].type?s("span",{class:[t.liveClass(i.tasks[0]),"live-text","ml5"]},[t._v(t._s(t._f("liveStatusText")(i.tasks[0])))]):t._e()]),s("div",{staticClass:"lesson-cell"},[s("span",{staticClass:"lesson-cell__number"},[t._v(t._s(t.filterNumber(i.tasks[0],0,!0)))]),s("div",{staticClass:"lesson-cell__content pl3"},[s("span",{staticClass:"lesson-cell-last__text"},[t._v(t._s(t._f("taskType")(i.tasks[0]))+t._s(t._f("filterTask")(i.tasks[0])))])]),t.details.member?t._e():s("div",{class:["lesson-cell__status",t.details.member?"":i.tasks[0].tagStatus]},[t._v("\n                  "+t._s(t.filterTaskStatus(i.tasks[0]))+"\n                ")])])])]):t._e()]):t._e()])}))],2)})),0)])}),[],!1,null,null,null);e.a=d.exports},b313:function(t,e,s){"use strict";var r=String.prototype.replace,i=/%20/g;t.exports={default:"RFC3986",formatters:{RFC1738:function(t){return r.call(t,i,"+")},RFC3986:function(t){return t}},RFC1738:"RFC1738",RFC3986:"RFC3986"}},bced:function(t,e,s){"use strict";var r={name:"ETagLink",props:{tagData:{type:Object,default:{earnings:0,isShow:!1,link:"",className:"",minDirectRewardRatio:0}}}},i=s("a6c2"),n=Object(i.a)(r,(function(){var t=this.$createElement,e=this._self._c||t;return this.tagData.isShow?e("div",{staticClass:"e-tag-link",class:this.tagData.className},[e("a",{attrs:{href:this.tagData.link}},[e("span",[this._v("赚"+this._s(this.tagData.earnings)+"元")])])]):this._e()}),[],!1,null,null,null);e.a=n.exports},d233:function(t,e,s){"use strict";var r=Object.prototype.hasOwnProperty,i=Array.isArray,n=function(){for(var t=[],e=0;e<256;++e)t.push("%"+((e<16?"0":"")+e.toString(16)).toUpperCase());return t}(),a=function(t,e){for(var s=e&&e.plainObjects?Object.create(null):{},r=0;r<t.length;++r)void 0!==t[r]&&(s[r]=t[r]);return s};t.exports={arrayToObject:a,assign:function(t,e){return Object.keys(e).reduce((function(t,s){return t[s]=e[s],t}),t)},combine:function(t,e){return[].concat(t,e)},compact:function(t){for(var e=[{obj:{o:t},prop:"o"}],s=[],r=0;r<e.length;++r)for(var n=e[r],a=n.obj[n.prop],o=Object.keys(a),c=0;c<o.length;++c){var l=o[c],u=a[l];"object"==typeof u&&null!==u&&-1===s.indexOf(u)&&(e.push({obj:a,prop:l}),s.push(u))}return function(t){for(;t.length>1;){var e=t.pop(),s=e.obj[e.prop];if(i(s)){for(var r=[],n=0;n<s.length;++n)void 0!==s[n]&&r.push(s[n]);e.obj[e.prop]=r}}}(e),t},decode:function(t,e,s){var r=t.replace(/\+/g," ");if("iso-8859-1"===s)return r.replace(/%[0-9a-f]{2}/gi,unescape);try{return decodeURIComponent(r)}catch(t){return r}},encode:function(t,e,s){if(0===t.length)return t;var r="string"==typeof t?t:String(t);if("iso-8859-1"===s)return escape(r).replace(/%u[0-9a-f]{4}/gi,(function(t){return"%26%23"+parseInt(t.slice(2),16)+"%3B"}));for(var i="",a=0;a<r.length;++a){var o=r.charCodeAt(a);45===o||46===o||95===o||126===o||o>=48&&o<=57||o>=65&&o<=90||o>=97&&o<=122?i+=r.charAt(a):o<128?i+=n[o]:o<2048?i+=n[192|o>>6]+n[128|63&o]:o<55296||o>=57344?i+=n[224|o>>12]+n[128|o>>6&63]+n[128|63&o]:(a+=1,o=65536+((1023&o)<<10|1023&r.charCodeAt(a)),i+=n[240|o>>18]+n[128|o>>12&63]+n[128|o>>6&63]+n[128|63&o])}return i},isBuffer:function(t){return!(!t||"object"!=typeof t||!(t.constructor&&t.constructor.isBuffer&&t.constructor.isBuffer(t)))},isRegExp:function(t){return"[object RegExp]"===Object.prototype.toString.call(t)},merge:function t(e,s,n){if(!s)return e;if("object"!=typeof s){if(i(e))e.push(s);else{if(!e||"object"!=typeof e)return[e,s];(n&&(n.plainObjects||n.allowPrototypes)||!r.call(Object.prototype,s))&&(e[s]=!0)}return e}if(!e||"object"!=typeof e)return[e].concat(s);var o=e;return i(e)&&!i(s)&&(o=a(e,n)),i(e)&&i(s)?(s.forEach((function(s,i){if(r.call(e,i)){var a=e[i];a&&"object"==typeof a&&s&&"object"==typeof s?e[i]=t(a,s,n):e.push(s)}else e[i]=s})),e):Object.keys(s).reduce((function(e,i){var a=s[i];return r.call(e,i)?e[i]=t(e[i],a,n):e[i]=a,e}),o)}}},d863:function(t,e,s){"use strict";s("a481");var r=s("7067");e.a={data:function(){return{redirect:""}},created:function(){this.redirect=decodeURIComponent(this.$route.fullPath)},methods:{afterLogin:function(){var t=this,e=this.$route.query.redirect?decodeURIComponent(this.$route.query.redirect):"/",s=this.$route.query.skipUrl?decodeURIComponent(this.$route.query.skipUrl):"",i=this.$route.query.callbackType,n=this.$route.query.activityId,a=decodeURIComponent(this.$route.query.callback);setTimeout((function(){if(i)switch(i){case"marketing":Object(r.a)(n,a)}else s?t.$router.replace({path:e,query:{backUrl:s}}):t.$router.replace({path:e})}),2e3)}}}},fd23:function(t,e,s){"use strict";s("6b54");var r=s("0d25"),i={props:{activity:{type:Object,default:{}}},data:function(){return{timer:null,counting:!0,seckillClass:"seckill-unstart",seckilling:!1,buyCountDownText:"",endCountDownText:""}},computed:{statusTitle:{get:function(){var t=this.activity.status;if("unstart"===t)return this.counting=!1,"秒杀未开始";if("closed"===t)return this.counting=!1,this.seckillClass="seckill-closed","秒杀已结束";if("ongoing"===t){if(!this.counting)return"秒杀已结束";if(0==this.activity.productRemaind)return this.counting=!1,this.seckillClass="seckill-closed",this.$emit("sellOut",!0),"商品已售空";var e=(new Date).getTime();if(this.startStamp<e&&e<this.endStamp)return this.seckilling=!0,this.counting=!0,this.seckillClass="seckill-ongoing",'距离结束仅剩<span class="ml10 mlm">'.concat(this.endCountDownText,"</span>");if(this.startStamp>e)return this.seckilling=!1,this.counting=!0,this.seckillClass="seckill-unstart",'距离开抢<span class="ml10 mlm">'.concat(this.buyCountDownText,"</span>")}},set:function(){}},startStamp:function(){return new Date(this.activity.startTime).getTime()},endStamp:function(){return new Date(this.activity.endTime).getTime()}},created:function(){this.countDownTime()},beforeDestroy:function(){this.clearInterval()},methods:{countDownTime:function(){var t=this;this.timer=setInterval((function(){t.endCountDownText=Object(r.dateTimeDown)(t.endStamp),t.buyCountDownText=Object(r.dateTimeDown)(t.startStamp),"已到期"==t.endCountDownText&&(t.seckillClass="seckill-closed",t.counting=!1,t.clearInterval(),t.$emit("timesUp"))}),1e3)},clearInterval:function(t){function e(){return t.apply(this,arguments)}return e.toString=function(){return t.toString()},e}((function(){clearInterval(this.timer),this.timer=null}))}},n=(s("50ad"),s("a6c2")),a=Object(n.a)(i,(function(){var t=this.$createElement,e=this._self._c||t;return e("div",{class:["seckill-countdown-container clearfix",this.seckillClass]},[e("span",{staticClass:"pull-left status-title"},[this._v("秒杀"+this._s("ongoing"===this.activity.status&&this.seckilling?"中":""))]),e("div",{staticClass:"pull-right text-12",domProps:{innerHTML:this._s(this.statusTitle)}})])}),[],!1,null,null,null);e.a=a.exports}}]);