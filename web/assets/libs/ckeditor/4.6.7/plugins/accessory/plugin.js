CKEDITOR.plugins.add('accessory', {
	icons: 'accessory',
	init: function(editor) {
		//Plugin logic goes here.
		editor.addCommand('accessory', {
			exec: function(editor) {

				$('#uploadModal').modal('show');	
				}
		});
		editor.ui.addButton('Accessory', {
			label: '上传附件',
			command: 'accessory',
			toolbar: 'insert,100'
		});

	}
});