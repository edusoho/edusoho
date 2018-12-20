<template>
  <div class="vip-detail">
    <div class="user-section gray-border-bottom clearfix">
      <div v-if="user">
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
      <router-link to="/login" v-else>
        <img class='user-img' src="static/images/avatar.png" />
        <div class="user-middle single-middle">
          <div class='user-vip'>
            立即登录，查看会员权益
          </div>
        </div>
      </router-link>
    </div>
    <vip-introduce :levels="vipData.levels" @activeIndex="activeIndex"></vip-introduce>
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
      index: 0,
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
    },
    courseData() {
      let data = {
        items: [],
        title: '会员课程',
        source: {},
        limit: 4
      }
      data.items = this.vipData.levels[this.index].courses.data;
      return data;
    },
    classroomData() {
      let data = {
        items: [],
        title: '会员班级',
        source: {},
        limit: 4
      }
      data.items = this.vipData.levels[this.index].classrooms.data;
      return data;
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
    })
  },
  methods: {
    activeIndex(index) {
      this.index = index;
    }
  }
}
</script>
