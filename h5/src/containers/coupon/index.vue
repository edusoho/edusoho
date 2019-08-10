<template>
  <div class="receive-all">
    <e-loading v-if="isLoading"></e-loading>
    <fast-receive v-if="cloudSetting ==1 && sitePlugins==1" />
    <pass-receive v-if="cloudSetting ==2 || sitePlugins==2"/>
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
      sitePlugins: 0,
      cloudSetting: 0
    };
  },
  created() {
  this.getsitePlugins();
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
    })
  },
  methods: {
    //获取版本号
    getsitePlugins() {
      const that=this;
       Api.sitePlugins({
         query: {
          pluginName: 'coupon',
        }
        }).then(res => {
          //当前版本小于支持版本 true  大于false
          if(Object.keys(res).length>0){
            if(needUpgrade('2.2.10',res.version)){
              that.sitePlugins=1
            }else{
              that.sitePlugins=2
            }
            that.getsettingsCloud();
          }
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