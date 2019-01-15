export default [
  {
    // 领取优惠券
    name: 'marketingActivities',
    url: '/marketing_activities/{activityId}/urls',
    method: 'POST'
  }, {
    // 班级下营销活动
    name: 'classroomsActivities',
    url: '/classrooms/{id}/marketing_activities',
    disableLoading: true
  }, {
    // 课程下营销活动
    name: 'coursesActivities',
    url: '/courses/{id}/marketing_activities',
    disableLoading: true
  }
];
