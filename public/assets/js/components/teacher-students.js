/**
 * L'École — Teacher Student Details Interactive Filtering
 */
(function () {
  'use strict';

  function initTeacherStudents() {
    const searchInput = document.getElementById('j-teacher-student-search');
    const tableBody = document.querySelector('#j-table-student tbody, #j-table-teacher-students tbody');
    if (!tableBody) return;

    const emptyRow = tableBody.querySelector('.c-empty-row');
    const rows = Array.from(tableBody.querySelectorAll('.j-person-row'));

    function filterRows() {
      const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
      let visibleCount = 0;

      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const matchesSearch = (!q || text.includes(q));

        if (matchesSearch) {
          row.style.display = '';
          visibleCount++;
        } else {
          row.style.display = 'none';
        }
      });

      if (emptyRow) {
        emptyRow.style.display = (visibleCount === 0) ? '' : 'none';
      }
    }

    if (searchInput) {
      searchInput.addEventListener('input', filterRows);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTeacherStudents);
  } else {
    initTeacherStudents();
  }
})();
