<script setup>
import { message } from 'ant-design-vue';
import Api from '../../../api';
import {goto} from '../../common';
import {ref} from 'vue';
import Selector from 'app/js/question-bank/common/selector';
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import TagSelect from '../components/tagSelect.vue';

const categoryId = ref()
const difficulty = ref('default')
const type = ref('default')
const keyword = ref('')
const tagIds = ref([])
const exportUrl = ref($('.js-export-value').val())

const selector = new Selector($('.js-question-html'))
const modal = $('#modal')
const importModalUrl = $('.js-list-header-import').val()

const addQuestion = [
  {label: '单选题', value: `/question_bank/${$('.js-questionBank-id').val()}/question/single_choice/create?goto=/question_bank/${$('.js-questionBank-id').val()}/questions&categoryId=`},
  {label: '多选题', value: `/question_bank/${$('.js-questionBank-id').val()}/question/choice/create?goto=/question_bank/${$('.js-questionBank-id').val()}/questions&categoryId=`},
  {label: '不定项选择题', value: `/question_bank/${$('.js-questionBank-id').val()}/question/uncertain_choice/create?goto=/question_bank/${$('.js-questionBank-id').val()}/questions&categoryId=`},
  {label: '问答题', value: `/question_bank/${$('.js-questionBank-id').val()}/question/essay/create?goto=/question_bank/${$('.js-questionBank-id').val()}/questions&categoryId=`},
  {label: '判断题', value: `/question_bank/${$('.js-questionBank-id').val()}/question/determine/create?goto=/question_bank/${$('.js-questionBank-id').val()}/questions&categoryId=`},
  {label: '填空题', value: `/question_bank/${$('.js-questionBank-id').val()}/question/fill/create?goto=/question_bank/${$('.js-questionBank-id').val()}/questions&categoryId=`},
  {label: '材料题', value: `/question_bank/${$('.js-questionBank-id').val()}/question/material/create?goto=/question_bank/${$('.js-questionBank-id').val()}/questions&categoryId=`},
]
const difficultyList = [
  {label: '全部难度', value: 'default'},
  {label: '简单', value: 'simple'},
  {label: '一般', value: 'normal'},
  {label: '困难', value: 'difficulty'},
]
const typeList = [
  {label: '全部题型', value: 'default'},
  {label: '单选题', value: 'single_choice'},
  {label: '多选题', value: 'choice'},
  {label: '不定项选择题', value: 'uncertain_choice'},
  {label: '问答题', value: 'essay'},
  {label: '判断题', value: 'determine'},
  {label: '填空题', value: 'fill'},
  {label: '材料题', value: 'material'},
]

async function duplicateChecking() {
  if($("[name=question_count]").val() == 0) {
    message.warning('该分类下暂无题目');
  }
  const hide = message.loading('正在查重...', 0);
  try {
    const res = await Api.repeat.getRepeatQuestion($("[name=questionBankId]").val(), $("[name=category_id]").val())
    hide();
    if (res.length > 0) {
      goto(`/question_bank/${$('.js-questionBank-id').val()}/check_duplicative_questions?categoryId=${categoryId.value ? categoryId.value : ''}`)
    } else {
      message.warning('无重复题目');
    }
  } catch (err) {
    hide()
    message.error(err.message)
  }
}

function exportQuestion() {
  const currentDifficulty = difficulty.value === 'default' ? '' : difficulty.value
  const currentType = type.value === 'default' ? '' : type.value
  const currentCategoryId = $('.js-category-choose').val()

  const a = document.createElement('a')
  a.href =  exportUrl.value + '?category_id=' + currentCategoryId + '&ids=' + selector.toJson() + '&difficulty=' + currentDifficulty + '&type=' + currentType + '&keyword=' + keyword.value
  a.click()
}

function importQuestion() {
  modal.load(importModalUrl)
  modal.modal('show');
}

const tagSelectModalVisible = ref(false)
function openTagSelectModal() {
  tagSelectModalVisible.value = true
}

function onConfirm(ids) {
  tagIds.value = ids;
}
</script>

<template>
  <AntConfigProvider>
    <div class="flex flex-col gap-18">
      <div class="flex items-center justify-between">
        <div class="text-[16px] text-[#37393D] font-medium">题库名称</div>
        <div class="flex gap-10">
          <a-button @click="duplicateChecking">试卷查重</a-button>
          <a-button @click="exportQuestion">导出题目</a-button>
          <a-dropdown>
            <a-button>添加题目</a-button>
            <template #overlay>
              <a-menu>
                <a-menu-item v-for="(item, index) in addQuestion" :key="index">
                  <a :href="item.value + categoryId">{{ item.label }}</a>
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
          <a-button @click="importQuestion">导入题目</a-button>
        </div>
      </div>
      <div class="flex justify-between">
        <div class="flex gap-10">
          <a-select
            v-model:value="difficulty"
            class="w-140"
          >
            <a-select-option v-for="(item, index) in difficultyList" :value="item.value" :key="index">{{ item.label }}</a-select-option>
          </a-select>
          <a-select
            v-model:value="type"
            class="w-140"
          >
            <a-select-option v-for="(item, index) in typeList" :value="item.value" :key="index">{{ item.label }}</a-select-option>
          </a-select>
          <a-input v-model:value="keyword" class="w-240" placeholder="请输入关键字" />
          <a-button type="primary" ghost @click="openTagSelectModal">{{ `筛选标签${tagIds.length > 0 ? ` (${tagIds.length}) ` : ''}` }}</a-button>
        </div>
        <div class="flex gap-10">
          <a-button>重置</a-button>
          <a-button type="primary">搜索</a-button>
        </div>
      </div>
    </div>
    <TagSelect
      v-model="tagSelectModalVisible"
      :mode="'search'"
      @ok="onConfirm"
    />
  </AntConfigProvider>
</template>
