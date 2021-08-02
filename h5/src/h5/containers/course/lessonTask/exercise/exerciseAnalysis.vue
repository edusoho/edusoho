<template>
  <div class="paper-swiper">
    <out-focus-mask
      :type="outFocusMaskType"
      :isShow="isShowOutFocusMask"
      :reportType="reportType"
      @outFocusMask="outFocusMask"
    ></out-focus-mask>
    <e-loading v-if="isLoading" />
    <item-bank
      v-if="info.length > 0"
      :is-wrong-mode="isWrongMode"
      :current.sync="cardSeq"
      :info="info"
      :answer="answer"
      :slide-index.sync="slideIndex"
      :can-do="canDo"
      :all="allList.length"
      :is-exercise="true"
    />
    <!-- 底部 -->
    <div class="paper-footer">
      <div>
        <span @click="cardShow = true">
          <i class="iconfont icon-Questioncard" />
          {{ $t('courseLearning.questionCard') }}
        </span>
      </div>
      <div>
        <span
          :class="{ 'footer__div__span--active': isWrongMode }"
          @click="showWrongList"
        >
          <i class="cuoti">
            <img
              :src="
                isWrongMode
                  ? 'static/images/cuoti-active.png'
                  : 'static/images/cuoti.png'
              "
              alt
            />
          </i>
          {{ $t('courseLearning.wrongQuestion') }}
        </span>
      </div>
    </div>
    <!-- 答题卡 -->
    <van-popup v-model="cardShow" position="bottom">
      <div v-if="info.length > 0" class="card">
        <div class="card-title">
          <div>
            <span class="card-right">{{ $t('courseLearning.right') }}</span>
            <span class="card-wrong">{{ $t('courseLearning.wrong') }}</span>
            <span class="card-nofinish">{{ $t('courseLearning.unanswered') }}</span>
            <span class="card-subjective">主观题</span>
          </div>
          <i class="iconfont icon-no" @click="cardShow = false" />
        </div>
        <div class="card-list">
          <div class="card-homework-item">
            <div class="card-item-list">
              <div
                v-for="cards in info"
                :key="cards.id"
                :class="['list-cicle', formatStatus(cards)]"
                @click="slideToNumber(cards.seq)"
              >
                {{ cards.seq }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </van-popup>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';
import Api from '@/api';
import itemBank from '../component/itemBank';
import { Toast } from 'vant';
import testMixin from '@/mixins/lessonTask/index.js';
import report from '@/mixins/course/report';
import OutFocusMask from '@/components/out-focus-mask.vue';
import i18n from '@/lang';

export default {
  name: 'ExerciseAnalysis',
  filters: {
    type: function(type) {
      switch (type) {
        case 'single_choice':
          return i18n.t('courseLearning.singleChoice');
        case 'choice':
          return i18n.t('courseLearning.choice');
        case 'essay':
          return i18n.t('courseLearning.essay');
        case 'uncertain_choice':
          return i18n.t('courseLearning.uncertainChoice');
        case 'determine':
          return i18n.t('courseLearning.determine');
        case 'fill':
          return 'courseLearning.fill';
        case 'material':
          return i18n.t('courseLearning.material');
      }
    },
  },
  components: {
    itemBank,
    OutFocusMask,
  },
  mixins: [testMixin, report],
  data() {
    return {
      result: null,
      items: {}, // 分组题目
      info: [],
      isWrongMode: false, // 是否是错题模式
      allList: [], // 所有题集
      wrongList: [], // 所有题集
      wrongType: [], // 错题包含的题型
      cardSeq: 0, // 点击题卡要滑动的指定位置的索引
      cardShow: false, // 答题卡显示标记1
      answer: {},
      slideIndex: 0, // 题库组件当前所在的划片位置
      canDo: false, // 是否能答题，解析模式下不能答题
    };
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
      user: state => state.user,
    }),
  },
  mounted() {
    this.initReport();
    this.setNavbarTitle(this.$route.query.title);
    this.getexerciseResult();
  },
  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE,
    }),
    // 初始化上报数据
    initReport() {
      this.initReportData(
        this.$route.query.courseId,
        this.$route.query.taskId,
        'exercise',
      );
    },
    getexerciseResult() {
      Api.exerciseResult({
        query: {
          exerciseId: this.$route.query.exerciseId,
          exerciseResultId: this.$route.query.exerciseResultId,
        },
      }).then(res => {
        this.result = res;
        this.setNavbarTitle(res.paperName);
        this.title = res.paperName;
        this.formatData(res);
      });
    },
    // 遍历数据类型去做对应处理
    formatData(res) {
      res.items.forEach(item => {
        if (item.type != 'material') {
          const detail = this.analysisSixType(item.type, item);

          this.setData(detail.item, detail.answer);
        }
        if (item.type == 'material') {
          item.subs.forEach(sub => {
            const detail = this.analysisSixType(sub.type, sub);

            this.setData(detail.item, detail.answer);
          });
        }
      });
    },
    setData(item, answer) {
      this.$set(this.answer, item.id, answer);
      this.info.push(item);
      this.allList.push(item);
      if (
        (item.testResult && item.testResult.status !== 'right') ||
        !item.testResult
      ) {
        const type = item.parentType ? item.parentType : item.type;
        if (!this.wrongType.includes(type)) {
          this.wrongType.push(type);
        }
        this.wrongList.push(item);
      }
    },

    // 答题卡状态判断
    formatStatus(item) {
      if (item.testResult) {
        const status = item.testResult.status;
        switch (status) {
          case 'right':
            return 'cicle-right';
          case 'none':
            return 'cicle-subjective';
          case 'wrong':
            return 'cicle-wrong';
          case 'partRight':
            return 'cicle-wrong';
          case 'noAnswer':
            return '';
        }
      }
    },
    // 答题卡定位
    slideToNumber(num) {
      const index = Number(num);
      if (!this.isWrongMode) {
        this.cardSeq = index;
      } else {
        // 解决了错题下答题卡定位不准的问题,错题情况下会少一些题，不能直接用index去找
        this.info.forEach((item, i) => {
          if (index === parseInt(item.seq)) {
            this.cardSeq = i + 1;
          }
        });
      }
      // 关闭弹出层
      this.cardShow = false;
    },
    // 点击错题按钮
    showWrongList() {
      if (this.wrongList.length === 0) {
        Toast('当前没有错题');
        return;
      }
      Toast({
        message: '切换成功',
        duration: 1000,
      });
      this.isWrongMode = !this.isWrongMode;

      if (this.isWrongMode) {
        this.info = this.wrongList;
        this.cardSeq = this.isWrongItem();
      } else {
        this.info = this.allList;
        this.cardSeq = parseInt(this.wrongList[this.slideIndex].seq);
      }
      // 修改后不会出现多次点第1题切换到第2题的问题
      this.slideIndex = this.cardSeq - 1;
    },
    // 当前题目是否是错误题目,是错题则找出当前题在错题list中的索引，保持当前错题位置不动
    isWrongItem() {
      const item = this.allList[this.slideIndex];
      let itemIndex = 1; // 如果不是错题，默认为从第一个开始
      if (item.testResult && item.testResult.status !== 'right') {
        this.wrongList.forEach((list, index) => {
          if (list.id == item.id) {
            itemIndex = index + 1;
          }
        });
      }
      return itemIndex;
    },
  },
};
</script>
<style></style>
