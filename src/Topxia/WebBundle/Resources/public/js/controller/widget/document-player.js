define(function(require, exports, module) {

    var Widget = require('widget');
    var swfobject = require('swfobject');

    var DocumentPlayer = Widget.extend({
        attrs: {
            slides: [],
            swfFileUrl:'http://7tebfn.com1.z0.glb.clouddn.com/testWord-pdf-swf',
            pdfFileUrl:'http://7sbrob.com1.z0.glb.clouddn.com/ActionScript 3.pdf',
            swfPlayerUrl:'swf/zviewer5.swf',
            swfPlayerWidth:'600',
            swfPlayerheight:'400'
        },

        events: {
        },

        setup: function() {
            var self = this;
            self.init();

        },

        init: function () {

            if (this.isSupportHtml5()) {
                this.initPDFJSViewer(this);
            }else{
                this.initSwfViewer(this);
            }
        },

        isSupportHtml5: function(){

            return $.support.leadingWhitespace;

        },

        initPDFJSViewer: function(thiz){

            $("html").attr('dir','ltr');
            thiz.load("pdfjs/pdfjsViewerSegment",function(){
                $.getScript("pdfjs/compatibility.js");
                $.getScript("pdfjs/l10n.js");
                $.getScript("pdfjs/pdf.js");
                $.getScript("pdfjs/viewer.js",function(){
                    setFileName(this.get['pdfFileUrl']);
                    webViewerLoad();
                });
              
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