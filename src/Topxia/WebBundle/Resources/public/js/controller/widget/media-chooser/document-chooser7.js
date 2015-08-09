define(function(require, exports, module) {

    var BaseChooser = require('./base-chooser-8');
    require('jquery.perfect-scrollbar');
    var Notify = require('common/bootstrap-notify');

    var DocumentChooser = BaseChooser.extend({
        attrs: {

        },

        setup: function() {
            DocumentChooser.superclass.setup.call(this);
            $('#disk-browser-document').perfectScrollbar({wheelSpeed:50});
        }

    });

    module.exports = DocumentChooser;

});


