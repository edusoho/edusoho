<template>
  <div class="receive-all">
    <e-loading v-if="isLoading"></e-loading>
    <fast-receive v-if="cloudSetting ==1" />
    <pass-receive v-if="cloudSetting == 2" />
  </div>
</template>
<script>
import { mapState } from 'vuex';
import Api from "@/api";
import { Toast } from "vant";
import fastReceive from "./fastReceive";
import passReceive from "./passReceive";
export default {
  components: {
    fastReceive,
    passReceive
  },
  data() {
    return {
      sitePlugins: false,
      cloudSetting: 0
    };
  },
  created() {
    //this.getsitePlugins();
    this.getsettingsCloud();
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
    })
  },
  methods: {
    async getsitePlugins() {
      await Api.sitePlugins()
        .then(res => {
          this.sitePlugins = true;
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    //是否开启云短信
    async getsettingsCloud() {
      await Api.settingsCloud()
        .then(res => {
          //开启了云短信
          if (res.sms_enabled == 1) {
            this.cloudSetting = 1;
          } else {
            this.cloudSetting = 2;
          }
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    }
  }
};
</script>