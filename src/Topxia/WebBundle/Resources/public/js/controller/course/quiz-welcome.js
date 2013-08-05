define(function(require, exports, module) {

    exports.run = function() {

        $(".quiz-page").on('click', ".start-quiz", function() {
            $.post($(this).data('url'), function(response) {
                $(".quiz-page").replaceWith(response.html);
            }, 'json');
        });

        
    };

});