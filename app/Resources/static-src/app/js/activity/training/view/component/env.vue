<template>
    <div class="iframe-box" style="width:100%;height:100%;">
        <iframe v-show="lab_url"
                class="iframe"
                :src="lab_url"
                style="width: 100%;height: 100%;padding-left: 5px"
                ref="terminals"
        ></iframe>
        <div style="width:80%;margin:0 0 0 -100px;position:relative;left:20%;top:50%;">
            <div class="text" style="text-aligin:center;color:#ffffff;font-size:18px;font-weight:700;">{{text}}</div>
            <el-progress :text-inside="true" :stroke-width="24" :percentage="percentage"></el-progress>
        </div>
    </div>
</template>
<script>
    import common from './common/mixins'
    export default {
        mixins:[common],
        data(){
            return {
                lab_url:'',
            }
        }
    }
</script>