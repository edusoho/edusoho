import { formatTimeByNumber } from '@/utils/date-toolkit';

const filters = [
  {
    name: 'toMoney',
    handler(value) {
      return isNaN(Number(value)) ? '0.00' : (Number(value) / 100).toFixed(2);
    }
  },
  {
    name: 'isFree',
    handler(value) {
      return value ? 'value' : '免费';
    }
  },
  {
    name: 'taskType',
    handler(type) {
      switch (type) {
        case 'video':
          type = '视频';
          break;
        case 'audio':
          type = '音频';
          break;
        case 'live':
          type = '直播';
          break;
        case 'text':
          type = '图文';
          break;
        case 'doc':
          type = '文档';
          break;
        default:
          type = '暂不支持此类型';
      }
      return type;
    }
  },
  {
    name: 'filterTask',
    handler(task) {
      switch (task.type) {
        case 'video':
        case 'audio':
          if (task.mediaSource !== 'self' && task.type !== 'audio') {
            return '';
          }
          return ` | 时长:  ${formatTimeByNumber(task.length)}`;
        case 'live':
          // var endDate = getDate(task.endTime * 1000);
          // var startDate = getDate(task.startTime * 1000);
          // var nowDate = getDate();
          // if (endDate < nowDate) {
          //   if (task.activity.replayStatus == 'ungenerated') {
          //     return '  |  暂无回放';
          //   }
          //   return '  |  回放';
          // }
          // if (nowDate > startDate & nowDate < endDate) {
          //   return '  |  正在直播';
          // }
          // return `  |  开始:  ${formatTime(startDate)}`;
          return '';
        case 'text':
        case 'doc':
          return '';
        default:
          return '';
      }
    }
  },
  {
    name: 'filterJoinStatus',
    handler(code) {
      switch (code) {
        case 'success':
        case 'user.not_login':
          code = '加入学习';
          break;
        case 'user.locked':
          code = '用户被锁';
          break;
        case 'course.unpublished':
          code = '课程未发布';
          break;
        case 'course.closed':
          code = '课程已关闭';
          break;
        case 'course.not_buyable':
          code = '课程不可加入';
          break;
        case 'course.buy_expired':
          code = '购买有效期已过 ';
          break;
        case 'course.expired':
          code = '学习有效期已过';
          break;
        case 'course.only_vip_join_way':
          code = '只能通过VIP加入';
          break;
        default:
      }
      return code;
    }
  }
];

export default {
  install(Vue) {
    filters.map(item => {
      Vue.filter(item.name, item.handler);

      return item;
    });
  }
};
