<template>
  <div class="e-swipe">
    <van-swipe :autoplay="2000">
      <van-swipe-item v-for="(slide, index) in slides" :key="index">
        <div class="item-container">
          <!-- course -->
          <div v-if="slide.link.type === 'course' || 'classroom'" @click="jumpTo(slide, index)">
            <img v-bind:src="slide.image.uri">
          </div>
          <!-- url -->
          <a v-if="slide.link.type === 'url'" :href="slide.link.url || 'javascript:;'">
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
      },
      feedback: {
        type: Boolean,
        default: true,
      },
    },
    methods: {
      jumpTo(slide, index) {
        if (!this.feedback) return;
        if (!slide) return;

        const itemLinkData = slide.link;
        if (itemLinkData.type === 'classroom' && itemLinkData.target) {
          this.$router.push({
            path: `/classroom/${itemLinkData.target.id}`
          });
          return;
        }
        if (itemLinkData.type === 'course' && itemLinkData.target) {
          this.$router.push({
            path: `/course/${itemLinkData.target.id}`
          });
          return;
        }
      }
    }
  }
</script>
