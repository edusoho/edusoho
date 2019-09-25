<template>
  <div class="paper-swiper">
    <e-loading v-if="isLoading"></e-loading>
    <item-bank
      v-if="info.length>0"
      :isWrongMode="isWrongMode"
      :current.sync="cardSeq"
      :info="info"
      :answer="answer"
      :slideIndex.sync="slideIndex"
      :canDo="canDo"
      :all="allList.length"
    />
    <!-- 底部 -->
    <div class="paper-footer">
      <div>
        <span @click="cardShow=true">
          <i class="iconfont icon-Questioncard"></i>
          题卡
        </span>
      </div>
      <div>
        <span @click="showWrongList" :class="{'footer__div__span--active': isWrongMode}">
          <i class="iconfont icon-submit"></i>
          错题
        </span>
      </div>
    </div>
    <!-- 答题卡 -->
    <van-popup v-model="cardShow" position="bottom">
      <div class="card" v-if="info.length>0">
        <div class="card-title">
          <div>
            <span class="card-right">正确</span>
            <span class="card-wrong">错误</span>
            <span class="card-nofinish">未作答</span>
            <span class="card-none">待批阅</span>
          </div>
          <i class="iconfont icon-no" @click="cardShow=false"></i>
        </div>
        <div class="card-list">
          <div class="card-homework-item card-item">
            <div class="card-item-list">
              <div
                v-for="cards in info" :key="cards.id"
                :class="['list-cicle',formatStatus(cards)]"
                @click="slideToNumber(cards.seq)"
              >{{cards.seq}}
              </div>
            </div>
          </div>
        </div>
      </div>
    </van-popup>
  </div>
</template>

<script>
  import { mapState, mapMutations, mapActions } from 'vuex';
  import * as types from '@/store/mutation-types';
  import Api from '@/api';
  import itemBank from '../component/itemBank';
  import { Toast } from 'vant';

  export default {
    name: 'homeworkAnalysis',
    data() {
      return {
        result: null,
        items: {}, //分组题目
        info: [],
        isWrongMode: false, //是否是错题模式
        allList: [],//所有题集
        wrongList: [], //所有题集
        wrongType: [],//错题包含的题型
        cardSeq: 0, //点击题卡要滑动的指定位置的索引
        cardShow: false, //答题卡显示标记1
        answer: {},
        slideIndex: 0, //题库组件当前所在的划片位置
        canDo: false, //是否能答题，解析模式下不能答题
      };
    },
    filters: {
      type: function (type) {
        switch (type) {
          case 'single_choice':
            return '单选题';
            break;
          case 'choice':
            return '多选题';
            break;
          case 'essay':
            return '问答题';
            break;
          case 'uncertain_choice':
            return '不定项选择题';
            break;
          case 'determine':
            return '判断题';
            break;
          case 'fill':
            return '填空题';
            break;
          case 'material':
            return '材料题';
            break;
        }
      }
    },
    components: {
      itemBank
    },
    computed: {
      ...mapState({
        isLoading: state => state.isLoading,
        user: state => state.user
      })
    },
    created() {
      this.setNavbarTitle(this.$route.query.title);
      this.gethomeworkResult();
    },
    methods: {
      ...mapMutations({
        setNavbarTitle: types.SET_NAVBAR_TITLE
      }),
      gethomeworkResult() {
        Api.homeworkResult({
          query: {
            homeworkId: this.$route.query.homeworkId,
            homeworkResultId: this.$route.query.homeworkResultId
          },
        })
          .then(res => {
            this.result = res;
            this.setNavbarTitle(res.paperName);
            this.title = res.paperName;
            this.formatData(res);
          });
      },
      //遍历数据类型去做对应处理
      formatData(res) {
        let items = [];
        res.items.forEach(element => {
          if (element.type != 'material') {
            element.status = this.getStatus(element);
            this.sixType(element.type, element);
          }
          if (element.type == 'material') {
            element.subs.forEach((sub) => {
              sub.status = this.getStatus(sub);
              this.sixType(sub.type, sub);
            });
          }
        });
        this.items = items;
      },
      getStatus(element) {
        if (element.testResult && element.testResult.status) {
          return element.testResult.status;
        } else {
          return 'noAnswer';
        }
      },
      //处理六大题型数据
      sixType(type, item) {
        if (type != 'fill' && type != 'essay') {
          //   由于后台返回的是string格式，前端需要用number格式才能回显。周一让后台统一改为number
          item.answer.forEach((num, index) => {
            item.answer[index] = Number(num);
          });
          let answer = item.answer;
          if (item.testResult) {
            item.testResult.answer.forEach((num, index) => {
              item.testResult.answer[index] = Number(num);
            });
            //为了回显，这里传给子组件的answer要是正确答案和学员选择答案的合集，因为都要选中。
            answer = Array.from(
              new Set([...item.answer, ...item.testResult.answer])
            );
          }
          this.$set(this.answer, item.id, answer);
          // this.info.push(item);
        }

        if (type == 'essay') {
          let answer = item.testResult ? item.testResult.answer : [];
          this.$set(this.answer, item.id, answer);
          // this.info.push(item);
        }

        if (type == 'fill') {
          let fillstem = item.stem;
          let { stem, index } = this.fillReplce(fillstem, 0);
          item.stem = stem;
          item.fillnum = index;
          let answer = item.testResult ? item.testResult.answer : [];
          this.$set(this.answer, item.id, answer);
          // this.info.push(item);
        }
        this.info.push(item);
        this.allList.push(item);
        if (item.testResult) {
          if (item.testResult.status !== 'right') {
            let type = item.parentType ? item.parentType : item.type;
            if (!this.wrongType.includes(type)) {
              this.wrongType.push(type);
            }
            this.wrongList.push(item);
          }
        }
      },
      //处理富文本，并统计填空题的空格个数
      fillReplce(stem, index) {
        const reg = /\[\[.+?\]\]/;
        while (reg.exec(stem)) {
          stem = stem.replace(reg, () => {
            return `<span class="fill-bank">（${++index}）</span>`;
          });
        }
        return { stem, index };
      },
      //答题卡状态判断
      formatStatus(item) {
        if (item.testResult) {
          let status = item.testResult.status;
          switch (status) {
            case 'right':
              return 'cicle-right';
              break;
            case 'none':
              return 'cicle-none';
              break;
            case 'wrong':
              return 'cicle-wrong';
              break;
            case 'partRight':
              return 'cicle-wrong';
              break;
            case 'noAnswer':
              return '';
              break;
          }
        }
      },
      //答题卡定位
      slideToNumber(num) {
        let index = Number(num);
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
        //关闭弹出层
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
          duration: 1000
        });
        this.isWrongMode = !this.isWrongMode;

        if (this.isWrongMode) {
          this.info = this.wrongList;
          this.cardSeq = this.isWrongItem();
        } else {
          this.info = this.allList;
          this.cardSeq = 1;
        }
        // 修改后不会出现多次点第1题切换到第2题的问题
        this.slideIndex = this.cardSeq - 1;
      },
      // 当前题目是否是错误题目,是错题则找出当前题在错题list中的索引，保持当前错题位置不动
      isWrongItem() {
        let item = this.allList[this.slideIndex];
        let itemIndex = 1; //如果不是错题，默认为从第一个开始
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

<style>
</style>
