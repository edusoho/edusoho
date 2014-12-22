define(function(require,exports,module){
    exports.run = function(){
        var CartsModule = require('./carts');

        var cartsModule = new CartsModule({
　　　　　　　　element:'#carts-module'
        });
    }
});