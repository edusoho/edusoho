<template>
  <div id="course-detail__head" class="course-detail__head pos-rl">
    <video-report-mask
      :type="outFocusMaskType"
      :isShow="isShowOutFocusMask"
      :reportType="reportType"
      @outFocusMask="outFocusMask"
    ></video-report-mask>
    <div
      v-if="textContent"
      v-show="
        ['audio'].includes(sourceType) && !isEncryptionPlus && !isCoverOpen
      "
      class="course-detail__nav--btn"
      @click="viewAudioDoc"
    >
      {{ $t('courseLearning.manuscripts') }}
    </div>
    <div
      v-if="textContent"
      v-show="['audio'].includes(sourceType) && !isEncryptionPlus"
      :class="{ opened: isCoverOpen }"
      class="course-detail__nav--cover web-view"
    >
      <div class="media-text" v-html="textContent" />
      <div
        v-show="isCoverOpen"
        class="course-detail__nav--cover-control"
        @click="handlePlayer"
      >
        <i
          :class="!isPlaying ? 'icon-bofang' : 'icon-zanting'"
          class="iconfont"
        />
      </div>
      <div class="course-detail__nav--cover-close-btn" @click="hideAudioDoc">
        <i class="van-icon van-icon-arrow van-nav-bar__arrow" />
      </div>
    </div>
    <div
      v-show="sourceType === 'img' || isEncryptionPlus || finishDialog"
      id="course-detail__head--img"
      class="course-detail__head--img"
    >

      <img v-if="courseSet.cover" :class="{ 'continue-learning-img': nextStudy.nextTask }" :src="courseSet.cover.large" alt />
      <div class="continue-learning" v-if="nextStudy.nextTask">
        <h3 class="continue-learning__title">{{ nextStudy.nextTask.title }}</h3>
        <div class="continue-learning__btn" @click="handleClickContinueLearning">{{ continueLearningText }}</div>
      </div>
      <countDown
        v-if="
          seckillActivities &&
            seckillActivities.status === 'ongoing' &&
            counting &&
            !isEmpty
        "
        :activity="seckillActivities"
        @timesUp="expire"
        @sellOut="sellOut"
      />
      <div class="wechat-subscribe-box">
        <wechat-subscribe />
      </div>
    </div>
    <!-- 由于在安卓端弹出层会被视频遮挡，因此在弹出层显示时，隐藏视频，显示课程封面图，判断字段 finishDialog-->
    <div v-show="!isShowOutFocusMask">
      <div
        v-show="
          ['video', 'audio', 'ppt'].includes(sourceType) &&
            !isEncryptionPlus &&
            !finishDialog
        "
        id="course-detail__head--video"
        ref="video"
      />
    </div>
    <!-- 学习上报按钮 -->
    <template v-if="showLearnBtn">
      <div
        v-if="isFinish"
        class="course-detail__head--btn course-detail__head--activebtn"
      >
        <i class="iconfont icon-markdone"></i>
        {{ $t('courseLearning.learned') }}
      </div>

      <div v-if="!isFinish">
        <div
          class="course-detail__head--btn"
          v-if="enableFinish"
          @click="toLearned"
        >
          {{ $t('courseLearning.learned') }}
        </div>
        <div
          class="course-detail__head--btn"
          v-if="!enableFinish"
          @click="toToast"
        >
          {{ $t('courseLearning.completionConditions') }}
        </div>
      </div>
    </template>
    <!-- 学习上报按钮 -->

    <tagLink :tag-data="tagData" />
    <finishDialog
      v-if="finishDialog"
      :finishResult="finishResult"
      :courseId="selectedPlanId"
      @closeFinishDialog="closeFinishDialog"
    ></finishDialog>
  </div>
</template>
<script>
import loadScript from 'load-script';
import { mapState, mapActions, mapMutations } from 'vuex';
import Api from '@/api';
import { Toast, Dialog } from 'vant';
import countDown from '&/components/e-marketing/e-count-down/index';
import tagLink from '&/components/e-tag-link/e-tag-link';
import finishDialog from '../components/finish-dialog';
import qs from 'qs';
import report from '@/mixins/course/report';
import VideoReportMask from '@/components/video-report-mask';
import WechatSubscribe from '../components/wechat-subscribe';
import * as types from '@/store/mutation-types.js';

