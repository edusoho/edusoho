<template>
  <span>
    <span>{{ fullMobile || mobile || '--' }}</span>
    <span v-if="mobile && mobile.indexOf('1') > -1 && isShowIcon" class="es-icon es-icon-ice cursor-pointer" @click="showMobile"></span>
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
