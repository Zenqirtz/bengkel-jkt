/**
 * Page Data management
 */

 'use strict';

 // Datatable (js)
 document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd, isEdit, isDelete;
 
   // Variable declaration for table
   const dt_basic_table = document.querySelector('.datatables-spk'),
    //  addRoleModal = document.getElementById('addRoleModal'),
     uploadRoleModal = document.getElementById('uploadRoleModal'),
    //  statusObj = {
    //   '01': { class: 'text-bg-success' },
    //   '02': { class: 'text-bg-info' },
    // };
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
         url: baseUrl + 'foto-pekerjaan-list',
         data: function (d) {
            // Ambil data dari input form modal dan masukkan ke parameter request
            d.kode_spk = $('#filter-nomor-spk').val();
            d.no_polisi = $('#filter-no-polisi').val();
            d.tgl_masuk_awal = $('#filter-tgl-spk-awal').val();
            d.tgl_masuk_akhir = $('#filter-tgl-spk-akhir').val();
            d.nama_pemilik = $('#filter-nama-pemilik').val();
            d.tipe_kendaraan = $('#filter-tipe-kendaraan').val();
            // d.nama_pelanggan = $('#filter-nama-pelanggan').val();
            // d.no_polis = $('#filter-no-polis').val();
            // d.kode_claim = $('#filter-no-klaim').val();
            // d.status_spk = $('#filter-status-spk').val(); // Pastikan value select2 terambil
            d.status = $('#filter-status').val();
            d.tipe = 'spk';
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
          { data: 'photo', className: 'text-center' },
          // { data: 'tgl_batal' },
          // { data: 'tgl_turun_lapangan' },
          // { data: 'tgl_finishing1' },
          // { data: 'tgl_keluar' },
          // { data: 'status_spk' },
          // { data: 'no_polis' },
          // { data: 'kode_claim' },
          // { data: 'action' }
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
         {
          targets: -1,
          orderable: false,
          // responsivePriority: 2
          render: function (data, type, full, meta) {
            if(data == '1') {
              return `<span class="badge badge-center text-bg-success"><i class="icon-base ri ri-check-line"></i></span>`;
              // return `<button class="btn btn-xs btn-icon btn-success download-file" data-id="${full['id']}" data-tipe="photo" title="Download"><i class="icon-base ri ri-check-line icon-22px"></i></button>`;
            } else {
              return `<span class="badge badge-center text-bg-danger"><i class="icon-base ri ri-close-line"></i></span>`;
            }
          }
         },
        //  {
        //    // Actions
        //    //  title: 'Actions',
        //    targets: -1,
        //    searchable: false,
        //    orderable: false,
        //    visible: false,
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

    const btnEditSelected = document.querySelector('.lihat-foto');
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

          clearFormData();

          // 1. Buka Modal secara manual
          const modalInstance = new bootstrap.Modal(uploadRoleModal);
          modalInstance.show();

          $(".btn-submit").hide();
          $(".filter-file-photo").hide();
          $(".filter-photo-container").show();

          // get data
          fetch(`${baseUrl}foto-pekerjaan-list/${user_id}/edit`)
          .then(response => response.json())
          .then(data => {
            // document.getElementById('user_id').value = data.id;
            document.getElementById('add-nomor-spk').value = data.kode_spk;
            document.getElementById('add-tanggal-spk').value = data.tgl_masuk;
            document.getElementById('add-nama-pemilik').value = data.pemilik;
            document.getElementById('add-nama-pelanggan').value = data.nama_pelanggan;
            document.getElementById('add-tahun').value = data.tahun;
            document.getElementById('add-nomor-polisi').value = data.no_polisi;
            document.getElementById('add-tipe-kendaraan').value = data.merek_tipe;
            document.getElementById('add-jenis-kendaraan').value = data.nama_jenis;
            document.getElementById('add-warna').value = data.warna;

            fetchFotoPekerjaan();

            // Delete Record
            document.addEventListener('click', function (e) {
              if (e.target.closest('.delete-foto')) {
                if (isDelete) {
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
                      fetch(`${baseUrl}foto-pekerjaan-list/${user_id}`, {
                        method: 'DELETE',
                        headers: {
                          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                          'Content-Type': 'application/json'
                        }
                      })
                        .then(response => {
                          if (response.ok) {
                            // dt_basic.draw();

                            fetchFotoPekerjaan();
          
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

          $(document).on('click', '#btn-download-all', function() {
            // Cari semua tag <a> yang memiliki atribut 'download' di dalam container foto
            const downloadLinks = document.querySelectorAll('#photo-container a[download]');
        
            if (downloadLinks.length === 0) {
                Swal.fire('Info', 'Tidak ada gambar valid yang bisa didownload.', 'info');
                return;
            }
        
            // Tampilkan notifikasi agar user tahu proses sedang berjalan
            // (Bisa pakai Toast/SweetAlert/biasa)
            // console.log(`Memulai unduhan ${downloadLinks.length} gambar...`);
        
            // Lakukan perulangan untuk menekan tombol download satu per satu
            downloadLinks.forEach((link, index) => {
                // Beri jeda 500ms (setengah detik) antar unduhan
                // Ini SANGAT PENTING agar browser tidak memblokir "Multiple Downloads"
                setTimeout(() => {
                    link.click();
                }, index * 500); 
            });
          });

        }
      });
    }

    // changing the title
    const uploadBtn = document.querySelector('.upload-foto');
    if (uploadBtn) {
      uploadBtn.addEventListener('click', function () {
        if (!isAdd) {
          Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Anda tidak memiliki izin untuk tambah data.',
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

          clearFormData();

          // 1. Buka Modal secara manual
          const modalInstance = new bootstrap.Modal(uploadRoleModal);
          modalInstance.show();

          $(".btn-submit").show();
          $(".filter-file-photo").show();
          $(".filter-photo-container").hide();

          // get data
          fetch(`${baseUrl}foto-pekerjaan-list/${user_id}/edit`)
          .then(response => response.json())
          .then(data => {
            document.getElementById('spk_id').value = user_id;
            // document.getElementById('user_id').value = data.id;
            document.getElementById('add-nomor-spk').value = data.kode_spk;
            document.getElementById('add-tanggal-spk').value = data.tgl_masuk;
            document.getElementById('add-nama-pemilik').value = data.pemilik;
            document.getElementById('add-nama-pelanggan').value = data.nama_pelanggan;
            document.getElementById('add-tahun').value = data.tahun;
            document.getElementById('add-nomor-polisi').value = data.no_polisi;
            document.getElementById('add-tipe-kendaraan').value = data.merek_tipe;
            document.getElementById('add-jenis-kendaraan').value = data.nama_jenis;
            document.getElementById('add-warna').value = data.warna;
          });

        }
      });
    }

    // user form validation
    const uploadNewDataForm = document.getElementById('uploadNewDataForm');
    if (uploadNewDataForm) {
      const fv = FormValidation.formValidation(uploadNewDataForm, {
        fields: {
          photo: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input File Foto'
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
        // uploadNewDataForm.submit();

        // adding or updating user when form successfully validate
        const form = uploadNewDataForm;
        const fd = new FormData(form);
  
        // endpoint: POST (create) atau PUT/PATCH (update)
        // const id = document.getElementById('user_id').value; //(fd.get('user_id') || '').trim();
        // const method = id ? 'POST' : 'POST';
        // const url = id ? `${baseUrl}profile-list/${id}` : `${baseUrl}profile-list`;

        // // jika update dengan method spoofing (Laravel)
        // if (id) fd.append('_method', 'PUT');

        PleaseWaitPage();

        fetch(`${baseUrl}foto-pekerjaan-list`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            // 'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: fd
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.text();
        })
        .then(result => {
          const { status, message, errors } = JSON.parse(result);

          if (document.querySelector(`.notiflix-loading`)) {
            Loading.remove();
          }

          // Hide offcanvas
          const offcanvasInstance = bootstrap.Modal.getInstance(uploadRoleModal);
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

          if (document.querySelector(`.notiflix-loading`)) {
            Loading.remove();
          }

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

  const invoiceItemPriceList = document.querySelectorAll('.invoice-price');
  if (invoiceItemPriceList) {
    invoiceItemPriceList.forEach(function (invoiceItemPrice) {
      if (invoiceItemPrice) {
        invoiceItemPrice.addEventListener('input', event => {
          invoiceItemPrice.value = formatNumeral(event.target.value, {
            delimiter: ',',
            numeral: true
          });
        });
      }
    });
  }

  const invoiceItemPriceSparepartList = document.querySelectorAll('.invoice-price-sparepart');
  if (invoiceItemPriceSparepartList) {
    invoiceItemPriceSparepartList.forEach(function (invoiceItemPriceSparepart) {
      if (invoiceItemPriceSparepart) {
        invoiceItemPriceSparepart.addEventListener('input', event => {
          invoiceItemPriceSparepart.value = formatNumeral(event.target.value, {
            delimiter: ',',
            numeral: true
          });

          hitungEstimasiSparepart();
        });
      }
    });
  }

  const invoiceDiskonList = document.querySelectorAll('.invoice-diskon');
  if (invoiceDiskonList) {
    invoiceDiskonList.forEach(function (invoiceDiskon) {
      if (invoiceDiskon) {
        invoiceDiskon.addEventListener('input', event => {
          invoiceDiskon.value = formatNumeral(event.target.value, {
            delimiter: ',',
            numeral: true
          });

          hitungTotalPerbaikan();
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
   const form = document.getElementById('uploadNewDataForm');
   // reset input/textarea standar
   form?.reset?.();
 
   // kosongkan select (Select2)
  //  clearSelect('#add-estimator');
  //  clearSelect('#add-pelanggan');
  //  clearSelect('#add-surveyor');

  // $('#add-estimator').addClass('is-invalid');
  // $('#add-pelanggan').addClass('is-invalid');
  // $('#add-surveyor').addClass('is-invalid');
  // $('#add-tanggal-survey').addClass('is-invalid');
 
   // bersihkan error jQuery/FormValidation (kalau ada)
   // try {
   //   const fv = FormValidation && FormValidation.utils && FormValidation.utils closest ? null : null;
   // } catch (e) {}
 }

  function fetchFotoPekerjaan() 
  {
    const kode_cabang = $("#kode_cabang").val();
    const kode_spk = $("#add-nomor-spk").val();
    let params = {
      kode_cabang: kode_cabang,
      kode_spk: kode_spk
    };

    // Bersihkan parameter kosong agar URL tidak terlalu panjang
    let queryString = $.param(params);

    fetch(`${baseUrl}administrasi/get-foto-pekerjaan?` + queryString)
    .then(response => {
      if (!response.ok) throw new Error('Gagal mengambil data');
      return response.json();
    })
    .then(data => {

      // Loop Gambar
      const photoContainer = document.getElementById('photo-container'); // Pastikan elemen dengan ID ini ada di HTML Anda
      const btnDownloadAll = document.getElementById('btn-download-all');

      if (photoContainer) {
        // 1. Kosongkan isi container sebelum mengisinya dengan data baru
        photoContainer.innerHTML = ''; 

        // 2. Cek apakah ada data foto dari respon AJAX
        if (data && data.length > 0) {

          if (btnDownloadAll) {
            btnDownloadAll.classList.remove('d-none');
          }
          
          // 3. Lakukan perulangan untuk setiap foto
          data.forEach(photo => {
            
            // Tentukan apakah menampilkan gambar Base64 atau Placeholder abu-abu
            let imageElement = '';
            let downloadButton = '';

            if (photo.photo_panel_base64) {
              imageElement = `<img src="data:image/jpeg;base64,${photo.photo_panel_base64}" class="card-img-top" alt="${photo.nama_panel}" style="height: 200px; object-fit: cover;">`;

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
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                  <span class="text-muted"><i class="icon-base ri ri-delete-bin-7-line"></i></span>
                </div>
              `;
            }

            // Susun struktur HTML Card persis seperti View Blade sebelumnya
            const cardHTML = `
              <div class="col-md-4 col-sm-6">
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
          if (btnDownloadAll) {
            btnDownloadAll.classList.add('d-none');
          }

          // Tampilan jika tidak ada foto sama sekali di database
          photoContainer.innerHTML = `
            <div class="col-12 text-center py-4">
              <span class="text-muted">Tidak ada foto untuk SPK ini.</span>
            </div>
          `;
        }
      }
    })
    .catch(error => {
      console.error('Error:', error);
    });
  }