define(function (require, exports, module) {
    var Validator = require('bootstrap.validator');

    Validator.addRule('open_live_course_title',
        function (options, commit) {
            var $courseType = $("#course-create-form .course-select.active");
            var courseType = $courseType.data('type');
            var title = options.element.val();
            if (courseType === 'liveOpen' && !/^[^(<|>|'|"|&|‘|’|”|“)]*$/.test(title)) {
                commit(false, "直播公开课标题暂不支持<、>、\"、&、‘、’、”、“字符");
            } else {
                return true;
            }
        });

    exports.run = function () {
        $('[data-toggle="tooltip"]').tooltip();

        var $form = $('#course-create-form');

        if ($form.length > 0) {

            var validator = new Validator({
                element: $form,
                triggerType: 'change',
                onFormValidated: function (error) {
                    if (error) {
                        return false;
                    }
                    $('#course-create-btn').button('submiting').addClass('disabled');
                }
            });

            validator.addItem({
                element: '[name="title"]',
                required: true,
                rule: 'open_live_course_title',
                display: '标题'
            });

            $("#course-create-form .course-select").click(function () {
                $this = $(this);
                var courseType = $this.data('type');
                $this.not('.disabled').addClass('active').parent().siblings().find('.course-select').removeClass('active');
                $('input[name="type"]').val(courseType);
            })
        }
    };

});