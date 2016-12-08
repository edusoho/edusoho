import 'bootstrap-notify';

const notify = (level, message) => {
  $('[data-notify="container"]').remove();
  $.notify({
    message: message
  }, {
    type: level, //info,danger,warning,success
    delay: 500000000,
    placement: {
      from: 'top',
      align: 'center'
    },
    offset: 5,
    z_index: 1051,
  });
}

export default notify;

