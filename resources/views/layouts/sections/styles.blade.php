<!-- BEGIN: Theme CSS-->
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap" rel="stylesheet" />

<!-- Fonts Icons -->
@vite(['resources/assets/vendor/fonts/iconify/iconify.css'])

<!-- BEGIN: Vendor CSS-->
@vite(['resources/assets/vendor/libs/node-waves/node-waves.scss'])

@if ($configData['hasCustomizer'])
  @vite(['resources/assets/vendor/libs/pickr/pickr-themes.scss'])
@endif

<!-- Core CSS -->
@vite(['resources/assets/vendor/scss/core.scss', 'resources/assets/css/demo.css', 'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss'])

<!-- Vendor Styles -->
@vite(['resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss', 'resources/assets/vendor/libs/typeahead-js/typeahead.scss'])
@yield('vendor-style')

<!-- Page Styles -->
@yield('page-style')

<!-- app CSS -->
@vite(['resources/css/app.css'])
<!-- END: app CSS-->

<style>
  .ticker-wrap{
    overflow: hidden;
    position: relative;
    height: 44px;
    line-height: 44px;                    /* tinggi bar */
    /* mask-image: linear-gradient(to right, transparent, black 20%, black 80%, transparent);
    -webkit-mask-image: linear-gradient(to right, transparent, black 20%, black 80%, transparent); */
    mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);
  -webkit-mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);
  }

  .ticker{
    display: inline-block;
    white-space: nowrap;
    will-change: transform;
    animation: tickerMove var(--dur, 30s) linear infinite;
    animation-delay: var(--start, 0s); /* bisa pakai delay negatif */
    /* KUNCI: Padding ini membuat teks mulai dari luar layar kanan */
    padding-left: 100%; 
    box-sizing: content-box; /* Pastikan padding dihitung sebagai tambahan lebar */
  }
  .ticker:hover{ animation-play-state: paused; }

  /* ==> bentuk “alert merah” */
  .ticker-item{
    display: inline-flex;                 /* mudah center vertikal */
    align-items: center;
    background: var(--bs-danger, #ff5b5c);
    color: #fff !important;               /* override .text-body */
    border-radius: .6rem;
    line-height: 1.25;                    /* reset, agar tidak kepotong */
    margin-right: 1rem;                   /* jarak antar item */
    vertical-align: middle;
    font-size: 1.05rem;     /* ±5% lebih besar */
    padding: .35rem .85rem; /* seimbangkan tinggi */
  }

  .form-control.input-wajib,
  .form-select.input-wajib {
      border-color: #ff4d49 !important;
      /* box-shadow:0 0 .25rem .05rem rgba(255, 77, 73,.1); */
      border-width: 2px;
  }

  /* Opsional: Agar Select2 juga ikut merah jika Anda menggunakannya */
  .input-wajib + .select2-container--default .select2-selection--single {
      border-color: #ff4d49 !important;
      /* box-shadow:0 0 .25rem .05rem rgba(255, 77, 73,.1); */
      border-width: 2px;
  }

  @keyframes tickerMove{
    /* 0%   { transform: translateX(100%); }
    100% { transform: translateX(-100%); } */
    0%   { transform: translate3d(0, 0, 0); }
    100% { transform: translate3d(-100%, 0, 0); }
  }
</style>
