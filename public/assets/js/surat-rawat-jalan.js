/**
 * Surat Rawat Jalan - Page Data management
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd, isEdit, isDelete;

  const dt_basic_table = document.querySelector('.datatables-spk'),
    statusObj = {
      '09': { class: 'text-bg-danger' },
      10: { class: 'text-bg-danger' },
      11: { class: 'text-bg-danger' }
    };

  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  if (dt_basic_table) {
    isAdd = dt_basic_table.dataset.add;
    isEdit = dt_basic_table.dataset.edit;
    isDelete = dt_basic_table.dataset.delete;

    const dt_basic = new DataTable(dt_basic_table, {
      searching: false,
      ordering: true,
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'surat-rawat-jalan-list',
        data: function (d) {
          d.kode_spk = $('#filter-nomor-spk').val();
          d.no_polisi = $('#filter-no-polisi').val();
          d.tgl_masuk_awal = $('#filter-tgl-spk-awal').val();
          d.tgl_masuk_akhir = $('#filter-tgl-spk-akhir').val();
          d.nama_pelanggan = $('#filter-nama-pelanggan').val();
          d.nama_pemilik = $('#filter-nama-pemilik').val();
          d.no_polis = $('#filter-no-polis').val();
          d.kode_claim = $('#filter-no-klaim').val();
          d.status_spk = $('#filter-status-spk').val();
          d.status = $('#filter-status').val();
        },
        dataSrc: function (json) {
          if (typeof json.recordsTotal !== 'number') json.recordsTotal = 0;
          if (typeof json.recordsFiltered !== 'number') json.recordsFiltered = 0;
          json.data = Array.isArray(json.data) ? json.data : [];
          return json.data;
        }
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'tgl_masuk' },
        { data: 'kode_spk' },
        { data: 'keterangan' },
        { data: 'no_polisi' },
        { data: 'merek_tipe' },
        { data: 'pemilik' },
        { data: 'nama_pelanggan' },
        { data: 'tgl_rawat_jalan1' },
        { data: 'tgl_rawat_jalan2' },
        { data: 'status_spk' },
        { data: 'no_polis' },
        { data: 'kode_claim' }
      ],
      columnDefs: [
        {
          targets: 0,
          orderable: false,
          searchable: false,
          render: function (data, type, full, meta) {
            return `<input type="checkbox" class="dt-checkboxes form-check-input" value="${data}">`;
          }
        },
        {
          targets: 1,
          searchable: false,
          orderable: false,
          visible: false,
          render: function (data, type, full, meta) {
            return `<span>${full.fake_id}</span>`;
          }
        },
        {
          targets: 4,
          render: function (data, type, full, meta) {
            const status = full['kode_status_spk'];
            const badgeClass = statusObj[status] ? statusObj[status].class : 'text-bg-success';
            return `<span class="badge rounded-pill ${badgeClass}">${data}</span>`;
          }
        }
      ],
      scrollX: true,
      order: [[1, 'desc']],
      layout: {
        topStart: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: [{ pageLength: { menu: [10, 20, 50, 70, 100], text: '_MENU_' } }]
        },
        topEnd: {
          features: [{ search: { placeholder: 'Cari', text: '_INPUT_' } }]
        },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: [{ info: { text: 'Showing _START_ to _END_ of _TOTAL_ entries' } }]
        },
        bottomEnd: 'paging'
      },
      displayLength: 10,
      language: {
        paginate: {
          next: '<i class="icon-base ri ri-arrow-right-s-line scaleX-n1-rtl icon-22px"></i>',
          previous: '<i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-22px"></i>',
          first: '<i class="icon-base ri ri-skip-back-mini-line scaleX-n1-rtl icon-22px"></i>',
          last: '<i class="icon-base ri ri-skip-forward-mini-line scaleX-n1-rtl icon-22px"></i>'
        }
      },
      initComplete: function () {
        $('.card-header').after('<hr class="my-0">');
        document.querySelectorAll('.dt-buttons .btn').forEach(btn => btn.classList.remove('btn-secondary'));
      }
    });

    // Batasi hanya 1 checkbox yang boleh dipilih
    document.addEventListener('click', function (e) {
      const chk = e.target.closest('.datatables-spk .dt-checkboxes');
      if (chk) {
        if (chk.checked) {
          $('.datatables-spk .dt-checkboxes').not(chk).prop('checked', false);
        }
      }
    });

    // ── Tombol Cetak ──────────────────────────────────────────────────
    const btnCetak = document.querySelector('.cetak-surat-rawat-jalan');
    if (btnCetak) {
      btnCetak.addEventListener('click', function () {
        const selectedCheckbox = document.querySelector('.datatables-spk .dt-checkboxes:checked');

        if (!selectedCheckbox) {
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silahkan pilih (checklist) data SPK terlebih dahulu!',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        const printUrl = `${baseUrl}administrasi/cetak-surat-rawat-jalan?id[]=${selectedCheckbox.value}`;
        window.open(printUrl, '_blank');
      });
    }

    // ── Filter form ───────────────────────────────────────────────────
    const formFilter = document.getElementById('formFilterSpk');
    if (formFilter) {
      formFilter.addEventListener('submit', function (e) {
        e.preventDefault();
        dt_basic.draw();
        const modalEl = document.getElementById('filterRoleModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
      });
    }

    // ── Styling tweaks ────────────────────────────────────────────────
    setTimeout(() => {
      const elementsToModify = [
        { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
        { selector: '.dt-length .form-select', classToAdd: 'ms-0' },
        { selector: '.dt-length', classToAdd: 'mb-md-5 mb-0' },
        {
          selector: '.dt-layout-end',
          classToRemove: 'justify-content-between',
          classToAdd: 'd-flex gap-md-4 justify-content-md-between justify-content-center gap-md-2 flex-wrap mt-0'
        },
        { selector: '.dt-layout-start', classToAdd: 'mt-md-0 mt-5' },
        { selector: '.dt-layout-start .dt-buttons', classToAdd: 'd-md-flex d-block gap-4 justify-content-center' },
        {
          selector: '.dt-layout-end .dt-buttons',
          classToAdd: 'd-md-flex d-block gap-4 mb-md-0 mb-5 justify-content-center'
        },
        { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
        { selector: '.dt-layout-full', classToRemove: 'col-md col-12' },
        { selector: '.dt-layout-full .table', classToAdd: 'table-responsive' }
      ];
      elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
        document.querySelectorAll(selector).forEach(el => {
          if (classToRemove) classToRemove.split(' ').forEach(c => el.classList.remove(c));
          if (classToAdd) classToAdd.split(' ').forEach(c => el.classList.add(c));
        });
      });
    }, 100);
  }

  // ── Select2 ───────────────────────────────────────────────────────
  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      var ph =
        $this.data('placeholder') ||
        $this.attr('placeholder') ||
        $this.find('option[value=""]').first().text() ||
        'Please select';
      if (typeof select2Focus === 'function') select2Focus($this);
      $this.wrap('<div class="position-relative"></div>').select2({
        placeholder: ph,
        allowClear: true,
        width: '100%',
        dropdownParent: $this.parent()
      });
    });
  }

  // ── Flatpickr ─────────────────────────────────────────────────────
  const flatpickrDate = document.querySelectorAll('.dt-date');
  if (flatpickrDate.length) {
    flatpickrDate.forEach(el => {
      el._flatpickr || flatpickr(el, { monthSelectorType: 'static', static: true, dateFormat: 'd/m/Y' });
    });
  }
});
