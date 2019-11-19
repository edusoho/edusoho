<template>
  <div class="e-course-switch-box">
    <!-- price -->
    <div v-if="type === 'price'" class="switch-box">
      <span class="switch-box__price">
        <p v-if="isFree" class="free">免费</p>
        <p v-if="!isFree" class="price">¥ {{ course.price }}</p>
      </span>
      <span class="switch-box__state">
        <p v-if="showStudent">{{ course.studentNum }}人在学</p>
      </span>
    </div>

    <!-- order -->
    <div v-if="type === 'order'" class="switch-box">
      <span class="switch-box__price">
        <p v-if="isFree" class="free">免费</p>
        <p v-if="!isFree" class="price">¥ {{ order.pay_amount/100 }}</p>
      </span>
      <span class="switch-box__state">
        <p
          v-if="order.status !== 'created' && order.status !== 'paying'"
          :class="order.status">
          {{ order.status | filterOrderStatus }}
        </p>
        <span
          v-if="order.status === 'created' || order.status === 'paying'"
          class="order-pay"
          @click="goToPay"
        >{{ order.status | filterOrderStatus }}</span>
      </span>
    </div>

    <!-- confirm order -->
    <div v-if="type === 'confirmOrder'" class="switch-box">
      <span class="switch-box__price">
        <p class="price">¥ {{ order.totalPrice | numFilter }}</p>
      </span>
    </div>

    <!-- rank -->
    <div v-if="type === 'rank'" class="rank-box">
      <div class="progress round-conner">
        <div :style="{ width: rate + '%' }" class="curRate round-conner"/>
      </div>
      <span>{{ this.rate }}%</span>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  props: {
    type: {
      type: String,
      default: 'price'
    },
    course: {
      type: Object,
      default: {}
    },
    order: {
      type: Object,
      default: {}
    }
  },
  data() {
    return {
      isFree: this.course.price == 0
    }
  },
  computed: {
    ...mapState(['courseSettings']),
    rate() {
      if (!parseInt(this.course.publishedTaskNum)) return 0
      return parseInt(this.course.progress.percent)
    },
    showStudent() {
      return this.courseSettings ? Number(this.courseSettings.show_student_num_enabled) : true
    }
  },
  filters: {
    numFilter(value) {
      return value ? parseFloat(value).toFixed(2) : ''
    }
  },
  methods: {
    goToPay() {
      this.$router.push({
        path: '/pay',
        query: {
          id: this.order.id,
          source: 'order',
          sn: this.order.sn,
          targetId: this.order.targetId,
          targetType: this.order.targetType
        }
      })
      //  this.$router.push({
      //   name: 'order',
      //   params: {
      //     id: this.order.targetId,
      //   },
      //   query: {
      //     orderId: this.order.id,
      //     source: 'order',
      //     sn: this.order.sn,
      //     targetId: this.order.targetId,
      //     targetType: this.order.targetType
      //   }
      // });
    }
  }
}
</script>
