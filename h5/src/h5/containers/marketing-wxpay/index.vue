<template>
  <div v-if="isWeixinBrowser" class="marketing-wxpay">
    <div class="marketing-wxpay__title">{{ $t('marketingPay.amountLabel') }}</div>
    <div class="marketing-wxpay__amount">
      <span class="amount-unit">￥</span>
      <span class="amount-number">{{ payInfo.amount }}</span>
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
import { Toast } from 'vant';

export default {
  name: 'MarketingWXPay',
  data() {
    return {
      isLoading: false,
      payInfo: {}
    }
  },
  computed: {
    isWeixinBrowser() {
      return /micromessenger/.test(navigator.userAgent.toLowerCase());
    },
  },
  async created() {
    const token = this.$route.query.payToken || ''

    if (!token) {
      Toast.fail('缺少关键信息')

      return
    }

    this.payInfo = await Api.getMarketingPayConfig({ data: { token } });
  },
  methods: {
    async handlePay() {
      // eslint-disable-next-line no-undef
      WeixinJSBridge.invoke(
        'getBrandWCPayRequest',
        this.payInfo.config,
        (res) => {
          if (res.err_msg == 'get_brand_wcpay_request:ok') {
            window.location.href = this.payInfo.redirectUrl + '&isNeedCheckOrderStatus=1';
          } else if (res.err_msg == 'get_brand_wcpay_request:fail') {
            // alert('支付失败');
          } else if (res.err_msg == 'get_brand_wcpay_request:cancel') {
            // alert('支付已失败');
          }
        },
      );
    }
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
      color: #272E3B;
      font-weight: 600;

      .amount-unit {
        margin-right: 4px;
        font-size: 20px;
        line-height: 22px;
      }

      .amount-number {
        font-size: 36px;
        line-height: 44px;
      }
    }

    &__info {
      display: flex;
      justify-content: space-between;
      width: 100%;
      padding: 16px 0;
      margin: 0 16px;
      font-weight: 400;
      font-size: 14px;
      line-height: 22px;
      border-bottom: solid 1px #F2F3F5;

      .info-label {
        color: #86909C;
      }

      .info-desc {
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
    line-height: 24px;
    color: #fff;
    text-align: center;
    background: linear-gradient(90.22deg, #FE6301 0.16%, #FF4402 102.24%), #94BFFF;
    border-radius: 8px;
  }
</style>
