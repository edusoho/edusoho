define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    require('jquery.select2-css');
    require('jquery.select2');

    exports.run = function() {
         require('/bundles/topxiaweb/js/controller/course-manage/base').run();

        $('#course_columns').select2({

            ajax: {
                url: app.arguments.columnMatchUrl + '#',
                dataType: 'json',
                quietMillis: 100,
                data: function(term, page) {
                    return {
                        q: term,
                        page_limit: 10
                    };
                },
                results: function(data) {

                    var results = [];

                    $.each(data, function(index, item) {

                        results.push({
                            id: item.name,
                            name: item.name
                        });
                    });

                    return {
                        results: results
                    };

                }
            },
            initSelection: function(element, callback) {
                var data = [];
                $(element.val().split(",")).each(function() {
                    data.push({
                        id: this,
                        name: this
                    });
                });
                callback(data);
            },
            formatSelection: function(item) {
                return item.name;
            },
            formatResult: function(item) {
                return item.name;
            },
            width: 'off',
            multiple: true,
            maximumSelectionSize: 20,
            placeholder: "请输入专题",
            width: 'off',
            multiple: true,
            createSearchChoice: function() {
                return null;
            },
            maximumSelectionSize: 20
        });

     

      



       

  




    };

});