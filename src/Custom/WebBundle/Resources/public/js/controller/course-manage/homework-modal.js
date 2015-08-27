define(function (require, exports, module) {

    var Validator = require('bootstrap.validator');
    require("jquery.bootstrap-datetimepicker");
    require('common/validator-rules').inject(Validator);
    require('ckeditor');

    Validator.addRule(
        'time_check',
        function () {
            var startTime = $('[name=completeTime]').val();
            var endTime = $('[name=reviewEndTime]').val();
            startTime = startTime.replace(/-/g, "/");
            startTime = Date.parse(startTime) / 1000;
            endTime = endTime.replace(/-/g, "/");
            endTime = Date.parse(endTime) / 1000;

            if (endTime >= startTime) {
                return true;
            } else {
                return false;
            }
        }, "作业完成时间必须小于互评结束时间"
    );

    Validator.addRule(
        'completeTimeCheck',
        function (options) {
            var completeTimeGroup = $('#completeTimeGroup');
            var completeTime = $('#completeTime').val();
            if (completeTimeGroup[0].style.display != 'none' && completeTime != "") {
                return true;
            } else {
                return false;
            }


        }, "作业完成时间必须小于互评结束时间"
    );

    Validator.addRule(
        'scoreRule_check',
        /^[0-9]+(.\d)?(:\d+.\d+){2}:[0-9]+$/,
        "请按如下格式依次输入完成互评的，部分完成的，未互评的，最少互评人数：1:0.8:0.5:5"
    );

    comment();
    function comment() {
        var $comment = $('[name="comment"]:checked').val();
        if ($comment == 1) {
            $('#completeTimeGroup').show();
            $('#reviewEndTimeGroup').show();
            $('#scoreRuleGroup').show();
            $('#tip').show();
        } else {
            $('#completeTimeGroup').hide();
            $('#reviewEndTimeGroup').hide();
            $('#scoreRuleGroup').hide();
        }
    }

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
            'click [name=comment]': 'changeComment',
            'click #save-homework-btn': 'onConfirmSubmit'
        },

        setup: function () {
            this.initItemSortable();
        },

        changeComment: function () {
            comment();
        },

        onConfirmSubmit: function () {
            var v = window.validator;
            var $comment = $('[name="comment"]:checked').val();
            if ($comment == 1) {
                for (var i = 0; i < v.items.length; i++) {
                    v.items[i].attrs.required.value = true;
                }
            } else {
                for (var i = 0; i < v.items.length; i++) {
                    v.items[i].attrs.required.value = false;
                }
            }

            $("#homework-form").trigger("submit.validator");
            if (window.validateResult == false)
                return false;
            editor.updateElement();
            var $btn = $('#save-homework-btn');
            var $description = editor.getData();
            var $completeLimit = $('[name="completeLimit"]:checked').val();
            var $comment = $('[name="comment"]:checked').val();
            var $completeTime = $('#completeTime').val();
            var $reviewEndTime = $('#reviewEndTime').val();
            var $scoreRule = $('#scoreRule').val();
            var $rules = $scoreRule.split(':');
            var excludeIds = [];
            var $tbodyValueLength = $('#homework-table-tbody:has(tr)').length;

            if ($tbodyValueLength == 0) {

                $('#homework_items_help').css('color', '#a94442');
                $('#homework_items_help').show();
                $btn.attr("disabled", true);
                if ($('[data-role=homework-edit]').length > 0) {
                    $btn.button('saving');
                    $.post($('#save-homework-btn').data('url'), {
                        description: $description,
                        pairReview: $comment,
                        completeTime: $completeTime,
                        reviewEndTime: $reviewEndTime,
                        completePercent: $rules[0],
                        partPercent: $rules[1],
                        zeroPercent: $rules[2],
                        minReviews: $rules[3]
                    }, function (response) {
                        if (response.status == 'success') {
                            window.location.href = "/course/" + response.courseId + "/manage/lesson";
                        }
                    });
                }
                ;

            } else {

                $('#homework_items_help').hide();
                $btn.attr("disabled", true);
                $btn.button('saving');

                $("#homework-table-tbody").find('[name="questionId[]"]').each(function () {
                    excludeIds.push($(this).val());
                });

                $.post($('#save-homework-btn').data('url'), {
                    description: $description,
                    pairReview: $comment,
                    completeTime: $completeTime,
                    reviewEndTime: $reviewEndTime,
                    completeLimit: $completeLimit,
                    completePercent: $rules[0],
                    partPercent: $rules[1],
                    zeroPercent: $rules[2],
                    minReviews: $rules[3],
                    excludeIds: excludeIds.join(',')
                }, function (response) {
                    if (response.status == 'success') {
                        window.location.href = "/course/" + response.courseId + "/manage/lesson";
                    }
                });
            }

        },

        onClickBatchSelect: function (e) {

            if ($(e.currentTarget).is(":checked") == true) {
                this.$('[data-role=batch-select]:visible, [data-role=batch-item]:visible').prop('checked', true);
            } else {
                this.$('[data-role=batch-select]:visible, [data-role=batch-item]:visible').prop('checked', false);
            }
        },

        onClickItemBatchSelect: function (e) {
            var checkedCount = 0;
            var length = this.$('[data-role=batch-item]:visible').length;
            this.$('[data-role=batch-item]').each(function () {
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

        onClickBatchDelete: function (e) {

            var ids = [];
            this.$('[data-role=batch-item]:checked').each(function () {
                ids.push(this.value);
            });

            if (ids.length == 0) {
                Notify.danger('未选中任何题目');
                return;
            }

            if (!confirm('确定要删除选中的题目吗？')) {
                return;
            }

            this.$('[data-role=batch-item]:checked').each(function () {
                var $tr = $(this).parents('tr');

                $tr.parents('tbody').find('[data-parent-id=' + $tr.data('id') + ']').remove();
                $tr.remove();
            });

            this.$('[data-role=batch-select]:visible').prop('checked', false);

            this.refreshSeqs();
        },

        onClickItemDelete: function (e) {
            var $btn = $(e.target);
            if (!confirm('您真的要删除该题目吗？')) {
                return;
            }
            var $tr = $btn.parents('tr');
            $tr.parents('tbody').find('[data-parent-id=' + $tr.data('id') + ']').remove();
            $tr.remove();
            this.refreshSeqs();
        },

        onClickPickItem: function (e) {

            var $btn = $(e.currentTarget);

            var excludeIds = [];

            $('#save-homework-btn').attr("disabled", false);

            $("#homework-table-tbody").find('[name="questionId[]"]').each(function () {
                excludeIds.push($(this).val());
            });

            var $modal = $("#modal").modal();
            $modal.data('manager', this);
            $.get($btn.data('url'), {excludeIds: excludeIds.join(',')}, function (html) {
                $modal.html(html);
            });
        },

        refreshSeqs: function () {
            var seq = 1;
            $("#homework-table").find("tbody tr").each(function () {
                var $tr = $(this);
                $tr.find('td.seq').html(seq);
                seq++;
            });
        },

        initItemSortable: function (e) {
            var $table = this.$("#homework-table-tbody"),
                self = this;
            $list = $table.sortable({
                // containerPath: '> tr',
                itemSelector: 'tr.is-question',
                placeholder: '<tr class="placeholder"/>',
                // exclude: '.notMoveHandle',
                onDrop: function (item, container, _super) {
                    _super(item, container);
                    self.refreshSeqs();
                },
            });
        },
    });
    exports.run = function () {
        new HomeworkItemManager({
            element: '#homework-items-manager'
        });

        var validator = new Validator({
            element: '#homework-form',
            failSilently: true,
            autoSubmit: false,

            onFormValidated: function (error, results, $form) {
                if (error) {
                    window.validateResult = false;
                } else
                    window.validateResult = true;
            }
        });
        window.validator = validator;

        validator.addItem({
            element: '[name=completeTime]',
            required: true
        });

        validator.addItem({
            element: '[name=reviewEndTime]',
            required: true,
            rule: 'time_check'
        });

        validator.addItem({
            element: '[name=scoreRule]',
            required: true,
            rule: 'scoreRule_check'
        });

        var now = new Date();

        $("[name=completeTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        }).on('hide', function (ev) {
            validator.query('[name=completeTime]').execute();
        });

        $('[name=completeTime]').datetimepicker('setStartDate', now);

        $("[name=reviewEndTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        }).on('hide', function (ev) {
            validator.query('[name=reviewEndTime]').execute();
        });

        $('[name=reviewEndTime]').datetimepicker('setStartDate', now);

        require('/bundles/topxiaweb/js/controller/course-manage/header').run();
    };

});