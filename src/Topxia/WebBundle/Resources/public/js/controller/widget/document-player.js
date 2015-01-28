define(function(require, exports, module) {

    var Widget = require('widget');
    var swfobject = require('swfobject');

    var DocumentPlayer = Widget.extend({
        attrs: {
            swfFileUrl:'',
            pdfFileUrl:'',
            swfPlayerUrl:'../../bundles/topxiaweb/js/controller/swf/edusohoViewer.swf',
            swfPlayerWidth:'100%',
            swfPlayerheight:'100%'
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

        initPDFJSViewer: function(thiz){
            self=this;

            $("html").attr('dir','ltr');
            $('#viewerIframe').attr('src', '../../bundles/topxiaweb/js/controller/pdfjs/viewer.html');
            $('#viewerIframe').load(function(){
                $("#viewerIframe")[0].contentWindow.setFileName(self.attrs.pdfFileUrl.value);
                $("#viewerIframe")[0].contentWindow. webViewerLoad();
            });
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
        }

    });

    module.exports = DocumentPlayer;

});
