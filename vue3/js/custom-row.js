export function createCustomRow(table, onSorted, options = {}) {
  const { draggable = true } = options

  return function customRow(record) {
    return {
      props: { draggable },
      style: { cursor: draggable ? 'move' : 'default' },

      onMouseenter(event) {
        event.target.draggable = draggable;
      },

      onDragstart(event) {
        if (!draggable) return
        event.stopPropagation()
        table.sourceId = record.id
      },

      onDragover(event) {
        if (!draggable) return
        event.preventDefault()
      },

      onDragenter(event) {
        if (!draggable) return
        event.preventDefault()
        const old = document.querySelector('.ant-table-tbody > tr.target')
        if (old) old.classList.remove('target')
        event.currentTarget.classList.add('target')
      },

      onDrop(event) {
        if (!draggable) return
        event.stopPropagation()
        table.targetId = record.id

        const old = document.querySelector('.ant-table-tbody > tr.target')
        if (old) old.classList.remove('target')

        const list = table.list.slice()
        const sourceIndex = list.findIndex(item => item.id === table.sourceId)
        const targetIndex = list.findIndex(item => item.id === table.targetId)
        if (sourceIndex === -1 || targetIndex === -1) return

        const [movedItem] = list.splice(sourceIndex, 1)
        list.splice(targetIndex, 0, movedItem)
        table.list = list

        if (typeof onSorted === 'function') {
          onSorted(list, { movedItem, sourceIndex, targetIndex })
        }
      },
    }
  }
}