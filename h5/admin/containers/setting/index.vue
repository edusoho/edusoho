<template>
  <div class="setting-page">
    <img class="find-head-img" src="/static/images/find_head_url.jpg" alt="">
    <van-nav-bar :title="title"
      class="nav-bar"/>
    This is setting page!
  </div>
</template>

<script>
import Api from '@/api'

export default {
  data() {
    return  {
      title: 'EduSoho 微网校',
      showLeftArrow: false
    }
  },
}
</script>
