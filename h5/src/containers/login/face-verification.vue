<template>
  <div class="login-face-verification">
    <div class="verification-tips">
      <div>即将进行人脸识别认证</div>
      <div class="mt5">请将面部正对摄像头</div>
    </div>
    <label for="cameraItem" class="btn-open-camera">立即开启摄像头</label>
    <input id="cameraItem" class="hide" type="file" accept="image/*" @change="openCamera" capture="user">
  </div>
</template>
<script>
import { mapActions } from 'vuex';
import { Toast } from 'vant';

export default {
  data() {
    return {
      isShow: true,
      faceImg: ''
    }
  },
  methods: {
    openCamera() {
      var faceImg = document.getElementById('cameraItem').files;
      if (faceImg.length) {
        this.faceImg = faceImg[0];
      }
    }
  }
}
</script>
