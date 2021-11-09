<template>
  <div class="payPage">
    <e-loading v-if="isLoading" />
    <div class="payPage__order">
      <div class="order__head">
        {{ detail.title }}
      </div>
      <div class="order__infomation">
        <div class="sum">
          <span>待支付</span>
          <span class="sum__price"
            >¥ <span class="num">{{ detail.pay_amount | toMoney }}</span></span
          >
        </div>
      </div>
    </div>
    <div class="payPage__payBtn" @click="handlePay">
      立即支付
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState } from 'vuex';
import { Toast } from 'vant';

export default {
  data() {
    return {
      detail: {},
    };
  },
  computed: {
    ...mapState(['wechatSwitch', 'isLoading']),
  },
  mounted() {
    const {
      // eslint-disable-next-line camelcase
      pay_amount,
      title,
      sn,
      openid,
      targetType,
      targetId,
      payWay,
    } = this.$route.query;
    this.detail = {
      pay_amount,
      title,
      sn,
      openid,
      targetType,
      targetId,
      payWay,
    };
  },
  methods: {
    handlePay() {
      const returnUrl =
        window.location.origin +
        window.location.pathname +
        `#/pay_center?targetType=${this.detail.targetType}&targetId=${this.detail.targetId}&payWay=${this.detail.payWay}`;
      Api.createTrade({
        data: {
          gateway: 'WechatPay_JsH5',
          type: 'purchase',
          orderSn: this.detail.sn,
          openid: this.detail.openid,
          success_url: returnUrl,
        },
      })
        .then(data => {
          alert('test')
          // 微信支付优化：使用优惠卷抵扣后 0 元，不再调用微信支付。
          const { isPaid, payUrl } = data;
          if (isPaid) {
            window.location.href = payUrl;
          }

          // eslint-disable-next-line no-undef
          WeixinJSBridge.invoke(
            'getBrandWCPayRequest',
            data.platformCreatedResult,
            res => {
              if (res.err_msg == 'get_brand_wcpay_request:ok') {
                // if (this.wechatSwitch) {
                //   this.$router.replace({
                //     path: '/pay_success',
                //     query: {
                //       paidUrl: data.paidSuccessUrlH5,
                //     },
                //   });
                //   return;
                // }
                location.href = data.paidSuccessUrlH5;
                // this.$router.push({ path: data.paidSuccessUrlH5 });
              } else {
                if (res.err_msg == 'get_brand_wcpay_request:fail') {
                  alert('支付失败');
                } else if (res.err_msg == 'get_brand_wcpay_request:cancel') {
                  alert('支付已失败');
                }
              }
            },
          );
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
  },
};
</script>
