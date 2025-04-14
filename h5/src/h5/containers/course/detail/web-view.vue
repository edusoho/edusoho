<template>
  <!-- 文档播放器 -->
  <div class="web-view">
    <out-focus-mask
      :type="outFocusMaskType"
      :isShow="isShowOutFocusMask"
      :reportType="reportType"
      @outFocusMask="outFocusMask"
    ></out-focus-mask>
    <e-loading v-if="isLoading" />
    <!-- web-view -->
    <div v-show="media !== 'text'" id="player" />
    <div v-show="media === 'text'" ref="text" class="media-text" />

    <!-- 学习上报按钮 -->
    <template v-if="joinStatus">
      <div v-if="isFinish" class="web-view--btn web-view--activebtn">
        <i class="iconfont icon-markdone"></i>
        学过了
      </div>

      <div v-if="!isFinish">
        <div class="web-view--btn" v-if="enableFinish" @click="toLearned">
          学过了
        </div>
        <div class="web-view--btn" v-if="!enableFinish" @click="toToast">
          完成条件
        </div>
      </div>
    </template>
    <!-- 学习上报按钮 -->
    <finishDialog
      v-if="finishDialog"
      :finishResult="finishResult"
      :courseId="courseId"
      @reloadPage="reloadPage"
    ></finishDialog>
  </div>
</template>
<script>
import loadScript from 'load-script';
import Api from '@/api';
import { mapState, mapMutations, mapActions } from 'vuex';
import * as types from '@/store/mutation-types';
import { Toast } from 'vant';
import report from '@/mixins/course/report';
import finishDialog from '../components/finish-dialog';
import OutFocusMask from '@/components/out-focus-mask.vue';
import {getTaskWatermark, destroyWatermark} from '@/utils/watermark';
import aiAgent from '@/mixins/aiAgent';

export default {
  components: {
    finishDialog,
    OutFocusMask,
  },
  mixins: [report, aiAgent],
  data() {
    return {
      finishCondition: undefined,
      enableFinish: false,
      media: '',
      isPreview: this.$route.query.preview,
      finishResult: null,
      finishDialog: false, // 下一课时弹出模态框
      courseId: null,
      taskId: null,
      type: null,
      player: null,
      aiAgentSdk: null,
    };
  },
  computed: {
    ...mapState(['cloudSdkCdn']),
    ...mapState('course', {
      details: state => state.details,
      joinStatus: state => state.joinStatus,
    }),
    ...mapState({
      isLoading: state => state.isLoading,
    }),
  },
  created() {
    this.courseId = this.$route.query.courseId;
    this.taskId = this.$route.query.taskId;
    this.type = this.$route.query.type;
  },
  async mounted() {
    this.initData();
    this.tryInitAIAgentSdk();
  },
  methods: {
    ...mapActions(['setCloudAddress']),
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE,
    }),
    tryInitAIAgentSdk() {
      Api.meCourseMember({
        query: {
          id: this.$route.query.courseId,
        },
      }).then(res => {
        if (res.aiTeacherEnabled) {
          this.aiAgentSdk = this.initAIAgentSdk(this.$store.state.user.aiAgentToken, {
            domainId: res.aiTeacherDomain,
            courseId: res.courseId,
            courseName: res.courseSetTitle,
          }, 60, null);
          if (res.studyPlanGenerated) {
            this.aiAgentSdk.setVariable('studyPlanGenerated' ,true)
          }
          this.aiAgentSdk.boot();
        }
      })
    },
    async initData() {
      if (this.joinStatus) {
        this.initReport();
      }
      this.enableFinish = !!parseInt(this.details.enableFinish);
      const player = await Api.getMedia(this.getParams()).catch(err => {
        Toast(err.message);
        return Promise.reject(err);
      });
      if (['ppt', 'doc'].includes(this.media)) {
        this.initPlayer(player);
      } else {
        // text类型不需要播放器
        this.$refs.text.innerHTML = player.media.content;
      }
    },
    initReport() {
      this.initReportData(this.courseId, this.taskId, this.type);
      this.finishDialog = false;
      this.getFinishCondition();
    },
    getFinishCondition() {
      this.getCourseData(this.courseId, this.taskId).then(res => {
        this.setNavbarTitle(res.title);
        this.finishCondition = res.activity && res.activity.finishCondition;
      });
    },
    toToast() {
      const condition = this.finishCondition;
      if (!condition) return;
      let message = '';
      if (condition.type === 'time') {
        const minute = Math.ceil((condition.data * 60 - this.learnedTime) / 60);
        message =
          minute > 0 ? `\n剩余 ${minute} 分完成` : '\n恭喜！你已完成该任务';
      }
      this.$toast({
        message: `完成条件：${condition.text}${message}`,
        position: 'bottom',
      });
    },

    /*
     * 试看需要传preview=1
     * eg: /api/courses/1/task_medias/1?preview=1
     */
    getParams() {
      const { courseId, taskId, type } = this.$route.query;
      const canTryLookable = !this.joinStatus && this.isPreview;
      this.media = type;

      return canTryLookable
        ? {
            query: {
              courseId,
              taskId,
            },
            params: {
              preview: 1,
              version: 'escloud',
            },
          }
        : {
            query: {
              courseId,
              taskId,
            },
            params: {
              version: 'escloud',
            },
          };
    },
    async initPlayer(playerParams) {
      const media = playerParams.media;
      // const playerSDKUri ="//service-cdn.qiqiuyun.net/js-sdk/sdk-v1.js?v="
      // + parseInt(Date.now() / 1000 / 60);
      if (!this.cloudSdkCdn) {
        await this.setCloudAddress();
      }
      const playerSDKUri =
        `//${this.cloudSdkCdn}/js-sdk-v2/sdk-v1.js?` +
        ~~(Date.now() / 1000 / 60);
      loadScript(playerSDKUri, async err => {
        if (err) throw err;

        const options = {
          id: 'player', // 用于初始化的DOM节点id
          resNo: media.resNo, // 想要播放的资源编号
          token: media.token,
          source: {
            type: playerParams.mediaType,
            args: media,
          },
        };
        const watermark = await getTaskWatermark();
        if (watermark.text) {
          options.fingerprint = {
            html: watermark.text,
            color: watermark.color,
            alpha: watermark.alpha,
          };
        }
        destroyWatermark();
        const player = new window.QiQiuYun.Player(options);
        this.player = player;
        player.on('ready', () => {});
        player.on('pagechanged', e => {
          if (e.page === e.total) {
            if (this.finishCondition && this.finishCondition.type === 'end') {
              this.reprtData({ eventName: 'finish' });
            }
          }
        });
      });
    },
    toLearned() {
      this.reprtData({ eventName: 'finish' }).then(res => {
        this.finishResult = res;
        this.finishDialog = true;
      });
    },
    reloadPage(data) {
      this.courseId = data.courseId;
      this.taskId = data.taskId;
      this.type = data.type;
      this.initData();
    },
  },
};
</script>
