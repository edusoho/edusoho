/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	config.language = 'zh-cn';
	config.toolbar_Full = [
	    [ 'Source', '-', 'Preview', 'Print', 'Templates' ],
	    [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ],
	    [ 'Find', 'Replace', 'SelectAll'],
	    [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'],
	    [ 'NumberedList', 'BulletedList', '-', 'Indent', 'Outdent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
	    [ 'Link', 'Unlink', 'Anchor'],
	    [ 'Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe'],
	    [ 'Styles' ],
	    [ 'Format' ],
	    [ 'Font' ],
	    [ 'FontSize'],
	    [ 'TextColor', 'BGColor'],
	    [ 'Maximize', 'ShowBlocks']
	];

	config.toolbar_Basic = [
	    [ 'Format' ],
	    [ 'FontSize'],
	    [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat'],
	    [ 'TextColor', 'BGColor'],
	    [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
	    '/',
	    [ 'NumberedList', 'BulletedList', '-', 'Indent', 'Outdent', '-', 'Blockquote' ],
	    [ 'Link', 'Unlink'],
	    [ 'Image', 'Flash', 'Table', 'HorizontalRule', 'Iframe'],
	    [ 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ],
	    [ 'Maximize', '-', 'Source']
	];

	config.toolbar_Simple = [
	    [ 'Bold', 'Italic', 'Underline', '-', 
	      'RemoveFormat', '-', 
	      'TextColor', '-', 
	      'NumberedList', 'BulletedList', '-', 
	      'Blockquote', '-',
	      'Link', 'Unlink', '-',
	      'Image', 'Flash', '-',
	      'Maximize'
	    ]
	];

	config.toolbar_Mini = [
	    [ 'Bold', 'Italic', 'Underline', '-', 
	      'NumberedList', 'BulletedList', '-', 
	      'Link', 'Unlink', '-',
	      'Image', '-', 'Source'
	    ]
	];

	config.toolbar = 'Basic';
};
