/**
 * Page Data management
 */

 'use strict';

 // Datatable (js)
 document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd, isEdit, isDelete;

   // Variable declaration for table
   const dt_basic_table = document.querySelector('.datatables-spk'),
     addRoleModal = document.getElementById('addRoleModal'),
     statusObj = {
      '09': { class: 'text-bg-danger' },
      '10': { class: 'text-bg-danger' },
      '11': { class: 'text-bg-danger' },
    };

   // ajax setup
   $.ajaxSetup({
     headers: {
       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
   });

  // COA datatable
  if (dt_basic_table) {
    isAdd = dt_basic_table.dataset.add;
    isEdit = dt_basic_table.dataset.edit;
    isDelete = dt_basic_table.dataset.delete;

     let tableTitle = document.createElement('h5');
     tableTitle.classList.add('card-title', 'mb-0', 'text-md-start', 'text-center');
     tableTitle.innerHTML = dt_basic_table.dataset.title;
     const dt_basic = new DataTable(dt_basic_table, {
       searching: false,  // Opsi ini akan menghilangkan input cari
       ordering: true,    // Opsi lain tetap bisa jalan
       processing: true,
       serverSide: true,
       ajax: {
         url: baseUrl + 'kewajiban-list',
         data: function (d) {
            // Ambil data dari input form modal dan masukkan ke parameter request
            d.kode_spk = $('#filter-nomor-spk').val();
            d.no_polisi = $('#filter-no-polisi').val();
            d.tgl_masuk_awal = $('#filter-tgl-spk-awal').val();
            d.tgl_masuk_akhir = $('#filter-tgl-spk-akhir').val();
            d.nama_pelanggan = $('#filter-nama-pelanggan').val();
            d.nama_pemilik = $('#filter-nama-pemilik').val();
            d.no_polis = $('#filter-no-polis').val();
            d.kode_claim = $('#filter-no-klaim').val();
            d.status_spk = $('#filter-status-spk').val(); // Pastikan value select2 terambil
            d.status = $('#filter-status').val();
         },
         dataSrc: function (json) {
           // Ensure recordsTotal and recordsFiltered are numeric and not undefined/null
           if (typeof json.recordsTotal !== 'number') {
             json.recordsTotal = 0;
           }
           if (typeof json.recordsFiltered !== 'number') {
             json.recordsFiltered = 0;
           }

           // Fallback for empty data to avoid pagination NaN issue
           json.data = Array.isArray(json.data) ? json.data : [];

           return json.data;
         }
       },
       columns: [
         // columns according to JSON
          { data: 'id' },
          { data: 'id' },
          { data: 'tgl_masuk' },
          { data: 'kode_spk' },
          { data: 'keterangan' },
          { data: 'no_polisi' },
          { data: 'nama_tipe' },
          { data: 'pemilik' },
          { data: 'nama_pelanggan' },
          { data: 'tgl_batal' },
          { data: 'tgl_turun_lapangan' },
          { data: 'tgl_finishing1' },
          { data: 'tgl_keluar' },
          { data: 'status_spk' },
          { data: 'no_polis' },
          { data: 'kode_claim' },
          { data: 'action' }
       ],
       columnDefs: [
        //  {
        //    // For Responsive
        //    className: 'control',
        //    searchable: false,
        //    orderable: false,
        //    responsivePriority: 2,
        //    targets: 0,
        //    render: function (data, type, full, meta) {
        //      return '';
        //    }
        //  },
         {
          // For Checkboxes
          targets: 0,
          orderable: false,
          searchable: false,
          // responsivePriority: 2,
          checkboxes: true,
          render: function (data, type, full, meta) {
            return `<input type="checkbox" class="dt-checkboxes form-check-input" value="${data}">`;
          },
          checkboxes: {
            selectAllRender: '<input type="checkbox" class="form-check-input">'
          }
         },
         {
           searchable: false,
           orderable: false,
           visible: false,
           targets: 1,
           render: function (data, type, full, meta) {
             return `<span>${full.fake_id}</span>`;
           }
         },
         {
          targets: 4,
          // responsivePriority: 6,
          render: function (data, type, full, meta) {
            const status = full['kode_status_spk'];
            const badgeClass = statusObj[status] ? statusObj[status].class : 'text-bg-success';

            return (
              '<span class="badge rounded-pill ' +
              badgeClass +
              '" text-capitalized>' +
              data +
              '</span>'
            );

          }
         },
        //  {
        //    targets: 2,
        //    responsivePriority: 2
        //  },
        //  {
        //    targets: 3,
        //    responsivePriority: 3
        //  },
         {
           // Actions
           //  title: 'Actions',
           targets: -1,
           searchable: false,
           orderable: false,
           visible: false,
           render: function (data, type, full, meta) {
             return (
               '<div class="d-flex align-items-center gap-4">' +
               `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill edit-record" data-id="${full['id']}" data-bs-toggle="modal" data-bs-target="#addRoleModal" title="Ubah"><i class="icon-base ri ri-edit-box-line icon-22px"></i></button>` +
               `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill delete-record" data-id="${full['id']}" title="Hapus"><i class="icon-base ri ri-delete-bin-7-line icon-22px"></i></button>` +
               '</div>'
             );
           }
         }
       ],
       // scrollY: '300px',
       scrollX: true,
       order: [[1, 'desc']],
       layout: {
        //  top2Start: {
        //    rowClass: 'row card-header mx-0 px-2',
        //    features: [tableTitle]
        //  },
        //  top2End: {
        //    features: [
        //      {
        //        buttons: [
        //          {
        //            text: '<i class="icon-base ri ri-add-line icon-sm me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Tambah</span>',
        //            className: 'add-new btn btn-primary',
        //            attr: {
        //              'data-bs-toggle': 'modal',
        //              'data-bs-target': '#addRoleModal'
        //            }
        //          }
        //        ]
        //      }
        //    ]
        //  },
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
           features: [
             {
               search: {
                 placeholder: 'Cari',
                 text: '_INPUT_'
               }
             }
           ]
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
         paginate: {
           next: '<i class="icon-base ri ri-arrow-right-s-line scaleX-n1-rtl icon-22px"></i>',
           previous: '<i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-22px"></i>',
           first: '<i class="icon-base ri ri-skip-back-mini-line scaleX-n1-rtl icon-22px"></i>',
           last: '<i class="icon-base ri ri-skip-forward-mini-line scaleX-n1-rtl icon-22px"></i>'
         }
       },
       // For responsive popup
      //  responsive: {
      //    details: {
      //      display: DataTable.Responsive.display.modal({
      //        header: function (row) {
      //          const data = row.data();
      //          return 'Details of ' + data['kode_spk'];
      //        }
      //      }),
      //      type: 'column',
      //      renderer: function (api, rowIdx, columns) {
      //        const data = columns
      //          .map(function (col) {
      //            return col.title !== '' // Do not show row in modal popup if title is blank (for check box)
      //              ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
      //                  <td>${col.title}:</td>
      //                  <td>${col.data}</td>
      //                </tr>`
      //              : '';
      //          })
      //          .join('');

      //        if (data) {
      //          const div = document.createElement('div');
      //          div.classList.add('table-responsive');
      //          const table = document.createElement('table');
      //          div.appendChild(table);
      //          table.classList.add('table');
      //          const tbody = document.createElement('tbody');
      //          tbody.innerHTML = data;
      //          table.appendChild(tbody);
      //          return div;
      //        }
      //        return false;
      //      }
      //    }
      //  },
       initComplete: function () {
         $('.card-header').after('<hr class="my-0">');
         // Remove btn-secondary from export buttons
         document.querySelectorAll('.dt-buttons .btn').forEach(btn => {
           btn.classList.remove('btn-secondary');
         });
       }
     });

     // Batasi hanya 1 checkbox yang boleh dipilih
    document.addEventListener('click', function (e) {
      const chk = e.target.closest('.dt-checkboxes');
      if (chk) {
        if (chk.checked) {
            $('.dt-checkboxes').not(chk).prop('checked', false);
        }
      }
    });

    const btnEditSelected = document.querySelector('.edit-selected-spk');
    if (btnEditSelected) {
      btnEditSelected.addEventListener('click', function () {
        // Cek Izin Edit
        if (!isEdit) {
          Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Anda tidak memiliki izin untuk mengubah data.',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        // Cari checkbox yang tercentang di dalam tabel
        const selectedCheckbox = document.querySelector('.datatables-spk .dt-checkboxes:checked');

        if (!selectedCheckbox) {
          // Jika tidak ada yang dipilih
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silahkan pilih (checklist) data SPK pada tabel terlebih dahulu!',
            customClass: { confirmButton: 'btn btn-primary' }
          });
        } else {
          // Jika ada yang dipilih
          const user_id = selectedCheckbox.value;

          // get data
          fetch(`${baseUrl}kewajiban-list/${user_id}/edit`)
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.text();
          })
          .then(result => {
            const { status, message, data } = JSON.parse(result);

            if(status) {
              // Buka Modal secara manual
              const modalInstance = new bootstrap.Modal(addRoleModal);
              modalInstance.show();

              document.getElementById('user_id').value = data.kewajiban_id;
              document.getElementById('kode_spk').value = data.kode_spk;
              document.getElementById('kode_estimasi').value = data.kode_estimasi;
              document.getElementById('kode_pelanggan').value = data.kode_pelanggan;
              document.getElementById('biaya_komisi').value = data.biaya_komisi;
              document.getElementById('biaya_estimasi').value = data.nilai_estimasi;
              document.getElementById('biaya_pribadi').value = data.biaya_pribadi;

              document.getElementById('add-nomor-estimasi').value = data.kode_estimasi;
              document.getElementById('add-tanggal-estimasi').value = data.tgl_estimasi;
              document.getElementById('add-nomor-spk').value = data.kode_spk;
              document.getElementById('add-nomor-polisi').value = data.no_polisi;
              document.getElementById('add-tipe-kendaraan').value = data.merek_tipe;
              document.getElementById('add-nama-pemilik').value = data.pemilik;
              document.getElementById('add-nama-pelanggan').value = data.nama_pelanggan;
              document.getElementById('add-contact-person').value = data.contact_person;
              document.getElementById('add-telepon').value = data.telepon;
              document.getElementById('add-nilai-estimasi').value = data.nilai_estimasi;
              document.getElementById('add-biaya-pribadi').value = data.biaya_pribadi;

              document.getElementById('add-jumlah-or').value = data.jumlah_or;
              document.getElementById('add-nilai-or').value = data.nilai_or;
              document.getElementById('add-total-or').value = data.total_or;
              document.getElementById('add-nilai-free-or').value = data.nilai_free_or;
              document.getElementById('add-keterangan').value = data.keterangan;

              setRadioValue('ada_or', data.ada_or);
              setRadioValue('is_free', data.is_free);
              setRadioValue('cek_polis', data.cek_polis);
              setRadioValue('pernyataan_puas', data.pernyataan_puas);
              setRadioValue('surat_kuasa', data.surat_kuasa);
              setRadioValue('biaya_penyusutan', data.biaya_penyusutan);
              setRadioValue('prorata', data.prorata);

            } else {
              // sweetalert
              Swal.fire({
                icon: 'warning',
                title: `Peringatan!`,
                text: `${message}`,
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            }
          })
          .catch(err => {
            Swal.fire({
              title: 'Error!',
              text: 'Gagal cek data SPK',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          });
        }
      });
    }

    // user form validation
    const addNewDataForm = document.getElementById('addNewDataForm');
    if (addNewDataForm) {
      const fv = FormValidation.formValidation(addNewDataForm, {
        fields: {
          // keterangan: {
          //   validators: {
          //     notEmpty: {
          //       message: 'Silahkan Input Keterangan'
          //     }
          //   }
          // }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            // Use this for enabling/changing valid/invalid class
            eleValidClass: '',
            rowSelector: function (field, ele) {
              // field is the field name & ele is the field element
              return '.form-control-validation';
            }
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        }
      }).on('core.form.valid', function () {
        // addNewDataForm.submit();

        // adding or updating user when form successfully validate
        const formData = new FormData(addNewDataForm);
        const formDataObj = {};

        // Convert FormData to URL-encoded string
        formData.forEach((value, key) => {
          formDataObj[key] = value;
        });

        const searchParams = new URLSearchParams();
        for (const [key, value] of Object.entries(formDataObj)) {
          searchParams.append(key, value);
        }

        fetch(`${baseUrl}kewajiban-list`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: searchParams.toString()
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.text();
        })
        .then(result => {
          const { status, message, errors } = JSON.parse(result);

          // Hide offcanvas
          const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
          offcanvasInstance && offcanvasInstance.hide();

          if(status) {
            // Refresh DataTable
            dt_basic_table && new DataTable(dt_basic_table).draw();

            // sweetalert
            Swal.fire({
              icon: 'success',
              title: `Informasi!`,
              text: `${message}`,
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });

          } else {
            let errorHtml = `<p>${message}</p>`; // Pesan utama (Gagal menyimpan data)

            if (errors) {
              errorHtml += '<ul style="text-align: left; margin-top: 10px;">';
              // Loop setiap key error (no_polisi, no_rangka, dll)
              Object.values(errors).forEach(errorArray => {
                // Loop setiap pesan error di dalam array
                errorArray.forEach(errMsg => {
                  errorHtml += `<li>${errMsg}</li>`;
                });
              });
              errorHtml += '</ul>';
            }

            // sweetalert
            Swal.fire({
              icon: 'error',
              title: `Error!`,
              html: errorHtml,
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          }
        })
        .catch(err => {
          // Hide offcanvas
          // const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
          // offcanvasInstance && offcanvasInstance.hide();

          Swal.fire({
            title: 'Error!',
            text: 'Gagal simpan data baru.',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        });
      });
    }

    const formFilter = document.getElementById('formFilterSpk');
    if (formFilter) {
        formFilter.addEventListener('submit', function (e) {
            e.preventDefault(); // Mencegah reload halaman

            // Reload DataTables (ini akan otomatis memanggil fungsi ajax.data di atas)
            dt_basic.draw();

            // (Opsional) Tutup modal setelah klik cari
            const modalEl = document.getElementById('filterRoleModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if(modalInstance) {
                modalInstance.hide();
            }
        });
    }

    // Event Listener: Jalankan fungsi saat radio button diklik/berubah
    $('input[name="ada_or"]').on('change', function() {
      toggleAdaOr();
    });

    $('input[name="is_free"]').on('change', function() {
      toggleFreeOr();
    });

    // Initial Check: Jalankan fungsi saat halaman pertama kali dimuat
    toggleAdaOr();
    toggleFreeOr();

    // Filter form control to default size
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
        {
          selector: '.dt-layout-start .dt-buttons',
          classToAdd: 'd-md-flex d-block gap-4 justify-content-center'
        },
        {
          selector: '.dt-layout-end .dt-buttons',
          classToAdd: 'd-md-flex d-block gap-4 mb-md-0 mb-5 justify-content-center'
        },
        { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
        { selector: '.dt-layout-full', classToRemove: 'col-md col-12' },
        { selector: '.dt-layout-full .table', classToAdd: 'table-responsive' }
      ];

      // Delete record
      elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
        document.querySelectorAll(selector).forEach(element => {
          if (classToRemove) {
            classToRemove.split(' ').forEach(className => element.classList.remove(className));
          }
          if (classToAdd) {
            classToAdd.split(' ').forEach(className => element.classList.add(className));
          }
        });
      });
    }, 100);
  }

  const invoiceItemPriceList = document.querySelectorAll('.invoice-price');
  if (invoiceItemPriceList) {
    invoiceItemPriceList.forEach(function (invoiceItemPrice) {
      if (invoiceItemPrice) {
        invoiceItemPrice.addEventListener('input', event => {
          invoiceItemPrice.value = formatNumeral(event.target.value, {
            delimiter: ',',
            numeral: true
          });

          if(invoiceItemPrice.name != "nilai_free_or") {
            hitungTotalOR();
          }

        });
      }
    });
  }

  // Select2 initialization
  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      // Ambil placeholder dari data/attr/opsi pertama kosong
      var ph =
        $this.data('placeholder') ||
        $this.attr('placeholder') ||
        $this.find('option[value=""]').first().text() ||
        'Please select';

      // Optional: fokus handler Anda
      if (typeof select2Focus === 'function') select2Focus($this);

      // Bungkus & init per elemen
      $this.wrap('<div class="position-relative"></div>').select2({
        placeholder: ph,
        allowClear: true,         // agar placeholder tampil saat kosong
        width: '100%',
        dropdownParent: $this.parent()
      });
    });
  }

  // Picker Date initialization
  const flatpickrDate = document.querySelectorAll('.dt-date');
  if (flatpickrDate) {
    flatpickrDate.flatpickr({
      monthSelectorType: 'static',
      static: true,
      dateFormat: 'd/m/Y'
    });
  }

  // Picker Date Range initialization
  const flatpickrDateRange = document.querySelectorAll('.dt-date-range');
  if (flatpickrDateRange) {
    flatpickrDateRange.flatpickr({
      mode: 'range',
      // monthSelectorType: 'static',
      static: true,
      dateFormat: 'd/m/Y'
    });
  }

 });

  function setSelectValue(selector, value, textIfMissing) {
    const $el = $(selector);
    if (!$el.length) return;

    // Normalisasi tipe: option.value adalah string
    const val = Array.isArray(value) ? value.map(v => String(v)) : (value ?? '');
    const isSelect2 = $el.hasClass('select2-hidden-accessible');

    // Jika select2 pakai AJAX dan opsi belum ada, tambahkan sementara
    const ensureOption = (v, t) => {
      if (!$el.find(`option[value="${v}"]`).length) {
        $el.append(new Option(t ?? v, v, false, false)); // text fallback = value
      }
    };

    if (Array.isArray(val)) {
      // multiple select
      val.forEach(v => ensureOption(v, (textIfMissing && textIfMissing[v]) || v));
      $el.val(val);
    } else {
      if (val === '' || val === null) { $el.val(null); }
      else { ensureOption(String(val), textIfMissing); $el.val(String(val)); }
    }

    // trigger agar UI Select2 ikut sync
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
      $el.trigger('change'); // penting untuk sync UI Select2
    }
  }

  function setRadioValue(name, value) {
    // 1. Cari semua radio button dengan name tersebut
    const $radios = $(`input[name="${name}"]`);

    // Guard clause: Jika elemen tidak ditemukan, stop.
    if (!$radios.length) return;

    // 2. Normalisasi value:
    // Jika null/undefined maka null, selain itu ubah ke String agar pencarian atribut akurat
    const val = (value !== null && value !== undefined) ? String(value) : null;

    // 3. Reset state: Uncheck semua radio button di grup ini terlebih dahulu
    $radios.prop('checked', false);

    // 4. Jika value ada, cari radio yang cocok dan set checked
    if (val !== null) {
        // Filter elemen yang value-nya sama persis
        $radios.filter(`[value="${val}"]`).prop('checked', true);
    }

    // 5. Trigger event 'change' (Penting jika ada logic lain yang memantau perubahan radio)
    $radios.trigger('change');
  }

  function clearFormData() {
    const form = document.getElementById('addNewDataForm');
    // reset input/textarea standar
    form?.reset?.();

    // kosongkan select (Select2)
    //  clearSelect('#add-nopolisi');

    // bersihkan error jQuery/FormValidation (kalau ada)
    // try {
    //   const fv = FormValidation && FormValidation.utils && FormValidation.utils closest ? null : null;
    // } catch (e) {}
  }

  function hitungTotalOR() {
    // Ambil elemen input di baris tersebut
    let elQty = $('#add-jumlah-or');
    let elHarga = $('#add-nilai-or');
    let elJumlah = $('#add-total-or');

    // Ambil Nilai & Bersihkan format (hapus koma)
    let qty = parseFloat(elQty.val().replace(/,/g, '')) || 0;
    let harga = parseFloat(elHarga.val().replace(/,/g, '')) || 0;

    // Hitung Subtotal Baris
    let subtotal = qty * harga;

    // Format hasil ke string angka (misal: 2,000.00)
    let formattedSubtotal = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(subtotal);

    // Update Kolom JUMLAH di baris tersebut
    elJumlah.val(formattedSubtotal);
  }

  function toggleAdaOr() {
    // 1. Ambil value dari radio button 'ada_or' yang sedang dipilih (checked)
    // Value '01' = Ya, Value '02' = Tidak (sesuai HTML Anda)
    let selectedValue = $('input[name="ada_or"]:checked').val();

    // 2. Definisikan elemen input yang akan dipengaruhi
    const inputsOr = $('#add-jumlah-or, #add-nilai-or, #add-total-or');

    // 3. Cek Kondisi
    if (selectedValue === '02') {
      // --- KONDISI: TIDAK ---

      // Set nilai jadi 0
      inputsOr.val(0);

      // Disable input (jadi abu-abu dan tidak bisa diklik)
      inputsOr.prop('disabled', true);
    } else {
      // --- KONDISI: YA (atau belum pilih) ---

      // Enable input (hapus atribut disabled)
      inputsOr.prop('disabled', false);
    }
  }

  function toggleFreeOr() {
    // 1. Ambil value dari radio button 'is_free' yang sedang dipilih (checked)
    // Value 'Y' = Ya, Value 'T' = Tidak (sesuai HTML Anda)
    let selectedValue = $('input[name="is_free"]:checked').val();

    // 2. Definisikan elemen input yang akan dipengaruhi
    const inputsOr = $('#add-nilai-free-or');

    // 3. Cek Kondisi
    if (selectedValue === 'T') {
      // --- KONDISI: TIDAK ---

      // Set nilai jadi 0
      inputsOr.val(0);

      // Disable input (jadi abu-abu dan tidak bisa diklik)
      inputsOr.prop('disabled', true);
    } else {
      // --- KONDISI: YA (atau belum pilih) ---

      // Enable input (hapus atribut disabled)
      inputsOr.prop('disabled', false);
    }
  }

