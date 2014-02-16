define(function(require, exports, module) {

    var Widget     = require('widget');
    var Handlebars = require('handlebars');
    var Notify = require('common/bootstrap-notify');
    require('jquery.sortable');

    var TestpaperItemManager = Widget.extend({

        attrs:{

        },

        events: {
            'click .testpaper-nav-link': 'onClickNav',
            'click [data-role=add-item]': 'onClickAddItem',
            'click .item-delete-btn': 'onClickItemDeleteBtn',
            'click [data-role=batch-select]': 'onClickBatchSelect',
            'click [data-role=batch-delete]': 'onClickBatchDelete',
        },

        setup:function() {
            this.$('.testpaper-nav-link').eq(0).click();
            this.initItemSortable();
        },

        onClickAddItem: function(e) {

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
                var $item = $(this).parents('tr');
                $item.remove();
                //@todo 移除子题
            });

            this.$('[data-role=batch-select]:visible').prop('checked', false);
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
            $btn.parents('tr').remove();
        },

        onClickNav: function(e) {
            var $nav = $(e.target);
            this.$('.testpaper-nav-link').parent().removeClass('active');
            $nav.parent().addClass('active');

            $("#testpaper-table").find('tbody').addClass('hide');
            $("#testpaper-items-" + $nav.data('type')).removeClass('hide'); 
            return true;
        },

        initItemSortable: function(e) {
            var $table = this.$('.testpaper-table-tbody');
            $table.sortable({
                // containerSelector: '> tbody',
                containerPath: '> tr',
                // itemPath: '> tbody',
                itemSelector: 'tr',
                placeholder: '<tr class="placeholder"/>',
                exclude: '.notMoveHandle',
                onDrop: function (item, container, _super) {
                    _super(item, container);
                    // if (item.data('type') == 'material') {
                    //     var id = item.data('id');
                    //     var $subItems = $("#questionType-material").find("[data-type=" + id + "]");
                    //     $subItems.detach().insertAfter(item);
                    // }
                    // Test.sortable();
                },
            });
        }

    });

    exports.run = function() {
        new TestpaperItemManager({
            element: '#testpaper-items-manager'
        });
    }

});