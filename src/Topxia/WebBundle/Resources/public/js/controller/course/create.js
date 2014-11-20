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

        $('#gradeId').on('change', function(){
            $.get($(this).data('url'), {gradeId:$(this).val()}, function(html){
                html = '<option value="0">--通用--</option>' + html;
                $('#subjectId').html(html);
                $.get($('#course-create-form').data('url'), { subjectId:$('#subjectId').val(), gradeId:$('#gradeId').val() }, function(result){
                    if('id' in result) {
                        $('#materialId').val(result.id);
                        $('#material').val(result.name);
                    } else {
                        $('#materialId').val('');
                        $('#material').val('');
                    }
                });
            });
        });
        $('#subjectId').on('change', function(){
            $.get($('#course-create-form').data('url'), {subjectId:$('#subjectId').val(), gradeId:$('#gradeId').val()}, function(result){
                if('id' in result) {
                    $('#materialId').val(result.id);
                    $('#material').val(result.name);
                } else {
                    $('#materialId').val('');
                    $('#material').val('');
                }
            });
        });

    };

});