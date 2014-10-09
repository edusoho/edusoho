define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

    if($('#exit-btn').length>0){
        $('#exit-btn').click(function(){
            if (!confirm( '真的要退出该小组？您在该小组的信息将删除！')) {
                    return false;
            }
        })

    }
  	$('#delete-btn').click(function(){
        if($(":checkbox:checked").length <1){
                alert("请选择要踢出的成员！");
                return false;
            }
		if (!confirm( '真的要踢出该成员？')) {
                    return false;
                 }

        $.post($("#member-form").attr('action'),$("#member-form").serialize(), function() {
            Notify.success('踢出成功！');
            setTimeout(function(){window.location.reload();},1500); 
               
            }).error(function(){
              
            });


      	})

    $('#set-admin-btn').click(function(){
        if($(":checkbox:checked").length <1){
                alert("请选择要任职的成员！");
                return false;
            }
        if (!confirm( '真的要任职该成员？')) {
                    return false;
                 }

        $.post($("#set-admin-url").attr('value'),$("#member-form").serialize(), function() {
            Notify.success('任职成功！');
            setTimeout(function(){window.location.reload();},1500); 
               
            }).error(function(){
              
            });


        })

    $('#remove-admin-btn').click(function(){
        if($(":checkbox:checked").length <1){
                alert("请选择要设置的成员！");
                return false;
            }
        if (!confirm( '真的要取消副组长？')) {
                    return false;
                 }

        $.post($("#admin-form").attr('action'),$("#admin-form").serialize(), function() {
            Notify.success('设置成功！');
            setTimeout(function(){window.location.reload();},1500); 
               
            }).error(function(){
              
            });


        })

    };
  
});