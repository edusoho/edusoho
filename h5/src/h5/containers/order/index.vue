<template>
  <div class="order">
    <div class="goods-info">
      <e-loading v-if="isLoading" />
      <!-- 商品缩略图 -->
      <e-course
        v-if="Object.keys(course).length > 0"
        :type-list="targetType"
        :duration="course.duration"
        :order="course"
        :course="course"
        type="confirmOrder"
      />
      <!-- 使用优惠券 -->
      <div v-show="couponSwitch" class="order-coupon">
        <div class="coupon-column" @click="showList = true">
          <span>{{ $t('order.coupon') }}</span>
          <span :class="['red', itemData ? 'coupon-money' : '']"
            >{{ couponShow
            }}<span v-if="itemData" class="coupon-type">{{
              itemData.type | couponType
            }}</span>
            <i class="iconfont icon-arrow-right" />
          </span>
        </div>
        <van-popup
          v-model="showList"
          :overlay="false"
          class="e-popup full-height-popup coupon-popup"
          position="bottom"
        >
          <van-nav-bar
            :left-arrow="true"
            :title="$t('order.coupon')"
            class="nav-bar"
            @click-left="disuse"
          />
          <div
            :class="['btn-coupon-exit', { active: activeItemIndex < 0 }]"
            @click="disuse"
          >
            {{ $t('order.doNotUseDiscount') }} <i class="iconfont icon-About" />
          </div>
          <div class="e-popup__content coupon-popup__content">
            <div class="coupon-number-change">
              <van-field
                v-model="preferenceCode"
                center
                border
                clearable
                :placeholder="$t('order.couponCode')"
              >
                <van-button
                  slot="button"
                  :disabled="!preferenceCode"
                  size="small"
                  type="primary"
                  @click="usePreferenceCode"
                  >{{ $t('order.use') }}</van-button
                >
              </van-field>
            </div>
            <coupon
              v-for="(item, index) in course.availableCoupons"
              :key="index"
              :coupon="item"
              :index="index"
              :active="activeItemIndex"
              :show-button="false"
              :show-selecet="true"
              @chooseItem="chooseItem"
            />
            <div v-show="!course.availableCoupons.length" class="coupon-empty">
              <img class="empty-img" src="static/images/coupon_empty.png" />
              <div class="empty-text">{{ $t('order.noCoupons') }}</div>
            </div>
          </div>
        </van-popup>
      </div>
      <div v-if="targetType !== 'vip'" class="order-goods-item">
        <span>{{ $t('order.validity') }}</span>
        <span class="gray-dark" v-html="getValidity" />
      </div>
    </div>
    <!-- 报名信息填写 -->
    <div
      class="personal-info"
      v-if="showCollectEntry"
      @click="showUserInfoCollectForm"
    >
      {{ userInfoCollectForm.formTitle }}
      <i class="iconfont icon-arrow-right"></i>
    </div>
    <!-- 个人信息表单填写 -->
    <van-popup
      v-model="isShowForm"
      :overlay="false"
      class="e-popup full-height-popup coupon-popup mt0"
      position="bottom"
    >
      <van-nav-bar
        :left-arrow="true"
        :title="this.userInfoCollectForm.formTitle"
        class="nav-bar"
        @click-left="hideUserInfoCollectForm"
      />
      <info-collection
        :userInfoCollectForm="this.userInfoCollectForm"
        :formRule="this.userInfoCollectForm.items"
        @submitForm="submitForm"
      ></info-collection>
    </van-popup>

    <div class="payPage">
      <e-loading v-if="isLoading" />
      <div class="payPage__order">
        <div class="order__head">
          {{ $t('order.paymentWay') }}
        </div>
        <div class="order__infomation">
          <!-- <div class="title">{{ detail.title }}</div>
          <div class="sum">
            <span>待支付</span>
            <span class="sum__price">¥ <span class="num">{{ detail.pay_amount | toMoney }}</span></span>
          </div> -->
          <div class="payWay">
            <div
              v-show="paySettings.alipayEnabled && !inWechat"
              :class="[
                'payWay__item',
                { 'payWay__item--selected': payWay === 'Alipay_LegacyH5' },
              ]"
              @click="
                payWay = 'Alipay_LegacyH5';
                selected = true;
              "
            >
              <img class="correct" src="static/images/correct.png" />
              <div class="right" />
              <img class="payWay__img" src="static/images/zfb.png" />
            </div>
            <div
              v-show="paySettings.wxpayEnabled"
              :class="[
                'payWay__item',
                { 'payWay__item--selected': payWay === 'WechatPay_H5' },
              ]"
              @click="
                payWay = 'WechatPay_H5';
                selected = false;
              "
            >
              <img class="correct" src="static/images/correct.png" />
              <div class="right" />
              <img class="payWay__img" src="static/images/wx.png" />
            </div>
          </div>
        </div>
        <div class="order__agreement" v-if="purchaseAgreement.enabled == 1">
          <van-checkbox
            :value="isAgree"
            icon-size="16"
            shape="square"
            @click="handleClickAgree"
          >
            我已阅读并同意<span class="order__agreement__btn" @click.stop="handleClickViewAgreement">《用户购买协议》</span>
          </van-checkbox>
        </div>
      </div>
    </div>
    <div class="order-footer">
      <div class="order-footer__text">
        <div>
          {{ $t('order.pay') }}：<span class="price">{{ total }}</span>
        </div>
        <div v-show="itemData" class="discount">{{ $t('order.discounted') }}{{ couponMoney }}</div>
      </div>
      <div
        :class="['order-footer__btn', { disabled: !validPayWay }]"
        @click="shouldCollectUserInfo"
      >
        {{ $t('order.pay2') }}
      </div>
    </div>

    <van-dialog
      v-if="purchaseAgreement.enabled == 1"
      class="purchase-agreement"
      v-model="showPurchaseAgreement"
      :show-confirm-button="false"
    >
      <h2 class="purchase-agreement__title">《{{ purchaseAgreement.title }}》</h2>
      <van-icon
        v-if="purchaseAgreement.open != 1"
        class="purchase-agreement__close"
        name="cross"
        @click="showPurchaseAgreement = false"
      />
      <div
        class="purchase-agreement__content"
        :class="{ 'purchase-agreement__content--btn': purchaseAgreement.open == 1 }"
        v-html="purchaseAgreement.content"
      />
      <van-button
        v-if="purchaseAgreement.open == 1"
        class="purchase-agreement__btn"
        type="primary"
        block
        @click="handleClickAgreeContinue"
      >同意并继续</van-button>
    </van-dialog>
  </div>
