<template>
  <div class="swiper-directory goods-swiper-directory">
    <van-swipe
      ref="chapterSwipe"
      :show-indicators="false"
      :loop="false"
      :touchable="true"
      :width="swiperWidth"
      :initial-swipe="slideIndex"
      @change="changeChapter"
    >
      <van-swipe-item v-for="(items, index) in item" :key="index">
        <div
          v-if="items.isExist == 0"
          :class="[current === index] ? 'swiper-directory-active' : ''"
          class="chapter nochapter"
          @click="handleChapter(index)"
          style="margin: 0 4vw 0 0;"
          :style="index == item.length - 1 && 'margin-right: 0;'"
        >
          <i class="iconfont icon-wuzhangjieliang" />
          {{ $t('goods.noChapter') }}
        </div>
        <div
          v-if="items.isExist == 1"
          :class="[current === index ? 'swiper-directory-active' : '']"
          class="chapter haschapter"
          @click="handleChapter(index)"
          style="margin: 0 4vw 0 0;"
          :style="index == item.length - 1 && 'margin-right: 0;'"
        >
          <p class="chapter-title text-overflow">
            第{{ items.number }}{{ hasChapter ? $t('goods.chapter') : $t('goods.section2') }}：{{
              items.title
            }}
          </p>
          <p class="chapter-des text-overflow">
            {{ hasChapter ? `${$t('goods.section2')}(${items.unitNum})` : '' }} {{ $t('goods.lesson') }}({{
              items.lessonNum
            }}) {{ $t('goods.learningTasks') }}({{ items.tasksNum }})
          </p>
        </div>
      </van-swipe-item>
    </van-swipe>
  </div>
</template>
<script>
export default {
  name: 'SwiperDirectory',
  props: {
    item: {
      type: Array,
      default: () => [],
    },
    slideIndex: {
      type: Number,
      default: 0,
    },
    hasChapter: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      current: this.slideIndex || 0,
    };
  },
  computed: {
    swiperWidth() {
      const w = window.innerWidth;
      if (w <= 500) {
        return 265;
      } else {
        return w / 1.5;
      }
    },
  },
  watch: {
    slideIndex: function(val, oldVal) {
      if (val != oldVal) {
        this.current = val || 0;
      }
    },
  },
  methods: {
    changeChapter(index) {
      this.current = index;
      this.$emit('changeChapter', index);
    },
    handleChapter(index) {
      this.$refs.chapterSwipe.swipeTo(index);
    },
  },
};
</script>
