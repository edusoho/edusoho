<template>
  <div id="list-header">
    <div class="header-btn-list">
      <div class="question-name">{{ 'question.bank.name'|trans }}</div>
      <div class="">
        <a-button class="duplicate-checking" type="default" @click="duplicateChecking">{{ 'question.bank.check'|trans }}</a-button>
        <a-button class="report" type="default" @click="exportQuestion">{{ 'question.bank.expoet'|trans }}</a-button>
        <a-dropdown>
          <a-button class="add-question" type="default">{{ 'question.bank.add'|trans }}</a-button>
          <a-menu slot="overlay">
            <a-menu-item v-for="(item, index) in addQuestion" :key="index">
              <a rel="noopener noreferrer" :href="item.value + categoryId"
                >{{ item.lable }}</a
              >
            </a-menu-item>
          </a-menu>
        </a-dropdown>
        <a-button class="import js-import-btn" type="primary" @click="importQuestion">{{ 'question.bank.import'|trans }}</a-button>
      </div>
    </div>
    <div>
      <a-form-model layout="inline">
        <a-form-model-item>
          <a-select v-model="difficulty" style="width: 216px;"
            v-decorator="[
              'difficulty',
              {initialValue: 'default'}
            ]"
          >
            <a-select-option v-for="(item, index) in difficultyList" :key="index" :value="item.value">
              {{ item.lable }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
        <a-form-model-item>
          <a-select v-model="type" style="width: 216px;"
            v-decorator="[
              'type',
              {initialValue: 'default'}
            ]"
          >
            <a-select-option v-for="(item, index) in  typeList" :key="index" :value="item.value">
              {{ item.lable }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
        <a-form-model-item>
          <a-input v-model="keyword" class="form-control" :placeholder="'placeholder.enter_keyword'| trans" style="width: 312px;" >
          </a-input>
        </a-form-model-item>
        <a-form-model-item>
          <a-button type="default" @click="handleReset">
            {{ 'question.bank.reset.btn'|trans }}
          </a-button>
        </a-form-model-item>
        <a-form-model-item>
          <a-button type="primary" @click="search">
            {{ 'site.search_hint'|trans }}
          </a-button>
        </a-form-model-item>
      </a-form-model>
    </div>
    <a-spin class="spin-fixed" :spinning="isLoading" :indicator="indicator" :tip="'question.bank.check_tip'|trans" />
    <input type="hidden" class="js-list-header-difficulty" :value=difficulty>
    <input type="hidden" class="js-list-header-type" :value=type>
    <input type="hidden" class="js-list-header-keyword" :value=keyword>
  </div>
</template>

<script>
import Selector from '../common/selector';
import { Repeat } from 'common/vue/service';

export default {
  data () {
    return {
      isLoading: false,
      indicator: '<a-icon type="loading" style="font-size: 24px" spin />',
      exportUrl: $('.js-export-value').val(),
      selector: new Selector($('.js-question-html')),
      difficulty: 'default',
      categoryId: '',
      difficultyList: [
        {lable: Translator.trans('question.bank.difficulty.default'), value: 'default'},
        {lable: Translator.trans('question.bank.difficulty.simple'), value: 'simple'},
        {lable: Translator.trans('question.bank.difficulty.normal'), value: 'normal'},
        {lable: Translator.trans('question.bank.difficulty'), value: 'difficulty'},
      ],
      type: 'default',
      typeList: [
        {lable: Translator.trans('course.question.by.question.type'), value: 'default'},
        {lable: Translator.trans('course.question.type.single_choice'), value: 'single_choice'},
        {lable: Translator.trans('course.question.type.choice'), value: 'choice'},
        {lable: Translator.trans('course.question.type.uncertain_choices'), value: 'uncertain_choice'},
        {lable: Translator.trans('course.question.type.essay'), value: 'essay'},
        {lable: Translator.trans('course.question.type.determine'), value: 'determine'},
        {lable: Translator.trans('course.question.type.fill'), value: 'fill'},
        {lable: Translator.trans('course.question.type.material'), value: 'material'},
      ],
      addQuestion: [
        {lable: Translator.trans('course.question.type.single_choice'), value: `/question_bank/${$('.js-questionBank-id').val()}/question/single_choice/create?goto=/question_bank/${$('.js-questionBank-id').val()}/questions&categoryId=`},
        {lable: Translator.trans('course.question.type.choice'), value: `/question_bank/${$('.js-questionBank-id').val()}/question/choice/create?goto=/question_bank/${$('.js-questionBank-id').val()}/questions&categoryId=`},
        {lable: Translator.trans('course.question.type.essay'), value: `/question_bank/${$('.js-questionBank-id').val()}/question/essay/create?goto=/question_bank/${$('.js-questionBank-id').val()}/questions&categoryId=`},
        {lable: Translator.trans('course.question.type.uncertain_choices'), value: `/question_bank/${$('.js-questionBank-id').val()}/question/uncertain_choice/create?goto=/question_bank/${$('.js-questionBank-id').val()}/questions&categoryId=`},
        {lable: Translator.trans('course.question.type.determine'), value: `/question_bank/${$('.js-questionBank-id').val()}/question/determine/create?goto=/question_bank/${$('.js-questionBank-id').val()}/questions&categoryId=`},
        {lable: Translator.trans('course.question.type.fill'), value: `/question_bank/${$('.js-questionBank-id').val()}/question/fill/create?goto=/question_bank/${$('.js-questionBank-id').val()}/questions&categoryId=`},
        {lable: Translator.trans('course.question.type.material'), value: `/question_bank/${$('.js-questionBank-id').val()}/question/material/create?goto=/question_bank/${$('.js-questionBank-id').val()}/questions&categoryId=`},
      ],
      keyword: '',
      defaultPages: 1,
      renderUrl: $('.js-question-html').data('url'),
      table: $('.js-question-html'),
      element: $('.js-question-container'),
      categoryContainer: $('.js-category-content'),
      importModalUrl: $('.js-list-header-import').val(),
      modal: $('#modal')
    }
  },
  mounted(){
    this.element.on('click', '.pagination li', (event) => {
      this.onClickPagination(event);
    });
    this.element.on('change', '.js-current-perpage-count', (event) => {
      this.onChangePagination(event);
    });
    this.element.on('click', '.js-category-search', (event) => {
      this.onClickCategorySearch(event);
    });

    this.element.on('click', '.js-all-category-search', (event) => {
      this.onClickAllCategorySearch(event);
    });
  },

  created () {
  },

  methods: {
    async duplicateChecking() {
      if($("[name=question_count]").val() == 0) {
        return this.$message.warning(Translator.trans('question.bank.check.result.category.noData'));
      }
      this.isLoading = true
      await Repeat.getRepeatQuestion($("[name=questionBankId]").val(), { categoryId: $("[name=category_id]").val() }).then(res => {
        this.isLoading = false

        if(res.length > 0) {
          window.location.href = `/question_bank/${$('.js-questionBank-id').val()}/check_duplicative_questions?categoryId=${this.categoryId}`
        } else {
          this.$message.warning(Translator.trans('question.bank.check.result.noData'));
        }
      }).catch(err => {
        this.isLoading = false
        this.$message.warning(err.message);
      });
    },
    userNameError() {
      const { getFieldError, isFieldTouched } = this.form;
      return isFieldTouched('userName') && getFieldError('userName');
    },

    passwordError() {
      const { getFieldError, isFieldTouched } = this.form;
      return isFieldTouched('password') && getFieldError('password');
    },

    exportQuestion() {
      const difficulty = this.difficulty === 'default' ? '' : this.difficulty
      const type = this.type === 'default' ? '' : this.type
      const categoryId = $('.js-category-choose').val()

      const a = document.createElement('a')
      a.href =  this.exportUrl + '?category_id=' + categoryId + '&ids=' + this.selector.toJson() + '&difficulty=' + difficulty + '&type=' + type + '&keyword=' + this.keyword     
      a.click()
    },
    
    importQuestion() {
      this.modal.load(this.importModalUrl)
      this.modal.modal('show')
    },

    search(isPaginator, defaultPages) {
      isPaginator || this._resetPage();
      const that = this
      const difficulty = this.difficulty === 'default' ? '' : this.difficulty
      const type = this.type === 'default' ? '' : this.type
      const category_id = $('.js-category-choose').val()
      that.categoryId = category_id
      const perpage = defaultPages ? defaultPages : $('.js-current-perpage-count').children('option:selected').val()
      const page = this.element.find('.js-page').val()
      const params = {
        category_id,
        difficulty,
        type,
        keyword: this.keyword,
        perpage,
        page
      }

      $.ajax({
        type: 'GET',
        url: this.renderUrl,
        data: params
      }).done(function(resp){
        that.$nextTick(() => {
          that.table.html(resp);
          that.selector.updateTable();
        })
      }).fail(function(){
      });

    },

    _resetPage() {
      this.element.find('.js-page').val(1);
    },

    onClickPagination(event) {
      const $target = $(event.currentTarget);
      this.element.find('.js-page').val($target.data('page'));
      this.search(true);
      event.preventDefault();
    },

    onChangePagination(){
      this.search()
    },

    handleReset() {
      this.difficulty = 'default'
      this.type = 'default'
      this.keyword = ''
    },

    onClickCategorySearch(event) {
      const $target = $(event.currentTarget);
      this.categoryContainer.find('.js-active-set.active').removeClass('active');
      $target.addClass('active');
      $('.js-category-choose').val($target.data('id'));
      const defaultPages = 10
      this.search( '',defaultPages);
    },

    onClickAllCategorySearch(event) {
      const $target = $(event.currentTarget);
      this.categoryContainer.find('.js-active-set.active').removeClass('active');
      $target.addClass('active');
      $('.js-category-choose').val('');
      const defaultPages = 10
      this.search( '',defaultPages);
    }
  }
}
</script>
