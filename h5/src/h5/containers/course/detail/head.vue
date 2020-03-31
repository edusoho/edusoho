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
      v-show="sourceType === 'img' || isEncryptionPlus"
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

    <div
      v-show="['video', 'audio'].includes(sourceType) && !isEncryptionPlus"
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
      :nextTask="nextTask"
      :completionRate="completionRate"
      :courseId="selectedPlanId"
    ></finishDialog>
  </div>
</template>
<script>
import loadScript from "load-script";
import { mapState } from "vuex";
import Api from "@/api";
import { Toast, Dialog } from "vant";
import countDown from "&/components/e-marketing/e-count-down/index";
import tagLink from "&/components/e-tag-link/e-tag-link";
import finishDialog from "../components/finish-dialog";
import qs from "qs";
import report from "@/mixins/course/report";
//import TaskPipe from "@/utils/task-pipe/index";

export default {
  components: {
    countDown,
    tagLink,
    finishDialog
  },
  mixins: [report],
  props: {
    courseSet: {
      type: Object,
      default: () => {
        return {};
      }
    },
    seckillActivities: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      finishCondition: undefined,
      learnMode: false,
      enableFinish: false,
      isEncryptionPlus: false,
      mediaOpts: {},
      isCoverOpen: false,
      isPlaying: false,
      player: null,
      counting: true,
      isEmpty: false,
      tagData: {
        // 分销标签信息
        earnings: 0,
        isShow: false,
        link: "",
        className: "course-tag",
        minDirectRewardRatio: 0
      },
      // currentTime: 0,
      // startTime: 0,
      timeChangingList: [],
      // taskPipe: undefined,
      bindAgencyRelation: {}, // 分销代理商绑定信息
      nextTask: null, //下一课时信息
      completionRate: 0, //任务完成率
      finishDialog: false, //下一课时弹出模态框
      watchTime: 0
    };
  },
  computed: {
    ...mapState(["DrpSwitch"]),
    ...mapState("course", {
      sourceType: state => state.sourceType,
      selectedPlanId: state => state.selectedPlanId,
      taskId: state => state.taskId,
      details: state => state.details,
      joinStatus: state => state.joinStatus,
      user: state => state.user
    }),
    textContent() {
      return this.mediaOpts.text;
    },
    showLearnBtn() {
      return ["video", "audio"].includes(this.sourceType);
    }
  },
  watch: {
    taskId(value, oldValue) {
      // 未登录情况下，详情页面不需要初始化播放器
      if (this.$route.name === "course" && !this.joinStatus) return;
      if (value > 0) {
        this.initHead();
      }
    },
    selectedPlanId() {
      // 未登录情况下，详情页面不需要初始化播放器
      if (this.$route.name === "course" && !this.joinStatus) return;
      if (value > 0) {
        this.initHead();
      }
    }
  },
  created() {
    this.initHead();
    this.showTagLink();
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
          position: "bottom"
        });
      }
    },
    // watchTime() {
    //   if (this.isAndroid() && this.taskPipe) {
    //     return Math.floor(this.taskPipe.getDuration() / 60000);
    //   }
    //   let timeCount = this.currentTime - this.startTime;
    //   this.timeChangingList.forEach(item => {
    //     timeCount += (item.end - item.start);
    //   });
    //   return Math.floor(timeCount / 60);
    // },
    isAndroid() {
      return !!navigator.userAgent.match(new RegExp("android", "i"));
    },
    initHead() {
      if (["video", "audio"].includes(this.sourceType)) {
        window.scrollTo(0, 0);
        this.initReport();
        this.initPlayer();
      }
    },
    initReport() {
      this.initReportData(this.selectedPlanId, this.taskId, this.sourceType);
      this.finishDialog = false;
      this.getFinishCondition();
    },
    getFinishCondition() {
      this.getCourseData(this.selectedPlanId, this.taskId).then(res => {
        this.finishCondition = res.activity && res.activity.finishCondition;
      });
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
              taskId: this.taskId
            },
            params: {
              preview: 1
            }
          }
        : {
            query: {
              courseId: this.selectedPlanId,
              taskId: this.taskId
            }
          };
    },
    async initPlayer() {
      this.$refs.video && (this.$refs.video.innerHTML = "");
      this.enableFinish = !!parseInt(this.details.enableFinish);
      const player = await Api.getMedia(this.getParams()).catch(err => {
        const courseId = Number(this.details.id);
        // 后台课程设置里设置了不允许未登录用户观看免费试看的视频
        if (err.code == 4040101) {
          this.$router.push({
            name: "login",
            query: {
              redirect: `/course/${courseId}`
            }
          });
        }
        Toast.fail(err.message);
      });
      if (!player) return; // 如果没有初始化成功

      if (player.mediaType === "video" && !player.media.url) {
        Toast("课程内容准备中，请稍候查看");
        return;
      }

      const timelimit = player.media.timeLimit;

      this.isEncryptionPlus = player.media.isEncryptionPlus;
      if (player.media.isEncryptionPlus) {
        Toast("该浏览器不支持云视频播放，请下载App");
        return;
      }
      const media = player.media;
      const options = {
        id: "course-detail__head--video",
        user: this.user,
        playlist: media.url,
        autoplay: true,
        disableFullscreen: this.sourceType === "audio",
        isAudio: this.sourceType === "audio",
        strictMode: !media.supportMobile, // 视频是否加密 1表示普通  0表示加密
        pluck: {
          timelimit: timelimit
        },
        resId: media.resId,
        disableDataUpload: true
        // poster: "https://img4.mukewang.com/szimg/5b0b60480001b95e06000338.jpg"
      };
      // 试看判断
      const canTryLookable =
        !this.joinStatus && Number(this.details.tryLookable);
      if (!canTryLookable) {
        delete options.pluck;
      }

      this.mediaOpts = Object.assign(
        {
          text: player.media.text
        },
        options
      );

      this.$store.commit("UPDATE_LOADING_STATUS", true);
      this.loadPlayerSDK().then(SDK => {
        this.$store.commit("UPDATE_LOADING_STATUS", false);
        if (this.player) {
          this.player.taskId = -1;
        }
        const player = new SDK(options);
        player.taskId = this.taskId;
        this.player = player;
        player.on("playing", () => {
          this.isPlaying = true;
        });
        player.on("unablePlay", () => {
          // 加密模式下在不支持的浏览器下提示
          this.$refs.video.innerHTML = "";
          Dialog.alert({
            message:
              "当前内容不支持该手机浏览器观看，建议您使用Chrome、Safari浏览器观看。"
          }).then(() => {});
        });
        player.on("paused", () => {
          this.isPlaying = false;
          if (player.taskId !== this.taskId) {
            return;
          }
          this.reprtData();
        });
        player.on("ready", () => {
          if (player.taskId !== this.taskId) {
            return;
          }
          // this.intervalReportData();
          // this.intervalReportLearnTime();
        });
        player.on("datapicker.start", e => {
          if (player.taskId !== this.taskId) {
            return;
          }
          console.log(11);
        });
        player.on("ended", () => {
          if (player.taskId !== this.taskId) {
            return;
          }
          if (this.finishCondition.type === "end") {
            this.reprtData("finish");
          }
        });
        player.on("timeupdate", e => {
          if (player.taskId !== this.taskId) {
            return;
          }
          this.watchTime = e.currentTime;
          // if (this.finishCondition.type === "time") {
          //   if (e.currentTime / 60 >= parseInt(this.finishCondition.data)) {
          //     this.reprtData("finish");
          //   }
          // }
          // this.currentTime = e.currentTime;
          // this.taskPipe.trigger('time', this.watchTime());
        });
      });
    },
    loadPlayerSDK() {
      if (!window.VideoPlayerSDK) {
        const VEDIOURL =
          "//service-cdn.qiqiuyun.net/js-sdk/video-player/sdk-v1.js?v=";
        const scrptSrc = VEDIOURL + Date.now() / 1000 / 60;
        // Cache SDK for 1 min.

        return new Promise((resolve, reject) => {
          loadScript(scrptSrc, err => {
            if (err) {
              reject(err);
            }
            resolve(window.VideoPlayerSDK);
          });
        });
      }
      return Promise.resolve(window.VideoPlayerSDK);
    },
    expire() {
      this.counting = false;
    },
    sellOut() {
      this.isEmpty = true;
      this.$emit("goodsEmpty");
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
          type: "course",
          id: this.details.id,
          merchant_id: this.drpSetting.merchantId
        };

        this.tagData.link =
          this.drpSetting.distributor_template_url + "?" + qs.stringify(params);
        const earnings =
          (this.drpSetting.minDirectRewardRatio / 100) * this.details.price;
        this.tagData.earnings = (Math.floor(earnings * 100) / 100).toFixed(2);
      });
    },
    toLearned() {
      this.reprtData("finish").then(res => {
        this.nextTask = res.nextTask;
        this.completionRate = res.completionRate;
        this.finishDialog = true;
      });
    }
  }
};
</script>
