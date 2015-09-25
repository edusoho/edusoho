CKEDITOR.plugins.add('kityformula', {
    init: function (editor) {
        var pluginName = 'kityformula';
        CKEDITOR.dialog.add(pluginName, this.path + 'dialogs/kityformula.js');
        editor.addCommand(pluginName, new CKEDITOR.dialogCommand(pluginName));
        editor.ui.addButton(pluginName,
        {
            label: '公式编辑器',
            command: pluginName,
            icon: this.path + 'icons/kityformula.png'
        });
    }
});