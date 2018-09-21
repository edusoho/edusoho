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
import axios from 'axios';
import Api from '@/api';

export default {
  data() {
    return {
      isShow: true,
      uploadParams: {},
    }
  },
  mounted() {
    const type = this.$route.params.type;
    const username = this.$route.params.loginField;
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
          var jumpAction = () => {
            this.$router.push({name: redirect});
          }
          setTimeout(jumpAction, 2000);
        } else {
          if (res.lastFailed === 1) {
            Toast.fail({
              duration: 2000,
              message: '人脸识别认证失败，多次不通过'
            });
          } else {
            Toast.fail({
              duration: 2000,
              message: '人脸识别认证失败'
            });
          }
        }
      })
    },
    openCamera(e) {
      const url = this.uploadParams.uploadUrl;
      const config = {
        headers: { 'Content-Type':'multipart/form-data'},
        interceptor: 'end',
      };
      const file = e.target.files[0];
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
            Toast.success({
              duration: 2000,
              message: '认证中，请稍候...'
            });
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
