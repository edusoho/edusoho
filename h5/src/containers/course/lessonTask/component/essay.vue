<template>
  <div class="essay">
    <div v-html="essaydata.stem"></div>
    <van-field
      v-model="essaydata.answers"
      class="essay-input"
      label-width="0px"
      type="textarea"
      placeholder="请填写你的答案......"
      :autosize="{ maxHeight: 200,minHeight: 200 }"
    />
  </div>
</template>

<script>
export default {
  name: "essay-type",
  props: {
    essaydata: {
      type: Object,
      default: () => {}
    }
  }
};
</script>

<style>
</style>