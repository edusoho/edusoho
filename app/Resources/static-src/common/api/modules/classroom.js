import ajax from '../ajax';

const classroomModule = (api) => {
  return {
    join(options) {
      return ajax(Object.assign({
        url: `${api}/classrooms/${options.params.classroomId}/members`,
        type: 'POST'
      }, options));
    }
  };
};

export default classroomModule;