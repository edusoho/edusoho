define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    var validator = require('bootstrap.validator');

    exports.run = function() {

        var $datePicker = $('#datePicker');
        var $table = $('#user-table');

        $table.on('click', '.lock-user, .unlock-user', function() {
            var $trigger = $(this);

            if (!confirm('真的要' + $trigger.attr('title') + '吗？')) {
                return ;
            }

            $.post($(this).data('url'), function(html){
                Notify.success($trigger.attr('title') + '成功！');
                 var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            }).error(function(){
                Notify.danger($trigger.attr('title') + '失败');
            });
        });

        $table.on('click', '.send-passwordreset-email', function(){
            Notify.info('正在发送密码重置验证邮件，请稍等。', 60);
            $.post($(this).data('url'),function(response){
                Notify.success('密码重置验证邮件，发送成功！');
            }).error(function(){
                Notify.danger('密码重置验证邮件，发送失败');
            });
        });

        $table.on('click', '.send-emailverify-email', function(){
            Notify.info('正在发送Email验证邮件，请稍等。', 60);
            $.post($(this).data('url'),function(response){
                Notify.success('Email验证邮件，发送成功！');
            }).error(function(){
                Notify.danger('Email验证邮件，发送失败');
            });
        });

                var $userSearchForm = $('#user-search-form');
                var $roles = $userSearchForm.find('[name=roles]').val();
                var $datePicker = $userSearchForm.find('[name=datePicker]').val();
                var $keywordType = $userSearchForm.find('[name=keywordType]').val();
                var $keyword = $userSearchForm.find('[name=keyword]').val();
                var $keywordUserType = $userSearchForm.find('[name=keywordUserType]').val();

                var $StartDate = $userSearchForm.find('[name=StartDate]').val();
                var $EndDate = $userSearchForm.find('[name=EndDate]').val();

                function getUnixTime(dateStr)
                    {
                        if (dateStr == '') {
                        return '';
                        }else{
                            var newstr = dateStr.replace(/-/g,'/'); 
                            var date =  new Date(newstr); 
                            var time_str = date.getTime().toString();
                        return time_str.substr(0, 10);
                        }
                    }

                var $StartDate = getUnixTime($StartDate);
                var $EndDate = getUnixTime($EndDate);

                $('#user-export').on('click', function() {
                   var self = $(this);
                   self.attr('data-url', self.attr('data-url')+"?roles="+$roles
                    +"&keywordType="+$keywordType
                    +"&keyword="+$keyword
                    +"&keywordUserType="+$keywordUserType
                    +"&StartDate="+$StartDate
                    +"&EndDate="+$EndDate
                    +"&datePicker="+$datePicker
                    );
                });

        $("#StartDate").datetimepicker({
            autoclose: true
        }).on('changeDate',function(){
            $("#EndDate").datetimepicker('setStartDate',$("#StartDate").val().substring(0,16));
        });

        $("#StartDate").datetimepicker('setEndDate',$("#EndDate").val().substring(0,16));

        $("#EndDate").datetimepicker({
            autoclose: true
        }).on('changeDate',function(){

            $("#StartDate").datetimepicker('setEndDate',$("#EndDate").val().substring(0,16));
        });

        $("#EndDate").datetimepicker('setStartDate',$("#StartDate").val().substring(0,16));
    };

});