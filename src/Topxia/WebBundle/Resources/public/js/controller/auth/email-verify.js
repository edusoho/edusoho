define(function(require, exports, module) {

    exports.run = function() {

        setTimeout(function() {
            window.location.href= $("#jump-btn").attr('href');
        }, 2000);

    }

});