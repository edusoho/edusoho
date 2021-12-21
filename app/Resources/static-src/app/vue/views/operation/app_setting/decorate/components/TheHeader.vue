<template>
  <header class="decorate-header clearfix">
    <div class="pull-left mt8">
      <a-button type="link" @click="handleClickExit">退出编辑</a-button>
    </div>
    <div class="pull-right mt8">
      <a-space size="large">
        <a-button v-if="preview" @click="handleClickPreview(false)">退出预览</a-button>
        <a-button v-else @click="handleClickPreview(true)">预览</a-button>
        <a-button type="primary" @click="handleClickSave">保存</a-button>
      </a-space>
    </div>
  </header>
</template>

<script>
export default {
  name: 'TheHeader',

  props: {
    preview: {
      type: Boolean,
      required: true
    }
  },

  methods: {
    handleClickSave() {
      this.$emit('save');
    },

    handleClickPreview(value) {
      this.$emit('preview', value);
    },

    handleClickExit() {
      this.$confirm({
        title: '确定退出编辑？',
        content: '推出编辑将不会保存修改。',
        okText: '确定',
        okType: 'danger',
        cancelText: '取消',
        onOk() {
          window.location.href = '/admin/v2/setting/mobile_discoveries';
        }
      });
    }
  }
}
</script>

<style lang="less" scoped>
.decorate-header {
  position: relative;
  padding: 0 24px;
  width: 100%;
  height: 52px;
  background-color: #fff;
  border-bottom: 1px solid #f5f5f5;
}
</style>
