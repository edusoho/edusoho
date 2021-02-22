<template>
    <div style="height:1000px;">
        <split-pane v-on:resize="resize" :min-percent='20' :default-percent='30' split="vertical">
        <template slot="paneL">
            <div slot="left" class="left-box">
                    <div class="top-bar">
                        <div  class="top-bar">
                            <span class="course-name">标题名称</span>
                        </div>
                    </div>
            </div>
        </template>
        <template slot="paneR">
            B
        </template>
        </split-pane>
    </div>
</template>
<script>
    // import Info from "./component/info.vue"

    import splitPane from 'vue-splitpane'
    export default{
        name:"view",
        components:{
            splitPane
        },
        mounted() {
        }
    }
</script>

<style lang="less" scoped>
    
</style>