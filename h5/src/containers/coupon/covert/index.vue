<template>
  <div class="coupon-covert-container">
    <h2 class="title">兑换卡券</h2>
    <van-field
      v-model.trim="code"
      :class="[{'error-code': isErrorCode}, 'e-input', 'coupon-input']"
      placeholder="请输入8位兑换码"
      clearable
      v-on:input="checkCode(code)"/>
      <span v-if="isErrorCode" class="error-code text-14">{{errorText}}</span>
    <van-button type="info" class="covert-submit" :disabled="!code || isErrorCode" @click="codeCovert(code)">确认</van-button>
    <van-popup v-model="popupShow" class="coupon-covert-popup" :closeOnClickOverlay="closeOnClickOverlay">
      <div class="modal-content">
        兑换成功<div>恭喜您获得{{courseTitle}}课程</div>
      </div>
      <div class="color-primary mt5 text-14" @click="toStudy">去学习</div>
    </van-popup>
  </div>
</template>
<script>
import Api from '@/api';

export default {

  data() {
    return {
      code: '',
      popupShow: false,
      closeOnClickOverlay: false,
      btnDisable: false,
      isErrorCode: false,
      errorText: '',
      courseId: '',
      courseTitle: ''
    };
  },

  methods: {
    checkCode(code) {
      if (code.length > 7) {
        const reg = /^[a-z0-9A-Z]{8}$/;
        const correctCode = reg.test(code);
        if (!correctCode) {
          this.isErrorCode = true;
          this.errorText = '8位数字、英文字母组成';
          return
        }
        this.isErrorCode = false
      }
      if (!code.length) {
        this.isErrorCode = false
      }
    },
    codeCovert(code) {
      Api.couponCheck({
        query: {
          code: code
        }
      }).then(res => {
        console.log(res, 'couponCheck res')
        if (res.success === false || res.error) {
          this.isErrorCode = true;
          this.errorText = res.error.message;
          return;
        }
        Api.exchangeCoupon({
          query: {
            code: code
          }
        }).then(res => {
          console.log('exchangeCoupon', res)
          this.courseId = res.products[0].course.id;
          this.courseTitle = res.products[0].course.displayedTitle;
          this.popupShow = true;
        }).catch(error => {
          this.isErrorCode = true;
          this.errorText = error.message;
        })
      })
    },
    toStudy() {
      const courseId = this.courseId;
      if (courseId) {
        // 跳转详情页后可直接返回到我的页面
        const myUrl = encodeURIComponent('/my/orders');
        this.$router.push({
          path: `/course/${courseId}?backUrl=${myUrl}`,
        });
      }
    }
  }
}
</script>

