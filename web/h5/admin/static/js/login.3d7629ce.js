(window.webpackJsonp=window.webpackJsonp||[]).push([["login"],{7067:function(e,t,r){"use strict";r("e17f");var n=r("2241"),i=(r("e7e5"),r("d399")),a=r("a026"),s=r("3ce7"),o=/micromessenger/.test(navigator.userAgent.toLowerCase());t.a=function(e,t){var r=arguments.length>2&&void 0!==arguments[2]&&arguments[2];if(!e||r&&!t)i.a.fail("缺少分享参数");else{var c={domainUri:location.origin,itemUri:"",source:"h5"};s.a.marketingActivities({query:{activityId:e},data:c}).then((function(e){var s=-1!==e.url.indexOf("?")?"&":"?",c=r?"".concat(t).concat(s,"ticket=").concat(e.ticket):e.url;o?window.location.href=c:n.a.confirm({message:"去微信完成活动",confirmButtonText:"复制链接",title:""}).then((function(){try{a.default.prototype.$copyText(c).then((function(){i.a.success("复制成功")}),(function(){i.a.fail("请更换浏览器复制")}))}catch(e){i.a.fail("请更换浏览器复制")}})).catch((function(){}))})).catch((function(e){i.a.fail(e.message)}))}}},"89eb":function(e,t,r){"use strict";r.r(t),r("8e6e"),r("ac6a"),r("456d"),r("e7e5");var n=r("d399"),i=r("bd86"),a=r("2f62"),s=r("3ce7");function o(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function c(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?o(Object(r),!0).forEach((function(t){Object(i.a)(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):o(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}var u={data:function(){return{username:"",password:"",faceRegistered:0,errorMessage:{password:""}}},computed:{btnSubmitDisable:function(){return!this.username},btnDisable:function(){return!(this.username&&this.password)}},methods:c(c({},Object(a.c)(["userLogin"])),{},{onSubmitInfo:function(){var e,t=this;e=/^1\d{10}$/.test(this.username)?"mobile":/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(this.username)?"email":"nickname",s.a.getUserIsExisted({query:{type:this.username},params:{identifyType:e}}).then((function(e){e.id?"0"===e.faceRegistered?(t.faceRegistered=e.faceRegistered,Object(n.a)({duration:2e3,message:"初次使用请验证密码"})):t.$router.push({name:"verification",query:{redirect:t.$route.query.redirect||"",loginToken:t.$route.query.loginToken||"",type:"compare",faceRegistered:0,loginField:t.username}}):n.a.fail({duration:2e3,message:"用户不存在"})})).catch((function(e){n.a.fail(e.message)}))},checkName:function(){this.faceRegistered=0},onCheckExisted:function(){var e=this;this.userLogin({username:this.username,password:this.password}).then((function(t){e.$router.push({name:"verification",query:{redirect:e.$route.query.redirect||"",loginToken:e.$route.query.loginToken||"",type:"register",faceRegistered:1}})})).catch((function(e){n.a.fail(e.message)}))}})},l=r("a6c2"),g=Object(l.a)(u,(function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",{staticClass:"login"},[r("span",{staticClass:"login-title"},[e._v("确认账号")]),r("van-field",{staticClass:"login-input e-input",attrs:{placeholder:"请输入邮箱/手机/用户名"},on:{input:e.checkName},model:{value:e.username,callback:function(t){e.username="string"==typeof t?t.trim():t},expression:"username"}}),e.faceRegistered?r("van-field",{staticClass:"login-input e-input",attrs:{"error-message":e.errorMessage.password,type:"password",placeholder:"请输入密码"},model:{value:e.password,callback:function(t){e.password=t},expression:"password"}}):e._e(),e.faceRegistered?r("van-button",{staticClass:"primary-btn mb20",attrs:{disabled:e.btnDisable,type:"default"},on:{click:e.onCheckExisted}},[e._v("下一步")]):r("van-button",{staticClass:"primary-btn mb20",attrs:{disabled:e.btnSubmitDisable,type:"default"},on:{click:e.onSubmitInfo}},[e._v("下一步")])],1)}),[],!1,null,null,null);t.default=g.exports},affc:function(e,t,r){"use strict";r.r(t),r("8e6e"),r("ac6a"),r("456d"),r("a481"),r("c5f6");var n=r("bd86"),i=(r("e7e5"),r("d399")),a=(r("96cf"),r("3b8d")),s=r("d863"),o=r("2f62"),c=r("3ce7");function u(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function l(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?u(Object(r),!0).forEach((function(t){Object(n.a)(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):u(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}var g={mixins:[s.a],data:function(){return{username:"",password:"",errorMessage:{password:""},faceSetting:0,bodyHeight:520,loginConfig:{},cloudSetting:!1}},computed:{btnDisable:function(){return!(this.username&&this.password)},isWeixinBrowser:function(){return/micromessenger/.test(navigator.userAgent.toLowerCase())},canloginConfig:function(){return!this.$route.query||!this.$route.query.forbidWxLogin}},created:function(){var e=this;return Object(a.a)(regeneratorRuntime.mark((function t(){return regeneratorRuntime.wrap((function(t){for(;;)switch(t.prev=t.next){case 0:if(!e.$store.state.token){t.next=4;break}return i.a.loading({message:"请稍后"}),e.afterLogin(),t.abrupt("return");case 4:return t.next=6,c.a.getSettings({query:{type:"register"}}).catch((function(e){i.a.fail(e.message)}));case 6:e.registerSettings=t.sent,e.getsettingsCloud();case 8:case"end":return t.stop()}}),t)})))()},mounted:function(){this.bodyHeight=document.documentElement.clientHeight-46,this.username=this.$route.params.username||this.$route.query.account||"",i.a.loading({message:"请稍后"}),this.faceLogin(),this.thirdPartyLogin()},methods:l(l({},Object(o.c)(["userLogin"])),{},{getsettingsCloud:function(){var e=this;return Object(a.a)(regeneratorRuntime.mark((function t(){return regeneratorRuntime.wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return t.next=2,c.a.settingsCloud().then((function(t){e.cloudSetting=!!t.sms_enabled})).catch((function(e){i.a.fail(e.message)}));case 2:case"end":return t.stop()}}),t)})))()},onSubmit:function(e){var t=this;this.userLogin({username:this.username,password:this.password}).then((function(e){i.a.success({duration:2e3,message:"登录成功"}),t.afterLogin()})).catch((function(e){i.a.fail(e.message)}))},jumpRegister:function(){this.registerSettings&&"closed"!==this.registerSettings.mode&&"email"!==this.registerSettings.mode?this.$router.push({name:"register",query:{redirect:this.$route.query.redirect||"/"}}):Object(i.a)("网校未开启手机注册，请联系管理员")},faceLogin:function(){var e=this;c.a.settingsFace({}).then((function(t){Number(t.login.enabled)?e.faceSetting=Number(t.login.h5_enabled):e.faceSetting=0})).catch((function(e){i.a.fail(e.message)}))},thirdPartyLogin:function(){var e=this;this.canloginConfig&&c.a.loginConfig({}).then((function(t){i.a.clear(),e.loginConfig=t,Number(t.weixinmob_enabled)&&e.isWeixinBrowser&&e.wxLogin()})).catch((function(e){i.a.fail(e.message),i.a.clear()}))},wxLogin:function(){this.$router.replace({path:"/auth/social",query:{type:"wx",weixinmob_key:this.loginConfig.weixinmob_key,redirect:this.$route.query.redirect||"/",callbackType:this.$route.query.callbackType,activityId:this.$route.query.activityId}})},changeLogin:function(){this.$route.query.redirect?this.$router.push({name:"fastlogin",query:{redirect:this.$route.query.redirect}}):this.$router.push({name:"fastlogin"})}})},d=r("a6c2"),f=Object(d.a)(g,(function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",{staticClass:"login",style:{height:e.bodyHeight+"px"}},[r("span",{staticClass:"login-title"},[e._v("登录账号")]),r("img",{staticClass:"login-avatarimg",attrs:{src:""}}),r("van-field",{staticClass:"login-input e-input",attrs:{autosize:{maxHeight:24},border:!1,type:"textarea",placeholder:"邮箱/手机/用户名"},model:{value:e.username,callback:function(t){e.username=t},expression:"username"}}),r("van-field",{staticClass:"login-input e-input",attrs:{border:!1,"error-message":e.errorMessage.password,type:"password",placeholder:"请输入密码"},model:{value:e.password,callback:function(t){e.password=t},expression:"password"}}),r("van-button",{staticClass:"primary-btn mb20",attrs:{disabled:e.btnDisable,type:"info"},on:{click:e.onSubmit}},[e._v("登录")]),r("div",{staticClass:"login-bottom text-center"},[r("router-link",{staticClass:"login-account",attrs:{to:"/setting/password/reset"}},[e._v("忘记密码？  |")]),r("span",{staticClass:"login-account",on:{click:e.jumpRegister}},[e._v("  立即注册  ")]),r("div",{directives:[{name:"show",rawName:"v-show",value:e.cloudSetting,expression:"cloudSetting"}],staticClass:"login-change",on:{click:e.changeLogin}},[r("img",{staticClass:"login_change-icon",attrs:{src:"static/images/login_change.png"}}),e._v("切换手机快捷登录\n    ")])],1)],1)}),[],!1,null,null,null);t.default=f.exports},d863:function(e,t,r){"use strict";r("a481");var n=r("7067");t.a={data:function(){return{redirect:""}},created:function(){this.redirect=decodeURIComponent(this.$route.fullPath)},methods:{afterLogin:function(){var e=this,t=this.$route.query.redirect?decodeURIComponent(this.$route.query.redirect):"/",r=this.$route.query.skipUrl?decodeURIComponent(this.$route.query.skipUrl):"",i=this.$route.query.callbackType,a=this.$route.query.activityId,s=decodeURIComponent(this.$route.query.callback);setTimeout((function(){if(i)switch(i){case"marketing":Object(n.a)(a,s)}else r?e.$router.replace({path:t,query:{backUrl:r}}):e.$router.replace({path:t})}),2e3)}}}}}]);