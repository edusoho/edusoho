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
    couponHandle(coupon, isReceive = false) {
      // 未登录跳转登录页面
      if (!this.$store.state.token) {
        this.$router.push({
          name: 'login',
          query: {
            redirect: this.$route.fullPath
          }
        });
        return;
      }


      /* 未领券 */
      if (!coupon.currentUserCoupon && !isReceive) {
        const token = coupon.token;
        Api.receiveCoupon({
          data: { token }
        }).then(res => {
          coupon.currentUserCoupon = res;
          Toast.success({
            message: '领取成功',
            duration: 2000
          });
        }).catch(err => {
          Toast.fail(err.message);
          coupon.unreceivedNum = '0';
        });
        return;
      }
      this.hasreceiveCoupon(coupon);
    },
    hasreceiveCoupon(coupon) {
      /* 已领券 */
      const targetType = coupon.targetDetail.product;
      const allType = Object.values(ALL_TYPE);

      if (!allType.includes(targetType)) {
        Toast.fail(`暂不支持查看：${targetType}类型商品`);
        return;
      }

      // 指定课程或者班级
      if (coupon.target) {
        const targetId = coupon.target.id;
        // 指定vip
        if (targetType === ALL_TYPE.vip) {
          this.$router.push({
            path: '/vip',
            query: {
              id: targetId
            }
          });
          return;
        }
        this.getPathParams(targetType, targetId).then(({ id }) => {
          if (!id) return;
          this.$router.push({
            path: `/${targetType}/${id}` // course/{id} | classroom/{id}
          });
        });
        return;
      }
      // 全站跳转到发现页
      if (targetType === ALL_TYPE.all) {
        this.$router.push({
          path: '/'
        });
        return;
      }

      // 所有班级或者课程
      this.$router.push({
        path: '/'
      });
    },
    /* 课程的id 需要转换成计划id 跳转到对应计划详情页 */
    getPathParams(type, id) {
      if (type !== ALL_TYPE.course) {
        return Promise.resolve({ id });
      }

      return Api.getCourseByCourseSet({
        query: { id }
      }).then(res => {
        if (res.length && res[0]) {
          return { id: res[0].id };
        }
        return Promise.reject({ message: '当前课程不存在了' });
      }).catch(err => {
        Toast.fail(err.message);
      });
    }
  }
};
