define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    require('jquery.select2-css');
    require('jquery.select2');

    exports.run = function() {
        
        require('./header').run();

            $('#course_tags').select2({
            
                ajax: {
                    url: app.arguments.tagMatchUrl+'#',
                    dataType: 'json',
                    quietMillis: 100,
                    data: function (term, page) { 
                        return {
                            q: term, 
                            page_limit: 10
                        };
                    },
                    results: function (data) {

                        var results = [];

                        $.each(data, function(index, item){

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
                initSelection : function (element, callback) {
                    var data = [];
                    $(element.val().split(",")).each(function () {
                        data.push({id: this, name: this});
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
                placeholder: Translator.trans('validate.tag_required_hint'),
                width: 'off',
                multiple: true,
                createSearchChoice: function() { return null; },
                maximumSelectionSize: 20
            });

        var validator = new Validator({
            element: '#course-form',
            failSilently: true,
            triggerType: 'change'
        });

        validator.addItem({
            element: '[name=title]',
            required: true
        });      
        
        validator.addItem({
            element: '[name=subtitle]',
            rule: 'maxlength{max:70}'
        });

        validator.addItem({
            element: '[name=expiryDay]',
            rule: 'integer'
        });
    };

});