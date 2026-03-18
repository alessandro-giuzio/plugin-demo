(function ($) {
  'use strict';

  $(function () {
    var debounceTimer;

    function fetchEmployees() {
      $.post(
        agEmployeeAjax.url,
        {
          action: 'ag_employee_filter',
          nonce: agEmployeeAjax.nonce,
          search: $('#ag-employee-search').val(),
          department: $('#ag-employee-department').val() || '',
          company: $('#ag-employee-company').val() || '',
          website: $('#ag-employee-website').val() || '',
        },
        function (response) {
          if (response.success) {
            $('#ag-employee-list').html(response.data.html);
          }
        },
      );
    }

    $('#ag-employee-search').on('keyup', function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(fetchEmployees, 300);
    });

    $('#ag-employee-department').on('change', fetchEmployees);
    $('#ag-employee-company').on('change', fetchEmployees);
    $('#ag-employee-website').on('keyup', function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(fetchEmployees, 300);
    });
  });
})(jQuery);
