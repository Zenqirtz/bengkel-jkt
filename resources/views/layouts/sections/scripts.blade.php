<!-- BEGIN: Vendor JS-->

@vite(['resources/assets/vendor/libs/jquery/jquery.js', 'resources/assets/vendor/libs/popper/popper.js', 'resources/assets/vendor/js/bootstrap.js', 'resources/assets/vendor/libs/node-waves/node-waves.js', 'resources/assets/vendor/libs/@algolia/autocomplete-js.js'])


@if ($configData['hasCustomizer'])
  @vite('resources/assets/vendor/libs/pickr/pickr.js')
@endif

@vite(['resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js', 'resources/assets/vendor/libs/hammer/hammer.js', 'resources/assets/vendor/js/menu.js'])

@yield('vendor-script')
<!-- END: Page Vendor JS-->

<!-- BEGIN: Theme JS-->
@vite(['resources/assets/js/main.js'])
<!-- END: Theme JS-->

<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->

<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->

<!-- app JS -->
@vite(['resources/js/app.js'])
<!-- END: app JS-->

{{-- Manual Book --}}
@auth
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
    ])
@endauth

<script>
  function PleaseWaitPage() {
    Loading.standard({
      backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
      svgSize: '0px'
    });
    let customSpinnerHTML = `
        <div class="d-flex">
            <p class="mb-0 text-white">Please wait...</p>
            <div class="sk-wave m-0">
                <div class="sk-rect sk-wave-rect"></div>
                <div class="sk-rect sk-wave-rect"></div>
                <div class="sk-rect sk-wave-rect"></div>
                <div class="sk-rect sk-wave-rect"></div>
                <div class="sk-rect sk-wave-rect"></div>
            </div>
        </div>
      `;
    let notiflixBlock = document.querySelector('.notiflix-loading');
    notiflixBlock.innerHTML = customSpinnerHTML;
  }

  (async function () {

    // const endpoint = '/api/news';
    const endpoint = '{{ url("/api/news") }}';

    // const SPEED = 65;              // px per detik (atur selera)
    // const MIN_S = 20, MAX_S = 45;  // batas bawah/atas durasi
    // // const endpoint = '/api/news';


    const SPEED = 65;              // px per detik
    const MIN_S = 20, MAX_S = 120; // Update: Batas atas diperbesar agar teks panjang tidak terlalu ngebut
    // const endpoint = '/api/news';

    const ticker = document.querySelector('.ticker');
    if (!ticker) return;

    try {
      const res = await fetch(endpoint, { headers: { 'Accept': 'application/json' }});
      const data = await res.json();
      const items = Array.isArray(data) ? data : (data.items || []);

      // Bersihkan isi awal
      ticker.innerHTML = '';

      if (!items.length) {
         ticker.innerHTML = '<span class="ticker-item text-body">Belum ada berita terbaru.</span>';
      } else {
          items.forEach(txt => {
            const span = document.createElement('span');
            span.className = 'ticker-item text-body';
            span.textContent = txt;
            ticker.appendChild(span);
          });
      }
    } catch (e) {
      ticker.innerHTML = '<span class="ticker-item text-body">Gagal memuat berita.</span>';
    }


    // Tunggu render selesai untuk hitung lebar

    requestAnimationFrame(() => {
      // w = lebar total elemen (termasuk padding-left 100% dari CSS)
      const w = ticker.scrollWidth;

      // Jarak tempuh adalah total lebar elemen itu sendiri
      let dur = w / SPEED;

      dur = Math.max(MIN_S, Math.min(MAX_S, dur));
      ticker.style.setProperty('--dur', dur + 's');

      // HAPUS logic start negatif/tengah. Mulai dari 0 agar fresh dari kanan.
      ticker.style.setProperty('--start', '0s');
    });
  })();
</script>

<script>
  /* ===== Global Ripple Effect on Buttons ===== */
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn');
    if (!btn || btn.disabled) return;
    var ripple = document.createElement('span');
    ripple.classList.add('ripple-effect');
    var rect = btn.getBoundingClientRect();
    var size = Math.max(rect.width, rect.height);
    ripple.style.cssText = 'width:' + size + 'px;height:' + size + 'px;left:' + (e.clientX - rect.left - size / 2) + 'px;top:' + (e.clientY - rect.top - size / 2) + 'px;position:absolute;';
    btn.appendChild(ripple);
    ripple.addEventListener('animationend', function() { ripple.remove(); });
  });

  /* ===== Re-animate DataTable rows on every draw ===== */
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined') {
      $(document).on('draw.dt', 'table', function() {
        $(this).find('tbody tr').each(function(i) {
          var tr = this;
          tr.style.animation = 'none';
          void tr.offsetHeight;
          tr.style.animation = '';
          tr.style.animationDelay = (i * 0.04) + 's';
        });
      });
    }
  });
</script>
