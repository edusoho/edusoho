<template>
  <a-form-model :model="form" layout="inline">
    <a-form-model-item>
      <a-select
        show-search
        option-filter-prop="children"
        :filter-option="filterOption"
        :notFoundContent="'my.learning.wrong_question_book.detail.screen.question_bank.empty'|trans"
        style="width: 120px;"
        v-model="form.exerciseMediaType"
        @change="(value) => handleChange(value, 'exerciseMediaType')"
      >
        <a-select-option
          v-for="source in ['chapter', 'testpaper']"
          :value="source"
          :key="source"
        >
          {{ source | sourceTitle }}
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item v-if="form.exerciseMediaType === 'testpaper'">
      <a-select
        show-search
        option-filter-prop="children"
        :filter-option="filterOption"
        :notFoundContent="'my.learning.wrong_question_book.detail.screen.question_bank.empty'|trans"
        style="width: 120px;"
        v-model="form.testpaperId"
      >
        <a-select-option value="default">{{ 'my.learning.wrong_question_book.detail.screen.question_bank.all_papers'|trans }}</a-select-option>

        <a-select-option
          v-for="testpaper in conditions.testpapers"
          :value="testpaper.assessmentId"
          :key="testpaper.assessmentId"
        >
          {{ testpaper.assessmentName }}
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item v-if="form.exerciseMediaType === 'chapter'">
      <a-tree-select
        :notFoundContent="'my.learning.wrong_question_book.detail.screen.question_bank.empty'|trans"
        v-model="form.chapterId"
        style="min-width: 120px"
        :dropdown-style="{ maxHeight: '400px',  overflow: 'auto' }"
        :tree-data="conditions.chapter"
        :placeholder="'my.learning.wrong_question_book.detail.screen.question_bank.all_chapters'|trans"
        :replace-fields="{title:'name', key:'id', value: 'id', children: 'children' }"
        tree-default-expand-all
      >
      </a-tree-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-select
        style="width: 120px;"
        v-model="form.wrongTimesSort"
      >
        <a-select-option value="default">
          {{ 'my.learning.wrong_question_book.detail.screen.question_bank.select_option.mistakes_frequency'|trans }}
        </a-select-option>
        <a-select-option value="DESC">
          {{ 'my.learning.wrong_question_book.detail.screen.question_bank.select_option.desc'|trans }}
        </a-select-option>
        <a-select-option value="ASC">
          {{ 'my.learning.wrong_question_book.detail.screen.question_bank.select_option.asc'|trans }}
        </a-select-option>
      </a-select>
    </a-form-model-item>

    <a-form-model-item>
      <a-button type="primary" @click="handleSubmit">{{ 'my.learning.wrong_question_book.detail.screen.question_bank.search'|trans }}</a-button>
    </a-form-model-item>
  </a-form-model>
</template>

<script>
import _ from 'lodash';
import { WrongBookCondition } from 'common/vue/service';

const sources = {
  chapter: Translator.trans('my.learning.wrong_question_book.detail.screen.question_bank.sources.chapter_exercises'),
  testpaper: Translator.trans('my.learning.wrong_question_book.detail.screen.question_bank.sources.test_paper_practice'),
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
        chapterId: '',
        exerciseMediaType: 'chapter',
        testpaperId: 'default',
        wrongTimesSort: 'default'
      },
      conditions: {
      },
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
      const { exerciseMediaType, testpaperId, chapterId } = this.form;
      const apiParams = {
        query: {
          poolId: this.id
        },
        params: {}
      };

      const params = apiParams.params;


      if (type == undefined) {
        _.assign(params, this.form);
      }

      if (type === 'exerciseMediaType') {
        if (exerciseMediaType === 'chapter') {
          _.assign(this.form, {
            chapterId: 'default',
          });
        }else if (exerciseMediaType === 'testpaper') {
          _.assign(this.form, {
            testpaperId: 'default',
          });
        }

        exerciseMediaType !== 'default' && (params.exerciseMediaType = exerciseMediaType)
      }

      return apiParams;
    },

    async fetchWrongBookCondition(type) {
      const params = this.getParams(type);

      try {
        const result = await WrongBookCondition.get(params);
        result.chapter = [{"id": "default", "name": Translator.trans('my.learning.wrong_question_book.detail.screen.question_bank.all_chapters')}].concat(result.chapter);
        result.testpapers = result.testpaper;
        this.$emit('set-title', result.title);
        this.conditions = result;
        if (this.form.chapterId === '') {
          this.form.chapterId = 'default'
        }
      } catch (error) {
        let result = {}
        result.chapter = [{"id": "default", "name": Translator.trans('my.learning.wrong_question_book.detail.screen.question_bank.all_chapters')}]
        this.conditions = result;
      }
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
