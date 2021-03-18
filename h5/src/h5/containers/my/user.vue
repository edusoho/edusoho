<template>
  <div class="user">
    <div class="user-section clearfix">
      <router-link to="/settings">
        <img v-if="user.avatar" :src="user.avatar.large" class="user-img" />
      </router-link>
      <div class="user-middle">
        <div class="user-name">{{ user.nickname }}</div>
      </div>
      <router-link to="/settings" class="user-setting">
        <img src="static/images/setting.png" />
      </router-link>
    </div>
    <div class="user-member" v-if="vipSwitch">
      <div class="user-member-style">
        <div class="user-member-text">
          <img src="static/images/vip_enter.png" />
          <div v-if="user.vip" class="user-vip">
            <router-link
              :to="{ path: '/vip', query: { id: user.vip.levelId } }"
              class="clearfix"
            >
              <span class="pull-left">
                <p>{{ user.vip.vipName }}</p>
                <p style="font-size: 12px; margin-top: 2px;">
                  会员到期时间：
                  {{ $moment(user.vip.deadline).format('YYYY-MM-DD') }}
                </p>
              </span>
              <span class="pull-right" style="margin-top: 10px;">
                续费/升级
                <van-icon name="arrow" />
              </span>
            </router-link>
          </div>
          <div v-else class="user-vip user-vip-open">
            <router-link to="/vip" class="clearfix">
              <span>您还不是会员，开通会员享特权</span>
              <span class="pull-right">
                去开通
                <van-icon name="arrow" />
              </span>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapState, mapActions } from 'vuex';

export default {
  computed: {
    ...mapState(['user', 'vipSwitch']),
    // vipDated() {
    //   if (!this.user.vip) return false;
    //   const deadLineStamp = new Date(this.user.vip.deadline).getTime();
    //   const nowStamp = new Date().getTime();
    //   return nowStamp > deadLineStamp;
    // },
  },
  created() {
    this.getUserInfo();
  },
  methods: {
    ...mapActions(['getUserInfo']),
  },
};
</script>
