<template>
  <div class="course-detail classroom-detail">
    <div class="join-after">
      <detail-head ref="head" :cover="details.cover"></detail-head>

      <van-tabs v-model="active" :class="tabsClass">
        <van-tab v-for="item in tabs" :title="item" :key="item"></van-tab>
      </van-tabs>

      <!-- 班级介绍 -->
      <div v-if="active == 0" style="margin-top: 44px;">
        <detail-plan :details="planDetails" :joinStatus="details.joinStatus"></detail-plan>
        <div class="segmentation"></div>

        <e-panel title="班级介绍" ref="about" class="about">
          <div v-html="details.summary"></div>
        </e-panel>
        <div class="segmentation"></div>

        <!-- 教师介绍 -->
        <teacher
          class="teacher" title="教师介绍"
          :teacherInfo="details.teachers"></teacher>
        <div class="segmentation"></div>

        <teacher
          class="teacher" title="班主任" :teacherInfo="details.headTeacher ? [details.headTeacher] : []"
          defaulValue="尚未设置班主任"></teacher>
      </div>

      <!-- 班级课程 -->
      <div v-if="active == 1" style="margin-top: 44px;">
        <course-set-list ref="course" :courseSets="details.courses" title="班级课程" defaulValue="暂无课程" :disableMask="true"></course-set-list>
      </div>

      <!-- 学员评价 -->
      <div v-if="active == 2" style="margin-top: 44px;">
        <review-list ref="review" :targetId="details.classId" :reviews="details.reviews" title="学员评价" defaulValue="暂无评价" type="classroom"></review-list>
      </div>
    </div>

  </div>
</template>

<script>
  import teacher from './teacher';
  import detailHead from './head';
  import reviewList from './review-list';
  import courseSetList from './course-set-list';
  import detailPlan from './plan';
  import directory from '../course/detail/directory';
  import moreMask from '@/components/more-mask';
  import { Dialog, Toast } from 'vant';
  import Api from '@/api';
  const TAB_HEIGHT = 44;

  export default {
    components: {
      directory,
      detailHead,
      detailPlan,
      teacher,
      courseSetList,
      reviewList,
      moreMask
    },
    props: ['details', 'planDetails'],
    data() {
      return {
        headBottom: 0,
        active: 1,
        scrollFlag: false,
        tabs: ['班级介绍', '班级课程', '学员评价'],
        tabsClass: '',
      }
    },
    mounted() {
      window.addEventListener('touchmove', this.handleScroll);

      let code = '';
      let errorMessage = '';
      let confirmCallback = function(){};

      if (this.details.member && this.details.member.access) {
        code = this.details.member.access.code;
      }
      if (!code || code === 'success') {
        return;
      }

      // 学习任务报错信息
      this.errorMsg = this.getErrorMsg(code);

      // 错误处理
      if (code === 'classroom.expired' || code === 'member.expired') {
        errorMessage = '班级已到期，无法继续学习，是否退出';
        const params = { id: this.details.classId };
        confirmCallback = () => {
          Api.deleteClassroom({ query: params }).then(res => {
            if (res.success) {
              window.location.reload();
              return;
            }
            Toast.fail('退出班级失败，请稍后重试')
          })
        };
        this.callConfirm(errorMessage, confirmCallback);
      } else if (code === 'vip.member_expired') {
        errorMessage = '会员已到期，请及时续费会员';
        confirmCallback = () => {
          this.$router.push({
            path: `/vip`
          });
        };
        this.callConfirm(errorMessage, confirmCallback);
      } else {
        Toast.fail(this.getErrorMsg(code));
      }
    },
    destroyed () {
      window.removeEventListener('touchmove', this.handleScroll);
    },
    methods: {
      handleScroll() {
        if (this.scrollFlag) {
          return;
        }
        this.scrollFlag = true;
        const refs = this.$refs;

        // 滚动节流
        setTimeout(() => {
          this.headBottom = refs['head'].$el.getBoundingClientRect().bottom
          this.scrollFlag = false;
          this.tabsClass = this.headBottom <= 0 ? 'van-tabs--fixed' : '';
        }, 400)
      },
      getErrorMsg(code) {
        switch(code) {
          case 'classroom.not_found':
            return '当前班级不存在';
          case 'classroom.unpublished':
            return '当前班级未发布';
          case 'classroom.expired':
            return '当前班级已过期';
          case 'user.not_login':
            return '用户未登录';
          case 'user.locked':
            return '用户被锁定';
          case 'member.not_found':
            return '用户未加入班级';
          case 'member.auditor':
            return '用户是旁听生';
          case 'member.expired':
            return '班级已过期';
          case 'vip.vip_closed':
            return '网校已关闭会员功能';
          case 'vip.not_login':
            return '用户未登录';
          case 'vip.not_member':
            return '当前用户并不是vip';
          case 'vip.member_expired':
            return '用户会员服务已过期';
          case 'vip.level_not_exist':
            return '用户会员等级或班级会员不存在';
          case 'vip.level_low':
            return '用户会员等级未达到班级要求';
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
      }
    },
  }
</script>
