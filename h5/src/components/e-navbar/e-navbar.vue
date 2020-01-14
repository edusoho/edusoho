<template>
   <div class="nav-bar" :class="{'box-shadow':showShadow}">
      <i class="iconfont icon-arrow-left"></i>
      <div class="nav-bar-title">{{title}}</div>
    </div>
</template>

<script>
export default {
  name:"navbar",
  props:{
    title:{
      type:String,
      default:""
    },
    showShadow:{
      type:Boolean,
      default:true
    }
  }
}
</script>

<style>

</style>