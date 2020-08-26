<template>
  <div class="goods-qr">
    <p class="goods-qr__want">
      {{ this.$route.query.title }}
    </p>
    <div class="goods-qr__img">
      <img :src="this.$route.query.imageUrl" alt="" />
    </div>
    <p class="goods-qr__scan">
      {{ this.$route.query.content }}
    </p>
  </div>
</template>
