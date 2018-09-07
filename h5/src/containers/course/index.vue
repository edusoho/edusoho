<template>
  <div class="course-detail">
    <e-loading v-if="isLoading"></e-loading>
    <join-before v-if="!joinStatus && !isEmpty" :details="details"></join-before>
    <join-after :details="details" v-if="joinStatus && !isEmpty"></join-after>
  </div>
</template>

<script>
  import joinAfter from './join-after.vue';
  import joinBefore from './join-before.vue';
  import Api from '@/api';
  import { mapState, mapActions, mapMutations } from 'vuex';
  import * as types from '@/store/mutation-types';

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
      ...mapState({
        isLoading: state => state.isLoading
      }),
      isEmpty() {
        return Object.keys(this.details).length === 0;
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
      ]),
      ...mapMutations('course', {
        setSourceType: types.SET_SOURCETYPE
      }),
    },
    beforeRouteLeave (to, from, next) {
      this.setSourceType({
        sourceType: 'img',
        taskId: -1
      });
      next();
    }
  }
</script>
