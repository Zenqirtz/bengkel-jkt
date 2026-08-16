/**
 * Page Data management
 */
'use strict';

const _transaksiCache = {};
window._isOpeningPopup = false;

document.addEventListener('DOMContentLoaded', function () {
  // ── Cache opsi transaksi dari DOM ────────────────────────────────────
  document.querySelectorAll('option[data-jenis]').forEach(function (opt) {
    const jenis = opt.dataset.jenis;
    const val = opt.value.trim();
    const text = opt.text.trim();
    if (!val || !jenis) return;
    if (!_transaksiCache[jenis]) _transaksiCache[jenis] = [];
    if (!_transaksiCache[jenis].find(o => o.value === val)) _transaksiCache[jenis].push({ value: val, text });
  });

  let isAdd, isEdit, isDelete;

  const dt_basic_table = document.querySelector('.datatables-ib');
  const addRoleModal = document.getElementById('addRoleModal');
  const filterRoleModal = document.getElementById('filterRoleModal');
  const modalPilihInv = document.getElementById('modalPilihInv');
  const modalPilihUmj = document.getElementById('modalPilihUmj');

  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  // ── FLATPICKR ────────────────────────────────────────────────────────
  document.querySelectorAll('.dt-date').forEach(el => {
    flatpickr(el, { monthSelectorType: 'static', static: true, dateFormat: 'd/m/Y' });
  });

  // ── DATATABLE UTAMA ──────────────────────────────────────────────────
  if (dt_basic_table) {
    isAdd = dt_basic_table.dataset.add;
    isEdit = dt_basic_table.dataset.edit;
    isDelete = dt_basic_table.dataset.delete;

    const dt_basic = new DataTable(dt_basic_table, {
      searching: false,
      ordering: true,
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'input-bank-list',
        data: function (d) {
          d.no_voucher = $('#filter-no-voucher').val();
          d.jenis = $('#filter-jenis').val();
          d.transaksi = $('#filter-transaksi').val();
          d.kode_bank = $('#filter-kode-bank').val();
          d.tanggal_awal = $('#filter-tanggal-awal').val();
          d.tanggal_akhir = $('#filter-tanggal-akhir').val();
        },
        dataSrc: function (json) {
          json.recordsTotal = json.recordsTotal || 0;
          json.recordsFiltered = json.recordsFiltered || 0;
          json.data = Array.isArray(json.data) ? json.data : [];
          return json.data;
        }
      },
      columns: [
        { data: 'id', width: '20px' },
        { data: 'tanggal' },
        { data: 'no_voucher' },
        { data: 'jenis' },
        { data: 'transaksi' },
        { data: 'no_inv_gabung' },
        { data: 'no_spk' },
        { data: 'nama_bank' },
        { data: 'account_coa' },
        { data: 'nilai', className: 'text-end' }
      ],
      // columnDefs: [
      //   {
      //     targets: 0,
      //     orderable: false,
      //     searchable: false,
      //     render: function (data, type, full) {
      //       return `<input type="checkbox" class="dt-checkboxes form-check-input" value="${data}"
      // data-jenis="${full.jenis || ''}">`;
      //     }
      //   },
      //   {
      //     targets: 9,
      //     searchable: false,
      //     render: function (data) {
      //       return formatAngkaDisplay(data);
      //     }
      //   }
      // ],
      columnDefs: [
        {
          targets: 0,
          orderable: false,
          searchable: false,
          render: function (data, type, full) {
            return `<input type="checkbox" class="dt-checkboxes form-check-input" value="${data}"
data-jenis="${full.jenis || ''}">`;
          }
        },
        {
          targets: 5, // kolom no_inv_gabung, cek urutan sesuai definisi columns di atas
          render: function (data) {
            if (!data) return '-';
            const maxLen = 25; // sesuaikan sesuai lebar kolom yang diinginkan
            const text = String(data);
            if (text.length <= maxLen) return text;
            return `<span>${text.substring(0, maxLen)}...</span>`;
          }
        },
        {
          targets: 9,
          searchable: false,
          render: function (data) {
            return formatAngkaDisplay(data);
          }
        }
      ],
      order: [[1, 'desc']],
      layout: {
        topStart: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: [{ pageLength: { menu: [10, 20, 50, 100], text: '_MENU_' } }]
        },
        topEnd: { features: [{ search: { placeholder: 'Cari', text: '_INPUT_' } }] },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: [{ info: { text: 'Showing _START_ to _END_ of _TOTAL_ entries' } }]
        },
        bottomEnd: 'paging'
      },
      displayLength: 10,
      language: {
        paginate: {
          next: '<i class="icon-base ri ri-arrow-right-s-line icon-22px"></i>',
          previous: '<i class="icon-base ri ri-arrow-left-s-line icon-22px"></i>'
        }
      },
      initComplete: function () {
        const sa = document.getElementById('selectAllIB');
        if (sa) {
          sa.addEventListener('change', function () {
            document.querySelectorAll('.datatables-ib .dt-checkboxes').forEach(chk => {
              chk.checked = this.checked;
            });
          });
        }
        dt_basic.on('draw', function () {
          const sa = document.getElementById('selectAllIB');
          if (sa) sa.checked = false;
        });
      }
    });

    // Satu checkbox saja aktif
    // document.addEventListener('click', function (e) {
    //   const chk = e.target.closest('.datatables-ib .dt-checkboxes');
    //   if (chk && chk.checked) $('.datatables-ib .dt-checkboxes').not(chk).prop('checked', false);
    // });

    // // ── Filter ───────────────────────────────────────────────────────
    // document.getElementById('formCariData')?.addEventListener('submit', function (e) {
    //   e.preventDefault();
    //   dt_basic.draw();
    //   bootstrap.Modal.getInstance(filterRoleModal)?.hide();
    // });
    // ── KLIK ROW → DETAIL (read-only, reuse endpoint edit yang sudah ada) ──
    const viewRoleModal = document.getElementById('viewRoleModal');

    $(dt_basic_table).on('click', 'tbody tr', function (e) {
      if ($(e.target).is('input') || $(e.target).closest('td').index() === 0) return;
      const rowData = dt_basic.row(this).data();
      if (!rowData || !rowData.id) return;
      showDetailInputBank(rowData.id);
    });

    function showDetailInputBank(id) {
      fetch(`${baseUrl}input-bank-list/${id}/edit`)
        .then(r => r.json())
        .then(res => {
          if (!res.status) {
            Swal.fire({
              icon: 'warning',
              title: 'Peringatan!',
              text: res.message,
              customClass: { confirmButton: 'btn btn-primary' }
            });
            return;
          }
          const d = res.data;

          document.getElementById('view-no-voucher').value = d.no_voucher || '';
          document.getElementById('view-tanggal').value = d.tanggal || '';
          document.getElementById('view-jenis').value = d.jenis || '';
          document.getElementById('view-transaksi').value = d.transaksi || '';
          document.getElementById('view-bank').value = d.nama_bank || '';
          document.getElementById('view-coa').value = d.account_coa || '';
          document.getElementById('view-spk').value = d.no_spk || ''; // ← No. SPK
          document.getElementById('view-nilai').value = d.nilai_fmt || '0'; // ← Nilai/Total

          // Parsing invoice gabung langsung di frontend, tanpa endpoint baru
          // Ambil rincian nilai per invoice gabung dari backend (d.rincian_inv_gabung)
          const $tbody = document.getElementById('body-invgabung-view');
          const rincian = Array.isArray(d.rincian_inv_gabung) ? d.rincian_inv_gabung : [];

          $tbody.innerHTML = rincian.length
            ? rincian
                .map(
                  (r, i) => `
      <tr>
        <td>${i + 1}</td>
        <td>${r.no_transaksi}</td>
        <td class="text-end">${r.nilai_fmt}</td>
        <td class="text-end">${r.pph_fmt || '0'}</td>
      </tr>
    `
                )
                .join('')
            : `<tr><td colspan="3" class="text-center text-muted">Tidak ada invoice gabungan</td></tr>`;

          new bootstrap.Modal(viewRoleModal).show();
        })
        .catch(() => {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Gagal mengambil data detail',
            customClass: { confirmButton: 'btn btn-success' }
          });
        });
    }

    // ── Filter ───────────────────────────────────────────────────────
    document.getElementById('formCariData')?.addEventListener('submit', function (e) {
      e.preventDefault();
      dt_basic.draw();
      bootstrap.Modal.getInstance(filterRoleModal)?.hide();
    });

    $('#filter-jenis').on('change', function () {
      rebuildTransaksiSelect('#filter-transaksi', $(this).val(), 'all', 'Semua');
    });

    // ── Tambah ───────────────────────────────────────────────────────
    document.querySelector('.add-new')?.addEventListener('click', function () {
      if (!isAdd) {
        alertAksesDitolak('tambah');
        return;
      }
      clearFormData();
      document.getElementById('ib_id').value = '';
    });

    // ── Ubah ─────────────────────────────────────────────────────────
    document.querySelector('.edit-record')?.addEventListener('click', function () {
      if (!isEdit) {
        alertAksesDitolak('ubah');
        return;
      }
      const checked = document.querySelectorAll('.datatables-ib .dt-checkboxes:checked');
      if (!checked.length) {
        alertPilihData();
        return;
      }
      if (checked.length > 1) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan',
          text: 'Pilih hanya satu data untuk diubah.',
          customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
      }
      const chk = checked[0];

      clearFormData();

      fetch(`${baseUrl}input-bank-list/${chk.value}/edit`)
        .then(r => r.json())
        .then(res => {
          if (!res.status) {
            Swal.fire({
              icon: 'warning',
              title: 'Peringatan!',
              text: res.message,
              customClass: { confirmButton: 'btn btn-primary' }
            });
            return;
          }
          const d = res.data;

          document.getElementById('ib_id').value = d.id;
          document.getElementById('add-no-voucher').value = d.no_voucher;
          document.getElementById('add-pph').value = d.pph_fmt;
          document.getElementById('add-biaya-admin').value = d.biaya_admin_fmt;
          document.getElementById('add-sisa').value = d.sisa_fmt;
          document.getElementById('add-keterangan').value = d.keterangan || '';
          // document.getElementById('add-nilai').value = d.nilai_fmt;

          populateUmjFields({
            no_uang_muka: d.no_uang_muka || '',
            nama: d.nama_uang_muka || '',
            nilai_fmt: d.nilai_uang_muka_fmt || '0',
            nilai: d.nilai_uang_muka || 0,
            jenis_penerimaan: d.jenis_penerimaan_uang_muka || '',
            tanggal: d.tanggal_uang_muka || '',
            dp_fmt: d.dp_fmt || ''
          });

          const tglEl = document.getElementById('add-tanggal');
          if (tglEl?._flatpickr) tglEl._flatpickr.setDate(d.tanggal, false, 'd/m/Y');
          else if (tglEl) tglEl.value = d.tanggal;

          setSelectValue('#add-jenis', d.jenis, '');
          toggleJenis(d.jenis);
          setSelectValue('#add-transaksi', d.transaksi, d.transaksi);
          setSelectValue('#add-account-coa', d.account_coa, d.account_coa);
          setSelectValue('#add-kode-bank', d.kode_bank, '');
          setSelectValue('#add-no-spk', d.no_spk, d.no_spk);

          const pakaiInvoice = !!d.no_inv_gabung;
          $('#chk-pakai-invoice').prop('checked', pakaiInvoice);
          toggleInvoiceMode(pakaiInvoice);
          document.getElementById('add-no-inv-gabung').value = d.no_inv_gabung || '';
          // document.getElementById('add-nilai').value = d.nilai_fmt;
          // if (d.no_inv_gabung || d.no_uang_muka) lockNilai();
          document.getElementById('add-nilai').value = d.nilai_fmt;
          if (d.no_inv_gabung) lockNilai(); // hanya lock kalau berasal dari invoice gabungan, bukan dari UMJ
          hitungSisa();
          resetSisaStyle();

          new bootstrap.Modal(addRoleModal).show();
          addRoleModal.addEventListener('shown.bs.modal', function onShown() {
            filterRekeningByBank(d.kode_bank, d.no_rekening);
            addRoleModal.removeEventListener('shown.bs.modal', onShown);
          });
        });
    });

    // ── Hapus ────────────────────────────────────────────────────────
    document.querySelector('.delete-record')?.addEventListener('click', function () {
      if (!isDelete) {
        alertAksesDitolak('hapus');
        return;
      }
      const checked = document.querySelectorAll('.datatables-ib .dt-checkboxes:checked');
      if (!checked.length) {
        alertPilihData();
        return;
      }

      const ids = Array.from(checked).map(c => c.value);
      const jumlah = ids.length;
      const teksKonfirmasi =
        jumlah > 1 ? `Anda yakin akan menghapus ${jumlah} data terpilih?` : 'Anda yakin akan menghapus data ini?';

      Swal.fire({
        title: 'Konfirmasi?',
        text: teksKonfirmasi,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
        buttonsStyling: false
      }).then(result => {
        if (!result.value) return;

        let sukses = 0;
        let gagal = 0;

        const hapusSatuPersatu = index => {
          if (index >= ids.length) {
            dt_basic.draw();
            Swal.fire({
              icon: gagal === 0 ? 'success' : 'warning',
              title: gagal === 0 ? 'Hapus!' : 'Selesai dengan catatan',
              text: `Berhasil menghapus ${sukses} data.` + (gagal > 0 ? ` ${gagal} data gagal dihapus.` : ''),
              customClass: { confirmButton: 'btn btn-success' }
            });
            return;
          }

          fetch(`${baseUrl}input-bank-list/${ids[index]}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Content-Type': 'application/json'
            }
          })
            .then(r => r.json())
            .then(json => {
              if (json.status) sukses++;
              else gagal++;
              hapusSatuPersatu(index + 1);
            })
            .catch(() => {
              gagal++;
              hapusSatuPersatu(index + 1);
            });
        };

        hapusSatuPersatu(0);
      });
    });

    // ── Lihat (Detail via checkbox) ─────────────────────────────────
    document.querySelector('.detail-record')?.addEventListener('click', function () {
      const checked = document.querySelectorAll('.datatables-ib .dt-checkboxes:checked');

      if (!checked.length) {
        alertPilihData();
        return;
      }

      if (checked.length > 1) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan',
          text: 'Pilih hanya satu data untuk dilihat.',
          customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
      }

      showDetailInputBank(checked[0].value);
    });

    // ── Cetak ────────────────────────────────────────────────────────
    document.querySelector('.cetak-record')?.addEventListener('click', function () {
      const checked = document.querySelectorAll('.datatables-ib .dt-checkboxes:checked');

      if (!checked.length) {
        alertPilihData();
        return;
      }

      const jenisList = [...new Set(Array.from(checked).map(c => c.dataset.jenis))];
      if (jenisList.length > 1) {
        const namaJenisList = [
          ...new Set(
            Array.from(checked).map(c => {
              const tr = c.closest('tr');
              return tr ? tr.querySelectorAll('td')[3]?.textContent?.trim() || '' : '';
            })
          )
        ].filter(Boolean);

        Swal.fire({
          icon: 'warning',
          title: 'Peringatan',
          html: `Data yang dipilih memiliki jenis transaksi berbeda:<br><br>
     <b>${namaJenisList.join('<br>')}</b><br><br>
     Silahkan pilih data dengan jenis yang sama!`,
          customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
      }

      const ids = Array.from(checked).map(c => c.value);
      const queryString = ids.map(id => `id[]=${id}`).join('&');
      window.open(`${baseUrl}keuangan/cetak-input-bank?` + queryString, '_blank');
    });

    setTimeout(() => {
      [
        { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
        { selector: '.dt-length .form-select', classToAdd: 'ms-0' },
        { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
        { selector: '.dt-layout-full', classToRemove: 'col-md col-12' }
      ].forEach(({ selector, classToRemove, classToAdd }) => {
        document.querySelectorAll(selector).forEach(el => {
          if (classToRemove) classToRemove.split(' ').forEach(c => el.classList.remove(c));
          if (classToAdd) classToAdd.split(' ').forEach(c => el.classList.add(c));
        });
      });
    }, 100);
  }

  // ── JENIS CHANGE ─────────────────────────────────────────────────────
  $('#add-jenis').on('change', function () {
    toggleJenis($(this).val());
  });

  // // $('#add-transaksi').on('change', function () {
  // //   const val = $(this).val();

  // //   document.getElementById('add-no-inv-gabung').value = '';
  // //   document.getElementById('add-id-hrl').value = '';
  // //   document.getElementById('add-nilai').value = '';
  // //   unlockNilai();
  // //   hitungSisa();

  // //   if (val === 'Pembayaran Upah Harian Lepas') {
  // $('#add-transaksi').on('change', function () {
  //   const val = $(this).val();

  //   document.getElementById('add-no-inv-gabung').value = '';
  //   document.getElementById('add-id-hrl').value = '';
  //   document.getElementById('add-nilai').value = '';
  //   document.getElementById('add-pph').value = '';
  //   unlockNilai();
  //   unlockPph();
  //   hitungSisa();

  //   if (val === 'Pembayaran Upah Harian Lepas') {
  //     $('#chk-pakai-invoice').prop('checked', true);
  //     $('#row-inv-gabung').show();
  //     $('#btn-pilih-inv').hide();
  //     $('#btn-pilih-hrl').removeClass('d-none').show();
  //     $('#label-inv-gabung').text('No. Upah Harian Lepas');
  //   } else {
  //     $('#btn-pilih-hrl').hide().addClass('d-none');
  //     $('#btn-pilih-inv').show();
  //     const jenis = $('#add-jenis').val();
  //     if (jenis === 'PENERIMAAN') $('#label-inv-gabung').text('No. Inv. Gabung (LCR)');
  //     else if (jenis === 'PENGELUARAN') $('#label-inv-gabung').text('No. Inv. Gabung (LSR)');
  //     else $('#label-inv-gabung').text('No. Inv. Gabung');
  //     // Ikutin state checkbox
  //     toggleInvoiceMode($('#chk-pakai-invoice').is(':checked'));
  //   }
  // });
  $('#add-transaksi').on('change', function () {
    const val = $(this).val();

    document.getElementById('add-no-inv-gabung').value = '';
    document.getElementById('add-id-hrl').value = '';
    document.getElementById('add-nilai').value = '';
    document.getElementById('add-pph').value = '';
    unlockNilai();
    unlockPph();
    hitungSisa();

    if (val === 'Pembayaran Upah Harian Lepas') {
      $('#chk-pakai-invoice').prop('checked', true).prop('disabled', true); // dikunci karena wajib pakai HRL
      $('#row-inv-gabung').show();
      $('#btn-pilih-inv').hide();
      $('#btn-pilih-hrl').removeClass('d-none').show();
      $('#label-inv-gabung').text('No. Upah Harian Lepas');
    } else {
      $('#chk-pakai-invoice').prop('disabled', false); // pastikan bisa dicentang/uncentang bebas
      $('#btn-pilih-hrl').hide().addClass('d-none');
      $('#btn-pilih-inv').show();
      const jenis = $('#add-jenis').val();
      if (jenis === 'PENERIMAAN') $('#label-inv-gabung').text('No. Inv. Gabung (LCR)');
      else if (jenis === 'PENGELUARAN') $('#label-inv-gabung').text('No. Inv. Gabung (LSR)');
      else $('#label-inv-gabung').text('No. Inv. Gabung');
      toggleInvoiceMode($('#chk-pakai-invoice').is(':checked'));
    }
  });

  $('#chk-pakai-invoice').on('change', function () {
    toggleInvoiceMode($(this).is(':checked'));
  });

  // ── REKENING BY BANK ─────────────────────────────────────────────────
  $('#add-kode-bank').on('change', function () {
    filterRekeningByBank($(this).val() || '', '');
  });

  // ── HITUNG SISA ──────────────────────────────────────────────────────
  document.querySelectorAll('.hitung-sisa').forEach(el => {
    el.addEventListener('input', function () {
      this.value = formatAngka(this.value);
      hitungSisa();
    });
  });

  // =====================================================================
  // POPUP: INVOICE GABUNGAN (INV)
  // =====================================================================

  // function _bukaModalInv(tipe) {
  //   document.getElementById('title-modal-inv').textContent =
  //     tipe === 'lcr' ? 'Pilih LCR (Penerimaan Gabungan)' : 'Pilih LSR (Pembayaran Gabungan)';

  //   const invTable = document.querySelector('.datatables-inv-popup');
  //   if (invTable && !window.dt_inv_global) {
  //     window.dt_inv_global = _initInvDataTable(invTable);
  //     setTimeout(() => _stylePopupTable('.datatables-inv-popup'), 300);
  //   }
  //   _loadInvToDataTable(tipe);
  //   new bootstrap.Modal(modalPilihInv).show();
  // }
  function _bukaModalInv(tipe) {
    document.getElementById('title-modal-inv').textContent =
      tipe === 'lcr' ? 'Pilih LCR (Penerimaan Gabungan)' : 'Pilih LSR (Pembayaran Gabungan)';

    const invTable = document.querySelector('.datatables-inv-popup');
    if (invTable && !window.dt_inv_global) {
      window.dt_inv_global = _initInvDataTable(invTable);
      setTimeout(() => _stylePopupTable('.datatables-inv-popup'), 300);

      const sa = document.getElementById('selectAllInvPopup');
      if (sa) {
        sa.addEventListener('change', function () {
          document.querySelectorAll('.datatables-inv-popup .inv-pg-chk').forEach(chk => {
            chk.checked = this.checked;
          });
        });
      }
      window.dt_inv_global.on('draw', function () {
        const sa2 = document.getElementById('selectAllInvPopup');
        if (sa2) sa2.checked = false;
      });
    }
    _loadInvToDataTable(tipe);
    new bootstrap.Modal(modalPilihInv).show();
  }

  document.getElementById('btn-pilih-inv')?.addEventListener('click', function () {
    const jenis = $('#add-jenis').val();
    if (!jenis) {
      Swal.fire({
        icon: 'warning',
        title: 'Peringatan',
        text: 'Pilih Jenis terlebih dahulu sebelum mencari Invoice Gabungan.',
        customClass: { confirmButton: 'btn btn-primary' }
      });
      setTimeout(() => $('#add-jenis').select2('open'), 300);
      return;
    }
    const tipe = $(this).data('tipe') || 'lcr';
    const instForm = bootstrap.Modal.getInstance(addRoleModal);
    if (instForm) {
      addRoleModal.addEventListener('hidden.bs.modal', function onHiddenInv() {
        addRoleModal.removeEventListener('hidden.bs.modal', onHiddenInv);
        window._isOpeningPopup = false;
        _bukaModalInv(tipe);
      });
      window._isOpeningPopup = true;
      instForm.hide();
    } else {
      _bukaModalInv(tipe);
    }
  });

  // document.getElementById('btn-pilih-inv-terpilih')?.addEventListener('click', function () {
  //   const chk = document.querySelector('.datatables-inv-popup .inv-pg-chk:checked');
  //   if (!chk) {
  //     Swal.fire({
  //       icon: 'warning',
  //       title: 'Peringatan',
  //       text: 'Pilih satu data terlebih dahulu.',
  //       customClass: { confirmButton: 'btn btn-primary' }
  //     });
  //     return;
  //   }
  //   document.getElementById('add-no-inv-gabung').value = chk.value;
  //   document.getElementById('add-nilai').value = Math.round(parseFloat(chk.dataset.total || 0)).toLocaleString('en-US');
  //   hitungSisa();
  //   lockNilai();

  //   const instInv = bootstrap.Modal.getInstance(modalPilihInv);
  //   if (instInv) {
  //     modalPilihInv.addEventListener('hidden.bs.modal', function onHiddenBack() {
  //       modalPilihInv.removeEventListener('hidden.bs.modal', onHiddenBack);
  //       new bootstrap.Modal(addRoleModal).show();
  //     });
  //     instInv.hide();
  //   } else {
  //     new bootstrap.Modal(addRoleModal).show();
  //   }
  // });
  document.getElementById('btn-pilih-inv-terpilih')?.addEventListener('click', function () {
    const checked = document.querySelectorAll('.datatables-inv-popup .inv-pg-chk:checked');
    if (!checked.length) {
      Swal.fire({
        icon: 'warning',
        title: 'Peringatan',
        text: 'Pilih minimal satu data terlebih dahulu.',
        customClass: { confirmButton: 'btn btn-primary' }
      });
      return;
    }

    const namaList = [...new Set(Array.from(checked).map(c => c.dataset.nama || '-'))];
    if (namaList.length > 1) {
      Swal.fire({
        icon: 'warning',
        title: 'Peringatan',
        html: `Data yang dipilih memiliki nama berbeda:<br><br>
   <b>${namaList.join('<br>')}</b><br><br>
   Silahkan pilih data dengan nama yang sama!`,
        customClass: { confirmButton: 'btn btn-primary' }
      });
      return;
    }

    const noTransaksiList = Array.from(checked).map(c => c.value);
    const totalNilai = Array.from(checked).reduce((sum, c) => sum + (parseFloat(c.dataset.total) || 0), 0);
    const totalPph = Array.from(checked).reduce((sum, c) => sum + (parseFloat(c.dataset.pph) || 0), 0);
    const totalBiayaMerimen = Array.from(checked).reduce((sum, c) => sum + (parseFloat(c.dataset.biaya_merimen) || 0), 0);
    const tipe = $('#btn-pilih-inv').data('tipe') || 'lcr'; // ← TAMBAHKAN BARIS INI

    // document.getElementById('add-no-inv-gabung').value = noTransaksiList.join(', ');
    // document.getElementById('add-nilai').value = Math.round(totalNilai).toLocaleString('en-US');
    // document.getElementById('add-pph').value = Math.round(totalPph).toLocaleString('en-US');
    document.getElementById('add-no-inv-gabung').value = noTransaksiList.join(', ');
    document.getElementById('add-nilai').value = Math.round(totalNilai).toLocaleString('en-US');

    if (tipe === 'lcr') {
      // LCR: PPH ikut ke-generate dari data invoice, kunci input manual
      document.getElementById('add-pph').value = Math.round(totalPph).toLocaleString('en-US');
      document.getElementById('add-biaya-merimen').value = Math.round(totalBiayaMerimen).toLocaleString('en-US');
      lockPph();
    } else {
      // LSR: PPH tidak dihitung dari invoice, biarkan user isi manual
      unlockPph();
      // JANGAN override value PPH yang mungkin sudah diisi user
    }
    hitungSisa();
    lockNilai();
    // lockPph();

    const instInv = bootstrap.Modal.getInstance(modalPilihInv);
    if (instInv) {
      modalPilihInv.addEventListener('hidden.bs.modal', function onHiddenBack() {
        modalPilihInv.removeEventListener('hidden.bs.modal', onHiddenBack);
        new bootstrap.Modal(addRoleModal).show();
      });
      instInv.hide();
    } else {
      new bootstrap.Modal(addRoleModal).show();
    }
  });

  // Tombol X & Batal modal INV → kembali ke form
  if (modalPilihInv) {
    let _invShouldReturn = false;

    modalPilihInv.querySelectorAll('.btn-close').forEach(btn => {
      btn.addEventListener('click', () => {
        _invShouldReturn = true;
      });
    });

    modalPilihInv.querySelectorAll('.btn-outline-danger').forEach(btn => {
      btn.removeAttribute('data-bs-dismiss');
      btn.addEventListener('click', () => {
        _invShouldReturn = true;
        bootstrap.Modal.getInstance(modalPilihInv)?.hide();
      });
    });

    modalPilihInv.addEventListener('hidden.bs.modal', function () {
      document.querySelectorAll('.datatables-inv-popup .inv-pg-chk').forEach(c => (c.checked = false));
      if (_invShouldReturn) {
        _invShouldReturn = false;
        new bootstrap.Modal(addRoleModal).show();
      }
    });
  }

  // =====================================================================
  // POPUP: UANG MUKA PENJUALAN (UMJ)
  // =====================================================================

  function _bukaModalUmj(editId) {
    const umjTable = document.querySelector('.datatables-umj-popup');
    if (umjTable && !window.dt_umj_global) {
      window.dt_umj_global = _initUmjDataTable(umjTable);
      setTimeout(() => _stylePopupTable('.datatables-umj-popup'), 300);
    }
    _loadUmjToDataTable(editId);
    new bootstrap.Modal(modalPilihUmj).show();
  }

  document.getElementById('btn-pilih-umj')?.addEventListener('click', function () {
    const editId = document.getElementById('ib_id')?.value || '';
    const instForm = bootstrap.Modal.getInstance(addRoleModal);
    if (instForm) {
      addRoleModal.addEventListener('hidden.bs.modal', function onHiddenUmj() {
        addRoleModal.removeEventListener('hidden.bs.modal', onHiddenUmj);
        window._isOpeningPopup = false;
        _bukaModalUmj(editId);
      });
      window._isOpeningPopup = true;
      instForm.hide();
    } else {
      _bukaModalUmj(editId);
    }
  });

  document.getElementById('btn-pilih-umj-terpilih')?.addEventListener('click', function () {
    const chk = document.querySelector('.datatables-umj-popup .umj-pg-chk:checked');
    if (!chk) {
      Swal.fire({
        icon: 'warning',
        title: 'Peringatan',
        text: 'Pilih satu data terlebih dahulu.',
        customClass: { confirmButton: 'btn btn-primary' }
      });
      return;
    }
    populateUmjFields({
      no_uang_muka: chk.value,
      nama: chk.dataset.nama || '',
      nilai: Math.round(parseFloat(chk.dataset.nilai || 0)),
      nilai_fmt: Math.round(parseFloat(chk.dataset.nilai || 0)).toLocaleString('en-US'),
      jenis_penerimaan: chk.dataset.jenisPenerimaan || '',
      tanggal: chk.dataset.tanggal || '',
      dp_fmt: Math.round(parseFloat(chk.dataset.nilai || 0)).toLocaleString('en-US')
    });
    hitungSisa();
    // lockNilai();
    // Nilai TIDAK dikunci di sini — user tetap harus mengisi total tagihan manual
    // Nilai hanya dikunci kalau dari Invoice Gabungan (lihat lockNilai() di btn-pilih-inv-terpilih)

    const instUmj = bootstrap.Modal.getInstance(modalPilihUmj);
    if (instUmj) {
      modalPilihUmj.addEventListener('hidden.bs.modal', function onHiddenBackUmj() {
        modalPilihUmj.removeEventListener('hidden.bs.modal', onHiddenBackUmj);
        new bootstrap.Modal(addRoleModal).show();
      });
      instUmj.hide();
    } else {
      new bootstrap.Modal(addRoleModal).show();
    }
  });

  // Tombol X & Batal modal UMJ → kembali ke form
  if (modalPilihUmj) {
    let _umjShouldReturn = false;

    modalPilihUmj.querySelectorAll('.btn-close').forEach(btn => {
      btn.addEventListener('click', () => {
        _umjShouldReturn = true;
      });
    });

    modalPilihUmj.querySelectorAll('.btn-outline-danger').forEach(btn => {
      btn.removeAttribute('data-bs-dismiss');
      btn.addEventListener('click', () => {
        _umjShouldReturn = true;
        bootstrap.Modal.getInstance(modalPilihUmj)?.hide();
      });
    });

    modalPilihUmj.addEventListener('hidden.bs.modal', function () {
      document.querySelectorAll('.datatables-umj-popup .umj-pg-chk').forEach(c => (c.checked = false));
      if (_umjShouldReturn) {
        _umjShouldReturn = false;
        new bootstrap.Modal(addRoleModal).show();
      }
    });
  }

  // Tombol hapus UMJ
  document.getElementById('btn-hapus-umj')?.addEventListener('click', function () {
    clearUmjFields();
    hitungSisa();
    unlockNilai();
  });

  // =====================================================================
  // POPUP: HARIAN LEPAS (HRL)
  // =====================================================================
  const modalPilihHrl = document.getElementById('modalPilihHrl');

  document.getElementById('btn-pilih-hrl')?.addEventListener('click', function () {
    const hrlTable = document.querySelector('.datatables-hrl-popup');
    if (hrlTable && !window.dt_hrl_global) {
      window.dt_hrl_global = _initHrlDataTable(hrlTable);
      setTimeout(() => _stylePopupTable('.datatables-hrl-popup'), 300);
    }
    _loadHrlToDataTable();
    const instForm = bootstrap.Modal.getInstance(addRoleModal);
    if (instForm) {
      addRoleModal.addEventListener('hidden.bs.modal', function onHiddenHrl() {
        addRoleModal.removeEventListener('hidden.bs.modal', onHiddenHrl);
        window._isOpeningPopup = false;
        new bootstrap.Modal(modalPilihHrl).show();
      });
      window._isOpeningPopup = true;
      instForm.hide();
    } else {
      new bootstrap.Modal(modalPilihHrl).show();
    }
  });

  document.getElementById('btn-pilih-hrl-terpilih')?.addEventListener('click', function () {
    const chk = document.querySelector('.datatables-hrl-popup .hrl-pg-chk:checked');
    if (!chk) {
      alertPilihData();
      return;
    }

    document.getElementById('add-no-inv-gabung').value = chk.dataset.no;
    document.getElementById('add-id-hrl').value = chk.value;
    const total = Math.round(parseFloat(chk.dataset.total || 0));
    document.getElementById('add-nilai').value = total.toLocaleString('en-US');
    hitungSisa();
    lockNilai();

    const instHrl = bootstrap.Modal.getInstance(modalPilihHrl);
    if (instHrl) {
      modalPilihHrl.addEventListener('hidden.bs.modal', function onBackHrl() {
        modalPilihHrl.removeEventListener('hidden.bs.modal', onBackHrl);
        new bootstrap.Modal(addRoleModal).show();
      });
      instHrl.hide();
    } else {
      new bootstrap.Modal(addRoleModal).show();
    }
  });

  if (modalPilihHrl) {
    let _hrlShouldReturn = false;
    modalPilihHrl.querySelector('.btn-close')?.addEventListener('click', () => {
      _hrlShouldReturn = true;
    });
    document.getElementById('btn-batal-hrl')?.addEventListener('click', () => {
      _hrlShouldReturn = true;
      bootstrap.Modal.getInstance(modalPilihHrl)?.hide();
    });
    modalPilihHrl.addEventListener('hidden.bs.modal', function () {
      document.querySelectorAll('.datatables-hrl-popup .hrl-pg-chk').forEach(c => (c.checked = false));
      if (_hrlShouldReturn) {
        _hrlShouldReturn = false;
        new bootstrap.Modal(addRoleModal).show();
      }
    });
  }

  // ── FORM VALIDATION ──────────────────────────────────────────────────
  const addNewDataForm = document.getElementById('addNewDataForm');
  if (addNewDataForm) {
    const fv = FormValidation.formValidation(addNewDataForm, {
      fields: {
        tanggal: { validators: { notEmpty: { message: 'Tanggal wajib diisi' } } },
        jenis: { validators: { notEmpty: { message: 'Jenis wajib dipilih' } } },
        transaksi: { validators: { notEmpty: { message: 'Transaksi wajib dipilih' } } },
        kode_bank: { validators: { notEmpty: { message: 'Bank wajib dipilih' } } },
        no_rekening: { validators: { notEmpty: { message: 'No. Rekening wajib dipilih' } } },
        account_coa: { validators: { notEmpty: { message: 'Account/COA wajib dipilih' } } }
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
      // addNewDataForm.submit();

      const nilai = parseAngka($('#add-nilai').val());
      if (nilai <= 0) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan!',
          text: 'Nilai wajib diisi. Pilih Invoice Gabungan terlebih dahulu.',
          customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
      }
      const sisa = parseAngka($('#add-sisa').val());
      if (sisa < 0) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan!',
          text: 'Total pembayaran melebihi nilai invoice. Periksa kembali jumlah yang diinput.',
          customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
      }

      const formData = new FormData(addNewDataForm);
      const params = new URLSearchParams();
      formData.forEach((v, k) => params.append(k, v));

      PleaseWaitPage();

      fetch(`${baseUrl}input-bank-list`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: params.toString()
      })
        .then(r => r.json())
        .then(json => {
          if (document.querySelector('.notiflix-loading')) Loading.remove();
          bootstrap.Modal.getInstance(addRoleModal)?.hide();
          if (dt_basic_table) new DataTable(dt_basic_table).draw();
          Swal.fire({
            icon: json.status ? 'success' : 'error',
            title: json.status ? 'Informasi!' : 'Error!',
            text: json.message,
            customClass: { confirmButton: json.status ? 'btn btn-success' : 'btn btn-primary' }
          });
        })
        .catch(err => {
          if (document.querySelector('.notiflix-loading')) Loading.remove();
          bootstrap.Modal.getInstance(addRoleModal)?.hide();
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: String(err.message || err),
            customClass: { confirmButton: 'btn btn-success' }
          });
        });
    });

    // Reset form HANYA jika modal ditutup beneran (bukan sementara untuk buka popup)
    addRoleModal?.addEventListener('hidden.bs.modal', function () {
      if (window._isOpeningPopup) return;
      fv.resetForm(true);
      clearFormData();
    });
  }

  // ── SELECT2 ──────────────────────────────────────────────────────────
  $('.select2').each(function () {
    const $this = $(this);
    $this.wrap('<div class="position-relative"></div>').select2({
      placeholder: $this.find('option[value=""]').first().text() || 'Pilih',
      allowClear: true,
      width: '100%',
      dropdownParent: $this.parent()
    });
  });
  lockUmjFields();
}); // end DOMContentLoaded

// ============================================================
// DATATABLE POPUP: INV
// ============================================================
function _initInvDataTable(table) {
  return new DataTable(table, {
    searching: true,
    ordering: true, // aktifkan ordering
    processing: false,
    serverSide: false,
    scrollX: true,
    autoWidth: true,
    data: [],
    columns: [
      { data: 'no_transaksi', width: '30px' },
      { data: 'no_transaksi' },
      { data: 'nama', defaultContent: '-' },
      { data: 'total_nilai', className: 'text-end' }
    ],
    columnDefs: [
      {
        targets: 0,
        orderable: false,
        searchable: false,
        render: function (data, type, full) {
          // return `<input type="checkbox" class="inv-pg-chk form-check-input" value="${data}"
          //   data-total="${full.total_nilai || 0}" style="width:16px;height:16px;cursor:pointer;">`;
          return `<input type="checkbox" class="inv-pg-chk dt-checkboxes form-check-input" value="${data}"
         data-total="${full.total_nilai || 0}" data-nama="${full.nama || ''}" data-pph="${full.pph || 0}" data-biaya_merimen="${full.biaya_merimen || 0}">`;
        }
      },
      {
        targets: 3,
        searchable: false,
        render: function (data, type, full) {
          const bMerimen = parseFloat(full.biaya_merimen) || 0;
          const pph = parseFloat(full.pph) || 0;

          data = parseFloat(data) + pph + bMerimen;

          return formatAngkaDisplay(data);
        }
      }
    ],
    layout: {
      topStart: {
        rowClass: 'row m-3 my-0 justify-content-between',
        features: [{ pageLength: { menu: [10, 20, 50, 100], text: '_MENU_' } }]
      },
      topEnd: { features: [{ search: { placeholder: 'Cari No. Transaksi / Nama', text: '_INPUT_' } }] },
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
      },
      zeroRecords: 'Data tidak ditemukan',
      emptyTable: 'Belum ada data',
      loadingRecords: 'Memuat...'
    }
    // drawCallback: function () {
    //   document.querySelectorAll('.datatables-inv-popup .inv-pg-chk').forEach(chk => {
    //     chk.addEventListener('change', function () {
    //       if (this.checked)
    //         document.querySelectorAll('.datatables-inv-popup .inv-pg-chk').forEach(c => {
    //           if (c !== this) c.checked = false;
    //         });
    //     });
    //   });
    // }
  });
}

