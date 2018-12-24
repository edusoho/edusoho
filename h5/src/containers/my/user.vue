<template>
  <div class="user">
    <div class="user-section clearfix">
      <router-link to="/settings">
        <img class='user-img' :src="user.avatar.large" />
      </router-link>
      <div :class="['user-middle', vipSettings.enabled ? '' : 'single-middle']">
        <div class='user-name color-primary'>{{ user.nickname }}</div>
        <div v-if="vipSettings.enabled">
          <span class='user-vip' v-if="user.vip">
            <router-link :to="{path: '/vip', vipLevelId: user.vip.levelId}">
              <img :class="['vip-img', vipDated ? 'vip-expired' : '']" :src="user.vip.icon">
              <span v-if="!vipDated">{{ user.vip.vipName }}</span>
              <span class="grey" v-else>会员已过期</span>
            </router-link>
          </span>
          <div v-else>
            <router-link class='user-vip' to="/vip">
              您还不是会员，<span class="color-primary">去开通</span>
            </router-link>
          </div>
        </div>
      </div>
      <router-link to="/settings" class='user-setting'>
        <img src='static/images/setting.png'>
      </router-link>
    </div>
  </div>
</template>
<script>
import Api from '@/api';

import { mapState, mapActions } from 'vuex';

export default {
  computed: {
    ...mapState(['user', 'vipSettings']),
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
