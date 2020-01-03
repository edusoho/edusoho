<template>
<div>
  <div class="nav-bar">
      <i class="iconfont icon-arrow-left"></i>
      <div class="nav-bar-title">所有课程</div>
  </div>

  <div class="default">
    <span class="active-bg">全部</span>
  </div>

  <infinite-scroll
      :course-list="courseList"
      :is-all-data="true"
      :normal-tag-show="false"
      :vip-tag-show="true"
      :course-item-type="courseItemType"
      :is-request-compile="isRequestCompile"
      :type-list="'classroom_list'"
      @needRequest="sendRequest"
    />

</div>

</template>

<script>
import Api from '@/api'
import infiniteScroll from '&/components/e-infinite-scroll/e-infinite-scroll.vue'
import { mapMutations } from 'vuex'
import * as types from '@/store/mutation-types'
import CATEGORY_DEFAULT from '@/config/category-default-config.js'
export default {
   name:'more_class_new',
   components: {
    infiniteScroll
  },
}
</script>

<style>

</style>