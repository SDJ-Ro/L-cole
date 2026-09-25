(function () {
  'use strict';
  const data = window.__GUARDIAN_CHANGE__;
  const dialog = document.getElementById('guardian-change-dialog');
  if (!data || !dialog) return;
  const search = document.getElementById('guardian-search');
  const results = document.getElementById('guardian-results');
  const count = document.getElementById('guardian-result-count');
  const confirmation = document.getElementById('guardian-confirmation');
  const footer = document.getElementById('guardian-picker-footer');
  const error = document.getElementById('guardian-change-error');
  let selected = null;
  let saving = false;

  function showResults(show) {
    results.hidden = !show; search.hidden = !show; count.hidden = !show; footer.hidden = !show; confirmation.hidden = show;
  }
  function render() {
    const query = search.value.trim().toLowerCase();
    const matches = data.parents.filter(function (parent) {
      return parent.id !== data.currentParentId && [parent.name, parent.id, parent.email].join(' ').toLowerCase().includes(query);
    });
    results.replaceChildren();
    count.textContent = matches.length ? matches.length + (matches.length === 1 ? ' parent found' : ' parents found') : 'No replacement parent found.';
    matches.forEach(function (parent) {
      const row = document.createElement('div'); row.className = 'c-parent-picker-row';
      const person = document.createElement('div'); person.className = 'c-parent-picker-person';
      const name = document.createElement('strong'); name.textContent = parent.name;
      const code = document.createElement('span'); code.textContent = parent.id + ' · ' + parent.status;
      person.append(name, code);
      const choose = document.createElement('button'); choose.type = 'button'; choose.className = 'c-btn-accent c-tone-terracotta'; choose.textContent = 'Select';
      choose.addEventListener('click', function () {
        selected = parent;
        document.getElementById('guardian-selected-name').textContent = parent.name;
        document.getElementById('guardian-selected-code').textContent = parent.id;
        showResults(false);
      });
      row.append(person, choose); results.append(row);
    });
  }
  document.getElementById('open-guardian-change').addEventListener('click', function () { search.value = ''; render(); showResults(true); dialog.showModal(); search.focus(); });
  search.addEventListener('input', render);
  document.getElementById('cancel-guardian-change').addEventListener('click', function () { dialog.close(); });
  document.getElementById('back-to-guardian-results').addEventListener('click', function () { selected = null; showResults(true); error.hidden = true; search.focus(); });
  document.getElementById('confirm-guardian-change').addEventListener('click', async function () {
    if (!selected || saving) return;
    saving = true; const confirmButton = this; confirmButton.disabled = true;
    const body = new FormData();
    body.set('_csrf_token', document.querySelector('meta[name="csrf-token"]').content);
    body.set('studentIndex', data.studentIndex); body.set('parentCode', selected.id); body.set('confirmed', 'yes');
    try {
      const response = await fetch('/management/changeStudentGuardian', {method:'POST',body:body,headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.error || 'Unable to change the guardian.');
      window.location.reload();
    } catch (failure) { error.textContent = failure.message; error.hidden = false; }
    finally { saving = false; confirmButton.disabled = false; }
  });
})();
