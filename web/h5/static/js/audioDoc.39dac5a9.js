(window.webpackJsonp=window.webpackJsonp||[]).push([["audioDoc"],{"32e6":function(t,e){function n(t,e){t.onload=function(){this.onerror=this.onload=null,e(null,t)},t.onerror=function(){this.onerror=this.onload=null,e(new Error("Failed to load "+this.src),t)}}function r(t,e){t.onreadystatechange=function(){"complete"!=this.readyState&&"loaded"!=this.readyState||(this.onreadystatechange=null,e(null,t))}}t.exports=function(t,e,o){var i=document.head||document.getElementsByTagName("head")[0],a=document.createElement("script");"function"==typeof e&&(o=e,e={}),e=e||{},o=o||function(){},a.type=e.type||"text/javascript",a.charset=e.charset||"utf8",a.async=!("async"in e)||!!e.async,a.src=t,e.attrs&&function(t,e){for(var n in e)t.setAttribute(n,e[n])}(a,e.attrs),e.text&&(a.text=""+e.text),("onload"in a?n:r)(a,o),a.onload||n(a,o),i.appendChild(a)}},af59:function(t,e,n){"use strict";n.r(e),n("8e6e"),n("ac6a"),n("456d"),n("e7e5");var r=n("d399"),o=(n("96cf"),n("3b8d")),i=n("bd86"),a=n("32e6"),s=n.n(a),c=n("2f62");function u(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,r)}return n}n("3ce7");var d={data:function(){return{isEncryptionPlus:!1}},computed:function(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?u(Object(n),!0).forEach((function(e){Object(i.a)(t,e,n[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):u(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}({},Object(c.e)("course",{sourceType:function(t){return t.sourceType},selectedPlanId:function(t){return t.selectedPlanId},taskId:function(t){return t.taskId},details:function(t){return t.details},joinStatus:function(t){return t.joinStatus},user:function(t){return t.user}})),created:function(){this.initPlayer()},methods:{getParams:function(){return this.joinStatus?{query:{courseId:this.selectedPlanId,taskId:this.taskId}}:{query:{courseId:this.selectedPlanId,taskId:this.taskId},params:{preview:1}}},initPlayer:function(){var t=this;return Object(o.a)(regeneratorRuntime.mark((function e(){var n,o;return regeneratorRuntime.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:if(t.$refs.audio&&(t.$refs.audio.innerHTML=""),n=t.$route.query,t.isEncryptionPlus=n.isEncryptionPlus,!n.isEncryptionPlus){e.next=6;break}return Object(r.a)("该浏览器不支持云视频播放，请下载App"),e.abrupt("return");case 6:o={id:"course-detail__audio--content",user:t.user,playlist:n.playlist,template:n.text,autoplay:!0,simpleMode:!0},t.$store.commit("UPDATE_LOADING_STATUS",!0),t.loadPlayerSDK().then((function(e){t.$store.commit("UPDATE_LOADING_STATUS",!1);var n=new e(o);n.on("ready",(function(){})),n.on("datapicker.start",(function(t){})),n.on("ended",(function(){})),n.on("timeupdate",(function(t){}))}));case 9:case"end":return e.stop()}}),e)})))()},loadPlayerSDK:function(){if(!window.AudioPlayerSDK){var t="//service-cdn.qiqiuyun.net/js-sdk/audio-player/sdk-v1.js?v="+Date.now()/1e3/60;return new Promise((function(e,n){s()(t,(function(t){t&&n(t),e(window.AudioPlayerSDK)}))}))}return Promise.resolve(window.AudioPlayerSDK)}}},l=n("a6c2"),f=Object(l.a)(d,(function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"course-detail__audio"},[e("div",{directives:[{name:"show",rawName:"v-show",value:!this.isEncryptionPlus,expression:"!isEncryptionPlus"}],ref:"audio",staticClass:"course-detail__audio--content",attrs:{id:"course-detail__audio--content"}})])}),[],!1,null,null,null);e.default=f.exports}}]);