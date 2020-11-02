import ajax from '../ajax';

const reviewModule = (api) => {
  return {
    reviewPost(options) {
      return ajax(Object.assign({
        url: `/api/review/${options.params.reviewId}/post`,
        type: 'POST',
      }, options));
    },
    review(options) {
      return ajax(Object.assign({
        url: '/api/reviews',
        type: 'POST',
      }, options));
    }
  };
};
export default reviewModule;