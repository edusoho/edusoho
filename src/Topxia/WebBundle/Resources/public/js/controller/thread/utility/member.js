define(function(require, exports, module) {

    var Widget = require('widget');

    exports.run = function() {
        var memberList = Widget.extend({
            currentPage:1,
            events: {
                'click .js-members-expand': 'expand',
                'click .js-members-collapse': 'collapse'
            },

            setup: function() {
                this.sum = this.element.data('sum');
            },
            expand: function(e) {
                var self = this;
                var $target = $(e.currentTarget);
                if ($target.data('expandAll')) {
                    this.$('ul.user-grids').fadeIn(500);
                    this.$('.js-members-expand').hide();
                    this.$('.js-members-collapse').show();
                } else {
                    $.get($target.data('url'), {page:this.currentPage + 1}, function(result){
                        self.$('ul.user-grids').append(result);
                        var length = self.$('ul.user-grids > li').length;
                        if (self.sum == length) {
                            $target.data('expandAll', true);
                            $target.hide();
                            self.$('.js-members-collapse').show();
                        }
                    });
                }
            
            },
            collapse: function(e) {
                this.$('ul.user-grids').fadeOut(500);
                this.$('.js-members-expand').show();
                this.$('.js-members-collapse').hide();
            }
        });
        
        new memberList({
            'element': '.joined-users'
        });

    };

});