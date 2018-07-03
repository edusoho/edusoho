<template>
  <div class="course-detail try">
    <detail-head
      :courseSet="details[selectedPlanIndex].courseSet"></detail-head>
    
    <directory
      :hiddeTitle=true
      :courseItem="details[selectedPlanIndex].courseItem"
    ></directory>
  </div>
</template>
<script>
import Directory from './detail/directory';
import DetailHead from './detail/head';
import { mapState } from 'vuex';

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
    })
  }
}
</script>
