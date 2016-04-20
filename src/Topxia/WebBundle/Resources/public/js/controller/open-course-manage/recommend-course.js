define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.sortable');
    
    exports.run = function() {
        require('../course-manage/header').run();
        
        $(".course-list-group").on('click','.close',function(){

            var courseId = $(this).data('id');

            $('.item-'+courseId).remove();
        });

        var $list = $(".course-list-group").sortable({
            distance: 20,
            onDrop: function (item, container, _super) {
                _super(item, container);
            }
        });

    };

});