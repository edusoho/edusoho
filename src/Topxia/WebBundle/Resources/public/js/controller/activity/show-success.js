define(function(require, exports, module) {

    var Widget = require('widget'),
        Backbone = require('backbone'),
        VideoJS = require('video-js'),
        swfobject = require('swfobject'),
        Scrollbar = require('jquery.perfect-scrollbar');
    
    require('mediaelementplayer');

    

    var $=require('jquery');
    var Validator = require('bootstrap.validator');
    require('jquery.raty');
    require('common/validator-rules').inject(Validator);
    var Carousel=require('carousel');
   


    var TempleteBindDate=function(templeteBindDate,canvertFun,data){
        var htmlcontent=$(templeteBindDate).html();
        return canvertFun(htmlcontent,data);
    }

    var createReplyObject=function(templeteHtml){
        return $(templeteHtml);
    }

    var postcanvertFun=function(templete,data){
        $.each(data,function(key,val){
            templete=templete.replace('{ '+key+' }',val);
        });
        return templete;
    }


    var createReply=function(){
        var replybox=$(this).parent().parent().find(".reply-box");
        var val=$(replybox).css('display');
        if(val=='none'){
            $(replybox).append($("#post-templete").html());
            $(".reply-btn").unbind("click");
            $(".reply-btn").bind("click",createReplyPost);
            $(replybox).show();
        }else if(val){
            $(replybox).html("");
            $(replybox).hide();
        }
    }

    var createReplyPost=function(){
        var parent=$(this).parent().parent();
        var pparent=$(parent).parent();
        var postlist=$(pparent).prev();
        var action=$(pparent).attr('action');
        var text=$(parent).find("input");

        $.post(action,{'activitypost[content]':text.val()}, function(json) {
            $(postlist).append(json); //业务逻辑 
            $(text).val('');
            $(pparent).hide();
            $(pparent).html('');
        });
        
    }

    exports.run = function() {

        
        $(".reply").bind("click",createReply);
        //提问
        var validator = new Validator({
            element: '#qustion-form',
            autoSubmit: false
        });

        validator.addItem({
            element: '[name="qustion[content]"]',
            required: true
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return;
            }
            $.post($form.attr('action'), $form.serialize(), function(json) {
               $("#qustiontext").val("");  //UI逻辑
               $("#qustion-media-list").prepend(json); //业务逻辑
               $(".reply").unbind("click"); //UI逻辑
               $(".reply").bind("click",createReply); //UI逻辑

            });
        });


        //图片
        var carousel = new Carousel({
            element: '#carousel-demo-1',
            hasTriggers: false,
            easing: 'easeOutStrong',
            effect: 'scrollx',
            step: 4,
            viewSize: [280],
            circular: false,
            autoplay: true
        }).render();

        //视频

       if ($("#lesson-preview-video-player").length > 0) {

            var videoPlayer = VideoJS("lesson-preview-video-player", {
                techOrder: ['flash','html5']
            });
            videoPlayer.width('100%');
            videoPlayer.play();

           
        }

        if ($("#lesson-preview-audio-player").length > 0) {
            var audioPlayer = new MediaElementPlayer('#lesson-preview-audio-player',{
                mode:'auto_plugin',
                enablePluginDebug: false,
                enableAutosize:true,
                success: function(media) {
                    media.play();
                }
            });

          

        }

        if ($("#lesson-preview-swf-player").length > 0) {
            swfobject.embedSWF($("#lesson-preview-swf-player").data('url'), 'lesson-preview-swf-player', '100%', '500', "9.0.0");

          
        }


    };

});