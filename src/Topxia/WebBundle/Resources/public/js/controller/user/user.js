define(function(require, exports, module) {

    exports.run = function() {
        require('../course/timeleft').run();
        $('.follow-btn').on('click', function() {
            var $this = $(this);
            $.post($this.data('url'), function() {
                $this.hide();
                $('.unfollow-btn').show();
            });
        });


        $('.unfollow-btn').on('click', function() {
            var $this = $(this);
            $.post($this.data('url'), function() {
                $this.hide();
                $('.follow-btn').show();
            });
        });


    }

});