<template>
  <div class="e-course-switch-box">
    <!-- price -->
    <div v-if="type === 'price'" class="switch-box">
      <span class="switch-box__price">
        <p v-if="isFree" class="free">{{ $t('e.free') }}</p>
        <p v-if="!isFree" class="price">¥ {{ course.price }}</p>
      </span>
      <span class="switch-box__state">
        <p v-if="showStudent">{{ $t('e.personStudying', { number: course.studentNum }) }}</p>
      </span>
    </div>

    <!-- order -->
    <div v-if="type === 'order'" class="switch-box">
      <span class="switch-box__price">
        <p v-if="isFree" class="free">{{ $t('e.free') }}</p>
        <p v-if="!isFree" class="price">
          {{ displayPrice(order.priceConvert) }}
        </p>
      </span>
      <span class="switch-box__state">
        <p
          v-if="order.status !== 'created' && order.status !== 'paying'"
          :class="order.status"
        >
          {{ order.status | filterOrderStatus }}
        </p>
        <span
          v-if="order.status === 'created' || order.status === 'paying'"
          class="order-pay"
          @click="goToPay"
          >{{ order.status | filterOrderStatus }}</span
        >
      </span>
    </div>

    <!-- confirm order -->
    <div v-if="type === 'confirmOrder'" class="switch-box">
      <span class="switch-box__price">
        <p class="price">{{ displayPrice(order) }}</p>
      </span>
    </div>

    <!-- rank -->
    <div v-if="type === 'rank'" class="rank-box">
      <div class="progress round-conner">
        <div :style="{ width: rate + '%' }" class="curRate round-conner" />
      </div>
      <span>{{ this.rate }}%</span>
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
      default() {
        return {};
      },
    },
    order: {
      type: Object,
      default() {
        return {};
      },
    },
  },
  data() {
    return {
      isFree: this.course.price == 0,
    };
  },
  computed: {
    ...mapState(['courseSettings']),
    rate() {
      if (!parseInt(this.course.publishedTaskNum)) return 0;
      return parseInt(this.course.progress.percent);
    },
    showStudent() {
      return this.courseSettings
        ? Number(this.courseSettings.show_student_num_enabled)
        : true;
    },
  },
  filters: {
    numFilter(value) {
      return value ? parseFloat(value).toFixed(2) : '';
    },
  },
  methods: {
    goToPay() {
      this.$router.replace({
        path: '/pay',
        query: {
          id: this.order.id,
          source: 'order',
          sn: this.order.sn,
          targetId: this.order.targetId,
          targetType: this.order.targetType,
        },
      });
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
    },
    displayPrice(priceConvert) {
      let price;
      const type = this.type;

      if (type === 'order') {
        const { currency, coinAmount, coinName, amount } = priceConvert;
        if (currency === 'coin') {
          price = coinAmount ? (coinAmount / 100).toFixed(2) : '';
          price = `${price} ${coinName}`;
        } else if (currency === 'RMB') {
          price = amount ? (amount / 100).toFixed(2) : '';
          price = `¥ ${price}`;
        }
        return price;
      }

      if (type === 'confirmOrder') {
        const { priceType, coinPayAmount, coinName, totalPrice } = priceConvert;
        if (priceType === 'Coin') {
          price = coinPayAmount ? parseFloat(coinPayAmount).toFixed(2) : '';
          price = `${price} ${coinName}`;
        } else if (priceType === 'RMB') {
          price = `¥ ${totalPrice}`;
        }
        return price;
      }
    },
  },
};
</script>
