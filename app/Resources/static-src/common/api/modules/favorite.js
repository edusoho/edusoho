import ajax from '../ajax';

const FavoriteModule = (api) => {
  return {
    favorite(options) {
      return ajax(Object.assign({
        url: `${api}/favorite`,
        type: 'POST',
      }, options));
    },
    unfavorite(options) {
      return ajax(Object.assign({
        url: `${api}/favorite`,
        type: 'DELETE',
      }, options));
    }
  };
};
export default FavoriteModule;