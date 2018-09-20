<template>
  <div class="login">
    <span class='login-title'>请将面部正对摄像头</span>
    <input @change="fileImage" type="file" capture="user" accept="image/jpeg,image/x-png,image/gif"/>
    <van-button type="default" class="primary-btn mb20" @click="onSubmitInfo">下一步</van-button>
    <span>{{uploadParams}}</span>
  </div>
</template>

<script>
  import axios from 'axios';
  import Api from '@/api';
  import { Toast } from 'vant';

export default {
  data() {
    return {
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
    onSubmitInfo() {
      this.polling();
    },

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

    fileImage(e) {
      const file = e.target.files[0];
      const formData = new FormData(); //创建form对象
      formData.append('file', file, file.name);
      formData.append('token', this.uploadParams.uploadToken);
      formData.append('key', this.uploadParams.uploadKey);
      const url = this.uploadParams.uploadUrl;
      const config = {
        headers: { 'Content-Type':'multipart/form-data'},
        interceptor: 'end',
      };    // 下面逻辑处理
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
