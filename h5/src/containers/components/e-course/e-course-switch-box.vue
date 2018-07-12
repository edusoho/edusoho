<template>
  <div class="e-course-switch-box">
    <!-- price -->
    <div class="switch-box" v-if="type === 'price'">
      <span class="switch-box__price">
        <p class="free" v-if="isFree">免费</p>
        <p class="price" v-if="!isFree">¥ {{ course.price }}</p>
      </span>
      <span class="switch-box__state">
        <p>{{ course.courseSet.studentNum }}人在学</p>
      </span>
    </div>

    <!-- order -->
    <div class="switch-box" v-if="type === 'order'">
      <span class="switch-box__price">
        <p class="free" v-if="isFree">免费</p>
        <p class="price" v-if="!isFree">¥ {{ course.pay_amount | toMoney}}</p>
      </span>
      <span class="switch-box__state">
        <p class="order-close" v-if="orderType === 'close'">交易关闭</p>
        <p class="order-success" v-if="orderType === 'success'">交易成功</p>
        <span class="order-pay" v-if="orderType === 'pay'">去支付</span>
      </span>
    </div>

     <!-- confirm order -->
    <div class="switch-box" v-if="type === 'confirmOrder'">
      <span class="switch-box__price">
        <p class="price">¥ {{ course.totalPrice | toMoney}}</p>
      </span>
    </div>

    <!-- rank -->
    <div class="rank-box" v-if="type === 'rank'">
      <div class="progress round-conner">
        <div class="curRate round-conner" :style="{ width: rate + '%' }"></div>
      </div>
      <span class="">{{ this.rate }}%</span>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      type: {
        type: String,
        default: 'price',
      },
      orderType: {
        type: String,
        default: 'pay',
      },
      course: {
        type: Object,
        default: {},
      },
    },
    data() {
      return {
        isFree: this.course.price == 0,
      };
    },
    computed: {
      rate() {
        if (this.course.publishedTaskNum) return 0;
        return (this.course.learnedNum/this.course.publishedTaskNum)*100
      }
    },
    created() {

    }
  }
</script>
