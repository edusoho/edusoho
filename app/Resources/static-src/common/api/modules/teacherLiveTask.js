import ajax from '../ajax';

const teacherLiveTaskModule = (api) => {
  return {
    search(options) {
      return ajax(Object.assign({
        url: `${api}/teacherLiveTasks`,
        type: 'GET'
      }, options));
    }
  }
}

export default teacherLiveTaskModule;