// function _loadInvToDataTable(tipe) {
//   if (!window.dt_inv_global) return;
//   const param = tipe === 'lcr' ? 'get_lcr=1' : 'get_lsr=1';
//   fetch(`${baseUrl}input-bank-list?${param}&search=`)
//     .then(r => r.json())
//     .then(data => {
//       const rows = Array.isArray(data)
//         ? data.map(row => ({ ...row, nama: tipe === 'lcr' ? row.nama_customer || '-' : row.nama_supplier || '-' }))
//         : [];
//       const th = document.querySelector('.datatables-inv-popup thead tr th:nth-child(3)');
//       if (th) th.textContent = tipe === 'lcr' ? 'Nama Customer' : 'Nama Supplier';
//       window.dt_inv_global.clear();
//       if (rows.length) window.dt_inv_global.rows.add(rows);
//       window.dt_inv_global.draw();
//     })
//     .catch(() => window.dt_inv_global.clear().draw());
// }

function _loadInvToDataTable(tipe) {
  if (!window.dt_inv_global) return;
  const param = tipe === 'lcr' ? 'get_lcr=1' : 'get_lsr=1';
  const editId = document.getElementById('ib_id')?.value?.trim() || '';
  const url = editId
    ? `${baseUrl}input-bank-list?${param}&search=&edit_id=${editId}`
    : `${baseUrl}input-bank-list?${param}&search=`;

  fetch(url)
    .then(r => r.json())
    .then(data => {
      const rows = Array.isArray(data)
        ? data.map(row => ({ ...row, nama: tipe === 'lcr' ? row.nama_customer || '-' : row.nama_supplier || '-' }))
        : [];
      const th = document.querySelector('.datatables-inv-popup thead tr th:nth-child(3)');
      if (th) th.textContent = tipe === 'lcr' ? 'Nama Customer' : 'Nama Supplier';
      window.dt_inv_global.clear();
      if (rows.length) window.dt_inv_global.rows.add(rows);
      window.dt_inv_global.draw();
    })
    .catch(() => window.dt_inv_global.clear().draw());
}