</template>
<script>
import { mapState } from 'vuex';
import coupon from '&/components/e-coupon/e-coupon.vue';
import eCourse from '&/components/e-course/e-course.vue';
import Api from '@/api';
import { Toast } from 'vant';
import collectUserInfoMixins from '@/mixins/collectUserInfo/index.js';
import infoCollection from '@/components/info-collection.vue';
export default {
  components: {
    eCourse,
    coupon,
    infoCollection,
  },
  mixins: [collectUserInfoMixins],
  data() {
    return {
      course: {
        availableCoupons: [],
        courseSet: {
          cover: {},
        },
      },
      activeItemIndex: -1,
      showList: false,
      itemData: null,
      couponNumber: 0,
      preferenceCode: '', // 优惠码
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
      isShowForm: false,
      hasCollectUserInfo: false,
      isAgree: false,
      purchaseAgreement: {},
      showPurchaseAgreement: false
    };
  },
  created() {
    if (this.vipOrderType === '升级') {
      this.targetUnit = undefined;
      this.targetNum = undefined;
    }
    this.fetchPurchaseAgreement();
    this.confirmOrder();
    this.getSettings();
  },
  computed: {
    ...mapState(['wechatSwitch', 'isLoading', 'couponSwitch']),
    total() {
      let price;
      const { priceType, coinName, totalPrice } = this.course;

      if (!this.itemData) {
        price = totalPrice ? Number(totalPrice).toFixed(2) : '';
      } else {
        const { type, rate } = this.itemData;

        // 抵价 (minus) or 打折 (discount)
        if (type === 'minus') {
          price = Math.max(totalPrice - rate, 0).toFixed(2);
        } else {
          price = totalPrice ? Number(totalPrice * rate * 0.1).toFixed(2) : '';
        }
      }

      if (priceType === 'Coin') {
        price = `${price} ${coinName}`;
      } else if (priceType === 'RMB') {
        price = `¥ ${price}`;
      }
      return price;
    },
    couponMoney() {
      if (!this.itemData) {
        return;
      }
      const minusType = this.itemData.type === 'discount';
      let money = this.itemData.rate;
      if (minusType) {
        money = Number(
          this.course.totalPrice -
            this.course.totalPrice * this.itemData.rate * 0.1,
        ).toFixed(2);
      }
      // eslint-disable-next-line vue/no-side-effects-in-computed-properties
      this.couponNumber = money;
      return money;
    },
    couponShow() {
      if (this.course.availableCoupons.length == 0) {
        return this.$t('order.noCouponsAvailable');
      }
      if (!this.couponNumber) {
        return this.course.availableCoupons.length + this.$t('order.available');
      }
      return parseFloat(this.itemData.rate);
    },
    getValidity() {
      return this.$route.query.expiryScope || this.$t('order.longTermEffective');
    },
    validPayWay() {
      if (this.IsCollectUserInfoType && !this.isReqUserInfoCollect) {
        return false;
      }
      if (this.needCollectUserInfo && !this.isRequserInfoCollectForm) {
        return false;
      }
      return (
        this.paySettings.wxpayEnabled ||
        (this.paySettings.alipayEnabled &&
          !this.inWechat &&
          this.userInfoCollect)
      );
    },
    IsCollectUserInfoType() {
      return this.targetType === 'course' || this.targetType === 'classroom';
    },
    showCollectEntry() {
      return Object.keys(this.userInfoCollectForm).length > 0;
    },
  },
  filters: {
    filterPrice(price) {
      return parseFloat(price).toFixed(2);
    },
    couponType(type) {
      if (type == 'discount') {
        return '折';
      }
      return '元';
    },
  },
  watch: {
    // $route(to, from) {
    //   this.confirmOrder();
    // },
  },
  beforeRouteLeave(to, from, next) {
    clearTimeout(this.timeoutId);
    next();
  },
  methods: {
    shouldCollectUserInfo() {
      if (!this.isAgree) {
        Toast('请勾选');
        return;
      }
      if (
        this.IsCollectUserInfoType &&
        !this.hasCollectUserInfo &&
        this.hasUserInfoCollectForm
      ) {
        Toast('请先提交信息后再提交订单');
        // if (this.hasUserInfoCollectForm) {
        //   Toast('请先提交信息后再提交订单');
        // } else {
        //   this.handleSubmit();
        // }
        return;
      }
      this.handleSubmit();
    },
    showUserInfoCollectForm() {
      this.isShowForm = true;
    },
    hideUserInfoCollectForm() {
      this.isShowForm = false;
    },
    getInfoCollection() {
      Toast.loading({
        duration: 0,
        message: '加载中...',
        forbidClick: true,
      });
      const paramsList = {
        action: 'buy_before',
        targetType: this.targetType,
        targetId: this.targetId,
      };
      this.getInfoCollectionEvent(paramsList).then(res => {
        if (Object.keys(res).length) {
          this.needCollectUserInfo = true;
          this.getInfoCollectionForm(res.id).then(() => {
            Toast.clear();
          });
          return;
        }
        Toast.clear();
      });
    },
    submitForm() {
      this.hideUserInfoCollectForm();
      this.hasCollectUserInfo = true;
    },
    handleSubmit() {
      if (this.total == 0) {
        this.createOrder('free');
      } else {
        if (!this.validPayWay) {
          Toast.fail('无可用支付方式');
          return;
        }
        this.createOrder('pay');
      }
    },
    // 优惠码兑换
    usePreferenceCode() {
      const that = this;
      Api.exchangePreferential({
        query: {
          code: this.preferenceCode,
        },
        data: {
          targetType: this.targetType,
          targetId: this.targetId,
          action: 'receive',
        },
      })
        .then(res => {
          if (res.success) {
            that.itemData = res.data;
            const index = that.course.availableCoupons.length || 0;
            that.$set(this.course.availableCoupons, index, res.data);
            that.preferenceCode = '';
            that.showList = false;
          } else {
            if (res.error) {
              Toast.fail(res.error.message);
            }
          }
        })
        .catch(err => {
          console.log(err);
        });
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
    // 获取确认订单信息
    confirmOrder() {
      const data = {
        targetType: this.targetType,
        targetId: this.targetId,
        num: this.targetNum,
        unit: this.targetUnit,
        platform: 'h5',
        platform_version: this.$version,
      };
      Api.confirmOrder({
        data: data,
      })
        .then(res => {
          this.course = res;
          if (this.couponSwitch) {
            const coupons = res.availableCoupons;
            this.itemData = coupons.length > 0 ? coupons[0] : null;
          }
          if (this.IsCollectUserInfoType) {
            this.getInfoCollection();
          }
        })
        .catch(err => {
          this.$toast(err.message);
          setTimeout(() => {
            // 购买后返回会造成重复下单报错
            this.$router.go(-1);
          }, 2000);
        });
    },
    // 0元下单后逻辑跳转
    routerChange() {
      if (this.wechatSwitch) {
        this.$router.replace({
          path: '/pay_success',
          query: {
            targetType: this.targetType,
            targetId: this.targetId,
          },
        });
        return;
      }
      if (this.targetType === 'vip') {
        this.$router.replace(
          {
            path: `/${this.targetType}`,
          },
          () => {
            this.$router.go(-1);
          },
        );
      } else {
        this.$router.replace(
          {
            path: `/${this.targetType}/${this.targetId}`,
          },
          () => {
            this.$router.go(-1);
          },
        );
      }
    },
    // 获取支付方式
    async getSettings() {
      this.paySettings = await Api.getSettings({
        query: {
          type: 'payment',
        },
      }).catch(err => {
        Toast.fail(err.message);
      });
      if (this.paySettings.alipayEnabled && !this.inWechat) {
        this.payWay = 'Alipay_LegacyH5';
      } else if (this.paySettings.wxpayEnabled) {
        this.payWay = 'WechatPay_H5';
      }
    },
    // 创建订单
    createOrder(payment) {
      const that = this;
      Api.createOrder({
        data: {
          targetType: this.targetType,
          targetId: this.targetId,
          isOrderCreate: 1,
          couponCode: this.itemData ? this.itemData.code : '',
          unit: this.targetUnit,
          num: this.targetNum,
          platform: 'h5',
          platform_version: this.$version,
        },
      })
        .then(res => {
          if (payment == 'free') {
            that.routerChange();
          } else if (payment == 'pay') {
            // 塞入付费信息
            this.detail = Object.assign({}, res);
            // 去付钱
            that.handlePay();
          }
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    // 判断是否是微信浏览器
    isWeixinBrowser() {
      return /micromessenger/.test(navigator.userAgent.toLowerCase());
    },
    // 轮询问检测微信外支付是否支付成功
    getTradeInfo(tradeSn) {
      return Api.getTrade({
        query: {
          tradesSn: tradeSn,
        },
      })
        .then(res => {
          if (res.isPaid) {
            // if (this.wechatSwitch) {
            //   this.$router.replace({
            //     path: '/pay_success',
            //     query: {
            //       paidUrl: window.location.origin + res.paidSuccessUrlH5,
            //     },
            //   });
            //   return;
            // }
            window.location.href = res.paidSuccessUrlH5;
            return;
          }
          this.timeoutId = setTimeout(() => {
            this.getTradeInfo(tradeSn);
          }, 2000);
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    // 付费
    handlePay() {
      if (!this.validPayWay) return;

      const isWxPay = this.payWay === 'WechatPay_H5' && this.inWechat;
      if (isWxPay) {
        window.location.href =
          `${window.location.origin}/pay/center/wxpay_h5?pay_amount=` +
          `${this.detail.pay_amount}&title=${this.detail.title}&sn=${this.detail.sn}&targetType=${this.targetType}&targetId=${this.targetId}&payWay=${this.payWay}`;
        return;
      }

      const returnUrl =
        window.location.origin +
        window.location.pathname +
        `#/pay_center?targetType=${this.targetType}&targetId=${this.targetId}&payWay=${this.payWay}`;

      Api.createTrade({
        data: {
          gateway: this.payWay,
          type: 'purchase',
          orderSn: this.detail.sn,
          app_pay: 'Y',
          success_url: returnUrl,
        },
      })
        .then(res => {
          if (this.payWay === 'WechatPay_H5') {
            this.getTradeInfo(res.tradeSn).then(() => {
              window.location.href = res.mwebUrl;
            });
            return;
          }
          window.location.href = res.payUrl;
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },

    fetchPurchaseAgreement() {
      Api.getPurchaseAgreement().then(res => {
        this.purchaseAgreement = res;
        if (res.open == 1) {
          this.showPurchaseAgreement = true;
        }
      });
    },

    handleClickAgree() {
      if (this.isAgree) {
        this.isAgree = false;
        return;
      }

      if (this.purchaseAgreement.open == 0) {
        this.isAgree = !this.isAgree;
      } else {
        this.showPurchaseAgreement = true;
      }
    },

    handleClickViewAgreement() {
      this.showPurchaseAgreement = true;
    },

    handleClickAgreeContinue() {
      this.isAgree = true;
      this.showPurchaseAgreement = false;
    }
  },
};
</script>

<style lang="scss" scoped>

.purchase-agreement {
  top: 50%;
  height: 90%;


  /deep/ .van-dialog__content {
    height: 100%;
  }

  &__title {
    margin: vw(10) vw(26);
    text-align: center;
    font-size: vw(16);
    font-weight: 500;
    color: #333;
    line-height: vw(24);
  }

  &__close {
    position: absolute;
    padding: 6px;
    top: 6px;
    right: 6px;
    font-size: 20px;
  }

  &__content {
    overflow-y: scroll;
    padding: 0 vw(16) vw(16);
    height: calc(100% - 44px);
    word-wrap: break-word;
    box-sizing: border-box;

    &--btn {
      height: calc(100% - 86px);
    }
  }

  &__btn {
    position: absolute;
    bottom: 0;
    left: 0;
  }
}
</style>
