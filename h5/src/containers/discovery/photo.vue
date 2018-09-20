<template>
  <div class="login">
    <span class='login-title'>请将面部正对摄像头</span>
    <input @change="fileImage" type="file" capture="user" accept="image/jpeg,image/x-png,image/gif"/>
    <van-button type="default" class="primary-btn mb20" @click="onSubmitInfo">下一步</van-button>
  </div>
</template>


<script>
  import axios from 'axios';
  import { mapState } from 'vuex';
  import Api from '@/api'

export default {
  computed: {
    ...mapState({
      user: state => state.user
    }),
  },

  methods: {
    onSubmitInfo() {
      console.log(this.user);
      console.log(this.$route.params);
    },

    fileImage(e) {
      const routeParams = this.$route.params;
      console.log(routeParams);
      const file = e.target.files[0];
      const formData = new FormData(); //创建form对象
      formData.append('file', file, file.name);
      formData.append('token', routeParams.uploadToken);
      formData.append('key', routeParams.uploadKey);
      const url = routeParams.uploadUrl;
      const config = {
        headers: { 'Content-Type':'multipart/form-data'},
        interceptor: 'end',
      };    // 下面逻辑处理
      axios.post(url, formData, config).then(res => {
        console.log(res);
        Api.finishUploadResult({
          query: {
            sessionId: routeParams.sessionId,
          },
          params: {
            response_body: res.data,
            response_code: res.status
          }
        }).then(res => {

        });
      }).catch(err => {
        console.log(err);
      });
    }
  }
}
</script>
