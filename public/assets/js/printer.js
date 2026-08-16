/**
 * Page Data management
 */

 'use strict';

 // Datatable (js)
 document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd, isEdit, isDelete;
 
   // Variable declaration for table
   const dt_basic_table = document.querySelector('.datatables-printer'),
     offCanvasForm = document.getElementById('offcanvasAdd'),
     statusObj = {
      'Y': { title: 'Aktif', class: 'text-bg-success' },
      'N': { title: 'Tidak Aktif', class: 'text-bg-danger' }
    };

  isAdd = dt_basic_table.dataset.add;
  isEdit = dt_basic_table.dataset.edit;
  isDelete = dt_basic_table.dataset.delete;
 
   // ajax setup
   $.ajaxSetup({
     headers: {
       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
   });
 
   // COA datatable
   if (dt_basic_table) {
     let tableTitle = document.createElement('h5');
     tableTitle.classList.add('card-title', 'mb-0', 'text-md-start', 'text-center');
     tableTitle.innerHTML = dt_basic_table.dataset.title;
     const dt_basic = new DataTable(dt_basic_table, {
       processing: true,
       serverSide: true,
       ajax: {
         url: baseUrl + 'printer-list',
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
         { data: 'printer' },
         { data: 'kertas' },
         { data: 'font' },
         { data: 'paragraph' },
         { data: 'action' }
       ],
       columnDefs: [
         {
           // For Responsive
           className: 'control',
           searchable: false,
           orderable: false,
           responsivePriority: 2,
           targets: 0,
           render: function (data, type, full, meta) {
             return '';
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
         {
           targets: 2,
           responsivePriority: 2
         },
         {
           targets: 3,
           responsivePriority: 3
         },
         {
           // Actions
           targets: -1,
          //  title: 'Actions',
           searchable: false,
           orderable: false,
           render: function (data, type, full, meta) {
             return (
               '<div class="d-flex align-items-center gap-4">' +
               `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill edit-record" data-id="${full['id']}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAdd" title="Ubah"><i class="icon-base ri ri-edit-box-line icon-22px"></i></button>` +
               `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill delete-record" data-id="${full['id']}" title="Hapus"><i class="icon-base ri ri-delete-bin-7-line icon-22px"></i></button>` +
               '</div>'
             );
           }
         }
       ],
       order: [[1, 'asc']],
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
                   attr: {
                     'data-bs-toggle': 'offcanvas',
                     'data-bs-target': '#offcanvasAdd'
                   }
                 }
               ]
             }
           ]
         },
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
       responsive: {
         details: {
           display: DataTable.Responsive.display.modal({
             header: function (row) {
               const data = row.data();
               return 'Details of ' + data['name'];
             }
           }),
           type: 'column',
           renderer: function (api, rowIdx, columns) {
             const data = columns
               .map(function (col) {
                 return col.title !== '' // Do not show row in modal popup if title is blank (for check box)
                   ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                       <td>${col.title}:</td>
                       <td>${col.data}</td>
                     </tr>`
                   : '';
               })
               .join('');
 
             if (data) {
               const div = document.createElement('div');
               div.classList.add('table-responsive');
               const table = document.createElement('table');
               div.appendChild(table);
               table.classList.add('table');
               const tbody = document.createElement('tbody');
               tbody.innerHTML = data;
               table.appendChild(tbody);
               return div;
             }
             return false;
           }
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
 
     // Delete Record
     document.addEventListener('click', function (e) {
       if (e.target.closest('.delete-record')) {
          if (isDelete) {
            const deleteBtn = e.target.closest('.delete-record');
            const user_id = deleteBtn.dataset.id;
            const dtrModal = document.querySelector('.dtr-bs-modal.show');
    
            // hide responsive modal in small screen
            if (dtrModal) {
              const bsModal = bootstrap.Modal.getInstance(dtrModal);
              bsModal.hide();
            }
    
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
                fetch(`${baseUrl}printer-list/${user_id}`, {
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
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: 'Anda tidak memiliki izin untuk akses hapus data',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          }
        }
     });
 
     // edit record
     document.addEventListener('click', function (e) {
       if (e.target.closest('.edit-record')) {
          if(isEdit) {
            const editBtn = e.target.closest('.edit-record');
            const user_id = editBtn.dataset.id;
            const dtrModal = document.querySelector('.dtr-bs-modal.show');
    
            // hide responsive modal in small screen
            if (dtrModal) {
              const bsModal = bootstrap.Modal.getInstance(dtrModal);
              bsModal.hide();
            }
    
            // changing the title of offcanvas
            document.getElementById('offcanvasAddLabel').innerHTML = 'Ubah Data';
    
            // get data
            fetch(`${baseUrl}printer-list/${user_id}/edit`)
              .then(response => response.json())
              .then(data => {
                document.getElementById('user_id').value = data.id;
                document.getElementById('add-printer').value = data.printer;
                document.getElementById('add-kertas').value = data.kertas;
                document.getElementById('add-font').value = data.font;
                document.getElementById('add-paragraph').value = data.paragraph;

                //  setSelectValue('#add-periode',   data.tgl_periode,  data.tgl_periode_name);
              });
          } else {
            // Hide offcanvas
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasForm);
            offcanvasInstance && offcanvasInstance.hide();
            
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: 'Anda tidak memiliki izin untuk akses rubah data',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          }
        }
     });
 
     // changing the title
     const addNewBtn = document.querySelector('.add-new');
     if (addNewBtn) {
       addNewBtn.addEventListener('click', function () {
          if(isAdd) {
            document.getElementById('user_id').value = ''; //resetting input field
            document.getElementById('offcanvasAddLabel').innerHTML = 'Tambah Data';
    
            // reset seluruh form (termasuk select2)
            clearFormData();
    
            // opsional: fokus ke username
            document.getElementById('add-periode')?.focus();
          } else {
            // Hide offcanvas
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasForm);
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
        printer: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Printer'
            }
          }
        },
        kertas: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Kertas'
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
 
       fetch(`${baseUrl}printer-list`, {
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
           const { status, message } = JSON.parse(result);
 
           // Hide offcanvas
           const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasForm);
           offcanvasInstance && offcanvasInstance.hide();
 
           if(status) {
             // Refresh DataTable
             dt_basic_table && new DataTable(dt_basic_table).draw();
 
              // sweetalert
              Swal.fire({
                icon: 'success',
                title: 'Informasi!',
                text: message,                 // sama saja dengan `${message}`
                customClass: {
                  confirmButton: 'btn btn-success'
                },
                buttonsStyling: false          // biar class kustom dipakai, opsional
              }).then((result) => {
                // if (result.isConfirmed) {
                //   location.reload();           // reload halaman
                // }
              });
           } else {
             // sweetalert
             Swal.fire({
               icon: 'error',
               title: `Error!`,
               text: `${message}`,
               customClass: {
                 confirmButton: 'btn btn-success'
               }
             });
           }
           
         })
         .catch(err => {
           // Hide offcanvas
           const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasForm);
           offcanvasInstance && offcanvasInstance.hide();
 
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
 
     // clearing form data when offcanvas hidden
     offCanvasForm.addEventListener('hidden.bs.offcanvas', function () {
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
  //  clearSelect('#add-periode');
 
   // bersihkan error jQuery/FormValidation (kalau ada)
   // try {
   //   const fv = FormValidation && FormValidation.utils && FormValidation.utils closest ? null : null;
   // } catch (e) {}
 }
 