define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $form = $("#message-search-form"),
            $modal = $form.parents('.modal'),
            $table = $("#classroom-table");

        $form.submit(function(e) {
            e.preventDefault();
            $.get($form.attr('action'), $form.serialize(), function(html) {
                $modal.html(html);
            });
        });

        $table.on('click', '.choose-classroom', function(e){
            var classroomId = $(this).data('target');
            var classroomName = $(this).data('name');
            var html = '<a href="/classroom/'+classroomId+'" target="_blank"><strong>'+classroomName+'</strong></a>';
            $('#choose-classroom-input').val(classroomId);
            $('#course-display .well').html(html);
            $('#course-display').show();
            $modal.modal('hide');
            Notify.success(Translator.trans('admin.classroom.choose_success_hint'));
        });

        $modal.on('hidden.bs.modal', function (e) {
            if (!$('#choose-classroom-input').val()) {
                $('.js-classroom-radios').button('reset');
            };
        });
    };
})