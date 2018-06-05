const config = [
  {
    name: 'login',
    url: '/tokens',
    method: 'POST',
  },
  {
    name: 'getCourse',
    url: '/course_sets/{courseId}',
    method: 'GET',
  },
  {
    name: 'getCourses',
    url: '/course_sets',
    method: 'GET',
  },
  {
    name: 'course',
    url: '/course?limit=4&sort=-studentNum',
    method: 'GET',
  },
];

export default config;
