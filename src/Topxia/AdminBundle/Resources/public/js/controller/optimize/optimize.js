define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        $('#removecache').on('click',  function() {
            $.post($(this).data('url'), function(response) {
                Notify.success(Translator.trans('admin.optimize.remove_cache_success_hint'));
            }).error(function(response){
                Notify.danger(Translator.trans('admin.optimize.remove_cache_fail_hint'));
            });
        });
        $('#removeTmp').on('click',  function() {
            $.post($(this).data('url'), function(response) {
                Notify.success(Translator.trans('admin.optimize.remove_tmp_success_hint'));
            }).error(function(response){
                Notify.danger(Translator.trans('admin.optimize.remove_tmp_fail_hint'));
            });
        });
        $('#removeBackup').on('click',  function() {
            if (!confirm(Translator.trans('admin.optimize.remove_backup_hint'))) return false;
            $.post($(this).data('url'), function(response) {
                Notify.success(Translator.trans('admin.optimize.remove_backup_success_hint'));
            }).error(function(response){
                Notify.danger(Translator.trans('admin.optimize.remove_backup_fail_hint'));
            });
        });
        $('#backupDatabase').on('click',  function() {
            $.post($(this).data('url'), function(response) {
                if(response.status=='ok'){
                    Notify.success(Translator.trans('admin.optimize.backup_database_success_hint'));
                    $('#dbbackup').removeClass('hide');
                    $('#dbdownload').attr('href',response.result);
                }
            }).error(function(response){
                Notify.danger(Translator.trans('admin.optimize.backup_database_fail_hint'));
            });
        });



    };

});