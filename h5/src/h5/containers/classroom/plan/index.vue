<template>
  <div class="classroom-plan">
    <e-panel :title="details.title" type="title">
      <div class="course-detail__plan-price">
        <span :class="{ isFree: Number(details.price) === 0 }">{{
          details.price | filterPrice
        }}</span>
        <span class="plan-price__student-num"
          >{{ details.studentNum }}人在学</span
        >
      </div>
    </e-panel>

    <div class="course-detail__validity">
      <div v-if="details.vipLevel && vipSwitch" class="mb15">
        <span class="mr20">会员免费</span>
        <img :src="details.vipLevel.icon" class="vipIcon" />
        <router-link
          :to="{ path: '/vip', query: { id: details.vipLevel.id } }"
          class="color-primary"
          >{{ details.vipLevel.name }}免费学</router-link
        >
      </div>
      <service v-if="details.service.length" :services="details.service" />
      <div>
        <span>学习有效期：</span>
        <span v-html="learnExpiryHtml" />
      </div>
    </div>

    <div
      class="course-detail__certificate"
      @click="toCertificate"
      v-if="$route.query.hasCertificate"
    >
      <span><span class="certificate-icon">证</span>证书</span>
      <i class="van-icon van-icon-arrow pull-right" />
    </div>
  </div>
</template>
<script>
import { mapState } from 'vuex';
import service from '../service';
import { formatFullTime } from '@/utils/date-toolkit.js';

export default {
  components: {
    service,
  },
  filters: {
    filterPrice(price) {
      const isFree = Number(price) === 0;
      return isFree ? '免费' : `¥${price}`;
    },
  },
  props: {
    details: {
      default: {},
    },
    joinStatus: {
      default: false,
    },
  },
  watch: {
    learnExpiryHtml: {
      immediate: true,
      handler(val) {
        this.$emit('getLearnExpiry', {
          val,
        });
      },
    },
  },
  computed: {
    ...mapState(['vipSwitch']),
    // eslint-disable-next-line vue/return-in-computed-property
    learnExpiryHtml() {
      const memberInfo = this.joinStatus;
      const learnExpiryData = this.details.expiryValue;
      const expiryMode = this.details.expiryMode;

      if (!memberInfo) {
        switch (expiryMode) {
          case 'forever':
            return '永久有效';
            // eslint-disable-next-line no-unreachable
            break;
          case 'date':
            // eslint-disable-next-line no-case-declarations
            const time = new Date(learnExpiryData * 1000);
            return formatFullTime(time).slice(0, 10) + '之前可学习';
            // eslint-disable-next-line no-unreachable
            break;
          case 'days':
            return learnExpiryData + '天内可学习';
            // eslint-disable-next-line no-unreachable
            break;
        }
      } else {
        if (expiryMode == 'forever') {
          return '永久有效';
        }
        return memberInfo.deadline != 0
          ? memberInfo.deadline.slice(0, 10) + '之前可学习'
          : '永久有效';
      }
    },
  },
  methods: {
    toCertificate() {
      this.$router.push({ path: `/certificate/list/${this.$route.params.id}` });
    },
  },
};
</script>
