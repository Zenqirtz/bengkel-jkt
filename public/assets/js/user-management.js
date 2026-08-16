/**
 * Page Data management
 */

 'use strict';

 // Datatable (js)
 document.addEventListener('DOMContentLoaded', function (e) {
    let isAdd, isEdit, isDelete;
 
   // Variable declaration for table
   const dt_basic_table = document.querySelector('.datatables-users'),
     addRoleModal = document.getElementById('addRoleModal'),
     statusObj = {
      'Y': { title: 'Aktif', class: 'text-bg-success' },
      'N': { title: 'Tidak Aktif', class: 'text-bg-danger' },
      'T': { title: 'Tidak Aktif', class: 'text-bg-danger' },
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

    const dt_basic = new DataTable(dt_basic_table, {
      searching: false,  // Opsi ini akan menghilangkan input cari
      ordering: true,    // Opsi lain tetap bisa jalan
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'registrasi-user-list',
        data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.username = $('#filter-username').val();
          d.fullname = $('#filter-fullname').val();
          d.email = $('#filter-email').val();
          d.level = $('#filter-level').val();
          d.status = $('#filter-status').val();
          d.grup = $('#filter-grup').val();
          d.cabang = $('#filter-cabang').val();
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
        { data: 'username' },
        { data: 'name' },
        { data: 'email' },
        { data: 'nm_group' },
        { data: 'nama_cabang' },
        { data: 'nm_level' },
        { data: 'file_photo', className: 'text-center' },
        { data: 'nm_status' },
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
          visible: true,
          targets: 1,
          render: function (data, type, full, meta) {
            return `<span>${full.fake_id}</span>`;
          }
        },
        // {
        //   targets: 2,
        //   responsivePriority: 4,
        //   render: function (data, type, full, meta) {
        //     const userImg = full['logo_cabang'];
        //     const name = full['nama_cabang'];
        //     const post = full['nama_singkat'];
        //     let output;

        //     if (userImg) {
        //       // For Avatar image
        //       output = `<img src="${assetsPath}img/cabang/${userImg}" alt="Avatar" class="rounded-circle">`;
        //     } else {
        //       // For Avatar badge
        //       const stateNum = Math.floor(Math.random() * 6);
        //       const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
        //       const state = states[stateNum];
        //       let initials = name.match(/\b\w/g) || [];
        //       initials = ((initials.shift() || '') + (initials.pop() || '')).toUpperCase();
        //       output = `<span class="avatar-initial rounded-circle bg-label-${state}">${initials}</span>`;
        //     }

        //     // Creates full output for row
        //     const rowOutput = `
        //       <div class="d-flex justify-content-start align-items-center user-name">
        //         <div class="avatar-wrapper">
        //           <div class="avatar me-2">
        //             ${output}
        //           </div>
        //         </div>
        //         <div class="d-flex flex-column">
        //           <span class="emp_name text-truncate h6 mb-0">${name}</span>
        //           <small class="emp_post text-truncate">${post}</small>
        //         </div>
        //       </div>
        //     `;

        //     return rowOutput;
        //   }
        // },
        {
          targets: -2,
          orderable: false,
          // responsivePriority: 2
          render: function (data, type, full, meta) {
            if(data == '1') {
              return `<button class="btn btn-xs btn-icon btn-success download-file" data-id="${full['id']}" data-tipe="photo" title="Download"><i class="icon-base ri ri-check-line icon-22px"></i></button>`;
            } else {
              return `<span class="badge badge-center text-bg-danger"><i class="icon-base ri ri-close-line"></i></span>`;
            }
          }
        },
        {
          targets: -1,
          render: function (data, type, full, meta) {
            const status = full['status'];

            if(status == 'Y') {
              return `<span class="badge rounded-pill text-bg-success text-capitalized">${data}</span>`;
            } else {
              return `<span class="badge rounded-pill text-bg-danger text-capitalized">${data}</span>`;
            }
          }
        },
      ],
      // scrollY: '300px',
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
      const chk = e.target.closest('.datatables-users .dt-checkboxes');
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
        const selectedCheckbox = document.querySelector('.datatables-users .dt-checkboxes:checked');

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
          fetch(`${baseUrl}registrasi-user-list/${user_id}/edit`)
          .then(response => response.json())
          .then(data => {
            // Buka Modal secara manual
            const modalInstance = new bootstrap.Modal(addRoleModal);
            modalInstance.show();

            document.getElementById('user_id').value = data.id;
            document.getElementById('old_photo').value = data.profile_photo_url;
            document.getElementById('add-username').value = data.username;
            document.getElementById('add-fullname').value = data.name;
            document.getElementById('add-email').value = data.email;

            setSelectValue('#add-grup',   data.user_group, '');
            setSelectValue('#add-level',  data.user_level, '');
            setSelectValue('#add-cabang', data.user_cabang, '');
            setSelectValue('#add-status', data.status, '');
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
        const selectedCheckbox = document.querySelector('.datatables-users .dt-checkboxes:checked');

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
              fetch(`${baseUrl}registrasi-user-list/${user_id}`, {
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
 
    document.addEventListener('click', function (e) {
      if (e.target.closest('.download-file')) {
        const dwlBtn = e.target.closest('.download-file');
        const user_id = dwlBtn.dataset.id;
        const tipe = dwlBtn.dataset.tipe;

        if(user_id.length) {
          let params = {
            id: user_id,
            tipe: tipe
          };

          // Bersihkan parameter kosong agar URL tidak terlalu panjang
          let queryString = $.param(params);
          
          // Redirect window untuk download file
          // Pastikan route URL sesuai dengan konfigurasi route Anda
          // window.location.href = `${baseUrl}laporan-kendaraan/print?` + queryString;
          const printUrl = `${baseUrl}setting/download-file-user?` + queryString;
          window.open(printUrl, '_blank');
        }
      }
    });
 
    // changing the title
    const addNewBtn = document.querySelector('.add-new');
    if (addNewBtn) {
      addNewBtn.addEventListener('click', function () {
      if(isAdd) {
        document.getElementById('user_id').value = ''; //resetting input field
        document.getElementById('old_photo').value = '';
        // document.getElementById('offcanvasAddLabel').innerHTML = 'Tambah Data';

        // reset seluruh form (termasuk select2)
        clearFormData();

        // opsional: fokus ke username
        document.getElementById('add-nama-cabang')?.focus();
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
        username: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Nama User'
            }
          }
        },
        fullname: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Nama Lengkap'
            }
          }
        },
        email: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Email'
            },
            emailAddress: {
              message: 'Format email tidak sesuai'
            }
          }
        },
        user_group: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Grup User'
            }
          }
        },
        user_level: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input User Level'
            }
          }
        },
        user_cabang: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Cabang'
            }
          }
        },
        status: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Status'
            }
          }
        },
        password: {
          validators: {
            // notEmpty: {
            //   message: 'Silahkan Input Password'
            // }
            stringLength: {
              min: 5,
              message: 'Minimal 5 karakter'
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

        // gunakan FormData apa adanya (include file)
        const form = addNewDataForm;
        const fd = new FormData(form);

        // endpoint: POST (create) atau PUT/PATCH (update)
        const id = document.getElementById('user_id').value; //(fd.get('user_id') || '').trim();
        const method = id ? 'POST' : 'POST';
        const url = id ? `${baseUrl}registrasi-user-list/${id}` : `${baseUrl}registrasi-user-list`;

        // jika update dengan method spoofing (Laravel)
        if (id) fd.append('_method', 'PUT');

        fetch(url, {
          method,
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            // JANGAN set 'Content-Type' → biar browser set boundary otomatis
          },
          body: fd
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
      });
  }
 
  // Phone mask initialization
  const phoneMaskList = document.querySelectorAll('.phone-mask');
  if (phoneMaskList) {
    phoneMaskList.forEach(function (phoneMask) {
      phoneMask.addEventListener('input', event => {
        const cleanValue = event.target.value.replace(/\D/g, '');
        phoneMask.value = formatGeneral(cleanValue, {
          blocks: [4, 4, 4],
          delimiters: ['-', '-']
        });
      });
      registerCursorTracker({
        input: phoneMask,
        delimiter: '-'
      });
    });
  }

  const telpMaskList = document.querySelectorAll('.telp-mask');
  if (telpMaskList) {
    telpMaskList.forEach(function (telpMask) {
      telpMask.addEventListener('input', event => {
        const cleanValue = event.target.value.replace(/\D/g, '');
        telpMask.value = formatGeneral(cleanValue, {
          blocks: [3, 10],
          delimiters: ['-']
        });
      });
      registerCursorTracker({
        input: telpMask,
        delimiter: '-'
      });
    });
  }

  // Number Only
  const numberMaskList = document.querySelectorAll('.text-number');
  if (numberMaskList) {
    numberMaskList.forEach(function (numberMask) {
      numberMask.addEventListener('input', event => {
        const cleanValue = event.target.value.replace(/\D/g, '');
        numberMask.value = formatGeneral(cleanValue, {
          blocks: [99],
          delimiters: ['']
        });
      });
      registerCursorTracker({
        input: numberMask,
        delimiter: ''
      });
    });
  }

  const inputUppercaseList = document.querySelectorAll('.text-uppercase');
  if (inputUppercaseList) {
    inputUppercaseList.forEach(function (inputUppercase) {
      inputUppercase.addEventListener('input', event => {
        inputUppercase.value = inputUppercase.value.toUpperCase();
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
 
    // Phone mask initialization
    const flatpickrDate = document.querySelectorAll('.dt-date');
    if (flatpickrDate) {
     flatpickrDate.flatpickr({
       monthSelectorType: 'static',
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
 
 function clearFormData() {
    const form = document.getElementById('addNewDataForm');
    // reset input/textarea standar
    form?.reset?.();
 
    // kosongkan select (Select2)
    clearSelect('#add-grup');
    clearSelect('#add-level');
    clearSelect('#add-cabang');
    clearSelect('#add-status');

    $('#add-username').addClass('is-invalid');
    $('#add-fullname').addClass('is-invalid');
    $('#add-email').addClass('is-invalid');
    $('#add-grup').addClass('is-invalid');
    $('#add-level').addClass('is-invalid');
    $('#add-cabang').addClass('is-invalid');
    $('#add-status').addClass('is-invalid');
 
    // bersihkan error jQuery/FormValidation (kalau ada)
    // try {
    //   const fv = FormValidation && FormValidation.utils && FormValidation.utils closest ? null : null;
    // } catch (e) {}
 }
 