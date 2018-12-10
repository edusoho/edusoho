import Api from '@/api';
import { Toast } from 'vant';

export default {
  methods: {
    couponHandle(coupon) {
      const token = coupon.token;

      // 未领券
      if (!coupon.currentUserCoupon) {
        coupon.currentUserCoupon = true;
        Api.receiveCoupon({
          query: { token }
        }).then(() => {
          Toast.success('领取成功');
          coupon.currentUserCoupon = true;
        }).catch(err => {
          Toast.fail(err.message);
        });
        return;
      }

      // 已领券
      const couponType = coupon.targetType;
      if (coupon.target) {
        const id = coupon.target.id;
        this.$router.push({
          path: `${couponType}/${id}`
        });
        return;
      }
      if (couponType === 'vip') {
        Toast.warning('你可以在电脑端或App上购买会员');
        return;
      }
      if (couponType === 'classroom') {
        this.$router.push({
          path: 'classroom/explore'
        });
        return;
      }
      this.$router.push({
        path: 'course/explore'
      });
    }
  }
};
