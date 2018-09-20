define(function(require, exports, module) {
        
    exports.run = function() {
        var $element = $('#question-table-container');
        var Notify = require('common/bootstrap-notify');

        require('../../util/short-long-text')($element);
        require('../../util/batch-select')($element);
        require('../../util/batch-delete')($element);
        require('../../util/item-delete')($element);

         $('.tbody').on('click', 'a.remind-teachers', function() {
            $.post($(this).data('url'), function(response) {
                Notify.success(Translator.trans('admin.course.remind_teacher_success_hint'));
            });
        });

    };

  });

