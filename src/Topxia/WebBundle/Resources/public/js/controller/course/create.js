define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');

    exports.run = function() {

        if($("#course-create-form").length>0) {

            var validator = new Validator({
                element: '#course-create-form',
                triggerType: 'change',
                onFormValidated: function(error){
                    if (error) {
                        return false;
                    }
                    $('#course-create-btn').button('submiting').addClass('disabled');
                }
            });

            validator.addItem({
                element: '[name="title"]',
                required: true
            });

            $('input[name="type"]').click(function(){
                var typeValue = $(this).val();
                if(typeValue == 'open' || typeValue == 'liveOpen') {
                    $('#course-buy-div').hide();
                } else {
                    $('#course-buy-div').show();
                }
            })
           $("#course-create-form .course-select").click(function(){
                $this = $(this);
                $this.addClass('active').parent().siblings().find('.course-select').removeClass('active');
           })
        }
    };

});