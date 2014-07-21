define(function(require, exports, module) {

    var BaseChooser = require('./base-chooser-2');
    require('jquery.perfect-scrollbar');

    var PPTChooser = BaseChooser.extend({
        attrs: {
            uploaderSettings: {
                file_types : "*.ppt;*.pptx",
                file_size_limit : "100 MB",
                file_types_description: "PPT文件"
            }
        },
        
        setup: function() {
            PPTChooser.superclass.setup.call(this);
            $('#disk-browser-ppt').perfectScrollbar({wheelSpeed:50});
        }

    });

    module.exports = PPTChooser;

});


