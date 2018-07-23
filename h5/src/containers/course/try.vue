 <!-- 试看页 -->
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
import { mapState } from 'vuex';
import * as types from '@/store/mutation-types';

export default {
  components: {
    Directory,
    DetailHead
  },
  computed: {
    ...mapState('course', {
      details: state => state.details,
    }),
    ...mapState({
      isLoading: state => state.isLoading,
    })
  },
  beforeRouteLeave (to, from, next) {
    this.$store.commit(`course/${types.SET_SOURCETYPE}`, {
      sourceType: 'img'
    });
    next();
  }
}
</script>
