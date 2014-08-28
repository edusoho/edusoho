    define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var EditorFactory = require('common/kindeditor-factory');

    function checkUrl (url){
        var hrefArray=new Array();
        hrefArray=url.split('#');
        hrefArray=hrefArray[0].split('?');
        return hrefArray[1];
    }
    exports.run = function() {
        if($('#thread_content').length>0){
            var editor_thread = EditorFactory.create('#thread_content', 'simpleHaveEmoticons', {extraFileUploadParams:{group:'user'}});
            var validator_thread = new Validator({
            element: '#user-thread-form',
            failSilently: true,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#groupthread-save-btn').button('submiting').addClass('disabled');
            }
        });
        
        validator_thread.addItem({
            element: '[name="thread[title]"]',
            required: true,
            rule: 'minlength{min:2} maxlength{max:200}',
            errormessageUrl: '长度为2-200位'
           
            
        });
        validator_thread.addItem({
            element: '[name="thread[content]"]',
            required: true,
            rule: 'minlength{min:2}'
            
        });
        }
        
        if($('#post_content').length>0){
        var editor_thread = EditorFactory.create('#post_content', 'simpleHaveEmoticons', {extraFileUploadParams:{group:'user'}});
        var validator_thread = new Validator({
            element: '#post-thread-form',
            failSilently: true,
            autoSubmit: false,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
              
                $('#post-thread-btn').button('submiting').addClass('disabled');

                $.ajax({
                url : $("#post-thread-form").attr('post-url'),
                data:$("#post-thread-form").serialize(),
                cache : false, 
                async : false,
                type : "POST",
                dataType : 'text',
                success : function (url){
                    if(url){
                        if(url=="/login"){
                            window.location.href=url;
                            return ;
                        }
                        href=window.location.href;
                        var olderHref=checkUrl(href);
                        if(checkUrl(url)==olderHref){
                            window.location.reload();
                        }else{
                            window.location.href=url;
                        }
                    }
                    else{
                            window.location.reload();
                    }                        
                }
                });          
            }
        });
        validator_thread.addItem({
            element: '[name="content"]',
            required: true,
            rule: 'minlength{min:2}'
                    
        });
        }

        if($('.reply').length>0){

            $('.table').on('click','.li-reply',function(){
               var postId=$(this).attr('postId');

               $('#li-'+postId).show();

               $('#reply-content-'+postId).focus();
               $('#reply-content-'+postId).val("回复 "+$(this).attr("postName")+":");

            });

            $('.table').on('click','.reply',function(){
               var postId=$(this).attr('postId');

               $(this).hide();
               $('#unreply-'+postId).show();
               $('.reply-'+postId).slideDown('slow');

            });

            $('.table').on('click','.unreply',function(){
               var postId=$(this).attr('postId');

               $(this).hide();
               $('#reply-'+postId).show();
               $('.reply-'+postId).slideUp('slow');

            });

            $('.table').on('click','.replyToo',function(){
               var postId=$(this).attr('postId');

               $('#li-'+postId).show();
               $('#reply-content-'+postId).focus();
               $('#reply-content-'+postId).val("");

            });

            $('.table').on('click','.lookOver',function(){
               
               var postId=$(this).attr('postId');
               $('.li-reply-'+postId).css('display',"");
               $('.lookOver-'+postId).hide();
               $('.paginator-'+postId).css('display',"");

            });

            $('.table').on('click','.postReply-page',function(){

                var postId=$(this).attr('postId');
                $.post($(this).data('url'),"",function(html){
                $('.reply-post-list-'+postId).replaceWith(html);
                
                })

            });

            $('.table').on('click','.reply-btn',function(){
                
                var postId=$(this).attr('postId');

                var replyContent=$('#reply-content-'+postId+'').val();

                if(replyContent.replace(/[ ]/g,"")==""){
                   
                    $('.danger-'+postId).css("display","");
                }else{

                    $('.reply-btn').button('submiting').addClass('disabled');
                    $.ajax({
                    url : $(".reply-thread-form").attr('post-url'),
                    data:"content="+replyContent+'&'+'postId='+postId,
                    cache : false, 
                    async : false,
                    type : "POST",
                    dataType : 'text',
                    success : function (url){
                        if(url=="/login"){
                            window.location.href=url;
                            return;
                        }
                        window.location.reload();                
                    }
                    });

                }
             
            });

        }


        if($('#post-action').length>0){
          
            $('#post-action').on('click','#closeThread',function(){
           
                var $trigger = $(this);
                 if (!confirm($trigger.attr('title') + '？')) {
                    return false;
                 }
               
                $.post($trigger.data('url'), function(data){
                
                    window.location.href=data;
              
                });
            })

            $('#post-action').on('click','#elite,#stick',function(){
           
                var $trigger = $(this);
               
                $.post($trigger.data('url'), function(data){
                
                    window.location.href=data;
              
                });
            })

        }
        if($('#exit-btn').length>0){
        $('#exit-btn').click(function(){
            if (!confirm( '真的要退出该小组？您在该小组的信息将删除！')) {
                    return false;
            }
        })

        }
        if($('.deletePost').length>0){
       
            $('.deletePost').on('click','#deletePostByself,#deletePostBythreadowner,#deletePostBygroupowner',function(){
                
                var $trigger = $(this);
                 if (!confirm($trigger.attr('title') + '？')) {
                    return false;
                 }
                    $.post($trigger.data('url'), function(){
                    window.location.reload();
                    });
            })

        }

        if($('#group_about').length>0){
            var editor = EditorFactory.create('#group_about', 'simpleHaveEmoticons', {extraFileUploadParams:{group:'user'}});
            var validator = new Validator({
            element: '#user-group-form',
            failSilently: true,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#group-save-btn').button('submiting').addClass('disabled');
            }
        });
        
        validator.addItem({
            element: '[name="group[grouptitle]"]',
            required: true,
            rule: 'minlength{min:2} maxlength{max:12}',
            errormessageUrl: '长度为2-12位'
           
            
        });

       }
    
        
       
    };

});

