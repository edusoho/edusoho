<template>
    <div>
        <div v-if="reviews.length <= 0 && !canCreate" v-html="reviewEmptyHtml" class="description-content"
             style="padding-left: 14px; padding-top: 10px;"></div>
        <div class="reviews">
            <create-review :target-id="targetId" :target-type="targetType" :can-create="canCreate"
                           :current-user-id="currentUserId"
                           :captcha="captcha"></create-review>
            <div class="reviews-item" v-for="review in reviews" :key="review.id" :class="'reviews-item-'+ review.id">
                <a  target="_blank" :class="'card-'+review.user.uuid">
                    <img class="js-user-card reviews-item__img"
                         :src="review.user.avatar.large"
                         :data-user-id="review.user.uuid"
                         :data-card-url="`/user/${review.user.uuid}/card/show`"
                         alt=""
                    >
                </a>
                <div class="reviews-item__text reviews-text">
                    <div class="reviews-text__nickname">
                        <a class="link-dark js-user-url" :class="'user-url-'+review.user.uuid" :data-userid ="review.user.uuid" target="_blank">{{ review.user.nickname }}</a>
                        <!--                    <span>{{ review.target.title }}</span>-->
                        {{ review.createdTime | createdTime }}
                    </div>
                    <div class="reviews-text__rating" v-html="$options.filters.rating(review.rating)"></div>
                    <div class="reviews-text__content" :id="`review-content-${review.id}`" style="white-space: pre-wrap;">{{ review.content|removeHtml}}<span v-if="currentUserId > 0 && review.me_report" style="color: red;">(已举报)</span>
                    </div>
                    <div class="reviews-text__reply">
                        <a class="review-text__hover" :id="`js-review-modal-${review.id}`" v-if="currentUserId > 0 && review.user.id != currentUserId && !review.me_report" href="#modal" data-toggle="modal" :data-url="`/common/report/${reportType}/target_id/${review.id}/tags_modal?contentTarget=review-content-${review.id}&modalTarget=js-review-modal-${review.id}`">举报</a>
                        <a href="javascript:;"
                           v-if="canCreate || review.posts.length > 0"
                           :data-toggle="'reviews-text__reply-content-'+review.id"
                           @click="switchDisplay">
                            <span v-if="review.posts.length == 0">{{ 'thread.post.reply'|trans }}</span>
                            <span v-else>{{ 'site.data.collapse'|trans }}</span>
                        </a>
                    </div>
                    <div v-if="review.posts.length == 0" class="reviews-text__reply-content clearfix hidden"
                         :class="'reviews-text__reply-content-'+review.id">
                        <form>
                            <textarea class="post-content" @blur="validatePostContent"></textarea>
                            <p></p>
                            <a :ref="`saveBtn-${review.id}`" href="javascript:;" class="btn btn-sm btn-default plm prm pull-right"
                               @click="onSave($event, review.id)">{{ 'form.btn.save'|trans }}</a>
                        </form>
                    </div>
                    <div v-else class="reviews-text__reply-content clearfix"
                         :class="'reviews-text__reply-content-'+review.id">
                        <ul class="media-list thread-post-list thread-subpost-list">
                            <li class="thread-post media" :class="'thread-subpost-'+post.id"
                                v-for="post in review.posts" :key="post.id">
                                <div class="media-left">
                                    <a  target="_blank" :class="'card-'+post.user.id">
                                        <img class="avatar-sm js-user-card"
                                             :src="post.user.avatar.large"
                                             :data-user-id="post.user.id"
                                             :data-card-url="`/user/${post.user.uuid}/card/show`"
                                             alt=""
                                        >
                                    </a>
                                </div>
                                <div class="media-body" style="overflow: visible !important;">
                                    <div class="metas">
                                        <div v-if="canOperate || (currentUserId > 0 && post.user.id != currentUserId && !post.me_report)" class="thread-post-manage-dropdown dropdown pull-right">
                                            <a href="javascript:;" class="dropdown-toggle color-gray"
                                               data-toggle="dropdown">
                                                <span class="glyphicon glyphicon-collapse-down"></span>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <li v-if="canOperate">
                                                    <a href="javascript:"
                                                       class="js-delete-post"
                                                       :data-target="'thread-subpost-'+post.id"
                                                       :data-target-id="post.id">{{'site.delete'|trans}}</a>
                                                </li>
                                                <li v-if="currentUserId > 0 && post.user.id != currentUserId && !post.me_report" :id="`js-review-reply-modal-${post.id}`">
                                                    <a href="#modal"
                                                       class="js-report-post"
                                                       data-toggle="modal"
                                                       :data-url="`/common/report/${replyReportType}/target_id/${post.id}/tags_modal?contentTarget=review-reply-content-${post.id}&modalTarget=js-review-reply-modal-${post.id}`"
                                                    >举报</a>
                                                </li>
                                            </ul>
                                        </div>

                                        <a class="link-dark js-user-url" :class="'user-url-'+post.user.id" :data-userid ="post.user.id"target="_blank">
                                            {{ post.user.nickname }}
                                        </a>
                                        <span class="bullet">•</span>
                                        <span class="color-gray">{{post.createdTime|smart_time}} </span>
                                    </div>
                                    <div class="editor-text" :id="`review-reply-content-${post.id}`">{{ post.content|removeHtml }} <span v-if="currentUserId > 0 && post.me_report" style="color: red;">(已举报)</span>  </div>
                                    <div class="ptm pbl"></div>
                                </div>
                            </li>
                        </ul>
                        <a href="javascript:;" class="btn btn-default btn-xs pull-right mbs"
                           v-if="canCreate"
                           :data-toggle="'reviews-text__reply-content-form-'+review.id"
                           @click="onFormDisplay">{{ 'thread.post.reply'|trans }}</a>
                        <form class="hidden" :class="'reviews-text__reply-content-form-'+review.id">
                            <textarea class="post-content" @blur="validatePostContent"></textarea>
                            <p></p>
                            <a :ref="`saveBtn-${review.id}`" href="javascript:;" class="btn btn-sm btn-default plm prm pull-right"
                               @click="onSave($event, review.id)">{{ 'form.btn.save'|trans }}</a>
                        </form>
                    </div>
                </div>
            </div>
            <div class="learn-more"><a href="javascript:;"
                                       v-if="parseInt(paging.offset) + parseInt(paging.limit) < parseInt(paging.total)"
                                       @click="searchReviews(parseInt(paging.offset) + parseInt(paging.limit), paging.limit)"
                                       :data-page="multiOffset" :data-limit="paging.limit" :data.total="paging.total">查看更多<i
                class="es-icon es-icon-chevronright"></i></a>
            </div>
        </div>
    </div>

