<template>
  <div>
    <e-loading v-if="isLoading" />
    <div class="certificate-detail">
      <h3 class="certificate-detail__title">认证对象</h3>
      <div class="certificate-user clearfix">
        <div class="certificate-user__img pull-left">
          <img v-if="user.avatar" :src="user.avatar.small" />
        </div>
        <div class="certificate-user__info pull-left">
          <p>姓名：{{ certificate.truename }}</p>
          <p>用户名：{{ user.nickname }}</p>
        </div>
      </div>
    </div>

    <div class="certificate-detail">
      <h3 class="certificate-detail__title">证书信息</h3>
      <div class="certificate-info">
        <p class="certificate-info__item" v-if="certificate.certificate">
          证书名称：{{ certificate.certificate.name }}
        </p>
        <p class="certificate-info__item">
          证书编号：{{ certificate.certificateCode }}
        </p>
        <p class="certificate-info__item">
          发证日期：{{ certificate.issueTime | formatSlashTime }}
        </p>
        <p class="certificate-info__item certificate-info__time">
          有效日期：<span
            v-if="certificate.expiryTime == 0"
            class="item-right__time--green"
            >长期有效</span
          ><span
            v-else-if="certificate.status == 'expired'"
            class="item-right__time--red"
            >{{ certificate.expiryTime | formatSlashTime }}
            <span>已过期</span>
          </span>
          <span
            v-else-if="certificate.status == 'valid'"
            class="item-right__time--green"
            >{{ certificate.expiryTime | formatSlashTime }}
            <span>有效中</span></span
          >
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
      <a
        class="certificate-detail__download"
        :href="certificate.imgUrl"
        :download="
          certificate.certificate ? certificate.certificate.name + '.png' : ''
        "
      >
        下载证书
      </a>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { Toast } from 'vant';
import * as types from '@/store/mutation-types';
import { mapMutations, mapState } from 'vuex';

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
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
    }),
    ...mapState(['user']),
  },
  methods: {
    ...mapMutations({
      setNavBarTitle: types.SET_NAVBAR_TITLE,
    }),
  },
  created() {
    Api.certificateRecords({
      query: { certificateRecordId: this.$route.params.id },
    })
      .then(res => {
        this.certificate = res;
        this.setNavBarTitle(res.certificate.name);
      })
      .catch(err => {
        Toast.fail(err.message);
      });
  },
};
</script>
