<template>
  <div class="e-swipe">
    <van-swipe>
      <van-swipe-item v-for="(slide, index) in slides" :key="index">
        <!-- course -->
        <div class="item-container" v-if="slide.link.type === 'course'">
          <div @click="jumpTo(slide.link.id)">
            <img v-bind:src="slide.image">
          </div>
        </div>
        <!-- url -->
        <div class="item-container" v-if="slide.link.type === 'url'">
          <a v-bind:href="slide.link.url">
            <img v-bind:src="slide.image">
          </a>
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
