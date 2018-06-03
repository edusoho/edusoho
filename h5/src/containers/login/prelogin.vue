<template>
  <div class="prelogin">
    <img class='prelogin-img' src="/static/images/noLoginEmpty.png"></image>
    <span class='prelogin-text'>登录后查看更多信息</span>
     <router-link to="/login">
       <van-button type="default" 
        class="prelogin-btn">立即登录
      </van-button>
     </router-link>
  </div>
</template>