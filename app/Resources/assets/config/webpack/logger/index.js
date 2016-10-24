// https://github.com/winstonjs/winston/blob/master/examples/custom-levels.js

import winston from 'winston';

const config = {
  levels: {
    error: 0,
    debug: 1,
    warn: 2,
    data: 3,
    info: 4,
    verbose: 5,
    silly: 6,
  },
  colors: {
    error: 'bold red',
    debug: 'bold blue',
    warn: 'bold yellow',
    data: 'bold grey',
    info: 'bold green',
    verbose: 'bold cyan',
    silly: 'bold magenta',
  },
};

const logger = new (winston.Logger)({
  transports: [
    new (winston.transports.Console)({
      colorize: 'all',
      // colorize: true,
    }),
  ],
  levels: config.levels,
  colors: config.colors,
});

// logger.silly('silly');
// logger.verbose('verbose');
// logger.data('data');
// logger.debug('debug');
// logger.info('info');
// logger.warn('warn');
// logger.error('error');

export default logger;
