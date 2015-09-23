define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    var validator = require('bootstrap.validator');

    exports.run = function() {

        var $datePicker = $('#datePicker');
        var $table = $('#user-table');
        
        $datePicker.on('click',function(){
            if($datePicker.val()=='longinDate'){
                $('.longinDate').show();
                $('.registerDate').hide();
            }else{
                $('.longinDate').hide();
                $('.registerDate').show();
            }
        });


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
                var $keywordType = $userSearchForm.find('[name=keywordType]').val();
                var $keyword = $userSearchForm.find('[name=keyword]').val();
                var $keywordUserType = $userSearchForm.find('[name=keywordUserType]').val();

                var $loginStartDate1 = $userSearchForm.find('[name=loginStartDate]').val();
                var $loginEndDate1 = $userSearchForm.find('[name=loginEndDate]').val();
                var $registerStartDate1 = $userSearchForm.find('[name=registerStartDate]').val();
                var $registerEndDate1 = $userSearchForm.find('[name=registerEndDate]').val();

                function get_unix_time(dateStr)
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

                var $loginStartDate = get_unix_time($loginStartDate1);
                var $loginEndDate = get_unix_time($loginEndDate1);
                var $registerStartDate = get_unix_time($registerStartDate1);
                var $registerEndDate = get_unix_time($registerEndDate1);

                

                $('#user-export').on('click', function() {
                   var self = $(this);
                   self.attr('data-url', self.attr('data-url')+"?roles="+$roles
                    +"&keywordType="+$keywordType
                    +"&keyword="+$keyword
                    +"&keywordUserType="+$keywordUserType
                    +"&loginStartTime="+$loginStartDate
                    +"&loginEndTime="+$loginEndDate
                    +"&startTime="+$registerStartDate
                    +"&endTime="+$registerEndDate
                    );
                });

        $("#loginStartDate").datetimepicker({
            autoclose: true
        }).on('changeDate',function(){
            $("#loginEndDate").datetimepicker('setStartDate',$("#loginStartDate").val().substring(0,16));
        });

        $("#loginStartDate").datetimepicker('setEndDate',$("#loginEndDate").val().substring(0,16));

        $("#loginEndDate").datetimepicker({
            autoclose: true
        }).on('changeDate',function(){

            $("#loginStartDate").datetimepicker('setEndDate',$("#loginEndDate").val().substring(0,16));
        });

        $("#loginEndDate").datetimepicker('setStartDate',$("#loginStartDate").val().substring(0,16));

        ///
        $("#registerStartDate").datetimepicker({
            autoclose: true
        }).on('changeDate',function(){
            $("#registerEndDate").datetimepicker('setStartDate',$("#registerStartDate").val().substring(0,16));
        });

        $("#registerStartDate").datetimepicker('setEndDate',$("#registerEndDate").val().substring(0,16));

        $("#registerEndDate").datetimepicker({
            autoclose: true
        }).on('changeDate',function(){

            $("#registerStartDate").datetimepicker('setEndDate',$("#registerEndDate").val().substring(0,16));
        });

        $("#registerEndDate").datetimepicker('setStartDate',$("#registerStartDate").val().substring(0,16));

    };

});