CKEDITOR.dialog.add('uploadpictures', function(editor) {
    
    var imageHtml = '', uploader;

    var initEvent = function () {
      function receiveMessage(event) {
        var eventName = event.data.eventName;
        if (eventName === 'ckeditor.post') {
          var innerHtml = event.data.html;
          $('.' + editor.id + ' #uploadpictures-body').append(innerHtml);
          $("#uploadContainer_"+editor.name)[0].remove();

          onLoadDialog();
        }
      }

      window.addEventListener("message", receiveMessage, false);
    };

    initEvent();

    var onLoadDialog = function() {

        var uploadUrl = editor.config.filebrowserImageUploadUrl;
        uploadUrl += uploadUrl.indexOf('?') == -1 ? '?' : '&';
        uploadUrl += 'CKEditorFuncNum=0&isWebuploader=1';

        uploader = WebUploader.create({
            swf: CKEDITOR.getUrl('plugins/uploadpictures/webuploader/Uploader.swf'),
            server: uploadUrl,
            pick: '.' + editor.id + ' .ckeditor-uploadpictures-pick-btn',
            compress: false,
            resize: false,
            fileNumLimit: 10,
            threads: 1,
            fileSingleSizeLimit: 10*1024*1024,
            accept: {
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png,ico',
                mimeTypes: 'image/*'
            }
        });

        $('.start-upload-btn').on('click', function(){
            uploader.upload();
        });

        uploader.on('error', function(errorCode) {
            if (errorCode == 'Q_TYPE_DENIED') {
                alert('文件类型只支持：'+uploader.get('accept')['extensions'].join(','));
            } else if (errorCode == 'F_EXCEED_SIZE') {
                alert('单个包大小不能超过：' + filesize(uploader.get('fileSingleSizeLimit')));
            }
        });

        uploader.on('fileQueued', function(file) {
            $('.' + editor.id + ' .balloon-nofile').remove();
            var $list = $('.' + editor.id + ' .balloon-filelist ul');
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

        $('.' + editor.id + ' .balloon-filelist').delegate('.file-remove', 'click', function(){
            uploader.removeFile($(this).parent('li').attr('id'), true);
            $(this).parent().remove();
        });

        uploader.on('uploadProgress', function(file, percentage) {
            var $li = $('.' + editor.id + ' #' + file.id);
            percentage = (percentage * 100).toFixed(2) + '%';
            $li.find('.file-status').html(percentage);
            $li.find('.file-progress-bar').css('width', percentage);
        });

        uploader.on('uploadSuccess', function(file, response) {
            imageHtml += '<p><img src="' + response.url  + '" /></p>';

            var $li = $('.' + editor.id + ' #' + file.id);
            $li.find('.file-status').html('已上传');
            $li.find('.file-progress-bar').css('width', '0%');
            $li.find('.file-remove').remove();
        });

        // uploader.on('uploadFinished', function() {
            
        // });
    };

    var url = CKEDITOR.getUrl('plugins/uploadpictures/html/index.html');
    var dialogHtml = `
        <div id="uploadpictures-body">
            <iframe id="uploadContainer_${editor.name}" src=${url} scrolling="no" width="0" height="0" style="display:none;visibility:hidden">
            </iframe>
        </div>
    `;

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
                id: "body",
                type: "html",
                html: dialogHtml
            }]
        }],
        
        onLoad: function() {
            $('.' + editor.id + ' #uploadpictures-body').css({'vertical-align': 'top'});
        },

        onOk: function() {
            if (uploader.isInProgress()) {
                alert('请等待文件上传完成...');
                return false;
            }

            if (uploader.getFiles('inited').length > 0) { //未点击上传
                if (!confirm("当前列表中还有文件未上传，确认将清空列表，是否继续？")) {
                    return false;
                }
            }
            if (imageHtml) {
                editor.insertHtml(imageHtml, 'unfiltered_html');
                // editor.insertElement(new CKEDITOR.dom.element.createFromHtml(imageHtml));
                imageHtml = ''; //清空
            }
            //关闭对话框后清除上传列表，因为列表有数量限制
            uploader.reset();
            $('.' + editor.id + ' .balloon-filelist ul').empty();
        }
       
    };

    return dialogDefinition;
});
