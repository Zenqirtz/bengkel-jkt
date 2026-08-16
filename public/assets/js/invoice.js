/**
 * Page Data management
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd, isEdit, isDelete;
  let selected_kode_spk = '';
  let selected_kode_cabang = '';

  // Variable declaration for table
  const dt_basic_table = document.querySelector('.datatables-spk'),
    addRoleModal = document.getElementById('addRoleModal'),
    addModalKirimInv = document.getElementById('addModalKirimInv'),
    statusObj = {
      '09': { class: 'text-bg-danger' },
      10: { class: 'text-bg-danger' },
      11: { class: 'text-bg-danger' }
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
      searching: false, // Opsi ini akan menghilangkan input cari
      ordering: true, // Opsi lain tetap bisa jalan
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'invoice-list',
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
          d.tipe = 'estimasi';
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
        { data: 'npwp' },  // ← TAMBAH setelah nama_pelanggan
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
      const chk = e.target.closest('.datatables-spk .dt-checkboxes');
      if (chk) {
        if (chk.checked) {
          $('.dt-checkboxes').not(chk).prop('checked', false);
        }
      }
    });

    const btnEditSelected = document.querySelector('.edit-terbit-invoice');
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
          fetch(`${baseUrl}invoice-list/${user_id}/edit`)
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

                document.getElementById('user_id').value = data.kwitansi_id;
                document.getElementById('kode_spk').value = data.kode_spk;
                document.getElementById('kode_estimasi').value = data.kode_estimasi;

                document.getElementById('add-nomor-estimasi').value = data.kode_estimasi;
                document.getElementById('add-nomor-spk').value = data.kode_spk;
                document.getElementById('add-nomor-polisi').value = data.no_polisi;
                document.getElementById('add-tipe-kendaraan').value = data.merek_tipe;
                document.getElementById('add-nama-pemilik').value = data.pemilik;
                document.getElementById('add-nama-pelanggan').value = data.nama_pelanggan;
                document.getElementById('add-nomor-polis').value = data.no_polis;

                document.getElementById('add-nomor-kwitansi').value = data.kode_kwitansi;
                document.getElementById('add-tanggal-kwitansi').value = data.tgl_kwitansi;

                setSelectValue('#add-tipe-kwitansi', data.kode_tipe_kwitansi, '');

                setRadioValue('sifat_ppn', data.sifat_ppn);
                setRadioValue('sparepart_ppn', data.sparepart_ppn);
                setRadioValue('lain_ppn', data.lain_ppn);

                document.getElementById('add-persen-jasa').value = data.persen_jasa;
                document.getElementById('add-persen-bahan').value = data.persen_bahan;
                document.getElementById('add-total-perbaikan').value = data.total_perbaikan;
                document.getElementById('add-total-bahan').value = data.total_bahan;
                document.getElementById('add-total-jasa').value = data.total_jasa;
                document.getElementById('add-total-sparepart').value = data.total_sparepart;
                document.getElementById('add-total-lain').value = data.total_lain;
                document.getElementById('add-total-keseluruhan').value = data.total;
                document.getElementById('add-total-ppn').value = data.ppn;
                document.getElementById('add-total-or').value = data.total_or_ass;
                document.getElementById('add-total-tagihan').value = data.grand_total;

                document.getElementById('add-total-prorata').value = data.prorata;
                document.getElementById('add-total-pph').value = data.pph;
                document.getElementById('add-total-penyusutan').value = data.penyusutan;
                document.getElementById('add-total-salvage').value = data.salvage;
                document.getElementById('add-total-discount').value = data.discount;
                document.getElementById('add-memo').value = data.memo;
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

    const btnEditSelected2 = document.querySelector('.edit-kirim-invoice');
    if (btnEditSelected2) {
      btnEditSelected2.addEventListener('click', function () {
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
          fetch(`${baseUrl}invoice-list/${user_id}/edit`)
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
                const modalInstance = new bootstrap.Modal(addModalKirimInv);
                modalInstance.show();

                document.getElementById('user_id2').value = data.kirim_kwitansi_id;
                document.getElementById('kode_spk2').value = data.kode_spk;
                document.getElementById('kode_estimasi2').value = data.kode_estimasi;
                document.getElementById('kode_kwitansi2').value = data.kode_kwitansi;

                document.getElementById('add-nomor-estimasi2').value = data.kode_estimasi;
                document.getElementById('add-nomor-spk2').value = data.kode_spk;
                document.getElementById('add-nomor-polisi2').value = data.no_polisi;
                document.getElementById('add-tipe-kendaraan2').value = data.merek_tipe;
                document.getElementById('add-nama-pemilik2').value = data.pemilik;
                document.getElementById('add-nama-pelanggan2').value = data.nama_pelanggan;
                document.getElementById('add-nomor-polis2').value = data.no_polis;

                document.getElementById('add-nomor-kwitansi2').value = data.kode_kwitansi;
                document.getElementById('add-tanggal-kwitansi2').value = data.tgl_kwitansi;

                document.getElementById('add-nomor-pengiriman').value = data.kode_kirim_kwitansi;
                document.getElementById('add-tanggal-kirim-kwitansi').value = data.tgl_kirim_kwitansi;

                setSelectValue('#add-tipe-kwitansi2', data.tipe_kwitansi, '');

                document.getElementById('add-total-perbaikan2').value = data.total_perbaikan;
                document.getElementById('add-total-sparepart2').value = data.total_sparepart;
                document.getElementById('add-total-lain2').value = data.total_lain;
                document.getElementById('add-total-keseluruhan2').value = data.total;
                document.getElementById('add-total-transport2').value = data.transport;
                document.getElementById('add-total-or2').value = data.total_or_ass;
                document.getElementById('add-total-tagihan2').value = data.grand_total;

                document.getElementById('add-total-ppn2').value = data.ppn;
                document.getElementById('add-total-prorata2').value = data.prorata;
                document.getElementById('add-total-pph2').value = data.pph;
                document.getElementById('add-total-penyusutan2').value = data.penyusutan;
                document.getElementById('add-total-salvage2').value = data.salvage;
                document.getElementById('add-total-discount2').value = data.discount;
                document.getElementById('add-memo2').value = data.memo;
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

    // const btnEditSelected3 = document.querySelector('.edit-terima-invoice');
    // if (btnEditSelected3) {
    //   btnEditSelected3.addEventListener('click', function () {
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

    //       let params = {
    //         id: user_id
    //       };

    //       // Bersihkan parameter kosong agar URL tidak terlalu panjang
    //       let queryString = $.param(params);

    //       // Redirect window untuk download file
    //       // Pastikan route URL sesuai dengan konfigurasi route Anda
    //       // window.location.href = `${baseUrl}laporan-kendaraan/print?` + queryString;
    //       const printUrl = `${baseUrl}administrasi/tanda-terima-invoice?` + queryString;
    //       window.open(printUrl, '_blank');
    //     }
    //   });
    // }

    // PRINT INVOICE
    const btnEditSelected3 = document.querySelector('.edit-terima-invoice');
    if (btnEditSelected3) {
      btnEditSelected3.addEventListener('click', function () {
        if (!isEdit) {
          Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Anda tidak memiliki izin untuk mengubah data.',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        const selectedCheckbox = document.querySelector('.datatables-spk .dt-checkboxes:checked');

        if (!selectedCheckbox) {
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silahkan pilih (checklist) data SPK pada tabel terlebih dahulu!',
            customClass: { confirmButton: 'btn btn-primary' }
          });
        } else {
          const user_id = selectedCheckbox.value;

          // Fetch dulu untuk ambil kwitansi_id
          fetch(`${baseUrl}invoice-list/${user_id}/edit`)
            .then(response => response.text())
            .then(result => {
              const { status, message, data } = JSON.parse(result);

              if (status) {
                if(data.kode_kwitansi) {
                  // Cukup langsung pakai user_id (id SPK) tanpa fetch
                  let params = { id: user_id };
                  let queryString = $.param(params);
                  const printUrl = `${baseUrl}administrasi/cetak-invoice?` + queryString;
                  window.open(printUrl, '_blank');
                } else {
                  // sweetalert
                  Swal.fire({
                    icon: 'warning',
                    title: `Peringatan!`,
                    text: `Terbit Invoice belum dibuat!`,
                    customClass: {
                      confirmButton: 'btn btn-success'
                    }
                  });
                }
              } else {
                Swal.fire({
                  icon: 'warning',
                  title: 'Peringatan!',
                  text: message,
                  customClass: { confirmButton: 'btn btn-primary' }
                });
              }
            })
            .catch(err => {
              Swal.fire({
                title: 'Error!',
                text: 'Gagal mengambil data SPK',
                icon: 'error',
                customClass: { confirmButton: 'btn btn-success' }
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
          tgl_kwitansi: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Tanggal Invoice'
              }
            }
          },
          kode_tipe_kwitansi: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Tipe Invoice'
              }
            }
          },
          // persen_bahan: {
          //   validators: {
          //     notEmpty: {
          //       message: 'Silahkan Input Persen Bahan'
          //     },
          //     callback: {
          //       message: 'Total Bahan dan Jasa maksimal 100%',
          //       callback: function (input) {
          //         // Ambil nilai, hapus koma (jika ada class ribuan), jadikan angka mutlak
          //         const bahan = parseFloat(input.value.replace(/,/g, '')) || 0;
          //         const jasa = parseFloat(document.getElementById('add-persen-jasa').value.replace(/,/g, '')) || 0;
                  
          //         // Validasi sukses jika total <= 100
          //         return (bahan + jasa) <= 100;
          //       }
          //     }
          //   }
          // },
          // persen_jasa: {
          //   validators: {
          //     notEmpty: {
          //       message: 'Silahkan Input Persen Jasa'
          //     },
          //     callback: {
          //       message: 'Total Bahan dan Jasa maksimal 100%',
          //       callback: function (input) {
          //         const jasa = parseFloat(input.value.replace(/,/g, '')) || 0;
          //         const bahan = parseFloat(document.getElementById('add-persen-bahan').value.replace(/,/g, '')) || 0;
                  
          //         return (bahan + jasa) <= 100;
          //       }
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

        PleaseWaitPage();

        fetch(`${baseUrl}invoice-list`, {
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

            if (document.querySelector(`.notiflix-loading`)) {
              Loading.remove();
            }

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

    const kirimInvoiceForm = document.getElementById('kirimInvoiceForm');
    if (kirimInvoiceForm) {
      const fv = FormValidation.formValidation(kirimInvoiceForm, {
        fields: {
          tgl_kirim_kwitansi: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Tanggal Pengiriman'
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
        const formData = new FormData(kirimInvoiceForm);
        const formDataObj = {};

        // Convert FormData to URL-encoded string
        formData.forEach((value, key) => {
          formDataObj[key] = value;
        });

        const searchParams = new URLSearchParams();
        for (const [key, value] of Object.entries(formDataObj)) {
          searchParams.append(key, value);
        }

        fetch(`${baseUrl}invoice-list`, {
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
            const offcanvasInstance = bootstrap.Modal.getInstance(addModalKirimInv);
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
        if (modalInstance) {
          modalInstance.hide();
        }
      });
    }

    const chkPPN = document.querySelectorAll('.add-ppn');
    if (chkPPN) {
      chkPPN.forEach(radio => {
        radio.addEventListener('change', function () {
          // Ambil value jika radio ini dicentang
          if (this.checked) {
            // let tipe = this.dataset.tipe;
            // let nilai = this.value;

            hitungTotalInvoice();
          }
        });
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

          hitungTotalInvoice();
        });
      }
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

function setRadioValue(name, value) {
  // 1. Cari semua radio button dengan name tersebut
  const $radios = $(`input[name="${name}"]`);

  // Guard clause: Jika elemen tidak ditemukan, stop.
  if (!$radios.length) return;

  // 2. Normalisasi value:
  // Jika null/undefined maka null, selain itu ubah ke String agar pencarian atribut akurat
  const val = value !== null && value !== undefined ? String(value) : null;

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

function hitungTotalInvoice() {
  let total = 0;
  let total_bahan = 0;
  let total_jasa = 0;
  let total_lain = 0;
  let total_part = 0;
  let total_seluruh = 0;
  let total_disc = 0;
  let subtotal = 0;
  let total_tagihan = 0;
  let ppn = 0;
  let ppn_lain = 0;
  let ppn_part = 0;
  let total_ppn = 0;
  
  let ppn_persen = $('#add-ppn-persen').val();
  let total_or = $('#add-total-or').val();
  let salvage = $('#add-total-salvage').val();

  let cleanValPPN = ppn_persen ? ppn_persen.replace(/,/g, '') : '0';
    ppn_persen = (parseFloat(cleanValPPN) / 100) || 0;

  let cleanValOR = total_or ? total_or.replace(/,/g, '') : '0';
  total_or = parseFloat(cleanValOR) || 0;

  let cleanValSv = salvage ? salvage.replace(/,/g, '') : '0';
  salvage = parseFloat(cleanValSv) || 0;

  total = $('#add-total-perbaikan').val();

  // $('#add-total-perbaikan').each(function () {
  //   // Ambil value
  //   let val = $(this).val();

  //   // Bersihkan format (hapus koma) agar bisa dijumlahkan
  //   // Contoh: "405,000.00" menjadi 405000.00
  //   // Jika Anda menggunakan library masking lain, sesuaikan cara unmask-nya
  //   let cleanVal = val.replace(/,/g, '');

  //   // Konversi ke float, jika NaN (kosong) anggap 0
  //   let numberVal = parseFloat(cleanVal) || 0;

  //   total += numberVal;
  // });

  $('#add-persen-bahan').each(function () {
    // Ambil value
    let val = $(this).val();
    let cleanVal = val.replace(/,/g, '');
    let numberVal = parseFloat(cleanVal) || 0;

    $('#add-persen-jasa').val(100 - numberVal);

    total_bahan += total * (numberVal / 100);
  });

  $('#add-persen-jasa').each(function () {
    // Ambil value
    let val = $(this).val();
    let cleanVal = val.replace(/,/g, '');
    let numberVal = parseFloat(cleanVal) || 0;

    total_jasa += total * (numberVal / 100);
  });

  $('#add-total-sparepart').each(function () {
    // Ambil value
    let val = $(this).val();

    // Bersihkan format (hapus koma) agar bisa dijumlahkan
    // Contoh: "405,000.00" menjadi 405000.00
    // Jika Anda menggunakan library masking lain, sesuaikan cara unmask-nya
    let cleanVal = val.replace(/,/g, '');

    // Konversi ke float, jika NaN (kosong) anggap 0
    let numberVal = parseFloat(cleanVal) || 0;

    total_part += numberVal;
  });

  $('#add-total-lain').each(function () {
    // Ambil value
    let val = $(this).val();

    // Bersihkan format (hapus koma) agar bisa dijumlahkan
    // Contoh: "405,000.00" menjadi 405000.00
    // Jika Anda menggunakan library masking lain, sesuaikan cara unmask-nya
    let cleanVal = val.replace(/,/g, '');

    // Konversi ke float, jika NaN (kosong) anggap 0
    let numberVal = parseFloat(cleanVal) || 0;

    total_lain += numberVal;
  });

  $('.add-ppn:checked').each(function () {
    // Ambil value
    let tipe = $(this).data('tipe');
    let nilai = $(this).val();

    if (nilai == '1' && tipe == 'perbaikan') {
      // let valDis = $("#add-disc-perbaikan").val();
      // let cleanValDis = valDis.replace(/,/g, '');
      // let numberValDis = parseFloat(cleanValDis) || 0;

      // ppn = (ppn_persen > 0) ? (total - numberValDis) / ppn_persen : 0;
      ppn = ppn_persen > 0 ? ((total_bahan + total_jasa) * ppn_persen) : 0;
    } else if (nilai == '1' && tipe == 'sparepart') {
      // let valDis = $("#add-disc-sparepart").val();
      // let cleanValDis = valDis.replace(/,/g, '');
      // let numberValDis = parseFloat(cleanValDis) || 0;

      // ppn_part = (ppn_persen > 0) ? (total_part - numberValDis) / ppn_persen : 0;
      ppn_part = ppn_persen > 0 ? (total_part * ppn_persen) : 0;
    } else if (nilai == '1' && tipe == 'lain') {
      // let valDis = $("#add-disc-lain").val();
      // let cleanValDis = valDis.replace(/,/g, '');
      // let numberValDis = parseFloat(cleanValDis) || 0;

      // ppn_lain = (ppn_persen > 0) ? (total_lain - numberValDis) / ppn_persen : 0;
      ppn_lain = ppn_persen > 0 ? (total_lain * ppn_persen) : 0;
    }
  });

  subtotal = total_bahan + total_jasa + total_part + total_lain - salvage;
  total_seluruh = subtotal;

  total_ppn = ppn + ppn_part + ppn_lain;
  total_tagihan = total_seluruh + total_ppn - total_or;

  // Format kembali ke tampilan angka (misal: 1,000,000.00)
  let formattedTotal = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(total_seluruh);

  let formattedTotalTagihan = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(total_tagihan);

  let formattedPPN = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(total_ppn);

  let formattedTotalBahan = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(total_bahan);

  let formattedTotalJasa = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(total_jasa);

  $('#add-total-bahan').val(formattedTotalBahan);
  $('#add-total-jasa').val(formattedTotalJasa);
  $('#add-total-keseluruhan').val(formattedTotal);
  $('#add-total-tagihan').val(formattedTotalTagihan);
  $('#add-total-ppn').val(formattedPPN);
}
