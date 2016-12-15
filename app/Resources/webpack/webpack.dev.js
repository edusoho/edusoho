import esWebpackEngine from 'es-webpack-engine';

import config from './webpack.config';
import settings from './settings';

const options = Object.assign({}, config, settings);

esWebpackEngine(options);