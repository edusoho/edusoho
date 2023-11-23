import ajax from '../ajax';

const courseTaskEventModule = (api) => {
  return {
    pushEvent(options) {
      return ajax(Object.assign({
        url: `${api}/courses/${options.params.courseId}/tasks/${options.params.taskId}/event_v2/${options.params.eventName}`,
        type: 'PATCH',
      }, options));
    }
  };
};

export default courseTaskEventModule;