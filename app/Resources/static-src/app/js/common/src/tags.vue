<template>
    <div>
        <el-select
            class="un-multi-course-set-tags"
            v-model="tagData"
            multiple
            filterable
            remote
            reserve-keyword
            @focus="searchTags('')"
            :remote-method="searchTags"
            v-on:change="updateTags"
            :no-data-text="'validate.tag_required_not_found_hint'|trans"
            :loading="loading">
            <el-option
                v-for="tag in tags"
                :key="tag.name"
                :label="tag.name"
                :value="tag.name">
            </el-option>
        </el-select>
    </div>
</template>

<script>
    export default {
        name: "tags",
        props: {
            tagData: [],
            tagSearchUrl: '',
        },
        methods: {
            searchTags(query) {
                this.$axios.get(this.tagSearchUrl ? this.tagSearchUrl : '/tag/match_jsonp', {
                    params: {q: query},
                }).then((response) => {
                    this.tags = response.data;
                });

            },
            updateTags(value) {
                this.$emit('update:tags', value);
            }
        },
        data() {
            return {
                tagData: [],
                tags: [],
            }
        }
    }
</script>

<style scoped>

</style>