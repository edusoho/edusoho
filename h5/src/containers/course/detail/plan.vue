<template>
  <div>
    <e-panel :title="details.courseSet.title">
      <div class="course-detail__plan-price">
        <span :class="{isFree: isFree}">{{ filterPrice() }}</span>
        <span>{{ details.courseSet.studentNum }}人在学</span>
      </div>
    </e-panel>

    <ul class="course-detail__plan">
      <li v-for="(item, index) in items"
        @click="handleClick(item, index)"
        :class="{ active: item.active }">{{item.title}}</li>
    </ul>

    <div class="course-detail__validity">
      <div>学习有效期<span class="validity dark">永久有效</span></div>
      <div v-if="details.buyExpiryTime != 0" class="mt5">购买截止日期<span class="validity orange">{{ details.buyExpiryTime }}</span></div>
    </div>
  </div>
</template>
<script>
import * as types from '@/store/mutation-types';
import { mapMutations, mapState, mapActions } from 'vuex';
import tryVue from '../try.vue';

export default {
  data() {
    return {
      items: [],
      isFree: false
    }
  },
  watch: {
    selectedPlanId: {
      immediate: true,
      handler(v) {
        this.items = this.details.courses.map((item, index) => {
          this.$set(item, 'active', false);

          if(item.id === this.details.id) {
            item.active = true;
          }

          return item;
        });
      }
    }
  },
  computed: {
    ...mapState('course', {
      details: state => state.details,
      selectedPlanId: state => state.selectedPlanId
    })
  },
  methods: {
    ...mapActions ('course', [
      'getCourseDetail'
    ]),
    handleClick (item, index){
      this.items.map(item => item.active = false);
      item.active = true;

      this.getCourseDetail({
        courseId: item.id
      })
    },
    filterPrice () {
      const details = this.details;

      if (Number(details.isFree) || details.price === '0.00') {
        this.isFree = true;
        return '免费';
      }

      this.isFree = false
      return `¥${details.price}`;
    }
  }
}
</script>
