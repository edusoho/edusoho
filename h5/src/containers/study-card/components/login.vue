<template>
  <div class="login">
    <van-action-sheet
        v-model="visible"
        title=" "
        @close="updateShow(show)"
    >
      <div class="login__container">
        <van-field
            v-model="phoneNumber"
            type="text"
            placeholder="请输入手机号"
            class="login__container__field"
            right-icon="1"
            @input="handlePhoneNumber"
        >
        </van-field>
        <div class="err-msg">手机号输入错误</div>

        <van-field
            v-model="verifyCode"
            center
            clearable
            placeholder="请输入验证码"
            class="login__container__field"
        >
          <span
              slot="button"
              class="login__container__button"
          >
            获取验证码
          </span>
        </van-field>
      </div>
    </van-action-sheet>
  </div>
</template>

<script>
  export default {
    name: 'login',
    props: {
      show: {
        type: Boolean,
        default: false
      }
    },
    data() {
      return {
        visible: this.show,
        phoneNumber: '',
        verifyCode: ''
      };
    },
    watch: {
      show() {
        this.visible = this.show;
      }
    },
    methods: {
      updateShow(show) {
        this.$emit('update:show', false);
      },
      handlePhoneNumber() {

      }
    }
  };
</script>

<style scoped>

</style>
