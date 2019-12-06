<template>
  <div class="preview-container">
    <div class="image-container clearfix">
      <div :class="{'phone-img-app': from === 'appSetting'}" class="phone-img">
        <img :src="bgImg">
        <div :class="getTitleClass()">{{ settings.name }}</div>
        <mobile-preview :class="getClass()" :feedback="false"/>
      </div>
      <div class="code-container" >
        <div v-if="isH5" class="code-item">
          <div class="code-img-container"><img :src="qrcode" class="code-image"></div>
          <div class="help-text">扫描二维码在手机端预览<div>二维码60分钟内首次扫描有效</div></div>
        </div>
        <div v-else class="code-item__img">
          <img class="code-image" src="static/images/preview.png" >
        </div>
        <el-button class="mrs btn-border-primary btn-common" @click="edit">返回编辑</el-button>
        <el-button class="btn-common btn-primary" @click="publish">发布</el-button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions, mapState } from 'vuex'
import mobilePreview from './mobile'
import pathName2Portal from 'admin/config/api-portal-config'

export default {
  components: {
    mobilePreview
  },
  data() {
    return {
      qrcode: '',
      from: this.$route.query.from
    }
  },
  computed: {
    ...mapState(['draft', 'settings']),
    isMiniprogramSetting() {
      return this.from === 'miniprogramSetting'
    },
    isH5() {
      return this.from === 'h5Setting'
    },
    bgImg() {
      if (this.from === 'miniprogramSetting') {
        return 'static/images/miniprogram.png'
      } else if (this.from === 'appSetting') {
        return 'static/images/app.png'
      } else {
        return 'static/images/h5.png'
      }
    }
  },
  created() {
    const { preview, times, duration } = this.$route.query

    if (!this.isH5) {
      return
    }

    this.getQrcode({
      preview,
      times,
      duration,
      route: 'homepage'
    }).then(res => {
      this.qrcode = res.img
    }).catch((err) => {
      this.$message({
        message: err.message,
        type: 'error'
      })
    })
  },
  methods: {
    ...mapActions([
      'getQrcode',
      'saveDraft'
    ]),
    edit() {
      this.$router.push({
        name: this.from,
        query: {
          draft: 1
        }
      })
    },
    publish() {
      this.saveDraft({
        data: this.draft,
        mode: 'published',
        portal: pathName2Portal[this.from],
        type: 'discovery'
      }).then(() => {
        this.$message({
          message: '发布成功',
          type: 'success'
        })
        this.$router.push({
          name: this.from
        })
      }).catch(err => {
        this.$message({
          message: err.message || '发布失败，请重新尝试',
          type: 'error'
        })
      })
    },
    getClass() {
      if (this.from === 'miniprogramSetting') {
        return 'preview-iframe preview-iframe__miniprogram'
      } else if (this.from === 'appSetting') {
        return 'preview-iframe preview-iframe__app'
      } else {
        return 'preview-iframe'
      }
    },
    getTitleClass() {
      if (this.from === 'miniprogramSetting') {
        return 'preview-title__miniprogram'
      } else if (this.from === 'appSetting') {
        return 'preview-title__app'
      } else {
        return 'preview-title__h5'
      }
    }
  }
}
</script>
