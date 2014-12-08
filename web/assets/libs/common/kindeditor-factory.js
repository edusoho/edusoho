define(function(require, exports, module) {

    require('kindeditor');

    KindEditor.lang({insertblank: '插入填空项'});
    KindEditor.lang({hidden: '插入隐藏内容'});
    KindEditor.lang({accessory: '上传附件'});

    KindEditor.plugin('hidden', function(K) {
        var editor = this, name = 'hidden';
        
        editor.clickToolbar(name, function() {
                
                $('#myModal').modal('show');

                $('#insert').on('click',function(){

                    var amount= $('#amount').val();

                    amount=parseInt(amount);

                    if(amount > 0){
                        
                        var text=$('#text').val();
                       
                        var content="&nbsp;[hide=coin"+amount+"]"+text+"[/hide]";

                        editor.insertHtml(content);
                    }
                    $('#text').val('');
                    $('#amount').val('');
                    $('#myModal').modal('hide');
                    
                });
        });
    });

    KindEditor.plugin('accessory', function(K) {
        var editor = this, name = 'accessory';

        editor.clickToolbar(name, function() {

            var Uploader = require('upload');
            var Notify = require('common/bootstrap-notify');
            var ids=[];
            var descriptions=[];
            var coins=[];
            var names=[];
            $('#uploadModal').on('click','.del-file',function(){

                var id=$(this).attr("data-id");
                $('#file-'+id).remove();

            });
            
            $('#sure').on('click',function(){

                var id=$('input[name="id[]"]');
                var description=$('input[name="description[]"]');
                var coin=$('input[name="coin[]"]');

                $.each(id,function(i,item){
                
                    ids.push(item.value);
                   
                });

                $.each(description,function(i,item){

                       descriptions.push(item.value+"end!");  
                       names.push(item.title);           
                   
                });

                $.each(coin,function(i,item){
                   
                    amount=parseInt(item.value);
                    if(amount > 0 ){

                        coins.push(amount);
                    }else{

                        coins.push(0);
                    }
                    
                   
                });

                $('input[name="fileIds"]').val(ids);
                $('input[name="fileDescriptions"]').val(descriptions);
                $('input[name="fileTitles"]').val(names);
                $('input[name="fileCoins"]').val(coins);

                ids=[];
                descriptions=[];
                coins=[];
                names=[];
                $('#uploadModal').modal('hide');   

            });

            $('#uploadModal').modal('show');

            $('#uploadModal').find('.upload-img').each(function(index, el) {

            var uploader = new Uploader({
                    trigger: $(el),
                    name: 'file',
                    action: $(el).data('url'),
                    data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
                    }).success(function(response) {
                    
                    var response=eval("("+response+")");
       
                    $('#block-table').append('<tr id="file-'+response.id+'" ><td><label class="control-label"><span class="glyphicon glyphicon-folder-close"></span> '+response.name+'</label></td><td><input type="hidden" name="id[]" value="'+response.id+'"/><input type="text" class="form-control" name="description[]" title="'+response.name+'"></td><td><input type="text" name="coin[]" class="form-control"></td><td><button type="button" class="del-file btn btn-default" data-id="'+response.id+'" >删除</button></td></tr>');
                 
     
                }).error(function(message) {
                    Notify.danger("上传失败！")

                });


            });
            
        });
    });

    var simpleNoImageItems = ['bold', 'italic', 'underline', 'forecolor', '|', 'insertorderedlist', 'insertunorderedlist', '|', 'link', 'unlink', '|', 'removeformat', 'source'];


    var simpleItems = ['bold', 'italic', 'underline', 'forecolor', '|', 'insertorderedlist', 'insertunorderedlist', '|', 'link', 'unlink', 'image', 'code', '|', 'removeformat', 'source'];


    var standardItems = [
        'bold', 'italic', 'underline', 'strikethrough', 'removeformat', '|',
        'fontsize', 'forecolor', 'hilitecolor',   '|', 
        'link', 'unlink', '|',
        'image', 'flash',  'code',  '|',
        'insertorderedlist', 'insertunorderedlist','indent', 'outdent', '|',
        'justifyleft', 'justifycenter', 'justifyright', '|',
        'source',  'fullscreen', 'about'
    ];

    var fullItems = [
        'bold', 'italic', 'underline', 'strikethrough', '|',
        'link', 'unlink', '|',
        'insertorderedlist', 'insertunorderedlist','indent', 'outdent', '|',
         'image', 'flash', 'insertfile', 'code', 'table', 'hr', '/',
        'formatblock', 'fontname', 'fontsize', '|',
        'forecolor', 'hilitecolor',   '|', 
        'justifyleft', 'justifycenter', 'justifyright', 'justifyfull',  '|',
        'removeformat', 'clearhtml', '|',
        'source', 'preview',  'fullscreen', '|',
        'about'
    ];

    var questionItems = ['bold', 'italic', 'underline', 'forecolor', '|', 'insertorderedlist', 'insertunorderedlist', '|', 'link', 'unlink', '|', 'removeformat', 'source', '|', 'insertblank'];


    var simpleHaveEmoticonsItems = ['bold', 'italic', 'underline', 'forecolor', '|', 'insertorderedlist', 'insertunorderedlist', '|', 'link', 'unlink', 'image', '|', 'removeformat', 'source','emoticons'];

    var haveHiddenItems = ['bold', 'italic', 'underline', 'forecolor', '|', 'insertorderedlist', 'insertunorderedlist', '|', 'link', 'unlink', 'image', '|', 'removeformat', 'source','emoticons','hidden','accessory'];


    var contentCss = [];
    contentCss.push('body {font-size: 14px; line-height: 1.428571429;color: #333333;}');
    contentCss.push('a {color: #428bca;}');
    contentCss.push('p {margin: 0 0 10px;}');
    contentCss.push('img {max-width: 100%;}');
    contentCss.push('p {font-size:14px;}');

    var defaultConfig = {
        width: '100%',
        resizeType: 1,
        uploadJson: app.config.editor_upload_path,
        extraFileUploadParams: {},
        filePostName: 'file',
        cssData: contentCss.join('\n')
    };

    var configs = {};
    configs.simple_noimage = $.extend({}, defaultConfig, {items:simpleNoImageItems});
    configs.simple = $.extend({}, defaultConfig, {items:simpleItems});
    configs.simpleHaveEmoticons = $.extend({}, defaultConfig, {items:simpleHaveEmoticonsItems});
    configs.haveHidden = $.extend({}, defaultConfig, {items:haveHiddenItems});
    configs.standard = $.extend({}, defaultConfig, {items:standardItems});
    configs.full = $.extend({}, defaultConfig, {items:fullItems});
    configs.question = $.extend({}, defaultConfig, {items:questionItems});

    function getConfig(name, extendConfig) {
        if (!extendConfig) {
            extendConfig = {};
        }
        return $.extend({}, configs[name], extendConfig);
    }

    exports.create = function(select, name, config) {
        return KindEditor.create(select, getConfig(name, config));
    }
});