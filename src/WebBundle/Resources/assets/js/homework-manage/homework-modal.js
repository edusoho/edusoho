define(function(require, exports, module) {

    require('ckeditor');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    // group: 'default'
    var editor = CKEDITOR.replace('homework-about-field', {
        toolbar: 'Minimal',
        filebrowserImageUploadUrl: $('#homework-about-field').data('imageUploadUrl')
    });



    var $modal = $("#modal");
    var Widget = require('widget');
    require('jquery.sortable');

    var HomeworkItemManager = Widget.extend({

        attrs: {
            currentType: null
        },

        events: {
            'click [data-role=pick-item]': 'onClickPickItem',
            'click [data-role=batch-select]': 'onClickBatchSelect',
            'click [data-role=batch-item]': 'onClickItemBatchSelect',
            'click [data-role=batch-delete]': 'onClickBatchDelete',
            'click .item-delete-btn': 'onClickItemDelete',
            'click .request-save': 'onRequestSave',
            'click .confirm-submit': 'onConfirmSubmit',
            'blur .correctPercent1, .correctPercent2, .correctPercent3': 'checkCorrectPercent',
        },

        setup: function() {
            this.initItemSortable();
        },

        onRequestSave: function(e) {
            var $tbodyValueLength = $('#homework-table-tbody:has(tr)').length;
            
            if ($tbodyValueLength == 0 && $('[data-role=homework-edit]').length == 0) {
                $('#homework_items_help').css('color', '#a94442');
                $('#homework_items_help').show();

                return false;
            } else {
                $('#homework_items_help').hide();
            }

            if ($('.correct_percent_help').html() != '') {
                return false;
            }

            $modal = $("#homework-confirm-modal");
            $modal.modal('show');
        },

        onConfirmSubmit: function(e) {

            editor.updateElement();
            var $btn = $('.confirm-submit');
            var $description = editor.getData();
            var $completeLimit = $('[name="completeLimit"]:checked').val();
            var excludeIds = [];
            var correctPercent = [];
            var $tbodyValueLength = $('#homework-table-tbody:has(tr)').length;

            if ($('#homework-create-form').length == 0) {

                $btn.attr("disabled", true);
                if ($('[data-role=homework-edit]').length > 0) {
                    $btn.button('saving');
                    $.post($btn.data('url'), {
                        description: $description
                    }, function(response) {
                        if (response.status == 'success') {
                            window.location.href = "/course/" + response.courseId + "/manage/lesson";
                        }
                    });
                };

            } else {
                //课时建议时长发布时删除这里
                /*if (!this.onRequestSave()) {
                    return false;
                }*/

                $btn.button('saving');

                $("#homework-table-tbody").find('[name="questionId[]"]').each(function() {
                    excludeIds.push($(this).val());
                });

                if ($('[name="correctPercent[]"]').length > 0) {
                    $('.correctPercentDiv').find('[name="correctPercent[]"]').each(function() {
                        correctPercent.push(parseInt($(this).val()));
                    })
                }


                $.post($btn.data('url'), {
                    description: $description,
                    completeLimit: $completeLimit,
                    excludeIds: excludeIds.join(','),
                    correctPercent: correctPercent
                }, function(response) {
                    if (response.status == 'success') {
                        window.location.href = "/course/" + response.courseId + "/manage/lesson";
                    }
                });
            }

        },

        onClickBatchSelect: function(e) {

            if ($(e.currentTarget).is(":checked") == true) {
                this.$('[data-role=batch-select]:visible, [data-role=batch-item]:visible').prop('checked', true);
            } else {
                this.$('[data-role=batch-select]:visible, [data-role=batch-item]:visible').prop('checked', false);
            }
        },

        onClickItemBatchSelect: function(e) {
            var checkedCount = 0;
            var length = this.$('[data-role=batch-item]:visible').length;
            this.$('[data-role=batch-item]').each(function() {
                if ($(this).is(':checked')) {
                    checkedCount++;
                }
            });

            if (checkedCount == length) {
                this.$('[data-role=batch-select]:visible').prop('checked', true);
            } else {
                this.$('[data-role=batch-select]:visible').prop('checked', false);
            }
        },

        onClickBatchDelete: function(e) {

            var ids = [];
            this.$('[data-role=batch-item]:checked').each(function() {
                ids.push(this.value);
            });

            if (ids.length == 0) {
                Notify.danger('未选中任何题目');
                return;
            }

            if (!confirm('确定要删除选中的题目吗？')) {
                return;
            }

            this.$('[data-role=batch-item]:checked').each(function() {
                var $tr = $(this).parents('tr');

                $tr.parents('tbody').find('[data-parent-id=' + $tr.data('id') + ']').remove();
                $tr.remove();
            });

            this.$('[data-role=batch-select]:visible').prop('checked', false);

            this.refreshSeqs();
            this.refreshPassedDivShow();
        },

        onClickItemDelete: function(e) {
            var $btn = $(e.target);
            if (!confirm('您真的要删除该题目吗？')) {
                return;
            }
            var $tr = $btn.parents('tr');
            $tr.parents('tbody').find('[data-parent-id=' + $tr.data('id') + ']').remove();
            $tr.remove();
            this.refreshSeqs();
            this.refreshPassedDivShow();
        },

        onClickPickItem: function(e) {

            var $btn = $(e.currentTarget);

            var excludeIds = [];

            $('#save-homework-btn').attr("disabled", false);

            $("#homework-table-tbody").find('[name="questionId[]"]').each(function() {
                excludeIds.push($(this).val());
            });

            var $modal = $("#modal").modal();
            $modal.data('manager', this);
            $.get($btn.data('url'), {
                excludeIds: excludeIds.join(',')
            }, function(html) {
                $modal.html(html);
            });
        },

        refreshSeqs: function() {
            var seq = 1;
            $("#homework-table").find("tbody tr").each(function() {
                var $tr = $(this);
                $tr.find('td.seq').html(seq);
                seq++;
            });
        },

        refreshPassedDivShow: function() {
            var hasEssay = false;
            $("#homework-table-tbody>tr").each(function() {
                if ($(this).data('type') == 'essay' || $(this).data('type') == 'material') {
                    hasEssay = true;
                }
            });

            if (hasEssay) {
                $(".correctPercentDiv").html('');
            } else {
                var html = '这是一份纯客观题的作业，正确率达到为' +
                    '<input type="text" name="correctPercent[]" class="form-control width-input width-input-mini correctPercent1" value="60" />％合格，' +
                    '<input type="text" name="correctPercent[]" class="form-control width-input width-input-mini correctPercent2" value="80" />％良好，' +
                    '<input type="text" name="correctPercent[]" class="form-control width-input width-input-mini correctPercent3" value="100" />％优秀';

                $(".correctPercentDiv").html(html);
            }
        },

        initItemSortable: function(e) {
            var $table = this.$("#homework-table-tbody"),
                self = this;
            $list = $table.sortable({
                // containerPath: '> tr',
                itemSelector: 'tr.is-question',
                placeholder: '<tr class="placeholder"/>',
                // exclude: '.notMoveHandle',
                onDrop: function(item, container, _super) {
                    _super(item, container);
                    self.refreshSeqs();
                },
            });
        },

        checkCorrectPercent: function(e) {
            var isEmpty = false;
            var isInteger = true;
            var $error = $('.correct_percent_help');
            var isSame = false;
            var valArr = [];
            $('input[name="correctPercent[]"]').each(function() {
                var val = $(this).val();
                if (val == '') {
                    isEmpty = true;
                }
                var isPositive_integer = /^[0-9]*[1-9][0-9]*$/.test(val);
                if (!isPositive_integer || Number(val) > 100) {
                    isInteger = false;
                }
                
                valArr.push(Number(val));
            })

            if (isEmpty) {
                $error.html('请输入正确率').css('color', '#a94442');
                return false;
            }

            if (!isInteger) {
                $error.html('正确率只能是<=100、且>0的整数').css('color', '#a94442');
                return false;
            }

            if (valArr[0] >= valArr[1] || valArr[1] >= valArr[2] || valArr[0] >= valArr[2]) {
                $error.html('正确率不能相等且要依次递增').css('color','#a94442');
                return false;
            }

            $error.html('');
            return true;
        },
    });
    exports.run = function() {

        new HomeworkItemManager({
            element: '#homework-items-manager'
        });

        require('/bundles/topxiaweb/js/controller/course-manage/header').run();
    };

});