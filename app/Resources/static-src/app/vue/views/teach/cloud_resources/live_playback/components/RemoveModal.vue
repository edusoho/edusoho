<template>
  <a-modal
    :title="'site.btn.remove_playback' | trans"
    :visible="visible"
    @cancel="handleCancel"
  >
    {{ 'modal.content.remove_playback_resources' | trans }}
    <template slot="footer">
      <div class="clearfix">
        <span class="pull-left" style="color: #fe4040; margin-top: 7px;">
          {{ 'live.playback.tip.cannot_be_used_normally' | trans }}
        </span>
        <a-button type="danger" :loading="confirmLoading" @click="handleOk">
          {{ 'site.btn.confirm' | trans }}
        </a-button>
      </div>
    </template>
  </a-modal>
</template>

<script>
import { LiveReplay } from 'common/vue/service';

export default {
  name: 'RemoveModal',

  data() {
    return {
      visible: false,
      confirmLoading: false,
      currentId: undefined
    }
  },

  methods: {
    showModal(id) {
      this.currentId = id;
      this.visible = true;
    },

    handleCancel() {
      this.visible = false;
    },

    async handleOk() {
      this.confirmLoading = true;
      const params = {
        params: {
          ids: [this.currentId],
          realDelete: false
        }
      };

      const { success } = await LiveReplay.delete(params);

      if (success) {
        this.$message.success(Translator.trans('message.removal_succeeded'));
        this.confirmLoading = false;
        this.visible = false;
        this.$emit('success', this.currentId);
      }
    }
  }
}
</script>
