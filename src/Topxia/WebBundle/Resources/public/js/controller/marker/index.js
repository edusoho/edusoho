define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    exports.run = function() {

        var count = parseInt((document.body.clientHeight-350)/50);

        $.get($('.js-pane-question').data('url')+ '?count', function(response) {
            $('.js-pane-question').html(response);
        })
    }
});
