<template>
  <div class="order">
    <div class="goods-info">
      <e-loading v-if="isLoading"></e-loading>
      <e-course type="confirmOrder" :order="course" :course="course" v-if="Object.keys(course).length >0"></e-course>
      <div class="order-coupon">
        <van-coupon-cell
          title= "优惠券"
          :chosen-coupon="activeItemIndex"
          @click="showList = true"
        />
        </van-coupon-cell>
        <van-popup v-model="showList" position="bottom">
          <div :class="['btn-coupon-exit', {active: activeItemIndex < 0}]" @click="disuse">不使用优惠
            <i class="h5-icon h5-icon-circle"></i>
            <i class="h5-icon h5-icon-checked-circle"></i>
          </div>
          <coupon v-for="(item, index) in course.availableCoupons"
            key="index"
            :data="item"
            :index="index"
            :active="activeItemIndex"
            @chooseItem="chooseItem"
            >
          </coupon>
        </van-popup>
      </div>
      <div class="order-goods-item">
        <span>学习有效期</span>
        <span class="gray-dark">{{ this.$route.params.validity || '永久有效' }}</span>
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
        <span class="red">-￥ {{ this.itemData.rate}}</span>
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
      course: {},
      activeItemIndex: -1,
      showList: false,
      itemData: ''
    }
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading
    }),
    total: {
      get() {
        if (this.itemData) {
          return (this.itemData.rate - this.course.totalPrice) > 0
          ? 0 : Math.abs(this.itemData.rate - this.course.totalPrice);
        } else {
          return this.course.totalPrice;
        }
      },
      set() {}
    },
  },
  created () {
    Api.confirmOrder({
      data: {
        targetType: 'course',
        targetId: this.$route.params.id
      }
    }).then(res => {
      console.log('res', res)
      this.course = Object.assign({}, res)
    })
  },
  methods: {
    handleSubmit () {
      this.$router.push({
        name: 'pay',
        query: {
          id: this.$route.params.id
        }
      })
    },
    disuse() {
      this.showList = false;
      this.activeItemIndex = -1;
    },
    chooseItem(data) {
      console.log(data,22)
      this.activeItemIndex = data.index;
      this.showList = false;
      this.itemData = data.itemData;
    }
  }
}
</script>
