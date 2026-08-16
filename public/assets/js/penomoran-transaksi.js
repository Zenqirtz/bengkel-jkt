/**
 * Page Data management
 */

 'use strict';

 // Datatable (js)
 document.addEventListener('DOMContentLoaded', function (e) {
    let isAdd, isEdit, isDelete;
 
   // Variable declaration for table
   const dt_basic_table = document.querySelector('.datatables-penomoran-transaksi'),
     addRoleModal = document.getElementById('addRoleModal'),
     statusObj = {
      'Y': { title: 'Aktif', class: 'text-bg-success' },
      'N': { title: 'Tidak Aktif', class: 'text-bg-danger' }
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

    // let tableTitle = document.createElement('h5');
    // tableTitle.classList.add('card-title', 'mb-0', 'text-md-start', 'text-center');
    // tableTitle.innerHTML = dt_basic_table.dataset.title;
    const dt_basic = new DataTable(dt_basic_table, {
      searching: false,  // Opsi ini akan menghilangkan input cari
      ordering: true,    // Opsi lain tetap bisa jalan
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'penomoran-transaksi-list',
        data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.modul = $('#filter-modul').val();
          d.contoh = $('#filter-contoh').val();
          d.autoreset = $('#filter-autoreset').val();
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
        { data: 'id', width: '20px' },
        { data: 'id', width: '30px' },
        { data: 'modul' },
        { data: 'cabang' },
        { data: 'nama_bank' },
        { data: 'autoreset' },
        { data: 'nourut' },
        { data: 'contoh' }
      ],
      columnDefs: [
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
          targets: 1,
          render: function (data, type, full, meta) {
            return `<span>${full.fake_id}</span>`;
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
        // {
        //   // Actions
        //   targets: -1,
        //   title: 'Actions',
        //   searchable: false,
        //   orderable: false,
        //   render: function (data, type, full, meta) {
        //     return (
        //       '<div class="d-flex align-items-center gap-4">' +
        //     //  `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill edit-record" data-id="${full['id']}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAdd" title="Ubah"><i class="icon-base ri ri-edit-box-line icon-22px"></i></button>` +
        //       `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill delete-record" data-id="${full['id']}" title="Hapus"><i class="icon-base ri ri-delete-bin-7-line icon-22px"></i></button>` +
        //       '</div>'
        //     );
        //   }
        // }
      ],
      order: [[1, 'desc']],
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
    });

    // Batasi hanya 1 checkbox yang boleh dipilih
    document.addEventListener('click', function (e) {
      const chk = e.target.closest('.datatables-penomoran-transaksi .dt-checkboxes');
      if (chk) {
        if (chk.checked) {
            $('.dt-checkboxes').not(chk).prop('checked', false);
        }
      }
    });
 
    const formFilter = document.getElementById('formCariData');
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

    // Edit Record
    const btnEditSelected = document.querySelector('.edit-record');
    if (btnEditSelected) {
      btnEditSelected.addEventListener('click', function () {
        // Cek Izin Edit
        if (!isEdit) {
          Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Anda tidak memiliki izin untuk ubah data.',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        // Cari checkbox yang tercentang di dalam tabel
        const selectedCheckbox = document.querySelector('.datatables-penomoran-transaksi .dt-checkboxes:checked');

        if (!selectedCheckbox) {
          // Jika tidak ada yang dipilih
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silahkan pilih data pada tabel terlebih dahulu!',
            customClass: { confirmButton: 'btn btn-primary' }
          });
        } else {
          // Jika ada yang dipilih
          const user_id = selectedCheckbox.value;

          // reset seluruh form (termasuk select2)
          clearFormData();

          // get data
          fetch(`${baseUrl}penomoran-transaksi-list/${user_id}/edit`)
          .then(response => response.json())
          .then(data => {
            // Buka Modal secara manual
            const modalInstance = new bootstrap.Modal(addRoleModal);
            modalInstance.show();

            document.getElementById('user_id').value = data.id;

            document.getElementById('noTerakhir').value = data.nourut;
            document.getElementById('add-segmen1').value = data.segmen1;
            document.getElementById('contoh').value = data.contoh;

            setRadioValue('autoreset', data.autoreset);

            setSelectValue('#add-modul',   data.modul,  '');
            setSelectValue('#add-dept',   data.cabang,  '');
            setSelectValue('#add-bank',   data.bank,  '');
            setSelectValue('#digitCnt',   data.digit_cnt,  '');

            buildPreview();
          });
        }
      });
    }

    // Delete Record
    const btnDeletedSelected = document.querySelector('.delete-record');
    if (btnDeletedSelected) {
      btnDeletedSelected.addEventListener('click', function () {
        // Cek Izin Edit
        if (!isDelete) {
          Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Anda tidak memiliki izin untuk hapus data.',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        // Cari checkbox yang tercentang di dalam tabel
        const selectedCheckbox = document.querySelector('.datatables-penomoran-transaksi .dt-checkboxes:checked');

        if (!selectedCheckbox) {
          // Jika tidak ada yang dipilih
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silahkan pilih data pada tabel terlebih dahulu!',
            customClass: { confirmButton: 'btn btn-primary' }
          });
        } else {
          // Jika ada yang dipilih
          const user_id = selectedCheckbox.value;
          
          // sweetalert for confirmation of delete
          Swal.fire({
            title: 'Konfirmasi?',
            text: "Anda yakin akan menghapus data ini!",
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
              // delete the data
              fetch(`${baseUrl}penomoran-transaksi-list/${user_id}`, {
                method: 'DELETE',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                  'Content-Type': 'application/json'
                }
              })
                .then(response => {
                  if (response.ok) {
                    dt_basic.draw();
  
                    // success sweetalert
                    Swal.fire({
                      icon: 'success',
                      title: 'Hapus!',
                      text: 'Data berhasil dihapus!',
                      customClass: {
                        confirmButton: 'btn btn-success'
                      }
                    });
                  } else {
                    throw new Error('Gagal Hapus Data');
                  }
                })
                .catch(error => {
                    //  console.log(error);
                    Swal.fire({
                      icon: 'error',
                      title: 'Error!',
                      text: `${error}`,
                      customClass: {
                        confirmButton: 'btn btn-success'
                      }
                    });
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
              Swal.fire({
                title: 'Batal',
                text: 'Data batal dihapus!',
                icon: 'error',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            }
          });
        }
      });
    }
 
    // changing the title
    const addNewBtn = document.querySelector('.add-new');
    if (addNewBtn) {
      addNewBtn.addEventListener('click', function () {
        if(isAdd) {
          document.getElementById('user_id').value = ''; //resetting input field
  
          // reset seluruh form (termasuk select2)
          clearFormData();
  
          // opsional: fokus ke username
          document.getElementById('add-nama-penomoran-transaksi')?.focus();
        } else {
          // Hide offcanvas
          const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
          offcanvasInstance && offcanvasInstance.hide();
          
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Anda tidak memiliki izin untuk akses tambah data',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      });
    }
 
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
 
   // validating form and updating user's data
   const addNewDataForm = document.getElementById('addNewDataForm');
   // user form validation
   if (addNewDataForm) {
     const fv = FormValidation.formValidation(addNewDataForm, {
       fields: {
        modul: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Modul Transaksi'
            }
          }
        },
        segmen1: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Format Penomoran'
            }
          }
        }
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

        fetch(`${baseUrl}penomoran-transaksi-list`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: searchParams.toString()
        })
        .then(async (response) => {
          const text = await response.text();
          let json = {};
          try { json = JSON.parse(text); } catch { json = { status:false, message:text || 'Gagal' }; }
          if (!response.ok) throw new Error(json.message || 'Gagal simpan');

          // Hide offcanvas
          const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
          offcanvasInstance && offcanvasInstance.hide();

          // Refresh DataTable
          dt_basic_table && new DataTable(dt_basic_table).draw();

          if(json.status) {
            Swal.fire({
              icon: 'success',
              title: 'Informasi!',
              text: json.message || 'Berhasil.',
              customClass: { confirmButton: 'btn btn-success' },
              buttonsStyling: false
            });
          } else {
            let errorHtml = `<p>${json.message}</p>`; // Pesan utama (Gagal menyimpan data)

            if (json.errors) {
              errorHtml += '<ul style="text-align: left; margin-top: 10px;">';
              // Loop setiap key error (no_polisi, no_rangka, dll)
              Object.values(json.errors).forEach(errorArray => {
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
        .catch((err) => {
          const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
          offcanvasInstance && offcanvasInstance.hide();

          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: String(err.message || err),
            customClass: { confirmButton: 'btn btn-success' }
          });
        });
     });
 
     // clearing form data when offcanvas hidden
     addRoleModal.addEventListener('hidden.bs.modal', function () {
       fv.resetForm(true);
       clearFormData();
       // clearSelect('#add-grup');
     });
   }
 
   // Phone mask initialization
   const phoneMaskList = document.querySelectorAll('.phone-mask');
   // Phone Number
   if (phoneMaskList) {
     phoneMaskList.forEach(function (phoneMask) {
       phoneMask.addEventListener('input', event => {
         const cleanValue = event.target.value.replace(/\D/g, '');
         phoneMask.value = formatGeneral(cleanValue, {
           blocks: [3, 3, 4],
           delimiters: [' ', ' ']
         });
       });
       registerCursorTracker({
         input: phoneMask,
         delimiter: ' '
       });
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

  // sisipkan token ke segmen yang sedang fokus
  let lastFocused = null;
  document.querySelectorAll('.fmt').forEach(i=>{
    i.addEventListener('focus', ()=> lastFocused = i);
    i.addEventListener('input', buildPreview);
  });
  document.getElementById('tokenPicker').addEventListener('change', function(){
    if(!this.value) return;
    (lastFocused || document.querySelector('.fmt')).value += (lastFocused?.value ? '' : '') + this.value;
    this.value = '';
    buildPreview();
  });

  document.getElementById('digitCnt').addEventListener('change', buildPreview);
  document.getElementById('noTerakhir').addEventListener('input', buildPreview);
  document.querySelector('[name="dept"]').addEventListener('change', buildPreview);
  document.querySelector('[name="bank"]').addEventListener('change', buildPreview);
  document.querySelector('[name="modul"]').addEventListener('change', buildPreview);

  buildPreview();
 });
 
  function buildPreview(){
    const parts = [...document.querySelectorAll('.fmt')].map(i=>i.value.trim()).filter(Boolean);
    const fmt = parts.join('');
    const bln = String(new Date().getMonth()+1).padStart(2,'0');
    const thn = String(new Date().getFullYear()).slice(-2);
    const blnthn = bln + String(new Date().getFullYear());
    const cnt = String(Number(document.getElementById('noTerakhir').value||0)+1).padStart(Number(document.getElementById('digitCnt').value||4), '0');
    const dept = document.querySelector('[name="dept"]').value || 'DPT';
    const bank = document.querySelector('[name="bank"]').value || 'BANK';
    const modul = document.querySelector('[name="modul"]').value || 'MODUL';

    let contoh = fmt
      .replaceAll('[BLN]', bln)
      .replaceAll('[THN]', thn)
      .replaceAll('[BLNTHN]', blnthn)
      .replaceAll('[CNT]', cnt)
      .replaceAll('[DEPT]', dept)
      .replaceAll('[BANK]', bank)
      .replaceAll('[MODUL]', modul);

    document.getElementById('contoh').textContent = contoh || '';
    document.getElementById('contoh_final').value = contoh || '';
  }

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
 
 function clearFormData() {
   const form = document.getElementById('addNewDataForm');
   // reset input/textarea standar
   form?.reset?.();
 
   // kosongkan select (Select2)
   clearSelect('#add-modul');
   clearSelect('#add-dept');
   clearSelect('#add-bank');
  //  clearSelect('#digitCnt');

  $('#add-modul').addClass('is-invalid');
  $('#add-segmen1').addClass('is-invalid');
 
   // bersihkan error jQuery/FormValidation (kalau ada)
   // try {
   //   const fv = FormValidation && FormValidation.utils && FormValidation.utils closest ? null : null;
   // } catch (e) {}
 }
 