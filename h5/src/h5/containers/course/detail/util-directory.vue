<template>
    <div class="util-directory text-overflow" v-if="util.isExist==1">
        第{{util.number}}节：{{util.title}}
    </div>
</template>
<script>
export default {
    name:'utilDirectory',
    props:{
        util:{
            type:Object,
            default:()=>{}
        }
    }
}
</script>