<template>
  <div class="e-graphic-navigation">
    <div class="graphicNavigation__warp" :class="getGraphicClass(graphicNavigation.length)" v-for="(empty, count) in getListCount(graphicNavigation)" :key="count">
      <div 
      :class="['graphicNavigation__item']" 
      v-for="(item, index) in sliceList(graphicNavigation, count)"
      :key="index"
      @click="goNavigation(item.link)">
        <img v-if="!item.image.uri" class="graphicNavigation__img" :src="getDefaultImg(item.link.type)" />
        <img v-else class="graphicNavigation__img" :src="item.image.uri" />
        <span class="graphicNavigation__text">{{item.title}}</span>
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
    },
    feedback:{
      type:Boolean,
      default:false
    }
  },
  data() {
    return {
      itemLength: 5
    }
  },
  methods: {
    getGraphicClass(itemCount) {
      if (itemCount >= this.itemLength) {
        return 'graphicNavigation__warp__small';
      }
      return 'graphicNavigation__warp__normal';
    },
    getDefaultImg(type){
      switch(type){
        case "openCourse":
          return "static/images/openCourse.png"
        case "course":
          return "static/images/hotcourse.png"
        case "classroom":
          return "static/images/hotclass.png"
        default:
          return "static/images/graphic/default/icon@2x.png"
      }
    },
    getListCount(data) {
      return new Array(Math.ceil(data.length / this.itemLength));
    },
    sliceList(data, count) {
      console.log(data);
      return data.slice(count * this.itemLength, (count + 1) * this.itemLength);
    },
    goNavigation(link){
       if (!this.feedback) {
        return;
      }
      if(link.type==="course"){
        this.$router.push({
          name: "more_course",
          query: {...link.conditions}
        });
        return
      }
      if(link.type==="classroom"){
        this.$router.push({
          name: "more_class",
          query: {...link.conditions}
        });
        return
      }
    }
  }
}
</script>

<style>

</style>