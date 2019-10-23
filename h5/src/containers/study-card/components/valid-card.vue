<template>
  <div class="valid-card">
    <e-loading v-if="isLoading !== 2"></e-loading>
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
            <span>{{coin}}</span>
          </div>
          <div class="password">{{formattedPassword}}</div>
        </div>
        <div class="result-box">
          <div class="result-box__valid-card" v-show="!initProcess && !invalidCard && this.isLogin && !processIsDone">
            <div>将充值到当前账户：{{account}}</div>
            <a href="javascript: void(0)" class="link" @click="jump2login">是否放入其他账户</a>
          </div>
          <div class="result-box__invalid-card--process-is-done" v-show="invalidCard">
            <div class="icon"><i class="iconfont icon-Fail"></i>
              <span>充值失败</span></div>
            <div class="res-msg" v-html="message"></div>
          </div>
          <div class="result-box__valid-card--process-is-done" v-show="processIsDone && !invalidCard">
            <div class="icon"><i class="result-box__icon-Success"></i>
              <span>充值成功</span></div>
            <div class="result-box__account">当前账户余额：{{cash}}{{coin}}</div>
            <div>尽情去购物啦～</div>
          </div>
        </div>
      </div>
      <div class="bottom">
        <van-button type="primary" block @click="switchSubmit" v-show="!processIsDone">立即充值</van-button>
        <van-button type="primary" block v-show="!initProcess && processIsDone" to="/">去首页</van-button>
      </div>
    </div>
    <e-login :show.sync="show" @submit="switchUser2charge"></e-login>
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
        coin: '',
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
        initProcess: false,
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
          'recharged': this.rechargedMsg,
          'usedByOther': '卡已被其他人充值，去看看其他精品吧～',
          'empty': '卡已被抢完，去看看其他精品吧～',
        },
        isLoading: 0
      };
    },
    computed: {
      ...mapState({
        account: state => state.user['verifiedMobile'] || state.user.nickname,
        isLogin: state => !!state.token,
        settingsName: state => state.settings.name,
        userToken: state => state.token,
        userId: state => state.user.id
      }),
      // 格式化成每4个空一个的样式
      formattedPassword() {
        return this.password.toString()
          .replace(/\W/g, '')
          .replace(/....(?!$)/g, '$& ');
      },
      rechargedMsg() {
        return 'xxxx';
      }
    },
    created() {
      document.title = '学习卡充值';
      // 根据token获取卡批次信息
      this.token = this.$route.params.token || '';
      this.password = this.$route.params.password || '';
      // isECard 为 true 的时候，根据 token 获取信息，为 false 根据 password 获取信息
      this.isECard = !!this.token.length;
      this.init();
    },
    methods: {
      async init() {
        await this.getCoin();
        await this.getCash();
        if (this.isECard === true) {
          this.getMoneyCardByToken();
        } else {
          this.getMoneyCardByPassword();
        }
      },
      getCoin() {
        return Api.getCoin()
          .then(res => {
            this.coin = res.name;
            this.isLoading += 1;
          })
          .catch(err => console.log(err));
      },
      switchCharge(name, query) {
        this.isLoading -= 1;
        Api[name]({
          query: { [query]: this[query] },
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Auth-Token': this.userToken
          }
        })
          .then(res => {
            this.isLoading += 1;
            if (res.cardPassword) {
              this.password = res.cardPassword;
            }
            if (res.success === true) {
              this.invalidCard = false;
              this.message = res.message;
              this.cash = res.cash;
            } else {
              this.invalidCard = true;
              this.message = res.error.message;
              if (res.error.status === 'recharged') {
                this.message = this.cardStatusList['recharged']
              }
            }
            this.processIsDone = true;
          })
          .catch(err => {
            console.log(err);
          });
      },
      switchSubmit() {
        this.initProcess ? this.initStatus() : this.submit();
      },
      switchUser2charge() {
        this.isECard === true ?
          this.chargeMoneyCardByToken() : this.chargeMoneyCardByPassword();
      },
      initStatus() {
        this.initProcess = false;
        //先校验卡是否有效，有效的话判断用户是否登录，卡无效的话就显示卡校验失败的页面。
        if (this.invalidCard) {
          //卡无效,流程结束
          this.processIsDone = true;
          return;
        }
        //用户没有登录就先去登录
        !this.isLogin && this.jump2login();
      },
      getCash() {
        return Api.getCash({
          query: {
            userId: this.userId
          }
        })
          .then(res => {
            this.cash = res.cash;
            this.cardStatusList['recharged'] =
              '之前已充值，当前账户余额：' + res.cash + this.coin + '<br/>尽情去购物啦～';
          })
          .catch(err => console.log(err));
      },
      getMoneyCardByToken() {
        Api.getMoneyCardByToken({
          query: { token: this.token }
        })
          .then(res => {
            this.isLoading += 1;
            this.date = res.deadline;
            this.money = res.coin;
            if (res.batchStatus === 'normal') return;
            this.message = this.cardStatusList[res.batchStatus];
            this.initProcess = false;
            this.invalidCard = true;
            this.processIsDone = true;
          })
          .catch(err => {
            // 第一次进页面的时候如果卡密无效就直接去首页
            this.$router.push('/');
          });
      },
      getMoneyCardByPassword() {
        Api.getMoneyCardByPassword({
          query: { password: this.password }
        })
          .then(res => {
            this.isLoading += 1;
            this.date = res.deadline;
            this.money = res.coin;
            this.code = res.password;
            if (res.cardStatus === 'normal' ||
              (res.rechargeUserId === this.userId && res.cardStatus === 'receive')) {
              return;
            }
            res.cardStatus = res.rechargeUserId !== this.userId &&
            (res.cardStatus === 'recharged' || res.cardStatus === 'receive') ?
              'usedByOther' : res.cardStatus;
            this.message = this.cardStatusList[res.cardStatus];
            this.initProcess = false;
            this.invalidCard = true;
            this.processIsDone = true;
          })
          .catch(err => {
            // 第一次进页面的时候如果卡密无效就直接去首页
            this.$router.push('/');
          });
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
      jump2login() {
        this.show = true;
      },
      chargeMoneyCardByToken() {
        this.switchCharge('chargeMoneyCardByToken', 'token');
      },
      chargeMoneyCardByPassword() {
        this.switchCharge('chargeMoneyCardByPassword', 'password');
      },
    },
  };
</script>

