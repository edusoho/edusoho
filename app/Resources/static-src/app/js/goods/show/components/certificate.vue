<template>
  <div class="info-right-certificate info-right-box" v-if="certificates.length">
    <div class="title">{{ 'goods.show_page.authentication_certificate'|trans }}</div>
    <div class="certificate-info" v-for="certificate in certificates" :key="certificate.id">
      <a href="#" data-target="#modal" data-toggle="modal" :data-url="`/certificate/${certificate.id}/detail`">
        <img src="/static-dist/app/img/default_certificate1.png" class="certificate-info__img" />

        <p class="certificate-info__title text-overflow">{{ certificate.name }}</p>
      </a>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() {
    return {
      certificates: []
    }
  },
  props: ['goodsId', 'sku'],
  methods: {
    requestCertificateData() {
      axios.get('/api/certificates', {
        params: {
          targetId: this.sku.targetId,
          limit: 4
        },
        headers: {
          Accept: "application/vnd.edusoho.v2+json",
        }
        })
        .then(res => {
          this.certificates = res.data.data;
        });
    }
  },
  mounted() {
    this.requestCertificateData();
  },
  watch: {
    sku() {
      this.requestCertificateData();
    }
  }
};
</script>
