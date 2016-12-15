/*!
 * ====================================================
 * kity - v2.0.0 - 2014-06-16
 * https://github.com/fex-team/kity
 * GitHub: https://github.com/fex-team/kity.git
 * Copyright (c) 2014 Baidu FEX; Licensed BSD
 * ====================================================
 */
!function(){function a(a,b,c){if(d[a]={exports:{},value:null,factory:null},2===arguments.length&&(c=b),"[object Object]"===d.toString.call(c))d[a].value=c;else{if("function"!=typeof c)throw new Error("define函数未定义的行为");d[a].factory=c}}function b(a){var c=d[a],e=null;return c?c.value?c.value:(e=c.factory.call(null,b,c.exports,c),e&&(c.exports=e),c.value=c.exports,c.value):null}function c(a){return b(a)}var d={};a("animate/animator",["animate/timeline","graphic/eventhandler","animate/frame","core/utils","core/class","animate/easing","graphic/shape","graphic/svg","graphic/styled","graphic/data","graphic/matrix","graphic/pen","graphic/box"],function(a){function b(a){var b=parseFloat(a,10);return/ms/.test(a)?b:/s/.test(a)?1e3*b:/min/.test(a)?60*b*1e3:b}var c=a("animate/timeline"),d=a("animate/easing"),e=a("core/class").createClass("Animator",{constructor:function(a,b,c){if(1==arguments.length){var d=arguments[0];this.beginValue=d.beginValue,this.finishValue=d.finishValue,this.setter=d.setter}else this.beginValue=a,this.finishValue=b,this.setter=c},start:function(a,c,d,e,f){4===arguments.length&&"function"==typeof e&&(f=e,e=0);var g=this.create(a,c,d,f);return e=b(e),e>0?setTimeout(function(){g.play()},e):g.play(),g},create:function(a,f,g,h){var i;return f=f&&b(f)||e.DEFAULT_DURATION,g=g||e.DEFAULT_EASING,"string"==typeof g&&(g=d[g]),i=new c(this,a,f,g),"function"==typeof h&&i.on("finish",h),i},reverse:function(){return new e(this.finishValue,this.beginValue,this.setter)}});e.DEFAULT_DURATION=300,e.DEFAULT_EASING="linear";var f=a("graphic/shape");return a("core/class").extendClass(f,{animate:function(a,b,c,d,e){function f(){g.shift(),g.length&&setTimeout(g[0].t.play.bind(g[0].t),g[0].d)}var g=this._KityAnimateQueue=this._KityAnimateQueue||[],h=a.create(this,b,c,e);return h.on("finish",f),g.push({t:h,d:d}),1==g.length&&setTimeout(h.play.bind(h),d),this},timeline:function(){return this._KityAnimateQueue[0].t},stop:function(){var a=this._KityAnimateQueue;if(a)for(;a.length;)a.shift().stop()}}),e}),a("animate/easing",[],function(){var a={linear:function(a,b,c,d){return c*(a/d)+b},swing:function(b,c,d,e){return a.easeOutQuad(b,c,d,e)},ease:function(b,c,d,e){return a.easeInOutCubic(b,c,d,e)},easeInQuad:function(a,b,c,d){return c*(a/=d)*a+b},easeOutQuad:function(a,b,c,d){return-c*(a/=d)*(a-2)+b},easeInOutQuad:function(a,b,c,d){return(a/=d/2)<1?c/2*a*a+b:-c/2*(--a*(a-2)-1)+b},easeInCubic:function(a,b,c,d){return c*(a/=d)*a*a+b},easeOutCubic:function(a,b,c,d){return c*((a=a/d-1)*a*a+1)+b},easeInOutCubic:function(a,b,c,d){return(a/=d/2)<1?c/2*a*a*a+b:c/2*((a-=2)*a*a+2)+b},easeInQuart:function(a,b,c,d){return c*(a/=d)*a*a*a+b},easeOutQuart:function(a,b,c,d){return-c*((a=a/d-1)*a*a*a-1)+b},easeInOutQuart:function(a,b,c,d){return(a/=d/2)<1?c/2*a*a*a*a+b:-c/2*((a-=2)*a*a*a-2)+b},easeInQuint:function(a,b,c,d){return c*(a/=d)*a*a*a*a+b},easeOutQuint:function(a,b,c,d){return c*((a=a/d-1)*a*a*a*a+1)+b},easeInOutQuint:function(a,b,c,d){return(a/=d/2)<1?c/2*a*a*a*a*a+b:c/2*((a-=2)*a*a*a*a+2)+b},easeInSine:function(a,b,c,d){return-c*Math.cos(a/d*(Math.PI/2))+c+b},easeOutSine:function(a,b,c,d){return c*Math.sin(a/d*(Math.PI/2))+b},easeInOutSine:function(a,b,c,d){return-c/2*(Math.cos(Math.PI*a/d)-1)+b},easeInExpo:function(a,b,c,d){return 0===a?b:c*Math.pow(2,10*(a/d-1))+b},easeOutExpo:function(a,b,c,d){return a==d?b+c:c*(-Math.pow(2,-10*a/d)+1)+b},easeInOutExpo:function(a,b,c,d){return 0===a?b:a==d?b+c:(a/=d/2)<1?c/2*Math.pow(2,10*(a-1))+b:c/2*(-Math.pow(2,-10*--a)+2)+b},easeInCirc:function(a,b,c,d){return-c*(Math.sqrt(1-(a/=d)*a)-1)+b},easeOutCirc:function(a,b,c,d){return c*Math.sqrt(1-(a=a/d-1)*a)+b},easeInOutCirc:function(a,b,c,d){return(a/=d/2)<1?-c/2*(Math.sqrt(1-a*a)-1)+b:c/2*(Math.sqrt(1-(a-=2)*a)+1)+b},easeInElastic:function(a,b,c,d){var e=1.70158,f=0,g=c;return 0===a?b:1==(a/=d)?b+c:(f||(f=.3*d),g<Math.abs(c)?(g=c,e=f/4):e=f/(2*Math.PI)*Math.asin(c/g),-(g*Math.pow(2,10*(a-=1))*Math.sin(2*(a*d-e)*Math.PI/f))+b)},easeOutElastic:function(a,b,c,d){var e=1.70158,f=0,g=c;return 0===a?b:1==(a/=d)?b+c:(f||(f=.3*d),g<Math.abs(c)?(g=c,e=f/4):e=f/(2*Math.PI)*Math.asin(c/g),g*Math.pow(2,-10*a)*Math.sin(2*(a*d-e)*Math.PI/f)+c+b)},easeInOutElastic:function(a,b,c,d){var e=1.70158,f=0,g=c;if(0===a)return b;if(2==(a/=d/2))return b+c;if(f||(f=.3*d*1.5),g<Math.abs(c)){g=c;var e=f/4}else var e=f/(2*Math.PI)*Math.asin(c/g);return 1>a?-.5*g*Math.pow(2,10*(a-=1))*Math.sin(2*(a*d-e)*Math.PI/f)+b:g*Math.pow(2,-10*(a-=1))*Math.sin(2*(a*d-e)*Math.PI/f)*.5+c+b},easeInBack:function(a,b,c,d,e){return void 0==e&&(e=1.70158),c*(a/=d)*a*((e+1)*a-e)+b},easeOutBack:function(a,b,c,d,e){return void 0==e&&(e=1.70158),c*((a=a/d-1)*a*((e+1)*a+e)+1)+b},easeInOutBack:function(a,b,c,d,e){return void 0==e&&(e=1.70158),(a/=d/2)<1?c/2*a*a*(((e*=1.525)+1)*a-e)+b:c/2*((a-=2)*a*(((e*=1.525)+1)*a+e)+2)+b},easeInBounce:function(b,c,d,e){return d-a.easeOutBounce(e-b,0,d,e)+c},easeOutBounce:function(a,b,c,d){return(a/=d)<1/2.75?7.5625*c*a*a+b:2/2.75>a?c*(7.5625*(a-=1.5/2.75)*a+.75)+b:2.5/2.75>a?c*(7.5625*(a-=2.25/2.75)*a+.9375)+b:c*(7.5625*(a-=2.625/2.75)*a+.984375)+b},easeInOutBounce:function(b,c,d,e){return e/2>b?.5*a.easeInBounce(2*b,0,d,e)+c:.5*a.easeOutBounce(2*b-e,0,d,e)+.5*d+c}};return a}),a("animate/frame",[],function(a,b){function c(a){1===j.push(a)&&i(d)}function d(){var a=j;for(j=[];a.length;)h(a.pop())}function e(a){var b=g(a);return c(b),b}function f(a){var b=j.indexOf(a);~b&&j.splice(b,1)}function g(a){var b={index:0,time:+new Date,elapsed:0,action:a,next:function(){c(b)}};return b}function h(a){var b=+new Date,c=b-a.time;c>200&&(c=1e3/60),a.dur=c,a.elapsed+=c,a.time=b,a.action.call(null,a),a.index++}var i=window.requestAnimationFrame||window.mozRequestAnimationFrame||window.webkitRequestAnimationFrame||window.msRequestAnimationFrame||function(a){return setTimeout(a,1e3/60)},j=[];b.requestFrame=e,b.releaseFrame=f}),a("animate/motionanimator",["animate/animator","animate/timeline","animate/easing","core/class","graphic/shape","graphic/geometry","core/utils","graphic/point","graphic/vector","graphic/matrix","graphic/path","graphic/svg"],function(a){var b=a("animate/animator"),c=a("graphic/geometry"),d=a("graphic/path"),e=a("core/class").createClass("MotionAnimator",{base:b,constructor:function(a){var b=this;this.callBase({beginValue:0,finishValue:1,setter:function(a,e){var f=b.motionPath instanceof d?b.motionPath.getPathData():b.motionPath,g=c.pointAtPath(f,e);a.setTranslate(g.x,g.y),a.setRotate(g.tan.getAngle())}}),this.updatePath(a)},updatePath:function(a){this.motionPath=a}});return a("core/class").extendClass(d,{motion:function(a,b,c,d,f){return this.animate(new e(a),b,c,d,f)}}),e}),a("animate/opacityanimator",["animate/animator","animate/timeline","animate/easing","core/class","graphic/shape","graphic/svg","core/utils","graphic/eventhandler","graphic/styled","graphic/data","graphic/matrix","graphic/pen","graphic/box"],function(a){var b=a("animate/animator"),c=a("core/class").createClass("OpacityAnimator",{base:b,constructor:function(a){this.callBase({beginValue:function(a){return a.getOpacity()},finishValue:a,setter:function(a,b){a.setOpacity(b)}})}}),d=a("graphic/shape");return a("core/class").extendClass(d,{fxOpacity:function(a,b,d,e,f){return this.animate(new c(a),b,d,e,f)},fadeTo:function(){return this.fxOpacity.apply(this,arguments)},fadeIn:function(){return this.fxOpacity.apply(this,[1].concat([].slice.call(arguments)))},fadeOut:function(){return this.fxOpacity.apply(this,[0].concat([].slice.call(arguments)))}}),c}),a("animate/pathanimator",["animate/animator","animate/timeline","animate/easing","core/class","graphic/shape","graphic/geometry","core/utils","graphic/point","graphic/vector","graphic/matrix","graphic/path","graphic/svg"],function(a){var b=a("animate/animator"),c=a("graphic/geometry"),d=a("core/class").createClass("OpacityAnimator",{base:b,constructor:function(a){this.callBase({beginValue:function(a){return this.beginPath=a.getPathData(),0},finishValue:1,setter:function(b,d){b.setPathData(c.pathTween(this.beginPath,a,d))}})}}),e=a("graphic/path");return a("core/class").extendClass(e,{fxPath:function(a,b,c,e,f){return this.animate(new d(a),b,c,e,f)}}),d}),a("animate/rotateanimator",["animate/animator","animate/timeline","animate/easing","core/class","graphic/shape","graphic/svg","core/utils","graphic/eventhandler","graphic/styled","graphic/data","graphic/matrix","graphic/pen","graphic/box"],function(a){var b=a("animate/animator"),c=a("core/class").createClass("RotateAnimator",{base:b,constructor:function(a,b,c){this.callBase({beginValue:0,finishValue:a,setter:function(a,d,e){var f=e.getDelta();a.rotate(f,b,c)}})}}),d=a("graphic/shape");return a("core/class").extendClass(d,{fxRotate:function(a,b,d,e,f){return this.animate(new c(a),b,d,e,f)},fxRotateAnchor:function(a,b,d,e,f,g,h){return this.animate(new c(a,b,d),e,f,g,h)}}),c}),a("animate/scaleanimator",["animate/animator","animate/timeline","animate/easing","core/class","graphic/shape","graphic/svg","core/utils","graphic/eventhandler","graphic/styled","graphic/data","graphic/matrix","graphic/pen","graphic/box"],function(a){var b=a("animate/animator"),c=a("core/class").createClass("ScaleAnimator",{base:b,constructor:function(a,b){this.callBase({beginValue:0,finishValue:1,setter:function(c,d,e){var f=e.getDelta(),g=Math.pow(a,f),h=Math.pow(b,f);c.scale(h,g)}})}}),d=a("graphic/shape");return a("core/class").extendClass(d,{fxScale:function(a,b,d,e,f,g){return this.animate(new c(a,b),d,e,f,g)}}),c}),a("animate/timeline",["graphic/eventhandler","core/utils","graphic/shapeevent","core/class","animate/frame"],function(a){function b(a,b,c){return g.paralle(a,b,function(a,b){return a+(b-a)*c})}function c(a,b){return g.paralle(a,b,function(a,b){return b-a})}function d(a,b,c){this.timeline=a,this.target=a.target,this.type=b;for(var d in c)c.hasOwnProperty(d)&&(this[d]=c[d])}var e=a("graphic/eventhandler"),f=a("animate/frame"),g=a("core/utils"),h=a("core/class").createClass("Timeline",{mixins:[e],constructor:function(a,b,c,d){this.callMixin(),this.target=b,this.time=0,this.duration=c,this.easing=d,this.animator=a,this.beginValue=a.beginValue,this.finishValue=a.finishValue,this.setter=a.setter,this.status="ready"},nextFrame:function(a){"playing"==this.status&&(this.time+=a.dur,this.setValue(this.getValue()),this.time>=this.duration&&this.timeUp(),a.next())},getPlayTime:function(){return this.rollbacking?this.duration-this.time:this.time},getTimeProportion:function(){return this.getPlayTime()/this.duration},getValueProportion:function(){return this.easing(this.getPlayTime(),0,1,this.duration)},getValue:function(){var a=this.beginValue,c=this.finishValue,d=this.getValueProportion();return b(a,c,d)},setValue:function(a){this.lastValue=this.currentValue,this.currentValue=a,this.setter.call(this.target,this.target,a,this)},getDelta:function(){return this.lastValue=void 0===this.lastValue?this.beginValue:this.lastValue,c(this.lastValue,this.currentValue)},play:function(){var a=this.status;switch(this.status="playing",a){case"ready":g.isFunction(this.beginValue)&&(this.beginValue=this.beginValue.call(this.target,this.target)),g.isFunction(this.finishValue)&&(this.finishValue=this.finishValue.call(this.target,this.target)),this.time=0,this.frame=f.requestFrame(this.nextFrame.bind(this));break;case"finished":case"stoped":this.time=0,this.frame=f.requestFrame(this.nextFrame.bind(this));break;case"paused":this.frame.next()}return this.fire("play",new d(this,"play",{lastStatus:a})),this},pause:function(){return this.status="paused",this.fire("pause",new d(this,"pause")),f.releaseFrame(this.frame),this},stop:function(){return this.status="stoped",this.setValue(this.finishValue),this.rollbacking=!1,this.fire("stop",new d(this,"stop")),f.releaseFrame(this.frame),this},timeUp:function(){this.repeatOption?(this.time=0,this.rollback?this.rollbacking?(this.decreaseRepeat(),this.rollbacking=!1):(this.rollbacking=!0,this.fire("rollback",new d(this,"rollback"))):this.decreaseRepeat(),this.repeatOption?this.fire("repeat",new d(this,"repeat")):this.finish()):this.finish()},finish:function(){this.setValue(this.finishValue),this.status="finished",this.fire("finish",new d(this,"finish")),f.releaseFrame(this.frame)},decreaseRepeat:function(){this.repeatOption!==!0&&this.repeatOption--},repeat:function(a,b){return this.repeatOption=a,this.rollback=b,this}});return h.requestFrame=f.requestFrame,h.releaseFrame=f.releaseFrame,h}),a("animate/translateanimator",["animate/animator","animate/timeline","animate/easing","core/class","graphic/shape","graphic/svg","core/utils","graphic/eventhandler","graphic/styled","graphic/data","graphic/matrix","graphic/pen","graphic/box"],function(a){var b=a("animate/animator"),c=a("core/class").createClass("TranslateAnimator",{base:b,constructor:function(a,b){this.callBase({x:0,y:0},{x:a,y:b},function(a,b,c){var d=c.getDelta();a.translate(d.x,d.y)})}}),d=a("graphic/shape");return a("core/class").extendClass(d,{fxTranslate:function(a,b,d,e,f,g){return this.animate(new c(a,b),d,e,f,g)}}),c}),a("core/browser",[],function(){var a=function(){var a,b=navigator.userAgent.toLowerCase(),c=window.opera;a={ie:/(msie\s|trident.*rv:)([\w.]+)/.test(b),opera:!!c&&c.version,webkit:b.indexOf(" applewebkit/")>-1,mac:b.indexOf("macintosh")>-1,quirks:"BackCompat"==document.compatMode},a.gecko="Gecko"==navigator.product&&!a.webkit&&!a.opera&&!a.ie;var d=0;if(a.ie&&(d=1*(b.match(/(msie\s|trident.*rv:)([\w.]+)/)[2]||0),a.ie11Compat=11==document.documentMode,a.ie9Compat=9==document.documentMode),a.gecko){var e=b.match(/rv:([\d\.]+)/);e&&(e=e[1].split("."),d=1e4*e[0]+100*(e[1]||0)+1*(e[2]||0))}return/chrome\/(\d+\.\d)/i.test(b)&&(a.chrome=+RegExp.$1),/(\d+\.\d)?(?:\.\d)?\s+safari\/?(\d+\.\d+)?/i.test(b)&&!/chrome/i.test(b)&&(a.safari=+(RegExp.$1||RegExp.$2)),a.opera&&(d=parseFloat(c.version())),a.webkit&&(d=parseFloat(b.match(/ applewebkit\/(\d+)/)[1])),a.version=d,a.isCompatible=!a.mobile&&(a.ie&&d>=6||a.gecko&&d>=10801||a.opera&&d>=9.5||a.air&&d>=1||a.webkit&&d>=522||!1),a}();return a}),a("core/class",[],function(a,b){function c(){}function d(a,b){var c=a.toString();if(!/this\.callBase/.test(c))throw new Error(b+" : 类构造函数没有调用父类的构造函数！为了安全，请调用父类的构造函数")}function e(a,b){var c=function(a){a!=h&&c.__KityConstructor.apply(this,arguments),this.__KityClassName=c.__KityClassName};c.__KityConstructor=a,c.prototype=new b(h);for(var d in b.prototype)b.prototype.hasOwnProperty(d)&&0!==d.indexOf("__Kity")&&(c.prototype[d]=b.prototype[d]);return c.prototype.constructor=c,c}function f(a,b){if(!1==b instanceof Array)return a;var c,d,e,f=b.length;for(a.__KityMixins={constructor:[]},c=0;f>c;c++){d=b[c].prototype;for(e in d)!1!==d.hasOwnProperty(e)&&0!==e.indexOf("__Kity")&&("constructor"===e?a.__KityMixins.constructor.push(d[e]):a.prototype[e]=a.__KityMixins[e]=d[e])}return a}function g(a,b){b.__KityClassName&&(b=b.prototype);for(var c in b)if(b.hasOwnProperty(c)&&c.indexOf("__Kity")&&"constructor"!=c){var d=a.prototype[c]=b[c];d.__KityMethodClass=a,d.__KityMethodName=c}return a}Function.prototype.bind=Function.prototype.bind||function(a){var b=Array.prototype.slice.call(arguments,1);return this.apply(a,b)},c.__KityClassName="Class",c.prototype.base=function(a){var b=arguments.callee.caller,c=b.__KityMethodClass.__KityBaseClass.prototype[a];return c.apply(this,Array.prototype.slice.call(arguments,1))},c.prototype.callBase=function(){var a=arguments.callee.caller,b=a.__KityMethodClass.__KityBaseClass.prototype[a.__KityMethodName];return b.apply(this,arguments)},c.prototype.mixin=function(a){var b=arguments.callee.caller,c=b.__KityMethodClass.__KityMixins;if(!c)return this;var d=c[a];return d.apply(this,Array.prototype.slice.call(arguments,1))},c.prototype.callMixin=function(){var a=arguments.callee.caller,b=a.__KityMethodName,c=a.__KityMethodClass.__KityMixins;if(!c)return this;var d=c[b];if("constructor"==b){for(var e=0,f=d.length;f>e;e++)d[e].call(this);return this}return d.apply(this,arguments)},c.prototype.pipe=function(a){return"function"==typeof a&&a.call(this,this),this},c.prototype.getType=function(){return this.__KityClassName},c.prototype.getClass=function(){return this.constructor};var h="__KITY_INHERIT_FLAG_"+ +new Date;c.prototype._accessProperty=function(){return this._propertyRawData||(this._propertyRawData={})},b.createClass=function(a,b){var h,i,j;return 1===arguments.length&&(b=arguments[0],a="AnonymousClass"),j=b.base||c,b.hasOwnProperty("constructor")?(h=b.constructor,j!=c&&d(h,a)):h=function(){this.callBase.apply(this,arguments),this.callMixin.apply(this,arguments)},i=e(h,j,a),i=f(i,b.mixins),i.__KityClassName=h.__KityClassName=a,i.__KityBaseClass=h.__KityBaseClass=j,i.__KityMethodName=h.__KityMethodName="constructor",i.__KityMethodClass=h.__KityMethodClass=i,delete b.mixins,delete b.constructor,delete b.base,i=g(i,b)},b.extendClass=g}),a("core/utils",[],function(){var a={each:function(a,b,c){if(null!==a)if(a.length===+a.length){for(var d=0,e=a.length;e>d;d++)if(b.call(c,a[d],d,a)===!1)return!1}else for(var f in a)if(a.hasOwnProperty(f)&&b.call(c,a[f],f,a)===!1)return!1},extend:function(a){for(var b=arguments,c=this.isBoolean(b[b.length-1])?b[b.length-1]:!1,d=this.isBoolean(b[b.length-1])?b.length-1:b.length,e=1;d>e;e++){var f=b[e];for(var g in f)c&&a.hasOwnProperty(g)||(a[g]=f[g])}return a},deepExtend:function(a){for(var b=arguments,c=this.isBoolean(b[b.length-1])?b[b.length-1]:!1,d=this.isBoolean(b[b.length-1])?b.length-1:b.length,e=1;d>e;e++){var f=b[e];for(var g in f)c&&a.hasOwnProperty(g)||(this.isObject(a[g])&&this.isObject(f[g])?this.deepExtend(a[g],f[g],c):a[g]=f[g])}return a},clone:function(a){var b={};for(var c in a)a.hasOwnProperty(c)&&(b[c]=a[c]);return b},copy:function(a){return"object"!=typeof a?a:"function"==typeof a?null:JSON.parse(JSON.stringify(a))},queryPath:function(a,b){for(var c=a.split("."),d=0,e=b,f=c.length;f>d;){if(!(c[d]in e))return void 0;if(e=e[c[d]],d++,d>=f||void 0===e)return e}},getValue:function(a,b){return void 0!==a?a:b},flatten:function(b){var c,d=[],e=b.length;for(c=0;e>c;c++)b[c]instanceof Array?d=d.concat(a.flatten(b[c])):d.push(b[c]);return d},paralle:function(b,c,d){var e,f,g,h;if(b instanceof Array){for(h=[],f=0;f<b.length;f++)h.push(a.paralle(b[f],c[f],d));return h}if(b instanceof Object){if(e=b.getClass&&b.getClass(),e&&e.parse)b=b.valueOf(),c=c.valueOf(),h=a.paralle(b,c,d),h=e.parse(h);else{h={};for(g in b)b.hasOwnProperty(g)&&c.hasOwnProperty(g)&&(h[g]=a.paralle(b[g],c[g],d))}return h}return!1===isNaN(parseFloat(b))?d(b,c):h},parallelize:function(b){return function(c,d){return a.paralle(c,d,b)}}};return a.each(["String","Function","Array","Number","RegExp","Object","Boolean"],function(b){a["is"+b]=function(a){return Object.prototype.toString.apply(a)=="[object "+b+"]"}}),a}),a("filter/effect/colormatrixeffect",["filter/effect/effect","graphic/svg","core/class","core/utils"],function(a){var b=a("filter/effect/effect"),c=a("core/utils"),d=a("core/class").createClass("ColorMatrixEffect",{base:b,constructor:function(a,e){this.callBase(b.NAME_COLOR_MATRIX),this.set("type",c.getValue(a,d.TYPE_MATRIX)),this.set("in",c.getValue(e,b.INPUT_SOURCE_GRAPHIC))}});return c.extend(d,{TYPE_MATRIX:"matrix",TYPE_SATURATE:"saturate",TYPE_HUE_ROTATE:"hueRotate",TYPE_LUMINANCE_TO_ALPHA:"luminanceToAlpha",MATRIX_ORIGINAL:"10000010000010000010".split("").join(" "),MATRIX_EMPTY:"00000000000000000000".split("").join(" ")}),d}),a("filter/effect/compositeeffect",["filter/effect/effect","graphic/svg","core/class","core/utils"],function(a){var b=a("filter/effect/effect"),c=a("core/utils"),d=a("core/class").createClass("CompositeEffect",{base:b,constructor:function(a,e,f){this.callBase(b.NAME_COMPOSITE),this.set("operator",c.getValue(a,d.OPERATOR_OVER)),e&&this.set("in",e),f&&this.set("in2",f)}});return c.extend(d,{OPERATOR_OVER:"over",OPERATOR_IN:"in",OPERATOR_OUT:"out",OPERATOR_ATOP:"atop",OPERATOR_XOR:"xor",OPERATOR_ARITHMETIC:"arithmetic"}),d}),a("filter/effect/convolvematrixeffect",["filter/effect/effect","graphic/svg","core/class","core/utils"],function(a){var b=a("filter/effect/effect"),c=a("core/utils"),d=a("core/class").createClass("ConvolveMatrixEffect",{base:b,constructor:function(a,e){this.callBase(b.NAME_CONVOLVE_MATRIX),this.set("edgeMode",c.getValue(a,d.MODE_DUPLICATE)),this.set("in",c.getValue(e,b.INPUT_SOURCE_GRAPHIC))}});return c.extend(d,{MODE_DUPLICATE:"duplicate",MODE_WRAP:"wrap",MODE_NONE:"none"}),d}),a("filter/effect/effect",["graphic/svg","core/class","core/utils"],function(a){var b=a("graphic/svg"),c=a("core/class").createClass("Effect",{constructor:function(a){this.node=b.createNode(a)},getId:function(){return this.node.id},setId:function(a){return this.node.id=a,this},set:function(a,b){return this.node.setAttribute(a,b),this},get:function(a){return this.node.getAttribute(a)},getNode:function(){return this.node},toString:function(){return this.node.getAttribute("result")||""}});return a("core/utils").extend(c,{NAME_GAUSSIAN_BLUR:"feGaussianBlur",NAME_OFFSET:"feOffset",NAME_COMPOSITE:"feComposite",NAME_COLOR_MATRIX:"feColorMatrix",NAME_CONVOLVE_MATRIX:"feConvolveMatrix",INPUT_SOURCE_GRAPHIC:"SourceGraphic",INPUT_SOURCE_ALPHA:"SourceAlpha",INPUT_BACKGROUND_IMAGE:"BackgroundImage",INPUT_BACKGROUND_ALPHA:"BackgroundAlpha",INPUT_FILL_PAINT:"FillPaint",INPUT_STROKE_PAINT:"StrokePaint"}),c}),a("filter/effect/gaussianblureffect",["filter/effect/effect","graphic/svg","core/class","core/utils"],function(a){var b=a("filter/effect/effect"),c=a("core/utils");return a("core/class").createClass("GaussianblurEffect",{base:b,constructor:function(a,d){this.callBase(b.NAME_GAUSSIAN_BLUR),this.set("stdDeviation",c.getValue(a,1)),this.set("in",c.getValue(d,b.INPUT_SOURCE_GRAPHIC))}})}),a("filter/effect/offseteffect",["filter/effect/effect","graphic/svg","core/class","core/utils"],function(a){var b=a("filter/effect/effect"),c=a("core/utils");return a("core/class").createClass("OffsetEffect",{base:b,constructor:function(a,d,e){this.callBase(b.NAME_OFFSET),this.set("dx",c.getValue(a,0)),this.set("dy",c.getValue(d,0)),this.set("in",c.getValue(e,b.INPUT_SOURCE_GRAPHIC))}})}),a("filter/effectcontainer",["core/class","graphic/container"],function(a){return a("core/class").createClass("EffectContainer",{base:a("graphic/container"),addEffect:function(){return this.addItem.apply(this,arguments)},prependEffect:function(){return this.prependItem.apply(this,arguments)},appendEffect:function(){return this.appendItem.apply(this,arguments)},removeEffect:function(){return this.removeItem.apply(this,arguments)},addEffects:function(){return this.addItems.apply(this,arguments)},setEffects:function(){return this.setItems.apply(this,arguments)},getEffect:function(){return this.getItem.apply(this,arguments)},getEffects:function(){return this.getItems.apply(this,arguments)},getFirstEffect:function(){return this.getFirstItem.apply(this,arguments)},getLastEffect:function(){return this.getLastItem.apply(this,arguments)},handleAdd:function(a,b){var c=this.getEffects().length,d=this.getItem(b+1);return c===b+1?void this.node.appendChild(a.getNode()):void this.node.insertBefore(a.getNode(),d.getNode())}})}),a("filter/filter",["graphic/svg","core/class","filter/effectcontainer","graphic/container","graphic/shape","core/utils","graphic/eventhandler","graphic/styled","graphic/data","graphic/matrix","graphic/pen","graphic/box"],function(a){var b=a("graphic/svg"),c=a("core/class"),d=c.createClass("Filter",{mixins:[a("filter/effectcontainer")],constructor:function(a,c,d,e){this.node=b.createNode("filter"),void 0!==a&&this.set("x",a),void 0!==c&&this.set("y",c),void 0!==d&&this.set("width",d),void 0!==e&&this.set("height",e)},getId:function(){return this.id},setId:function(a){return this.node.id=a,this},set:function(a,b){return this.node.setAttribute(a,b),this},get:function(a){return this.node.getAttribute(a)},getNode:function(){return this.node}}),e=a("graphic/shape");return c.extendClass(e,{applyFilter:function(a){var b=a.get("id");return b&&this.node.setAttribute("filter","url(#"+b+")"),this}}),d}),a("filter/gaussianblurfilter",["filter/effect/gaussianblureffect","filter/effect/effect","core/utils","core/class","filter/filter","graphic/svg","filter/effectcontainer","graphic/shape"],function(a){var b=a("filter/effect/gaussianblureffect");return a("core/class").createClass("GaussianblurFilter",{base:a("filter/filter"),constructor:function(a){this.callBase(),this.addEffect(new b(a))}})}),a("filter/projectionfilter",["filter/effect/gaussianblureffect","filter/effect/effect","core/utils","core/class","graphic/svg","filter/effect/colormatrixeffect","graphic/color","graphic/standardcolor","filter/effect/compositeeffect","filter/effect/offseteffect","filter/filter","filter/effectcontainer","graphic/shape"],function(a){var b=a("filter/effect/gaussianblureffect"),c=a("filter/effect/effect"),d=a("filter/effect/colormatrixeffect"),e=a("graphic/color"),f=a("core/utils"),g=a("filter/effect/compositeeffect"),h=a("filter/effect/offseteffect");return a("core/class").createClass("ProjectionFilter",{base:a("filter/filter"),constructor:function(a,e,f){this.callBase(),this.gaussianblurEffect=new b(a,c.INPUT_SOURCE_ALPHA),this.gaussianblurEffect.set("result","gaussianblur"),this.addEffect(this.gaussianblurEffect),this.offsetEffect=new h(e,f,this.gaussianblurEffect),this.offsetEffect.set("result","offsetBlur"),this.addEffect(this.offsetEffect),this.colorMatrixEffect=new d(d.TYPE_MATRIX,this.offsetEffect),this.colorMatrixEffect.set("values",d.MATRIX_ORIGINAL),this.colorMatrixEffect.set("result","colorOffsetBlur"),this.addEffect(this.colorMatrixEffect),this.compositeEffect=new g(g.OPERATOR_OVER,c.INPUT_SOURCE_GRAPHIC,this.colorMatrixEffect),this.addEffect(this.compositeEffect)},setColor:function(a){var b=null,c=[];if(f.isString(a)&&(a=e.parse(a)),!a)return this;b=d.MATRIX_EMPTY.split(" "),c.push(a.get("r")),c.push(a.get("g")),c.push(a.get("b"));for(var g=0,h=c.length;h>g;g++)b[5*g+3]=c[g]/255;return b[18]=a.get("a"),this.colorMatrixEffect.set("values",b.join(" ")),this},setOpacity:function(a){var b=this.colorMatrixEffect.get("values").split(" ");return b[18]=a,this.colorMatrixEffect.set("values",b.join(" ")),this},setOffset:function(a,b){this.setOffsetX(a),this.setOffsetY(b)},setOffsetX:function(a){this.offsetEffect.set("dx",a)},setOffsetY:function(a){this.offsetEffect.set("dy",a)},setDeviation:function(a){this.gaussianblurEffect.set("stdDeviation",a)}})}),a("graphic/bezier",["core/class","graphic/pointcontainer","graphic/container","graphic/path","core/utils","graphic/shape","graphic/svg","graphic/geometry"],function(a){return a("core/class").createClass("Bezier",{mixins:[a("graphic/pointcontainer")],base:a("graphic/path"),constructor:function(a){this.callBase(),a=a||[],this.changeable=!0,this.setBezierPoints(a)},getBezierPoints:function(){return this.getPoints()},setBezierPoints:function(a){return this.setPoints(a)},onContainerChanged:function(){this.changeable&&this.update()},update:function(){var a=null,b=this.getBezierPoints();if(!(b.length<2)){a=this.getDrawer(),a.clear();var c=b[0].getVertex(),d=null,e=null;a.moveTo(c.x,c.y);for(var f=1,g=b.length;g>f;f++)c=b[f].getVertex(),e=b[f].getBackward(),d=b[f-1].getForward(),a.bezierTo(d.x,d.y,e.x,e.y,c.x,c.y);return this}}})}),a("graphic/bezierpoint",["graphic/shapepoint","core/class","graphic/point","graphic/vector","graphic/matrix"],function(a){var b=a("graphic/shapepoint"),c=a("graphic/vector"),d=a("core/class").createClass("BezierPoint",{constructor:function(a,c,d){this.vertex=new b(a,c),this.forward=new b(a,c),this.backward=new b(a,c),this.setSmooth(void 0===d||d),this.setSymReflaction(!0)},clone:function(){var a=new d,b=null;return b=this.getVertex(),a.setVertex(b.x,b.y),b=this.getForward(),a.setForward(b.x,b.y),b=this.getBackward(),a.setBackward(b.x,b.y),a.setSmooth(a.isSmooth()),a},setVertex:function(a,b){return this.vertex.setPoint(a,b),this.update(),this},moveTo:function(a,b){var c=this.forward.getPoint(),d=this.backward.getPoint(),e=this.vertex.getPoint(),f={left:a-e.x,top:b-e.y};this.forward.setPoint(c.x+f.left,c.y+f.top),this.backward.setPoint(d.x+f.left,d.y+f.top),this.vertex.setPoint(a,b),this.update()},setForward:function(a,b){return this.forward.setPoint(a,b),this.smooth&&this.updateAnother(this.forward,this.backward),this.update(),this},setBackward:function(a,b){return this.backward.setPoint(a,b),this.smooth&&this.updateAnother(this.backward,this.forward),this.update(),this},setSymReflaction:function(a){this.symReflaction=a},isSymReflaction:function(){return this.symReflaction},updateAnother:function(a,b){var d=this.getVertex(),e=c.fromPoints(a.getPoint(),d),f=c.fromPoints(d,b.getPoint());f=c.normalize(e,this.isSymReflaction()?e.length():f.length()),b.setPoint(d.x+f.x,d.y+f.y)},setSmooth:function(a){return this.smooth=!!a,this},getVertex:function(){return this.vertex.getPoint()},getForward:function(){return this.forward.getPoint()},getBackward:function(){return this.backward.getPoint()},isSmooth:function(){return this.smooth},update:function(){return this.container?void(this.container.update&&this.container.update(this)):this}});return d}),a("graphic/box",["core/class"],function(a){var b=a("core/class").createClass("Box",{constructor:function(a,b,c,d){var e=arguments[0];e&&"object"==typeof e&&(a=e.x,b=e.y,c=e.width,d=e.height),0>c&&(a-=c=-c),0>d&&(b-=d=-d),this.x=a,this.y=b,this.width=c,this.height=d},getLeft:function(){return this.x},getRight:function(){return this.x+this.width},getTop:function(){return this.y},getBottom:function(){return this.y+this.height},getRangeX:function(){return[this.x,this.x+this.width]},getRangeY:function(){return[this.y,this.y+this.height]},merge:function(a){var c=Math.min(this.x,a.x),d=Math.max(this.x+this.width,a.x+a.width),e=Math.min(this.y,a.y),f=Math.max(this.y+this.height,a.y+a.height);return new b(c,e,d-c,f-e)},valueOf:function(){return[this.x,this.y,this.width,this.height]},toString:function(){return this.valueOf().join(" ")}});return b}),a("graphic/circle",["core/class","graphic/ellipse","core/utils","graphic/point","graphic/path"],function(a){return a("core/class").createClass("Circle",{base:a("graphic/ellipse"),constructor:function(a,b,c){this.callBase(a,a,b,c)},getRadius:function(){return this.getRadiusX()},setRadius:function(a){return this.callBase(a,a)}})}),a("graphic/clip",["core/class","graphic/shape","graphic/svg","core/utils","graphic/eventhandler","graphic/styled","graphic/data","graphic/matrix","graphic/pen","graphic/box","graphic/shapecontainer","graphic/container"],function(a){var b=a("core/class"),c=a("graphic/shape"),d=b.createClass("Clip",{base:c,mixins:[a("graphic/shapecontainer")],constructor:function(){this.callBase("clipPath")},clip:function(a){return a.getNode().setAttribute("clip-path","url(#"+this.getId()+")"),this}});return b.extendClass(c,{clipWith:function(a){return a.clip(this),this}}),d}),a("graphic/color",["core/utils","graphic/standardcolor","core/class"],function(a){var b=a("core/utils"),c=a("graphic/standardcolor"),d={},e=a("core/class").createClass("Color",{constructor:function(){var a=null;"string"==typeof arguments[0]?(a=d.parseToValue(arguments[0]),null===a&&(a={r:0,g:0,b:0,h:0,s:0,l:0,a:1})):(a={r:0|arguments[0],g:0|arguments[1],b:0|arguments[2],a:parseFloat(arguments[3])||1},a=d.overflowFormat(a),a=b.extend(a,d.rgbValueToHslValue(a))),this._color=a},set:function(a,c){if(!e._MAX_VALUE[a])throw new Error("Color set(): Illegal parameter");return"a"!==a&&(c=Math.floor(c)),"h"==a&&(c=(c+360)%360),this._color[a]=Math.max(e._MIN_VALUE[a],Math.min(e._MAX_VALUE[a],c)),-1!=="rgb".indexOf(a)?this._color=b.extend(this._color,d.rgbValueToHslValue(this._color)):-1!=="hsl".indexOf(a)&&(this._color=b.extend(this._color,d.hslValueToRGBValue(this._color))),this},inc:function(a,b){return b=this.get(a)+b,"h"==a?b=(b+360)%360:(b=Math.min(e._MAX_VALUE[a],b),b=Math.max(e._MIN_VALUE[a],b)),this.clone().set(a,b)},dec:function(a,b){return this.inc(a,-b)},clone:function(){return new e(this.toRGBA())},get:function(a){return e._MAX_VALUE[a]?this._color[a]:null
},getValues:function(){return b.clone(this._color)},valueOf:function(){return this.getValues()},toRGB:function(){return d.toString(this._color,"rgb")},toRGBA:function(){return d.toString(this._color,"rgba")},toHEX:function(){return d.toString(this._color,"hex")},toHSL:function(){return d.toString(this._color,"hsl")},toHSLA:function(){return d.toString(this._color,"hsla")},toString:function(){return 1===this._color.a?this.toRGB():this.toRGBA()}});return b.extend(e,{_MAX_VALUE:{r:255,g:255,b:255,h:360,s:100,l:100,a:1},_MIN_VALUE:{r:0,g:0,b:0,h:0,s:0,l:0,a:0},R:"r",G:"g",B:"b",H:"h",S:"s",L:"l",A:"a",parse:function(a){var c;return b.isString(a)&&(c=d.parseToValue(a)),b.isObject(a)&&"r"in a&&(c=a),null===c?new e:new e(c.r,c.g,c.b,c.a)},createHSL:function(a,b,c){return e.createHSLA(a,b,c,1)},createHSLA:function(a,b,c,d){var f=null;return b+="%",c+="%",f=["hsla("+a,b,c,d+")"],e.parse(f.join(", "))},createRGB:function(a,b,c){return e.createRGBA(a,b,c,1)},createRGBA:function(a,b,c,d){return new e(a,b,c,d)}}),b.extend(d,{parseToValue:function(a){var b={};if(a=c.EXTEND_STANDARD[a]||c.COLOR_STANDARD[a]||a,/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(a))b=d.hexToValue(a);else if(/^(rgba?)/i.test(a))b=d.rgbaToValue(a);else{if(!/^(hsla?)/i.test(a))return null;b=d.hslaToValue(a)}return d.overflowFormat(b)},hexToValue:function(a){var c={},e=["r","g","b"];return/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(a)?(a=RegExp.$1.split(""),b.each(e,function(b,e){c[b]=d.toNumber(3===a.length?a[e]+a[e]:a[2*e]+a[2*e+1])}),c=b.extend(c,d.rgbValueToHslValue(c)),c.a=1,c):null},rgbaToValue:function(a){var c={},e=!1,f=["r","g","b"];return/^(rgba?)/i.test(a)?(e=4===RegExp.$1.length,a=a.replace(/^rgba?/i,"").replace(/\s+/g,"").replace(/[^0-9,.]/g,"").split(","),b.each(f,function(b,d){c[b]=0|a[d]}),c=b.extend(c,d.rgbValueToHslValue(c)),c.a=e?parseFloat(a[3]):1,c):null},hslaToValue:function(a){var c={},e=!1;return/^(hsla?)/i.test(a)?(e=4===RegExp.$1.length,a=a.replace(/^hsla?/i,"").replace(/\s+/g,"").replace(/[^0-9,.]/g,"").split(","),c.h=0|a[0],c.s=0|a[1],c.l=0|a[2],c=b.extend(c,d.hslValueToRGBValue(c)),c=d.hslValueToRGBValue(c),c.a=e?parseFloat(a[3]):1,c):null},hslValueToRGBValue:function(a){function c(a,b,c){return 0>c?c+=1:c>1&&(c-=1),1>6*c?a+6*(b-a)*c:1>2*c?b:2>3*c?a+(b-a)*(2/3-c)*6:a}var d=null,e=null,f={};return a=b.extend({},a),a.h=a.h/360,a.s=a.s/100,a.l=a.l/100,0===a.s?f.r=f.g=f.b=a.l:(d=a.l<.5?a.l*(1+a.s):a.l+a.s-a.l*a.s,e=2*a.l-d,f.r=c(e,d,a.h+1/3),f.g=c(e,d,a.h),f.b=c(e,d,a.h-1/3)),f.r=Math.min(Math.round(255*f.r),255),f.g=Math.min(Math.round(255*f.g),255),f.b=Math.min(Math.round(255*f.b),255),f},rgbValueToHslValue:function(a){var c=null,d=null,e={};return a=b.extend({},a),a.r=a.r/255,a.g=a.g/255,a.b=a.b/255,c=Math.max(a.r,a.g,a.b),d=Math.min(a.r,a.g,a.b),c===d?e.h=0:c===a.r?e.h=a.g>=a.b?60*(a.g-a.b)/(c-d):60*(a.g-a.b)/(c-d)+360:c===a.g?e.h=60*(a.b-a.r)/(c-d)+120:c===a.b&&(e.h=60*(a.r-a.g)/(c-d)+240),e.l=(c+d)/2,e.s=0===e.l||c===d?0:e.l>0&&e.l<=.5?(c-d)/(c+d):(c-d)/(2-c-d),e.h=Math.round(e.h),e.s=Math.round(100*e.s),e.l=Math.round(100*e.l),e},toString:function(a,c){var e=[];return a=b.extend({},a),-1!==c.indexOf("hsl")&&(a.s+="%",a.l+="%"),"hex"!==c?(b.each(c.split(""),function(b){e.push(a[b])}),(c+"("+e.join(", ")+")").toLowerCase()):(e.push(d.toHexValue(+a.r)),e.push(d.toHexValue(+a.g)),e.push(d.toHexValue(+a.b)),("#"+e.join("")).toLowerCase())},toNumber:function(a){return 0|Number("0x"+a)},toHexValue:function(a){var b=a.toString(16);return 1===b.length?"0"+b:b},overflowFormat:function(a){var c=b.extend({},a),d="rgba";return b.each(d.split(""),function(a){c.hasOwnProperty(a)&&(c[a]=Math.min(e._MAX_VALUE[a],c[a]),c[a]=Math.max(e._MIN_VALUE[a],c[a]))}),c}}),e}),a("graphic/container",["core/class"],function(a){function b(){return this.container.removeItem(this),this}return a("core/class").createClass("Container",{getItems:function(){return this.items||(this.items=[])},getItem:function(a){return this.getItems()[a]},getFirstItem:function(){return this.getItem(0)},getLastItem:function(){return this.getItem(this.getItems().length-1)},indexOf:function(a){return this.getItems().indexOf(a)},eachItem:function(a){var b,c=this.getItems(),d=c.length;for(b=0;d>b;b++)a.call(this,b,c[b]);return this},addItem:function(a,c,d){var e=this.getItems(),f=e.length;return~e.indexOf(a)?this:(c>=0&&f>c||(c=f),e.splice(c,0,a),"object"==typeof a&&(a.container=this,a.remove=b),this.handleAdd(a,c),d||this.onContainerChanged("add",[a]),this)},addItems:function(a){for(var b=0,c=a.length;c>b;b++)this.addItem(a[b],-1,!0);return this.onContainerChanged("add",a),this},setItems:function(a){return this.clear().addItems(a)},appendItem:function(a){return this.addItem(a)},prependItem:function(a){return this.addItem(a,0)},removeItem:function(a,b){if("number"!=typeof a)return this.removeItem(this.indexOf(a));var c=this.getItems(),d=(c.length,c[a]);return void 0===d?this:(c.splice(a,1),d.container&&delete d.container,d.remove&&delete d.remove,this.handleRemove(d,a),b||this.onContainerChanged("remove",[d]),this)},clear:function(){for(var a,b=[];a=this.getFirstItem();)b.push(a),this.removeItem(0,!0);return this.onContainerChanged("remove",b),this},onContainerChanged:function(){},handleAdd:function(){},handleRemove:function(){}})}),a("graphic/curve",["core/utils","core/class","graphic/path","graphic/shape","graphic/svg","graphic/geometry","graphic/pointcontainer","graphic/container"],function(a){var b=a("core/utils"),c={getCurvePanLines:function(a,b){var d=c.getCenterPoints(a),e=c.getPanLine(a.length,d);return c.getMovedPanLines(a,e,b)},getCenterPoints:function(a){for(var b={},c=null,d=0,e=0,f=a.length;f>d;d++)e=d===f-1?0:d+1,c=d+","+e,b[c]={x:(a[d].x+a[e].y)/2,y:(a[d].x+a[e].y)/2};return b},getPanLine:function(a,b){for(var c,d={},e=null,f=0;a>f;f++){var g=null,h=null;c=(f+1)%a,e=c,g=b[f+","+c],f=c,c=(f+1)%a,h=b[f+","+c],d[e]={points:[{x:g.x,y:g.y},{x:h.x,y:h.y}],center:{x:(g.x+h.x)/2,y:(g.y+h.y)/2}},f=(e+a-1)%a}return d},getMovedPanLines:function(a,c,d){var e={};return b.each(a,function(a,f){var g=c[f],h=g.center,i={x:h.x-a.x,y:h.y-a.y},j=e[f]={points:[],center:{x:a.x,y:a.y}};b.each(g.points,function(a){var b={x:a.x-i.x,y:a.y-i.y},c=j.center,e=b.x-c.x,f=b.y-c.y;b.x=c.x+d*e,b.y=c.y+d*f,j.points.push(b)})}),e}};return a("core/class").createClass("Curve",{base:a("graphic/path"),mixins:[a("graphic/pointcontainer")],constructor:function(a,b){this.callBase(),this.setPoints(a||[]),this.closeState=!!b,this.changeable=!0,this.smoothFactor=1,this.update()},onContainerChanged:function(){this.changeable&&this.update()},setSmoothFactor:function(a){return this.smoothFactor=0>a?0:a,this.update(),this},getSmoothFactor:function(){return this.smoothFactor},update:function(){var a=this.getPoints(),b=null,d=this.getDrawer(),e=null,f=null,g=null;if(d.clear(),0===a.length)return this;if(d.moveTo(a[0]),1===a.length)return this;if(2===a.length)return d.lineTo(a[1]),this;b=c.getCurvePanLines(a,this.getSmoothFactor());for(var h=1,i=a.length;i>h;h++)e=b[h].center,f=this.closeState||h!=i-1?b[h].points[0]:b[h].center,g=this.closeState||1!=h?b[h-1].points[1]:b[h-1].center,d.bezierTo(g.x,g.y,f.x,f.y,e.x,e.y);return this.closeState&&(e=b[0].center,f=b[0].points[0],g=b[a.length-1].points[1],d.bezierTo(g.x,g.y,f.x,f.y,e.x,e.y)),this},close:function(){return this.closeState=!0,this.update()},open:function(){return this.closeState=!1,this.update()},isClose:function(){return!!this.closeState}})}),a("graphic/data",["core/class"],function(a){return a("core/class").createClass("Data",{constructor:function(){this._data={}},setData:function(a,b){return this._data[a]=b,this},getData:function(a){return this._data[a]},removeData:function(a){return delete this._data[a],this}})}),a("graphic/defbrush",["core/class","graphic/resource","graphic/svg"],function(a){return a("core/class").createClass("GradientBrush",{base:a("graphic/resource"),constructor:function(a){this.callBase(a)}})}),a("graphic/ellipse",["core/utils","graphic/point","core/class","graphic/path","graphic/shape","graphic/svg","graphic/geometry"],function(a){var b=(a("core/utils"),a("graphic/point"));return a("core/class").createClass("Ellipse",{base:a("graphic/path"),constructor:function(a,b,c,d){this.callBase(),this.rx=a||0,this.ry=b||0,this.cx=c||0,this.cy=d||0,this.update()},update:function(){var a=this.rx,b=this.ry,c=this.cx+a,d=this.cx-a,e=this.cy,f=this.getDrawer();return f.clear(),f.moveTo(c,e),f.arcTo(a,b,0,1,1,d,e),f.arcTo(a,b,0,1,1,c,e),this},getRadius:function(){return{x:this.rx,y:this.ry}},getRadiusX:function(){return this.rx},getRadiusY:function(){return this.ry},getCenter:function(){return new b(this.cx,this.cy)},getCenterX:function(){return this.cx},getCenterY:function(){return this.cy},setRadius:function(a,b){return this.rx=a,this.ry=b,this.update()},setRadiusX:function(a){return this.rx=a,this.update()},setRadiusY:function(a){return this.ry=a,this.update()},setCenter:function(a,c){if(1==arguments.length){var d=b.parse(arguments[0]);a=d.x,c=d.y}return this.cx=a,this.cy=c,this.update()},setCenterX:function(a){return this.cx=a,this.update()},setCenterY:function(a){return this.cy=a,this.update()}})}),a("graphic/eventhandler",["core/utils","graphic/shapeevent","graphic/matrix","graphic/point","core/class"],function(a){function b(a,b,c){return c=!!c,i.isString(a)&&(a=a.match(/\S+/g)),i.each(a,function(a){d.call(this,this.node,a,b,c)},this),this}function c(a,b){var c=null,d=this._EVNET_UID,e=void 0===b;try{c=l[d][a]}catch(g){return}return e||(e=!0,i.each(c,function(a,d){a===b?delete c[d]:e=!1})),e&&(f(this.node,a,k[d][a]),delete l[d][a],delete k[d][a]),this}function d(a,b,c,d){var f=this._EVNET_UID,g=this;k[f]||(k[f]={}),k[f][b]||(k[f][b]=function(a){a=new j(a||window.event),i.each(l[f][b],function(c){var e;return c&&(e=c.call(g,a),d&&g.off(b,c)),e},g)}),l[f]||(l[f]={}),l[f][b]?l[f][b].push(c):(l[f][b]=[c],a&&e(a,b,k[f][b]))}function e(a,b,c){a.addEventListener?a.addEventListener(b,c,!1):a.attachEvent("on"+b,c)}function f(a,b,c){a.removeEventListener?a.removeEventListener(b,c,!1):a.detachEvent(b,c)}function g(a,b,c){var d=new CustomEvent(b,{bubbles:!0,cancelable:!0});d._kityParam=c,a.dispatchEvent(d)}function h(a,b,c){var d=null,e=null;try{if(e=k[a._EVNET_UID][b],!e)return}catch(f){return}d=i.extend({type:b,target:a},c||{}),e.call(a,d)}!function(){function a(a,b){b=b||{bubbles:!1,cancelable:!1,detail:void 0};var c=document.createEvent("CustomEvent");return c.initCustomEvent(a,b.bubbles,b.cancelable,b.detail),c}a.prototype=window.Event.prototype,window.CustomEvent=a}();var i=a("core/utils"),j=a("graphic/shapeevent"),k={},l={},m=0;return a("core/class").createClass("EventHandler",{constructor:function(){this._EVNET_UID=++m},addEventListener:function(a,c){return b.call(this,a,c,!1)},addOnceEventListener:function(a,c){return b.call(this,a,c,!0)},removeEventListener:function(a,b){return c.call(this,a,b)},on:function(){return this.addEventListener.apply(this,arguments)},once:function(){return this.addOnceEventListener.apply(this,arguments)},off:function(){return this.removeEventListener.apply(this,arguments)},fire:function(){return this.trigger.apply(this,arguments)},trigger:function(a,b){return this.node?g(this.node,a,b):h(this,a,b),this}})}),a("graphic/geometry",["core/utils","graphic/point","core/class","graphic/vector","graphic/matrix","graphic/box"],function(a){function b(a){var b,c,d,e,f;for(b=[],c=0;c<a.length;c++)for(e=a[c],b.push(f=[]),d=0;d<e.length;d++)f.push(e[d]);return a.isUniform&&(b.isUniform=!0),a.isAbsolute&&(b.isAbsolute=!0),a.isCurve&&(b.isCurve=!0),b}function c(a,b,c){function d(a,b){for(var c=0,d=a.length;d>c;c++)if(a[c]===b)return a.push(a.splice(c,1)[0])}function e(){var f=Array.prototype.slice.call(arguments,0),g=f.join("␀"),h=e.cache=e.cache||{},i=e.count=e.count||[];return h.hasOwnProperty(g)?(d(i,g),c?c(h[g]):h[g]):(i.length>=1e3&&delete h[i.shift()],i.push(g),h[g]=a.apply(b,f),c?c(h[g]):h[g])}return e}function d(a,b,c,e,f,g,h,i,j,k){var l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q=Math,R=Q.PI,S=Math.abs,T=120*R/180,U=R/180*(+f||0),V=[],W=function(a,b,c){var d=a*Q.cos(c)-b*Q.sin(c),e=a*Q.sin(c)+b*Q.cos(c);return{x:d,y:e}};if(k?(w=k[0],x=k[1],u=k[2],v=k[3]):(l=W(a,b,-U),a=l.x,b=l.y,l=W(i,j,-U),i=l.x,j=l.y,m=Q.cos(R/180*f),n=Q.sin(R/180*f),p=(a-i)/2,q=(b-j)/2,o=p*p/(c*c)+q*q/(e*e),o>1&&(o=Q.sqrt(o),c=o*c,e=o*e),r=c*c,s=e*e,t=(g==h?-1:1)*Q.sqrt(S((r*s-r*q*q-s*p*p)/(r*q*q+s*p*p))),u=t*c*q/e+(a+i)/2,v=t*-e*p/c+(b+j)/2,w=Q.asin(((b-v)/e).toFixed(9)),x=Q.asin(((j-v)/e).toFixed(9)),w=u>a?R-w:w,x=u>i?R-x:x,0>w&&(w=2*R+w),0>x&&(x=2*R+x),h&&w>x&&(w-=2*R),!h&&x>w&&(x-=2*R)),y=x-w,S(y)>T&&(z=x,A=i,B=j,x=w+T*(h&&x>w?1:-1),i=u+c*Q.cos(x),j=v+e*Q.sin(x),V=d(i,j,c,e,f,0,h,A,B,[x,z,u,v])),y=x-w,C=Q.cos(w),D=Q.sin(w),E=Q.cos(x),F=Q.sin(x),G=Q.tan(y/4),H=4/3*c*G,I=4/3*e*G,J=[a,b],K=[a+H*D,b-I*C],L=[i+H*F,j-I*E],M=[i,j],K[0]=2*J[0]-K[0],K[1]=2*J[1]-K[1],k)return[K,L,M].concat(V);for(V=[K,L,M].concat(V).join().split(","),N=[],O=0,P=V.length;P>O;O++)N[O]=O%2?W(V[O-1],V[O],U).y:W(V[O],V[O+1],U).x;return N}function e(a,b,c,d,e,f){var g=1/3,h=2/3;return[g*a+h*c,g*b+h*d,g*e+h*c,g*f+h*d,e,f]}function f(a,b){function c(a){return function(b,c){return b+a*(c-b)}}var d=c(b||.5),e=a,f=e[0],g=e[1],h=e[2],i=e[3],j=e[4],k=e[5],l=e[6],m=e[7],n=d(f,h),o=d(g,i),p=d(h,j),q=d(i,k),r=d(j,l),s=d(k,m),t=d(n,p),u=d(o,q),v=d(p,r),w=d(q,s),x=d(t,v),y=d(u,w);return[[f,g,n,o,t,u,x,y],[x,y,v,w,r,s,l,m]]}var g=a("core/utils"),h=a("graphic/point"),i=a("graphic/vector"),j=a("graphic/matrix"),k={},l=/([achlmrqstvz])[\s,]*((-?\d*\.?\d*(?:e[\-+]?\d+)?[\s]*,?\s*)+)/gi,m=/(-?\d*\.?\d*(?:e[\-+]?\d+)?)\s*,?\s*/gi,n={a:7,c:6,h:1,l:2,m:2,q:4,s:4,t:2,v:1,z:0};k.pathToString=function(a){return a=a||this,"string"==typeof a?a:a instanceof Array?(a=g.flatten(a),a.join(",").replace(/,?([achlmqrstvxz]),?/gi,"$1")):void 0},k.parsePathString=c(function(a){var b=[];return a.replace(l,function(a,c,d){var e=[],f=c.toLowerCase();if(d.replace(m,function(a,b){b&&e.push(+b)}),"m"==f&&e.length>2&&(b.push([c].concat(e.splice(0,2))),f="l",c="m"==c?"l":"L"),"r"==f)b.push([c].concat(e));else for(;e.length>=n[f]&&(b.push([c].concat(e.splice(0,n[f]))),n[f]););}),b.isUniform=!0,b.toString=k.pathToString,b}),k.pathToAbsolute=c(function(a){var b,c,d,e,f,g,h,i,j=a.isUniform?a:k.parsePathString(k.pathToString(a)),l=[],m=0,n=0,o=0,p=0,q=0;for("M"==j[0][0]&&(m=+j[0][1],n=+j[0][2],o=m,p=n,q++,l[0]=["M",m,n]),d=q,g=j.length;g>d;d++){if(l.push(b=[]),c=j[d],c[0]!=c[0].toUpperCase())switch(b[0]=c[0].toUpperCase(),b[0]){case"A":b[1]=c[1],b[2]=c[2],b[3]=c[3],b[4]=c[4],b[5]=c[5],b[6]=+(c[6]+m),b[7]=+(c[7]+n);break;case"V":b[1]=+c[1]+n;break;case"H":b[1]=+c[1]+m;break;case"M":o=+c[1]+m,p=+c[2]+n;break;default:for(e=1,h=c.length;h>e;e++)b[e]=+c[e]+(e%2?m:n)}else for(f=0,i=c.length;i>f;f++)b[f]=c[f];switch(b[0]){case"Z":m=o,n=p;break;case"H":m=b[1];break;case"V":n=b[1];break;case"M":o=b[b.length-2],p=b[b.length-1];break;default:m=b[b.length-2],n=b[b.length-1]}}return l.isUniform=!0,l.isAbsolute=!0,l.toString=k.pathToString,l}),k.pathToCurve=c(function(a){var b,c,f,g,h,i,j,l,m,n,o,p=[];for(a.isAbsolute||(a=k.pathToAbsolute(a)),b=0;b<a.length;b++)if(c=a[b][0],f=a[b].slice(1),"M"!=c){switch("Z"==c&&(j=!0,c="L",f=g),i=f.slice(f.length-2),"H"==c&&(i=[f[0],h[1]],c="L"),"V"==c&&(i=[h[0],f[0]],c="L"),("S"==c||"T"==c)&&(m=[h[0]+(h[0]-l[0]),h[1]+(h[1]-l[1])]),c){case"L":n=h,o=i;break;case"C":n=f.slice(0,2),o=f.slice(2,4);break;case"S":n=m.slice(),o=f.slice(0,2);break;case"Q":l=f.slice(0,2),f=e.apply(null,h.concat(f)),n=f.slice(0,2),o=f.slice(2,4);break;case"T":f=e.apply(null,h.concat(m).concat(f)),n=f.slice(0,2),o=f.slice(2,4);break;case"A":f=d.apply(null,h.concat(f)),n=f.slice(0,2),o=f.slice(2,4)}p.push(["C"].concat(n).concat(o).concat(i)),h=i,"Q"!=c&&(l=o),j&&(p.push(["Z"]),j=!1)}else g=l=h=f,p.push(a[b]);return p.isUniform=!0,p.isAbsolute=!0,p.isCurve=!0,p.toString=k.pathToString,p}),k.cutBezier=c(f),k.subBezier=function(a,b,c){var d=f(a,b)[0];return c?f(d,c/b)[1]:d},k.pointAtBezier=function(a,b){var c=f(a,b)[0],d=h.parse(c.slice(6)),e=h.parse(c.slice(4,2)),g=i.fromPoints(e,d);return d.tan=0===b?k.pointAtBezier(a,.01).tan:g.normalize(),d},k.bezierLength=c(function q(a,b){function c(a,b){var c=a[0]-b[0],d=a[1]-b[1];return Math.sqrt(c*c+d*d)}b=Math.max(b||.001,1e-9);var d,e,g,h,i;return d=f(a),e=a.slice(0,2),g=a.slice(6),h=d[1].slice(0,2),i=c(e,h)+c(h,g),i-c(e,g)<b?i:q(d[0],b/2)+q(d[1],b/3)});var o=c(function(a){var b,c,d,e,f,g,h;for(g=[],h=0,b=0,c=a.length;c>b;b++)d=a[b],"M"!=d[0]?"Z"!=d[0]?(f=k.bezierLength(e.concat(d.slice(1))),g.push([h,h+f]),h+=f,e=d.slice(4)):g.push(null):(e=d.slice(1),g.push(null));return g.totalLength=h,g});k.subPath=function(a,b,c){var d;if(c=c||0,d=b-c,d-=0|d,c-=0|c,b=c+d,b>1)return k.subPath(a,1,c).concat(k.subPath(a,b-1));a.isCurve||(a=k.pathToCurve(a));var e,f,g,h,i,j,l,m,n,p=o(a),q=p.totalLength,r=q*b,s=q*(c||0),t=[];for(e=0,f=a.length;f>e;e++)if("M"!=a[e][0]){if("Z"!=a[e][0])if(g=p[e][0],h=p[e][1],i=h-g,l=j.concat(a[e].slice(1)),s>h)j=l.slice(l.length-2);else{if(s>=g)m=k.subBezier(l,Math.min((r-g)/i,1),(s-g)/i),n=!0,j=m.slice(0,2),t.push(["M"].concat(m.slice(0,2))),t.push(["C"].concat(m.slice(2)));else if(r>=h)t.push(a[e].slice());else{if(!(r>=g))break;m=k.subBezier(l,(r-g)/i),t.push(["C"].concat(m.slice(2))),n=!1}j=l.slice(l.length-2)}}else j=a[e].slice(1),n&&t.push(a[e].slice());return t.isAbsolute=!0,t.isCurve=!0,t.isUniform=!0,t.toString=k.pathToString,t},k.pointAtPath=function(a,b){a.isCurve||(a=k.pathToCurve(a));var c=k.subPath(a,b),d="Z"==c[c.length-1][0]?c[c.length-2]:c[c.length-1];d=d.slice(1);var e=h.parse(d.slice(4)),f=h.parse(d.slice(2,4));return e.tan=i.fromPoints(f,e).normalize(),e},k.pathLength=c(function(a){a.isCurve||(a=k.pathToCurve(a));var b=o(a);return b.totalLength}),k.pathKeyPoints=c(function(a){var b,c,d;for(a.isCurve||(a=k.pathToCurve(a)),d=[],b=0,c=a.length;c>b;b++)"z"!=a[b][0]&&d.push(a[b].slice(a[b].length-2));return d});var p=c(function(a,c){function d(a,b){return a[b||a.i]&&a[b||a.i][0]}function e(a,b){return a[b||a.i]&&a[b||a.i].slice(1)}function f(a,b){var c=e(a,b);return c&&c.slice(-2)}function g(a){return"Z"==d(a)?(a.splice(a.i,1),!0):!1}function h(a){return"M"==d(a)?(a.o.splice(a.o.i,0,["M"].concat(f(a.o,a.o.i-1))),a.i++,a.o.i++,!0):!1}function i(a){for(var b,c=1;!b;)b=f(a,a.length-c++);for(a.o.i=a.i;a.length<a.o.length;)g(a.o)||h(a.o)||(a.push(["C"].concat(b).concat(b).concat(b)),a.i++,a.o.i++)}a.isCurve||(a=k.pathToCurve(a)),c.isCurve||(c=k.pathToCurve(c));var j=b(a),l=b(c);for(j.i=0,l.i=0,j.o=l,l.o=j;j.i<j.length&&l.i<l.length;)g(j)||g(l)||(d(j)!=d(l)?h(j)||h(l)||(j.i++,l.i++):(j.i++,l.i++));return j.i==j.length&&i(j),l.i==l.length&&i(l),delete j.i,delete j.o,delete l.i,delete l.o,[j,l]});return k.alignCurve=p,k.pathTween=function(a,b,c){if(0===c)return a;if(1===c)return b;var d,e,f,g=p(a,b),h=[];for(a=g[0],b=g[1],e=0;e<a.length;e++)for(h.push(d=[]),d.push(a[e][0]),f=1;f<a[e].length;f++)d.push(a[e][f]+c*(b[e][f]-a[e][f]));return h.isUniform=h.isCurve=h.isAbsolute=!0,h},k.transformPath=c(function(a,b){var c,d,e,f,g,i;for(a.isCurve||(a=k.pathToCurve(a)),f=[],c=0,d=a.length;d>c;c++)for(f.push(g=[a[c][0]]),e=1;e<a[c].length;e+=2)i=a[c].slice(e,e+2),i=b.transformPoint(h.parse(i)),f.push(i);return f}),a("core/class").extendClass(j,{transformPath:function(a){return k.transformPath(a,this)}}),k}),a("graphic/gradientbrush",["graphic/svg","graphic/defbrush","core/class","graphic/resource","graphic/color","core/utils","graphic/standardcolor"],function(a){var b=a("graphic/svg"),c=a("graphic/defbrush"),d=a("graphic/color");return a("core/class").createClass("GradientBrush",{base:c,constructor:function(a){this.callBase(a),this.stops=[]},addStop:function(a,c,e){var f=b.createNode("stop");return c instanceof d||(c=d.parse(c)),void 0===e&&(e=c.get("a")),f.setAttribute("offset",a),f.setAttribute("stop-color",c.toRGB()),1>e&&f.setAttribute("stop-opacity",e),this.node.appendChild(f),this}})}),a("graphic/group",["graphic/shapecontainer","graphic/container","core/utils","core/class","graphic/shape","graphic/svg","graphic/eventhandler","graphic/styled","graphic/data","graphic/matrix","graphic/pen","graphic/box"],function(a){var b=a("graphic/shapecontainer");return a("core/class").createClass("Group",{mixins:[b],base:a("graphic/shape"),constructor:function(){this.callBase("g")}})}),a("graphic/hyperlink",["graphic/shapecontainer","graphic/container","core/utils","core/class","graphic/shape","graphic/svg","graphic/eventhandler","graphic/styled","graphic/data","graphic/matrix","graphic/pen","graphic/box"],function(a){var b=a("graphic/shapecontainer");return a("core/class").createClass("HyperLink",{mixins:[b],base:a("graphic/shape"),constructor:function(a){this.callBase("a"),this.setHref(a)},setHref:function(a){return this.node.setAttributeNS("http://www.w3.org/1999/xlink","xlink:href",a),this},getHref:function(){return this.node.getAttributeNS("xlink:href")},setTarget:function(a){return this.node.setAttribute("target",a),this},getTarget:function(){return this.node.getAttribute("target")}})}),a("graphic/image",["core/class","graphic/shape","graphic/svg","core/utils","graphic/eventhandler","graphic/styled","graphic/data","graphic/matrix","graphic/pen","graphic/box"],function(a){return a("core/class").createClass("Image",{base:a("graphic/shape"),constructor:function(a,b,c,d,e){this.callBase("image"),this.url=a,this.width=b||0,this.height=c||0,this.x=d||0,this.y=e||0,this.update()},update:function(){return this.node.setAttributeNS("http://www.w3.org/1999/xlink","xlink:href",this.url),this.node.setAttribute("x",this.x),this.node.setAttribute("y",this.y),this.node.setAttribute("width",this.width),this.node.setAttribute("height",this.height),this},setUrl:function(a){return this.url=""===a?null:a,this.update()},getUrl:function(){return this.url},setWidth:function(a){return this.width=a,this.update()},getWidth:function(){return this.width},setHeight:function(a){return this.height=a,this.update()},getHeight:function(){return this.height},setX:function(a){return this.x=a,this.update()},getX:function(){return this.x},setY:function(a){return this.y=a,this.update()},getY:function(){return this.y}})}),a("graphic/line",["core/class","graphic/path","core/utils","graphic/shape","graphic/svg","graphic/geometry"],function(a){return a("core/class").createClass("Line",{base:a("graphic/path"),constructor:function(a,b,c,d){this.callBase(),this.point1={x:a||0,y:b||0},this.point2={x:c||0,y:d||0},this.update()},setPoint1:function(a,b){return this.point1.x=a,this.point1.y=b,this.update()},setPoint2:function(a,b){return this.point2.x=a,this.point2.y=b,this.update()},getPoint1:function(){return{x:this.point1.x,y:this.point1.y}},getPoint2:function(){return{x:this.point2.x,y:this.point2.y}},update:function(){var a=this.getDrawer();return a.clear(),a.moveTo(this.point1.x,this.point1.y),a.lineTo(this.point2.x,this.point2.y),this}})}),a("graphic/lineargradientbrush",["graphic/svg","graphic/gradientbrush","graphic/defbrush","graphic/color","core/class"],function(a){var b="LinearGradientBrush",c=(a("graphic/svg"),a("graphic/gradientbrush"));return a("core/class").createClass(b,{base:c,constructor:function(a){this.callBase("linearGradient"),this.setStartPosition(0,0),this.setEndPosition(1,0),"function"==typeof a&&a.call(this,this)},setStartPosition:function(a,b){return this.node.setAttribute("x1",a),this.node.setAttribute("y1",b),this},setEndPosition:function(a,b){return this.node.setAttribute("x2",a),this.node.setAttribute("y2",b),this},getStartPosition:function(){return{x:+this.node.getAttribute("x1"),y:+this.node.getAttribute("y1")}},getEndPosition:function(){return{x:+this.node.getAttribute("x2"),y:+this.node.getAttribute("y2")}}})}),a("graphic/marker",["graphic/point","core/class","graphic/resource","graphic/svg","graphic/shapecontainer","graphic/container","core/utils","graphic/shape","graphic/viewbox","graphic/path","graphic/geometry"],function(a){var b=a("graphic/point"),c=a("core/class").createClass("Marker",{base:a("graphic/resource"),mixins:[a("graphic/shapecontainer"),a("graphic/viewbox")],constructor:function(){this.callBase("marker"),this.setOrient("auto")},setRef:function(a,b){return 1===arguments.length&&(b=a.y,a=a.x),this.node.setAttribute("refX",a),this.node.setAttribute("refY",b),this},getRef:function(){return new b(+this.node.getAttribute("refX"),+this.node.getAttribute("refY"))},setWidth:function(a){return this.node.setAttribute("markerWidth",this.width=a),this},setOrient:function(a){return this.node.setAttribute("orient",this.orient=a),this},getOrient:function(){return this.orient},getWidth:function(){return+this.width},setHeight:function(a){return this.node.setAttribute("markerHeight",this.height=a),this},getHeight:function(){return+this.height}}),d=a("graphic/path");return a("core/class").extendClass(d,{setMarker:function(a,b){return b=b||"end",a?this.node.setAttribute("marker-"+b,a.toString()):this.node.removeAttribute("marker-"+b),this}}),c}),a("graphic/mask",["core/class","graphic/shape","graphic/svg","core/utils","graphic/eventhandler","graphic/styled","graphic/data","graphic/matrix","graphic/pen","graphic/box","graphic/shapecontainer","graphic/container"],function(a){var b=a("core/class"),c=a("graphic/shape"),d=b.createClass("Mask",{base:c,mixins:[a("graphic/shapecontainer")],constructor:function(){this.callBase("mask")},mask:function(a){return a.getNode().setAttribute("mask","url(#"+this.getId()+")"),this}});return b.extendClass(c,{maskWith:function(a){return a.mask(this),this}}),d}),a("graphic/matrix",["core/utils","graphic/box","core/class","graphic/point"],function(a){function b(a,b){return{a:b.a*a.a+b.c*a.b,b:b.b*a.a+b.d*a.b,c:b.a*a.c+b.c*a.d,d:b.b*a.c+b.d*a.d,e:b.a*a.e+b.c*a.f+b.e,f:b.b*a.e+b.d*a.f+b.f}}function c(a){return a*Math.PI/180}var d=a("core/utils"),e=a("graphic/box"),f=/matrix\((.+)\)/i,g=a("graphic/point"),h=a("core/class").createClass("Matrix",{constructor:function(){arguments.length?this.setMatrix.apply(this,arguments):this.setMatrix(1,0,0,1,0,0)},translate:function(a,c){return this.m=b(this.m,{a:1,c:0,e:a,b:0,d:1,f:c}),this},rotate:function(a){var d=c(a),e=Math.sin(d),f=Math.cos(d);return this.m=b(this.m,{a:f,c:-e,e:0,b:e,d:f,f:0}),this},scale:function(a,c){return void 0===c&&(c=a),this.m=b(this.m,{a:a,c:0,e:0,b:0,d:c,f:0}),this},skew:function(a,d){void 0===d&&(d=a);var e=Math.tan(c(a)),f=Math.tan(c(d));return this.m=b(this.m,{a:1,c:e,e:0,b:f,d:1,f:0}),this},inverse:function(){var a,b,c,d,e,f,g,i=this.m,j=i.a,k=i.b,l=i.c,m=i.d,n=i.e,o=i.f;return a=j*m-k*l,b=m/a,c=-k/a,d=-l/a,e=j/a,f=(l*o-n*m)/a,g=(k*n-j*o)/a,new h(b,c,d,e,f,g)},setMatrix:function(a,b,c,e,f,g){return this.m=1===arguments.length?d.clone(arguments[0]):{a:a,b:b,c:c,d:e,e:f,f:g},this},getMatrix:function(){return d.clone(this.m)},getTranslate:function(){var a=this.m;return{x:a.e/a.a,y:a.f/a.d}},mergeMatrix:function(a){return new h(b(this.m,a.m))},merge:function(a){return this.mergeMatrix(a)},toString:function(){return this.valueOf().join(" ")},valueOf:function(){var a=this.m;return[a.a,a.b,a.c,a.d,a.e,a.f]},equals:function(a){var b=this.m,c=a.m;return b.a==c.a&&b.b==c.b&&b.c==c.c&&b.d==c.d&&b.e==c.e&&b.f==c.f},transformPoint:function(){return h.transformPoint.apply(null,[].slice.call(arguments).concat([this.m]))},transformBox:function(a){return h.transformBox(a,this.m)}});return h.parse=function(a){var b,c=parseFloat;if(a instanceof Array)return new h({a:a[0],b:a[1],c:a[2],d:a[3],e:a[4],f:a[5]});if(b=f.exec(a)){var d=b[1].split(",");return 6!=d.length&&(d=b[1].split(" ")),new h({a:c(d[0]),b:c(d[1]),c:c(d[2]),d:c(d[3]),e:c(d[4]),f:c(d[5])})}return new h},h.transformPoint=function(a,b,c){return 2===arguments.length&&(c=b,b=a.y,a=a.x),new g(c.a*a+c.c*b+c.e,c.b*a+c.d*b+c.f)},h.transformBox=function(a,b){for(var c,f,g=Number.MAX_VALUE,i=-Number.MAX_VALUE,j=Number.MAX_VALUE,k=-Number.MAX_VALUE,l=[[a.x,a.y],[a.x+a.width,a.y],[a.x,a.y+a.height],[a.x+a.width,a.y+a.height]],m=[];c=l.pop();)f=h.transformPoint(c[0],c[1],b),m.push(f),g=Math.min(g,f.x),i=Math.max(i,f.x),j=Math.min(j,f.y),k=Math.max(k,f.y);return a=new e({x:g,y:j,width:i-g,height:k-j}),d.extend(a,{closurePoints:m,left:g,right:i,top:j,bottom:k,cx:(g+i)/2,cy:(j+k)/2}),a},h.getCTM=function(a,b){var c={a:1,b:0,c:0,d:1,e:0,f:0};switch(b=b||"parent"){case"screen":c=a.node.getScreenCTM();break;case"doc":case"paper":c=a.node.getCTM();break;case"view":case"top":a.getPaper()&&(c=a.node.getTransformToElement(a.getPaper().shapeNode));break;case"parent":a.node.parentNode&&(c=a.node.getTransformToElement(a.node.parentNode));break;default:b.node&&(c=a.node.getTransformToElement(b.shapeNode||b.node))}return c?new h(c.a,c.b,c.c,c.d,c.e,c.f):new h},h}),a("graphic/palette",["graphic/standardcolor","graphic/color","core/utils","core/class"],function(a){var b=a("graphic/standardcolor"),c=a("graphic/color"),d=a("core/utils"),e=a("core/class").createClass("Palette",{constructor:function(){this.color={}},get:function(a){var d=this.color[a]||b.EXTEND_STANDARD[a]||b.COLOR_STANDARD[a]||"";return d?new c(d):null},getColorValue:function(a){return this.color[a]||b.EXTEND_STANDARD[a]||b.COLOR_STANDARD[a]||""},add:function(a,b){return this.color[a]="string"==typeof b?new c(b).toRGBA():b.toRGBA(),b},remove:function(a){return this.color.hasOwnProperty(a)?(delete this.color[a],!0):!1}});return d.extend(e,{getColor:function(a){var d=b.EXTEND_STANDARD[a]||b.COLOR_STANDARD[a];return d?new c(d):null},getColorValue:function(a){return b.EXTEND_STANDARD[a]||b.COLOR_STANDARD[a]||""},addColor:function(a,d){return b.EXTEND_STANDARD[a]="string"==typeof d?new c(d).toRGBA():d.toRGBA(),d},removeColor:function(a){return b.EXTEND_STANDARD.hasOwnProperty(a)?(delete b.EXTEND_STANDARD[a],!0):!1}}),e}),a("graphic/paper",["core/class","core/utils","graphic/svg","graphic/container","graphic/shapecontainer","graphic/shape","graphic/viewbox","graphic/eventhandler","graphic/shapeevent","graphic/styled","graphic/matrix","graphic/box","graphic/point","graphic/data","graphic/pen"],function(a){var b=a("core/class"),c=a("core/utils"),d=a("graphic/svg"),e=a("graphic/container"),f=a("graphic/shapecontainer"),g=a("graphic/viewbox"),h=a("graphic/eventhandler"),i=a("graphic/styled"),j=a("graphic/matrix"),k=b.createClass("Paper",{mixins:[f,h,i,g],constructor:function(a){this.callBase(),this.node=this.createSVGNode(),this.node.paper=this,this.node.appendChild(this.resourceNode=d.createNode("defs")),this.node.appendChild(this.shapeNode=d.createNode("g")),this.resources=new e,this.setWidth("100%").setHeight("100%"),a&&this.renderTo(a),this.callMixin()},renderTo:function(a){c.isString(a)&&(a=document.getElementById(a)),this.container=a,a.appendChild(this.node)},createSVGNode:function(){var a=d.createNode("svg");return a.setAttribute("xmlns","http://www.w3.org/2000/svg"),a.setAttribute("xmlns:xlink","http://www.w3.org/1999/xlink"),a},getNode:function(){return this.node},getContainer:function(){return this.container},getWidth:function(){return this.node.clientWidth},setWidth:function(a){return this.node.setAttribute("width",a),this},getHeight:function(){return this.node.clientHeight},setHeight:function(a){return this.node.setAttribute("height",a),this},setViewPort:function(a,b,c){var d,e;1==arguments.length&&(d=arguments[0],a=d.center.x,b=d.center.y,c=d.zoom),c=c||1,e=this.getViewBox();var f=new j,g=e.x+e.width/2-a,h=e.y+e.height/2-b;return f.translate(-a,-b),f.scale(c),f.translate(a,b),f.translate(g,h),this.shapeNode.setAttribute("transform","matrix("+f+")"),this.viewport={center:{x:a,y:b},offset:{x:g,y:h},zoom:c},this},getViewPort:function(){if(!this.viewport){var a=this.getViewBox();return{zoom:1,center:{x:a.x+a.width/2,y:a.y+a.height/2},offset:{x:0,y:0}}}return this.viewport},getViewPortTransform:function(){var a=this.shapeNode.getCTM();return new j(a.a,a.b,a.c,a.d,a.e,a.f)},getTransform:function(){return this.getViewPortTransform().reverse()},addResource:function(a){return this.resources.appendItem(a),a.node&&this.resourceNode.appendChild(a.node),this},removeResource:function(a){return a.remove&&a.remove(),a.node&&this.resourceNode.removeChild(a.node),this
},getPaper:function(){return this}}),l=a("graphic/shape");return b.extendClass(l,{getPaper:function(){for(var a=this.container;a&&a instanceof k==!1;)a=a.container;return a},whenPaperReady:function(a){function b(){var b=c.getPaper();return b&&a&&a.call(c,b),b}var c=this;return b()||this.on("add treeadd",function d(){b()&&(c.off("add",d),c.off("treeadd",d))}),this}}),k}),a("graphic/path",["core/utils","core/class","graphic/shape","graphic/svg","graphic/eventhandler","graphic/styled","graphic/data","graphic/matrix","graphic/pen","graphic/box","graphic/geometry","graphic/point","graphic/vector"],function(a){var b=a("core/utils"),c=a("core/class").createClass,d=a("graphic/shape"),e=a("graphic/svg"),f=a("graphic/geometry"),g=Array.prototype.slice,h=(b.flatten,c("PathDrawer",{constructor:function(a){this.segment=[],this.path=a,this.__clear=!1},getPath:function(){return this.path},redraw:function(){return this._transation=this._transation||[],this.clear()},done:function(){var a=this._transation;return this._transation=null,this.push(a),this},clear:function(){return this._transation?this._transation=[]:this.path.setPathData("M 0 0"),this._clear=!0,this},push:function(){var a,b=g.call(arguments);return this._transation?(this._transation.push(b),this):(this._clear?(a="",this._clear=!1):a=this.path.getPathData(),a=a||"",this.path.setPathData(a+f.pathToString(b)),this)},moveTo:function(){return this.push("M",g.call(arguments))},moveBy:function(){return this.push("m",g.call(arguments))},lineTo:function(){return this.push("L",g.call(arguments))},lineBy:function(){return this.push("l",g.call(arguments))},arcTo:function(){return this.push("A",g.call(arguments))},arcBy:function(){return this.push("a",arguments)},carcTo:function(a){return this.push("A",[a,a,0].concat(g.call(arguments,1)))},carcBy:function(a){return this.push("a",[a,a,0].concat(g.call(arguments,1)))},bezierTo:function(){return this.push("C",g.call(arguments))},bezierBy:function(){return this.push("c",g.call(arguments))},close:function(){return this.push("z")}}));return c("Path",{base:d,constructor:function(a){this.callBase("path"),a&&this.setPathData(a),this.node.setAttribute("fill",e.defaults.fill),this.node.setAttribute("stroke",e.defaults.stroke)},setPathData:function(a){return a=a||"M0,0",this.pathdata=f.pathToString(a),this.node.setAttribute("d",this.pathdata),this.trigger("shapeupdate",{type:"pathdata"}),this},getPathData:function(){return this.pathdata||""},getDrawer:function(){return new h(this)},isClosed:function(){var a=this.getPathData();return!!~a.indexOf("z")||!!~a.indexOf("Z")}})}),a("graphic/patternbrush",["graphic/defbrush","core/class","graphic/resource","graphic/shapecontainer","graphic/container","core/utils","graphic/shape","graphic/svg"],function(a){{var b=a("graphic/defbrush"),c=a("graphic/shapecontainer");a("graphic/svg")}return a("core/class").createClass("PatternBrush",{base:b,mixins:[c],constructor:function(){this.callBase("pattern"),this.node.setAttribute("patternUnits","userSpaceOnUse")},setX:function(a){return this.x=a,this.node.setAttribute("x",a),this},setY:function(a){return this.y=a,this.node.setAttribute("y",a),this},setWidth:function(a){return this.width=a,this.node.setAttribute("width",a),this},setHeight:function(a){return this.height=a,this.node.setAttribute("height",a),this},getWidth:function(){return this.width},getHeight:function(){return this.height}})}),a("graphic/pen",["graphic/color","core/utils","graphic/standardcolor","core/class"],function(a){var b=a("graphic/color");return a("core/class").createClass("Pen",{constructor:function(a,b){this.brush=a,this.width=b||1,this.linecap=null,this.linejoin=null,this.dashArray=null,this.opacity=1},getBrush:function(){return this.brush},setBrush:function(a){return this.brush=a,this},setColor:function(a){return this.setBrush(a)},getColor:function(){return this.brush instanceof b?this.brush:null},getWidth:function(){return this.width},setWidth:function(a){return this.width=a,this},getOpacity:function(){return this.opacity},setOpacity:function(a){this.opacity=a},getLineCap:function(){return this.linecap},setLineCap:function(a){return this.linecap=a,this},getLineJoin:function(){return this.linejoin},setLineJoin:function(a){return this.linejoin=a,this},getDashArray:function(){return this.dashArray},setDashArray:function(a){return this.dashArray=a,this},stroke:function(a){var b=a.node;b.setAttribute("stroke",this.brush.toString()),b.setAttribute("stroke-width",this.getWidth()),this.getOpacity()<1&&b.setAttribute("stroke-opacity",this.getOpacity()),this.getLineCap()&&b.setAttribute("stroke-linecap",this.getLineCap()),this.getLineJoin()&&b.setAttribute("stroke-linejoin",this.getLineJoin()),this.getDashArray()&&b.setAttribute("stroke-dasharray",this.getDashArray())}})}),a("graphic/pie",["core/class","graphic/sweep","graphic/point","graphic/path"],function(a){return a("core/class").createClass({base:a("graphic/sweep"),constructor:function(a,b,c){this.callBase([0,a],b,c)},getRadius:function(){return this.getSectionArray()[1]},setRadius:function(a){this.setSectionArray([0,a])}})}),a("graphic/point",["core/class"],function(a){var b=a("core/class").createClass("Point",{constructor:function(a,b){this.x=a||0,this.y=b||0},offset:function(a,c){return 1==arguments.length&&(c=a.y,a=a.x),new b(this.x+a,this.y+c)},valueOf:function(){return[this.x,this.y]},toString:function(){return this.valueOf().join(" ")},spof:function(){return new b((0|this.x)+.5,(0|this.y)+.5)}});return b.fromPolar=function(a,c,d){return"rad"!=d&&(c=c/180*Math.PI),new b(a*Math.cos(c),a*Math.sin(c))},b.parse=function(a){return a instanceof b?a:"string"==typeof a?b.parse(a.split(/\s*[\s,]\s*/)):"0"in a&&"1"in a?new b(a[0],a[1]):void 0},b}),a("graphic/pointcontainer",["core/class","graphic/container"],function(a){return a("core/class").createClass("PointContainer",{base:a("graphic/container"),constructor:function(){this.callBase()},addPoint:function(){return this.addItem.apply(this,arguments)},prependPoint:function(){return this.prependItem.apply(this,arguments)},appendPoint:function(){return this.appendItem.apply(this,arguments)},removePoint:function(){return this.removeItem.apply(this,arguments)},addPoints:function(){return this.addItems.apply(this,arguments)},setPoints:function(){return this.setItems.apply(this,arguments)},getPoint:function(){return this.getItem.apply(this,arguments)},getPoints:function(){return this.getItems.apply(this,arguments)},getFirstPoint:function(){return this.getFirstItem.apply(this,arguments)},getLastPoint:function(){return this.getLastItem.apply(this,arguments)}})}),a("graphic/poly",["core/utils","core/class","graphic/path","graphic/shape","graphic/svg","graphic/geometry","graphic/pointcontainer","graphic/container"],function(a){a("core/utils");return a("core/class").createClass("Poly",{base:a("graphic/path"),mixins:[a("graphic/pointcontainer")],constructor:function(a,b){this.callBase(),this.closeable=!!b,this.setPoints(a||[]),this.changeable=!0,this.update()},onContainerChanged:function(){this.changeable&&this.update()},update:function(){var a=this.getDrawer(),b=this.getPoints();if(a.clear(),!b.length)return this;a.moveTo(b[0]);for(var c,d=1,e=b.length;e>d;d++)c=b[d],a.lineTo(c);return this.closeable&&b.length>2&&a.close(),this}})}),a("graphic/polygon",["core/class","graphic/poly","core/utils","graphic/path","graphic/pointcontainer"],function(a){return a("core/class").createClass("Polygon",{base:a("graphic/poly"),constructor:function(a){this.callBase(a,!0)}})}),a("graphic/polyline",["core/class","graphic/poly","core/utils","graphic/path","graphic/pointcontainer"],function(a){return a("core/class").createClass("Polyline",{base:a("graphic/poly"),constructor:function(a){this.callBase(a)}})}),a("graphic/radialgradientbrush",["graphic/gradientbrush","graphic/svg","graphic/defbrush","graphic/color","core/class"],function(a){var b=a("graphic/gradientbrush");return a("core/class").createClass("RadialGradientBrush",{base:b,constructor:function(a){this.callBase("radialGradient"),this.setCenter(.5,.5),this.setFocal(.5,.5),this.setRadius(.5),"function"==typeof a&&a.call(this,this)},setCenter:function(a,b){return this.node.setAttribute("cx",a),this.node.setAttribute("cy",b),this},getCenter:function(){return{x:+this.node.getAttribute("cx"),y:+this.node.getAttribute("cy")}},setFocal:function(a,b){return this.node.setAttribute("fx",a),this.node.setAttribute("fy",b),this},getFocal:function(){return{x:+this.node.getAttribute("fx"),y:+this.node.getAttribute("fy")}},setRadius:function(a){return this.node.setAttribute("r",a),this},getRadius:function(){return+this.node.getAttribute("r")}})}),a("graphic/rect",["core/utils","graphic/point","core/class","graphic/path","graphic/shape","graphic/svg","graphic/geometry"],function(a){var b={},c=a("core/utils"),d=a("graphic/point");return c.extend(b,{formatRadius:function(a,b,c){var d=Math.floor(Math.min(a/2,b/2));return Math.min(d,c)}}),a("core/class").createClass("Rect",{base:a("graphic/path"),constructor:function(a,c,d,e,f){this.callBase(),this.x=d||0,this.y=e||0,this.width=a||0,this.height=c||0,this.radius=b.formatRadius(this.width,this.height,f||0),this.update()},update:function(){var a=this.x,b=this.y,c=this.width,d=this.height,e=this.radius,f=this.getDrawer().redraw();return e?(c-=2*e,d-=2*e,f.push("M",a+e,b),f.push("h",c),f.push("a",e,e,0,0,1,e,e),f.push("v",d),f.push("a",e,e,0,0,1,-e,e),f.push("h",-c),f.push("a",e,e,0,0,1,-e,-e),f.push("v",-d),f.push("a",e,e,0,0,1,e,-e),f.push("z")):(f.push("M",a,b),f.push("h",c),f.push("v",d),f.push("h",-c),f.push("z")),f.done(),this},setWidth:function(a){return this.width=a,this.update()},setHeight:function(a){return this.height=a,this.update()},setSize:function(a,b){return this.width=a,this.height=b,this.update()},getRadius:function(){return this.radius},setRadius:function(a){return this.radius=a,this.update()},getPosition:function(){return new d(this.x,this.y)},setPosition:function(a,b){if(1==arguments.length){var c=d.parse(arguments[0]);b=c.y,a=c.x}return this.x=a,this.y=b,this.update()},getWidth:function(){return this.width},getHeight:function(){return this.height},getPositionX:function(){return this.x},getPositionY:function(){return this.y},setPositionX:function(a){return this.x=a,this.update()},setPositionY:function(a){return this.y=a,this.update()}})}),a("graphic/regularpolygon",["graphic/point","core/class","graphic/path","core/utils","graphic/shape","graphic/svg","graphic/geometry"],function(a){var b=a("graphic/point");return a("core/class").createClass("RegularPolygon",{base:a("graphic/path"),constructor:function(a,c,d,e){this.callBase(),this.radius=c||0,this.side=Math.max(a||3,3),arguments.length>2&&3==arguments.length&&(e=d.y,d=d.x),this.center=new b(d,e),this.draw()},getSide:function(){return this.side},setSide:function(a){return this.side=a,this.draw()},getRadius:function(){return this.radius},setRadius:function(a){return this.radius=a,this.draw()},draw:function(){var a,c=this.radius,d=this.side,e=2*Math.PI/d,f=this.getDrawer();for(f.clear(),f.moveTo(b.fromPolar(c,Math.PI/2,"rad").offset(this.center)),a=0;d>=a;a++)f.lineTo(b.fromPolar(c,e*a+Math.PI/2,"rad").offset(this.center));return f.close(),this}})}),a("graphic/resource",["graphic/svg","core/class"],function(a){var b=a("graphic/svg");return a("core/class").createClass("Resource",{constructor:function(a){this.callBase(),this.node=b.createNode(a)},toString:function(){return"url(#"+this.node.id+")"}})}),a("graphic/ring",["core/class","graphic/sweep","graphic/point","graphic/path"],function(a){return a("core/class").createClass({base:a("graphic/sweep"),constructor:function(a,b){this.callBase([a,b],360,0)},getInnerRadius:function(){return this.getSectionArray()[0]},getOuterRadius:function(){return this.getSectionArray()[1]},setInnerRadius:function(a){this.setSectionArray([a,this.getOuterRadius()])},setOuterRadius:function(a){this.setSectionArray([this.getInnerRadius(),a])}})}),a("graphic/shape",["graphic/svg","core/utils","graphic/eventhandler","graphic/shapeevent","core/class","graphic/styled","graphic/data","graphic/matrix","graphic/box","graphic/point","graphic/pen","graphic/color"],function(a){var b=a("graphic/svg"),c=a("core/utils"),d=a("graphic/eventhandler"),e=a("graphic/styled"),f=a("graphic/data"),g=a("graphic/matrix"),h=(a("graphic/pen"),Array.prototype.slice),i=a("graphic/box"),j=a("core/class").createClass("Shape",{mixins:[d,e,f],constructor:function(a){this.node=b.createNode(a),this.node.shape=this,this.transform={translate:null,rotate:null,scale:null,matrix:null},this.callMixin()},getId:function(){return this.node.id},setId:function(a){return this.node.id=a,this},getNode:function(){return this.node},getBoundaryBox:function(){var a;try{a=this.node.getBBox()}catch(b){a={x:this.node.clientLeft,y:this.node.clientTop,width:this.node.clientWidth,height:this.node.clientHeight}}return new i(a)},getRenderBox:function(a){var b=this.getBoundaryBox(),c=this.getTransform(a);return c.transformBox(b)},getWidth:function(){return this.getRenderBox().width},getHeight:function(){return this.getRenderBox().height},getSize:function(){var a=this.getRenderBox();return delete a.x,delete a.y,a},setOpacity:function(a){return this.node.setAttribute("opacity",a),this},getOpacity:function(){var a=this.node.getAttribute("opacity");return a?+a:1},setVisible:function(a){return a?this.node.removeAttribute("display"):this.node.setAttribute("display","none"),this},getVisible:function(){this.node.getAttribute("display")},hasAncestor:function(a){for(var b=this.container;b;){if(b===a)return!0;b=b.container}return!1},getTransform:function(a){return g.getCTM(this,a)},clearTransform:function(){return this.node.removeAttribute("transform"),this.transform={translate:null,rotate:null,scale:null,matrix:null},this.trigger("shapeupdate",{type:"transform"}),this},_applyTransform:function(){var a=this.transform,b=[];return a.translate&&b.push(["translate(",a.translate,")"]),a.rotate&&b.push(["rotate(",a.rotate,")"]),a.scale&&b.push(["scale(",a.scale,")"]),a.matrix&&b.push(["matrix(",a.matrix,")"]),this.node.setAttribute("transform",c.flatten(b).join(" ")),this},setMatrix:function(a){return this.transform.matrix=a,this._applyTransform()},setTranslate:function(a){return this.transform.translate=null!==a&&h.call(arguments)||null,this._applyTransform()},setRotate:function(a){return this.transform.rotate=null!==a&&h.call(arguments)||null,this._applyTransform()},setScale:function(a){return this.transform.scale=null!==a&&h.call(arguments)||null,this._applyTransform()},translate:function(a,b){var c=this.transform.matrix||new g;return void 0===b&&(b=0),this.transform.matrix=c.translate(a,b),this._applyTransform()},rotate:function(a){var b=this.transform.matrix||new g;return this.transform.matrix=b.rotate(a),this._applyTransform()},scale:function(a,b){var c=this.transform.matrix||new g;return void 0===b&&(b=a),this.transform.matrix=c.scale(a,b),this._applyTransform()},skew:function(a,b){var c=this.transform.matrix||new g;return void 0===b&&(b=a),this.transform.matrix=c.skew(a,b),this._applyTransform()},stroke:function(a,b){return a&&a.stroke?a.stroke(this):(this.node.setAttribute("stroke",a.toString()),b&&this.node.setAttribute("stroke-width",b)),this},fill:function(a){return this.node.setAttribute("fill",a.toString()),this},setAttr:function(a,b){var d=this;c.isObject(a)&&c.each(a,function(a,b){d.setAttr(b,a)}),void 0===b||null===b||""===b?this.node.removeAttribute(a):this.node.setAttribute(a,b)},getAttr:function(a){return this.node.getAttribute(a)}});return j}),a("graphic/shapecontainer",["graphic/container","core/class","core/utils","graphic/shape","graphic/svg","graphic/eventhandler","graphic/styled","graphic/data","graphic/matrix","graphic/pen","graphic/box"],function(a){var b=a("graphic/container"),c=a("core/utils"),d=a("core/class").createClass("ShapeContainer",{base:b,isShapeContainer:!0,handleAdd:function(a,b){var c=this.getShapeNode();c.insertBefore(a.node,c.childNodes[b]||null),a.trigger("add",{container:this}),a.notifyTreeModification&&a.notifyTreeModification("treeadd",this)},handleRemove:function(a){var b=this.getShapeNode();b.removeChild(a.node),a.trigger("remove",{container:this}),a.notifyTreeModification&&a.notifyTreeModification("treeremove",this)},notifyTreeModification:function(a,b){this.eachItem(function(c,d){d.notifyTreeModification&&d.notifyTreeModification(a,b),d.trigger(a,{container:b})})},getShape:function(a){return this.getItem(a)},addShape:function(a,b){return this.addItem(a,b)},appendShape:function(a){return this.addShape(a)},prependShape:function(a){return this.addShape(a,0)},replaceShape:function(a,b){var c=this.indexOf(b);if(-1!==c)return this.removeShape(c),this.addShape(a,c),this},addShapeBefore:function(a,b){var c=this.indexOf(b);return this.addShape(a,c)},addShapeAfter:function(a,b){var c=this.indexOf(b);return this.addShape(a,-1===c?void 0:c+1)},addShapes:function(a){return this.addItems(a)},removeShape:function(a){return this.removeItem(a)},getShapes:function(){return this.getItems()},getShapesByType:function(a){function b(e){a.toLowerCase()==e.getType().toLowerCase()&&d.push(e),e.isShapeContainer&&c.each(e.getShapes(),function(a){b(a)})}var d=[];return b(this),d},getShapeById:function(a){return this.getShapeNode().getElementById(a).shape},arrangeShape:function(a,b){return this.removeShape(a).addShape(a,b)},getShapeNode:function(){return this.shapeNode||this.node}}),e=a("graphic/shape");return a("core/class").extendClass(e,{bringTo:function(a){return this.container.arrangeShape(this,a),this},bringFront:function(){return this.bringTo(this.container.indexOf(this)+1)},bringBack:function(){return this.bringTo(this.container.indexOf(this)-1)},bringTop:function(){return this.container.removeShape(this).addShape(this),this},bringRear:function(){return this.bringTo(0)},bringRefer:function(a,b){return a.container&&(this.remove&&this.remove(),a.container.addShape(this,a.container.indexOf(a)+(b||0))),this},bringAbove:function(a){return this.bringRefer(a)},bringBelow:function(a){return this.bringRefer(a,1)},replaceBy:function(a){return this.container&&(a.bringAbove(this),this.remove()),this}}),d}),a("graphic/shapeevent",["graphic/matrix","core/utils","graphic/box","graphic/point","core/class"],function(a){{var b=a("graphic/matrix"),c=a("core/utils");a("graphic/point")}return a("core/class").createClass("ShapeEvent",{constructor:function(a){var b=null;c.isObject(a.target)?c.extend(this,a):(this.type=a.type,b=a.target,b.correspondingUseElement&&(b=b.correspondingUseElement),this.originEvent=a,this.targetShape=b.shape||b.paper||a.currentTarget&&(a.currentTarget.shape||a.currentTarget.paper),a._kityParam&&c.extend(this,a._kityParam))},preventDefault:function(){var a=this.originEvent;return a?a.preventDefault?(a.preventDefault(),a.cancelable):(a.returnValue=!1,!0):!0},getPosition:function(a,c){if(!this.originEvent)return null;var d=this.originEvent.touches?this.originEvent.touches[c||0]:this.originEvent,e=d&&d.clientX||0,f=d&&d.clientY||0,g=this.targetShape.shapeNode||this.targetShape.node,h=b.transformPoint(e,f,g.getScreenCTM().inverse());return b.getCTM(this.targetShape,a||"view").transformPoint(h)},stopPropagation:function(){var a=this.originEvent;return a?void(a.stopPropagation?a.stopPropagation():a.cancelBubble=!1):!0}})}),a("graphic/shapepoint",["core/class","graphic/point"],function(a){return a("core/class").createClass("ShapePoint",{base:a("graphic/point"),constructor:function(a,b){this.callBase(a,b)},setX:function(a){return this.setPoint(a,this.y)},setY:function(a){return this.setPoint(this.x,a)},setPoint:function(a,b){return this.x=a,this.y=b,this.update(),this},getPoint:function(){return this},update:function(){return this.container&&this.container.update&&this.container.update(),this}})}),a("graphic/standardcolor",[],{COLOR_STANDARD:{aliceblue:"#f0f8ff",antiquewhite:"#faebd7",aqua:"#00ffff",aquamarine:"#7fffd4",azure:"#f0ffff",beige:"#f5f5dc",bisque:"#ffe4c4",black:"#000000",blanchedalmond:"#ffebcd",blue:"#0000ff",blueviolet:"#8a2be2",brown:"#a52a2a",burlywood:"#deb887",cadetblue:"#5f9ea0",chartreuse:"#7fff00",chocolate:"#d2691e",coral:"#ff7f50",cornflowerblue:"#6495ed",cornsilk:"#fff8dc",crimson:"#dc143c",cyan:"#00ffff",darkblue:"#00008b",darkcyan:"#008b8b",darkgoldenrod:"#b8860b",darkgray:"#a9a9a9",darkgreen:"#006400",darkgrey:"#a9a9a9",darkkhaki:"#bdb76b",darkmagenta:"#8b008b",darkolivegreen:"#556b2f",darkorange:"#ff8c00",darkorchid:"#9932cc",darkred:"#8b0000",darksalmon:"#e9967a",darkseagreen:"#8fbc8f",darkslateblue:"#483d8b",darkslategray:"#2f4f4f",darkslategrey:"#2f4f4f",darkturquoise:"#00ced1",darkviolet:"#9400d3",deeppink:"#ff1493",deepskyblue:"#00bfff",dimgray:"#696969",dimgrey:"#696969",dodgerblue:"#1e90ff",firebrick:"#b22222",floralwhite:"#fffaf0",forestgreen:"#228b22",fuchsia:"#ff00ff",gainsboro:"#dcdcdc",ghostwhite:"#f8f8ff",gold:"#ffd700",goldenrod:"#daa520",gray:"#808080",green:"#008000",greenyellow:"#adff2f",grey:"#808080",honeydew:"#f0fff0",hotpink:"#ff69b4",indianred:"#cd5c5c",indigo:"#4b0082",ivory:"#fffff0",khaki:"#f0e68c",lavender:"#e6e6fa",lavenderblush:"#fff0f5",lawngreen:"#7cfc00",lemonchiffon:"#fffacd",lightblue:"#add8e6",lightcoral:"#f08080",lightcyan:"#e0ffff",lightgoldenrodyellow:"#fafad2",lightgray:"#d3d3d3",lightgreen:"#90ee90",lightgrey:"#d3d3d3",lightpink:"#ffb6c1",lightsalmon:"#ffa07a",lightseagreen:"#20b2aa",lightskyblue:"#87cefa",lightslategray:"#778899",lightslategrey:"#778899",lightsteelblue:"#b0c4de",lightyellow:"#ffffe0",lime:"#00ff00",limegreen:"#32cd32",linen:"#faf0e6",magenta:"#ff00ff",maroon:"#800000",mediumaquamarine:"#66cdaa",mediumblue:"#0000cd",mediumorchid:"#ba55d3",mediumpurple:"#9370db",mediumseagreen:"#3cb371",mediumslateblue:"#7b68ee",mediumspringgreen:"#00fa9a",mediumturquoise:"#48d1cc",mediumvioletred:"#c71585",midnightblue:"#191970",mintcream:"#f5fffa",mistyrose:"#ffe4e1",moccasin:"#ffe4b5",navajowhite:"#ffdead",navy:"#000080",oldlace:"#fdf5e6",olive:"#808000",olivedrab:"#6b8e23",orange:"#ffa500",orangered:"#ff4500",orchid:"#da70d6",palegoldenrod:"#eee8aa",palegreen:"#98fb98",paleturquoise:"#afeeee",palevioletred:"#db7093",papayawhip:"#ffefd5",peachpuff:"#ffdab9",peru:"#cd853f",pink:"#ffc0cb",plum:"#dda0dd",powderblue:"#b0e0e6",purple:"#800080",red:"#ff0000",rosybrown:"#bc8f8f",royalblue:"#4169e1",saddlebrown:"#8b4513",salmon:"#fa8072",sandybrown:"#f4a460",seagreen:"#2e8b57",seashell:"#fff5ee",sienna:"#a0522d",silver:"#c0c0c0",skyblue:"#87ceeb",slateblue:"#6a5acd",slategray:"#708090",slategrey:"#708090",snow:"#fffafa",springgreen:"#00ff7f",steelblue:"#4682b4",tan:"#d2b48c",teal:"#008080",thistle:"#d8bfd8",tomato:"#ff6347",turquoise:"#40e0d0",violet:"#ee82ee",wheat:"#f5deb3",white:"#ffffff",whitesmoke:"#f5f5f5",yellow:"#ffff00"},EXTEND_STANDARD:{}}),a("graphic/star",["graphic/point","core/class","graphic/path","core/utils","graphic/shape","graphic/svg","graphic/geometry"],function(a){var b={3:.2,5:.38196601125,6:.57735026919,8:.541196100146,10:.726542528005,12:.707106781187},c=a("graphic/point");return a("core/class").createClass("Star",{base:a("graphic/path"),constructor:function(a,b,d,e,f){this.callBase(),this.vertex=a||3,this.radius=b||0,this.shrink=d,this.offset=e||new c(0,0),this.angleOffset=f||0,this.draw()},getVertex:function(){return this.vertex},setVertex:function(a){return this.vertex=a,this.draw()},getRadius:function(){return this.radius},setRadius:function(a){return this.radius=a,this.draw()},getShrink:function(){return this.shrink},setShrink:function(a){return this.shrink=a,this.draw()},getOffset:function(){return this.offset},setOffset:function(a){return this.offset=a,this.draw()},getAngleOffset:function(){return this.angleOffset},setAngleOffset:function(a){return this.angleOffset=a,this.draw()},draw:function(){var a,d,e=this.radius,f=this.radius*(this.shrink||b[this.vertex]||.5),g=this.vertex,h=this.offset,i=90,j=180/g,k=this.angleOffset,l=this.getDrawer();for(l.clear(),l.moveTo(c.fromPolar(f,i)),a=1;2*g>=a;a++)d=i+j*a,l.lineTo(a%2?c.fromPolar(e,d+k).offset(h):c.fromPolar(f,d));l.close()}})}),a("graphic/styled",["core/class"],function(a){function b(a){return a.classList||(a.classList=new c(a)),a.classList}var c=a("core/class").createClass("ClassList",{constructor:function(a){this._node=a,this._list=a.className.toString().split(" ")},_update:function(){this._node.className=this._list.join(" ")},add:function(a){this._list.push(a),this._update()},remove:function(a){var b=this._list.indexOf(a);~b&&this._list.splice(b,1),this._update()},contains:function(a){return!!~this._list.indexOf(a)}});return a("core/class").createClass("Styled",{addClass:function(a){return b(this.node).add(a),this},removeClass:function(a){return b(this.node).remove(a),this},hasClass:function(a){return b(this.node).contains(a)},setStyle:function(a){if(2==arguments.length)return this.node.style[arguments[0]]=arguments[1],this;for(var b in a)a.hasOwnProperty(b)&&(this.node.style[b]=a[b]);return this}})}),a("graphic/svg",[],function(){var a=document,b=0,c={createNode:function(d){var e=a.createElementNS(c.ns,d);return e.id="kity_"+d+"_"+b++,e},defaults:{stroke:"none",fill:"none"},xlink:"http://www.w3.org/1999/xlink",ns:"http://www.w3.org/2000/svg"};return c}),a("graphic/sweep",["graphic/point","core/class","graphic/path","core/utils","graphic/shape","graphic/svg","graphic/geometry"],function(a){var b=a("graphic/point");return a("core/class").createClass("Sweep",{base:a("graphic/path"),constructor:function(a,b,c){this.callBase(),this.sectionArray=a||[],this.angle=b||0,this.angleOffset=c||0,this.draw()},getSectionArray:function(){return this.sectionArray},setSectionArray:function(a){return this.sectionArray=a,this.draw()},getAngle:function(){return this.angle},setAngle:function(a){return this.angle=a,this.draw()},getAngleOffset:function(){return this.angleOffset},setAngleOffset:function(a){return this.angleOffset=a,this.draw()},draw:function(){var a,b=this.sectionArray;for(a=0;a<b.length;a+=2)this.drawSection(b[a],b[a+1]);return this},drawSection:function(a,c){var d=this.angle&&(this.angle%360?this.angle%360:360),e=this.angleOffset,f=e+d/2,g=e+d,h=this.getDrawer();return h.redraw(),0===d?void h.done():(h.moveTo(b.fromPolar(a,e)),h.lineTo(b.fromPolar(c,e)),c&&(h.carcTo(c,0,1,b.fromPolar(c,f)),h.carcTo(c,0,1,b.fromPolar(c,g))),h.lineTo(b.fromPolar(a,g)),a&&(h.carcTo(a,0,1,b.fromPolar(a,f)),h.carcTo(a,0,1,b.fromPolar(a,e))),h.close(),void h.done())}})}),a("graphic/text",["graphic/textcontent","graphic/shape","core/class","graphic/shapecontainer","graphic/container","core/utils","graphic/svg"],function(a){function b(a){var b=window.getComputedStyle(a.node),c=[b.fontFamily,b.fontSize,b.fontStretch,b.fontStyle,b.fontVariant,b.fontWeight].join("-");if(f[c])return f[c];var d=a.getContent();a.setContent("test");var e=a.getBoundaryBox(),g=a.getY()+ +a.node.getAttribute("dy"),h=g-e.y,i=h-e.height;return a.setContent(d),f[c]={top:h,bottom:i,middle:(h+i)/2}}var c=a("graphic/textcontent"),d=a("graphic/shapecontainer"),e=a("graphic/svg"),f={};return a("core/class").createClass("Text",{base:c,mixins:[d],constructor:function(a){this.callBase("text"),void 0!==a&&this.setContent(a)},setX:function(a){return this.node.setAttribute("x",a),this},setPosition:function(a,b){return this.setX(a).setY(b)},setY:function(a){return this.node.setAttribute("y",a),this},getX:function(){return+this.node.getAttribute("x")||0},getY:function(){return+this.node.getAttribute("y")||0},setFont:function(a){return this.callBase(a),this.setVerticalAlign(this.getVerticalAlign())},setTextAnchor:function(a){return this.node.setAttribute("text-anchor",a),this},getTextAnchor:function(){return this.node.getAttribute("text-anchor")||"start"},setVerticalAlign:function(a){return this.whenPaperReady(function(){var c;switch(a){case"top":c=b(this).top;break;case"bottom":c=b(this).bottom;break;case"middle":c=b(this).middle;break;default:c=0}this.node.setAttribute("dy",c)}),this.verticalAlign=a,this},getVerticalAlign:function(){return this.verticalAlign||"baseline"},setStartOffset:function(a){this.shapeNode!=this.node&&this.shapeNode.setAttribute("startOffset",100*a+"%")},addSpan:function(a){return this.addShape(a),this},setPath:function(a){var b=this.shapeNode;if(this.shapeNode==this.node){for(b=this.shapeNode=e.createNode("textPath");this.node.firstChild;)this.shapeNode.appendChild(this.node.firstChild);this.node.appendChild(b)}return b.setAttributeNS(e.xlink,"xlink:href","#"+a.node.id),this.setTextAnchor(this.getTextAnchor()),this}})}),a("graphic/textcontent",["graphic/shape","graphic/svg","core/utils","graphic/eventhandler","graphic/styled","graphic/data","graphic/matrix","graphic/pen","graphic/box","core/class"],function(a){var b=a("graphic/shape");return a("core/class").createClass("TextContent",{base:b,constructor:function(a){this.callBase(a),this.shapeNode=this.shapeNode||this.node},clearContent:function(){for(;this.shapeNode.firstChild;)this.shapeNode.removeChild(this.shapeNode.firstChild);return this},setContent:function(a){return this.shapeNode.textContent=a,this},getContent:function(){return this.shapeNode.textContent},appendContent:function(a){return this.shapeNode.textContent+=a,this},setSize:function(a){return this.setFontSize(a)},setFontSize:function(a){return this.setFont({size:a})},setFontFamily:function(a){return this.setFont({family:a})},setFontBold:function(a){return this.setFont({weight:a?"bold":"normal"})},setFontItalic:function(a){return this.setFont({style:a?"italic":"normal"})},setFont:function(a){var b=this.node;return["family","size","weight","style"].forEach(function(c){null===a[c]?b.removeAttribute("font-"+c):a[c]&&b.setAttribute("font-"+c,a[c])}),this},getExtentOfChar:function(a){return this.node.getExtentOfChar(a)},getRotationOfChar:function(a){return this.node.getRotationOfChar(a)},getCharNumAtPosition:function(a,b){return this.node.getCharNumAtPosition(this.node.viewportElement.createSVGPoint(a,b))}})}),a("graphic/textspan",["graphic/textcontent","graphic/shape","core/class","graphic/styled"],function(a){var b=a("graphic/textcontent"),c=a("graphic/styled");return a("core/class").createClass("TextSpan",{base:b,mixins:[c],constructor:function(a){this.callBase("tspan"),this.setContent(a)}})}),a("graphic/use",["graphic/svg","core/class","graphic/shape","core/utils","graphic/eventhandler","graphic/styled","graphic/data","graphic/matrix","graphic/pen","graphic/box"],function(a){var b=a("graphic/svg"),c=a("core/class"),d=c.createClass("Use",{base:a("graphic/shape"),constructor:function(a){var c=null;this.callBase("use"),c=a.getId(),c&&this.node.setAttributeNS(b.xlink,"xlink:href","#"+c),"none"===a.node.getAttribute("fill")&&a.node.removeAttribute("fill"),"none"===a.node.getAttribute("stroke")&&a.node.removeAttribute("stroke")}}),e=a("graphic/shape");return c.extendClass(e,{use:function(){return new d(this)}}),d}),a("graphic/vector",["graphic/point","core/class","graphic/matrix","core/utils","graphic/box"],function(a){var b=a("graphic/point"),c=a("graphic/matrix"),d=a("core/class").createClass("Vector",{base:b,constructor:function(a,b){this.callBase(a,b)},square:function(){return this.x*this.x+this.y*this.y},length:function(){return Math.sqrt(this.square())},add:function(a){return new d(this.x+a.x,this.y+a.y)},minus:function(a){return new d(this.x-a.x,this.y-a.y)},dot:function(a){return this.x*a.x+this.y*a.y},project:function(a){return a.multipy(this.dot(a)/a.square())},normalize:function(a){return void 0===a&&(a=1),this.multipy(a/this.length())},multipy:function(a){return new d(this.x*a,this.y*a)},rotate:function(a,b){"rad"==b&&(a=a/Math.PI*180);var e=(new c).rotate(a).transformPoint(this);return new d(e.x,e.y)},vertical:function(){return new d(this.y,-this.x)},reverse:function(){return this.multipy(-1)},getAngle:function(){var a=this.length();if(0===a)return 0;var b=Math.acos(this.x/a),c=this.y>0?1:-1;return 180*c*b/Math.PI}});return d.fromPoints=function(a,b){return new d(b.x-a.x,b.y-a.y)},a("core/class").extendClass(b,{asVector:function(){return new d(this.x,this.y)
}}),d}),a("graphic/view",["graphic/shapecontainer","graphic/container","core/utils","core/class","graphic/shape","graphic/viewbox","graphic/view"],function(a){var b=a("graphic/shapecontainer"),c=a("graphic/viewbox");return a("core/class").createClass("View",{mixins:[b,c],base:a("graphic/view"),constructor:function(){this.callBase("view")}})}),a("graphic/viewbox",["core/class"],function(a){return a("core/class").createClass("ViewBox",{getViewBox:function(){var a=this.node.getAttribute("viewBox");return null===a?{x:0,y:0,width:this.node.clientWidth||this.node.parentNode.clientWidth,height:this.node.clientHeight||this.node.parentNode.clientHeight}:(a=a.split(" "),{x:+a[0],y:+a[1],width:+a[2],height:+a[3]})},setViewBox:function(a,b,c,d){return this.node.setAttribute("viewBox",[a,b,c,d].join(" ")),this}})}),a("kity",["core/utils","core/class","core/browser","graphic/bezier","graphic/pointcontainer","graphic/path","graphic/bezierpoint","graphic/shapepoint","graphic/vector","graphic/circle","graphic/ellipse","graphic/clip","graphic/shape","graphic/shapecontainer","graphic/color","graphic/standardcolor","graphic/container","graphic/curve","graphic/point","graphic/gradientbrush","graphic/svg","graphic/defbrush","graphic/group","graphic/hyperlink","graphic/image","graphic/line","graphic/lineargradientbrush","graphic/mask","graphic/matrix","graphic/box","graphic/marker","graphic/resource","graphic/viewbox","graphic/palette","graphic/paper","graphic/eventhandler","graphic/styled","graphic/geometry","graphic/patternbrush","graphic/pen","graphic/polygon","graphic/poly","graphic/polyline","graphic/pie","graphic/sweep","graphic/radialgradientbrush","graphic/rect","graphic/regularpolygon","graphic/ring","graphic/data","graphic/star","graphic/text","graphic/textcontent","graphic/textspan","graphic/use","animate/animator","animate/timeline","animate/easing","animate/opacityanimator","animate/rotateanimator","animate/scaleanimator","animate/frame","animate/translateanimator","animate/pathanimator","animate/motionanimator","filter/filter","filter/effectcontainer","filter/gaussianblurfilter","filter/effect/gaussianblureffect","filter/projectionfilter","filter/effect/effect","filter/effect/colormatrixeffect","filter/effect/compositeeffect","filter/effect/offseteffect","filter/effect/convolvematrixeffect"],function(a){var b={},c=a("core/utils");return b.version="2.0.0",c.extend(b,{createClass:a("core/class").createClass,extendClass:a("core/class").extendClass,Utils:c,Browser:a("core/browser"),Bezier:a("graphic/bezier"),BezierPoint:a("graphic/bezierpoint"),Circle:a("graphic/circle"),Clip:a("graphic/clip"),Color:a("graphic/color"),Container:a("graphic/container"),Curve:a("graphic/curve"),Ellipse:a("graphic/ellipse"),GradientBrush:a("graphic/gradientbrush"),Group:a("graphic/group"),HyperLink:a("graphic/hyperlink"),Image:a("graphic/image"),Line:a("graphic/line"),LinearGradientBrush:a("graphic/lineargradientbrush"),Mask:a("graphic/mask"),Matrix:a("graphic/matrix"),Marker:a("graphic/marker"),Palette:a("graphic/palette"),Paper:a("graphic/paper"),Path:a("graphic/path"),PatternBrush:a("graphic/patternbrush"),Pen:a("graphic/pen"),Point:a("graphic/point"),Polygon:a("graphic/polygon"),Polyline:a("graphic/polyline"),Pie:a("graphic/pie"),RadialGradientBrush:a("graphic/radialgradientbrush"),Rect:a("graphic/rect"),RegularPolygon:a("graphic/regularpolygon"),Ring:a("graphic/ring"),Shape:a("graphic/shape"),ShapePoint:a("graphic/shapepoint"),ShapeContainer:a("graphic/shapecontainer"),Sweep:a("graphic/sweep"),Star:a("graphic/star"),Text:a("graphic/text"),TextSpan:a("graphic/textspan"),Use:a("graphic/use"),Vector:a("graphic/vector"),g:a("graphic/geometry"),Animator:a("animate/animator"),Easing:a("animate/easing"),OpacityAnimator:a("animate/opacityanimator"),RotateAnimator:a("animate/rotateanimator"),ScaleAnimator:a("animate/scaleanimator"),Timeline:a("animate/timeline"),TranslateAnimator:a("animate/translateanimator"),PathAnimator:a("animate/pathanimator"),MotionAnimator:a("animate/motionanimator"),Filter:a("filter/filter"),GaussianblurFilter:a("filter/gaussianblurfilter"),ProjectionFilter:a("filter/projectionfilter"),ColorMatrixEffect:a("filter/effect/colormatrixeffect"),CompositeEffect:a("filter/effect/compositeeffect"),ConvolveMatrixEffect:a("filter/effect/convolvematrixeffect"),Effect:a("filter/effect/effect"),GaussianblurEffect:a("filter/effect/gaussianblureffect"),OffsetEffect:a("filter/effect/offseteffect")}),window.kity=b}),function(){try{inc.use("kity")}catch(a){c("kity")}}()}();

/*!
 * ====================================================
 * Kity Formula Render - v1.0.0 - 2014-07-30
 * https://github.com/kitygraph/formula
 * GitHub: https://github.com/kitygraph/formula.git
 * Copyright (c) 2014 Baidu Kity Group; Licensed MIT
 * ====================================================
 */
!function(){function a(a){b.r([c[a]])}var b={r:function(a){if(b[a].inited)return b[a].value;if("function"!=typeof b[a].value)return b[a].inited=!0,b[a].value;var c={exports:{}},d=b[a].value(null,c.exports,c);if(b[a].inited=!0,b[a].value=d,void 0!==d)return d;for(var e in c.exports)if(c.exports.hasOwnProperty(e))return b[a].inited=!0,b[a].value=c.exports,c.exports}};b[0]={value:function(){function a(b){this.ok=!1,"#"==b.charAt(0)&&(b=b.substr(1,6)),b=b.replace(/ /g,""),b=b.toLowerCase();var c={aliceblue:"f0f8ff",antiquewhite:"faebd7",aqua:"00ffff",aquamarine:"7fffd4",azure:"f0ffff",beige:"f5f5dc",bisque:"ffe4c4",black:"000000",blanchedalmond:"ffebcd",blue:"0000ff",blueviolet:"8a2be2",brown:"a52a2a",burlywood:"deb887",cadetblue:"5f9ea0",chartreuse:"7fff00",chocolate:"d2691e",coral:"ff7f50",cornflowerblue:"6495ed",cornsilk:"fff8dc",crimson:"dc143c",cyan:"00ffff",darkblue:"00008b",darkcyan:"008b8b",darkgoldenrod:"b8860b",darkgray:"a9a9a9",darkgreen:"006400",darkkhaki:"bdb76b",darkmagenta:"8b008b",darkolivegreen:"556b2f",darkorange:"ff8c00",darkorchid:"9932cc",darkred:"8b0000",darksalmon:"e9967a",darkseagreen:"8fbc8f",darkslateblue:"483d8b",darkslategray:"2f4f4f",darkturquoise:"00ced1",darkviolet:"9400d3",deeppink:"ff1493",deepskyblue:"00bfff",dimgray:"696969",dodgerblue:"1e90ff",feldspar:"d19275",firebrick:"b22222",floralwhite:"fffaf0",forestgreen:"228b22",fuchsia:"ff00ff",gainsboro:"dcdcdc",ghostwhite:"f8f8ff",gold:"ffd700",goldenrod:"daa520",gray:"808080",green:"008000",greenyellow:"adff2f",honeydew:"f0fff0",hotpink:"ff69b4",indianred:"cd5c5c",indigo:"4b0082",ivory:"fffff0",khaki:"f0e68c",lavender:"e6e6fa",lavenderblush:"fff0f5",lawngreen:"7cfc00",lemonchiffon:"fffacd",lightblue:"add8e6",lightcoral:"f08080",lightcyan:"e0ffff",lightgoldenrodyellow:"fafad2",lightgrey:"d3d3d3",lightgreen:"90ee90",lightpink:"ffb6c1",lightsalmon:"ffa07a",lightseagreen:"20b2aa",lightskyblue:"87cefa",lightslateblue:"8470ff",lightslategray:"778899",lightsteelblue:"b0c4de",lightyellow:"ffffe0",lime:"00ff00",limegreen:"32cd32",linen:"faf0e6",magenta:"ff00ff",maroon:"800000",mediumaquamarine:"66cdaa",mediumblue:"0000cd",mediumorchid:"ba55d3",mediumpurple:"9370d8",mediumseagreen:"3cb371",mediumslateblue:"7b68ee",mediumspringgreen:"00fa9a",mediumturquoise:"48d1cc",mediumvioletred:"c71585",midnightblue:"191970",mintcream:"f5fffa",mistyrose:"ffe4e1",moccasin:"ffe4b5",navajowhite:"ffdead",navy:"000080",oldlace:"fdf5e6",olive:"808000",olivedrab:"6b8e23",orange:"ffa500",orangered:"ff4500",orchid:"da70d6",palegoldenrod:"eee8aa",palegreen:"98fb98",paleturquoise:"afeeee",palevioletred:"d87093",papayawhip:"ffefd5",peachpuff:"ffdab9",peru:"cd853f",pink:"ffc0cb",plum:"dda0dd",powderblue:"b0e0e6",purple:"800080",red:"ff0000",rosybrown:"bc8f8f",royalblue:"4169e1",saddlebrown:"8b4513",salmon:"fa8072",sandybrown:"f4a460",seagreen:"2e8b57",seashell:"fff5ee",sienna:"a0522d",silver:"c0c0c0",skyblue:"87ceeb",slateblue:"6a5acd",slategray:"708090",snow:"fffafa",springgreen:"00ff7f",steelblue:"4682b4",tan:"d2b48c",teal:"008080",thistle:"d8bfd8",tomato:"ff6347",turquoise:"40e0d0",violet:"ee82ee",violetred:"d02090",wheat:"f5deb3",white:"ffffff",whitesmoke:"f5f5f5",yellow:"ffff00",yellowgreen:"9acd32"};for(var d in c)b==d&&(b=c[d]);for(var e=[{re:/^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/,example:["rgb(123, 234, 45)","rgb(255,234,245)"],process:function(a){return[parseInt(a[1]),parseInt(a[2]),parseInt(a[3])]}},{re:/^(\w{2})(\w{2})(\w{2})$/,example:["#00ff00","336699"],process:function(a){return[parseInt(a[1],16),parseInt(a[2],16),parseInt(a[3],16)]}},{re:/^(\w{1})(\w{1})(\w{1})$/,example:["#fb0","f0f"],process:function(a){return[parseInt(a[1]+a[1],16),parseInt(a[2]+a[2],16),parseInt(a[3]+a[3],16)]}}],f=0;f<e.length;f++){var g=e[f].re,h=e[f].process,i=g.exec(b);i&&(channels=h(i),this.r=channels[0],this.g=channels[1],this.b=channels[2],this.ok=!0)}this.r=this.r<0||isNaN(this.r)?0:this.r>255?255:this.r,this.g=this.g<0||isNaN(this.g)?0:this.g>255?255:this.g,this.b=this.b<0||isNaN(this.b)?0:this.b>255?255:this.b,this.toRGB=function(){return"rgb("+this.r+", "+this.g+", "+this.b+")"},this.toHex=function(){var a=this.r.toString(16),b=this.g.toString(16),c=this.b.toString(16);return 1==a.length&&(a="0"+a),1==b.length&&(b="0"+b),1==c.length&&(c="0"+c),"#"+a+b+c},this.getHelpXML=function(){for(var b=new Array,d=0;d<e.length;d++)for(var f=e[d].example,g=0;g<f.length;g++)b[b.length]=f[g];for(var h in c)b[b.length]=h;var i=document.createElement("ul");i.setAttribute("id","rgbcolor-examples");for(var d=0;d<b.length;d++)try{var j=document.createElement("li"),k=new a(b[d]),l=document.createElement("div");l.style.cssText="margin: 3px; border: 1px solid black; background:"+k.toHex()+"; color:"+k.toHex(),l.appendChild(document.createTextNode("test"));var m=document.createTextNode(" "+b[d]+" -> "+k.toRGB()+" -> "+k.toHex());j.appendChild(l),j.appendChild(m),i.appendChild(j)}catch(n){}return i}}function b(a,b,f,g,h,i){if(!(isNaN(i)||1>i)){i|=0;var j,k=document.getElementById(a),l=k.getContext("2d");try{try{j=l.getImageData(b,f,g,h)}catch(m){try{netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead"),j=l.getImageData(b,f,g,h)}catch(m){throw alert("Cannot access local image"),new Error("unable to access local image data: "+m)}}}catch(m){throw alert("Cannot access image"),new Error("unable to access image data: "+m)}var n,o,p,q,r,s,t,u,v,w,x,y,z,A,B,C,D,E,F,G,H,I,J,K,L=j.data,M=i+i+1,N=g-1,O=h-1,P=i+1,Q=P*(P+1)/2,R=new c,S=R;for(p=1;M>p;p++)if(S=S.next=new c,p==P)var T=S;S.next=R;var U=null,V=null;t=s=0;var W=d[i],X=e[i];for(o=0;h>o;o++){for(C=D=E=F=u=v=w=x=0,y=P*(G=L[s]),z=P*(H=L[s+1]),A=P*(I=L[s+2]),B=P*(J=L[s+3]),u+=Q*G,v+=Q*H,w+=Q*I,x+=Q*J,S=R,p=0;P>p;p++)S.r=G,S.g=H,S.b=I,S.a=J,S=S.next;for(p=1;P>p;p++)q=s+((p>N?N:p)<<2),u+=(S.r=G=L[q])*(K=P-p),v+=(S.g=H=L[q+1])*K,w+=(S.b=I=L[q+2])*K,x+=(S.a=J=L[q+3])*K,C+=G,D+=H,E+=I,F+=J,S=S.next;for(U=R,V=T,n=0;g>n;n++)L[s+3]=J=x*W>>X,0!=J?(J=255/J,L[s]=(u*W>>X)*J,L[s+1]=(v*W>>X)*J,L[s+2]=(w*W>>X)*J):L[s]=L[s+1]=L[s+2]=0,u-=y,v-=z,w-=A,x-=B,y-=U.r,z-=U.g,A-=U.b,B-=U.a,q=t+((q=n+i+1)<N?q:N)<<2,C+=U.r=L[q],D+=U.g=L[q+1],E+=U.b=L[q+2],F+=U.a=L[q+3],u+=C,v+=D,w+=E,x+=F,U=U.next,y+=G=V.r,z+=H=V.g,A+=I=V.b,B+=J=V.a,C-=G,D-=H,E-=I,F-=J,V=V.next,s+=4;t+=g}for(n=0;g>n;n++){for(D=E=F=C=v=w=x=u=0,s=n<<2,y=P*(G=L[s]),z=P*(H=L[s+1]),A=P*(I=L[s+2]),B=P*(J=L[s+3]),u+=Q*G,v+=Q*H,w+=Q*I,x+=Q*J,S=R,p=0;P>p;p++)S.r=G,S.g=H,S.b=I,S.a=J,S=S.next;for(r=g,p=1;i>=p;p++)s=r+n<<2,u+=(S.r=G=L[s])*(K=P-p),v+=(S.g=H=L[s+1])*K,w+=(S.b=I=L[s+2])*K,x+=(S.a=J=L[s+3])*K,C+=G,D+=H,E+=I,F+=J,S=S.next,O>p&&(r+=g);for(s=n,U=R,V=T,o=0;h>o;o++)q=s<<2,L[q+3]=J=x*W>>X,J>0?(J=255/J,L[q]=(u*W>>X)*J,L[q+1]=(v*W>>X)*J,L[q+2]=(w*W>>X)*J):L[q]=L[q+1]=L[q+2]=0,u-=y,v-=z,w-=A,x-=B,y-=U.r,z-=U.g,A-=U.b,B-=U.a,q=n+((q=o+P)<O?q:O)*g<<2,u+=C+=U.r=L[q],v+=D+=U.g=L[q+1],w+=E+=U.b=L[q+2],x+=F+=U.a=L[q+3],U=U.next,y+=G=V.r,z+=H=V.g,A+=I=V.b,B+=J=V.a,C-=G,D-=H,E-=I,F-=J,V=V.next,s+=g}l.putImageData(j,b,f)}}function c(){this.r=0,this.g=0,this.b=0,this.a=0,this.next=null}var d=[512,512,456,512,328,456,335,512,405,328,271,456,388,335,292,512,454,405,364,328,298,271,496,456,420,388,360,335,312,292,273,512,482,454,428,405,383,364,345,328,312,298,284,271,259,496,475,456,437,420,404,388,374,360,347,335,323,312,302,292,282,273,265,512,497,482,468,454,441,428,417,405,394,383,373,364,354,345,337,328,320,312,305,298,291,284,278,271,265,259,507,496,485,475,465,456,446,437,428,420,412,404,396,388,381,374,367,360,354,347,341,335,329,323,318,312,307,302,297,292,287,282,278,273,269,265,261,512,505,497,489,482,475,468,461,454,447,441,435,428,422,417,411,405,399,394,389,383,378,373,368,364,359,354,350,345,341,337,332,328,324,320,316,312,309,305,301,298,294,291,287,284,281,278,274,271,268,265,262,259,257,507,501,496,491,485,480,475,470,465,460,456,451,446,442,437,433,428,424,420,416,412,408,404,400,396,392,388,385,381,377,374,370,367,363,360,357,354,350,347,344,341,338,335,332,329,326,323,320,318,315,312,310,307,304,302,299,297,294,292,289,287,285,282,280,278,275,273,271,269,267,265,263,261,259],e=[9,11,12,13,13,14,14,15,15,15,15,16,16,16,16,17,17,17,17,17,17,17,18,18,18,18,18,18,18,18,18,19,19,19,19,19,19,19,19,19,19,19,19,19,19,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,21,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,22,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,23,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24,24];return function(){function c(){var c={};return c.FRAMERATE=30,c.MAX_VIRTUAL_PIXELS=3e4,c.init=function(a){var b=0;c.UniqueId=function(){return b++,"canvg"+b},c.Definitions={},c.Styles={},c.Animations=[],c.Images=[],c.ctx=a,c.ViewPort=new function(){this.viewPorts=[],this.Clear=function(){this.viewPorts=[]},this.SetCurrent=function(a,b){this.viewPorts.push({width:a,height:b})},this.RemoveCurrent=function(){this.viewPorts.pop()},this.Current=function(){return this.viewPorts[this.viewPorts.length-1]},this.width=function(){return this.Current().width},this.height=function(){return this.Current().height},this.ComputeSize=function(a){return null!=a&&"number"==typeof a?a:"x"==a?this.width():"y"==a?this.height():Math.sqrt(Math.pow(this.width(),2)+Math.pow(this.height(),2))/Math.sqrt(2)}}},c.init(),c.ImagesLoaded=function(){for(var a=0;a<c.Images.length;a++)if(!c.Images[a].loaded)return!1;return!0},c.trim=function(a){return a.replace(/^\s+|\s+$/g,"")},c.compressSpaces=function(a){return a.replace(/[\s\r\t\n]+/gm," ")},c.ajax=function(a){var b;return b=window.XMLHttpRequest?new XMLHttpRequest:new ActiveXObject("Microsoft.XMLHTTP"),b?(b.open("GET",a,!1),b.send(null),b.responseText):null},c.parseXml=function(a){if(window.DOMParser){var b=new DOMParser;return b.parseFromString(a,"text/xml")}a=a.replace(/<!DOCTYPE svg[^>]*>/,"");var c=new ActiveXObject("Microsoft.XMLDOM");return c.async="false",c.loadXML(a),c},c.Property=function(a,b){this.name=a,this.value=b},c.Property.prototype.getValue=function(){return this.value},c.Property.prototype.hasValue=function(){return null!=this.value&&""!==this.value},c.Property.prototype.numValue=function(){if(!this.hasValue())return 0;var a=parseFloat(this.value);return(this.value+"").match(/%$/)&&(a/=100),a},c.Property.prototype.valueOrDefault=function(a){return this.hasValue()?this.value:a},c.Property.prototype.numValueOrDefault=function(a){return this.hasValue()?this.numValue():a},c.Property.prototype.addOpacity=function(b){var d=this.value;if(null!=b&&""!=b&&"string"==typeof this.value){var e=new a(this.value);e.ok&&(d="rgba("+e.r+", "+e.g+", "+e.b+", "+b+")")}return new c.Property(this.name,d)},c.Property.prototype.getDefinition=function(){var a=this.value.match(/#([^\)'"]+)/);return a&&(a=a[1]),a||(a=this.value),c.Definitions[a]},c.Property.prototype.isUrlDefinition=function(){return 0==this.value.indexOf("url(")},c.Property.prototype.getFillStyleDefinition=function(a,b){var d=this.getDefinition();if(null!=d&&d.createGradient)return d.createGradient(c.ctx,a,b);if(null!=d&&d.createPattern){if(d.getHrefAttribute().hasValue()){var e=d.attribute("patternTransform");d=d.getHrefAttribute().getDefinition(),e.hasValue()&&(d.attribute("patternTransform",!0).value=e.value)}return d.createPattern(c.ctx,a)}return null},c.Property.prototype.getDPI=function(){return 96},c.Property.prototype.getEM=function(a){var b=12,d=new c.Property("fontSize",c.Font.Parse(c.ctx.font).fontSize);return d.hasValue()&&(b=d.toPixels(a)),b},c.Property.prototype.getUnits=function(){var a=this.value+"";return a.replace(/[0-9\.\-]/g,"")},c.Property.prototype.toPixels=function(a,b){if(!this.hasValue())return 0;var d=this.value+"";if(d.match(/em$/))return this.numValue()*this.getEM(a);if(d.match(/ex$/))return this.numValue()*this.getEM(a)/2;if(d.match(/px$/))return this.numValue();if(d.match(/pt$/))return this.numValue()*this.getDPI(a)*(1/72);if(d.match(/pc$/))return 15*this.numValue();if(d.match(/cm$/))return this.numValue()*this.getDPI(a)/2.54;if(d.match(/mm$/))return this.numValue()*this.getDPI(a)/25.4;if(d.match(/in$/))return this.numValue()*this.getDPI(a);if(d.match(/%$/))return this.numValue()*c.ViewPort.ComputeSize(a);var e=this.numValue();return b&&1>e?e*c.ViewPort.ComputeSize(a):e},c.Property.prototype.toMilliseconds=function(){if(!this.hasValue())return 0;var a=this.value+"";return a.match(/s$/)?1e3*this.numValue():(a.match(/ms$/),this.numValue())},c.Property.prototype.toRadians=function(){if(!this.hasValue())return 0;var a=this.value+"";return a.match(/deg$/)?this.numValue()*(Math.PI/180):a.match(/grad$/)?this.numValue()*(Math.PI/200):a.match(/rad$/)?this.numValue():this.numValue()*(Math.PI/180)},c.Font=new function(){this.Styles="normal|italic|oblique|inherit",this.Variants="normal|small-caps|inherit",this.Weights="normal|bold|bolder|lighter|100|200|300|400|500|600|700|800|900|inherit",this.CreateFont=function(a,b,d,e,f,g){var h=null!=g?this.Parse(g):this.CreateFont("","","","","",c.ctx.font);return{fontFamily:f||h.fontFamily,fontSize:e||h.fontSize,fontStyle:a||h.fontStyle,fontWeight:d||h.fontWeight,fontVariant:b||h.fontVariant,toString:function(){return[this.fontStyle,this.fontVariant,this.fontWeight,this.fontSize,this.fontFamily].join(" ")}}};var a=this;this.Parse=function(b){for(var d={},e=c.trim(c.compressSpaces(b||"")).split(" "),f={fontSize:!1,fontStyle:!1,fontWeight:!1,fontVariant:!1},g="",h=0;h<e.length;h++)f.fontStyle||-1==a.Styles.indexOf(e[h])?f.fontVariant||-1==a.Variants.indexOf(e[h])?f.fontWeight||-1==a.Weights.indexOf(e[h])?f.fontSize?"inherit"!=e[h]&&(g+=e[h]):("inherit"!=e[h]&&(d.fontSize=e[h].split("/")[0]),f.fontStyle=f.fontVariant=f.fontWeight=f.fontSize=!0):("inherit"!=e[h]&&(d.fontWeight=e[h]),f.fontStyle=f.fontVariant=f.fontWeight=!0):("inherit"!=e[h]&&(d.fontVariant=e[h]),f.fontStyle=f.fontVariant=!0):("inherit"!=e[h]&&(d.fontStyle=e[h]),f.fontStyle=!0);return""!=g&&(d.fontFamily=g),d}},c.ToNumberArray=function(a){for(var b=c.trim(c.compressSpaces((a||"").replace(/,/g," "))).split(" "),d=0;d<b.length;d++)b[d]=parseFloat(b[d]);return b},c.Point=function(a,b){this.x=a,this.y=b},c.Point.prototype.angleTo=function(a){return Math.atan2(a.y-this.y,a.x-this.x)},c.Point.prototype.applyTransform=function(a){var b=this.x*a[0]+this.y*a[2]+a[4],c=this.x*a[1]+this.y*a[3]+a[5];this.x=b,this.y=c},c.CreatePoint=function(a){var b=c.ToNumberArray(a);return new c.Point(b[0],b[1])},c.CreatePath=function(a){for(var b=c.ToNumberArray(a),d=[],e=0;e<b.length;e+=2)d.push(new c.Point(b[e],b[e+1]));return d},c.BoundingBox=function(a,b,c,d){this.x1=Number.NaN,this.y1=Number.NaN,this.x2=Number.NaN,this.y2=Number.NaN,this.x=function(){return this.x1},this.y=function(){return this.y1},this.width=function(){return this.x2-this.x1},this.height=function(){return this.y2-this.y1},this.addPoint=function(a,b){null!=a&&((isNaN(this.x1)||isNaN(this.x2))&&(this.x1=a,this.x2=a),a<this.x1&&(this.x1=a),a>this.x2&&(this.x2=a)),null!=b&&((isNaN(this.y1)||isNaN(this.y2))&&(this.y1=b,this.y2=b),b<this.y1&&(this.y1=b),b>this.y2&&(this.y2=b))},this.addX=function(a){this.addPoint(a,null)},this.addY=function(a){this.addPoint(null,a)},this.addBoundingBox=function(a){this.addPoint(a.x1,a.y1),this.addPoint(a.x2,a.y2)},this.addQuadraticCurve=function(a,b,c,d,e,f){var g=a+2/3*(c-a),h=b+2/3*(d-b),i=g+1/3*(e-a),j=h+1/3*(f-b);this.addBezierCurve(a,b,g,i,h,j,e,f)},this.addBezierCurve=function(a,b,c,d,e,f,g,h){var j=[a,b],k=[c,d],l=[e,f],m=[g,h];for(this.addPoint(j[0],j[1]),this.addPoint(m[0],m[1]),i=0;1>=i;i++){var n=function(a){return Math.pow(1-a,3)*j[i]+3*Math.pow(1-a,2)*a*k[i]+3*(1-a)*Math.pow(a,2)*l[i]+Math.pow(a,3)*m[i]},o=6*j[i]-12*k[i]+6*l[i],p=-3*j[i]+9*k[i]-9*l[i]+3*m[i],q=3*k[i]-3*j[i];if(0!=p){var r=Math.pow(o,2)-4*q*p;if(!(0>r)){var s=(-o+Math.sqrt(r))/(2*p);s>0&&1>s&&(0==i&&this.addX(n(s)),1==i&&this.addY(n(s)));var t=(-o-Math.sqrt(r))/(2*p);t>0&&1>t&&(0==i&&this.addX(n(t)),1==i&&this.addY(n(t)))}}else{if(0==o)continue;var u=-q/o;u>0&&1>u&&(0==i&&this.addX(n(u)),1==i&&this.addY(n(u)))}}},this.isPointInBox=function(a,b){return this.x1<=a&&a<=this.x2&&this.y1<=b&&b<=this.y2},this.addPoint(a,b),this.addPoint(c,d)},c.Transform=function(a){var b=this;this.Type={},this.Type.translate=function(a){this.p=c.CreatePoint(a),this.apply=function(a){a.translate(this.p.x||0,this.p.y||0)},this.unapply=function(a){a.translate(-1*this.p.x||0,-1*this.p.y||0)},this.applyToPoint=function(a){a.applyTransform([1,0,0,1,this.p.x||0,this.p.y||0])}},this.Type.rotate=function(a){var b=c.ToNumberArray(a);this.angle=new c.Property("angle",b[0]),this.cx=b[1]||0,this.cy=b[2]||0,this.apply=function(a){a.translate(this.cx,this.cy),a.rotate(this.angle.toRadians()),a.translate(-this.cx,-this.cy)},this.unapply=function(a){a.translate(this.cx,this.cy),a.rotate(-1*this.angle.toRadians()),a.translate(-this.cx,-this.cy)},this.applyToPoint=function(a){var b=this.angle.toRadians();a.applyTransform([1,0,0,1,this.p.x||0,this.p.y||0]),a.applyTransform([Math.cos(b),Math.sin(b),-Math.sin(b),Math.cos(b),0,0]),a.applyTransform([1,0,0,1,-this.p.x||0,-this.p.y||0])}},this.Type.scale=function(a){this.p=c.CreatePoint(a),this.apply=function(a){a.scale(this.p.x||1,this.p.y||this.p.x||1)},this.unapply=function(a){a.scale(1/this.p.x||1,1/this.p.y||this.p.x||1)},this.applyToPoint=function(a){a.applyTransform([this.p.x||0,0,0,this.p.y||0,0,0])}},this.Type.matrix=function(a){this.m=c.ToNumberArray(a),this.apply=function(a){a.transform(this.m[0],this.m[1],this.m[2],this.m[3],this.m[4],this.m[5])},this.applyToPoint=function(a){a.applyTransform(this.m)}},this.Type.SkewBase=function(a){this.base=b.Type.matrix,this.base(a),this.angle=new c.Property("angle",a)},this.Type.SkewBase.prototype=new this.Type.matrix,this.Type.skewX=function(a){this.base=b.Type.SkewBase,this.base(a),this.m=[1,0,Math.tan(this.angle.toRadians()),1,0,0]},this.Type.skewX.prototype=new this.Type.SkewBase,this.Type.skewY=function(a){this.base=b.Type.SkewBase,this.base(a),this.m=[1,Math.tan(this.angle.toRadians()),0,1,0,0]},this.Type.skewY.prototype=new this.Type.SkewBase,this.transforms=[],this.apply=function(a){for(var b=0;b<this.transforms.length;b++)this.transforms[b].apply(a)},this.unapply=function(a){for(var b=this.transforms.length-1;b>=0;b--)this.transforms[b].unapply(a)},this.applyToPoint=function(a){for(var b=0;b<this.transforms.length;b++)this.transforms[b].applyToPoint(a)};for(var d=c.trim(c.compressSpaces(a)).replace(/\)(\s?,\s?)/g,") ").split(/\s(?=[a-z])/),e=0;e<d.length;e++){var f=c.trim(d[e].split("(")[0]),g=d[e].split("(")[1].replace(")",""),h=new this.Type[f](g);h.type=f,this.transforms.push(h)}},c.AspectRatio=function(a,b,d,e,f,g,h,i,j,k){b=c.compressSpaces(b),b=b.replace(/^defer\s/,"");var l=b.split(" ")[0]||"xMidYMid",m=b.split(" ")[1]||"meet",n=d/e,o=f/g,p=Math.min(n,o),q=Math.max(n,o);"meet"==m&&(e*=p,g*=p),"slice"==m&&(e*=q,g*=q),j=new c.Property("refX",j),k=new c.Property("refY",k),j.hasValue()&&k.hasValue()?a.translate(-p*j.toPixels("x"),-p*k.toPixels("y")):(l.match(/^xMid/)&&("meet"==m&&p==o||"slice"==m&&q==o)&&a.translate(d/2-e/2,0),l.match(/YMid$/)&&("meet"==m&&p==n||"slice"==m&&q==n)&&a.translate(0,f/2-g/2),l.match(/^xMax/)&&("meet"==m&&p==o||"slice"==m&&q==o)&&a.translate(d-e,0),l.match(/YMax$/)&&("meet"==m&&p==n||"slice"==m&&q==n)&&a.translate(0,f-g)),"none"==l?a.scale(n,o):"meet"==m?a.scale(p,p):"slice"==m&&a.scale(q,q),a.translate(null==h?0:-h,null==i?0:-i)},c.Element={},c.EmptyProperty=new c.Property("EMPTY",""),c.Element.ElementBase=function(a){if(this.attributes={},this.styles={},this.children=[],this.attribute=function(a,b){var d=this.attributes[a];return null!=d?d:(1==b&&(d=new c.Property(a,""),this.attributes[a]=d),d||c.EmptyProperty)},this.getHrefAttribute=function(){for(var a in this.attributes)if(a.match(/:href$/))return this.attributes[a];return c.EmptyProperty},this.style=function(a,b){var d=this.styles[a];if(null!=d)return d;var e=this.attribute(a);if(null!=e&&e.hasValue())return this.styles[a]=e,e;var f=this.parent;if(null!=f){var g=f.style(a);if(null!=g&&g.hasValue())return g}return 1==b&&(d=new c.Property(a,""),this.styles[a]=d),d||c.EmptyProperty},this.render=function(a){if("none"!=this.style("display").value&&"hidden"!=this.attribute("visibility").value){if(a.save(),this.attribute("mask").hasValue()){var b=this.attribute("mask").getDefinition();null!=b&&b.apply(a,this)}else if(this.style("filter").hasValue()){var c=this.style("filter").getDefinition();null!=c&&c.apply(a,this)}else this.setContext(a),this.renderChildren(a),this.clearContext(a);a.restore()}},this.setContext=function(){},this.clearContext=function(){},this.renderChildren=function(a){for(var b=0;b<this.children.length;b++)this.children[b].render(a)},this.addChild=function(a,b){var d=a;b&&(d=c.CreateElement(a)),d.parent=this,this.children.push(d)},null!=a&&1==a.nodeType){for(var b=0;b<a.childNodes.length;b++){var d=a.childNodes[b];if(1==d.nodeType&&this.addChild(d,!0),this.captureTextNodes&&3==d.nodeType){var e=d.nodeValue||d.text||"";""!=c.trim(c.compressSpaces(e))&&this.addChild(new c.Element.tspan(d),!1)}}for(var b=0;b<a.attributes.length;b++){var f=a.attributes[b];this.attributes[f.nodeName]=new c.Property(f.nodeName,f.nodeValue)}var g=c.Styles[a.nodeName];if(null!=g)for(var h in g)this.styles[h]=g[h];if(this.attribute("class").hasValue())for(var i=c.compressSpaces(this.attribute("class").value).split(" "),j=0;j<i.length;j++){if(g=c.Styles["."+i[j]],null!=g)for(var h in g)this.styles[h]=g[h];if(g=c.Styles[a.nodeName+"."+i[j]],null!=g)for(var h in g)this.styles[h]=g[h]}if(this.attribute("id").hasValue()){var g=c.Styles["#"+this.attribute("id").value];if(null!=g)for(var h in g)this.styles[h]=g[h]}if(this.attribute("style").hasValue())for(var g=this.attribute("style").value.split(";"),b=0;b<g.length;b++)if(""!=c.trim(g[b])){var k=g[b].split(":"),h=c.trim(k[0]),l=c.trim(k[1]);this.styles[h]=new c.Property(h,l)}this.attribute("id").hasValue()&&null==c.Definitions[this.attribute("id").value]&&(c.Definitions[this.attribute("id").value]=this)}},c.Element.RenderedElementBase=function(a){this.base=c.Element.ElementBase,this.base(a),this.setContext=function(a){if(this.style("fill").isUrlDefinition()){var b=this.style("fill").getFillStyleDefinition(this,this.style("fill-opacity"));null!=b&&(a.fillStyle=b)}else if(this.style("fill").hasValue()){var d=this.style("fill");"currentColor"==d.value&&(d.value=this.style("color").value),a.fillStyle="none"==d.value?"rgba(0,0,0,0)":d.value}if(this.style("fill-opacity").hasValue()){var d=new c.Property("fill",a.fillStyle);d=d.addOpacity(this.style("fill-opacity").value),a.fillStyle=d.value}if(this.style("stroke").isUrlDefinition()){var b=this.style("stroke").getFillStyleDefinition(this,this.style("stroke-opacity"));null!=b&&(a.strokeStyle=b)}else if(this.style("stroke").hasValue()){var e=this.style("stroke");"currentColor"==e.value&&(e.value=this.style("color").value),a.strokeStyle="none"==e.value?"rgba(0,0,0,0)":e.value}if(this.style("stroke-opacity").hasValue()){var e=new c.Property("stroke",a.strokeStyle);e=e.addOpacity(this.style("stroke-opacity").value),a.strokeStyle=e.value}if(this.style("stroke-width").hasValue()){var f=this.style("stroke-width").toPixels();a.lineWidth=0==f?.001:f}if(this.style("stroke-linecap").hasValue()&&(a.lineCap=this.style("stroke-linecap").value),this.style("stroke-linejoin").hasValue()&&(a.lineJoin=this.style("stroke-linejoin").value),this.style("stroke-miterlimit").hasValue()&&(a.miterLimit=this.style("stroke-miterlimit").value),this.style("stroke-dasharray").hasValue()){var g=c.ToNumberArray(this.style("stroke-dasharray").value);"undefined"!=typeof a.setLineDash?a.setLineDash(g):"undefined"!=typeof a.webkitLineDash?a.webkitLineDash=g:"undefined"!=typeof a.mozDash&&(a.mozDash=g);var h=this.style("stroke-dashoffset").numValueOrDefault(1);"undefined"!=typeof a.lineDashOffset?a.lineDashOffset=h:"undefined"!=typeof a.webkitLineDashOffset?a.webkitLineDashOffset=h:"undefined"!=typeof a.mozDashOffset&&(a.mozDashOffset=h)}if("undefined"!=typeof a.font&&(a.font=c.Font.CreateFont(this.style("font-style").value,this.style("font-variant").value,this.style("font-weight").value,this.style("font-size").hasValue()?this.style("font-size").toPixels()+"px":"",this.style("font-family").value).toString()),this.attribute("transform").hasValue()){var i=new c.Transform(this.attribute("transform").value);i.apply(a)}if(this.style("clip-path").hasValue()){var j=this.style("clip-path").getDefinition();null!=j&&j.apply(a)}this.style("opacity").hasValue()&&(a.globalAlpha=this.style("opacity").numValue())}},c.Element.RenderedElementBase.prototype=new c.Element.ElementBase,c.Element.PathElementBase=function(a){this.base=c.Element.RenderedElementBase,this.base(a),this.path=function(a){return null!=a&&a.beginPath(),new c.BoundingBox},this.renderChildren=function(a){this.path(a),c.Mouse.checkPath(this,a),""!=a.fillStyle&&(this.attribute("fill-rule").hasValue()?a.fill(this.attribute("fill-rule").value):a.fill()),""!=a.strokeStyle&&a.stroke();var b=this.getMarkers();if(null!=b){if(this.style("marker-start").isUrlDefinition()){var d=this.style("marker-start").getDefinition();d.render(a,b[0][0],b[0][1])}if(this.style("marker-mid").isUrlDefinition())for(var d=this.style("marker-mid").getDefinition(),e=1;e<b.length-1;e++)d.render(a,b[e][0],b[e][1]);if(this.style("marker-end").isUrlDefinition()){var d=this.style("marker-end").getDefinition();d.render(a,b[b.length-1][0],b[b.length-1][1])}}},this.getBoundingBox=function(){return this.path()},this.getMarkers=function(){return null}},c.Element.PathElementBase.prototype=new c.Element.RenderedElementBase,c.Element.svg=function(a){this.base=c.Element.RenderedElementBase,this.base(a),this.baseClearContext=this.clearContext,this.clearContext=function(a){this.baseClearContext(a),c.ViewPort.RemoveCurrent()},this.baseSetContext=this.setContext,this.setContext=function(a){a.strokeStyle="rgba(0,0,0,0)",a.lineCap="butt",a.lineJoin="miter",a.miterLimit=4,this.baseSetContext(a),this.attribute("x").hasValue()||(this.attribute("x",!0).value=0),this.attribute("y").hasValue()||(this.attribute("y",!0).value=0),a.translate(this.attribute("x").toPixels("x"),this.attribute("y").toPixels("y"));var b=c.ViewPort.width(),d=c.ViewPort.height();if(this.attribute("width").hasValue()||(this.attribute("width",!0).value="100%"),this.attribute("height").hasValue()||(this.attribute("height",!0).value="100%"),"undefined"==typeof this.root){b=this.attribute("width").toPixels("x"),d=this.attribute("height").toPixels("y");var e=0,f=0;this.attribute("refX").hasValue()&&this.attribute("refY").hasValue()&&(e=-this.attribute("refX").toPixels("x"),f=-this.attribute("refY").toPixels("y")),a.beginPath(),a.moveTo(e,f),a.lineTo(b,f),a.lineTo(b,d),a.lineTo(e,d),a.closePath(),a.clip()}if(c.ViewPort.SetCurrent(b,d),this.attribute("viewBox").hasValue()){var g=c.ToNumberArray(this.attribute("viewBox").value),h=g[0],i=g[1];b=g[2],d=g[3],c.AspectRatio(a,this.attribute("preserveAspectRatio").value,c.ViewPort.width(),b,c.ViewPort.height(),d,h,i,this.attribute("refX").value,this.attribute("refY").value),c.ViewPort.RemoveCurrent(),c.ViewPort.SetCurrent(g[2],g[3])}}},c.Element.svg.prototype=new c.Element.RenderedElementBase,c.Element.rect=function(a){this.base=c.Element.PathElementBase,this.base(a),this.path=function(a){var b=this.attribute("x").toPixels("x"),d=this.attribute("y").toPixels("y"),e=this.attribute("width").toPixels("x"),f=this.attribute("height").toPixels("y"),g=this.attribute("rx").toPixels("x"),h=this.attribute("ry").toPixels("y");return this.attribute("rx").hasValue()&&!this.attribute("ry").hasValue()&&(h=g),this.attribute("ry").hasValue()&&!this.attribute("rx").hasValue()&&(g=h),g=Math.min(g,e/2),h=Math.min(h,f/2),null!=a&&(a.beginPath(),a.moveTo(b+g,d),a.lineTo(b+e-g,d),a.quadraticCurveTo(b+e,d,b+e,d+h),a.lineTo(b+e,d+f-h),a.quadraticCurveTo(b+e,d+f,b+e-g,d+f),a.lineTo(b+g,d+f),a.quadraticCurveTo(b,d+f,b,d+f-h),a.lineTo(b,d+h),a.quadraticCurveTo(b,d,b+g,d),a.closePath()),new c.BoundingBox(b,d,b+e,d+f)}},c.Element.rect.prototype=new c.Element.PathElementBase,c.Element.circle=function(a){this.base=c.Element.PathElementBase,this.base(a),this.path=function(a){var b=this.attribute("cx").toPixels("x"),d=this.attribute("cy").toPixels("y"),e=this.attribute("r").toPixels();return null!=a&&(a.beginPath(),a.arc(b,d,e,0,2*Math.PI,!0),a.closePath()),new c.BoundingBox(b-e,d-e,b+e,d+e)}},c.Element.circle.prototype=new c.Element.PathElementBase,c.Element.ellipse=function(a){this.base=c.Element.PathElementBase,this.base(a),this.path=function(a){var b=4*((Math.sqrt(2)-1)/3),d=this.attribute("rx").toPixels("x"),e=this.attribute("ry").toPixels("y"),f=this.attribute("cx").toPixels("x"),g=this.attribute("cy").toPixels("y");return null!=a&&(a.beginPath(),a.moveTo(f,g-e),a.bezierCurveTo(f+b*d,g-e,f+d,g-b*e,f+d,g),a.bezierCurveTo(f+d,g+b*e,f+b*d,g+e,f,g+e),a.bezierCurveTo(f-b*d,g+e,f-d,g+b*e,f-d,g),a.bezierCurveTo(f-d,g-b*e,f-b*d,g-e,f,g-e),a.closePath()),new c.BoundingBox(f-d,g-e,f+d,g+e)}},c.Element.ellipse.prototype=new c.Element.PathElementBase,c.Element.line=function(a){this.base=c.Element.PathElementBase,this.base(a),this.getPoints=function(){return[new c.Point(this.attribute("x1").toPixels("x"),this.attribute("y1").toPixels("y")),new c.Point(this.attribute("x2").toPixels("x"),this.attribute("y2").toPixels("y"))]},this.path=function(a){var b=this.getPoints();return null!=a&&(a.beginPath(),a.moveTo(b[0].x,b[0].y),a.lineTo(b[1].x,b[1].y)),new c.BoundingBox(b[0].x,b[0].y,b[1].x,b[1].y)},this.getMarkers=function(){var a=this.getPoints(),b=a[0].angleTo(a[1]);return[[a[0],b],[a[1],b]]}},c.Element.line.prototype=new c.Element.PathElementBase,c.Element.polyline=function(a){this.base=c.Element.PathElementBase,this.base(a),this.points=c.CreatePath(this.attribute("points").value),this.path=function(a){var b=new c.BoundingBox(this.points[0].x,this.points[0].y);null!=a&&(a.beginPath(),a.moveTo(this.points[0].x,this.points[0].y));for(var d=1;d<this.points.length;d++)b.addPoint(this.points[d].x,this.points[d].y),null!=a&&a.lineTo(this.points[d].x,this.points[d].y);return b},this.getMarkers=function(){for(var a=[],b=0;b<this.points.length-1;b++)a.push([this.points[b],this.points[b].angleTo(this.points[b+1])]);return a.push([this.points[this.points.length-1],a[a.length-1][1]]),a}},c.Element.polyline.prototype=new c.Element.PathElementBase,c.Element.polygon=function(a){this.base=c.Element.polyline,this.base(a),this.basePath=this.path,this.path=function(a){var b=this.basePath(a);return null!=a&&(a.lineTo(this.points[0].x,this.points[0].y),a.closePath()),b}},c.Element.polygon.prototype=new c.Element.polyline,c.Element.path=function(a){this.base=c.Element.PathElementBase,this.base(a);var b=this.attribute("d").value;b=b.replace(/,/gm," "),b=b.replace(/([MmZzLlHhVvCcSsQqTtAa])([MmZzLlHhVvCcSsQqTtAa])/gm,"$1 $2"),b=b.replace(/([MmZzLlHhVvCcSsQqTtAa])([MmZzLlHhVvCcSsQqTtAa])/gm,"$1 $2"),b=b.replace(/([MmZzLlHhVvCcSsQqTtAa])([^\s])/gm,"$1 $2"),b=b.replace(/([^\s])([MmZzLlHhVvCcSsQqTtAa])/gm,"$1 $2"),b=b.replace(/([0-9])([+\-])/gm,"$1 $2"),b=b.replace(/(\.[0-9]*)(\.)/gm,"$1 $2"),b=b.replace(/([Aa](\s+[0-9]+){3})\s+([01])\s*([01])/gm,"$1 $3 $4 "),b=c.compressSpaces(b),b=c.trim(b),this.PathParser=new function(a){this.tokens=a.split(" "),this.reset=function(){this.i=-1,this.command="",this.previousCommand="",this.start=new c.Point(0,0),this.control=new c.Point(0,0),this.current=new c.Point(0,0),this.points=[],this.angles=[]},this.isEnd=function(){return this.i>=this.tokens.length-1},this.isCommandOrEnd=function(){return this.isEnd()?!0:null!=this.tokens[this.i+1].match(/^[A-Za-z]$/)},this.isRelativeCommand=function(){switch(this.command){case"m":case"l":case"h":case"v":case"c":case"s":case"q":case"t":case"a":case"z":return!0
}return!1},this.getToken=function(){return this.i++,this.tokens[this.i]},this.getScalar=function(){return parseFloat(this.getToken())},this.nextCommand=function(){this.previousCommand=this.command,this.command=this.getToken()},this.getPoint=function(){var a=new c.Point(this.getScalar(),this.getScalar());return this.makeAbsolute(a)},this.getAsControlPoint=function(){var a=this.getPoint();return this.control=a,a},this.getAsCurrentPoint=function(){var a=this.getPoint();return this.current=a,a},this.getReflectedControlPoint=function(){if("c"!=this.previousCommand.toLowerCase()&&"s"!=this.previousCommand.toLowerCase()&&"q"!=this.previousCommand.toLowerCase()&&"t"!=this.previousCommand.toLowerCase())return this.current;var a=new c.Point(2*this.current.x-this.control.x,2*this.current.y-this.control.y);return a},this.makeAbsolute=function(a){return this.isRelativeCommand()&&(a.x+=this.current.x,a.y+=this.current.y),a},this.addMarker=function(a,b,c){null!=c&&this.angles.length>0&&null==this.angles[this.angles.length-1]&&(this.angles[this.angles.length-1]=this.points[this.points.length-1].angleTo(c)),this.addMarkerAngle(a,null==b?null:b.angleTo(a))},this.addMarkerAngle=function(a,b){this.points.push(a),this.angles.push(b)},this.getMarkerPoints=function(){return this.points},this.getMarkerAngles=function(){for(var a=0;a<this.angles.length;a++)if(null==this.angles[a])for(var b=a+1;b<this.angles.length;b++)if(null!=this.angles[b]){this.angles[a]=this.angles[b];break}return this.angles}}(b),this.path=function(a){var b=this.PathParser;b.reset();var d=new c.BoundingBox;for(null!=a&&a.beginPath();!b.isEnd();)switch(b.nextCommand(),b.command){case"M":case"m":var e=b.getAsCurrentPoint();for(b.addMarker(e),d.addPoint(e.x,e.y),null!=a&&a.moveTo(e.x,e.y),b.start=b.current;!b.isCommandOrEnd();){var e=b.getAsCurrentPoint();b.addMarker(e,b.start),d.addPoint(e.x,e.y),null!=a&&a.lineTo(e.x,e.y)}break;case"L":case"l":for(;!b.isCommandOrEnd();){var f=b.current,e=b.getAsCurrentPoint();b.addMarker(e,f),d.addPoint(e.x,e.y),null!=a&&a.lineTo(e.x,e.y)}break;case"H":case"h":for(;!b.isCommandOrEnd();){var g=new c.Point((b.isRelativeCommand()?b.current.x:0)+b.getScalar(),b.current.y);b.addMarker(g,b.current),b.current=g,d.addPoint(b.current.x,b.current.y),null!=a&&a.lineTo(b.current.x,b.current.y)}break;case"V":case"v":for(;!b.isCommandOrEnd();){var g=new c.Point(b.current.x,(b.isRelativeCommand()?b.current.y:0)+b.getScalar());b.addMarker(g,b.current),b.current=g,d.addPoint(b.current.x,b.current.y),null!=a&&a.lineTo(b.current.x,b.current.y)}break;case"C":case"c":for(;!b.isCommandOrEnd();){var h=b.current,i=b.getPoint(),j=b.getAsControlPoint(),k=b.getAsCurrentPoint();b.addMarker(k,j,i),d.addBezierCurve(h.x,h.y,i.x,i.y,j.x,j.y,k.x,k.y),null!=a&&a.bezierCurveTo(i.x,i.y,j.x,j.y,k.x,k.y)}break;case"S":case"s":for(;!b.isCommandOrEnd();){var h=b.current,i=b.getReflectedControlPoint(),j=b.getAsControlPoint(),k=b.getAsCurrentPoint();b.addMarker(k,j,i),d.addBezierCurve(h.x,h.y,i.x,i.y,j.x,j.y,k.x,k.y),null!=a&&a.bezierCurveTo(i.x,i.y,j.x,j.y,k.x,k.y)}break;case"Q":case"q":for(;!b.isCommandOrEnd();){var h=b.current,j=b.getAsControlPoint(),k=b.getAsCurrentPoint();b.addMarker(k,j,j),d.addQuadraticCurve(h.x,h.y,j.x,j.y,k.x,k.y),null!=a&&a.quadraticCurveTo(j.x,j.y,k.x,k.y)}break;case"T":case"t":for(;!b.isCommandOrEnd();){var h=b.current,j=b.getReflectedControlPoint();b.control=j;var k=b.getAsCurrentPoint();b.addMarker(k,j,j),d.addQuadraticCurve(h.x,h.y,j.x,j.y,k.x,k.y),null!=a&&a.quadraticCurveTo(j.x,j.y,k.x,k.y)}break;case"A":case"a":for(;!b.isCommandOrEnd();){var h=b.current,l=b.getScalar(),m=b.getScalar(),n=b.getScalar()*(Math.PI/180),o=b.getScalar(),p=b.getScalar(),k=b.getAsCurrentPoint(),q=new c.Point(Math.cos(n)*(h.x-k.x)/2+Math.sin(n)*(h.y-k.y)/2,-Math.sin(n)*(h.x-k.x)/2+Math.cos(n)*(h.y-k.y)/2),r=Math.pow(q.x,2)/Math.pow(l,2)+Math.pow(q.y,2)/Math.pow(m,2);r>1&&(l*=Math.sqrt(r),m*=Math.sqrt(r));var s=(o==p?-1:1)*Math.sqrt((Math.pow(l,2)*Math.pow(m,2)-Math.pow(l,2)*Math.pow(q.y,2)-Math.pow(m,2)*Math.pow(q.x,2))/(Math.pow(l,2)*Math.pow(q.y,2)+Math.pow(m,2)*Math.pow(q.x,2)));isNaN(s)&&(s=0);var t=new c.Point(s*l*q.y/m,s*-m*q.x/l),u=new c.Point((h.x+k.x)/2+Math.cos(n)*t.x-Math.sin(n)*t.y,(h.y+k.y)/2+Math.sin(n)*t.x+Math.cos(n)*t.y),v=function(a){return Math.sqrt(Math.pow(a[0],2)+Math.pow(a[1],2))},w=function(a,b){return(a[0]*b[0]+a[1]*b[1])/(v(a)*v(b))},x=function(a,b){return(a[0]*b[1]<a[1]*b[0]?-1:1)*Math.acos(w(a,b))},y=x([1,0],[(q.x-t.x)/l,(q.y-t.y)/m]),z=[(q.x-t.x)/l,(q.y-t.y)/m],A=[(-q.x-t.x)/l,(-q.y-t.y)/m],B=x(z,A);w(z,A)<=-1&&(B=Math.PI),w(z,A)>=1&&(B=0);var C=1-p?1:-1,D=y+C*(B/2),E=new c.Point(u.x+l*Math.cos(D),u.y+m*Math.sin(D));if(b.addMarkerAngle(E,D-C*Math.PI/2),b.addMarkerAngle(k,D-C*Math.PI),d.addPoint(k.x,k.y),null!=a){var w=l>m?l:m,F=l>m?1:l/m,G=l>m?m/l:1;a.translate(u.x,u.y),a.rotate(n),a.scale(F,G),a.arc(0,0,w,y,y+B,1-p),a.scale(1/F,1/G),a.rotate(-n),a.translate(-u.x,-u.y)}}break;case"Z":case"z":null!=a&&a.closePath(),b.current=b.start}return d},this.getMarkers=function(){for(var a=this.PathParser.getMarkerPoints(),b=this.PathParser.getMarkerAngles(),c=[],d=0;d<a.length;d++)c.push([a[d],b[d]]);return c}},c.Element.path.prototype=new c.Element.PathElementBase,c.Element.pattern=function(a){this.base=c.Element.ElementBase,this.base(a),this.createPattern=function(a){var b=this.attribute("width").toPixels("x",!0),d=this.attribute("height").toPixels("y",!0),e=new c.Element.svg;e.attributes.viewBox=new c.Property("viewBox",this.attribute("viewBox").value),e.attributes.width=new c.Property("width",b+"px"),e.attributes.height=new c.Property("height",d+"px"),e.attributes.transform=new c.Property("transform",this.attribute("patternTransform").value),e.children=this.children;var f=document.createElement("canvas");f.width=b,f.height=d;var g=f.getContext("2d");this.attribute("x").hasValue()&&this.attribute("y").hasValue()&&g.translate(this.attribute("x").toPixels("x",!0),this.attribute("y").toPixels("y",!0));for(var h=-1;1>=h;h++)for(var i=-1;1>=i;i++)g.save(),g.translate(h*f.width,i*f.height),e.render(g),g.restore();var j=a.createPattern(f,"repeat");return j}},c.Element.pattern.prototype=new c.Element.ElementBase,c.Element.marker=function(a){this.base=c.Element.ElementBase,this.base(a),this.baseRender=this.render,this.render=function(a,b,d){a.translate(b.x,b.y),"auto"==this.attribute("orient").valueOrDefault("auto")&&a.rotate(d),"strokeWidth"==this.attribute("markerUnits").valueOrDefault("strokeWidth")&&a.scale(a.lineWidth,a.lineWidth),a.save();var e=new c.Element.svg;e.attributes.viewBox=new c.Property("viewBox",this.attribute("viewBox").value),e.attributes.refX=new c.Property("refX",this.attribute("refX").value),e.attributes.refY=new c.Property("refY",this.attribute("refY").value),e.attributes.width=new c.Property("width",this.attribute("markerWidth").value),e.attributes.height=new c.Property("height",this.attribute("markerHeight").value),e.attributes.fill=new c.Property("fill",this.attribute("fill").valueOrDefault("black")),e.attributes.stroke=new c.Property("stroke",this.attribute("stroke").valueOrDefault("none")),e.children=this.children,e.render(a),a.restore(),"strokeWidth"==this.attribute("markerUnits").valueOrDefault("strokeWidth")&&a.scale(1/a.lineWidth,1/a.lineWidth),"auto"==this.attribute("orient").valueOrDefault("auto")&&a.rotate(-d),a.translate(-b.x,-b.y)}},c.Element.marker.prototype=new c.Element.ElementBase,c.Element.defs=function(a){this.base=c.Element.ElementBase,this.base(a),this.render=function(){}},c.Element.defs.prototype=new c.Element.ElementBase,c.Element.GradientBase=function(a){this.base=c.Element.ElementBase,this.base(a),this.gradientUnits=this.attribute("gradientUnits").valueOrDefault("objectBoundingBox"),this.stops=[];for(var b=0;b<this.children.length;b++){var d=this.children[b];"stop"==d.type&&this.stops.push(d)}this.getGradient=function(){},this.createGradient=function(a,b,d){var e=this;this.getHrefAttribute().hasValue()&&(e=this.getHrefAttribute().getDefinition());var f=function(a){if(d.hasValue()){var b=new c.Property("color",a);return b.addOpacity(d.value).value}return a},g=this.getGradient(a,b);if(null==g)return f(e.stops[e.stops.length-1].color);for(var h=0;h<e.stops.length;h++)g.addColorStop(e.stops[h].offset,f(e.stops[h].color));if(this.attribute("gradientTransform").hasValue()){var i=c.ViewPort.viewPorts[0],j=new c.Element.rect;j.attributes.x=new c.Property("x",-c.MAX_VIRTUAL_PIXELS/3),j.attributes.y=new c.Property("y",-c.MAX_VIRTUAL_PIXELS/3),j.attributes.width=new c.Property("width",c.MAX_VIRTUAL_PIXELS),j.attributes.height=new c.Property("height",c.MAX_VIRTUAL_PIXELS);var k=new c.Element.g;k.attributes.transform=new c.Property("transform",this.attribute("gradientTransform").value),k.children=[j];var l=new c.Element.svg;l.attributes.x=new c.Property("x",0),l.attributes.y=new c.Property("y",0),l.attributes.width=new c.Property("width",i.width),l.attributes.height=new c.Property("height",i.height),l.children=[k];var m=document.createElement("canvas");m.width=i.width,m.height=i.height;var n=m.getContext("2d");return n.fillStyle=g,l.render(n),n.createPattern(m,"no-repeat")}return g}},c.Element.GradientBase.prototype=new c.Element.ElementBase,c.Element.linearGradient=function(a){this.base=c.Element.GradientBase,this.base(a),this.getGradient=function(a,b){var c=b.getBoundingBox();this.attribute("x1").hasValue()||this.attribute("y1").hasValue()||this.attribute("x2").hasValue()||this.attribute("y2").hasValue()||(this.attribute("x1",!0).value=0,this.attribute("y1",!0).value=0,this.attribute("x2",!0).value=1,this.attribute("y2",!0).value=0);var d="objectBoundingBox"==this.gradientUnits?c.x()+c.width()*this.attribute("x1").numValue():this.attribute("x1").toPixels("x"),e="objectBoundingBox"==this.gradientUnits?c.y()+c.height()*this.attribute("y1").numValue():this.attribute("y1").toPixels("y"),f="objectBoundingBox"==this.gradientUnits?c.x()+c.width()*this.attribute("x2").numValue():this.attribute("x2").toPixels("x"),g="objectBoundingBox"==this.gradientUnits?c.y()+c.height()*this.attribute("y2").numValue():this.attribute("y2").toPixels("y");return d==f&&e==g?null:a.createLinearGradient(d,e,f,g)}},c.Element.linearGradient.prototype=new c.Element.GradientBase,c.Element.radialGradient=function(a){this.base=c.Element.GradientBase,this.base(a),this.getGradient=function(a,b){var c=b.getBoundingBox();this.attribute("cx").hasValue()||(this.attribute("cx",!0).value="50%"),this.attribute("cy").hasValue()||(this.attribute("cy",!0).value="50%"),this.attribute("r").hasValue()||(this.attribute("r",!0).value="50%");var d="objectBoundingBox"==this.gradientUnits?c.x()+c.width()*this.attribute("cx").numValue():this.attribute("cx").toPixels("x"),e="objectBoundingBox"==this.gradientUnits?c.y()+c.height()*this.attribute("cy").numValue():this.attribute("cy").toPixels("y"),f=d,g=e;this.attribute("fx").hasValue()&&(f="objectBoundingBox"==this.gradientUnits?c.x()+c.width()*this.attribute("fx").numValue():this.attribute("fx").toPixels("x")),this.attribute("fy").hasValue()&&(g="objectBoundingBox"==this.gradientUnits?c.y()+c.height()*this.attribute("fy").numValue():this.attribute("fy").toPixels("y"));var h="objectBoundingBox"==this.gradientUnits?(c.width()+c.height())/2*this.attribute("r").numValue():this.attribute("r").toPixels();return a.createRadialGradient(f,g,0,d,e,h)}},c.Element.radialGradient.prototype=new c.Element.GradientBase,c.Element.stop=function(a){this.base=c.Element.ElementBase,this.base(a),this.offset=this.attribute("offset").numValue(),this.offset<0&&(this.offset=0),this.offset>1&&(this.offset=1);var b=this.style("stop-color");this.style("stop-opacity").hasValue()&&(b=b.addOpacity(this.style("stop-opacity").value)),this.color=b.value},c.Element.stop.prototype=new c.Element.ElementBase,c.Element.AnimateBase=function(a){this.base=c.Element.ElementBase,this.base(a),c.Animations.push(this),this.duration=0,this.begin=this.attribute("begin").toMilliseconds(),this.maxDuration=this.begin+this.attribute("dur").toMilliseconds(),this.getProperty=function(){var a=this.attribute("attributeType").value,b=this.attribute("attributeName").value;return"CSS"==a?this.parent.style(b,!0):this.parent.attribute(b,!0)},this.initialValue=null,this.initialUnits="",this.removed=!1,this.calcValue=function(){return""},this.update=function(a){if(null==this.initialValue&&(this.initialValue=this.getProperty().value,this.initialUnits=this.getProperty().getUnits()),this.duration>this.maxDuration){if("indefinite"!=this.attribute("repeatCount").value&&"indefinite"!=this.attribute("repeatDur").value)return"remove"!=this.attribute("fill").valueOrDefault("remove")||this.removed?!1:(this.removed=!0,this.getProperty().value=this.initialValue,!0);this.duration=0}this.duration=this.duration+a;var b=!1;if(this.begin<this.duration){var c=this.calcValue();if(this.attribute("type").hasValue()){var d=this.attribute("type").value;c=d+"("+c+")"}this.getProperty().value=c,b=!0}return b},this.from=this.attribute("from"),this.to=this.attribute("to"),this.values=this.attribute("values"),this.values.hasValue()&&(this.values.value=this.values.value.split(";")),this.progress=function(){var a={progress:(this.duration-this.begin)/(this.maxDuration-this.begin)};if(this.values.hasValue()){var b=a.progress*(this.values.value.length-1),d=Math.floor(b),e=Math.ceil(b);a.from=new c.Property("from",parseFloat(this.values.value[d])),a.to=new c.Property("to",parseFloat(this.values.value[e])),a.progress=(b-d)/(e-d)}else a.from=this.from,a.to=this.to;return a}},c.Element.AnimateBase.prototype=new c.Element.ElementBase,c.Element.animate=function(a){this.base=c.Element.AnimateBase,this.base(a),this.calcValue=function(){var a=this.progress(),b=a.from.numValue()+(a.to.numValue()-a.from.numValue())*a.progress;return b+this.initialUnits}},c.Element.animate.prototype=new c.Element.AnimateBase,c.Element.animateColor=function(b){this.base=c.Element.AnimateBase,this.base(b),this.calcValue=function(){var b=this.progress(),c=new a(b.from.value),d=new a(b.to.value);if(c.ok&&d.ok){var e=c.r+(d.r-c.r)*b.progress,f=c.g+(d.g-c.g)*b.progress,g=c.b+(d.b-c.b)*b.progress;return"rgb("+parseInt(e,10)+","+parseInt(f,10)+","+parseInt(g,10)+")"}return this.attribute("from").value}},c.Element.animateColor.prototype=new c.Element.AnimateBase,c.Element.animateTransform=function(a){this.base=c.Element.AnimateBase,this.base(a),this.calcValue=function(){for(var a=this.progress(),b=c.ToNumberArray(a.from.value),d=c.ToNumberArray(a.to.value),e="",f=0;f<b.length;f++)e+=b[f]+(d[f]-b[f])*a.progress+" ";return e}},c.Element.animateTransform.prototype=new c.Element.animate,c.Element.font=function(a){this.base=c.Element.ElementBase,this.base(a),this.horizAdvX=this.attribute("horiz-adv-x").numValue(),this.isRTL=!1,this.isArabic=!1,this.fontFace=null,this.missingGlyph=null,this.glyphs=[];for(var b=0;b<this.children.length;b++){var d=this.children[b];"font-face"==d.type?(this.fontFace=d,d.style("font-family").hasValue()&&(c.Definitions[d.style("font-family").value]=this)):"missing-glyph"==d.type?this.missingGlyph=d:"glyph"==d.type&&(""!=d.arabicForm?(this.isRTL=!0,this.isArabic=!0,"undefined"==typeof this.glyphs[d.unicode]&&(this.glyphs[d.unicode]=[]),this.glyphs[d.unicode][d.arabicForm]=d):this.glyphs[d.unicode]=d)}},c.Element.font.prototype=new c.Element.ElementBase,c.Element.fontface=function(a){this.base=c.Element.ElementBase,this.base(a),this.ascent=this.attribute("ascent").value,this.descent=this.attribute("descent").value,this.unitsPerEm=this.attribute("units-per-em").numValue()},c.Element.fontface.prototype=new c.Element.ElementBase,c.Element.missingglyph=function(a){this.base=c.Element.path,this.base(a),this.horizAdvX=0},c.Element.missingglyph.prototype=new c.Element.path,c.Element.glyph=function(a){this.base=c.Element.path,this.base(a),this.horizAdvX=this.attribute("horiz-adv-x").numValue(),this.unicode=this.attribute("unicode").value,this.arabicForm=this.attribute("arabic-form").value},c.Element.glyph.prototype=new c.Element.path,c.Element.text=function(a){this.captureTextNodes=!0,this.base=c.Element.RenderedElementBase,this.base(a),this.baseSetContext=this.setContext,this.setContext=function(a){this.baseSetContext(a),this.style("dominant-baseline").hasValue()&&(a.textBaseline=this.style("dominant-baseline").value),this.style("alignment-baseline").hasValue()&&(a.textBaseline=this.style("alignment-baseline").value)},this.getBoundingBox=function(){return new c.BoundingBox(this.attribute("x").toPixels("x"),this.attribute("y").toPixels("y"),0,0)},this.renderChildren=function(a){this.x=this.attribute("x").toPixels("x"),this.y=this.attribute("y").toPixels("y"),this.x+=this.getAnchorDelta(a,this,0);for(var b=0;b<this.children.length;b++)this.renderChild(a,this,b)},this.getAnchorDelta=function(a,b,c){var d=this.style("text-anchor").valueOrDefault("start");if("start"!=d){for(var e=0,f=c;f<b.children.length;f++){var g=b.children[f];if(f>c&&g.attribute("x").hasValue())break;e+=g.measureTextRecursive(a)}return-1*("end"==d?e:e/2)}return 0},this.renderChild=function(a,b,c){var d=b.children[c];d.attribute("x").hasValue()?d.x=d.attribute("x").toPixels("x")+this.getAnchorDelta(a,b,c):(this.attribute("dx").hasValue()&&(this.x+=this.attribute("dx").toPixels("x")),d.attribute("dx").hasValue()&&(this.x+=d.attribute("dx").toPixels("x")),d.x=this.x),this.x=d.x+d.measureText(a),d.attribute("y").hasValue()?d.y=d.attribute("y").toPixels("y"):(this.attribute("dy").hasValue()&&(this.y+=this.attribute("dy").toPixels("y")),d.attribute("dy").hasValue()&&(this.y+=d.attribute("dy").toPixels("y")),d.y=this.y),this.y=d.y,d.render(a);for(var c=0;c<d.children.length;c++)this.renderChild(a,d,c)}},c.Element.text.prototype=new c.Element.RenderedElementBase,c.Element.TextElementBase=function(a){this.base=c.Element.RenderedElementBase,this.base(a),this.getGlyph=function(a,b,c){var d=b[c],e=null;if(a.isArabic){var f="isolated";(0==c||" "==b[c-1])&&c<b.length-2&&" "!=b[c+1]&&(f="terminal"),c>0&&" "!=b[c-1]&&c<b.length-2&&" "!=b[c+1]&&(f="medial"),c>0&&" "!=b[c-1]&&(c==b.length-1||" "==b[c+1])&&(f="initial"),"undefined"!=typeof a.glyphs[d]&&(e=a.glyphs[d][f],null==e&&"glyph"==a.glyphs[d].type&&(e=a.glyphs[d]))}else e=a.glyphs[d];return null==e&&(e=a.missingGlyph),e},this.renderChildren=function(a){var b=this.parent.style("font-family").getDefinition();if(null==b)""!=a.fillStyle&&a.fillText(c.compressSpaces(this.getText()),this.x,this.y),""!=a.strokeStyle&&a.strokeText(c.compressSpaces(this.getText()),this.x,this.y);else{var d=this.parent.style("font-size").numValueOrDefault(c.Font.Parse(c.ctx.font).fontSize),e=this.parent.style("font-style").valueOrDefault(c.Font.Parse(c.ctx.font).fontStyle),f=this.getText();b.isRTL&&(f=f.split("").reverse().join(""));for(var g=c.ToNumberArray(this.parent.attribute("dx").value),h=0;h<f.length;h++){var i=this.getGlyph(b,f,h),j=d/b.fontFace.unitsPerEm;a.translate(this.x,this.y),a.scale(j,-j);var k=a.lineWidth;a.lineWidth=a.lineWidth*b.fontFace.unitsPerEm/d,"italic"==e&&a.transform(1,0,.4,1,0,0),i.render(a),"italic"==e&&a.transform(1,0,-.4,1,0,0),a.lineWidth=k,a.scale(1/j,-1/j),a.translate(-this.x,-this.y),this.x+=d*(i.horizAdvX||b.horizAdvX)/b.fontFace.unitsPerEm,"undefined"==typeof g[h]||isNaN(g[h])||(this.x+=g[h])}}},this.getText=function(){},this.measureTextRecursive=function(a){for(var b=this.measureText(a),c=0;c<this.children.length;c++)b+=this.children[c].measureTextRecursive(a);return b},this.measureText=function(a){var b=this.parent.style("font-family").getDefinition();if(null!=b){var d=this.parent.style("font-size").numValueOrDefault(c.Font.Parse(c.ctx.font).fontSize),e=0,f=this.getText();b.isRTL&&(f=f.split("").reverse().join(""));for(var g=c.ToNumberArray(this.parent.attribute("dx").value),h=0;h<f.length;h++){var i=this.getGlyph(b,f,h);e+=(i.horizAdvX||b.horizAdvX)*d/b.fontFace.unitsPerEm,"undefined"==typeof g[h]||isNaN(g[h])||(e+=g[h])}return e}var j=c.compressSpaces(this.getText());if(!a.measureText)return 10*j.length;a.save(),this.setContext(a);var k=a.measureText(j).width;return a.restore(),k}},c.Element.TextElementBase.prototype=new c.Element.RenderedElementBase,c.Element.tspan=function(a){this.captureTextNodes=!0,this.base=c.Element.TextElementBase,this.base(a),this.text=a.nodeValue||a.text||"",this.getText=function(){return this.text}},c.Element.tspan.prototype=new c.Element.TextElementBase,c.Element.tref=function(a){this.base=c.Element.TextElementBase,this.base(a),this.getText=function(){var a=this.getHrefAttribute().getDefinition();return null!=a?a.children[0].getText():void 0}},c.Element.tref.prototype=new c.Element.TextElementBase,c.Element.a=function(a){this.base=c.Element.TextElementBase,this.base(a),this.hasText=!0;for(var b=0;b<a.childNodes.length;b++)3!=a.childNodes[b].nodeType&&(this.hasText=!1);this.text=this.hasText?a.childNodes[0].nodeValue:"",this.getText=function(){return this.text},this.baseRenderChildren=this.renderChildren,this.renderChildren=function(a){if(this.hasText){this.baseRenderChildren(a);var b=new c.Property("fontSize",c.Font.Parse(c.ctx.font).fontSize);c.Mouse.checkBoundingBox(this,new c.BoundingBox(this.x,this.y-b.toPixels("y"),this.x+this.measureText(a),this.y))}else{var d=new c.Element.g;d.children=this.children,d.parent=this,d.render(a)}},this.onclick=function(){window.open(this.getHrefAttribute().value)},this.onmousemove=function(){c.ctx.canvas.style.cursor="pointer"}},c.Element.a.prototype=new c.Element.TextElementBase,c.Element.image=function(a){this.base=c.Element.RenderedElementBase,this.base(a);var b=this.getHrefAttribute().value,d=b.match(/\.svg$/);if(c.Images.push(this),this.loaded=!1,d)this.img=c.ajax(b),this.loaded=!0;else{this.img=document.createElement("img");var e=this;this.img.onload=function(){e.loaded=!0},this.img.onerror=function(){"undefined"!=typeof console&&(console.log('ERROR: image "'+b+'" not found'),e.loaded=!0)},this.img.src=b}this.renderChildren=function(a){var b=this.attribute("x").toPixels("x"),e=this.attribute("y").toPixels("y"),f=this.attribute("width").toPixels("x"),g=this.attribute("height").toPixels("y");0!=f&&0!=g&&(a.save(),d?a.drawSvg(this.img,b,e,f,g):(a.translate(b,e),c.AspectRatio(a,this.attribute("preserveAspectRatio").value,f,this.img.width,g,this.img.height,0,0),a.drawImage(this.img,0,0)),a.restore())},this.getBoundingBox=function(){var a=this.attribute("x").toPixels("x"),b=this.attribute("y").toPixels("y"),d=this.attribute("width").toPixels("x"),e=this.attribute("height").toPixels("y");return new c.BoundingBox(a,b,a+d,b+e)}},c.Element.image.prototype=new c.Element.RenderedElementBase,c.Element.g=function(a){this.base=c.Element.RenderedElementBase,this.base(a),this.getBoundingBox=function(){for(var a=new c.BoundingBox,b=0;b<this.children.length;b++)a.addBoundingBox(this.children[b].getBoundingBox());return a}},c.Element.g.prototype=new c.Element.RenderedElementBase,c.Element.symbol=function(a){this.base=c.Element.RenderedElementBase,this.base(a),this.baseSetContext=this.setContext,this.setContext=function(a){if(this.baseSetContext(a),this.attribute("viewBox").hasValue()){var b=c.ToNumberArray(this.attribute("viewBox").value),d=b[0],e=b[1];width=b[2],height=b[3],c.AspectRatio(a,this.attribute("preserveAspectRatio").value,this.attribute("width").toPixels("x"),width,this.attribute("height").toPixels("y"),height,d,e),c.ViewPort.SetCurrent(b[2],b[3])}}},c.Element.symbol.prototype=new c.Element.RenderedElementBase,c.Element.style=function(a){this.base=c.Element.ElementBase,this.base(a);for(var b="",d=0;d<a.childNodes.length;d++)b+=a.childNodes[d].nodeValue;b=b.replace(/(\/\*([^*]|[\r\n]|(\*+([^*\/]|[\r\n])))*\*+\/)|(^[\s]*\/\/.*)/gm,""),b=c.compressSpaces(b);for(var e=b.split("}"),d=0;d<e.length;d++)if(""!=c.trim(e[d]))for(var f=e[d].split("{"),g=f[0].split(","),h=f[1].split(";"),i=0;i<g.length;i++){var j=c.trim(g[i]);if(""!=j){for(var k={},l=0;l<h.length;l++){var m=h[l].indexOf(":"),n=h[l].substr(0,m),o=h[l].substr(m+1,h[l].length-m);null!=n&&null!=o&&(k[c.trim(n)]=new c.Property(c.trim(n),c.trim(o)))}if(c.Styles[j]=k,"@font-face"==j)for(var p=k["font-family"].value.replace(/"/g,""),q=k.src.value.split(","),r=0;r<q.length;r++)if(q[r].indexOf('format("svg")')>0)for(var s=q[r].indexOf("url"),t=q[r].indexOf(")",s),u=q[r].substr(s+5,t-s-6),v=c.parseXml(c.ajax(u)),w=v.getElementsByTagName("font"),x=0;x<w.length;x++){var y=c.CreateElement(w[x]);c.Definitions[p]=y}}}},c.Element.style.prototype=new c.Element.ElementBase,c.Element.use=function(a){this.base=c.Element.RenderedElementBase,this.base(a),this.baseSetContext=this.setContext,this.setContext=function(a){this.baseSetContext(a),this.attribute("x").hasValue()&&a.translate(this.attribute("x").toPixels("x"),0),this.attribute("y").hasValue()&&a.translate(0,this.attribute("y").toPixels("y"))},this.getDefinition=function(){var a=this.getHrefAttribute().getDefinition();return this.attribute("width").hasValue()&&(a.attribute("width",!0).value=this.attribute("width").value),this.attribute("height").hasValue()&&(a.attribute("height",!0).value=this.attribute("height").value),a},this.path=function(a){var b=this.getDefinition();null!=b&&b.path(a)},this.getBoundingBox=function(){var a=this.getDefinition();return null!=a?a.getBoundingBox():void 0},this.renderChildren=function(a){var b=this.getDefinition();if(null!=b){var c=b.parent;b.parent=null,b.render(a),b.parent=c}}},c.Element.use.prototype=new c.Element.RenderedElementBase,c.Element.mask=function(a){this.base=c.Element.ElementBase,this.base(a),this.apply=function(a,b){var d=this.attribute("x").toPixels("x"),e=this.attribute("y").toPixels("y"),f=this.attribute("width").toPixels("x"),g=this.attribute("height").toPixels("y");if(0==f&&0==g){for(var h=new c.BoundingBox,i=0;i<this.children.length;i++)h.addBoundingBox(this.children[i].getBoundingBox());var d=Math.floor(h.x1),e=Math.floor(h.y1),f=Math.floor(h.width()),g=Math.floor(h.height())}var j=b.attribute("mask").value;b.attribute("mask").value="";var k=document.createElement("canvas");k.width=d+f,k.height=e+g;var l=k.getContext("2d");this.renderChildren(l);var m=document.createElement("canvas");m.width=d+f,m.height=e+g;var n=m.getContext("2d");b.render(n),n.globalCompositeOperation="destination-in",n.fillStyle=l.createPattern(k,"no-repeat"),n.fillRect(0,0,d+f,e+g),a.fillStyle=n.createPattern(m,"no-repeat"),a.fillRect(0,0,d+f,e+g),b.attribute("mask").value=j},this.render=function(){}},c.Element.mask.prototype=new c.Element.ElementBase,c.Element.clipPath=function(a){this.base=c.Element.ElementBase,this.base(a),this.apply=function(a){for(var b=0;b<this.children.length;b++){var d=this.children[b];if("undefined"!=typeof d.path){var e=null;d.attribute("transform").hasValue()&&(e=new c.Transform(d.attribute("transform").value),e.apply(a)),d.path(a),a.clip(),e&&e.unapply(a)}}},this.render=function(){}},c.Element.clipPath.prototype=new c.Element.ElementBase,c.Element.filter=function(a){this.base=c.Element.ElementBase,this.base(a),this.apply=function(a,b){var c=b.getBoundingBox(),d=Math.floor(c.x1),e=Math.floor(c.y1),f=Math.floor(c.width()),g=Math.floor(c.height()),h=b.style("filter").value;b.style("filter").value="";for(var i=0,j=0,k=0;k<this.children.length;k++){var l=this.children[k].extraFilterDistance||0;i=Math.max(i,l),j=Math.max(j,l)}var m=document.createElement("canvas");m.width=f+2*i,m.height=g+2*j;var n=m.getContext("2d");n.translate(-d+i,-e+j),b.render(n);for(var k=0;k<this.children.length;k++)this.children[k].apply(n,0,0,f+2*i,g+2*j);a.drawImage(m,0,0,f+2*i,g+2*j,d-i,e-j,f+2*i,g+2*j),b.style("filter",!0).value=h},this.render=function(){}},c.Element.filter.prototype=new c.Element.ElementBase,c.Element.feMorphology=function(a){this.base=c.Element.ElementBase,this.base(a),this.apply=function(){}},c.Element.feMorphology.prototype=new c.Element.ElementBase,c.Element.feColorMatrix=function(a){function b(a,b,c,d,e,f){return a[c*d*4+4*b+f]}function d(a,b,c,d,e,f,g){a[c*d*4+4*b+f]=g}this.base=c.Element.ElementBase,this.base(a),this.apply=function(a,c,e,f,g){for(var h=a.getImageData(0,0,f,g),e=0;g>e;e++)for(var c=0;f>c;c++){var i=b(h.data,c,e,f,g,0),j=b(h.data,c,e,f,g,1),k=b(h.data,c,e,f,g,2),l=(i+j+k)/3;d(h.data,c,e,f,g,0,l),d(h.data,c,e,f,g,1,l),d(h.data,c,e,f,g,2,l)}a.clearRect(0,0,f,g),a.putImageData(h,0,0)}},c.Element.feColorMatrix.prototype=new c.Element.ElementBase,c.Element.feGaussianBlur=function(a){this.base=c.Element.ElementBase,this.base(a),this.blurRadius=Math.floor(this.attribute("stdDeviation").numValue()),this.extraFilterDistance=this.blurRadius,this.apply=function(a,d,e,f,g){return"undefined"==typeof b?void("undefined"!=typeof console&&console.log("ERROR: StackBlur.js must be included for blur to work")):(a.canvas.id=c.UniqueId(),a.canvas.style.display="none",document.body.appendChild(a.canvas),b(a.canvas.id,d,e,f,g,this.blurRadius),void document.body.removeChild(a.canvas))}},c.Element.feGaussianBlur.prototype=new c.Element.ElementBase,c.Element.title=function(){},c.Element.title.prototype=new c.Element.ElementBase,c.Element.desc=function(){},c.Element.desc.prototype=new c.Element.ElementBase,c.Element.MISSING=function(a){"undefined"!=typeof console&&console.log("ERROR: Element '"+a.nodeName+"' not yet implemented.")},c.Element.MISSING.prototype=new c.Element.ElementBase,c.CreateElement=function(a){var b=a.nodeName.replace(/^[^:]+:/,"");b=b.replace(/\-/g,"");var d=null;return d="undefined"!=typeof c.Element[b]?new c.Element[b](a):new c.Element.MISSING(a),d.type=a.nodeName,d},c.load=function(a,b){c.loadXml(a,c.ajax(b))},c.loadXml=function(a,b){c.loadXmlDoc(a,c.parseXml(b))},c.loadXmlDoc=function(a,b){c.init(a);var d=function(b){for(var c=a.canvas;c;)b.x-=c.offsetLeft,b.y-=c.offsetTop,c=c.offsetParent;return window.scrollX&&(b.x+=window.scrollX),window.scrollY&&(b.y+=window.scrollY),b};1!=c.opts.ignoreMouse&&(a.canvas.onclick=function(a){var b=d(new c.Point(null!=a?a.clientX:event.clientX,null!=a?a.clientY:event.clientY));c.Mouse.onclick(b.x,b.y)},a.canvas.onmousemove=function(a){var b=d(new c.Point(null!=a?a.clientX:event.clientX,null!=a?a.clientY:event.clientY));c.Mouse.onmousemove(b.x,b.y)});var e=c.CreateElement(b.documentElement);e.root=!0;var f=!0,g=function(){c.ViewPort.Clear(),a.canvas.parentNode&&c.ViewPort.SetCurrent(a.canvas.parentNode.clientWidth,a.canvas.parentNode.clientHeight),1!=c.opts.ignoreDimensions&&(e.style("width").hasValue()&&(a.canvas.width=e.style("width").toPixels("x"),a.canvas.style.width=a.canvas.width+"px"),e.style("height").hasValue()&&(a.canvas.height=e.style("height").toPixels("y"),a.canvas.style.height=a.canvas.height+"px"));var d=a.canvas.clientWidth||a.canvas.width,g=a.canvas.clientHeight||a.canvas.height;if(1==c.opts.ignoreDimensions&&e.style("width").hasValue()&&e.style("height").hasValue()&&(d=e.style("width").toPixels("x"),g=e.style("height").toPixels("y")),c.ViewPort.SetCurrent(d,g),null!=c.opts.offsetX&&(e.attribute("x",!0).value=c.opts.offsetX),null!=c.opts.offsetY&&(e.attribute("y",!0).value=c.opts.offsetY),null!=c.opts.scaleWidth&&null!=c.opts.scaleHeight){var h=1,i=1,j=c.ToNumberArray(e.attribute("viewBox").value);e.attribute("width").hasValue()?h=e.attribute("width").toPixels("x")/c.opts.scaleWidth:isNaN(j[2])||(h=j[2]/c.opts.scaleWidth),e.attribute("height").hasValue()?i=e.attribute("height").toPixels("y")/c.opts.scaleHeight:isNaN(j[3])||(i=j[3]/c.opts.scaleHeight),e.attribute("width",!0).value=c.opts.scaleWidth,e.attribute("height",!0).value=c.opts.scaleHeight,e.attribute("viewBox",!0).value="0 0 "+d*h+" "+g*i,e.attribute("preserveAspectRatio",!0).value="none"}1!=c.opts.ignoreClear&&a.clearRect(0,0,d,g),e.render(a),f&&(f=!1,"function"==typeof c.opts.renderCallback&&c.opts.renderCallback(b))},h=!0;c.ImagesLoaded()&&(h=!1,g()),c.intervalID=setInterval(function(){var a=!1;if(h&&c.ImagesLoaded()&&(h=!1,a=!0),1!=c.opts.ignoreMouse&&(a|=c.Mouse.hasEvents()),1!=c.opts.ignoreAnimation)for(var b=0;b<c.Animations.length;b++)a|=c.Animations[b].update(1e3/c.FRAMERATE);"function"==typeof c.opts.forceRedraw&&1==c.opts.forceRedraw()&&(a=!0),a&&(g(),c.Mouse.runEvents())
},1e3/c.FRAMERATE)},c.stop=function(){c.intervalID&&clearInterval(c.intervalID)},c.Mouse=new function(){this.events=[],this.hasEvents=function(){return 0!=this.events.length},this.onclick=function(a,b){this.events.push({type:"onclick",x:a,y:b,run:function(a){a.onclick&&a.onclick()}})},this.onmousemove=function(a,b){this.events.push({type:"onmousemove",x:a,y:b,run:function(a){a.onmousemove&&a.onmousemove()}})},this.eventElements=[],this.checkPath=function(a,b){for(var c=0;c<this.events.length;c++){var d=this.events[c];b.isPointInPath&&b.isPointInPath(d.x,d.y)&&(this.eventElements[c]=a)}},this.checkBoundingBox=function(a,b){for(var c=0;c<this.events.length;c++){var d=this.events[c];b.isPointInBox(d.x,d.y)&&(this.eventElements[c]=a)}},this.runEvents=function(){c.ctx.canvas.style.cursor="";for(var a=0;a<this.events.length;a++)for(var b=this.events[a],d=this.eventElements[a];d;)b.run(d),d=d.parent;this.events=[],this.eventElements=[]}},c}this.canvg=function(a,b,d){if(null!=a||null!=b||null!=d){d=d||{},"string"==typeof a&&(a=document.getElementById(a)),null!=a.svg&&a.svg.stop();var e=c();(1!=a.childNodes.length||"OBJECT"!=a.childNodes[0].nodeName)&&(a.svg=e),e.opts=d;var f=a.getContext("2d");"undefined"!=typeof b.documentElement?e.loadXmlDoc(f,b):"<"==b.substr(0,1)?e.loadXml(f,b):e.load(f,b)}else for(var g=document.getElementsByTagName("svg"),h=0;h<g.length;h++){var i=g[h],j=document.createElement("canvas");j.width=i.clientWidth,j.height=i.clientHeight,i.parentNode.insertBefore(j,i),i.parentNode.removeChild(i);var k=document.createElement("div");k.appendChild(i),canvg(j,k.innerHTML)}}}(),"undefined"!=typeof CanvasRenderingContext2D&&(CanvasRenderingContext2D.prototype.drawSvg=function(a,b,c,d,e){canvg(this.canvas,a,{ignoreMouse:!0,ignoreAnimation:!0,ignoreDimensions:!0,ignoreClear:!0,offsetX:b,offsetY:c,scaleWidth:d,scaleHeight:e})}),canvg}},b[1]={value:function(){function a(a,b,e){var f=a.container.getRenderBox();return c(a.node.ownerDocument,{width:f.width,height:f.height,content:d(a.node)},b,e)}function c(a,b,c,d){var e=arguments;f.apply(null,e)}function d(a){var b=a.ownerDocument.createElement("div"),c=['<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="',a.getAttribute("width"),'" height="',a.getAttribute("height"),'">'];return b.appendChild(a.cloneNode(!0)),b.innerHTML.replace(/<svg[^>]+?>/i,c.join("")).replace(/&nbsp;/g,"")}function e(a,b,c,d){var e=a.createElement("canvas"),f=e.getContext("2d");return e.width=b,e.height=c,"image/png"!==d&&(f.fillStyle="white",f.fillRect(0,0,e.width,e.height)),e}function f(a,b,c,d){var f=e(a,b.width,b.height,c);f.style.cssText="position: absolute; top: 0; left: 100000px; z-index: -1;",window.setTimeout(function(){a.body.appendChild(f),h(f,b.content),a.body.removeChild(f),d(f.toDataURL(c))},0)}var g=b.r(34),h=b.r(0);return g.createClass("Output",{constructor:function(a){this.formula=a},toJPG:function(b){a(this.formula,"image/jpeg",b)},toPNG:function(b){a(this.formula,"image/png",b)}})}},b[2]={value:function(){return["0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","&#x237;","&#x131;","&#x3b1;","&#x3b2;","&#x3b3;","&#x3b4;","&#x3b5;","&#x3b6;","&#x3b7;","&#x3b8;","&#x3b9;","&#x3ba;","&#x3bb;","&#x3bc;","&#x3bd;","&#x3be;","&#x3bf;","&#x3c0;","&#x3c1;","&#x3c2;","&#x3c3;","&#x3c4;","&#x3c5;","&#x3c6;","&#x3c7;","&#x3c8;","&#x3c9;","&#x3d1;","&#x3d5;","&#x3d6;","&#x3de;","&#x3dc;","&#x3f5;","&#x3f1;","&#x3f9;","&#x211c;","&#x2135;","&#x2111;","&#x2127;","&#x2136;","&#x2137;","&#x2138;","&#xf0;","&#x210f;","&#x2141;","&#x210e;","&#x2202;","&#x2118;","&#x214c;","&#x2132;","&#x2201;","&#x2113;","&#x24c8;","(",")","&#x393;","&#x394;","&#x395;","&#x396;","&#x397;","&#x398;","&#x399;","&#x39a;","&#x39b;","&#x39c;","&#x39d;","&#x39e;","&#x39f;","&#x3a0;","&#x3a1;","&#x3a3;","&#x3a4;","&#x3a5;","&#x3a6;","&#x3a7;","&#x3a8;","&#x3a9;","&#x391;","&#x392;","#","!","$","%","&#x26;","&#x2220;","&#x2032;","&#x2035;","&#x2605;","&#x25c6;","&#x25a0;","&#x25b2;","&#x25bc;","&#x22a4;","&#x22a5;","&#x2663;","&#x2660;","&#x2662;","&#x2661;","&#x2203;","&#x2204;","&#x266d;","&#x266e;","&#x266f;","&#x2200;","&#x221e;","&#x2221;","&#x2207;","&#xac;","&#x2222;","&#x221a;","&#x25b3;","&#x25bd;","&#x2205;","&#xf8;","&#x25c7;","&#x25c0;","&#x25b8;","[","]","{","}","&#x3008;","&#x3009;","&#x3f0;",",",".","/",":",";","?","\\","&#x22ee;","&#x22ef;","&#x22f0;","&#x2026;","@","&#x22;","'","|","^","`","&#x201c;","_","*","+","-","&#x2210;","&#x22bc;","&#x22bb;","&#x25ef;","&#x22a1;","&#x229f;","&#x229e;","&#x22a0;","&#x2022;","&#x2229;","&#x222a;","&#x22d2;","&#x22d3;","&#x22d0;","&#x22d1;","&#xb7;","&#x25aa;","&#x25e6;","&#x229b;","&#x229a;","&#x2296;","&#x2299;","&#x229d;","&#x2295;","&#x2297;","&#x2298;","&#xb1;","&#x2213;","&#x22cf;","&#x22ce;","&#x2020;","&#x2021;","&#x22c4;","&#xf7;","&#x22c7;","&#x2214;","&#x232d;","&#x22d7;","&#x22d6;","&#x22c9;","&#x22ca;","&#x22cb;","&#x22cc;","&#x2293;","&#x2294;","&#x2291;","&#x2292;","&#x228f;","&#x2290;","&#x22c6;","&#xd7;","&#x22b3;","&#x22b2;","&#x22b5;","&#x22b4;","&#x228e;","&#x2228;","&#x2227;","&#x2240;","&#x3c;","=","&#x3e;","&#x2248;","&#x2247;","&#x224d;","&#x2252;","&#x2253;","&#x224a;","&#x223d;","&#x2241;","&#x2242;","&#x2243;","&#x22cd;","&#x224f;","&#x224e;","&#x2257;","&#x2245;","&#x22de;","&#x22df;","&#x2250;","&#x2251;","&#x2256;","&#x2a96;","&#x2a95;","&#x2261;","&#x2265;","&#x2264;","&#x2266;","&#x2267;","&#x2a7e;","&#x2a7d;","&#x226b;","&#x226a;","&#x2268;","&#x2269;","&#x22d8;","&#x22d9;","&#x2a87;","&#x2a88;","&#x2a89;","&#x2a8a;","&#x22e7;","&#x22e6;","&#x2a86;","&#x2a85;","&#x22db;","&#x22da;","&#x2a8b;","&#x2a8c;","&#x2277;","&#x2276;","&#x2273;","&#x2272;","&#x232e;","&#x232f;","&#x226f;","&#x2271;","&#x2270;","&#x226e;","&#x2331;","&#x2330;","&#x2332;","&#x2333;","&#x226c;","&#x2280;","&#x2281;","&#x22e0;","&#x22e1;","&#x227a;","&#x227b;","&#x227c;","&#x227d;","&#x227e;","&#x227f;","&#x2282;","&#x2283;","&#x2288;","&#x2289;","&#x2286;","&#x2287;","&#x228a;","&#x228b;","&#x2ab7;","&#x2ab8;","&#x2aaf;","&#x2ab0;","&#x2ab9;","&#x2aba;","&#x2ab5;","&#x2ab6;","&#x22e8;","&#x22e9;","&#x223c;","&#x225c;","&#x21b6;","&#x21b7;","&#x21ba;","&#x21bb;","&#x21be;","&#x21bf;","&#x21c2;","&#x21c3;","&#x21c4;","&#x21c6;","&#x21c8;","&#x21ca;","&#x21cb;","&#x21cc;","&#x21cd;","&#x21ce;","&#x21cf;","&#x21d0;","&#x21d1;","&#x21d2;","&#x21d3;","&#x21d4;","&#x21d5;","&#x21da;","&#x21db;","&#x21dd;","&#x21ab;","&#x21ac;","&#x21ad;","&#x21ae;","&#x2190;","&#x2191;","&#x2192;","&#x2193;","&#x2194;","&#x2195;","&#x2196;","&#x2197;","&#x2198;","&#x2199;","&#x219e;","&#x21a0;","&#x21a2;","&#x21a3;","&#x21b0;","&#x21b1;","&#x22a2;","&#x22a3;","&#x22a8;","&#x22a9;","&#x22aa;","&#x22ad;","&#x22af;","&#x22b8;","&#x22ba;","&#x22d4;","&#x22ea;","&#x22eb;","&#x22ec;","&#x22ed;","&#x2308;","&#x2309;","&#x230a;","&#x230b;","&#x2acb;","&#x2acc;","&#x2ac5;","&#x2ac6;","&#x2208;","&#x220b;","&#x221d;","&#x2224;","&#x2226;","&#x2234;","&#x2235;","&#x220d;","&#x22c8;","&#x2322;","&#x2323;","&#x2223;","&#x2225;","&#x23d0;","&#x23d1;","&#x23d2;","&#x23d3;","&#x2ac7;","&#x2ac8;","&#x22ae;","&#x22ac;","&#x2ac9;","&#x23d4;","&#x23d5;","&#x23d6;","&#x23d7;","&#x21c7;","&#x21c9;","&#x21bc;","&#x21bd;","&#x21c0;","&#x21c1;","&#x219a;","&#x219b;","&#x27f5;","&#x27f6;","&#x27f7;","&#x27f9;","&#x27f8;","&#x27fa;","&#x2262;","&#x2260;","&#x2209;"]}},b[3]={value:function(){return{defaultFont:"KF AMS MAIN"}}},b[4]={value:function(){function a(a){var b=new d.Text;return"innerHTML"in b.node?b.node.setAttributeNS(f,"xml:space","preserve"):-1!=a.indexOf(" ")&&(a=c(a)),b.setContent(a),b}function c(a){return e.innerHTML='<svg><text gg="asfdas">'+a.replace(/\s/gi,"&nbsp;")+"</text></svg>",e.firstChild.firstChild.textContent}var d=b.r(34),e=document.createElement("div"),f="http://www.w3.org/XML/1998/namespace";return{create:function(b){return a(b)}}}},b[5]={value:function(){var a=b.r(34),c=b.r(47).font,d=b.r(25),e=b.r(4);return a.createClass("Text",{base:b.r(46),constructor:function(b,c){this.callBase(),this.fontFamily=c,this.fontSize=50,this.content=b||"",this.box.remove(),this.translationContent=this.translation(this.content),this.contentShape=new a.Group,this.contentNode=this.createContent(),this.contentShape.addShape(this.contentNode),this.addShape(this.contentShape)},createContent:function(){var a=e.create(this.translationContent);return a.setAttr({"font-family":this.fontFamily,"font-size":50,x:0,y:c.offset}),a},setFamily:function(a){this.fontFamily=a,this.contentNode.setAttr("font-family",a)},setFontSize:function(a){this.fontSize=a,this.contentNode.setAttr("font-size",a+"px"),this.contentNode.setAttr("y",a/50*c.offset)},getBaseHeight:function(){for(var a=this.contentShape.getItems(),b=null,c=0,d=0;b=a[c];)d=Math.max(d,b.getHeight()),c++;return d},translation:function(a){var b=this.fontFamily;return a.replace(/``/g,"\u201c").replace(/\\([a-zA-Z,]+)\\/g,function(a,c){if(","===c)return" ";var e=d.getCharacterValue(c,b);return e?e:""})}})}},b[6]={value:function(){return{UNKNOWN:-1,EXP:0,COMPOUND_EXP:1,OP:2}}},b[7]={value:function(){return{SIDE:"side",FOLLOW:"follow"}}},b[8]={value:function(){var a=b.r(34);return a.createClass("SubscriptExpression",{base:b.r(17),constructor:function(a,b){this.callBase(a,null,b),this.setFlag("Subscript")}})}},b[9]={value:function(){var a=b.r(34);return a.createClass("SuperscriptExpression",{base:b.r(17),constructor:function(a,b){this.callBase(a,b,null),this.setFlag("Superscript")}})}},b[10]={value:function(){var a=b.r(34);return a.createClass("BinaryExpression",{base:b.r(19),constructor:function(a,b){this.callBase(),this.setFirstOperand(a),this.setLastOperand(b)},setFirstOperand:function(a){return this.setOperand(a,0)},getFirstOperand:function(){return this.getOperand(0)},setLastOperand:function(a){return this.setOperand(a,1)},getLastOperand:function(){return this.getOperand(1)}})}},b[11]={value:function(){var a=b.r(34),c=b.r(35);return a.createClass("BracketsExpression",{base:b.r(19),constructor:function(a,b,d){this.callBase(),this.setFlag("Brackets"),2===arguments.length&&(d=b,b=a),this.leftSymbol=a,this.rightSymbol=b,this.setOperator(new c),this.setOperand(d,0)},getLeftSymbol:function(){return this.leftSymbol},getRightSymbol:function(){return this.rightSymbol}})}},b[12]={value:function(){var a=b.r(34),c=b.r(47).font,d=b.r(36);return a.createClass("CombinationExpression",{base:b.r(19),constructor:function(){this.callBase(),this.setFlag("Combination"),this.setOperator(new d),a.Utils.each(arguments,function(a,b){this.setOperand(a,b)},this)},getRenderBox:function(a){var b=this.callBase(a);return 0===this.getOperands().length&&(b.height=c.spaceHeight),b},getBaseline:function(b){var c=0,d=this.getOperands();return 0===d.length?this.callBase(b):(a.Utils.each(d,function(a){c=Math.max(a.getBaseline(b),c)}),c)},getMeanline:function(b){var c=1e7,d=this.getOperands();return 0===d.length?this.callBase(b):(a.Utils.each(d,function(a){c=Math.min(a.getMeanline(b),c)}),c)}})}},b[13]={value:function(){var a=b.r(34),c=b.r(38);return a.createClass("FractionExpression",{base:b.r(10),constructor:function(a,b){this.callBase(a,b),this.setFlag("Fraction"),this.setOperator(new c)},getBaseline:function(a){var b=this.getOperand(1),c=b.getRenderBox(a);return c.y+b.getBaselineProportion()*c.height},getMeanline:function(a){var b=this.getOperand(0),c=b.getRenderBox(a);return b.getMeanlineProportion()*c.height}})}},b[14]={value:function(){var a=b.r(34),c=b.r(47).func,d=b.r(39);return a.createClass("FunctionExpression",{base:b.r(19),constructor:function(a,b,c,e){this.callBase(),this.setFlag("Func"),this.funcName=a,this.setOperator(new d(a)),this.setExpr(b),this.setSuperscript(c),this.setSubscript(e)},isSideScript:function(){return!c["ud-script"][this.funcName]},setExpr:function(a){return this.setOperand(a,0)},setSuperscript:function(a){return this.setOperand(a,1)},setSubscript:function(a){return this.setOperand(a,2)}})}},b[15]={value:function(){var a=b.r(34),c=b.r(40),d=a.createClass("IntegrationExpression",{base:b.r(19),constructor:function(a,b,d){this.callBase(),this.setFlag("Integration"),this.setOperator(new c),this.setIntegrand(a),this.setSuperscript(b),this.setSubscript(d)},setType:function(a){return this.getOperator().setType(a),this},resetType:function(){return this.getOperator().resetType(),this},setIntegrand:function(a){this.setOperand(a,0)},setSuperscript:function(a){this.setOperand(a,1)},setSubscript:function(a){this.setOperand(a,2)}});return d}},b[16]={value:function(){var a=b.r(34),c=b.r(42);return a.createClass("RadicalExpression",{base:b.r(10),constructor:function(a,b){this.callBase(a,b),this.setFlag("Radicand"),this.setOperator(new c)},setRadicand:function(a){return this.setFirstOperand(a)},getRadicand:function(){return this.getFirstOperand()},setExponent:function(a){return this.setLastOperand(a)},getExponent:function(){return this.getLastOperand()}})}},b[17]={value:function(){var a=b.r(34),c=b.r(43);return a.createClass("ScriptExpression",{base:b.r(19),constructor:function(a,b,d){this.callBase(),this.setFlag("Script"),this.setOperator(new c),this.setOpd(a),this.setSuperscript(b),this.setSubscript(d)},setOpd:function(a){this.setOperand(a,0)},setSuperscript:function(a){this.setOperand(a,1)},setSubscript:function(a){this.setOperand(a,2)}})}},b[18]={value:function(){var a=b.r(34),c=b.r(44);return a.createClass("SummationExpression",{base:b.r(19),constructor:function(a,b,d){this.callBase(),this.setFlag("Summation"),this.setOperator(new c),this.setExpr(a),this.setSuperscript(b),this.setSubscript(d)},setExpr:function(a){this.setOperand(a,0)},setSuperscript:function(a){this.setOperand(a,1)},setSubscript:function(a){this.setOperand(a,2)}})}},b[19]={value:function(){var a=b.r(34),c=b.r(6),d=b.r(21);return a.createClass("CompoundExpression",{base:b.r(21),constructor:function(){this.callBase(),this.type=c.COMPOUND_EXP,this.operands=[],this.operator=null,this.operatorBox=new a.Group,this.operatorBox.setAttr("data-type","kf-editor-exp-op-box"),this.operandBox=new a.Group,this.operandBox.setAttr("data-type","kf-editor-exp-operand-box"),this.setChildren(0,this.operatorBox),this.setChildren(1,this.operandBox)},setOperator:function(a){return void 0===a?this:(this.operator&&this.operator.remove(),this.operatorBox.addShape(a),this.operator=a,this.operator.setParentExpression(this),a.expression=this,this)},getOperator:function(){return this.operator},setOperand:function(a,b,c){return c===!1?(this.operands[b]=a,this):(a=d.wrap(a),this.operands[b]&&this.operands[b].remove(),this.operands[b]=a,this.operandBox.addShape(a),this)},getOperand:function(a){return this.operands[a]},getOperands:function(){return this.operands},addedCall:function(){return this.operator.applyOperand.apply(this.operator,this.operands),this}})}},b[20]={value:function(){var a=b.r(34),c=b.r(47).font,d=b.r(21),e=a.createClass("EmptyExpression",{base:d,constructor:function(){this.callBase(),this.setFlag("Empty")},getRenderBox:function(){return{width:0,height:c.spaceHeight,x:0,y:0}}});return e.isEmpty=function(a){return a instanceof e},d.registerWrap("empty",function(a){return null===a||void 0===a?new e:void 0}),e}},b[21]={value:function(){var a=b.r(34),c=b.r(6),d=b.r(47).font,e=[],f={},g=a.createClass("Expression",{base:b.r(46),constructor:function(){this.callBase(),this.type=c.EXP,this._offset={top:0,bottom:0},this.children=[],this.box.fill("transparent").setAttr("data-type","kf-editor-exp-box"),this.box.setAttr("data-type","kf-editor-exp-bg-box"),this.expContent=new a.Group,this.expContent.setAttr("data-type","kf-editor-exp-content-box"),this.addShape(this.expContent)},getChildren:function(){return this.children},getChild:function(a){return this.children[a]||null},getTopOffset:function(){return this._offset.top},getBottomOffset:function(){return this._offset.bottom},getOffset:function(){return this._offset},setTopOffset:function(a){this._offset.top=a},setBottomOffset:function(a){this._offset.bottom=a},setOffset:function(a,b){this._offset.top=a,this._offset.bottom=b},setFlag:function(a){this.setAttr("data-flag",a||"Expression")},setChildren:function(a,b){this.children[a]&&this.children[a].remove(),this.children[a]=b,this.expContent.addShape(b)},getBaselineProportion:function(){return d.baselinePosition},getMeanlineProportion:function(){return d.meanlinePosition},getBaseline:function(a){return this.getRenderBox(a).height*d.baselinePosition-3},getMeanline:function(a){return this.getRenderBox(a).height*d.meanlinePosition-1},getAscenderline:function(){return this.getFixRenderBox().height*d.ascenderPosition},getDescenderline:function(){return this.getFixRenderBox().height*d.descenderPosition},translateElement:function(a,b){this.expContent.translate(a,b)},expand:function(a,b){var c=this.getFixRenderBox();this.setBoxSize(c.width+a,c.height+b)},getBaseWidth:function(){return this.getWidth()},getBaseHeight:function(){return this.getHeight()},updateBoxSize:function(){var a=this.expContent.getFixRenderBox();this.setBoxSize(a.width,a.height)},getBox:function(){return this.box}});return a.Utils.extend(g,{registerWrap:function(a,b){f[a]=e.length,e.push(b)},revokeWrap:function(a){var b=null;return a in f&&(b=e[f[a]],e[f[a]]=null,delete f[a]),b},wrap:function(b){var c;return a.Utils.each(e,function(a){return a?(c=a(b),c?!1:void 0):void 0}),c}}),g}},b[22]={value:function(){var a=b.r(5),c=b.r(34),d=b.r(3),e=b.r(21),f=c.createClass("TextExpression",{base:b.r(21),constructor:function(b,e){this.callBase(),this.fontFamily=e||d.defaultFont,this.setFlag("Text"),this.content=b+"",this.textContent=new a(this.content,this.fontFamily),this.setChildren(0,this.textContent),this.setChildren(1,new c.Rect(0,0,0,0).fill("transparent"))},setFamily:function(a){this.textContent.setFamily(a)},setFontSize:function(a){this.textContent.setFontSize(a)},addedCall:function(){var a=this.textContent.getFixRenderBox();return this.getChild(1).setSize(a.width,a.height),this.updateBoxSize(),this}});return e.registerWrap("text",function(a){var b=typeof a;return("number"===b||"string"===b)&&(a=new f(a)),a}),f}},b[23]={value:function(){return['<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">','<text id="abcd" font-family="KF AMS MAIN" font-size="50" x="0" y="0">x</text>',"</svg>"]}},b[24]={value:function(){function a(a,b,c){i.get(b.meta.src,function(e,f){"success"===f&&d(a,b),c()})}function c(a,b){window.setTimeout(function(){e(a),f(),b()},100)}function d(a,b){var c=document.createElement("div"),d=b.meta.fontFamily;c.style.cssText="position: absolute; top: -10000px; left: -100000px;",c.style.fontFamily=d,c.innerHTML=k.join(""),a.body.appendChild(c),l.push(c)}function e(a){var c=a.createElement("div");c.style.cssText="position: absolute; top: 0; left: -100000px;",c.innerHTML=b.r(23).join(""),a.body.appendChild(c);var d=c.getElementsByTagName("text")[0].getBBox();j.spaceHeight=d.height,j.topSpace=-d.y-j.baseline,j.bottomSpace=j.spaceHeight-j.topSpace-j.baseHeight,j.offset=j.baseline+j.topSpace,j.baselinePosition=(j.topSpace+j.baseline)/j.spaceHeight,j.meanlinePosition=(j.topSpace+j.meanline)/j.spaceHeight,j.ascenderPosition=j.topSpace/j.spaceHeight,j.descenderPosition=(j.topSpace+j.baseHeight)/j.spaceHeight,a.body.removeChild(c)}function f(){g.Utils.each(l,function(a){a.parentNode.removeChild(a)}),l=[]}var g=b.r(34),h=b.r(25),i=b.r(33),j=b.r(47).font,k=b.r(2),l=[];return g.createClass("FontInstaller",{constructor:function(a,b){this.callBase(),this.resource=b||"../src/resource/",this.doc=a},mount:function(b){var d=h.getFontList(),e=0,f=this;g.Utils.each(d,function(d){e++,d.meta.src=f.resource+d.meta.src,f.createFontStyle(d),a(f.doc,d,function(){e--,0===e&&c(f.doc,b)})})},createFontStyle:function(a){var b=this.doc.createElement("style"),c='@font-face{\nfont-family: "${fontFamily}";\nsrc: url("${src}");\n}';b.setAttribute("type","text/css"),b.innerHTML=c.replace("${fontFamily}",a.meta.fontFamily).replace("${src}",a.meta.src),this.doc.head.appendChild(b)}})}},b[25]={value:function(){var a={},c=b.r(34),d=b.r(47).font.list;return function(){c.Utils.each(d,function(b){a[b.meta.fontFamily]=b})}(),{getFontList:function(){return a},getCharacterValue:function(b,c){return a[c]?a[c].map[b]||null:null}}}},b[26]={value:function(){return{meta:{fontFamily:"KF AMS BB",src:"KF_AMS_BB.woff"}}}},b[27]={value:function(){return{meta:{fontFamily:"KF AMS CAL",src:"KF_AMS_CAL.woff"}}}},b[28]={value:function(){return{meta:{fontFamily:"KF AMS FRAK",src:"KF_AMS_FRAK.woff"}}}},b[29]={value:function(){return{meta:{fontFamily:"KF AMS MAIN",src:"KF_AMS_MAIN.woff"},map:{Alpha:"\u0391",Beta:"\u0392",Gamma:"\u0393",Delta:"\u0394",Epsilon:"\u0395",Zeta:"\u0396",Eta:"\u0397",Theta:"\u0398",Iota:"\u0399",Kappa:"\u039a",Lambda:"\u039b",Mu:"\u039c",Nu:"\u039d",Xi:"\u039e",Omicron:"\u039f",Pi:"\u03a0",Rho:"\u03a1",Sigma:"\u03a3",Tau:"\u03a4",Upsilon:"\u03a5",Phi:"\u03a6",Chi:"\u03a7",Psi:"\u03a8",Omega:"\u03a9",alpha:"\u03b1",beta:"\u03b2",gamma:"\u03b3",delta:"\u03b4",epsilon:"\u03b5",zeta:"\u03b6",eta:"\u03b7",theta:"\u03b8",iota:"\u03b9",kappa:"\u03ba",lambda:"\u03bb",mu:"\u03bc",nu:"\u03bd",xi:"\u03be",omicron:"\u03bf",pi:"\u03c0",rho:"\u03c1",sigma:"\u03c3",tau:"\u03c4",upsilon:"\u03c5",phi:"\u03c6",varkappa:"\u03f0",chi:"\u03c7",psi:"\u03c8",omega:"\u03c9",digamma:"\u03dc",varepsilon:"\u03f5",varrho:"\u03f1",varphi:"\u03d5",vartheta:"\u03d1",varpi:"\u03d6",varsigma:"\u03f9",aleph:"\u2135",/*beth:"\u2136",*/daleth:"\u2138",gimel:"\u2137",eth:"\xf0",hbar:"\u210e",hslash:"\u210f",mho:"\u2127",partial:"\u2202",wp:"\u2118",Game:"\u2141",Bbbk:"\u214c",Finv:"\u2132",Im:"\u2111",Re:"\u211c",complement:"\u2201",ell:"\u2113",circledS:"\u24c8",imath:"\u0131",jmath:"\u0237",doublecap:"\u22d2",Cap:"\u22d2",doublecup:"\u22d3",Cup:"\u22d3",ast:"*",divideontimes:"\u22c7",rightthreetimes:"\u22cc",leftthreetimes:"\u22cb",cdot:"\xb7",odot:"\u2299",dotplus:"\u2214",rtimes:"\u22ca",ltimes:"\u22c9",centerdot:"\u25aa",doublebarwedge:"\u232d",setminus:"\u2481",amalg:"\u2210",circ:"\u25e6",bigcirc:"\u25ef",gtrdot:"\u22d7",lessdot:"\u22d6",smallsetminus:"\u2485",circledast:"\u229b",circledcirc:"\u229a",sqcap:"\u2293",sqcup:"\u2294",barwedge:"\u22bc",circleddash:"\u229d",star:"\u22c6",bigtriangledown:"\u25bd",bigtriangleup:"\u25b3",cup:"\u222a",cap:"\u2229",times:"\xd7",mp:"\u2213",pm:"\xb1",triangleleft:"\u22b2",triangleright:"\u22b3",boxdot:"\u22a1",curlyvee:"\u22cf",curlywedge:"\u22ce",boxminus:"\u229f",boxtimes:"\u22a0",ominus:"\u2296",oplus:"\u2295",oslash:"\u2298",otimes:"\u2297",uplus:"\u228e",boxplus:"\u229e",dagger:"\u2020",ddagger:"\u2021",vee:"\u2228",lor:"\u2228",veebar:"\u22bb",bullet:"\u2022",diamond:"\u22c4",wedge:"\u2227",land:"\u2227",div:"\xf7",wr:"\u2240",geqq:"\u2267",lll:"\u22d8",llless:"\u22d8",ggg:"\u22d9",gggtr:"\u22d9",preccurlyeq:"\u227c",geqslant:"\u2a7e",lnapprox:"\u2a89",preceq:"\u2aaf",gg:"\u226b",lneq:"\u2a87",precnapprox:"\u2ab9",approx:"\u2248",lneqq:"\u2268",precneqq:"\u2ab5",approxeq:"\u224a",gnapprox:"\u2a8a",lnsim:"\u22e6",precnsim:"\u22e8",asymp:"\u224d",gneq:"\u2a88",lvertneqq:"\u232e",precsim:"\u227e",backsim:"\u223d",gneqq:"\u2269",ncong:"\u2247",risingdotseq:"\u2253",backsimeq:"\u22cd",gnsim:"\u22e7",sim:"\u223c",simeq:"\u2243",bumpeq:"\u224f",gtrapprox:"\u2a86",ngeq:"\u2271",Bumpeq:"\u224e",gtreqless:"\u22db",ngeqq:"\u2331",succ:"\u227b",circeq:"\u2257",gtreqqless:"\u2a8c",ngeqslant:"\u2333",succapprox:"\u2ab8",cong:"\u2245",gtrless:"\u2277",ngtr:"\u226f",succcurlyeq:"\u227d",curlyeqprec:"\u22de",gtrsim:"\u2273",nleq:"\u2270",succeq:"\u2ab0",curlyeqsucc:"\u22df",gvertneqq:"\u232f",neq:"\u2260",ne:"\u2260",nequiv:"\u2262",nleqq:"\u2330",succnapprox:"\u2aba",doteq:"\u2250",leq:"\u2264",le:"\u2264",nleqslant:"\u2332",succneqq:"\u2ab6",doteqdot:"\u2251",Doteq:"\u2251",leqq:"\u2266",nless:"\u226e",succnsim:"\u22e9",leqslant:"\u2a7d",nprec:"\u2280",succsim:"\u227f",eqsim:"\u2242",lessapprox:"\u2a85",npreceq:"\u22e0",eqslantgtr:"\u2a96",lesseqgtr:"\u22da",nsim:"\u2241",eqslantless:"\u2a95",lesseqqgtr:"\u2a8b",nsucc:"\u2281",triangleq:"\u225c",eqcirc:"\u2256",equiv:"\u2261",lessgtr:"\u2276",nsucceq:"\u22e1",fallingdotseq:"\u2252",lesssim:"\u2272",prec:"\u227a",geq:"\u2265",ge:"\u2265",ll:"\u226a",precapprox:"\u2ab7",uparrow:"\u2191",downarrow:"\u2193",updownarrow:"\u2195",Uparrow:"\u21d1",Downarrow:"\u21d3",Updownarrow:"\u21d5",circlearrowleft:"\u21ba",circlearrowright:"\u21bb",curvearrowleft:"\u21b6",curvearrowright:"\u21b7",downdownarrows:"\u21ca",downharpoonleft:"\u21c3",downharpoonright:"\u21c2",leftarrow:"\u2190",gets:"\u2190",Leftarrow:"\u21d0",leftarrowtail:"\u21a2",leftharpoondown:"\u21bd",leftharpoonup:"\u21bc",leftleftarrows:"\u21c7",leftrightarrow:"\u2194",Leftrightarrow:"\u21d4",leftrightarrows:"\u21c4",leftrightharpoons:"\u21cb",leftrightsquigarrow:"\u21ad",Lleftarrow:"\u21da",looparrowleft:"\u21ab",looparrowright:"\u21ac",multimap:"\u22b8",nLeftarrow:"\u21cd",nRightarrow:"\u21cf",nLeftrightarrow:"\u21ce",nearrow:"\u2197",nleftarrow:"\u219a",nleftrightarrow:"\u21ae",nrightarrow:"\u219b",nwarrow:"\u2196",rightarrow:"\u2192",to:"\u2192",Rightarrow:"\u21d2",rightarrowtail:"\u21a3",rightharpoondown:"\u21c1",rightharpoonup:"\u21c0",rightleftarrows:"\u21c6",rightleftharpoons:"\u21cc",rightrightarrows:"\u21c9",rightsquigarrow:"\u21dd",Rrightarrow:"\u21db",searrow:"\u2198",swarrow:"\u2199",twoheadleftarrow:"\u219e",twoheadrightarrow:"\u21a0",upharpoonleft:"\u21bf",upharpoonright:"\u21be",restriction:"\u21be",upuparrows:"\u21c8",Lsh:"\u21b0",Rsh:"\u21b1",longleftarrow:"\u27f5",longrightarrow:"\u27f6",Longleftarrow:"\u27f8",Longrightarrow:"\u27f9",implies:"\u27f9",longleftrightarrow:"\u27f7",Longleftrightarrow:"\u27fa",backepsilon:"\u220d",because:"\u2235",therefore:"\u2234",between:"\u226c",blacktriangleleft:"\u25c0",blacktriangleright:"\u25b8",dashv:"\u22a3",bowtie:"\u22c8",frown:"\u2322","in":"\u2208",notin:"\u2209",mid:"\u2223",parallel:"\u2225",models:"\u22a8",ni:"\u220b",owns:"\u220b",nmid:"\u2224",nparallel:"\u2226",nshortmid:"\u23d2",nshortparallel:"\u23d3",nsubseteq:"\u2288",nsubseteqq:"\u2ac7",nsupseteq:"\u2289",nsupseteqq:"\u2ac8",ntriangleleft:"\u22ea",ntrianglelefteq:"\u22ec",ntriangleright:"\u22eb",ntrianglerighteq:"\u22ed",nvdash:"\u22ac",nVdash:"\u22ae",nvDash:"\u22ad",nVDash:"\u22af",perp:"\u22a5",pitchfork:"\u22d4",propto:"\u221d",shortmid:"\u23d0",shortparallel:"\u23d1",smile:"\u2323",sqsubset:"\u228f",sqsubseteq:"\u2291",sqsupset:"\u2290",sqsupseteq:"\u2292",subset:"\u2282",Subset:"\u22d0",subseteq:"\u2286",subseteqq:"\u2ac5",subsetneq:"\u228a",subsetneqq:"\u2acb",supset:"\u2283",Supset:"\u22d1",supseteq:"\u2287",supseteqq:"\u2ac6",supsetneq:"\u228b",supsetneqq:"\u2acc",trianglelefteq:"\u22b4",trianglerighteq:"\u22b5",varpropto:"\u2ac9",varsubsetneq:"\u23d4",varsubsetneqq:"\u23d6",varsupsetneq:"\u23d5",varsupsetneqq:"\u23d7",vdash:"\u22a2",Vdash:"\u22a9",vDash:"\u22a8",Vvdash:"\u22aa",vert:"|",Vert:"\u01c1","|":"\u01c1","{":"{","}":"}",backslash:"\\",langle:"\u3008",rangle:"\u3009",lceil:"\u2308",rceil:"\u2309",lbrace:"{",rbrace:"}",lfloor:"\u230a",rfloor:"\u230b",cdots:"\u22ef",ddots:"\u22f0",vdots:"\u22ee",dots:"\u2026",ldots:"\u2026","#":"#",bot:"\u22a5",angle:"\u2220",backprime:"\u2035",bigstar:"\u2605",blacklozenge:"\u25c6",blacksquare:"\u25a0",blacktriangle:"\u25b2",blacktriangledown:"\u25bc",clubsuit:"\u2663",diagdown:"\u2481",diagup:"\u2482",diamondsuit:"\u2662",emptyset:"\xf8",exists:"\u2203",flat:"\u266d",forall:"\u2200",heartsuit:"\u2661",infty:"\u221e",lozenge:"\u25c7",measuredangle:"\u2221",nabla:"\u2207",natural:"\u266e",neg:"\xac",lnot:"\xac",/*nexists:"\u2204",*/prime:"\u2032",sharp:"\u266f",spadesuit:"\u2660",sphericalangle:"\u2222",surd:"\u221a",top:"\u22a4",varnothing:"\u2205",triangle:"\u25b3",triangledown:"\u25bd"}}}},b[30]={value:function(){return{meta:{fontFamily:"KF AMS ROMAN",src:"KF_AMS_ROMAN.woff"}}}},b[31]={value:function(){function a(){var a=0;return d.Utils.each(this.expressions,function(b){var c=null;b&&(b.setMatrix(new d.Matrix(1,0,0,1,0,0)),c=b.getFixRenderBox(),b.translate(0-c.x,a),a+=c.height+j)}),this}function c(a){var b=0;if(a){if(a.getType()===e.EXP)for(var d=0,b=a.getChildren().length;b>d;d++)c(a.getChild(d));else if(a.getType()===e.COMPOUND_EXP){for(var d=0,b=a.getOperands().length;b>d;d++)c(a.getOperand(d));c(a.getOperator())}a.addedCall&&a.addedCall()}}var d=b.r(34),e=b.r(6),f=b.r(25),g=b.r(24),h={fontsize:50,autoresize:!0,padding:[0]},i=b.r(1),j=10,k=d.createClass("ExpressionWrap",{constructor:function(a,b){this.wrap=new d.Group,this.bg=new d.Rect(0,0,0,0).fill("transparent"),this.exp=a,this.config=b,this.wrap.setAttr("data-type","kf-exp-wrap"),this.bg.setAttr("data-type","kf-exp-wrap-bg"),this.wrap.addShape(this.bg),this.wrap.addShape(this.exp)},getWrapShape:function(){return this.wrap},getExpression:function(){return this.exp},getBackground:function(){return this.bg},resize:function(){var a=this.config.padding,b=this.exp.getFixRenderBox();1===a.length&&(a[1]=a[0]),this.bg.setSize(2*a[1]+b.width,2*a[0]+b.height),this.exp.translate(a[1],a[0])}}),l=d.createClass("Formula",{base:b.r(32),constructor:function(a,b){this.callBase(a),this.expressions=[],this.fontInstaller=new g(this),this.config=d.Utils.extend({},h,b),this.initEnvironment(),this.initInnerFont()},getContentContainer:function(){return this.container},initEnvironment:function(){this.zoom=this.config.fontsize/50,"width"in this.config&&this.setWidth(this.config.width),"height"in this.config&&this.setHeight(this.config.height),this.node.setAttribute("font-size",h.fontsize)},initInnerFont:function(){function a(a){var b=c.doc.createElement("style"),d='@font-face{font-family: "${fontFamily}";font-style: normal;src: url("${src}") format("woff");}';b.setAttribute("type","text/css"),b.innerHTML=d.replace("${fontFamily}",a.meta.fontFamily).replace("${src}",a.meta.src),c.resourceNode.appendChild(b)}var b=f.getFontList(),c=this;d.Utils.each(b,function(b){a(b)})},insertExpression:function(b,d){var e=this.wrap(b);this.container.clearTransform(),this.expressions.splice(d,0,e.getWrapShape()),this.addShape(e.getWrapShape()),c.call(this,e.getExpression()),e.resize(),a.call(this),this.resetZoom(),this.config.autoresize&&this.resize()},appendExpression:function(a){this.insertExpression(a,this.expressions.length)},resize:function(){var a=this.container.getRenderBox("paper");this.node.setAttribute("width",a.width),this.node.setAttribute("height",a.height)},resetZoom:function(){var a=this.zoom/this.getBaseZoom();0!==a&&this.container.scale(a)},wrap:function(a){return new k(a,this.config)},clear:function(){this.callBase(),this.expressions=[]},clearExpressions:function(){d.Utils.each(this.expressions,function(a){a.remove()}),this.expressions=[]},toJPG:function(a){new i(this).toJPG(a)},toPNG:function(a){new i(this).toPNG(a)}});return d.Utils.extend(l,{registerFont:function(a){f.registerFont(a)}}),l}},b[32]={value:function(){var a=b.r(34);return a.createClass("FPaper",{base:a.Paper,constructor:function(b){this.callBase(b),this.doc=b.ownerDocument,this.container=new a.Group,this.container.setAttr("data-type","kf-container"),this.background=new a.Group,this.background.setAttr("data-type","kf-bg"),this.baseZoom=1,this.zoom=1,this.base("addShape",this.background),this.base("addShape",this.container)},getZoom:function(){return this.zoom},getBaseZoom:function(){return this.baseZoom},addShape:function(a,b){return this.container.addShape(a,b)},getBackground:function(){return this.background},removeShape:function(a){return this.container.removeShape(a)},clear:function(){return this.container.clear()}})}},b[33]={value:function(){if(!window.jQuery)throw new Error("Missing jQuery");return window.jQuery}},b[34]={value:function(){if(!window.kity)throw new Error("Missing Kity Graphic Lib");return window.kity}},b[35]={value:function(){function a(a){var b=this.getParentExpression().getLeftSymbol(),e=this.getParentExpression().getRightSymbol(),f=a.getFixRenderBox().height,g=new c.Group,h=0,i=new d(b,"KF AMS MAIN").fill("black"),j=new d(e,"KF AMS MAIN").fill("black");
    i.setFontSize(f),j.setFontSize(f),this.addOperatorShape(g.addShape(i).addShape(j)),h+=i.getFixRenderBox().width,a.translate(h,0),h+=a.getFixRenderBox().width,j.translate(h,0)}var c=b.r(34),d=b.r(5);return c.createClass("BracketsOperator",{base:b.r(41),constructor:function(){this.callBase("Brackets")},applyOperand:function(b){a.call(this,b)}})}},b[36]={value:function(){var a=b.r(34);return a.createClass("CombinationOperator",{base:b.r(41),constructor:function(){this.callBase("Combination")},applyOperand:function(){var b=0,c=arguments,d=0,e=0,f=0,g=[],h=[];a.Utils.each(c,function(a){var b=a.getFixRenderBox(),c=a.getOffset();b.height-=c.top+c.bottom,g.push(b),h.push(c),e=Math.max(c.top,e),f=Math.max(c.bottom,f),d=Math.max(b.height,d)}),a.Utils.each(c,function(a,c){var e=g[c];a.translate(b-e.x,(d-(e.y+e.height))/2+f-h[c].bottom),b+=e.width}),this.parentExpression.setOffset(e,f),this.parentExpression.updateBoxSize()}})}},b[37]={value:function(){var a=b.r(34),c=b.r(20),d={subOffset:0,supOffset:0,zoom:.66};return a.createClass("ScriptController",{constructor:function(b,c,e,f,g){this.observer=b.getParentExpression(),this.target=c,this.sup=e,this.sub=f,this.options=a.Utils.extend({},d,g)},applyUpDown:function(){var a=this.target,b=this.sup,d=this.sub,e=this.options;b.scale(e.zoom),d.scale(e.zoom);var f=a.getFixRenderBox();return c.isEmpty(b)&&c.isEmpty(d)?{width:f.width,height:f.height,top:0,bottom:0}:!c.isEmpty(b)&&c.isEmpty(d)?this.applyUp(a,b):c.isEmpty(b)&&!c.isEmpty(d)?this.applyDown(a,d):this.applyUpDownScript(a,b,d)},applySide:function(){var a=this.target,b=this.sup,d=this.sub;if(c.isEmpty(b)&&c.isEmpty(d)){var e=a.getRenderBox(this.observer);return{width:e.width,height:e.height,top:0,bottom:0}}return c.isEmpty(b)&&!c.isEmpty(d)?this.applySideSub(a,d):!c.isEmpty(b)&&c.isEmpty(d)?this.applySideSuper(a,b):this.applySideScript(a,b,d)},applySideSuper:function(a,b){b.scale(this.options.zoom);var c=a.getRenderBox(this.observer),d=b.getRenderBox(this.observer),e=a.getMeanline(this.observer),f=b.getBaseline(this.observer),g=e,h=f-g,i={top:0,bottom:0,width:c.width+d.width,height:c.height};return b.translate(c.width,0),this.options.supOffset&&b.translate(this.options.supOffset,0),h>0?(a.translate(0,h),i.bottom=h,i.height+=h):b.translate(0,-h),i},applySideSub:function(a,b){b.scale(this.options.zoom);var c=a.getRenderBox(this.observer),d=b.getRenderBox(this.observer),e=b.getOffset(),f=a.getBaseline(this.observer),g=(d.height+e.top+e.bottom)/2,h=c.height-f-g,i={top:0,bottom:0,width:c.width+d.width,height:c.height};return b.translate(c.width,e.top+f-g),this.options.subOffset&&b.translate(this.options.subOffset,0),0>h&&(i.top=-h,i.height-=h),i},applySideScript:function(a,b,c){b.scale(this.options.zoom),c.scale(this.options.zoom);var d=a.getRenderBox(this.observer),e=c.getRenderBox(this.observer),f=b.getRenderBox(this.observer),g=a.getMeanline(this.observer),h=a.getBaseline(this.observer),i=b.getBaseline(this.observer),j=c.getAscenderline(this.observer),k=g,l=g+2*(h-g)/3,m=k-i,n=d.height-l-(e.height-j),o={top:0,bottom:0,width:d.width+Math.max(e.width,f.width),height:d.height};return b.translate(d.width,m),c.translate(d.width,l-j),this.options.supOffset&&b.translate(this.options.supOffset,0),this.options.subOffset&&c.translate(this.options.subOffset,0),m>0?0>n&&(d.height-=n,o.top=-n):(a.translate(0,-m),b.translate(0,-m),c.translate(0,-m),o.height-=m,n>0?o.bottom=-m:(o.height-=n,m=-m,n=-n,m>n?o.bottom=m-n:o.top=n-m)),o},applyUp:function(a,b){var c=b.getFixRenderBox(),d=a.getFixRenderBox(),e={width:Math.max(d.width,c.width),height:c.height+d.height,top:0,bottom:c.height};return b.translate((e.width-c.width)/2,0),a.translate((e.width-d.width)/2,c.height),e},applyDown:function(a,b){var c=b.getFixRenderBox(),d=a.getFixRenderBox(),e={width:Math.max(d.width,c.width),height:c.height+d.height,top:c.height,bottom:0};return b.translate((e.width-c.width)/2,d.height),a.translate((e.width-d.width)/2,0),e},applyUpDownScript:function(a,b,c){var d=b.getFixRenderBox(),e=c.getFixRenderBox(),f=a.getFixRenderBox(),g={width:Math.max(f.width,d.width,e.width),height:d.height+e.height+f.height,top:0,bottom:0};return b.translate((g.width-d.width)/2,0),a.translate((g.width-f.width)/2,d.height),c.translate((g.width-e.width)/2,d.height+f.height),g}})}},b[38]={value:function(){function a(a,b){return new c.Rect(a+2*b,1).fill("black")}var c=b.r(34),d=b.r(47).zoom;return c.createClass("FractionOperator",{base:b.r(41),constructor:function(){this.callBase("Fraction")},applyOperand:function(b,c){b.scale(d),c.scale(d);var e=Math.ceil(b.getWidth()),f=Math.ceil(c.getWidth()),g=Math.ceil(b.getHeight()),h=Math.ceil(c.getHeight()),i=3,j=1,k=Math.max(e,f),l=Math.max(g,h),m=a(k,i);this.addOperatorShape(m),b.translate((k-e)/2+i,0),m.translate(0,g+1),c.translate((k-f)/2+i,g+m.getHeight()+2),this.parentExpression.setOffset(l-g,l-h),this.parentExpression.expand(2*j,2*j),this.parentExpression.translateElement(j,j)}})}},b[39]={value:function(){function a(){var a=new d(this.funcName,"KF AMS ROMAN");return this.addOperatorShape(a),a.getBaseline=function(){return a.getFixRenderBox().height},a.getMeanline=function(){return 0},a}var c=b.r(34),d=b.r(5),e=b.r(37);return c.createClass("FunctionOperator",{base:b.r(41),constructor:function(a){this.callBase("Function: "+a),this.funcName=a},applyOperand:function(b,c,d){var f=a.call(this),g=b.getFixRenderBox(),h=this.parentExpression.isSideScript()?"applySide":"applyUpDown",i=new e(this,f,c,d,{zoom:.5})[h](),j=5,k=(i.height+i.top+i.bottom-g.height)/2;f.translate(0,i.top),c.translate(0,i.top),d.translate(0,i.top),k>=0?b.translate(i.width+j,k):(k=-k,f.translate(0,k),c.translate(0,k),d.translate(0,k),b.translate(i.width+j,0)),this.parentExpression.expand(j,2*j),this.parentExpression.translateElement(j,j)}})}},b[40]={value:function(){var a=b.r(34),c=b.r(37);return a.createClass("IntegrationOperator",{base:b.r(41),constructor:function(a){this.callBase("Integration"),this.opType=a||1},setType:function(a){this.opType=0|a},resetType:function(){this.opType=1},applyOperand:function(a,b,d){var e=this.getOperatorShape(),f=3,g=a.getFixRenderBox(),h=new c(this,e,b,d,{supOffset:3,subOffset:-15}).applySide(),i=(h.height+h.top-g.height)/2;e.translate(0,h.top),b.translate(0,h.top),d.translate(0,h.top),i>=0?a.translate(h.width+f,i):(i=-i,e.translate(0,i),b.translate(0,i),d.translate(0,i),a.translate(h.width+f,0)),this.parentExpression.expand(f,2*f),this.parentExpression.translateElement(f,f)},getOperatorShape:function(){var b="M1.318,48.226c0,0,0.044,0.066,0.134,0.134c0.292,0.313,0.626,0.447,1.006,0.447c0.246,0.022,0.358-0.044,0.604-0.268   c0.782-0.782,1.497-2.838,2.324-6.727c0.514-2.369,0.938-4.693,1.586-8.448C8.559,24.068,9.9,17.878,11.978,9.52   c0.917-3.553,1.922-7.576,3.866-8.983C16.247,0.246,16.739,0,17.274,0c1.564,0,2.503,1.162,2.592,2.57   c0,0.827-0.424,1.386-1.273,1.386c-0.671,0-1.229-0.514-1.229-1.251c0-0.805,0.514-1.095,1.185-1.274   c0.022,0-0.291-0.29-0.425-0.379c-0.201-0.134-0.514-0.224-0.737-0.224c-0.067,0-0.112,0-0.157,0.022   c-0.469,0.134-0.983,0.939-1.453,2.234c-0.537,1.475-0.961,3.174-1.631,6.548c-0.424,2.101-0.693,3.464-1.229,6.727   c-1.608,9.185-2.949,15.487-5.006,23.756c-0.514,2.034-0.849,3.24-1.207,4.335c-0.559,1.698-1.162,2.95-1.811,3.799   c-0.514,0.715-1.385,1.408-2.436,1.408c-1.363,0-2.391-1.185-2.458-2.592c0-0.804,0.447-1.363,1.273-1.363   c0.671,0,1.229,0.514,1.229,1.251C2.503,47.757,1.989,48.047,1.318,48.226z",c=new a.Group,d=new a.Group,e=new a.Path(b).fill("black"),f=new a.Rect(0,0,0,0).fill("transparent"),g=null;d.addShape(e),c.addShape(f),c.addShape(d),this.addOperatorShape(c);for(var h=1;h<this.opType;h++)g=new a.Use(e).translate(e.getWidth()/2*h,0),d.addShape(g);return d.scale(1.6),g=null,c.getBaseline=function(){return d.getFixRenderBox().height},c.getMeanline=function(){return 10},c}})}},b[41]={value:function(){var a=b.r(34),c=b.r(6);return a.createClass("Operator",{base:b.r(46),constructor:function(b){this.callBase(),this.type=c.OP,this.parentExpression=null,this.operatorName=b,this.operatorShape=new a.Group,this.addShape(this.operatorShape)},applyOperand:function(){throw new Error("applyOperand is abstract")},setParentExpression:function(a){this.parentExpression=a},getParentExpression:function(){return this.parentExpression},clearParentExpression:function(){this.parentExpression=null},addOperatorShape:function(a){return this.operatorShape.addShape(a)},getOperatorShape:function(){return this.operatorShape}})}},b[42]={value:function(){function a(a,b){var h=c(a),i=d(a),j=5,k=e(a);this.addOperatorShape(h),this.addOperatorShape(i),this.addOperatorShape(k),g.call(this,f(h,i,k),this.operatorShape,a,b),this.parentExpression.expand(0,2*j),this.parentExpression.translateElement(0,j)}function c(a){var b=new h.Path,c=i,d=a.getHeight()/3,e=b.getDrawer();return e.moveTo(0,l*c*6),e.lineBy(k*c,l*c),e.lineBy(l*c*3,-k*c*3),e.lineBy(m*d,d),e.lineBy(k*c*3,-l*c*3),e.lineBy(-k*d,-d),e.close(),b.fill("black")}function d(a){var b=new h.Path,c=.9*a.getHeight(),d=b.getDrawer();return d.moveTo(m*c,0),d.lineTo(0,c),d.lineBy(k*i*3,l*i*3),d.lineBy(m*c+k*i*3,-(c+3*i*l)),d.close(),b.fill("black")}function e(a){var b=a.getWidth()+2*i;return new h.Rect(b,2*i).fill("black")}function f(a,b,c){var d=a.getFixRenderBox(),e=b.getFixRenderBox();return b.translate(d.width-k*i*3,0),a.translate(0,e.height-d.height),e=b.getFixRenderBox(),c.translate(e.x+e.width-i/l,0),{x:e.x+e.width-i/l,y:0}}function g(a,b,c,d){var e=null,f={x:0,y:0},g=b.getFixRenderBox();d.scale(.66),e=d.getFixRenderBox(),e.width>0&&e.height>0&&(f.y=e.height-g.height/2,f.y<0&&(d.translate(0,-f.y),f.y=0),f.x=e.width+g.height/2*m-a.x),b.translate(f.x,f.y),c.translate(f.x+a.x+i,f.y+2*i)}var h=b.r(34),i=1,j=2*Math.PI/360,k=Math.sin(15*j),l=Math.cos(15*j),m=Math.tan(15*j);return h.createClass("RadicalOperator",{base:b.r(41),constructor:function(){this.callBase("Radical")},applyOperand:function(b,c){a.call(this,b,c)}})}},b[43]={value:function(){var a=b.r(34),c=b.r(37);return a.createClass("ScriptOperator",{base:b.r(41),constructor:function(a){this.callBase(a||"Script")},applyOperand:function(a,b,d){var e=1,f=this.parentExpression,g=new c(this,a,b,d).applySide();this.getOperatorShape(),g&&f.setOffset(g.top,g.bottom),f.expand(4,2*e),f.translateElement(2,e)}})}},b[44]={value:function(){var a=b.r(34),c=b.r(37);return a.createClass("SummationOperator",{base:b.r(41),constructor:function(){this.callBase("Summation"),this.displayType="equation"},applyOperand:function(a,b,d){var e=this.getOperatorShape(),f=a.getFixRenderBox(),g=0,h=new c(this,e,b,d).applyUpDown(),i=(h.height-h.top-h.bottom-f.height)/2;i>=0?a.translate(h.width+g,i+h.bottom):(i=-i,e.translate(0,i),b.translate(0,i),d.translate(0,i),a.translate(h.width+g,h.bottom)),this.parentExpression.setOffset(h.top,h.bottom),this.parentExpression.expand(g,2*g),this.parentExpression.translateElement(g,g)},getOperatorShape:function(){var b="M0.672,33.603c-0.432,0-0.648,0-0.648-0.264c0-0.024,0-0.144,0.24-0.432l12.433-14.569L0,0.96c0-0.264,0-0.72,0.024-0.792   C0.096,0.024,0.12,0,0.672,0h28.371l2.904,6.745h-0.6C30.531,4.8,28.898,3.72,28.298,3.336c-1.896-1.2-3.984-1.608-5.28-1.8   c-0.216-0.048-2.4-0.384-5.617-0.384H4.248l11.185,15.289c0.168,0.24,0.168,0.312,0.168,0.36c0,0.12-0.048,0.192-0.216,0.384   L3.168,31.515h14.474c4.608,0,6.96-0.624,7.464-0.744c2.76-0.72,5.305-2.352,6.241-4.848h0.6l-2.904,7.681H0.672z",c=new a.Path(b).fill("black"),d=new a.Rect(0,0,0,0).fill("transparent"),e=new a.Group,f=null;return e.addShape(d),e.addShape(c),c.scale(1.6),this.addOperatorShape(e),f=c.getFixRenderBox(),"inline"===this.displayType?(c.translate(5,15),d.setSize(f.width+10,f.height+25)):(c.translate(2,5),d.setSize(f.width+4,f.height+8)),e}})}},b[45]={value:function(){function a(a){a=e.Utils.extend({},g,a),/^(https?:)?\/\//.test(a.path)||(a.path=d(a.path)),new h(document,a.path).mount(c)}function c(){e.Utils.each(f,function(a){a(i)})}function d(a){var b,c=location.pathname.split("/");return c.length-=1,c=c.join("/")+"/",b=[location.protocol,"//",location.host,c,a.replace(/^\//,"")],b.join("")}var e=b.r(34),f=[],g=b.r(47).resource,h=b.r(24),i=b.r(31),j=!1,k=!1;return{ready:function(b,c){k||(k=!0,a(c)),j?window.setTimeout(function(){b(i)},0):f.push(b)}}}},b[46]={value:function(){var a=b.r(34),c=b.r(6);return a.createClass("SignGroup",{base:a.Group,constructor:function(){this.callBase(),this.box=new a.Rect(0,0,0,0),this.type=c.UNKNOWN,this.addShape(this.box),this.zoom=1},setZoom:function(a){this.zoom=a},getZoom:function(){return this.zoom},setBoxSize:function(a,b){return this.box.setSize(a,b)},setBoxWidth:function(a){return this.box.setWidth(a)},setBoxHeight:function(a){return this.box.setHeight(a)},getType:function(){return this.type},getBaseHeight:function(){return this.getHeight()},getBaseWidth:function(){return this.getWidth()},addedCall:function(){}})}},b[47]={value:function(){return{zoom:.66,font:{meanline:Math.round(19),baseline:Math.round(40),baseHeight:50,list:[b.r(29),b.r(27),b.r(28),b.r(26),b.r(30)]},resource:{path:"src/resource/"},func:{"ud-script":{lim:!0}}}}},b[48]={value:function(){window.kf={ResourceManager:b.r(45),Operator:b.r(41),Expression:b.r(21),CompoundExpression:b.r(19),TextExpression:b.r(22),EmptyExpression:b.r(20),CombinationExpression:b.r(12),FunctionExpression:b.r(14),FractionExpression:b.r(13),IntegrationExpression:b.r(15),RadicalExpression:b.r(16),ScriptExpression:b.r(17),SuperscriptExpression:b.r(9),SubscriptExpression:b.r(8),SummationExpression:b.r(18),BracketsExpression:b.r(11)}}};var c={"kf.start":48};!function(){kity.Shape.getRenderBox;kity.extendClass(kity.Shape,{getFixRenderBox:function(){return this.getRenderBox(this.container.container)},getTranslate:function(){return this.transform.translate}});try{a("kf.start")}catch(b){}}(this)}();

/*!
 * ====================================================
 * Kity Formula Parser - v1.0.0 - 2014-07-31
 * https://github.com/HanCong03/kityformula-editor
 * GitHub: https://github.com/kitygraph/kityformula-editor.git
 * Copyright (c) 2014 Baidu Kity Group; Licensed MIT
 * ====================================================
 */
!function(){function a(a){b.r([c[a]])}var b={r:function(a){if(b[a].inited)return b[a].value;if("function"!=typeof b[a].value)return b[a].inited=!0,b[a].value;var c={exports:{}},d=b[a].value(null,c.exports,c);if(b[a].inited=!0,b[a].value=d,void 0!==d)return d;for(var e in c.exports)if(c.exports.hasOwnProperty(e))return b[a].inited=!0,b[a].value=c.exports,c.exports}};b[0]={value:function(){function a(a){this.formula=a}function b(a,e,f,g,i){var j,k=null,l=null,m=[],n=e.operand||[],o=null;if(f.operand=[],-1===e.name.indexOf("text")){for(var p=0,q=n.length;q>p;p++)k=n[p],k!==h?k?"string"==typeof k?(n[p]="brackets"===e.name&&2>p?k:"function"===e.name&&0===p?k:c("text",k),f.operand.push(n[p])):(f.operand.push({}),n[p]=b(a.operand[p],k,f.operand[f.operand.length-1],g,i)):(n[p]=c("empty"),f.operand.push(n[p])):(m.push(p),i.hasOwnProperty("startOffset")||(i.startOffset=p),i.endOffset=p,e.attr&&e.attr.id&&(i.groupId=e.attr.id));for(2===m.length&&(i.endOffset-=1);p=m.length;)p=m[p-1],n.splice(p,1),m.length--,a.operand.splice(p,1)}if(o=d(e.name),!o)throw new Error("operator type error: not found "+e.operator);j=function(){},j.prototype=o.prototype,l=new j,o.apply(l,n),f.func=l;for(var r in e.callFn)e.callFn.hasOwnProperty(r)&&l[r]&&l[r].apply(l,e.callFn[r]);return e.attr&&(e.attr.id&&(g[e.attr.id]={objGroup:l,strGroup:a}),e.attr["data-root"]&&(g.root={objGroup:l,strGroup:a}),l.setAttr(e.attr)),l}function c(a,b){switch(a){case"empty":return new kf.EmptyExpression;case"text":return new kf.TextExpression(b)}}function d(a){return g[a]||kf[a.replace(/^[a-z]/i,function(a){return a.toUpperCase()}).replace(/-([a-z])/gi,function(a,b){return b.toUpperCase()})+"Expression"]}function e(a){var b={};if("[object Array]"==={}.toString.call(a)){b=[];for(var c=0,d=a.length;d>c;c++)b[c]=f(a[c])}else for(var e in a)a.hasOwnProperty(e)&&(b[e]=f(a[e]));return b}function f(a){return a?"object"!=typeof a?a:e(a):a}var g={},h="\uf155";return a.prototype.generateBy=function(a){var c=a.tree,d={},f={},g={};if("string"==typeof c)throw new Error("Unhandled error");return this.formula.appendExpression(b(c,e(c),d,g,f)),{select:f,parsedTree:c,tree:d,mapping:g}},a.prototype.regenerateBy=function(a){return this.formula.clearExpressions(),this.generateBy(a)},a}},b[1]={value:function(){return{toRPNExpression:b.r(2),generateTree:b.r(3)}}},b[2]={value:function(){function a(b){var e=[],f=null;for(b=c(b);f=b.shift();)"combination"===f.name&&1===f.operand.length&&"brackets"===f.operand[0].name&&(f=f.operand[0]),e.push(d.isArray(f)?a(f):f);return e}function c(a){for(var b=[],c=null;void 0!==(c=a.pop());)if(!c||"object"!=typeof c||c.sign!==!1&&"function"!==c.name)b.push(c);else{var d=c.handler(c,[],b.reverse());b.unshift(d),b.reverse()}return b.reverse()}var d=b.r(4);return a}},b[3]={value:function(){function a(b){for(var e=null,f=[],g=0,h=b.length;h>g;g++)d.isArray(b[g])&&(b[g]=a(b[g]));for(;e=b.shift();)f.push("object"==typeof e&&e.handler?e.handler(e,f,b):e);return c(f)}var c=b.r(13),d=b.r(4);return a}},b[4]={value:function(){var a=b.r(7),c=b.r(6),d=b.r(15),e={getLatexType:function(b){return b=b.replace(/^\\/,""),a[b]?"operator":c[b]?"function":"text"},isArray:function(a){return a&&"[object Array]"===Object.prototype.toString.call(a)},getDefine:function(b){return e.extend({},a[b.replace("\\","")])},getFuncDefine:function(a){return{name:"function",params:a.replace(/^\\/,""),handler:d}},getBracketsDefine:function(b,c){return e.extend({params:[b,c]},a.brackets)},extend:function(a,b){for(var c in b)b.hasOwnProperty(c)&&(a[c]=b[c]);return a}};return e}},b[5]={value:function(){var a=!0;return{".":a,"{":a,"}":a,"[":a,"]":a,"(":a,")":a,"|":a}}},b[6]={value:function(){return{sin:1,cos:1,arccos:1,cosh:1,det:1,inf:1,limsup:1,Pr:1,tan:1,arcsin:1,cot:1,dim:1,ker:1,ln:1,sec:1,tanh:1,arctan:1,coth:1,exp:1,lg:1,log:1,arg:1,csc:1,gcd:1,lim:1,max:1,sinh:1,deg:1,hom:1,liminf:1,min:1,sup:1}}},b[7]={value:function(){var a=b.r(22),c=b.r(11);return{"^":{name:"superscript",type:c.OP,handler:a},_:{name:"subscript",type:c.OP,handler:a},frac:{name:"fraction",type:c.FN,sign:!1,handler:b.r(14)},sqrt:{name:"radical",type:c.FN,sign:!1,handler:b.r(23)},sum:{name:"summation",type:c.FN,traversal:"rtl",handler:b.r(24)},"int":{name:"integration",type:c.FN,traversal:"rtl",handler:b.r(16)},brackets:{name:"brackets",type:c.FN,handler:b.r(12)},mathcal:{name:"mathcal",type:c.FN,sign:!1,handler:b.r(19)},mathfrak:{name:"mathfrak",type:c.FN,sign:!1,handler:b.r(20)},mathbb:{name:"mathbb",type:c.FN,sign:!1,handler:b.r(18)},mathrm:{name:"mathrm",type:c.FN,sign:!1,handler:b.r(21)}}}},b[8]={value:function(){return{"int":b.r(26),quot:b.r(27)}}},b[9]={value:function(){return{combination:b.r(29),fraction:b.r(30),"function":b.r(31),integration:b.r(32),subscript:b.r(39),superscript:b.r(41),script:b.r(37),radical:b.r(38),summation:b.r(40),brackets:b.r(28),mathcal:b.r(34),mathfrak:b.r(35),mathbb:b.r(33),mathrm:b.r(36)}}},b[10]={value:function(){return{"#":1,$:1,"%":1,_:1,"&":1,"{":1,"}":1,"^":1,"~":1}}},b[11]={value:function(){return{OP:1,FN:2}}},b[12]={value:function(){var a=b.r(5);return function(b,c,d){for(var e=0,f=b.params.length;f>e;e++)if(!(b.params[e]in a))throw new Error("Brackets: invalid params");return b.operand=b.params,b.params[2]=d.shift(),delete b.handler,delete b.params,b}}},b[13]={value:function(){return function(){return{name:"combination",operand:arguments[0]||[]}}}},b[14]={value:function(){return function(a,b,c){var d=c.shift(),e=c.shift();if(void 0===d||void 0===e)throw new Error("Frac: Syntax Error");return d.handler&&"integration"===d.name?(d=d.handler(d,b,[e]),e=c.shift()):e.handler&&"integration"===e.name&&(e=e.handler(e,b,[c.shift()])),a.operand=[d,e],delete a.handler,a}}},b[15]={value:function(){var a=b.r(17);return function(b,c,d){var e=a.exec(d);return e.expr&&e.expr.handler&&"integration"===e.expr.name&&(e.expr=e.expr.handler(e.expr,c,[d.shift()])),b.operand=[b.params,e.expr,e.superscript,e.subscript],delete b.params,delete b.handler,b}}},b[16]={value:function(){var a=b.r(17),c=b.r(11).FN;return function(b,d,e){var f=e.shift(),g=a.exec(e);return g.expr&&g.expr.type===c&&g.expr.handler&&"integration"===g.expr.name&&(g.expr=g.expr.handler(g.expr,d,[e.shift()])),b.operand=[g.expr,g.superscript,g.subscript],b.callFn={setType:[0|f]},delete b.handler,b}}},b[17]={value:function(){function a(a){var c=b(a),d=null,e={superscript:null,subscript:null};if(!c)return e;if(d=b(a),e[c.type]=c.value||null,d){if(d.type===c.type)throw new Error("Script: syntax error!");e[d.type]=d.value||null}return e}function b(a){var b=a.shift();return b?"subscript"===b.name||"superscript"===b.name?{type:b.name,value:a.shift()}:(a.unshift(b),null):null}return{exec:function(b){var c=a(b),d=b.shift();if(d&&d.name&&-1!==d.name.indexOf("script"))throw new Error("Script: syntax error!");return c.expr=d||null,c}}}},b[18]={value:function(){return function(a,b,c){var d=c.shift();return"object"==typeof d&&"combination"===d.name&&(d=d.operand.join("")),a.name="text",a.attr={_reverse:"mathbb"},a.callFn={setFamily:["KF AMS BB"]},a.operand=[d],delete a.handler,a}}},b[19]={value:function(){return function(a,b,c){var d=c.shift();return"object"==typeof d&&"combination"===d.name&&(d=d.operand.join("")),a.name="text",a.attr={_reverse:"mathcal"},a.callFn={setFamily:["KF AMS CAL"]},a.operand=[d],delete a.handler,a}}},b[20]={value:function(){return function(a,b,c){var d=c.shift();return"object"==typeof d&&"combination"===d.name&&(d=d.operand.join("")),a.name="text",a.attr={_reverse:"mathfrak"},a.callFn={setFamily:["KF AMS FRAK"]},a.operand=[d],delete a.handler,a}}},b[21]={value:function(){return function(a,b,c){var d=c.shift();return"object"==typeof d&&"combination"===d.name&&(d=d.operand.join("")),a.name="text",a.attr={_reverse:"mathrm"},a.callFn={setFamily:["KF AMS ROMAN"]},a.operand=[d],delete a.handler,a}}},b[22]={value:function(){return function(a,b,c){var d=b.pop(),e=c.shift()||null;if(!e)throw new Error("Missing script");if(d=d||"",d.name===a.name||"script"===d.name)throw new Error("script error");return"subscript"===d.name?(d.name="script",d.operand[2]=d.operand[1],d.operand[1]=e,d):"superscript"===d.name?(d.name="script",d.operand[2]=e,d):(a.operand=[d,e],delete a.handler,a)}}},b[23]={value:function(){var a=b.r(13);return function(b,c,d){var e=d.shift(),f=null,g=null;if("["===e){for(e=[];(f=d.shift())&&"]"!==f;)e.push(f);e=0===e.length?null:a(e),g=d.shift()}else g=e,e=null;return b.operand=[g,e],delete b.handler,b}}},b[24]={value:function(){var a=b.r(17),c=b.r(11).FN;return function(b,d,e){var f=a.exec(e);return f.expr&&f.expr.type===c&&f.expr.handler&&"integration"===f.expr.name&&(f.expr=f.expr.handler(f.expr,d,[e.shift()])),b.operand=[f.expr,f.superscript,f.subscript],delete b.handler,b}}},b[25]={value:function(){function a(a){if(d(a))return a.substring(1);switch(m.getLatexType(a)){case"operator":return m.getDefine(a);case"function":return m.getFuncDefine(a);default:return c(a)}}function c(a){return 0===a.indexOf("\\")?a+"\\":a}function d(a){return 0===a.indexOf("\\")?!!l[a.substring(1)]:!1}function e(a){return a.replace(/\\\s+/,"").replace(/\s*([^a-z0-9\s])\s*/gi,function(a,b){return b})}var f=b.r(43).Parser,g=b.r(1),h=b.r(8),i=b.r(42),j=b.r(7),k=b.r(9),l=b.r(10),m=b.r(4),n="\ufff8",o="\ufffc",p=new RegExp(n+"|"+o,"g"),q=new RegExp(n,"g"),r=new RegExp(o,"g");f.register("latex",f.implement({parse:function(a){var b=this.split(this.format(a));return b=this.parseToGroup(b),b=this.parseToStruct(b),this.generateTree(b)},serialization:function(a,b){return i(a,b)},expand:function(a){var b=a.parse,c=null,d=a.pre,e=a.reverse;for(var f in b)b.hasOwnProperty(f)&&(c=f.replace(/\\/g,""),j[c]=b[f]);for(var f in e)e.hasOwnProperty(f)&&(k[f.replace(/\\/g,"")]=e[f]);if(d)for(var f in d)d.hasOwnProperty(f)&&(h[f.replace(/\\/g,"")]=d[f])},format:function(a){a=e(a),a=a.replace(p,"").replace(/\\{/gi,n).replace(/\\}/gi,o);for(var b in h)h.hasOwnProperty(b)&&(a=h[b](a));return a},split:function(a){var b=[],c=/(?:\\[^a-z]\s*)|(?:\\[a-z]+\s*)|(?:[{}]\s*)|(?:[^\\{}]\s*)/gi,d=/^\s+|\s+$/g,e=null;for(a=a.replace(d,"");e=c.exec(a);)e=e[0].replace(d,""),e&&b.push(e);return b},generateTree:function(a){for(var b=[],c=null;c=a.shift();)b.push(m.isArray(c)?this.generateTree(c):c);return b=g.toRPNExpression(b),g.generateTree(b)},parseToGroup:function(a){for(var b=[],c=[b],d=0,e=0,f=0,g=a.length;g>f;f++)switch(a[f]){case"{":d++,c.push(b),b.push([]),b=b[b.length-1];break;case"}":d--,b=c.pop();break;case"\\left":e++,c.push(b),b.push([[]]),b=b[b.length-1][0],b.type="brackets",f++,b.leftBrackets=a[f].replace(q,"{").replace(r,"}");break;case"\\right":e--,f++,b.rightBrackets=a[f].replace(q,"{").replace(r,"}"),b=c.pop();break;default:b.push(a[f].replace(q,"\\{").replace(r,"\\}"))}if(0!==d)throw new Error("Group Error!");if(0!==e)throw new Error("Brackets Error!");return c[0]},parseToStruct:function(b){for(var c=[],d=0,e=b.length;e>d;d++)m.isArray(b[d])?"brackets"===b[d].type?(c.push(m.getBracketsDefine(b[d].leftBrackets,b[d].rightBrackets)),c.push(this.parseToStruct(b[d]))):c.push(this.parseToStruct(b[d])):c.push(a(b[d]));return c}}))}},b[26]={value:function(){return function(a){return a.replace(/\\(i+)nt(\b|[^a-zA-Z])/g,function(a,b,c){return"\\int "+b.length+c})}}},b[27]={value:function(){return function(a){return a.replace(/``/g,"\u201c")}}},b[28]={value:function(){return function(a){return("{"===a[0]||"}"===a[0])&&(a[0]="\\"+a[0]),("{"===a[1]||"}"===a[1])&&(a[1]="\\"+a[1]),["\\left",a[0],a[2],"\\right",a[1]].join(" ")}}},b[29]={value:function(){return function(a){return this.attr["data-root"]||this.attr["data-placeholder"]?a.join(""):"{"+a.join("")+"}"}}},b[30]={value:function(){return function(a){return"\\frac "+a[0]+" "+a[1]}}},b[31]={value:function(){return function(a){var b=["\\"+a[0]];return a[2]&&b.push("^"+a[2]),a[3]&&b.push("_"+a[3]),a[1]&&b.push(" "+a[1]),b.join("")}}},b[32]={value:function(){return function(a){var b=["\\int "];if(this.callFn&&this.callFn.setType){b=["\\"];for(var c=0,d=this.callFn.setType;d>c;c++)b.push("i");b.push("nt ")}return a[1]&&b.push("^"+a[1]),a[2]&&b.push("_"+a[2]),a[0]&&b.push(" "+a[0]),b.join("")}}},b[33]={value:function(){return function(a){return"\\mathbb{"+a[0]+"}"}}},b[34]={value:function(){return function(a){return"\\mathcal{"+a[0]+"}"}}},b[35]={value:function(){return function(a){return"\\mathfrak{"+a[0]+"}"}}},b[36]={value:function(){return function(a){return"\\mathrm{"+a[0]+"}"}}},b[37]={value:function(){return function(a){return a[0]+"^"+a[1]+"_"+a[2]}}},b[38]={value:function(){return function(a){var b=["\\sqrt"];return a[1]&&b.push("["+a[1]+"]"),b.push(" "+a[0]),b.join("")}}},b[39]={value:function(){return function(a){return a[0]+"_"+a[1]}}},b[40]={value:function(){return function(a){var b=["\\sum "];return a[1]&&b.push("^"+a[1]),a[2]&&b.push("_"+a[2]),a[0]&&b.push(" "+a[0]),b.join("")}}},b[41]={value:function(){return function(a){return a[0]+"^"+a[1]}}},b[42]={value:function(){function a(b,e){var g=[],h=null,i=null;if("object"!=typeof b)return c(b)?"\\"+b+" ":b.replace(f,function(a,b){return b+" "});"combination"===b.name&&1===b.operand.length&&"combination"===b.operand[0].name&&(b=b.operand[0]),i=b.operand;for(var j=0,k=i.length;k>j;j++)g.push(i[j]?a(i[j]):i[j]);return h=b.attr&&b.attr._reverse?b.attr._reverse:b.name,d[h].call(b,g,e)}function c(a){return!!e[a]}var d=b.r(9),e=b.r(10),f=/(\\(?:[\w]+)|(?:[^a-z]))\\/gi;return function(b,c){return a(b,c)}}},b[43]={value:function(a,b,c){function d(a){this.impl=new a,this.conf={}}function e(){this.conf={}}var f={},g={},h={extend:function(a,b){var c=null;b=[].slice.call(arguments,1);for(var d=0,e=b.length;e>d;d++){c=b[d];for(var f in c)c.hasOwnProperty(f)&&(a[f]=c[f])}},setData:function(a,b,c){if("string"==typeof b)a[b]=c;else{if("object"!=typeof b)throw new Error("invalid option");for(c in b)b.hasOwnProperty(c)&&(a[c]=b[c])}}},i={use:function(a){if(!g[a])throw new Error("unknown parser type");return this.proxy(g[a])},config:function(a,b){return h.setData(f,a,b),this},register:function(a,b){return g[a.toLowerCase()]=b,this},implement:function(a){var b=function(){},c=a.constructor||function(){},d=function(){e.call(this),c.call(this)};b.prototype=e.prototype,d.prototype=new b,delete a.constructor;for(var f in a)"constructor"!==f&&a.hasOwnProperty(f)&&(d.prototype[f]=a[f]);return d},proxy:function(a){return new d(a)}};h.extend(d.prototype,{config:function(a,b){h.setData(this.conf,a,b)},set:function(a,b){this.impl.set(a,b)},parse:function(a){var b={config:{},tree:this.impl.parse(a)};return h.extend(b.config,f,this.conf),b},serialization:function(a,b){return this.impl.serialization(a,b)},expand:function(a){this.impl.expand(a)}}),h.extend(e.prototype,{set:function(a,b){h.extend(this.conf,a,b)},parse:function(){throw new Error("Abstract function")}}),c.exports={Parser:i,ParserInterface:e}}},b[44]={value:function(){var a=b.r(43).Parser;b.r(25),window.kf.Parser=a,window.kf.Assembly=b.r(0)}};var c={"kf.start":44};!function(){try{a("kf.start")}catch(b){}}(this)}();

/*!
 * ====================================================
 * Kity Formula Editor - v1.0.0 - 2015-04-10
 * https://github.com/kitygraph/formula
 * GitHub: https://github.com/kitygraph/formula.git
 * Copyright (c) 2015 Baidu Kity Group; Licensed MIT
 * ====================================================
 */

(function () {
    var _p = {
        r: function(index) {
            if (_p[index].inited) {
                return _p[index].value;
            }
            if (typeof _p[index].value === "function") {
                var module = {
                    exports: {}
                }, returnValue = _p[index].value(null, module.exports, module);
                _p[index].inited = true;
                _p[index].value = returnValue;
                if (returnValue !== undefined) {
                    return returnValue;
                } else {
                    for (var key in module.exports) {
                        if (module.exports.hasOwnProperty(key)) {
                            _p[index].inited = true;
                            _p[index].value = module.exports;
                            return module.exports;
                        }
                    }
                }
            } else {
                _p[index].inited = true;
                return _p[index].value;
            }
        }
    };

//src/base/common.js
    /**
     * Created by hn on 14-3-17.
     */
    _p[0] = {
        value: function(require) {
            // copy保护
            var MAX_COPY_DEEP = 10, commonUtils = {
                extend: function(target, source) {
                    var isDeep = false;
                    if (typeof target === "boolean") {
                        isDeep = target;
                        target = source;
                        source = [].splice.call(arguments, 2);
                    } else {
                        source = [].splice.call(arguments, 1);
                    }
                    if (!target) {
                        throw new Error("Utils: extend, target can not be empty");
                    }
                    commonUtils.each(source, function(src) {
                        if (src && typeof src === "object" || typeof src === "function") {
                            copy(isDeep, target, src);
                        }
                    });
                    return target;
                },
                /**
                 * 返回给定节点parent是否包含target节点
                 * @param parent
                 * @param target
                 */
                contains: function(parent, target) {
                    if (parent.contains) {
                        return parent.contains(target);
                    } else if (parent.compareDocumentPosition) {
                        return !!(parent.compareDocumentPosition(target) & 16);
                    }
                },
                getRect: function(node) {
                    return node.getBoundingClientRect();
                },
                isArray: function(obj) {
                    return obj && {}.toString.call(obj) === "[object Array]";
                },
                isString: function(obj) {
                    return typeof obj === "string";
                },
                proxy: function(fn, context) {
                    return function() {
                        return fn.apply(context, arguments);
                    };
                },
                each: function(obj, fn) {
                    if (!obj) {
                        return;
                    }
                    if ("length" in obj && typeof obj.length === "number") {
                        for (var i = 0, len = obj.length; i < len; i++) {
                            if (fn.call(null, obj[i], i, obj) === false) {
                                break;
                            }
                        }
                    } else {
                        for (var key in obj) {
                            if (obj.hasOwnProperty(key)) {
                                if (fn.call(null, obj[key], key, obj) === false) {
                                    break;
                                }
                            }
                        }
                    }
                }
            };
            function copy(isDeep, target, source, count) {
                count = count | 0;
                if (count > MAX_COPY_DEEP) {
                    return source;
                }
                count++;
                commonUtils.each(source, function(value, index, origin) {
                    if (isDeep) {
                        if (!value || typeof value !== "object" && typeof value !== "function") {
                            target[index] = value;
                        } else {
                            target[index] = target[index] || (commonUtils.isArray(value) ? [] : {});
                            target[index] = copy(isDeep, target[index], value, count);
                        }
                    } else {
                        target[index] = value;
                    }
                });
                return target;
            }
            return commonUtils;
        }
    };

//src/base/component.js
    /*!
     * 组件抽象类，所有的组件都是该类的子类
     * @abstract
     */
    _p[1] = {
        value: function(require) {
            var kity = _p.r(20);
            return kity.createClass("Component", {
                constructor: function() {}
            });
        }
    };

//src/base/event/event.js
    /*!
     * event模块
     */
    /* jshint camelcase: false */
    _p[2] = {
        value: function(require, exports, modules) {
            var EVENT_LISTENER = {}, eid = 0, BEFORE_RESULT = true, KFEvent = _p.r(3), commonUtils = _p.r(0), EVENT_HANDLER = function(e) {
                var type = e.type, target = e.target, eid = this.__kfe_eid, hasAutoTrigger = /^(?:before|after)/.test(type), HANDLER_LIST = EVENT_LISTENER[eid][type];
                if (!hasAutoTrigger) {
                    EventListener.trigger(target, "before" + type);
                    if (BEFORE_RESULT === false) {
                        BEFORE_RESULT = true;
                        return false;
                    }
                }
                commonUtils.each(HANDLER_LIST, function(handler, index) {
                    if (!handler) {
                        return;
                    }
                    if (handler.call(target, e) === false) {
                        BEFORE_RESULT = false;
                        return BEFORE_RESULT;
                    }
                });
                if (!hasAutoTrigger) {
                    EventListener.trigger(target, "after" + type);
                }
            };
            var EventListener = {
                addEvent: function(target, type, handler) {
                    var hasHandler = true, eventCache = null;
                    if (!target.__kfe_eid) {
                        hasHandler = false;
                        target.__kfe_eid = generateId();
                        EVENT_LISTENER[target.__kfe_eid] = {};
                    }
                    eventCache = EVENT_LISTENER[target.__kfe_eid];
                    if (!eventCache[type]) {
                        hasHandler = false;
                        eventCache[type] = [];
                    }
                    eventCache[type].push(handler);
                    if (hasHandler) {
                        return;
                    }
                    target.addEventListener(type, EVENT_HANDLER, false);
                },
                trigger: function(target, type, e) {
                    e = e || KFEvent.createEvent(type, e);
                    target.dispatchEvent(e);
                }
            };
            function generateId() {
                return ++eid;
            }
            return EventListener;
        }
    };

//src/base/event/kfevent.js
    /**
     * Created by hn on 14-3-17.
     */
    _p[3] = {
        value: function(require) {
            return {
                createEvent: function(type, e) {
                    var evt = document.createEvent("Event");
                    evt.initEvent(type, true, true);
                    return evt;
                }
            };
        }
    };

//src/base/utils.js
    /*!
     * 基础工具包
     */
    _p[4] = {
        value: function(require) {
            var Utils = {}, commonUtils = _p.r(0);
            commonUtils.extend(Utils, commonUtils, _p.r(2));
            return Utils;
        }
    };

//src/control/controller.js
    /**
     * Created by hn on 14-4-11.
     */
    _p[5] = {
        value: function(require) {
            var kity = _p.r(20), ListenerComponent = _p.r(8), ControllerComponent = kity.createClass("ControllerComponent", {
                constructor: function(kfEditor) {
                    this.kfEditor = kfEditor;
                    this.components = {};
                    this.initComponents();
                },
                initComponents: function() {
                    this.components.listener = new ListenerComponent(this, this.kfEditor);
                }
            });
            return ControllerComponent;
        }
    };

//src/control/input-filter.js
    /*!
     * 输入过滤器
     */
    _p[6] = {
        value: function(require) {
            // 过滤列表， 其中的key对应于键盘事件的keycode， 带有s+字样的key，匹配的是shift+keycode
            var LIST = {
                32: "\\,",
                "s+219": "\\{",
                "s+221": "\\}",
                "220": "\\backslash",
                "s+51": "\\#",
                "s+52": "\\$",
                "s+53": "\\%",
                "s+54": "\\^",
                "s+55": "\\&",
                "s+189": "\\_",
                "s+192": "\\~"
            };
            return {
                getReplaceString: function(key) {
                    return LIST[key] || null;
                }
            };
        }
    };

//src/control/input.js
    /*!
     * 输入控制组件
     */
    _p[7] = {
        value: function(require, exports, module) {
            var kity = _p.r(20), kfUtils = _p.r(4), InputFilter = _p.r(6), KEY_CODE = {
                LEFT: 37,
                RIGHT: 39,
                DELETE: 8,
                // 输入法特殊处理
                INPUT: 229
            };
            return kity.createClass("InputComponent", {
                constructor: function(parentComponent, kfEditor) {
                    this.parentComponent = parentComponent;
                    this.kfEditor = kfEditor;
                    this.inputBox = this.createInputBox();
                    this.initServices();
                    this.initCommands();
                    this.initEvent();
                },
                initServices: function() {
                    this.kfEditor.registerService("control.update.input", this, {
                        updateInput: this.updateInput
                    });
                    this.kfEditor.registerService("control.insert.string", this, {
                        insertStr: this.insertStr
                    });
                },
                initCommands: function() {
                    this.kfEditor.registerCommand("focus", this, this.focus);
                },
                createInputBox: function() {
                    var editorContainer = this.kfEditor.getContainer(), box = this.kfEditor.getDocument().createElement("input");
                    box.className = "kf-editor-input-box";
                    box.type = "text";
                    // focus是否可信
                    box.isTrusted = false;
                    editorContainer.appendChild(box);
                    return box;
                },
                focus: function() {
                    var rootInfo = null;
                    this.inputBox.focus();
                    // 如果当前不包含光标信息， 则手动设置光标信息， 以使得当前根节点被全选中
                    if (!this.kfEditor.requestService("syntax.has.cursor.info")) {
                        rootInfo = this.kfEditor.requestService("syntax.get.root.group.info");
                        this.kfEditor.requestService("syntax.update.record.cursor", {
                            groupId: rootInfo.id,
                            startOffset: 0,
                            endOffset: rootInfo.content.length
                        });
                        this.kfEditor.requestService("control.update.input");
                    }
                    this.kfEditor.requestService("control.reselect");
                },
                setUntrusted: function() {
                    this.inputBox.isTrusted = false;
                },
                setTrusted: function() {
                    this.inputBox.isTrusted = true;
                },
                updateInput: function() {
                    var latexInfo = this.kfEditor.requestService("syntax.serialization");
                    this.setUntrusted();
                    this.inputBox.value = latexInfo.str;
                    this.inputBox.selectionStart = latexInfo.startOffset;
                    this.inputBox.selectionEnd = latexInfo.endOffset;
                    this.inputBox.focus();
                    this.setTrusted();
                },
                insertStr: function(str) {
                    var latexInfo = this.kfEditor.requestService("syntax.serialization"), originString = latexInfo.str;
                    // 拼接latex字符串
                    originString = originString.substring(0, latexInfo.startOffset) + " " + str + " " + originString.substring(latexInfo.endOffset);
                    this.restruct(originString);
                    this.updateInput();
                    this.kfEditor.requestService("ui.update.canvas.view");
                },
                initEvent: function() {
                    var _self = this;
                    kfUtils.addEvent(this.inputBox, "keydown", function(e) {
                        var isControl = false;
                        if (e.ctrlKey) {
                            // 处理用户控制行为
                            _self.processUserCtrl(e);
                            return;
                        }
                        switch (e.keyCode) {
                            case KEY_CODE.INPUT:
                                return;

                            case KEY_CODE.LEFT:
                                e.preventDefault();
                                _self.leftMove();
                                isControl = true;
                                break;

                            case KEY_CODE.RIGHT:
                                e.preventDefault();
                                _self.rightMove();
                                isControl = true;
                                break;

                            case KEY_CODE.DELETE:
                                e.preventDefault();
                                _self.delete();
                                isControl = true;
                                break;
                        }
                        if (isControl) {
                            _self.kfEditor.requestService("ui.update.canvas.view");
                        }
                        if (!_self.pretreatmentInput(e)) {
                            e.preventDefault();
                        }
                    });
                    // 用户输入
                    kfUtils.addEvent(this.inputBox, "input", function(e) {
                        _self.processingInput();
                    });
                    // 光标显隐控制
                    kfUtils.addEvent(this.inputBox, "blur", function(e) {
                        _self.kfEditor.requestService("ui.toolbar.disable");
                        _self.kfEditor.requestService("ui.toolbar.close");
                        _self.kfEditor.requestService("control.cursor.hide");
                        _self.kfEditor.requestService("render.clear.select");
                    });
                    kfUtils.addEvent(this.inputBox, "focus", function(e) {
                        _self.kfEditor.requestService("ui.toolbar.enable");
                        if (this.isTrusted) {
                            _self.kfEditor.requestService("control.reselect");
                        }
                    });
                    // 粘贴过滤
                    kfUtils.addEvent(this.inputBox, "paste", function(e) {
                        e.preventDefault();
                    });
                },
                hasRootplaceholder: function() {
                    return this.kfEditor.requestService("syntax.has.root.placeholder");
                },
                leftMove: function() {
                    // 当前处于"根占位符"上， 则不允许move
                    if (this.hasRootplaceholder()) {
                        return;
                    }
                    this.kfEditor.requestService("syntax.cursor.move.left");
                    this.update();
                },
                rightMove: function() {
                    if (this.hasRootplaceholder()) {
                        return;
                    }
                    this.kfEditor.requestService("syntax.cursor.move.right");
                    this.update();
                },
                "delete": function() {
                    var isNeedRedraw = null;
                    // 当前处于"根占位符"上，不允许删除操作
                    if (this.hasRootplaceholder()) {
                        return;
                    }
                    // 返回是否修要重绘
                    isNeedRedraw = this.kfEditor.requestService("syntax.delete.group");
                    if (isNeedRedraw) {
                        this.updateInput();
                        this.processingInput();
                    } else {
                        this.updateInput();
                        this.kfEditor.requestService("control.reselect");
                    }
                },
                processUserCtrl: function(e) {
                    e.preventDefault();
                    switch (e.keyCode) {
                        // ctrl + A
                        case 65:
                            this.kfEditor.requestService("control.select.all");
                            break;

                        // ctrl + S
                        case 83:
                            this.kfEditor.requestService("print.image");
                            break;
                    }
                },
                // 输入前的预处理， 执行输入过滤
                pretreatmentInput: function(evt) {
                    var keyCode = this.getKeyCode(evt), replaceStr = InputFilter.getReplaceString(keyCode);
                    if (replaceStr === null) {
                        return true;
                    }
                    this.insertStr(replaceStr);
                    return false;
                },
                getKeyCode: function(e) {
                    return (e.shiftKey ? "s+" : "") + e.keyCode;
                },
                processingInput: function() {
                    this.restruct(this.inputBox.value);
                    this.kfEditor.requestService("ui.update.canvas.view");
                },
                // 根据给定的字符串重新进行构造公式
                restruct: function(latexStr) {
                    this.kfEditor.requestService("render.draw", latexStr);
                    this.kfEditor.requestService("control.reselect");
                },
                update: function() {
                    // 更新输入框
                    this.updateInput();
                    this.kfEditor.requestService("control.reselect");
                }
            });
        }
    };

//src/control/listener.js
    /**
     * Created by hn on 14-4-11.
     */
    _p[8] = {
        value: function(require, exports, module) {
            var kity = _p.r(20), // 光标定位
                LocationComponent = _p.r(9), // 输入控制组件
                InputComponent = _p.r(7), // 选区
                SelectionComponent = _p.r(10);
            return kity.createClass("MoveComponent", {
                constructor: function(parentComponent, kfEditor) {
                    this.parentComponent = parentComponent;
                    this.kfEditor = kfEditor;
                    this.components = {};
                    this.initComponents();
                },
                initComponents: function() {
                    this.components.location = new LocationComponent(this, this.kfEditor);
                    this.components.selection = new SelectionComponent(this, this.kfEditor);
                    this.components.input = new InputComponent(this, this.kfEditor);
                }
            });
        }
    };

//src/control/location.js
    /*!
     * 光标定位组件
     */
    _p[9] = {
        value: function(require, exports, module) {
            var kity = _p.r(20);
            return kity.createClass("LocationComponent", {
                constructor: function(parentComponent, kfEditor) {
                    this.parentComponent = parentComponent;
                    this.kfEditor = kfEditor;
                    // 创建光标
                    this.paper = this.getPaper();
                    this.cursorShape = this.createCursor();
                    this.initServices();
                    this.initEvent();
                },
                getPaper: function() {
                    return this.kfEditor.requestService("render.get.paper");
                },
                initServices: function() {
                    // 重定位光标
                    this.kfEditor.registerService("control.cursor.relocation", this, {
                        relocationCursor: this.updateCursor
                    });
                    // 清除光标
                    this.kfEditor.registerService("control.cursor.hide", this, {
                        hideCursor: this.hideCursor
                    });
                    this.kfEditor.registerService("control.reselect", this, {
                        reselect: this.reselect
                    });
                    this.kfEditor.registerService("control.get.cursor.location", this, {
                        getCursorLocation: this.getCursorLocation
                    });
                },
                createCursor: function() {
                    var cursorShape = new kity.Rect(1, 0, 0, 0).fill("black");
                    cursorShape.setAttr("style", "display: none");
                    this.paper.addShape(cursorShape);
                    return cursorShape;
                },
                // 光标定位监听
                initEvent: function() {
                    var eventServiceObject = this.kfEditor.request("ui.canvas.container.event"), _self = this;
                    eventServiceObject.on("mousedown", function(e) {
                        e.preventDefault();
                        _self.updateCursorInfo(e);
                        _self.kfEditor.requestService("control.update.input");
                        _self.reselect();
                    });
                },
                updateCursorInfo: function(evt) {
                    var wrapNode = null, groupInfo = null, index = -1;
                    // 有根占位符存在， 所有定位到定位到根占位符内部
                    if (this.kfEditor.requestService("syntax.has.root.placeholder")) {
                        this.kfEditor.requestService("syntax.update.record.cursor", {
                            groupId: this.kfEditor.requestService("syntax.get.root.group.info").id,
                            startOffset: 0,
                            endOffset: 1
                        });
                        return false;
                    }
                    wrapNode = this.kfEditor.requestService("position.get.wrap", evt.target);
                    // 占位符处理, 选中该占位符
                    if (wrapNode && this.kfEditor.requestService("syntax.is.placeholder.node", wrapNode.id)) {
                        groupInfo = this.kfEditor.requestService("position.get.group.info", wrapNode);
                        this.kfEditor.requestService("syntax.update.record.cursor", groupInfo.group.id, groupInfo.index, groupInfo.index + 1);
                        return;
                    }
                    groupInfo = this.kfEditor.requestService("position.get.group", evt.target);
                    if (groupInfo === null) {
                        groupInfo = this.kfEditor.requestService("syntax.get.root.group.info");
                    }
                    index = this.getIndex(evt.clientX, groupInfo);
                    this.kfEditor.requestService("syntax.update.record.cursor", groupInfo.id, index);
                },
                hideCursor: function() {
                    this.cursorShape.setAttr("style", "display: none");
                },
                // 根据当前的光标信息， 对选区和光标进行更新
                reselect: function() {
                    var cursorInfo = this.kfEditor.requestService("syntax.get.record.cursor"), groupInfo = null;
                    this.hideCursor();
                    // 根节点单独处理
                    if (this.kfEditor.requestService("syntax.is.select.placeholder")) {
                        groupInfo = this.kfEditor.requestService("syntax.get.group.content", cursorInfo.groupId);
                        this.kfEditor.requestService("render.select.group", groupInfo.content[cursorInfo.startOffset].id);
                        return;
                    }
                    if (cursorInfo.startOffset === cursorInfo.endOffset) {
                        // 更新光标位置
                        this.updateCursor();
                        // 请求背景着色
                        this.kfEditor.requestService("render.tint.current.cursor");
                    } else {
                        this.kfEditor.requestService("render.select.current.cursor");
                    }
                },
                updateCursor: function() {
                    var cursorInfo = this.kfEditor.requestService("syntax.get.record.cursor");
                    if (cursorInfo.startOffset !== cursorInfo.endOffset) {
                        this.hideCursor();
                        return;
                    }
                    var groupInfo = this.kfEditor.requestService("syntax.get.group.content", cursorInfo.groupId), isBefore = cursorInfo.endOffset === 0, index = isBefore ? 0 : cursorInfo.endOffset - 1, focusChild = groupInfo.content[index], paperContainerRect = getRect(this.paper.container.node), cursorOffset = 0, focusChildRect = getRect(focusChild), cursorTransform = this.cursorShape.getTransform(this.cursorShape), canvasZoom = this.kfEditor.requestService("render.get.canvas.zoom"), formulaZoom = this.paper.getZoom();
                    this.cursorShape.setHeight(focusChildRect.height / canvasZoom / formulaZoom);
                    // 计算光标偏移位置
                    cursorOffset = isBefore ? focusChildRect.left - 2 : focusChildRect.left + focusChildRect.width - 2;
                    cursorOffset -= paperContainerRect.left;
                    // 定位光标
                    cursorTransform.m.e = Math.floor(cursorOffset / canvasZoom / formulaZoom) + .5;
                    cursorTransform.m.f = (focusChildRect.top - paperContainerRect.top) / canvasZoom / formulaZoom;
                    this.cursorShape.setMatrix(cursorTransform);
                    this.cursorShape.setAttr("style", "display: block");
                },
                getCursorLocation: function() {
                    var rect = this.cursorShape.getRenderBox("paper");
                    return {
                        x: rect.x,
                        y: rect.y
                    };
                },
                getIndex: function(distance, groupInfo) {
                    var index = -1, children = groupInfo.content, boundingRect = null;
                    for (var i = children.length - 1, child = null; i >= 0; i--) {
                        index = i;
                        child = children[i];
                        boundingRect = getRect(child);
                        if (boundingRect.left < distance) {
                            if (boundingRect.left + boundingRect.width / 2 < distance) {
                                index += 1;
                            }
                            break;
                        }
                    }
                    return index;
                }
            });
            function getRect(node) {
                return node.getBoundingClientRect();
            }
        }
    };

//src/control/selection.js
    /*!
     * 光标选区组件
     */
    _p[10] = {
        value: function(require, exports, module) {
            var kity = _p.r(20), kfUtils = _p.r(4), // 鼠标移动临界距离
                MAX_DISTANCE = 10;
            return kity.createClass("SelectionComponent", {
                constructor: function(parentComponent, kfEditor) {
                    this.parentComponent = parentComponent;
                    this.kfEditor = kfEditor;
                    this.isDrag = false;
                    this.isMousedown = false;
                    this.startPoint = {
                        x: -1,
                        y: -1
                    };
                    // 起始位置是占位符
                    this.startGroupIsPlaceholder = false;
                    this.startGroup = {};
                    this.initServices();
                    this.initEvent();
                },
                initServices: function() {
                    this.kfEditor.registerService("control.select.all", this, {
                        selectAll: this.selectAll
                    });
                },
                initEvent: function() {
                    var eventServiceObject = this.kfEditor.request("ui.canvas.container.event"), _self = this;
                    /* 选区拖拽 start */
                    eventServiceObject.on("mousedown", function(e) {
                        e.preventDefault();
                        // 存在根占位符， 禁止拖动
                        if (_self.kfEditor.requestService("syntax.has.root.placeholder")) {
                            return false;
                        }
                        _self.isMousedown = true;
                        _self.updateStartPoint(e.clientX, e.clientY);
                        _self.updateStartGroup();
                    });
                    eventServiceObject.on("mouseup", function(e) {
                        e.preventDefault();
                        _self.stopUpdateSelection();
                    });
                    eventServiceObject.on("mousemove", function(e) {
                        e.preventDefault();
                        if (!_self.isDrag) {
                            if (_self.isMousedown) {
                                // 移动的距离达到临界条件
                                if (MAX_DISTANCE < _self.getDistance(e.clientX, e.clientY)) {
                                    _self.kfEditor.requestService("control.cursor.hide");
                                    _self.startUpdateSelection();
                                }
                            }
                        } else {
                            if (e.which !== 1) {
                                _self.stopUpdateSelection();
                                return;
                            }
                            _self.updateSelection(e.target, e.clientX, e.clientY);
                        }
                    });
                    /* 选区拖拽 end */
                    /* 双击选区 start */
                    eventServiceObject.on("dblclick", function(e) {
                        _self.updateSelectionByTarget(e.target);
                    });
                },
                getDistance: function(x, y) {
                    var distanceX = Math.abs(x - this.startPoint.x), distanceY = Math.abs(y - this.startPoint.y);
                    return Math.max(distanceX, distanceY);
                },
                updateStartPoint: function(x, y) {
                    this.startPoint.x = x;
                    this.startPoint.y = y;
                },
                updateStartGroup: function() {
                    var cursorInfo = this.kfEditor.requestService("syntax.get.record.cursor");
                    this.startGroupIsPlaceholder = this.kfEditor.requestService("syntax.is.select.placeholder");
                    this.startGroup = {
                        groupInfo: this.kfEditor.requestService("syntax.get.group.content", cursorInfo.groupId),
                        offset: cursorInfo.startOffset
                    };
                },
                startUpdateSelection: function() {
                    this.isDrag = true;
                    this.isMousedown = false;
                    this.clearSelection();
                },
                stopUpdateSelection: function() {
                    this.isDrag = false;
                    this.isMousedown = false;
                    this.kfEditor.requestService("control.update.input");
                },
                clearSelection: function() {
                    this.kfEditor.requestService("render.clear.select");
                },
                updateSelection: function(target, x, y) {
                    // 移动方向， true为右， false为左
                    var dir = x > this.startPoint.x, cursorInfo = {}, communityGroupInfo = null, inRightArea = false, startGroupInfo = this.startGroup, currentGroupNode = null, currentGroupInfo = this.getGroupInof(x, target);
                    if (currentGroupInfo.groupInfo.id === startGroupInfo.groupInfo.id) {
                        cursorInfo = {
                            groupId: currentGroupInfo.groupInfo.id,
                            startOffset: startGroupInfo.offset,
                            endOffset: currentGroupInfo.offset
                        };
                        // 如果起始点是占位符， 要根据移动方向修正偏移
                        if (this.startGroupIsPlaceholder) {
                            // 左移修正
                            if (!dir) {
                                cursorInfo.startOffset += 1;
                            } else if (cursorInfo.startOffset === cursorInfo.endOffset) {
                                cursorInfo.endOffset += 1;
                            }
                        }
                    } else {
                        // 存在包含关系
                        if (kfUtils.contains(startGroupInfo.groupInfo.groupObj, currentGroupInfo.groupInfo.groupObj)) {
                            cursorInfo = {
                                groupId: startGroupInfo.groupInfo.id,
                                startOffset: startGroupInfo.offset,
                                endOffset: this.getIndex(startGroupInfo.groupInfo.groupObj, target, x)
                            };
                        } else if (kfUtils.contains(currentGroupInfo.groupInfo.groupObj, startGroupInfo.groupInfo.groupObj)) {
                            cursorInfo = {
                                groupId: currentGroupInfo.groupInfo.id,
                                startOffset: this.kfEditor.requestService("position.get.index", currentGroupInfo.groupInfo.groupObj, startGroupInfo.groupInfo.groupObj),
                                endOffset: currentGroupInfo.offset
                            };
                            // 向左移动要修正开始偏移
                            if (!dir) {
                                cursorInfo.startOffset += 1;
                            }
                        } else {
                            // 获取公共容器
                            communityGroupInfo = this.getCommunityGroup(startGroupInfo.groupInfo, currentGroupInfo.groupInfo);
                            // 修正偏移相同时的情况， 比如在分数中选中时
                            if (communityGroupInfo.startOffset === communityGroupInfo.endOffset) {
                                communityGroupInfo.endOffset += 1;
                            } else {
                                // 当前光标移动所在的组元素节点
                                currentGroupNode = communityGroupInfo.group.content[communityGroupInfo.endOffset];
                                inRightArea = this.kfEditor.requestService("position.get.area", currentGroupNode, x);
                                // 当前移动到右区域， 则更新结束偏移
                                if (inRightArea) {
                                    communityGroupInfo.endOffset += 1;
                                }
                                // 左移动时， 修正起始偏移
                                if (!dir) {
                                    communityGroupInfo.startOffset += 1;
                                }
                            }
                            cursorInfo = {
                                groupId: communityGroupInfo.group.id,
                                startOffset: communityGroupInfo.startOffset,
                                endOffset: communityGroupInfo.endOffset
                            };
                        }
                    }
                    // 更新光标信息
                    this.kfEditor.requestService("syntax.update.record.cursor", cursorInfo.groupId, cursorInfo.startOffset, cursorInfo.endOffset);
                    // 仅重新选中就可以，不用更新输入框内容
                    this.kfEditor.requestService("control.reselect");
                },
                updateSelectionByTarget: function(target) {
                    var parentGroupInfo = this.kfEditor.requestService("position.get.parent.group", target), containerInfo = null, cursorInfo = {};
                    if (parentGroupInfo === null) {
                        return;
                    }
                    // 如果是根节点， 则直接选中其内容
                    if (this.kfEditor.requestService("syntax.is.root.node", parentGroupInfo.id)) {
                        this.selectAll();
                        return;
                    } else {
                        // 当前组可以是容器， 则选中该容器的内容
                        if (!this.kfEditor.requestService("syntax.is.virtual.node", parentGroupInfo.id)) {
                            cursorInfo = {
                                groupId: parentGroupInfo.id,
                                startOffset: 0,
                                endOffset: parentGroupInfo.content.length
                            };
                        } else {
                            // 获取包含父组的容器
                            containerInfo = this.kfEditor.requestService("position.get.group.info", parentGroupInfo.groupObj);
                            cursorInfo = {
                                groupId: containerInfo.group.id,
                                startOffset: containerInfo.index,
                                endOffset: containerInfo.index + 1
                            };
                        }
                    }
                    this.kfEditor.requestService("syntax.update.record.cursor", cursorInfo);
                    this.kfEditor.requestService("control.reselect");
                    this.kfEditor.requestService("control.update.input");
                },
                selectAll: function() {
                    var rootGroupInfo = this.kfEditor.requestService("syntax.get.root.group.info");
                    var cursorInfo = {
                        groupId: rootGroupInfo.id,
                        startOffset: 0,
                        endOffset: rootGroupInfo.content.length
                    };
                    this.kfEditor.requestService("syntax.update.record.cursor", cursorInfo);
                    this.kfEditor.requestService("control.reselect");
                    this.kfEditor.requestService("control.update.input");
                },
                getGroupInof: function(offset, target) {
                    var groupInfo = this.kfEditor.requestService("position.get.group", target);
                    if (groupInfo === null) {
                        groupInfo = this.kfEditor.requestService("syntax.get.root.group.info");
                    }
                    var index = this.kfEditor.requestService("position.get.location.info", offset, groupInfo);
                    return {
                        groupInfo: groupInfo,
                        offset: index
                    };
                },
                getIndex: function(groupNode, targetNode, offset) {
                    var index = this.kfEditor.requestService("position.get.index", groupNode, targetNode), groupInfo = this.kfEditor.requestService("syntax.get.group.content", groupNode.id), targetWrapNode = groupInfo.content[index], targetRect = kfUtils.getRect(targetWrapNode);
                    if (targetRect.left + targetRect.width / 2 < offset) {
                        index += 1;
                    }
                    return index;
                },
                /**
                 * 根据给定的两个组信息， 获取其所在的公共容器及其各自的偏移
                 * @param startGroupInfo 组信息
                 * @param endGroupInfo 另一个组信息
                 */
                getCommunityGroup: function(startGroupInfo, endGroupInfo) {
                    var bigBoundingGroup = null, targetGroup = startGroupInfo.groupObj, groupNode = null;
                    while (bigBoundingGroup = this.kfEditor.requestService("position.get.group.info", targetGroup)) {
                        targetGroup = bigBoundingGroup.group.groupObj;
                        if (kfUtils.contains(bigBoundingGroup.group.groupObj, endGroupInfo.groupObj)) {
                            break;
                        }
                    }
                    groupNode = bigBoundingGroup.group.groupObj;
                    return {
                        group: bigBoundingGroup.group,
                        startOffset: bigBoundingGroup.index,
                        endOffset: this.kfEditor.requestService("position.get.index", groupNode, endGroupInfo.groupObj)
                    };
                }
            });
        }
    };

//src/def/group-type.js
    /*!
     * 组类型
     */
    _p[11] = {
        value: function() {
            return {
                GROUP: "kf-editor-group",
                VIRTUAL: "kf-editor-virtual-group"
            };
        }
    };

//src/editor/editor.js
    /*!
     * 编辑器主体结构
     */
    _p[12] = {
        value: function(require) {
            var kity = _p.r(20), Utils = _p.r(4), defaultOpt = {
                formula: {
                    fontsize: 50,
                    autoresize: false
                },
                ui: {
                    zoom: true,
                    maxzoom: 2,
                    minzoom: 1
                }
            };
            // 同步组件列表
            var COMPONENTS = {}, // 异步组件列表
                ResourceManager = _p.r(19).ResourceManager;
            var KFEditor = kity.createClass("KFEditor", {
                constructor: function(container, opt) {
                    this.options = Utils.extend(true, {}, defaultOpt, opt);
                    this.FormulaClass = null;
                    kf.toolbarPath=this.options.toolbarPath;
                    this.options.button= {
                        icon: {
                            src: this.options.toolbarPath+"btn.png"
                        }
                    }
                    // 就绪状态
                    this._readyState = false;
                    this._callbacks = [];
                    this.container = container;
                    this.services = {};
                    this.commands = {};
                    this.initResource();
                },
                isReady: function() {
                    return !!this._readyState;
                },
                triggerReady: function() {
                    var cb = null, _self = this;
                    while (cb = this._callbacks.shift()) {
                        cb.call(_self, _self);
                    }
                },
                ready: function(cb) {
                    if (this._readyState) {
                        cb.call(this, this);
                    } else {
                        this._callbacks.push(cb);
                    }
                },
                getContainer: function() {
                    return this.container;
                },
                getDocument: function() {
                    return this.container.ownerDocument;
                },
                getFormulaClass: function() {
                    return this.FormulaClass;
                },
                getOptions: function() {
                    return this.options;
                },
                initResource: function() {
                    var _self = this;
                    ResourceManager.ready(function(Formula) {
                        _self.FormulaClass = Formula;
                        _self.initComponents();
                        _self._readyState = true;
                        _self.triggerReady();
                    }, this.options.resource);
                },
                /**
                 * 初始化同步组件
                 */
                initComponents: function() {
                    var _self = this;
                    Utils.each(COMPONENTS, function(Component, name) {
                        new Component(_self, _self.options[name]);
                    });
                },
                requestService: function(serviceName, args) {
                    var serviceObject = getService.call(this, serviceName);
                    return serviceObject.service[serviceObject.key].apply(serviceObject.provider, [].slice.call(arguments, 1));
                },
                request: function(serviceName) {
                    var serviceObject = getService.call(this, serviceName);
                    return serviceObject.service;
                },
                registerService: function(serviceName, provider, serviceObject) {
                    var key = null;
                    for (key in serviceObject) {
                        if (serviceObject[key] && serviceObject.hasOwnProperty(key)) {
                            serviceObject[key] = Utils.proxy(serviceObject[key], provider);
                        }
                    }
                    this.services[serviceName] = {
                        provider: provider,
                        key: key,
                        service: serviceObject
                    };
                },
                registerCommand: function(commandName, executor, execFn) {
                    this.commands[commandName] = {
                        executor: executor,
                        execFn: execFn
                    };
                },
                execCommand: function(commandName, args) {
                    var commandObject = this.commands[commandName];
                    if (!commandObject) {
                        throw new Error("KFEditor: not found command, " + commandName);
                    }
                    return commandObject.execFn.apply(commandObject.executor, [].slice.call(arguments, 1));
                },
                replaceSpecialCharacter: function(source) {
                    var $source = source.replace(/\\cong/g,'=^\\sim')
                    .replace(/\\varnothing/g,'\\oslash')
                    .replace(/\\gets/g,'\\leftarrow')
                    .replace(/\\because/g,'\\cdot_\\cdot\\cdot')
                    .replace(/\\blacksquare/g,'\\rule{20}{20}');
                    return $source;
                }
            });
            function getService(serviceName) {
                var serviceObject = this.services[serviceName];
                if (!serviceObject) {
                    throw new Error("KFEditor: not found service, " + serviceName);
                }
                return serviceObject;
            }
            Utils.extend(KFEditor, {
                registerComponents: function(name, component) {
                    COMPONENTS[name] = component;
                }
            });
            return KFEditor;
        }
    };

//src/editor/factory.js
    /**
     * 编辑器工厂方法
     * 用于创建编辑器
     */
    _p[13] = {
        value: function(require) {
            var kity = _p.r(20), KFEditor = _p.r(12);
            /* ------------------------------- 编辑器装饰对象 */
            function EditorWrapper(container, options) {
                var _self = this;
                this._callbacks = [];
                this.editor = new KFEditor(container, options);
                this.editor.ready(function() {
                    _self._trigger();
                });
            }
            EditorWrapper.prototype._trigger = function() {
                var editor = this.editor;
                kity.Utils.each(this._callbacks, function(cb) {
                    cb.call(editor, editor);
                });
            };
            EditorWrapper.prototype.ready = function(cb) {
                if (this.editor.isReady()) {
                    cb.call(this.editor, this.editor);
                } else {
                    this._callbacks.push(cb);
                }
            };
            return {
                create: function(container, options) {
                    return new EditorWrapper(container, options);
                }
            };
        }
    };

//src/jquery.js
    /**
     * Created by hn on 14-3-31.
     */
    _p[14] = {
        value: function() {
            return window.jQuery;
        }
    };

//src/kf-ext/def.js
    /**
     * Created by hn on 14-3-18.
     */
    _p[15] = {
        value: function() {
            return {
                selectColor: "rgba(42, 106, 189, 0.2)",
                allSelectColor: "rgba(42, 106, 189, 0.6)"
            };
        }
    };

//src/kf-ext/expression/placeholder.js
    /**
     * 占位符表达式， 扩展KF自有的Empty表达式
     */
    _p[16] = {
        value: function(require, exports, module) {
            var kity = _p.r(20), kf = _p.r(19), PlaceholderOperator = _p.r(18);
            return kity.createClass("PlaceholderExpression", {
                base: kf.CompoundExpression,
                constructor: function() {
                    this.callBase();
                    this.setFlag("Placeholder");
                    this.label = null;
                    this.box.setAttr("data-type", null);
                    this.setOperator(new PlaceholderOperator());
                },
                setLabel: function(label) {
                    this.label = label;
                },
                getLabel: function() {
                    return this.label;
                },
                // 重载占位符的setAttr， 以处理根占位符节点
                setAttr: function(key, val) {
                    if (key === "label") {
                        this.setLabel(val);
                    } else {
                        if (key.label) {
                            this.setLabel(key.label);
                            // 删除label
                            delete key.label;
                        }
                        // 继续设置其他属性
                        this.callBase(key, val);
                    }
                },
                select: function() {
                    this.getOperator().select();
                },
                selectAll: function() {
                    this.getOperator().selectAll();
                },
                unselect: function() {
                    this.getOperator().unselect();
                }
            });
        }
    };

//src/kf-ext/extension.js
    /**
     * 公式扩展接口
     */
    _p[17] = {
        value: function(require) {
            var kf = _p.r(19), SELECT_COLOR = _p.r(15).selectColor, ALL_SELECT_COLOR = _p.r(15).allSelectColor;
            function ext(parser) {
                kf.PlaceholderExpression = _p.r(16);
                kf.Expression.prototype.select = function() {
                    this.box.fill(SELECT_COLOR);
                };
                kf.Expression.prototype.selectAll = function() {
                    this.box.fill(ALL_SELECT_COLOR);
                };
                kf.Expression.prototype.unselect = function() {
                    this.box.fill("transparent");
                };
                // 扩展解析和逆解析
                parser.getKFParser().expand({
                    parse: {
                        placeholder: {
                            name: "placeholder",
                            handler: function(info) {
                                delete info.handler;
                                info.operand = [];
                                return info;
                            },
                            sign: false
                        }
                    },
                    reverse: {
                        placeholder: function() {
                            return "\\placeholder ";
                        }
                    }
                });
            }
            return {
                ext: ext
            };
        }
    };

//src/kf-ext/operator/placeholder.js
    /**
     * 占位符操作符
     */
    _p[18] = {
        value: function(require, exports, modules) {
            var kity = _p.r(20), FILL_COLOR = _p.r(29).rootPlaceholder.color, SELECT_COLOR = _p.r(15).selectColor, ALL_SELECT_COLOR = _p.r(15).allSelectColor;
            return kity.createClass("PlaceholderOperator", {
                base: _p.r(19).Operator,
                constructor: function() {
                    this.opShape = null;
                    this.callBase("Placeholder");
                },
                applyOperand: function() {
                    this.opShape = generateOpShape(this, this.parentExpression.getLabel());
                    this.parentExpression.expand(20, 20);
                    this.parentExpression.translateElement(10, 10);
                },
                select: function() {
                    this.opShape.fill(SELECT_COLOR);
                },
                selectAll: function() {
                    this.opShape.fill(ALL_SELECT_COLOR);
                },
                unselect: function() {
                    this.opShape.fill("transparent");
                }
            });
            function generateOpShape(operator, label) {
                if (label !== null) {
                    return createRootPlaceholder(operator, label);
                } else {
                    return createCommonShape(operator);
                }
            }
            // 创建通用图形
            function createCommonShape(operator) {
                var w = 35, h = 50, shape = null;
                shape = new kity.Rect(w, h, 0, 0).stroke("black").fill("transparent");
                shape.setAttr("stroke-dasharray", "5, 5");
                operator.addOperatorShape(shape);
                return shape;
            }
            // 创建根占位符图形
            function createRootPlaceholder(operator, label) {
                var textShape = new kity.Text(label).fill(FILL_COLOR), shapeGroup = new kity.Group(), padding = 20, radius = 7, borderBoxShape = new kity.Rect(0, 0, 0, 0, radius).stroke(FILL_COLOR).fill("transparent"), textBox = null;
                textShape.setFontSize(40);
                shapeGroup.addShape(borderBoxShape);
                shapeGroup.addShape(textShape);
                operator.addOperatorShape(shapeGroup);
                textBox = textShape.getFixRenderBox();
                // 宽度要加上padding
                borderBoxShape.stroke(FILL_COLOR).fill("transparent");
                borderBoxShape.setSize(textBox.width + padding * 2, textBox.height + padding * 2);
                borderBoxShape.setRadius(radius);
                borderBoxShape.setAttr("stroke-dasharray", "5, 5");
                textShape.setAttr({
                    dx: 0 - textBox.x,
                    dy: 0 - textBox.y
                });
                textShape.translate(padding, padding);
                // 对于根占位符， 返回的不是组， 而是组容器内部的虚线框。 以方便选中变色
                return borderBoxShape;
            }
        }
    };

//src/kf.js
    /**
     * Created by hn on 14-3-12.
     */
    _p[19] = {
        value: function() {
            return window.kf;
        }
    };

//src/kity.js
    /**
     * 数学公式Latex语法解析器
     */
    _p[20] = {
        value: function() {
            return window.kity;
        }
    };

//src/parse/parser.js
    /**
     * 数学公式解析器
     */
    _p[21] = {
        value: function(require) {
            var KFParser = _p.r(19).Parser, kity = _p.r(20), CURSOR_CHAR = _p.r(29).cursorCharacter, VGROUP_LIST = _p.r(22), ROOT_P_TEXT = _p.r(29).rootPlaceholder.content, COMBINATION_NAME = "combination", PID_PREFIX = "_kf_editor_", GROUP_TYPE = _p.r(11), PID = 0;
            var Parser = kity.createClass("Parser", {
                constructor: function(kfEditor) {
                    this.kfEditor = kfEditor;
                    this.callBase();
                    // kityformula 解析器
                    this.kfParser = KFParser.use("latex");
                    this.initKFormulExtension();
                    this.pid = generateId();
                    this.groupRecord = 0;
                    this.tree = null;
                    this.isResetId = true;
                    this.initServices();
                },
                parse: function(str, isResetId) {
                    var parsedResult = null;
                    this.isResetId = !!isResetId;
                    if (this.isResetId) {
                        this.resetGroupId();
                    }
                    parsedResult = this.kfParser.parse(str);
                    // 对解析出来的结果树做适当的处理，使得编辑器能够更容易地识别当前表达式的语义
                    supplementTree(this, parsedResult.tree);
                    return parsedResult;
                },
                // 序列化， parse的逆过程
                serialization: function(tree) {
                    return this.kfParser.serialization(tree);
                },
                initServices: function() {
                    this.kfEditor.registerService("parser.parse", this, {
                        parse: this.parse
                    });
                    this.kfEditor.registerService("parser.latex.serialization", this, {
                        serialization: this.serialization
                    });
                },
                getKFParser: function() {
                    return this.kfParser;
                },
                // 初始化KF扩展
                initKFormulExtension: function() {
                    _p.r(17).ext(this);
                },
                resetGroupId: function() {
                    this.groupRecord = 0;
                },
                getGroupId: function() {
                    return this.pid + "_" + ++this.groupRecord;
                }
            });
            // 把解析树丰富成公式编辑器的语义树, 该语义化的树同时也是合法的解析树
            function supplementTree(parser, tree, parentTree) {
                var currentOperand = null, // 只有根节点才没有parentTree
                    isRoot = !parentTree;
                tree.attr = tree.attr || {};
                tree.attr.id = parser.getGroupId();
                if (isRoot) {
                    processRootGroup(parser, tree);
                } else if (parentTree.attr["data-root"] && tree.name === "placeholder" && onlyPlaceholder(parentTree.operand)) {
                    tree.attr.label = ROOT_P_TEXT;
                }
                for (var i = 0, len = tree.operand.length; i < len; i++) {
                    currentOperand = tree.operand[i];
                    if (isVirtualGroup(tree)) {
                        // 虚拟组处理
                        processVirtualGroup(parser, i, tree, currentOperand);
                    } else {
                        processGroup(parser, i, tree, currentOperand);
                    }
                }
                return tree;
            }
            function generateId() {
                return PID_PREFIX + ++PID;
            }
            function processRootGroup(parser, tree) {
                // 如果isResetId为false， 表示当前生成的是子树
                // 则不做data-root标记， 同时更改该包裹的类型为GROUP_TYPE.VIRTUAL
                if (!parser.isResetId) {
                    tree.attr["data-type"] = GROUP_TYPE.VIRTUAL;
                } else {
                    tree.attr["data-root"] = "true";
                }
            }
            /**
             * 虚拟组处理
             * @param parser 解析器实例
             * @param index 当前处理的子树所在其父节点的索引位置
             * @param tree 需要处理的树父树
             * @param subtree 当前需要处理的树
             */
            function processVirtualGroup(parser, index, tree, subtree) {
                // 括号组的前两个元素不用处理
                if (tree.name === "brackets" && index < 2) {
                    return;
                } else if (tree.name === "function" && index === 0) {
                    return;
                }
                tree.attr["data-type"] = GROUP_TYPE.VIRTUAL;
                if (!subtree) {
                    tree.operand[index] = subtree;
                } else if (typeof subtree === "string") {
                    tree.operand[index] = createGroup(parser);
                    tree.operand[index].operand[0] = subtree;
                } else if (isPlaceholder(subtree)) {
                    tree.operand[index] = createGroup(parser);
                    tree.operand[index].operand[0] = supplementTree(parser, subtree, tree.operand[index]);
                } else {
                    tree.operand[index] = supplementTree(parser, subtree, tree);
                }
            }
            function processGroup(parser, index, tree, subtree) {
                tree.attr["data-type"] = GROUP_TYPE.GROUP;
                if (!subtree || typeof subtree === "string") {
                    tree.operand[index] = subtree;
                } else if (subtree.name === "text") {
                    tree.operand[index] = subtree;
                } else {
                    tree.operand[index] = supplementTree(parser, subtree, tree);
                }
            }
            /**
             * 判断给定的操作数列表内是否仅有一个占位符存在, 该判断仅支持对根内部的表达式做判断
             * @param operands 操作数列表
             * @returns {boolean}
             */
            function onlyPlaceholder(operands) {
                var result = 1;
                if (operands.length > 3) {
                    return false;
                }
                for (var i = 0, len = operands.length; i < len; i++) {
                    if (operands[i] === CURSOR_CHAR) {
                        continue;
                    }
                    if (operands[i] && operands[i].name === "placeholder") {
                        result--;
                    }
                }
                return !result;
            }
            // 判断给定的树是否是一个虚拟组
            function isVirtualGroup(tree) {
                return !!VGROUP_LIST[tree.name];
            }
            // 判断给定的树是否是一个占位符
            function isPlaceholder(tree) {
                return tree.name === "placeholder";
            }
            // 创建一个新组， 组的内容是空
            function createGroup(parser) {
                return {
                    name: COMBINATION_NAME,
                    attr: {
                        "data-type": GROUP_TYPE.GROUP,
                        id: parser.getGroupId()
                    },
                    operand: []
                };
            }
            return Parser;
        }
    };

//src/parse/vgroup-def.js
    /*!
     * 虚拟组列表
     */
    _p[22] = {
        value: function() {
            return {
                radical: true,
                fraction: true,
                summation: true,
                integration: true,
                placeholder: true,
                script: true,
                superscript: true,
                subscript: true,
                brackets: true,
                "function": true
            };
        }
    };

//src/position/position.js
    /*!
     * 定位模块
     */
    _p[23] = {
        value: function(require) {
            var kity = _p.r(20), kfUtils = _p.r(4), PositionComponenet = kity.createClass("PositionComponenet", {
                constructor: function(kfEditor) {
                    this.kfEditor = kfEditor;
                    this.initServices();
                },
                initServices: function() {
                    this.kfEditor.registerService("position.get.group", this, {
                        getGroupByTarget: this.getGroupByTarget
                    });
                    this.kfEditor.registerService("position.get.index", this, {
                        getIndexByTargetInGroup: this.getIndexByTargetInGroup
                    });
                    this.kfEditor.registerService("position.get.location.info", this, {
                        getLocationInfo: this.getLocationInfo
                    });
                    this.kfEditor.registerService("position.get.parent.group", this, {
                        getParentGroupByTarget: this.getParentGroupByTarget
                    });
                    this.kfEditor.registerService("position.get.wrap", this, {
                        getWrap: this.getWrap
                    });
                    this.kfEditor.registerService("position.get.area", this, {
                        getAreaByCursorInGroup: this.getAreaByCursorInGroup
                    });
                    this.kfEditor.registerService("position.get.group.info", this, {
                        getGroupInfoByNode: this.getGroupInfoByNode
                    });
                    this.kfEditor.registerService("position.get.parent.info", this, {
                        getParentInfoByNode: this.getParentInfoByNode
                    });
                },
                getGroupByTarget: function(target) {
                    var groupDom = getGroup(target, false, false);
                    if (groupDom) {
                        return this.kfEditor.requestService("syntax.get.group.content", groupDom.id);
                    }
                    return null;
                },
                /**
                 * 根据给定的组节点和目标节点， 获取目标节点在组节点内部的索引
                 * @param groupNode 组节点
                 * @param targetNode 目标节点
                 */
                getIndexByTargetInGroup: function(groupNode, targetNode) {
                    var groupInfo = this.kfEditor.requestService("syntax.get.group.content", groupNode.id), index = -1;
                    kity.Utils.each(groupInfo.content, function(child, i) {
                        index = i;
                        if (kfUtils.contains(child, targetNode)) {
                            return false;
                        }
                    });
                    return index;
                },
                /**
                 * 根据给定的组节点和给定的偏移值，获取当前偏移值在组中的区域值。
                 * 该区域值的取值为true时， 表示在右区域， 反之则在左区域
                 * @param groupNode 组节点
                 * @param offset 偏移值
                 */
                getAreaByCursorInGroup: function(groupNode, offset) {
                    var groupRect = kfUtils.getRect(groupNode);
                    return groupRect.left + groupRect.width / 2 < offset;
                },
                getLocationInfo: function(distance, groupInfo) {
                    var index = -1, children = groupInfo.content, boundingRect = null;
                    for (var i = children.length - 1, child = null; i >= 0; i--) {
                        index = i;
                        child = children[i];
                        boundingRect = kfUtils.getRect(child);
                        if (boundingRect.left < distance) {
                            if (boundingRect.left + boundingRect.width / 2 < distance) {
                                index += 1;
                            }
                            break;
                        }
                    }
                    return index;
                },
                getParentGroupByTarget: function(target) {
                    var groupDom = getGroup(target, true, false);
                    if (groupDom) {
                        return this.kfEditor.requestService("syntax.get.group.content", groupDom.id);
                    }
                    return null;
                },
                getWrap: function(node) {
                    return getGroup(node, true, true);
                },
                /**
                 * 给定一个节点， 获取其节点所属的组及其在该组内的偏移
                 * @param target 目标节点
                 */
                getGroupInfoByNode: function(target) {
                    var result = {}, containerNode = getGroup(target, false, false), containerInfo = null;
                    if (!containerNode) {
                        return null;
                    }
                    containerInfo = this.kfEditor.requestService("syntax.get.group.content", containerNode.id);
                    for (var i = 0, len = containerInfo.content.length; i < len; i++) {
                        result.index = i;
                        if (kfUtils.contains(containerInfo.content[i], target)) {
                            break;
                        }
                    }
                    result.group = containerInfo;
                    return result;
                },
                /**
                 * 给定一个节点， 获取其节点所属的直接包含组及其在该直接包含组内的偏移
                 * @param target 目标节点
                 */
                getParentInfoByNode: function(target) {
                    var group = getGroup(target, true, false);
                    group = this.kfEditor.requestService("syntax.get.group.content", group.id);
                    return {
                        group: group,
                        index: group.content.indexOf(target)
                    };
                }
            });
            /**
             * 获取给定节点元素所属的组
             * @param node 当前点击的节点
             * @param isAllowVirtual 是否允许选择虚拟组
             * @param isAllowWrap 是否允许选择目标节点的最小包裹单位
             * @returns {*}
             */
            function getGroup(node, isAllowVirtual, isAllowWrap) {
                var tagName = null;
                if (!node.ownerSVGElement) {
                    return null;
                }
                node = node.parentNode;
                tagName = node.tagName.toLowerCase();
                if (node && tagName !== "body" && tagName !== "svg") {
                    if (node.getAttribute("data-type") === "kf-editor-group") {
                        return node;
                    }
                    if (isAllowVirtual && node.getAttribute("data-type") === "kf-editor-virtual-group") {
                        return node;
                    }
                    if (isAllowWrap && node.getAttribute("data-flag") !== null) {
                        return node;
                    }
                    return getGroup(node, isAllowVirtual, isAllowWrap);
                } else {
                    return null;
                }
            }
            return PositionComponenet;
        }
    };

//src/print/printer.js
    /*!
     * 打印服务
     */
    _p[24] = {
        value: function(require) {
            var kity = _p.r(20);
            return kity.createClass("Printer", {
                constructor: function(kfEditor) {
                    this.kfEditor = kfEditor;
                    this.initServices();
                    this.initCommands();
                },
                initServices: function() {
                    this.kfEditor.registerService("print.image", this, {
                        printImage: this.printImage
                    });
                },
                initCommands: function() {
                    this.kfEditor.registerCommand("get.image.data", this, this.getImageData);
                },
                printImage: function(type) {
                    var formula = this.kfEditor.requestService("render.get.paper");
                    this._formatCanvas();
                    formula.toPNG(function(dataUrl) {
                        document.body.innerHTML = '<img style="background: red;" src="' + dataUrl + '">';
                    });
                    this._restoreCanvas();
                },
                getImageData: function(cb) {
                    var canvas = this.kfEditor.requestService("render.get.canvas"), formula = this.kfEditor.requestService("render.get.paper");
                    this._formatCanvas();
                    formula.toPNG(function(dataUrl) {
                        cb({
                            width: canvas.width,
                            height: canvas.height,
                            img: dataUrl
                        });
                    });
                    this._restoreCanvas();
                },
                _formatCanvas: function() {
                    var canvas = this.kfEditor.requestService("render.get.canvas"), rect = canvas.container.getRenderBox();
                    canvas.node.setAttribute("width", rect.width);
                    canvas.node.setAttribute("height", rect.height);
                    this.kfEditor.requestService("render.clear.canvas.transform");
                    this.kfEditor.requestService("control.cursor.hide");
                    this.kfEditor.requestService("render.clear.select");
                },
                _restoreCanvas: function() {
                    var canvas = this.kfEditor.requestService("render.get.canvas");
                    canvas.node.setAttribute("width", "100%");
                    canvas.node.setAttribute("height", "100%");
                    this.kfEditor.requestService("render.revert.canvas.transform");
                    this.kfEditor.requestService("control.cursor.relocation");
                    this.kfEditor.requestService("render.reselect");
                }
            });
        }
    };

//src/render/render.js
    /**
     * Created by hn on 14-3-17.
     */
    _p[25] = {
        value: function(require) {
            var kity = _p.r(20), Assembly = _p.r(19).Assembly, DEFAULT_OPTIONS = {
                autoresize: false,
                fontsize: 50,
                padding: [ 20, 50 ]
            }, RenderComponenet = kity.createClass("RenderComponent", {
                // 异步组件
                base: _p.r(1),
                constructor: function(kfEditor, options) {
                    this.callBase();
                    this.options = kity.Utils.extend({}, DEFAULT_OPTIONS, options);
                    this.kfEditor = kfEditor;
                    this.assembly = null;
                    this.formula = null;
                    // 是否禁用重定位
                    this.relDisabled = false;
                    this.canvasZoom = 1;
                    this.record = {
                        select: {},
                        cursor: {},
                        // 画布信息
                        canvas: {}
                    };
                    this.initCanvas();
                    this.initServices();
                    this.initCommands();
                },
                initCanvas: function() {
                    var canvasContainer = this.kfEditor.requestService("ui.get.canvas.container"), Formula = this.kfEditor.getFormulaClass();
                    this.assembly = new Assembly(new Formula(canvasContainer, this.options));
                    this.formula = this.assembly.formula;
                    this.setCanvasToCenter();
                },
                setCanvasOffset: function(offsetX, offsetY) {
                    var viewBox = this.formula.getViewBox();
                    offsetY = offsetY !== undefined ? offsetY : -viewBox.height / 2;
                    this.formula.setViewBox(offsetX, offsetY, viewBox.width, viewBox.height);
                },
                setCanvasToCenter: function() {
                    var viewBox = this.formula.getViewBox();
                    this.formula.setViewBox(-viewBox.width / 2, -viewBox.height / 2, viewBox.width, viewBox.height);
                },
                initServices: function() {
                    this.kfEditor.registerService("render.get.canvas", this, {
                        getCanvas: this.getCanvas
                    });
                    this.kfEditor.registerService("render.get.content.size", this, {
                        getContentSize: this.getContentSize
                    });
                    this.kfEditor.registerService("render.clear.canvas.transform", this, {
                        clearCanvasOffset: this.clearCanvasTransform
                    });
                    this.kfEditor.registerService("render.set.canvas.offset", this, {
                        setCanvasOffset: this.setCanvasOffset
                    });
                    this.kfEditor.registerService("render.set.canvas.to.center", this, {
                        setCanvasToCenter: this.setCanvasToCenter
                    });
                    this.kfEditor.registerService("render.revert.canvas.transform", this, {
                        revertCanvasTransform: this.revertCanvasTransform
                    });
                    this.kfEditor.registerService("render.relocation", this, {
                        relocation: this.relocation
                    });
                    this.kfEditor.registerService("render.disable.relocation", this, {
                        disableRelocation: this.disableRelocation
                    });
                    this.kfEditor.registerService("render.enable.relocation", this, {
                        enableRelocation: this.enableRelocation
                    });
                    this.kfEditor.registerService("render.select.group.content", this, {
                        selectGroupContent: this.selectGroupContent
                    });
                    this.kfEditor.registerService("render.select.group", this, {
                        selectGroup: this.selectGroup
                    });
                    this.kfEditor.registerService("render.select.group.all", this, {
                        selectAllGroup: this.selectAllGroup
                    });
                    this.kfEditor.registerService("render.tint.current.cursor", this, {
                        tintCurrentGroup: this.tintCurrentGroup
                    });
                    this.kfEditor.registerService("render.select.current.cursor", this, {
                        selectCurrentCursor: this.selectCurrentCursor
                    });
                    this.kfEditor.registerService("render.reselect", this, {
                        reselect: this.reselect
                    });
                    this.kfEditor.registerService("render.clear.select", this, {
                        clearSelect: this.clearSelect
                    });
                    this.kfEditor.registerService("render.set.canvas.zoom", this, {
                        setCanvasZoom: this.setCanvasZoom
                    });
                    this.kfEditor.registerService("render.get.canvas.zoom", this, {
                        getCanvasZoom: this.getCanvasZoom
                    });
                    this.kfEditor.registerService("render.get.paper.offset", this, {
                        getPaperOffset: this.getPaperOffset
                    });
                    this.kfEditor.registerService("render.draw", this, {
                        render: this.render
                    });
                    this.kfEditor.registerService("render.insert.string", this, {
                        insertString: this.insertString
                    });
                    this.kfEditor.registerService("render.insert.group", this, {
                        insertGroup: this.insertGroup
                    });
                    this.kfEditor.registerService("render.get.paper", this, {
                        getPaper: this.getPaper
                    });
                },
                initCommands: function() {
                    this.kfEditor.registerCommand("render", this, function(str) {
                        this.render(str);
                        this.kfEditor.requestService("ui.update.canvas.view");
                    });
                    this.kfEditor.registerCommand("getPaper", this, this.getPaper);
                },
                relocation: function() {
                    if (!this.relDisabled) {
                        this.relocationToCenter();
                    } else {
                        this.relocationToLeft();
                    }
                },
                relocationToCenter: function() {
                    var formulaSpace = this.formula.container.getRenderBox();
                    this.formula.container.setTranslate(-formulaSpace.width / 2, -formulaSpace.height / 2);
                    this.setCanvasToCenter();
                },
                relocationToLeft: function() {
                    var formulaSpace = this.formula.container.getRenderBox();
                    this.formula.container.setTranslate(0, -formulaSpace.height / 2);
                    this.setCanvasOffset(0);
                },
                selectGroup: function(groupId) {
                    var groupObject = this.kfEditor.requestService("syntax.get.group.object", groupId);
                    this.clearSelect();
                    if (groupObject.node.getAttribute("data-root")) {
                        // 根节点不着色
                        return;
                    }
                    this.record.select.lastSelect = groupObject;
                    groupObject.select();
                },
                selectGroupContent: function(group) {
                    // 处理占位符
                    if (group.groupObj.getAttribute("data-placeholder") !== null) {
                        group = {
                            id: group.content[0].id
                        };
                    }
                    var groupObject = this.kfEditor.requestService("syntax.get.group.object", group.id);
                    this.clearSelect();
                    this.record.select.lastSelect = groupObject;
                    if (groupObject.node.getAttribute("data-root")) {
                        // 根节点不着色
                        return;
                    }
                    groupObject.select();
                },
                selectAllGroup: function(group) {
                    // 处理占位符
                    if (group.groupObj.getAttribute("data-placeholder") !== null) {
                        group = {
                            id: group.content[0].id
                        };
                    }
                    var groupObject = this.kfEditor.requestService("syntax.get.group.object", group.id);
                    this.clearSelect();
                    this.record.select.lastSelect = groupObject;
                    groupObject.selectAll();
                },
                /**
                 * 根据当前光标信息绘制选区
                 */
                selectCurrentCursor: function() {
                    var cursorInfo = this.kfEditor.requestService("syntax.get.record.cursor"), group = this.kfEditor.requestService("syntax.get.group.object", cursorInfo.groupId), box = null, offset = -1, width = 0, startIndex = Math.min(cursorInfo.startOffset, cursorInfo.endOffset), endIndex = Math.max(cursorInfo.startOffset, cursorInfo.endOffset);
                    this.clearSelect();
                    // 更新记录
                    this.record.select.lastSelect = group;
                    for (var i = startIndex, len = endIndex; i < len; i++) {
                        box = group.getOperand(i).getRenderBox(group);
                        if (offset == -1) {
                            offset = box.x;
                        }
                        width += box.width;
                    }
                    group.setBoxWidth(width);
                    group.selectAll();
                    group.getBox().setTranslate(offset, 0);
                },
                /**
                 * 根据当前的光标信息，对当前光标所在的容器进行着色
                 */
                tintCurrentGroup: function() {
                    var groupId = this.kfEditor.requestService("syntax.get.record.cursor").groupId, groupObject = this.kfEditor.requestService("syntax.get.group.object", groupId), isPlaceholder = this.kfEditor.requestService("syntax.is.placeholder.node", groupId);
                    this.clearSelect();
                    if (groupObject.node.getAttribute("data-root")) {
                        // 根节点不着色
                        return;
                    }
                    // 占位符着色
                    if (isPlaceholder) {
                        // 替换占位符包裹组为占位符本身
                        groupObject = this.kfEditor.requestService("syntax.get.group.object", groupObject.operands[0].node.id);
                    }
                    this.record.select.lastSelect = groupObject;
                    groupObject.select();
                },
                reselect: function() {
                    var cursorInfo = this.kfEditor.requestService("syntax.get.record.cursor"), groupObject = null;
                    groupObject = this.kfEditor.requestService("syntax.get.group.object", cursorInfo.groupId);
                    this.clearSelect();
                    this.record.select.lastSelect = groupObject;
                    if (groupObject.node.getAttribute("data-root")) {
                        // 根节点不着色
                        return;
                    }
                    groupObject.select();
                },
                clearSelect: function() {
                    var box = null, currentSelect = this.record.select.lastSelect;
                    if (!currentSelect || !currentSelect.node.ownerSVGElement) {
                        return;
                    }
                    currentSelect.unselect();
                    box = currentSelect.getRenderBox(currentSelect);
                    currentSelect.setBoxWidth(box.width);
                    currentSelect.getBox().setTranslate(0, 0);
                },
                getPaper: function() {
                    return this.formula;
                },
                render: function(latexStr) {
                    var parsedTree = this.kfEditor.requestService("parser.parse", latexStr, true), objTree = this.assembly.regenerateBy(parsedTree);
                    // 更新语法模块所维护的树
                    this.kfEditor.requestService("syntax.update.objtree", objTree);
                },
                enableRelocation: function() {
                    this.relDisabled = false;
                },
                disableRelocation: function() {
                    this.relDisabled = true;
                },
                setCanvasZoom: function(zoom) {
                    var viewPort = this.formula.getViewPort();
                    this.canvasZoom = zoom;
                    viewPort.zoom = zoom;
                    this.formula.setViewPort(viewPort);
                },
                getCanvas: function() {
                    return this.formula;
                },
                getContentSize: function() {
                    return this.formula.container.getRenderBox();
                },
                /**
                 * 清除编辑器里内容的偏移
                 */
                clearCanvasTransform: function() {
                    var canvasInfo = this.record.canvas;
                    canvasInfo.viewBox = this.formula.getViewBox();
                    canvasInfo.contentOffset = this.formula.container.getTranslate();
                    this.setCanvasToCenter();
                    this.formula.node.removeAttribute("viewBox");
                    this.formula.container.setTranslate(0, 0);
                },
                /**
                 * 恢复被clearCanvasTransform清除的偏移， 该方法仅针对上一次清除有效，
                 * 且该方法应该只有在调用clearCanvasTransform后才可以调用该方法，并且两者之间应该配对出现
                 * @returns {boolean}
                 */
                revertCanvasTransform: function() {
                    var canvasInfo = this.record.canvas, viewBox = canvasInfo.viewBox;
                    if (!viewBox) {
                        return false;
                    }
                    this.formula.setViewBox(viewBox.x, viewBox.y, viewBox.width, viewBox.height);
                    this.formula.container.setTranslate(canvasInfo.contentOffset);
                    canvasInfo.viewBox = null;
                    canvasInfo.contentOffset = null;
                },
                getCanvasZoom: function() {
                    return this.canvasZoom;
                }
            });
            return RenderComponenet;
        }
    };

//src/syntax/delete.js
    /*！
     * 删除控制
     */
    _p[26] = {
        value: function(require, exports, module) {
            var kity = _p.r(20);
            return kity.createClass("DeleteComponent", {
                constructor: function(parentComponent, kfEditor) {
                    this.parentComponent = parentComponent;
                    this.kfEditor = kfEditor;
                },
                deleteGroup: function() {
                    var cursorInfo = this.parentComponent.getCursorRecord(), objTree = this.parentComponent.getObjectTree(), // 当前的树信息
                        currentTree = objTree.mapping[cursorInfo.groupId].strGroup;
                    // 选区长度为0, 则删除前一个组
                    if (cursorInfo.startOffset === cursorInfo.endOffset) {
                        // 已经到最前， 需要进一步处理
                        if (cursorInfo.startOffset === 0) {
                            // 根节点时， 直接退出， 不做任何处理
                            if (this.parentComponent.isRootTree(currentTree)) {
                                return false;
                            }
                            // 不是根节点时， 选中当前容器的父容器
                            cursorInfo = this.selectParentContainer(cursorInfo.groupId);
                            this.parentComponent.updateCursor(cursorInfo);
                            return false;
                        } else {
                            // 还有更多剩余内容， 则直接删除前一个组
                            if (currentTree.operand.length > 1) {
                                cursorInfo = this.deletePrevGroup(currentTree, cursorInfo);
                            } else {
                                // 更新光标位置
                                cursorInfo.startOffset = 0;
                                cursorInfo.endOffset = 1;
                                // 处理组类型， 选中该组即可
                                if (currentTree.operand[0].attr && this.parentComponent.isGroupNode(currentTree.operand[0].attr.id)) {
                                    this.parentComponent.updateCursor(cursorInfo);
                                    return false;
                                } else {
                                    // 替换成占位符
                                    currentTree.operand[0] = {
                                        name: "placeholder",
                                        operand: []
                                    };
                                    this.parentComponent.updateCursor(cursorInfo);
                                    return true;
                                }
                            }
                        }
                    } else {
                        // 当前选中占位符的情况
                        if (this.parentComponent.isSelectPlaceholder()) {
                            // 如果是根节点， 则不允许删除
                            if (this.parentComponent.isRootTree(currentTree)) {
                                return false;
                            } else {
                                cursorInfo = this.selectParentContainer(cursorInfo.groupId);
                                this.parentComponent.updateCursor(cursorInfo);
                                return false;
                            }
                        } else {
                            return this.deleteSelection(currentTree, cursorInfo);
                        }
                    }
                    this.parentComponent.updateCursor(cursorInfo);
                    // 选区长度为0， 则可以判定当前公式发生了改变
                    if (cursorInfo.startOffset === cursorInfo.endOffset) {
                        return true;
                    }
                    return false;
                },
                // 删除前一个节点, 返回更新后的光标信息
                deletePrevGroup: function(tree, cursorInfo) {
                    // 待删除的组
                    var index = cursorInfo.startOffset - 1, group = tree.operand[index];
                    // 叶子节点可以直接删除
                    if (this.parentComponent.isLeafTree(group)) {
                        tree.operand.splice(index, 1);
                        cursorInfo.startOffset -= 1;
                        cursorInfo.endOffset -= 1;
                    } else {
                        cursorInfo.startOffset -= 1;
                    }
                    return cursorInfo;
                },
                // 删除选区内容
                deleteSelection: function(tree, cursorInfo) {
                    // 选中的是容器内的所有内容
                    if (cursorInfo.startOffset === 0 && cursorInfo.endOffset === tree.operand.length) {
                        tree.operand.length = 1;
                        tree.operand[0] = {
                            name: "placeholder",
                            operand: []
                        };
                        cursorInfo.endOffset = 1;
                    } else {
                        tree.operand.splice(cursorInfo.startOffset, cursorInfo.endOffset - cursorInfo.startOffset);
                        cursorInfo.endOffset = cursorInfo.startOffset;
                    }
                    this.parentComponent.updateCursor(cursorInfo);
                    return true;
                },
                // 选中给定ID节点的父容器
                selectParentContainer: function(groupId) {
                    var currentGroupNode = this.parentComponent.getGroupObject(groupId).node, parentContainerInfo = this.kfEditor.requestService("position.get.group", currentGroupNode), // 当前组在父容器中的索引
                        index = this.kfEditor.requestService("position.get.index", parentContainerInfo.groupObj, currentGroupNode);
                    // 返回新的光标信息
                    return {
                        groupId: parentContainerInfo.id,
                        startOffset: index,
                        endOffset: index + 1
                    };
                }
            });
        }
    };

//src/syntax/move.js
    /*！
     * 光标移动控制
     */
    _p[27] = {
        value: function(require, exports, module) {
            var kity = _p.r(20), DIRECTION = {
                LEFT: "left",
                RIGHT: "right"
            };
            return kity.createClass("MoveComponent", {
                constructor: function(parentComponent, kfEditor) {
                    this.parentComponent = parentComponent;
                    this.kfEditor = kfEditor;
                },
                leftMove: function() {
                    var cursorInfo = this.parentComponent.getCursorRecord();
                    cursorInfo = updateCursorGoLeft.call(this, cursorInfo);
                    // cursorInfo 为null则不用处理
                    if (cursorInfo) {
                        this.parentComponent.updateCursor(cursorInfo);
                    }
                },
                rightMove: function() {
                    var cursorInfo = this.parentComponent.getCursorRecord();
                    cursorInfo = updateCursorGoRight.call(this, cursorInfo);
                    // cursorInfo 为null则不用处理
                    if (cursorInfo) {
                        this.parentComponent.updateCursor(cursorInfo);
                    }
                }
            });
            function updateCursorGoLeft(cursorInfo) {
                var prevGroupNode = null, syntaxComponent = this.parentComponent, containerInfo = null;
                containerInfo = syntaxComponent.getGroupContent(cursorInfo.groupId);
                // 当前处于占位符中
                if (syntaxComponent.isSelectPlaceholder()) {
                    return locateOuterIndex(this, containerInfo.content[cursorInfo.startOffset], DIRECTION.LEFT);
                }
                if (cursorInfo.startOffset === cursorInfo.endOffset) {
                    if (cursorInfo.startOffset > 0) {
                        prevGroupNode = containerInfo.content[cursorInfo.startOffset - 1];
                        if (isGroupNode(prevGroupNode)) {
                            cursorInfo = locateIndex(this, prevGroupNode, DIRECTION.LEFT);
                        } else {
                            cursorInfo.startOffset -= 1;
                            // 非占位符处理
                            if (!isPlaceholderNode(prevGroupNode)) {
                                cursorInfo.endOffset = cursorInfo.startOffset;
                            }
                        }
                    } else {
                        cursorInfo = locateOuterIndex(this, containerInfo.groupObj, DIRECTION.LEFT);
                    }
                } else {
                    cursorInfo.startOffset = Math.min(cursorInfo.startOffset, cursorInfo.endOffset);
                    // 收缩
                    cursorInfo.endOffset = cursorInfo.startOffset;
                }
                return cursorInfo;
            }
            function updateCursorGoRight(cursorInfo) {
                var nextGroupNode = null, syntaxComponent = this.parentComponent, containerInfo = null;
                containerInfo = syntaxComponent.getGroupContent(cursorInfo.groupId);
                // 当前处于占位符中
                if (syntaxComponent.isSelectPlaceholder()) {
                    return locateOuterIndex(this, containerInfo.content[cursorInfo.startOffset], DIRECTION.RIGHT);
                }
                if (cursorInfo.startOffset === cursorInfo.endOffset) {
                    if (cursorInfo.startOffset < containerInfo.content.length) {
                        nextGroupNode = containerInfo.content[cursorInfo.startOffset];
                        // 进入容器内部
                        if (isGroupNode(nextGroupNode)) {
                            cursorInfo = locateIndex(this, nextGroupNode, DIRECTION.RIGHT);
                        } else {
                            cursorInfo.startOffset += 1;
                            // 非占位符同时更新结束偏移
                            if (!isPlaceholderNode(nextGroupNode)) {
                                cursorInfo.endOffset = cursorInfo.startOffset;
                            }
                        }
                    } else {
                        cursorInfo = locateOuterIndex(this, containerInfo.groupObj, DIRECTION.RIGHT);
                    }
                } else {
                    cursorInfo.endOffset = Math.max(cursorInfo.startOffset, cursorInfo.endOffset);
                    // 收缩
                    cursorInfo.startOffset = cursorInfo.endOffset;
                }
                return cursorInfo;
            }
            /**
             * 组内寻址, 入组
             */
            function locateIndex(moveComponent, groupNode, dir) {
                switch (dir) {
                    case DIRECTION.LEFT:
                        return locateLeftIndex(moveComponent, groupNode);

                    case DIRECTION.RIGHT:
                        return locateRightIndex(moveComponent, groupNode);
                }
                throw new Error("undefined move direction!");
            }
            /**
             * 组外寻址, 出组
             */
            function locateOuterIndex(moveComponent, groupNode, dir) {
                switch (dir) {
                    case DIRECTION.LEFT:
                        return locateOuterLeftIndex(moveComponent, groupNode);

                    case DIRECTION.RIGHT:
                        return locateOuterRightIndex(moveComponent, groupNode);
                }
                throw new Error("undefined move direction!");
            }
            // 左移内部定位
            function locateLeftIndex(moveComponent, groupNode) {
                var syntaxComponent = moveComponent.parentComponent, groupInfo = null, groupElement = null;
                if (isPlaceholderNode(groupNode) || isEmptyNode(groupNode)) {
                    return locateOuterLeftIndex(moveComponent, groupNode);
                }
                if (isGroupNode(groupNode)) {
                    groupInfo = syntaxComponent.getGroupContent(groupNode.id);
                    // 容器内部中末尾的元素
                    groupElement = groupInfo.content[groupInfo.content.length - 1];
                    // 空检测
                    if (isEmptyNode(groupElement)) {
                        // 做跳出处理
                        return locateOuterLeftIndex(moveComponent, groupElement);
                    }
                    // 待定位的组本身就是一个容器, 则检测其内部结构是否还包含容器
                    if (isContainerNode(groupNode)) {
                        // 进入到占位符包裹容器内
                        if (isPlaceholderNode(groupElement)) {
                            return {
                                groupId: groupNode.id,
                                startOffset: groupInfo.content.length - 1,
                                endOffset: groupInfo.content.length
                            };
                        } else if (isContainerNode(groupElement) && groupInfo.content.length === 1) {
                            return locateLeftIndex(moveComponent, groupElement);
                        }
                        return {
                            groupId: groupNode.id,
                            startOffset: groupInfo.content.length,
                            endOffset: groupInfo.content.length
                        };
                    } else {
                        while (!isContainerNode(groupElement) && !isEmptyNode(groupElement) && !isPlaceholderNode(groupElement)) {
                            groupInfo = syntaxComponent.getGroupContent(groupElement.id);
                            groupElement = groupInfo.content[groupInfo.content.length - 1];
                        }
                        if (isEmptyNode(groupElement)) {
                            return locateOuterLeftIndex(moveComponent, groupElement);
                        }
                        if (isPlaceholderNode(groupElement)) {
                            return {
                                groupId: groupElement.id,
                                startOffset: groupInfo.content.length,
                                endOffset: groupInfo.content.length
                            };
                        }
                        return locateLeftIndex(moveComponent, groupElement);
                    }
                }
                return null;
            }
            // 左移外部定位
            function locateOuterLeftIndex(moveComponent, groupNode) {
                var kfEditor = moveComponent.kfEditor, outerGroupInfo = null, groupInfo = null;
                // 根容器， 不用再跳出
                if (isRootNode(groupNode)) {
                    return null;
                }
                outerGroupInfo = kfEditor.requestService("position.get.parent.info", groupNode);
                while (outerGroupInfo.index === 0) {
                    if (isRootNode(outerGroupInfo.group.groupObj)) {
                        return {
                            groupId: outerGroupInfo.group.id,
                            startOffset: 0,
                            endOffset: 0
                        };
                    }
                    // 如果父组是一个容器， 并且该容器包含不止一个节点， 则跳到父组开头
                    if (isContainerNode(outerGroupInfo.group.groupObj) && outerGroupInfo.group.content.length > 1) {
                        return {
                            groupId: outerGroupInfo.group.id,
                            startOffset: 0,
                            endOffset: 0
                        };
                    }
                    outerGroupInfo = kfEditor.requestService("position.get.parent.info", outerGroupInfo.group.groupObj);
                }
                // 如果外部组是容器， 则直接定位即可
                if (isContainerNode(outerGroupInfo.group.groupObj)) {
                    return {
                        groupId: outerGroupInfo.group.id,
                        startOffset: outerGroupInfo.index,
                        endOffset: outerGroupInfo.index
                    };
                }
                groupNode = outerGroupInfo.group.content[outerGroupInfo.index - 1];
                // 定位到的组是一个容器， 则定位到容器尾部
                if (isGroupNode(groupNode)) {
                    // 容器节点
                    if (isContainerNode(groupNode)) {
                        // 进入容器内部
                        return locateLeftIndex(moveComponent, groupNode);
                    } else {
                        return locateLeftIndex(moveComponent, groupNode);
                    }
                    return {
                        groupId: groupNode.id,
                        startOffset: groupInfo.content.length,
                        endOffset: groupInfo.content.length
                    };
                }
                if (isEmptyNode(groupNode)) {
                    return locateOuterLeftIndex(moveComponent, groupNode);
                }
                return {
                    groupId: outerGroupInfo.group.id,
                    startOffset: outerGroupInfo.index,
                    endOffset: outerGroupInfo.index
                };
            }
            // 右移内部定位
            function locateRightIndex(moveComponent, groupNode) {
                var syntaxComponent = moveComponent.parentComponent, groupInfo = null, groupElement = null;
                if (isGroupNode(groupNode)) {
                    groupInfo = syntaxComponent.getGroupContent(groupNode.id);
                    // 容器内部中末尾的元素
                    groupElement = groupInfo.content[0];
                    // 待定位的组本身就是一个容器, 则检测其内部结构是否还包含容器
                    if (isContainerNode(groupNode)) {
                        // 内部元素仍然是一个容器
                        if (isContainerNode(groupElement)) {
                            // 递归处理
                            return locateRightIndex(moveComponent, groupElement);
                        }
                        if (isPlaceholderNode(groupElement)) {
                            return {
                                groupId: groupNode.id,
                                startOffset: 0,
                                endOffset: 1
                            };
                        }
                        return {
                            groupId: groupNode.id,
                            startOffset: 0,
                            endOffset: 0
                        };
                    } else {
                        while (!isContainerNode(groupElement) && !isPlaceholderNode(groupElement) && !isEmptyNode(groupElement)) {
                            groupInfo = syntaxComponent.getGroupContent(groupElement.id);
                            groupElement = groupInfo.content[0];
                        }
                        // 定位到占位符内部
                        if (isPlaceholderNode(groupElement)) {
                            return {
                                groupId: groupElement.id,
                                startOffset: 0,
                                endOffset: 0
                            };
                        } else if (isEmptyNode(groupElement)) {
                            return locateOuterRightIndex(moveComponent, groupElement);
                        } else {
                            return locateRightIndex(moveComponent, groupElement);
                        }
                    }
                }
                return null;
            }
            // 右移外部定位
            function locateOuterRightIndex(moveComponent, groupNode) {
                var kfEditor = moveComponent.kfEditor, syntaxComponent = moveComponent.parentComponent, outerGroupInfo = null, groupInfo = null;
                // 根容器， 不用再跳出
                if (isRootNode(groupNode)) {
                    return null;
                }
                outerGroupInfo = kfEditor.requestService("position.get.parent.info", groupNode);
                // 仍然需要回溯
                while (outerGroupInfo.index === outerGroupInfo.group.content.length - 1) {
                    if (isRootNode(outerGroupInfo.group.groupObj)) {
                        return {
                            groupId: outerGroupInfo.group.id,
                            startOffset: outerGroupInfo.group.content.length,
                            endOffset: outerGroupInfo.group.content.length
                        };
                    }
                    // 如果父组是一个容器， 并且该容器包含不止一个节点， 则跳到父组末尾
                    if (isContainerNode(outerGroupInfo.group.groupObj) && outerGroupInfo.group.content.length > 1) {
                        return {
                            groupId: outerGroupInfo.group.id,
                            startOffset: outerGroupInfo.group.content.length,
                            endOffset: outerGroupInfo.group.content.length
                        };
                    }
                    outerGroupInfo = kfEditor.requestService("position.get.parent.info", outerGroupInfo.group.groupObj);
                }
                groupNode = outerGroupInfo.group.content[outerGroupInfo.index + 1];
                // 空节点处理
                if (isEmptyNode(groupNode)) {
                    return locateOuterRightIndex(moveComponent, groupNode);
                }
                // 定位到的组是一个容器， 则定位到容器内部开头位置上
                if (isContainerNode(groupNode)) {
                    groupInfo = syntaxComponent.getGroupContent(groupNode.id);
                    // 检查内容开始元素是否是占位符
                    if (syntaxComponent.isPlaceholder(groupInfo.content[0].id)) {
                        return {
                            groupId: groupNode.id,
                            startOffset: 0,
                            endOffset: 1
                        };
                    }
                    return {
                        groupId: groupNode.id,
                        startOffset: 0,
                        endOffset: 0
                    };
                }
                return {
                    groupId: outerGroupInfo.group.id,
                    startOffset: outerGroupInfo.index + 1,
                    endOffset: outerGroupInfo.index + 1
                };
            }
            function isRootNode(node) {
                return !!node.getAttribute("data-root");
            }
            function isContainerNode(node) {
                return node.getAttribute("data-type") === "kf-editor-group";
            }
            function isGroupNode(node) {
                var dataType = node.getAttribute("data-type");
                return dataType === "kf-editor-group" || dataType === "kf-editor-virtual-group";
            }
            function isPlaceholderNode(node) {
                return node.getAttribute("data-flag") === "Placeholder";
            }
            function isEmptyNode(node) {
                return node.getAttribute("data-flag") === "Empty";
            }
        }
    };

//src/syntax/syntax.js
    /*!
     * 语法控制单元
     */
    _p[28] = {
        value: function(require) {
            var kity = _p.r(20), MoveComponent = _p.r(27), DeleteComponent = _p.r(26), CURSOR_CHAR = _p.r(29).cursorCharacter, GROUP_TYPE = _p.r(11), SyntaxComponenet = kity.createClass("SyntaxComponenet", {
                constructor: function(kfEditor) {
                    this.kfEditor = kfEditor;
                    // 数据记录表
                    this.record = {
                        // 光标位置
                        cursor: {
                            group: null,
                            startOffset: -1,
                            endOffset: -1
                        }
                    };
                    // 子组件结构
                    this.components = {};
                    // 对象树
                    this.objTree = null;
                    this.initComponents();
                    this.initServices();
                    this.initCommands();
                },
                initComponents: function() {
                    this.components.move = new MoveComponent(this, this.kfEditor);
                    this.components.delete = new DeleteComponent(this, this.kfEditor);
                },
                initServices: function() {
                    this.kfEditor.registerService("syntax.update.objtree", this, {
                        updateObjTree: this.updateObjTree
                    });
                    this.kfEditor.registerService("syntax.get.objtree", this, {
                        getObjectTree: this.getObjectTree
                    });
                    this.kfEditor.registerService("syntax.get.group.object", this, {
                        getGroupObject: this.getGroupObject
                    });
                    this.kfEditor.registerService("syntax.is.root.node", this, {
                        isRootNode: this.isRootNode
                    });
                    this.kfEditor.registerService("syntax.is.group.node", this, {
                        isGroupNode: this.isGroupNode
                    });
                    this.kfEditor.registerService("syntax.is.virtual.node", this, {
                        isVirtualNode: this.isVirtualNode
                    });
                    this.kfEditor.registerService("syntax.is.placeholder.node", this, {
                        isPlaceholder: this.isPlaceholder
                    });
                    this.kfEditor.registerService("syntax.is.select.placeholder", this, {
                        isSelectPlaceholder: this.isSelectPlaceholder
                    });
                    this.kfEditor.registerService("syntax.has.root.placeholder", this, {
                        hasRootplaceholder: this.hasRootplaceholder
                    });
                    this.kfEditor.registerService("syntax.valid.brackets", this, {
                        isBrackets: this.isBrackets
                    });
                    this.kfEditor.registerService("syntax.get.group.content", this, {
                        getGroupContent: this.getGroupContent
                    });
                    this.kfEditor.registerService("syntax.get.root.group.info", this, {
                        getRootGroupInfo: this.getRootGroupInfo
                    });
                    this.kfEditor.registerService("syntax.get.root", this, {
                        getRootObject: this.getRootObject
                    });
                    this.kfEditor.registerService("syntax.update.record.cursor", this, {
                        updateCursor: this.updateCursor
                    });
                    this.kfEditor.registerService("syntax.update.selection", this, {
                        updateSelection: this.updateSelection
                    });
                    this.kfEditor.registerService("syntax.get.record.cursor", this, {
                        getCursorRecord: this.getCursorRecord
                    });
                    this.kfEditor.registerService("syntax.has.cursor.info", this, {
                        hasCursorInfo: this.hasCursorInfo
                    });
                    this.kfEditor.registerService("syntax.serialization", this, {
                        serialization: this.serialization
                    });
                    this.kfEditor.registerService("syntax.cursor.move.left", this, {
                        leftMove: this.leftMove
                    });
                    this.kfEditor.registerService("syntax.cursor.move.right", this, {
                        rightMove: this.rightMove
                    });
                    this.kfEditor.registerService("syntax.delete.group", this, {
                        deleteGroup: this.deleteGroup
                    });
                },
                initCommands: function() {
                    this.kfEditor.registerCommand("get.source", this, this.getSource);
                    this.kfEditor.registerCommand("content.is.empty", this, this.isEmpty);
                },
                updateObjTree: function(objTree) {
                    var selectInfo = objTree.select;
                    if (selectInfo && selectInfo.groupId) {
                        this.updateCursor(selectInfo.groupId, selectInfo.startOffset, selectInfo.endOffset);
                    }
                    this.objTree = objTree;
                },
                hasCursorInfo: function() {
                    return this.record.cursor.group !== null;
                },
                // 验证给定ID的组是否是根节点
                isRootNode: function(groupId) {
                    return this.objTree.mapping.root.strGroup.attr.id === groupId;
                },
                // 验证给定ID的组是否是组节点
                isGroupNode: function(groupId) {
                    var type = this.objTree.mapping[groupId].strGroup.attr["data-type"];
                    return type === GROUP_TYPE.GROUP || type === GROUP_TYPE.VIRTUAL;
                },
                isVirtualNode: function(groupId) {
                    return this.objTree.mapping[groupId].strGroup.attr["data-type"] === GROUP_TYPE.VIRTUAL;
                },
                // 验证给定ID的组是否是占位符
                isPlaceholder: function(groupId) {
                    var currentNode = this.objTree.mapping[groupId];
                    if (!currentNode) {
                        return false;
                    }
                    currentNode = currentNode.objGroup.node;
                    return currentNode.getAttribute("data-flag") === "Placeholder";
                },
                isBrackets: function(groupId) {
                    return !!this.objTree.mapping[groupId].objGroup.node.getAttribute("data-brackets");
                },
                // 当前是否存在“根占位符”
                hasRootplaceholder: function() {
                    return this.objTree.mapping.root.strGroup.operand[0].name === "placeholder";
                },
                // 当前光标选中的是否是占位符
                isSelectPlaceholder: function() {
                    var cursorInfo = this.record.cursor, groupInfo = null;
                    if (cursorInfo.endOffset - cursorInfo.startOffset !== 1) {
                        return false;
                    }
                    groupInfo = this.getGroupContent(cursorInfo.groupId);
                    if (!this.isPlaceholder(groupInfo.content[cursorInfo.startOffset].id)) {
                        return false;
                    }
                    return true;
                },
                // 给定的子树是否是一个叶子节点
                isLeafTree: function(tree) {
                    return typeof tree === "string";
                },
                // 给定的子树是否是根节点
                isRootTree: function(tree) {
                    return tree.attr && tree.attr["data-root"];
                },
                getObjectTree: function() {
                    return this.objTree;
                },
                getGroupObject: function(id) {
                    return this.objTree.mapping[id].objGroup || null;
                },
                getCursorRecord: function() {
                    return kity.Utils.extend({}, this.record.cursor) || null;
                },
                getGroupContent: function(groupId) {
                    var groupInfo = this.objTree.mapping[groupId], content = [], operands = groupInfo.objGroup.operands, offset = operands.length - 1, isLtr = groupInfo.strGroup.traversal !== "rtl";
                    kity.Utils.each(operands, function(operand, i) {
                        if (isLtr) {
                            content.push(operand.node);
                        } else {
                            content[offset - i] = operand.node;
                        }
                    });
                    return {
                        id: groupId,
                        traversal: groupInfo.strGroup.traversal || "ltr",
                        groupObj: groupInfo.objGroup.node,
                        content: content
                    };
                },
                getRootObject: function() {
                    return this.objTree.mapping.root.objGroup;
                },
                getRootGroupInfo: function() {
                    var rootGroupId = this.objTree.mapping.root.strGroup.attr.id;
                    return this.getGroupContent(rootGroupId);
                },
                updateSelection: function(group) {
                    var groupObj = this.objTree.mapping[group.id], curStrGroup = groupObj.strGroup, parentGroup = null, parentGroupObj = null, resultStr = null, startOffset = -1, endOffset = -1;
                    parentGroup = group;
                    parentGroupObj = groupObj;
                    if (curStrGroup.name === "combination") {
                        this.record.cursor = {
                            groupId: parentGroup.id,
                            startOffset: 0,
                            endOffset: curStrGroup.operand.length
                        };
                        // 字符内容处理
                        curStrGroup.operand.unshift(CURSOR_CHAR);
                        curStrGroup.operand.push(CURSOR_CHAR);
                    } else {
                        // 函数处理， 找到函数所处的最大范围
                        while (parentGroupObj.strGroup.name !== "combination" || parentGroup.content === 1) {
                            group = parentGroup;
                            groupObj = parentGroupObj;
                            parentGroup = this.kfEditor.requestService("position.get.parent.group", groupObj.objGroup.node);
                            parentGroupObj = this.objTree.mapping[parentGroup.id];
                        }
                        var parentIndex = [].indexOf.call(parentGroup.content, group.groupObj);
                        this.record.cursor = {
                            groupId: parentGroup.id,
                            startOffset: parentIndex,
                            endOffset: parentIndex + 1
                        };
                        // 在当前函数所在的位置作标记
                        parentGroupObj.strGroup.operand.splice(parentIndex + 1, 0, CURSOR_CHAR);
                        parentGroupObj.strGroup.operand.splice(parentIndex, 0, CURSOR_CHAR);
                    }
                    // 返回结构树进过序列化后所对应的latex表达式， 同时包含有当前光标定位点信息
                    resultStr = this.kfEditor.requestService("parser.latex.serialization", this.objTree.parsedTree);
                    startOffset = resultStr.indexOf(CURSOR_CHAR);
                    resultStr = resultStr.replace(CURSOR_CHAR, "");
                    endOffset = resultStr.indexOf(CURSOR_CHAR);
                    parentGroupObj.strGroup.operand.splice(this.record.cursor.startOffset, 1);
                    parentGroupObj.strGroup.operand.splice(this.record.cursor.endOffset, 1);
                    return {
                        str: resultStr,
                        startOffset: startOffset,
                        endOffset: endOffset
                    };
                },
                getSource: function() {
                    return this.serialization().str.replace(CURSOR_CHAR, "").replace(CURSOR_CHAR, "");
                },
                isEmpty: function() {
                    return this.hasRootplaceholder();
                },
                serialization: function() {
                    var cursor = this.record.cursor, objGroup = this.objTree.mapping[cursor.groupId], curStrGroup = objGroup.strGroup, resultStr = null, strStartIndex = -1, strEndIndex = -1;
                    // 格式化偏移值， 保证在处理操作数时， 标记位置不会出错
                    strStartIndex = Math.min(cursor.endOffset, cursor.startOffset);
                    strEndIndex = Math.max(cursor.endOffset, cursor.startOffset);
                    curStrGroup.operand.splice(strEndIndex, 0, CURSOR_CHAR);
                    curStrGroup.operand.splice(strStartIndex, 0, CURSOR_CHAR);
                    strEndIndex += 1;
                    // 返回结构树进过序列化后所对应的latex表达式， 同时包含有当前光标定位点信息
                    resultStr = this.kfEditor.requestService("parser.latex.serialization", this.objTree.parsedTree);
                    curStrGroup.operand.splice(strEndIndex, 1);
                    curStrGroup.operand.splice(strStartIndex, 1);
                    strStartIndex = resultStr.indexOf(CURSOR_CHAR);
                    // 选区长度为0, 则只使用一个标记位
                    if (cursor.startOffset === cursor.endOffset) {
                        resultStr = resultStr.replace(CURSOR_CHAR, "");
                    }
                    strEndIndex = resultStr.lastIndexOf(CURSOR_CHAR);
                    return {
                        str: resultStr,
                        startOffset: strStartIndex,
                        endOffset: strEndIndex
                    };
                },
                // 更新光标记录， 同时更新数据
                updateCursor: function(groupId, startOffset, endOffset) {
                    var tmp = null;
                    // 支持一个cursorinfo对象
                    if (arguments.length === 1) {
                        endOffset = groupId.endOffset;
                        startOffset = groupId.startOffset;
                        groupId = groupId.groupId;
                    }
                    if (endOffset === undefined) {
                        endOffset = startOffset;
                    }
                    if (startOffset > endOffset) {
                        tmp = endOffset;
                        endOffset = startOffset;
                        startOffset = tmp;
                    }
                    this.record.cursor = {
                        groupId: groupId,
                        startOffset: startOffset,
                        endOffset: endOffset
                    };
                },
                leftMove: function() {
                    this.components.move.leftMove();
                },
                rightMove: function() {
                    this.components.move.rightMove();
                },
                // 根据当前光标的信息，删除组
                deleteGroup: function() {
                    return this.components.delete.deleteGroup();
                },
                insertSubtree: function(subtree) {
                    var cursorInfo = this.record.cursor, // 当前光标信息所在的子树
                        startOffset = 0, endOffset = 0, currentTree = null, diff = 0;
                    if (this.isPlaceholder(cursorInfo.groupId)) {
                        // 当前在占位符内，所以用子树替换占位符
                        this.replaceTree(subtree);
                    } else {
                        startOffset = Math.min(cursorInfo.startOffset, cursorInfo.endOffset);
                        endOffset = Math.max(cursorInfo.startOffset, cursorInfo.endOffset);
                        diff = endOffset - startOffset;
                        currentTree = this.objTree.mapping[cursorInfo.groupId].strGroup;
                        // 插入子树
                        currentTree.operand.splice(startOffset, diff, subtree);
                        // 更新光标记录
                        cursorInfo.startOffset += 1;
                        cursorInfo.endOffset = cursorInfo.startOffset;
                    }
                },
                replaceTree: function(subtree) {
                    var cursorInfo = this.record.cursor, groupNode = this.objTree.mapping[cursorInfo.groupId].objGroup.node, parentInfo = this.kfEditor.requestService("position.get.parent.info", groupNode), currentTree = this.objTree.mapping[parentInfo.group.id].strGroup;
                    // 替换占位符为子树
                    currentTree.operand[parentInfo.index] = subtree;
                    // 更新光标
                    cursorInfo.groupId = parentInfo.group.id;
                    cursorInfo.startOffset = parentInfo.index + 1;
                    cursorInfo.endOffset = parentInfo.index + 1;
                }
            });
            return SyntaxComponenet;
        }
    };

//src/sysconf.js
    /*!
     * 系统配置文件
     */
    _p[29] = {
        value: function() {
            return {
                // 光标符号
                cursorCharacter: "",
                // 根占位符内容与颜色
                rootPlaceholder: {
                    color: "#666",
                    content: "在此处键入公式",
                    fontsize: 16
                },
                scrollbar: {
                    padding: 5,
                    step: 150
                }
            };
        }
    };

//src/ui/char-position.data.js
    /**
     * 特殊字符区域的icon位置数据
     */
    _p[30] = {
        value: function() {
            return {
                "\\pm": {
                    x: 5,
                    y: 0
                },
                "\\infty": {
                    x: 42,
                    y: 0
                },
                "=": {
                    x: 79,
                    y: 0
                },
                "\\sim": {
                    x: 116,
                    y: 0
                },
                "\\times": {
                    x: 153,
                    y: 0
                },
                "\\div": {
                    x: 190,
                    y: 0
                },
                "!": {
                    x: 227,
                    y: 0
                },
                "<": {
                    x: 264,
                    y: 0
                },
                "\\ll": {
                    x: 301,
                    y: 0
                },
                ">": {
                    x: 338,
                    y: 0
                },
                "\\gg": {
                    x: 375,
                    y: 0
                },
                "\\leq": {
                    x: 412,
                    y: 0
                },
                "\\geq": {
                    x: 449,
                    y: 0
                },
                "\\mp": {
                    x: 486,
                    y: 0
                },
                "\\cong": {
                    x: 523,
                    y: 0
                },
                "\\equiv": {
                    x: 560,
                    y: 0
                },
                "\\propto": {
                    x: 597,
                    y: 0
                },
                "\\approx": {
                    x: 634,
                    y: 0
                },
                "\\forall": {
                    x: 671,
                    y: 0
                },
                "\\partial": {
                    x: 708,
                    y: 0
                },
                "\\surd": {
                    x: 745,
                    y: 0
                },
                "\\cup": {
                    x: 782,
                    y: 0
                },
                "\\cap": {
                    x: 819,
                    y: 0
                },
                "\\varnothing": {
                    x: 856,
                    y: 0
                },
                "%": {
                    x: 893,
                    y: 0
                },
                "\\circ": {
                    x: 930,
                    y: 0
                },
                "\\exists": {
                    x: 967,
                    y: 0
                },
                // "\\nexists": {
                //     x: 1004,
                //     y: 0
                // },
                "\\in": {
                    x: 1041,
                    y: 0
                },
                "\\ni": {
                    x: 1078,
                    y: 0
                },
                "\\gets": {
                    x: 5,
                    y: 37
                },
                "\\uparrow": {
                    x: 42,
                    y: 37
                },
                "\\to": {
                    x: 79,
                    y: 37
                },
                "\\downarrow": {
                    x: 116,
                    y: 37
                },
                "\\leftrightarrow": {
                    x: 153,
                    y: 37
                },
                "\\therefore": {
                    x: 190,
                    y: 37
                },
                "\\because": {
                    x: 227,
                    y: 37
                },
                "+": {
                    x: 264,
                    y: 37
                },
                "-": {
                    x: 301,
                    y: 37
                },
                "\\neg": {
                    x: 338,
                    y: 37
                },
                "\\ast": {
                    x: 375,
                    y: 37
                },
                "\\cdot": {
                    x: 412,
                    y: 37
                },
                "\\vdots": {
                    x: 449,
                    y: 37
                },
                "\\ddots": {
                    x: 486,
                    y: 37
                },
                "\\aleph": {
                    x: 523,
                    y: 37
                },
                // "\\beth": {
                //     x: 560,
                //     y: 37
                // },
                "\\blacksquare": {
                    x: 597,
                    y: 37
                },
                "\\alpha": {
                    x: 634,
                    y: 37
                },
                "\\beta": {
                    x: 671,
                    y: 37
                },
                "\\gamma": {
                    x: 708,
                    y: 37
                },
                "\\delta": {
                    x: 745,
                    y: 37
                },
                "\\epsilon": {
                    x: 782,
                    y: 37
                },
                "\\zeta": {
                    x: 819,
                    y: 37
                },
                "\\eta": {
                    x: 856,
                    y: 37
                },
                "\\theta": {
                    x: 893,
                    y: 37
                },
                "\\iota": {
                    x: 930,
                    y: 37
                },
                "\\kappa": {
                    x: 967,
                    y: 37
                },
                "\\lambda": {
                    x: 1004,
                    y: 37
                },
                "\\mu": {
                    x: 1041,
                    y: 37
                },
                "\\nu": {
                    x: 1078,
                    y: 37
                },
                "\\xi": {
                    x: 5,
                    y: 74
                },
                "\\omicron": {
                    x: 42,
                    y: 74
                },
                "\\pi": {
                    x: 79,
                    y: 74
                },
                "\\rho": {
                    x: 116,
                    y: 74
                },
                "\\sigma": {
                    x: 153,
                    y: 74
                },
                "\\tau": {
                    x: 190,
                    y: 74
                },
                "\\upsilon": {
                    x: 227,
                    y: 74
                },
                "\\phi": {
                    x: 264,
                    y: 74
                },
                "\\chi": {
                    x: 301,
                    y: 74
                },
                "\\psi": {
                    x: 338,
                    y: 74
                },
                "\\omega": {
                    x: 375,
                    y: 74
                },
                "\\Alpha": {
                    x: 412,
                    y: 74
                },
                "\\Beta": {
                    x: 449,
                    y: 74
                },
                "\\Gamma": {
                    x: 486,
                    y: 74
                },
                "\\Delta": {
                    x: 523,
                    y: 74
                },
                "\\Epsilon": {
                    x: 560,
                    y: 74
                },
                "\\Zeta": {
                    x: 597,
                    y: 74
                },
                "\\Eta": {
                    x: 634,
                    y: 74
                },
                "\\Theta": {
                    x: 671,
                    y: 74
                },
                "\\Iota": {
                    x: 708,
                    y: 74
                },
                "\\Kappa": {
                    x: 745,
                    y: 74
                },
                "\\Lambda": {
                    x: 782,
                    y: 74
                },
                "\\Mu": {
                    x: 819,
                    y: 74
                },
                "\\Nu": {
                    x: 856,
                    y: 74
                },
                "\\Xi": {
                    x: 893,
                    y: 74
                },
                "\\Omicron": {
                    x: 930,
                    y: 74
                },
                "\\Pi": {
                    x: 967,
                    y: 74
                },
                "\\Rho": {
                    x: 1004,
                    y: 74
                },
                "\\Sigma": {
                    x: 1041,
                    y: 74
                },
                "\\Tau": {
                    x: 1078,
                    y: 74
                },
                "\\Upsilon": {
                    x: 5,
                    y: 111
                },
                "\\Phi": {
                    x: 42,
                    y: 111
                },
                "\\Chi": {
                    x: 79,
                    y: 111
                },
                "\\Psi": {
                    x: 116,
                    y: 111
                },
                "\\Omega": {
                    x: 153,
                    y: 111
                },
                "\\digamma": {
                    x: 190,
                    y: 111
                },
                "\\varepsilon": {
                    x: 227,
                    y: 111
                },
                "\\varkappa": {
                    x: 264,
                    y: 111
                },
                "\\varphi": {
                    x: 301,
                    y: 111
                },
                "\\varpi": {
                    x: 338,
                    y: 111
                },
                "\\varrho": {
                    x: 375,
                    y: 111
                },
                "\\varsigma": {
                    x: 412,
                    y: 111
                },
                "\\vartheta": {
                    x: 449,
                    y: 111
                },
                "\\neq": {
                    x: 486,
                    y: 111
                },
                "\\nless": {
                    x: 523,
                    y: 111
                },
                "\\ngtr": {
                    x: 560,
                    y: 111
                },
                "\\nleq": {
                    x: 597,
                    y: 111
                },
                "\\ngeq": {
                    x: 634,
                    y: 111
                },
                "\\nsim": {
                    x: 671,
                    y: 111
                },
                "\\lneqq": {
                    x: 708,
                    y: 111
                },
                "\\gneqq": {
                    x: 745,
                    y: 111
                },
                "\\nprec": {
                    x: 782,
                    y: 111
                },
                "\\nsucc": {
                    x: 819,
                    y: 111
                },
                "\\notin": {
                    x: 856,
                    y: 111
                },
                "\\nsubseteq": {
                    x: 893,
                    y: 111
                },
                "\\nsupseteq": {
                    x: 930,
                    y: 111
                },
                "\\subsetneq": {
                    x: 967,
                    y: 111
                },
                "\\supsetneq": {
                    x: 1004,
                    y: 111
                },
                "\\lnsim": {
                    x: 1041,
                    y: 111
                },
                "\\gnsim": {
                    x: 1078,
                    y: 111
                },
                "\\precnsim": {
                    x: 5,
                    y: 148
                },
                "\\succnsim": {
                    x: 42,
                    y: 148
                },
                "\\ntriangleleft": {
                    x: 79,
                    y: 148
                },
                "\\ntriangleright": {
                    x: 116,
                    y: 148
                },
                "\\ntrianglelefteq": {
                    x: 153,
                    y: 148
                },
                "\\ntrianglerighteq": {
                    x: 190,
                    y: 148
                },
                "\\nmid": {
                    x: 227,
                    y: 148
                },
                "\\nparallel": {
                    x: 264,
                    y: 148
                },
                "\\nvdash": {
                    x: 301,
                    y: 148
                },
                "\\nVdash": {
                    x: 338,
                    y: 148
                },
                "\\nvDash": {
                    x: 375,
                    y: 148
                },
                "\\nVDash": {
                    x: 412,
                    y: 148
                },
                "\\daleth": {
                    x: 449,
                    y: 148
                },
                "\\gimel": {
                    x: 486,
                    y: 148
                },
                "\\complement": {
                    x: 523,
                    y: 148
                },
                "\\ell": {
                    x: 560,
                    y: 148
                },
                "\\eth": {
                    x: 597,
                    y: 148
                },
                "\\hbar": {
                    x: 634,
                    y: 148
                },
                "\\hslash": {
                    x: 671,
                    y: 148
                },
                "\\mho": {
                    x: 708,
                    y: 148
                },
                "\\wp": {
                    x: 745,
                    y: 148
                },
                "\\circledS": {
                    x: 782,
                    y: 148
                },
                "\\Bbbk": {
                    x: 819,
                    y: 148
                },
                "\\Finv": {
                    x: 856,
                    y: 148
                },
                "\\Game": {
                    x: 893,
                    y: 148
                },
                "\\Im": {
                    x: 930,
                    y: 148
                },
                "\\Re": {
                    x: 967,
                    y: 148
                },
                "\\updownarrow": {
                    x: 1004,
                    y: 148
                },
                "\\Leftarrow": {
                    x: 1041,
                    y: 148
                },
                "\\Rightarrow": {
                    x: 1078,
                    y: 148
                },
                "\\Uparrow": {
                    x: 5,
                    y: 185
                },
                "\\Downarrow": {
                    x: 42,
                    y: 185
                },
                "\\Leftrightarrow": {
                    x: 79,
                    y: 185
                },
                "\\Updownarrow": {
                    x: 116,
                    y: 185
                },
                "\\longleftarrow": {
                    x: 153,
                    y: 185
                },
                "\\longrightarrow": {
                    x: 190,
                    y: 185
                },
                "\\longleftrightarrow": {
                    x: 227,
                    y: 185
                },
                "\\Longleftarrow": {
                    x: 264,
                    y: 185
                },
                "\\Longrightarrow": {
                    x: 301,
                    y: 185
                },
                "\\Longleftrightarrow": {
                    x: 338,
                    y: 185
                },
                "\\nearrow": {
                    x: 375,
                    y: 185
                },
                "\\nwarrow": {
                    x: 412,
                    y: 185
                },
                "\\searrow": {
                    x: 449,
                    y: 185
                },
                "\\swarrow": {
                    x: 486,
                    y: 185
                },
                "\\nleftarrow": {
                    x: 523,
                    y: 185
                },
                "\\nrightarrow": {
                    x: 560,
                    y: 185
                },
                "\\nLeftarrow": {
                    x: 597,
                    y: 185
                },
                "\\nRightarrow": {
                    x: 634,
                    y: 185
                },
                "\\nLeftrightarrow": {
                    x: 671,
                    y: 185
                },
                "\\leftharpoonup": {
                    x: 708,
                    y: 185
                },
                "\\leftharpoondown": {
                    x: 745,
                    y: 185
                },
                "\\rightharpoonup": {
                    x: 782,
                    y: 185
                },
                "\\rightharpoondown": {
                    x: 819,
                    y: 185
                },
                "\\upharpoonleft": {
                    x: 856,
                    y: 185
                },
                "\\upharpoonright": {
                    x: 893,
                    y: 185
                },
                "\\downharpoonleft": {
                    x: 930,
                    y: 185
                },
                "\\downharpoonright": {
                    x: 967,
                    y: 185
                },
                "\\leftrightharpoons": {
                    x: 1004,
                    y: 185
                },
                "\\rightleftharpoons": {
                    x: 1041,
                    y: 185
                },
                "\\leftleftarrows": {
                    x: 1078,
                    y: 185
                },
                "\\rightrightarrows": {
                    x: 5,
                    y: 222
                },
                "\\upuparrows": {
                    x: 42,
                    y: 222
                },
                "\\downdownarrows": {
                    x: 79,
                    y: 222
                },
                "\\leftrightarrows": {
                    x: 116,
                    y: 222
                },
                "\\rightleftarrows": {
                    x: 153,
                    y: 222
                },
                "\\looparrowleft": {
                    x: 190,
                    y: 222
                },
                "\\looparrowright": {
                    x: 227,
                    y: 222
                },
                "\\leftarrowtail": {
                    x: 264,
                    y: 222
                },
                "\\rightarrowtail": {
                    x: 301,
                    y: 222
                },
                "\\Lsh": {
                    x: 338,
                    y: 222
                },
                "\\Rsh": {
                    x: 375,
                    y: 222
                },
                "\\Lleftarrow": {
                    x: 412,
                    y: 222
                },
                "\\Rrightarrow": {
                    x: 449,
                    y: 222
                },
                "\\curvearrowleft": {
                    x: 486,
                    y: 222
                },
                "\\curvearrowright": {
                    x: 523,
                    y: 222
                },
                "\\circlearrowleft": {
                    x: 560,
                    y: 222
                },
                "\\circlearrowright": {
                    x: 597,
                    y: 222
                },
                "\\multimap": {
                    x: 634,
                    y: 222
                },
                "\\leftrightsquigarrow": {
                    x: 671,
                    y: 222
                },
                "\\twoheadleftarrow": {
                    x: 708,
                    y: 222
                },
                "\\twoheadrightarrow": {
                    x: 745,
                    y: 222
                },
                "\\rightsquigarrow": {
                    x: 782,
                    y: 222
                },
                "\\mathcal{A}": {
                    x: 819,
                    y: 222
                },
                "\\mathcal{B}": {
                    x: 856,
                    y: 222
                },
                "\\mathcal{C}": {
                    x: 893,
                    y: 222
                },
                "\\mathcal{D}": {
                    x: 930,
                    y: 222
                },
                "\\mathcal{E}": {
                    x: 967,
                    y: 222
                },
                "\\mathcal{F}": {
                    x: 1004,
                    y: 222
                },
                "\\mathcal{G}": {
                    x: 1041,
                    y: 222
                },
                "\\mathcal{H}": {
                    x: 1078,
                    y: 222
                },
                "\\mathcal{I}": {
                    x: 5,
                    y: 259
                },
                "\\mathcal{J}": {
                    x: 42,
                    y: 259
                },
                "\\mathcal{K}": {
                    x: 79,
                    y: 259
                },
                "\\mathcal{L}": {
                    x: 116,
                    y: 259
                },
                "\\mathcal{M}": {
                    x: 153,
                    y: 259
                },
                "\\mathcal{N}": {
                    x: 190,
                    y: 259
                },
                "\\mathcal{O}": {
                    x: 227,
                    y: 259
                },
                "\\mathcal{P}": {
                    x: 264,
                    y: 259
                },
                "\\mathcal{Q}": {
                    x: 301,
                    y: 259
                },
                "\\mathcal{R}": {
                    x: 338,
                    y: 259
                },
                "\\mathcal{S}": {
                    x: 375,
                    y: 259
                },
                "\\mathcal{T}": {
                    x: 412,
                    y: 259
                },
                "\\mathcal{U}": {
                    x: 449,
                    y: 259
                },
                "\\mathcal{V}": {
                    x: 486,
                    y: 259
                },
                "\\mathcal{W}": {
                    x: 523,
                    y: 259
                },
                "\\mathcal{X}": {
                    x: 560,
                    y: 259
                },
                "\\mathcal{Y}": {
                    x: 597,
                    y: 259
                },
                "\\mathcal{Z}": {
                    x: 634,
                    y: 259
                },
                "\\mathfrak{A}": {
                    x: 671,
                    y: 259
                },
                "\\mathfrak{B}": {
                    x: 708,
                    y: 259
                },
                "\\mathfrak{C}": {
                    x: 745,
                    y: 259
                },
                "\\mathfrak{D}": {
                    x: 782,
                    y: 259
                },
                "\\mathfrak{E}": {
                    x: 819,
                    y: 259
                },
                "\\mathfrak{F}": {
                    x: 856,
                    y: 259
                },
                "\\mathfrak{G}": {
                    x: 893,
                    y: 259
                },
                "\\mathfrak{H}": {
                    x: 930,
                    y: 259
                },
                "\\mathfrak{I}": {
                    x: 967,
                    y: 259
                },
                "\\mathfrak{J}": {
                    x: 1004,
                    y: 259
                },
                "\\mathfrak{K}": {
                    x: 1041,
                    y: 259
                },
                "\\mathfrak{L}": {
                    x: 1078,
                    y: 259
                },
                "\\mathfrak{M}": {
                    x: 5,
                    y: 296
                },
                "\\mathfrak{N}": {
                    x: 42,
                    y: 296
                },
                "\\mathfrak{O}": {
                    x: 79,
                    y: 296
                },
                "\\mathfrak{P}": {
                    x: 116,
                    y: 296
                },
                "\\mathfrak{Q}": {
                    x: 153,
                    y: 296
                },
                "\\mathfrak{R}": {
                    x: 190,
                    y: 296
                },
                "\\mathfrak{S}": {
                    x: 227,
                    y: 296
                },
                "\\mathfrak{T}": {
                    x: 264,
                    y: 296
                },
                "\\mathfrak{U}": {
                    x: 301,
                    y: 296
                },
                "\\mathfrak{V}": {
                    x: 338,
                    y: 296
                },
                "\\mathfrak{W}": {
                    x: 375,
                    y: 296
                },
                "\\mathfrak{X}": {
                    x: 412,
                    y: 296
                },
                "\\mathfrak{Y}": {
                    x: 449,
                    y: 296
                },
                "\\mathfrak{Z}": {
                    x: 486,
                    y: 296
                },
                "\\mathfrak{a}": {
                    x: 523,
                    y: 296
                },
                "\\mathfrak{b}": {
                    x: 560,
                    y: 296
                },
                "\\mathfrak{c}": {
                    x: 597,
                    y: 296
                },
                "\\mathfrak{d}": {
                    x: 634,
                    y: 296
                },
                "\\mathfrak{e}": {
                    x: 671,
                    y: 296
                },
                "\\mathfrak{f}": {
                    x: 708,
                    y: 296
                },
                "\\mathfrak{g}": {
                    x: 745,
                    y: 296
                },
                "\\mathfrak{h}": {
                    x: 782,
                    y: 296
                },
                "\\mathfrak{i}": {
                    x: 819,
                    y: 296
                },
                "\\mathfrak{j}": {
                    x: 856,
                    y: 296
                },
                "\\mathfrak{k}": {
                    x: 893,
                    y: 296
                },
                "\\mathfrak{l}": {
                    x: 930,
                    y: 296
                },
                "\\mathfrak{m}": {
                    x: 967,
                    y: 296
                },
                "\\mathfrak{n}": {
                    x: 1004,
                    y: 296
                },
                "\\mathfrak{o}": {
                    x: 1041,
                    y: 296
                },
                "\\mathfrak{p}": {
                    x: 1078,
                    y: 296
                },
                "\\mathfrak{q}": {
                    x: 5,
                    y: 333
                },
                "\\mathfrak{r}": {
                    x: 42,
                    y: 333
                },
                "\\mathfrak{s}": {
                    x: 79,
                    y: 333
                },
                "\\mathfrak{t}": {
                    x: 116,
                    y: 333
                },
                "\\mathfrak{u}": {
                    x: 153,
                    y: 333
                },
                "\\mathfrak{v}": {
                    x: 190,
                    y: 333
                },
                "\\mathfrak{w}": {
                    x: 227,
                    y: 333
                },
                "\\mathfrak{x}": {
                    x: 264,
                    y: 333
                },
                "\\mathfrak{y}": {
                    x: 301,
                    y: 333
                },
                "\\mathfrak{z}": {
                    x: 338,
                    y: 333
                },
                "\\mathbb{A}": {
                    x: 375,
                    y: 333
                },
                "\\mathbb{B}": {
                    x: 412,
                    y: 333
                },
                "\\mathbb{C}": {
                    x: 449,
                    y: 333
                },
                "\\mathbb{D}": {
                    x: 486,
                    y: 333
                },
                "\\mathbb{E}": {
                    x: 523,
                    y: 333
                },
                "\\mathbb{F}": {
                    x: 560,
                    y: 333
                },
                "\\mathbb{G}": {
                    x: 597,
                    y: 333
                },
                "\\mathbb{H}": {
                    x: 634,
                    y: 333
                },
                "\\mathbb{I}": {
                    x: 671,
                    y: 333
                },
                "\\mathbb{J}": {
                    x: 708,
                    y: 333
                },
                "\\mathbb{K}": {
                    x: 745,
                    y: 333
                },
                "\\mathbb{L}": {
                    x: 782,
                    y: 333
                },
                "\\mathbb{M}": {
                    x: 819,
                    y: 333
                },
                "\\mathbb{N}": {
                    x: 856,
                    y: 333
                },
                "\\mathbb{O}": {
                    x: 893,
                    y: 333
                },
                "\\mathbb{P}": {
                    x: 930,
                    y: 333
                },
                "\\mathbb{Q}": {
                    x: 967,
                    y: 333
                },
                "\\mathbb{R}": {
                    x: 1004,
                    y: 333
                },
                "\\mathbb{S}": {
                    x: 1041,
                    y: 333
                },
                "\\mathbb{T}": {
                    x: 1078,
                    y: 333
                },
                "\\mathbb{U}": {
                    x: 5,
                    y: 370
                },
                "\\mathbb{V}": {
                    x: 42,
                    y: 370
                },
                "\\mathbb{W}": {
                    x: 79,
                    y: 370
                },
                "\\mathbb{X}": {
                    x: 116,
                    y: 370
                },
                "\\mathbb{Y}": {
                    x: 153,
                    y: 370
                },
                "\\mathbb{Z}": {
                    x: 190,
                    y: 370
                },
                "\\mathrm{A}": {
                    x: 227,
                    y: 370
                },
                "\\mathrm{B}": {
                    x: 264,
                    y: 370
                },
                "\\mathrm{C}": {
                    x: 301,
                    y: 370
                },
                "\\mathrm{D}": {
                    x: 338,
                    y: 370
                },
                "\\mathrm{E}": {
                    x: 375,
                    y: 370
                },
                "\\mathrm{F}": {
                    x: 412,
                    y: 370
                },
                "\\mathrm{G}": {
                    x: 449,
                    y: 370
                },
                "\\mathrm{H}": {
                    x: 486,
                    y: 370
                },
                "\\mathrm{I}": {
                    x: 523,
                    y: 370
                },
                "\\mathrm{J}": {
                    x: 560,
                    y: 370
                },
                "\\mathrm{K}": {
                    x: 597,
                    y: 370
                },
                "\\mathrm{L}": {
                    x: 634,
                    y: 370
                },
                "\\mathrm{M}": {
                    x: 671,
                    y: 370
                },
                "\\mathrm{N}": {
                    x: 708,
                    y: 370
                },
                "\\mathrm{O}": {
                    x: 745,
                    y: 370
                },
                "\\mathrm{P}": {
                    x: 782,
                    y: 370
                },
                "\\mathrm{Q}": {
                    x: 819,
                    y: 370
                },
                "\\mathrm{R}": {
                    x: 856,
                    y: 370
                },
                "\\mathrm{S}": {
                    x: 893,
                    y: 370
                },
                "\\mathrm{T}": {
                    x: 930,
                    y: 370
                },
                "\\mathrm{U}": {
                    x: 967,
                    y: 370
                },
                "\\mathrm{V}": {
                    x: 1004,
                    y: 370
                },
                "\\mathrm{W}": {
                    x: 1041,
                    y: 370
                },
                "\\mathrm{X}": {
                    x: 1078,
                    y: 370
                },
                "\\mathrm{Y}": {
                    x: 5,
                    y: 407
                },
                "\\mathrm{Z}": {
                    x: 42,
                    y: 407
                },
                "\\mathrm{a}": {
                    x: 79,
                    y: 407
                },
                "\\mathrm{b}": {
                    x: 116,
                    y: 407
                },
                "\\mathrm{c}": {
                    x: 153,
                    y: 407
                },
                "\\mathrm{d}": {
                    x: 190,
                    y: 407
                },
                "\\mathrm{e}": {
                    x: 227,
                    y: 407
                },
                "\\mathrm{f}": {
                    x: 264,
                    y: 407
                },
                "\\mathrm{g}": {
                    x: 301,
                    y: 407
                },
                "\\mathrm{h}": {
                    x: 338,
                    y: 407
                },
                "\\mathrm{i}": {
                    x: 375,
                    y: 407
                },
                "\\mathrm{j}": {
                    x: 412,
                    y: 407
                },
                "\\mathrm{k}": {
                    x: 449,
                    y: 407
                },
                "\\mathrm{l}": {
                    x: 486,
                    y: 407
                },
                "\\mathrm{m}": {
                    x: 523,
                    y: 407
                },
                "\\mathrm{n}": {
                    x: 560,
                    y: 407
                },
                "\\mathrm{o}": {
                    x: 597,
                    y: 407
                },
                "\\mathrm{p}": {
                    x: 634,
                    y: 407
                },
                "\\mathrm{q}": {
                    x: 671,
                    y: 407
                },
                "\\mathrm{r}": {
                    x: 708,
                    y: 407
                },
                "\\mathrm{s}": {
                    x: 745,
                    y: 407
                },
                "\\mathrm{t}": {
                    x: 782,
                    y: 407
                },
                "\\mathrm{u}": {
                    x: 819,
                    y: 407
                },
                "\\mathrm{v}": {
                    x: 856,
                    y: 407
                },
                "\\mathrm{w}": {
                    x: 893,
                    y: 407
                },
                "\\mathrm{x}": {
                    x: 930,
                    y: 407
                },
                "\\mathrm{y}": {
                    x: 967,
                    y: 407
                },
                "\\mathrm{z}": {
                    x: 1004,
                    y: 407
                }
            };
        }
    };

//src/ui/control/zoom.js
    /*!
     * 滚动缩放控制器
     */
    _p[31] = {
        value: function(require) {
            var Utils = _p.r(4), kity = _p.r(20), DEFAULT_OPTIONS = {
                min: 1,
                max: 2
            }, ScrollZoomController = kity.createClass("ScrollZoomController", {
                constructor: function(parentComponent, kfEditor, target, options) {
                    this.kfEditor = kfEditor;
                    this.target = target;
                    this.zoom = 1;
                    this.step = .05;
                    this.options = Utils.extend({}, DEFAULT_OPTIONS, options);
                    this.initEvent();
                },
                initEvent: function() {
                    var kfEditor = this.kfEditor, _self = this, min = this.options.min, max = this.options.max, step = this.step;
                    Utils.addEvent(this.target, "mousewheel", function(e) {
                        e.preventDefault();
                        if (e.wheelDelta < 0) {
                            // 缩小
                            _self.zoom -= _self.zoom * step;
                        } else {
                            // 放大
                            _self.zoom += _self.zoom * step;
                        }
                        _self.zoom = Math.max(_self.zoom, min);
                        _self.zoom = Math.min(_self.zoom, max);
                        kfEditor.requestService("render.set.canvas.zoom", _self.zoom);
                    });
                }
            });
            return ScrollZoomController;
        }
    };

//src/ui/def.js
    /*!
     * UI定义
     */
    _p[32] = {
        value: function(require) {
            return {
                // 视窗状态
                VIEW_STATE: {
                    // 内容未超出画布
                    NO_OVERFLOW: 0,
                    // 内容溢出
                    OVERFLOW: 1
                },
                scrollbar: {
                    step: 50,
                    thumbMinSize: 50
                }
            };
        }
    };

//src/ui/other-position.data.js
    /**
     * 特殊字符区域之外的icon位置和大小数据
     */
    _p[33] = {
        value: function() {
            return {
                "x=\\frac {-b\\pm\\sqrt {b^2-4ac}}{2a}": {
                    pos: {
                        x: 0,
                        y: 0
                    },
                    size: {
                        width: 310,
                        height: 73
                    }
                },
                "{\\placeholder/\\placeholder}": {
                    pos: {
                        x: 315,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\frac \\placeholder\\placeholder": {
                    pos: {
                        x: 376,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "a^2+b^2=c^2": {
                    pos: {
                        x: 437,
                        y: 0
                    },
                    size: {
                        width: 310,
                        height: 73
                    }
                },
                "{\\left(x+a\\right)}^2=\\sum^n_{k=0}{\\left(^n_k\\right)x^ka^{n-k}}": {
                    pos: {
                        x: 752,
                        y: 0
                    },
                    size: {
                        width: 310,
                        height: 73
                    }
                },
                "\\frac {dy}{dx}": {
                    pos: {
                        x: 1067,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\frac {\\Delta y}{\\Delta x}": {
                    pos: {
                        x: 1128,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\frac {\\delta y}{\\delta x}": {
                    pos: {
                        x: 1189,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\frac \\pi 2": {
                    pos: {
                        x: 1250,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\placeholder^\\placeholder": {
                    pos: {
                        x: 1311,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\placeholder^\\placeholder_\\placeholder": {
                    pos: {
                        x: 1372,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\placeholder_\\placeholder": {
                    pos: {
                        x: 1433,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "{^\\placeholder_\\placeholder\\placeholder}": {
                    pos: {
                        x: 1494,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "e^{-i\\omega t}": {
                    pos: {
                        x: 1555,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "x^2": {
                    pos: {
                        x: 1616,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "{}^n_1Y": {
                    pos: {
                        x: 1677,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\sqrt \\placeholder": {
                    pos: {
                        x: 1738,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\sqrt [\\placeholder] \\placeholder": {
                    pos: {
                        x: 1799,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\sqrt [2] \\placeholder": {
                    pos: {
                        x: 1860,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\sqrt [3] \\placeholder": {
                    pos: {
                        x: 1921,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\frac {-b\\pm\\sqrt{b^2-4ac}}{2a}": {
                    pos: {
                        x: 1982,
                        y: 0
                    },
                    size: {
                        width: 137,
                        height: 75
                    }
                },
                "\\sqrt {a^2+b^2}": {
                    pos: {
                        x: 2124,
                        y: 0
                    },
                    size: {
                        width: 137,
                        height: 75
                    }
                },
                "\\int \\placeholder": {
                    pos: {
                        x: 2266,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\int^\\placeholder_\\placeholder\\placeholder": {
                    pos: {
                        x: 2327,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\iint\\placeholder": {
                    pos: {
                        x: 2388,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\iint^\\placeholder_\\placeholder\\placeholder": {
                    pos: {
                        x: 2449,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\iiint\\placeholder": {
                    pos: {
                        x: 2510,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\iiint^\\placeholder_\\placeholder\\placeholder": {
                    pos: {
                        x: 2571,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\sum\\placeholder": {
                    pos: {
                        x: 2632,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\sum^\\placeholder_\\placeholder\\placeholder": {
                    pos: {
                        x: 2693,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\sum_\\placeholder\\placeholder": {
                    pos: {
                        x: 2754,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\left(\\placeholder\\right)": {
                    pos: {
                        x: 2815,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\left[\\placeholder\\right]": {
                    pos: {
                        x: 2876,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\left\\{\\placeholder\\right\\}": {
                    pos: {
                        x: 2937,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\left|\\placeholder\\right|": {
                    pos: {
                        x: 2998,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\sin\\placeholder": {
                    pos: {
                        x: 3059,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\cos\\placeholder": {
                    pos: {
                        x: 3120,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\tan\\placeholder": {
                    pos: {
                        x: 3181,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\csc\\placeholder": {
                    pos: {
                        x: 3242,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\sec\\placeholder": {
                    pos: {
                        x: 3303,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\cot\\placeholder": {
                    pos: {
                        x: 3364,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\sin\\theta": {
                    pos: {
                        x: 3425,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\cos{2x}": {
                    pos: {
                        x: 3486,
                        y: 0
                    },
                    size: {
                        width: 56,
                        height: 75
                    }
                },
                "\\tan\\theta=\\frac {\\sin\\theta}{\\cos\\theta}": {
                    pos: {
                        x: 3547,
                        y: 0
                    },
                    size: {
                        width: 137,
                        height: 75
                    }
                }
            };
        }
    };

//src/ui/toolbar-ele-list.bak.js
    /*!
     * toolbar元素列表定义
     */
    _p[34] = {
        value: function(require) {
            var UI_ELE_TYPE = _p.r(41), BOX_TYPE = _p.r(40), kity = _p.r(20);
            var config = [ {
                type: UI_ELE_TYPE.DRAPDOWN_BOX,
                options: {
                    button: {
                        label: "预设<br/>",
                        className: "yushe-btn",
                        icon: "button/fx.png",
                        iconSize: {
                            w: 40
                        }
                    },
                    box: {
                        width: 367,
                        group: [ {
                            title: "预设公式",
                            items: [ {
                                title: "预设公式",
                                content: [ {
                                    label: "二次公式",
                                    item: {
                                        show: "ys/1.png",
                                        val: "x=\\frac {-b\\pm\\sqrt {b^2-4ac}}{2a}"
                                    }
                                }, {
                                    label: "二项式定理",
                                    item: {
                                        show: "ys/2.png",
                                        val: "{\\left(x+a\\right)}^2=\\sum^n_{k=0}{\\left(^n_k\\right)x^ka^{n-k}}"
                                    }
                                }, {
                                    label: "勾股定理",
                                    item: {
                                        show: "ys/3.png",
                                        val: "a^2+b^2=c^2"
                                    }
                                } ]
                            } ]
                        } ]
                    }
                }
            }, {
                type: UI_ELE_TYPE.DELIMITER
            }, {
                type: UI_ELE_TYPE.AREA,
                options: {
                    box: {
                        fixOffset: true,
                        width: 527,
                        type: BOX_TYPE.OVERLAP,
                        group: [ {
                            title: "基础数学",
                            items: []
                        }, {
                            title: "希腊字母",
                            items: []
                        }, {
                            title: "求反关系运算符",
                            items: []
                        }, {
                            title: "字母类符号",
                            items: []
                        }, {
                            title: "箭头",
                            items: []
                        }, {
                            title: "手写体",
                            items: []
                        } ]
                    }
                }
            }, {
                type: UI_ELE_TYPE.DELIMITER
            }, {
                type: UI_ELE_TYPE.DRAPDOWN_BOX,
                options: {
                    button: {
                        label: "分数<br/>",
                        icon: "button/frac.png"
                    },
                    box: {
                        width: 332,
                        group: [ {
                            title: "分数",
                            items: [ {
                                title: "分数",
                                content: [ {
                                    item: {
                                        show: "frac/1.png",
                                        val: "\\frac \\placeholder\\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "frac/2.png",
                                        val: "{\\placeholder/\\placeholder}"
                                    }
                                } ]
                            }, {
                                title: "常用分数",
                                content: [ {
                                    item: {
                                        show: "frac/c1.png",
                                        val: "\\frac {dy}{dx}"
                                    }
                                }, {
                                    item: {
                                        show: "frac/c2.png",
                                        val: "\\frac {\\Delta y}{\\Delta x}"
                                    }
                                }, {
                                    item: {
                                        show: "frac/c4.png",
                                        val: "\\frac {\\delta y}{\\delta x}"
                                    }
                                }, {
                                    item: {
                                        show: "frac/c5.png",
                                        val: "\\frac \\pi 2"
                                    }
                                } ]
                            } ]
                        } ]
                    }
                }
            }, {
                type: UI_ELE_TYPE.DRAPDOWN_BOX,
                options: {
                    button: {
                        label: "上下标<br/>",
                        icon: "button/script.png"
                    },
                    box: {
                        width: 332,
                        group: [ {
                            title: "上标和下标",
                            items: [ {
                                title: "上标和下标",
                                content: [ {
                                    item: {
                                        show: "script/1.png",
                                        val: "\\placeholder^\\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "script/2.png",
                                        val: "\\placeholder_\\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "script/3.png",
                                        val: "\\placeholder^\\placeholder_\\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "script/4.png",
                                        val: "{^\\placeholder_\\placeholder\\placeholder}"
                                    }
                                } ]
                            }, {
                                title: "常用的上标和下标",
                                content: [ {
                                    item: {
                                        show: "script/c1.png",
                                        val: "e^{-i\\omega t}"
                                    }
                                }, {
                                    item: {
                                        show: "script/c2.png",
                                        val: "x^2"
                                    }
                                }, {
                                    item: {
                                        show: "script/c3.png",
                                        val: "{}^n_1Y"
                                    }
                                } ]
                            } ]
                        } ]
                    }
                }
            }, {
                type: UI_ELE_TYPE.DRAPDOWN_BOX,
                options: {
                    button: {
                        label: "根式<br/>",
                        icon: "button/sqrt.png"
                    },
                    box: {
                        width: 342,
                        group: [ {
                            title: "根式",
                            items: [ {
                                title: "根式",
                                content: [ {
                                    item: {
                                        show: "sqrt/1.png",
                                        val: "\\sqrt \\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "sqrt/2.png",
                                        val: "\\sqrt [\\placeholder] \\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "sqrt/3.png",
                                        val: "\\sqrt [2] \\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "sqrt/4.png",
                                        val: "\\sqrt [3] \\placeholder"
                                    }
                                } ]
                            }, {
                                title: "常用根式",
                                content: [ {
                                    item: {
                                        show: "sqrt/c1.png",
                                        val: "\\frac {-b\\pm\\sqrt{b^2-4ac}}{2a}"
                                    }
                                }, {
                                    item: {
                                        show: "sqrt/c2.png",
                                        val: "\\sqrt {a^2+b^2}"
                                    }
                                } ]
                            } ]
                        } ]
                    }
                }
            }, {
                type: UI_ELE_TYPE.DRAPDOWN_BOX,
                options: {
                    button: {
                        label: "积分<br/>",
                        icon: "button/int.png"
                    },
                    box: {
                        width: 332,
                        group: [ {
                            title: "积分",
                            items: [ {
                                title: "积分",
                                content: [ {
                                    item: {
                                        show: "int/1.png",
                                        val: "\\int \\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "int/2.png",
                                        val: "\\int^\\placeholder_\\placeholder\\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "int/3.png",
                                        val: "\\iint\\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "int/4.png",
                                        val: "\\iint^\\placeholder_\\placeholder\\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "int/5.png",
                                        val: "\\iiint\\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "int/6.png",
                                        val: "\\iiint^\\placeholder_\\placeholder\\placeholder"
                                    }
                                } ]
                            } ]
                        } ]
                    }
                }
            }, {
                type: UI_ELE_TYPE.DRAPDOWN_BOX,
                options: {
                    button: {
                        label: "大型<br/>运算符",
                        icon: "button/sum.png"
                    },
                    box: {
                        width: 332,
                        group: [ {
                            title: "求和",
                            items: [ {
                                title: "求和",
                                content: [ {
                                    item: {
                                        show: "large/1.png",
                                        val: "\\sum\\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "large/2.png",
                                        val: "\\sum^\\placeholder_\\placeholder\\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "large/3.png",
                                        val: "\\sum_\\placeholder\\placeholder"
                                    }
                                } ]
                            } ]
                        } ]
                    }
                }
            }, {
                type: UI_ELE_TYPE.DRAPDOWN_BOX,
                options: {
                    button: {
                        label: "括号<br/>",
                        icon: "button/brackets.png"
                    },
                    box: {
                        width: 332,
                        group: [ {
                            title: "方括号",
                            items: [ {
                                title: "方括号",
                                content: [ {
                                    item: {
                                        show: "brackets/1.png",
                                        val: "\\left(\\placeholder\\right)"
                                    }
                                }, {
                                    item: {
                                        show: "brackets/2.png",
                                        val: "\\left[\\placeholder\\right]"
                                    }
                                }, {
                                    item: {
                                        show: "brackets/3.png",
                                        val: "\\left\\{\\placeholder\\right\\}"
                                    }
                                }, {
                                    item: {
                                        show: "brackets/4.png",
                                        val: "\\left|\\placeholder\\right|"
                                    }
                                } ]
                            } ]
                        } ]
                    }
                }
            }, {
                type: UI_ELE_TYPE.DRAPDOWN_BOX,
                options: {
                    button: {
                        label: "函数<br/>",
                        icon: "button/sin.png"
                    },
                    box: {
                        width: 340,
                        group: [ {
                            title: "函数",
                            items: [ {
                                title: "三角函数",
                                content: [ {
                                    item: {
                                        show: "func/1.png",
                                        val: "\\sin\\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "func/2.png",
                                        val: "\\cos\\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "func/3.png",
                                        val: "\\tan\\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "func/4.png",
                                        val: "\\csc\\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "func/5.png",
                                        val: "\\sec\\placeholder"
                                    }
                                }, {
                                    item: {
                                        show: "func/6.png",
                                        val: "\\cot\\placeholder"
                                    }
                                } ]
                            }, {
                                title: "常用函数",
                                content: [ {
                                    item: {
                                        show: "func/c1.png",
                                        val: "\\sin\\theta"
                                    }
                                }, {
                                    item: {
                                        show: "func/c2.png",
                                        val: "\\sin{2x}"
                                    }
                                }, {
                                    item: {
                                        show: "func/c3.png",
                                        val: "\\tan\\theta=\\frac {\\sin\\theta}{\\cos\\theta}"
                                    }
                                } ]
                            } ]
                        } ]
                    }
                }
            } ];
            // 初始化基础数学
            (function() {
                var list = [ "pm", "infty", {
                    key: "=",
                    img: "eq"
                }, "sim", "times", "div", {
                    key: "!",
                    img: "tanhao"
                }, {
                    key: "<",
                    img: "lt"
                }, "ll", {
                    key: ">",
                    img: "gt"
                }, "gg", "leq", "geq", "mp", "cong", "equiv", "propto", "approx", "forall", "partial", "surd", "cup", "cap", "varnothing", {
                    key: "%",
                    img: "baifenhao"
                }, "circ", "exists", /*"nexists",*/ "in", "ni", "gets", "uparrow", "to", "downarrow", "leftrightarrow", "therefore", "because", {
                    key: "+",
                    img: "plus"
                }, {
                    key: "-",
                    img: "minus"
                }, "neg", "ast", "cdot", "vdots", "ddots", "aleph", /*"beth", */"blacksquare" ], configList = config[2].options.box.group[0].items;
                configList.push({
                    title: "基础数学",
                    content: getContents({
                        path: "char/math/",
                        values: list
                    })
                });
            })();
            // 初始化希腊字符配置
            (function() {
                var greekList = [ {
                    title: "小写",
                    values: [ "alpha", "beta", "gamma", "delta", "epsilon", "zeta", "eta", "theta", "iota", "kappa", "lambda", "mu", "nu", "xi", "omicron", "pi", "rho", "sigma", "tau", "upsilon", "phi", "chi", "psi", "omega" ]
                }, {
                    title: "大写",
                    values: [ "Alpha", "Beta", "Gamma", "Delta", "Epsilon", "Zeta", "Eta", "Theta", "Iota", "Kappa", "Lambda", "Mu", "Nu", "Xi", "Omicron", "Pi", "Rho", "Sigma", "Tau", "Upsilon", "Phi", "Chi", "Psi", "Omega" ]
                }, {
                    title: "变体",
                    values: [ "digamma", "varepsilon", "varkappa", "varphi", "varpi", "varrho", "varsigma", "vartheta" ]
                } ], greekConfigList = config[2].options.box.group[1].items;
                // 小写处理
                greekConfigList.push({
                    title: greekList[0].title,
                    content: getContents({
                        path: "char/greek/lower/",
                        values: greekList[0].values
                    })
                });
                // 大写处理
                greekConfigList.push({
                    title: greekList[1].title,
                    content: getContents({
                        path: "char/greek/upper/",
                        values: greekList[1].values
                    })
                });
                // 变体处理
                greekConfigList.push({
                    title: greekList[2].title,
                    content: getContents({
                        path: "char/greek/misc/",
                        values: greekList[2].values
                    })
                });
            })();
            // 初始化求反运算符
            (function() {
                var greekList = [ {
                    title: "求反关系运算符",
                    values: [ "neq", "nless", "ngtr", "nleq", "ngeq", "nsim", "lneqq", "gneqq", "nprec", "nsucc", "notin", "nsubseteq", "nsupseteq", "subsetneq", "supsetneq", "lnsim", "gnsim", "precnsim", "succnsim", "ntriangleleft", "ntriangleright", "ntrianglelefteq", "ntrianglerighteq", "nmid", "nparallel", "nvdash", {
                        key: "\\nVdash",
                        img: "nvdash-1"
                    }, {
                        key: "\\nvDash",
                        img: "nvdash-2"
                    }, {
                        key: "\\nVDash",
                        img: "nvdash-3"
                    }/*, "nexists"*/ ]
                } ], greekConfigList = config[2].options.box.group[2].items;
                greekConfigList.push({
                    title: greekList[0].title,
                    content: getContents({
                        path: "char/not/",
                        values: greekList[0].values
                    })
                });
            })();
            // 初始字母类符号
            (function() {
                var list = [ "aleph", /*"beth",*/ "daleth", "gimel", "complement", "ell", "eth", "hbar", "hslash", "mho", "partial", "wp", "circledS", "Bbbk", "Finv", "Game", "Im", "Re" ], configList = config[2].options.box.group[3].items;
                configList.push({
                    title: "字母类符号",
                    content: getContents({
                        path: "alphabetic/",
                        values: list
                    })
                });
            })();
            (function() {
                var list = [ "gets", "to", "uparrow", "downarrow", "leftrightarrow", "updownarrow", {
                    key: "\\Leftarrow",
                    img: "u-leftarrow"
                }, {
                    key: "\\Rightarrow",
                    img: "u-rightarrow"
                }, {
                    key: "\\Uparrow",
                    img: "u-uparrow"
                }, {
                    key: "\\Downarrow",
                    img: "u-downarrow"
                }, {
                    key: "\\Leftrightarrow",
                    img: "u-leftrightarrow"
                }, {
                    key: "\\Updownarrow",
                    img: "u-updownarrow"
                }, "longleftarrow", "longrightarrow", "longleftrightarrow", {
                    key: "\\Longleftarrow",
                    img: "u-longleftarrow"
                }, {
                    key: "\\Longrightarrow",
                    img: "u-longrightarrow"
                }, {
                    key: "\\Longleftrightarrow",
                    img: "u-longleftrightarrow"
                }, "nearrow", "nwarrow", "searrow", "swarrow", "nleftarrow", "nrightarrow", {
                    key: "\\nLeftarrow",
                    img: "u-nleftarrow"
                }, {
                    key: "\\nRightarrow",
                    img: "u-nrightarrow"
                }, {
                    key: "\\nLeftrightarrow",
                    img: "u-nleftrightarrow"
                }, "leftharpoonup", "leftharpoondown", "rightharpoonup", "rightharpoondown", "upharpoonleft", "upharpoonright", "downharpoonleft", "downharpoonright", "leftrightharpoons", "rightleftharpoons", "leftleftarrows", "rightrightarrows", "upuparrows", "downdownarrows", "leftrightarrows", "rightleftarrows", "looparrowleft", "looparrowright", "leftarrowtail", "rightarrowtail", {
                    key: "\\Lsh",
                    img: "u-lsh"
                }, {
                    key: "\\Rsh",
                    img: "u-rsh"
                }, {
                    key: "\\Lleftarrow",
                    img: "u-lleftarrow"
                }, {
                    key: "\\Rrightarrow",
                    img: "u-rrightarrow"
                }, "curvearrowleft", "curvearrowright", "circlearrowleft", "circlearrowright", "multimap", "leftrightsquigarrow", "twoheadleftarrow", "twoheadrightarrow", "rightsquigarrow" ], configList = config[2].options.box.group[4].items;
                configList.push({
                    title: "箭头",
                    content: getContents({
                        path: "arrow/",
                        values: list
                    })
                });
            })();
            (function() {
                var list = [ {
                    title: "手写体",
                    values: [ "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z" ]
                }, {
                    title: "花体",
                    values: [ "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z" ]
                }, {
                    title: "双线",
                    values: [ "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z" ]
                }, {
                    title: "罗马",
                    values: [ "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z" ]
                } ], configList = config[2].options.box.group[5].items;
                kity.Utils.each(list[0].values, function(item, index) {
                    list[0].values[index] = {
                        key: "\\mathcal{" + item + "}",
                        img: item.toLowerCase()
                    };
                });
                kity.Utils.each(list[1].values, function(item, index) {
                    list[1].values[index] = {
                        key: "\\mathfrak{" + item + "}",
                        img: item.replace(/[A-Z]/, function(match) {
                            return "u" + match.toLowerCase();
                        })
                    };
                });
                kity.Utils.each(list[2].values, function(item, index) {
                    list[2].values[index] = {
                        key: "\\mathbb{" + item + "}",
                        img: item.toLowerCase()
                    };
                });
                kity.Utils.each(list[3].values, function(item, index) {
                    list[3].values[index] = {
                        key: "\\mathrm{" + item + "}",
                        img: item.replace(/[A-Z]/, function(match) {
                            return "u" + match.toLowerCase();
                        })
                    };
                });
                // 手写体
                configList.push({
                    title: list[0].title,
                    content: getContents({
                        path: "char/cal/",
                        values: list[0].values
                    })
                });
                configList.push({
                    title: list[1].title,
                    content: getContents({
                        path: "char/frak/",
                        values: list[1].values
                    })
                });
                configList.push({
                    title: list[2].title,
                    content: getContents({
                        path: "char/bb/",
                        values: list[2].values
                    })
                });
                configList.push({
                    title: list[3].title,
                    content: getContents({
                        path: "char/rm/",
                        values: list[3].values
                    })
                });
            })();
            function getContents(data) {
                var result = [], path = data.path, values = data.values;
                kity.Utils.each(values, function(value) {
                    var img = value, val = value;
                    if (typeof value !== "string") {
                        img = value.img;
                        val = value.key;
                    } else {
                        val = "\\" + value;
                    }
                    result.push({
                        item: {
                            show: "" + path + img.toLowerCase() + ".png",
                            val: val
                        }
                    });
                });
                return result;
            }
            window.iconConfig = config;
            alert(1)
            return config;
        }
    };

//src/ui/toolbar-ele-list.js
    /*!
     * toolbar元素列表定义
     */
    _p[35] = {
        value: function(require) {
            var UI_ELE_TYPE = _p.r(41), BOX_TYPE = _p.r(40), CHAR_POSITION = _p.r(30), OTHER_POSITION = _p.r(33), kity = _p.r(20);
            var config = [ {
                type: UI_ELE_TYPE.DRAPDOWN_BOX,
                options: {
                    button: {
                        label: "预设<br/>",
                        className: "yushe-btn",
                        icon: {
                            src: "btn.png",
                            x: 0,
                            y: 0
                        },
                        iconSize: {
                            w: 40
                        }
                    },
                    box: {
                        width: 367,
                        group: [ {
                            title: "预设公式",
                            items: [ {
                                title: "预设公式",
                                content: [ {
                                    label: "二次公式",
                                    item: {
                                        val: "x=\\frac {-b\\pm\\sqrt {b^2-4ac}}{2a}"
                                    }
                                }, {
                                    label: "二项式定理",
                                    item: {
                                        val: "{\\left(x+a\\right)}^2=\\sum^n_{k=0}{\\left(^n_k\\right)x^ka^{n-k}}"
                                    }
                                }, {
                                    label: "勾股定理",
                                    item: {
                                        val: "a^2+b^2=c^2"
                                    }
                                } ]
                            } ]
                        } ]
                    }
                }
            }, {
                type: UI_ELE_TYPE.DELIMITER
            }, {
                type: UI_ELE_TYPE.AREA,
                options: {
                    box: {
                        fixOffset: true,
                        width: 527,
                        type: BOX_TYPE.OVERLAP,
                        group: [ {
                            title: "基础数学",
                            items: []
                        }, {
                            title: "希腊字母",
                            items: []
                        }, {
                            title: "求反关系运算符",
                            items: []
                        }, {
                            title: "字母类符号",
                            items: []
                        }, {
                            title: "箭头",
                            items: []
                        }, {
                            title: "手写体",
                            items: []
                        } ]
                    }
                }
            }, {
                type: UI_ELE_TYPE.DELIMITER
            }, {
                type: UI_ELE_TYPE.DRAPDOWN_BOX,
                options: {
                    button: {
                        label: "分数<br/>",
                        icon: {
                            src: "btn.png",
                            x: 45,
                            y: 0
                        }
                    },
                    box: {
                        width: 332,
                        group: [ {
                            title: "分数",
                            items: [ {
                                title: "分数",
                                content: [ {
                                    item: {
                                        val: "\\frac \\placeholder\\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "{\\placeholder/\\placeholder}"
                                    }
                                } ]
                            }, {
                                title: "常用分数",
                                content: [ {
                                    item: {
                                        val: "\\frac {dy}{dx}"
                                    }
                                }, {
                                    item: {
                                        val: "\\frac {\\Delta y}{\\Delta x}"
                                    }
                                }, {
                                    item: {
                                        val: "\\frac {\\delta y}{\\delta x}"
                                    }
                                }, {
                                    item: {
                                        val: "\\frac \\pi 2"
                                    }
                                } ]
                            } ]
                        } ]
                    }
                }
            }, {
                type: UI_ELE_TYPE.DRAPDOWN_BOX,
                options: {
                    button: {
                        label: "上下标<br/>",
                        icon: {
                            src: "btn.png",
                            x: 82,
                            y: 0
                        }
                    },
                    box: {
                        width: 332,
                        group: [ {
                            title: "上标和下标",
                            items: [ {
                                title: "上标和下标",
                                content: [ {
                                    item: {
                                        val: "\\placeholder^\\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\placeholder_\\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\placeholder^\\placeholder_\\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "{^\\placeholder_\\placeholder\\placeholder}"
                                    }
                                } ]
                            }, {
                                title: "常用的上标和下标",
                                content: [ {
                                    item: {
                                        val: "e^{-i\\omega t}"
                                    }
                                }, {
                                    item: {
                                        val: "x^2"
                                    }
                                }, {
                                    item: {
                                        val: "{}^n_1Y"
                                    }
                                } ]
                            } ]
                        } ]
                    }
                }
            }, {
                type: UI_ELE_TYPE.DRAPDOWN_BOX,
                options: {
                    button: {
                        label: "根式<br/>",
                        icon: {
                            src: "btn.png",
                            x: 119,
                            y: 0
                        }
                    },
                    box: {
                        width: 342,
                        group: [ {
                            title: "根式",
                            items: [ {
                                title: "根式",
                                content: [ {
                                    item: {
                                        val: "\\sqrt \\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\sqrt [\\placeholder] \\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\sqrt [2] \\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\sqrt [3] \\placeholder"
                                    }
                                } ]
                            }, {
                                title: "常用根式",
                                content: [ {
                                    item: {
                                        val: "\\frac {-b\\pm\\sqrt{b^2-4ac}}{2a}"
                                    }
                                }, {
                                    item: {
                                        val: "\\sqrt {a^2+b^2}"
                                    }
                                } ]
                            } ]
                        } ]
                    }
                }
            }, {
                type: UI_ELE_TYPE.DRAPDOWN_BOX,
                options: {
                    button: {
                        label: "积分<br/>",
                        icon: {
                            src: "btn.png",
                            x: 156,
                            y: 0
                        }
                    },
                    box: {
                        width: 332,
                        group: [ {
                            title: "积分",
                            items: [ {
                                title: "积分",
                                content: [ {
                                    item: {
                                        val: "\\int \\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\int^\\placeholder_\\placeholder\\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\iint\\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\iint^\\placeholder_\\placeholder\\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\iiint\\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\iiint^\\placeholder_\\placeholder\\placeholder"
                                    }
                                } ]
                            } ]
                        } ]
                    }
                }
            }, {
                type: UI_ELE_TYPE.DRAPDOWN_BOX,
                options: {
                    button: {
                        label: "大型<br/>运算符",
                        icon: {
                            src: "btn.png",
                            x: 193,
                            y: 0
                        }
                    },
                    box: {
                        width: 332,
                        group: [ {
                            title: "求和",
                            items: [ {
                                title: "求和",
                                content: [ {
                                    item: {
                                        val: "\\sum\\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\sum^\\placeholder_\\placeholder\\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\sum_\\placeholder\\placeholder"
                                    }
                                } ]
                            } ]
                        } ]
                    }
                }
            }, {
                type: UI_ELE_TYPE.DRAPDOWN_BOX,
                options: {
                    button: {
                        label: "括号<br/>",
                        icon: {
                            src: "btn.png",
                            x: 230,
                            y: 0
                        }
                    },
                    box: {
                        width: 332,
                        group: [ {
                            title: "方括号",
                            items: [ {
                                title: "方括号",
                                content: [ {
                                    item: {
                                        val: "\\left(\\placeholder\\right)"
                                    }
                                }, {
                                    item: {
                                        val: "\\left[\\placeholder\\right]"
                                    }
                                }, {
                                    item: {
                                        val: "\\left\\{\\placeholder\\right\\}"
                                    }
                                }, {
                                    item: {
                                        val: "\\left|\\placeholder\\right|"
                                    }
                                } ]
                            } ]
                        } ]
                    }
                }
            }, {
                type: UI_ELE_TYPE.DRAPDOWN_BOX,
                options: {
                    button: {
                        label: "函数<br/>",
                        icon: {
                            src: "btn.png",
                            x: 267,
                            y: 0
                        }
                    },
                    box: {
                        width: 340,
                        group: [ {
                            title: "函数",
                            items: [ {
                                title: "三角函数",
                                content: [ {
                                    item: {
                                        val: "\\sin\\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\cos\\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\tan\\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\csc\\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\sec\\placeholder"
                                    }
                                }, {
                                    item: {
                                        val: "\\cot\\placeholder"
                                    }
                                } ]
                            }, {
                                title: "常用函数",
                                content: [ {
                                    item: {
                                        val: "\\sin\\theta"
                                    }
                                }, {
                                    item: {
                                        val: "\\cos{2x}"
                                    }
                                }, {
                                    item: {
                                        val: "\\tan\\theta=\\frac {\\sin\\theta}{\\cos\\theta}"
                                    }
                                } ]
                            } ]
                        } ]
                    }
                }
            } ];
            //--------------------------------------------- 初始化特殊字符区域以外的配置项
            (function() {
                var tmp = [], otherImageSrc = "other.png", currentConf = [];
                kity.Utils.each(config, function(conf) {
                    if (conf.type === UI_ELE_TYPE.DELIMITER) {
                        return;
                    }
                    conf = conf.options.box.group;
                    tmp = tmp.concat(conf);
                });
                kity.Utils.each(tmp, function(conf) {
                    conf = conf.items;
                    for (var i = 0, len = conf.length; i < len; i++) {
                        currentConf = currentConf.concat(conf[i].content);
                    }
                });
                // 添加定位信息
                kity.Utils.each(currentConf, function(conf) {
                    var data = OTHER_POSITION[conf.item.val];
                    if (!data) {
                        return;
                    }
                    conf.item.img = otherImageSrc;
                    conf.item.pos = data.pos;
                    conf.item.size = data.size;
                });
            })();
            //--------------------------------------------- 初始化特殊字符区域
            // 基础数学
            (function() {
                var list = [ "pm", "infty", "=", "sim", "times", "div", "!", "<", "ll", ">", "gg", "leq", "geq", "mp", "cong", "equiv", "propto", "approx", "forall", "partial", "surd", "cup", "cap", "varnothing", "%", "circ", "exists", /*"nexists",*/ "in", "ni", "gets", "uparrow", "to", "downarrow", "leftrightarrow", "therefore", "because", "+", "-", "neg", "ast", "cdot", "vdots", /* "ddots",*/ "aleph", /*"beth",*/ "blacksquare" ], configList = config[2].options.box.group[0].items;
                configList.push({
                    title: "基础数学",
                    content: getIconContents(list, "char.png")
                });
            })();
            // 希腊字符配置
            (function() {
                var greekList = [ {
                    title: "小写",
                    values: [ "alpha", "beta", "gamma", "delta", "epsilon", "zeta", "eta", "theta", "iota", "kappa", "lambda", "mu", "nu", "xi", "omicron", "pi", "rho", "sigma", "tau", "upsilon", "phi", "chi", "psi", "omega" ]
                }, {
                    title: "大写",
                    values: [ "Alpha", "Beta", "Gamma", "Delta", "Epsilon", "Zeta", "Eta", "Theta", "Iota", "Kappa", "Lambda", "Mu", "Nu", "Xi", "Omicron", "Pi", "Rho", "Sigma", "Tau", "Upsilon", "Phi", "Chi", "Psi", "Omega" ]
                }, {
                    title: "变体",
                    values: [ "digamma", "varepsilon", "varkappa", "varphi", "varpi", "varrho", "varsigma", "vartheta" ]
                } ], greekConfigList = config[2].options.box.group[1].items;
                // 小写处理
                greekConfigList.push({
                    title: greekList[0].title,
                    content: getIconContents(greekList[0].values, "char.png")
                });
                // 大写处理
                greekConfigList.push({
                    title: greekList[1].title,
                    content: getIconContents(greekList[1].values, "char.png")
                });
                // 变体处理
                greekConfigList.push({
                    title: greekList[2].title,
                    content: getIconContents(greekList[2].values, "char.png")
                });
            })();
            // 求反运算符
            (function() {
                var greekList = [ {
                    title: "求反关系运算符",
                    values: [ "neq", "nless", "ngtr", "nleq", "ngeq", "nsim", "lneqq", "gneqq", "nprec", "nsucc", "notin", "nsubseteq", "nsupseteq", "subsetneq", "supsetneq", "lnsim", "gnsim", "precnsim", "succnsim", "ntriangleleft", "ntriangleright", "ntrianglelefteq", "ntrianglerighteq", "nmid", "nparallel", "nvdash", "nVdash", "nvDash", "nVDash"/*, "nexists"*/ ]
                } ], greekConfigList = config[2].options.box.group[2].items;
                greekConfigList.push({
                    title: greekList[0].title,
                    content: getIconContents(greekList[0].values, "char.png")
                });
            })();
            // 字母类符号
            (function() {
                var list = [ "aleph", /*"beth",*/ "daleth", "gimel", "complement", "ell", "eth", "hbar", "hslash", "mho", "partial", "wp", "circledS", "Bbbk", "Finv", "Game", "Im", "Re" ], configList = config[2].options.box.group[3].items;
                configList.push({
                    title: "字母类符号",
                    content: getIconContents(list, "char.png")
                });
            })();
            // 化箭头
            (function() {
                var list = [ "gets", "to", "uparrow", "downarrow", "leftrightarrow", "updownarrow", "Leftarrow", "Rightarrow", "Uparrow", "Downarrow", "Leftrightarrow", "Updownarrow", "longleftarrow", "longrightarrow", "longleftrightarrow", "Longleftarrow", "Longrightarrow", "Longleftrightarrow", "nearrow", "nwarrow", "searrow", "swarrow", "nleftarrow", "nrightarrow", "nLeftarrow", "nRightarrow", "nLeftrightarrow", "leftharpoonup", "leftharpoondown", "rightharpoonup", "rightharpoondown", "upharpoonleft", "upharpoonright", "downharpoonleft", "downharpoonright", "leftrightharpoons", "rightleftharpoons", "leftleftarrows", "rightrightarrows", "upuparrows", "downdownarrows", "leftrightarrows", "rightleftarrows", "looparrowleft", "looparrowright", "leftarrowtail", "rightarrowtail", "Lsh", "Rsh", "Lleftarrow", "Rrightarrow", "curvearrowleft", "curvearrowright", "circlearrowleft", "circlearrowright", "multimap", "leftrightsquigarrow", "twoheadleftarrow", "twoheadrightarrow", "rightsquigarrow" ], configList = config[2].options.box.group[4].items;
                configList.push({
                    title: "箭头",
                    content: getIconContents(list, "char.png")
                });
            })();
            // 手写体
            (function() {
                var list = [ {
                    title: "手写体",
                    values: [ "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z" ]
                }, {
                    title: "花体",
                    values: [ "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z" ]
                }, {
                    title: "双线",
                    values: [ "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z" ]
                }, {
                    title: "罗马",
                    values: [ "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z" ]
                } ], configList = config[2].options.box.group[5].items;
                kity.Utils.each(list[0].values, function(item, index) {
                    list[0].values[index] = "mathcal{" + item + "}";
                });
                kity.Utils.each(list[1].values, function(item, index) {
                    list[1].values[index] = "mathfrak{" + item + "}";
                });
                kity.Utils.each(list[2].values, function(item, index) {
                    list[2].values[index] = "mathbb{" + item + "}";
                });
                kity.Utils.each(list[3].values, function(item, index) {
                    list[3].values[index] = "mathrm{" + item + "}";
                });
                // 手写体
                configList.push({
                    title: list[0].title,
                    content: getIconContents(list[0].values, "char.png")
                });
                configList.push({
                    title: list[1].title,
                    content: getIconContents(list[1].values, "char.png")
                });
                configList.push({
                    title: list[2].title,
                    content: getIconContents(list[2].values, "char.png")
                });
                configList.push({
                    title: list[3].title,
                    content: getIconContents(list[3].values, "char.png")
                });
            })();
            function getIconContents(keySet, imgSrc) {
                var result = [];
                kity.Utils.each(keySet, function(key) {
                    if (key.length > 1) {
                        key = "\\" + key;
                    }
                    result.push({
                        key: key,
                        img: imgSrc,
                        pos: CHAR_POSITION[key]
                    });
                });
                return result;
            }
            return config;
        }
    };

//src/ui/toolbar/toolbar.js
    /*!
     * 工具条组件
     */
    _p[36] = {
        value: function(require) {
            var kity = _p.r(20), UiImpl = _p.r(48), $$ = _p.r(47), UI_ELE_TYPE = _p.r(41), Tollbar = kity.createClass("Tollbar", {
                constructor: function(uiComponent, kfEditor, elementList) {
                    this.kfEditor = kfEditor;
                    this.uiComponent = uiComponent;
                    // 工具栏元素定义列表
                    this.elementList = elementList;
                    this.elements = [];
                    this.initToolbarElements();
                    this.initServices();
                    this.initEvent();
                },
                initServices: function() {
                    this.kfEditor.registerService("ui.toolbar.disable", this, {
                        disableToolbar: this.disableToolbar
                    });
                    this.kfEditor.registerService("ui.toolbar.enable", this, {
                        enableToolbar: this.enableToolbar
                    });
                    this.kfEditor.registerService("ui.toolbar.close", this, {
                        closeToolbar: this.closeToolbar
                    });
                },
                initEvent: function() {
                    var _self = this;
                    $$.on(this.uiComponent.toolbarContainer, "mousedown", function(e) {
                        e.preventDefault();
                    });
                    $$.on(this.uiComponent.toolbarContainer, "mousewheel", function(e) {
                        e.preventDefault();
                    });
                    // 通知所有组件关闭
                    $$.on(this.kfEditor.getContainer(), "mousedown", function() {
                        _self.notify("closeAll");
                    });
                    // 订阅数据选择主题
                    $$.subscribe("data.select", function(data) {
                        _self.insertSource(data);
                    });
                },
                insertSource: function(val) {
                    this.kfEditor.requestService("control.insert.string", val);
                },
                disableToolbar: function() {
                    kity.Utils.each(this.elements, function(ele) {
                        ele.disable && ele.disable();
                    });
                },
                enableToolbar: function() {
                    kity.Utils.each(this.elements, function(ele) {
                        ele.enable && ele.enable();
                    });
                },
                getContainer: function() {
                    return this.kfEditor.requestService("ui.get.canvas.container");
                },
                closeToolbar: function() {
                    this.closeElement();
                },
                // 接受到关闭通知
                notify: function(type) {
                    switch (type) {
                        // 关闭所有组件
                        case "closeAll":
                        // 关闭其他组件
                        case "closeOther":
                            this.closeElement(arguments[1]);
                            return;
                    }
                },
                closeElement: function(exception) {
                    kity.Utils.each(this.elements, function(ele) {
                        if (ele != exception) {
                            ele.hide && ele.hide();
                        }
                    });
                },
                initToolbarElements: function() {
                    var elements = this.elements, doc = this.uiComponent.toolbarContainer.ownerDocument, _self = this;
                    kity.Utils.each(this.elementList, function(eleInfo, i) {
                        var ele = createElement(eleInfo.type, doc, eleInfo.options);
                        elements.push(ele);
                        _self.appendElement(ele);
                    });
                },
                appendElement: function(uiElement) {
                    uiElement.setToolbar(this);
                    uiElement.attachTo(this.uiComponent.toolbarContainer);
                }
            });
            function createElement(type, doc, options) {
                switch (type) {
                    case UI_ELE_TYPE.DRAPDOWN_BOX:
                        return createDrapdownBox(doc, options);

                    case UI_ELE_TYPE.DELIMITER:
                        return createDelimiter(doc);

                    case UI_ELE_TYPE.AREA:
                        return createArea(doc, options);
                }
            }
            function createDrapdownBox(doc, options) {
                return new UiImpl.DrapdownBox(doc, options);
            }
            function createDelimiter(doc) {
                return new UiImpl.Delimiter(doc);
            }
            function createArea(doc, options) {
                return new UiImpl.Area(doc, options);
            }
            return Tollbar;
        }
    };

//src/ui/ui-impl/area.js
    /*!
     * 特殊字符区域
     */
    _p[37] = {
        value: function(require) {
            var kity = _p.r(20), PREFIX = "kf-editor-ui-", PANEL_HEIGHT = 66, // UiUitls
                $$ = _p.r(47), Box = _p.r(38), Area = kity.createClass("Area", {
                    constructor: function(doc, options) {
                        this.options = options;
                        this.doc = doc;
                        this.toolbar = null;
                        this.disabled = true;
                        this.panelIndex = 0;
                        this.maxPanelIndex = 0;
                        this.currentItemCount = 0;
                        this.lineMaxCount = 9;
                        this.element = this.createArea();
                        this.container = this.createContainer();
                        this.panel = this.createPanel();
                        this.buttonContainer = this.createButtonContainer();
                        this.button = this.createButton();
                        this.mountPoint = this.createMountPoint();
                        this.moveDownButton = this.createMoveDownButton();
                        this.moveUpButton = this.createMoveUpButton();
                        this.boxObject = this.createBox();
                        this.mergeElement();
                        this.mount();
                        this.setListener();
                        this.initEvent();
                    },
                    initEvent: function() {
                        var _self = this;
                        $$.on(this.button, "mousedown", function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            if (e.which !== 1 || _self.disabled) {
                                return;
                            }
                            _self.showMount();
                            _self.toolbar.notify("closeOther", _self);
                        });
                        $$.on(this.moveDownButton, "mousedown", function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            if (e.which !== 1 || _self.disabled) {
                                return;
                            }
                            _self.nextPanel();
                            _self.toolbar.notify("closeOther", _self);
                        });
                        $$.on(this.moveUpButton, "mousedown", function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            if (e.which !== 1 || _self.disabled) {
                                return;
                            }
                            _self.prevPanel();
                            _self.toolbar.notify("closeOther", _self);
                        });
                        $$.delegate(this.container, ".kf-editor-ui-area-item", "mousedown", function(e) {
                            e.preventDefault();
                            if (e.which !== 1 || _self.disabled) {
                                return;
                            }
                            $$.publish("data.select", this.getAttribute("data-value"));
                        });
                        this.boxObject.initEvent();
                    },
                    disable: function() {
                        this.disabled = true;
                        this.boxObject.disable();
                        $$.getClassList(this.element).remove(PREFIX + "enabled");
                    },
                    enable: function() {
                        this.disabled = false;
                        this.boxObject.enable();
                        $$.getClassList(this.element).add(PREFIX + "enabled");
                    },
                    setListener: function() {
                        var _self = this;
                        this.boxObject.setSelectHandler(function(val) {
                            // 发布
                            $$.publish("data.select", val);
                            _self.hide();
                        });
                        // 内容面板切换
                        this.boxObject.setChangeHandler(function(index) {
                            _self.updateContent();
                        });
                    },
                    createArea: function() {
                        var areaNode = $$.ele(this.doc, "div", {
                            className: PREFIX + "area"
                        });
                        if ("width" in this.options) {
                            areaNode.style.width = this.options.width + "px";
                        }
                        return areaNode;
                    },
                    checkMaxPanelIndex: function() {
                        this.maxPanelIndex = Math.ceil(this.currentItemCount / this.lineMaxCount / 2);
                    },
                    updateContent: function() {
                        var items = this.boxObject.getOverlapContent(), count = 0, style = null, lineno = 0, colno = 0, lineMaxCount = this.lineMaxCount, newContent = [];
                        // 清空原有内容
                        this.panel.innerHTML = "";
                        kity.Utils.each(items, function(item) {
                            var contents = item.content;
                            kity.Utils.each(contents, function(currentContent, index) {
                                lineno = Math.floor(count / lineMaxCount);
                                colno = count % lineMaxCount;
                                count++;
                                style = "top: " + (lineno * 33 + 5) + "px; left: " + (colno * 32 + 5) + "px;";
                                newContent.push('<div class="' + PREFIX + 'area-item" data-value="' + currentContent.key + '" style="' + style + '"><div class="' + PREFIX + 'area-item-inner"><div class="' + PREFIX + 'area-item-img" style="background: url(' +kf.toolbarPath+ currentContent.img + ") no-repeat " + -currentContent.pos.x + "px " + -currentContent.pos.y + 'px;"></div></div></div>');
                            });
                        });
                        this.currentItemCount = count;
                        this.panelIndex = 0;
                        this.panel.style.top = 0;
                        this.panel.innerHTML = newContent.join("");
                        this.checkMaxPanelIndex();
                        this.updatePanelButtonState();
                    },
                    // 挂载
                    mount: function() {
                        this.boxObject.mountTo(this.mountPoint);
                    },
                    showMount: function() {
                        this.mountPoint.style.display = "block";
                        this.boxObject.updateSize();
                    },
                    hideMount: function() {
                        this.mountPoint.style.display = "none";
                    },
                    hide: function() {
                        this.hideMount();
                        this.boxObject.hide();
                    },
                    createButton: function() {
                        return $$.ele(this.doc, "div", {
                            className: PREFIX + "area-button"
                        });
                    },
                    createMoveDownButton: function() {
                        return $$.ele(this.doc, "div", {
                            className: PREFIX + "movedown-button",
                            content: ""
                        });
                    },
                    createMoveUpButton: function() {
                        return $$.ele(this.doc, "div", {
                            className: PREFIX + "moveup-button",
                            content: ""
                        });
                    },
                    createMountPoint: function() {
                        return $$.ele(this.doc, "div", {
                            className: PREFIX + "area-mount"
                        });
                    },
                    createBox: function() {
                        return new Box(this.doc, this.options.box);
                    },
                    createContainer: function() {
                        return $$.ele(this.doc, "div", {
                            className: PREFIX + "area-container"
                        });
                    },
                    createPanel: function() {
                        return $$.ele(this.doc, "div", {
                            className: PREFIX + "area-panel"
                        });
                    },
                    createButtonContainer: function() {
                        return $$.ele(this.doc, "div", {
                            className: PREFIX + "area-button-container"
                        });
                    },
                    mergeElement: function() {
                        this.buttonContainer.appendChild(this.moveUpButton);
                        this.buttonContainer.appendChild(this.moveDownButton);
                        this.buttonContainer.appendChild(this.button);
                        this.container.appendChild(this.panel);
                        this.element.appendChild(this.container);
                        this.element.appendChild(this.buttonContainer);
                        this.element.appendChild(this.mountPoint);
                    },
                    disablePanelUp: function() {
                        this.disabledUp = true;
                        $$.getClassList(this.moveUpButton).add("kf-editor-ui-disabled");
                    },
                    enablePanelUp: function() {
                        this.disabledUp = false;
                        $$.getClassList(this.moveUpButton).remove("kf-editor-ui-disabled");
                    },
                    disablePanelDown: function() {
                        this.disabledDown = true;
                        $$.getClassList(this.moveDownButton).add("kf-editor-ui-disabled");
                    },
                    enablePanelDown: function() {
                        this.disabledDown = false;
                        $$.getClassList(this.moveDownButton).remove("kf-editor-ui-disabled");
                    },
                    updatePanelButtonState: function() {
                        if (this.panelIndex === 0) {
                            this.disablePanelUp();
                        } else {
                            this.enablePanelUp();
                        }
                        if (this.panelIndex + 1 >= this.maxPanelIndex) {
                            this.disablePanelDown();
                        } else {
                            this.enablePanelDown();
                        }
                    },
                    nextPanel: function() {
                        if (this.disabledDown) {
                            return;
                        }
                        if (this.panelIndex + 1 >= this.maxPanelIndex) {
                            return;
                        }
                        this.panelIndex++;
                        this.panel.style.top = -this.panelIndex * PANEL_HEIGHT + "px";
                        this.updatePanelButtonState();
                    },
                    prevPanel: function() {
                        if (this.disabledUp) {
                            return;
                        }
                        if (this.panelIndex === 0) {
                            return;
                        }
                        this.panelIndex--;
                        this.panel.style.top = -this.panelIndex * PANEL_HEIGHT + "px";
                        this.updatePanelButtonState();
                    },
                    setToolbar: function(toolbar) {
                        this.toolbar = toolbar;
                        this.boxObject.setToolbar(toolbar);
                    },
                    attachTo: function(container) {
                        container.appendChild(this.element);
                        this.updateContent();
                        this.updatePanelButtonState();
                    }
                });
            return Area;
        }
    };

//src/ui/ui-impl/box.js
    /**
     * Created by hn on 14-3-31.
     */
    _p[38] = {
        value: function(require) {
            var kity = _p.r(20), PREFIX = "kf-editor-ui-", // UiUitls
                $$ = _p.r(47), BOX_TYPE = _p.r(40), ITEM_TYPE = _p.r(42), Button = _p.r(39), List = _p.r(45), SCROLL_STEP = 20, Box = kity.createClass("Box", {
                    constructor: function(doc, options) {
                        this.options = options;
                        this.toolbar = null;
                        this.options.type = this.options.type || BOX_TYPE.DETACHED;
                        this.doc = doc;
                        this.itemPanels = null;
                        this.overlapButtonObject = null;
                        this.overlapIndex = -1;
                        this.element = this.createBox();
                        this.groupContainer = this.createGroupContainer();
                        this.itemGroups = this.createItemGroup();
                        this.mergeElement();
                    },
                    createBox: function() {
                        var boxNode = $$.ele(this.doc, "div", {
                            className: PREFIX + "box"
                        });
                        if ("width" in this.options) {
                            boxNode.style.width = this.options.width + "px";
                        }
                        return boxNode;
                    },
                    setToolbar: function(toolbar) {
                        this.toolbar = toolbar;
                        if (this.overlapButtonObject) {
                            this.overlapButtonObject.setToolbar(toolbar);
                        }
                    },
                    updateSize: function() {
                        var containerBox = $$.getRectBox(this.toolbar.getContainer()), diff = 30, curBox = $$.getRectBox(this.element);
                        if (this.options.type === BOX_TYPE.DETACHED) {
                            if (curBox.bottom <= containerBox.bottom) {
                                this.element.scrollTop = 0;
                                return;
                            }
                            this.element.style.height = curBox.height - (curBox.bottom - containerBox.bottom + diff) + "px";
                        } else {
                            var panel = this.getCurrentItemPanel(), panelRect = null;
                            panel.scrollTop = 0;
                            if (curBox.bottom <= containerBox.bottom) {
                                return;
                            }
                            panelRect = getRectBox(panel);
                            panel.style.height = containerBox.bottom - panelRect.top - diff + "px";
                        }
                    },
                    initEvent: function() {
                        var className = "." + PREFIX + "box-item", _self = this;
                        $$.delegate(this.groupContainer, className, "mousedown", function(e) {
                            e.preventDefault();
                            if (e.which !== 1) {
                                return;
                            }
                            _self.onselectHandler && _self.onselectHandler(this.getAttribute("data-value"));
                        });
                        $$.on(this.element, "mousedown", function(e) {
                            e.stopPropagation();
                            e.preventDefault();
                        });
                        $$.on(this.element, "mousewheel", function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            _self.scroll(e.originalEvent.wheelDelta);
                        });
                    },
                    getNode: function() {
                        return this.element;
                    },
                    setSelectHandler: function(onselectHandler) {
                        this.onselectHandler = onselectHandler;
                    },
                    scroll: function(delta) {
                        // down
                        if (delta < 0) {
                            this.scrollDown();
                        } else {
                            this.scrollUp();
                            this.element.scrollTop -= 20;
                        }
                    },
                    scrollDown: function() {
                        if (this.options.type === BOX_TYPE.DETACHED) {
                            this.element.scrollTop += SCROLL_STEP;
                        } else {
                            this.getCurrentItemPanel().scrollTop += SCROLL_STEP;
                        }
                    },
                    scrollUp: function() {
                        if (this.options.type === BOX_TYPE.DETACHED) {
                            this.element.scrollTop -= SCROLL_STEP;
                        } else {
                            this.getCurrentItemPanel().scrollTop -= SCROLL_STEP;
                        }
                    },
                    setChangeHandler: function(changeHandler) {
                        this.onchangeHandler = changeHandler;
                    },
                    onchangeHandler: function(index) {},
                    createGroupContainer: function() {
                        return $$.ele(this.doc, "div", {
                            className: PREFIX + "box-container"
                        });
                    },
                    getPositionInfo: function() {
                        return $$.getRectBox(this.element);
                    },
                    createItemGroup: function() {
                        var itemGroup = this.createGroup();
                        switch (this.options.type) {
                            case BOX_TYPE.DETACHED:
                                return itemGroup.items[0];

                            case BOX_TYPE.OVERLAP:
                                return this.createOverlapGroup(itemGroup);
                        }
                        return null;
                    },
                    enable: function() {
                        if (this.overlapButtonObject) {
                            this.overlapButtonObject.enable();
                        }
                    },
                    disable: function() {
                        if (this.overlapButtonObject) {
                            this.overlapButtonObject.disable();
                        }
                    },
                    hide: function() {
                        this.overlapButtonObject && this.overlapButtonObject.hideMount();
                    },
                    getOverlapContent: function() {
                        // 只有重叠式才可以获取重叠内容
                        if (this.options.type !== BOX_TYPE.OVERLAP) {
                            return null;
                        }
                        return this.options.group[this.overlapIndex].items;
                    },
                    createOverlapGroup: function(itemGroup) {
                        var classifyList = itemGroup.title, _self = this, overlapContainer = createOverlapContainer(this.doc), overlapButtonObject = createOverlapButton(this.doc, {
                            fixOffset: this.options.fixOffset
                        }), overlapListObject = createOverlapList(this.doc, {
                            width: 150,
                            items: classifyList
                        }), wrapNode = $$.ele(this.doc, "div", {
                            className: PREFIX + "wrap-group"
                        });
                        this.overlapButtonObject = overlapButtonObject;
                        // 组合选择组件
                        overlapButtonObject.mount(overlapListObject);
                        overlapButtonObject.initEvent();
                        overlapListObject.initEvent();
                        // 合并box的内容
                        kity.Utils.each(itemGroup.items, function(itemArr, index) {
                            var itemWrapNode = wrapNode.cloneNode(false);
                            kity.Utils.each(itemArr, function(item) {
                                itemWrapNode.appendChild(item);
                            });
                            itemGroup.items[index] = itemWrapNode;
                        });
                        this.itemPanels = itemGroup.items;
                        // 切换面板处理器
                        overlapListObject.setSelectHandler(function(index, oldIndex) {
                            _self.overlapIndex = index;
                            overlapButtonObject.setLabel(classifyList[index]);
                            overlapButtonObject.hideMount();
                            // 切换内容
                            itemGroup.items[oldIndex].style.display = "none";
                            itemGroup.items[index].style.display = "block";
                            if (index !== oldIndex) {
                                _self.updateSize();
                            }
                            _self.onchangeHandler(index);
                        });
                        overlapContainer.appendChild(overlapButtonObject.getNode());
                        kity.Utils.each(itemGroup.items, function(group, index) {
                            if (index > 0) {
                                group.style.display = "none";
                            }
                            overlapContainer.appendChild(group);
                        });
                        overlapListObject.select(0);
                        return [ overlapContainer ];
                    },
                    getCurrentItemPanel: function() {
                        return this.itemPanels[this.overlapIndex];
                    },
                    // 获取group的list列表, 该类表满足box的group参数格式
                    getGroupList: function() {
                        var lists = [];
                        kity.Utils.each(this.options.group, function(group, index) {
                            lists.push(group.title);
                        });
                        return {
                            width: 150,
                            items: lists
                        };
                    },
                    createGroup: function() {
                        var doc = this.doc, itemGroup = [], result = {
                            title: [],
                            items: []
                        }, groupNode = null, groupTitle = null, itemType = BOX_TYPE.DETACHED === this.options.type ? ITEM_TYPE.BIG : ITEM_TYPE.SMALL, itemContainer = null;
                        groupNode = $$.ele(this.doc, "div", {
                            className: PREFIX + "box-group"
                        });
                        itemContainer = groupNode.cloneNode(false);
                        itemContainer.className = PREFIX + "box-group-item-container";
                        kity.Utils.each(this.options.group, function(group, i) {
                            result.title.push(group.title || "");
                            itemGroup = [];
                            kity.Utils.each(group.items, function(item) {
                                groupNode = groupNode.cloneNode(false);
                                itemContainer = itemContainer.cloneNode(false);
                                groupTitle = $$.ele(doc, "div", {
                                    className: PREFIX + "box-group-title",
                                    content: item.title
                                });
                                groupNode.appendChild(groupTitle);
                                groupNode.appendChild(itemContainer);
                                kity.Utils.each(createItems(doc, item.content, itemType), function(boxItem) {
                                    boxItem.appendTo(itemContainer);
                                });
                                itemGroup.push(groupNode);
                            });
                            result.items.push(itemGroup);
                        });
                        return result;
                    },
                    mergeElement: function() {
                        var groupContainer = this.groupContainer;
                        this.element.appendChild(groupContainer);
                        kity.Utils.each(this.itemGroups, function(group) {
                            groupContainer.appendChild(group);
                        });
                    },
                    mountTo: function(container) {
                        container.appendChild(this.element);
                    },
                    appendTo: function(container) {
                        container.appendChild(this.element);
                    }
                }), BoxItem = kity.createClass("BoxItem", {
                    constructor: function(type, doc, options) {
                        this.type = type;
                        this.doc = doc;
                        this.options = options;
                        this.element = this.createItem();
                        // 项的label是可选的
                        this.labelNode = this.createLabel();
                        this.contentNode = this.createContent();
                        this.mergeElement();
                    },
                    getNode: function() {
                        return this.element;
                    },
                    createItem: function() {
                        var itemNode = $$.ele(this.doc, "div", {
                            className: PREFIX + "box-item"
                        });
                        return itemNode;
                    },
                    createLabel: function() {
                        var labelNode = null;
                        if (!("label" in this.options)) {
                            return;
                        }
                        labelNode = $$.ele(this.doc, "div", {
                            className: PREFIX + "box-item-label",
                            content: this.options.label
                        });
                        return labelNode;
                    },
                    getContent: function() {},
                    createContent: function() {
                        switch (this.type) {
                            case ITEM_TYPE.BIG:
                                return this.createBigContent();

                            case ITEM_TYPE.SMALL:
                                return this.createSmallContent();
                        }
                    },
                    createBigContent: function() {
                        var doc = this.doc, contentNode = $$.ele(doc, "div", {
                            className: PREFIX + "box-item-content"
                        }), cls = PREFIX + "box-item-val", tmpContent = this.options.item, tmpNode = null, styleStr = getStyleByData(tmpContent);
                        tmpNode = $$.ele(doc, "div", {
                            className: cls
                        });
                        tmpNode.innerHTML = '<div class="' + PREFIX + 'item-image" style="' + styleStr + '"></div>';
                        // 附加属性到项的根节点上
                        this.element.setAttribute("data-value", tmpContent.val);
                        contentNode.appendChild(tmpNode);
                        return contentNode;
                    },
                    createSmallContent: function() {
                        var doc = this.doc, contentNode = $$.ele(doc, "div", {
                            className: PREFIX + "box-item-content"
                        }), cls = PREFIX + "box-item-val", tmpContent = this.options, tmpNode = null;
                        tmpNode = $$.ele(doc, "div", {
                            className: cls
                        });
                        tmpNode.style.background = "url( " + kf.toolbarPath + tmpContent.img + " )";
                        tmpNode.style.backgroundPosition = -tmpContent.pos.x + "px " + -tmpContent.pos.y + "px";
                        // 附加属性到项的根节点上
                        this.element.setAttribute("data-value", tmpContent.key);
                        contentNode.appendChild(tmpNode);
                        return contentNode;
                    },
                    mergeElement: function() {
                        if (this.labelNode) {
                            this.element.appendChild(this.labelNode);
                        }
                        this.element.appendChild(this.contentNode);
                    },
                    appendTo: function(container) {
                        container.appendChild(this.element);
                    }
                });
            function createItems(doc, group, type) {
                var items = [];
                kity.Utils.each(group, function(itemVal, i) {
                    items.push(new BoxItem(type, doc, itemVal));
                });
                return items;
            }
            // 为重叠式box创建容器
            function createOverlapContainer(doc) {
                return $$.ele(doc, "div", {
                    className: PREFIX + "overlap-container"
                });
            }
            function createOverlapButton(doc, options) {
                return new Button(doc, {
                    className: "overlap-button",
                    label: "",
                    fixOffset: options.fixOffset
                });
            }
            function createOverlapList(doc, list) {
                return new List(doc, list);
            }
            function getRectBox(node) {
                return node.getBoundingClientRect();
            }
            function getStyleByData(data) {
                // background
                var style = "background: url( " + kf.toolbarPath + data.img + " ) no-repeat ";
                style += -data.pos.x + "px ";
                style += -data.pos.y + "px;";
                // width height
                style += " width: " + data.size.width + "px;";
                style += " height: " + data.size.height + "px;";
                return style;
            }
            return Box;
        }
    };

//src/ui/ui-impl/button.js
    /**
     * Created by hn on 14-3-31.
     */
    _p[39] = {
        value: function(require) {
            var kity = _p.r(20), PREFIX = "kf-editor-ui-", LIST_OFFSET = 7, DEFAULT_OPTIONS = {
                    iconSize: {
                        w: 32,
                        h: 32
                    }
                }, // UiUitls
                $$ = _p.r(47), Button = kity.createClass("Button", {
                    constructor: function(doc, options) {
                        this.options = kity.Utils.extend({}, DEFAULT_OPTIONS, options);
                        // 事件状态， 是否已经初始化
                        this.eventState = false;
                        this.toolbar = null;
                        this.displayState = false;
                        this.fixOffset = options.fixOffset || false;
                        this.doc = doc;
                        this.element = this.createButton();
                        this.disabled = true;
                        // 挂载的对象
                        this.mountElement = null;
                        this.icon = this.createIcon();
                        this.label = this.createLabel();
                        this.sign = this.createSign();
                        this.mountPoint = this.createMountPoint();
                        this.mergeElement();
                    },
                    initEvent: function() {
                        var _self = this;
                        if (this.eventState) {
                            return;
                        }
                        this.eventState = true;
                        $$.on(this.element, "mousedown", function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            if (e.which !== 1) {
                                return;
                            }
                            if (_self.disabled) {
                                return;
                            }
                            _self.toggleSelect();
                            _self.toggleMountElement();
                        });
                    },
                    setToolbar: function(toolbar) {
                        this.toolbar = toolbar;
                    },
                    toggleMountElement: function() {
                        if (this.displayState) {
                            this.hideMount();
                        } else {
                            this.showMount();
                        }
                    },
                    setLabel: function(labelText) {
                        var signText = "";
                        if (this.sign) {
                            signText = '<div class="' + PREFIX + 'button-sign"></div>';
                        }
                        this.label.innerHTML = labelText + signText;
                    },
                    toggleSelect: function() {
                        $$.getClassList(this.element).toggle(PREFIX + "button-in");
                    },
                    unselect: function() {
                        $$.getClassList(this.element).remove(PREFIX + "button-in");
                    },
                    select: function() {
                        $$.getClassList(this.element).add(PREFIX + "button-in");
                    },
                    show: function() {
                        this.select();
                        this.showMount();
                    },
                    hide: function() {
                        this.unselect();
                        this.hideMount();
                    },
                    showMount: function() {
                        this.displayState = true;
                        this.mountPoint.style.display = "block";
                        if (this.fixOffset) {
                            var elementRect = this.element.getBoundingClientRect();
                            this.mountElement.setOffset(elementRect.left + LIST_OFFSET, elementRect.bottom);
                        }
                        var editorContainer = this.toolbar.getContainer(), currentBox = null, containerBox = $$.getRectBox(editorContainer), mountEleBox = this.mountElement.getPositionInfo();
                        // 修正偏移
                        if (mountEleBox.right > containerBox.right) {
                            currentBox = $$.getRectBox(this.element);
                            // 对齐到按钮的右边界
                            this.mountPoint.style.left = currentBox.right - mountEleBox.right - 1 + "px";
                        }
                        this.mountElement.updateSize && this.mountElement.updateSize();
                    },
                    hideMount: function() {
                        this.displayState = false;
                        this.mountPoint.style.display = "none";
                    },
                    getNode: function() {
                        return this.element;
                    },
                    mount: function(element) {
                        this.mountElement = element;
                        element.mountTo(this.mountPoint);
                    },
                    createButton: function() {
                        var buttonNode = $$.ele(this.doc, "div", {
                            className: PREFIX + "button"
                        });
                        // 附加className
                        if (this.options.className) {
                            buttonNode.className += " " + PREFIX + this.options.className;
                        }
                        return buttonNode;
                    },
                    createIcon: function() {
                        if (!this.options.icon) {
                            return null;
                        }
                        var iconNode = $$.ele(this.doc, "div", {
                            className: PREFIX + "button-icon"
                        });
                        if (typeof this.options.icon === "string") {
                            iconNode.style.backgroundImage = "url(" + kf.toolbarPath + this.options.icon + ") no-repeat";
                        } else {
                            iconNode.style.background = getBackgroundStyle(this.options.icon);
                        }
                        if (this.options.iconSize.w) {
                            iconNode.style.width = this.options.iconSize.w + "px";
                        }
                        if (this.options.iconSize.h) {
                            iconNode.style.height = this.options.iconSize.h + "px";
                        }
                        return iconNode;
                    },
                    createLabel: function() {
                        var labelNode = $$.ele(this.doc, "div", {
                            className: PREFIX + "button-label",
                            content: this.options.label
                        });
                        return labelNode;
                    },
                    createSign: function() {
                        if (this.options.sign === false) {
                            return null;
                        }
                        return $$.ele(this.doc, "div", {
                            className: PREFIX + "button-sign"
                        });
                    },
                    createMountPoint: function() {
                        return $$.ele(this.doc, "div", {
                            className: PREFIX + "button-mount-point"
                        });
                    },
                    disable: function() {
                        this.disabled = true;
                        $$.getClassList(this.element).remove(PREFIX + "enabled");
                    },
                    enable: function() {
                        this.disabled = false;
                        $$.getClassList(this.element).add(PREFIX + "enabled");
                    },
                    mergeElement: function() {
                        this.icon && this.element.appendChild(this.icon);
                        this.element.appendChild(this.label);
                        this.sign && this.label.appendChild(this.sign);
                        this.element.appendChild(this.mountPoint);
                    }
                });
            function getBackgroundStyle(data) {
                var style = "url( " + kf.toolbarPath + data.src + " ) no-repeat ";
                style += -data.x + "px ";
                style += -data.y + "px";
                return style;
            }
            return Button;
        }
    };

//src/ui/ui-impl/def/box-type.js
    /*!
     * box类型定义
     */
    _p[40] = {
        value: function(require) {
            return {
                // 分离式
                DETACHED: 1,
                // 重叠式
                OVERLAP: 2
            };
        }
    };

//src/ui/ui-impl/def/ele-type.js
    /*!
     * toolbar元素类型定义
     */
    _p[41] = {
        value: function(require) {
            return {
                DRAPDOWN_BOX: 1,
                AREA: 2,
                DELIMITER: 3
            };
        }
    };

//src/ui/ui-impl/def/item-type.js
    /*!
     * 组元素类型定义
     */
    _p[42] = {
        value: function(require) {
            return {
                BIG: 1,
                SMALL: 2
            };
        }
    };

//src/ui/ui-impl/delimiter.js
    /*!
     * 分割符
     */
    _p[43] = {
        value: function(require) {
            var kity = _p.r(20), PREFIX = "kf-editor-ui-", // UiUitls
                $$ = _p.r(47), Delimiter = kity.createClass("Delimiter", {
                    constructor: function(doc) {
                        this.doc = doc;
                        this.element = this.createDilimiter();
                    },
                    setToolbar: function(toolbar) {},
                    createDilimiter: function() {
                        var dilimiterNode = $$.ele(this.doc, "div", {
                            className: PREFIX + "delimiter"
                        });
                        dilimiterNode.appendChild($$.ele(this.doc, "div", {
                            className: PREFIX + "delimiter-line"
                        }));
                        return dilimiterNode;
                    },
                    attachTo: function(container) {
                        container.appendChild(this.element);
                    }
                });
            return Delimiter;
        }
    };

//src/ui/ui-impl/drapdown-box.js
    /**
     * Created by hn on 14-3-31.
     */
    _p[44] = {
        value: function(require) {
            var kity = _p.r(20), // UiUitls
                $$ = _p.r(47), Button = _p.r(39), Box = _p.r(38), DrapdownBox = kity.createClass("DrapdownBox", {
                    constructor: function(doc, options) {
                        this.options = options;
                        this.toolbar = null;
                        this.doc = doc;
                        this.buttonElement = this.createButton();
                        this.element = this.buttonElement.getNode();
                        this.boxElement = this.createBox();
                        this.buttonElement.mount(this.boxElement);
                        this.initEvent();
                    },
                    initEvent: function() {
                        var _self = this;
                        // 通知工具栏互斥
                        $$.on(this.element, "mousedown", function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            _self.toolbar.notify("closeOther", _self);
                        });
                        this.buttonElement.initEvent();
                        this.boxElement.initEvent();
                        this.boxElement.setSelectHandler(function(val) {
                            // 发布
                            $$.publish("data.select", val);
                            _self.buttonElement.hide();
                        });
                    },
                    disable: function() {
                        this.buttonElement.disable();
                    },
                    enable: function() {
                        this.buttonElement.enable();
                    },
                    setToolbar: function(toolbar) {
                        this.toolbar = toolbar;
                        this.buttonElement.setToolbar(toolbar);
                        this.boxElement.setToolbar(toolbar);
                    },
                    createButton: function() {
                        return new Button(this.doc, this.options.button);
                    },
                    show: function() {
                        this.buttonElement.show();
                    },
                    hide: function() {
                        this.buttonElement.hide();
                    },
                    createBox: function() {
                        return new Box(this.doc, this.options.box);
                    },
                    attachTo: function(container) {
                        container.appendChild(this.element);
                    }
                });
            return DrapdownBox;
        }
    };

//src/ui/ui-impl/list.js
    /**
     * Created by hn on 14-3-31.
     */
    _p[45] = {
        value: function(require) {
            var kity = _p.r(20), PREFIX = "kf-editor-ui-", // UiUitls
                $$ = _p.r(47), List = kity.createClass("List", {
                    constructor: function(doc, options) {
                        this.options = options;
                        this.doc = doc;
                        this.onselectHandler = null;
                        this.currentSelect = -1;
                        this.element = this.createBox();
                        this.itemGroups = this.createItems();
                        this.mergeElement();
                    },
                    // 预定义的方法留空
                    onselectHandler: function(index, oldIndex) {},
                    setSelectHandler: function(selectHandler) {
                        this.onselectHandler = selectHandler;
                    },
                    createBox: function() {
                        var boxNode = $$.ele(this.doc, "div", {
                                className: PREFIX + "list"
                            }), // 创建背景
                            bgNode = $$.ele(this.doc, "div", {
                                className: PREFIX + "list-bg"
                            });
                        if ("width" in this.options) {
                            boxNode.style.width = this.options.width + "px";
                        }
                        boxNode.appendChild(bgNode);
                        return boxNode;
                    },
                    select: function(index) {
                        var oldSelect = this.currentSelect;
                        if (oldSelect === -1) {
                            oldSelect = index;
                        }
                        this.unselect(oldSelect);
                        this.currentSelect = index;
                        $$.getClassList(this.itemGroups.items[index]).add(PREFIX + "list-item-select");
                        this.onselectHandler(index, oldSelect);
                    },
                    unselect: function(index) {
                        $$.getClassList(this.itemGroups.items[index]).remove(PREFIX + "list-item-select");
                    },
                    setOffset: function(x, y) {
                        this.element.style.left = x + "px";
                        this.element.style.top = y + "px";
                    },
                    initEvent: function() {
                        var className = "." + PREFIX + "list-item", _self = this;
                        $$.delegate(this.itemGroups.container, className, "mousedown", function(e) {
                            e.preventDefault();
                            if (e.which !== 1) {
                                return;
                            }
                            _self.select(this.getAttribute("data-index"));
                        });
                        $$.on(this.element, "mousedown", function(e) {
                            e.stopPropagation();
                            e.preventDefault();
                        });
                    },
                    getPositionInfo: function() {
                        return $$.getRectBox(this.element);
                    },
                    createItems: function() {
                        var doc = this.doc, groupNode = null, itemNode = null, iconNode = null, items = [], itemContainer = null;
                        groupNode = $$.ele(this.doc, "div", {
                            className: PREFIX + "list-item"
                        });
                        itemContainer = groupNode.cloneNode(false);
                        itemContainer.className = PREFIX + "list-item-container";
                        kity.Utils.each(this.options.items, function(itemText, i) {
                            itemNode = groupNode.cloneNode(false);
                            iconNode = groupNode.cloneNode(false);
                            iconNode.className = PREFIX + "list-item-icon";
                            itemNode.appendChild(iconNode);
                            itemNode.appendChild($$.ele(doc, "text", itemText));
                            itemNode.setAttribute("data-index", i);
                            items.push(itemNode);
                            itemContainer.appendChild(itemNode);
                        });
                        return {
                            container: itemContainer,
                            items: items
                        };
                    },
                    mergeElement: function() {
                        this.element.appendChild(this.itemGroups.container);
                    },
                    mountTo: function(container) {
                        container.appendChild(this.element);
                    }
                });
            return List;
        }
    };

//src/ui/ui-impl/scrollbar/scrollbar.js
    /*!
     * 滚动条组件
     */
    _p[46] = {
        value: function(require) {
            var kity = _p.r(20), SCROLLBAR_DEF = _p.r(32).scrollbar, SCROLLBAR_CONF = _p.r(29).scrollbar, Utils = _p.r(4), CLASS_PREFIX = "kf-editor-ui-";
            return kity.createClass("Scrollbar", {
                constructor: function(uiComponent, kfEditor) {
                    this.uiComponent = uiComponent;
                    this.kfEditor = kfEditor;
                    this.widgets = null;
                    this.container = this.uiComponent.scrollbarContainer;
                    // 显示状态
                    this.state = false;
                    // 滚动条当前各个状态下的值
                    this.values = {
                        // 滚动条此时实际的偏移值, 计算的时候假定滑块的宽度为0
                        offset: 0,
                        // 滑块此时偏移位置所占轨道的比例, 计算的时候假定滑块的宽度为0
                        left: 0,
                        // 滚动条控制的容器的可见宽度
                        viewWidth: 0,
                        // 滚动条对应的内容实际宽度
                        contentWidth: 0,
                        // 轨道长度
                        trackWidth: 0,
                        // 滑块宽度
                        thumbWidth: 0,
                        // 可滚动的宽度
                        scrollWidth: 0
                    };
                    // 滑块的物理偏移， 不同于values.offset
                    this.thumbLocationX = 0;
                    // 左溢出长度
                    this.leftOverflow = 0;
                    // 右溢出长度
                    this.rightOverflow = 0;
                    // 记录本次和上一次改变内容之间宽度是否变大
                    this.isExpand = true;
                    this.initWidget();
                    this.mountWidget();
                    this.initSize();
                    this.hide();
                    this.initServices();
                    this.initEvent();
                    this.updateHandler = function() {};
                },
                initWidget: function() {
                    var doc = this.container.ownerDocument;
                    this.widgets = {
                        leftButton: createElement(doc, "div", "left-button"),
                        rightButton: createElement(doc, "div", "right-button"),
                        track: createElement(doc, "div", "track"),
                        thumb: createElement(doc, "div", "thumb"),
                        thumbBody: createElement(doc, "div", "thumb-body")
                    };
                },
                initSize: function() {
                    var leftBtnWidth = getRect(this.widgets.leftButton).width, rightBtnWidth = getRect(this.widgets.rightButton).width;
                    this.values.viewWidth = getRect(this.container).width;
                    this.values.trackWidth = this.values.viewWidth - leftBtnWidth - rightBtnWidth;
                    this.widgets.track.style.width = this.values.trackWidth + "px";
                },
                initServices: function() {
                    this.kfEditor.registerService("ui.show.scrollbar", this, {
                        showScrollbar: this.show
                    });
                    this.kfEditor.registerService("ui.hide.scrollbar", this, {
                        hideScrollbar: this.hide
                    });
                    this.kfEditor.registerService("ui.update.scrollbar", this, {
                        updateScrollbar: this.update
                    });
                    this.kfEditor.registerService("ui.set.scrollbar.update.handler", this, {
                        setUpdateHandler: this.setUpdateHandler
                    });
                    this.kfEditor.registerService("ui.relocation.scrollbar", this, {
                        relocation: this.relocation
                    });
                },
                initEvent: function() {
                    preventDefault(this);
                    trackClick(this);
                    thumbHandler(this);
                    btnClick(this);
                },
                mountWidget: function() {
                    var widgets = this.widgets, container = this.container;
                    for (var wgtName in widgets) {
                        if (widgets.hasOwnProperty(wgtName)) {
                            container.appendChild(widgets[wgtName]);
                        }
                    }
                    widgets.thumb.appendChild(widgets.thumbBody);
                    widgets.track.appendChild(widgets.thumb);
                },
                show: function() {
                    this.state = true;
                    this.container.style.display = "block";
                },
                hide: function() {
                    this.state = false;
                    this.container.style.display = "none";
                },
                update: function(contentWidth) {
                    var trackWidth = this.values.trackWidth, thumbWidth = 0;
                    this.isExpand = contentWidth > this.values.contentWidth;
                    this.values.contentWidth = contentWidth;
                    this.values.scrollWidth = contentWidth - this.values.viewWidth;
                    if (trackWidth >= contentWidth) {
                        this.hide();
                        return;
                    }
                    thumbWidth = Math.max(Math.ceil(trackWidth * trackWidth / contentWidth), SCROLLBAR_DEF.thumbMinSize);
                    this.values.thumbWidth = thumbWidth;
                    this.widgets.thumb.style.width = thumbWidth + "px";
                    this.widgets.thumbBody.style.width = thumbWidth - 10 + "px";
                },
                setUpdateHandler: function(updateHandler) {
                    this.updateHandler = updateHandler;
                },
                updateOffset: function(offset) {
                    var values = this.values;
                    values.offset = offset;
                    values.left = offset / values.trackWidth;
                    this.leftOverflow = values.left * (values.contentWidth - values.viewWidth);
                    this.rightOverflow = values.contentWidth - values.viewWidth - this.leftOverflow;
                    this.updateHandler(values.left, values.offset, values);
                },
                relocation: function() {
                    var cursorLocation = this.kfEditor.requestService("control.get.cursor.location"), padding = SCROLLBAR_CONF.padding, contentWidth = this.values.contentWidth, viewWidth = this.values.viewWidth, // 视图左溢出长度
                        viewLeftOverflow = this.values.left * (contentWidth - viewWidth), diff = 0;
                    if (cursorLocation.x < viewLeftOverflow) {
                        if (cursorLocation.x < 0) {
                            cursorLocation.x = 0;
                        }
                        setThumbOffsetByViewOffset(this, cursorLocation.x);
                    } else if (cursorLocation.x + padding > viewLeftOverflow + viewWidth) {
                        cursorLocation.x += padding;
                        if (cursorLocation.x > contentWidth) {
                            cursorLocation.x = contentWidth;
                        }
                        diff = cursorLocation.x - viewWidth;
                        setThumbOffsetByViewOffset(this, diff);
                    } else {
                        if (this.isExpand) {
                            // 根据上一次左溢出值设置滑块位置
                            setThumbByLeftOverflow(this, this.leftOverflow);
                        } else {
                            // 减少左溢出
                            setThumbByLeftOverflow(this, contentWidth - viewWidth - this.rightOverflow);
                        }
                    }
                }
            });
            function createElement(doc, eleName, className) {
                var node = doc.createElement(eleName), str = '<div class="$1"></div><div class="$2"></div>';
                node.className = CLASS_PREFIX + className;
                if (className === "thumb") {
                    className = CLASS_PREFIX + className;
                    node.innerHTML = str.replace("$1", className + "-left").replace("$2", className + "-right");
                }
                return node;
            }
            function getRect(node) {
                return node.getBoundingClientRect();
            }
            // 阻止浏览器在scrollbar上的默认行为
            function preventDefault(container) {
                Utils.addEvent(container, "mousedown", function(e) {
                    e.preventDefault();
                });
            }
            function preventDefault(comp) {
                Utils.addEvent(comp.container, "mousedown", function(e) {
                    e.preventDefault();
                });
            }
            // 轨道点击
            function trackClick(comp) {
                Utils.addEvent(comp.widgets.track, "mousedown", function(e) {
                    trackClickHandler(this, comp, e);
                });
            }
            // 两端按钮点击
            function btnClick(comp) {
                // left
                Utils.addEvent(comp.widgets.leftButton, "mousedown", function() {
                    setThumbOffsetByStep(comp, -SCROLLBAR_CONF.step);
                });
                Utils.addEvent(comp.widgets.rightButton, "mousedown", function() {
                    setThumbOffsetByStep(comp, SCROLLBAR_CONF.step);
                });
            }
            // 滑块处理
            function thumbHandler(comp) {
                var isMoving = false, startPoint = 0, startOffset = 0, trackWidth = comp.values.trackWidth;
                Utils.addEvent(comp.widgets.thumb, "mousedown", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    isMoving = true;
                    startPoint = e.clientX;
                    startOffset = comp.thumbLocationX;
                });
                Utils.addEvent(comp.container.ownerDocument, "mouseup", function() {
                    isMoving = false;
                    startPoint = 0;
                    startOffset = 0;
                });
                Utils.addEvent(comp.container.ownerDocument, "mousemove", function(e) {
                    if (!isMoving) {
                        return;
                    }
                    var distance = e.clientX - startPoint, offset = startOffset + distance, thumbWidth = comp.values.thumbWidth;
                    if (offset < 0) {
                        offset = 0;
                    } else if (offset + thumbWidth > trackWidth) {
                        offset = trackWidth - thumbWidth;
                    }
                    setThumbLocation(comp, offset);
                });
            }
            // 轨道点击处理器
            function trackClickHandler(track, comp, evt) {
                var trackRect = getRect(track), values = comp.values, // 单位偏移值， 一个viewWidth所对应到轨道上后的offset值
                    unitOffset = values.viewWidth / (values.contentWidth - values.viewWidth) * values.trackWidth, // 点击位置在轨道中的偏移
                    clickOffset = evt.clientX - trackRect.left;
                // right click
                if (clickOffset > values.offset) {
                    // 剩余距离已经不足以支撑滚动， 则直接偏移置最大
                    if (values.offset + unitOffset > values.trackWidth) {
                        setThumbOffset(comp, values.trackWidth);
                    } else {
                        setThumbOffset(comp, values.offset + unitOffset);
                    }
                } else {
                    // 剩余距离已经不足以支撑滚动， 则直接把偏移置零
                    if (values.offset - unitOffset < 0) {
                        setThumbOffset(comp, 0);
                    } else {
                        setThumbOffset(comp, values.offset - unitOffset);
                    }
                }
            }
            function setThumbLocation(comp, locationX) {
                // 滑块偏移值
                var values = comp.values, trackPieceWidth = values.trackWidth - values.thumbWidth, offset = Math.floor(locationX / trackPieceWidth * values.trackWidth);
                comp.updateOffset(offset);
                // 更新滑块物理偏移: 定位
                comp.thumbLocationX = locationX;
                comp.widgets.thumb.style.left = locationX + "px";
            }
            // 根据指定的内容视图上移动的步长来改变滚动条的offset值
            function setThumbOffsetByStep(comp, step) {
                var leftOverflow = comp.leftOverflow + step;
                // 修正越界
                if (leftOverflow < 0) {
                    leftOverflow = 0;
                } else if (leftOverflow > comp.values.scrollWidth) {
                    leftOverflow = comp.values.scrollWidth;
                }
                setThumbByLeftOverflow(comp, leftOverflow);
            }
            // 设置偏移值, 会同时更新滑块在显示上的定位
            function setThumbOffset(comp, offset) {
                var values = comp.values, offsetProportion = offset / values.trackWidth, trackPieceWidth = values.trackWidth - values.thumbWidth, thumbLocationX = 0;
                thumbLocationX = Math.floor(offsetProportion * trackPieceWidth);
                if (offset < 0) {
                    offset = 0;
                    thumbLocationX = 0;
                }
                comp.updateOffset(offset);
                // 更新滑块定位
                comp.widgets.thumb.style.left = thumbLocationX + "px";
                comp.thumbLocationX = thumbLocationX;
            }
            /**
             * 根据内容视图上的偏移值设置滑块位置
             */
            function setThumbOffsetByViewOffset(comp, viewOffset) {
                var values = comp.values, offsetProportion = 0, offset = 0;
                // 轨道偏移比例
                offsetProportion = viewOffset / (values.contentWidth - values.viewWidth);
                // 轨道偏移值
                offset = Math.floor(offsetProportion * values.trackWidth);
                setThumbOffset(comp, offset);
            }
            /**
             * 根据左溢出值设置滑块定位
             */
            function setThumbByLeftOverflow(comp, leftViewOverflow) {
                var values = comp.values, overflowProportion = leftViewOverflow / (values.contentWidth - values.viewWidth);
                setThumbOffset(comp, overflowProportion * values.trackWidth);
            }
        }
    };

//src/ui/ui-impl/ui-utils.js
    /**
     * Created by hn on 14-4-1.
     */
    _p[47] = {
        value: function(require) {
            var $ = _p.r(14), kity = _p.r(20), TOPIC_POOL = {};
            var Utils = {
                ele: function(doc, name, options) {
                    var node = null;
                    if (name === "text") {
                        return doc.createTextNode(options);
                    }
                    node = doc.createElement(name);
                    options.className && (node.className = options.className);
                    if (options.content) {
                        node.innerHTML = options.content;
                    }
                    return node;
                },
                getRectBox: function(node) {
                    return node.getBoundingClientRect();
                },
                on: function(target, type, fn) {
                    $(target).on(type, fn);
                    return this;
                },
                delegate: function(target, selector, type, fn) {
                    $(target).delegate(selector, type, fn);
                    return this;
                },
                publish: function(topic, args) {
                    var callbackList = TOPIC_POOL[topic];
                    if (!callbackList) {
                        return;
                    }
                    args = [].slice.call(arguments, 1);
                    kity.Utils.each(callbackList, function(callback) {
                        callback.apply(null, args);
                    });
                },
                subscribe: function(topic, callback) {
                    if (!TOPIC_POOL[topic]) {
                        TOPIC_POOL[topic] = [];
                    }
                    TOPIC_POOL[topic].push(callback);
                },
                getClassList: function(node) {
                    return node.classList || new ClassList(node);
                }
            };
            //注意： 仅保证兼容IE9以上
            function ClassList(node) {
                this.node = node;
                this.classes = node.className.replace(/^\s+|\s+$/g, "").split(/\s+/);
            }
            ClassList.prototype = {
                constructor: ClassList,
                contains: function(className) {
                    return this.classes.indexOf(className) !== -1;
                },
                add: function(className) {
                    if (this.classes.indexOf(className) == -1) {
                        this.classes.push(className);
                    }
                    this._update();
                    return this;
                },
                remove: function(className) {
                    var index = this.classes.indexOf(className);
                    if (index !== -1) {
                        this.classes.splice(index, 1);
                        this._update();
                    }
                    return this;
                },
                toggle: function(className) {
                    var method = this.contains(className) ? "remove" : "add";
                    return this[method](className);
                },
                _update: function() {
                    this.node.className = this.classes.join(" ");
                }
            };
            return Utils;
        }
    };

//src/ui/ui-impl/ui.js
    /**
     * Created by hn on 14-3-31.
     */
    _p[48] = {
        value: function(require) {
            return {
                DrapdownBox: _p.r(44),
                Delimiter: _p.r(43),
                Area: _p.r(37)
            };
        }
    };

//src/ui/ui.js
    /**
     * Created by hn on 14-3-17.
     */
    _p[49] = {
        value: function(require) {
            var kity = _p.r(20), // UiUitls
                $$ = _p.r(47), Utils = _p.r(4), VIEW_STATE = _p.r(32).VIEW_STATE, Scrollbar = _p.r(46), Toolbar = _p.r(36), // 控制组件
                ScrollZoom = _p.r(31), ELEMENT_LIST = _p.r(35), UIComponent = kity.createClass("UIComponent", {
                    constructor: function(kfEditor, options) {
                        var currentDocument = null;
                        this.options = options;
                        this.container = kfEditor.getContainer();
                        currentDocument = this.container.ownerDocument;
                        // ui组件实例集合
                        this.components = {};
                        this.canvasRect = null;
                        this.viewState = VIEW_STATE.NO_OVERFLOW;
                        this.kfEditor = kfEditor;
                        this.toolbarWrap = createToolbarWrap(currentDocument);
                        this.toolbarContainer = createToolbarContainer(currentDocument);
                        this.editArea = createEditArea(currentDocument);
                        this.canvasContainer = createCanvasContainer(currentDocument);
                        this.scrollbarContainer = createScrollbarContainer(currentDocument);
                        this.toolbarWrap.appendChild(this.toolbarContainer);
                        this.container.appendChild(this.toolbarWrap);
                        this.editArea.appendChild(this.canvasContainer);
                        this.container.appendChild(this.editArea);
                        this.container.appendChild(this.scrollbarContainer);
                        this.initComponents();
                        this.initServices();
                        this.initEvent();
                        this.updateContainerSize(this.container, this.toolbarWrap, this.editArea, this.canvasContainer);
                        this.initScrollEvent();
                    },
                    // 组件实例化
                    initComponents: function() {
                        // 工具栏组件
                        this.components.toolbar = new Toolbar(this, this.kfEditor, ELEMENT_LIST);
                        // TODO 禁用缩放, 留待后面再重新开启
                        if (false) {
                            //                if ( this.options.zoom ) {
                            this.components.scrollZoom = new ScrollZoom(this, this.kfEditor, this.canvasContainer, {
                                max: this.options.maxzoom,
                                min: this.options.minzoom
                            });
                        }
                        this.components.scrollbar = new Scrollbar(this, this.kfEditor);
                    },
                    updateContainerSize: function(container, toolbar, editArea) {
                        var containerBox = container.getBoundingClientRect(), toolbarBox = toolbar.getBoundingClientRect();
                        editArea.style.width = containerBox.width + "px";
                        editArea.style.height = containerBox.bottom - toolbarBox.bottom + "px";
                    },
                    // 初始化服务
                    initServices: function() {
                        this.kfEditor.registerService("ui.get.canvas.container", this, {
                            getCanvasContainer: this.getCanvasContainer
                        });
                        this.kfEditor.registerService("ui.update.canvas.view", this, {
                            updateCanvasView: this.updateCanvasView
                        });
                        this.kfEditor.registerService("ui.canvas.container.event", this, {
                            on: this.addEvent,
                            off: this.removeEvent,
                            trigger: this.trigger,
                            fire: this.trigger
                        });
                    },
                    initEvent: function() {},
                    initScrollEvent: function() {
                        var _self = this;
                        this.kfEditor.requestService("ui.set.scrollbar.update.handler", function(proportion, offset, values) {
                            offset = Math.floor(proportion * (values.contentWidth - values.viewWidth));
                            _self.kfEditor.requestService("render.set.canvas.offset", offset);
                        });
                    },
                    getCanvasContainer: function() {
                        return this.canvasContainer;
                    },
                    addEvent: function(type, handler) {
                        Utils.addEvent(this.canvasContainer, type, handler);
                    },
                    removeEvent: function() {},
                    trigger: function(type) {
                        Utils.trigger(this.canvasContainer, type);
                    },
                    // 更新画布视窗， 决定是否出现滚动条
                    updateCanvasView: function() {
                        var canvas = this.kfEditor.requestService("render.get.canvas"), contentContainer = canvas.getContentContainer(), contentRect = null;
                        if (this.canvasRect === null) {
                            // 兼容firfox， 获取容器大小，而不是获取画布大小
                            this.canvasRect = this.canvasContainer.getBoundingClientRect();
                        }
                        contentRect = contentContainer.getRenderBox("paper");
                        if (contentRect.width > this.canvasRect.width) {
                            if (this.viewState === VIEW_STATE.NO_OVERFLOW) {
                                this.toggleViewState();
                                this.kfEditor.requestService("ui.show.scrollbar");
                                this.kfEditor.requestService("render.disable.relocation");
                            }
                            this.kfEditor.requestService("render.relocation");
                            // 更新滚动条， 参数是：滚动条所控制的内容长度
                            this.kfEditor.requestService("ui.update.scrollbar", contentRect.width);
                            this.kfEditor.requestService("ui.relocation.scrollbar");
                        } else {
                            if (this.viewState === VIEW_STATE.OVERFLOW) {
                                this.toggleViewState();
                                this.kfEditor.requestService("ui.hide.scrollbar");
                                this.kfEditor.requestService("render.enable.relocation");
                            }
                            this.kfEditor.requestService("render.relocation");
                        }
                    },
                    toggleViewState: function() {
                        this.viewState = this.viewState === VIEW_STATE.NO_OVERFLOW ? VIEW_STATE.OVERFLOW : VIEW_STATE.NO_OVERFLOW;
                    }
                });
            function createToolbarWrap(doc) {
                return $$.ele(doc, "div", {
                    className: "kf-editor-toolbar"
                });
            }
            function createToolbarContainer(doc) {
                return $$.ele(doc, "div", {
                    className: "kf-editor-inner-toolbar"
                });
            }
            function createEditArea(doc) {
                var container = doc.createElement("div");
                container.className = "kf-editor-edit-area";
                container.style.width = "80%";
                container.style.height = "800px";
                return container;
            }
            function createCanvasContainer(doc) {
                var container = doc.createElement("div");
                container.className = "kf-editor-canvas-container";
                return container;
            }
            function createScrollbarContainer(doc) {
                var container = doc.createElement("div");
                container.className = "kf-editor-edit-scrollbar";
                return container;
            }
            return UIComponent;
        }
    };

//dev-lib/start.js
    /*!
     * 启动模块
     */
    _p[50] = {
        value: function(require) {
            var KFEditor = _p.r(12), Factory = _p.r(13);
            // 注册组件
            KFEditor.registerComponents("ui", _p.r(49));
            KFEditor.registerComponents("parser", _p.r(21));
            KFEditor.registerComponents("render", _p.r(25));
            KFEditor.registerComponents("position", _p.r(23));
            KFEditor.registerComponents("syntax", _p.r(28));
            KFEditor.registerComponents("control", _p.r(5));
            KFEditor.registerComponents("print", _p.r(24));
            kf.EditorFactory = Factory;
        }
    };

    var moduleMapping = {
        "kf.start": 50
    };

    function use(name) {
        _p.r([ moduleMapping[name] ]);
    }
    /**
     * 启动代码
     */

    ( function ( global ) {

        // build环境中才含有use
        try {
            use( 'kf.start' );
        } catch ( e ) {
        }

    } )( this );
})();

