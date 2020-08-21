<template>
  <div class="certificate-list">
    <div
      class="certificate-list__item"
      v-for="(certificate, index) in certificate"
      :key="index"
      @click="toCourseCertificateDetail"
    >
      <div class="certificate-list__item__img">
        <img :src="certificate.img" alt="" />
      </div>
      <div class="certificate-list__item__info">
        <p class="title">
          <span class="text-overflow text">{{ certificate.name }}</span>
          <span class="acquired" v-if="certificate.isGet">已获取</span>
          <span class="obtain" v-else>待获取</span>
        </p>
        <p class="condition text-overflow">{{ certificate.condition }}</p>
      </div>
      <div class="certificate-list__item__more">
        <i class="van-icon van-icon-arrow pull-right" />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      certificate: [
        {
          img:
            'https://dss1.bdstatic.com/70cFuXSh_Q1YnxGkpoWK1HF6hhy/it/u=3892521478,1695688217&fm=26&gp=0.jpg',
          name: '证书名称',
          isGet: false,
          condition: '通过参加课程线上考试可以获得',
        },
        {
          img:
            'https://dss1.bdstatic.com/70cFuXSh_Q1YnxGkpoWK1HF6hhy/it/u=3892521478,1695688217&fm=26&gp=0.jpg',
          name: '证书名称',
          isGet: false,
          condition: '通过参加课程线上考试可以获得',
        },
        {
          img:
            'https://dss1.bdstatic.com/70cFuXSh_Q1YnxGkpoWK1HF6hhy/it/u=3892521478,1695688217&fm=26&gp=0.jpg',
          name: '证书名称',
          isGet: false,
          condition: '通过参加课程线上考试可以获得',
        },
        {
          img:
            'https://dss1.bdstatic.com/70cFuXSh_Q1YnxGkpoWK1HF6hhy/it/u=3892521478,1695688217&fm=26&gp=0.jpg',
          name: '证书名称',
          isGet: true,
          condition: '通过参加课程线上考试可以获得',
        },
        {
          img:
            'https://dss1.bdstatic.com/70cFuXSh_Q1YnxGkpoWK1HF6hhy/it/u=3892521478,1695688217&fm=26&gp=0.jpg',
          name: '证书名称',
          isGet: true,
          condition: '通过参加课程线上考试可以获得',
        },
        {
          img:
            'https://dss1.bdstatic.com/70cFuXSh_Q1YnxGkpoWK1HF6hhy/it/u=3892521478,1695688217&fm=26&gp=0.jpg',
          name: '证书名称证书名称证书名称证书名称证书名称证书名称证书名称',
          isGet: true,
          condition:
            '通过参加课程线上考试可以获得通过参加课程线上考试可以获得通过参加课程线上考试可以获得通过参加课程线上考试可以获得',
        },
      ],
    };
  },
  methods: {
    toCourseCertificateDetail() {
      this.$router.push({ path: '/course/certificate/detail' });
    },
  },
};
</script>
