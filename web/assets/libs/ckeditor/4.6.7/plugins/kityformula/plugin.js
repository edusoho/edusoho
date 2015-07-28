(function() {
	CKEDITOR.plugins.add('kityformula', {
		requires: ['dialog'],
		init: function(editor) {
			editor.kityFormuaPath=this.path;
			editor.addCommand("kityformula", new CKEDITOR.dialogCommand("kityformula"));
			editor.ui.addButton("kityformula", {
				label: "公式编辑器",
				command: "kityformula",
				icon: this.path + "icons/kityformula.png"
			});
			CKEDITOR.dialog.add("kityformula", this.path + "dialogs/kityformula.js");
			editor.on('doubleclick', function( evt ) {
				var element = evt.data.element;
				var kityformula=$(element).attr("kityformula");
				var $editorEle=$("#cke_"+editor.name);
				var source = $(element).attr("alt");
				if ( kityformula=="true" && element.is( 'img' ) && !element.data( 'cke-realelement' ) && !element.isReadOnly() ){
					$editorEle.data("source", source);
					evt.data.dialog = 'kityformula';
				}
			});
		}
	});
})();
