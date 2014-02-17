define(function(require, exports, module) {

    var Widget     = require('widget');
    var Handlebars = require('handlebars');
    var Notify = require('common/bootstrap-notify');
    require('jquery.sortable');

    var TestpaperItemManager = Widget.extend({

        attrs: {
            currentType: null

        },

        events: {
            'click .testpaper-nav-link': 'onClickNav',
            'click [data-role=pick-item]': 'onClickPickItem',
            'click .item-delete-btn': 'onClickItemDeleteBtn',
            'click [data-role=batch-select]': 'onClickBatchSelect',
            'click [data-role=batch-delete]': 'onClickBatchDelete',
        },

        setup:function() {
            this.$('.testpaper-nav-link').eq(0).click();
            this.initItemSortable();
        },

        refreshSeqs: function () {
            var seq = 1;
            $("#testpaper-table").find("tbody tr").each(function(){
                var $tr = $(this);

                if (!$tr.hasClass('have-sub-questions')) {
                    $tr.find('td.seq').html(seq);
                    seq ++;
                }
            });
        },

        onClickPickItem: function(e) {
            var $btn = $(e.currentTarget);
            console.log(this.get('currentType'));
            console.log($btn.data('url'));

            var excludeIds = [];
            $("#testpaper-items-" + this.get('currentType')).find('[name="questionId[]"]').each(function(){
                excludeIds.push($(this).val());
            });

            var $modal = $("#modal").modal();
            $modal.data('manager', this);
            $.get($btn.data('url'), {excludeIds: excludeIds.join(','), type: this.get('currentType')}, function(html) {
                $modal.html(html);
            });
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

        onClickBatchSelect: function(e) {
            if ($(e.currentTarget).is(":checked") == true){
                this.$('[data-role=batch-select]:visible, [data-role=batch-item]:visible').prop('checked', true);
            } else {
                this.$('[data-role=batch-select]:visible, [data-role=batch-item]:visible').prop('checked', false);
            }
        },

        onClickItemDeleteBtn: function(e) {
            var $btn = $(e.target);
            if (!confirm('您真的要删除该题目吗？')) {
                return ;
            }
            var $tr = $btn.parents('tr');
            $tr.parents('tbody').find('[data-parent-id=' + $tr.data('id') + ']').remove();
            $tr.remove();
            this.refreshSeqs();
        },

        onClickNav: function(e) {
            var $nav = $(e.target);
            this.$('.testpaper-nav-link').parent().removeClass('active');
            $nav.parent().addClass('active');

            $("#testpaper-table").find('tbody').addClass('hide');
            $("#testpaper-items-" + $nav.data('type')).removeClass('hide');
            this.set('currentType', $nav.data('type')); 
            return true;
        },

        initItemSortable: function(e) {
            var $table = this.$('.testpaper-table-tbody'),
                self = this;
            $table.sortable({
                containerPath: '> tr',
                itemSelector: 'tr.is-question',
                placeholder: '<tr class="placeholder"/>',
                exclude: '.notMoveHandle',
                onDrop: function (item, container, _super) {
                    _super(item, container);
                    if (item.hasClass('have-sub-questions')) {
                        var $tbody = item.parents('tbody');
                        $tbody.find('tr.is-question').each(function() {
                            var $tr = $(this);
                            $tbody.find('[data-parent-id=' + $tr.data('id') + ']').detach().insertAfter($tr);
                        });

                        // console.log($tbody.find('[data-parent-id=' + item.data('id') + ']'));

                        // $tbody.find('[data-parent-id=' + item.data('id') + ']').detach().insertAfter(item);
                    }

                    self.refreshSeqs();
                }
            });
        }

    });

    exports.run = function() {
        new TestpaperItemManager({
            element: '#testpaper-items-manager'
        });
    }

});