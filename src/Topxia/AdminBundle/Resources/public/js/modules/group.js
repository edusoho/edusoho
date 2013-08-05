define(function(require, exports, module) {

    exports.bootstrap = function(options) {
        $('.group-tag-btn').click(function() {
            $('#group-tag-modal').data('url', $(this).data('url'));
        });

        $('.group-term-btn').click(function() {
            $('#group-term-modal').data('url', $(this).data('url'));
        });

        $('#group-tag-modal').on('show', function(e) {
            var $this = $(this).html('');
            $.get($this.data('url'), function(response) {
                $this.html(response);
            });
        });

        $('#group-term-modal').on('show', function(e) {
            var $this = $(this).html('');
            $.get($this.data('url'), function(response) {
                $this.html(response);
            });
        });

        $('.group-delete-btn').on('click', function(){
            var title = '您正在删除小组《' + $(this).data('title') + '》，请输入删除口令';
            var password = prompt(title);
            if (password != 'DELETE') {
                alert('口令不正确');
                return ;
            }

            if (!confirm('您真的要删除《' + $(this).data('title') + '》小组吗？同时会删除小组的所有话题！' )) {
                return ;
            }

            $.post($(this).data('url'), function() {
                window.location.reload();
            });

            return ;
        });
    };
});
