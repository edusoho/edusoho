<template>
  <div>
    <div class="my-course-item cd-mb16 clearfix">

      <a class="my-course-item__link relative" href="/course_set/931">
        <img src="https://img0.baidu.com/it/u=1240640540,1985929821&fm=253&fmt=auto&app=138&f=JPEG?w=975&h=500" alt="jc本地测试课程" class="my-course-item__picture">
        <span class="absolute course-status-expired">已关闭
        </span> </a>
      <div class="my-course-item__info">
        <div class="my-course-item__title text-overflow">

          <a class="cd-link-major" href="/course_set/931">jc本地测试课程</a>
        </div>


        <div class="my-course-item__progress cd-mt32 cd-clearfix">
          <span class="my-course-item__progress__text">学习进度</span>
          <div class="cd-progress cd-progress-sm">
            <div class="progress-bar">
              <div class="progress-outer">
                <div class="progress-inner" :style="`width: ${progress}%;`"></div>
              </div>
            </div>
            <div class="progress-text">{{progress}}%</div>
        </div>
      </div>
    </div>
      <div class="my-course-item__btn">
        <a class="btn cd-btn cd-btn-primary" href="/my/course/981">继续学习</a>
      </div>
    
    </div>
  </div>
</template>
<script>
import { Tabs } from 'ant-design-vue';

export default {
  props: {
    type: String
  },
  data(){
    return {
      progress: 97
    }
  }
}
</script>