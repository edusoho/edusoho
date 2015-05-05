define(function(require, exports, module) {

    (function($) {

        $.fn.WaterMark = function(options) {
            var settings = $.extend({
                'duringTime': 5 * 60 * 1000,
                'interval': 10 * 60 * 1000,
                'isAlwaysShow': true,
                'xPosition': 'center',
                'yPosition': 'top',
                'isUseRandomPos': false,
                'opacity': 0.8,
                'rotate': 45,
                'contents': ''
            }, options);

            var showTimer;
            var thiz = $(this);
            var minTopOffset = 40;
            var minLeftOffset = 15;
            var topOffset = minTopOffset;
            var leftOffset = minLeftOffset;

            function genereateDiv() {
                var $watermarkDiv = $('<div id="waterMark"></div>');
                $watermarkDiv.css({
                    '-webkit-touch-callout': 'none',
                    '-webkit-user-select': 'none',
                    '-khtml-user-select': 'none',
                    '-moz-user-select': 'none',
                    '-ms-user-select': 'none',
                    'user-select': 'none',
                    'text-align': 'center', 
                    'display': 'none', 
                    'position': 'absolute',
                    'width': 500,
                    'height': 20,
                    'vertical-align': 'middle'
                });
                $watermarkDiv.html(settings.contents);
                console.log($watermarkDiv);
                return $watermarkDiv;
            }

            function alwaysShow() {
                displayWaterMark();
            }

            function displayWaterMark() {
                getOffset();
                $('#waterMark').css({
                    'top': topOffset,
                    'left': leftOffset
                });
                $('#waterMark').show();
            }

            function timeingShow() {
                displayWaterMark();
                showTimer = setInterval(function() {
                    displayWaterMark();
                    setTimeout(function() {
                        $('#waterMark').hide();
                    }, settings.duringTime);

                }, settings.interval);
            }

            function getOffset() {
                if (settings.isUseRandomPos) {
                    setOffsetRandom();
                } else {
                    setOffsetByPosition();
                }
            }

            function setOffsetRandom() {
                var maxTopOffset = (thiz.height() - $('#waterMark').height() - minTopOffset);
                var maxLeftOffset = (thiz.width() - $('#waterMark').width() - minLeftOffset);

                topOffset = Math.random() * maxTopOffset + minTopOffset;
                leftOffset = Math.random() * maxLeftOffset;
            }

            function setOffsetByPosition() {
                if (settings.xPosition == "left") {
                    leftOffset = minLeftOffset;
                }
                if (settings.xPosition == "center") {
                    leftOffset = (thiz.width() - $('#waterMark').width()) / 2;
                }
                if (settings.xPosition == "right") {
                    leftOffset = (thiz.width() - $('#waterMark').width()) - minLeftOffset;
                }
                if (settings.yPosition == "top") {
                    topOffset = minTopOffset;
                }
                if (settings.yPosition == "center") {
                    topOffset = (thiz.height() - $('#waterMark').height()) / 2 + minTopOffset;
                }
                if (settings.yPosition == "bottom") {
                    topOffset = (thiz.height() - $('#waterMark').height() - minTopOffset);
                }
            }

            function startShow() {
                if (settings.isAlwaysShow) {
                    alwaysShow();
                } else {
                    timeingShow();

                }
            }

            function init() {
                thiz.append(genereateDiv());
                var rotate = 'rotate(' + settings.rotate + 'deg)';
                $("#waterMark").css({
                    opacity: settings.opacity,
                    '-webkit-transform': rotate,
                    '-moz-transform': rotate,
                    '-ms-transform': rotate,
                    '-o-transform': rotate,
                    'transform': rotate,
                    'filter': "progid:DXImageTransform.Microsoft.Matrix(M11=0.70710678, M12=0.70710678, M21=-0.70710678, M22=0.70710678, sizingMethod='auto expand')"
                });
                startShow();
            }

            init();
        };
    })(jQuery);

    var Widget = require('widget');
    var swfobject = require('swfobject');
    window.onmessage=function(e){  
        var isPageFullScreen = e.data;
        var docContent = document.getElementById("lesson-document-content");
        if (isPageFullScreen) {
          docContent.removeAttribute("style");
        }else{
          docContent.style.width = window.document.body.offsetWidth+"px";
          docContent.style.position = "fixed";
          docContent.style.left = "0";
          docContent.style.top = "0";
          docContent.style.zIndex = "9999";
          
        }
    };

    var DocumentPlayer = Widget.extend({
        attrs: {
            swfFileUrl:'',
            pdfFileUrl:'',
            swfPlayerUrl:'../../bundles/topxiaweb/js/controller/swf/edusohoViewer.swf',
            swfPlayerWidth:'100%',
            swfPlayerheight:'100%',
            watermark: ''
        },

        events: {
        },

        setup: function() {
            var self = this;

            self.init(this.element);

        },

        init: function (thiz) {

            if (this.isSupportHtml5() && !this.isIE9()) {
                this.initPDFJSViewer(thiz);
           
            }else{
                this.initSwfViewer(thiz);
            }

        },
        isIE9: function(){
            return navigator.appVersion.indexOf("MSIE 9.")!=-1;
        },
        isSupportHtml5: function(){

            return $.support.leadingWhitespace;

        },

        initPDFJSViewer: function(thiz) {
            self=this;
            $("html").attr('dir','ltr');
            $('#viewerIframe').attr('src', 'http://opencdn.edusoho.net/pdf.js/v2/viewer.html#'+self.attrs.pdfFileUrl.value);

            if (this.get('watermark')) {
                $('#lesson-document-content').WaterMark(this.get('watermark'));
            }
        },

        initSwfViewer: function(thiz){

            thiz.html('<div id="website"><p align="center" class="style1">您还没有安装flash播放器 请点击<a href="http://www.adobe.com/go/getflashplayer">这里</a>安装</p></div>');
            var flashvars = {
              doc_url: escape(this.attrs.swfFileUrl.value) 
            };
            var params = {
                //menu: "false",
                bgcolor: '#efefef',
                allowFullScreen: 'true',
                wmode:'window',
                allowNetworking:'all',
                allowscriptaccess:'always',
                wmode: 'transparent',
                autoPlay:false
              };
            var attributes = {
                id: 'website'
            };

            swfobject.embedSWF(
                this.get('swfPlayerUrl'),
                'website',
                this.get('swfPlayerWidth'),  this.get('swfPlayerheight') , "9.0.45", null, flashvars, params, attributes
            );

            if (this.get('watermark')) {
                $('#lesson-document-content').WaterMark(this.get('watermark'));
            }

        }

    });

    module.exports = DocumentPlayer;

});