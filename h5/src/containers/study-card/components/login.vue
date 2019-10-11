<template>
  <div class="login">
    <van-action-sheet
        v-model="visible"
        title=" "
        @close="updateShow(show)"
    >
      <div class="login__container">
        <div class="receive-login-input">
          <van-field
              v-model="userinfo.mobile"
              type="number"
              placeholder="请输入手机号"
              clearable
              :border=false
              class="login__container__field"
              :error-message="errorMessage.mobile"
              @blur="validateMobileOrPsw('mobile')"
              @input="validatedChecker()"
          >
          </van-field>
        </div>
        <div class="mobile-drag" v-if="dragEnable">
          <div class="mobile-drag-content">
            <e-drag
                v-if="dragEnable"
                ref="dragComponent"
                limitType="receive_coupon"
                :key="dragKey"
                @success="handleSmsSuccess"></e-drag>
          </div>
        </div>
        <div class="receive-login-input">
          <van-field
              v-model="userinfo.smsCode"
              type="number"
              :border=false
              clearable
              maxlength=6
              ref="smsCode"
              placeholder="请输入验证码"
              class="login__container__field"
          >
            <div slot="button" @click="clickSmsBtn" :class="['code-btn',cansentCode ? '': 'code-btn--disabled']">
              <span v-show="!count.showCount">发送验证码</span>
              <span v-show="count.showCount">{{ count.num }} s</span>
            </div>
          </van-field>
        </div>
        <div :class="['receive-login__btn',btnDisable ? 'disabled__btn' : '']" @click="handleSubmit()">登录并领取</div>
        <div class="receive-login__text">
          <span class="receive-login-tools">新用户领取将为您自动注册</span>
        </div>
      </div>
    </van-action-sheet>
  </div>
</template>

<script>
  import EDrag from '@/containers/components/e-drag';
  import fastLoginMixin from '@/mixins/fastLogin';
  import { mapActions } from 'vuex';
  import { Toast } from 'vant';
  import activityMixin from '@/mixins/activity';
  import redirectMixin from '@/mixins/saveRedirect';

  export default {
    name: 'login',
    components: {
      EDrag
    },
    props: {
      show: {
        type: Boolean,
        default: false
      }
    },

    data() {
      return {
        visible: this.show,
        userinfo: {
          mobile: '',
          dragCaptchaToken: undefined, // 默认不需要滑动验证,图片验证码token
          smsCode: '',//验证码
          smsToken: '',//验证码token
          type: 'sms_login'
        },
        dragEnable: false,
        dragKey: 0,
        errorMessage: {
          mobile: '',
        },
        validated: {
          mobile: false,
        },
        count: {
          showCount: false,
          num: 60,
          codeBtnDisable: false
        },
      };
    },
    computed: {
      btnDisable() {
        return !(this.userinfo.mobile && this.userinfo.smsCode);
      },
      cansentCode() {
        return !(this.count.codeBtnDisable || !this.validated.mobile);
      }
    },
    watch: {
      show() {
        this.visible = this.show;
      }
    },
    mixins: [activityMixin, redirectMixin, fastLoginMixin],
    methods: {
      ...mapActions([
        'addUser',
        'setMobile',
        'sendSmsSend',
        'fastLogin'
      ]),
      updateShow(show) {
        this.$emit('update:show', false);
      },
      clickSmsBtn() {
        if (this.count.codeBtnDisable || !this.validated.mobile) {
          return;
        }
        if (!this.dragEnable) {
          this.handleSendSms();
          return;
        }
        // 验证码组件更新数据
        if (!this.$refs.dragComponent.dragToEnd) {
          Toast('请先完成拼图验证');
          return;
        }
        this.$refs.dragComponent.initDragCaptcha();
      },
      handleSendSms() {
        this.sendSmsSend(this.userinfo)
          .then((res) => {
            this.userinfo.smsToken = res.smsToken;
            this.countDown();
            this.dragEnable = false;
            this.userinfo.dragCaptchaToken = '';
          })
          .catch(err => {
            switch (err.code) {
              case 4030301:
              case 4030302:
                this.dragKey++;
                this.userinfo.dragCaptchaToken = '';
                this.userinfo.smsToken = '';
                Toast.fail(err.message);
                break;
              case 4030303:
                if (this.dragEnable) {
                  Toast.fail(err.message);
                } else {
                  this.dragEnable = true;
                }
                break;
              default:
                Toast.fail(err.message);
                break;
            }
          });
      },
      // 校验成功
      handleSmsSuccess(token) {
        this.userinfo.dragCaptchaToken = token;
        this.handleSendSms();
      },
      handleSubmit() {
        if(this.btnDisable){
          return
        }
        let data={
          mobile:this.userinfo.mobile,
          smsToken:this.userinfo.smsToken,
          smsCode:this.userinfo.smsCode,
        }
        this.fastLogin({
          mobile: this.userinfo.mobile,
          smsToken: this.userinfo.smsToken,
          smsCode: this.userinfo.smsCode,
          loginType: 'sms',
          client:'h5',
        }).then((res) =>{
          this.$emit('lReceiveCoupon',data);
        }).catch((err) =>{
          Toast.fail(err.message);
        })
      },
      countDown() {
        //验证码自动聚焦
        this.$nextTick(_=>{
          this.$refs.smsCode.$refs.input.focus()
        })

        this.count.showCount = true;
        this.count.codeBtnDisable = true;
        this.count.num = 60;

        const timer = setInterval(() => {
          if (this.count.num <= 0) {
            this.count.codeBtnDisable = false;
            this.count.showCount = false;
            clearInterval(timer);
            return;
          }
          // eslint-disable-next-line no-plusplus
          this.count.num--;
        }, 1000);
      },
    }
  };
</script>

<style scoped>

</style>
