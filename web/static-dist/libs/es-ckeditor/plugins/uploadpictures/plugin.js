CKEDITOR.plugins.add('uploadpictures', {
    lang: 'en,zh,ug,zh-cn',
    icons: 'uploadpictures',
    init: function(editor) {
        CKEDITOR.dialog.add('uploadpictures', this.path + 'dialogs/uploadpictures.js');
        editor.addCommand('uploadpictures', new CKEDITOR.dialogCommand('uploadpictures'));
        editor.ui.addButton('uploadpictures', {
            label: editor.lang.uploadpictures.toolbar,
            command: 'uploadpictures'
        });
        CKEDITOR.scriptLoader.load(CKEDITOR.getUrl('plugins/uploadpictures/webuploader/webuploader.js'));
        CKEDITOR.scriptLoader.load(CKEDITOR.getUrl('plugins/uploadpictures/webuploader/filesize.js'));
        CKEDITOR.document.appendStyleSheet(CKEDITOR.getUrl('plugins/uploadpictures/html/style.css'));
    }
});