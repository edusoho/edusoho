<template>
  <div>
    {{ record.tasks.type | teachType }}

    <template v-if="progressStatus">
      <a-divider type="vertical" style="margin: 0 4px;" />
      <span v-if="progressStatus == 'created'" style="font-size: 14px; color: #fb8d4d;">未开始</span>
      <span v-else-if="progressStatus == 'start'" style="font-size: 14px; color: #43bc60;">直播中</span>
      <span v-else-if="progressStatus == 'closed'" style="font-size: 14px; color: #999;">已结束</span>
    </template>

    <template v-if="replayStatus">
      <br>
      <a-tag color="green" style="margin-top: 4px;">有回放</a-tag>
    </template>
  </div>
</template>

<script>
const type = {
  video: '视频',
  audio: '音频',
  live: '直播',
  discuss: '讨论',
  flash: 'Flash',
  doc: '文档',
  ppt: 'PPT',
  testpaper: '考试',
  homework: '作业',
  exercise: '练习',
  download: '下载资料',
  text: '图文'
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
      if (type == 'live' && ['generate', 'videoGenerate'].includes(ext.replayStatus)) {
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
