<template>
  <div class="login-face-verification">
    <div v-if="!errorShow">
      <div v-show="tipShow" class="verification-tips">
        <div>即将进行人脸识别{{ verifiedText }}</div>
        <div class="mt5">请将面部正对摄像头</div>
      </div>
      <div v-if="!failTextShow" v-show="!tipShow">
        <img class="img-content" :src="imgAddress" alt="人脸照片">
        <div>{{ verifiedText }}中，请稍候...</div>
      </div>
      <div v-show="failTextShow">人脸识别多次{{ verifiedText }}不通过<div class="mt5">请改用其它方式{{ verifiedText }}或联系管理员</div></div>
      <div v-show="tipShow">
        <label for="cameraItem" class="btn-open-camera">{{ btnText }}</label>
        <input id="cameraItem" class="hide" type="file" accept="image/*" @change="openCamera" capture="user">
      </div>
    </div>
    <div v-if="errorShow">
      此链接已失效<div class="mt5">请确认后再操作</div>
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
      requestStartT: '',
      requestEndT: '',
      verifiedText: '认证',
      errorShow: false,
      scanCode: this.$route.query.loginToken
    }
  },
  mounted() {
    if (this.$route.query.faceRegistered == 1) {
      this.verifiedText = '设置';
    }
    const data = {
      'type': this.$route.query.type,
      'loginField': this.$route.query.loginField,
      'loginToken': this.scanCode
    }
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
      this.errorShow = true;
      setTimeout(this.feedbackAction, 3000);
    })
  },
  methods: {
    polling() {
      const self = this;
      Api.faceSession({
        query: {
          sessionId: this.uploadParams.sessionId,
        },
        params: {
          loginToken: this.scanCode
        }
      }).then(res => {
        console.log(res.status);
        if (res.status === 'processing') {
          if (!this.requestStartT) {
            this.requestStartT = new Date();
          } else {
            this.requestEndT = new Date();
          }

          const duration = this.requestEndT ? this.requestEndT - this.requestStartT : 0;
          if (duration > 58000) {
            self.recognitionFail();
            return;
          }

          setTimeout(() => {
            self.polling();
          }, 2000);

        } else if (res.status === 'successed') {
          Toast.success({
            duration: 2000,
            message: '人脸识别成功'
          });
          if (this.scanCode) {
            setTimeout(this.feedbackAction, 3000);
            return;
          }
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
              message: `人脸识别${this.verifiedText}失败，多次不通过`
            });
            if (this.scanCode) {
              setTimeout(this.feedbackAction, 3000);
              return;
            }
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
    isWeixin(){
      const ua = navigator.userAgent.toLowerCase();
      return (ua.match(/MicroMessenger/i) == 'micromessenger') ? true : false;
    },
    feedbackAction() {
      if (!this.isWeixin()) {
        this.$router.back(-1);
        return;
      }
      WeixinJSBridge.call('closeWindow');
    },
    recognitionFail() {
      Toast.fail({
        duration: 2000,
        message: `人脸识别${this.verifiedText}失败`
      });
      if (this.scanCode) {
        setTimeout(this.feedbackAction, 3000);
        return;
      }
      this.tipShow = true;
      this.btnText = `重新${this.verifiedText}`
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
          params: {
            loginToken: this.scanCode
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
