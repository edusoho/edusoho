import 'bootstrap-notify';

const notify = (level, message, settings = {}, options = {}) => {
  $('[data-notify="container"]').remove();
  let iconFont = '';
  switch (level) {
  case 'info':
    iconFont = 'cd-icon cd-icon-info-o color-info mrm';
    break;
  case 'success':
    iconFont = 'cd-icon cd-icon-success-o color-success mrm';
    break;
  case 'danger':
    iconFont = 'cd-icon cd-icon-danger-o color-danger mrm';
    break;
  case 'warning':
    iconFont = 'cd-icon cd-icon-warning-o color-warning mrm';
    break;
  default:
    break;
  }

  let defaultOptions = {
    message: message,
    icon: iconFont,
  };

  let defaultSettings = {
    type: level, //info,danger,warning,success
    delay: 3000,
    placement: {
      from: 'top',
      align: 'center'
    },
    animate: {
      enter: 'animated fadeInDownSmall',
      exit: 'animated fadeOutUp'
    },
    offset: 80,
    z_index: 1051,
    timer: 100,
    template: '<div data-notify="container" class="notify-content">' +
      '<div class="notify notify-{0}">' +
        '<span data-notify="icon"></span>' +
        '<span data-notify="title">{1}</span>' +
        '<span data-notify="message">{2}</span>' +
        '<div class="progress" data-notify="progressbar">' +
          '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
        '</div>' +
        '<a href="{3}" target="{4}" data-notify="url"></a>' +
        '</div>' +
      '</div>'
  };

  $.notify(Object.assign(defaultOptions, options), Object.assign(defaultSettings, settings));
};

export default notify;

