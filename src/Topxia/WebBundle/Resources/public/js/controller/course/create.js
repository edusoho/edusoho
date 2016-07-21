define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    exports.run = function() {

        var $form = $('#course-create-form');

        if($form.length>0) {

            var validator = new Validator({
                element: $form,
                triggerType: 'change',
                onFormValidated: function(error) {
                    if (error) {
                        return false;
                    }
                    $('#course-create-btn').button('submiting').addClass('disabled');
                }
            });

            validator.addItem({
                element: '[name="title"]',
                required: true,
                display: '标题'
            });

           $("#course-create-form .course-select").click(function(){
                $this = $(this);
                var courseType = $this.data('type');
                $this.not('.disabled').addClass('active').parent().siblings().find('.course-select').removeClass('active');

                $form.find('input[name="type"]').val(courseType);
           })
        }
    };

});