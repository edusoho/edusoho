<template>
  <div class="valid-card">
    <e-loading v-if="isLoading"></e-loading>
    <div class="container">
      <div class="top text-overflow">
        {{settingsName}}
      </div>
      <div class="middle">
        <div class="card">
          <div class="valid-date">
            学习卡充值有效期至：{{date}}
          </div>
          <div class="money">{{money}}
            <span>金币</span>
          </div>
          <div class="password">{{formattedPassword}}</div>
        </div>
        <div class="result-box">
          <div class="result-box__valid-card" v-show="!initProcess && !invalidCard && this.isLogin && !processIsDone">
            <div>将充值到当前账户：{{account}}</div>
            <a href="javascript: void(0)" class="link" @click="jump2login">是否放入其他账户</a>
          </div>
          <div class="result-box__invalid-card--process-is-done" v-show="invalidCard">
            <i class="iconfont icon-Fail"></i>
            <span>充值失败</span>
            <div class="res-msg">{{message}}</div>
          </div>
          <div class="result-box__valid-card--process-is-done" v-show="processIsDone && !invalidCard">
            <i class="result-box__icon-Success"></i>
            <span>充值成功</span>
            <div class="result-box__account">当前账户余额：{{cash}}金币</div>
            <div>尽情去购物啦～</div>
          </div>
        </div>
      </div>
      <div class="bottom">
        <van-button type="primary" block @click="initStatus" v-show="initProcess">立即充值</van-button>
        <van-button type="primary" block @click="submit" v-show="!initProcess && !processIsDone">立即充值</van-button>
        <van-button type="primary" block v-show="!initProcess && processIsDone" to="/">去首页</van-button>
      </div>
    </div>
    <e-login :show.sync="show" @submit="switchUser2submit"></e-login>
  </div>
</template>

<script>

  import eLogin from './login';
  import { Dialog } from 'vant';
  import { mapState } from 'vuex';
  import Api from '@/api';

  export default {
    name: 'valid-card',
    components: {
      eLogin
    },
    data() {
      return {
        //卡的有效期
        date: '',
        //卡的金额
        money: 0,
        // 卡密
        password: '',
        // 是否显示登录的菜单
        show: false,
        // invalidCard 为 true 的时候表示卡是无效的，这个时候流程该结束了
        invalidCard: false,
        initProcess: true,
        // processIsDone 为 true 的时候表示充值卡的流程已经结束，之后就是显示失败或成功的内容
        processIsDone: false,
        // 充值后返回的信息
        message: '',
        isECard: undefined,
        // 学习卡的token，不是用户的token， 用户token是userToken
        token: '',
        cash: 0,
        cardStatusList: {
          'expired': '卡已过期，去看看其他精品吧～',
          'invalid': '卡已失效，去看看其他精品吧～',
          'recharged': '之前已充值，去看看其他精品吧～',
          'usedByOther': '卡已被其他人充值，去看看其他精品吧～',
          'failed': '卡已被抢完，去看看其他精品吧～'
        }
      };
    },
    computed: {
      ...mapState({
        account: state => state.user['verifiedMobile'] || state.user.nickname,
        isLogin: state => !!state.token,
        settingsName: state => state.settings.name,
        userToken: state => state.token,
        isLoading: state => state.isLoading
      }),
      // 格式化成每4个空一个的样式
      formattedPassword() {
        return this.password.toString()
          .replace(/\W/g, '')
          .replace(/....(?!$)/g, '$& ');
      }
    },
    created() {
      // 根据token获取卡批次信息
      this.token = this.$route.params.token || '';
      this.password = this.$route.params.password || '';
      if (this.token.length) {
        this.isECard = true;
        Api.getMoneyCardByToken({
          query: { token: this.token }
        })
          .then(res => {
            this.date = res.deadline;
            this.money = res.coin;
            if (res.cardStatus === 'normal') return;
            this.message = this.cardStatusList[res.batchStatus];
            console.log(res);
            this.initProcess = false;
            this.invalidCard = true;
            this.processIsDone = true;
          })
          .catch(err => {
            this.initProcess = false;
            this.invalidCard = true;
            this.processIsDone = true;
            this.message = err.message;
          });
        return;
      }

      this.isECard = false;
      Api.getMoneyCardByPassword({
        query: { password: this.password }
      })
        .then(res => {
          this.date = res.deadline;
          this.money = res.coin;
          this.code = res.password;
          if (res.cardStatus === 'normal') return;
          this.message = this.cardStatusList[res.cardStatus];
          this.initProcess = false;
          this.invalidCard = true;
          this.processIsDone = true;
        })
        .catch(err => {
          this.initProcess = false;
          this.invalidCard = true;
          this.processIsDone = true;
          this.message = err.message;
        });
    },
    methods: {
      initStatus() {
        this.initProcess = false;
        //先校验卡是否有效，有效的话判断用户是否登录，卡无效的话就显示卡校验失败的页面。
        if (this.invalidCard) {
          //卡无效,立即充值变成去首页
          this.processIsDone = true;
          return;
        }
        //卡有效
        !this.isLogin && this.jump2login();
      },

      submit() {
        // 卡有效且用户已经登录的情况
        if (!this.invalidCard && this.isLogin) {
          Dialog.confirm({
            title: '将充值到当前登录账户',
            message: this.account,
            cancelButtonText: '充值其他账户',
          })
            .then(() => {
              // 判断是电子卡还是实体卡
              this.isECard === true ?
                this.chargeMoneyCardByToken() : this.chargeMoneyCardByPassword();
            })
            .catch(() => {
              this.jump2login();
            });
        }

        //卡有效但用户没有登录的情况
        if (!this.invalidCard && !this.isLogin) {
          this.jump2login();
        }
      },
      switchUser2submit() {
        this.isECard === true ?
          this.chargeMoneyCardByToken() : this.chargeMoneyCardByPassword();
      },
      jump2login() {
        this.show = true;
      },
      chargeMoneyCardByToken() {
        Api.chargeMoneyCardByToken({
          query: { token: this.token },
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Auth-Token': this.userToken
          }
        })
          .then(res => {
            if (res.success === true) {
              this.invalidCard = false;
              this.message = res.message;
              this.cash = res.cash;
            } else {
              this.invalidCard = true;
              this.message = res.error.message;
            }
            this.processIsDone = true;
          })
          .catch(err => {
            console.log(err);
          });
      },
      chargeMoneyCardByPassword() {
        Api.chargeMoneyCardByPassword({
          query: { password: this.password },
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Auth-Token': this.userToken
          }
        })
          .then(res => {
            if (res.success === true) {
              this.invalidCard = false;
              this.message = res.message;
              this.cash = res.cash;
            } else {
              this.invalidCard = true;
              this.message = res.error.message;
            }
            this.processIsDone = true;
          })
          .catch(err => {
            console.log(err);
          });
      },
    },

  };
</script>

<style scoped>

</style>
