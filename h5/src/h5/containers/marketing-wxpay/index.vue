<template>
  <div v-if="isWeixinBrowser && payInfo" class="marketing-wxpay">
    <div class="marketing-wxpay__title">{{ $t('marketingPay.amountLabel') }}</div>
    <div class="marketing-wxpay__amount">
      <span class="amount-unit">￥</span>
      <span class="amount-number">{{ (payInfo.amount / 100).toFixed(2) }}</span>
    </div>
    <div class="marketing-wxpay__info">
      <div class="info-label">{{ $t('marketingPay.orderSnLabel') }}</div>
      <div class="info-desc">{{ payInfo.orderSn }}</div>
    </div>
    <div class="marketing-wxpay__info" style="border: none;">
      <div class="info-label">{{ $t('marketingPay.acceptLabel') }}</div>
      <div class="info-desc">{{ payInfo.siteName }}</div>
    </div>
    <div class="pay-btn" @click="handlePay">
      {{ $t('marketingPay.payNow') }}
    </div>
  </div>
  
</template>

<script>
import Api from '@/api';
import { Toast, Dialog } from 'vant';
import { mapState } from 'vuex';
import 'navigator.sendbeacon'
import store from '@/store';

export default {
  name: 'MarketingWXPay',
  data() {
    return {
      isLoading: false,
      payInfo: null,
      loginConfig: {},
      isPay: false,
    }
  },
  computed: {
    ...mapState({
      token: state => state.token
    }),
    isWeixinBrowser() {
      return /micromessenger/.test(navigator.userAgent.toLowerCase());
    },
  },
  async created() {
    document.title = this.$t('title.confirmPayment')

    if (this.$route.query.loginToken) {
      store.state.token = this.$route.query.loginToken
    }

    if (!this.token) {
      Api.loginConfig({}).then(res => {
        this.loginConfig = res;

        if (Number(res.weixinmob_enabled) && this.isWeixinBrowser) {
          this.wxLogin();
        }
      })

      return
    }

    const token = this.$route.query.payToken || ''

    if (!token) {
      Toast.fail('缺少关键信息')

      return
    }

    const result = await Api.getMarketingMallPayConfig({ data: { token } });

    if (result.success === false) {
      Dialog.alert({
        confirmButtonText: '我知道了',
        confirmButtonColor: '#165DFF',
        message: result.message,
      });

      return
    }

    this.payInfo = result
    this.handlePay()
    window.addEventListener('unload', () => this.closeOrder())
    window.addEventListener('pagehide', () => this.closeOrder())
  },
  methods: {
    async closeOrder() {
      if (this.isPay) return
      
      const url =  `/api/unified_payment/${this.payInfo.tradeSn}/close_trade`

      try {

      await fetch(url, {
        method: 'POST', // 设置请求方法为POST
        headers: {
          'X-Auth-Token': store.state.token,
          'Accept': 'application/vnd.edusoho.v2+json',
          'Content-Type': 'application/json', // 设置请求头中的Content-Type为JSON格式
        },
        keepalive: true
      })
    } catch(err) {
      console.log(err)
    }

    },
    handleAmount(amount) {
      return amount / 100
    },
    async handlePay() {
      const token = this.$route.query.payToken || ''

      const result = await Api.checkMarketingMallPayConfig({ params: { token } });

      if (result.success === false) {
        Dialog.alert({
          confirmButtonText: '我知道了',
          confirmButtonColor: '#165DFF',
          message: result.message,
        });

        return
      }

      // eslint-disable-next-line no-undef
      WeixinJSBridge.invoke(
        'getBrandWCPayRequest',
        this.payInfo.config,
        (res) => {
          if (res.err_msg == 'get_brand_wcpay_request:ok') {
            this.isPay = true
            window.location.href = this.payInfo.redirectUrl + `&isNeedCheckOrderStatus=1&sn=${this.payInfo.orderSn}`;
          } else if (res.err_msg == 'get_brand_wcpay_request:fail') {
            // alert('支付失败');
          } else if (res.err_msg == 'get_brand_wcpay_request:cancel') {
            // alert('支付已失败');
          }
        },
      );
    },
    wxLogin() {
      this.$router.push({
        path: '/auth/social',
        query: {
          type: 'wx',
          weixinmob_key: this.loginConfig.weixinmob_key,
          redirect: this.$route.query.redirect || this.$route.path,
          callbackType: this.$route.query.callbackType,
          activityId: this.$route.query.activityId,
        },
      });
    },
  }
}
</script>

<style lang="scss" scoped>
  .marketing-wxpay {
    &__title {
      margin-top: 24px;
      text-align: center;
      font-weight: 400;
      font-size: 16px;
      line-height: 24px;
      color: #272E3B;
    }
    
    &__amount {
      display: flex;
      align-items: flex-end;
      justify-content: center;
      margin-top: 12px;
      margin-bottom: 44px;
      color: #272E3B;
      font-weight: 600;

      .amount-unit {
        margin-right: 4px;
        font-size: 20px;
        line-height: 22px;
      }

      .amount-number {
        font-size: 36px;
        line-height: 32px;
      }
    }

    &__info {
      display: flex;
      justify-content: space-between;
      padding: 16px 0;
      margin: 0 16px;
      font-weight: 400;
      font-size: 14px;
      line-height: 22px;
      border-bottom: solid 1px #F2F3F5;

      .info-label {
        width: 64px;
        color: #86909C;
      }

      .info-desc {
        flex: 1;
        text-align: right;
        color: #272E3B;
      }
    }
  }

  .pay-btn {
    position: fixed;
    left: 16px;
    right: 16px;
    bottom: 16px;
    height: 44px;
    line-height: 44px;
    font-size: 16px;
    color: #fff;
    text-align: center;
    background: linear-gradient(90.22deg, #FE6301 0.16%, #FF4402 102.24%), #94BFFF;
    border-radius: 8px;
  }
</style>
