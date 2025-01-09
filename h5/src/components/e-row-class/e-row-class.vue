<template>
  <div class="e-row-class"
    @click="onClick(course.hasCertificate, $event)"
    :style="{ marginLeft: typeList === 'classroom_list' ? '30px' : '16px' }">
    <div class="relative row-class-left">
      <img
        v-if="typeList === 'classroom_list'"
        style="height: 90%;left: -15px;top: 50%;transform: translateY(-50%);"
        class="absolute z-1"
        src="static/images/classroom/classroom-icon.png"
        srcset="static/images/classroom/classroom-icon@2x.png" />

      <div class="relative z-1 h-full">
        <img class="cover" v-lazy="course.imgSrc.url" :class="course.imgSrc.className"/>
        <div v-if="Number(isVip)" class="absolute left-0 bottom-0 px-8 py-2 text-white text-12 leading-20 font-medium bg-black bg-opacity-80 rounded-bl-6 rounded-tr-12">会员免费</div>
        <div v-if="course.videoMaxLevel === '2k' && !Number(isVip)" class="absolute left-0 bottom-0 px-8 py-2 text-white text-12 leading-20 font-medium bg-black bg-opacity-80 rounded-bl-6 rounded-tr-12">2K 优享</div>
        <div v-if="course.videoMaxLevel === '4k' && !Number(isVip)" class="absolute left-0 bottom-0 px-8 py-2 text-[#492F0B] text-12 leading-20 font-medium bg-gradient-to-l from-[#F7D27B] to-[#FCEABE] rounded-bl-6 rounded-tr-12">4K 臻享</div>
        <div v-if="courseType === 'live'" class="absolute bottom-0 w-full px-6 py-4 text-white font-medium text-12 leading-12 bg-black bg-opacity-40 text-right rounded-bl-6">直播</div>
      </div>

    </div>

    <div class="row-class-right relative">
      <img v-if="isShowErrImg" class="err-img" :src="errImgUrl" />
      <div class="row-class-right__top">
        <div v-if="discountNum" style="height:14px;margin: 3px 4px 0 0;text-align: center;line-height: 14px;border: 1px solid #ff900e;border-radius: 2px;">
          <div style="font-size: 12px; transform: scale(0.75); color: #FF900E;">{{ $t('e.discount') }}</div>
        </div>
        <div v-if="course.hasCertificate" style="height:14px;margin: 3px 4px 0 0;text-align: center;line-height: 14px;border: 1px solid #3DCD7F;border-radius: 2px;">
          <div style="font-size: 12px; transform: scale(0.75); color: #3DCD7F;">{{ $t('e.certificate') }}</div>
        </div>
        <div class="flex items-center">
          <van-icon v-if="course.bindTitle" name="info-o" color="#919399" @click.stop="showBindTitleDialog"/>
          <div class="line-clamp-2 ml-4">{{ course.header }}</div>
        </div>
      </div>

      <div class="row-class-right__center">
        <div class="text-overflow" v-if="course.middle.value" v-html="course.middle.html" />
      </div>

      <div class="row-class-right__bottom text-overflow">
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
import { Dialog } from "vant";
export default {
  mixins: [eClassMixins],
  computed: {
    isShowErrImg() {
      if(this.course?.bottom?.data?.itemBankExercise?.status == 'closed') {
        return true;
      }

      if(this.course?.bottom?.data?.isExpired) {
        return true;
      }

      return false;
    },
    errImgUrl() {
      if(this.course?.bottom?.data?.itemBankExercise?.status == 'closed') {
        return 'static/images/closed.png';
      }

      if(this.course?.bottom?.data?.isExpired) {
        return 'static/images/expired.png';
      }

      return '';
    }
  },
  methods: {
    showBindTitleDialog() {
      Dialog.alert({
        message: `${this.course.bindTitle} 赠送的题库`,
        confirmButtonColor: '#46C37B',
      })
        .then(() => {

        })
    }
  }
};
</script>
<style scoped>
.err-img {
    position: absolute;
    height: 40px;
    top: 0;
    right: 0;
  }
</style>
