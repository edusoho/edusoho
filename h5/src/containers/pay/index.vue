<template>
  <div class="payPage">
    <e-loading v-if="isLoading"></e-loading>
    <div class="payPage__order">
      <div class="order__head">
        支付方式
      </div>
      <div class="order__infomation">
        <div class="title">{{ detail.title }}</div>
        <div class="sum">
          <span>待支付</span>
          <span class="sum__price">¥ <span class="num">{{ detail.pay_amount | toMoney }}</span></span>
        </div>
        <div class="payWay">
          <div :class="['payWay__item', {'payWay__item--selected': payWay === 'Alipay_LegacyH5'}]"
            v-show="paySettings.alipayEnabled && !inWechat"
            @click="payWay = 'Alipay_LegacyH5';selected = true">
            <img class="correct" src="static/images/correct.png">
            <div class="right"></div>
            <img src="static/images/zfb.png">
          </div>
          <div :class="['payWay__item', {'payWay__item--selected': payWay === 'WechatPay_H5'}]"
            v-show="paySettings.wxpayEnabled"
            @click="payWay = 'WechatPay_H5'; selected = false">
            <img class="correct" src="static/images/correct.png">
            <div class="right"></div>
            <img src="static/images/wx.png">
          </div>
        </div>
      </div>
    </div>
    <div :class="['payPage__payBtn', {'disabled': !validPayWay}]" @click="handlePay">
      {{ validPayWay ? '立即支付' : '无可用支付方式'}}
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
      // WechatPay_JsH5--微信内支付 WechatPay_H5--微信wap支付
      payWay: '',
      selected: true,
      paySettings: {},
      inWechat: this.isWeixinBrowser(),
      targetType: this.$route.query.targetType,
    };
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading
    }),
    validPayWay() {
      return this.paySettings.wxpayEnabled ||
        (this.paySettings.alipayEnabled && !this.inWechat);
    }
  },
  async created () {
    this.paySettings = await Api.getSettings({
      query: {
        type: 'payment'
      }
    })
    if (this.paySettings.alipayEnabled && !this.inWechat) {
      this.payWay = 'Alipay_LegacyH5';
    } else if (this.paySettings.wxpayEnabled) {
      this.payWay = 'WechatPay_H5';
    }
    const { source, id, sn, targetId } = this.$route.query;
    if (source !== 'order') {
      Api.createOrder({
        data: {
          targetType: this.targetType,
          targetId: id,
          isOrderCreate: 1,
          couponCode: this.$route.params.couponCode
        }
      }).then(res => {
        this.detail = Object.assign({}, res)
      })
    } else {
      // 从我的订单入口进入
      Api.getOrderDetail({
        query: {
          sn
        }
      }).then(res => {
        if (res.status === 'success' && targetId) {
          this.$router.push({
            path: `/course/${targetId}`,
          })
        }
        this.detail = Object.assign({}, res)
      })
    }
  },
  methods: {
    handlePay () {
      if (!this.validPayWay) return

      const isWxPay = this.payWay === 'WechatPay_H5' && this.inWechat
      if (isWxPay) {
        window.location.href = `${window.location.origin}/pay/center/wxpay_h5?pay_amount=` +
          `${this.detail.pay_amount}&title=${this.detail.title}&sn=${this.detail.sn}`;
        return;
      }

      Api.createTrade({
        data: {
          gateway: this.payWay,
          type: 'purchase',
          orderSn: this.detail.sn,
          app_pay: 'Y'
        }
      }).then(res => {
        if (this.payWay === 'WechatPay_H5') {
          this.getTradeInfo(res.tradeSn).then(() => {
            window.location.href = res.mwebUrl
          });
          return;
        }
        window.location.href = res.payUrl
      })
    },
    isWeixinBrowser (){
      return /micromessenger/.test(navigator.userAgent.toLowerCase())
    },
    getTradeInfo(tradeSn) {
      // 轮询问检测微信内支付是否支付成功
      return Api.getTrade({
        query: {
          tradesSn: tradeSn,
        }
      }).then((res) => {
        if (res.isPaid) {
          window.location.href = window.location.origin + res.paidSuccessUrlH5
          return;
        }
        setTimeout(() => {
          this.getTradeInfo(tradeSn);
        },2000)
      })
    }
  }
}
</script>
