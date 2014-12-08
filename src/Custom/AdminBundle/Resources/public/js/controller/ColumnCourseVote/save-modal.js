define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require("jquery.bootstrap-datetimepicker");
    var Notify = require('common/bootstrap-notify');
    exports.run = function() {
        var $form = $('#courseVotes-form');
        var $modal = $form.parents('.modal');
        var $table = $('#courseVotes-table');


        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return;
                }

                $('#courseVotes-create-btn').button('submiting').addClass('disabled');


                $.post($form.attr('action'), $form.serialize(), function(html) {
                     var $html = $(html);
                if(Object.prototype.toString.call(html) === "[object String]"){
                     
                    if ($table.find('#' + $html.attr('id')).length > 0) {
                        $('#' + $html.attr('id')).replaceWith($html);
                        Notify.success('专栏更新成功！');
                    } else {
                        $table.find('tbody').prepend(html);
                        Notify.success('专栏添加成功!');
                    }
                    $modal.modal('hide');
                }else{
                     Notify.warning($html.message);
                }
                
           
                });

            }
        });

        $("#voteStartTime").datetimepicker({
            format: 'yyyy-mm-dd hh:ii',
            language: 'zh-CN',
            todayBtn: true,
            autoclose: true,
            startDate: new Date(),
            todayHighlight: true,
            forceParse: false,
            pickerPosition: "top-right"
        });

        $("#voteEndTime").datetimepicker({
            format: 'yyyy-mm-dd hh:ii',
            language: 'zh-CN',
            todayBtn: true,
            autoclose: true,
            startDate: new Date(),
            todayHighlight: true,
            forceParse: false
        });



        validator.addItem({
            element: '[name=specialColumnId]',
            // element: '#specialColumnId',
            required: true
        });
        validator.addItem({
            element: '#courseAName',
            required: true
        });
        validator.addItem({
            element: '#courseBName',
            required: true
        });
        validator.addItem({
            element: '#voteStartTime',
            required: true
        });
        validator.addItem({
            element: '#voteEndTime',
            required: true
        });

        // $modal.find('.delete-column').on('click', function() {
        //     if (!confirm('真的要删除该专栏吗？')) {
        //         return ;
        //     }

        //     var trId = '#column-tr-' + $(this).data('columnId');
        //     $.post($(this).data('url'), function(html) {
        //         $modal.modal('hide');
        //         $table.find(trId).remove();
        //     });

        // });

    };



});