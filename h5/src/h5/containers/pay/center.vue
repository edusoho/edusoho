<template>
  <div class="">
    <e-loading />
  </div>
</template>

<script>
import { mapState } from 'vuex';
import collectUserInfoMixins from '@/mixins/collectUserInfo/index.js';
export default {
  mixins: [collectUserInfoMixins],
  components: {},
  data() {
    return {
      targetType: null,
      targetId: null,
      payWay: null,
    };
  },
  computed: {
    ...mapState(['wechatSwitch']),
  },
  watch: {},
  created() {
    this.getInfoCollection();
  },
  methods: {
    getInfoCollection() {
      this.targetType = this.$route.query.targetType;
      this.targetId = this.$route.query.targetId;
      this.payWay = this.$route.query.payWay;
      const paramsList = {
        action: 'buy_after',
        targetType: this.targetType,
        targetId: this.targetId,
      };
      this.getInfoCollectionEvent(paramsList)
        .then(res => {
          if (Object.keys(res).length) {
            this.toCollectUserInfo(res.id);
            return;
          }
          this.doDefault();
        })
        .catch(() => {
          this.doDefault();
        });
    },
    doDefault() {
      if (this.payWay === 'WechatPay_H5' && this.wechatSwitch) {
        this.toWxNotify();
        return;
      }
      this.toTarget();
    },
    toCollectUserInfo(eventId) {
      const query = {
        eventId,
        targetType: this.targetType,
        targetId: this.targetId,
        payWay: this.payWay,
      };
      this.$router.replace({
        path: `/pay_collectInfo`,
        query,
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
    toTarget() {
      this.$router.replace({
        path: `/${this.targetType}/${this.targetId}`,
      });
    },
  },
};
</script>
