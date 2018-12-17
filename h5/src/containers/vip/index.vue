<template>
  <div class="user">
    <div class="user-section clearfix">
      <router-link to="/settings">
        <img class='user-img' :src="user.avatar.large" />
      </router-link>
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
  </div>
</template>
<script>
import Api from '@/api';

import { mapState, mapActions } from 'vuex';

export default {
  computed: {
    ...mapState({
      user: state => state.user
    }),
    vipDated() {
      if (!this.user.vip) return false;
      const deadLineStamp = new Date(this.user.vip.deadline).getTime();
      const nowStamp = new Date().getTime();
      return nowStamp > deadLineStamp ? true : false;
    }
  },
  created() {
    this.getUserInfo();
  },
  methods: {
    ...mapActions([
      'getUserInfo'
    ])
  }
}
</script>
