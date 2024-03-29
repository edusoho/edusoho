<template>
  <div class="inspection-card">
    <div class="inspection-card__info" :class="{ 'noborder': inspection.liveInfo.length == 0 }">
      <div class="inspection-card__title info text-overflow">班课名称：{{ inspection.multiClass.title }}</div>
      <div class="inspection-card__item info text-overflow">课时名称：{{ inspection.title }}</div>
      <div class="inspection-card__item info">开课时间：{{ $dateFormat(inspection.startTime, 'YYYY-MM-DD HH:mm') }}</div>
      <div class="inspection-card__item info">课程时长：{{ inspection.length }}分钟</div>
      <div class="inspection-card__item info">实时学员人数：{{ realTimeStudent }}/{{ inspection.studentNum }}</div>
      <div class="inspection-card__item info">授课教师：
        <span class="teacher">
          {{ inspection.teacherInfo.nickname }}
          <template v-if="inspection.liveInfo.length !== 0">
            <svg-icon v-if="isAttend('teacher', inspection.teacherInfo.id)" class="icon-check-circle" icon="icon-check-circle" />
            <svg-icon v-else class="icon-a-closecircle" icon="icon-a-closecircle" />
          </template>
        </span>
      </div>
      <div class="inspection-card__item info text-overflow" ref="assistant">助教出席：
        <span class="teacher" v-for="assistant in inspection.assistantInfo" :key="assistant.id">
          {{ assistant.nickname }}
          <template v-if="inspection.liveInfo.length !== 0">
            <svg-icon v-if="isAttend('assistant', assistant.id)" class="icon-check-circle" icon="icon-check-circle" />
            <svg-icon v-else class="icon-a-closecircle" icon="icon-a-closecircle" />
          </template>
        </span>
      </div>
      <a-popover v-if="ellipsis" class="inspection-card__popover">
        <template slot="content">
          <span class="teacher" v-for="assistant in inspection.assistantInfo" :key="assistant.id">
            {{ assistant.nickname }}
            <template v-if="inspection.liveInfo.length !== 0">
              <svg-icon v-if="isAttend('assistant', assistant.id)" style="width: 14px;height: 14px;color: #46c37b;" icon="icon-check-circle" />
              <svg-icon v-else icon="icon-a-closecircle" style="width: 14px;height: 14px;color: #ff6464;" />
            </template>
          </span>
        </template>
        <div class="empty-block"></div>
      </a-popover>
    </div>
    <div class="inspection-card__button">
      <div v-if="inspection.liveInfo.status === 'notOnTime'" class="inspection-card__button not-live-start">
        直播未按时开始
      </div>
      <div v-if="inspection.liveInfo.status === 'living'" class="inspection-card__button">
        <a class="live-start url-block" :href="inspection.liveInfo.viewUrl">
          <svg-icon class="icon-live" icon="icon-live" />
          进入直播
        </a>
      </div>
      <div v-if="inspection.liveInfo.status === 'finished' && inspection.activityInfo.ext.replayStatus !== 'ungenerated'" class="inspection-card__button live-start">
        <a class="live-start url-block" :href="inspection.liveInfo.viewUrl">
          <svg-icon class="icon-live" icon="icon-live-playback" />
          查看回放
        </a>
      </div>
      <div v-if="inspection.liveInfo.status === 'finished' && inspection.activityInfo.ext.replayStatus === 'ungenerated'" class="inspection-card__button live-start">
        <svg-icon class="icon-live" icon="icon-live-playback" />
        直播已结束，回放生成中
      </div>
      <div v-if="inspection.liveInfo.status === 'unstart'" class="inspection-card__button no-start-live">
        <svg-icon class="icon-live" icon="icon-no-start-live" style="width:24px;height:24px;top:4px" />
        直播未开始
      </div>
    </div>
  </div>
</template>

<script>
import _ from "lodash";

export default {
  name: "InspectionCard",
  components: {},
  props: {
    inspection: {
      type: Object,
      require: true,
      default: () => ({}),
    },
  },
  data() {
    return {
      ellipsis: false,
    };
  },
  computed: {
    realTimeStudent() {
      return this.inspection.liveInfo.viewerOnlineNum
        ? this.inspection.liveInfo.viewerOnlineNum
        : 0;
    },
  },
  mounted() {
    const assistantRef = this.$refs.assistant;
    this.ellipsis = assistantRef.scrollWidth > assistantRef.clientWidth;
  },
  methods: {
    isAttend(type, id) {
      const identity = {
        teacher: this.inspection.liveInfo.speakers,
        assistant: this.inspection.liveInfo.assistants,
      };
      return _.find(identity[type], ["userId", Number(id)]);
    },
  },
};
</script>
<style lang="less" scoped>
.inspection-card {
  background: #fff;
  box-shadow: 0 0 16px 0 rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  margin-top: 16px;

  .inspection-card__info {
    padding: 24px;
    border-bottom: 1px solid #f5f5f5;

    .inspection-card__title {
      font-size: 18px;
      color: #333;
      line-height: 28px;
      font-weight: 500;
    }

    .inspection-card__item {
      font-size: 14px;
      color: #333;
      letter-spacing: 0;
      line-height: 20px;
      font-weight: 400;
    }
  }
  .noborder {
    border-bottom: transparent !important;
  }
  .inspection-card__popover {
    position: absolute;
    right: 16px;
    bottom: 97px;
    height: 18px;
    width: 18px;
  }
  .icon-check-circle {
    width: 14px;
    height: 14px;
    color: #46c37b;
  }

  .icon-a-closecircle {
    width: 14px;
    height: 14px;
    color: #ff6464;
  }

  .teacher:not(:first-child) {
    margin-left: 24px;
  }

  .info {
    margin-bottom: 8px;
  }

  .inspection-card__button {
    height: 52px;
    border-radius: 0 0 12px 12px;
    text-align: center;
    line-height: 52px;
  }

  .not-live-start {
    color: #fff;
    background-color: #ff6464;
  }

  .live-start {
    font-size: 14px;
    color: #43bc60;
    letter-spacing: 0;
    font-weight: 400;
  }

  .no-start-live {
    font-size: 14px;
    color: #fb8d4d;
    letter-spacing: 0;
    font-weight: 400;
  }

  .icon-live {
    position: relative;
    top: 2px;
    width: 20px;
    height: 20px;
    margin-right: 4px;
  }
  .url-block {
    display: block;
    width: auto;
    height: auto;
  }
  .empty-block {
    width: 80%;
  }
}
</style>
