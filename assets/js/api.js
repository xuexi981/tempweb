/**
 * TempWeb v2 - API工具
 */

// 获取Token
function getToken() {
  return localStorage.getItem('tw_token') || '';
}

// 获取用户信息
function getUser() {
  const u = localStorage.getItem('tw_user');
  return u ? JSON.parse(u) : null;
}

// 保存登录信息
function saveLogin(token, user) {
  localStorage.setItem('tw_token', token);
  localStorage.setItem('tw_user', JSON.stringify(user));
}

// 清除登录
function clearLogin() {
  localStorage.removeItem('tw_token');
  localStorage.removeItem('tw_user');
}

// API GET
async function apiGet(url) {
  const token = getToken();
  const headers = { 'Content-Type': 'application/json' };
  if (token) headers['Authorization'] = 'Bearer ' + token;
  const res = await fetch(url, { headers });
  const text = await res.text();
  try {
    return JSON.parse(text);
  } catch (e) {
    console.error('API解析失败:', text.substring(0, 500));
    return { code: -1, message: '服务器错误，请稍后重试' };
  }
}

// API POST
async function apiPost(url, data) {
  const token = getToken();
  const headers = { 'Content-Type': 'application/json' };
  if (token) headers['Authorization'] = 'Bearer ' + token;
  const res = await fetch(url, { method: 'POST', headers, body: JSON.stringify(data) });
  const text = await res.text();
  try {
    return JSON.parse(text);
  } catch (e) {
    console.error('API解析失败:', text.substring(0, 500));
    return { code: -1, message: '服务器错误，请稍后重试' };
  }
}

// API Upload (multipart)
async function apiUpload(url, formData) {
  const token = getToken();
  const headers = {};
  if (token) headers['Authorization'] = 'Bearer ' + token;
  const res = await fetch(url, { method: 'POST', headers, body: formData });
  const text = await res.text();
  try {
    return JSON.parse(text);
  } catch (e) {
    console.error('API解析失败:', text.substring(0, 500));
    return { code: -1, message: '服务器错误，请稍后重试' };
  }
}

// 加载站点信息
let _siteInfo = null;
async function loadSiteInfo() {
  if (_siteInfo) return _siteInfo;
  try {
    const res = await apiGet('/api/admin.php?action=site_info');
    if (res.code === 0) { _siteInfo = res.data; return res.data; }
  } catch (e) {}
  return {};
}

// 更新导航栏用户状态
function updateNavAuth() {
  const el = document.getElementById('nav-auth');
  if (!el) return;
  const user = getUser();
  if (user) {
    el.innerHTML = `
      <span class="text-muted me-2"><i class="bi bi-person-circle me-1"></i>${user.username}</span>
      <a href="/projects.html" class="btn btn-sm btn-outline-primary">我的项目</a>
      ${user.role === 'admin' ? '<a href="/admin.html" class="btn btn-sm btn-outline-danger">管理</a>' : ''}
      <button class="btn btn-sm btn-link text-muted" onclick="doLogout()">退出</button>
    `;
  } else {
    el.innerHTML = `
      <a href="/login.html" class="btn btn-sm btn-outline-primary">登录</a>
      <a href="/register.html" class="btn btn-sm btn-primary">注册</a>
    `;
  }
}

// 退出登录
function doLogout() {
  clearLogin();
  window.location.href = '/index.html';
}

// 格式化字节
function formatBytes(bytes) {
  if (!bytes || bytes === 0) return '0 B';
  if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(1) + ' GB';
  if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
  if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return bytes + ' B';
}

// 复制到剪贴板
function copyText(text) {
  navigator.clipboard.writeText(text).then(() => {
    showToast('已复制到剪贴板');
  });
}

// Toast提示
function showToast(msg, type = 'success') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.style.cssText = 'position:fixed;top:80px;right:20px;z-index:9999;';
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  toast.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} py-2 px-3 mb-2 shadow-sm`;
  toast.style.cssText = 'min-width:200px;border-radius:8px;font-size:14px;animation:fadeIn 0.3s;';
  toast.textContent = msg;
  container.appendChild(toast);
  setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 2500);
}

// 美化确认弹窗（替代原生confirm）
function showConfirm(title, message, confirmText, onConfirm) {
  let modal = document.getElementById('global-confirm-modal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'global-confirm-modal';
    modal.className = 'modal fade';
    modal.innerHTML = `
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="gc-title"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="d-flex gap-3 align-items-start">
              <i class="bi bi-exclamation-triangle-fill text-warning fs-3" id="gc-icon"></i>
              <div>
                <p class="mb-0" id="gc-message"></p>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
            <button class="btn btn-danger" id="gc-confirm-btn"></button>
          </div>
        </div>
      </div>`;
    document.body.appendChild(modal);
  }
  document.getElementById('gc-title').textContent = title || '确认操作';
  document.getElementById('gc-message').textContent = message || '';
  document.getElementById('gc-confirm-btn').textContent = confirmText || '确定';
  const btn = document.getElementById('gc-confirm-btn');
  btn.onclick = function() {
    bootstrap.Modal.getInstance(modal).hide();
    if (onConfirm) onConfirm();
  };
  new bootstrap.Modal(modal).show();
}

// 美化提示弹窗（替代原生alert）
function showAlert(title, message, onOk) {
  let modal = document.getElementById('global-alert-modal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'global-alert-modal';
    modal.className = 'modal fade';
    modal.innerHTML = `
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="ga-title"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="ga-body"></div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
            <button class="btn btn-primary" id="ga-confirm">确定</button>
          </div>
        </div>
      </div>`;
    document.body.appendChild(modal);
  }
  document.getElementById('ga-title').textContent = title || '提示';
  // 判断是否为HTML内容（包含标签）
  const isHtml = /<[a-z][\s\S]*>/i.test(message);
  const body = document.getElementById('ga-body');
  if (isHtml) {
    body.innerHTML = message;
  } else {
    body.innerHTML = `<div class="d-flex gap-3 align-items-start"><i class="bi bi-info-circle-fill text-primary fs-3"></i><div><p class="mb-0">${message}</p></div></div>`;
  }
  const confirmBtn = document.getElementById('ga-confirm');
  if (onOk) {
    confirmBtn.style.display = '';
    confirmBtn.onclick = () => {
      bootstrap.Modal.getInstance(modal).hide();
      onOk();
    };
  } else {
    confirmBtn.style.display = 'none';
  }
  new bootstrap.Modal(modal).show();
}
