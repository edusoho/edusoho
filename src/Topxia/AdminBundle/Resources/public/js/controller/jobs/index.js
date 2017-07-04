define(function(require, exports, module){

    require("jquery.bootstrap-datetimepicker");

    exports.run = function(){

        $("#job-table").on('click', '.job-enabled', function(){
            var link = $(this);
            $.post($(this).data('url'), function (data) {
                link.parents('tr').replaceWith(data);
            });
        });

        $("#tips").popover({
            html: true,
            trigger: 'hover',//'hover','click'
            content: $("#tips-html").html()
        });
    };
});

