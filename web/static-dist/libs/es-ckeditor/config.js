/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.env.isCompatible = true;

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
    var lang = document.documentElement.lang;
    lang = lang ? lang : 'zh-ch';
    config.language = lang.replace('_', '-').toLowerCase();
	// config.uiColor = '#AADC6E';

    config.toolbar_Minimal = [
        { items: [ 'Bold', 'Italic', 'Underline', 'TextColor', '-', 'RemoveFormat', 'PasteText', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', '-', 'Source', 'uploadpictures', 'CodeSnippet', 'kityformula'] }
    ];

    config.toolbar_editVip = [
        { items: [ 'Bold', 'Italic', 'Underline', 'TextColor', '-', 'RemoveFormat', 'PasteText', '-', 'NumberedList', 'BulletedList'] }
    ];

    config.toolbar_Simple = [
        { items: [ 'Bold', 'Italic', 'Underline', 'TextColor', '-', 'RemoveFormat', 'PasteText', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', 'uploadpictures', 'CodeSnippet', '-', 'Source'] }
    ];

    config.toolbar_Thread = [
        { items: [ 'Bold', 'Italic', 'Underline', 'TextColor', '-', 'RemoveFormat', 'PasteText', '-', 'Smiley', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', 'uploadpictures', 'CodeSnippet', '-', 'Source', 'kityformula', '-', 'Maximize'] }
    ];

    config.toolbar_Question = [
        { items: [ 'Bold', 'Italic', 'Underline', 'TextColor', '-', 'RemoveFormat', 'PasteText', '-', 'QuestionBlank', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', '-', 'Source', 'uploadpictures', 'CodeSnippet', 'kityformula', '-', 'Maximize'] }
    ];

    config.toolbar_Group = [
        { items: [ 'Bold', 'Italic', 'Underline', 'TextColor', '-', 'RemoveFormat', 'PasteText', '-', 'Smiley', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', 'uploadpictures', 'CodeSnippet', '-', 'Source', '-', 'Maximize'] }
    ];

    config.toolbar_Detail = [
        { items: [ 'FontSize', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
        { items: [ 'Bold', 'Italic', 'Underline', 'TextColor', '-', 'RemoveFormat', 'PasteText', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink', 'uploadpictures', 'CodeSnippet', '-', 'Source', '-', 'Maximize'] }
    ];

    config.toolbar_Task = [
        { items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat', 'Format' ] },
        { items: [ 'Link', 'Unlink' ] },
        { items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
        { items: [ 'FontSize', 'TextColor', 'BGColor' ] },
        { items: [ 'uploadpictures', 'CodeSnippet', 'Flash', 'Table', 'HorizontalRule', 'SpecialChar', 'Iframe', 'kityformula' ] },
        { items: [ 'PasteText', 'PasteFromWord'] },
        { items: [ 'Find', '-', 'Source'] }
    ];

    config.toolbar_Full = [
        { items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat', 'Format' ] },
        { items: [ 'Link', 'Unlink' ] },
        { items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
        { items: [ 'FontSize', 'TextColor', 'BGColor' ] },
        { items: [ 'uploadpictures', 'CodeSnippet', 'Flash', 'Table', 'HorizontalRule', 'SpecialChar', 'Iframe', 'kityformula' ] },
        { items: [ 'PasteText', 'PasteFromWord'] },
        { items: [ 'Find', '-', 'Source', '-', 'Maximize'] }
    ];

    config.toolbar_Admin = [
        { items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat', 'Format' ] },
        { items: [ 'Link', 'Unlink' ] },
        { items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
        { items: [ 'FontSize', 'TextColor', 'BGColor' ] },
        { items: [ 'uploadpictures', 'CodeSnippet', 'Flash', 'Table', 'HorizontalRule', 'SpecialChar', 'Iframe' ] },
        { items: [ 'PasteText', 'PasteFromWord'] },
        { items: [ 'Find', '-', 'Source', '-', 'Maximize'] }
    ];

    config.toolbar_SimpleMini = [
        { items: [ 'Bold', 'Italic', 'Underline', 'TextColor', '-', 'RemoveFormat', 'PasteText', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink'] }
    ];

    config.resize_enabled = false;
    config.title = false;

    config.extraAllowedContent = 'img[src,width,height,alt,title]';

    config.removePlugins= 'elementspath';

    config.extraPlugins = 'questionblank,smiley,table,font,kityformula,codesnippet,uploadpictures,shortUrl,image2,colorbutton,colordialog,justify,flash,find';
    // config.dialog_backgroundCoverColor = 'white';
    // config.stylesSet = 'my_styles';
    config.codeSnippet_theme = 'zenburn';

};
