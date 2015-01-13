define(function(require, exports, module) {

    var Widget = require('widget');
    var swfobject = require('swfobject');

    var DocumentPlayer = Widget.extend({
        attrs: {
            swfFileUrl:'',
            pdfFileUrl:'',
            swfPlayerUrl:'../../bundles/topxiaweb/js/controller/swf/zviewer5.swf',
            swfPlayerWidth:'800',
            swfPlayerheight:'400'
        },

        events: {
        },

        setup: function() {
            var self = this;

            self.init(this.element);

        },

        init: function (thiz) {

            if (this.isSupportHtml5()) {
                this.initPDFJSViewer(thiz);
           
            }else{
                this.initSwfViewer(thiz);
            }

        },

        isSupportHtml5: function(){

            return $.support.leadingWhitespace;

        },

        initPDFJSViewer: function(thiz){
            self=this;
//self.attrs.pdfFileUrl.value
            $("html").attr('dir','ltr');
            $('#viewerIframe').attr('src', '../../bundles/topxiaweb/js/controller/pdfjs/viewer.html');
            $('#viewerIframe').load(function(){
                $("#viewerIframe")[0].contentWindow.setFileName('http://7sbrob.com1.z0.glb.clouddn.com/largefile');
                $("#viewerIframe")[0].contentWindow. webViewerLoad();
            });
        },

        initSwfViewer: function(thiz){

            thiz.html('<div id="website"><p align="center" class="style1">需要plash player版本９+</p><p align="center"><a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></p></div>');
            var flashvars = {
              doc_url: this.get['swfFileUrl'],
              autoPlay:false
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