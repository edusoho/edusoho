CKEDITOR.plugins.add('shortUrl', {
    lang: 'en,zh,ug,zh-cn',
    icons: '',
    init: function(editor) {
        CKEDITOR.dialog.add('shortUrl', this.path + 'dialogs/shortUrl.js');
        editor.addCommand('shortUrl', new CKEDITOR.dialogCommand('shortUrl'));
        editor.ui.addButton('shortUrl', {
            label: editor.lang.shortUrl.toolbar,
            command: 'shortUrl'
        });
        CKEDITOR.document.appendStyleSheet(CKEDITOR.getUrl('plugins/shortUrl/html/style.css'));
    }
});