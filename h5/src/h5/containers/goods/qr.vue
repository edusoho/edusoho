<template>
  <div class="goods-qr">
    <div class="goods-qr__header">
      <img src="static/images/qr_bg.png" alt="" />
    </div>
    <div class="goods-qr__body">
      <h3 class="goods-qr__body__title">{{ this.$route.query.title }}</h3>
      <p class="goods-qr__body__subtitle">{{ this.$route.query.content }}</p>
      <div class="goods-qr__body__img">
        <img :src="this.$route.query.imageUrl" alt="" />
        <span class="qr-border qr-border-tl"></span>
        <span class="qr-border qr-border-tr"></span>
        <span class="qr-border qr-border-bl"></span>
        <span class="qr-border qr-border-br"></span>
      </div>
    </div>
  </div>
</template>
