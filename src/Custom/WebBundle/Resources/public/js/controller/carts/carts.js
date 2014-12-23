define(function(require,exports,module){

    var Notify = require('common/bootstrap-notify');

    var Widget = require('widget');

    var Carts = Widget.extend({
        attrs: {
            confirmMsg : '您真的要移除该课程吗',
            selectConfirmMsg : '您真的要移除选中课程吗'
        },

        events: {
            "click [data-role=delete-carts-btn]": "_OnClickDeleteItem",
            "click [data-role=batch-select]" : "_OnClickBatch",
            "click [data-role=single-select]" : "_initTotalNum",
            "click [data-role=batch-delete-btn]" : "_OnClickBatchDelete",
            "click [data-role=batch-favourite-btn]" : "_OnClickBatchFavourite",
            "click [data-role=single-favourite-btn]" : "_OnClickSingleFavourite"
        },

        setup: function() {
            this._initTotalPrice();
            this.on('selectedItems',function(){
                var $items = [];
                var $cartsIds = [];
                this.$('[data-role=single-select]').each(function(index,item){
                    if ($(item).is(':checked')) {
                        $cartsIds.push($(item).data('id'));
                        $items.push($(item).data('itemId'));
                    };
                });
                this.set('cartsIds',$cartsIds);
                this.set('selectedItems',$items);
            });
        },

        _OnClickDeleteItem: function(e){
            var msg = this.get('confirmMsg');
            if (!confirm(msg)) {
                return ;
            };

            var $btn = $(e.currentTarget);

            self = this;

            $.post($btn.data('url'),function(){
                Notify.success("删除成功");
                $role = '[data-role=cart-tr-'+$btn.data('id')+']';
                $($role).remove();

                self._initCartsList();
                self._initTotalNum();
                self._initTotalPrice();
            }).error(function(){
                Notify.danger("删除失败");
            });
        },

        _OnClickBatch: function(e) {
            if ($(e.currentTarget).is(":checked") == true){
                $('[data-role=single-select]').prop('checked', true);
            } else {
               $('[data-role=single-select]').prop('checked', false);
            }
            this._initTotalNum();
        },

        _OnClickBatchDelete: function(e) {

            var msg = this.get('selectConfirmMsg');

            if (!confirm(msg)) {
                return ;
            };

            var ids = [];
            var $btn = $(e.currentTarget);
            var self = this;

            this.trigger('selectedItems');
            ids = this.get('cartsIds');

            $.post($btn.data('url'),{ids:ids},function(){
                Notify.success("删除成功");

                $.each(ids,function(index,id){
                    $role = '[data-role=cart-tr-'+id+']';
                    $($role).remove();
                });

                self._initCartsList();
                self._initTotalNum();
                self._initTotalPrice();
            }).error(function(){
                    Notify.danger("删除失败");
            });
        },

        _initCartsList: function () {
            if (this.$('#carts-table').find('tbody > tr').length == 0) {
                this.$('#carts-table').empty();
                this.$('.carts-text-help').removeClass('hide');
            }
        },

        _initTotalPrice: function () {
            var priceTotal = 0;

            this.$('[data-role=price]').each(function(index,item){
                priceTotal += Number($(item).text())
            });

            this.$('[data-role=total-price]').html(priceTotal.toFixed(2));
        },

        _initTotalNum: function () {
            this.trigger('selectedItems');
            var items = [];
            items = this.get('cartsIds');
            this.$('[data-role=total-num]').html(items.length);
        },

        _OnClickBatchFavourite: function (e) {
            if (!confirm('确定收藏选中课程')) {
                return ;
            };
            var $btn = $(e.currentTarget);
            var ids = [];
            this.trigger('selectedItems');

            ids = this.get('selectedItems');
            $.post($btn.data('url'),{ids:ids},function(result){
                Notify.success('收藏成功');
            }).error(function(result){
                Notify.danger('已经收藏');
            });
        },

        _OnClickSingleFavourite: function (e) {
            $btn = $(e.currentTarget);
            var id = [];
            id.push($btn.data('itemId'));
            $.post($btn.data('url'),{ids:id},function(result){
                Notify.success('收藏成功');
            }).error(function(result){
                Notify.danger('已经收藏');
            });
        }

    });

    module.exports = Carts;

});