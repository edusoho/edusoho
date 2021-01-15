<template>
  <div class="e-row-class" @click="onClick(course.hasCertificate, $event)">
    <div class="row-class-left">
      <img v-lazy="course.imgSrc.url" :class="course.imgSrc.className" />
      <div v-if="discountNum" class="row-class-left__discount">
        {{ discountNum }}
      </div>
      <div v-if="Number(isVip)" class="row-class-left__member">会员免费</div>
      <div class="row-class-left__live">
        <div>
          <span v-show="courseType === 'live'">直播</span>
        </div>
        <!--        <div v-if="course.studentNum">-->
        <!--          <i class="iconfont icon-renqi" />-->
        <!--          {{ course.studentNum }}-->
        <!--        </div>-->
      </div>
    </div>

    <div class="row-class-right">
      <div class="row-class-right__top text-overflow">
        <span class="certificate-icon" v-if="course.hasCertificate">证</span
        >{{ course.header }}
      </div>
      <div class="row-class-right__center text-overflow">
        <div v-if="course.middle.value" v-html="course.middle.html" />
      </div>
      <div
        class="row-class-right__bottom text-overflow"
        v-html="course.bottom.html"
      />
    </div>
    <div v-if="show_number_data === 'join'" class="pull-right study-num">
      <i class="iconfont icon-people"></i>
      {{ course.studentNum }}人
    </div>
    <div
      v-else-if="show_number_data === 'visitor'"
      class="pull-right study-num"
    >
      <i class="iconfont icon-visibility"></i>
      {{ course.studentNum }}人
    </div>
  </div>
</template>

<script>
import eClassMixins from '@/mixins/eClass';
export default {
  mixins: [eClassMixins],
  props: {
    show_number_data: {
      type: String,
      default: 'none',
    },
  },
};
</script>
