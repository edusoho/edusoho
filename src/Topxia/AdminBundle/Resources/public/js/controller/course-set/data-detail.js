define(function(require, exports, module) {

    exports.run = function() {
        $('body').on('change','#course-select',function(){
            var url = $(this).find("option:selected").data('url');
            $('#modal').load(url);
        })
    };
})