// Sidebar toggle (mobile)
const sidebar        = document.getElementById('sidebar');
const sidebarToggle  = document.getElementById('sidebarToggle');
const sidebarOverlay = document.getElementById('sidebarOverlay');

function openSidebar()  { sidebar?.classList.add('open'); sidebarOverlay?.classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeSidebar() { sidebar?.classList.remove('open'); sidebarOverlay?.classList.remove('open'); document.body.style.overflow = ''; }

sidebarToggle?.addEventListener('click', () => sidebar?.classList.contains('open') ? closeSidebar() : openSidebar());
sidebarOverlay?.addEventListener('click', closeSidebar);

// Auto-dismiss flash
const flash = document.getElementById('flashMsg');
if (flash) setTimeout(() => flash.style.opacity = '0', 3500);

// Confirm delete (fallback for non-AJAX forms that still use data-confirm)
document.addEventListener('click', e => {
  const btn = e.target.closest('[data-confirm]');
  if (!btn) return;
  if (!confirm(btn.dataset.confirm || 'Are you sure?')) e.preventDefault();
});

// Shared delete utilities 

function adminSnackbar(msg, type) {
  var el = document.getElementById('adminSnackbar');
  if (!el) {
    el = document.createElement('div');
    el.id = 'adminSnackbar';
    document.body.appendChild(el);
  }
  el.className = 'admin-snackbar snackbar-' + (type || 'success');
  el.innerHTML = '<i class="fas ' + (type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle') + '"></i> ' + msg;
  el.classList.add('snackbar-show');
  clearTimeout(el._t);
  el._t = setTimeout(function () { el.classList.remove('snackbar-show'); }, 2800);
}

function adminDeleteRow(btn, apiUrl, onSuccess) {
  var row      = btn.closest('tr');
  var cell     = row.querySelector('td:last-child');
  var original = cell.innerHTML;
  var id       = btn.dataset.id;

  row.classList.add('row-deleting');
  cell.innerHTML =
    '<div style="display:flex;gap:6px;justify-content:flex-end;align-items:center">' +
      '<span style="font-size:11px;color:#777;white-space:nowrap">Sure?</span>' +
      '<button id="cfm-' + id + '" class="btn btn-danger btn-sm" style="gap:5px">' +
        '<i class="fas fa-trash"></i> Delete' +
      '</button>' +
      '<button id="cnl-' + id + '" class="btn btn-ghost btn-sm">Cancel</button>' +
    '</div>';

  document.getElementById('cnl-' + id).onclick = function () {
    cell.innerHTML = original;
    row.classList.remove('row-deleting');
  };

  document.getElementById('cfm-' + id).onclick = async function () {
    var cfm = this;
    cfm.disabled = true;
    cfm.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    try {
      var res  = await fetch(apiUrl, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ id: parseInt(id, 10) })
      });
      var data = await res.json();
      if (data.success) {
        row.style.transition = 'opacity .25s ease, transform .25s ease';
        row.style.opacity    = '0';
        row.style.transform  = 'translateX(16px)';
        setTimeout(function () { row.remove(); adminSnackbar(data.message || 'Deleted'); if (typeof onSuccess === 'function') onSuccess(); }, 260);
      } else {
        cell.innerHTML = original;
        row.classList.remove('row-deleting');
        adminSnackbar(data.error || 'Could not delete. Try again.', 'error');
      }
    } catch (_) {
      cell.innerHTML = original;
      row.classList.remove('row-deleting');
      adminSnackbar('Network error. Try again.', 'error');
    }
  };
}
