define(function(require, exports, module) {
    var Widget = require('widget');
        require("chineserp-jquery");
    var OrderVerify = Widget.extend({
        attrs: {},
        events: {
            "change [name=needInvoice]": "_onChangeNeedInvoice",
            "change [name=invoiceTitle]": "_onChangeInvoiceTitle"
        },
        setup: function() {

        },
        _onChangeNeedInvoice: function(e) {
            var $radios = $(e.currentTarget);
            if($radios.val() == 'yes') {

                this.$('[data-role=address]').slideDown('normal');
            } else {
                this.$('[data-role=address]').slideUp('normal');
            }
        },
        _onChangeInvoiceTitle: function(e) {
            var $target = $(e.currentTarget);
            $.post($target.data('updateUrl'), {title:$target.val()});
        }

        
    });

    new OrderVerify({
        'element': '#order-verify'
    });

});