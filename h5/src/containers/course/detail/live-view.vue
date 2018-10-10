<template>
  <div class="web-view">
    <e-loading v-if="isLoading"></e-loading>
    <!-- web-view -->
    <iframe id="player" :src="playUrl" width="100%" frameborder="0"></iframe>
  </div>
</template>
<script>
import Api from '@/api';
import { mapState, mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';
import { Toast } from 'vant';
import redirectMixin from '@/mixins/saveRedirect';

export default {
  data () {
    return {
      playUrl: '',
      requestCount: 0
    }
  },
  mixins: [redirectMixin],
  computed: {
    ...mapState('course', {
      joinStatus: state => state.joinStatus
    }),
    ...mapState({
      isLoading: state => state.isLoading
    })
  },
  async mounted () {
    if (!this.$store.state.token) {
      this.$router.push({
        name: 'login',
        query: {
          redirect: this.redirect
        }
      });
      return;
    }
    this.handleLive();
  },
  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE
    }),
    handleLive() {
      const { taskId, replay, title } = this.$route.query;
      this.setNavbarTitle(title)

      if (replay == 'true') { // query boolean 被转成字符串了
        this.getReplayUrl(taskId)
        return;
      }
      this.requestLiveNo(taskId)
    },
    getReplayUrl(taskId) {
      Api.getLiveReplayUrl({
        query: {
          taskId
        }
      }).then(res => {
        if(res.nonsupport) {
          Toast('回放暂不支持');
          return
        }
        if(res.url) {
          if (res.url.indexOf('/error/') > -1) {
            Toast('暂无回放');
          } else {
            this.playUrl = res.url;
          }
          return;
        }
        if (res.error) {
          Toast.fail(res.error.message);
        }
      }).catch(err => {
        Toast.fail(err.message);
      })
    },

    requestLiveNo(taskId) {
      Api.requestLiveNo({
        query: {
          taskId,
        }
      }).then(res =>{
        if(res.no) {
          this.getLiveUrl(taskId, res.no)
        }
        if (res.error) {
          Toast.fail(res.error.message);
        }
      }).catch(err => {
        Toast.fail(err.message);
      })
    },

    getLiveUrl(taskId, no) {
      this.requestCount ++
      Api.getLiveUrl({
        query: {
          taskId,
          no
        }
      }).then(res => {
        if (res.roomUrl) {
          this.playUrl = res.roomUrl;
        } else {
          if (this.requestCount < 30) {
            this.getLiveUrl(taskId, no)
          } else {
            Toast('获取直播失败')
          }
        }
        if (res.error) {
          Toast.fail(res.error.message);
        }
      }).catch(err => {
        Toast.fail(err.message);
      })
    }
  },
}
</script>
