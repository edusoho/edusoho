<template>
  <div class="join-before">
    <img src="/static/images/orderEmpty.png" alt="">
    <van-tabs v-model="active" @click="onTabClick" :class="tabsClass" ref="tabs">
      <van-tab v-for="item in tabs" :title="item" :key="item">
      </van-tab>
    </van-tabs>

    <e-panel title="课程介绍" ref="about"></e-panel>
    <div class="segmentation"></div>
    <teacher :teacherInfo="teacherInfo" ref="teacher"></teacher>
    <div class="segmentation"></div>
    <e-panel title="课程目录" ref="directory">
      暂无学习任务
      <p> 暂无学习任务</p>
      <p> 暂无学习任务</p>
      <p> 暂无学习任务</p>
      <p> 暂无学习任务</p>
      <p> 暂无学习任务</p>
      <p> 暂无学习任务</p>
      <p> 暂无学习任务</p>
      <p> 暂无学习任务</p>
      <p> 暂无学习任务</p>
      <p> 暂无学习任务</p>
      <p> 暂无学习任务</p>
    </e-panel>
  </div>
</template>
<script>
  import Teacher from './detail/teacher';

  export default {
    name: 'joinBefore',
    data() {
      return {
        teacherInfo: {
          nickname: 'gaot',
          title: 'IMC国际数学竞赛优秀教练员，两届“希望杯”数学竞赛优秀教练员，两届北京市迎春杯（数学解题能力展示）命题组组长。'
        },
        tabs: ['课程介绍', '教师介绍', '目录'],
        active: 0,
        tabsClass: '',
        tabsTop: 0
      }
    },
    components: {
      Teacher
    },
    mounted() {
      window.addEventListener('scroll', this.handleScroll);
      this.tabsTop = this.$refs.tabs.$el.getBoundingClientRect().top;
    },
    methods: {
      onTabClick(index, title) {
        const ref = this.$refs[this.transIndex2Tab(index)];
        window.scrollTo(0, ref.$el.offsetTop - 46);
      },
      transIndex2Tab(index) {
        return index ? (index > 1 ? 'directory' : 'teacher') : 'about';
      },
      handleScroll() {
        const scrollTop = window.pageYOffset ||
          document.documentElement.scrollTop || document.body.scrollTop;

        if(scrollTop >= this.tabsTop) {
          this.tabsClass = 'van-tabs--fixed';
        }else {
          this.tabsClass = '';
        }
      }
    }
  }
</script>
