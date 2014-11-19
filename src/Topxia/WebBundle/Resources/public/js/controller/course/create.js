define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');

    exports.run = function() {
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
        
        validator.addItem({
            element: '[name="subjectId"]',
            required: true
        });

        validator.addItem({
            element: '[name="materialId"]',
            required: true,
            errormessage: '无教材可用!!'
        });

        $('#course-create-form').on('change', '.event', function(){
            $.get($('#course-create-form').data('url'), {subjectId:$('#subjectId').val(), gradeId:$('#gradeId').val()}, function(result){
                if('id' in result) {
                    $('#materialId').val(result.id);
                    $('#material').val(result.name);
                } else {
                    $('#materialId').val('');
                    $('#material').val('');
                }
            })
        });

    };

});