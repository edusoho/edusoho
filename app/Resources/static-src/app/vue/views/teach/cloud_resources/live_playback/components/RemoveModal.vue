<template>
  <a-modal
    :title="'site.btn.remove_playback' | trans"
    :visible="visible"
    @cancel="handleCancel"
  >
    是否确认移除回放资源？
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
export default {
  name: 'RemoveModal',

  data() {
    return {
      visible: false,
      confirmLoading: false
    }
  },

  methods: {
    showModal() {
      this.visible = true;
    },

    handleCancel() {
      this.visible = false;
    },

    handleOk() {
      console.log('ok');
    }
  }
}
</script>
