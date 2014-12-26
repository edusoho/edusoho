define(function(require,exports,module){
    exports.run = function(){
        var CartsModule = require('./carts');

        var cartsModule = new CartsModule({
　　　　　　　　element:'#carts-module'
        });

        $('#favorited-btn').on('click',function(){
            $('#hot-sale-btn').removeClass('active');
            $('#favorited-btn').addClass('active');
            $('#favorited-courses').removeClass('hide');
            $('#hot-sale-courses').addClass('hide');
        });

        $('#hot-sale-btn').on('click',function(){
            $('#hot-sale-btn').addClass('active');
            $('#favorited-btn').removeClass('active');
            $('#favorited-courses').addClass('hide');
            $('#hot-sale-courses').removeClass('hide');
        });
    }
});