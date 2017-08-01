const ajax = (options) => {
  let parameter = {
    type: options.type || 'GET',
    url: options.url || '',
    dataType: options.dataType || 'json',
    beforeSend(request) {
      request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
      request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
    }
  }

  if (options.data) {
    Object.assign(parameter, {
      data: options.data
    })
  }

  return Promise.resolve($.ajax(parameter));
}

export default ajax;