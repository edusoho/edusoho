<template>
  <div class="e-swipe">
    <van-swipe :autoplay="2000">
      <van-swipe-item v-for="(slide, index) in slides" :key="index">
        <div class="item-container">
          <!-- course/classroom -->
          <div v-if="slide.link.type !== 'url'" @click="jumpTo(slide, index)">
            <img :src="slide.image.uri" />
          </div>
          <!-- url -->
          <a v-else :href="slide.link.url || 'javascript:;'">
            <img v-lazy="slide.image.uri" />
          </a>
          <div class="text-overflow item-container__title">
            {{ slide.title }}
          </div>
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
      default: function() {
        return [];
      },
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
        // this.$router.push({
        //   path: `/classroom/${itemLinkData.target.id}`,
        // });
        this.$router.push({
          path: `/goods/${itemLinkData.target.goodsId}/show`,
          query: {
            targetId: itemLinkData.target.id,
            type: 'classroom_list',
          },
        });
        return;
      }
      if (itemLinkData.type === 'vip') {
        this.$router.push({
          path: `/vip`,
        });
        return;
      }
      if (itemLinkData.type === 'course' && itemLinkData.target) {
        // this.$router.push({
        //   path: `/course/${itemLinkData.target.id}`,
        // });
        this.$router.push({
          path: `/goods/${itemLinkData.target.goodsId}/show`,
          query: {
            targetId: itemLinkData.target.id,
            type: 'course_list',
          },
        });
      }
    },
  },
};
</script>

<style lang="scss" scoped>
  .e-swipe {
    margin: 10px 16px;
    border-radius: 6px;
    overflow: hidden;
  }
  
  /deep/ .van-swipe__indicators {
    left: 12px;
    bottom: 8px;
    transform: none;

    .van-swipe__indicator {
      width: 12px;
      height: 2px;
      border-radius: 0 !important;
      background: rgba(255, 255, 255, 0.6);
      &.active {
        background-color: #fff;
      }
    }
  }
</style>
