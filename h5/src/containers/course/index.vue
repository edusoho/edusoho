<template>
  <div class="course-detail">
    <join-before v-if="!joinStatus" :details="details"></join-before>    
    <join-after :details="details" v-else></join-after>
  </div>
</template>

<script>
  import joinAfter from './joinAfter.vue';
  import joinBefore from './joinBefore.vue';
  import Api from '@/api';

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
