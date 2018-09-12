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
                var user_name = $('#change-user-roles-btn').data('user') ;
                Notify.info(Translator.trans('admin.user.change_user_role_fail_hint',{user:user_name}));
            }
        });

        $form.on('submit', function() {
            var roles = [];

            var $modal = $('#modal');

            $form.find('input[name="roles[]"]:checked').each(function(){
                roles.push($(this).val());
            });

            if ($.inArray('ROLE_USER', roles) < 0) {
            	   var user_name = $('#change-user-roles-btn').data('user') ;
                Notify.danger(Translator.trans('admin.user.change_user_role_fail_hint',{user:user_name}));
                return false;
            }

            if (isTeacher && $.inArray('ROLE_TEACHER', roles) < 0) {
                if (!confirm(Translator.trans('admin.user.cancel_user_teacher_role_hint'))) {
                    return false;
                }
            }

            $form.find('input[value=ROLE_SUPER_ADMIN]').removeAttr('disabled');
            $('#change-user-roles-btn').button('submiting').addClass('disabled');
            $.post($form.attr('action'), $form.serialize(), function(html) {

                $modal.modal('hide');
                Notify.success(Translator.trans('admin.user.change_roles_success_hint'));
                var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            }).error(function(){
                Notify.danger(Translator.trans('admin.user.change_roles_fail_hint'));
            });

            return false;
        });
        /*var $checkbox = $('#new-checkboxs');
        $('#old-checkboxs').change(function(){
            if ($('#admin').prop('checked') === true) {
                $checkbox.show();
            } else {
                $('#new-checkboxs').find('[type=checkbox]:checked').attr('checked', false);
                $checkbox.hide();
            }
        });*/

	};

});