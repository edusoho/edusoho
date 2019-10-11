<template>
  <div class="valid-card">
    <div class="container">
      <div class="top text-overflow">
        第一网校/休闲鞋谢谢啊晚上的期望乔卫东阿三
      </div>
      <div class="middle">
        <div class="card">
          <div class="valid-date">
            学习卡充值有效期至：{{date}}
          </div>
          <div class="money">{{money}}
            <span>金币</span>
          </div>
          <div class="code">{{formattedCode}}</div>
        </div>
        <div class="result-box">
          <div class="result-box__valid-card" v-show="!invalidCard && isLogin && !processIsDone">
            <div>将充值到当前账户：xxxx</div>
            <a href="javascript: void(0)" class="link" @click="jump2login">是否放入其他账户</a>
          </div>
          <div class="result-box__invalid-card--process-is-done" v-show="invalidCard">
            <i class="iconfont icon-Fail"></i>
            <span>充值失败</span>
            <!--充值失败时的文案有很多种，不能写死在这里-->
            <div class="res-msg">卡已失效，去看看其他精品吧</div>
          </div>
          <div class="result-box__valid-card--process-is-done" v-show="processIsDone && !invalidCard">
            <i class="result-box__icon-Success"></i>
            <span>充值成功</span>
            <div class="result-box__account">当前账户余额：{{}}金币</div>
            <div>尽情去购物啦～</div>
          </div>
        </div>
      </div>
      <div class="bottom">
        <van-button type="primary" block @click="initStatus" v-show="startProcess">立即充值</van-button>
        <van-button type="primary" block @click="submit" v-show="!startProcess && !processIsDone">立即充值</van-button>
        <van-button type="primary" block v-show="!startProcess && processIsDone">去首页</van-button>
      </div>
    </div>
    <e-login :show.sync="show"></e-login>
  </div>
</template>

<script>

  import eLogin from './login';
  import { Dialog } from 'vant';
  import { mapState } from 'vuex';
  export default {
    name: 'valid-card',
    components: {
      eLogin
    },
    data() {
      return {
        //卡的有效期
        date: '2019年10月31日',
        //卡的金额
        money: 1000,
        // 卡密
        code: '1231232132showMe',
        // 是否显示登录的菜单
        show: false,
        // invalidCard 为 true 的时候表示卡是无效的，这个时候流程该结束了
        invalidCard: false,
        // isLogin 为 true 的时候表示用户已经登录
        isLogin: false,
        startProcess: true,
        // processIsDone 为 true 的时候表示充值卡的流程已经结束，之后就是显示失败或成功的内容
        processIsDone: false,
      };
    },
    computed: {
      ...mapState({
        nickname: 'nickname',
      }),
      // 格式化成每4个空一个的样式
      formattedCode() {
        return this.code.toString()
          .replace(/\W/g, '')
          .replace(/....(?!$)/g, '$& ');
      }
    },
    methods: {
      initStatus() {
        this.startProcess = false;
        //先校验卡是否有效，有效的话判断是否登录，卡无效的话就显示卡校验失败的页面。
        // @todo
        //如果卡无效,立即充值变成去首页
        // @todo
        // this.invalidCard = true;
        //卡有效
        // @todo
        // 如果用户登录
        if (this.$store.state.token) {
          this.isLogin = true;
          return;
        }

        this.isLogin = false;
        this.show = true;
      },

      submit() {
        // 卡有效且用户已经登录的情况
        if (!this.invalidCard && this.isLogin) {
          //  @todo
          Dialog.confirm({
            title: '将充值到当前登录账户',
            message: '1879871798798',
            cancelButtonText: '充值其他账户',
          })
            .then(() => {
              this.processIsDone = true;
            })
            .catch(() => {
              this.show = true;
            });
        }

        //卡有效但用户没有登录的情况
        if (!this.invalidCard && !this.isLogin) {
          this.show = true;
        }
      },
      jump2login() {
        this.show = true;
      }
    },

  };
</script>

<style scoped>

</style>
