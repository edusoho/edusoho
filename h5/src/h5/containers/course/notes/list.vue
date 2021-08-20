<template>
  <div class="note-list">
    <van-list
      v-model="loading"
      :finished="finished"
      @load="onLoad"
    >
      <note-item
        v-for="item in list"
        :key="item.id"
        :item="item"
        @click.native="handleClickViewDetail(item)"
        @handle-like="handleClickLike"
      />
    </van-list>
    <empty
      v-if="!list.length && finished"
      text="暂无笔记"
    />
  </div>
</template>

<script>
import _ from 'lodash';
import Api from '@/api';
import NoteItem from './components/NoteItem.vue';
import Empty from '&/components/e-empty/e-empty.vue';

export default {
  name: 'Note-list',

  components: {
    NoteItem,
    Empty
  },

  data() {
    return {
      list: [],
      loading: false,
      finished: false,
      paging: {
        offset: 0,
        limit: 10
      },
      courseId: this.$route.params.id
    }
  },

  methods: {
    onLoad() {
      const { offset, limit } = this.paging;
      Api.getCoursesNotes({
        query: {
          courseId: this.courseId
        },
        params: {
          limit: limit,
          offset: offset
        }
      }).then(res => {
        const { data, paging: { total } } = res;

        _.assign(this, {
          list: _.concat(this.list, data),
          loading: false
        });

        this.paging.offset++;

        if (_.size(this.list) >= total) {
          this.finished = true;
        }
      });
    },


    handleClickViewDetail(data) {
      this.$emit('change-current-component', { component: 'Detail', data });
    },

    handleClickLike(params) {
      const { noteId, status } = params;
      const query = {
        courseId: this.courseId,
        noteId
      };

      _.forEach(this.list, item => {
        const { id, likeNum } = item;

        if (noteId === id) {
          const note = status ? { like: {}, likeNum: likeNum - 1 } : { like: { status: 1 }, likeNum: (likeNum * 1) + 1 };

          status ? Api.cancelNoteLike({ query }) : Api.noteLike({ query });

          _.assign(item, note);

          return false;
        }
      });
    }
  }
}
</script>

<style lang="scss" scoped>
.note-list {

  .van-list {
    margin-top: 0;
  }

  .e-empty {
    margin-top: vw(50);
  }
}
</style>
