<template>
  <div class="order">
    <div class="goods-info">
      <e-loading v-if="isLoading"></e-loading>
      <!-- 商品缩略图 -->
      <e-course
        v-if="Object.keys(course).length > 0"
        type="confirmOrder"
        :typeList="targetType"
        :duration="course.duration"
        :order="course"
        :course="course">
      </e-course>
      <!-- 使用优惠券 -->
      <div class="order-coupon">
        <div class="coupon-column" @click="showList = true">
          <span>优惠券</span>
          <span :class="['red',itemData ? 'coupon-money':'']">{{ couponShow }}<span class="coupon-type" v-if="itemData">{{itemData.type | couponType}}</span>
            <i class="iconfont icon-arrow-right"></i>
          </span>
        </div>
        <van-popup class="e-popup full-height-popup coupon-popup" v-model="showList" position="bottom" :overlay="false">
          <van-nav-bar title="优惠券"
            class="nav-bar"
            :left-arrow="true"
            @click-left="disuse"/>
          <div :class="['btn-coupon-exit', {active: activeItemIndex < 0}]" @click="disuse">不使用优惠
            <i class="h5-icon h5-icon-circle"></i>
            <i class="h5-icon h5-icon-check"></i>
          </div>
          <div class="e-popup__content coupon-popup__content">
            <div class="coupon-number-change">
              <van-field
                v-model="preferenceCode"
                center
                border
                clearable
                placeholder="请输入优惠码"
              >
                <van-button slot="button" size="small" type="primary" :disabled="!preferenceCode" @click='usePreferenceCode'>使用</van-button>
              </van-field>
            </div>
            <coupon v-for="(item, index) in course.availableCoupons"
              :key="index"
              :coupon="item"
              :index="index"
              :active="activeItemIndex"
              :showButton="false"
              :showSelecet="true"
              @chooseItem="chooseItem">
            </coupon>
            <div class="coupon-empty" v-show="!course.availableCoupons.length">
              <img class="empty-img" src='static/images/coupon_empty.png'>
              <div class="empty-text">暂无优惠券</div>
          </div>
          </div>
        </van-popup>
      </div>
      <div class="order-goods-item" v-if="targetType !== 'vip'">
        <span>学习有效期</span>
        <span class="gray-dark" v-html="getValidity"></span>
      </div>
    </div>
    <div class="payPage">
      <e-loading v-if="isLoading"></e-loading>
      <div class="payPage__order">
        <div class="order__head">
          支付方式
        </div>
        <div class="order__infomation">
          <!-- <div class="title">{{ detail.title }}</div>
          <div class="sum">
            <span>待支付</span>
            <span class="sum__price">¥ <span class="num">{{ detail.pay_amount | toMoney }}</span></span>
          </div> -->
          <div class="payWay">
            <div :class="['payWay__item', {'payWay__item--selected': payWay === 'Alipay_LegacyH5'}]"
              v-show="paySettings.alipayEnabled && !inWechat"
              @click="payWay = 'Alipay_LegacyH5';selected = true">
              <img class="correct" src="static/images/correct.png">
              <div class="right"></div>
              <img class="payWay__img" src="static/images/zfb.png">
            </div>
            <div :class="['payWay__item', {'payWay__item--selected': payWay === 'WechatPay_H5'}]"
              v-show="paySettings.wxpayEnabled"
              @click="payWay = 'WechatPay_H5'; selected = false">
              <img class="correct" src="static/images/correct.png">
              <div class="right"></div>
              <img class="payWay__img" src="static/images/wx.png">
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class='order-footer'>
      <div class='order-footer__text'>
        实付：<div class="price">{{total}}</div>
        <div class="discount" v-show="itemData">已优惠{{couponMoney}}</div>
      </div>
      <div :class="['order-footer__btn', {'disabled': !validPayWay}]" @click="handleSubmit">
        去支付
      </div>
    </div>
  </div>
</template>
<script>
import { mapState } from 'vuex';
import coupon from '@/containers/components/e-coupon/e-coupon.vue';
import eCourse from '@/containers/components/e-course/e-course.vue';
import Api from '@/api';
import { Toast } from 'vant';

