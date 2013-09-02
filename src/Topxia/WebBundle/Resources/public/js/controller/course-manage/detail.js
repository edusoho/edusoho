define(function(require, exports, module) {

    require('ckeditor');
    var DynamicCollection = require('../widget/dynamic-collection2');
    require('jquery.sortable');

    exports.run = function() {
        require('./header').run();

        CKEDITOR.replace('course-about-field', {
            resize_enabled: false,
            forcePasteAsPlainText: true,
            toolbar: 'Simple',
            removePlugins: 'elementspath',
            filebrowserUploadUrl: '/ckeditor/upload?group=course'
        });

        var goalDynamicCollection = new DynamicCollection({
            element: '#course-goals-form-group',
        });

        var goalDynamicCollection = new DynamicCollection({
            element: '#course-audiences-form-group',
        });

        $(".sortable-list").sortable();

    };

});