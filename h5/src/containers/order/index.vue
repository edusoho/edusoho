<template>
  <div class="order">
    <div class="goods-info">
      <e-loading v-if="isLoading"></e-loading>
      <e-course type="confirmOrder" :order="course" :course="course" v-if="Object.keys(course).length >0"></e-course>
      <div class="order-coupon">
        <div class="coupon-column"
          :chosen-coupon="activeItemIndex"
          @click="showList = true"
          >
          <span>优惠券</span>
          <span class="red">{{ couponShow }}</span>
        </div>
        <van-popup v-model="showList" position="bottom" :overlay="false">
          <van-nav-bar title="优惠券"
            class="nav-bar"
            :left-arrow="true"
            @click-left="disuse"/>
          <div :class="['btn-coupon-exit', {active: activeItemIndex < 0}]" @click="disuse">不使用优惠
            <i class="h5-icon h5-icon-circle"></i>
            <i class="h5-icon h5-icon-checked-circle"></i>
          </div>
          <coupon v-for="(item, index) in course.availableCoupons"
            :key="index"
            :data="item"
            :index="index"
            :active="activeItemIndex"
            @chooseItem="chooseItem"
            >
          </coupon>
          <div class="coupon-empty" v-show="!course.availableCoupons.length">
            <img class="empty-img" src='static/images/coupon_empty.png'>
            <div class="empty-text">暂无优惠券</div>
          </div>
        </van-popup>
      </div>
      <div class="order-goods-item">
        <span>学习有效期</span>
        <span class="gray-dark">{{ getValidity }}</span>
      </div>
    </div>
    <div class="order-accounts" v-show="itemData">
      <div class="mb20 title-18">结算</div>
      <div class="flex-between-item">
        <span class="mbl">商品价格：</span>
        <span class="red">￥ {{ course.totalPrice }}</span>
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
import coupon from './coupon.vue';
import eCourse from '@/containers/components/e-course/e-course.vue';
import Api from '@/api';

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
      couponNumber: 0
    }
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading
    }),
    total() {
      if (!this.itemData) {
        return this.course.totalPrice;
      }
      const minusType = (this.itemData.type === 'minus');
      const couponRate = this.itemData.rate;
      const totalNumber = this.course.totalPrice;
      if (minusType) {
        return Math.max(totalNumber - couponRate, 0).toFixed(2);
      }
      return Number(totalNumber * couponRate * 0.1).toFixed(2);
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
      return this.$route.query.expiry || '永久有效';
    }
  },
  created () {
    Api.confirmOrder({
      data: {
        targetType: 'course',
        targetId: this.$route.params.id
      }
    }).then(res => {
      this.course = res
    })
  },
  methods: {
    handleSubmit () {
      const courseId = this.$route.params.id;
      if (this.total == 0) {
        Api.createOrder({
          data: {
            targetType: 'course',
            targetId: courseId,
            isOrderCreate: 1,
            couponCode: this.itemData ? this.itemData.code : '',
          }
        }).then(() => {
          this.$router.push({
            path: `/course/${courseId}`
          })
        })
        return;
      }
      this.$router.push({
        name: 'pay',
        query: {
          id: courseId,
        },
        params: {
          couponCode: this.itemData ? this.itemData.code : ''
        }
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
