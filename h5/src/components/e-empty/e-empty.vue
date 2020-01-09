<template>
  <div class="e-empty">
       <img class="e-empty__img"  src="static/images/empty.png"/>
       <p class="e-empty__text">{{text}}</p>
  </div>
</template>

<script>
export default {
  name:"e-empty",
  props:{
    text:{
      type:String,
      default:""
    }
  }
}
</script>

<style>

</style>