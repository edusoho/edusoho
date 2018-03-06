import {
  closeCourse,
  publishCourse,
  deleteCourse
} from './help';

import CourseManage from './course-manage';

cd.select({
  el: '#select-single',
  type: 'single'
}).on('change', (value, text) => {
  console.log('single', value, text);
});

closeCourse();
deleteCourse();
publishCourse();

new CourseManage();



