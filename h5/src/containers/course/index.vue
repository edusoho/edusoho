<template>
  <div class="course-detail">
    <join-before v-if="!joinStatus && !isEmpty" :details="details"></join-before>    
    <join-after :details="details" v-if="joinStatus && !isEmpty"></join-after>
  </div>
</template>

<script>
  import joinAfter from './join-after.vue';
  import joinBefore from './join-before.vue';
  import Api from '@/api';
  import { mapState, mapActions } from 'vuex';

  export default {
    components: {
      joinAfter,
      joinBefore
    },
    computed: {
      ...mapState('course', {
        selectedPlanIndex: state => state.selectedPlanIndex,
        joinStatus: state => state.joinStatus,
        details:  state => state.details
      }),
      isEmpty() {
        return Object.keys(this.details).length == 0;
      }
    },
    created(){
      this.getCourseDetail({
        courseId: this.$route.params.id
      })
    },
    methods: {
      ...mapActions('course', [
        'getCourseDetail'
      ])
    }
  }
</script>
