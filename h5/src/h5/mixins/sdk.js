import loadScript from 'load-script'
import { mapState, mapActions } from 'vuex'

export default {
  data() {
    return {
      sdkLoaded: false,
    }
  },
  computed: {
    ...mapState(['cloudSdkCdn']),
  },
  watch: {
    cloudSdkCdn() {
      if (this.sdkLoaded || window.QiQiuYun) return

      loadScript(`https://${this.cloudSdkCdn}/js-sdk-v2/sdk-v1.js?${Date.now()}`, () => {
        this.sdkLoaded = true
      })
    }
  },
  created() {
    if (!this.cloudSdkCdn && !window.QiQiuYun) {
      this.setCloudAddress();
    }
  },
  methods: {
    ...mapActions(['setCloudAddress']),
  }
}
