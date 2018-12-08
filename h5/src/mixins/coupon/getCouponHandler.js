import Api from '@/api';
import { Toast } from 'vant';

export default {
  methods: {
    couponHandle(value, couponList) {
      const data = value.item;
      const itemIndex = value.itemIndex;
      const token = data.token;
      const item = couponList[itemIndex];
      // debugger;
      if (data.currentUserCoupon) {
        const couponType = data.targetType;
        if (data.target) {
          const id = data.target.id;
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
        return;
      }

      Api.receiveCoupon({
        query: { token }
      }).then(res => {
        Toast.success('领取成功');
        item.currentUserCoupon = true;
        // xxxxxxxx
        // if (Number(res.targetId) !== 0) {
        //   item.target = {
        //     id: res.targetId
        //   };
        // }
      }).catch(err => {
        Toast.fail(err.message);
      });
    }
  }
};
