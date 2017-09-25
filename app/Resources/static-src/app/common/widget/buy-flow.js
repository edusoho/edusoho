export const buyFlow  = ($element) => {
  $element.on('click', event => {
    $.post($(event.currentTarget).data('url'), resp => {
      if (typeof resp === 'object') {
        window.location.href = resp.url;
      } else {
        $('#modal').modal('show').html(resp);
      }

    });
  });
};