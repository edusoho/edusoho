$('body').on('click', '#cd-tabs a', function () {
  cd.tabs({
    el: '#cd-tabs a',
    target: '#tabs-panel',
    url: $(this).data('url'),
  }).on('success', (response) => {
    $('#checkin-tabs-panel').html(response);
  })
});

