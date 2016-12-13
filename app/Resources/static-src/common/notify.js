import 'bootstrap-notify';

const notify = (level, message) => {
  $.notify({
    message: message
  }, {
    type: level, //info,danger,warning,success
    delay: 5000,
    placement: {
      from: 'top',
      align: 'center'
    },
    offset: 20,
    z_index: 1051,
    timer: 1000,
  });
}

export default notify;

