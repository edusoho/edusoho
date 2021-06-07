<template>
  <a-modal
    title="复制班课"
    :visible="visible"
    :confirm-loading="confirmLoading"
    :ok-text="okText"
    cancelText="取消"
    @ok="handleOk"
    @cancel="handleCancel"
  >
  </a-modal>
</template>

<script>
import { MultiClass } from 'common/vue/service/index.js';

export default {
  name: 'CopyMultiClassModal',

  props: {
    visible: {
      type: Boolean,
      required: true
    },

    id: {
      type: [String, Number],
      required: true
    }
  },

  data() {
    return {
      confirmLoading: false,
      okText: '确认'
    };
  },

  methods: {
    handleOk(e) {
      this.confirmLoading = true;
      this.okText = '复制中';
      // const { success } = await MultiClass.copyMultiClass(multiClass.id);
      if ('success') {
        this.$message.success('复制成功');
        this.okText = '确认';
        this.confirmLoading = false;
        this.handleCancel();
      }
    },

    handleCancel() {
      this.$emit('event-communication', { event: 'cancal-modal' });
    },
  },
};
</script>
