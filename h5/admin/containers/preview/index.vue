<template>
  <div class="preview-container">
    <div class="image-container clearfix">
      <img class="phone-img" src="static/images/phone_shell.png">
      <div class="code-container">
        <div class="code-item">
          <img class="code-img" src="static/images/code_default.png">
          <div>手机扫码预览</div>
        </div>
        <el-button class="mrs btn-border-primary btn-common">返回编辑</el-button>
        <el-button class="btn-common btn-primary">发布</el-button>
      </div>
    </div>
  </div>
</template>

<script>
import Api from '@admin/api'

export default {
  data() {
    return  {

    }
  },
  computed: {

  },
  methods: {
  }
}
</script>
