<template>
  <div class="info-buy">
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
    <div @click="handleJoin" v-if="currentSku.isMember" class="info-buy__btn">
      去学习
    </div>
    <div
      @click="handleJoin"
      v-else-if="currentSku.displayPrice != 0"
      :class="!accessToJoin ? 'disabled' : ''"
      class="info-buy__btn"
    >
      {{
        currentSku.access.code
          | filterGoodsBuyStatus(goods.type, vipAccessToJoin)
      }}
    </div>
    <div
      @click="handleJoin"
      v-else
      :class="!accessToJoin ? 'disabled' : ''"
      class="info-buy__btn"
    >
      <span v-if="accessToJoin">免费加入</span
      ><span v-else>{{
        currentSku.access.code
          | filterGoodsBuyStatus(goods.type, vipAccessToJoin)
      }}</span>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapActions, mapState } from 'vuex';
export default {
  data() {
    return {
      // isFavorite: false
      redirect: '',
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
    handleJoin() {
      if (this.currentSku.access.code === 'member.member_exist') {
        this.$router.push({
          path: `/${this.goods.type}/${this.currentSku.targetId}`,
        });
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
      // const endDate = this.currentSku.usageEndTime;
      // const endDateStamp = new Date(endDate).getTime();
      // const todayStamp = new Date().getTime();
      // let isPast = todayStamp < endDateStamp;
      // endDate == 0 ? (isPast = true) : (isPast = todayStamp < endDateStamp);
      if (Number(this.currentSku.buyable) || vipAccessToJoin) {
        if (+this.currentSku.displayPrice && !vipAccessToJoin) {
          this.getOrder();
        } else {
          if (this.goods.type === 'course') {
            this.joinCourse({
              id: this.currentSku.targetId,
            })
              .then(res => {
                // 返回空对象，表示加入失败，需要去创建订单购买
                if (!(Object.keys(res).length === 0)) {
                } else {
                  this.getOrder();
                }
                this.$router.push({
                  path: `/course/${this.currentSku.targetId}`,
                });
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
                this.$router.push({
                  path: `/classroom/${this.currentSku.targetId}`,
                });
              })
              .catch(err => {
                console.error(err.message);
              });
          }
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
        console.log(res);
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
        console.log(res);
      });
    },
    onFavorite() {
      if (this.isFavorite) {
        this.isFavorite = false;
        this.removeFavorite();
      } else {
        this.isFavorite = true;
        this.addFavorite();
      }
    },
    getOrder() {
      // const expiryMode = this.details.learningExpiryDate.expiryMode;
      // const expiryScopeStr = `${this.startDateStr} 至 ${this.endDateStr}`;
      // const expiryStr =
      //   expiryMode === 'date' ? expiryScopeStr : this.learnExpiry;
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
          new Date(this.currentSku.vipUser.deadline).getTime() <
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