// ============================================================
// DATATABLE POPUP: UMJ
// ============================================================
function _initUmjDataTable(table) {
  return new DataTable(table, {
    searching: true,
    ordering: true, // aktifkan ordering
    processing: false,
    serverSide: false,
    scrollX: true,
    autoWidth: true,
    data: [],
    columns: [
      { data: 'no_transaksi', width: '30px' },
      { data: 'no_transaksi' },
      { data: 'nama', defaultContent: '-' },
      { data: 'jenis_penerimaan', defaultContent: '-' },
      { data: 'nilai', className: 'text-end' }
    ],
    columnDefs: [
      {
        targets: 0,
        orderable: false,
        searchable: false,
        render: function (data, type, full) {
          const tgl = full.tanggal_transaksi
            ? new Date(full.tanggal_transaksi).toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
              })
            : '';
          return `<input type="checkbox" class="umj-pg-chk form-check-input" value="${data}"
            data-nama="${full.nama || ''}"
            data-nilai="${full.nilai || 0}"
            data-jenis-penerimaan="${full.jenis_penerimaan || ''}"
            data-tanggal="${tgl}"
            style="width:16px;height:16px;cursor:pointer;">`;
        }
      },
      {
        targets: 4,
        searchable: false,
        render: function (data) {
          return formatAngkaDisplay(data);
        }
      }
    ],
    layout: {
      topStart: {
        rowClass: 'row m-3 my-0 justify-content-between',
        features: [{ pageLength: { menu: [10, 20, 50, 100], text: '_MENU_' } }]
      },
      topEnd: { features: [{ search: { placeholder: 'Cari No. Transaksi / Nama', text: '_INPUT_' } }] },
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
      },
      zeroRecords: 'Data tidak ditemukan',
      emptyTable: 'Belum ada data',
      loadingRecords: 'Memuat...'
    },
    drawCallback: function () {
      document.querySelectorAll('.datatables-umj-popup .umj-pg-chk').forEach(chk => {
        chk.addEventListener('change', function () {
          if (this.checked)
            document.querySelectorAll('.datatables-umj-popup .umj-pg-chk').forEach(c => {
              if (c !== this) c.checked = false;
            });
        });
      });
    }
  });
}

