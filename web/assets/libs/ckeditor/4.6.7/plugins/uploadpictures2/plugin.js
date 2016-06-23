CKEDITOR.plugins.add('uploadpictures', {
    requires: 'dialog',
    icons: 'uploadpictures',
    init: function(editor) {
        CKEDITOR.dialog.add('uploadpictures', this.path + 'dialogs/uploadpictures.js');
        editor.addCommand('uploadpictures', new CKEDITOR.dialogCommand('uploadpictures'));
        editor.ui.addButton('uploadpictures', {
            label: '批量图片上传',
            command: 'uploadpictures'
        });
    }

});