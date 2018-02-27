import { isEmpty } from 'common/utils';
import 'waypoints/lib/jquery.waypoints.min';
import Emitter from "common/es-event-emitter";

/** 
 * 伪分页，从缓存中读取数据，数据结构格式见 构造方法
 *   例子见 app/Resources/static-src/app/js/courseset/show/index.js
 *     及 app/Resources/views/course/task-list/default-task-list.html.twig
 * 
     <!-- 
      必须通过模板来生成节点, 模板由dataTemplateNode自定义（见构造函数）
        1. 变量以 {chapterName} 的方式生成，chapterName是变量，会先从 context 中找chapterName方法(参数为当前行data和context),
        如果没有此方法，则去data中找到相应属性
        2. display-if 如果结果为0或false, 则删除该节点
        3. hide-if 如果结果为1或true, 则删除该节点
        4. tmp节点, 一般用于处理 display-if 和 hide-if，会移除, 通过子节点unwrap的方式移除，如果没有子节点，无法移除
     -->
     <div class="js-infinite-item-template hidden">
      <li class="task-item bg-gray-lighter js-task-chapter infinite-item" 
          display-if="{isChapter}">
        <i class="es-icon es-icon-menu left-menu"></i>
        <a href="javascript:" class="title gray-dark">{chapterName}</a>
        <i class="right-menu es-icon es-icon-remove js-remove-icon"></i>
      </li>

      <tmp hide-if="{isChapter}">
        <tmp display-if="{isUnit}">
          // 节信息
        </tmp>
      </tmp>
    </div>

     <!-- 
       显示节点的容器, 必须要有class "infinite-container", 
       根据所给的值，会将模板内的节点生成并显示到容器内（不包含模板节点本身）
     -->
     <ul class="task-list task-list-md task-list-hover infinite-container">
     </ul>

     <!-- 当页面上看到此节点时，会自动显示下一页 -->
     <div class="js-down-loading-more" style="min-height: 1px"></div>
 */

export default class ESInfiniteCachedScroll extends Emitter {
  /**
   * @param options  
   *  {
   *    'data': eval($('.infiniteScrollDataJson').val()),  
          //json数组
            [
              {
                'itemType': 'chapter',
              },
              {
                'itemType': 'unit',
              }
            ]
   *    'context': jsonData,
          //json, 用于将data中的值做转换, 或设置一些公共变量
          // 支持普通json和方法
          {
            'course': {
              'id': 1,
              'isFree': 'true'
            },
            'isChapter': function(data, context) {
              return 'chapter' == data.itemType;
            },
          }
   *    'dataTemplateNode': '.js-infinite-item-template',  
   *        //会通过 $(dataTemplateNode)来找到模板, 只会生成 dataTemplateNode 指定节点内的内容（不包括该节点本身）,
   *        // 模板如下
            <div class="js-infinite-item-template hidden">  
              <i class="es-icon es-icon-undone-check color-{color} left-menu"></i> // 实际显示的节点
            </div>
   *  }
   */
  constructor(options) {
    super();

    this._options = options;
    this._initConfig();

    this._initUpLoading();
  }

  _initUpLoading() {
    const self = this;
    // 滚动到 class='js-down-loading-more' 的dom节点时，自动刷新下一页
    let waypoint = new Waypoint({
      element: $('.js-down-loading-more')[0],
      handler: function(direction) {
        if (direction == 'down') {
          if (self._isLastPage) {
            waypoint.disable();
          } else {
            waypoint.disable();
            self._displayCurrentPageDataAndSwitchToNext();
            Waypoint.refreshAll();
            waypoint.enable();
          }
        }
      },
      offset: 'bottom-in-view'
    });
  }

  _initConfig() {
    this._currentPage = 1;
    this._pageSize = this._options['pageSize'] ? this._options['pageSize'] : 25;
    this._isLastPage = false;
  }

  _displayCurrentPageDataAndSwitchToNext() {
    this._displayData();
    if (!this._isLastPage) {
      this._currentPage++;
    }
  }

  _displayData() {
    if (this._isLastPage) {
      return;
    }
    let startIndex = this._getStartIndex();
    for (let index = 0; index < this._pageSize; index++) {
      let data = this._options['data'][index + startIndex];
      if (!isEmpty(data)) {
        this._generateSingleCachedData(data);
      } else {
        this._isLastPage = true;
      }
    }
  }

  _generateSingleCachedData(data) {
    let clonedHtml = $(this._options['dataTemplateNode']).html();

    let currentData = data;
    const self = this;
    //所有花括号里面的内容都替换掉为相应变量值,
    // 如{lock} 替换为 this._options.context.lock 或 data.lock, 如果找不到，则不替换
    let replacedHtml = clonedHtml.replace(
      /({\w+})/g,
      function(param) {
        return self._replace(param, currentData, '{', '}');
      }
    );

    replacedHtml = replacedHtml.replace(
      /(%7B\w+%7D)/g,
      function(param) {
        return self._replace(param, currentData, '%7B', '%7D');
      }
    );

    let tempNode = $('<div>').append(replacedHtml);
    tempNode.find('[display-if=false]').remove();
    tempNode.find('[display-if=0]').remove();
    tempNode.find('[hide-if=1]').remove();
    tempNode.find('[hide-if=true]').remove();
    tempNode.find('tmp :first-child').each(
      function() {
        if (!$(this).hasClass('ignoreRemove') && $(this).parent()[0].nodeName == 'TMP') {
          $(this).unwrap();
        }
      }
    );

    $('.infinite-container').append(tempNode.html());
  }

  _getStartIndex() {
    return (this._currentPage - 1) * this._pageSize;
  }

  _replace(param, currentData, firstReplaceStr, secondReplaceStr) {
    let paramName = param.split(firstReplaceStr)[1].split(secondReplaceStr)[0];
    let context = this._options.context;
    if (typeof context[paramName] == 'function') {
      return context[paramName](currentData, context);
    } else if (typeof currentData[paramName] != 'undefined') {
      return currentData[paramName];
    }
    return param;
  }
}