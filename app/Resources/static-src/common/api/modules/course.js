import ajax from '../ajax';

const courseModule = (api) => {
  return {
    get(options) {
      return ajax(Object.assign({
        url: `${api}/courses/${options.params.courseId}`,
      }, options));
    }
  }
}

export default courseModule;