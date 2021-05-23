<template>
  <a-modal
    title="学员信息"
    :visible="visible"
    @cancel="handleCancel"
  >
    <a-descriptions bordered :column="1">
      <a-descriptions-item label="用户名">
        Zhou Maomao
      </a-descriptions-item>
      <a-descriptions-item label="Email">
        1810000000
      </a-descriptions-item>
      <a-descriptions-item label="用户组">
        Hangzhou, Zhejiang
      </a-descriptions-item>
      <a-descriptions-item label="用户名">
        Zhou Maomao
      </a-descriptions-item>
      <a-descriptions-item label="Email">
        1810000000
      </a-descriptions-item>
      <a-descriptions-item label="用户组">
        Hangzhou, Zhejiang
      </a-descriptions-item>
      <a-descriptions-item label="Address">
        No. 18, Wantang Road, Xihu District, Hangzhou, Zhejiang, China
      </a-descriptions-item>
      <a-descriptions-item label="Remark">
        empty
      </a-descriptions-item>
    </a-descriptions>
  </a-modal>
</template>

<script>
export default {
  props: {
    visible: {
      type: Boolean,
      required: true,
      default: false
    }
  },

  methods: {
    handleCancel() {
      this.$emit('handle-cancel')
    }
  }
}
</script>
