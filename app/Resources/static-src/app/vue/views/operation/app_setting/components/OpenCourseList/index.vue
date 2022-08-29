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
          <div class="open-course-info" v-if="currentItem">
            <div class="title">{{ currentItem.title }}</div>
            <div class="time">
              <img :src="timeIcon" style="position: relative;top: -2px;width: 12px;height: 12px;" />
              <span v-html="formateTime(currentItem.lesson.startTime * 1000)"></span>
            </div>
            <div class="student-num">
              <img :src="numIcon" style="position: relative;top: -2px;width: 12px;height: 12px;" />
              <span>{{ currentItem.studentNum  + ' '}}{{ 'decorate.persons' | trans }}</span>
            </div>
          </div>
        </div>
        <div class="pull-left">
          <div class="course-living">
            <img class="course-living-bg" src="/static-dist/app/img/vue/decorate/living_bg.png" srcset="/static-dist/app/img/vue/decorate/living_bg@2x.png" />
            <div class="y-center" style="left: 12px;">
              <div class="course-living__title">{{ 'decorate.living' | trans }}</div>
              <div class="course-living__desc">Live streaming</div>
            </div>
          </div>
          <div class="course-replay">
            <img class="course-replay-bg" src="/static-dist/app/img/vue/decorate/replay_bg.png" srcset="/static-dist/app/img/vue/decorate/replay_bg@2x.png" />
            <div class="y-center" style="left: 12px;">
             <div class="course-replay__title">{{ 'decorate.replay' | trans }}</div>
              <div class="course-living__desc">Live Playback</div>
            </div>
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

  computed: {
    currentItem() {
      return this.list[0]
    }
  },

  methods: {
    async fetchOpenCourse() {
      const { categoryId, sourceType, items } = this.moduleData;
      if (sourceType === 'custom') {
        this.list = items;
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
    formateTime(value) {
      const date = new Date(value)
      const year = date.getFullYear()
      const month = date.getMonth() + 1
      const day = date.getDate()
      const hour = date.getHours()
      const minutes = date.getMinutes()

      return `<span>${year}-${month}-${day}</span> <br /> <span style="margin-left: 12px;">${hour}:${minutes}</span>`
    }
  }
}
</script>

<style lang="less" scoped>
.open-course-list {
  padding-right: 16px;
  padding-left: 16px;

  .open-course-list__title {
    position: relative;
    max-width: 60%;
    height: 24px;
    font-size: 16px;
    font-weight: 500;
    color: #333;
    line-height: 24px;
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

.y-center {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
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
    font-size: 14px;
    line-height: 22px;
    font-weight: 500;
    color: #184F98;
  }

  &__desc {
    font-weight: 500;
    font-size: 12px;
    line-height: 20px;
    color: #83BCDC;
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
    color: #060083;
    font-size: 14px;
    line-height: 22px;
    font-weight: 500;
  }

  &__desc {
    font-weight: 500;
    font-size: 12px;
    line-height: 20px;
    color: #A19FD9;
  }
}
</style>

