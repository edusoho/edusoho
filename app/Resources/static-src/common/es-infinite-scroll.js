import 'waypoints/lib/jquery.waypoints.min';
import 'waypoints/lib/shortcuts/infinite.min';
import Emitter from "common/es-event-emitter";

/**
 * 支持2种方式
 *   ajax 加载 及 缓存内伪分页， 见构造方法
 */
export default class ESInfiniteScroll extends Emitter {

  /**
   * @param options  
   *  {
   *    'type': 'text', //默认为text, 可设置json
   *    'dataSource': eval($('.infiniteScrollDataJson').val()),  
          //json数组，有值则不从ajax中获取, 格式为
            [
              {
                'itemType': 'chapter',
              },
              {
                'itemType': 'unit',
              }
            ]
   *    'dataSourceMapping': jsonData,
          //json, 用于将dataSource中的值做转换
          {
            'itemClass': {
              'itemType': {
                'chapter': 'bg-gray-lighter js-task-chapter',
                'unit': 'color-gray bg-gray-lighter',
                'task': 'task-content mouse-control'
              }
            }
          }
   *    'dataSourceTemplateNode': '.infiniteScrollTemplate',  
   *       //会通过 $(dataSourceTemplateNode)来找到模板, 模板格式为
            <div class="infinite-item infinite-item-template">  #本行必须
              <i class="es-icon es-icon-undone-check color-{color} left-menu"></i> ##
            </div>
   *  }
   */
  constructor(options) {
    super();

    this.options = options;

    this.initDownInfinite();
    this.initUpLoading();
  }

  initUpLoading() {
    $('.js-up-more-link').on('click', event => {
      let $target = $(event.currentTarget);
      $.ajax({
        method: 'GET',
        url: $target.data('url'),
        async: false,
        success: html => {
          $(html).find('.infinite-item').prependTo($('.infinite-container'));
          let $upLink = $(html).find('.js-up-more-link');
          if ($upLink.length > 0) {
            $target.data('url', $upLink.data('url'));
          } else {
            $target.remove();
          }
        }
      });

    })
  }

  initDownInfinite() {
    let defaultDownOptions = {
      element: $('.infinite-container')[0],
    };

    defaultDownOptions = Object.assign(defaultDownOptions, this.options);

    this.downInfinite = new Waypoint.Infinite(defaultDownOptions);
  }
}