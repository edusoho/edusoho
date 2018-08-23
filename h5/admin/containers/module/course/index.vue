<template>
  <div class="setting-carousel setting-course">
   <!--  <div class="find-page__part" v-for="part in parts">
      <swipe :slides="part.data"></swipe>
    </div> -->
    <div class="carousel-allocate course-allocate">
      <header class="title">课程列表设置</header>
      <div class="carousel-item-setting course-item-setting clearfix">
        <div class="course-item-setting__section">
          <p class="pull-left section-left">列表名称：</p>
          <div class="section-right">
            <el-input size="mini" v-model="input" placeholder="请输入列表名称"></el-input>
          </div>
        </div>
        <div class="course-item-setting__section mtl">
          <p class="pull-left section-left">课程来源：</p>
          <div class="section-right">
            <el-input size="mini" v-model="input" placeholder="请输入内容"></el-input>
          </div>
        </div>
        <div class="course-item-setting__section mtl">
          <p class="pull-left section-left">课程分类：</p>
          <div class="section-right">
            <el-input size="mini" v-model="input" placeholder="请选择分类"></el-input>
          </div>
        </div>
        <div class="course-item-setting__section mtl">
          <p class="pull-left section-left">排列顺序：</p>
          <div class="section-right">
            <el-input size="mini" v-model="input" placeholder="请输入内容"></el-input>
          </div>
        </div>
        <div class="course-item-setting__section mtl">
          <p class="pull-left section-left">显示个数：</p>
          <div class="section-right">
            <el-input size="mini" v-model="input" placeholder="请选择个数"></el-input>
          </div>
        </div>
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
      input: '',
    }
  }
}
</script>
