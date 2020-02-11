<template>
  <div class="e-row-class">
    <div class="row-class-left">
      <img v-lazy="course.imgSrc.url" :class="course.imgSrc.className" />
      <div class="row-class-left__live">
        <div v-if="course.studentNum">
          <i class="iconfont icon-renqi" />
          {{ course.studentNum }}
        </div>
      </div>
    </div>

    <div class="row-class-right">
      <div class="row-class-right__top text-overflow">{{ course.header }}</div>
      <div class="row-class-right__center">
        <div style="display:flex">
          <!-- <span :class="getStatusClass(status)">
            {{ status | liveStatusText }}
            <i v-show="status === 'default'" class="iconfont icon-zhibo1"></i>
          </span>
          <div class="live-content__right" v-if="status !== 'end'">
            <div
              :class="[
                'live-btn',
                status === 'default' ? 'live-btn--start' : 'live-btn--default'
              ]"
              @click="toTask()"
            >
              {{ status | liveBtnText }}
            </div>
          </div> -->
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import eClassMixins from "@/mixins/eClass";
export default {
  data(){
    return{
      course:{
        bottom:{},
        imgSrc:{
          className:"",
          url:"http://devtest.edusoho.cn/files/system/2019/08-07/1747506b1910625434.JPG"
        },
        middle:{},
        header:"2-2直播测试",
        id:"581",
        studentNum:"3"
      },
      courseType:"live"
      discount:"10"
      feedback:true
      isAppUse:true
      isVip:"0"
      normalTagShow:true
      type:"price"
      typeList:"course_list"
      vipTagShow:true
    }
  },
  mixins: [eClassMixins]
};
</script>
