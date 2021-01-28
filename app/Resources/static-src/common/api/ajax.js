const ajax = (options) => {

  let DEFAULTS = {
    type: 'GET',
    url: null,
    async: true,
    promise: true,
    dataType: 'json',
    beforeSend(request) {
      request.setRequestHeader('Accept', 'application/vnd.edusoho.v2+json');
      request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
      request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      
      if (typeof options.before === 'function') {
        options.before(request);
      }
    }
  };

  options = Object.assign(DEFAULTS, options);

  if (options.promise) {
    return Promise.resolve($.ajax(options));
  } else {
    return $.ajax(options);
  }
};

export default ajax;