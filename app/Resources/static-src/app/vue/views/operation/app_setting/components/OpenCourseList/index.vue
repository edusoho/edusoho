<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    :preview="preview"
    :validator-result="validatorResult"
    @event-actions="handleClickAction"
  >
    <div class="open-course-list">
      <div class="clearfix">
        <div class="open-course-list__title pull-left text-overflow">{{ moduleData.title }}</div>
        <div class="open-course-list__more pull-right">{{ 'site.btn.see_more' | trans }}<a-icon type="right" /></div>
      </div>
      <div class="clearfix mt8">
        <div class="open-course-item pull-left">
          <img class="open-course-bg" src="/static-dist/app/img/vue/decorate/openCourse_bg.png" srcset="/static-dist/app/img/vue/decorate/openCourse_bg@2x.png" />
          <div class="open-course-info">
            <div class="title">1111</div>
            <div class="time">
              <img :src="timeIcon" style="position: relative;top: -2px;width: 12px;height: 12px;" />
              <span>2022-05-29 17:00</span>
            </div>
            <div class="student-num">
              <img :src="numIcon" style="position: relative;top: -2px;width: 12px;height: 12px;" />
              <span>2345人</span>
            </div>
          </div>
        </div>
        <div class="pull-left">
          <div class="course-living">
            <img class="course-living-bg" src="/static-dist/app/img/vue/decorate/living_bg.png" srcset="/static-dist/app/img/vue/decorate/living_bg@2x.png" />
            <div class="course-living__title">{{ 'decorate.living' | trans }}</div>
          </div>
          <div class="course-replay">
            <img class="course-replay-bg" src="/static-dist/app/img/vue/decorate/replay_bg.png" srcset="/static-dist/app/img/vue/decorate/replay_bg@2x.png" />
            <div class="course-replay__title">{{ 'decorate.replay' | trans }}</div>
          </div>
        </div>
      </div>
    </div>
  </layout>
</template>

<script>
import _ from 'lodash';
import { OpenCourse } from 'common/vue/service/index.js';
import moduleMixin from '../moduleMixin';

const timeIcon = `data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAYAAABWdVznAAAACXBIWXMAAAsT
AAALEwEAmpwYAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAACzSURBVHgBlZLhCcIwEIUvzQJu4AiCCwgZoQP4Q/cQXMQd
3MCM4H8ldQLdIN6DF3kVi/TBR9PLXS7v2lBrjTbWhkCZfNR9Jd6dg7NgDOubHGCGDk5ynnzifU2w7nWvFQzciORIohxYsO7YbnDON
i34eCC3FWT7r2srMDHZhG5bZyWxVzON+13kvlGMFzGPnKSm00TRjgMpOqU21v5H0WjkQb40zJ84sUxf8LB09oxZmPtrvAHDH9++wBt5
uQAAAABJRU5ErkJggg==`;
const numIcon = `data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAYAAABWdVznAAAACXBIWXMAAAsTAAAL
EwEAmpwYAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAACqSURBVHgBhVELDQIxDG0JAibhHHASDgeTMAmg4IISTgIOAAVIAAf
goLwHvWTZbdDkpe3rf1MpxMwCVHL3pKqPPL4qkjuoG7ABeuDsDeqC4A44Zv5Erjmh1qMkVJb7c6WLUwOwze9QWa7VQUV3JyS/pDnfbA
B46NO+Qju1kkfg7kXBuejcWCYnD4RKozA3ykmOjv9Wpb12jtVXkH2jhpM/sfkf9vJb+FIHGm+R0342iXFPDAAAAABJRU5ErkJggg==`;

export default {
  name: 'OpenCourseList',

  mixins: [moduleMixin],

  data() {
    return {
      timeIcon,
      numIcon,
      list: []
    }
  },

  mounted() {
    this.fetchOpenCourse();
  },

  watch: {
    moduleData: {
      handler: function() {
        this.fetchOpenCourse();
      },
      deep: true
    }
  },

  methods: {
    async fetchOpenCourse() {
      const { categoryId, sourceType, items } = this.moduleData;
      if (sourceType === 'custom') {
        this.list = items;
        this.reInitSwiper();
        return;
      }
      
      const params = {
        params: {
          categoryId,
          isHomePage: 1
        }
      };
      const { data } = await OpenCourse.search(params);
      this.list = data;
    },
  }
}
</script>

<style lang="less" scoped>
.open-course-list {
  padding-right: 16px;
  padding-left: 16px;

  .open-course-list__title {
    position: relative;
    padding-left: 10px;
    max-width: 60%;
    height: 24px;
    font-size: 16px;
    font-weight: 500;
    color: #333;
    line-height: 24px;

    &::before {
      content: "";
      position: absolute;
      top: 6px;
      left: 0;
      width: 4px;
      height: 12px;
      background: #03c777;
      border-radius: 1px;
    }
  }

  &__more {
    margin-top: 4px;
    font-size: 12px;
    color: #999;
    line-height: 16px;
  }
}

.open-course-item {
  position: relative;
  width: 165px;
  height: 162px;
  margin-right: 12px;
  border-radius: 6px;

  img.open-course-bg {
    width: 100%;
    height: 100%;
    border-radius: 6px;
  }

  .open-course-info {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 20px 12px;

    .title {
      margin-bottom: 12px;
      font-weight: bold;
      font-size: 14px;
      line-height: 22px;
      color: #fff;
    }

    .time {
      margin-bottom: 12px;
      color: #fff;
      font-size: 12px;
      font-weight: 400;
      line-height: 20px;
      opacity: 0.8;
    }

    .student-num {
      color: #fff;
      font-size: 12px;
      font-weight: 400;
      line-height: 20px;
      opacity: 0.8;
    }
  }
}

.course-living {
  position: relative;
  width: 165px;
  height: 75px;
  margin-bottom: 12px;
  border-radius: 6px;

  > img {
    width: 100%;
    height: 100%;
    border-radius: 6px;
  }

  &__title {
    position: absolute;
    top: 26px;
    left: 16px;
    color: #184F98;
    font-size: 14px;
    line-height: 22px;
    font-weight: bold;
  }
}

.course-replay {
  position: relative;
  width: 165px;
  height: 75px;
  border-radius: 6px;

  > img {
    width: 100%;
    height: 100%;
    border-radius: 6px;
  }

   &__title {
    position: absolute;
    top: 26px;
    left: 16px;
    color: #060083;
    font-size: 14px;
    line-height: 22px;
    font-weight: bold;
  }
}
</style>