function _loadUmjToDataTable(editId) {
  if (!window.dt_umj_global) return;
  const params = new URLSearchParams({ get_umj: 1, search: '' });
  if (editId) params.append('edit_id', editId);
  fetch(`${baseUrl}input-bank-list?${params.toString()}`)
    .then(r => r.json())
    .then(data => {
      window.dt_umj_global.clear();
      if (Array.isArray(data) && data.length) window.dt_umj_global.rows.add(data);
      window.dt_umj_global.draw();
    })
    .catch(() => window.dt_umj_global.clear().draw());
}

function _stylePopupTable(prefix) {
  [
    { selector: `${prefix} .dt-length .form-select`, classToAdd: 'ms-0' },
    { selector: `${prefix} .dt-length`, classToAdd: 'mb-md-5 mb-0' },
    { selector: `${prefix} .dt-layout-table`, classToRemove: 'row mt-2' },
    { selector: `${prefix} .dt-layout-full`, classToRemove: 'col-md col-12' }
  ].forEach(({ selector, classToRemove, classToAdd }) => {
    document.querySelectorAll(selector).forEach(el => {
      if (classToRemove) classToRemove.split(' ').forEach(c => el.classList.remove(c));
      if (classToAdd) classToAdd.split(' ').forEach(c => el.classList.add(c));
    });
  });
}

