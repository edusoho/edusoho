<template>
  <div class="receive-all">
    <e-loading v-if="isLoading"></e-loading>
    <fast-receive v-if="sitePlugins && cloudSetting ==1" />
    <pass-receive v-if="!sitePlugins && cloudSetting == 2" />
  </div>
</template>
<script>
import { mapState } from 'vuex';
import Api from "@/api";
import needUpgrade from '@/utils/version-compare';
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
    this.getsitePlugins();
    //this.getsettingsCloud();
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
    })
  },
  methods: {
    getsitePlugins() {
      const that=this;
       Api.sitePlugins({
         query: {
          pluginName: 'coupon',
        }
        }).then(res => {
          //当前版本小于支持版本 true  大于false
          that.sitePlugins=needUpgrade('2.2.9',res.version);
          that.getsettingsCloud();
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    //是否开启云短信
    getsettingsCloud() {
       Api.settingsCloud()
        .then(res => {
          //开启了云短信
          if (res.sms_enabled) {
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