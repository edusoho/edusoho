$('[data-toggle="popover"]').popover({
    html: true,
});

$('body').on('click', '.js-modal-export-btn', (event) => {
    console.log($(event.currentTarget));
})