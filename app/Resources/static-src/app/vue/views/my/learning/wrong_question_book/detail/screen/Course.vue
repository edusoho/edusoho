<template>
  <a-form-model :model="form" layout="inline">
    <a-form-model-item>
      <a-select
        show-search
        option-filter-prop="children"
        :filter-option="filterOption"
        :notFoundContent="'my.learning.wrong_question_book.detail.screen.course.empty'|trans"
        style="width: 120px;"
        v-model="form.courseId"
        @change="(value) => handleChange(value, 'plan')"
      >
        <a-select-option value="default">{{ 'my.learning.wrong_question_book.detail.screen.course.entire_plan'|trans }}</a-select-option>

        <a-select-option
          v-for="plan in conditions.plans"
          :value="plan.id"
          :key="plan.id"
        >
          {{ plan.title }}
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-select
        show-search
        option-filter-prop="children"
        :filter-option="filterOption"
        :notFoundContent="'my.learning.wrong_question_book.detail.screen.course.empty'|trans"
        style="width: 120px;"
        v-model="form.courseMediaType"
        @change="(value) => handleChange(value, 'source')"
      >
        <a-select-option value="default">{{ 'my.learning.wrong_question_book.detail.screen.course.source'|trans }}</a-select-option>

        <a-select-option
          v-for="item in conditions.source"
          :value="item"
          :key="item"
        >
          {{ item | sourceTitle }}
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-select
        show-search
        option-filter-prop="children"
        :filter-option="filterOption"
        :notFoundContent="'my.learning.wrong_question_book.detail.screen.course.empty'|trans"
        style="width: 325px;"
        v-model="form.courseTaskId"
      >
        <a-select-option value="default">{{ 'my.learning.wrong_question_book.detail.screen.course.task_name'|trans }}</a-select-option>

        <a-select-option
          v-for="task in conditions.tasks"
          :value="task.id"
          :key="task.id"
        >
          {{ task.title }}
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-select
        style="width: 120px;"
        v-model="form.wrongTimesSort"
      >
        <a-select-option value="default">
          {{ 'my.learning.wrong_question_book.detail.screen.course.select_option.mistakes_frequency'|trans }}
        </a-select-option>
        <a-select-option value="DESC">
          {{ 'my.learning.wrong_question_book.detail.screen.course.select_option.desc'|trans }}
        </a-select-option>
        <a-select-option value="ASC">
          {{ 'my.learning.wrong_question_book.detail.screen.course.select_option.asc'|trans }}
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-button type="primary" @click="handleSubmit">{{ 'my.learning.wrong_question_book.detail.screen.course.search'|trans }}</a-button>
    </a-form-model-item>
  </a-form-model>
</template>

<script>
import _ from 'lodash';
import { WrongBookCondition } from 'common/vue/service';

const sources = {
  testpaper: Translator.trans('my.learning.wrong_question_book.detail.screen.course.sources.exam_task'),
  homework: Translator.trans('my.learning.wrong_question_book.detail.screen.course.sources.job_task'),
  exercise: Translator.trans('my.learning.wrong_question_book.detail.screen.course.sources.practice_task'),
}

export default {
  filters: {
    sourceTitle(value) {
      return sources[value];
    }
  },

  props: {
    id: {
      type: String,
      required: true
    }
  },

  data() {
    return {
      form: {
        courseId: 'default',
        courseMediaType: 'default',
        courseTaskId: 'default',
        wrongTimesSort: 'default'
      },
      conditions: {}
    }
  },

  created() {
    this.initSearchParams();
    this.fetchWrongBookCondition();
  },

  methods: {
    initSearchParams() {
      const params = this.$route.query;

      _.forEach(params, (value, key) => {
        this.form[key] = value;
      });
    },

    getParams(type) {
      const { courseId, courseMediaType } = this.form;
      const apiParams = {
        query: {
          poolId: this.id
        },
        params: {}
      };

      const params = apiParams.params;

      if (type === 'plan') {
        _.assign(this.form, {
          courseMediaType: 'default',
          courseTaskId: 'default'
        });

        courseId !== 'default' && (params.courseId = courseId);
      }

      if (type === 'source') {
        _.assign(this.form, {
          courseTaskId: 'default'
        });

        courseMediaType !== 'default' && (params.courseMediaType = courseMediaType);
        courseId !== 'default' && (params.courseId = courseId);
      }

      return apiParams;
    },

    async fetchWrongBookCondition(type) {
      const params = this.getParams(type);

      const result = await WrongBookCondition.get(params);

      _.forEach(result.plans, (plan, index) => {
        if (!plan.title) {
          result.plans.splice(index, 1);
        }
      });

      this.conditions = result;
      this.$emit('set-title', result.title);
    },

    handleChange(value, type) {
      this.fetchWrongBookCondition(type);
    },

    filterOption(input, option) {
      return (
        option.componentOptions.children[0].text.toLowerCase().indexOf(input.toLowerCase()) >= 0
      );
    },

    handleSubmit() {
      const params = {};
      _.forEach(_.keys(this.form), item => {
        const value = this.form[item];
        if (value != 'default') {
          params[item] = value;
        }
      });
      this.$emit('on-search', params);
    }
  }
}
</script>
