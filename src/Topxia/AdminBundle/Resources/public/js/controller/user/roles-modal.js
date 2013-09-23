define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

	exports.run = function() {
        var $form = $("#user-roles-form"),
            isTeacher = $form.find('input[value=ROLE_TEACHER]').prop('checked');

        $form.find('input[value=ROLE_USER]').on('change', function(){
            if ($(this).prop('checked') === false) {
                $(this).prop('checked', true);
                Notify.info('用户必须拥有会员角色');
            }
        });

        $form.on('submit', function() {
            var roles = [];

            $form.find('input[name="roles[]"]:checked').each(function(){
                roles.push($(this).val());
            });

            if ($.inArray('ROLE_USER', roles) < 0) {
                Notify.danger('用户必须拥有会员角色');
                return false;
            }

            if (isTeacher && $.inArray('ROLE_TEACHER', roles) < 0) {
                if (!confirm('取消该用户的教师角色，同时将收回该用户所有教授的课程的教师权限。您真的要这么做吗？')) {
                    return false;
                }
            }

            return true;
        });

	};

});