define(function(require, exports, module) {

    var themeModal = require('./theme-modal');

    exports.run = function() {

        var x = themeModal.getAll();
console.log(x);
        // $('xxx').on('click', function(){
            
        //     // themeModal.set('name', config);

        //     $('#modal').modal('hide');

        // })

    };

});