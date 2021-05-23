<template>
  <div>
    <span>{{ record.tasks.type | teachType }}</span>
    <span class="class-status-tag">已结束</span>
    <!-- <span class="class-status-tag" style="color: #43bc60;">直播中</span> -->
    <!-- <span class="class-status-tag" style="color: #fb8d4d;">未开始</span> -->
    <br>
    <a-tag color="green" style="margin-top: 4px;">有回放</a-tag>
  </div>
</template>

<script>
const type = {
  text: '文本',
  video: '视频',
  live: '直播'
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

<style lang="less" scoped>
.class-status-tag {
  position: relative;
  padding-left: 16px;
  color: #999;

  &::before {
    content: "";
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 1px;
    height: 12px;
    background-color: #999;
  }
}
</style>
