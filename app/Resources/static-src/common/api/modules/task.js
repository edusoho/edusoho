import ajax from '../ajax';

const taskModule = (api) => {
  return {
    search(options) {
      return ajax(Object.assign({
        url: `${api}/tasks`,
        type: 'GET'
      }, options));
    }
  }
}

export default taskModule;