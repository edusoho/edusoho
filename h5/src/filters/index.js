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
    handler(task) {
      if (task.status !== 'published') {
        return '敬请期待';
      }
      const type = task.type;
      switch (type) {
        case 'video':
          return '视频';
        case 'audio':
          return '音频';
        case 'live':
          return '直播';
        case 'text':
          return '图文';
        case 'ppt':
        case 'doc':
          return '文档';
        default:
          return '暂不支持此类型';
      }
    }
  },
  {
    name: 'filterTask',
    handler(task) {
      if (task.status !== 'published') {
        return '';
      }
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
  },
  {
    name: 'filterOrderStatus',
    handler(status) {
      switch (status) {
        case 'created':
          status = '去支付';
          break;
        case 'success':
          status = '已付款';
          break;
        case 'refunded':
          status = '退款成功';
          break;
        case 'finished':
          status = '交易成功';
          break;
        case 'closed':
          status = '交易关闭';
          break;
        case 'paid':
          status = '已付款';
          break;
        default:
      }
      return status;
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
