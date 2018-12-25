<template>
  <div class="join-after">
    <detail-head :courseSet="details.courseSet"></detail-head>

    <van-tabs v-model="active" :class="tabsClass">
      <van-tab v-for="item in tabs" :title="item" :key="item"></van-tab>
    </van-tabs>

     <!-- 课程目录 -->
    <div class="join-after__content">
      <div v-if="active == 1">
        <div class="progress-bar">
          <div class="progress-bar__content">
            <div class="progress-bar__rate" :style="{'width': progress}"></div>
          </div>
          <div class="progress-bar__text">{{ progress }}</div>
        </div>

        <directory
          @showDialog="showDialog"
          :hiddeTitle=true
          :errorMsg="errorMsg"
          class="join-after-dirctory"
          :tryLookable="details.tryLookable"></directory>
      </div>

      <div v-if="active == 0">
        <!-- 课程计划 -->
        <detail-plan @switchPlan="showDialog"></detail-plan>

        <div class="segmentation"></div>
        <!-- 课程介绍 -->
        <e-panel title="课程介绍">
          <div v-html="summary"></div>
        </e-panel>
        <div class="segmentation"></div>

        <!-- 教师介绍 -->
        <teacher
          class="teacher"
          :teacherInfo="details.teachers"></teacher>
      </div>

      <!-- 学员评价 -->
      <div v-if="active == 2">
        <review-list ref="review" :targetId="details.courseSet.id" :reviews="details.reviews" title="学员评价" defaulValue="暂无评价" type="course"></review-list>
      </div>
    </div>
  </div>
</template>
<script>
import reviewList from '@/containers/classroom/review-list';
import Directory from './detail/directory';
import DetailHead from './detail/head';
import DetailPlan from './detail/plan';
import Teacher from './detail/teacher';
import { mapState } from 'vuex';
import { Dialog, Toast } from 'vant';
import Api from '@/api';

export default {
  props: {
    details: {
      type: Object,
      value: () => {
        return {}
      }
    },
  },
  data() {
    return {
      headBottom: 0,
      active: 1,
      scrollFlag: false,
      tabs: ['课程介绍', '课程目录', '学员评价'],
      tabsClass: '',
      errorMsg: '',
    }
  },
  computed: {
    ...mapState('course', {
      selectedPlanId: state => state.selectedPlanId,
    }),
    ...mapState(['user']),
    progress () {
      if(!Number(this.details.publishedTaskNum)) return '0%';

      return parseInt(this.details.progress.percent)+'%';
    },
    summary () {
      return  this.details.summary || this.details.courseSet.summary;
    },
    isClassCourse() {
      return Number(this.details.parentId);
    },
  },
  watch: {
    selectedPlanId: (val, oldVal) => {
      val !== oldVal && (this.active = 0)
      console.log(this.active, 'active')
    },
  },
  async created() {
    this.showDialog();
  },
  components: {
    Directory,
    DetailHead,
    DetailPlan,
    Teacher,
    reviewList
  },
  methods: {
    showDialog() {
      if (!this.details.member) return;

      let errorCode = '';
      if (this.details.member.access) {
        errorCode = this.details.member.access.code;
      }
      if (!errorCode || errorCode === 'success') {
        return;
      }

      // 学习任务报错信息
      this.errorMsg = this.getErrorMsg(errorCode);

      let errorMessage = '';
      let confirmCallback = function(){};

      if (errorCode === 'course.expired' || errorCode === 'member.expired'
        && !this.isClassCourse) { // 班级课程不可以退出, 普通课程可以退出
        errorMessage = '课程已到期，无法继续学习，是否退出';
        const params = { id: this.details.id };
        confirmCallback = () => {
          Api.deleteCourse({ query: params }).then(res => {
            if (res.success) {
              window.location.reload();
              return;
            }
            Toast.fail('退出课程失败，请稍后重试')
          })
        };
        this.callConfirm(errorMessage, confirmCallback);
      } else if (errorCode === 'vip.member_expired') {
        errorMessage = '会员已到期，请及时续费会员';
        confirmCallback = () => {
          this.$router.push({
            path: `/vip`,
            query: {
              id: this.user.vip && this.user.vip.levelId,
            }
          });
        };
        this.callConfirm(errorMessage, confirmCallback);
      } else {
        Toast.fail(this.errorMsg);
      }
    },
    getErrorMsg(code) {
      switch(code) {
        case 'course.not_found':
          return '当前课程不存在';
        case 'course.unpublished':
          return '当前课程未发布';
        case 'course.expired':
          return '当前课程已过期';
        case 'course.not_arrive':
          return '当前课程还不能学习';
        case 'user.not_login':
          return '用户未登录';
        case 'user.locked':
          return '用户被锁定';
        case 'member.not_found':
          return '用户未加入课程';
        case 'member.expired':
          return '课程已过期';
        case 'vip.vip_closed':
          return '网校已关闭会员功能';
        case 'vip.not_login':
          return '用户未登录';
        case 'vip.not_member':
          return '当前用户并不是vip';
        case 'vip.member_expired':
          return '用户会员服务已过期';
        case 'vip.level_not_exist':
          return '用户会员等级或课程会员不存在';
        case 'vip.level_low':
          return '用户会员等级过低';
        default:
          return '异常错误';
      }
    },
    callConfirm(message, callback) {
      Dialog.confirm({
        title: '',
        message: message,
      }).then(() => {
        callback();
      }).catch(() => {})
    },

  }
}
</script>
