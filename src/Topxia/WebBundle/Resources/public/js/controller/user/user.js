define(function(require, exports, module) {
    require("jquery.blurr");

    exports.run = function() {
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

        $(".user-center-header").blurr();

    }

});