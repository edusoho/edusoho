define(function (require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('es-ckeditor');

    require('jquery.select2-css');
    require('jquery.select2');

    require('../widget/category-select').run('course');
    require('jquery.bootstrap-datetimepicker');

    exports.run = function () {

        $.get($("#maxStudentNum-field").data("liveCapacityUrl"), function (data) {
            $("#maxStudentNum-field").data("liveCapacity", data.capacity);
            if (data.code == 2 || data.code == 1) {
                $("#live-plugin-url").removeClass("hidden");
                $("#live-plugin-url").find("a").attr("href", "http://www.edusoho.com/files/liveplugin/live_desktop_" + data.code + ".rar");
            }
        })


        $('#course_tags').select2({

            ajax: {
                url: app.arguments.tagMatchUrl + '#',
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
                    $.each(data, function (index, item) {

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
            initSelection: function (element, callback) {
                var data = [];
                $(element.val().split(",")).each(function () {
                    data.push({
                        id: this,
                        name: this
                    });
                });
                callback(data);
            },
            formatSelection: function (item) {
                return item.name;
            },
            formatResult: function (item) {
                return item.name;
            },
            width: 'off',
            multiple: true,
            maximumSelectionSize: 20,
            placeholder: Translator.trans('请输入标签'),
            width: 'off',
            multiple: true,
            createSearchChoice: function () {
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
            required: true,
            display: '标题'
        });

        validator.addItem({
            element: '[name=subtitle]',
            rule: 'maxlength{max:70}',
            display: '副标题'
        });


        validator.addItem({
            element: '[name=maxStudentNum]',
            rule: 'integer',
            onItemValidated: function (error, message, elem) {
                if (error) {
                    return;
                }

                var current = parseInt($(elem).val());
                var capacity = parseInt($(elem).data('liveCapacity'));
                if (current > capacity) {
                    message = Translator.trans('网校可支持最多%capacity%人同时参加直播，您可以设置一个更大的数值，但届时有可能会导致满额后其他学员无法进入直播。', {capacity: capacity});
                    if ($(elem).parent().find('.alert-warning').length > 0) {
                        $(elem).parent().find('.alert-warning').html(message).show();
                    } else {
                        $(elem).parent().find('.alert-warning').hide();
                    }
                } else {
                    validator.removeItem('[name=expiryDay]');
                }
            }
        })

        if ($('#course-about-field').length > 0) {
            CKEDITOR.replace('course-about-field', {
                allowedContent: true,
                toolbar: 'Detail',
                filebrowserImageUploadUrl: $('#course-about-field').data('imageUploadUrl')
            });
        }

        toggleExpiryValue($("[name=expiryMode]:checked").val());

        $("[name='expiryMode']").change(function () {
            if (app.arguments.isCoursePublished == 'published') {
                return false;
            }

            validator.removeItem('[name=expiryDay]');

            var expiryDay = $("[name='expiryDay']").val();
            if (expiryDay) {
                if (expiryDay.match("-")) {
                    $("[name='expiryDay']").data('date', $("[name='expiryDay']").val());
                } else {
                    $("[name='expiryDay']").data('days', $("[name='expiryDay']").val());
                }
                $("[name='expiryDay']").val('')
            }

            if ($(this).val() == 'none') {
                $('.expiry-day-js').addClass('hidden');
            } else {
                $('.expiry-day-js').removeClass('hidden');
                var $esBlock = $('.expiry-day-js > .controls > .help-block');
                $esBlock.text($esBlock.data($(this).val()));
                toggleExpiryValue($(this).val());
            }

        });
        function toggleExpiryValue(expiryMode) {
            if (!$("[name='expiryDay']").val()) {
                $("[name='expiryDay']").val($("[name='expiryDay']").data(expiryMode));
            }
            switch (expiryMode) {
                case 'days':
                    $('[name=expiryDay]').datetimepicker('remove');
                    $(".expiry-day-js .controls > span").removeClass('hidden');
                    validator.addItem({
                        element: '[name=expiryDay]',
                        rule: 'positive_integer maxlength{max:10}',
                        required: true,
                        display: '有效期'
                    });
                    break;
                case 'date':
                    $(".expiry-day-js .controls > span").addClass('hidden');
                    validator.addItem({
                        element: '[name=expiryDay]',
                        required: true,
                        display: '有效期'
                    });
                    $("#course_expiryDay").datetimepicker({
                        language: 'zh-CN',
                        autoclose: true,
                        format: 'yyyy-mm-dd',
                        minView: 'month'
                    });
                    $("#course_expiryDay").datetimepicker('setStartDate', new Date);
                    break;
                default:
                    break;
            }
        }

    };

});