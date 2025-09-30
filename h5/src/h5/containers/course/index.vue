<template>
  <div class="course-detail">
    <e-loading v-if="isLoading" />
    <join-after :details="details" />
  </div>
</template>

<script>
import joinAfter from './join-after.vue';
import { mapState, mapActions, mapMutations } from 'vuex';
import { Dialog } from 'vant';
import * as types from '@/store/mutation-types';

export default {
  components: {
    joinAfter
  },

  computed: {
    ...mapState('course', {
      details: state => state.details,
    }),

    ...mapState({
      isLoading: state => state.isLoading,
    })
  },

  provide() {
    return {
      getDetailsContent: this.getData,
    }
  },

  watch: {
    $route(to, from) {
      this.getData();
    }
  },

  created() {
    this.getData();
  },

  methods: {
    ...mapActions('course', ['getCourseLessons']),

    ...mapMutations('course', {
      setSourceType: types.SET_SOURCETYPE,
    }),

    async getData() {
      await this.getCourseLessons({
        courseId: this.$route.params.id,
      }).then(res => {
        if (!res.member && !Number(this.details.parentId)) {
          this.$router.push({
            path: `/goods/${res.goodsId}/show`
          });
        }

        if (res.contract?.sign !== 'no' && res.member.isContractSigned == 0) {
          this.signContractConfirm(res)

          return
        }
      })
    },

    signContractConfirm(res) {
      const { id, goodsKey, name } = res.contract

      Dialog.confirm({
        title: this.$t('contract.signContractTitle'),
        message: this.$t('contract.signContractTips', { name }),
        confirmButtonText: this.$t('contract.sign'),
        cancelButtonText: this.$t('contract.cancel')
      }).then(() => {
        this.$router.push({ name: 'signContract', params: { id, goodsKey } })
      }).catch(() => {
        if (res.contract.sign === 'required') {
          this.$router.go(-1)
        }
      });
    },

    // 获取加入后课程目录和学习状态
    getJoinAfter() {
      this.getJoinAfterDetail({
        courseId: this.$route.params.id,
      }).catch(err => {
        this.$toast.fail(err.message);
      });
    }
  },

  beforeRouteEnter(to, from, next) {
		if (to.name === 'course' && from.name === 'testpaperResult' || to.name === 'course' && from.name === 'testpaperIntro') {
			window.location.reload();
		}
    next();
  },

  beforeRouteLeave(to, from, next) {
    this.setSourceType({
      sourceType: 'img',
      taskId: 0
    });
    next();
  }
};
</script>
