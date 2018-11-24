import classroom from './classroom/index';
import setting from './setting/index';
import course from './course/index';
import lesson from './lesson/index';
import order from './order/index';
import me from './me/index';

const config = [...classroom, ...course, ...lesson, ...me, ...order, ...setting];

export default config;
