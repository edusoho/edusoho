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
    const counter = this.moduleCounter[type];
    if (isNaN(counter) || counter < 0) {
      this.moduleCounter[type] = 1;
      return 0;
    }
    this.moduleCounter[type] = counter + 1;
    return this.moduleCounter[type];
  }
  removeByType(type) {
    const counter = this.moduleCounter[type];
    if (isNaN(counter) || counter < 0) {
      this.moduleCounter[type] = 0;
      return 0;
    }
    this.moduleCounter[type] = counter - 1;
    return this.moduleCounter[type];
  }
}

export default ModuleCounter;
