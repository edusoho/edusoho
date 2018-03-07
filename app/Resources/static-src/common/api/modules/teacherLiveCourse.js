import ajax from '../ajax';

const teacherLiveCourseModule = (api) => {
  return {
    search(options) {
      return ajax(Object.assign({
        url: `${api}/teacherLiveCourses`,
        type: 'GET'
      }, options));
    }
  };
};

export default teacherLiveCourseModule;