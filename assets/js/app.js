/**
 * TempWeb v2 - 公共App逻辑
 */

// 页面加载后更新导航
document.addEventListener('DOMContentLoaded', () => {
  updateNavAuth();
});

// 统一弹窗组件
function showAlert(title, message, type = 'warning') {
  const modalHtml = `
    <div class="modal fade" id="alertModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">${title}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>${message}</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">确定</button>
          </div>
        </div>
      </div>
    </div>
  `;
  // 移除旧弹窗
  const oldModal = document.getElementById('alertModal');
  if (oldModal) oldModal.remove();
  // 创建新弹窗
  document.body.insertAdjacentHTML('beforeend', modalHtml);
  const modal = new bootstrap.Modal(document.getElementById('alertModal'));
  modal.show();
  // 关闭后移除
  document.getElementById('alertModal').addEventListener('hidden.bs.modal', function() {
    this.remove();
  });
}

function showConfirm(title, message, confirmText = '确定', onConfirm) {
  const modalHtml = `
    <div class="modal fade" id="confirmModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">${title}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>${message}</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
            <button type="button" class="btn btn-danger" id="confirmBtn">${confirmText}</button>
          </div>
        </div>
      </div>
    </div>
  `;
  // 移除旧弹窗
  const oldModal = document.getElementById('confirmModal');
  if (oldModal) oldModal.remove();
  // 创建新弹窗
  document.body.insertAdjacentHTML('beforeend', modalHtml);
  const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
  modal.show();
  // 确认按钮事件
  document.getElementById('confirmBtn').addEventListener('click', function() {
    modal.hide();
    if (onConfirm) onConfirm();
  });
  // 关闭后移除
  document.getElementById('confirmModal').addEventListener('hidden.bs.modal', function() {
    this.remove();
  });
}

function showToast(message, type = 'success') {
  // 创建Toast容器
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;';
    document.body.appendChild(container);
  }
  const toastHtml = `
    <div class="toast show" role="alert" style="min-width:200px;">
      <div class="toast-header bg-${type} text-white">
        <strong class="me-auto">${type === 'success' ? '成功' : '提示'}</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
      </div>
      <div class="toast-body">${message}</div>
    </div>
  `;
  container.insertAdjacentHTML('beforeend', toastHtml);
  // 3秒后自动关闭
  setTimeout(() => {
    const toasts = container.querySelectorAll('.toast');
    if (toasts.length) toasts[0].remove();
  }, 3000);
}
