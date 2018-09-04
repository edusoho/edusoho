<template>
  <div class="preview-container">
    <div class="image-container clearfix">
      <div class="phone-img">
        <img  src="static/images/phone_shell.png">
        <mobile-preview class="preview-iframe" :feedback="false"></mobile-preview>
      </div>
      <div class="code-container">
        <div class="code-item">
          <img class="code-img" :src="qrcode">
          <div>手机扫码预览</div>
        </div>
        <el-button class="mrs btn-border-primary btn-common" @click="edit">返回编辑</el-button>
        <el-button class="btn-common btn-primary" @click="publish">发布</el-button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions, mapState } from 'vuex';
import mobilePreview from './mobile'

export default {
  data() {
    return  {
      qrcode: '',
    }
  },
  components: {
    mobilePreview
  },
  computed: {
    ...mapState(['draft'])
  },
  created() {
    const { preview, times, duration } = this.$route.query;
    console.log(preview, times, duration)
    this.getQrcode({
      preview,
      times,
      duration,
      route: 'homepage',
    }).then(res => {
      console.log(res);
      this.qrcode = res.img;
    });
  },
  methods: {
    ...mapActions([
      'getQrcode',
      'saveDraft',
    ]),
    edit() {
      this.$router.push({
        name: 'admin'
      })
    },
    publish() {
      this.saveDraft({
        data: this.draft,
        mode: 'published',
        portal: 'h5',
        type: 'discovery',
      }).then(() => {
        this.$message({
          message: '发布成功',
          type: 'success'
        });
        this.$router.push({
          name: 'admin',
        });
      }).catch(err => {
        this.$message({
          message: err.message || '发布失败，请重新尝试',
          type: 'error'
        });
      })
    }
  }
}
</script>
