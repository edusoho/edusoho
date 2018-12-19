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
  </div>
</template>
<script>
import Api from '@/api';
import introduce from './introduce';

export default {
  data() {
    return {
      user: null,
      vipLevelId: this.$router.query ? this.$router.query.vipLevelId : 1
    }
  },
  components: {
    'vip-introduce': introduce
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
      this.user = res.vipUser.user;
    })
  }
}
</script>
