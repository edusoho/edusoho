define(function(require, exports, module) {
     var Uploader = require('upload');
     var Notify = require('common/bootstrap-notify');
     var ids = [];
     var descriptions = [];
     var coins = [];
     var names = [];
     $('#uploadModal').on('click', '.del-file', function() {

         var id = $(this).attr("data-id");

         if (!$('#file1-' + id).length > 0) {
             $.post("/group/attach/delete/" + id);
         }

         $('#file1-' + id).remove();
         $('#file-' + id).remove();

         var coin = $('input[name="coin[]"]');
         $.each(coin, function(i, item) {

             coins.push(item.value);

         });

         if ($('.del-file').length == 0) {

            $('.cke_button__accessory_icon').css("background-image", "url('/assets/img/default/iconfont-accessory.png')");

         }

     });

     $('#sure').on('click', function() {

         var id = $('input[name="id[]"]');
         var description = $('input[name="description[]"]');
         var coin = $('input[name="coin[]"]');

         $('.file').remove();
         $.each(id, function(i, item) {


             $('.thread-form').append('<input type="hidden" class="file" name="file[id][]" value="' + item.value + '">');

         });

         $.each(description, function(i, item) {

             $('.thread-form').append('<input type="hidden" class="file" name="file[title][]" value="' + item.title + '">');
             $('.thread-form').append('<input type="hidden" class="file" name="file[description][]" value="' + item.value + '">');

         });

         $.each(coin, function(i, item) {

             amount = parseInt(item.value);
             if (amount > 0) {

                 $('.thread-form').append('<input type="hidden" class="file" name="file[coin][]" value="' + amount + '">');

             } else {

                 $('.thread-form').append('<input type="hidden" class="file" name="file[coin][]" value="0">');
             }


         });

         $('#uploadModal').modal('hide');

     });



     $('#uploadModal').find('.upload-img').each(function(index, el) {

         var uploader = new Uploader({
             trigger: $(el),
             name: 'file',
             action: $(el).data('url'),
             data: {
                 '_csrf_token': $('meta[name=csrf-token]').attr('content')
             },
         }).success(function(response) {

             var response = eval("(" + response + ")");

             $('#block-table').append('<tr id="file1-' + response.id + '" ><td><label class="control-label"><span class="glyphicon glyphicon-folder-close"></span> ' + response.name + '</label></td><td><input type="hidden" name="id[]" value="' + response.id + '"/><input type="text" class="form-control" name="description[]" title="' + response.name + '"></td><td><input type="text" name="coin[]" class="form-control"></td><td><button type="button" class="del-file btn btn-default" data-id="' + response.id + '" >删除</button></td></tr>');

             $('.cke_button__accessory_icon').css("background-image", "url('/assets/img/default/iconfont-accessory-red.png')");

         }).error(function(message) {
             Notify.danger("上传失败！请查看文件类型和大小！")

         });

     });

 });