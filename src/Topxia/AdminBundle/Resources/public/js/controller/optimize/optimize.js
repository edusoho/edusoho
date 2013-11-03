define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        $('#removecache').on('click',  function() {
            $.post($(this).data('url'), function(response) {
                Notify.success('刷新缓存成功！');
            }).error(function(response){
                Notify.danger('刷新缓存失败！');
            });
        });
        $('#removeTmp').on('click',  function() {
            $.post($(this).data('url'), function(response) {
                Notify.success('移除临时数据成功！');
            }).error(function(response){
                Notify.danger('移除临时数据失败！');
            });
        });
        $('#removeBackup').on('click',  function() {
            if (!confirm('确认要移除自动升级过程中产生的备份数据吗？')) return false;
            $.post($(this).data('url'), function(response) {
                Notify.success('移除备份数据成功！');
            }).error(function(response){
                Notify.danger('移除备份数据失败！');
            });
        });
        $('#backupDatabase').on('click',  function() {
            $.post($(this).data('url'), function(response) {
                if(response.status=='ok'){
                    Notify.success('备份数据库成功，请下载数据库，并及时清除临时文件！');
                    $('#dbbackup').removeClass('hide');
                    $('#dbdownload').attr('href',response.result);
                }
            }).error(function(response){
                Notify.danger('备份数据库失败！');
            });
        });

        // $("#popular-courses-type").on('change', function() {
        //     $.get($(this).data('url'), {dateType: this.value}, function(html) {
        //         $('#popular-courses-table').html(html);
        //     });
        // }).trigger('change');

        // $("#site-logo-remove").on('click', function(){
        //     var $btn = $(this);

        //     $.post($btn.data('url'), function(){
        //         $("#site-logo-container").html('');
        //         $form.find('[name=logo]').val('');
        //         $btn.hide();
        //         Notify.success('删除网站LOGO成功！');
        //     }).error(function(){
        //         Notify.danger('删除网站LOGO失败！');
        //     });


    };

});