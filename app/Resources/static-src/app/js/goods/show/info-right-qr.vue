<template>
  <div v-if="mpQrCode.length" class="info-right-qr info-right-box">
    <div class="title">{{ mpQrCode.title }}</div>
    <div class="qr-info">
      <img class="qr-info__img" :src="mpQrCode.imageUrl" alt="">
      <div class="qr-info__introduce">
        <p>{{ mpQrCode.mpName }}</p>
        <p>{{ mpQrCode.content }}</p>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      mpQrCode: {
        type: Array,
        default: function () {
          return []
        }
      }
    }
  }
</script>