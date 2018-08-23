<template>
  <div class="setting-carousel setting-course">
   <!--  <div class="find-page__part" v-for="part in parts">
      <swipe :slides="part.data"></swipe>
    </div> -->
    <div class="carousel-allocate course-allocate">
      <header class="title">课程列表设置</header>
      <div class="carousel-item-setting course-item-setting clearfix">
        <div><label for="" class="add-title">列表名称：<input type="text" placeHolder="请输入列表名称"></label></div>
        <div><label for="" class="add-title">课程来源：<input type="text" placeHolder="请输入列表名称"></label></div>
        <div><label for="" class="add-title">课程分类：<input type="text" placeHolder="请选择分类"></label></div>
        <div><label for="" class="add-title">排列顺序：<input type="text" placeHolder="请输入列表名称"></label></div>
        <div><label for="" class="add-title">显示个数：<input type="text" placeHolder="请输入列表名称"></label></div>
      </div>
    </div>
  </div>
</template>


<script>

export default {
  components: {
  },
  data() {
    return  {
    }
  }
}
</script>
