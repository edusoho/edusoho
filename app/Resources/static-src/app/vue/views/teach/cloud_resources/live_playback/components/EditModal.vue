<template>
  <a-modal
    title="编辑"
    :visible="visible"
    :confirm-loading="confirmLoading"
    @ok="handleOk"
    @cancel="handleCancel"
  >
    <a-form-model ref="form" :model="form" :labelCol="{ span: 4 }" :wrapperCol="{ span: 20 }">
      <a-form-model-item label="标签">
        <a-select v-model="form.tag" placeholder="回放标签">
          <a-select-option v-for="tag in tags" :key="tag.id" :value="tag.id">
            {{ tag.name }}
          </a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item label="分享">
        <a-radio-group v-model="form.replayPublic">
          <a-radio value="1" class="mt8">
            共享到直播回放（其他老师可以查看、预览、引用该回放）
          </a-radio>
          <a-radio value="0" class="mt8">
            仅自己可见
          </a-radio>
        </a-radio-group>
      </a-form-model-item>
    </a-form-model>
  </a-modal>
</template>

<script>
import { LiveReplay } from 'common/vue/service';

export default {
  name: 'EditModal',

  props: {
    tags: {
      type: Array,
      required: true,
    }
  },

  data() {
    return {
      visible: false,
      confirmLoading: false,
      form: {
        id: undefined,
        tag: undefined,
        replayPublic: '0'
      }
    }
  },

  methods: {
    showModal({ id, replayPublic }) {
      this.form.id = id;
      this.form.replayPublic = replayPublic;
      this.visible = true;
    },

    handleCancel() {
      this.visible = false;
    },

    async handleOk() {
      this.confirmLoading = true;
      const params = {
        query: {
          id: this.form.id
        },
        params: this.form
      };
      const { success } = await LiveReplay.update(params);

      if (success) {
        this.$message.success('编辑成功');
        this.confirmLoading = false;
        this.visible = false;
        this.$emit('success', this.form);
      }
    }
  }
}
</script>
