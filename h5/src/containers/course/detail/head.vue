<template>
  <div class="course-detail__head pos-rl" id="course-detail__head">
    <div class="course-detail__nav--btn" @click="viewAudioDoc" v-if="textContent" v-show="['audio'].includes(sourceType) && !isEncryptionPlus && !isCoverOpen">
      文稿
    </div>
    <div class="course-detail__nav--cover web-view" :class="{ opened: isCoverOpen }" v-if="textContent" v-show="['audio'].includes(sourceType) && !isEncryptionPlus">
      <div class="media-text" v-html="textContent"></div>
      <div class="course-detail__nav--cover-control" v-show="isCoverOpen" @click="handlePlayer">
        <i class="h5-icon" :class="!isPlaying ? 'h5-icon-bofang' : 'h5-icon-zanting'"></i>
      </div>
      <div class="course-detail__nav--cover-close-btn" @click="hideAudioDoc">
        <i class="van-icon van-icon-arrow van-nav-bar__arrow"></i>
      </div>
    </div>
    <div class="course-detail__head--img" id="course-detail__head--img"
      v-show="sourceType === 'img' || isEncryptionPlus">
      <img v-if="courseSet.cover" :src="courseSet.cover.large" alt="">
      <countDown
        v-if="seckillActivities && seckillActivities.status === 'ongoing' && counting && !isEmpty"
        :activity="seckillActivities"
        @timesUp="expire"
        @sellOut="sellOut">
      </countDown>
    </div>
    <div id="course-detail__head--video"
      ref="video"
      v-show="['video', 'audio'].includes(sourceType) && !isEncryptionPlus">
    </div>
    <tagLink :tagData="tagData"></tagLink>
  </div>
</template>
<script>
import loadScript from 'load-script';
import { mapState } from 'vuex';
import Api from '@/api'
import { Toast,Dialog } from 'vant';
import countDown from '@/containers/components/e-marketing/e-count-down/index';
import tagLink from '@/containers/components/e-tag-link/e-tag-link';
import qs from 'qs';

