<template>
  <div class="more">
    <treeSelect :selectType="selectType"></treeSelect>
    <lazyLoading></lazyLoading>
  </div>
</template>

<script>
  import treeSelect from '../components/e-tree-select/e-tree-select.vue';
  import lazyLoading from '../components/e-lazy-loading/e-lazy-loading.vue';

  export default {
    components: {
      treeSelect,
      lazyLoading,
    },
    data() {
      return {
        selectType: [
          {
            type: 'tree',
            text: '分类',
            children: [
              {
                text: '一',
                children: [
                  {
                    text: '一一',
                    children: [
                      {
                        text: '一一一',
                      }
                    ]
                  }
                ]
              },
            ]
          },
          {
            type: 'normal',
            text: '热门',
            children: [
              {
                text: '233'
              },
              {
                text: '233'
              },
              {
                text: '233'
              }
            ]
          },
          {
            type: 'normal',
            text: '最新',
            children: [
              {
                text: '233'
              },
              {
                text: '233'
              },
              {
                text: '233'
              }
            ]
          }
        ]
      };
    },
    created() {
      console.log(this.$route, 'query data');
    }
  }
</script>
