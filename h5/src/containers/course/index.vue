<template>
  <div class="course-detail">
    <e-loading v-if="isLoading"></e-loading>
    <component :is="currentComp" :details="details"></component>
  </div>
</template>

<script>
  import joinAfter from './join-after.vue';
  import joinBefore from './join-before.vue';
  import { mapState, mapActions, mapMutations } from 'vuex';
  import * as types from '@/store/mutation-types';
  import { Toast } from 'vant';

  export default {
    components: {
    },
    data() {
      return {
        currentComp: '',
      };
    },
    computed: {
      ...mapState('course', {
        selectedPlanIndex: state => state.selectedPlanIndex,
        joinStatus: state => state.joinStatus,
        details:  state => state.details,
        selectedPlanId: state => state.selectedPlanId,
      }),
      ...mapState({
        isLoading: state => state.isLoading
      }),
    },
    watch: {
      joinStatus(status) {
        this.getComponent(status);
      }
    },
    created(){
      this.getCourse({
         courseId: this.$route.params.id
      }).then(() => {
        this.getDetail();
      }).catch(err => {
          Toast.fail(err.message)
      });

    },
    methods: {
      ...mapActions('course', [
        'getCourse',
        'getCourseDetail',
        'getNextStudy'
      ]),
      ...mapMutations('course', {
        setSourceType: types.SET_SOURCETYPE
      }),
      getDetail(){
        this.getCourseDetail({
         courseId: this.$route.params.id
        }).then(() => {
            this.getComponent(this.joinStatus);
        }).catch(err => {
            Toast.fail(err.message)
        });
      },
      getComponent(status) {
        if(status){
           this.getNextStudy({
              courseId: this.selectedPlanId
            }).then(() => {}).catch(err => {
                Toast.fail(err.message)
            });
        }
        this.currentComp = status ? joinAfter : joinBefore;
      },
      //获取加入后课程目录和学习状态
      getJoinAfter(){
        this.getJoinAfterDetail({
          courseId: this.$route.params.id
        }).then((res) => {}).catch(err => {
          Toast.fail(err.message)
        })
      }
    },
    beforeRouteLeave (to, from, next) {
      this.setSourceType({
        sourceType: 'img',
        taskId: 0
      });
      next();
    }
  }
</script>
