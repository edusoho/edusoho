define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    
    require('jquery.select2-css');
    require('jquery.select2');
    exports.run = function() {


        $('#course-table tbody tr').on('click',function(){
            $('#course-table tbody').find('.success').removeClass('success');
            $(this).addClass('success');
            $('#select-area').attr('class','show');
            $('#title-span').html($(this).data('title'));
            $('#name-span').html($(this).data('teachername'));
            $('#templateId').val($(this).data('id'));
        });

/*        $('[role=tablist]').on('click','li', function(){
            var $priorli = $(this).parent().find('.active');
            console.log($priorli);
            $priorli.removeClass('active');
            $($priorli.data('target')).addClass('hidden');
            $(this).addClass('active');
            $($(this).data('target')).removeClass('hidden').addClass('show');
        });
*/
        var $modal = $('#class-create-form').parents('.modal');
        var $form = $("#class-course-add-form");

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
                }).error(function(){
                    Notify.danger('添加课程失败');
                });

            }
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