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
            'click .confirm-submit': 'onConfirmSubmit',
            'click .request-save': 'onRequestSave',
            'change [name="scores[]"]': 'onChangeScore',
        },

        setup:function() {
            this.$('.testpaper-nav-link').eq(0).click();
            this.initItemSortable();

            $("#testpaper-confirm-modal").modal('hide').on('hidden.bs.modal', function (e) {
                $(this).find('.confirm-submit').button('reset');    
            })
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

        onChangeScore: function(e) {
            this.refreshTestpaperStats();
        },

        onConfirmSubmit: function (e) {
            var $btn = $(e.currentTarget),
                $modal = $btn.parents('.modal');

            $btn.button('saving');

            $("#testpaper-items-form").submit();
        },

        onRequestSave: function(e) {
            var isOk = true;
            $("#testpaper-table").find('[name="scores[]"]').each(function() {
                var score = $(this).val();

                if (score == '0') {
                    Notify.danger('题目分值不能为0。');
                    isOk = false;
                    return false;
                }

                if (!/^(([1-9]{1}\d*)|([0]{1}))(\.(\d){1})?$/.test(score)) {
                    Notify.danger('题目分值只能填写数字，且最多一位小数。');
                    $(this).focus();
                    isOk = false;
                    return false;
                }
            });

            if (!isOk) {
                return ;
            }

            $modal = $("#testpaper-confirm-modal");

            var stats = this._calTestpaperStats();

            var html='';
            $.each(stats, function(index, statsItem){
                var tr = "<tr>";
                    tr += "<td>" + statsItem.name + "</td>";
                    tr += "<td>" + statsItem.count + "</td>";
                    tr += "<td>" + statsItem.score.toFixed(1) + "</td>";
                    tr += "</tr>";
                html += tr;
            });

            $modal.find('.detail-tbody').html(html);

            $modal.modal('show');
        },

        refreshTestpaperStats: function() {
            var type = this.get('currentType');
            var stats = this._calTestpaperStats();
            var html = '试卷总分<strong>' + stats.total.score.toFixed(1) + '</strong>分';
            html += ' <span class="stats-part">';
            if (type == 'material') {
                html += stats[type].name + '<strong>' + stats[type].count + '</strong>子题/<strong>' + stats[type].score.toFixed(1) + '</strong>分';
            } else {
                html += stats[type].name + '<strong>' + stats[type].count + '</strong>题/<strong>' + stats[type].score.toFixed(1) + '</strong>分';
            }

            if (stats[type].missScore > 0) {
                html += ' 漏选得分<strong>' + stats[type].missScore + '</strong>分</span>';
            }

            $("#testpaper-stats").html(html);
        },

        _calTestpaperStats: function() {
            var stats = {};
            this.$('.testpaper-nav-link').each(function() {
                var type = $(this).data('type'),
                    name = $(this).data('name');

                stats[type] = {name:name, count:0, score:0, missScore:0};
                $("#testpaper-items-" + type).find('[name="scores[]"][type=text]').each(function() {
                    stats[type]['count'] ++;
                    stats[type]['score'] += parseFloat($(this).val());
                    stats[type]['missScore'] = parseFloat($(this).data('miss-score'));
                });
            });

            var total = {name:'总计', count:0, score:0};
            $.each(stats, function(index, statsItem) {
                total.count += statsItem.count;
                total.score += statsItem.score;
            });

            stats.total = total;

            return stats;
        },

        onClickPickItem: function(e) {
            var $btn = $(e.currentTarget);

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
            this0.refreshTestpaperStats();
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
            this.refreshTestpaperStats();
        },

        onClickNav: function(e) {
            var $nav = $(e.target);
            this.$('.testpaper-nav-link').parent().removeClass('active');
            $nav.parent().addClass('active');

            $("#testpaper-table").find('tbody').addClass('hide');
            $("#testpaper-items-" + $nav.data('type')).removeClass('hide');
            this.set('currentType', $nav.data('type'));
            this.refreshTestpaperStats();
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