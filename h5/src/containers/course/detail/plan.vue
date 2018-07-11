<template>
  <div>
    <e-panel :title="details.courseSet.title">
      <div class="course-detail__plan-price">
        <span>¥{{ details.price }}</span>
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

export default {
  data() {
    return {
      items: []
    }
  },
  created () {
    this.items = this.details.courses.map((item, index) => {
      this.$set(item, 'active', false);

      if(this.details.id === item.id) {
        item.active = true;
      }
      return item;
    });
    console.log('items', this.items)
  },
  computed: {
    ...mapState('course', {
      details: state => state.details,

    })
  },
  methods: {
    ...mapActions('course', [
      'getCourseDetail'
    ]),
    handleClick(item, index){
      this.items.map(item => item.active = false);
      item.active = true;

      this.getCourseDetail({
        courseId: item.id
      })
    }
  }
}
</script>
