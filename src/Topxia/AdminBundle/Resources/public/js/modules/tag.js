define(function(require, exports, module) {

    exports.bootstrap = function(options) {
        $(function() {
            $('#tag-create-modal').on('shown', function() {
                var $this = $(this).html('');
                $.get($this.data('modal').options.url, function(response) {
                    $this.html(response);
                });
            });

            $('.update-btn').click(function(){
                $('#tag-update-modal').data('url', $(this).data('url'));
            });

            $('.delete-btn').click(function(){
                var $this = $(this);
                if (!confirm('真的要该删除吗？') || !confirm('删错了要扣工资的哦，真的要删吗？')) {
                    return false;
                }

                $.post($this.data('url'), function(response){
                    window.location.reload();
                });
            });

            $('#tag-update-modal').on('show', function(e) {
                var $this = $(this).html('');
                $.get($this.data('url'), function(response) {
                    $this.html(response);
                });

            });
        });
    };

});