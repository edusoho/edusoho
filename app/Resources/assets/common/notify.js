import 'bootstrap-notify';

const notify = (level, message) => {
  $('[data-notify="container"]').remove();
  $.notify({
    message: message
  }, {
    type: level, //info,danger,warning,success
    delay: 2000,
    placement: {
      from: 'top',
      align: 'center'
    },
    offset: 0,
    z_index: 1051,
  });
}

export default notify;

