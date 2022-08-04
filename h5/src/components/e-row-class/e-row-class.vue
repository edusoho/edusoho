<template>
  <div class="e-row-class" @click="onClick(course.hasCertificate, $event)">
    <div class="row-class-left">
      <img v-lazy="course.imgSrc.url" :class="course.imgSrc.className" />
      
      <div v-if="Number(isVip)" class="row-class-left__member">{{ $t('e.freeForMembers') }}</div>
    </div>

    <div class="row-class-right">
      <div class="row-class-right__top">
        <div v-if="discountNum" style="width: 14px;height:14px;margin: 3px 4px 0 0;text-align: center;line-height: 14px;border: 1px solid #ff900e;border-radius: 2px;">
          <div style="font-size: 12px; transform: scale(0.75); color: #FF900E;">{{ $t('e.discount') }}</div>
        </div>
        <div v-if="course.hasCertificate" style="width: 14px;height:14px;margin: 3px 4px 0 0;text-align: center;line-height: 14px;border: 1px solid #3DCD7F;border-radius: 2px;">
          <div style="font-size: 12px; transform: scale(0.75); color: #3DCD7F;">{{ $t('e.certificate') }}</div>
        </div>
        <div class="text-overflow">{{ course.header }}</div>
      </div>

      <div class="row-class-right__center text-overflow">
        <div v-if="course.middle.value" v-html="course.middle.html" />
      </div>

      <div class="row-class-right__bottom text-overflow">
        <div v-html="course.bottom.html"></div>
        <div style="margin-top: 3px; color: #86909c; font-size: 12px; transform: scale(0.83);">
          <template v-if="showNumberData === 'join'">{{ course.studentNum }}人在学</template>
          <template v-else-if="showNumberData === 'visitor'">{{ hitNum }}人浏览</template>
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
