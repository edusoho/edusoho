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
import { debug } from 'util';

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
      joinStatus: {
        handler: "joinStatusChange",
      }
    },
    created(){
      //this.getBeCourse()
      this.getCourseLessons({
           courseId: this.$route.params.id
        }).then(res=>{
          this.joinStatusChange(res.member)
        })
    },
    methods: {
      ...mapActions('course', [
        'getCourseLessons',
        'getCourseDetail',
        'getBeforeCourse',
        'getAfterCourse'
      ]),
      ...mapMutations('course', {
        setSourceType: types.SET_SOURCETYPE
      }),
      joinStatusChange(status) {
        this.currentComp = '';
        if (status) {
          this.currentComp =joinAfter
        } else {
          this.currentComp =joinBefore;
        }
      },
      getBeCourse(){
        this.getBeforeCourse({
           courseId: this.$route.params.id
        }).then(() => {
            this.getAfCourse();
        }).catch(err => {
            Toast.fail(err.message)
        })
      },
      getAfCourse(){
        this.getAfterCourse({
          courseId: this.$route.params.id
        }).then(() => {
          this.getDetail();
        }).catch(err => {
          Toast.fail(err.message)
        })
      },
      getDetail(){
        this.getCourseDetail({
          courseId: this.$route.params.id
        }).then(res => {
          this.joinStatusChange(res.member)
        })
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
