import ajax from '../ajax';

const courseModule = (api) => {
  return {
    get(options) {
      return ajax(Object.assign({
        url: `${api}/courses/${options.params.courseId}`,
      }, options));
    },
    search(options) {
      return ajax(Object.assign({
        url: `${api}/courses`,
      }, options));
    }
  };
};

export default courseModule;