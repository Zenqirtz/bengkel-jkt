/**
 * Page Data Pemilik management
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  let borderColor, bodyBg, headingColor;

  borderColor = config.colors.borderColor;
  bodyBg = config.colors.bodyBg;
  headingColor = config.colors.headingColor;

  // Variable declaration for table
  const dt_basic_table = document.querySelector('.datatables-data-pemilik'),
    offCanvasForm = document.getElementById('offcanvasAdd'),
    statusObj = {
      '00001': { title: 'Badan', class: 'bg-label-info' },
      '00002': { title: 'Perorangan', class: 'bg-label-primary' }
    };

  // DECLARE dt_basic OUTSIDE to make it accessible
  let dt_basic = null;

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // Data Pemilik datatable
  if (dt_basic_table) {
    let tableTitle = document.createElement('h5');
    tableTitle.classList.add('card-title', 'mb-0', 'text-md-start', 'text-center');
    tableTitle.innerHTML = 'Data Pemilik';
    dt_basic = new DataTable(dt_basic_table, {
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'data-pemilik-list',
        type: 'GET',
        dataSrc: function (json) {
          if (typeof json.recordsTotal !== 'number') {
            json.recordsTotal = 0;
          }
          if (typeof json.recordsFiltered !== 'number') {
            json.recordsFiltered = 0;
          }
          json.data = Array.isArray(json.data) ? json.data : [];
          return json.data;
        },
        error: function (xhr, error, code) {
          // console.log('DataTables Error:', xhr.responseJSON);
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Gagal memuat data. Silakan refresh halaman.',
            customClass: {
              confirmButton: 'btn btn-danger'
            }
          });
        }
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'nama_pemilik' },
        { data: 'kode_jenis_pemilik' },
        { data: 'alamat1' },
        { data: 'kota' },
        { data: 'telepon' },
        { data: 'handphone' },
        { data: 'action' }
      ],
      columnDefs: [
        {
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function () {
            return '';
          }
        },
        {
          searchable: false,
          orderable: false,
          targets: 1,
          render: function (data, type, full, meta) {
            return `<span>${meta.row + meta.settings._iDisplayStart + 1}</span>`;
          }
        },
        {
          targets: 2,
          responsivePriority: 2
        },
        {
          targets: 3,
          responsivePriority: 3,
          render: function (data, type, full) {
            const status = full['kode_jenis_pemilik'];
            return (
              '<span class="badge rounded-pill ' + statusObj[status].class + '">' + statusObj[status].title + '</span>'
            );
          }
        },
        {
          targets: 4,
          responsivePriority: 4,
          render: function (data, type, full) {
            let alamat = [];
            if (full['alamat1']) alamat.push(full['alamat1']);
            if (full['alamat2']) alamat.push(full['alamat2']);
            return alamat.join(', ') || '-';
          }
        },
        {
          targets: 5,
          render: function (data) {
            return data || '-';
          }
        },
        {
          targets: 6,
          render: function (data) {
            return data || '-';
          }
        },
        {
          targets: 7,
          render: function (data) {
            return data || '-';
          }
        },
        {
          targets: -1,
          searchable: false,
          orderable: false,
          render: function (data, type, full) {
            return (
              '<div class="d-flex align-items-center gap-4">' +
              '<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill edit-record" data-id="' +
              full['id'] +
              '" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAdd" title="Ubah"><i class="icon-base ri ri-edit-box-line icon-22px"></i></button>' +
              '<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill delete-record" data-id="' +
              full['id'] +
              '" title="Hapus"><i class="icon-base ri ri-delete-bin-7-line icon-22px"></i></button>' +
              '</div>'
            );
          }
        }
      ],
      order: [[2, 'asc']],
      layout: {
        top2Start: {
          rowClass: 'row card-header mx-0 px-2',
          features: [tableTitle]
        },
        top2End: {
          features: [
            {
              buttons: [
                {
                  text: '<i class="icon-base ri ri-add-line icon-sm me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Tambah</span>',
                  className: 'add-new btn btn-primary',
                  attr: { 'data-bs-toggle': 'offcanvas', 'data-bs-target': '#offcanvasAdd' }
                }
              ]
            }
          ]
        },
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
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              return 'Details of ' + row.data()['nama_pemilik'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = columns
              .map(function (col) {
                return col.title !== '' ? `<tr><td>${col.title}:</td><td>${col.data}</td></tr>` : '';
              })
              .join('');
            if (data) {
              const div = document.createElement('div');
              div.classList.add('table-responsive');
              const table = document.createElement('table');
              table.classList.add('table');
              const tbody = document.createElement('tbody');
              tbody.innerHTML = data;
              table.appendChild(tbody);
              div.appendChild(table);
              return div;
            }
            return false;
          }
        }
      },
      initComplete: function () {
        $('.card-header').after('<hr class="my-0">');
        document.querySelectorAll('.dt-buttons .btn').forEach(btn => btn.classList.remove('btn-secondary'));
      }
    });

    // Delete Record
    document.addEventListener('click', function (e) {
      if (e.target.closest('.delete-record')) {
        const deleteBtn = e.target.closest('.delete-record');
        const user_id = deleteBtn.dataset.id;
        const dtrModal = document.querySelector('.dtr-bs-modal.show');
        if (dtrModal) bootstrap.Modal.getInstance(dtrModal).hide();

        Swal.fire({
          title: 'Konfirmasi?',
          text: 'Anda yakin akan menghapus data ini!',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, hapus!',
          cancelButtonText: 'Batal',
          customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
          },
          buttonsStyling: false
        }).then(function (result) {
          if (result.value) {
            fetch(`${baseUrl}data-pemilik-list/${user_id}`, {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
              }
            })
              .then(response => {
                if (response.ok) {
                  dt_basic.draw();
                  Swal.fire({
                    icon: 'success',
                    title: 'Hapus!',
                    text: 'Data berhasil dihapus!',
                    customClass: { confirmButton: 'btn btn-success' }
                  });
                } else {
                  throw new Error('Gagal Hapus Data');
                }
              })
              .catch(error => {
                Swal.fire({
                  icon: 'error',
                  title: 'Error!',
                  text: `${error}`,
                  customClass: { confirmButton: 'btn btn-success' }
                });
              });
          } else if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire({
              title: 'Batal',
              text: 'Data batal dihapus!',
              icon: 'error',
              customClass: { confirmButton: 'btn btn-success' }
            });
          }
        });
      }
    });

    // Edit record
    document.addEventListener('click', function (e) {
      if (e.target.closest('.edit-record')) {
        const editBtn = e.target.closest('.edit-record');
        const user_id = editBtn.dataset.id;
        const dtrModal = document.querySelector('.dtr-bs-modal.show');
        if (dtrModal) bootstrap.Modal.getInstance(dtrModal).hide();

        document.getElementById('offcanvasAddLabel').innerHTML = 'Edit Data';

        fetch(`${baseUrl}data-pemilik-list/${user_id}/edit`)
          .then(response => response.json())
          .then(data => {
            // ⚠️ TAMBAHKAN LOG UNTUK DEBUG
            // console.log('=== DATA FROM EDIT API ===');
            // console.log('Full data:', data);
            // console.log('alamat:', data.alamat);
            // console.log('alamat1:', data.alamat1);

            document.getElementById('user_id').value = data.id;

            // Set old file values
            document.getElementById('old_file_identitas').value = data.file_identitas || '';
            document.getElementById('old_file_npwp').value = data.file_npwp || '';

            document.getElementById('add-nama-pemilik').value = data.nama_pemilik || '';
            document.getElementById('add-no-identitas').value = data.no_identitas || '';

            // ⚠️ GUNAKAN data.alamat (sudah diconvert dari controller)
            document.getElementById('add-alamat').value = data.alamat || '';
            // console.log('Set alamat to textarea:', data.alamat);

            document.getElementById('add-kota').value = data.kota || '';
            document.getElementById('add-kode-pos').value = data.kode_pos || '';
            document.getElementById('add-po-box').value = data.po_box || '';
            document.getElementById('add-telepon').value = data.telepon || '';
            document.getElementById('add-fax').value = data.fax || '';
            document.getElementById('add-handphone').value = data.handphone || '';
            document.getElementById('add-email').value = data.email || '';
            document.getElementById('add-npwp').value = data.npwp || '';
            document.getElementById('add-tgl-lahir').value = data.tgl_lahir || '';

            // Set select2 values
            setSelectValue('#add-kode-jenis', data.kode_jenis_pemilik);
            setSelectValue('#add-kode-agama', data.kode_agama);

            // Reset file inputs
            document.getElementById('add-file-identitas').value = '';
            document.getElementById('add-file-npwp').value = '';
          })
          .catch(error => {
            // console.error('Error fetching edit data:', error);
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: 'Gagal memuat data',
              customClass: { confirmButton: 'btn btn-danger' }
            });
          });
      }
    });

    // Add new button
    const addNewBtn = document.querySelector('.add-new');
    if (addNewBtn) {
      addNewBtn.addEventListener('click', function () {
        document.getElementById('user_id').value = '';
        document.getElementById('old_file_identitas').value = '';
        document.getElementById('old_file_npwp').value = '';
        document.getElementById('offcanvasAddLabel').innerHTML = 'Data Pemilik';
        clearFormData();
        document.getElementById('add-nama-pemilik')?.focus();
      });
    }

    // Filter form control
    setTimeout(() => {
      const modifications = [
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
      modifications.forEach(({ selector, classToRemove, classToAdd }) => {
        document.querySelectorAll(selector).forEach(el => {
          if (classToRemove) classToRemove.split(' ').forEach(c => el.classList.remove(c));
          if (classToAdd) classToAdd.split(' ').forEach(c => el.classList.add(c));
        });
      });
    }, 100);
  }

  // Form validation - INI YANG PENTING! GUNAKAN FormData LANGSUNG
  const addNewDataForm = document.getElementById('addNewDataForm');
  if (addNewDataForm) {
    const fv = FormValidation.formValidation(addNewDataForm, {
      fields: {
        email: {
          validators: {
            notEmpty: { message: 'Silahkan Input Email' },
            emailAddress: { message: 'Format email tidak sesuai' }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: '',
          rowSelector: () => '.form-control-validation'
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    }).on('core.form.valid', function () {
      // GUNAKAN FormData LANGSUNG - JANGAN CONVERT KE URLSearchParams
      const form = addNewDataForm;
      const fd = new FormData(form);

      const id = document.getElementById('user_id').value;
      const method = 'POST';
      const url = id ? `${baseUrl}data-pemilik-list/${id}` : `${baseUrl}data-pemilik-list`;

      // Jika update, tambahkan _method
      if (id) fd.append('_method', 'PUT');

      fetch(url, {
        method,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          // JANGAN SET Content-Type! Biar browser set otomatis dengan boundary
        },
        body: fd // Kirim FormData langsung
      })
        .then(async response => {
          const text = await response.text();
          let json = {};
          try {
            json = JSON.parse(text);
          } catch {
            json = { success: false, message: text || 'Gagal simpan data' };
          }

          if (!response.ok) {
            throw new Error(json.message || 'Gagal simpan data');
          }

          // Hide offcanvas
          const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasForm);
          offcanvasInstance?.hide();

          // Refresh DataTable
          if (dt_basic) dt_basic.draw();

          Swal.fire({
            icon: 'success',
            title: 'Informasi!',
            text: json.message || 'Berhasil simpan data.',
            customClass: { confirmButton: 'btn btn-success' }
          });
        })
        .catch(error => {
          bootstrap.Offcanvas.getInstance(offCanvasForm)?.hide();

          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: String(error.message || error),
            customClass: { confirmButton: 'btn btn-success' }
          });
        });
    });

    offCanvasForm.addEventListener('hidden.bs.offcanvas', () => fv.resetForm(true));
  }

  // Phone mask
  document.querySelectorAll('.phone-mask').forEach(phoneMask => {
    phoneMask.addEventListener('input', function (e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length > 20) {
        value = value.slice(0, 20);
      }
      e.target.value = value;
    });
  });

  // Flatpickr initialization
  const flatpickrDate = document.querySelectorAll('.dt-date');
  if (flatpickrDate) {
    flatpickrDate.flatpickr({
      monthSelectorType: 'static',
      static: true,
      dateFormat: 'd/m/Y',
      allowInput: true
    });
  }

  // Select2 initialization
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
});

function clearFormData() {
  document.getElementById('addNewDataForm')?.reset();
  clearSelect('#add-kode-jenis');
  clearSelect('#add-kode-agama');
}

function setSelectValue(selector, value, textIfMissing) {
  const $el = $(selector);
  if (!$el.length) return;

  const val = Array.isArray(value) ? value.map(v => String(v)) : (value ?? '');
  const isSelect2 = $el.hasClass('select2-hidden-accessible');

  const ensureOption = (v, t) => {
    if (!$el.find(`option[value="${v}"]`).length) {
      $el.append(new Option(t ?? v, v, false, false));
    }
  };

  if (Array.isArray(val)) {
    val.forEach(v => ensureOption(v, (textIfMissing && textIfMissing[v]) || v));
    $el.val(val);
  } else {
    if (val === '' || val === null) {
      $el.val(null);
    } else {
      ensureOption(String(val), textIfMissing);
      $el.val(String(val));
    }
  }

  if (isSelect2) $el.trigger('change');
}

function clearSelect(selector, { keepOptions = true } = {}) {
  const $el = $(selector);
  if (!$el.length) return;

  if (!keepOptions) {
    $el.empty().append(new Option('', '', true, false));
  }
  $el.val(null);
  if ($el.hasClass('select2-hidden-accessible')) {
    $el.trigger('change');
  }
}
