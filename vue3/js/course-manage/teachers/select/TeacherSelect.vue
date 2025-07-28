<template>
  <a-select
      class="w-full"
      show-search
      :filter-option="false"
      placeholder="请选择主讲老师"
      v-model:value="teachers.current"
      @popupScroll="onPopupScroll"
      @search="onSearch"
      @change="onChange"
  >
    <a-select-option v-for="teacher in teachers.list" :key="teacher.id" :disabled="teacher.disabled">
      {{ teacher.nickname }}
    </a-select-option>
  </a-select>
</template>

<script setup>
import _ from 'lodash';
import Api from 'vue3/api';
import {getData} from 'vue3/js/common';
import {reactive} from 'vue';

const defaultTeacher = JSON.parse(getData('teacher-select-app', 'teacher'));

const teachers = reactive({
  list: [],
  current: defaultTeacher.id,
  search: undefined,
});

const paging = {};

const resetPaging = () => {
  paging.enable = true;
  paging.pageSize = 10;
  paging.current = 0;
  paging.total = 0;
};

const onPopupScroll = _.debounce(e => {
  const { scrollHeight, offsetHeight, scrollTop } = e.target;
  const maxScrollTop = scrollHeight - offsetHeight - 20;
  if (maxScrollTop < scrollTop && paging.enable) {
    fetchTeacher();
  }
}, 300);

const onSearch = _.debounce(input => {
  teachers.list = [];
  teachers.search = input;
  resetPaging();
  fetchTeacher();
}, 300);

const teacherId = document.getElementById('teacherId');

const onChange = (value) => {
  teacherId.value = value;
};

const fetchTeacher = () => {
  const params = {
    limit: paging.pageSize,
    offset: paging.pageSize * paging.current,
    excludeIds: [],
  };
  if (teachers.search) {
    params.nickname = teachers.search;
  }
  if (teachers.list.length === 0 && (!teachers.search || defaultTeacher.nickname.includes(teachers.search))) {
    teachers.list.push(defaultTeacher);
    params.excludeIds.push(defaultTeacher.id);
  }
  Api.teacher.search(params).then(res => {
    teachers.list = _.concat(teachers.list, res.data);
    paging.current++;
    paging.total += res.data.length;
    if (paging.total >= res.paging.total) {
      paging.enable = false;
    }
  });
};

fetchTeacher();

</script>

<style lang="less" scoped>
</style>