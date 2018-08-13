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
          <div :class="['payWay__item', {'payWay__item--selected': selected}]"
            v-show="paySettings.alipayEnabled && !inWechat"
            @click="payWay = 'Alipay_LegacyH5';selected = true">
            <div class="right"></div>
            <i></i>
            <img src="static/images/zfb.png">
          </div>
          <div :class="['payWay__item', {'payWay__item--selected': !selected}]"
            v-show="paySettings.wxpayEnabled"
            @click="payWay = 'WechatPay_H5'; selected = false">
            <div class="right"></div>
            <i></i>
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
      payWay: 'Alipay_LegacyH5', // WechatPay_JsH5--微信内支付 WechatPay_H5--微信wap支付
      selected: true,
      paySettings: {},
      inWechat: this.isWeixinBrowser()
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
    const { source, id, sn } = this.$route.query;
    if (source !== 'order') {
      Api.createOrder({
        data: {
          targetType: 'course',
          targetId: this.$route.query.id,
          isOrderCreate: 1
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
        window.location.href = this.payWay ===  'Alipay_LegacyH5' ? res.payUrl: res.mwebUrl
      })
    },
    isWeixinBrowser (){
      return /micromessenger/.test(navigator.userAgent.toLowerCase())
    }
  }
}
</script>
