import ajax from '../ajax';

const courseTaskResultModule = (api) => {
  return {
    update(options) {
      return ajax(Object.assign({
        url: `${api}/courses/${options.params.courseId}/tasks/${options.params.taskId}/results`,
        type: 'POST',
      }, options));
    }
  };
};

export default courseTaskResultModule;