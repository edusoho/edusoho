import React from 'react';
import MultiInputList from 'app/common/component/multi-input/list';

export default class List extends MultiInputList {
  constructor(props) {
    super(props);
  }
  
  render() {
    const {
      dataSourceUi,
      listClassName,
      sortable,
      showCheckbox,
      showDeleteBtn,
      checkBoxName,
      inputName
    } = this.context;
    let name = '';
    if (dataSourceUi.length > 0) {
      name = 'list-group';
    }
    return (
      <ul id={this.listId} className={`multi-list sortable-list ${name} ${listClassName}`}>
        {
          dataSourceUi.map((item, i) => {
            return (
              <li className="list-group-item" id={item.itemId} key={i} data-seq={item.seq}>
                {sortable && <i className="es-icon es-icon-yidong mrl color-gray inline-block vertical-middle"></i>}
                <img className="avatar-sm avatar-sm-square mrm" src={item.avatar} />
                <span className="label-name text-overflow inline-block vertical-middle">{item.nickname}</span>
                <label className={showCheckbox ? '' : 'hidden'}><input type="checkbox" name={checkBoxName + item.id} checked={item.isVisible} onChange={event => this.context.onChecked(event)} value={item.itemId} />{Translator.trans('course.manage.teacher_display_label')}</label>
								<span data-toggle="tooltip" data-placement="top" title={Translator.trans(item.isLiveTeacher ? 'course.teacher.hover.title.live_teacher' : 'del.role.theater.hover.title')} className={item.isCanceledTeacherRoles || item.isLiveTeacher ? 'pull-right mtm mlm' : 'hidden'}>
									<i className="es-icon es-icon-infooutline text-16 text-FF7D00"></i>
								</span>
                <a className={showDeleteBtn && !item.isLiveTeacher ? 'pull-right link-gray mtm' : 'hidden'} onClick={event => this.context.removeItem(event)} data-item-id={item.itemId}>
                  <i className="es-icon es-icon-close01 text-16"></i>
                </a>
                <input type="hidden" name={inputName} value={item.id} />
              </li>
            );
          })
        }
      </ul>
    );
  }
}

List.contextTypes = {
  ...MultiInputList.contextTypes,
  showCheckbox: React.PropTypes.bool,
  showDeleteBtn: React.PropTypes.bool,
  checkBoxName: React.PropTypes.string,
  onChecked: React.PropTypes.func,
};