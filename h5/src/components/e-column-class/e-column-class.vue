<template>
  <div
    class="e-column-class pull-left"
    @click="onClick(course.hasCertificate, $event)"
  >
    <div class="column-class-left">
      <img v-lazy="course.imgSrc.url" :class="course.imgSrc.className" />
      <div v-if="discountNum" class="column-class-left__discount">
        {{ discountNum }}
      </div>
      <div v-if="Number(isVip)" class="column-class-left__member">会员免费</div>
      <div class="column-class-left__live">
        <div>
          <span v-show="courseType === 'live'">直播</span>
        </div>
        <div v-if="show_number_data === 'join'">
          <i class="iconfont icon-people" />
          {{ course.studentNum }}
        </div>
        <div v-if="show_number_data === 'visitor'">
          <i class="iconfont icon-visibility" />
          {{ hitNum }}
        </div>
      </div>
    </div>
    <div class="column-class-right">
      <div class="column-class-right__top text-overflow">
        <span class="certificate-icon" v-if="course.hasCertificate">证</span
        >{{ course.header }}
      </div>
      <div class="column-class-right__center  text-overflow">
        <div v-if="course.middle.value" v-html="course.middle.html" />
      </div>
      <div
        class="column-class-right__bottom text-overflow"
        v-html="course.bottom.html"
      />
    </div>
  </div>
</template>

<script>
import eClassMixins from '@/mixins/eClass';
export default {
  mixins: [eClassMixins],
};
</script>
