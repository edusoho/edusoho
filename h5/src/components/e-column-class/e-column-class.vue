<template>
  <div
    class="relative e-column-class"
    @click="onClick(course.hasCertificate, $event)"
  >
    <div class="column-class-left" :style="{ marginTop: typeList === 'classroom_list' ? '24px' : 0 }">
      <img 
        v-if="typeList === 'classroom_list'"
        style="width: 90%;top: -17px;left: 50%;transform: translateX(-50%);"
        class="absolute"
        src="static/images/classroom/classroom-icon2.png" 
        srcset="static/images/classroom/classroom-icon2@2x.png" />
      <img class="cover" v-lazy="course.imgSrc.url" :class="course.imgSrc.className" />
      <div v-if="Number(isVip)" class="column-class-left__member">
        <div style="transform: scale(0.83);">{{ $t('e.freeForMembers') }}</div>
      </div>
      <div class="column-class-left__live">
        <span v-show="courseType === 'live'">{{ $t('e.live') }}</span>
      </div>
    </div>
    <div class="column-class-right">
      <div class="column-class-right__top">
        <div v-if="discountNum" style="width: 14px;height:14px;margin:3px 4px 0 0;text-align: center;line-height: 14px;border: 1px solid #ff900e;border-radius: 2px;">
          <div style="font-size: 12px; transform: scale(0.75); color: #FF900E;">{{ $t('e.discount') }}</div>
        </div>
        <div v-if="course.hasCertificate" style="width: 14px;height:14px;margin:3px 4px 0 0;text-align: center;line-height: 14px;border: 1px solid #3DCD7F;border-radius: 2px;">
          <div style="font-size: 12px; transform: scale(0.75); color: #3DCD7F;">{{ $t('e.certificate') }}</div>
        </div>
        <div class="text-overflow course-title">{{ course.header }}</div>
      </div>
      <div class="column-class-right__center text-overflow">
        <div v-if="course.middle.value" v-html="course.middle.html" />
      </div>
      <div class="column-class-right__bottom text-overflow">
        <div style="position: relative; left: 12px;" v-html="course.bottom.html"></div>
        <div style="margin: 1px 5px 0 0; color: #86909c; font-size: 12px; transform: scale(0.83);">
          <template v-if="showNumberData === 'join'">{{ $t('e.personStudying', { number: course.studentNum }) }}</template>
          <template v-else-if="showNumberData === 'visitor'">{{ hitNum }}{{ $t('e.browse') }}</template>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import eClassMixins from '@/mixins/eClass';
export default {
  mixins: [eClassMixins],
};
</script>
