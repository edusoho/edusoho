<template>
  <div class="fixed top-0 bottom-0 left-0 right-0 bg-no-repeat bg-contain"
    style="background-color: #F2F5F7;background-image: url('static/images/home-bg.png');">
    <e-loading v-if="isLoading" />

    <user />

    <div class="px-16 py-24 mx-16 bg-fill-1" style="border-radius: 6px;">
      <div class="flex items-center justify-between mb-24" @click="$router.push({ name: 'myOrder' })">
        <div class="flex items-center">
          <img class="mr-12" :src="icon.orderIcon" :srcset="icon.orderIcon2" style="height: 22px;" />
          <div class="text-text-5 text-14" style="line-height: 22px;">{{ $t('title.myOrder') }}</div>
        </div>
        <i class="van-icon van-icon-arrow" />
      </div>

      <div class="flex items-center justify-between mb-24" @click="$router.push({ name: 'myActivity' })">
        <div class="flex items-center">
          <img class="mr-12" :src="icon.activityIcon" :srcset="icon.activityIcon2" style="height: 22px;" />
          <div class="text-text-5 text-14" style="line-height: 22px;">{{ $t('title.myActivity') }}</div>
        </div>
        <i class="van-icon van-icon-arrow" />
      </div>

      <div class="flex items-center justify-between mb-24" @click="$router.push({ name: 'couponCovert' })">
        <!-- <div v-if="hasBusinessDrainage"> -->
        <div class="flex items-center">
          <img class="mr-12" :src="icon.couponIcon" :srcset="icon.couponIcon2" style="height: 22px;" />
          <div class="text-text-5 text-14" style="line-height: 22px;">{{ $t('enter.coupon') }}</div>
        </div>
        <i class="van-icon van-icon-arrow" />
      </div>

      <!-- <a v-if="isShowDistributorEntrance" :href="drpSetting.distributor_login_url"> -->
      <a class="flex items-center justify-between block mb-24" :href="drpSetting.distributor_login_url">
        <div class="flex items-center">
          <img class="mr-12" :src="icon.activityIcon" :srcset="icon.activityIcon2" style="height: 22px;" />
          <div class="text-text-5 text-14" style="line-height: 22px;">{{ $t('enter.distribution') }}</div>
        </div>
        <i class="van-icon van-icon-arrow" />
      </a>

      <div class="flex items-center justify-between mb-24" @click="$router.push({ name: 'my_certificate' })">
        <div class="flex items-center">
          <img class="mr-12" :src="icon.certificateIcon" :srcset="icon.certificateIcon2" style="height: 22px;" />
          <div class="text-text-5 text-14" style="line-height: 22px;">{{ $t('enter.myCertificate') }}</div>
        </div>
        <i class="van-icon van-icon-arrow" />
      </div>

      <div class="flex items-center justify-between mb-24" @click="$router.push({ name: 'myContract' })">
        <div class="flex items-center">
          <img class="mr-12" :src="icon.contractIcon" :srcset="icon.contractIcon" style="height: 22px;" />
          <div class="text-text-5 text-14" style="line-height: 22px;">{{ $t('title.myContract') }}</div>
        </div>
        <i class="van-icon van-icon-arrow" />
      </div>

      <div class="flex items-center justify-between" @click="$router.push({ name: 'myWrongQuestionBook' })">
        <div class="flex items-center">
          <img class="mr-12" :src="icon.mistakesCollectionIcon" :srcset="icon.mistakesCollectionIcon2" style="height: 22px;" />
          <div class="text-text-5 text-14" style="line-height: 22px;">{{ $t('enter.mistakesCollection') }}</div>
        </div>
        <i class="van-icon van-icon-arrow" />
      </div>
    </div>

    <!-- <van-tabs v-model="activeIndex" class="after-tabs e-learn">
      <van-tab v-for="(item, index) in tabs" :title="$t(item)" :key="index" />
    </van-tabs>
    <orders v-show="activeIndex === 0" />
    <activity v-show="activeIndex === 1" /> -->
  </div>
</template>
<script>
// import Orders from '../order/orders.vue';
// import activity from './activity';
import User from './user.vue';
import { mapState } from 'vuex';
import preloginMixin from '@/mixins/preLogin';
import Api from '@/api';
import icon from './icon'

const entryData = [
  {
    name: 'enter.mistakesCollection',
    link: 'myWrongQuestionBook',
  },
];

export default {
  components: {
    // Orders,
    // activity,
    User,
  },
  mixins: [preloginMixin],
  data() {
    return {
      activeIndex: 0,
      tabs: ['enter.myOrder', 'enter.activities'],
      hasBusinessDrainage: false,
      isShowDistributorEntrance: false, // 是否展示分销中心入口
      drpSetting: {},
      entryData,
      icon,
    };
  },
  computed: {
    ...mapState(['DrpSwitch']),
    ...mapState({
      isLoading: state => state.isLoading,
    }),
  },
  created() {
    setTimeout(() => {
      window.scrollTo(0, 0);
    }, 100);
    Api.hasPluginInstalled().then(res => {
      this.hasBusinessDrainage = res.BusinessDrainage;
    });
    this.showDistributorEntrance();
  },
  methods: {
    showDistributorEntrance() {
      if (!this.DrpSwitch) {
        this.isShowDistributorEntrance = false;
        return;
      }
      this.getDrpSetting();
      this.getAgencyBindRelation();
    },
    getAgencyBindRelation() {
      Api.getAgencyBindRelation().then(data => {
        if (!data.agencyId) {
          this.isShowDistributorEntrance = false;
          return;
        }
        this.isShowDistributorEntrance = true;
      });
    },
    getDrpSetting() {
      Api.getDrpSetting().then(data => {
        this.drpSetting = data;
      });
    },
  },
};
</script>
