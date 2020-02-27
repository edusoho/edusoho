<template>
  <div v-if="btnFlag" class="e-back-top" :style="{ color: color }" @click="backTop">
    <i :class="['iconfont', icon]"/>
    <span>{{text}}</span>
  </div>
</template>

<script>
export default {
  data(){
    return{
      scrollTop:0,
      btnFlag:false
    }
  },
  props:{
    icon:{
      type:String,
      default:''
    },
    text:{
      type:String,
      default:'顶部'
    },
    color:{
      type:String,
      default:'#408ffb'
    }
  },
  mounted () {
    window.addEventListener('scroll', this.scrollToTop)
  },
  destroyed () {
    window.removeEventListener('scroll', this.scrollToTop)
  },
  methods: {
    // 点击图片回到顶部方法，加计时器是为了过渡顺滑
    backTop () {
        let timer = setInterval(() => {
          let ispeed = Math.floor(-this.scrollTop / 5)
          document.documentElement.scrollTop = document.body.scrollTop = this.scrollTop + ispeed
          if (this.scrollTop === 0) {
            clearInterval(timer)
          }
        }, 16)
    },
    // 为了计算距离顶部的高度，当高度大于60显示回顶部图标，小于60则隐藏
    scrollToTop () {
      let scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop
      this.scrollTop = scrollTop
      if (this.scrollTop > 60) {
        this.btnFlag = true
      } else {
        this.btnFlag = false
      }
    }
  }
}

</script>

<style>

</style>