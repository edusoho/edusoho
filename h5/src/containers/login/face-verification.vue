<template>
  <div class="login-face-verification">
    <div v-show="tipShow" class="verification-tips">
      <div>即将进行人脸识别认证</div>
      <div class="mt5">请将面部正对摄像头</div>
    </div>
    <div v-show="imgShow">
      <img id="imgContent" alt="">
      <div>认证中，请稍后...</div>
    </div>
    <div v-show="failText">人脸识别多次认证不通过<div class="mt5">请改用其它方式认证或联系管理员</div></div>
    <div v-show="btnShow">
      <label for="cameraItem" class="btn-open-camera">{{ btnText }}</label>
      <input id="cameraItem" class="hide" type="file" accept="image/*" @change="openCamera" capture="user">
    </div>
  </div>
</template>
<script>
import { mapActions } from 'vuex';
import { Toast } from 'vant';
import axios from 'axios';
import Api from '@/api';

export default {
  data() {
    return {
      tipShow: true,
      btnShow: true,
      imgShow: false,
      failText: false,
      btnText: '立即开启摄像头',
      uploadParams: {},
    }
  },
  mounted() {
    const data = this.$route.params;
    Api.getSessions({
      data: data
    }).then(res => {
      const upload = res.upload.form;
      this.uploadParams = {
        sessionId: res.id,
        uploadUrl: upload.action,
        uploadKey: upload.params.key,
        uploadToken: upload.params.token,
      }
      console.log(this.uploadParams);
    }).catch(err => {
      Toast.fail(err.message);
    });
  },
  methods: {
    polling() {
      const self = this;
      Api.faceSession({
        query: {
          sessionId: this.uploadParams.sessionId,
        },
      }).then(res => {
        console.log(res.status);
        if (res.status === 'processing') {
          setTimeout(() => {
            self.polling();
          }, 2000);
        } else if (res.status === 'successed') {
          Toast.success({
            duration: 2000,
            message: '人脸识别成功'
          });
          const redirect = decodeURIComponent(this.$route.query.redirect || 'find');
          const jumpAction = () => {
            this.$router.push({name: redirect});
          }
          setTimeout(jumpAction, 3000);
        } else {
          if (res.lastFailed === 1) {
            Toast.fail({
              duration: 2000,
              message: '人脸识别认证失败，多次不通过'
            });
            this.failText = true;
            this.btnShow = false;
            this.imgShow = false;
            const toLogin = () => {
              this.$router.push({
                name: 'login',
                query: {
                  redirect: this.$route.query.redirect || ''
                }
              });
            }
            setTimeout(toLogin, 2000);
          } else {
            Toast.fail({
              duration: 2000,
              message: '人脸识别认证失败'
            });
            this.btnShow = true;
            this.tipShow = true;
            this.btnText = '重新认证';
            this.imgShow = false;
          }
        }
      })
    },
    openCamera(e) {
      this.imgShow = true;
      const file = e.target.files[0];
      const reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onloadend= (e) => {
        document.getElementById('imgContent').src = e.target.result;
        this.tipShow = false;
        this.btnShow = false;
      };

      const url = this.uploadParams.uploadUrl;
      const config = {
        headers: { 'Content-Type':'multipart/form-data'},
        interceptor: 'end',
      };

      const formData = new FormData();
      formData.append('file', file, file.name);
      formData.append('token', this.uploadParams.uploadToken);
      formData.append('key', this.uploadParams.uploadKey);
      axios.post(url, formData, config).then(res => {
        const data = {
          query: {
            sessionId: this.uploadParams.sessionId,
          },
          data: {
            response_body: JSON.stringify(res.data),
            response_code: res.status
          }
        };
        Api.finishUploadResult(data).then(res => {
          if (res.success) {
            // Toast.success({
            //   duration: 2000,
            //   message: '认证中，请稍候...'
            // });
            this.imgShow = true;
            this.btnShow = false;
            this.polling();
          } else {
            console.log(res.error.message);
          }
        }).catch(err => {
          console.log(err);
        });
      }).catch(err => {
        console.log(err);
      });
    }
  }
}
</script>
