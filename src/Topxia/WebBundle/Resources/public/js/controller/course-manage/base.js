define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');

    require('jquery.select2-css');
    require('jquery.select2');

    exports.run = function() {

        require('./header').run();

        // 标签选择组件初始化
        require.async(app.arguments.tagUrl + '#', function(tags) {
            $('#course_tags').select2({
                width: 'off',
                multiple: true,
                maximumSelectionSize: 20,
                id: 'name',
                data: {results:tags, key:'name'},
                formatSelection: function(item) {
                    return item.name;
                },
                formatResult: function(item) {
                    return item.name;
                },
                initSelection : function (element, callback) {
                    var data = [];
                    $(element.val().split(",")).each(function () {
                        data.push({id: this, name: this});
                    });
                    callback(data);
                }
            });
        });

        // 表单校验
        var validator = new Validator({
            element: '#course-form',
            failSilently: true,
            triggerType: 'change'
        });

        validator.addItem({
            element: '[name="course[title]"]',
            required: true
        });

    };

});