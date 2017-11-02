let loading = ({ loadingClass } = { loadingClass: ''}) => {
  return `<div class="cd-loading ${loadingClass}">
            <div class="loading-content">
              <div></div>
              <div></div>
              <div></div>
            </div>
          </div>`;
}

export default loading;