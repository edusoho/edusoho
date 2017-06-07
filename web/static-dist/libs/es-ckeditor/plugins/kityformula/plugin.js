CKEDITOR.plugins.add('kityformula', {
    lang: 'en,zh,ug,zh-cn',
    init: function (editor) {
        var pluginName = 'kityformula';
        CKEDITOR.dialog.add(pluginName, this.path + 'dialogs/kityformula.js');
        editor.addCommand(pluginName, new CKEDITOR.dialogCommand(pluginName));
        editor.ui.addButton(pluginName,
        {
            label: editor.lang.kityformula.toolbar,
            command: pluginName,
            icon: this.path + 'icons/kityformula.png'
        });
    }
});