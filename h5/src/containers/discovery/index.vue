<template>
  <div class="login">
    <span class='login-title'>确认账号</span>
    <van-field v-model="username"
      class="login-input e-input"
      placeholder="请输入邮箱/手机/用户名"/>
    <van-field v-if="faceRegistered" v-model="password"
      type="password"
      class="login-input e-input"
      :error-message="errorMessage.password"
      placeholder="请输入密码" />
    <van-button v-if="faceRegistered" type="default" class="primary-btn mb20" @click="onCheckExisted" :disabled="btnDisable">下一步</van-button>
    <van-button v-else type="default" class="primary-btn mb20" @click="onSubmitInfo" :disabled="btnSubmitDisable">下一步</van-button>
  </div>

</template>
<script>
  import { mapActions } from 'vuex';
  import { Toast } from 'vant';
  import Api from '@/api'

export default {
  data() {
    return {
      username: '',
      password: '',
      faceRegistered: 0,
      errorMessage: {
        password: ''
      }
    }
  },
  computed: {
    btnSubmitDisable() {
      return !(this.username);
    },
    btnDisable() {
      return !(this.username && this.password);
    }
  },
  methods: {
    ...mapActions([
      'userLogin',
    ]),

    onSubmitInfo() {
      Api.getUserIsExisted({
        query: {
          type: this.username,
        },
        params: {
          identifyType: 'nickname',
        }
      }).then(res => {
        if (!res.uuid) {
          Toast.fail({
            duration: 2000,
            message: '用户不存在'
          });
          return;
        };

        if (res.faceRegistered === '0') {
          this.faceRegistered = res.faceRegistered;
          Toast.fail({
            duration: 2000,
            message: '初次使用请验证密码'
          });
        } else {
          Api.getSessions({
            type: 'compare',
            loginField: this.username,
          }).then(res => {
            console.log(res);
            const upload = res.upload.form;
            this.$router.push({
              name: 'photo',
              params: {
                sessionId: res.id,
                uploadUrl: upload.action,
                uploadKey: upload.params.key,
                uploadToken: upload.params.token,
              }
            })
          }).catch(err => {
            Toast.fail(err.message);
          });
        }
      }).catch(err => {
        Toast.fail(err.message);
      });
    },

    onCheckExisted() {
      this.userLogin({
        username: this.username,
        password: this.password
      }).then(res => {
        Api.getSessions({
          type: 'register',
        }).then(res => {
          const upload = res.upload.form;
          this.$router.push({
            name: 'photo',
            params: {
              sessionId: res.id,
              uploadUrl: upload.action,
              uploadKey: upload.params.key,
              uploadToken: upload.params.token,
            }
          })
        }).catch(err => {
          Toast.fail(err.message);
        });

      }).catch(err => {
        Toast.fail(err.message);
      })
    }
  }
}
</script>
