<template>
  <div class="certificate-item">
    <e-loading v-if="isLoading" />
    <div class="certificate-item__left">
      <img src="static/images/certificate.png" alt="" />
    </div>
    <div class="certificate-item__right item-right">
      <p class="item-right__title">{{ certificate.certificate.name }}</p>
      <p class="item-right__time">
        {{ $t('certificate.getTime') }}：{{ certificate.issueTime | formatSlashTime }}
      </p>
      <p class="item-right__time">
        {{ $t('certificate.effectiveTime') }}：<span
          v-if="certificate.expiryTime == 0"
          class="item-right__time--green"
          >{{ $t('certificate.longTermEffective') }}</span
        ><span
          v-else-if="certificate.status == 'expired'"
          class="item-right__time--red"
          >{{ certificate.expiryTime | formatSlashTime }}
          <span>{{ $t('certificate.expired') }}</span>
        </span>
        <span
          v-else-if="certificate.status == 'valid'"
          class="item-right__time--green"
          >{{ certificate.expiryTime | formatSlashTime }}
          <span>{{ $t('certificate.effective') }}</span></span
        >
      </p>
      <div
        class="item-right__show"
        @click="toCertificateDetail(certificate.id)"
      >
        {{ $t('certificate.details') }}
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';

export default {
  props: {
    certificate: {
      type: Object,
      default: () => {
        return {};
      },
    },
  },
  filters: {
    formatSlashTime(time) {
      const date = new Date(time);
      const year = date.getFullYear();
      const month = date.getMonth() + 1;
      const day = date.getDate();
      return [year, month, day]
        .map(n => {
          n = n.toString();
          return n[1] ? n : '0' + n;
        })
        .join('/');
    },
  },
  methods: {
    toCertificateDetail(id) {
      this.$router.push({
        path: `/certificate_records/${id}`,
      });
    },
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
    }),
  },
};
</script>