function _initHrlDataTable(table) {
  return new DataTable(table, {
    searching: true,
    ordering: true,
    processing: false,
    serverSide: false,
    scrollX: true,
    autoWidth: true,
    data: [],
    columns: [
      { data: 'id', width: '30px' },
      { data: 'no_transaksi' },
      { data: 'tanggal_transaksi', defaultContent: '-' },
      { data: 'nama_pekerja', defaultContent: '-' },
      { data: 'nama_jenis_pekerjaan', defaultContent: '-' },
      { data: 'total_nilai', className: 'text-end' }
    ],
    columnDefs: [
      {
        targets: 0,
        orderable: false,
        searchable: false,
        render: function (data, type, full) {
          return `<input type="checkbox" class="hrl-pg-chk form-check-input" value="${data}"
            data-no="${full.no_transaksi || ''}"
            data-total="${full.total_nilai_raw || full.total_nilai || 0}"
            style="width:16px;height:16px;cursor:pointer;">`;
        }
      },
      {
        targets: 5,
        searchable: false,
        render: function (data) {
          return formatAngkaDisplay(data);
        }
      }
    ],
    layout: {
      topStart: {
        rowClass: 'row m-3 my-0 justify-content-between',
        features: [{ pageLength: { menu: [10, 20, 50, 100], text: '_MENU_' } }]
      },
      topEnd: { features: [{ search: { placeholder: 'Cari No. Transaksi / Nama', text: '_INPUT_' } }] },
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
      },
      zeroRecords: 'Data tidak ditemukan',
      emptyTable: 'Belum ada data',
      loadingRecords: 'Memuat...'
    },
    drawCallback: function () {
      document.querySelectorAll('.datatables-hrl-popup .hrl-pg-chk').forEach(chk => {
        chk.addEventListener('change', function () {
          if (this.checked)
            document.querySelectorAll('.datatables-hrl-popup .hrl-pg-chk').forEach(c => {
              if (c !== this) c.checked = false;
            });
        });
      });
    }
  });
}

