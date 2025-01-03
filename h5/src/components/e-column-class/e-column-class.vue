<template>
  <div
    class="relative e-column-class"
    @click="onClick(course.hasCertificate, $event)"
  >
    <div class="column-class-left" :style="{ marginTop: typeList === 'classroom_list' ? '24px' : 0 }">
      <img
        v-if="typeList === 'classroom_list'"
        style="width: 90%;top: -15px;left: 50%;transform: translateX(-50%);"
        class="absolute z-1"
        src="static/images/classroom/classroom-icon2.png"
        srcset="static/images/classroom/classroom-icon2@2x.png" />
      <div class="relative z-1">
        <img class="relative cover" v-lazy="course.imgSrc.url" :class="course.imgSrc.className"/>
        <div v-if="course.videoMaxLevel === '2k'" class="absolute left-0 top-0 px-8 py-2 text-white text-12 font-medium bg-black bg-opacity-80 rounded-tl-6 rounded-br-8">2K 优享</div>
        <div v-if="course.videoMaxLevel === '4k'" class="absolute left-0 top-0 px-8 py-2 text-[#492F0B] text-12 font-medium bg-gradient-to-l from-[#F7D27B] to-[#FCEABE] rounded-tl-6 rounded-br-8">4K 臻享</div>
      </div>
      <div v-if="Number(isVip)" class="column-class-left__member">{{ $t('e.freeForMembers') }}</div>
      <div v-show="courseType === 'live'" class="column-class-left__live">{{ $t('e.live') }}</div>
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
      <div class="column-class-right__center">
        <div class="text-overflow" v-if="course.middle.value" v-html="course.middle.html" />
      </div>
      <div class="px-12 column-class-right__bottom text-overflow">
        <div v-html="course.bottom.html"></div>
        <div style="color: #86909c; font-size: 12px;">
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
