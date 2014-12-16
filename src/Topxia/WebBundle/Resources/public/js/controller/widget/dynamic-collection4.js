define(function(require, exports, module) {

    var Widget = require('widget');
    var Handlebars = require('handlebars');
    var Notify = require('common/bootstrap-notify');
    var DynamicCollection = Widget.extend({
        attrs: {
            onlyAddItemWithModel: false
        },
        events: {
            'click [data-role=item-add]': 'addItem',
            'keydown [data-role=item-input]': 'addItemWithEnter',
            'click [data-role=item-delete]' : 'deleteItem'
        },

        setup: function() {
            this._setupAttrsFromHtml();
            this._setupItems();
        },

        addItemWithModel: function(model) {
            var html = this.get('itemTemplate')(model);
            this.$('[data-role=list]').append(html).show();
            this.clearInput();
        },
        addItem: function(e) {
            var value = this.$('[data-role=item-input]').val();
            this.trigger('beforeAddItem', value);

            if (value.length == '') {
                return ;
            }

            if (this.get('onlyAddItemWithModel')) {
                return ;
            }

            this.addItemWithModel(value);

            this.trigger('afterAddItem', value);
        },

        addItemWithEnter: function(e) {
            if (e.which == 13) {
                e.preventDefault();

                if (this.get('onlyAddItemWithModel')) {
                    var value = this.$('[data-role=item-input]').val();
                    this.trigger('beforeAddItem', value);
                    return ;
                }

                this.addItem();
            }
        },

        deleteItem: function(e) {
            var beforeDeleteItem = this.get("beforeDeleteItem");          
            if(beforeDeleteItem && !beforeDeleteItem()){
                return;
            }
            $(e.currentTarget).parents('[data-role=item]').remove();
            this._toggleList();
        },

        clearInput: function() {
            this.$('[data-role=item-input]').val('');
        },

        _setupAttrsFromHtml: function() {
            var model = $.parseJSON(this.$('[data-role=model]').html());
            var itemTemplate = Handlebars.compile(this.$('[data-role=item-template]').html());
            this.set('model', model);
            this.set('itemTemplate', itemTemplate);
        },
        _setupItems: function() {

            var template = this.get('itemTemplate');
            var html = '';
            $.each(this.get('model'), function(i, item) {
                html += template(item);
            });

            this.$('[data-role=list]').html(html);
            this._toggleList();
        },
        _toggleList: function() {
            var $list = this.$('[data-role=list]');
            if ($list.children().length > 0) {
                $list.show();
            } else {
                $list.hide();
            }
        }
    });

    module.exports = DynamicCollection;
});