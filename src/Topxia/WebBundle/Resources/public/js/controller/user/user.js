define(function(require, exports, module) {

    exports.run = function() {
        var teacherTitle=$('#teacherTitle').text().trim();
        $('#teacherTitle').text(teacherTitle.substring(0,teacherTitle.length-1));
        
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