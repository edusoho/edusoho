<template>
  <div>
    <detail-head 
      status="after"
      :courseSet="details.courseSet">
    </detail-head>

    <van-tabs v-model="active" 
      class="after-tabs"
      @click="onTabClick" ref="tabs">
      <van-tab v-for="item in tabs" :title="item" :key="item"></van-tab>
    </van-tabs>

     <!-- 课程目录 -->
    <directory ref="directory" 
      v-if="!active"
      :tryLookable="details.tryLookable"
      :courseItem="details.courseItem"></directory>
  </div>
</template>
<script>
import Directory from './detail/directory';
import DetailHead from './detail/head';

export default {
  props: ['details'],
  data() {
    return {
      active: 0,
      tabs: ['课程目录', '课程简介'],
    }
  },
  components: {
    Directory,
    DetailHead
  },
  created(){
    console.log('after', this.details)
  },
  methods: {
    onTabClick(){
      console.log('click')
    }
  }
}
</script>
