/**
 * Page Data management
 */

 'use strict';

 // Datatable (js)
 document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd, isEdit, isDelete;
  let validationStepper;
 
   // Variable declaration for table
   const dt_basic_table = document.querySelector('.datatables-kendaraan'),
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
 
  // datatable
  if (dt_basic_table) {
    isAdd = dt_basic_table.dataset.add;
    isEdit = dt_basic_table.dataset.edit;
    isDelete = dt_basic_table.dataset.delete;
    //  let tableTitle = document.createElement('h5');
    //  tableTitle.classList.add('card-title', 'mb-0', 'text-md-start', 'text-center');
    //  tableTitle.innerHTML = dt_basic_table.dataset.title;
    const dt_basic = new DataTable(dt_basic_table, {
      searching: false,  // Opsi ini akan menghilangkan input cari
      ordering: true,    // Opsi lain tetap bisa jalan
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
         url: baseUrl + 'kendaraan-list',
         data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.no_polisi = $('#filter-no-polisi').val();
          d.nama_distnk = $('#filter-nama-distnk').val();
          d.no_mesin = $('#filter-mesin').val();
          d.no_rangka = $('#filter-rangka').val();
          d.no_model = $('#filter-model').val();
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
          { data: 'no_polisi' },
          { data: 'nama_pemilik' },
          { data: 'nama_distnk' },
          { data: 'nama_merek' },
          { data: 'nama_tipe' },
          { data: 'nama_jenis' },
          { data: 'no_rangka' },
          { data: 'no_mesin' },
          { data: 'no_model' },
          { data: 'tahun' },
          { data: 'ukuran_cc' },
          { data: 'perseneling' },
          { data: 'warna' },
          { data: 'bahan_bakar' },
          { data: 'tgl_stnk_berakhir' },
          // { data: 'action' }
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
        //  {
        //    // For Responsive
        //    className: 'control',
        //    searchable: false,
        //    orderable: false,
        //    visible: false,
        //    responsivePriority: 2,
        //    targets: 0,
        //    render: function (data, type, full, meta) {
        //      return '';
        //    }
        //  },
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
        //  {
        //    // Actions
        //    targets: -1,
        //   //  title: 'Actions',
        //    searchable: false,
        //    orderable: false,
        //    render: function (data, type, full, meta) {
        //      return (
        //        '<div class="d-flex align-items-center gap-4">' +
        //        `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill edit-record" data-id="${full['id']}" data-bs-toggle="modal" data-bs-target="#addRoleModal" title="Ubah"><i class="icon-base ri ri-edit-box-line icon-22px"></i></button>` +
        //        `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill delete-record" data-id="${full['id']}" title="Hapus"><i class="icon-base ri ri-delete-bin-7-line icon-22px"></i></button>` +
        //        '</div>'
        //      );
        //    }
        //  }
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
      // initComplete: function () {
      //    $('.card-header').after('<hr class="my-0">');
      //    // Remove btn-secondary from export buttons
      //    document.querySelectorAll('.dt-buttons .btn').forEach(btn => {
      //      btn.classList.remove('btn-secondary');
      //    });
      // }
    });

    // Batasi hanya 1 checkbox yang boleh dipilih
    document.addEventListener('click', function (e) {
      const chk = e.target.closest('.datatables-kendaraan .dt-checkboxes');
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
        const selectedCheckbox = document.querySelector('.datatables-kendaraan .dt-checkboxes:checked');

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

          if (validationStepper) {
            validationStepper.to(1); // Pindah ke Step 1
          }

          // get data
          fetch(`${baseUrl}kendaraan-list/${user_id}/edit`)
          .then(response => response.json())
          .then(data => {
            // Buka Modal secara manual
            const modalInstance = new bootstrap.Modal(addRoleModal);
            modalInstance.show();

            document.getElementById('user_id').value = data.id;
            document.getElementById('kode_tipe').value = data.kode_tipe;
            document.getElementById('add-no-polisi').value = data.no_polisi;
            document.getElementById('add-nama').value = data.nama_distnk;
            document.getElementById('add-warna').value = data.warna;
            document.getElementById('add-cc').value = data.ukuran_cc;
            document.getElementById('add-tahun').value = data.tahun;
            document.getElementById('add-rangka').value = data.no_rangka;
            document.getElementById('add-mesin').value = data.no_mesin;
            document.getElementById('add-model').value = data.no_model;
            document.getElementById('add-tgl-stnk').value = data.tgl_stnk_berakhir;

            setSelectValue('#add-nama-pemilik', data.kode_pemilik,  '');
            setSelectValue('#add-merek', data.kode_merek, data.nama_merek);
            setSelectValue('#add-jenis-kendaraan', data.jenis, data.nama_jenis);
            setSelectValue('#add-perseneling', data.kode_jenis_perseneling,  '');
            setSelectValue('#add-bahan-bakar', data.kode_bahan_bakar,  '');

            setSelectValue('#add-tipe', data.kode_tipe, '');

            fetchFotoSTNK(data.id);

            document.addEventListener('click', function (e) {
              if (e.target.closest('.delete-foto')) {
                if (isEdit) {
                  const deleteBtn = e.target.closest('.delete-foto');
                  const user_id = deleteBtn.dataset.id;
                  // const dtrModal = document.querySelector('.dtr-bs-modal.show');
  
                  // hide responsive modal in small screen
                  // if (dtrModal) {
                  //   const bsModal = bootstrap.Modal.getInstance(dtrModal);
                  //   bsModal.hide();
                  // }
          
                  // sweetalert for confirmation of delete
                  Swal.fire({
                    title: 'Konfirmasi?',
                    text: "Anda yakin akan menghapus foto ini!",
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
                      fetch(`${baseUrl}customer-service/hapus-foto-stnk/${user_id}`, {
                        method: 'DELETE',
                        headers: {
                          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                          'Content-Type': 'application/json'
                        }
                      })
                        .then(response => {
                          if (response.ok) {
                            // dt_basic.draw();

                            fetchFotoSTNK();
          
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
        const selectedCheckbox = document.querySelector('.datatables-kendaraan .dt-checkboxes:checked');

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
              fetch(`${baseUrl}kendaraan-list/${user_id}`, {
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
            // reset seluruh form (termasuk select2)
            clearFormData();

            if (validationStepper) {
              validationStepper.to(1); // Pindah ke Step 1
            }
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
 
   // Phone Number mask initialization
   const phoneMaskList = document.querySelectorAll('.phone-mask');
   // Phone Number
   if (phoneMaskList) {
     phoneMaskList.forEach(function (phoneMask) {
       phoneMask.addEventListener('input', event => {
         const cleanValue = event.target.value.replace(/\D/g, '');
         phoneMask.value = formatGeneral(cleanValue, {
           blocks: [3, 10],
           delimiters: ['-']
         });
       });
       registerCursorTracker({
         input: phoneMask,
         delimiter: '-'
       });
     });
   }

   // Tahun & CC mask initialization
   const tahunMaskList = document.querySelectorAll('.tahun-mask');
   if (tahunMaskList) {
    tahunMaskList.forEach(function (tahunMask) {
      tahunMask.addEventListener('input', event => {
         const cleanValue = event.target.value.replace(/\D/g, '');
         tahunMask.value = formatGeneral(cleanValue, {
           blocks: [4],
           delimiters: ['']
         });
       });
       registerCursorTracker({
         input: tahunMask,
         delimiter: ''
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
 
    // Picker Date initialization
    const flatpickrDate = document.querySelectorAll('.dt-date');
    if (flatpickrDate) {
     flatpickrDate.flatpickr({
       monthSelectorType: 'static',
       static: true,
       dateFormat: 'd/m/Y'
     });
    }

    const selectPemilik = $('#add-nama-pemilik'); // ID dari select box
    if (selectPemilik.length) {
      selectPemilik.on('change', function () {
        const id = $(this).val();

        // Elemen input yang akan diisi
        const inputKtp = $('#add-ktp-pemilik');
        const inputHp = $('#add-handphone-pemilik');
        const inputTlpn = $('#add-telepon-pemilik');
        const inputAlamat = $('#add-alamat-pemilik');
        const inputNama = $('#add-nama');

        if (id) {
          // Tampilkan loading atau disable sementara (opsional)
          inputKtp.val('Loading...');
          inputHp.val('Loading...');
          inputTlpn.val('Loading...');
          inputAlamat.val('Loading...');

          clearSelect('#add-jenis-pemilik');

          // Fetch data ke server
          // fetch(`${baseUrl}pemilik-list/${id}/edit`) // Sesuaikan URL dengan route di langkah 1
          fetch(`${baseUrl}customer-service/get-pemilik/${id}`) // Sesuaikan URL dengan route di langkah 1
            .then(response => {
              if (!response.ok) {
                throw new Error('Network response was not ok');
              }
              return response.json();
            })
            .then(data => {
              // console.log(data);
              // Isi form dengan data dari database
              // Pastikan key (data.xxx) sesuai dengan kolom database Anda
              const alamat = data.alamat1 + '\n' + (data.alamat2 || '');
              inputKtp.val(data.no_identitas || '-'); 
              inputHp.val(data.handphone || '-'); 
              inputTlpn.val(data.telepon || '-'); 
              inputAlamat.val(alamat || '-'); 
              inputNama.val(data.nama_pemilik || '-'); 

              setSelectValue('#add-jenis-pemilik', data.kode_jenis_pemilik, '');
            })
            .catch(error => {
              // console.error('Error fetching data:', error);
              // Reset jika error
              inputKtp.val('');
              inputHp.val('');
              inputTlpn.val('');
              inputAlamat.val('');
              inputNama.val('');
              Swal.fire({
                  icon: 'error',
                  title: 'Gagal',
                  text: 'Gagal mengambil data pemilik.',
                  customClass: { confirmButton: 'btn btn-primary' }
              });
            });
        } else {
          // Jika pilihan dikosongkan (clear), kosongkan juga inputan
          inputKtp.val('');
          inputHp.val('');
          inputTlpn.val('');
          inputAlamat.val('');
        }
      });
    }

    const wizardPropertyListing = document.querySelector('#wizard-property-listing');
    if (typeof wizardPropertyListing !== undefined && wizardPropertyListing !== null) {
      // Wizard form
      const wizardPropertyListingForm = wizardPropertyListing.querySelector('#wizard-property-listing-form');
      // Wizard steps
      const wizardPropertyListingFormStep1 = wizardPropertyListingForm.querySelector('#personal-details');
      const wizardPropertyListingFormStep2 = wizardPropertyListingForm.querySelector('#property-details');
      // Wizard next prev button
      const wizardPropertyListingNext = [].slice.call(wizardPropertyListingForm.querySelectorAll('.btn-next'));
      const wizardPropertyListingPrev = [].slice.call(wizardPropertyListingForm.querySelectorAll('.btn-prev'));

      validationStepper = new Stepper(wizardPropertyListing, {
        linear: true
      });

      // Personal Details
      const FormValidation1 = FormValidation.formValidation(wizardPropertyListingFormStep1, {
        fields: {
          // * Validate the fields here based on your requirements
          kode_pemilik: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Nama Pemilik'
              }
            }
          }
        },

        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            // Use this for enabling/changing valid/invalid class
            // eleInvalidClass: '',
            eleValidClass: '',
            rowSelector: '.form-control-validation'
          }),
          autoFocus: new FormValidation.plugins.AutoFocus(),
          submitButton: new FormValidation.plugins.SubmitButton()
        },
        init: instance => {
          instance.on('plugins.message.placed', function (e) {
            //* Move the error message out of the `input-group` element
            if (e.element.parentElement.classList.contains('input-group')) {
              e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
            }
          });
        }
      }).on('core.form.valid', function () {
        // Jump to the next step when all fields in the current step are valid
        validationStepper.next();
      });

      // Property Details
      const FormValidation2 = FormValidation.formValidation(wizardPropertyListingFormStep2, {
        fields: {
          no_polisi: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Nomor Polisi'
              }
            }
          },
          nama_distnk: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Nama STNK'
              }
              // stringLength: {
              //   min: 4,
              //   max: 10,
              //   message: 'The zip code must be more than 4 and less than 10 characters long'
              // }
            }
          },
          kode_merek: {
            validators: {
              notEmpty: {
                message: 'Silahkan Pilih Merek Kendaraan'
              }
            }
          },
          jenis_kendaraan: {
            validators: {
              notEmpty: {
                message: 'Silahkan Pilih Jenis Kendaraan'
              }
            }
          },
          kode_tipe: {
            validators: {
              notEmpty: {
                message: 'Silahkan Pilih Tipe Kendaraan'
              }
            }
          },
          kode_jenis_perseneling: {
            validators: {
              notEmpty: {
                message: 'Silahkan Pilih Perseneling'
              }
            }
          },
          kode_bahan_bakar: {
            validators: {
              notEmpty: {
                message: 'Silahkan Pilih Bahan Bakar'
              }
            }
          },
          no_rangka: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Nomor Rangka'
              }
            }
          },
          no_mesin: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Nomor Mesin'
              }
            }
          },
          tahun: {
            validators: {
              regexp: {
                regexp: /^\d+$/,              // hanya digit 0-9
                message: 'Hanya boleh angka'
              }
            }
          },
          ukuran_cc: {
            validators: {
              regexp: {
                regexp: /^\d+$/,              // hanya digit 0-9
                message: 'Hanya boleh angka'
              }
            }
          }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            // Use this for enabling/changing valid/invalid class
            // eleInvalidClass: '',
            eleValidClass: '',
            rowSelector: function (field, ele) {
              // field is the field name & ele is the field element
              switch (field) {
                case 'plAddress':
                  return '.form-control-validation';
                default:
                  return '.form-control-validation';
              }
            }
          }),
          autoFocus: new FormValidation.plugins.AutoFocus(),
          submitButton: new FormValidation.plugins.SubmitButton()
        }
      }).on('core.form.valid', function () {
        // Jump to the next step when all fields in the current step are valid
        // alert('Submitted..!!');
        // wizardPropertyListingForm.submit();
        // adding or updating user when form successfully validate
        const formData = new FormData(wizardPropertyListingForm);
        // const formDataObj = {};
  
        // Convert FormData to URL-encoded string
        // formData.forEach((value, key) => {
        //   formDataObj[key] = value;
        // });
  
        // const searchParams = new URLSearchParams();
        // for (const [key, value] of Object.entries(formDataObj)) {
        //   searchParams.append(key, value);
        // }

        // const searchParams = new URLSearchParams(formData);
  
        fetch(`${baseUrl}kendaraan-list`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            // 'Content-Type': 'application/x-www-form-urlencoded'
          },
          // body: searchParams.toString()
          body: formData
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
          const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
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

      wizardPropertyListingNext.forEach(item => {
        item.addEventListener('click', event => {
          // When click the Next button, we will validate the current step
          switch (validationStepper._currentIndex) {
            case 0:
              FormValidation1.validate();
              break;

            case 1:
              FormValidation2.validate();
              break;

            default:
              break;
          }
        });
      });

      wizardPropertyListingPrev.forEach(item => {
        item.addEventListener('click', event => {
          switch (validationStepper._currentIndex) {

            case 2:
              validationStepper.previous();
              break;

            case 1:
              validationStepper.previous();
              break;

            case 0:

            default:
              break;
          }
        });
      });

    }

    const selectMerek = $('#add-merek');
    if (selectMerek.length) {
      selectMerek.on('change', function () {
        // const id = $(this).val();
        fetchTipeKendaraan('#add-merek', '#add-jenis-kendaraan', '#add-tipe');
      });
    }

    const selectJenis = $('#add-jenis-kendaraan');
    if (selectJenis.length) {
      selectJenis.on('change', function () {
        // const id = $(this).val();
        fetchTipeKendaraan('#add-merek', '#add-jenis-kendaraan', '#add-tipe');
      });
    }
 });

  function fetchTipeKendaraan(selectorMerek, selectorJenis, selectorTipe) {
    const merekId = $(selectorMerek).val();
    const jenisId = $(selectorJenis).val();
    const selectTipe = $(selectorTipe);
    const kode_tipe = $("#kode_tipe").val();

    // Hanya request jika Merek DAN Jenis sudah dipilih
    if (merekId && jenisId) {
      
      // Disable sementara agar user tahu sedang loading
      selectTipe.prop('disabled', true);
      
      // Gunakan helper clearSelect yg sudah ada di kode Anda untuk reset opsi lama
      clearSelect('#add-tipe', { keepOptions: false }); 

      // Fetch data
      fetch(`${baseUrl}customer-service/get-tipe-kendaraan?merek_id=${merekId}&jenis_id=${jenisId}`)
        .then(response => {
          if (!response.ok) throw new Error('Gagal mengambil data');
          return response.json();
        })
        .then(data => {
          // console.log(data);
          // Tambahkan opsi default
          const defaultOption = new Option('Pilih Tipe Kendaraan', '', true, true);
          selectTipe.append(defaultOption).trigger('change');

          // Loop data dari server dan masukkan ke select
          data.forEach(item => {
            // Cek apakah kode_bahan saat ini sama dengan yang dicari
            var isSelected = (item.kode_tipe == kode_tipe);

            // Pastikan key 'item.id' dan 'item.nama_tipe' sesuai return Controller
            const newOption = new Option(item.nama_tipe, item.kode_tipe, false, isSelected);
            selectTipe.append(newOption);
          });

          // Refresh Select2 agar opsi baru muncul
          selectTipe.trigger('change');
          selectTipe.prop('disabled', false); // Aktifkan kembali
        })
        .catch(error => {
          console.error('Error:', error);
          selectTipe.prop('disabled', false);
        });

    } else {
      // Jika salah satu kosong, reset Tipe menjadi kosong
      clearSelect('#add-tipe');
    }
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
   const form = document.getElementById('wizard-property-listing-form');
   // reset input/textarea standar
   form?.reset?.();
 
   // kosongkan select (Select2)
   clearSelect('#add-nama-pemilik');
   clearSelect('#add-merek');
   clearSelect('#add-jenis-kendaraan');
   clearSelect('#add-tipe');
   clearSelect('#add-perseneling');
   clearSelect('#add-bahan-bakar');

   $('#add-nama-pemilik').addClass('is-invalid');
   $('#add-no-polisi').addClass('is-invalid');
   $('#add-nama').addClass('is-invalid');
   $('#add-merek').addClass('is-invalid');
   $('#add-jenis-kendaraan').addClass('is-invalid');
   $('#add-tipe').addClass('is-invalid');
   $('#add-perseneling').addClass('is-invalid');
   $('#add-bahan-bakar').addClass('is-invalid');
   $('#add-rangka').addClass('is-invalid');
   $('#add-mesin').addClass('is-invalid');
 
   // bersihkan error jQuery/FormValidation (kalau ada)
   // try {
   //   const fv = FormValidation && FormValidation.utils && FormValidation.utils closest ? null : null;
   // } catch (e) {}
 }

  function fetchFotoSTNK(id) 
  {
    let params = {
      id: id
    };

    // Bersihkan parameter kosong agar URL tidak terlalu panjang
    let queryString = $.param(params);

    fetch(`${baseUrl}customer-service/get-foto-stnk?` + queryString)
    .then(response => {
      if (!response.ok) throw new Error('Gagal mengambil data');
      return response.json();
    })
    .then(data => {

      // Loop Gambar
      const photoContainer = document.getElementById('stnk-container'); // Pastikan elemen dengan ID ini ada di HTML Anda

      if (photoContainer) {
        // 1. Kosongkan isi container sebelum mengisinya dengan data baru
        photoContainer.innerHTML = ''; 

        // 2. Cek apakah ada data foto dari respon AJAX
        if (data && data.length > 0) {

          // 3. Lakukan perulangan untuk setiap foto
          data.forEach(photo => {
            
            // Tentukan apakah menampilkan gambar Base64 atau Placeholder abu-abu
            let imageElement = '';
            let downloadButton = '';

            if (photo.photo_panel_base64) {
              imageElement = `<img src="data:image/jpeg;base64,${photo.photo_panel_base64}" class="card-img-top" alt="${photo.nama_panel}" style="height: 150px; object-fit: cover;">`;

              downloadButton = `
                <a href="data:image/jpeg;base64,${photo.photo_panel_base64}" 
                  download="${photo.nama_panel}" 
                  class="btn btn-success btn-sm position-absolute rounded-circle" 
                  style="top: -10px; right: 25px; width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center; text-decoration: none; z-index: 10;">
                  <i class="icon-base ri ri-download-2-line"></i>
                </a>
              `;
            } else {
              imageElement = `
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                  <span class="text-muted"><i class="icon-base ri ri-delete-bin-7-line"></i></span>
                </div>
              `;
            }

            // Susun struktur HTML Card persis seperti View Blade sebelumnya
            const cardHTML = `
              <div class="col-12">
                <div class="card position-relative border">
                  
                  <button type="button" class="btn btn-danger btn-sm position-absolute rounded-circle delete-foto" 
                      data-id="${photo.id}"
                      style="top: -10px; right: -10px; width: 30px; height: 30px; padding: 0; line-height: 1;">
                    <i class="icon-base ri ri-delete-bin-7-line"></i>
                  </button>

                  ${downloadButton}

                  ${imageElement}

                  <div class="card-body text-center p-2">
                    <h6 class="card-title mb-0">${photo.nama_panel}</h6>
                  </div>

                </div>
              </div>
            `;

            // Masukkan HTML Card ke dalam Container
            photoContainer.insertAdjacentHTML('beforeend', cardHTML);
          });

        } else {
          // Tampilan jika tidak ada foto sama sekali di database
          photoContainer.innerHTML = '';
          // photoContainer.innerHTML = `
          //   <div class="col-12 text-center py-4">
          //     <span class="text-muted">Tidak ada foto untuk SPK ini.</span>
          //   </div>
          // `;
        }
      }
    })
    .catch(error => {
      console.error('Error:', error);
    });
  }
 