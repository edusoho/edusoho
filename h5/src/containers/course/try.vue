<template>
  <div class="course-detail try">
    <e-loading v-if="isLoading"></e-loading>
    <detail-head
      :courseSet="details.courseSet"></detail-head>
    
    <directory
      :hiddeTitle=true
      :courseItems="details.courseItems"
    ></directory>
  </div>
</template>
<script>
import Directory from './detail/directory';
import DetailHead from './detail/head';
import { mapState, mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';

export default {
  components: {
    Directory,
    DetailHead
  },
  data () {
    return {
      courseItem: [],
      type: ''
    }
  },
  computed: {
    ...mapState('course', {
      details: state => state.details,
      selectedPlanIndex: state => state.selectedPlanIndex
    }),
    ...mapState({
      isLoading: state => state.isLoading,
    })
  },
  beforeRouteLeave (to, from, next) {
    this.setSourceType('img');
    next();
  },
  methods: {
    ...mapMutations('course', {
      setSourceType: types.SET_SOURCETYPE
    }),
  }
}
</script>
