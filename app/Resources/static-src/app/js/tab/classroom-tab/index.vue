<template>
  <div>
    <div class="panel-heading" style="padding: 10px 0; line-height: 30px;">
      <label class="text-18">我的班级</label>
      <div class="pull-right">
        <form class="search-form" @submit.prevent="getTabData(tabValue)" style="margin-right: 54px;">
          <input class="search-input-content inline-block" v-model:value="searchValue" type="text" name="title" placeholder="请输入班级名称" />
          <a class="btn inline-block searchCourseBtn es-icon es-icon-search" type="submit" @click="getTabData(tabValue)" style="padding-top: 6px !important;"></a>
        </form>
      </div>
    </div>

    <div class="panel-body" style="padding: 0 0 16px 0;">
    <a-tabs 
    v-model:activeKey="tabValue"
    :tabBarGutter="0"
    size="small" 
    @change="tabOnChange">
      <a-tab-pane key="learning" tab="学习中">
        <ClassroomList :classroomLists="classroomLists"></ClassroomList>
      </a-tab-pane>
      <a-tab-pane key="learned" tab="已学完" force-render>
        <ClassroomList :classroomLists="classroomLists"></ClassroomList>
      </a-tab-pane>
      <a-tab-pane key="expired" tab="已过期">
        <ClassroomList :classroomLists="classroomLists" :tabValue="tabValue"></ClassroomList>
      </a-tab-pane>
    </a-tabs>
    <a-pagination v-if="total>pageSize" :defaultPageSize="pageSize" v-model="current" @change="onChange" :total="total" />
  </div>
  </div>
</template>
<script>
import ClassroomList from './ClassroomList.vue';
import { Me } from 'common/vue/service/index.js';

export default {
  data(){
    return {
      tabValue: 'learning',
      searchValue: '',
      current: 1,
      classroomLists: [],
      total: 0,
      pageSize: 12
    }
  },
  components: {
    ClassroomList
        },
  async mounted(){
    const params = this.getParams(window.location.href)
    
    if (params.search) {
      this.searchValue = decodeURIComponent(params.search)
    }

    if (params.type && params.page) {
      this.tabValue = params.type
      this.current = parseInt(params.page)
      await this.getTabData(params.type, params.page);
    } else {
      await this.getTabData(this.tabValue);
    }
  },
  methods: {
    async tabOnChange(key) {
      this.current = 1
      await this.getTabData(key);
    },
    onChange(pageNumber) {
      window.location.href = window.location.pathname + `?type=${this.tabValue}&page=${pageNumber}${this.searchValue ? `&search=${this.searchValue}` : ''}`
    },
    async getTabData(type, pageNumber=1) {
      let params = {
        title: this.searchValue,
        limit: this.pageSize,
        offset: (pageNumber-1)*this.pageSize,
        type,
        format: 'pagelist'
      }

      const { data, paging } = await Me.searchClassrooms(params)
      this.classroomLists = data
      this.total = paging.total
    },
    getParams(str) {
      let search = str.includes('?') ? str.split('?')[1] : str;
      let params = {};

      search.split('&').forEach(vars => {
        let value = vars.split('=') || [vars];
        params[value[0]] = value[1]
      });

      return params;
    }
  },
}
</script>