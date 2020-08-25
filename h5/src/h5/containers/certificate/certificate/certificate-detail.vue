<template>
  <div class="course-certificate-detail">
    <e-loading v-if="isLoading" />

    <div class="ccd-item">
      <h3 class="ccd-item__title">证书名称</h3>
      <div class="ccd-item__body">
        {{ certificate.name }}
        <span class="acquired" v-if="certificate.isObtained">已获取</span>
        <span class="obtain" v-else>待获取</span>
      </div>
    </div>

    <div class="ccd-item">
      <h3 class="ccd-item__title">证书简介</h3>
      <div class="ccd-item__body" v-html="certificate.description">
        <!-- <div class="ccd-item__body__img">
          <img
            src="https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1596534569057&di=3e6089efae55b6a71a09c5ae4bb16c75&imgtype=0&src=http%3A%2F%2Fa1.att.hudong.com%2F05%2F00%2F01300000194285122188000535877.jpg"
            alt=""
          />
        </div>
        <div class="ccd-item__body__content">
          证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介我是简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介证书简介
        </div> -->
      </div>
    </div>

    <div class="ccd-item">
      <h3 class="ccd-item__title">获取途径</h3>
      <div class="ccd-item__body" v-if="certificate.targetType == 'course'">
        通过参加{{ certificate.course.courseSetTitle }}可以获得。
      </div>
      <div class="ccd-item__body" v-if="certificate.targetType == 'classroom'">
        通过参加{{ certificate.classroom.title }}可以获得。
      </div>
    </div>

    <div class="ccd-item ccd-item--noborder">
      <h3 class="ccd-item__title">获取条件</h3>
      <div class="ccd-item__body" v-if="certificate.targetType == 'course'">
        完成课程全部任务
      </div>
      <div class="ccd-item__body" v-if="certificate.targetType == 'classroom'">
        完成班级全部任务
      </div>
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
  created() {
    Api.certificatesDetail({
      query: { certificateId: this.$route.params.id },
    })
      .then(res => {
        this.certificate = res;
        this.setNavBarTitle(res.name);
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
  methods: {
    ...mapMutations({
      setNavBarTitle: types.SET_NAVBAR_TITLE,
    }),
  },
};
</script>
