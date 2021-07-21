<template>
  <div class="user">
    <div class="user-section clearfix">
      <router-link to="/settings">
        <div class="user-img">
          <img v-if="user.avatar" :src="user.avatar.large" />
          <img
            class="user-vip-icon"
            v-if="user.vip && !vipDated && vipSwitch"
            :src="user.vip.icon"
            alt=""
          />
        </div>
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
          <div
            v-if="user.vip"
            class="user-vip"
            :class="{ 'user-vip-open': vipDated }"
          >
            <router-link
              :to="{ path: '/vip', query: { id: user.vip.levelId } }"
              class="clearfix"
            >
              <template v-if="!vipDated">
                <span class="pull-left">
                  <p>{{ user.vip.vipName }}</p>
                  <p style="font-size: 12px; margin-top: 2px;">
                    {{ $t('vip.memberExpirationTime') }}：
                    {{ $moment(user.vip.deadline).format('YYYY-MM-DD') }}
                  </p>
                </span>
                <span class="pull-right" style="margin-top: 10px;">
                  {{ $t('vip.renewalUpgrade') }}
                  <van-icon name="arrow" />
                </span>
              </template>
              <template v-else>
                <span>{{ $t('vip.yourMembershipHasExpired') }}</span>
                <span class="pull-right">
                  {{ $t('vip.immediatelyRenewals') }}
                  <van-icon name="arrow" />
                </span>
              </template>
            </router-link>
          </div>
          <div v-else class="user-vip user-vip-open">
            <router-link to="/vip" class="clearfix">
              <span>{{ $t('vip.youAreNotAVipYet') }}</span>
              <span class="pull-right">
                {{ $t('vip.joinNow') }}
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

    vipDated() {
      const deadLineStamp = new Date(this.user.vip.deadline).getTime();
      const nowStamp = new Date().getTime();
      return nowStamp > deadLineStamp;
    },
  },
  created() {
    this.getUserInfo();
  },
  methods: {
    ...mapActions(['getUserInfo']),
  },
};
</script>
