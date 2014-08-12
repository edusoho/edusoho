define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    require('jquery.select2-css');
    require('jquery.select2');

    exports.run = function() {

        require('./header').run();

        $('#course_tags').select2({

            ajax: {
                url: app.arguments.tagMatchUrl + '#',
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
            placeholder: "请输入标签",
            width: 'off',
            multiple: true,
            createSearchChoice: function() {
                return null;
            },
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

        validator.addItem({
            element: '[name=maxStudentNum]',
            rule: 'integer',
            onItemValidated: function(error, message, elem) {
                if (error) {
                    return ;
                }

                var current = parseInt($(elem).val());
                var capacity = parseInt($(elem).data('liveCapacity'));
                if (current > capacity) {
                    message = '网校可支持最多' + capacity +'人同时参加直播，您可以设置一个更大的数值，但届时有可能会导致满额后其他学员无法进入直播。';
                    if ($(elem).parent().find('.alert-warning').length > 0) {
                        $(elem).parent().find('.alert-warning').html(message).show();
                    } else {
                        $(elem).parent().append('<div class="alert alert-warning mts">' + message + '</div>');
                    }
                } else {
                    $(elem).parent().find('.alert-warning').hide();
                }



            }
        });

    };

});