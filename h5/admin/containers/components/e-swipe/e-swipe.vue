<template>
  <div class="e-swipe">
    <swipe class="my-swipe">
      <swipe-item class="slide1" v-for="(slide, index) in slides" :key="index">
        <a v-bind:href="slide.link.url" >
          <img v-bind:src="slide.image">
        </a>
      </swipe-item>
    </swipe>
  </div>
</template>

<script>
  export default {
    props: {
      slides: {
        default: []
      }
    },
    methods: {
      jumpTo(courseId) {
        this.$router.push({
          path: `/course/${courseId}`
        });
      }
    }
  }
</script>