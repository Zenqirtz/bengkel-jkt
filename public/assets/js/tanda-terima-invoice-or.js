/**
 * Page Data management
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd, isEdit, isDelete;
  let validationStepper;

  // Variable declaration for table
  const dt_basic_table = document.querySelector('.datatables-spk'),
    addRoleModal = document.getElementById('addRoleModal'),
    rowDataMap = new Map(), // ← TAMBAH INI
    statusObj = {
      '09': { class: 'text-bg-danger' },
      10: { class: 'text-bg-danger' },
      11: { class: 'text-bg-danger' }
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
        url: baseUrl + 'tanda-terima-invoice-or-list',
        data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.kode_spk = $('#filter-nomor-spk').val();
          d.no_polisi = $('#filter-no-polisi').val();
          d.tgl_masuk_awal = $('#filter-tgl-spk-awal').val();
          d.tgl_masuk_akhir = $('#filter-tgl-spk-akhir').val();
          d.tgl_invoice_awal = $('#filter-tgl-invoice-awal').val();
          d.tgl_invoice_akhir = $('#filter-tgl-invoice-akhir').val();
          d.nama_pelanggan = $('#filter-nama-pelanggan').val();
          d.nama_pemilik = $('#filter-nama-pemilik').val();
          d.no_invoice = $('#filter-no-invoice').val();
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
        { data: 'tgl_invoice' },
        { data: 'no_invoice' },
        { data: 'tgl_masuk' },
        { data: 'kode_spk' },
        { data: 'keterangan' },
        { data: 'no_polisi' },
        { data: 'nama_tipe' },
        { data: 'pemilik' },
        { data: 'nama_pelanggan' },
        { data: 'kode_claim' },
        { data: 'total_or' }
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
          responsivePriority: 2,
          checkboxes: true,
          render: function (data, type, full, meta) {
            rowDataMap.set(String(full.id), full);
            return `<input type="checkbox" class="dt-checkboxes form-check-input"
            value="${full.id}"
            data-pemilik="${full.pemilik}">`;
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
          targets: 6,
          // responsivePriority: 6,
          render: function (data, type, full, meta) {
            const status = full['kode_status_spk'];
            const badgeClass = statusObj[status] ? statusObj[status].class : 'text-bg-success';

            return '<span class="badge rounded-pill ' + badgeClass + '" text-capitalized>' + data + '</span>';
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
      order: [[2, 'desc']],
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

        // ← TAMBAH INI
        const sa = document.getElementById('selectAllInvoice');
        if (sa) {
          sa.addEventListener('change', function () {
            document.querySelectorAll('.datatables-spk .dt-checkboxes').forEach(chk => {
              chk.checked = this.checked;
            });
          });
        }
        dt_basic.on('draw', function () {
          const sa = document.getElementById('selectAllInvoice');
          if (sa) sa.checked = false;
        });
      }
    });

    // Batasi hanya 1 checkbox yang boleh dipilih
    // document.addEventListener('click', function (e) {
    //   const chk = e.target.closest('.dt-checkboxes');
    //   if (chk) {
    //     if (chk.checked) {
    //         $('.dt-checkboxes').not(chk).prop('checked', false);
    //     }
    //   }
    // });

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
              // delete the data
              fetch(`${baseUrl}tanda-terima-invoice-or-list/${user_id}`, {
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
        if (isEdit) {
          const editBtn = e.target.closest('.edit-record');
          const user_id = editBtn.dataset.id;
          const dtrModal = document.querySelector('.dtr-bs-modal.show');

          // hide responsive modal in small screen
          if (dtrModal) {
            const bsModal = bootstrap.Modal.getInstance(dtrModal);
            bsModal.hide();
          }

          if (validationStepper) {
            validationStepper.to(1); // Pindah ke Step 1
          }

          // get data
          fetch(`${baseUrl}tanda-terima-invoice-or-list/${user_id}/edit`)
            .then(response => response.json())
            .then(data => {
              document.getElementById('user_id').value = data.id;
              document.getElementById('add-nomor-spk').value = data.kode_spk;
              document.getElementById('add-no-polis').value = data.no_polis;
              document.getElementById('add-no-klaim').value = data.kode_claim;
              document.getElementById('add-tertanggung').value = data.tertanggung;
              document.getElementById('add-catatan-khusus').value = data.catatan_khusus;
              document.getElementById('add-jenis-perbaikan').value = data.jenis_perbaikan;

              setSelectValue('#add-status-spk', data.kode_status_spk, '');
              setSelectValue('#add-jenis-asuransi', data.kode_jenis_pelanggan, '');
              setSelectValue('#add-pelanggan', data.kode_pelanggan, '');
              setSelectValue('#add-perantara', data.kode_perantara, '');
              setSelectValue('#add-marketing', data.kode_marketing, '');
              setSelectValue('#add-jenis-polis', data.kode_jenis_polis, '');

              setSelectValue('#add-nopolisi', data.no_polisi, '');
            });
        } else {
          // Hide offcanvas
          const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
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

          // reset seluruh form (termasuk select2)
          clearFormData();

          // 2. Isi data ke dalam form modal
          if (validationStepper) {
            validationStepper.to(1); // Pindah ke Step 1
          }

          // get data
          fetch(`${baseUrl}tanda-terima-invoice-or-list/${user_id}/edit`)
            .then(response => {
              if (!response.ok) {
                throw new Error('Network response was not ok');
              }
              return response.text();
            })
            .then(result => {
              const { status, message, data } = JSON.parse(result);

              if (status) {
                // Buka Modal secara manual
                const modalInstance = new bootstrap.Modal(addRoleModal);
                modalInstance.show();

                document.getElementById('user_id').value = data.id;
                document.getElementById('add-nomor-spk').value = data.kode_spk;
                document.getElementById('add-nomor-polisi').value = data.no_polisi;
                document.getElementById('add-nama-pemilik').value = data.pemilik;
                document.getElementById('add-merek-kendaraan').value = data.nama_merek;
                document.getElementById('add-tipe-kendaraan').value = data.nama_tipe;
                document.getElementById('add-jenis-kendaraan').value = data.nama_jenis;
                document.getElementById('add-nama-pelanggan').value = data.nama_pelanggan;

                document.getElementById('add-nomor-invoice').value = data.no_invoice;
                document.getElementById('add-tgl-invoice').value = data.tgl_invoice;
                document.getElementById('add-nilai').value = data.nilai_or;
                document.getElementById('add-nilai2').value = data.total_or;

                setSelectValue('#add-or-fee', data.ada_or, '');
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

    // const btnEditSelected2 = document.querySelector('.cetak-invoice');
    // if (btnEditSelected2) {
    //   btnEditSelected2.addEventListener('click', function () {
    //     // Cek Izin Edit
    //     if (!isEdit) {
    //       Swal.fire({
    //         icon: 'error',
    //         title: 'Akses Ditolak',
    //         text: 'Anda tidak memiliki izin untuk mengubah data.',
    //         customClass: { confirmButton: 'btn btn-primary' }
    //       });
    //       return;
    //     }

    //     // Cari checkbox yang tercentang di dalam tabel
    //     const selectedCheckbox = document.querySelector('.datatables-spk .dt-checkboxes:checked');

    //     if (!selectedCheckbox) {
    //       // Jika tidak ada yang dipilih
    //       Swal.fire({
    //         icon: 'warning',
    //         title: 'Peringatan',
    //         text: 'Silahkan pilih (checklist) data SPK pada tabel terlebih dahulu!',
    //         customClass: { confirmButton: 'btn btn-primary' }
    //       });
    //     } else {
    //       // Jika ada yang dipilih
    //       const user_id = selectedCheckbox.value;

    //       // get data
    //       fetch(`${baseUrl}tanda-terima-invoice-or-list/${user_id}/edit`)
    //       .then(response => {
    //         if (!response.ok) {
    //           throw new Error('Network response was not ok');
    //         }
    //         return response.text();
    //       })
    //       .then(result => {
    //         const { status, message, data } = JSON.parse(result);

    //         if(status) {

    //           let params = {
    //             id: data.id
    //           };

    //           // Bersihkan parameter kosong agar URL tidak terlalu panjang
    //           let queryString = $.param(params);

    //           // Redirect window untuk download file
    //           // Pastikan route URL sesuai dengan konfigurasi route Anda
    //           // window.location.href = `${baseUrl}laporan-kendaraan/print?` + queryString;
    //           const printUrl = `${baseUrl}customer-service/cetak-tanda-terima-invoice-or?` + queryString;
    //           window.open(printUrl, '_blank');

    //         } else {
    //           // sweetalert
    //           Swal.fire({
    //             icon: 'warning',
    //             title: `Peringatan!`,
    //             text: `${message}`,
    //             customClass: {
    //               confirmButton: 'btn btn-success'
    //             }
    //           });
    //         }
    //       })
    //       .catch(err => {
    //         Swal.fire({
    //           title: 'Error!',
    //           text: 'Gagal cek data SPK',
    //           icon: 'error',
    //           customClass: {
    //             confirmButton: 'btn btn-success'
    //           }
    //         });
    //       });
    //     }
    //   });
    // }
    const btnEditSelected2 = document.querySelector('.cetak-invoice');
    if (btnEditSelected2) {
      btnEditSelected2.addEventListener('click', function () {
        if (!isEdit) {
          Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Anda tidak memiliki izin untuk mengubah data.',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        const selectedCheckboxes = document.querySelectorAll('.datatables-spk .dt-checkboxes:checked');

        if (selectedCheckboxes.length === 0) {
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silahkan pilih (checklist) data SPK pada tabel terlebih dahulu!',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        const ids = Array.from(selectedCheckboxes).map(chk => chk.value);

        // Ambil pemilik dari data-pemilik attribute (lebih reliable dari Map)
        const pemilikList = Array.from(selectedCheckboxes).map(chk => chk.dataset.pemilik);
        const uniquePemilik = [...new Set(pemilikList)];

        if (uniquePemilik.length > 1) {
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            html: `Data yang dipilih memiliki nama pemilik berbeda:<br><br>
       <b>${uniquePemilik.join('<br>')}</b><br><br>
       Silahkan pilih data dengan pemilik yang sama!`,
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        // Semua pemilik sama, lanjut cetak
        const queryString = ids.map(id => `id[]=${id}`).join('&');
        const printUrl = `${baseUrl}customer-service/cetak-tanda-terima-invoice-or?` + queryString;
        window.open(printUrl, '_blank');
      });
    }

    // changing the title
    const addNewBtn = document.querySelector('.add-new');
    if (addNewBtn) {
      addNewBtn.addEventListener('click', function () {
        if (isAdd) {
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

    // Handle Filter Search
    const formFilter = document.getElementById('formFilterSpk');
    if (formFilter) {
      formFilter.addEventListener('submit', function (e) {
        e.preventDefault(); // Mencegah reload halaman

        // Reload DataTables (ini akan otomatis memanggil fungsi ajax.data di atas)
        dt_basic.draw();

        // (Opsional) Tutup modal setelah klik cari
        const modalEl = document.getElementById('filterRoleModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) {
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
        allowClear: true, // agar placeholder tampil saat kosong
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
        kode_spk: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Nomor SPK'
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
        no_invoice: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Nomor Invoice'
            }
          }
        },
        tgl_invoice: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Tanggal Invoice'
            }
          }
        },
        nilai: {
          validators: {
            // notEmpty: {
            //   message: 'Silahkan Input Tarif PPN'
            // },
            numeric: {
              decimalSeparator: '.', // gunakan titik sebagai desimal
              thousandsSeparator: ',', // kosongkan jika tanpa pemisah ribuan
              message: 'Masukkan angka desimal yang valid'
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
      const formDataObj = {};

      // Convert FormData to URL-encoded string
      formData.forEach((value, key) => {
        formDataObj[key] = value;
      });

      const searchParams = new URLSearchParams();
      for (const [key, value] of Object.entries(formDataObj)) {
        searchParams.append(key, value);
      }

      fetch(`${baseUrl}tanda-terima-invoice-or-list`, {
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

          if (status) {
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
    if (val === '' || val === null) {
      $el.val(null);
    } else {
      ensureOption(String(val), textIfMissing);
      $el.val(String(val));
    }
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
  //  clearSelect('#add-nopolisi');

  $('#add-nomor-invoice').addClass('is-invalid');
  $('#add-tgl-invoice').addClass('is-invalid');

  // bersihkan error jQuery/FormValidation (kalau ada)
  // try {
  //   const fv = FormValidation && FormValidation.utils && FormValidation.utils closest ? null : null;
  // } catch (e) {}
}
