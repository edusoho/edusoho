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
        <span style="color: #FF7E56;">{{ $t('goods.collected') }}</span>
      </template>
      <template v-else>
        <i class="iconfont icon-aixin"></i>
        <span>{{ $t('goods.favorites') }}</span>
      </template>
    </div>

    <div class="info-buy__btn" :class="classDisabled" @click="handleJoin">
      {{ buyStatus }}
    </div>

    <!-- <div class="info-buy__btn" v-if="currentSku.isMember" @click="handleJoin">
      去学习
    </div> -->

    <!-- 不免费课程 -->
    <!-- <div
      class="info-buy__btn"
      :class="classDisabled"
      v-else-if="currentSku.displayPrice != 0"
      @click="handleJoin"
    >
      {{ currentSku | filterGoodsBuyStatus(goods.type, vipAccessToJoin) }}
    </div> -->

    <!-- 免费课程 -->
    <!-- <div
      class="info-buy__btn"
      :class="classDisabled"
      v-else
      @click="handleJoin"
    >
      <span v-if="accessToJoin">免费加入</span>
      <span v-else>
        {{ currentSku | filterGoodsBuyStatus(goods.type, vipAccessToJoin) }}
      </span>
    </div> -->
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
  data() {
    return {
      // isFavorite: false
      redirect: '',
      isShowForm: false,
    };
  },
  created() {
    this.redirect = decodeURIComponent(this.$route.fullPath);
  },
  computed: {
    ...mapState(['vipSwitch']),

    // 会员是否有效
    vipAccessToJoin() {
      let vipAccess = false;
      const { vipLevelInfo, vipUser } = this.currentSku;
      if (!vipLevelInfo || !vipUser) {
        return false;
      }

      if (!vipUser.level) return vipAccess;

      if (vipLevelInfo.seq <= vipUser.level.seq) {
        const vipExpired =
          parseInt(vipUser.deadline) * 1000 < new Date().getTime();
        vipAccess = !vipExpired;
      }
      return vipAccess;
    },

    accessToJoin() {
      const code = this.currentSku.access.code;
      return (
        code === 'success' ||
        code === 'user.not_login' ||
        code === 'member.member_exist'
      );
    },

    buyableModeHtml() {
      const memberInfo = this.goods.member;
      if (!memberInfo) {
        switch (this.currentSku.usageMode) {
          case 'forever':
            return this.$t('goods.longTermEffective');
          case 'end_date':
            return (
              this.formatDate(this.currentSku.usageEndTime.slice(0, 10)) +
              `&nbsp;${this.$t('goods.canLearnBefore')}`
            );
          case 'days':
            return this.$t('goods.studyWithinDay', { number: this.currentSku.usageDays });
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
          return this.$t('goods.longTermEffective');
        }
        return memberInfo.deadline != 0
          ? memberInfo.deadline.slice(0, 10) + this.$t('goods.canLearnBefore')
          : this.$t('goods.longTermEffective');
      }
    },

    // 购买按钮样式展示
    classDisabled() {
      const code = this.currentSku.access.code;
      const status = [
        'user.locked',
        'course.reach_max_student_num',
        'classroom.reach_max_student_num',
        'course.not_found',
        'classroom.not_found',
        'course.unpublished',
        'classroom.unpublished',
        'course.closed',
        'classroom.closed',
        'course.not_buyable',
        'classroom.not_buyable',
        'course.buy_expired',
        'classroom.buy_expired',
        'course.expired',
        'classroom.expired',
      ];
      return {
        disabled: status.includes(code),
      };
    },

    /**
     * 购买按钮状态
     * currentSku // 当前计划信息
     */
    buyStatus() {
      const {
        isMember,
        displayPrice,
        access: { code },
        vipLevelInfo,
      } = this.currentSku;

      // 已加入, 去学习
      if (isMember) {
        return this.$t('goods.toLearn');
      }

      // 不可加入 + 会员免费学 时的文案
      const onlyVipJoinWay = vipLevelInfo ? `${vipLevelInfo.name}免费` : '';

      // currentSku.access.code 存在的状态
      const status = {
        success: '立即购买',
        'user.not_login': '立即购买',
        'user.locked': '用户被锁定',
        'member.member_exist': '课程学员已存在',
        'course.reach_max_student_num': '学员达到上限',
        'classroom.reach_max_student_num': '学员达到上限',
        'course.not_found': '计划不存在',
        'classroom.not_found': '计划不存在',
        'course.unpublished': '课程未发布',
        'classroom.unpublished': '班级未发布',
        'course.closed': '课程已关闭',
        'classroom.closed': '班级已关闭',
        'course.not_buyable': '课程无法学习，请联系老师',
        'classroom.not_buyable': '班级无法学习，请联系老师',
        'course.buy_expired': '购买有效期已过',
        'classroom.buy_expired': '购买有效期已过',
        'course.expired': '学习有效期已过',
        'classroom.expired': '学习有效期已过',
        'course.only_vip_join_way': onlyVipJoinWay,
        'classroom.only_vip_join_way': onlyVipJoinWay,
      };

      // 会员有效且不是以下状态时, 会员免费兑换
      const notVipStatus = [
        'member.member_exist',
        'course.buy_expired',
        'classroom.buy_expired',
        'course.expired',
        'classroom.expired',
      ];

      if (this.vipAccessToJoin && !notVipStatus.includes(code)) {
        return this.$t('goods.freeRedemptionForMembers');
      }

      if (displayPrice == 0 && this.accessToJoin) {
        return this.$t('goods.freeToJoin');
      }

      return status[code];
    },
  },
  methods: {
    ...mapActions('course', ['joinCourse']),
    ...mapMutations('classroom', {
      setCurrentJoinClass: types.SET_CURRENT_JOIN_CLASS,
    }),
    handleJoin() {
      const type = this.goods.type;
      const {
        isMember,
        access: { code },
        targetId,
        vipLevelInfo,
        buyable,
        displayPrice,
      } = this.currentSku;

      if (code === 'member.member_exist' || isMember) {
        this.$router.push({
          path: `/${type}/${targetId}`,
        });
        return;
      }
      // 会员免费学
      const vipAccessToJoin = this.vipAccessToJoin;

      // 班级和课程, 仅会员可加入, code都是 course.only_vip_join_way
      const goToVipCode = [
        'course.only_vip_join_way',
        'classroom.only_vip_join_way',
      ];

      // 不是会员跳转到会员页面
      if (goToVipCode.includes(code) && !vipAccessToJoin) {
        this.$router.push({
          path: '/vip',
          query: { id: vipLevelInfo.id },
        });
      }

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
      if (Number(buyable) || vipAccessToJoin) {
        if (+displayPrice && !vipAccessToJoin) {
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
        message: this.$t('toast.loading'),
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
    formatDate(time, fmt = 'yyyy-MM-dd') {
      time = time * 1000;
      const date = new Date(time);
      if (/(y+)/.test(fmt)) {
        fmt = fmt.replace(
          RegExp.$1,
          (date.getFullYear() + '').substr(4 - RegExp.$1.length),
        );
      }
      const o = {
        'M+': date.getMonth() + 1,
        'd+': date.getDate(),
        'h+': date.getHours(),
        'm+': date.getMinutes(),
        's+': date.getSeconds(),
      };
      for (const k in o) {
        if (new RegExp(`(${k})`).test(fmt)) {
          const str = o[k] + '';
          fmt = fmt.replace(
            RegExp.$1,
            RegExp.$1.length === 1 ? str : ('00' + str).substr(str.length),
          );
        }
      }
      return fmt;
    },
  },
};
</script>