export default {
  components: {
    countDown,
    tagLink,
  },
  data() {
    return {
      isEncryptionPlus: false,
      mediaOpts: {},
      isCoverOpen: false,
      isPlaying: false,
      player: null,
      counting: true,
      isEmpty: false,
      tagData: { // 分销标签信息
        earnings: 0,
        isShow: false,
        link: '',
        className: 'course-tag',
        minDirectRewardRatio: 0,
      },
      bindAgencyRelation: {}, // 分销代理商绑定信息
    };
  },
  props: {
    courseSet: {
      type: Object,
      default: () => {
        return {};
      },
    },
    seckillActivities: {
      type: Object,
      default: null
    }
  },
  computed: {
    ...mapState('course', {
      sourceType: state => state.sourceType,
      selectedPlanId: state => state.selectedPlanId,
      taskId: state => state.taskId,
      details: state => state.details,
      joinStatus: state => state.joinStatus,
      user: state => state.user,
    }),
    textContent() {
      return this.mediaOpts.text;
    }
  },
  watch: {
    taskId (value, oldValue) {
      // 未登录情况下，详情页面不需要初始化播放器
      if (this.$route.name === 'course' && !this.joinStatus) return;
      if (value > 0) this.initHead();
    },
  },
  created() {
    this.initHead();
    this.showTagLink();
    this.initTagData();
  },
  /*
  * 试看需要传preview=1
  * eg: /api/courses/1/task_medias/1?preview=1
  */
  methods: {
    initHead() {
      if (['video', 'audio'].includes(this.sourceType)) {
        window.scrollTo(0, 0);
        this.initPlayer();
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
    getParams () {
      const canTryLookable = !this.joinStatus
      return canTryLookable ? {
        query: {
          courseId: this.selectedPlanId,
          taskId: this.taskId
        }, params: {
          preview: 1
        }
      } : {
        query: {
          courseId: this.selectedPlanId,
          taskId: this.taskId
        }
      }
    },
    async initPlayer (){
      this.$refs.video && (this.$refs.video.innerHTML = '');

      const player = await Api.getMedia(this.getParams()).catch((err)=> {
        const courseId = Number(this.details.id);
        // 后台课程设置里设置了不允许未登录用户观看免费试看的视频
        if (err.code == 4040101) {
          this.$router.push({
            name: 'login',
            query: {
              redirect: `/course/${courseId}`
            }
          })
        }
        Toast.fail(err.message);
      })
      console.log(player)
      if (!player) return; //如果没有初始化成功

      if (player.mediaType === 'video' && !player.media.url) {
        Toast('课程内容准备中，请稍候查看')
        return;
      }

      const timelimit = player.media.timeLimit;


      this.isEncryptionPlus = player.media.isEncryptionPlus;
      if (player.media.isEncryptionPlus) {
        Toast('该浏览器不支持云视频播放，请下载App')
        return;
      }
      const media = player.media;
      const options = {
        id: 'course-detail__head--video',
        user: this.user,
        playlist: media.url,
        autoplay: true,
        disableFullscreen: this.sourceType === 'audio',
        isAudio: this.sourceType === 'audio',
        strictMode:!media.supportMobile, //视频是否加密 1表示普通  0表示加密
        pluck: {
          timelimit: timelimit,
        },
        resId: media.resId,
        disableDataUpload: true,
        // poster: "https://img4.mukewang.com/szimg/5b0b60480001b95e06000338.jpg"
      };
      // 试看判断
      const canTryLookable = !this.joinStatus && Number(this.details.tryLookable)
      if(!canTryLookable){
        delete options.pluck
      }

      this.mediaOpts = Object.assign({
        text: player.media.text
      }, options) ;

      this.$store.commit('UPDATE_LOADING_STATUS', true);
      this.loadPlayerSDK().then(SDK => {
        this.$store.commit('UPDATE_LOADING_STATUS', false);
        const player = new SDK(options);
        player.on('playing', () => {
          this.isPlaying = true;
        });
        player.on('unablePlay', () => { //加密模式下在不支持的浏览器下提示
        this.$refs.video.innerHTML = ''
          Dialog.alert({
            message: '当前内容不支持该手机浏览器观看，建议您使用Chrome、Safari浏览器观看。'
          }).then(() => {});
        });
        player.on('paused', () => {
          this.isPlaying = false;
        });
        this.player = player;
      })
    },
    loadPlayerSDK () {
      if (!window.VideoPlayerSDK) {
      const VEDIOURL='//service-cdn.qiqiuyun.net/js-sdk/video-player/sdk-v1.js?v='
      const scrptSrc =  VEDIOURL+ (Date.now() / 1000 / 60);
      // Cache SDK for 1 min.

        return new Promise((resolve, reject) => {
          loadScript(scrptSrc, (err) => {
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
      this.isEmpty = true
      this.$emit('goodsEmpty')
    },
    showTagLink() {
      Api.hasDrpPluginInstalled().then(res => {
        if (!res.Drp) {
          this.tagData.isShow = false;
          return;
        }

        Api.getAgencyBindRelation().then(data => {
          if (!data) {
            this.tagData.isShow = false;
            return;
          }
          this.bindAgencyRelation = data;
          this.tagData.isShow = true;
        })
      })
    },
    initTagData() {
      Api.getDrpSetting().then(data => {
        this.drpSetting = data;
        this.tagData.minDirectRewardRatio = data.minDirectRewardRatio;

        let params = {
          type: 'course',
          id: this.details.id,
          merchant_id: this.bindAgencyRelation.merchantId,
        };

        this.tagData.link = this.drpSetting.distributor_template_url + '?' + qs.stringify(params);
        this.tagData.earnings = (this.drpSetting.minDirectRewardRatio / 100) * this.details.price;
      });
    },
  }
}
</script>
