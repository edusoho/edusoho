define(function(require, exports, module) {

    var BaseChooser = require('./base-chooser-8');
    require('jquery.perfect-scrollbar');
    var Notify = require('common/bootstrap-notify');

    var FlashChooser = BaseChooser.extend({
        attrs: {

        },

        setup: function() {
            FlashChooser.superclass.setup.call(this);
            $('#disk-browser-flash').perfectScrollbar({wheelSpeed:50});
        }

    });

    module.exports = FlashChooser;

});


