import { deleteTask } from './help';
import sortable from 'common/sortable';

deleteTask();

sortable({
  element : '#sortable-list'
});

