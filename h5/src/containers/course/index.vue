<template>
  <div class="course-detail">
    <span>selectedPlanIndex {{selectedPlanIndex}}</span>
    <join-before v-if="!joinStatus && details.length" :details="details"></join-before>    
    <join-after :details="details[0]" v-if="joinStatus && details.length"></join-after>
  
  </div>
</template>

<script>
  import joinAfter from './join-after.vue';
  import joinBefore from './join-before.vue';
  import Api from '@/api';
  import { mapState } from 'vuex';

  export default {
    components: {
      joinAfter,
      joinBefore
    },
    data() {
      return {
        joinStatus: true, // joinAfter = true
        details: []
      };
    },
    computed: {
      ...mapState('course', {
        selectedPlanIndex: state => state.selectedPlanIndex
      })
    },
    created(){
      Api.getCourseDetail({
        query: {
          id: this.$route.params.id
        }
      }).then(res => {
        this.details = res;
        this.joinStatus = false;
      })
    }
  }
</script>
