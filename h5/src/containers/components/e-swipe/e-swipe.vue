<template>
  <div class="e-swipe">
    <van-swipe :autoplay="2000">
      <van-swipe-item v-for="(slide, index) in slides" :key="index">
        <div class="item-container">
          <!-- course -->
          <div v-if="slide.link.type === 'course'" @click="jumpTo(slide.link.id)">
            <img v-bind:src="slide.image.uri">
          </div>
          <!-- url -->
          <a v-if="slide.link.type === 'url'" v-bind:href="slide.link.url">
            <img v-bind:src="slide.image.uri">
          </a>
          <div class="text-overflow item-container__title">{{ slide.title }}</div>
        </div>
      </van-swipe-item>
    </van-swipe>
  </div>
</template>

<script>
  export default {
    props: {
      slides: {
        type: Array,
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
