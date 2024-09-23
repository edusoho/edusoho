'use strict';

( function() {
	CKEDITOR.plugins.add( 'questionblank', {
		requires: 'widget,dialog',
		lang: 'en,zh,ug,zh-cn', // %REMOVE_LINE_CORE%
		icons: 'questionblank', // %REMOVE_LINE_CORE%
		hidpi: true, // %REMOVE_LINE_CORE%

		onLoad: function() {
			// Register styles for questionblank widget frame.
			CKEDITOR.addCss( '.cke_questionblank{background-color:#eee}' );
		},

		init: function( editor ) {

			var lang = editor.lang.questionblank;

			CKEDITOR.dialog.add( 'questionblank', this.path + 'dialogs/questionblank.js' );

			editor.widgets.add( 'questionblank', {
				dialog: 'questionblank',
				pathName: lang.pathName,
				template: '<span class="cke_questionblank">[[]]</span>',

				downcast: function() {
					return new CKEDITOR.htmlParser.text( '[[' + this.data.name + ']]' );
				},

				init: function() {
					this.setData( 'name', this.element.getText().slice( 2, -2 ) );
				},

				data: function( data ) {
					this.element.setText( '[[' + this.data.name + ']]' );
				}
			} );

			editor.ui.addButton && editor.ui.addButton( 'QuestionBlank', {
				label: lang.toolbar,
				command: 'questionblank',
				toolbar: 'insert,5',
				icon: 'questionblank'
			} );
		},

		afterInit: function( editor ) {
			var questionblankReplaceRegex = /\[\[([^\[\]])+\]\]/g;

			editor.dataProcessor.dataFilter.addRules( {
				text: function( text, node ) {
					var dtd = node.parent && CKEDITOR.dtd[ node.parent.name ];

					// Skip the case when questionblank is in elements like <title> or <textarea>
					// but upcast questionblank in custom elements (no DTD).
					if ( dtd && !dtd.span )
						return;

					return text.replace( questionblankReplaceRegex, function( match ) {
						// Creating widget code.
						var widgetWrapper = null,
							innerElement = new CKEDITOR.htmlParser.element( 'span', {
								'class': 'cke_questionblank'
							} );

						// Adds questionblank identifier as innertext.
						innerElement.add( new CKEDITOR.htmlParser.text( match ) );
						widgetWrapper = editor.widgets.wrapElement( innerElement, 'questionblank' );

						// Return outerhtml of widget wrapper so it will be placed
						// as replacement.
						return widgetWrapper.getOuterHtml();
					} );
				}
			} );
		}
	} );

} )();
