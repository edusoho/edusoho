<template>
  <div class="e-course">
    <a class="clearfix" href="">
      <div class="e-course__left pull-left">
        <img src="">
      </div>
      <div class="e-course__right pull-left">
        <div class="e-course__title">Photoshop基础入门教程</div>
        <div class="e-course__project">
          <span>三月班</span>
        </div>
        <switchBox></switchBox>
      </div>
    </a>
  </div>
</template>

<script>
  import switchBox from '../e-course-switch-box/e-course-switch-box.vue';

  export default {
    data () {
      return {
      };
    },
    components: {
      switchBox,
    }
  }
</script>
