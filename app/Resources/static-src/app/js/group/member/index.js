import notify from 'common/notify';

if ($('#exit-btn').length > 0) {
    $('#exit-btn').click(function() {
        if (!confirm(Translator.trans('真的要退出该小组？您在该小组的信息将删除'))) {
            return false;
        }
    })

}
$('#delete-btn').click(function() {
    if ($(":checkbox:checked").length < 1) {
        alert(Translator.trans('请选择要删除的成员'));
        return false;
    }
    if (!confirm(Translator.trans('真的要删除该成员？'))) {
        return false;
    }

    $.post($("#member-form").attr('action'), $("#member-form").serialize(), function() {
        notify('success',Translator.trans('删除成功'));
        setTimeout(function() { window.location.reload(); }, 1500);
    }).error(function() {
        notify('danger',Translator.trans('删除失败'));
    });
})

$('#set-admin-btn').click(function() {
    if ($(":checkbox:checked").length < 1) {
        alert(Translator.trans('请选择要设置的成员'));
        return false;
    }
    if (!confirm(Translator.trans('确认要设置该成员的权限？'))) {
        return false;
    }

    $.post($("#set-admin-url").attr('value'), $("#member-form").serialize(), function() {
        notify('success',Translator.trans('设置成功'));
        setTimeout(function() { window.location.reload(); }, 1500);

    }).error(function() {

    });

})

$('#remove-admin-btn').click(function() {
    if ($(":checkbox:checked").length < 1) {
        alert(Translator.trans('请选择要设置的成员'));
        return false;
    }
    if (!confirm(Translator.trans('确认要取消该成员的权限？'))) {
        return false;
    }

    $.post($("#admin-form").attr('action'), $("#admin-form").serialize(), function() {
        notify('success',Translator.trans('设置成功'));
        setTimeout(function() { window.location.reload(); }, 1500);

    }).error(function() {

    });


})