<template>
  <div class="goods-qr">
    <p class="goods-qr__want">
      想要了解更多课程内容，关注我们，将提供更贴心的教学服务。
    </p>
    <div class="goods-qr__img">
      <img :src="this.$route.query.imageUrl" alt="" />
    </div>
    <p class="goods-qr__scan">
      请保存二维码，通过微信右上角+，扫一扫选择图片后进行加入。
    </p>
  </div>
</template>
