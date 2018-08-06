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
    const { pay_amount, title, sn, openId } = this.$route.query;
    this.detail = { pay_amount, title, sn, openId };
  },
  methods: {
    handlePay() {
      Api.createTrade({
        data: {
          gateway: 'WechatPay_MWeb',
          type: 'purchase',
          orderSn: this.detail.sn,
          openId: this.detail.openId,
        }
      }).then(res => {
        WeixinJSBridge.invoke(
          'getBrandWCPayRequest',
          res,
          (res) => {
            if (res.err_msg == 'get_brand_wcpay_request:ok') {
              this.$router.push({name: 'learning'});
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
