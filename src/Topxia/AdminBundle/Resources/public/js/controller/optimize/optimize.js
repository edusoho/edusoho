define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        $('#removecache').on('click',  function() {
            $.post($(this).data('url'), function(response) {
                Notify.success(Translator.trans('刷新缓存成功！'));
            }).error(function(response){
                Notify.danger(Translator.trans('刷新缓存失败！'));
            });
        });
        $('#removeTmp').on('click',  function() {
            $.post($(this).data('url'), function(response) {
                Notify.success(Translator.trans('优化磁盘空间成功！'));
            }).error(function(response){
                Notify.danger(Translator.trans('优化磁盘空间失败！'));
            });
        });
        $('#removeBackup').on('click',  function() {
            if (!confirm(Translator.trans('确认要清空系统备份数据吗？'))) return false;
            $.post($(this).data('url'), function(response) {
                Notify.success(Translator.trans('清空系统备份数据成功！'));
            }).error(function(response){
                Notify.danger(Translator.trans('清空系统备份数据失败！'));
            });
        });
        $('#backupDatabase').on('click',  function() {
            $.post($(this).data('url'), function(response) {
                if(response.status=='ok'){
                    Notify.success(Translator.trans('备份数据库成功，请下载数据库，并及时优化磁盘空间！'));
                    $('#dbbackup').removeClass('hide');
                    $('#dbdownload').attr('href',response.result);
                }
            }).error(function(response){
                Notify.danger(Translator.trans('备份数据库失败！'));
            });
        });



    };

});