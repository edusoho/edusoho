<script>

export default {
  props: {
    visible: false,
    description: '',
  },
  methods: {
    handleCancel() {
      this.$emit('closed');
    }
  }
}

</script>
<template>
  <a-modal
    :visible="visible"
    :width="420"
    :centered="true"
    :maskClosable="true"
    :footer="null"
    title="试卷说明"
    @cancel="handleCancel"
  >
    <div v-html="description"></div>
  </a-modal>
</template>
