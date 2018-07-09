<template>
  <div>
    <e-panel :title="courseSet.title">
      <div class="course-detail__plan-price">
        <span>¥{{ price }}</span>
        <span>{{ courseSet.studentNum }}人在学</span>
      </div>
    </e-panel>

    <ul class="course-detail__plan">
      <li v-for="(item, index) in items" 
        @click="handleClick(item, index)" 
        :class="{ active: item.active }">三月班</li>
    </ul>
  </div>
</template>
<script>
import * as types from '@/store/mutation-types';
import { mapMutations } from 'vuex';

export default {
  props: ['courseSet', 'price'],
  data() {
    return {
      items: [{
        name: '三月班',
        active: true,
      },{
        name: '三月班',
        active: false,
      },{
        name: '三月班',
        active: false,
      },{
        name: '三月班',
        active: false,
      }]
    }
  },
  methods: {
    ...mapMutations('course',{
      setPlanIndex: types.SET_PLAN_INDEX
    }),
    handleClick(item, index){
      this.items.map(item => item.active = false);
      item.active = true;

      this.setPlanIndex(index);
    }
  }
}
</script>
