define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('jquery.sortable');
    
    exports.run = function() {
        
        $(".course-list-group").on('click','.close',function(){
            var recommendId = $(this).data('recommendId');
            var courseId = $(this).data('id');
            $.post($(this).data('cancelUrl')).done(function () {

                $('.item-'+courseId).remove();
            });
        });

        var $list = $(".course-list-group").sortable({
            distance: 20,
            onDrop: function (item, container, _super) {
                _super(item, container);
            }
        });

    };

});