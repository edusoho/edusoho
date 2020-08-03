<template>
  <div class="certificate-item">
    <div class="certificate-item__left">
      <img :src="certificate.img" alt="" />
    </div>
    <div class="certificate-item__right item-right">
      <p class="item-right__title">{{ certificate.name }}</p>
      <p class="item-right__time">
        获取时间：{{ certificate.getTime | formatSlashTime }}
      </p>
      <p class="item-right__time">
        有效时间：<span class="item-right__time--green"
          >{{ certificate.currentTime | formatSlashTime
          }}<span>已过期</span></span
        >
      </p>
      <div class="item-right__show" @click="toCertificateDetail">查看证书</div>
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
    formatSlashTime(timestamp) {
      const date = new Date(timestamp * 1);
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
    toCertificateDetail() {
      this.$router.push({
        path: '/certificate/detail',
      });
    },
  },
};
</script>
