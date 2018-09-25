<template>
  <div class="login-face-verification">
    <div v-show="tipShow" class="verification-tips">
      <div>即将进行人脸识别认证</div>
      <div class="mt5">请将面部正对摄像头</div>
    </div>
    <div v-if="!failTextShow" v-show="!tipShow">
      <img class="img-content" :src="imgAddress" alt="人脸照片">
      <div>认证中，请稍候...</div>
    </div>
    <div v-show="failTextShow">人脸识别多次认证不通过<div class="mt5">请改用其它方式认证或联系管理员</div></div>
    <div v-show="tipShow">
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
import * as types from '@/store/mutation-types';

export default {
  data() {
    return {
      tipShow: true,
      failTextShow: false,
      imgAddress: '',
      btnText: '立即开启摄像头',
      uploadParams: {},
      seconds: 60
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
          var timer = window.setInterval(function () {
            if (self.seconds > 0) {
              self.seconds = self.seconds - 1
            } else {
              self.recognitionFail();
              window.clearInterval(timer);
            }
          },1000);

        } else if (res.status === 'successed') {
          Toast.success({
            duration: 2000,
            message: '人脸识别成功'
          });
          if (res.login) {
            const avatarData = {
              avatar: {
                large: res.login.largeAvatar,
                medium: res.login.mediumAvatar,
                small: res.login.smallAvatar,
              }
            }
            const loginUser = Object.assign(res.login.user, avatarData);
            this.$store.commit(types.USER_LOGIN, {
              token: res.login.token,
              user: loginUser,
            });
          }
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
            this.failTextShow = true;
            this.tipShow = false;
            const toLogin = () => {
              this.$router.push({
                name: 'login',
                query: {
                  redirect: this.$route.query.redirect || ''
                }
              });
            }
            setTimeout(toLogin, 3000);
          } else {
            this.recognitionFail();
          }
        }
      })
    },
    recognitionFail() {
      Toast.fail({
        duration: 2000,
        message: '人脸识别认证失败'
      });
      this.tipShow = true;
      this.btnText = '重新认证';
    },
    openCamera(e) {
      const file = e.target.files[0];
      const reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onloadend= (e) => {
        this.imgAddress = e.target.result;
        this.tipShow = false;
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
            this.tipShow = false;
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