</template>

<script>
    import axios from 'axios';
    import createReview from './src/create-review';
    import Api from 'common/api';
    import Captcha from 'app/common/captcha';

    axios.interceptors.request.use((config) => {
        config.headers = {
            'Accept': 'application/vnd.edusoho.v2+json',
            'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        };

        return config;
    });

    axios.interceptors.response.use((response) => {
        return response;
    }, (error) => {
        if (error.response.data.error.message) {
            cd.message({
                'type': 'danger',
                'message': error.response.data.error.message
            });
        } else {
            cd.message({
                'type': 'danger',
                'message': Translator.trans('site.service_error_hint')
            });
        }
        return error;
    });

    const captcha = new Captcha({ drag: { limitType: "course", bar:'#drag-btn', target: '.js-jigsaw' } });
    captcha.isShowCaptcha = $(captcha.params.maskClass).length ? 1 : 0

    export default {
        name: 'reviews',
        components: {
            createReview
        },
        data() {
            this.searchReviews();
            return {
                postContent: '',
                reviews: [],
                paging: {
                    limit: 5,
                    offset: 0,
                    total: 0
                },
                _dragCaptchaToken: '',
                captcha
            }
        },
        computed: {
            multiOffset() {
                return this.paging.offset;
            },
            reviewEmptyHtml() {
                return '暂无评价哦～';
            }
        },
        props: {
            targetType: {
                type: String,
                default: null
            },
            targetId: {
                type: Number,
                default: null
            },
            reportType: {
                type: String,
                default: null
            },
            replyReportType: {
                type: String,
                default: null
            },
            needPosts: {
                type: Boolean,
                default: true
            },
            limit: {
                type: Number,
                default: null
            },
            canCreate: {
                type: Boolean,
                default: false,
            },
            canOperate: {
                type: Boolean,
                default: false,
            },
            currentUserId: {
                type: Number,
                default: null
            }
        },
        created() {
            const _this = this;
            captcha.on('success', (data) => {
                if (data.type === 'review') {
                    captcha.isShowCaptcha = 0;
                    _this._dragCaptchaToken = data.token;
                    _this.$refs[`saveBtn-${captcha.reviewId}`][0].click();
                }
            })
        },
        methods: {
            searchReviews(offset = 0, limit = 5) {
                if (!this.targetType || !this.targetId) {
                    return;
                }

                axios.get('/api/reviews', {
                    params: {
                        targetType: this.targetType,
                        targetId: this.targetId,
                        offset: parseInt(offset),
                        limit: this.limit == null ? parseInt(limit) : this.limit,
                        needPosts: this.needPosts,
                    },
                }).then(response => {
                    this.reviews = this.reviews.concat(response.data.data);
                    this.paging = response.data.paging;
                });
            },
            switchDisplay(event) {
                let $target = $('.' + $(event.currentTarget).data('toggle'));

                $target.toggleClass('hidden');
                if ($target.hasClass('hidden')) {
                    $(event.currentTarget).html(Translator.trans('thread.post.reply'));
                } else {
                    $(event.currentTarget).html(Translator.trans('site.data.collapse'));
                }
            },
            onFormDisplay(event) {
                if ($('.' + $(event.currentTarget).data('toggle')).hasClass('hidden') && $(event.currentTarget).siblings('ul').find('.thread-post').length >= 5) {
                    cd.message({
                        type: 'danger',
                        message: Translator.trans('course.manage.post_limit_hint')
                    });
                    return;
                }

                this.switchDisplay(event);
            },
            validatePostContent(event, reviewId) {
                let $form = $(event.currentTarget).parent('form');
                if (!$form.find('.post-content').val().trim()) {
                    $form.find('.post-content').addClass('form-control-error');
                    $form.find('p').addClass('form-error-message');
                    $form.find('p').html(Translator.trans('validate.empty_content_hint'));

                    return false;
                }

                $form.find('.post-content').removeClass('form-control-error');
                $form.find('p').removeClass('form-error-message');
                $form.find('p').empty();

                if (reviewId && $("input[name=enable_anti_brush_captcha]").val() == 1 && captcha.isShowCaptcha == 1){
                    captcha.setType('review')
                    captcha.reviewId = reviewId
                    captcha.showDrag();

                    return false;
                }

                return true;
            },
            generateReviewPostLi(post) {
                let html = '<li class="thread-post thread-subpost-' + post.id + ' media">\n' +
                    '  <div class="media-left">\n' +
                    `<a href="/user/${post.user.uuid}" target="_blank">
                        <img class="avatar-sm js-user-card"
                             src="${post.user.avatar.large}"
                             data-user-id="${post.user.id}"
                             data-card-url="/user/${post.user.uuid}/card/show"
                             alt=""
                        >
                    </a>` +
                    '  </div>\n' +
                    '  <div class="media-body" style="overflow: visible !important;">\n' +
                    '    <div class="metas">\n';
                if (this.canOperate) {
                    html = html + ' <div class="thread-post-manage-dropdown dropdown pull-right">\n' +
                        '             <a href="javascript:;" class="dropdown-toggle color-gray" data-toggle="dropdown">\n' +
                        '               <span class="glyphicon glyphicon-collapse-down"></span></a>\n' +
                        '             <ul class="dropdown-menu">\n' +
                        '               <li>\n' +
                        '                 <a href="javascript:" class="js-delete-post" data-target="thread-subpost-' + post.id + '" data-target-id="' + post.id + '">' +
                        Translator.trans('site.delete') + '</a></li></ul></div>';
                }

                html = html + `      <a class="link-dark" href="/user/${post.user.uuid}" target="_blank">\n` + post.user.nickname +
                    '      </a>\n' +
                    '      <span class="bullet">•</span>\n' +
                    '      <span class="color-gray">' + Translator.trans('site.twig.extension.smarttime.hardly') + '</span>\n' +
                    '    </div>\n' +
                    '    <div class="editor-text">' + post.content + '</div>\n' +
                    '    <div class="ptm pbl"></div>' +
                    '  </div>\n' +
                    '</li>';

                return html;

            },
            onSave(event, reviewId) {
                let $targetForm = $(event.currentTarget).parent('form');

                if ($targetForm.siblings('ul').find('.thread-post').length >= 5) {
                    cd.message({
                        type: 'danger',
                        message: Translator.trans('course.manage.post_limit_hint')
                    });
                    return;
                }

                if (!this.validatePostContent(event, reviewId)) {
                    return;
                }

                Api.review.reviewPost({
                    params: {
                        reviewId: reviewId,
                        _dragCaptchaToken: this._dragCaptchaToken
                    },
                    data: {
                        'content': $targetForm.find('.post-content').val().trim()
                    }
                }).then(res => {
                    let html = this.generateReviewPostLi(res);

                    if ($targetForm.siblings('ul').length) {
                        $targetForm.siblings('ul').append(html);
                    } else {
                        html = '<ul class="media-list thread-post-list thread-subpost-list">' + html + '</ul>';
                        $targetForm.before(html);
                    }

                    $targetForm.find('.post-content').val('');
                    cd.message({
                        type: 'success',
                        message: Translator.trans('site.save_success_hint')
                    });
                }).finally(() => {
                    captcha.isShowCaptcha = 1;
                    captcha.hideDrag();
                })

                // axios({
                //     url: "/api/review/" + reviewId + "/post",
                //     method: "POST",
                //     data: {
                //         'content': $targetForm.find('.post-content').val().trim()
                //     },
                // }).then(res => {
                //     let html = this.generateReviewPostLi(res.data);
                //
                //     if ($targetForm.siblings('ul').length) {
                //         $targetForm.siblings('ul').append(html);
                //     } else {
                //         html = '<ul class="media-list thread-post-list thread-subpost-list">' + html + '</ul>';
                //         $targetForm.before(html);
                //     }
                //
                //     $targetForm.find('.post-content').val('');
                //     cd.message({
                //         type: 'success',
                //         message: Translator.trans('site.save_success_hint')
                //     });
                // });
            },
            onDelete() {
                $('.reviews').on('click', '.js-delete-post', function (event) {
                    event.stopPropagation();
                    let $target = $('.' + $(event.currentTarget).data('target'));

                    cd.confirm({
                        title: '',
                        content: Translator.trans('thread.post.delete_hint'),
                        okText: Translator.trans('site.confirm'),
                        cancelText: Translator.trans('site.cancel'),
                        className: '',
                    }).on('ok', () => {
                        axios({
                            url: "/api/review/" + $(event.currentTarget).data('targetId'),
                            method: 'DELETE',
                        }).then(res => {
                            $target.remove();
                            cd.message({
                                type: 'success',
                                message: Translator.trans('site.delete_success_hint')
                            });
                        });
                    }).on('cancel', () => {
                    });
                });
            },
          onUserUrl() {
            $('.reviews').on('mouseover', '.js-user-url', function (event) {
              event.stopPropagation();
              let userid = $(event.currentTarget).data('userid')
              let userUrl = $(".user-url-" + userid);
              let attr = userUrl.attr('href');
              if (typeof attr === typeof undefined) {
                axios({
                  url: "/api/student_open_info/" + userid,
                  method: "GET",
                }).then(res => {
                  if (res.data.enable === 1) {
                    userUrl.attr('href',"/user/" + userid);
                  }
                });
              }
            });
          },
        },
        filters: {
            createdTime(date) {
                return date.slice(0, 10);
            },
            rating(score) {
                let floorScore = Math.floor(score);
                let emptyNum = 5 - floorScore;
                let ele = '';
                if (floorScore > 0) {
                    for (let i = 0; i < floorScore; i++) {
                        ele += `<i class="es-icon es-icon-star color-warning"></i>`;
                    }
                }
                if ((score - floorScore) >= 0.5) {
                    ele += `<i class="es-icon es-icon-starhalf color-warning"></i>`;
                }
                if (emptyNum > 0) {
                    for (let i = 0; i < emptyNum; i++) {
                        ele += `<i class="es-icon es-icon-staroutline"></i>`;
                    }
                }
                return ele;
            },
            trans(value, params) {
                if (!value) return '';
                return Translator.trans(value, params);
            },
            removeHtml(input) {
                return input && input.replace(/<(?:.|\n)*?>/gm, '')
                    .replace(/(&rdquo;)/g, '\"')
                    .replace(/&ldquo;/g, '\"')
                    .replace(/&mdash;/g, '-')
                    .replace(/&nbsp;/g, '')
                    .replace(/&amp;/g, '&')
                    .replace(/&gt;/g, '>')
                    .replace(/&lt;/g, '<')
                    .replace(/<[\w\s"':=\/]*/, '');
            },
            smart_time(value) {
                let time = new Date(value);

                let diff = parseInt(new Date().getTime()) / 1000 - parseInt(time.getTime()) / 1000;

                if (diff < 0) {
                    return Translator.trans('site.twig.extension.smarttime.future');
                }

                if (0 == diff) {
                    return Translator.trans('site.twig.extension.smarttime.hardly');
                }

                if (diff < 60) {
                    return Translator.trans('site.twig.extension.smarttime.previous_second', {'diff': Math.round(diff)});
                }

                if (diff < 3600) {
                    return Translator.trans('site.twig.extension.smarttime.previous_minute', {'diff': Math.round(diff / 60)});
                }

                if (diff < 86400) {
                    return Translator.trans('site.twig.extension.smarttime.previous_hour', {'diff': Math.round(diff / 3600)});
                }

                if (diff < 2592000) {
                    return Translator.trans('site.twig.extension.smarttime.previous_day', {'diff': Math.round(diff / 86400)});
                }

                if (diff < 31536000) {
                    return value.slice(4, 5);
                }

                return value.slice(0, 10);
            }
        },
        mounted() {
            this.onDelete();
            this.onUserUrl();
        }
    }
</script>
