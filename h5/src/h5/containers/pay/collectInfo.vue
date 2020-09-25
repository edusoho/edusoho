<template>
  <div class="">
    <e-loading v-if="isLoading" />
    <div v-if="Object.keys(userInfoCellectForm).length">
      <info-collection
        :userInfoCellectForm="this.userInfoCellectForm"
        :formRule="this.userInfoCellectForm.items"
        @submitForm="handleSubmit"
      ></info-collection>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import infoCollection from '@/containers/info-collection/index';
import collectUserInfoMixins from '@/mixins/collectUserInfo/index.js';
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
    getForm() {
      const eventId = this.$route.query.eventId;
      this.getInfoCollectionForm(eventId)
        .then(() => {
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
      this.$router.replace({
        path: `/${this.targetType}/${this.targetId}`,
      });
    },
    toWxNotify() {
      const paidUrl =
        window.location.origin + `/#/${this.targetType}/${this.targetId}`;
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
