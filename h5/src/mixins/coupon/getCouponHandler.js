import Api from '@/api';
import { Toast } from 'vant';

const ALL_TYPE = {
  classroom: 'classroom',
  course: 'course',
  vip: 'vip',
  all: 'all'
};

export default {
  methods: {
    couponHandle(coupon) {
      const token = coupon.token;

      // 未领券
      if (!coupon.currentUserCoupon) {
        Api.receiveCoupon({
          data: { token }
        }).then(() => {
          Toast.success('领取成功');
          coupon.currentUserCoupon = true;
        }).catch(err => {
          Toast.fail(err.message);
        });
        return;
      }

      // 已领券
      const targetType = coupon.targetType === ALL_TYPE.all ?
        ALL_TYPE.course : coupon.targetType; // 全站优惠券跳转课程列表页
      const allType = Object.values(ALL_TYPE);

      if (!allType.includes(targetType)) {
        Toast.fail(`暂不支持查看：${targetType}类型商品`);
        return;
      }

      if (coupon.target) {
        const targetId = coupon.target.id;
        this.getPathParams('course', targetId).then(({ id }) => {
          if (!id) return;
          this.$router.push({
            path: `${targetType}/${id}` // course/{id} | classroom/{id}
          });
        });
        return;
      }

      if (targetType === ALL_TYPE.vip) {
        Toast.fail('你可以在电脑端或App上购买会员');
        return;
      }

      this.$router.push({
        path: `${targetType}/explore` // course/explore | classroom/explore
      });
    },
    // 课程的id 需要转换成计划id 跳转到对应计划详情页
    getPathParams(type, id) {
      if (type !== ALL_TYPE.course) {
        return Promise.resolve({ type, id });
      }

      return Api.getCourseByCourseSet({
        query: { id }
      }).then(res => {
        if (res.length && res[0]) {
          return { id: res[0].id };
        }
        return Promise.reject({
          error: { message: '当前课程不存在了' }
        });
      }).catch(err => {
        Toast.fail(err.error.message);
      });
    }
  }
};
