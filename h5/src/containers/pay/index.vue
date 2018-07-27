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
            v-show="paySettings.alipayEnabled"
            @click="payWay = 'Alipay_LegacyWap';selected = true">
            <div class="right"></div>
            <i></i>
            <img src="/static/images/zfb.png">
          </div>
          <div :class="['payWay__item', {'payWay__item--selected': !selected}]" 
            v-show="paySettings.wxpayEnabled"
            @click="payWay = 'WechatPay_MWeb'; selected = false">
            <div class="right"></div>
            <i></i>
            <img src="/static/images/wx.png">
          </div>
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
      payWay: 'Alipay_LegacyWap', // WechatPay_Js--微信内支付 WechatPay_MWeb--微信wap支付
      selected: true,
      paySettings: {},
      inWechat: this.isWeixinBrowser()
    };
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading
    }),
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
      this.payWay === 'WechatPay_MWeb' && this.isWeixinBrowser() && (this.payWay = 'WechatPay_Js')

      Api.createTrade({
        data: {
          gateway: this.payWay,
          type: 'purchase',
          orderSn: this.detail.sn
        }
      }).then(res => {
        window.location.href = res.payUrl
      })
    },
    isWeixinBrowser (){
      return /micromessenger/.test(navigator.userAgent.toLowerCase())
    }
  }
}
</script>
