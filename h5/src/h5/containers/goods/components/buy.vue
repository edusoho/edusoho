<template>
  <div class="info-buy">
    <van-action-sheet
      v-model="isShowForm"
      class="minHeight50"
      :title="userInfoCollectForm.formTitle"
      :close-on-click-overlay="false"
      :safe-area-inset-bottom="true"
    >
      <info-collection
        :userInfoCollectForm="userInfoCollectForm"
        :formRule="userInfoCollectForm.items"
        @submitForm="freeJoin"
      ></info-collection>
    </van-action-sheet>

    <div class="info-buy__collection" @click="onFavorite">
      <template v-if="isFavorite">
        <i class="iconfont icon-aixin1" style="color: #FF7E56;"></i>
        <span style="color: #FF7E56;">已收藏</span>
      </template>
      <template v-else>
        <i class="iconfont icon-aixin"></i>
        <span>收藏</span>
      </template>
    </div>

    <div class="info-buy__btn" v-if="currentSku.isMember" @click="handleJoin">
      去学习
    </div>
    <div
      class="info-buy__btn"
      :class="!accessToJoin ? 'disabled' : ''"
      v-else-if="currentSku.displayPrice != 0"
      @click="handleJoin"
    >
      {{ currentSku | filterGoodsBuyStatus(goods.type, vipAccessToJoin) }}
    </div>
    <div
      class="info-buy__btn"
      :class="!accessToJoin ? 'disabled' : ''"
      v-else
      @click="handleJoin"
    >
      <span v-if="accessToJoin">免费加入</span>
      <span v-else>
        {{ currentSku | filterGoodsBuyStatus(goods.type, vipAccessToJoin) }}
      </span>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapActions, mapMutations, mapState } from 'vuex';
import { Toast } from 'vant';
import collectUserInfo from '@/mixins/collectUserInfo';
import infoCollection from '@/components/info-collection.vue';
import * as types from '@/store/mutation-types';

