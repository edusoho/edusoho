import ajax from '../ajax';

const informationCollect = (api) => {
    return {
        getEvent(options) {
            return ajax(Object.assign({
                url: `${api}/information_collect_event/${options.params.action}`,
            }, options));
        }
    };
};

export default informationCollect;