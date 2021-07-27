import {
  formatTimeByNumber,
  formatCompleteTime,
  formatSimpleHour,
} from '@/utils/date-toolkit';
import i18n from '@/lang';

const filters = [
  {
    name: 'toMoney',
    handler(value) {
      return isNaN(Number(value)) ? '0.00' : (Number(value) / 100).toFixed(2);
    },
  },
  {
    name: 'isFree',
    handler(value) {
      return value ? 'value' : i18n.t('filters.free');
    },
  },
  {
    name: 'taskType',
    handler(task) {
      if (task.status !== 'published') {
        return i18n.t('filters.stayTuned');
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
        case 'testpaper':
          return '考试';
        case 'homework':
          return '作业';
        case 'exercise':
          return '练习';
        default:
          return i18n.t('filters.doesNotSupportThisType');
      }
    },
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
    },
  },
  {
    name: 'filterTaskTime',
    handler(task) {
      if (task.status !== 'published') {
        return i18n.t('filters.stayTuned');
      }
      switch (task.type) {
        case 'video':
        case 'audio':
          if (task.mediaSource !== 'self' && task.type !== 'audio') {
            return '';
          }
          return `${formatTimeByNumber(task.length)}`;
        case 'live':
          // eslint-disable-next-line no-case-declarations
          const now = new Date().getTime();
          // eslint-disable-next-line no-case-declarations
          const startTimeStamp = new Date(task.startTime * 1000);
          // eslint-disable-next-line no-case-declarations
          const endTimeStamp = new Date(task.endTime * 1000);
          // 直播未开始
          if (now <= startTimeStamp) {
            return `${formatCompleteTime(startTimeStamp)}${i18n.t('filters.start')}`;
          }
          if (now > endTimeStamp) {
            if (task.activity.replayStatus === 'ungenerated') {
              return i18n.t('filters.over');
            }
            return i18n.t('filters.replay');
          }
          return i18n.t('filters.live');
        // case 'testpaper':
        //   const nowTime = new Date().getTime();
        //   const testStartTime = new Date(task.startTime * 1000);
        //   // 考试未开始
        //   if (nowTime <= testStartTime) {
        //     return `${formatCompleteTime(startTimeStamp)}开始`;
        //   }
        //   return '';
        case 'text':
        case 'doc':
        case 'ppt':
        case 'testpaper':
        case 'homework':
        case 'exercise':
          return '';
        default:
          return i18n.t('filters.notCurrentlySupported');
      }
    },
  },
  {
    name: 'filterCourse',
    handler(task) {
      if (task.status !== 'published') {
        return i18n.t('filters.stayTuned');
      }
      switch (task.type) {
        case 'live':
          // eslint-disable-next-line no-case-declarations
          const now = new Date().getTime();
          // eslint-disable-next-line no-case-declarations
          const startTimeStamp = new Date(task.startTime);
          // eslint-disable-next-line no-case-declarations
          const endTimeStamp = new Date(task.endTime);
          // 直播未开始
          if (now <= startTimeStamp) {
            return `${formatCompleteTime(startTimeStamp)}${i18n.t('filters.start')}`;
          }
          if (now > endTimeStamp) {
            if (task.activity.replayStatus === 'ungenerated') {
              return i18n.t('filters.over');
            }
            return i18n.t('filters.replay');
          }
          return i18n.t('filters.live');
        // case 'testpaper':
        //   const nowTime = new Date().getTime();
        //   const testStartTime = new Date(task.startTime * 1000);
        //   // 考试未开始
        //   if (nowTime <= testStartTime) {
        //     return `${formatCompleteTime(startTimeStamp)}开始`;
        //   }
        //   return '';
        case 'text':
        case 'doc':
        case 'ppt':
        case 'testpaper':
        case 'homework':
        case 'exercise':
          return '';
        default:
          return i18n.t('filters.notCurrentlySupported');
      }
    },
  },
  {
    name: 'filterJoinStatus',
    handler(code, type = 'course', vipAccessToJoin) {
      if (vipAccessToJoin) {
        return '会员免费学';
      }
      const targetType = {
        course: '课程',
        classroom: '班级',
      };
      switch (code) {
        case 'success':
        case 'user.not_login':
          code = '加入学习';
          break;
        case 'user.locked':
          code = '用户被锁定';
          break;
        case 'member.member_exist':
          code = '课程学员已存在';
          break;
        case `${type}.reach_max_student_num`:
          code = '学员达到上限';
          break;
        case `${type}.not_found`:
          code = '计划不存在';
          break;
        case `${type}.unpublished`:
          code = `${targetType[type]}未发布`;
          break;
        case `${type}.closed`:
          code = `${targetType[type]}已关闭`;
          break;
        case `${type}.not_buyable`:
          code = `${targetType[type]}不可加入`;
          break;
        case `${type}.buy_expired`:
          code = '购买有效期已过';
          break;
        case `${type}.expired`:
          code = '学习有效期已过';
          break;
        case `${type}.only_vip_join_way`:
        case 'course.only_vip_join_way': // type 为班级时，code显示为 'course.only_vip_join_way', 此处临时处理
          code = '只能通过VIP加入';
          break;
        default:
      }
      return code;
    },
  },
  {
    name: 'filterGoodsBuyStatus',
    handler(currentSku, type = 'course', vipAccessToJoin) {
      let code = currentSku.access.code;
      if (
        vipAccessToJoin &&
        code !== 'member.member_exist' &&
        code !== `${type}.buy_expired` &&
        code !== `${type}.expired`
      ) {
        return '会员免费学';
      }
      const targetType = {
        course: '课程',
        classroom: '班级',
      };
      switch (code) {
        case 'success':
        case 'user.not_login':
          code = '立即购买';
          break;
        case 'user.locked':
          code = '用户被锁定';
          break;
        case 'member.member_exist':
          code = '课程学员已存在';
          break;
        case `${type}.reach_max_student_num`:
          code = '学员达到上限';
          break;
        case `${type}.not_found`:
          code = '计划不存在';
          break;
        case `${type}.unpublished`:
          code = `${targetType[type]}未发布`;
          break;
        case `${type}.closed`:
          code = `${targetType[type]}已关闭`;
          break;
        case `${type}.not_buyable`:
          // code = `${targetType[type]}不可加入`;
          code = `抱歉，该商品为限制商品，请联系客服`;
          break;
        case `${type}.buy_expired`:
          code = '购买有效期已过';
          break;
        case `${type}.expired`:
          code = '学习有效期已过';
          break;
        case `${type}.only_vip_join_way`:
        case 'course.only_vip_join_way': // type 为班级时，code显示为 'course.only_vip_join_way', 此处临时处理
          code = `${currentSku.vipLevelInfo.name}免费`;
          break;
        default:
      }
      return code;
    },
  },
  {
    name: 'filterOrderStatus',
    handler(status) {
      switch (status) {
        case 'created':
          status = i18n.t('filters.toPay');
          break;
        case 'paying':
          status = i18n.t('filters.toPay');
          break;
        case 'success':
          status = i18n.t('filters.paid');
          break;
        case 'refunded':
          status = i18n.t('filters.refunded');
          break;
        case 'finished':
          status = i18n.t('filters.successfulTrade');
          break;
        case 'closed':
          status = i18n.t('filters.transactionClosure');
          break;
        case 'paid':
          status = i18n.t('filters.paid');
          break;
        case 'refunding':
          status = i18n.t('filters.paid');
          break;
        case 'fail':
          status = i18n.t('filters.paid');
          break;
        default:
      }
      return status;
    },
  },
  {
    name: 'formateTime',
    handler(target) {
      switch (target.task.type) {
        case 'video':
        case 'audio':
          return `时长: ${formatTimeByNumber(target.task.length)}`;
        case 'live':
          // eslint-disable-next-line no-case-declarations
          const now = new Date().getTime();
          // eslint-disable-next-line no-case-declarations
          const time = formatSimpleHour(new Date(target.task.startTime * 1000));
          // eslint-disable-next-line no-case-declarations
          const startTimeStamp = new Date(target.task.startTime * 1000);
          // eslint-disable-next-line no-case-declarations
          const endTimeStamp = new Date(target.task.endTime * 1000);
          // 直播未开始
          if (now <= startTimeStamp) {
            return `${time} | 未开始`;
          }
          if (now > endTimeStamp) {
            if (target.activity.replayStatus === 'ungenerated') {
              return `${time} | 已结束`;
            }
            return `${time} | 观看回放`;
          }
          return `${time} | 正在直播`;
        case 'text':
        case 'doc':
        case 'ppt':
        case 'testpaper':
        case 'homework':
        case 'exercise':
        case 'download':
        case 'discuss':
          return '';
        default:
          return i18n.t('filters.notCurrentlySupported');
      }
    },
  },
  {
    name: 'formateLiveTime',
    handler(time) {
      return `${formatSimpleHour(new Date(time))} | `;
    },
  },
];

export default {
  install(Vue) {
    filters.map(item => {
      Vue.filter(item.name, item.handler);
      return item;
    });
  },
};
