<template>
  <div class="product-info clearfix">
    <div class="product-info__left pull-left">
      left
    </div>
    <div class="product-info__right pull-right">
      <!-- 授课老师 -->
      <info-right-teacher></info-right-teacher>
    </div>
  </div>
</template>

<script>
  import infoRightTeacher from './info-right-teacher';
  export default {
    data() {
      return {
        
      }
    },
    components: {
      infoRightTeacher
    },
    methods: {
      
    }
  }
</script>