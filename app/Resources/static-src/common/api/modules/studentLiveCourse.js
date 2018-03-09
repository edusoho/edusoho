import ajax from '../ajax';

const studentLiveCourseModule = (api) => {
  return {
    search(options) {
      return ajax(Object.assign({
        url: `${api}/studentLiveCourses`,
        type: 'GET'
      }, options));
    }
  };
};

export default studentLiveCourseModule;