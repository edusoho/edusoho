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
      </div>
      <div class="bottom">
        <van-button type="primary" block @click="submit">{{btnText}}</van-button>
      </div>
    </div>
    <e-login :show.sync="show"></e-login>
  </div>
</template>

<script>

  import eLogin from './login';
  export default {
    name: 'entity-cards',
    components: {
      eLogin
    },
    data() {
      return {
        date: '2019年10月31日',
        money: 1000,
        code: '1231232132showMe',
        show: false,
        btnText: '立即充值'
      };
    },
    methods: {
      submit() {
        //先校验卡是否有效，有效的话判断是否登录，卡无效的话就显示卡校验失败的页面。

        //如果卡无效

        //卡有效

        // 如果没有登录
        this.show = true;

        //如果登录了

      }
    },
    computed: {
      formattedCode() {
        return this.code.toString().replace(/\W/g, '')
          .replace(/....(?!$)/g, '$& ');
      }
    }
  };
</script>

<style scoped>

</style>
