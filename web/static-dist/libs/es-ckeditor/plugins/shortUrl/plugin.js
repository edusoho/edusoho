CKEDITOR.plugins.add('shortUrl', {
    icons: '',
    init: function(editor) {
        CKEDITOR.dialog.add('shortUrl', this.path + 'dialogs/shortUrl.js');
        editor.addCommand('shortUrl', new CKEDITOR.dialogCommand('shortUrl'));
        editor.ui.addButton('shortUrl', {
            label: '短网址',
            command: 'shortUrl'
        });
        CKEDITOR.document.appendStyleSheet(CKEDITOR.getUrl('plugins/shortUrl/html/style.css'));
    }
});