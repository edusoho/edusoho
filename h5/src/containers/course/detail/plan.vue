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
