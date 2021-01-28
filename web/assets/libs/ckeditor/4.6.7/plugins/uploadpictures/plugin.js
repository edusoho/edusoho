CKEDITOR.plugins.add('uploadpictures', {
    icons: 'uploadpictures',
    init: function(editor) {
        CKEDITOR.dialog.add('uploadpictures', this.path + 'dialogs/uploadpictures.js');
        editor.addCommand('uploadpictures', new CKEDITOR.dialogCommand('uploadpictures'));
        editor.ui.addButton('uploadpictures', {
            label: '批量图片上传',
            command: 'uploadpictures'
        });
        CKEDITOR.scriptLoader.load(CKEDITOR.getUrl('plugins/uploadpictures/webuploader/webuploader.js'));
        CKEDITOR.scriptLoader.load(CKEDITOR.getUrl('plugins/uploadpictures/webuploader/filesize.js'));
        CKEDITOR.document.appendStyleSheet(CKEDITOR.getUrl('plugins/uploadpictures/html/style.css'));
    }
});