<template>
  <div class="preview-container">
    <div class="image-container clearfix">
      <div class="phone-img">
        <img  src="static/images/phone_shell.png">
        <mobile-preview class="preview-iframe" :feedback="false"></mobile-preview>
      </div>
      <div class="code-container">
        <div class="code-item" v-if="qrcode">
          <div class="code-img-container"><img class="code-image" :src="qrcode"></div>
          <div class="help-text">扫描二维码在手机端预览<div>二维码60分钟内首次扫描有效</div></div>
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
import pathName2Portal from '@admin/utils/api-portal-config';

export default {
  data() {
    return  {
      qrcode: '',
      from: this.$route.query.from,
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
    console.log(this.from, 999)

    if (this.from === 'miniprogramSetting') {
      return;
    }

    this.getQrcode({
      preview,
      times,
      duration,
      route: 'homepage',
    }).then(res => {
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
        name: this.from,
        query: {
          draft: 1
        },
      })
    },
    publish() {
      this.saveDraft({
        data: this.draft,
        mode: 'published',
        portal: pathName2Portal[this.from],
        type: 'discovery',
      }).then(() => {
        this.$message({
          message: '发布成功',
          type: 'success'
        });
        this.$router.push({
          name: this.from,
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
