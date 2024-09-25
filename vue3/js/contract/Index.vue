<script>
import AntConfigProvider from '../components/AntConfigProvider.vue';
import {defineComponent} from 'vue';

export default defineComponent({
  components: {AntConfigProvider}
});

</script>

<template>
  <ant-config-provider>
    <router-view/>
  </ant-config-provider>
</template>
