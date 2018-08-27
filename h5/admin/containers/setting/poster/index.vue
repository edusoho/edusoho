<template>
    <module-frame containerClass="setting-poster" :isActive="isActive">
        <div slot="preview" class="poster-image-container">
            <img v-bind:src="updateImg" class="poster-image">
            <img class="icon-delete" src="static/images/delete.png" @click="handleRemove()" v-show="isActive">
        </div>
    </module-frame>
</template>

<script>
import moduleFrame from '../module-frame'

export default {
    components: {
        moduleFrame,
    },
    data() {
        return  {
            activeItemIndex: 0,
            modalVisible: false,
            courseSets: [],
            defaultItem: {
                image: 'http://www.esdev.com/themes/jianmo/img/banner_net.jpg',
                link: {
                    type: 'url',
                    url: 'http://zyc.st.edusoho.cn'
                }
            },
            imgAdress: 'http://www.esdev.com/themes/jianmo/img/banner_net.jpg',
            parts: [{
                data:[],
            }]
        }
    },
    props: {
        active: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        isActive: {
            get() {
                return this.active;
            },
            set() {}
        },
        updateImg() {
            return this.imgAdress
        },
    },
    created() {
        this.addItem();
    },
    methods: {
        addItem() {
            this.parts[0].data.push(JSON.parse(JSON.stringify(this.defaultItem)));
        },
        selected(selected) {
            this.imgAdress = selected.imageUrl;
            this.activeItemIndex = selected.selectIndex;
        },
        handleRemove() {
            this.$el.remove();
        },
        getSortedCourses(courses) {
            this.courseSets = courses;
        },
        modalVisibleHandler(visible) {
            this.modalVisible = visible;
        },
        openModal() {
            this.modalVisible = true;
        },
    }
}

</script>