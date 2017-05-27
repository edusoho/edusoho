CKEDITOR.dialog.add('shortUrl', function(editor) {
    var shortUrlModal,$messageUrl,$editorSource,diff,source,showError,showNormal,isShortUrl = false;
    var onLoadDialog = function() {
        shortUrlModal = $('#ckeditor-shorturl');
        $messageUrl = shortUrlModal.find('#message-url');
        $editorSource = $(editor.element.$);
        diff = $editorSource.data('shortDiff');
        var shortUrlLength = $editorSource.data('shortUrlLength');
        var url = shortUrlModal.find('.js-transfer-link').data('url');
        var formGroup = shortUrlModal.find('.form-group');
        var helpBlock = shortUrlModal.find('.help-block');

        showError = function(message){
            formGroup.addClass('has-error');
            helpBlock.show().text(message);
        }
        showNormal = function(){
            formGroup.removeClass('has-error');
            helpBlock.hide();
        }
        shortUrlModal.on('click','.js-transfer-link',function(){
            var postData = $messageUrl.val();
            if (postData　===　'') {
                showError(editor.lang.shortUrl.empty_error);
                return ;
            };
            if (shortUrlLength && $(editor.getData()).find('.js-short-url').length >= shortUrlLength) {
                showError(editor.lang.shortUrl.max_error);
                isShortUrl = false;
                return;
            };
            if (diff==1) {
                if (postData.indexOf('?') >= 0) {
                    postData += '&shorturltamp='+ new Date().getTime();
                } else {
                    postData += '?shorturltamp='+ new Date().getTime();
                }
            };
            $.post(url,{url:postData},function(data){
               if (data.error) {
                    showError(data.error);
                    isShortUrl = false
               } else {
                    $messageUrl.val(data.short_url);
                    isShortUrl = true;
                 //$('#shorturl-body-ckeditor').closest('.cke_dialog_contents').find('.cke_dialog_ui_button_ok').trigger('click');
               }
            }).error(function(e){
                showError(e.responseJSON.error.message);
                isShortUrl = false
            });
        })
        shortUrlModal.on('focus','#message-url',function(){
            showNormal();
        });
    };

    var dialogDefinition = {
        title: editor.lang.shortUrl.title,
        minWidth: 500,
        minHeight: 200,
        resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
        buttons: [CKEDITOR.dialog.okButton],
        contents: [{
            id: 'shortUrl',
            label: editor.lang.shortUrl.title,
            title: editor.lang.shortUrl.title,
            expand: true,
            elements: [{
                id: "body",
                type: "html",
                html: '<div id="shorturl-body-ckeditor"></div>'
            }]
        }],
        
        onLoad: function() {
            $('.' + editor.id + ' #shorturl-body-ckeditor').load(CKEDITOR.getUrl('plugins/shortUrl/html/index_'+editor.config.language+'.html'), onLoadDialog);
        },

        onOk: function() {
            if (isShortUrl) {
                var shortUrl = $messageUrl.val();
                editor.insertHtml("<a class='js-short-url' href="+shortUrl+'>'+shortUrl+'</a>', 'unfiltered_html');
                $editorSource.trigger('shortUrlInster',shortUrl);
            };
            $messageUrl.val('');
            showNormal();
        }
       
    };

    return dialogDefinition;
});
