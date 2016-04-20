CKEDITOR.dialog.add( 'addDialog', function( editor ) {
    html = '<iframe scrolling="" id="editorContainer_'+editor.name+'" src="/assets/libs/ckeditor/4.6.7/plugins/addpic/webuploader/index.html" width="600" height="350" style="border:0"></iframe>'
    return {
    	name:'Title',
        title: '图片上传',
        minWidth: 400,
        minHeight: 200,
        buttons: [
            CKEDITOR.dialog.okButton,
            CKEDITOR.dialog.cancelButton],
        contents: [
            {
                id: 'tab-basic',
                label: '本地多图',
                elements: [
                    {
                        type: 'html',
                        id: 'local',
                        label: 'Abbreviation',
                        html:html
                    }
                ]
            }, 
            {
                id: 'tab-adv',
                label: '网络图片',
                elements: [
                    {
                        type: 'text',
                        id: 'id',
                        label: 'Id'
                    }
                ]
            }
        ],
        onOk: function() {
            var dialog = this;

            var abbr = editor.document.createElement( 'abbr' );
            abbr.setAttribute( 'title', dialog.getValueOf( 'tab-basic', 'local' ) );
            abbr.setText( dialog.getValueOf( 'tab-basic', 'local' ) );

            var id = dialog.getValueOf( 'tab-adv', 'id' );
            if ( id )
                abbr.setAttribute( 'id', id );

            editor.insertElement( abbr );

        }
    };
});
// CKEDITOR.stylesSet.add( 'my_styles', [
//  // Block-level styles
//  { name: 'Blue Title', styles: { 'color': 'Blue' } },
// ]);