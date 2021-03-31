$('[data-toggle="popover"]').popover({
    html: true,
});

$('#course-select').on('change', function () {
    $('#modal').load($(this).find("option:selected").data('url'));
})