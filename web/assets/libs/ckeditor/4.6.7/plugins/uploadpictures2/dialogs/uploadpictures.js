CKEDITOR.dialog.add('uploadpictures', function(editor) {
    html = '<iframe scrolling="" id="editorContainer_uploadpictures" src="/assets/libs/ckeditor/4.6.7/plugins/uploadpictures/webuploader/index.html" width="600" height="350" style="border:0"></iframe>'
    var $iframeupload  = null;
    return {
    	name:'Title',
        title: '图片上传',
        minWidth: 400,
        minHeight: 200,
        padding:0,
        buttons: [
            CKEDITOR.dialog.okButton,
            CKEDITOR.dialog.cancelButton],
        contents: [{
            id: 'uploadpictures',
            label: '图片上传',
            title: '图片上传',
            expand: true,
            padding: 0,
            elements: [{
                type: "html",
                html:html
            }]
        }],
        
        onLoad: function() {
            $('.cke_dialog_contents_body').css('padding',0);
            $iframeupload = $("#editorContainer_uploadpictures").contents();
            

        },
        onOk: function() {
            console.log($iframeupload);
            var $hzpicker = $iframeupload.find('#hzpicker');
            var isdone = $hzpicker.attr("data-uploadfinished");
            var $thelist = $iframeupload.contents().find("#thelist");
            if(isdone==0) {
                alert("文件正在上传中,请稍作等待...");
            }else {
                $thelist.find('.file-item.upload-state-done').each(function(){
                    var $this = $(this);
                    var $img = $this.find('img');
                    console.log($img[0]);
                    editor.insertHtml($img[0]);
                })
            }
        }
       
    };
});
