$('.js-log').on('click', '.pagination li', function () {
    let url = $(this).data('url');

    if (typeof (url) !== 'undefined') {
        $.get(url, (data) => {
            $('#modal').html(data);
        });
    }
});