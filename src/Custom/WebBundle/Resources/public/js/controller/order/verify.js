define(function(require, exports, module) {
    var Widget = require('widget');

    var OrderVerify = Widget.extend({
        attrs: {},
        events: {
            "change [name=needBill]": "_onChangeNeedBill"
        },
        setup: function() {

        },
        _onChangeNeedBill: function(e) {
            var $radios = $(e.currentTarget);
            if($radios.val() == 'yes') {

                this.$('[data-role=address]').slideDown('normal');
            } else {
                this.$('[data-role=address]').slideUp('normal');
            }
        } 
        
    });

    new OrderVerify({
        'element': '#order-verify'
    });

});