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
      :result-show="resultShow"
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
            <span v-if="!isReadOver" class="card-none">{{ $t('courseLearning.toBeReviewed') }}</span>
          </div>
          <i class="iconfont icon-no" @click="cardShow = false" />
        </div>
        <div class="card-list">
          <template v-for="(cards, name) in items">
            <div v-if="isWrongType(name)" :key="name" class="card-item">
              <div class="card-item-title">{{ name | type }}</div>
              <div v-if="name != 'material'" class="card-item-list">
                <template v-for="craditem in items[name]">
                  <div
                    v-if="isWrongList(craditem)"
                    :class="['list-cicle', formatStatus(craditem)]"
                    :key="craditem.id"
                    @click="slideToNumber(craditem.seq)"
                  >
                    {{ craditem.seq }}
                  </div>
                </template>
              </div>
              <div v-if="name == 'material'" class="card-item-list">
                <template v-for="craditem in items[name]">
                  <template v-for="materialitem in craditem.subs">
                    <div
                      v-if="isWrongList(materialitem)"
                      :class="['list-cicle', formatStatus(materialitem)]"
                      :key="materialitem.id"
                      @click="slideToNumber(materialitem.seq)"
                    >
                      {{ materialitem.seq }}
                    </div>
                  </template>
                </template>
              </div>
            </div>
          </template>
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
  name: 'TestpaperAnalysis',
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
      isReadOver: false,
      isWrongMode: false, // 是否是错题模式
      allList: [], // 所有题集
      wrongList: [], // 所有题集
      wrongType: [], // 错题包含的题型
      cardSeq: 0, // 点击题卡要滑动的指定位置的索引
      cardShow: false, // 答题卡显示标记
      answer: {},
      slideIndex: 0, // 题库组件当前所在的划片位置
      canDo: false, // 是否能答题，解析模式下不能答题
      resultShow: false,
    };
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
      user: state => state.user,
      selectedPlanId: state => state.course.selectedPlanId,
    }),
  },
  mounted() {
    this.initReport();
    this.setNavbarTitle(this.$route.query.title);
    this.getTestpaperResult();
  },
  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE,
    }),
    // 初始化上报数据
    initReport() {
      this.initReportData(
        this.selectedPlanId,
        this.$route.query.targetId,
        'testpaper',
      );
    },
    async getTestpaperResult() {
      await Api.testpaperResult({
        query: {
          resultId: this.$route.query.resultId,
        },
      }).then(res => {
        this.result = res.testpaperResult;
        this.formatData(res);
        this.isReadOver = this.result.status === 'finished';
        this.result = res.testpaperResult;
        this.items = res.items;
        this.resultShow = res.resultShow;
      });
    },
    // 遍历数据类型去做对应处理
    formatData(res) {
      const paper = res.items;
      Object.keys(paper).forEach(key => {
        if (key != 'material') {
          paper[key].forEach(item => {
            const detail = this.analysisSixType(item.type, item);

            this.setData(detail.item, detail.answer);
          });
        }
        if (key == 'material') {
          // 材料题下面有子题需要特殊处理
          paper[key].forEach(item => {
            const title = Object.assign({}, item, { subs: '' });
            item.subs.forEach((sub, index) => {
              sub.parentTitle = title; // 材料题题干
              sub.parentType = item.type; // 材料题题型
              sub.materialIndex = index + 1; // 材料题子题的索引值，在页面要显示

              const detail = this.analysisSixType(sub.type, sub);

              this.setData(detail.item, detail.answer);
            });
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
    // //处理六大题型数据
    // sixType(type, item) {
    //   if (type != "fill" && type != "essay") {
    //     //   由于后台返回的是string格式，前端需要用number格式才能回显。周一让后台统一改为number
    //     item.answer.forEach((num, index) => {
    //       item.answer[index] = Number(num);
    //     });
    //     let answer = item.answer;
    //     if (item.testResult) {
    //       item.testResult.answer.forEach((num, index) => {
    //         item.testResult.answer[index] = Number(num);
    //       });
    //       //为了回显，这里传给子组件的answer要是正确答案和学员选择答案的合集，因为都要选中。
    //       answer = Array.from(
    //         new Set([...item.answer, ...item.testResult.answer])
    //       );
    //     }
    //     this.$set(this.answer, item.id, answer);
    //     // this.info.push(item);
    //   }

    //   if (type == "essay") {
    //     let answer = item.testResult ? item.testResult.answer : [];
    //     this.$set(this.answer, item.id, answer);
    //     // this.info.push(item);
    //   }

    //   if (type == "fill") {
    //     let fillstem = item.stem;
    //     let { stem, index } = this.fillReplce(fillstem, 0);
    //     item.stem = stem;
    //     item.fillnum = index;
    //     let answer = item.testResult ? item.testResult.answer : [];
    //     this.$set(this.answer, item.id, answer);
    //     // this.info.push(item);
    //   }
    //   this.info.push(item);
    //   this.allList.push(item);
    //   if (item.testResult) {
    //     if (
    //       (item.testResult && item.testResult.status !== "right") ||
    //       !item.testResult
    //     ) {
    //       let type = item.parentType ? item.parentType : item.type;
    //       if (!this.wrongType.includes(type)) {
    //         this.wrongType.push(type);
    //       }
    //       this.wrongList.push(item);
    //     }
    //   }
    // },
    // //处理富文本，并统计填空题的空格个数
    // fillReplce(stem, index) {
    //   const reg = /\[\[.+?\]\]/;
    //   while (reg.exec(stem)) {
    //     stem = stem.replace(reg, () => {
    //       return `<span class="fill-bank">（${++index}）</span>`;
    //     });
    //   }
    //   return { stem, index };
    // },
    // 答题卡状态判断
    formatStatus(item) {
      if (item.testResult) {
        const status = item.testResult.status;
        switch (status) {
          case 'right':
            return 'cicle-right';
          case 'none':
            return 'cicle-none';
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
    isWrongType(name) {
      return this.isWrongMode ? this.wrongType.indexOf(name) !== -1 : true;
    },
    isWrongList(item) {
      return this.isWrongMode
        ? item.testResult && item.testResult.status !== 'right'
        : true;
    },
  },
};
</script>

<style></style>
