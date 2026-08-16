'use strict';

document.addEventListener('DOMContentLoaded', function () {
  let dt_log_activity;

  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  const dt_table = document.querySelector('.datatables-log-activity');

  if (dt_table) {
    dt_log_activity = new DataTable(dt_table, {
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'log-activity-list',
        type: 'GET',
        data: function (d) {
          d.tgl_awal    = $('#filter-tgl-awal').val();
          d.tgl_akhir   = $('#filter-tgl-akhir').val();
          d.created_by  = $('#filter-created-by').val();
          d.description = $('#filter-description').val();
        },
        dataSrc: function (json) {
          if (typeof json.recordsTotal    !== 'number') json.recordsTotal    = 0;
          if (typeof json.recordsFiltered !== 'number') json.recordsFiltered = 0;
          json.data = Array.isArray(json.data) ? json.data : [];
          return json.data;
        }
      },
      columns: [
        { data: 'no',          className: 'text-center', width: '50px', orderable: false, searchable: false },
        { data: 'created_at',  className: 'text-center', orderable: true },
        { data: 'updated_at',  className: 'text-center', orderable: true },
        { data: 'created_by',  className: 'text-center', orderable: true },
        { data: 'description', orderable: true }
      ],
      order: [[1, 'desc']], // default sort: Waktu Dibuat descending
      layout: {
        topStart: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: [
            {
              pageLength: {
                menu: [10, 20, 50, 70, 100],
                text: '_MENU_'
              }
            }
          ]
        },
        topEnd: {
          features: ['search']
        },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: [
            {
              info: {
                text: 'Showing _START_ to _END_ of _TOTAL_ entries'
              }
            }
          ]
        },
        bottomEnd: 'paging'
      },
      displayLength: 10,
      language: {
        search: '',
        searchPlaceholder: 'Cari...',
        lengthMenu: '_MENU_',
        emptyTable:  'Tidak ada data tersedia',
        zeroRecords: 'Tidak ada data yang cocok',
        paginate: {
          next:     '<i class="icon-base ri ri-arrow-right-s-line scaleX-n1-rtl icon-22px"></i>',
          previous: '<i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-22px"></i>',
          first:    '<i class="icon-base ri ri-skip-back-mini-line scaleX-n1-rtl icon-22px"></i>',
          last:     '<i class="icon-base ri ri-skip-forward-mini-line scaleX-n1-rtl icon-22px"></i>'
        }
      },
      initComplete: function () {
        document.querySelectorAll('.dt-buttons .btn').forEach(btn => {
          btn.classList.remove('btn-secondary');
        });
      }
    });

    // Filter form control to default size
    setTimeout(() => {
      const elementsToModify = [
        { selector: '.dt-buttons .btn',        classToRemove: 'btn-secondary' },
        { selector: '.dt-length .form-select', classToAdd: 'ms-0' },
        { selector: '.dt-length',              classToAdd: 'mb-md-5 mb-0' },
        {
          selector: '.dt-layout-end',
          classToRemove: 'justify-content-between',
          classToAdd: 'd-flex gap-md-4 justify-content-md-between justify-content-center gap-md-2 flex-wrap mt-0'
        },
        { selector: '.dt-layout-start',             classToAdd: 'mt-md-0 mt-5' },
        { selector: '.dt-layout-start .dt-buttons', classToAdd: 'd-md-flex d-block gap-4 justify-content-center' },
        { selector: '.dt-layout-end .dt-buttons',   classToAdd: 'd-md-flex d-block gap-4 mb-md-0 mb-5 justify-content-center' },
        { selector: '.dt-layout-table',             classToRemove: 'row mt-2' },
        { selector: '.dt-layout-full',              classToRemove: 'col-md col-12' },
        { selector: '.dt-layout-full .table',       classToAdd: 'table-responsive' }
      ];

      elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
        document.querySelectorAll(selector).forEach(element => {
          if (classToRemove) classToRemove.split(' ').forEach(c => element.classList.remove(c));
          if (classToAdd)    classToAdd.split(' ').forEach(c => element.classList.add(c));
        });
      });
    }, 100);
  }

  // -------------------------------------------------------
  // Flatpickr
  // -------------------------------------------------------
  const flatpickrInputs = document.querySelectorAll('.dt-date');
  if (flatpickrInputs.length) {
    flatpickr(flatpickrInputs, {
      monthSelectorType: 'static',
      static:     true,
      dateFormat: 'd/m/Y'
    });
  }

  // -------------------------------------------------------
  // Filter Form — intercept submit, reload DataTable via AJAX
  // -------------------------------------------------------
  const filterForm = document.getElementById('filterForm');
  if (filterForm) {
    // Cegah default form submit (redirect/session)
    filterForm.addEventListener('submit', function (e) {
      e.preventDefault();
    });

    FormValidation.formValidation(filterForm, {
      fields: {
        tgl_awal: {
          validators: { notEmpty: { message: 'Silahkan Input Tanggal Awal' } }
        },
        tgl_akhir: {
          validators: { notEmpty: { message: 'Silahkan Input Tanggal Akhir' } }
        }
      },
      plugins: {
        trigger:      new FormValidation.plugins.Trigger(),
        bootstrap5:   new FormValidation.plugins.Bootstrap5({
          eleValidClass: '',
          rowSelector: function () { return '.form-control-validation'; }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        autoFocus:    new FormValidation.plugins.AutoFocus()
      }
    }).on('core.form.valid', function () {
      // Validasi lolos → reload DataTable dengan filter baru
      if (dt_log_activity) {
        dt_log_activity.ajax.reload();
      }
    });
  }
});
