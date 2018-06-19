<template>
  <e-panel title="课程目录" class="directory layout-col">
    <!-- 暂无学习任务 -->

    <div class="directory-list">
      <div class="directory-list__item">
        <div class="directory-list__item-chapter" @click="show = !show">
          <span>音视频</span> 
          <i :class="[ show ? 'icon-packup': 'icon-unfold']"></i>
        </div>
        <div :class="['directory-list__item-unit', {'unit-show': show}]">
          <div class="lesson-cell__unit">
            第1节：云音视频
          </div>
          <div :class="['box', {'show-box': show}]">
            <div class="lesson-cell">
            <span class="lesson-cell__number">01</span>
            <div class="lesson-cell__content">
              <span>《哈士奇》MP4《哈士奇》MP4</span>
              <span>视频 | 时长: 01:49</span>
            </div>
            <div class="lesson-cell__status">试看</div>
          </div>
          </div>
          
        </div>
      </div>
    </div>
  </e-panel>
</template>
<script>
  import { Collapse, CollapseItem } from 'vant';

  export default {
    props: ['courseItem'],
    data() {
      return {
        show: false
      }
    },
    created() {
      console.log('courseItem', this.courseItem);
    },
    methods: {
      lessonTitleClick() {

      }
    }
  }
</script>
