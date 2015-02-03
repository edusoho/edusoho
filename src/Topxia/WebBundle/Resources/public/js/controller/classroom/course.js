define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.sortable');
    
    exports.run = function() {
        $(".course-list-group").sortable({
            'distance':20
        });

        $(".course-list-group").on('click','.close',function(){

            var courseId=$(this).data('id');

            var currentPrice=$('.course-price-'+courseId).data('price');
            var price=$('#price').html();
            price=price-currentPrice;

            var price=$('#price').html(price);

            $('.item-'+courseId).remove();



        });
    };

});