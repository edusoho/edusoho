CKEDITOR.plugins.add( 'uploadpictures', {
    init: function( editor ) {
        var pluginName = 'uploadpictures';
        CKEDITOR.dialog.add( 'addDialog', this.path + 'dialogs/uploadpictures.js' );
        editor.addCommand(pluginName, new CKEDITOR.dialogCommand( 'addDialog' ) );
        editor.ui.addButton(pluginName, {
            label: '图片上传',
            command: 'uploadpictures',
            icon: this.path + 'icons/uploadpictures.png'
        });
    }
});