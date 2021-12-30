<template>
  <header class="decorate-header clearfix">
    <div class="pull-left mt8">
      <a-button type="link" @click="handleClickExit">{{ 'site.btn.exit_editing' | trans }}</a-button>
    </div>
    <div class="pull-right mt8">
      <a-space size="large">
        <a-button v-if="preview" @click="handleClickPreview(false)">{{ 'site.btn.exit_preview' | trans }}</a-button>
        <a-button v-else @click="handleClickPreview(true)">{{ 'site.btn.preview' | trans }}</a-button>
        <a-button type="primary" @click="handleClickSave">{{ 'site.btn.save' | trans }}</a-button>
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
        title: Translator.trans('decorate.are_you_sure_exiting_editing'),
        content: Translator.trans('decorate.exiting_editing_not_save'),
        okText: Translator.trans('site.confirm'),
        okType: 'danger',
        cancelText: Translator.trans('site.cancel'),
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
