import ajax from '../ajax';

const courseTaskModule = (api) => {
  return {
    search(options) {
      return ajax(Object.assign({
        url: `${api}/courseTasks`,
        type: 'GET'
      }, options));
    }
  }
}

export default courseTaskModule;