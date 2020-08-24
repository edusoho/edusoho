<template>
  <div>
    <div class="certificate-detail">
      <h3 class="certificate-detail__title">认证对象</h3>
      <div class="certificate-user clearfix">
        <div class="certificate-user__img pull-left">
          <img
            src="https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1596102446781&di=e0009ca29f72350c4455832286c64f3a&imgtype=0&src=http%3A%2F%2Fa2.att.hudong.com%2F86%2F10%2F01300000184180121920108394217.jpg"
          />
        </div>
        <div class="certificate-user__info pull-left">
          <p>姓名：{{ certificate.truename }}</p>
          <p>用户名：houyizhen</p>
        </div>
      </div>
    </div>

    <div class="certificate-detail">
      <h3 class="certificate-detail__title">证书信息</h3>
      <div class="certificate-info">
        <p class="certificate-info__item">
          证书名称：{{ certificate.certificate.name }}
        </p>
        <p class="certificate-info__item">证书编号：ABCDEF778248308458</p>
        <p class="certificate-info__item">
          发证日期：{{ certificate.createdTime | formatSlashTime }}
        </p>
        <p class="certificate-info__item certificate-info__time">
          有效日期：<span>长期有效</span>
        </p>
        <div class="certificate-info__img">
          <img :src="certificate.qrcodeUrl" />
        </div>
      </div>
    </div>

    <div class="certificate-detail certificate-detail--border">
      <div class="certificate-detail__img">
        <img :src="certificate.imgUrl" />
      </div>
      <div
        class="certificate-detail__download"
        @click="onDownload(certificate.certificateId)"
      >
        下载证书
      </div>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { Toast } from 'vant';
export default {
  data() {
    return {
      certificate: {},
    };
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
    onDownload(id) {
      Api.certificateDownload({
        query: { recordId: id },
      })
        .then(res => {
          console.log(res);
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
  },
  created() {
    console.log('id' + this.$route.params.id);
    Api.certificateRecords({
      query: { certificateRecordId: this.$route.params.id },
    })
      .then(res => {
        this.certificate = res;
        console.log(res);
      })
      .catch(err => {
        Toast.fail(err.message);
      });
  },
};
</script>
