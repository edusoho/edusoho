<template>
  <div class="product-info clearfix">
    <div class="product-info__left pull-left">
      left
    </div>
    <div class="product-info__right pull-right">
      <!-- 授课老师 -->
      <info-right-teacher></info-right-teacher>
      <!-- 公众号 -->
      <info-right-qr></info-right-qr>
      <!-- 猜你想学 -->
      <info-right-learn></info-right-learn>
    </div>
  </div>
</template>

<script>
  import infoRightTeacher from './info-right-teacher';
  import infoRightQr from './info-right-qr';
  import infoRightLearn from './info-right-learn';
  export default {
    data() {
      return {
        
      }
    },
    components: {
      infoRightTeacher,
      infoRightQr,
      infoRightLearn
    },
    methods: {
      
    }
  }
</script>