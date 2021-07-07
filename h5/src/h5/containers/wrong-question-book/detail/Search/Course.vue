<template>
  <van-popup
    v-model="visible"
    round
    position="bottom"
    class="wrong-question-search"
  >
    <van-nav-bar
      title="筛选"
      @click-left="onClickReset"
      @click-right="onClickSearch"
    >
      <template #left>
        <span class="search-reset">重置</span>
      </template>
      <template #right>
        <span class="search-btn">查看错题</span>
      </template>
    </van-nav-bar>

    <van-divider style="margin-top: 4px;" />

    <div class="search-sort">
      <div class="search-sort__title">排序</div>
      <div class="search-sort__btns">
        <div
          :class="['sort-btn', { active: sortType === 'default' }]"
          @click="onClickSort('default')"
        >
          综合排序
        </div>
        <div
          :class="['sort-btn', { active: sortType === 'DESC' }]"
          @click="onClickSort('DESC')"
        >
          由高至低
        </div>
        <div
          :class="['sort-btn', { active: sortType === 'ASC' }]"
          @click="onClickSort('ASC')"
        >
          由低至高
        </div>
      </div>
    </div>

    <div class="search-checked">
      <div
        v-for="(condition, index) in conditions"
        :key="index"
        :class="['search-checked__item', { active: currentIndex == index }]"
        @click="currentIndex = index"
      >
        <div class="checked-title">{{ condition.title }}</div>
        <div class="checked-result">{{ condition.selectdText }}</div>
      </div>
    </div>

    <div class="search-select">
      <div class="search-select__toolbar">
        {{ currentCondition.title }}
        <div class="search-select__confirm" @click="onClickConfirm">确定</div>
      </div>

      <van-picker
        :swipe-duration="500"
        :columns="currentCondition.columns"
        :default-index="currentCondition.selectdIndex"
        @change="onChange"
      />
    </div>
  </van-popup>
</template>

<script>
import _ from 'lodash';
import Api from '@/api';

const sources = {
  testpaper: '考试任务',
  homework: '作业任务',
  exercise: '练习任务',
};

export default {
  name: 'CourseSearch',

  props: {
    show: {
      type: Boolean,
      required: true,
    },

    poolId: {
      type: String,
      required: true,
    },
  },

  data() {
    return {
      visible: this.show,
      sortType: 'default',
      currentIndex: 0,
      searchParams: {},
      conditions: [
        {
          title: '选择计划',
          columns: [],
          selectdText: '选择计划',
          selectdIndex: 0,
        },
        {
          title: '选择题目来源',
          columns: [],
          selectdText: '选择题目来源',
          selectdIndex: 0,
        },
        {
          title: '选择任务名称',
          columns: [],
          selectdText: '选择任务名称',
          selectdIndex: 0,
        },
      ],
    };
  },

  computed: {
    currentCondition() {
      return this.conditions[this.currentIndex];
    },
  },

  watch: {
    show(value) {
      if (value) this.visible = value;
    },

    visible(value) {
      if (!value) this.$emit('hidden-search');
    },
  },

  created() {
    this.fetchCondition();
  },

  methods: {
    fetchCondition() {
      Api.getWrongQuestionCondition({
        query: {
          poolId: this.poolId,
        },
        params: this.searchParams,
      }).then(res => {
        const { plans, source, tasks } = res;

        const newSource = [];

        _.forEach(plans, item => {
          item.text = item.title;
        });

        _.forEach(source, item => {
          newSource.push({
            type: item,
            text: sources[item],
          });
        });

        _.forEach(tasks, item => {
          item.text = item.title;
        });

        this.conditions[0].columns = plans;
        this.conditions[1].columns = newSource;
        this.conditions[2].columns = tasks;
      });
    },

    onClickReset() {
      this.sortType = 'default';
      this.currentIndex = 0;
      this.searchParams = {};
      this.conditions[0].selectdIndex = 0;
      this.conditions[0].selectdText = '选择计划';
      this.conditions[1].selectdIndex = 0;
      this.conditions[1].selectdText = '选择题目来源';
      this.conditions[2].selectdIndex = 0;
      this.conditions[2].selectdText = '选择任务名称';
      this.fetchCondition();
    },

    onClickSearch() {
      this.visible = false;
      this.$emit('on-search', this.searchParams);
    },

    onClickSort(value) {
      this.sortType = value;
      if (value === 'default') {
        delete this.searchParams.wrongTimesSort;
        return;
      }
      this.searchParams.wrongTimesSort = value;
    },

    onChange(picker, value, index) {
      this.currentCondition.selectdIndex = index;
    },

    onClickConfirm() {
      const { selectdIndex, columns } = this.currentCondition;
      const value = columns[selectdIndex];

      if (!_.size(value)) {
        return;
      }

      this.currentCondition.selectdText = value.text;

      if (this.currentIndex === 0) {
        this.searchParams.courseId = value.id;
        this.conditions[1].selectdIndex = 0;
        this.conditions[1].selectdText = '选择题目来源';
        this.conditions[2].selectdIndex = 0;
        this.conditions[2].selectdText = '选择任务名称';
        delete this.searchParams.courseMediaType;
        delete this.searchParams.courseTaskId;
      } else if (this.currentIndex === 1) {
        this.searchParams.courseMediaType = value.type;
        this.conditions[2].selectdIndex = 0;
        this.conditions[2].selectdText = '选择任务名称';
        delete this.searchParams.courseTaskId;
      } else if (this.currentIndex === 2) {
        this.searchParams.courseTaskId = value.id;
      }

      this.fetchCondition();
    },
  },
};
</script>
