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
          <span class="red">{{ couponShow }}<i class="iconfont icon-arrow-right"></i></span>
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
    <!-- 结算区域 -->
    <div class="order-accounts" v-show="itemData">
      <div class="mb20 title-18">结算</div>
      <div class="flex-between-item">
        <span class="mbl">商品价格：</span>
        <span class="red">￥ {{ course.totalPrice | filterPrice }}</span>
      </div>
      <div class="flex-between-item">
        <span class="mbl">优惠券：</span>
        <span class="red">-￥ {{ couponMoney }}</span>
      </div>
      <div class="flex-between-item">
        <span class="mbl">应付：</span>
        <span class="red">￥ {{ total }}</span>
      </div>
    </div>
    <van-button class="order-submit-bar submit-btn"
      @click="handleSubmit"
      size="small">应付￥ {{ total }}</van-button>
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
      vipOrderType: this.$route.params.type
    }
  },
  computed: {
    ...mapState(['wechatSwitch', 'isLoading']),
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
      if (!this.couponNumber) {
        return this.course.availableCoupons.length + '张可用';
      }
      return '-￥' + this.couponNumber;
    },
    getValidity() {
      return this.$route.query.expiryScope || '永久有效';
    }
  },
  filters: {
    filterPrice(price) {
      return parseFloat(price).toFixed(2)
    },
  },
  created () {
    if (this.vipOrderType === '升级') {
      this.targetUnit = undefined;
      this.targetNum = undefined;
    }

    let data = {
      targetType: this.targetType,
      targetId: this.targetId,
      num: this.targetNum,
      unit: this.targetUnit
    }

    Api.confirmOrder({
      data: data
    }).then(res => {
      this.course = res;
    }).catch(err => {
      //购买后返回会造成重复下单报错
      this.$router.go(-1);
    })
  },
  methods: {
    handleSubmit () {
      if (this.total == 0) {
        Api.createOrder({
          data: {
            targetType: this.targetType,
            targetId: this.targetId,
            isOrderCreate: 1,
            couponCode: this.itemData ? this.itemData.code : '',
            unit: this.targetUnit,
            num: this.targetNum,
          }
        }).then(() => {
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
        })
        return;
      }
      this.$router.push({
        name: 'pay',
        query: {
          id: this.targetId,
          targetType: this.targetType,
        },
        params: {
          couponCode: this.itemData ? this.itemData.code : '',
          unit: this.targetUnit,
          num: this.targetNum,
        }
      })
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
    }
  }
}
</script>
