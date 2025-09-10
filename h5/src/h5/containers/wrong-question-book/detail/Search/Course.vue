<template>
  <van-popup
    v-model="visible"
    round
    position="bottom"
    class="wrong-question-search"
  >
    <van-nav-bar
      :title="$t('wrongQuestion.filter')"
      @click-left="onClickReset"
      @click-right="onClickSearch"
    >
      <template #left>
        <span class="search-reset">{{ $t('wrongQuestion.reset') }}</span>
      </template>
      <template #right>
        <span class="search-btn">{{ $t('wrongQuestion.check') }}</span>
      </template>
    </van-nav-bar>

    <van-divider style="margin-top: 4px;" />

    <div class="search-sort">
      <div class="search-sort__title">{{ $t('wrongQuestion.sort') }}</div>
      <div class="search-sort__btns">
        <div
          :class="['sort-btn', { active: sortType === 'default' }]"
          @click="onClickSort('default')"
        >
          {{ $t('wrongQuestion.all') }}
        </div>
        <div
          :class="['sort-btn', { active: sortType === 'DESC' }]"
          @click="onClickSort('DESC')"
        >
          {{ $t('wrongQuestion.highToLow') }}
        </div>
        <div
          :class="['sort-btn', { active: sortType === 'ASC' }]"
          @click="onClickSort('ASC')"
        >
          {{ $t('wrongQuestion.LowToHigh') }}
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
        <div class="checked-title text-overflow">{{ $t(condition.title) }}</div>
        <div class="checked-result">{{ $t(condition.selectdText) }}</div>
      </div>
    </div>

    <div class="search-select">
      <div class="search-select__toolbar">
        {{  $t(currentCondition.placeholder) }}
        <div class="search-select__confirm" @click="onClickConfirm">{{ $t('btn.confirm') }}</div>
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
  testpaper: 'wrongQuestion.examTask',
  homework: 'wrongQuestion.homeworkTask',
  exercise: 'wrongQuestion.practiceTask',
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
          title: 'wrongQuestion.allPlan',
          placeholder: 'wrongQuestion.choosePlan',
          columns: [],
          selectdText: 'wrongQuestion.choosePlan',
          selectdIndex: 0,
        },
        {
          title: 'wrongQuestion.questionSource',
          placeholder: 'wrongQuestion.chooseSource',
          columns: [],
          selectdText: 'wrongQuestion.chooseSource',
          selectdIndex: 0,
        },
        {
          title: 'wrongQuestion.missionName',
          placeholder: 'wrongQuestion.chooseMission',
          columns: [],
          selectdText: 'wrongQuestion.chooseMission',
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

        _.forEach(plans, item => {
          item.text = item.title;
        });

        const newSource = [];
        _.forEach(source, item => {
          newSource.push({
            type: item,
            text: this.$t(sources[item]),
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
      this.conditions[1].selectdIndex = 0;
      this.conditions[2].selectdIndex = 0;
      this.conditions[0].selectdText = this.$t('wrongQuestion.choosePlan');
      this.conditions[1].selectdText = this.$t('wrongQuestion.chooseSource');
      this.conditions[2].selectdText = this.$t('wrongQuestion.chooseMission');
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
        this.conditions[1].selectdText = this.$t('wrongQuestion.chooseSource');
        this.conditions[2].selectdIndex = 0;
        this.conditions[2].selectdText = this.$t('wrongQuestion.chooseMission');
        delete this.searchParams.courseMediaType;
        delete this.searchParams.courseTaskId;
      } else if (this.currentIndex === 1) {
        this.searchParams.courseMediaType = value.type;
        this.conditions[2].selectdIndex = 0;
        this.conditions[2].selectdText = this.$t('wrongQuestion.chooseMission');
        delete this.searchParams.courseTaskId;
      } else if (this.currentIndex === 2) {
        this.searchParams.courseTaskId = value.id;
      }

      this.fetchCondition();
    },
  },
};
</script>
