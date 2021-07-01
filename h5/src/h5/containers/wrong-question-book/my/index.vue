<template>
  <van-tabs :border="true" color="#43c793">
    <template v-for="(listItem, index) in list">
      <van-tab :title="listItem.title" :key="index">
        <van-search
          v-model="listItem.keyword"
          shape="round"
          :placeholder="listItem.placeholder"
          @search="value => onSearch(index, value)"
        />
        <van-list
          class="wrong-list"
          v-model="listItem.loading"
          :finished="listItem.finished"
          finished-text="没有更多了"
          @load="onLoad(index)"
        >
          <item
            v-for="item in listItem.items"
            :key="item.id"
            :question="item"
          />
        </van-list>
        <div class="wrong-question-number">
          {{ listItem.totalTitle }}：{{ listItem.total }}
        </div>
      </van-tab>
    </template>
  </van-tabs>
</template>

<script>
import Api from '@/api';
import Item from './Item.vue';

export default {
  name: 'MyWrongQuestionBook',

  components: {
    Item,
  },

  data() {
    return {
      list: [
        {
          title: '课程错题',
          type: 'course',
          placeholder: '搜索相应课程',
          items: [],
          keyword: '',
          loading: false,
          finished: false,
          totalTitle: '课程错题数量',
          total: 0,
          paging: {
            current: 0,
            total: 0,
          },
        },
        {
          title: '班级错题',
          type: 'classroom',
          placeholder: '搜索相应班级',
          items: [],
          keyword: '',
          loading: false,
          finished: false,
          totalTitle: '班级错题数量',
          total: 0,
          paging: {
            current: 0,
            total: 0,
          },
        },
        {
          title: '题库错题',
          type: 'exercise',
          placeholder: '搜索相应练习名称',
          items: [],
          keyword: '',
          loading: false,
          finished: false,
          totalTitle: '题库错题数量',
          total: 0,
          paging: {
            current: 0,
            total: 0,
          },
        },
      ],
    };
  },

  created() {
    this.fetchWrongQuestionBooks();
  },

  methods: {
    fetchWrongQuestionBooks() {
      Api.getWrongBooks().then(res => {
        this.list[0].total = res.course.sum_wrong_num;
        this.list[1].total = res.classroom.sum_wrong_num;
        this.list[2].total = res.exercise.sum_wrong_num;
      });
    },

    onLoad(index) {
      const list = this.list[index];
      list.loading = true;

      Api.getWrongBooksCertainTypes({
        query: {
          targetType: list.type,
        },
        params: {
          keyWord: list.keyword,
          offset: list.paging.current * 10,
        },
      }).then(res => {
        if (list.refreshing) {
          list.items = [];
          list.refreshing = false;
        }
        list.items = list.items.concat(res.data);
        list.loading = false;
        list.refreshing = false;

        const { total } = res.paging;
        list.paging.total = total;
        list.paging.current++;

        if (list.items.length >= total) {
          list.finished = true;
        }
      });
    },

    onSearch(index, value) {
      const list = this.list[index];
      list.keyword = value;
      list.refreshing = true;
      list.paging.current = 0;
      this.onLoad(index);
    },
  },
};
</script>
