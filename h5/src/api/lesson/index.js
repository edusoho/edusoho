export default [
  {
    // 获取直播地址
    name: 'getLiveUrl',
    url: '/lessons/{taskId}/live_tickets/{no}',
    method: 'GET'
  }, {
    // 获取录播地址
    name: 'getLiveReplayUrl',
    url: '/lessons/{taskId}/replay',
    method: 'GET'
  }, {
    // 获取直播No
    name: 'requestLiveNo',
    url: '/lessons/{taskId}/live_tickets',
    method: 'POST'
  }, {
    // 获得考试说明信息
    name: 'testpaperIntro',
    url: '/testpaper_infos/{testId}',
    method: 'GET'
  }, {
    // 获得考试成绩
    name: 'testpaperResult',
    url: '/testpaper_results/{resultId}',
    method: 'GET'
  },
  {
    // 获取考试试卷信息
    name: 'getExamInfo',
    url: '/testpapers/{testId}/actions',
    method: 'POST'
  },
  {
    // 考试交卷
    name: 'handExam',
    url: '/testpaper_result',
    method: 'POST'
  }, {
    // 获得作业说明信息
    name: 'getHomeworkIntro',
    url: '/courses/{courseId}/task_medias/{taskId}',
    method: 'GET'
  },
  {
    // 获取作业信息
    name: 'getHomeworkInfo',
    url: '/homeworks/{homeworkId}/results',
    method: 'POST'
  },
  {
    // 作业提交
    name: 'handHomework',
    url: '/homeworks/{homeworkId}/results/{homeworkResultId}',
    method: 'PUT'
  },
  {
    // 获得作业结果
    name: 'homeworkResult',
    url: '/homeworks/{homeworkId}/results/{homeworkResultId}',
    method: 'GET'
  }
];
