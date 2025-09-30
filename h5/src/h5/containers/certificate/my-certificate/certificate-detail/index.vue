<template>
  <div>
    <e-loading v-if="isLoading" />
    <div class="certificate-detail">
      <h3 class="certificate-detail__title">{{ $t('certificate.certifiedObject') }}</h3>
      <div class="certificate-user clearfix">
        <div class="certificate-user__img pull-left">
          <img v-if="userInfo.avatar" :src="userInfo.avatar.small" />
        </div>
        <div class="certificate-user__info pull-left">
          <p>{{ $t('certificate.name') }}：{{ certificate.truename }}</p>
          <p>{{ $t('certificate.username') }}：{{ userInfo.nickname }}</p>
        </div>
      </div>
    </div>

    <div class="certificate-detail">
      <h3 class="certificate-detail__title">{{ $t('certificate.information') }}</h3>
      <div class="certificate-info">
        <p class="certificate-info__item" v-if="certificate.certificate">
          {{ $t('certificate.certificateName') }}：{{ certificate.certificate.name }}
        </p>
        <p class="certificate-info__item">
          {{ $t('certificate.certificateNumber') }}：{{ certificate.certificateCode }}
        </p>
        <p class="certificate-info__item">
          {{ $t('certificate.getTime2') }}：{{ certificate.issueTime | formatSlashTime }}
        </p>
        <p class="certificate-info__item certificate-info__time">
          {{ $t('certificate.effectiveTime2') }}：<span
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
        <div class="certificate-info__img">
          <img :src="certificate.qrcodeUrl" />
        </div>
      </div>
    </div>

    <div class="certificate-detail certificate-detail--border">
      <div class="certificate-detail__img">
        <img :src="certificate.imgUrl" />
      </div>
      <a v-if="isUser" class="certificate-detail__download" href="javascript:;">
        {{ $t('certificate.longPressToSaveThePicture') }}
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
      userInfo: {},
      isUser: false,
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
    getUserInfo(userId) {
      Api.getCertificateUserInfo({
        query: { userId: userId },
      }).then(res => {
        this.userInfo = res;
        if (res.nickname == this.user.nickname) {
          this.isUser = true;
        } else {
          this.isUser = false;
        }
      });
    },
  },
  created() {
    Api.certificateRecords({
      query: { certificateRecordId: this.$route.params.id },
    })
      .then(res => {
        this.certificate = res;
        this.setNavBarTitle(res.certificate.name);
        this.getUserInfo(res.userId);
      })
      .catch(err => {
        Toast.fail(err.message);
      });
  },
};
</script>
