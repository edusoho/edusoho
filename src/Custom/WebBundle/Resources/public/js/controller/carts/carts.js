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
            "click [data-role=single-select]" : "_initTotal",
            "click [data-role=batch-delete-btn]" : "_OnClickBatchDelete",
            "click [data-role=batch-favourite-btn]" : "_OnClickBatchFavourite",
            "click [data-role=single-favourite-btn]" : "_OnClickSingleFavourite",
            "click [data-role=single-un-favourite-btn]" : "_OnClickSingleUnFavourite"
        },

        setup: function() {
            this._initTotal();
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
                self._initTotal();
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
            this._initTotal();
        },



        _OnClickBatchDelete: function(e) {

           if( !this.get('cartsIds')){
                Notify.warning("您还未选中任何记录");
                return ;
           }

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
                self._initTotal();
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

        _initTotal: function () {
            this.trigger('selectedItems');
            var items = [];
            items = this.get('cartsIds');
            var self = this;
            var priceTotal = 0;

            if (typeof(items) != "undefined") {
                $.each(items,function(index,itemId){
                    $price = self.$('[data-role=cart-tr-'+itemId+']').find('[data-role=price]').text();
                    priceTotal += Number($price)
                });
                self.$('[data-role=total-price]').html(priceTotal.toFixed(2));
                self.$('[data-role=total-num]').html(items.length);
            }
        },

        _OnClickBatchFavourite: function (e) {
            if( !this.get('cartsIds')){
                Notify.warning("您还未选中任何记录");
                return ;
           }

            var $btn = $(e.currentTarget);
            var ids = [];
            var items = [];
            var self = this;

            this.trigger('selectedItems');
            items = this.get('cartsIds');
            ids = this.get('selectedItems');
            $.post($btn.data('url'),{ids:ids},function(result){
                $.each(items,function(index,itemId){
                    self.$('[data-role=cart-tr-'+itemId+']').find('[data-role=single-favourite-btn]').hide();
                    self.$('[data-role=cart-tr-'+itemId+']').find('[data-role=single-un-favourite-btn]').show();
                });
            }).error(function(result){
            });
        },

        _OnClickSingleFavourite: function (e) {
  
            $btn = $(e.currentTarget);
            
            $.post($btn.data('url'),function(result){
                $btn.hide();
                $btn.siblings(".un-favorite-btn").show();
                // $("#un-favorite-btn").show();
            }).error(function(result){
                // Notify.danger('已经收藏');
            });
        },
        _OnClickSingleUnFavourite: function (e) {
  
            $btn = $(e.currentTarget);
          
            $.post($btn.data('url'),function(result){
                $btn.hide();
                // $("#favorite-btn").show();
                $btn.siblings(".favorite-btn").show();

            }).error(function(result){
               
            });
        }
        

    });

    module.exports = Carts;

});