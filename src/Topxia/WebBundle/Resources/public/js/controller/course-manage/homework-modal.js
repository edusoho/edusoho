define(function(require, exports, module) {

    var EditorFactory = require('common/kindeditor-factory');
    var editor = EditorFactory.create('#homework-about-field', 'simple_noimage');
    var  $modal = $("#modal");
    var Widget = require('widget');
    // var Handlebars = require('handlebars');
    // var Notify = require('common/bootstrap-notify');
        require('jquery.sortable');

    var HomeworkItemManager = Widget.extend({

        attrs: {
            currentType: null
        },

        events: {
            'click [data-role=pick-item]': 'onClickPickItem',
            'click [data-role=batch-select]': 'onClickBatchSelect',
            'click [data-role=batch-delete]': 'onClickBatchDelete',
            'click .item-delete-btn': 'onClickItemDelete',
            'click #save-homework-btn': 'onConfirmSubmit'
        },

        setup:function() {
            this.initItemSortable();
        },

        onConfirmSubmit: function(e) {

            editor.sync();
            var $btn = $('#save-homework-btn');
            var $description = editor.html();
            var $completeLimit = $('[name="completeLimit"]:checked').val();
            var excludeIds = [];
            var $tbodyValueLength = $('#homework-table-tbody:has(tr)').length;

            if ($tbodyValueLength == 0) {

                $('#homework_items_help').css('color','#a94442');
                $('#homework_items_help').show();
                $btn.attr("disabled", true);
                $btn.button('saving');
                $.post($('#save-homework-btn').data('url'),{description:$description},function(response){
                        if (response.status == 'success') {
                            window.location.href="/course/"+response.courseId+"/manage/lesson";
                        }
                 });

            } else {

                $('#homework_items_help').hide();
                $btn.attr("disabled", true);
                $btn.button('saving');

                $("#homework-table-tbody").find('[name="questionId[]"]').each(function(){
                    excludeIds.push($(this).val());
                });

                $.post($('#save-homework-btn').data('url'),{description:$description,completeLimit:$completeLimit,excludeIds: excludeIds.join(',')},function(response){
                        if (response.status == 'success') {
                            window.location.href="/course/"+response.courseId+"/manage/lesson";
                        }
                 });
            }

        },

        onClickBatchSelect: function(e) {

            if ($(e.currentTarget).is(":checked") == true){
                this.$('[data-role=batch-select]:visible, [data-role=batch-item]:visible').prop('checked', true);
            } else {
                this.$('[data-role=batch-select]:visible, [data-role=batch-item]:visible').prop('checked', false);
            }
        },

        onClickBatchDelete: function(e) {

            var ids = [];
            this.$('[data-role=batch-item]:checked').each(function() {
                ids.push(this.value);
            });

            if (ids.length == 0) {
                Notify.danger('未选中任何题目');
                return ;
            }

            if (!confirm('确定要删除选中的题目吗？')) {
                return ;
            }

            this.$('[data-role=batch-item]:checked').each(function() {
                var $tr = $(this).parents('tr');

                $tr.parents('tbody').find('[data-parent-id=' + $tr.data('id') + ']').remove();
                $tr.remove();
            });

            this.$('[data-role=batch-select]:visible').prop('checked', false);

            this.refreshSeqs();
        },

        onClickItemDelete: function(e) {
            var $btn = $(e.target);
            if (!confirm('您真的要删除该题目吗？')) {
                return ;
            }
            var $tr = $btn.parents('tr');
            $tr.parents('tbody').find('[data-parent-id=' + $tr.data('id') + ']').remove();
            $tr.remove();
            this.refreshSeqs();
        },

        onClickPickItem: function(e) {

            var $btn = $(e.currentTarget);
               
            var excludeIds = [];

            $('#save-homework-btn').attr("disabled", false);
     
            $("#homework-table-tbody").find('[name="questionId[]"]').each(function(){
                excludeIds.push($(this).val());
            });

            var $modal = $("#modal").modal();
            $modal.data('manager', this);
            $.get($btn.data('url'), {excludeIds: excludeIds.join(',')},function(html) {
                $modal.html(html);
            });
        },

        refreshSeqs: function () {
            var seq = 1;
            $("#homework-table").find("tbody tr").each(function(){
                var $tr = $(this);
                    $tr.find('td.seq').html(seq);
                    seq ++;
            });
        },

        initItemSortable: function (e) {
            var $table = this.$("#homework-table-tbody"),
            self = this;
            $list =$table.sortable({
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
    exports.run = function() {
        new HomeworkItemManager({
            element: '#homework-items-manager'
        });

        require('./header').run();
    };

});