<script setup>
import AntConfigProvider from '../../../components/AntConfigProvider.vue';
import {nextTick, ref} from 'vue';
import TagSelectModal from '../../widgets/TagSelectModal.vue';
import Selector from 'app/js/question-bank/common/selector';

const element = $('.js-select-container');
const table = $('.js-select-table');
const renderUrl = table.data('url');
const selector = new Selector(table);

const difficulty = ref('default')
const type = ref('default')
const keyword = ref()
const tagIds = ref([])

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

const tagSelectModalVisible = ref(false)
function openTagSelectModal() {
  tagSelectModalVisible.value = true
}

function onConfirm(ids) {
  tagIds.value = ids;
}

function onSearch() {
  element.find('.js-page').val(1);
  const conditions = `difficulty=${difficulty.value === 'default' ? '' : difficulty.value
                      }&type=${type.value === 'default' ? '' : type.value
                      }&keyword=${keyword.value || ''
                      }&tagIds=${tagIds.value
                      }&page=${element.find('.js-page').val()
                      }&exclude_ids=${$('.js-excludeIds').val()}`;
  $.ajax({
    type: 'GET',
    url: renderUrl,
    data: conditions
  }).done(
    function(resp) {
      nextTick(() => {
        table.html(resp);
        selector.updateTable();
        $('a[data-toggle=tooltip]').tooltip({container: 'body'});
      })
    }
  ).fail(
    function() {
      const loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">' + Translator.trans('加载出错了...') + '</div>';
      table.html(loading);
    }
  );
}
</script>

<template>
  <AntConfigProvider>
    <div class="flex justify-between items-center mb-24">
      <div class="flex gap-10">
        <a-select
          v-model:value="difficulty"
          class="w-100"
        >
          <a-select-option v-for="(item, index) in difficultyList" :value="item.value" :key="index">{{ item.label }}</a-select-option>
        </a-select>
        <a-select
          v-model:value="type"
          class="w-120"
        >
          <a-select-option v-for="(item, index) in typeList" :value="item.value" :key="index">{{ item.label }}</a-select-option>
        </a-select>
        <a-input v-model:value="keyword" class="w-200" placeholder="请输入关键字" />
        <a-button type="primary" ghost @click="openTagSelectModal">{{ `筛选标签${tagIds.length > 0 ? ` (${tagIds.length}) ` : ''}` }}</a-button>
      </div>
      <a-button type="primary" @click="onSearch">搜索</a-button>
    </div>
    <TagSelectModal
      v-model="tagSelectModalVisible"
      :params="{mode: 'filter', tagIds: tagIds}"
      @ok="onConfirm"
    />
  </AntConfigProvider>
</template>

