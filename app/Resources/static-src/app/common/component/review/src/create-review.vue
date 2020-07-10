<template>
    <div class="create-review" v-if="canCreate">
        <form v-show="!userReview || (userReview && showForm)">
            <div class="review-form-rating create-review__grade">
                {{ 'validate.raty_star.message'|trans }}：
                <span @mouseleave="leaveRating">
                    <img v-for="(star, index) in stars" :src="star.src" :key="index" @click="rating(index)"
                         @mouseenter="enterRating(index)"/>
                </span>
                <p></p>
            </div>
            <div class="review-form-content">
                <textarea class="form-control" v-model="form.content"></textarea>
                <p></p>
            </div>
            <div class="create-review__btn">
                <span class="btn-cancel" @click="onCancle(false)">{{ 'site.cancel'|trans }}</span>
                <span class="btn-confirm" @click="onConfirm">{{ 'form.btn.save'|trans }}</span>
            </div>
        </form>

        <div v-show="userReview && !showForm" class="create-review__btn" @click="onCancle(true)"><span
            class="btn-confirm">{{ 'reviews.review_again'|trans }}</span>
        </div>
    </div>
</template>

<script>
    let starOnImg = '/assets/img/raty/star-on.png';
    let starOffImg = '/assets/img/raty/star-off.png';
    import axios from 'axios';

    export default {
        created() {
            if (this.form.rating) {
                this.rating(this.form.rating - 1);
            }
        },
        data() {
            return {
                stars: [{
                    src: starOffImg,
                    active: false
                }, {
                    src: starOffImg,
                    active: false
                }, {
                    src: starOffImg,
                    active: false
                }, {
                    src: starOffImg,
                    active: false
                }, {
                    src: starOffImg,
                    active: false
                }],
                form: {
                    targetType: this.userReview ? this.userReview.targetType : this.targetType,
                    targetId: this.userReview ? this.userReview.targetId : this.targetId,
                    rating: this.userReview ? this.userReview.rating : 0,
                    content: this.userReview ? this.userReview.content : null,
                },
                starHover: 0,
                content: "",
                showForm: false
            }
        },
        props: {
            targetType: {
                type: String,
                default: null,
            },
            targetId: {
                type: Number,
                default: null,
            },
            canCreate: {
                type: Boolean,
                default: false,
            },
            userReview: {
                type: Object,
                default: null
            }
        },
        methods: {
            enterRating(index) {
                let total = this.stars.length;
                let idx = index + 1;
                if (this.starHover == 0) {
                    this.starHover = idx;
                    for (let i = 0; i < this.stars.length; i++) {
                        if (i < this.starHover) {
                            this.stars[i].src = starOnImg;
                            this.stars[i].active = true;
                        } else {
                            this.stars[i].src = starOffImg;
                            this.stars[i].active = false;
                        }
                    }
                } else {
                    if (idx < this.starHover) {
                        for (let i = idx; i < this.starHover; i++) {
                            this.stars[i].src = starOffImg;
                            this.stars[i].active = false;
                        }
                    }
                    if (idx > this.starHover) {
                        for (let i = 0; i < idx; i++) {
                            this.stars[i].src = starOnImg;
                            this.stars[i].active = true;
                        }
                    }
                    let count = 0;
                    for (let i = 0; i < total; i++) {
                        if (this.stars[i].active) {
                            count++;
                        }
                    }
                    this.starHover = count;
                }

            },
            leaveRating() {
                for (let i = 0; i < this.stars.length; i++) {
                    if (i < this.form.rating) {
                        this.stars[i].src = starOnImg;
                        this.stars[i].active = true;
                    } else {
                        this.stars[i].src = starOffImg;
                        this.stars[i].active = false;
                    }
                }
                this.starHover = 0;
            },
            rating(index) {
                console.log(index);
                let total = this.stars.length;
                let idx = index + 1;
                if (this.form.rating == 0) {
                    this.form.rating = idx;
                    for (let i = 0; i < idx; i++) {
                        this.stars[i].src = starOnImg;
                        this.stars[i].active = true;
                    }
                } else {
                    if (idx < this.form.rating) {
                        for (let i = idx; i < this.form.rating; i++) {
                            this.stars[i].src = starOffImg;
                            this.stars[i].active = false;
                        }
                    }

                    if (idx >= this.form.rating) {
                        for (let i = 0; i < idx; i++) {
                            this.stars[i].src = starOnImg;
                            this.stars[i].active = true;
                        }
                    }

                    let count = 0;
                    for (let i = 0; i < total; i++) {
                        if (this.stars[i].active) {
                            count++;
                        }
                    }
                    this.form.rating = count;
                }
            },
            onCancle(val) {
                this.showForm = val;
            },
            validateFormItems() {
                if (!this.form.content) {
                    $('.review-form-content').addClass('form-control-error');
                    $('.review-form-content').find('p').addClass('form-error-message');
                    $('.review-form-content').find('p').html(Translator.trans('validate.empty_content_hint'));

                    return false;
                } else {
                    $('.review-form-content').removeClass('form-control-error');
                    $('.review-form-content').find('p').removeClass('form-error-message');
                    $('.review-form-content').find('p').empty();
                }

                if (!this.form.rating) {
                    $('.review-form-rating').addClass('form-control-error');
                    $('.review-form-rating').find('p').addClass('form-error-message');
                    $('.review-form-rating').find('p').html(Translator.trans('validate.raty_star.message'));

                    return false;
                } else {
                    $('.review-form-rating').removeClass('form-control-error');
                    $('.review-form-rating').find('p').removeClass('form-error-message');
                    $('.review-form-rating').find('p').empty();
                }

                return true;
            },
            onConfirm() {
                if (!this.validateFormItems()) return;
                axios({
                    url: "/api/review",
                    method: "POST",
                    data: {
                        'targetType': this.targetType,
                        'targetId': this.targetId,
                        'content': this.form.content,
                        'rating': this.form.rating
                    }
                }).then(res => {
                    cd.message({
                        'type': 'success',
                        'message': Translator.trans('site.save_success_hint')
                    });
                    window.location.reload();
                });
            }
        }
    }
</script>