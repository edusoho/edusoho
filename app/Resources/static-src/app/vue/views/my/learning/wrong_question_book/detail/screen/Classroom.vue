<template>
  <a-form-model :model="form" layout="inline">
    <a-form-model-item>
      <a-select
        show-search
        option-filter-prop="children"
        :filter-option="filterOption"
        :notFoundContent="'my.learning.wrong_question_book.detail.screen.classroom.empty'|trans"
        style="width: 120px;"
        v-model="form.classroomCourseSetId"
        @change="(value) => handleChange(value, 'classroomCourseSetId')"
      >
        <a-select-option value="default">{{ 'my.learning.wrong_question_book.detail.screen.classroom.all_courses'|trans }}</a-select-option>

        <a-select-option
          v-for="courseSet in conditions.courseSets"
          :value="courseSet.id"
          :key="courseSet.id"
        >
          {{ courseSet.title }}
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-select
        show-search
        option-filter-prop="children"
        :filter-option="filterOption"
        :notFoundContent="'my.learning.wrong_question_book.detail.screen.classroom.empty'|trans"
        style="width: 120px;"
        v-model="form.classroomMediaType"
        @change="(value) => handleChange(value, 'classroomMediaType')"
      >
        <a-select-option value="default">{{ 'my.learning.wrong_question_book.detail.screen.classroom.source'|trans }}</a-select-option>

        <a-select-option
          v-for="item in conditions.mediaTypes"
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
        :notFoundContent="'my.learning.wrong_question_book.detail.screen.classroom.empty'|trans"
        style="width: 325px;"
        v-model="form.classroomTaskId"
      >
        <a-select-option value="default">{{ 'my.learning.wrong_question_book.detail.screen.classroom.task_name'|trans }}</a-select-option>

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
          {{ 'my.learning.wrong_question_book.detail.screen.classroom.select_option.mistakes_frequency'|trans }}
        </a-select-option>
        <a-select-option value="DESC">
          {{ 'my.learning.wrong_question_book.detail.screen.classroom.select_option.desc'|trans }}
        </a-select-option>
        <a-select-option value="ASC">
          {{ 'my.learning.wrong_question_book.detail.screen.classroom.select_option.asc'|trans }}
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-button type="primary" @click="handleSubmit">{{ 'my.learning.wrong_question_book.detail.screen.classroom.search'|trans }}</a-button>
    </a-form-model-item>
  </a-form-model>
</template>

<script>
import _ from 'lodash';
import { WrongBookCondition } from 'common/vue/service';


const sources = {
  testpaper: Translator.trans('my.learning.wrong_question_book.detail.screen.classroom.sources.exam_task'),
  homework: Translator.trans('my.learning.wrong_question_book.detail.screen.classroom.sources.exam_task'),
  exercise: Translator.trans('my.learning.wrong_question_book.detail.screen.classroom.sources.exam_task'),
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
        classroomCourseSetId: 'default',
        classroomMediaType: 'default',
        classroomTaskId: 'default',
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
      const { classroomCourseSetId, classroomMediaType } = this.form;

      const apiParams = {
        query: {
          poolId: this.id
        },
        params: {}
      };

      const params = apiParams.params;

      if (type === 'classroomCourseSetId') {
        _.assign(this.form, {
          classroomMediaType: 'default',
          classroomTaskId: 'default'
        });

        classroomCourseSetId !== 'default' && (params.classroomCourseSetId = classroomCourseSetId);
      }

      if (type === 'classroomMediaType') {
        _.assign(this.form, {
          classroomTaskId: 'default'
        });

        classroomMediaType !== 'default' && (params.classroomMediaType = classroomMediaType);
        classroomCourseSetId !== 'default' && (params.classroomCourseSetId = classroomCourseSetId);
      }

      return apiParams;
    },

    async fetchWrongBookCondition(type) {
      const params = this.getParams(type);

      const result = await WrongBookCondition.get(params);

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
