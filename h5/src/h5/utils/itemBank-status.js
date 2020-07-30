export default status => {
  switch (status) {
    case 'doing':
    case 'paused':
      return {
        text: '继续答题',
        class: 'learn-btn learn-btn-doing',
      };
    case 'reviewing':
    case 'finished':
      return {
        text: '查看报告',
        class: 'learn-btn learn-btn-report',
      };
    default:
      return {
        text: '开始做题',
        class: 'learn-btn',
      };
  }
};
