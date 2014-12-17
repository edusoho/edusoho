define(function(require,exports,module){

    var Widget = require('widget');

    var BaseChooser = require('./base-chooser');

    var TestpaperChooser = BaseChooser.extend({
        attrs:{

        },

        events: {
            "click [data-role=testpaper-trigger]": "open"
        }



    });

    module.exports = TestpaperChooser;
});