export default {
  components: {
    infoCollection,
  },
  mixins: [collectUserInfo],
  data() {
    return {
      // isFavorite: false
      redirect: '',
      isShowForm: false,
    };
  },
  props: {
    isFavorite: {
      type: Boolean,
      default: false,
    },
    goods: {
      type: Object,
      default: () => {},
    },
    currentSku: {
      type: Object,
      default: () => {},
    },
  },
  methods: {
    ...mapActions('course', ['joinCourse']),
    ...mapMutations('classroom', {
      setCurrentJoinClass: types.SET_CURRENT_JOIN_CLASS,
    }),
    handleJoin() {
      if (this.currentSku.access.code === 'member.member_exist') {
        this.$router.push({
          path: `/${this.goods.type}/${this.currentSku.targetId}`,
        });
        return;
      }
      // 会员免费学
      const vipAccessToJoin = this.vipAccessToJoin;

      // 禁止加入
      if (!this.accessToJoin && !vipAccessToJoin) {
        return;
      }

      if (!this.$store.state.token) {
        this.$router.push({
          name: 'login',
          query: {
            redirect: this.redirect,
          },
        });
        return;
      }
      if (Number(this.currentSku.buyable) || vipAccessToJoin) {
        if (+this.currentSku.displayPrice && !vipAccessToJoin) {
          this.getOrder();
        } else {
          this.collectUseInfoEvent();
        }
      }
    },
    // 添加收藏
    addFavorite() {
      Api.addFavorite({
        data: {
          targetType: 'goods',
          targetId: this.$route.params.id,
        },
      }).then(res => {
        // console.log(res);
      });
    },
    // 移除收藏
    removeFavorite() {
      Api.removeFavorite({
        data: {
          targetType: 'goods',
          targetId: this.$route.params.id,
        },
      }).then(res => {
        // console.log(res);
      });
    },
    onFavorite() {
      if (!this.$store.state.token) {
        this.$router.push({
          name: 'login',
          query: {
            redirect: this.redirect,
          },
        });
        return;
      }
      if (this.isFavorite) {
        this.removeFavorite();
        this.$emit('update-data', false);
      } else {
        this.addFavorite();
        this.$emit('update-data', true);
      }
    },
    getParamsList() {
      this.paramsList = {
        action: 'buy_before',
        targetType: this.goods.type,
        targetId: this.currentSku.targetId,
      };
    },
    collectUseInfoEvent() {
      if (this.hasUserInfoCollectForm) {
        this.isShowForm = true;
        return;
      }
      Toast.loading({
        duration: 0,
        message: '加载中...',
        forbidClick: true,
      });
      this.getParamsList();
      this.getInfoCollectionEvent(this.paramsList).then(res => {
        console.log(Object.keys(res).length);
        if (Object.keys(res).length) {
          this.userInfoCollect = res;
          this.getInfoCollectionForm(res.id).then(res => {
            this.isShowForm = true;
            Toast.clear();
          });
          return;
        }
        this.freeJoin();
      });
    },
    freeJoin() {
      if (this.goods.type === 'course') {
        this.joinCourse({
          id: this.currentSku.targetId,
        })
          .then(res => {
            // 返回空对象，表示加入失败，需要去创建订单购买
            if (!(Object.keys(res).length === 0)) {
              this.$router.push({
                path: `/course/${this.currentSku.targetId}`,
              });
            }
          })
          .catch(err => {
            console.error(err);
          });
      }
      if (this.goods.type === 'classroom') {
        Api.joinClass({
          query: {
            classroomId: this.currentSku.targetId,
          },
        })
          .then(res => {
            this.setCurrentJoinClass(true);
            this.$router.push({
              path: `/classroom/${this.currentSku.targetId}`,
            });
          })
          .catch(err => {
            console.error(err.message);
          });
      }
    },
    getOrder() {
      this.$router.push({
        name: 'order',
        params: {
          id: this.currentSku.targetId,
        },
        query: {
          expiryScope: this.buyableModeHtml,
          targetType: `${this.goods.type}`,
        },
      });
    },
  },
  computed: {
    vipAccessToJoin() {
      let vipAccess = false;
      if (!this.currentSku.vipLevelInfo || !this.currentSku.vipUser) {
        return false;
      }

      if (
        this.currentSku.vipLevelInfo.seq <= this.currentSku.vipUser.level.seq
      ) {
        const vipExpired =
          parseInt(this.currentSku.vipUser.deadline) * 1000 <
          new Date().getTime();
        vipAccess = !vipExpired;
      }
      return vipAccess;
    },
    accessToJoin() {
      return (
        this.currentSku.access.code === 'success' ||
        this.currentSku.access.code === 'user.not_login' ||
        this.currentSku.access.code === 'member.member_exist'
      );
    },
    ...mapState(['vipSwitch']),
    buyableModeHtml() {
      const memberInfo = this.goods.member;
      if (!memberInfo) {
        switch (this.currentSku.usageMode) {
          case 'forever':
            return '长期有效';
          case 'end_date':
            return (
              this.formatDate(this.currentSku.usageEndTime.slice(0, 10)) +
              '&nbsp;之前可学习'
            );
          case 'days':
            return this.currentSku.usageDays + '天内可学习';
          case 'date':
            return (
              this.formatDate(this.currentSku.usageStartTime.slice(0, 10)) +
              '&nbsp;~&nbsp;' +
              this.formatDate(this.currentSku.usageEndTime.slice(0, 10))
            );
          default:
            return '';
        }
      } else {
        if (this.currentSku.usageMode === 'forever') {
          return '长期有效';
        }
        return memberInfo.deadline != 0
          ? memberInfo.deadline.slice(0, 10) + '之前可学习'
          : '长期有效';
      }
    },
  },
  created() {
    this.redirect = decodeURIComponent(this.$route.fullPath);
  },
};
</script>
