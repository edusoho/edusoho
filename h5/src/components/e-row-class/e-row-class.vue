<template>
  <div class="e-row-class" @click="onClick">
    <div class="row-class-left">
      <img :class="course.imgSrc.className" v-lazy="course.imgSrc.url" />
      <div v-if="discountNum" class="row-class-left__discount">
        {{ discountNum }}
      </div>
      <div v-if="Number(isVip)" class="row-class-left__member">会员免费</div>
      <div class="row-class-left__live">
        <div>
          <span v-show="courseType === 'live'">直播</span>
        </div>
        <div v-if="course.studentNum">
          <i class="iconfont icon-renqi"></i>
          {{ course.studentNum }}
        </div>
      </div>
    </div>

    <div class="row-class-right">
      <div class="row-class-right__top text-overflow">{{ course.header }}</div>
      <div class="row-class-right__center text-overflow">
        <div v-if="course.middle.value" v-html="course.middle.html"></div>
      </div>
      <div
        class="row-class-right__bottom text-overflow"
        v-html="course.bottom.html"
      ></div>
    </div>
  </div>
</template>

<script>
import eClassMixins from "@/mixins/eClass";
export default {
  mixins: [eClassMixins]
};
</script>
