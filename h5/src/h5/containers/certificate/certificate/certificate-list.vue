<template>
  <div class="certificate-list">
    <e-loading v-if="isLoading" />

    <div
      class="certificate-list__item"
      v-for="(certificate, index) in certificates"
      :key="index"
      @click="toCertificateDetail(certificate.id)"
    >
      <div class="certificate-list__item__img">
        <img src="static/images/certificate.png" alt="" />
      </div>
      <div class="certificate-list__item__info">
        <p class="title">
          <span class="text-overflow text">{{ certificate.name }}</span>
          <span class="acquired" v-if="certificate.isObtained">已获取</span>
          <span class="obtain" v-else>待获取</span>
        </p>
        <p
          class="condition text-overflow"
          v-if="certificate.targetType == 'classroom'"
        >
          通过完成班级学习可以获得
        </p>
        <p
          class="condition text-overflow"
          v-if="certificate.targetType == 'course'"
        >
          通过完成课程学习可以获得
        </p>
      </div>
      <div class="certificate-list__item__more">
        <i class="van-icon van-icon-arrow pull-right" />
      </div>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState } from 'vuex';
import { Toast } from 'vant';

export default {
  data() {
    return {
      certificates: [],
    };
  },
  methods: {
    toCertificateDetail(id) {
      this.$router.push({ path: `/certificate/detail/${id}` });
    },
  },
  created() {
    Api.certificates({
      params: {
        targetId: this.$route.params.id,
      },
    })
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
