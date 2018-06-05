<template>
  <div class="e-course-switch-box">
    <!-- price -->
    <div class="switch-box" v-if="type === 'price'">
      <span class="switch-box__price">
        <p class="free" v-if="isFree">免费</p>
        <p class="price" v-if="!isFree">¥ {{ course.price }}</p>
      </span>
      <span class="switch-box__state">
        <p>{{ course.studentNum }}人在学</p>
      </span>
    </div>

    <!-- order -->
    <div class="switch-box" v-if="type === 'order'">
      <span class="switch-box__price">
        <p class="free" v-if="isFree">免费</p>
        <p class="price" v-if="!isFree">¥ {{ course.price }}</p>
      </span>
      <span class="switch-box__state">
        <p class="order-close" v-if="orderType === 'close'">交易关闭</p>
        <p class="order-success" v-if="orderType === 'success'">交易成功</p>
        <span class="order-pay" v-if="orderType === 'pay'">去支付</span>
      </span>
    </div>

    <!-- rank -->
    <div class="rank-box clearfix" v-if="type === 'rank'">
      <div>
        <div class="progress round-conner pull-left">
          <div class="curRate round-conner" :style="{ width: rate + '%' }"></div>
        </div>
      </div>
      <span class="pull-right">{{ this.rate }}%</span>
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
      rate: {
        type: Number,
        default: 0,
      }
    },
    data() {
      return {
        isFree: this.course.price == 0,
      };
    },
    created() {

    }
  }
</script>
