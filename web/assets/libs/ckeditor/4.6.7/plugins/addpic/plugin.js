CKEDITOR.plugins.add( 'addpic', {
    init: function( editor ) {
        var pluginName = 'addpic';
        CKEDITOR.dialog.add( 'addDialog', this.path + 'dialogs/addpic.js' );
        editor.addCommand(pluginName, new CKEDITOR.dialogCommand( 'addDialog' ) );
        editor.ui.addButton(pluginName, {
            label: '图片上传',
            command: 'addpic',
            icon: this.path + 'icons/addpic.png'
        });
    }
});