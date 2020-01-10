<template>
   <div class="nav-bar">
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
    }
  }
}
</script>

<style>

</style>