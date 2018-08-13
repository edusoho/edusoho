<template>
  <div class="payPage">
    <e-loading v-if="isLoading"></e-loading>
    <div class="payPage__order">
      <div class="order__head">
        {{ detail.title }}
      </div>
      <div class="order__infomation">
        <div class="sum">
          <span>待支付</span>
          <span class="sum__price">¥ <span class="num">{{ detail.pay_amount | toMoney }}</span></span>
        </div>
      </div>
    </div>
    <div class="payPage__payBtn" @click="handlePay">
      立即支付
    </div>
  </div>
</template>

<script>
import Api from '@/api'
import axios from 'axios'
import { mapState } from 'vuex';
export default {
  data () {
    return {
      detail: {},
    };
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading
    }),
  },
  mounted() {
    const { pay_amount, title, sn, openid } = this.$route.query;
    this.detail = { pay_amount, title, sn, openid };
  },
  methods: {
    handlePay() {
      Api.createTrade({
        data: {
          gateway: 'WechatPay_JsH5',
          type: 'purchase',
          orderSn: this.detail.sn,
          openid: this.detail.openid,
        }
      }).then((data) => {
        WeixinJSBridge.invoke(
          'getBrandWCPayRequest',
          data.platformCreatedResult,
          (res) => {
            if (res.err_msg == 'get_brand_wcpay_request:ok') {
              this.$router.push({ path: data.paidSuccessUrlH5 });
            } else {
              if (res.err_msg == 'get_brand_wcpay_request:fail') {
                alert('支付失败');
              } else if (res.err_msg == 'get_brand_wcpay_request:cancel') {
                alert('支付已失败')
              }
            }

          }
        );
      })
    },
  }
}
</script>
