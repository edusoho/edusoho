define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    require('jquery.colorbox');

    exports.run = function() {

        var $panel = $('#material-lib-items-panel');
        require('../../../../topxiaweb/js/util/batch-select')($panel);

        $(".tip").tooltip();

        var $list = $("#material-item-list");

        $list.on('mouseover', '.item-material', function(e) {
            $(".file-name-container", this).hide();
            $(".action-buttons-container", this).show();
        });

        $list.on('mouseout', '.item-material', function(e) {
            $(".file-name-container", this).show();
            $(".action-buttons-container", this).hide();
        });

        $list.on('click', '.delete-material-btn', function(e) {
            var warning = '您真的要删除该文件吗？';

            if ($(this).data('link-count') > 0) {
                warning = "该文件目前正被 " + $(this).data('link-count') + " 个地方使用。 删除文件将导致这些地方不可用。\n\n" + warning;
            }

            if (!confirm(warning)) {
                return;
            }

            var $btn = $(e.currentTarget);

            $.post($(this).data('url'), function(response) {
                $btn.parents('.item-material').remove();
                Notify.success('文件已删除！');
                window.location.reload();
            }, 'json');
        });

        // Batch delete (there is a special logic for this page, so we're not using the common batch-delete.js)
        $panel.on('click', '[data-role=batch-delete]', function() {
            var $btn = $(this);
            var name = $btn.data('name');

            var ids = [];
            var warning = "";

            $panel.find('[data-role=batch-item]:checked').each(function() {
                ids.push(this.value);

                if ($(this).data('link-count') > 0) {
                    warning = "\t\"" + $(this).data('file-name') + "\"目前被 " + $(this).data('link-count') + " 个地方使用。 \n" + warning;
                }
            });

            if (warning.length > 0) {
                warning = "下列文件目前正被其它地方使用： \n\n" + warning + "\n删除文件将导致这些地方不可用。\n\n";
            }

            if (ids.length == 0) {
                Notify.danger('未选中任何' + name);
                return;
            }

            if (!confirm(warning + '确定要删除选中的' + ids.length + '条' + name + '吗？')) {
                return;
            }

            $panel.find('.btn').addClass('disabled');

            Notify.info('正在删除' + name + '，请稍等。', 60);

            $.post($btn.data('url'), {
                ids: ids
            }, function(response) {
                window.location.reload();
            });

        });


        $list.on('click', '.convert-file-btn', function() {
            $.post($(this).data('url'), function(response) {
                if (response.status == 'error') {
                    alert(response.message);
                } else {
                    window.location.reload();
                }
            }, 'json').fail(function() {
                alert('文件转换提交失败，请重试！');
            });
        });

        $('.tip').tooltip();

        $("#modal").modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

        $('.image-preview').colorbox({innerWidth:'70%',innerHeight:'70%',rel:'group1', photo:true, current:'{current} / {total}', title:function() {
            return $(this).data('fileName');
        }});

        asyncLoadFiles();
    }

    function asyncLoadFiles()
    {
      var fileIds = new Array();
        $('#material-item-list [type=checkbox]').each(function(){
            if(!isNaN($(this).val())){
                fileIds.push($(this).val());
            }
        });

        if(fileIds.length==0){
          return ;
        }

        $.get("/course/manage/file/status?ids="+fileIds.join(","),'',function(data){
            if(!data||data.length==0){
                return ;
            }
            
            for(var i=0;i<data.length;i++){
              var file=data[i];
              if(file.convertStatus=='waiting'||file.convertStatus=='doing'){
                $(".convertInfo"+file.id).append("<br><span class='text-warning text-sm'>正在文件格式转换</span>");
              }else if(file.convertStatus=='error'){
                $(".convertInfo"+file.id).append("<br><span class='text-danger text-sm'>文件格式转换失败</span>");
              }
            }
        });
    }

});