export default {
  components: {
    eCourse,
    coupon
  },
  data () {
    return {
      course: {
        availableCoupons: [],
        courseSet: {
          cover: {}
        }
      },
      activeItemIndex: -1,
      showList: false,
      itemData: null,
      couponNumber: 0,
      preferenceCode:'',//优惠码
      targetType: this.$route.query.targetType,
      targetId: this.$route.params.id,
      targetUnit: this.$route.params.unit,
      targetNum: this.$route.params.num,
      vipOrderType: this.$route.params.type,

      detail: {},
      // WechatPay_JsH5--微信内支付 WechatPay_H5--微信wap支付
      payWay: '',
      selected: true,
      paySettings: {},
      inWechat: this.isWeixinBrowser(),
      timeoutId: -1,
    }
  },
  created () {
    if (this.vipOrderType === '升级') {
      this.targetUnit = undefined;
      this.targetNum = undefined;
    }
    this.confirmOrder();
    this.getSettings();
  },
  computed: {
    ...mapState(['wechatSwitch', 'isLoading', 'couponSwitch']),
    total() {
      const totalNumber = this.course.totalPrice;
      if (!this.itemData) {
        return totalNumber ? Number(this.course.totalPrice).toFixed(2) : '';
      }
      const minusType = (this.itemData.type === 'minus');
      const couponRate = this.itemData.rate;
      if (minusType) {
        return Math.max(totalNumber - couponRate, 0).toFixed(2);
      }
      return totalNumber ? Number(totalNumber * couponRate * 0.1).toFixed(2) : '';
    },
    couponMoney() {
      if (!this.itemData) {
        return;
      }
      const minusType = (this.itemData.type === 'discount');
      let money = this.itemData.rate;
      if (minusType) {
        money = Number(this.course.totalPrice
          - this.course.totalPrice * this.itemData.rate * 0.1).toFixed(2);
      }
      this.couponNumber = money;
      return money;
    },
    couponShow() {
      if (this.course.availableCoupons.length==0) {
        return '无可用优惠券';
      }
      if (!this.couponNumber) {
        return this.course.availableCoupons.length + '张可用';
      }
      return  parseFloat(this.itemData.rate);
    },
    getValidity() {
      return this.$route.query.expiryScope || '永久有效';
    },
    validPayWay() {
      return this.paySettings.wxpayEnabled ||
        (this.paySettings.alipayEnabled && !this.inWechat);
    }
  },
  filters: {
    filterPrice(price) {
      return parseFloat(price).toFixed(2)
    },
    couponType(type){
      if(type=='discount'){
        return '折'
      }
      return '元'
    }
  },
  watch:{
    $route(to, from) {
      this.confirmOrder()
    }
  },
  beforeRouteLeave (to, from, next) {
    clearTimeout(this.timeoutId);
    next();
  },
  methods: {
    handleSubmit () {
      if (this.total == 0) {
        // if(this.detail.sn){
        //   this.handlePay();
        //   return;
        // }
        this.createOrder('free');
      }else{
        if (!this.validPayWay){
          Toast.fail('无可用支付方式')
          return;
        }
        //从我的订单进来已经创建订单，直接去支付
        // if(this.detail.sn){
        //   this.handlePay();
        //   return;
        // }
        this.createOrder('pay');
      }
    },
    //优惠码兑换
    usePreferenceCode(){
      const that=this;
       Api.exchangePreferential({
         query: {
          code: this.preferenceCode,
        },
          data: {
            targetType: this.targetType,
            targetId: this.targetId,
            action: 'receive',
          }
        }).then((res)=>{
          if(res.success){
            that.itemData = res.data;
            let index =that.course.availableCoupons.length||0;
            that.$set(this.course.availableCoupons,index,res.data);
            that.preferenceCode='';
            that.showList = false;
          }else{
            if(res.error){
               Toast.fail(res.error.message)
            }
          }
        }).catch((err)=>{
          console.log(err)
        })
    },
    disuse() {
      this.showList = false;
      this.activeItemIndex = -1;
      this.itemData = null;
      this.couponNumber = 0;
    },
    chooseItem(data) {
      this.activeItemIndex = data.index;
      this.itemData = data.itemData;
      this.showList = false;
    },
    //获取确认订单信息
    confirmOrder(){
       let data = {
          targetType: this.targetType,
          targetId: this.targetId,
          num: this.targetNum,
          unit: this.targetUnit
       };
       Api.confirmOrder({
        data: data
      }).then(res => {
        if (this.couponSwitch) {
          let coupons=res.availableCoupons;
          this.itemData= coupons.length>0 ? coupons[0]:null;
        }
        this.course = res;
      }).catch(err => {
        //购买后返回会造成重复下单报错
        this.$router.go(-1);
      })
    },
    //0元下单后逻辑跳转
    routerChange(){
      if (this.wechatSwitch) {
          this.$router.replace({
            path: '/pay_success',
            query: {
               targetType: this.targetType,
               targetId: this.targetId
              }
            })
          return;
      }
      if (this.targetType === 'vip') {
          this.$router.replace({
              path: `/${this.targetType}`
          }, () => {
              this.$router.go(-1)
          })
      } else {
          this.$router.replace({
            path: `/${this.targetType}/${this.targetId}`
          }, () => {
            this.$router.go(-1)
          })
      }
    },
    //获取支付方式
    async getSettings(){
      this.paySettings = await Api.getSettings({
        query: {
          type: 'payment'
        }
      }).catch(err => {
        Toast.fail(err.message)
      })
      if (this.paySettings.alipayEnabled && !this.inWechat) {
        this.payWay = 'Alipay_LegacyH5';
      } else if (this.paySettings.wxpayEnabled) {
        this.payWay = 'WechatPay_H5';
      }
    },
    //创建订单
    createOrder(payment){
      const that=this;
      Api.createOrder({
        data: {
          targetType: this.targetType,
          targetId: this.targetId,
          isOrderCreate: 1,
          couponCode: this.itemData ? this.itemData.code : '',
          unit: this.targetUnit,
          num: this.targetNum,
        }
      }).then(res => {

        if(payment=='free'){
          that.routerChange()
        }else if(payment=='pay'){
          console.log(res)
          //塞入付费信息
          this.detail = Object.assign({}, res);
          //去付钱
          that.handlePay();
        }
      }).catch(err => {
          Toast.fail(err.message)
      })
    },
    //判断是否是微信浏览器
    isWeixinBrowser (){
      return /micromessenger/.test(navigator.userAgent.toLowerCase())
    },
    // 轮询问检测微信外支付是否支付成功
    getTradeInfo(tradeSn) {
      return Api.getTrade({
        query: {
          tradesSn: tradeSn,
        }
      }).then((res) => {
        if (res.isPaid) {
          if (this.wechatSwitch) {
            this.$router.replace({
              path: '/pay_success',
              query: {
                paidUrl: window.location.origin + res.paidSuccessUrlH5
              }
            })
            return;
          }
          window.location.href = window.location.origin + res.paidSuccessUrlH5
          return;
        }
        this.timeoutId = setTimeout(() => {
          this.getTradeInfo(tradeSn);
        },2000)
      }).catch(err => {
        Toast.fail(err.message)
      })
    },
    //付费
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
      }).catch(err => {
        Toast.fail(err.message)
      })
    },
  }
}
</script>
