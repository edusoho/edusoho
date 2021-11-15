<template>
  <a-modal
    :title="'modal.title.edit' | trans"
    :visible="visible"
    :confirm-loading="confirmLoading"
    @ok="handleOk"
    @cancel="handleCancel"
  >
    <a-form-model
      :model="form"
      :labelCol="{ span: 4 }"
      :wrapperCol="{ span: 20 }"
    >
      <a-form-model-item :label="'form.label.tag' | trans">
        <a-select
          mode="multiple"
          v-model="form.tagIds"
          :placeholder="'placeholder.playback_label' | trans"
        >
          <a-select-option
            v-for="tag in tags"
            :key="tag.id"
            :value="tag.id"
          >
            {{ tag.name }}
          </a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item :label="'form.label.share' | trans">
        <a-radio-group v-model="form.replayPublic">
          <a-radio value="1" class="mt8">
            {{ 'radio.share_to_live_playback' | trans }}
          </a-radio>
          <a-radio value="0" class="mt8">
            {{ 'radio.visible_only_to_yourself' | trans }}
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
        tagIds: undefined,
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
        this.$message.success(Translator.trans('message.edit_succeeded'));
        this.confirmLoading = false;
        this.visible = false;
        this.$emit('success', this.form);
      }
    }
  }
}
</script>
