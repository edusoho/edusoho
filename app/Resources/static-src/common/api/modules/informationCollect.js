import ajax from '../ajax';

const informationCollect = (api) => {
    return {
        getEvent(options) {
            return ajax(Object.assign({
                url: `${api}/information_collect_event/${options.params.action}`,
            }, options));
        },
        submitEvent(options) {
            return ajax(Object.assign({
                url: `${api}/information_collect_form`,
                type: 'POST',
            }, options));
        }
    };
};

export default informationCollect;