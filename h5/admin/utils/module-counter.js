class ModuleCounter {
  constructor() {
    this.moduleCounter = {};
  }
  getInstance() {
    return this.moduleCounter;
  }
  getCounterByType(type) {
    return this.moduleCounter[type] || 0;
  }
  addByType(type) {
    if (!(this.moduleCounter[type] >= 0)) {
      this.moduleCounter[type] = 0
      return 0;
    }
    return ++this.moduleCounter[type];
  }
  removeByType(type) {
    if (!(this.moduleCounter[type] >= 0)) {
      this.moduleCounter[type] = 0
      return 0;
    }
    return --this.moduleCounter[type];
  }
}

export default ModuleCounter;
