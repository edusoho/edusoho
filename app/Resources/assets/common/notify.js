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
    offset: 5,
    z_index: 1051,
  });
}

export default notify;

