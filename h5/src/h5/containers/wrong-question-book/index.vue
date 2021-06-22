<template>
  <van-tabs :border="true" color="#43c793">
    <template v-for="(listItem, index) in list">
      <van-tab :title="listItem.title" :key="index">
        <van-search
          v-model="listItem.keyword"
          shape="round"
          placeholder="请输入搜索关键词"
          @search="value => onSearch(index, value)"
        />
        <van-list
          class="wrong-list"
          v-model="listItem.loading"
          :finished="listItem.finished"
          finished-text="没有更多了"
          @load="onLoad(index)"
        >
          <item v-for="item in listItem.items" :key="item" :title="item" />
        </van-list>
        <div class="wrong-question-number">
          {{ listItem.totalTitle }}：{{ listItem.total }}
        </div>
      </van-tab>
    </template>
  </van-tabs>
</template>

<script>
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
          items: [],
          keyword: '',
          loading: false,
          error: false,
          finished: false,
          totalTitle: '课程错题数量',
          total: 0,
        },
        {
          title: '班级错题',
          items: [],
          keyword: '',
          loading: false,
          error: false,
          finished: false,
          totalTitle: '班级错题数量',
          total: 0,
        },
        {
          title: '题库错题',
          items: [],
          keyword: '',
          loading: false,
          error: false,
          finished: false,
          totalTitle: '题库错题数量',
          total: 0,
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

<style lang="scss" scoped>
@import '../../assets/styles/mixins.scss';

.wrong-list {
  padding: 0 vw(16) vw(20);
  margin-top: 0;
}

.wrong-question-number {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  padding: vw(4) 0;
  text-align: center;
  font-size: vw(10);
  color: #999;
  line-height: vw(20);
  background-color: #fff;
  box-shadow: 0px -1px 2px 0px rgba(0, 0, 0, 0.05);
}
</style>
