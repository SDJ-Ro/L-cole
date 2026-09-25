(function () {
  'use strict';
  const dialog = document.getElementById('parent-picker');
  if (!dialog) return;
  const chooseButton = document.getElementById('choose-existing-parent');
  const search = document.getElementById('parent-picker-search');
  const results = document.getElementById('parent-picker-results');
  const parentInput = document.getElementById('existing-parent-id');
  const parents = (window.__PEOPLE_DATA__.parents || []).filter(function (parent) {
    return parent.status !== 'Deactivated';
  });

  function showDetails(parent) {
    document.getElementById('parent-picker-detail-name').textContent = parent.name;
    document.getElementById('parent-picker-detail-id').textContent = parent.id;
    document.getElementById('parent-picker-detail-email').textContent = 'Email: ' + parent.email;
    document.getElementById('parent-picker-detail-phone').textContent = 'Phone: ' + (parent.phone || 'Not recorded');
    document.getElementById('parent-picker-detail-status').textContent = 'Account: ' + parent.status;
    document.getElementById('parent-picker-details').hidden = false;
  }

  function selectParent(parent) {
    parentInput.value = parent.id;
    document.getElementById('selected-parent-name').textContent = parent.name;
    document.getElementById('selected-parent-code').textContent = parent.id;
    const parts = parent.email.split('@');
    const maskedEmail = parts.length === 2 ? parts[0].slice(0, 1) + '••••@' + parts[1] : 'Not recorded';
    document.getElementById('selected-parent-email').textContent = 'Email: ' + maskedEmail;
    document.getElementById('selected-parent-card').hidden = false;
    chooseButton.textContent = 'Change parent';
    dialog.close();
  }

  function renderResults() {
    const query = search.value.trim().toLowerCase();
    const matches = parents.filter(function (parent) {
      return [parent.name, parent.id, parent.email].join(' ').toLowerCase().includes(query);
    });
    results.replaceChildren();
    document.getElementById('parent-picker-details').hidden = true;
    document.getElementById('parent-picker-count').textContent = matches.length
      ? matches.length + (matches.length === 1 ? ' parent found' : ' parents found')
      : 'No matching parents. Try another search, or cancel and choose New Parent.';
    matches.forEach(function (parent) {
      const row = document.createElement('div');
      row.className = 'c-parent-picker-row';
      const person = document.createElement('div');
      person.className = 'c-parent-picker-person';
      const name = document.createElement('strong');
      name.textContent = parent.name;
      const code = document.createElement('span');
      code.textContent = parent.id;
      person.append(name, code);
      const actions = document.createElement('div');
      actions.className = 'c-parent-picker-actions';
      const details = document.createElement('button');
      details.type = 'button';
      details.className = 'c-row-action-btn';
      details.textContent = 'View details';
      details.addEventListener('click', function () { showDetails(parent); });
      const select = document.createElement('button');
      select.type = 'button';
      select.className = 'c-btn-accent c-tone-terracotta';
      select.textContent = 'Select';
      select.setAttribute('aria-label', 'Select ' + parent.name + ' ' + parent.id);
      select.addEventListener('click', function () { selectParent(parent); });
      actions.append(details, select);
      row.append(person, actions);
      results.append(row);
    });
  }

  chooseButton.addEventListener('click', function () {
    search.value = '';
    renderResults();
    dialog.showModal();
    search.focus();
  });
  search.addEventListener('input', renderResults);
  document.getElementById('parent-picker-cancel').addEventListener('click', function () { dialog.close(); });
  dialog.addEventListener('close', function () { chooseButton.focus(); });
  parentInput.form.addEventListener('reset', function () {
    parentInput.value = '';
    document.getElementById('selected-parent-card').hidden = true;
    chooseButton.textContent = 'Choose Existing Parent';
  });
})();