// function _loadHrlToDataTable() {
//   if (!window.dt_hrl_global) return;
//   fetch(`${baseUrl}input-bank-list?get_hrl=1`)
//     .then(r => r.json())
//     .then(data => {
//       window.dt_hrl_global.clear();
//       if (Array.isArray(data) && data.length) window.dt_hrl_global.rows.add(data);
//       window.dt_hrl_global.draw();
//     })
//     .catch(() => window.dt_hrl_global.clear().draw());
// }
function _loadHrlToDataTable() {
  if (!window.dt_hrl_global) return;
  const editId = document.getElementById('ib_id')?.value || '';
  const editParam = editId ? `&edit_id=${editId}` : '';
  fetch(`${baseUrl}input-bank-list?get_hrl=1${editParam}`)
    .then(r => r.json())
    .then(data => {
      window.dt_hrl_global.clear();
      if (Array.isArray(data) && data.length) window.dt_hrl_global.rows.add(data);
      window.dt_hrl_global.draw();
    })
    .catch(() => window.dt_hrl_global.clear().draw());
}

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Format angka dari nilai mentah DB (bisa integer, float, atau string tanpa koma)
 * Aman untuk nilai yang sudah di-format maupun belum.
 */
function formatAngkaDisplay(data) {
  // Hapus hanya koma pemisah ribuan (bukan titik desimal)
  // Jika dari DB nilainya sudah string berformat "60,000,000" → hapus koma → parse float
  const cleaned = String(data || 0).replace(/,/g, '');
  const n = parseFloat(cleaned) || 0;
  return Math.round(n).toLocaleString('en-US');
}

