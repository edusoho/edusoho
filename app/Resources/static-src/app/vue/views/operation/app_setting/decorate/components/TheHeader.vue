<template>
  <header class="decorate-header clearfix">
    <div class="pull-left mt8">
      <a-button type="link">退出编辑</a-button>
    </div>
    <div class="pull-right mt8">
      <a-space size="large">
        <a-button>保存并预览</a-button>
        <a-button type="primary" @click="handleClickSave">保存</a-button>
      </a-space>
    </div>
  </header>
</template>

<script>
export default {
  name: 'TheHeader',

  methods: {
    handleClickSave() {
      this.$emit('save');
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
