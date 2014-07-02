define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

	exports.run = function() {
        var $form = $("#user-roles-form"),
            isTeacher = $form.find('input[value=ROLE_TEACHER]').prop('checked'),
            currentUser = $form.data('currentuser'),
            editUser = $form.data('edituser');

        if (currentUser == editUser) {
            $form.find('input[value=ROLE_SUPER_ADMIN]').attr('disabled', 'disabled');
        };

        $form.find('input[value=ROLE_USER]').on('change', function(){
            if ($(this).prop('checked') === false) {
                $(this).prop('checked', true);
                Notify.info('用户必须拥有学员角色');
            }
        });

        $form.on('submit', function() {
            var roles = [];

            var $modal = $('#modal');

            $form.find('input[name="roles[]"]:checked').each(function(){
                roles.push($(this).val());
            });

            if ($.inArray('ROLE_USER', roles) < 0) {
                Notify.danger('用户必须拥有学员角色');
                return false;
            }

            if (isTeacher && $.inArray('ROLE_TEACHER', roles) < 0) {
                if (!confirm('取消该用户的教师角色，同时将收回该用户所有教授的课程的教师权限。您真的要这么做吗？')) {
                    return false;
                }
            }

            $form.find('input[value=ROLE_SUPER_ADMIN]').removeAttr('disabled');
            $('#change-user-roles-btn').button('submiting').addClass('disabled');
            $.post($form.attr('action'), $form.serialize(), function(html) {

                $modal.modal('hide');
                Notify.success('用户组保存成功');
                var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            }).error(function(){
                Notify.danger('操作失败');
            });

            return false;
        });

	};

});