// function toggleJenis(kode) {
//   rebuildTransaksiSelect('#add-transaksi', kode, '', 'Pilih Transaksi');

//   // Jangan reset checkbox, tapi reset field inv gabung
//   document.getElementById('add-no-inv-gabung').value = '';
//   document.getElementById('add-id-hrl').value = '';
//   document.getElementById('add-nilai').value = '';
//   hitungSisa();
//   unlockNilai();

//   const umjSection = document.getElementById('section-uang-muka');
//   if (kode === 'PENERIMAAN') {
//     $('#label-inv-gabung').text('No. Inv. Gabung (LCR)');
//     $('#btn-pilih-inv').data('tipe', 'lcr');
//     if (umjSection) $(umjSection).show();
//   } else if (kode === 'PENGELUARAN') {
//     $('#label-inv-gabung').text('No. Inv. Gabung (LSR)');
//     $('#btn-pilih-inv').data('tipe', 'lsr');
//     if (umjSection) $(umjSection).hide();
//     clearUmjFields();
//   } else {
//     $('#label-inv-gabung').text('No. Inv. Gabung');
//     $('#btn-pilih-inv').data('tipe', 'lcr');
//     if (umjSection) $(umjSection).hide();
//   }

//   // Tampilkan/sembunyikan row inv gabung sesuai state checkbox saat ini
//   toggleInvoiceMode($('#chk-pakai-invoice').is(':checked'));
// }

// function toggleInvoiceMode(pakaiInvoice) {
//   if (pakaiInvoice) {
//     $('#row-inv-gabung').show();
//     $('#btn-pilih-inv').show(); // ← tambah ini
//     $('#btn-pilih-hrl').hide().addClass('d-none');
//     unlockNilai();
//   } else {
//     document.getElementById('add-no-inv-gabung').value = '';
//     document.getElementById('add-id-hrl').value = '';
//     document.getElementById('add-nilai').value = '';
//     $('#row-inv-gabung').hide();
//     $('#btn-pilih-inv').hide();
//     $('#btn-pilih-hrl').hide().addClass('d-none');
//     unlockNilai();
//     hitungSisa();
//   }
// }

function toggleJenis(kode) {
  rebuildTransaksiSelect('#add-transaksi', kode, '', 'Pilih Transaksi');

  document.getElementById('add-no-inv-gabung').value = '';
  document.getElementById('add-id-hrl').value = '';
  document.getElementById('add-nilai').value = '';
  document.getElementById('add-pph').value = '';
  hitungSisa();
  unlockNilai();
  unlockPph(); // reset PPH agar tidak ikut ter-lock dari state jenis sebelumnya

  const umjSection = document.getElementById('section-uang-muka');
  if (kode === 'PENERIMAAN') {
    $('#label-inv-gabung').text('No. Inv. Gabung (LCR)');
    $('#btn-pilih-inv').data('tipe', 'lcr');
    if (umjSection) $(umjSection).show();
  } else if (kode === 'PENGELUARAN') {
    $('#label-inv-gabung').text('No. Inv. Gabung (LSR)');
    $('#btn-pilih-inv').data('tipe', 'lsr');
    if (umjSection) $(umjSection).hide();
    clearUmjFields();
  } else {
    $('#label-inv-gabung').text('No. Inv. Gabung');
    $('#btn-pilih-inv').data('tipe', 'lcr');
    if (umjSection) $(umjSection).hide();
  }

  toggleInvoiceMode($('#chk-pakai-invoice').is(':checked'));
}

function toggleInvoiceMode(pakaiInvoice) {
  if (pakaiInvoice) {
    $('#row-inv-gabung').show();
    $('#btn-pilih-inv').show();
    $('#btn-pilih-hrl').hide().addClass('d-none');
    unlockNilai();
  } else {
    document.getElementById('add-no-inv-gabung').value = '';
    document.getElementById('add-id-hrl').value = '';
    document.getElementById('add-nilai').value = '';
    $('#row-inv-gabung').hide();
    $('#btn-pilih-inv').hide();
    $('#btn-pilih-hrl').hide().addClass('d-none');
    unlockNilai();
    hitungSisa();
  }
}

function clearUmjFields() {
  ['add-no-uang-muka', 'add-nama-uang-muka', 'add-jenis-uang-muka', 'add-tanggal-uang-muka', 'add-dp'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  $('#row-nama-uang-muka').hide();
  $('#row-info-uang-muka').hide();
  $('#btn-hapus-umj').hide();
  lockUmjFields();
}

function populateUmjFields(umj) {
  const setVal = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.value = val || '';
  };
  setVal('add-no-uang-muka', umj.no_uang_muka);
  setVal('add-nama-uang-muka', umj.nama);
  setVal('add-jenis-uang-muka', umj.jenis_penerimaan);
  setVal('add-tanggal-uang-muka', umj.tanggal);
  setVal('add-dp', umj.dp_fmt);
  if (umj.no_uang_muka) {
    if (umj.nama) $('#row-nama-uang-muka').show();
    else $('#row-nama-uang-muka').hide();
    if (umj.jenis_penerimaan || umj.tanggal) $('#row-info-uang-muka').show();
    else $('#row-info-uang-muka').hide();
    $('#btn-hapus-umj').show();
  } else {
    $('#row-nama-uang-muka').hide();
    $('#row-info-uang-muka').hide();
    $('#btn-hapus-umj').hide();
  }
  lockUmjFields();
}

