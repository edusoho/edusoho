<template>
  <div class="course-detail">
    <join-before v-if="!joinStatus && details.length" :details="details"></join-before>    
    <join-after :details="details[0]" v-if="joinStatus && details.length"></join-after>
  </div>
</template>

<script>
  import joinAfter from './join-after.vue';
  import joinBefore from './join-before.vue';
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
        this.joinStatus = true;
      })
    }
  }
</script>
