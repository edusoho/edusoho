define(function(require, exports, module) {

    $('#modal').on('click', '.pagination li', function () {
        var url = $(this).data('url');
        if (typeof (url) !== 'undefined') {
            $.post(url, function (data) {
                $('#modal').html(data);
            });
        }
    });

    $('.job-fire-logs').on('click', function () {
        var url = $(this).data('url');
        if (typeof (url) !== 'undefined') {
            $.post(url, function (data) {
                $('#modal').html(data);
            });
        }
    });
})