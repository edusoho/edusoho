define(function(require, exports, module) {
    require("jquery.blurr");

    exports.run = function() {
        $('.follow-btn').on('click', function() {
            var $this = $(this);
            $.post($this.data('url'), function() {
                $this.hide();
                $this.next('.unfollow-btn').show();
            });
        });


        $('.unfollow-btn').on('click', function() {
            var $this = $(this);
            $.post($this.data('url'), function() {
                $this.hide();
                $this.prev('.follow-btn').show();
            });
        });

        $(".user-center-header").blurr({height:220});

    }

});