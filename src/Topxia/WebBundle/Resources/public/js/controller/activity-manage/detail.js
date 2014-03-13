define(function(require, exports, module) {

    var EditorFactory = require('common/kindeditor-factory');
    var DynamicCollection = require('../widget/dynamic-collection3');
    require('jquery.sortable');

    exports.run = function() {
        require('./header').run();

        var editor = EditorFactory.create('#course-about-field', 'full', {extraFileUploadParams:{group:'course'}});

        var goalDynamicCollection = new DynamicCollection({
            element: '#course-goals-form-group',
        });

        var goalDynamicCollection = new DynamicCollection({
            element: '#course-audiences-form-group',
        });

        $(".sortable-list").sortable();

    };

});