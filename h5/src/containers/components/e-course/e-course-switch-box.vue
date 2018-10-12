<template>
  <div class="e-course-switch-box">
    <!-- price -->
    <div class="switch-box" v-if="type === 'price'">
      <span class="switch-box__price">
        <p class="free" v-if="isFree">免费</p>
        <p class="price" v-if="!isFree">¥ {{ course.price }}</p>
      </span>
      <span class="switch-box__state">
        <p v-if="showStudent">{{ course.studentNum }}人在学</p>
      </span>
    </div>

    <!-- order -->
    <div class="switch-box" v-if="type === 'order'">
      <span class="switch-box__price">
        <p class="free" v-if="isFree">免费</p>
        <p class="price" v-if="!isFree">¥ {{ order.pay_amount/100 }}</p>
      </span>
      <span class="switch-box__state">
        <p :class="order.status"
          v-if="order.status !== 'created'">
          {{ order.status | filterOrderStatus}}
        </p>
        <span class="order-pay"
          v-if="order.status === 'created'"
          @click="goToPay"
          >{{ order.status | filterOrderStatus}}</span>
      </span>
    </div>

     <!-- confirm order -->
    <div class="switch-box" v-if="type === 'confirmOrder'">
      <span class="switch-box__price">
        <p class="price">¥ {{ order.totalPrice}}</p>
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
  import { mapState } from 'vuex';

  export default {
    props: {
      type: {
        type: String,
        default: 'price',
      },
      course: {
        type: Object,
        default: {},
      },
      order: {
        type: Object,
        default: {},
      },
    },
    data() {
      return {
        isFree: this.course.price == 0
      };
    },
    computed: {
      ...mapState(['courseSettings']),
      rate() {
        if (!parseInt(this.course.publishedTaskNum)) return 0;
        return parseInt(this.course.progress.percent)
      },
      showStudent() {
        return this.courseSettings ? Number(this.courseSettings.show_student_num_enabled) : true;
      },
    },
    methods: {
      goToPay() {
        this.$router.push({
          path: '/pay',
          query: {
            id: this.order.id,
            source: 'order',
            sn: this.order.sn,
            targetId: this.order.targetId
          }
        });
      }
    }
  }
</script>
