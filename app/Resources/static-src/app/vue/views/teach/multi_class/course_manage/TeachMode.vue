<template>
  <div>
    {{ record.tasks.type | teachType }}
    <a-divider type="vertical" />
    <a-checkable-tag>已结束</a-checkable-tag>
    <a-checkable-tag style="color: #43bc60;">直播中</a-checkable-tag>
    <a-checkable-tag style="color: #fb8d4d;">未开始</a-checkable-tag>
    <br>
    <a-tag color="green" style="margin-top: 4px;">有回放</a-tag>
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

  props: {
    record: {
      type: Object,
      required: true,
      default() {
        return {}
      }
    }
  },

  filters: {
    teachType(value) {
      return type[value];
    }
  },
}
</script>
