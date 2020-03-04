<template>
  <div class="e-graphic-navigation">
       <div class="graphicNavigation__warp">
          <div 
          :class="[index===1? 'graphicNavigation__border':'','graphicNavigation__item']" 
          v-for="(item, index) in graphicNavigation"
          :key="index">
            <img v-if="!item.image.url" class="graphicNavigation__img" :src="getDefaultImg(item.link.type)" />
            <img v-else class="graphicNavigation__img" :src="item.image.url" />
            <span v-if="item.title">{{item.title}}</span>
          </div>
        </div>
  </div>
</template>

<script>
export default {
  name:"e-graphic-navigation",
  props:{
    graphicNavigation:{
      type:Array,
      default:()=>[]
    }
  },
   methods: {
    getDefaultImg(type){
      switch(type){
        case "openCourse":
          return "static/images/openCourse.png"
        case "course":
          return "static/images/hotcourse.png"
        case "classroom":
          return "static/images/hotclass.png"
      }
    }
  }
}
</script>

<style>

</style>