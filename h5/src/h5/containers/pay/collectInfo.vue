<template>
  <div class="">
    <e-loading v-if="isLoading" />
    <div v-if="Object.keys(userInfoCollectForm).length">
      <info-collection
        :userInfoCollectForm="this.userInfoCollectForm"
        :formRule="this.userInfoCollectForm.items"
        @submitForm="handleSubmit"
      ></info-collection>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import infoCollection from '@/components/info-collection.vue';
import collectUserInfoMixins from '@/mixins/collectUserInfo/index.js';
import * as types from '@/store/mutation-types';
export default {
  mixins: [collectUserInfoMixins],
  components: {
    infoCollection,
  },
  data() {
    return {
      isLoading: true,
    };
  },
  computed: {
    ...mapState(['wechatSwitch']),
  },
  watch: {},
  created() {
    this.targetType = this.$route.query.targetType;
    this.targetId = this.$route.query.targetId;
    this.payWay = this.$route.query.payWay;
    this.getForm();
  },
  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE,
    }),
    getForm() {
      const eventId = this.$route.query.eventId;
      this.getInfoCollectionForm(eventId)
        .then(res => {
          this.setNavbarTitle(res.formTitle);
          this.isLoading = false;
        })
        .catch(() => {
          this.handleSubmit();
        });
    },
    handleSubmit() {
      if (this.$route.query.payWay === 'WechatPay_H5' && this.wechatSwitch) {
        this.toWxNotify();
        return;
      }
      this.toTarget();
    },
    toTarget() {
      if (this.targetType === 'vip') {
        this.$router.replace({
          path: `/${this.targetType}`,
          query: {
            backUrl: '/my/orders',
          },
        });
      } else {
        this.$router.replace({
          path: `/${this.targetType}/${this.targetId}`,
        });
      }
    },
    toWxNotify() {
      const paidUrl =
        window.location.origin +
        window.location.pathname +
        `#/${this.targetType}/${this.targetId}`;
      this.$router.replace({
        path: '/pay_success',
        query: {
          paidUrl,
        },
      });
    },
  },
};
</script>
