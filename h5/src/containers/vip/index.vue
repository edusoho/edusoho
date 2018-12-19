<template>
  <div class="vip-detail" v-if="user">
    <div class="user-section gray-border-bottom clearfix">
      <img class='user-img' :src="user.avatar.large" />
      <div class="user-middle">
        <div class='user-name'>{{ user.nickname }}</div>
        <span class='user-vip' v-if="user.vip">
          <img :class="['vip-img', vipDated ? 'vip-expired' : '']" :src="user.vip.icon">
          <span v-if="!vipDated">{{ user.vip.vipName }}</span>
          <span class="grey" v-else>会员已过期</span>
        </span>
        <router-link to="/vip" class='user-vip' v-else>
          您还不是会员
        </router-link>
      </div>
    </div>
    <vip-introduce></vip-introduce>
    <e-course-list
      class="gray-border-bottom"
      :courseList="courseData"
      :typeList="'course_list'"
      />
    <e-course-list
      class="gray-border-bottom"
      :courseList="classroomData"
      :typeList="'classroom_list'"
      />
    <div class="btn-join-bottom">立即开通</div>
  </div>
</template>
<script>
import Api from '@/api';
import introduce from './introduce';
import courseList from '../components/e-course-list/e-course-list.vue';

export default {
  data() {
    return {
      user: null,
      vipData: null,
      courseData: {
        items: [],
        title: '会员课程',
        source: {},
        limit: 4
      },
      classroomData: {
        items: [],
        title: '会员班级',
        source: {},
        limit: 4
      },
      vipLevelId: this.$router.query ? this.$router.query.vipLevelId : 1
    }
  },
  components: {
    'vip-introduce': introduce,
    'e-course-list': courseList
  },
  computed: {
    vipDated() {
      if (!this.user.vip) return false;
      const deadLineStamp = new Date(this.user.vip.deadline).getTime();
      const nowStamp = new Date().getTime();
      return nowStamp > deadLineStamp ? true : false;
    }
  },
  created() {
    Api.getVipDetail({
      query: {
        levelId: this.vipLevelId
      }
    }).then((res) => {
      this.vipData = res;
      this.user = res.vipUser.user;
      this.courseData.items = res.courses.data;
      this.classroomData.items = res.classrooms.data;
    })
  }
}
</script>
