import { isEmpty } from 'common/utils';
import 'waypoints/lib/jquery.waypoints.min';
import Emitter from 'common/es-event-emitter';
import { debounce } from 'app/common/widget/debounce';
/**
 *
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
           如果tmp节点上有 js-ignore-remove, 则不会被删除
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
        'displayAllImmediately': false,  //没有分页刷新功能，直接显示全部
        'afterFirstLoad': function, //加载第一页后，会做的操作
        'displayItem': {'key': 'taskId', 'value': '123'}, //显示指定数据项，第一页没有，则自动展开n页直到显示该项为止
   *  }
   */
  constructor(options) {
    super();

    this._options = options;
    this._initConfig();
    this.chapterAnimate();
    if (this._displayAllImmediately) {
      this._displayCurrentPageDataAndSwitchToNext();
    } else {
      this._initUpLoading();
    }
  }

  toggleIcon(target, $expandIconClass, $putIconClass) {
    return new Promise((resolve, reject) => {
      let $icon = target.find('.js-remove-icon');
      let $text = target.find('.js-remove-text');
      if ($icon.hasClass($expandIconClass)) {
        $icon.removeClass($expandIconClass).addClass($putIconClass);
        if ($('.js-only-display-one-page').length == 0) {
          this._displayCurrentPageDataAndSwitchToNext();
        }
      } else {
        $icon.removeClass($putIconClass).addClass($expandIconClass);
      }
      resolve();
    });
  }

  chapterAnimate(
    delegateTarget = 'body',
    target = '.js-task-chapter',
    $expandIconClass = 'es-icon-remove',
    $putIconClass = 'es-icon-anonymous-iconfont') {
    const self = this;
    $(delegateTarget).off('click').on('click', target, (event) => {
      let $this = $(event.currentTarget);
      self.toggleIcon($this, $expandIconClass, $putIconClass).then(() => {
        $this.nextUntil(target).animate({ height: 'toggle', opacity: 'toggle' }, 'normal');
      });
    })
  }

  _initUpLoading() {
    if ($('.js-down-loading-more').length != 0) {
      const self = this;
      // 滚动到 class='js-down-loading-more' 的dom节点时，自动刷新下一页
      let waypoint = new Waypoint({
        element: $('.js-down-loading-more')[0],
        handler: function(direction) {
          if (direction == 'down') {
            if (self._isLastPage || self._canNotDisplayMore()) {
              waypoint.disable();
            } else {
              self._scrollToBottom();
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
  }

  _initConfig() {
    this._currentPage = 1;
    this._displayAllImmediately = this._options['displayAllImmediately'] ? true : false;

    if (this._displayAllImmediately) {
      this._pageSize = 10000;
    } else {
      this._pageSize = this._options['pageSize'] ? this._options['pageSize'] : 25;
    }
    //适配介绍页的查看全部要固定25个
    if (this._pageSize > 25 && $('.js-only-display-one-page').length != 0) {
      this._pageSize = 25
    }

    this._afterFirstLoad = this._options['afterFirstLoad'] ? this._options['afterFirstLoad'] : null;
    this._isFirstLoad = true;

    if (this._options['displayItem']) {
      this._displayItemDisplayed = false;
      this._displayItem = this._options['displayItem'];
    } else {
      this._displayItemDisplayed = true;
      this._displayItem = null;
    }
    this._isLastPage = false;
  }

  _displayCurrentPageDataAndSwitchToNext() {
    this._displayData();
    if (!this._isLastPage) {
      this._currentPage++;
    }

    if (this._isFirstLoad) {
      if (!this._displayItemDisplayed) {
        this._displayCurrentPageDataAndSwitchToNext();
      } else {
        this._isFirstLoad = false;
        if (this._afterFirstLoad) {
          this._afterFirstLoad();
        }
      }
    }

  }

  _displayData() {
    if (this._isLastPage) {
      return;
    }
    let startIndex = this._getStartIndex();
    for (let index = 0; index < this._pageSize; index++) {
      let data = this._options['data'][index + startIndex];
      if (!this._displayItemDisplayed) {
        let key = this._displayItem['key'];
        let value = this._displayItem['value'];
        if (data[key] == value) {
          this._displayItemDisplayed = true;
        }
      }
      if (!isEmpty(data)) {
        this._generateSingleCachedData(data);
      } else {
        this._isLastPage = true;
      }
    }
  }

  _scrollToBottom() {
    const self = this;
    const $target = $('.js-sidebar-pane');
    if (!$target.length) {
      return;
    }
    const $targetDom = $target[0];
    const sidebarHight = $target.height();
    const scrollHight = $targetDom.scrollHeight;
    const scrollTop = $targetDom.scrollTop;
    if (self._afterFirstLoad) {
      $targetDom.addEventListener('scroll', debounce(() => {
        if (scrollTop + sidebarHight >= scrollHight && !this._isLastPage) {
          self._displayCurrentPageDataAndSwitchToNext();
        }
      }, 500, true));
    }
  }


  _generateSingleCachedData(data) {
    const templateClass = this._options['dataTemplateNode'];
    let clonedHtml = this._options.wrapDom ? this._options.wrapDom.find(templateClass).html() : $(templateClass).html();

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

    //%7B %7D 是转义后的 { 和 }
    replacedHtml = replacedHtml.replace(
      /(%7B\w+%7D)/g,
      function(param) {
        return self._replace(param, currentData, '%7B', '%7D');
      }
    );

    let tempNode = $('<div>').append(replacedHtml);

    this._removeUnNeedNodes(tempNode);
    let $infiniteContainer = this._options.wrapDom ? this._options.wrapDom.find('.infinite-container'): $('.infinite-container');
    $infiniteContainer.append(tempNode.html());
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

  _canNotDisplayMore() {
    return this._currentPage != 1 && $('.js-only-display-one-page').length != 0;
  }

  _removeUnNeedNodes(tempNode) {
    tempNode.find('[display-if=false]').remove();
    tempNode.find('[display-if=0]').remove();
    tempNode.find('[hide-if=1]').remove();
    tempNode.find('[hide-if=true]').remove();

    // 调试用， tmp node如果有 js-ignore-remove class, 不会自动删除
    tempNode.find('tmp :first-child').each(
      function() {
        let parentNode = $(this).parent();
        if (!parentNode.hasClass('js-ignore-remove') && parentNode[0].nodeName == 'TMP') {
          $(this).unwrap();
        }
      }
    );
  }
}