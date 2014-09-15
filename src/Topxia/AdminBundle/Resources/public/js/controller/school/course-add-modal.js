define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    
    require('jquery.select2-css');
    require('jquery.select2');
    exports.run = function() {
        var $form = $("#class-course-add-form");
        $form.find('#course-table tbody').on('click','tr',function(){
            $form.find('#course-table tbody').find('.success').removeClass('success');
            $(this).addClass('success');
            $form.find('#select-area').attr('class','show');
            $form.find('#title-span').html($(this).data('title'));
            $form.find('#name-span').html($(this).data('teachername'));
            $form.find('#parentId').val($(this).data('id'));
            $form.find('[name=compulsory][value=' + $(this).data('compulsory') + ']').prop('checked', true);
        });

        $form.find('[role=tablist]').on('click','li', function(){
            var $priorli = $(this).parent().find('.active');
            $priorli.removeClass('active');
            $(this).addClass('active');
            $.get($(this).data('url'),function(html) {
                $form.find('#select-area').attr('class','hidden');
                $form.find('.tab-target').html($(html).find('.tab-target').html());
                

                $form.find('#course-table tbody').on('click','tr',function(){
                    $form.find('#course-table tbody').find('.success').removeClass('success');
                    $(this).addClass('success');
                    $form.find('#select-area').attr('class','show');
                    $form.find('#title-span').html($(this).data('title'));
                    $form.find('#name-span').html($(this).data('teachername'));
                    $form.find('#parentId').val($(this).data('id'));
                    $form.find('[name=compulsory][value=' + $(this).data('compulsory') + ']').prop('checked', true);
                });

            });
        });

        var $modal = $('#class-create-form').parents('.modal');

        var validator = new Validator({
            element: '#class-course-add-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                $('#class-course-add-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success('添加课程成功');
                    window.location.href=$('#backto').data('url');
                }).error(function(result){
                    $('#class-course-add-btn').button('reset');
                    Notify.danger("添加课程失败");
                });

            }
        });

        validator.addItem({
            element: '#teacherId',
            required: true,
            errormessage:'请选择任课老师'
        });

        $('#teacherId').select2({
            ajax: {
                url: $('#teacherId').data('url') + '#',
                dataType: 'json',
                quietMillis: 100,
                data: function(term, page) {
                    return {
                        q: term,
                        page_limit: 10
                    };
                },
                results: function(data) {

                    var results = [];

                    $.each(data, function(index, item) {

                        results.push({
                            id: item.id,
                            name: item.name
                        });
                    });

                    return {
                        results: results
                    };

                }
            },
            initSelection: function(element, callback) {
            },
            formatSelection: function(item) {
                return item.name;
            },
            formatResult: function(item) {
                return item.name;
            },
            width: 'off',
            multiple: false,
            placeholder: "选择任课老师",
            createSearchChoice: function() {
                return null;
            }
        });

    };
});