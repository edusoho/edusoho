<template>
  <span>
    <span>{{ fullMobile || mobile || '--' }}</span>
    <a-tooltip v-if="mobile && mobile.indexOf('1') > -1 && isShowIcon" color="#333">
      <template #title>
        <span>点击查看</span>
      </template>
      <span class="es-icon es-icon-ice cursor-pointer" @click="showMobile" style="color: #999;"></span>
    </a-tooltip>
  </span>
</template>

<script>
export default {
  name: 'MobileIce',
  props: {
    mobile: String,
    encryptedMobile: String,
  },
  data() {
    return {
      isShowIcon: true,
      fullMobile: ''
    }
  },
  methods: {
    showMobile() {
      $.post('/show_mobile', { encryptedMobile: this.encryptedMobile }).then(res => {
        if (res.mobile) {
          this.isShowIcon = false
          this.fullMobile = res.mobile
        }
      })
    },
    isMobile() {

    }
  },
}
</script>
