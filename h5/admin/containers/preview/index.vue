<template>
  <div class="preview-container">
    <div class="image-container clearfix">
      <img class="phone-img" src="static/images/phone_shell.png">
      <iframe class="preview-iframe" src="http://localhost:8011/" frameborder="0"></iframe>
      <div class="code-container">
        <div class="code-item">
          <img class="code-img" :src="qrcode">
          <div>手机扫码预览</div>
        </div>
        <el-button class="mrs btn-border-primary btn-common" @click="edit">返回编辑</el-button>
        <el-button class="btn-common btn-primary">发布</el-button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions } from 'vuex';


export default {
  data() {
    return  {
      qrcode: '',
    }
  },
  computed: {

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
      'getQrcode'
    ]),
    edit() {
      this.$router.push({
        name: 'admin'
      })
    }
  }
}
</script>
