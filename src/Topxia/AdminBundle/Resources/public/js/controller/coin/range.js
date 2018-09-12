define(function(require, exports, module) {
    
   var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    var Uploader = require('upload');
    require('common/validator-rules').inject(Validator);
    require('jquery.select2-css');
    require('jquery.select2');
    require('jquery.bootstrap-datetimepicker');
    require('jquery.form');
    require('es-ckeditor');

exports.run = function() {
    
        $("#article-property-tips").popover({
            html: true,
            trigger: 'hover',//'hover','click'
            placement: 'left',//'bottom',
            content: $("#article-property-tips-html").html()
        });

        var $form = $("#coin-settings-form"),
            $modal = $form.children('.coin_content');

        var validator = _initValidator($form, $modal);
        var $editor = _initEditorFields($form, validator);

        var uploader = new Uploader({
            trigger: '#coin-picture-upload',
            name: 'coin_picture',
            action: $('#coin-picture-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger(Translator.trans('admin.coin.picture_upload_fail_hint'))
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#coin-picture-largeSize").html('<img src="' + response.coin_picture_50_50 + '">');
                $("#coin-picture-middleSize").html('<img src="' + response.coin_picture_30_30 + '">');
                $("#coin-picture-smallSize").html('<img src="' + response.coin_picture_20_20 + '">');
                $("#coin-picture-extraSmallSize").html('<img src="' + response.coin_picture_10_10 + '">');
                $form.find('[name=coin_picture]').val(response.path);
                $form.find('[name=coin_picture_50_50]').val(response.path_50_50);
                $form.find('[name=coin_picture_30_30]').val(response.path_30_30);
                $form.find('[name=coin_picture_20_20]').val(response.path_20_20);
                $form.find('[name=coin_picture_10_10]').val(response.path_10_10);
                $("#coin-picture-remove").show();
                Notify.success(Translator.trans('admin.coin.picture_upload_success_hint'));
            }
        });

        $("#coin-picture-remove").on('click', function(){
            if (!confirm(Translator.trans('admin.coin.picture_delete_hint'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#coin-picture-largeSize").html('');
                $("#coin-picture-middleSize").html('');
                $("#coin-picture-smallSize").html('');
                $("#coin-picture-extraSmallSize").html('');
                $form.find('[name=coin_picture]').val('');
                $form.find('[name=coin_picture_50_50]').val('');
                $form.find('[name=coin_picture_30_30]').val('');
                $form.find('[name=coin_picture_20_20]').val('');
                $form.find('[name=coin_picture_10_10]').val('');
                $btn.hide();
                Notify.success(Translator.trans('admin.coin.picture_delete_success_hint'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.coin.picture_delete_fail_hint'));
            });
        });






    function _initValidator($form, $modal)
    {
        var validator = new Validator({
            element: $form,
            autoSubmit: false
        });

        return validator;
    }

    function _initEditorFields($form, validator)
    {
        // group: 'default'
        CKEDITOR.replace('coin_content', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#coin_content').data('imageUploadUrl')
        });
    }

        var global_number_reserved = [];
        var validator = new Validator({
            element: '#coin-settings-form'
        });

        $(document).ready(function(){
            var validator_someone = function(i){
                var min = "[name=coin_consume_range_min_"+i+"]";
                var pst = "[name=coin_present_"+i+"]";

                validator.addItem({
                    element: min,
                    required: true,
                    rule: 'integer'
                });
                validator.addItem({
                    element: pst,
                    required: true,
                    rule: 'integer'
                }); 
            };
            
            var reflash_validation = function(number){
                for (var i = 1; i <= number; i++) {
                    validator_someone(i);               
                };

            };

            var reflash_after_delete_range = function(){
                var str=$(this).parent().prev().children('input').attr('id');
                var i = str.charAt(str.length-1);
                var min = "[name=coin_consume_range_min_"+i+"]";
                var pst = "[name=coin_present_"+i+"]";
                validator.removeItem(min);
                validator.removeItem(pst);              
                $(this).parent().parent().parent('.range').remove();
                var range_number = parseInt($('#range_number').html())-1;
                $('#range_number').html(range_number);
                global_number_reserved.push(i);             
            };

            var range_number = parseInt($('#range_number').html());
            reflash_validation(range_number);
            $('.delete_range').click(reflash_after_delete_range);
            $('.add_range').click(function(){
                var _=document.getElementsByName('coin_template');
                var new_range= _[0].innerHTML;
                var range_number_or_reserved_pop = parseInt($('#range_number').html())+1;
                $('#range_number').html(range_number_or_reserved_pop);
                if (global_number_reserved.length > 0){
                    global_number_reserved.sort().reverse();
                    range_number_or_reserved_pop = global_number_reserved.pop();
                }
                new_range = new_range.replace(new RegExp('NUM','g'),range_number_or_reserved_pop);
                $('.ranges').append(new_range);
                validator_someone(range_number_or_reserved_pop);
                $('.delete_range'+range_number_or_reserved_pop).click(reflash_after_delete_range);
            });
        });
    };  
});