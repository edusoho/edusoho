<template>
  <div class="e-row-class" @click="onClick(course.hasCertificate, $event)">
    <div class="row-class-left">
      <img v-lazy="course.imgSrc.url" :class="course.imgSrc.className" />
      <div v-if="discountNum" class="row-class-left__discount">
        {{ discountNum }}
      </div>
      <div v-if="Number(isVip)" class="row-class-left__member">{{ $t('e.freeForMembers') }}</div>
      <div class="row-class-left__live">
        <div>
          <span v-show="courseType === 'live'">{{ $t('e.live') }}</span>
        </div>
        <div v-if="showNumberData === 'join'">
          <i class="iconfont icon-people" />
          {{ course.studentNum }}
        </div>
        <div v-if="showNumberData === 'visitor'">
          <i class="iconfont icon-visibility" />
          {{ hitNum }}
        </div>
      </div>
    </div>

    <div class="row-class-right">
      <div class="row-class-right__top text-overflow">
        <span class="certificate-icon" v-if="course.hasCertificate">{{ $t('e.certificate') }}</span
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
  </div>
</template>

<script>
import eClassMixins from '@/mixins/eClass';
export default {
  mixins: [eClassMixins],
};
</script>
