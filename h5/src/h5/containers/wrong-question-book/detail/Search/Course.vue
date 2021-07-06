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

      <van-picker :columns="currentCondition.columns" @change="onChange" />
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
      selectdIndex: 0,
      conditions: [
        {
          title: '选择计划',
          columns: [],
          courseId: 'default',
          selectdText: '选择计划',
        },
        {
          title: '选择题目来源',
          columns: [],
          courseMediaType: 'default',
          selectdText: '选择题目来源',
        },
        {
          title: '选择任务名称',
          columns: [],
          courseTaskId: 'default',
          selectdText: '选择任务名称',
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
      this.currentType = 'plans';
    },

    onClickSearch() {
      this.visible = false;
      console.log('onClickSearch');
    },

    onClickSort(value) {
      this.sortType = value;
    },

    onChange(picker, value, index) {
      this.selectdIndex = index;
    },

    onClickConfirm() {
      const value = this.currentCondition.columns[this.selectdIndex];

      if (!_.size(value)) {
        return;
      }

      this.currentCondition.selectdText = value.text;

      if (this.currentIndex === 0) {
        this.currentCondition.courseId = value.id;
        return;
      }

      if (this.currentIndex === 1) {
        this.currentCondition.courseMediaType = value.type;
        return;
      }

      if (this.currentIndex === 2) {
        this.currentCondition.courseTaskId = value.id;
      }
    },
  },
};
</script>
