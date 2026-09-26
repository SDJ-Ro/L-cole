(function () {
  'use strict';
  const dialog = document.getElementById('deactivate-parent-dialog');
  if (!dialog) return;
  const confirm = document.getElementById('confirm-parent-deactivation');
  const cancel = document.getElementById('cancel-parent-deactivation');
  const error = document.getElementById('deactivate-parent-error');
  let selectedId = '';
  let saving = false;

  document.addEventListener('click', function (event) {
    const button = event.target.closest('.j-deactivate-parent');
    if (!button || saving) return;
    const parent = window.__PEOPLE_DATA__.parents.find(parent => parent.id === button.dataset.id);
    if (!parent) return;
    selectedId = parent.id;
    document.getElementById('deactivate-parent-name').textContent = parent.name;
    document.getElementById('deactivate-parent-code').textContent = parent.id;
    document.getElementById('deactivate-parent-description').textContent = parent.children.length > 0
      ? 'This can continue only if every linked student is deactivated. Their links, the parent profile, and historical records will remain.'
      : 'This will block login and further access from open sessions. The parent profile and historical records will remain.';
    confirm.hidden = false;
    confirm.disabled = false;
    cancel.textContent = 'Cancel';
    error.hidden = true;
    dialog.showModal();
  });
  cancel.addEventListener('click', function () { if (!saving) dialog.close(); });
  dialog.addEventListener('cancel', function (event) { if (saving) event.preventDefault(); });
  confirm.addEventListener('click', async function () {
    if (saving) return;
    saving = true;
    confirm.disabled = true;
    cancel.disabled = true;
    const data = new FormData();
    data.set('parentCode', selectedId);
    data.set('confirmed', 'yes');
    data.set('_csrf_token', document.querySelector('input[name="_csrf_token"]').value);
    try {
      const response = await fetch('/management/deactivateParent', {
        method:'POST', body:data,
        headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.error || 'Unable to deactivate account.');
      window.location.assign('/management/people?tab=parents');
    } catch (failure) {
      error.textContent = failure.message;
      error.hidden = false;
    } finally {
      saving = false;
      confirm.disabled = false;
      cancel.disabled = false;
    }
  });
})();
