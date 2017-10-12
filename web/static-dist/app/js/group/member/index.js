webpackJsonp(["app/js/group/member/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	if ($('#exit-btn').length > 0) {
	    $('#exit-btn').click(function () {
	        if (!confirm(Translator.trans('group.manage.member_exit_hint'))) {
	            return false;
	        }
	    });
	}
	$('#delete-btn').click(function () {
	    if ($(":checkbox:checked").length < 1) {
	        alert(Translator.trans('group.manage.delete_required_error_hint'));
	        return false;
	    }
	    if (!confirm(Translator.trans('group.manage.delete_member_hint'))) {
	        return false;
	    }
	
	    $.post($("#member-form").attr('action'), $("#member-form").serialize(), function () {
	        (0, _notify2["default"])('success', Translator.trans('site.delete_success_hint'));
	        setTimeout(function () {
	            window.location.reload();
	        }, 1500);
	    }).error(function () {
	        (0, _notify2["default"])('danger', Translator.trans('site.delete_fail_hint'));
	    });
	});
	
	$('#set-admin-btn').click(function () {
	    if ($(":checkbox:checked").length < 1) {
	        alert(Translator.trans('group.manage.choose_setting_member_hint'));
	        return false;
	    }
	    if (!confirm(Translator.trans('group.manage.setting_member_permission_hint'))) {
	        return false;
	    }
	
	    $.post($("#set-admin-url").attr('value'), $("#member-form").serialize(), function () {
	        (0, _notify2["default"])('success', Translator.trans('site.save_success_hint'));
	        setTimeout(function () {
	            window.location.reload();
	        }, 1500);
	    }).error(function () {});
	});
	
	$('#remove-admin-btn').click(function () {
	    if ($(":checkbox:checked").length < 1) {
	        alert(Translator.trans('group.manage.choose_setting_member_hint'));
	        return false;
	    }
	    if (!confirm(Translator.trans('group.manage.cancel_member_permission'))) {
	        return false;
	    }
	
	    $.post($("#admin-form").attr('action'), $("#admin-form").serialize(), function () {
	        (0, _notify2["default"])('success', Translator.trans('site.save_success_hint'));
	        setTimeout(function () {
	            window.location.reload();
	        }, 1500);
	    }).error(function () {});
	});

/***/ })
]);
//# sourceMappingURL=index.js.map