function rebuildTransaksiSelect(selector, kode, emptyVal, emptyText) {
  const $sel = $(selector);
  if (!$sel.length) return;
  if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
  $sel.empty().append(new Option(emptyText, emptyVal, true, false));
  let opts = [];
  if (!kode || kode === 'all') {
    Object.values(_transaksiCache).forEach(arr =>
      arr.forEach(o => {
        if (!opts.find(x => x.value === o.value)) opts.push(o);
      })
    );
  } else {
    opts = _transaksiCache[kode] || [];
  }
  opts.forEach(opt => $sel.append(new Option(opt.text, opt.value, false, false)));
  const $parent = $sel.parent().hasClass('position-relative')
    ? $sel.parent()
    : $sel.wrap('<div class="position-relative"></div>').parent();
  $sel.select2({ placeholder: emptyText, allowClear: true, width: '100%', dropdownParent: $parent });
}

function filterRekeningByBank(kodeBank, preselect) {
  const $sel = $('#add-no-rekening');
  if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
  $sel.empty().append(new Option('Pilih No. Rekening', '', true, false));
  if (!kodeBank) {
    initSelect2Rekening($sel);
    return;
  }
  fetch(`${baseUrl}input-bank-list?rekening=1&kode_bank=${kodeBank}`)
    .then(r => r.json())
    .then(data => {
      data
        .filter(i => i.no_rekening?.trim())
        .forEach(item => {
          $sel.append(new Option(item.no_rekening, item.no_rekening, false, item.no_rekening === preselect));
        });
      initSelect2Rekening($sel);
      if (preselect) $sel.val(preselect).trigger('change');
      else if ($sel.find('option[value!=""]').length === 1)
        $sel.val($sel.find('option[value!=""]').first().val()).trigger('change');
    })
    .catch(() => initSelect2Rekening($sel));
}

function initSelect2Rekening($sel) {
  if (!$sel.parent().hasClass('position-relative')) $sel.wrap('<div class="position-relative"></div>');
  $sel.select2({ placeholder: 'Pilih No. Rekening', allowClear: true, width: '100%', dropdownParent: $sel.parent() });
}

function hitungSisa() {
  const nilai = parseAngka($('#add-nilai').val());
  const dp = parseAngka($('#add-dp').val());
  const pph = parseAngka($('#add-pph').val());
  const admin = parseAngka($('#add-biaya-admin').val());
  const merimen = parseAngka($('#add-biaya-merimen').val());
  // const sisa = nilai - dp - pph - admin;
  const sisa = (nilai + pph + admin + merimen) - dp;
  $('#add-sisa').val(sisa.toLocaleString('en-US'));
  if (sisa < 0) {
    $('#add-sisa').removeClass('text-primary').addClass('text-danger');
    $('#sisa-warning').show();
  } else {
    $('#add-sisa').removeClass('text-danger').addClass('text-primary');
    $('#sisa-warning').hide();
  }
}

function resetSisaStyle() {
  $('#add-sisa').removeClass('text-danger').addClass('text-primary');
  $('#sisa-warning').hide();
}

function lockNilai() {
  const el = document.getElementById('add-nilai');
  if (!el) return;
  el.readOnly = true;
  el.classList.add('input-readonly');
}
function unlockNilai() {
  const el = document.getElementById('add-nilai');
  if (!el) return;
  el.readOnly = false;
  el.classList.remove('input-readonly');
}

function lockPph() {
  const el = document.getElementById('add-pph');
  if (!el) return;
  el.readOnly = true;
  el.classList.add('input-readonly');

  const el2 = document.getElementById('add-biaya-merimen');
  if (!el2) return;
  el2.readOnly = true;
  el2.classList.add('input-readonly');
}
function unlockPph() {
  const el = document.getElementById('add-pph');
  if (!el) return;
  el.readOnly = false;
  el.classList.remove('input-readonly');

  const el2 = document.getElementById('add-biaya-merimen');
  if (!el2) return;
  el2.readOnly = false;
  el2.classList.remove('input-readonly');
}

function parseAngka(val) {
  return parseFloat(String(val).replace(/,/g, '')) || 0;
}

function formatAngka(val) {
  const n = parseFloat(String(val).replace(/[^0-9]/g, '')) || 0;
  return n ? n.toLocaleString('en-US') : '';
}

function setSelectValue(selector, value, textIfMissing) {
  const $el = $(selector);
  if (!$el.length) return;
  const val = value ?? '';
  if (val !== '' && !$el.find(`option[value="${val}"]`).length)
    $el.append(new Option(textIfMissing ?? val, val, false, false));
  $el.val(val === '' ? null : String(val));
  if ($el.hasClass('select2-hidden-accessible')) $el.trigger('change');
}

function clearSelect(selector) {
  const $el = $(selector);
  if (!$el.length) return;
  $el.val(null);
  if ($el.hasClass('select2-hidden-accessible')) $el.trigger('change');
}

function alertAksesDitolak(aksi) {
  Swal.fire({
    icon: 'error',
    title: 'Akses Ditolak',
    text: `Anda tidak memiliki izin untuk ${aksi} data.`,
    customClass: { confirmButton: 'btn btn-primary' }
  });
}

function alertPilihData() {
  Swal.fire({
    icon: 'warning',
    title: 'Peringatan',
    text: 'Silahkan pilih data pada tabel terlebih dahulu!',
    customClass: { confirmButton: 'btn btn-primary' }
  });
}

function lockUmjFields() {
  ['add-no-uang-muka', 'add-nama-uang-muka', 'add-jenis-uang-muka', 'add-tanggal-uang-muka', 'add-dp'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.readOnly = true;
      el.classList.add('input-readonly');
    }
  });
}

function unlockUmjFields() {
  ['add-no-uang-muka', 'add-nama-uang-muka', 'add-jenis-uang-muka', 'add-tanggal-uang-muka', 'add-dp'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.readOnly = false;
      el.classList.remove('input-readonly');
    }
  });
}

// function clearFormData() {
//   document.getElementById('addNewDataForm')?.reset();
//   [
//     'add-no-voucher',
//     'add-nilai',
//     'add-pph',
//     'add-biaya-admin',
//     'add-sisa',
//     'add-no-inv-gabung',
//     'add-keterangan'
//   ].forEach(id => {
//     const el = document.getElementById(id);
//     if (el) el.value = '';
//   });

//   const tgl = document.getElementById('add-tanggal');
//   if (tgl?._flatpickr) tgl._flatpickr.setDate(new Date(), true);

//   clearSelect('#add-jenis');
//   clearSelect('#add-account-coa');
//   clearSelect('#add-kode-bank');
//   rebuildTransaksiSelect('#add-transaksi', '', '', 'Pilih Jenis dahulu');
//   filterRekeningByBank('', '');
//   clearUmjFields();
//   $('#section-uang-muka').hide();
//   resetSisaStyle();
//   unlockNilai();
//   clearSelect('#add-no-spk');
//   $('#label-inv-gabung').text('No. Inv. Gabung');
//   $('#btn-pilih-inv').data('tipe', 'lcr');
//   $('#chk-pakai-invoice').prop('checked', false);
//   $('#row-inv-gabung').hide();
// }
function clearFormData() {
  document.getElementById('addNewDataForm')?.reset();
  [
    'add-no-voucher',
    'add-nilai',
    'add-pph',
    'add-biaya-admin',
    'add-sisa',
    'add-no-inv-gabung',
    'add-keterangan'
  ].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });

  const tgl = document.getElementById('add-tanggal');
  if (tgl?._flatpickr) tgl._flatpickr.setDate(new Date(), true);

  clearSelect('#add-jenis');
  clearSelect('#add-account-coa');
  clearSelect('#add-kode-bank');
  rebuildTransaksiSelect('#add-transaksi', '', '', 'Pilih Jenis dahulu');
  filterRekeningByBank('', '');
  clearUmjFields();
  $('#section-uang-muka').hide();
  resetSisaStyle();
  unlockNilai();
  unlockPph();
  clearSelect('#add-no-spk');
  $('#label-inv-gabung').text('No. Inv. Gabung');
  $('#btn-pilih-inv').data('tipe', 'lcr');
  $('#chk-pakai-invoice').prop('checked', false);
  $('#row-inv-gabung').hide();
}
