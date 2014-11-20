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
            $.get($(this).data('url'), {gradeId:$(this).val()}, function(result){
                result = '<option value="0">--通用--</option>' + result;
                $('#subjectId').html(result);
                $.get($('.select-section').data('url'), {subjectId:$('#subjectId').val(), gradeId:$('#gradeId').val()}, function(result){
                    if('id' in result) {
                        $('#materialId').val(result.id);
                        $('#material').val(result.name);
                    } else {
                        $('#materialId').val('');
                        $('#material').val('');
                    }
                    var zTree = $.fn.zTree.getZTreeObj("knowledge-tree");
                    zTree.setting.async.otherParam = {
                        'gradeId': $('#gradeId').val(),
                        'materialId': $('#materialId').val(),
                        'subjectId': $('#subjectId').val(),
                        'term': $('#term').val()
                    };
                    zTree.reAsyncChildNodes(null, "refresh");
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
                    var zTree = $.fn.zTree.getZTreeObj("knowledge-tree");
                    zTree.setting.async.otherParam = {
                        'gradeId': $('#gradeId').val(),
                        'materialId': $('#materialId').val(),
                        'subjectId': $('#subjectId').val(),
                        'term': $('#term').val()
                    };
                    zTree.reAsyncChildNodes(null, "refresh");
                });
            });
        });

    };

});