<template>
  <div class="e-column-class pull-left">
    <div class="column-class-left">
      <img v-lazy="course.imgSrc.url" :class="course.imgSrc.className" >
      <div v-if="discountNum" class="column-class-left__discount">
        {{ discountNum }}
      </div>
      <div v-if="Number(isVip)" class="column-class-left__member">会员免费</div>
      <div class="column-class-left__live">
        <div>
          <span v-show="courseType === 'live'">直播</span>
        </div>
        <div v-if="course.studentNum">
          <i class="iconfont icon-renqi"/>
          {{ course.studentNum }}
        </div>
      </div>
    </div>
    <div class="column-class-right">
      <div class="column-class-right__top text-overflow">
        {{ course.header }}
      </div>
      <div class="column-class-right__center text-overflow">
        <div v-if="course.middle.value" v-html="course.middle.html"/>
      </div>
      <div
        class="column-class-right__bottom text-overflow"
        v-html="course.bottom.html"
      />
    </div>
  </div>
</template>

<script>
import eClassMixins from '@/mixins/eClass'
export default {
  mixins: [eClassMixins]
}
</script>
