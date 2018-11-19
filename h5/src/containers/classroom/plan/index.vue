<template>
  <div>
    <e-panel :title="details.title">
      <div class="course-detail__plan-price">
        <span :class="{ isFree: Number(details.price) === 0 }">{{ details.price | filterPrice() }}</span>
        <span class="plan-price__student-num">{{ details.studentNum }}人在学</span>
      </div>
    </e-panel>
    <service v-if="details.service.length" :services="details.service" ></service>
  </div>
</template>
<script>
import service from '../service';

export default {
  props: {
    details: {},
    type: '',
  },
  components: {
    service,
  },
  filters: {
    filterPrice(price, defaultValue) {
      const isFree = Number(price) === 0;
      return isFree ? '免费' : `¥${price}`;
    }
  },
}
</script>
