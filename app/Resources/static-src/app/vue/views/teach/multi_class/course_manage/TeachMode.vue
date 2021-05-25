<template>
  <div>
    {{ record.tasks.type | teachType }}

    <template v-if="progressStatus">
      <a-divider type="vertical" />
      <a-checkable-tag v-if="progressStatus">已结束</a-checkable-tag>
      <a-checkable-tag v-if="progressStatus" style="color: #43bc60;">直播中</a-checkable-tag>
      <a-checkable-tag v-if="progressStatus" style="color: #fb8d4d;">未开始</a-checkable-tag>
    </template>

    <template v-if="replayStatus">
      <br>
      <a-tag color="green" style="margin-top: 4px;">有回放</a-tag>
    </template>
  </div>
</template>

<script>
const type = {
  text: '图文',
  video: '视频',
  live: '直播',
  testpaper: '考试',
  homework: '作业'
};

export default {
  name: 'TeachMode',

  filters: {
    teachType(value) {
      return type[value];
    }
  },

  props: {
    record: {
      type: Object,
      required: true,
      default() {
        return {}
      }
    }
  },

  computed: {
    replayStatus() {
      const { tasks: { type, activity: { ext } }} = this.record;
      if (type == 'live' && ext.replayStatus == 'videoGenerate') {
        return true;
      }
      return false;
    },

    progressStatus() {
      const { tasks: { type, activity: { ext } }} = this.record;
      if (type == 'live') {
        return ext.progressStatus;
      }
      return '';
    }
  }
}
</script>
