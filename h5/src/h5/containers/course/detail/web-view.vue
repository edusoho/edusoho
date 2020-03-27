<template>
  <!-- 文档播放器 -->
  <div class="web-view">
    <e-loading v-if="isLoading"/>
    <!-- web-view -->
    <div v-show="media !== 'text'" id="player"/>
    <div v-show="media === 'text'" ref="text" class="media-text"/>
    <div v-if="learnMode">
      <div class="course-detail__head--btn course-detail__head--activebtn" v-if="enableFinish">
        <i class="iconfont icon-markdone"></i>
        学过了
      </div>
      <div class="course-detail__head--btn" v-if="enableFinish" @click="toToast">完成条件</div>
    </div>
  </div>
</template>
<script>
import loadScript from 'load-script'
import Api from '@/api'
import { mapState, mapMutations } from 'vuex'
import * as types from '@/store/mutation-types'
import { Toast } from 'vant'
import TaskPipe from '@/utils/task-pipe/index'

export default {
  data() {
    return {
      finishCondition: undefined,
      learnMode: false,
      enableFinish: false,
      media: '',
      isPreview: this.$route.query.preview,
      taskPipe: undefined,
      pageLength: 0,
    }
  },
  computed: {
    ...mapState('course', {
      details: state => state.details,
      joinStatus: state => state.joinStatus
    }),
    ...mapState({
      isLoading: state => state.isLoading
    })
  },
  async mounted() {
    this.initTaskPipe();
    const player = await Api.getMedia(this.getParams()).catch(err => {
      Toast(err.message)
      return Promise.reject(err)
    })
    this.learnMode = this.details.learnMode !== 'freeMode';
    this.enableFinish = !!this.details.enableFinish;
    if (['ppt', 'doc'].includes(this.media)) {
      this.initPlayer(player)
      this.pageLength = (player.media && player.media.images && player.media.images.length) || 0;
    } else {
      // text类型不需要播放器
      this.$refs.text.innerHTML = player.media.content
      this.setNavbarTitle(player.media.title)
    }
  },
  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE
    }),
    toToast() {
      if (this.finishCondition) {
        this.$toast({
          message: this.finishCondition.text,
          position: 'bottom'
        });
      }
    },
    initTaskPipe() {
      if (this.taskPipe) {
        return;
      }
      const { courseId, taskId, type } = this.$route.query
      this.taskPipe = new TaskPipe({
        reportData: {
          courseId: this.selectedPlanId,
          taskId: this.taskId
        },
      });
      this.taskPipe.on('courseData', (res) => {
        this.pageLength = res.length;
      });
      this.taskPipe.on('courseData', (res) => {
        this.finishCondition = res.activity && res.activity.finishCondition;
      });
      this.taskPipe.on('report.finish', () => {
        this.enableFinish = true;
      })
      setInterval(() => {
        const duration = Math.floor(this.taskPipe.getDuration() / 60000);
        this.taskPipe.trigger('time', duration);
      }, 1000);
    },
    /*
    * 试看需要传preview=1
    * eg: /api/courses/1/task_medias/1?preview=1
    */
    getParams() {
      const { courseId, taskId, type } = this.$route.query
      const canTryLookable = !this.joinStatus && this.isPreview
      this.media = type

      return canTryLookable ? {
        query: {
          courseId,
          taskId
        }, params: {
          preview: 1
        }
      } : {
        query: {
          courseId,
          taskId
        }
      }
    },
    initPlayer(player) {
      const media = player.media
      const playerSDKUri = '//service-cdn.qiqiuyun.net/js-sdk/sdk-v1.js?v=' +
      // const playerSDKUri = '//oilgb9e2p.qnssl.com/js-sdk/sdk-v1.js?v=' // 测试 sdk
       (Date.now() / 1000 / 60)

      loadScript(playerSDKUri, (err) => {
        if (err) throw err

        const player = new window.QiQiuYun.Player({
          id: 'player', // 用于初始化的DOM节点id
          // playServer: 'play.test.qiqiuyun.cn', // 测试 playServer
          resNo: media.resId, // 想要播放的资源编号
          token: media.token, // 请求播放的认证token
          source: {
            type: player.mediaType,
            args: media
          }
        })
        player.on('ready', () => {
          this.taskPipe.clearInterval();
          this.taskPipe.initInterval();
        })
        player.on('pagechanged', (e) => {
          if (e.page === this.pageLength) {
            this.taskPipe.trigger('end');
          }
        })
      })
    }
  }
}
</script>
