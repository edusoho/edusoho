<template>
  <div class="join-before">
    <div>
      <img src="/static/images/noLoginEmpty.png" alt="">
    </div>
    <van-tabs v-model="active" @click="onTabClick" :class="tabsClass" ref="tabs">
      <van-tab v-for="item in tabs" :title="item" :key="item"></van-tab>
    </van-tabs>

    <!-- 课程介绍 -->
    <e-panel title="课程介绍" ref="about" class="about"></e-panel>
    <div class="segmentation"></div>

    <!-- 教师介绍 -->
    <teacher :teacherInfo="teacherInfo" ref="teacher"></teacher>
    <div class="segmentation"></div>

    <!-- 课程目录 -->
    <directory ref="directory"></directory>
    
    <e-footer @click.native="handleJoin">加入学习</e-footer>
  </div>
</template>
<script>
  import Teacher from './detail/teacher';
  import Directory from './detail/directory';

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
        tops: {
          tabsTop: 0,
          teacherTop: 0,
          aboutTop: 0,
        }
      }
    },
    components: {
      Teacher,
      Directory
    },
    created() {

    },
    mounted() {
      const refs = this.$refs;

      window.addEventListener('scroll', this.handleScroll);

      setTimeout(() => {
        window.scrollTo(0,0);

        Object.keys(refs).forEach(item => {
          this.tops[`${item}Top`] = refs[item].$el.getBoundingClientRect().top
        })
        console.log(this.tops);
      }, 100)
    },
    methods: {
      onTabClick(index, title) {
        const ref = this.$refs[this.transIndex2Tab(index)];

        window.scrollTo(0, ref.$el.offsetTop - 44);
      },
      transIndex2Tab(index) {
        return index ? (index > 1 ? 'directory' : 'teacher') : 'about';
      },
      handleScroll() {
        const scrollTop = window.pageYOffset ||
          document.documentElement.scrollTop || document.body.scrollTop;

        this.active = this.activeCurrentTab(scrollTop);

        scrollTop >= this.tops.tabsTop
          ? this.tabsClass = 'van-tabs--fixed'
          : this.tabsClass = '';
      },
      activeCurrentTab(scrollTop) {
        const tops = this.tops;

        scrollTop  = scrollTop + 44;

        return (scrollTop < tops.teacherTop) ? 0
          :(scrollTop >= tops.directoryTop ? 2 : 1);
      },
      handleJoin(){
        console.log('join');
      }
    },
    destroyed () {
      window.removeEventListener('scroll', this.handleScroll);
    },
  }
</script>
