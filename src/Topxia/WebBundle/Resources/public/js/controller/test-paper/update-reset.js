define(function(require, exports, module) {

    var CreateBase = require('./util/create-base');

    exports.run = function() {

        CreateBase.initNoUiSlider();

        CreateBase.isDifficulty();

        CreateBase.initRangeField();

        CreateBase.sortable();

        $('#test-reset-form').submit( function () {

            if (!CreateBase.checkIsNum()) {
                return false;
            }

            if (!CreateBase.getCheckResult()) {
                return false;
            }

            return true;

        });

        

    };



});