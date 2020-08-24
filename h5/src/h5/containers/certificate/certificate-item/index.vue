<template>
  <div class="certificate-item">
    <div class="certificate-item__left">
      <img src="" alt="" />
    </div>
    <div class="certificate-item__right item-right">
      <p class="item-right__title">{{ certificate.certificate.name }}</p>
      <p class="item-right__time">
        获取时间：{{ certificate.createdTime | formatSlashTime }}
      </p>
      <p class="item-right__time">
        有效时间：
        <span
          v-if="certificate.certificateCode == '长期有效'"
          class="item-right__time--green"
          >长期有效</span
        >
        <span
          v-else-if="certificate.certificateCode == '已过期'"
          class="item-right__time--red"
        >
          2020.08.24
          <span>已过期</span>
        </span>
        <span v-else class="item-right__time--green"
          >2020.08.24<span>有效中</span></span
        >
      </p>
      <div
        class="item-right__show"
        @click="toCertificateDetail(certificate.id)"
      >
        查看证书
      </div>
    </div>
  </div>
</template>

<script>
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
        path: `/certificate/detail/${id}`,
      });
    },
  },
};
</script>
