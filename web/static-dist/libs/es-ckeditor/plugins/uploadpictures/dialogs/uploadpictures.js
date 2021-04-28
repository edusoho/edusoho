CKEDITOR.dialog.add('uploadpictures', function(editor) {
    
    var imageHtml = '', uploader;
    var lang = editor.lang.uploadpictures;

    var initEvent = function () {
      function receiveMessage(event) {
          var eventName = event.data.eventName;
          if (eventName === 'es-ckeditor.post') {
              var innerHtml = event.data.html;
              $('.' + editor.id + ' #js-uploadpictures-body').append(innerHtml);
              try{
                $(document.getElementById("uploadContainer_" + editor.name))[0].remove();
              }catch(err){
                $(document.getElementById("uploadContainer_" + editor.name))[0].removeNode(true);
              };

              onLoadDialog();
          }
          window.removeEventListener("message", receiveMessage, false);
      }
      window.addEventListener("message", receiveMessage, false);
    };

    initEvent();

    var onLoadDialog = function() {

        var uploadUrl = editor.config.filebrowserImageUploadUrl;
        uploadUrl += uploadUrl.indexOf('?') == -1 ? '?' : '&';
        uploadUrl += 'CKEditorFuncNum=0&isWebuploader=1';

        var fileSingleSizeLimit = editor.config.fileSingleSizeLimit;

        uploader = WebUploader.create({
            swf: CKEDITOR.getUrl('plugins/uploadpictures/webuploader/Uploader.swf'),
            server: uploadUrl,
            pick: '.' + editor.id + ' .ckeditor-uploadpictures-pick-btn',
            compress: {
                width: 1200,
                height: 8000,
                quality: 90,
                allowMagnify: false,
                crop: false,
                preserveHeaders: true
            },
            resize: false,
            fileNumLimit: 10,
            threads: 1,
            fileSingleSizeLimit: fileSingleSizeLimit*1024*1024,
            accept: {
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png,ico',
                mimeTypes: 'image/*'
            }
        });

        $('.js-start-upload-btn').on('click', function() {
            uploader.upload();
        });

        uploader.on('error', function(errorCode) {
            if (errorCode == 'Q_TYPE_DENIED') {
                alert(lang.file_type_tip + uploader.options.accept[0].extensions.join(','));
            } else if (errorCode == 'F_EXCEED_SIZE') {
                alert(lang.single_file_max_size_tip + filesize(uploader.options.fileSingleSizeLimit));
            }
        });

        uploader.on('fileQueued', function(file) {
            $('.' + editor.id + ' .balloon-nofile').remove();
            var $list = $('.' + editor.id + ' .balloon-filelist ul');
            $list.append(
                '<li id="' + file.id + '">' +
                '  <div class="file-name">' + file.name + '</div>' +
                '  <div class="file-size">' + filesize(file.size) + '</div>' +
                '  <div class="file-status">' + lang.waiting_upload_text + '</div>' +
                '  <div class="file-remove">' + lang.delete_text + '</div>' +
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
            console.log('file', response);
            imageHtml += '<p><img src="' + response.url  + '" /></p>';

            var $li = $('.' + editor.id + ' #' + file.id);
            $li.find('.file-status').html(lang.uploaded_text);
            $li.find('.file-progress-bar').css('width', '0%');
            $li.find('.file-remove').remove();
        });

        // uploader.on('uploadFinished', function() {
            
        // });
    };

    var url = CKEDITOR.getUrl('plugins/uploadpictures/html/index_'+editor.config.language+'.html');
    var dialogHtml = '<div id="js-uploadpictures-body"><iframe src=' + url + ' scrolling="no" id="uploadContainer_' + editor.name + '" width="0" height="0" style="display:none;visibility:hidden"></iframe></div>';

    var dialogDefinition = {
        title: editor.lang.uploadpictures.title,
        minWidth: 600,
        minHeight: 280,
        resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
        buttons: [CKEDITOR.dialog.cancelButton, CKEDITOR.dialog.okButton],
        contents: [{
            id: 'uploadpictures',
            label: editor.lang.uploadpictures.title,
            title: editor.lang.uploadpictures.title,
            expand: true,
            elements: [{
                id: "body",
                type: "html",
                html: dialogHtml
            }]
        }],
        
        onLoad: function() {
            $('.' + editor.id + ' .js-uploadpictures-body').css({'vertical-align': 'top'});
        },

        onOk: function() {
            if (uploader.isInProgress()) {
                alert(lang.waiting_finish_tip);
                return false;
            }

            if (uploader.getFiles('inited').length > 0) { //未点击上传
                if (!confirm(lang.confirm_clear_tip)) {
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
