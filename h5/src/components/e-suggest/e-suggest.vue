<template>
  <div class="setting-tip">
      <template v-if="suggest.length>=2">
          <div class="tip__arrow tip__left" @click="prev">
          <i class="iconfont icon-arrow-left"></i>
          </div>
          <div class="tip__arrow tip__right" @click="next">
            <i class="iconfont icon-arrow-right"></i>
          </div>
        </template>
        <el-carousel
          :interval="-1"
          arrow="never"
          :autoplay="false"
          indicator-position="none"
          height="auto"
          ref="suggestCarousel"
          :initial-index="0"
        >
          <el-carousel-item v-for="item in suggest" :key="item">
            <div class="setting-tip__main">
              <div class="setting-tip__title">
                <i class="iconfont icon-jianyi"></i>装修小建议：
              </div>
              <div class="setting-tip__content">{{ item }}</div>
            </div>
          </el-carousel-item>
        </el-carousel>
      </div>
</template>
<script>
export default {
  name:"e-suggest",
  props:{
    suggest:{
      type:Array,
      default:()=>[]
    }
  },
  methods:{
    prev(){
      this.$refs.suggestCarousel.prev()
    },
    next(){
      this.$refs.suggestCarousel.next()
    }
  }
}
</script>
