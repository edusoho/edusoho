<template>
  <van-pull-refresh v-model="refreshing" @refresh="onRefresh">
    <van-list
      v-model="loading"
      :finished="finished"
      :finished-text="$t('goods.noMore')"
      @load="onLoad"
      style="padding: 16px;"
    >
      <template #finished>
        <ListEmpty v-if="contractList.length === 0"/>
      </template>
      <div v-for="item in contractList" class="contract-item">
        <div class="flex">
          <img src="static/images/contract-icon.png" class="mr-12 w-56 h-56"/>
          <div class="flex flex-col justify-between">
            <div class="line-clamp-1 text-text-7 text-16 font-medium">{{ item.name }}</div>
            <div class="line-clamp-1 text-text-6 text-12">
              <template v-if="item.relatedGoods.type === 'course'">{{ $t('contract.relatedCourse') }}</template>
              <template v-if="item.relatedGoods.type === 'classroom'">{{ $t('contract.relatedClassRoom') }}</template>
              <template v-if="item.relatedGoods.type === 'itemBankExercise'">{{
                  $t('contract.relatedItemBank')
                }}
              </template>
              ：
              {{ item.relatedGoods.name }}
            </div>
          </div>
        </div>
        <div class="mt-16 flex">
          <van-button type="default" size="small" class="flex-1 mr-16 rounded-md"
                      @click="downloadContract(item.id, `${item.relatedGoods.name}-${item.name}`)">
            <div class="flex items-center space-x-8">
              <div>{{ $t('btn.download') }}</div>
              <van-loading color="#1989fa" size="16px" v-if="downloadLoading[item.id]"/>
            </div>
          </van-button>
          <van-button type="primary" size="small" class="flex-1 rounded-md" @click="viewContract(item)">
            {{ $t('btn.view') }}
          </van-button>
        </div>
      </div>
    </van-list>
  </van-pull-refresh>
</template>

<script>
import Api from '@/api';
import ListEmpty from './ListEmpty.vue';
import {Notify} from 'vant';

export default {
  name: 'MyContract',
  components: {
    ListEmpty
  },
  data() {
    return {
      loading: false,
      finished: false,
      refreshing: false,
      offset: 0,
      total: 0,
      limit: 0,
      contractList: [],
      downloadLoading: {},
    };
  },
  methods: {
    onRefresh() {
      this.finished = false;
      this.loading = true;
      this.onLoad();
    },
    async onLoad() {
      if (this.refreshing) {
        this.contractList = [];
        this.offset = 0;
        this.total = 0;
        this.refreshing = false;
      }

      const {paging, data} = await Api.getMyContract({
        params: {offset: this.offset}
      });

      this.contractList = this.contractList.concat(data);
      this.total = Number(paging.total);
      this.offset = Number(paging.offset) + Number(paging.limit);
      this.loading = false;

      if (this.contractList.length >= this.total) {
        this.finished = true;
      }
    },
    viewContract(item) {
      this.$router.push({name: 'myContractDetail', params: {id: item.id}});
    },
    async downloadContract(id, fileName) {
      try {
        this.$set(this.downloadLoading, id, true);
        const response = await Api.downloadContract({
          query: {id: id},
          responseType: 'blob'
        });

        const url = window.URL.createObjectURL(response);
        const a = document.createElement('a');
        a.href = url;
        a.download = fileName;
        document.body.appendChild(a);
        this.$set(this.downloadLoading, id, false);
        a.click();

        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
      } catch (error) {
        console.log('error', error)
        this.$set(this.downloadLoading, id, false);
        Notify({type: 'danger', message: `${ this.$t('contract.contractDownloadFailure') }`});
      }
    }
  }
};
</script>

<style>
.contract-item {
  padding: 20px 16px;
  margin-bottom: 12px;
  border: solid 1px #E5E6EB;
  border-radius: 6px;
  background-color: #fff;
}
</style>
