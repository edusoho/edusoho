const ajax = (options) => {

  let defaultOptions = {
    async: true,
    promise: true
  };

  options = Object.assign(defaultOptions, options);

  let parameter = {
    type: options.type || 'GET',
    url: options.url || '',
    dataType: options.dataType || 'json',
    async: options.async,
    beforeSend(request) {
      request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
      request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
    }
  };

  if (options.data) {
    Object.assign(parameter, {
      data: options.data
    })
  }

  if (options.success) {
    Object.assign(parameter, {
      success: options.success
    })
  }

  if (options.promise) {
    return Promise.resolve($.ajax(parameter));
  } else {
    return $.ajax(parameter);
  }

};

export default ajax;