export default {
  components: {
    countDown,
    tagLink,
    finishDialog,
    VideoReportMask,
    WechatSubscribe,
  },
  mixins: [report],
  props: {
    courseSet: {
      type: Object,
      default: () => {
        return {};
      },
    },
    seckillActivities: {
      type: Object,
      default: null,
    },
  },
  data() {
    return {
      finishCondition: undefined,
      learnMode: false,
      enableFinish: false,
      isEncryptionPlus: false,
      isCoverOpen: false,
      isPlaying: false,
      player: null,
      counting: true,
      isEmpty: false,
      textContent: null,
      tagData: {
        // 分销标签信息
        earnings: 0,
        isShow: false,
        link: '',
        className: 'course-tag',
        minDirectRewardRatio: 0,
      },
      timeChangingList: [],
      bindAgencyRelation: {}, // 分销代理商绑定信息
      finishResult: null,
      finishDialog: false, // 下一课时弹出模态框
      lastWatchTime: 0, // 上一次暂停上报的视频时间
      nowWatchTime: 0, // 当前刚看时间计时
    };
  },
  computed: {
    ...mapState(['DrpSwitch', 'cloudSdkCdn']),
    ...mapState('course', {
      sourceType: state => state.sourceType,
      selectedPlanId: state => state.selectedPlanId,
      taskId: state => state.taskId,
      details: state => state.details,
      joinStatus: state => state.joinStatus,
      user: state => state.joinStatus?.user || {},
      allTask: state => state.allTask,
      nextStudy: state => state.nextStudy
    }),
    showLearnBtn() {
      return this.joinStatus && ['video', 'audio', 'ppt'].includes(this.sourceType);
    },

    continueLearningText() {
      const { nextTask } = this.nextStudy;
      return nextTask && nextTask.result ? this.$t('courseLearning.continueLearning') : this.$t('courseLearning.startLearning');
    }
  },
  watch: {
    taskId(value, oldValue) {
      // 未登录情况下，详情页面不需要初始化播放器
      if (this.$route.name === 'course' && !this.joinStatus) return;
      if (value > 0) {
        this.initHead();
      }
    },
    selectedPlanId(value) {
      // 未登录情况下，详情页面不需要初始化播放器
      if (this.$route.name === 'course' && !this.joinStatus) return;
      if (value > 0) {
        this.initHead();
      }
    },
  },
  created() {
    this.initHead();
    this.showTagLink();
  },
  beforeDestroy() {
    if (this.sign.length > 0) {
      localStorage.setItem('flowSign', this.sign);
    }
    // 销毁播放器
    if (this.player && this.player.destory) {
      this.player.destory();
    }
    // 清除计时器
    this.clearComputeWatchTime();
  },
  /*
   * 试看需要传preview=1
   * eg: /api/courses/1/task_medias/1?preview=1
   */
  methods: {
    ...mapActions(['setCloudAddress']),

    ...mapMutations('course', {
      setSourceType: types.SET_SOURCETYPE
    }),

    toToast() {
      const condition = this.finishCondition;
      if (!condition) return;
      let message = '';
      if (['time', 'watchTime'].includes(condition.type)) {
        const minute = Math.ceil((condition.data * 60 - this.learnedTime) / 60);
        message =
          minute > 0 ? `\n剩余 ${minute} 分完成` : '\n恭喜！你已完成该任务';
      }
      this.$toast({
        message: `完成条件：${condition.text}${message}`,
        position: 'bottom',
      });
    },
    isAndroid() {
      return !!navigator.userAgent.match(new RegExp('android', 'i'));
    },
    initHead() {
      if (['video', 'audio', 'ppt'].includes(this.sourceType)) {
        window.scrollTo(0, 0);
        if (this.joinStatus) {
          this.initReport();
          this.clearComputeWatchTime();
          this.lastWatchTime = 0;
          this.nowWatchTime = 0;
        }
        this.initData();
      }
    },
    initReport() {
      this.finishDialog = false;
      this.getFinishCondition();
      this.IsLivePlayback();
    },
    getFinishCondition() {
      this.getCourseData(this.selectedPlanId, this.taskId).then(res => {
        this.finishCondition = res.activity && res.activity.finishCondition;
      });
    },
    // 直播视频回放刚进入课程就算学习完成
    IsLivePlayback() {
      if (this.allTask[this.taskId].type === 'live') {
        this.reprtData({ eventName: 'finish' });
      }
    },
    viewAudioDoc() {
      this.isCoverOpen = true;
    },
    hideAudioDoc() {
      this.isCoverOpen = false;
    },
    handlePlayer() {
      if (this.isPlaying) {
        return this.player && this.player.pause();
      }
      return this.player && this.player.play();
    },
    getParams() {
      const canTryLookable = !this.joinStatus;
      return canTryLookable
        ? {
            query: {
              courseId: this.selectedPlanId,
              taskId: this.taskId,
            },
            params: {
              preview: 1,
              version: 'escloud',
            },
          }
        : {
            query: {
              courseId: this.selectedPlanId,
              taskId: this.taskId,
            },
            params: {
              version: 'escloud',
            },
          };
    },
    initData() {
      this.isShowOutFocusMask = false;
      if (this.sign.length > 0) {
        localStorage.setItem('flowSign', this.sign);
        this.sign = '';
      }
      this.$refs.video && (this.$refs.video.innerHTML = '');
      // 是否为无限制任务
      this.enableFinish = !!parseInt(this.details.enableFinish);
      // 销毁播放器
      if (this.player && this.player.destory) {
        this.player.destory();
      }
      this.getData();
    },
    getData() {
      Api.getMedia(this.getParams())
        .then(async res => {
          const {
            media: { resNo },
            mediaType,
          } = res;

          if (resNo === '0') {
            const media = await Api.getLocalMediaLive({
              query: {
                taskId: this.taskId,
              },
              params: {
                hls_encryption: 1,
              },
            });
            delete res.media.resNo;
            res.media.url = media.mediaUri;
          }
          if (mediaType === 'audio') {
            this.formateAudioData(res);
            return;
          }
          if (mediaType === 'video') {
            this.formateVedioData(res);
            return;
          }
          if (mediaType === 'ppt') {
            this.formatePptData(res);
          }
        })
        .catch(err => {
          const courseId = Number(this.details.id);
          // 后台课程设置里设置了不允许未登录用户观看免费试看的视频
          if (err.code == 4040101) {
            this.$router.push({
              name: 'login',
              query: {
                redirect: `/course/${courseId}`,
              },
            });
          }
          Toast.fail(err.message);
        });
    },
    formateAudioData(player) {
      const media = player.media;
      if (!media.isFinishConvert) {
        Toast('课程内容准备中，请稍候查看');
        return;
      }
      // 不支持浏览器判断
      this.isEncryptionPlus = media.isEncryptionPlus;
      if (media.isEncryptionPlus) {
        Toast('该浏览器不支持云视频播放，请下载App');
        return;
      }
      // 音频文稿
      this.textContent = media.text;
      const options = {
        id: 'course-detail__head--video',
        user: this.user,
        resNo: media.resNo,
        token: media.token,
        autoplay: true,
        disableDataUpload: true,
        watermark: {
          pos: 'top.right',
          width: 30,
          height: 30,
        },
        rememberLastPos: true,
        playlist: media.url,
      };
      this.$store.commit('UPDATE_LOADING_STATUS', true);
      this.initPlayer(options);
    },
    formateVedioData(player) {
      const media = player.media;
      const timelimit = media.timeLimit;
      const securityVideoPlayer = media.securityVideoPlayer;
      // 视频试看判断
      const canTryLookable =
        !this.joinStatus && Number(this.details.tryLookable);
      // 不支持浏览器判断
      if (!media.isFinishConvert) {
        Toast('课程内容准备中，请稍候查看');
        return;
      }
      this.isEncryptionPlus = media.isEncryptionPlus;
      if (media.isEncryptionPlus && !this.isWechat() && securityVideoPlayer) {
        Toast('该浏览器不支持云视频播放，请用微信打开或下载App');
        return;
      } else if (media.isEncryptionPlus && !securityVideoPlayer) {
        Toast('该浏览器不支持云视频播放，请下载App');
        return;
      }

      const options = {
        id: 'course-detail__head--video',
        user: this.user,
        autoplay: true,
        disableFullscreen: this.sourceType === 'audio',
        strictMode: !media.supportMobile, // 视频是否加密 1表示普通  0表示加密
        pluck: {
          timelimit: timelimit,
        },
        resNo: media.resNo,
        disableDataUpload: true,
        watermark: {
          pos: 'top.right',
          width: 30,
          height: 30,
        },
        token: media.token,
        rememberLastPos: true,
        playlist: media.url,
      };

      if (media.isEncryptionPlus && this.isWechat && securityVideoPlayer) {
        options.playerType = 'wasm';
      }

      if (!canTryLookable) {
        delete options.pluck;
      }
      this.$store.commit('UPDATE_LOADING_STATUS', true);
      this.initPlayer(options);
    },

    async formatePptData(playerParams) {
      const media = playerParams.media;

      if (!this.cloudSdkCdn) {
        await this.setCloudAddress();
      }

      const playerSDKUri = `//${this.cloudSdkCdn}/js-sdk-v2/sdk-v1.js?` + ~~(Date.now() / 1000 / 60);

      loadScript(playerSDKUri, err => {
        if (err) throw err;

        const player = new window.QiQiuYun.Player({
          id: 'course-detail__head--video',
          resNo: media.resNo,
          token: media.token,
          source: {
            type: playerParams.mediaType,
            args: media
          }
        });
        this.player = player;
        player.on('ready', () => {
          this.initReportData(
            this.selectedPlanId,
            this.taskId,
            this.sourceType,
          );
        });
        player.on('pagechanged', e => {
          if (e.page === e.total) {
            if (this.finishCondition && this.finishCondition.type === 'end') {
              this.reprtData({ eventName: 'finish' });
            }
          }
        });
      });
    },

    async initPlayer(options) {
      if (!this.cloudSdkCdn) {
        await this.setCloudAddress();
      }
      const playerSDKUri =
        `//${this.cloudSdkCdn}/js-sdk-v2/sdk-v1.js?` +
        ~~(Date.now() / 1000 / 60);
      loadScript(playerSDKUri, err => {
        this.$store.commit('UPDATE_LOADING_STATUS', false);
        if (err) throw err;
        const player = new window.QiQiuYun.Player(options);
        this.player = player;
        player.on('unablePlay', () => {
          // 加密模式下在不支持的浏览器下提示
          this.$refs.video.innerHTML = '';
          Dialog.alert({
            message:
              '当前内容不支持该手机浏览器观看，建议您使用Chrome、Safari浏览器观看。',
          }).then(() => {});
        });
        player.on('ready', () => {
          this.initReportData(
            this.selectedPlanId,
            this.taskId,
            this.sourceType,
          );
        });
        player.on('playing', () => {
          this.isPlaying = true;
          this.clearComputeWatchTime();
          this.computeWatchTime();
        });
        player.on('paused', e => {
          this.isPlaying = false;
          this.clearComputeWatchTime();
          this.reprtData({
            eventName: 'doing',
            ContinuousReport: true,
          });
        });
        player.on('ended', () => {
          this.clearComputeWatchTime();
          if (this.finishCondition && this.finishCondition.type === 'end') {
            this.reprtData({ eventName: 'finish' });
          }
        });
      });
    },
    isWechat() {
      const ua = navigator.userAgent.toLowerCase();
      if (ua.match(/MicroMessenger/i) == 'micromessenger') {
        return true;
      } else {
        return false;
      }
    },
    expire() {
      this.counting = false;
    },
    sellOut() {
      this.isEmpty = true;
      this.$emit('goodsEmpty');
    },
    showTagLink() {
      if (!this.DrpSwitch) {
        this.tagData.isShow = false;
        return;
      }
      this.initTagData();
      this.getAgencyBindRelation();
    },
    getAgencyBindRelation() {
      Api.getAgencyBindRelation().then(data => {
        if (!data.agencyId) {
          this.tagData.isShow = false;
          return;
        }
        this.bindAgencyRelation = data;
        this.tagData.isShow = true;
      });
    },
    initTagData() {
      Api.getDrpSetting().then(data => {
        this.drpSetting = data;
        this.tagData.minDirectRewardRatio = data.minDirectRewardRatio;

        const params = {
          type: 'course',
          id: this.details.id,
          merchant_id: this.drpSetting.merchantId,
        };

        this.tagData.link =
          this.drpSetting.distributor_template_url + '?' + qs.stringify(params);
        const earnings =
          (this.drpSetting.minDirectRewardRatio / 100) * this.details.price;
        this.tagData.earnings = (Math.floor(earnings * 100) / 100).toFixed(2);
      });
    },
    toLearned() {
      this.reprtData({ eventName: 'finish' }).then(res => {
        this.finishResult = res;
        this.finishDialog = true;
      });
    },
    // 计算观看时长计时器，一直累加 nowWatchTime
    computeWatchTime() {
      this.intervalWatchTime = setInterval(() => {
        this.nowWatchTime++;
      }, 1000);
    },
    // 清除计时器
    clearComputeWatchTime() {
      clearInterval(this.intervalWatchTime);
      this.intervalWatchTime = null;
    },
    closeFinishDialog() {
      this.finishDialog = false;
    },

    handleClickContinueLearning() {
      const { id } = this.nextStudy.nextTask;
      const params = {
        courseId: this.selectedPlanId,
        taskId: id
      };

      Api.getCourseData({ query: params }).then(res => {
        this.toLearnTask(res);
      });
    },

     // 跳转到task
    toLearnTask(task) {
      // 课程再创建阶段或者和未发布状态
      if (task.status === 'create') {
        Toast('课时创建中，敬请期待');
        return;
      }
      // const nextTask = {
      //   id: task.id
      // };
      // 更改store中的当前学习
      this.$store.commit(`course/${types.GET_NEXT_STUDY}`, { nextTask: task });
      this.showTypeDetail(task);
      this.show = false;
      this.setSourceType({
        sourceType: 'img',
        taskId: task.id,
      });
    },

    showTypeDetail(task) {
      if (task.status !== 'published') {
        Toast('敬请期待');
        return;
      }
      switch (task.type) {
        case 'video':
          this.setSourceType({
            sourceType: 'video',
            taskId: task.id,
          });
          break;
        case 'audio':
          this.setSourceType({
            sourceType: 'audio',
            taskId: task.id,
          });
          break;
        case 'ppt':
          this.setSourceType({
            sourceType: 'ppt',
            taskId: task.id,
          });
          break;
        case 'text':
        case 'doc':
          this.$router.push({
            name: 'course_web',
            query: {
              courseId: this.selectedPlanId,
              taskId: task.id,
              type: task.type,
              backUrl: `/course/${this.selectedPlanId}`,
            },
          });
          break;
        case 'live':
          // eslint-disable-next-line no-case-declarations
          const nowDate = new Date();
          // eslint-disable-next-line no-case-declarations
          const endDate = new Date(task.endTime * 1000);
          // const startDate = new Date(task.startTime * 1000);
          // eslint-disable-next-line no-case-declarations
          let replay = false;
          if (nowDate > endDate) {
            if (
              task.activity &&
              task.activity.replayStatus === 'videoGenerated'
            ) {
              // 本站文件
              if (task.mediaSource === 'self') {
                this.setSourceType({
                  sourceType: 'video',
                  taskId: task.id,
                });
              }
              return;
            } else if (
              task.activity &&
              task.activity.replayStatus === 'ungenerated'
            ) {
              Toast('暂无回放');
              return;
            } else {
              replay = true;
            }
          }

          this.$router.push({
            name: 'live',
            query: {
              courseId: this.selectedPlanId,
              taskId: task.id,
              type: task.type,
              title: task.title,
              replay,
            },
          });
          break;
        case 'testpaper':
          // eslint-disable-next-line no-case-declarations
          const testId = task.activity.testpaperInfo.testpaperId;
          this.$router.push({
            name: 'testpaperIntro',
            query: {
              testId: testId,
              targetId: task.id,
            },
          });
          break;
        case 'homework':
          this.$router.push({
            name: 'homeworkIntro',
            query: {
              courseId: this.selectedPlanId,
              taskId: task.id,
            },
          });
          break;
        case 'exercise':
          this.$router.push({
            name: 'exerciseIntro',
            query: {
              courseId: this.selectedPlanId,
              taskId: task.id,
            },
          });
          break;
      }
    }
  },
};
</script>
