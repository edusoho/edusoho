<template>
  <van-tabs>
    <template v-for="(listItem, index) in list">
      <van-tab :title="listItem.title" :key="index">
        <van-search
          v-model="listItem.keyword"
          shape="round"
          placeholder="请输入搜索关键词"
          @search="value => onSearch(index, value)"
        />
        <van-list
          v-model="listItem.loading"
          :finished="listItem.finished"
          finished-text="没有更多了"
          @load="onLoad(index)"
        >
          <van-cell v-for="item in listItem.items" :key="item" :title="item" />
        </van-list>
      </van-tab>
    </template>
  </van-tabs>
</template>

<script>
export default {
  name: 'MyWrongQuestionBook',

  data() {
    return {
      list: [
        {
          title: '课程错题',
          items: [],
          keyword: '',
          loading: false,
          error: false,
          finished: false,
        },
        {
          title: '班级错题',
          items: [],
          keyword: '',
          loading: false,
          error: false,
          finished: false,
        },
        {
          title: '题库错题',
          items: [],
          keyword: '',
          loading: false,
          error: false,
          finished: false,
        },
      ],
    };
  },

  methods: {
    onLoad(index) {
      const list = this.list[index];
      list.loading = true;
      setTimeout(() => {
        if (list.refreshing) {
          list.items = [];
          list.refreshing = false;
        }
        for (let i = 0; i < 10; i++) {
          const text = list.items.length + 1;
          list.items.push(text < 10 ? '0' + text : String(text));
        }
        list.loading = false;
        list.refreshing = false;
        // show error info in second demo
        if (index === 1 && list.items.length === 10 && !list.error) {
          list.error = true;
        } else {
          list.error = false;
        }
        if (list.items.length >= 40) {
          list.finished = true;
        }
      }, 1000);
    },

    onSearch(value, index) {
      this.list[index].keyword = value;
      this.list[index].finished = false;
      this.onLoad(index);
    },
  },
};
</script>
