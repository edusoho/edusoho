<template>
  <div class="certificate">
    <e-loading v-if="isLoading" />
    <div
      class="certificate-year"
      v-for="(certificate, index) in certificates"
      :key="index"
      :class="[index + 1 == certificates.length ? 'lastChild' : '']"
    >
      <p class="certificate-year__title">{{ certificate.issueYear }}</p>
      <certificate-item
        v-for="(certificateItem, ind) in certificate.certificateRecords"
        :key="certificateItem.id"
        :certificate="certificateItem"
        :class="[
          ind == 0 ? 'firstChild' : '',
          ind + 1 == certificate.certificateRecords.length ? 'lastChild' : '',
        ]"
      >
      </certificate-item>
    </div>
  </div>
</template>

<script>
import CertificateItem from './certificate-item/index';
import Api from '@/api';
import { mapState } from 'vuex';
import { Toast } from 'vant';

export default {
  data() {
    return {
      certificates: [],
    };
  },
  components: {
    CertificateItem,
  },
  created() {
    Api.meCertificate()
      .then(res => {
        this.certificates = res.data;
      })
      .catch(err => {
        Toast.fail(err.message);
      });
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
    }),
  },
};
</script>
