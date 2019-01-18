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

      const token = coupon.token;

      /* 未领券 */
      if (!coupon.currentUserCoupon && !isReceive) {
        Api.receiveCoupon({
          data: { token }
        }).then(() => {
          Toast.success({
            message: '领取成功',
            duration: 2000
          });
          coupon.currentUserCoupon = true;
        }).catch(err => {
          Toast.fail(err.message);
          coupon.unreceivedNum = '0';
        });
        return;
      }

      /* 已领券 */
      const targetType = coupon.targetType === ALL_TYPE.all ?
        ALL_TYPE.course : coupon.targetType; // 全站优惠券跳转课程列表页
      const allType = Object.values(ALL_TYPE);

      if (!allType.includes(targetType)) {
        Toast.fail(`暂不支持查看：${targetType}类型商品`);
        return;
      }

      if (targetType === ALL_TYPE.vip) {
        const targetId = coupon.target && coupon.target.id;
        this.$router.push({
          path: '/vip',
          query: {
            id: targetId
          }
        });
        return;
      }

      if (coupon.target) {
        const targetId = coupon.target.id;
        this.getPathParams(targetType, targetId).then(({ id }) => {
          if (!id) return;
          this.$router.push({
            path: `/${targetType}/${id}` // course/{id} | classroom/{id}
          });
        });
        return;
      }

      this.$router.push({
        path: `/${targetType}/explore` // course/explore | classroom/explore
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
    },
    activityHandle(activityId) {
      const params = {
        domainUri: 'http://lvliujie.st.edusoho.cn',
        itemUri: '',
        source: 'h5'
      };
      Api.marketingActivities({
        query: {
          activityId
        },
        data: params
      }).then(res => {
        window.location.href = res.url;
      }).catch(err => {
        console.log(err.message);
      });
    }
  }
};
