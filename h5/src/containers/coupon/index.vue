<template>
  <div class="coupon-receive-page">
    <div :class="['coupon-card-bg', 'clearfix', couponStatus]">
      <div class="coupon-info">{{ message }}</div>
      <div class="e-coupon__body" v-if="Object.keys(item).length">
        <div class="e-coupon__header clearfix">
          <span class="e-coupon__price" v-html="priceHtml(item)"></span>
          <div class="e-coupon__name">
            <div class="text-overflow text-14 coupon-name">{{ item.name }}</div>
            <span class="text-10">{{ timeExpire(item) }}</span>
          </div>
          <span class="coupon-button" @click="couponHandle(item)">去使用</span>
        </div>
        <div class="e-coupon__middle"></div>
        <div class="e-coupon__bottom text-overflow">
          可用范围：{{ scopeFilter(item) }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import Api from '@/api';
  import { Toast } from 'vant';
  import couponMixin from '@/mixins/coupon'
  const ALL_TYPE = {
    classroom: 'classroom',
    course: 'course',
    vip: 'vip',
    all: 'all'
  };
  export default {
    mixins: [couponMixin],
    data() {
      return {
        item: {},
        message: ''
      };
    },
    computed: {
      couponStatus() {
        return Object.keys(this.item).length ? 'coupon-receive-success' : '';
      }
    },
    created() {
      // 通过链接领取优惠券
      const token = this.$route.params.token;

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

      if (token) {
        Api.receiveCoupon({
          data: { token }
        }).then(res => {
          this.item = res;
          this.message = '恭喜您成功领取了一张优惠券！';
        }).catch(err => {
          this.message = '优惠券领取失败！'
          Toast.fail(err.message);
        });
      }
    },
    methods: {
      couponHandle(coupon) {
        /* 已领券 */
        const targetType = coupon.targetType === ALL_TYPE.all ?
          ALL_TYPE.course : coupon.targetType; // 全站优惠券跳转课程列表页
        const allType = Object.values(ALL_TYPE);

        if (!allType.includes(targetType)) {
          Toast.fail(`暂不支持查看：${targetType}类型商品`);
          return;
        }

        if (coupon.target) {
          const targetId = coupon.target.id;
          this.getPathParams(targetType, targetId).then(({ id }) => {
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
          path: `/${targetType}/explore` // course/explore | classroom/explore
        });
      },
    }
  }
</script>
