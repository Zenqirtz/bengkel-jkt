/**
 * Page Data management
 */

 'use strict';

 // Datatable (js)
 document.addEventListener('DOMContentLoaded', function (e) {
    let isAdd, isEdit, isDelete, fvDetail;
    let selected_no_transaksi = '';
    let selected_kode_cabang = '';
    let selected_tipe = 'detail';
    let rowToEdit = null;
    let dt_detail;
 
   // Variable declaration for table
   const dt_basic_table = document.querySelector('.datatables-bukti-penerimaan'),
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
        url: baseUrl + 'bukti-penerimaan-list',
        data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.no_transaksi = $('#filter-nomor-transaksi').val();
          d.memo = $('#filter-memo').val();
          d.tanggal_awal = $('#filter-tanggal-awal').val();
          d.tanggal_akhir = $('#filter-tanggal-akhir').val();
          d.no_voucher = $('#filter-nomor-voucher').val();
          d.no_ch_bg = $('#filter-nomor-chbg').val();
          d.kode_kategori = $('#filter-kode-kategori').val();
          d.kode_bank = $('#filter-kode-bank').val();
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
        { data: 'tanggal_transaksi' },
        { data: 'no_transaksi' },
        { data: 'kategori' },
        { data: 'no_voucher' },
        { data: 'nama_pelanggan' },
        { data: 'memo' },
        { data: 'nama_cabang' },
        { data: 'nama_bank' },
        { data: 'tanggal_ch_bg' },
        { data: 'no_ch_bg' },
        { data: 'tanggal_kliring' },
        { data: 'no_voucher_cabang' },
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
        {
          visible: false,
          targets: 8,
        },
        // {
        //   // Actions
        //   targets: -1,
        //   //  title: 'Actions',
        //   searchable: false,
        //   orderable: false,
        //   render: function (data, type, full, meta) {
        //     return (
        //       '<div class="d-flex align-items-center gap-4">' +
        //       `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill edit-record" data-id="${full['id']}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAdd" title="Ubah"><i class="icon-base ri ri-edit-box-line icon-22px"></i></button>` +
        //       `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill delete-record" data-id="${full['id']}" title="Hapus"><i class="icon-base ri ri-delete-bin-7-line icon-22px"></i></button>` +
        //       '</div>'
        //     );
        //   }
        // }
      ],
      order: [[2, 'desc']],
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
      }
    });

    const dt_detail_table = document.querySelector('.datatables-detail');
    if (dt_detail_table) {
      dt_detail = new DataTable(dt_detail_table, {
        searching: false,  // Opsi ini akan menghilangkan input cari
        ordering: false,    // Opsi lain tetap bisa jalan
        paging: false,
        info: false,
        processing: true,
        serverSide: false,
        destroy: true, // Fix Error reinitialise
        ajax: {
          url: baseUrl + 'bukti-penerimaan-list',
          data: function (d) {
            // Ambil data dari input form modal dan masukkan ke parameter request
            d.no_transaksi = selected_no_transaksi;
            d.kode_cabang = selected_kode_cabang;
            d.tipe = selected_tipe;
          }
        },
        columns: [
          // columns according to JSON
          { data: 'id', width: '20px' },
          { data: 'uraian' },
          { data: 'jumlah', className: 'text-end' },
          { data: 'action', width: '20px' }
        ],
        columnDefs: [
          {
            searchable: false,
            orderable: false,
            visible: true,
            targets: 0,
            render: function (data, type, full, meta) {
              return `<span>${full.fake_id}</span>`;
            }
          },
          {
            targets: 1,
            render: function (data, type, full, meta) {
              return `
                ${data} 
                <input type="hidden" name="detail[${full.id}][uraian]" value="${full.uraian}" />
              `; 
            }
          },
          {
            targets: 2,
            render: function (data, type, full, meta) {
              return `
                ${data} 
                <input type="hidden" name="detail[${full.id}][jumlah]" class="add-jumlah-det" value="${full.jumlah}" />
              `; 
            }
          },
          {
            // Actions
            //  title: 'Actions',
            targets: -1,
            searchable: false,
            orderable: false,
            visible: true,
            render: function (data, type, full, meta) {
              return (
                '<div class="d-flex align-items-center gap-4">' +
                `<button type="button" class="btn btn-icon btn-text-secondary btn-sm rounded-pill edit-detail" data-tipe="detail" data-id="${full['id']}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDet1" title="Ubah"><i class="icon-base ri ri-edit-box-line icon-22px"></i></button>` +
                `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill delete-detail" data-tipe="detail" data-id="${full['id']}" title="Hapus"><i class="icon-base ri ri-delete-bin-7-line icon-22px"></i></button>` +
                '</div>'
              );
            }
          }
        ],
        drawCallback: function() {
          hitungTotalUraian();
        },
      });
    }

    // Batasi hanya 1 checkbox yang boleh dipilih
    document.addEventListener('click', function (e) {
      const chk = e.target.closest('.datatables-bukti-penerimaan .dt-checkboxes');
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
        const selectedCheckbox = document.querySelector('.datatables-bukti-penerimaan .dt-checkboxes:checked');

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
          fetch(`${baseUrl}bukti-penerimaan-list/${user_id}/edit`)
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

              document.getElementById('user_id').value = data.id;

              document.getElementById('add-nomor-transaksi').value = data.no_transaksi;
              document.getElementById('add-nomor-voucher').value = data.no_voucher;
              document.getElementById('add-tanggal').value = data.tanggal_transaksi;
              document.getElementById('add-nomor-chbg').value = data.no_ch_bg;
              document.getElementById('add-tanggal-chbg').value = data.tanggal_ch_bg;
              document.getElementById('add-memo').value = data.memo;
  
              setSelectValue('#add-kode-kategori', data.kode_kategori, '');
              setSelectValue('#add-kode-pelanggan', data.kode_pelanggan, '');
              setSelectValue('#add-bank-tujuan', data.kode_bank, '');
              setSelectValue('#add-bank-asal', data.kode_bank_asal, '');

              selected_no_transaksi = data.no_transaksi; // Sesuaikan dengan nama field di database/json
              selected_kode_cabang = data.kode_cabang;

              // Reload DataTables (ini akan otomatis memanggil fungsi ajax.data di atas)
              dt_detail.ajax.reload();
  
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
              text: 'Gagal Cetak Estimasi',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
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
        const selectedCheckbox = document.querySelector('.datatables-bukti-penerimaan .dt-checkboxes:checked');

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
              fetch(`${baseUrl}bukti-penerimaan-list/${user_id}`, {
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
          document.getElementById('add-nomor-bukti-penerimaan')?.focus();
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

    // changing the title
    const addDetBtn = document.querySelector('.add-detail');
    if (addDetBtn) {
      addDetBtn.addEventListener('click', function () {
        if(isAdd) {
          // reset seluruh form (termasuk select2)
          clearFormDataDet1();
        } else {
          // Hide offcanvas
          const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet1);
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

    // Delete Record Detail
    document.addEventListener('click', function (e) {
      const editDetBtn = e.target.closest('.edit-detail');
      if (editDetBtn) {
        if(isEdit) {
          clearFormDataDet1();

          const tipe = editDetBtn.dataset.tipe;
          const user_id = editDetBtn.dataset.id;

          // 1. Ambil instance DataTable
          const tableEl = document.querySelector('.datatables-detail');
          const dt = $(tableEl).DataTable();

          // 2. Cari baris (TR) terdekat dari tombol yang diklik
          const row = $(editDetBtn).closest('tr');

          // 3. AMBIL DATA (Tambahkan .data() di akhir)
          const rowData = dt.row(row).data();

          console.log(rowData);

          rowToEdit = dt.row(row);

          document.getElementById('est_dtl1_id').value = rowData.id;
          document.getElementById('add-uraian').value = rowData.uraian;
          document.getElementById('add-jumlah').value = rowData.jumlah;

        } else {
          // Hide offcanvas
          if (tipe == "detail") {
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet1);
            offcanvasInstance && offcanvasInstance.hide();
          }
          
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

      const deleteDetBtn = e.target.closest('.delete-detail');
      if (deleteDetBtn) {
         if (isDelete) {
          const tipe = deleteDetBtn.dataset.tipe;
          if (tipe == "detail") {
            // 1. Ambil instance DataTable
            const tableEl = document.querySelector('.datatables-detail');
            const dt = $(tableEl).DataTable();

            // 2. Cari baris (TR) terdekat dari tombol yang diklik
            const row = $(deleteDetBtn).closest('tr');

            // 3. Hapus baris tersebut dari DataTable
            dt.row(row).remove().draw(false);

          }

         } else {
          // Hide offcanvas
          if (tipe == "detail") {
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet1);
            offcanvasInstance && offcanvasInstance.hide();
          }

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

    const addNewDataFormDet1 = document.getElementById('addNewDataFormDet1');
    if (addNewDataFormDet1) {
      if (fvDetail) {
        fvDetail.destroy(); // Hancurkan instance lama agar tidak menumpuk
      }
      
      fvDetail = FormValidation.formValidation(addNewDataFormDet1, {
        fields: {
          uraian: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Uraian'
              }
            }
          },
          jumlah: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Jumlah'
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
        const cekID = $("#est_dtl1_id").val();
        if(cekID) {

          const oldData = rowToEdit.data();

          rowToEdit.data({
            id: oldData.id,           // Pertahankan ID lama
            fake_id: oldData.fake_id, // Pertahankan Fake ID lama

            // Update field sesuai input form terbaru
            uraian: $("#add-uraian").val(),
            jumlah: $("#add-jumlah").val(),

            action: oldData.action    // Pertahankan kolom action
          }).draw(false); // .draw(false) agar tidak mereset paging kembali ke halaman 1

          rowToEdit = null;

          const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasAddDet1);
          offcanvasInstance && offcanvasInstance.hide();
          
        } else {
          if (dt_detail) {
            const newId = 'new_' + Date.now();
            dt_detail.row.add({
                id: newId, 
                fake_id: '', 
                uraian: $("#add-uraian").val(),
                jumlah: $("#add-jumlah").val(),
                action: '' 
            }).draw(false); // .draw(false) agar tidak mereset paging kembali ke halaman 1

            // Pindah ke halaman terakhir agar baris baru terlihat (opsional)
            // dt_detail.page('last').draw(false);

            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasAddDet1);
            offcanvasInstance && offcanvasInstance.hide();
          }
        }
      });
    }

    const btnCetakSelected = document.querySelector('.cetak-record');
    if (btnCetakSelected) {
      btnCetakSelected.addEventListener('click', function () {
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
        const selectedCheckbox = document.querySelector('.datatables-bukti-penerimaan .dt-checkboxes:checked');

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

          PleaseWaitPage();

          // get data
          fetch(`${baseUrl}bukti-penerimaan-list/${user_id}/edit`)
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.text();
          })
          .then(result => {
            const { status, message, data } = JSON.parse(result);

            if (document.querySelector(`.notiflix-loading`)) {
              Loading.remove();
            }
  
            if(status) {
             
              let params = {
                id: data.id
              };
    
              // Bersihkan parameter kosong agar URL tidak terlalu panjang
              let queryString = $.param(params);
              
              // Redirect window untuk download file
              // Pastikan route URL sesuai dengan konfigurasi route Anda
              const printUrl = `${baseUrl}keuangan/cetak-bukti-penerimaan?` + queryString;
              window.open(printUrl, '_blank');
  
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
            if (document.querySelector(`.notiflix-loading`)) {
              Loading.remove();
            }
            
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
        tanggal_transaksi: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Tanggal'
            }
          }
        },
        kode_kategori: {
          validators: {
            notEmpty: {
              message: 'Silahkan Pilih Kategori'
            }
          }
        },
        kode_bank: {
          validators: {
            notEmpty: {
              message: 'Silahkan Pilih Bank Tujuan'
            }
          }
        },
        kode_bank_asal: {
          validators: {
            notEmpty: {
              message: 'Silahkan Pilih Bank Asal'
            }
          }
        },
        memo: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Memo'
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

      PleaseWaitPage();

      fetch(`${baseUrl}bukti-penerimaan-list`, {
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

        if (document.querySelector(`.notiflix-loading`)) {
          Loading.remove();
        }

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

        if (document.querySelector(`.notiflix-loading`)) {
          Loading.remove();
        }
        
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
  // Phone Number
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

  // Decimal Only
  const decimalMaskList = document.querySelectorAll('.text-decimal');
  if (decimalMaskList) {
    decimalMaskList.forEach(function (decimalMask) {
      decimalMask.addEventListener('input', event => {
        
        let cleanValue = event.target.value.replace(/[^0-9.]/g, '');
        let parts = cleanValue.split('.');
        if (parts.length > 2) {
          cleanValue = parts[0] + '.' + parts.slice(1).join('');
        }

        decimalMask.value = formatGeneral(cleanValue, {
          blocks: [99],
          delimiters: ['']
        });
      });
      registerCursorTracker({
        input: decimalMask,
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
    clearSelect('#add-kode-kategori');
    clearSelect('#add-kode-pelanggan');
    clearSelect('#add-bank-tujuan');
    clearSelect('#add-bank-asal');

    $('#add-tanggal').addClass('is-invalid');
    $('#add-kode-kategori').addClass('is-invalid');
    $('#add-bank-tujuan').addClass('is-invalid');
    $('#add-bank-asal').addClass('is-invalid');
    $('#add-memo').addClass('is-invalid');

  // bersihkan error jQuery/FormValidation (kalau ada)
  // try {
  //   const fv = FormValidation && FormValidation.utils && FormValidation.utils closest ? null : null;
  // } catch (e) {}
  }

  function clearFormDataDet1() {
    const form = document.getElementById('addNewDataFormDet1');
    // reset input/textarea standar
    form?.reset?.();

    $("#est_dtl1_id").val('');

    $('#add-uraian').addClass('is-invalid');
    $('#add-jumlah').addClass('is-invalid');
  }

  function hitungTotalUraian() {
    let total = 0;

    // Loop semua elemen dengan class 'add-harga'
    $('.add-jumlah-det').each(function() {
        // Ambil value
        let val = $(this).val();

        // Bersihkan format (hapus koma) agar bisa dijumlahkan
        // Contoh: "405,000.00" menjadi 405000.00
        // Jika Anda menggunakan library masking lain, sesuaikan cara unmask-nya
        let cleanVal = val.replace(/,/g, '');

        // Konversi ke float, jika NaN (kosong) anggap 0
        let numberVal = parseFloat(cleanVal) || 0;

        total += numberVal;
    });

    // Format kembali ke tampilan angka (misal: 1,000,000.00)
    let formattedTotal = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(total);

    // Update teks di elemen footer
    $('#total-uraian').text(formattedTotal);
    $('#add-total-uraian').val(formattedTotal);
  }
 