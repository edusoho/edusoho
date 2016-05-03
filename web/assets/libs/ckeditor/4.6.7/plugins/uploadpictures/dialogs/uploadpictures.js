CKEDITOR.dialog.add('uploadpictures', function(editor) {

    var onLoadDialog = function() {

        var uploadUrl = editor.config.filebrowserImageUploadUrl;
        uploadUrl += uploadUrl.indexOf('?') == -1 ? '?' : '&';
        uploadUrl += 'CKEditorFuncNum=0&isWebuploader=1';

        var uploader = WebUploader.create({
            swf: CKEDITOR.getUrl('plugins/uploadpictures/webuploader/Uploader.swf'),
            server: uploadUrl,
            pick: '#ckeditor-uploadpictures-pick-btn',
            resize: false,
            fileNumLimit: 10,
            fileSingleSizeLimit: 2*1024*1024,
            accept: {
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png',
                mimeTypes: 'image/*'
            }
        });

        $('.start-upload-btn').on('click', function(){
            uploader.upload();
        });
 
        var imageHtml = '';
        uploader.on('uploadSuccess', function(file, response) {
            imageHtml += '<p><img src=' + response.url  + ' /></p>';
        });

        uploader.on('uploadFinished', function() {
            editor.insertHtml(imageHtml);
            imageHtml = ''; //清空
        });

        uploader.on('error', function(errorCode) {
            if (errorCode == 'Q_TYPE_DENIED') {
                alert('文件类型只支持：'+uploader.get('accept')['extensions'].join(','));
            } else if (errorCode == 'F_EXCEED_SIZE') {
                alert('单个包大小不能超过：' + filesize(uploader.get('fileSingleSizeLimit')));
            }
        });

        uploader.on('fileQueued', function(file) {
            $('.balloon-nofile').remove();
            var $list =$('.balloon-filelist ul');
            $list.append(
                '<li id="' + file.id + '">' +
                '  <div class="file-name">' + file.name + '</div>' +
                '  <div class="file-size">' + filesize(file.size) + '</div>' +
                '  <div class="file-status">待上传</div>' +
                '  <div class="file-remove">移除</div>' +
                '  <div class="file-progress"><div class="file-progress-bar" style="width: 0%;"></div></div>' +
                '</li>'
            );
        });

        $(".balloon-filelist").delegate(".file-remove", "click", function(){
            uploader.removeFile($(this).parent('li').attr('id'), true);
            $(this).parent().remove();
        });

        uploader.on('uploadProgress', function(file, percentage) {
            var $li = $('#' + file.id);
            percentage = (percentage * 100).toFixed(2) + '%';
            $li.find('.file-status').html(percentage);
            $li.find('.file-progress-bar').css('width', percentage);
        });

        uploader.on('uploadSuccess', function(file) {
            var $li = $('#' + file.id);
            $li.find('.file-status').html('已上传');
            $li.find('.file-progress-bar').css('width', '0%');
            $li.find('.file-remove').remove();
        });

    };

    var dialogDefinition = {
        title: '批量图片上传',
        minWidth: 600,
        minHeight: 280,
        resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
        buttons: [CKEDITOR.dialog.okButton],
        contents: [{
            id: 'uploadpictures',
            label: '批量图片上传',
            title: '批量图片上传',
            expand: true,
            elements: [{
                type: "html",
                html: ''
            }]
        }],
        
        onLoad: function() {
            $('.cke_dialog_contents_body').css({'vertical-align': 'top'});
            $('.cke_dialog_contents_body').load(CKEDITOR.getUrl('plugins/uploadpictures/html/index.html'), onLoadDialog);
        },

        onOk: function() {
            
        }
       
    };


    return dialogDefinition;
});
