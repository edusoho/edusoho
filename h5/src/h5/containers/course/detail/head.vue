<template>
  <div id="course-detail__head" class="course-detail__head pos-rl">
    <div
      v-if="textContent"
      v-show="
        ['audio'].includes(sourceType) && !isEncryptionPlus && !isCoverOpen
      "
      class="course-detail__nav--btn"
      @click="viewAudioDoc"
    >
      文稿
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
      <img v-if="courseSet.cover" :src="courseSet.cover.large" alt />
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
    </div>
    <!-- 由于在安卓端弹出层会被视频遮挡，因此在弹出层显示时，隐藏视频，显示课程封面图，判断字段 finishDialog-->
    <div
      v-show="
        ['video', 'audio'].includes(sourceType) &&
          !isEncryptionPlus &&
          !finishDialog
      "
      id="course-detail__head--video"
      ref="video"
    />
    <!-- 学习上报按钮 -->
    <template v-if="showLearnBtn">
      <div
        v-if="isFinish"
        class="course-detail__head--btn course-detail__head--activebtn"
      >
        <i class="iconfont icon-markdone"></i>
        学过了
      </div>

      <div v-if="!isFinish">
        <div
          class="course-detail__head--btn"
          v-if="enableFinish"
          @click="toLearned"
        >
          学过了
        </div>
        <div
          class="course-detail__head--btn"
          v-if="!enableFinish"
          @click="toToast"
        >
          完成条件
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
import { mapState } from 'vuex';
import Api from '@/api';
import { Toast, Dialog } from 'vant';
import countDown from '&/components/e-marketing/e-count-down/index';
import tagLink from '&/components/e-tag-link/e-tag-link';
import finishDialog from '../components/finish-dialog';
import qs from 'qs';
import report from '@/mixins/course/report';

export default {
  components: {
    countDown,
    tagLink,
    finishDialog,
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
    ...mapState(['DrpSwitch']),
    ...mapState('course', {
      sourceType: state => state.sourceType,
      selectedPlanId: state => state.selectedPlanId,
      taskId: state => state.taskId,
      details: state => state.details,
      joinStatus: state => state.joinStatus,
      user: state => state.joinStatus?.user || {},
      allTask: state => state.allTask,
    }),
    showLearnBtn() {
      return this.joinStatus && ['video', 'audio'].includes(this.sourceType);
    },
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
    toToast() {
      if (this.finishCondition) {
        this.$toast({
          message: this.finishCondition.text,
          position: 'bottom',
        });
      }
    },
    isAndroid() {
      return !!navigator.userAgent.match(new RegExp('android', 'i'));
    },
    initHead() {
      if (['video', 'audio'].includes(this.sourceType)) {
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
      this.initReportData(this.selectedPlanId, this.taskId, this.sourceType);
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
        this.reprtData('finish');
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
        .then(res => {
          if (res.mediaType === 'audio') {
            this.formateAudioData(res);
          } else if (res.mediaType === 'video') {
            this.formateVedioData(res);
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
      const media = player.downloadMedia;
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
        disableDataUpload: true,
        watermark: {
          pos: 'top.right',
          width: 30,
          height: 30,
        },
      };
      this.$store.commit('UPDATE_LOADING_STATUS', true);
      this.initPlayer(options);
    },
    formateVedioData(player) {
      const media = player.media;
      const timelimit = media.timeLimit;
      // 视频试看判断
      const canTryLookable =
        !this.joinStatus && Number(this.details.tryLookable);
      // 不支持浏览器判断
      if (!media.isFinishConvert) {
        Toast('课程内容准备中，请稍候查看');
        return;
      }
      this.isEncryptionPlus = media.isEncryptionPlus;
      if (media.isEncryptionPlus) {
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
      };

      if (!canTryLookable) {
        delete options.pluck;
      }
      this.$store.commit('UPDATE_LOADING_STATUS', true);
      this.initPlayer(options);
    },
    initPlayer(options) {
      const playerSDKUri =
        '//service-cdn.qiqiuyun.net/js-sdk-v2/sdk-v1.js?' +
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
        player.on('ready', () => {});
        player.on('playing', () => {
          this.isPlaying = true;
          this.computeWatchTime();
        });
        player.on('paused', e => {
          this.isPlaying = false;
          this.clearComputeWatchTime();
          const watchTime = parseInt(this.nowWatchTime - this.lastWatchTime);
          this.lastWatchTime = this.nowWatchTime;
          this.reprtData('doing', true, watchTime);
        });
        player.on('ended', () => {
          this.clearComputeWatchTime();
          if (this.finishCondition && this.finishCondition.type === 'end') {
            this.reprtData('finish');
          }
        });
      });
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
      this.reprtData('finish').then(res => {
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
    },
    closeFinishDialog() {
      this.finishDialog = false;
    },
  },
};
</script>
