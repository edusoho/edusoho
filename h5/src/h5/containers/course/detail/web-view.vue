<template>
  <!-- 文档播放器 -->
  <div class="web-view">
    <e-loading v-if="isLoading" />
    <!-- web-view -->
    <div v-show="media !== 'text'" id="player" />
    <div v-show="media === 'text'" ref="text" class="media-text" />

    <!-- 学习上报按钮 -->
    <template>
      <div
        v-if="isFinish"
        class="web-view--btn web-view--activebtn"
      >
        <i class="iconfont icon-markdone"></i>
        学过了
      </div>

      <div v-if="!isFinish">
        <div
          class="web-view--btn"
          v-if="enableFinish"
          @click="toLearned"
        >
          学过了
        </div>
        <div
          class="web-view--btn"
          v-if="!enableFinish"
          @click="toToast"
        >
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
import loadScript from "load-script";
import Api from "@/api";
import { mapState, mapMutations } from "vuex";
import * as types from "@/store/mutation-types";
import { Toast } from "vant";
import report from "@/mixins/course/report";
import finishDialog from "../components/finish-dialog";
export default {
  components: {
    finishDialog
  },
  mixins: [report],
  data() {
    return {
      finishCondition: undefined,
      enableFinish: false,
      media: "",
      isPreview: this.$route.query.preview,
      finishResult:null,
      finishDialog: false, //下一课时弹出模态框
      courseId: null,
      taskId: null,
      type: null,
      player: null
    };
  },
  computed: {
    ...mapState("course", {
      details: state => state.details,
      joinStatus: state => state.joinStatus
    }),
    ...mapState({
      isLoading: state => state.isLoading
    })
  },
  created() {
    this.courseId = this.$route.query.courseId;
    this.taskId = this.$route.query.taskId;
    this.type = this.$route.query.type;
  },
  async mounted() {
    this.initData();
  },
  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE
    }),
    async initData() {
      this.initReport();
      this.enableFinish = !!parseInt(this.details.enableFinish);
      const player = await Api.getMedia(this.getParams()).catch(err => {
        Toast(err.message);
        return Promise.reject(err);
      });
      if (["ppt", "doc"].includes(this.media)) {
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
      if (this.finishCondition) {
        this.$toast({
          message: this.finishCondition.text,
          position: "bottom"
        });
      }
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
              taskId
            },
            params: {
              preview: 1
            }
          }
        : {
            query: {
              courseId,
              taskId
            }
          };
    },
    initPlayer(playerParams) {
      const media = playerParams.media;
      const playerSDKUri =
        "//service-cdn.qiqiuyun.net/js-sdk/sdk-v1.js?v=" +
        // const playerSDKUri = '//oilgb9e2p.qnssl.com/js-sdk/sdk-v1.js?v=' // 测试 sdk
        parseInt(Date.now() / 1000 / 60);

      loadScript(playerSDKUri, err => {
        if (err) throw err;

        const player = new window.QiQiuYun.Player({
          id: "player", // 用于初始化的DOM节点id
          // playServer: 'play.test.qiqiuyun.cn', // 测试 playServer
          resNo: media.resId, // 想要播放的资源编号
          token: media.token, // 请求播放的认证token
          source: {
            type: playerParams.mediaType,
            args: media
          }
        });
        this.player = player;
        player.on("ready", () => {
          // this.intervalReportData();
          // this.intervalReportLearnTime();
        });
        player.on("pagechanged", e => {
          if (e.pageNum === e.total) {
            if (this.finishCondition.type === "end") {
              this.reprtData("finish");
            }
          }
        });
      });
    },
    toLearned() {
      this.reprtData("finish").then(res => {
        this.finishResult=res;
        this.finishDialog = true;
      });
    },
    reloadPage(data) {
      this.courseId = data.courseId;
      this.taskId = data.taskId;
      this.type = data.type;
      this.initData();
    }
  }
};
</script>
