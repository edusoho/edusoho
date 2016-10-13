define(function (require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    exports.run = function () {

        $.get($('.js-pane-question').data('url'), function (response) {
            $('.js-pane-question').html(response);
        })
    }
});
