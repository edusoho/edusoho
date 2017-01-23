define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.sortable');
    
    exports.run = function() {

        $(".course-list-group").on('click','.close',function(){

            var courseId=$(this).data('id');

            var currentPrice=parseFloat($('.course-price-'+courseId).data('price')).toFixed(2);
            var price=parseFloat($('#price').html()).toFixed(2);
            price=parseFloat(price-currentPrice).toFixed(2);

            var price=parseFloat($('#price').html(price)).toFixed(2);

            $('.item-'+courseId).remove();

        });

        var $list = $(".course-list-group").sortable({
            distance: 20,
            onDrop: function (item, container, _super) {
                _super(item, container);
            }
        });

    };

});