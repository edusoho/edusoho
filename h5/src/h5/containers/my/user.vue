<template>
  <div class="user">
    <div class="flex items-center px-16 py-24">
      <div class="flex items-center" style="flex-grow: 1;" @click="$router.push('/editInformation')" >
        <div class="user-img" style="width: 68px; height: 68px">
          <img v-if="user.avatar" :src="user.avatar.large" style="width: 68px; height: 68px"/>
          <img
            class="user-vip-icon"
            v-if="user.vip && !vipDated && vipSwitch"
            :src="user.vip.icon"
            alt=""
          />
        </div>
        <div class="pl-20 flex flex-col justify-between" style="flex-grow: 1;">
          <div class="font-bold text-text-5 text-20">{{ user.nickname }}</div>
          <div class="w-full mt-4 font-bold text-overflow text-text-3 text-14" style="line-height: 22px;height: 22px;" v-html="user.about || ''"></div>
        </div>
      </div>
      <img src="static/images/settings.svg" alt="" @click="$router.push('/setting')" class="flex-shrink-0"/>
    </div>

    <div v-if="vipSwitch" class="mx-16 mb-16" style="background-color: #202212; border-radius: 6px;">
      <div v-if="user.vip" class="px-12 py-8">
        <router-link
          :to="{ path: '/vip', query: { id: user.vip.levelId } }"
          class="flex items-center justify-between"
        >
          <div class="text-12" style="color: #ffb977;">
            <div class="flex items-center font-bold">
              <img class="mr-8" :src="icon.vipIcon" :srcset="icon.vipIcon2" style="height: 20px;" />
              <span style="line-height: 20px;">{{ user.vip.vipName }}</span>
            </div>
            <div style="color: #ba9875; margin-top: 2px;">
              {{ $t('vip.memberExpirationTime') }}
              {{ $moment(user.vip.deadline).format('YYYY-MM-DD') }}
            </div>
          </div>
          <div class="px-8 py-4 ml-0 text-10" style="margin-right: -8px; border: solid 1px #FECFA0; color: #FECFA0; border-radius: 28px;">
            {{ $t('vip.renewalUpgrade') }}
          </div>
        </router-link>
      </div>

      <div v-if="!user.vip" class="p-12" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="flex font-bold text-14" style="color: #ffb977;">
          <img class="mr-8" :src="icon.vipIcon" :srcset="icon.vipIcon2" style="height: 20px;" /> {{ $t('vip.youAreNotAVipYet') }}
        </div>
        <div class="flex items-center justify-center font-bold text-12"
          @click="$router.push({ name: 'vip' })"
          style="width: 44px; height: 22px;color: #162923;background: linear-gradient(101.25deg, #FFD8AF -3.41%, #FFB36C 100%);border-radius: 29px;">
          {{ $t('vip.join') }}
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapState, mapActions } from 'vuex';
import icon from './icon';

export default {
  data() {
    return {
      icon,
    }
  },
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
