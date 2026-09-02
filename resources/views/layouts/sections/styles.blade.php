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
@vite(['resources/assets/vendor/libs/typeahead-js/typeahead.scss'])
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

<!-- ===== Global Smooth Animations ===== -->
<style id="global-animations">
  /* ----- Page entrance ----- */
  .layout-page,
  .content-wrapper > :first-child {
    animation: pageEnter 0.45s cubic-bezier(0.22, 1, 0.36, 1) both;
  }
  @keyframes pageEnter {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* ----- Card ----- */
  .card {
    animation: cardSlideUp 0.48s cubic-bezier(0.22, 1, 0.36, 1) both;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
  }
  .card:hover {
    box-shadow: 0 8px 32px rgba(0,0,0,0.13) !important;
    transform: translateY(-2px);
  }
  @keyframes cardSlideUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* ----- Card Header ----- */
  .card-header {
    animation: fadeInDown 0.4s cubic-bezier(0.22, 1, 0.36, 1) 0.07s both;
  }
  @keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* ----- Card title underline on hover ----- */
  .card-header .card-title h5,
  .card-header .card-title h6 {
    position: relative;
    display: inline-block;
  }
  .card-header .card-title h5::after,
  .card-header .card-title h6::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--bs-primary);
    border-radius: 2px;
    transition: width 0.45s cubic-bezier(0.22, 1, 0.36, 1);
  }
  .card-header .card-title h5:hover::after,
  .card-header .card-title h6:hover::after {
    width: 100%;
  }

  /* ----- Buttons ----- */
  .btn {
    position: relative;
    overflow: hidden;
    transition: transform 0.25s cubic-bezier(0.22,1,0.36,1),
                box-shadow 0.25s cubic-bezier(0.22,1,0.36,1) !important;
  }
  .btn:not(:disabled):hover {
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 6px 18px rgba(0,0,0,0.14);
  }
  .btn:not(:disabled):active {
    transform: translateY(0) scale(0.97) !important;
    box-shadow: none !important;
  }
  /* Ripple span injected by JS */
  .btn .ripple-effect {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.35);
    transform: scale(0);
    animation: rippleAnim 0.55s linear forwards;
    pointer-events: none;
  }
  @keyframes rippleAnim {
    to { transform: scale(4); opacity: 0; }
  }

  /* Stagger header buttons pop-in */
  .demo-inline-spacing .btn:nth-child(1) { animation: btnPopIn 0.38s cubic-bezier(0.22,1,0.36,1) 0.12s both; }
  .demo-inline-spacing .btn:nth-child(2) { animation: btnPopIn 0.38s cubic-bezier(0.22,1,0.36,1) 0.22s both; }
  .demo-inline-spacing .btn:nth-child(3) { animation: btnPopIn 0.38s cubic-bezier(0.22,1,0.36,1) 0.32s both; }
  .demo-inline-spacing .btn:nth-child(4) { animation: btnPopIn 0.38s cubic-bezier(0.22,1,0.36,1) 0.42s both; }
  @keyframes btnPopIn {
    from { opacity: 0; transform: translateY(8px) scale(0.9); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
  }

  /* ----- DataTable rows ----- */
  table tbody tr {
    animation: rowFadeIn 0.32s ease both;
    transition: background-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
  }
  table tbody tr:hover {
    transform: scale(1.0015);
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    z-index: 1;
    position: relative;
  }
  @keyframes rowFadeIn {
    from { opacity: 0; transform: translateX(-6px); }
    to   { opacity: 1; transform: translateX(0); }
  }
  /* Stagger first 10 rows */
  table tbody tr:nth-child(1)  { animation-delay: 0.02s; }
  table tbody tr:nth-child(2)  { animation-delay: 0.05s; }
  table tbody tr:nth-child(3)  { animation-delay: 0.08s; }
  table tbody tr:nth-child(4)  { animation-delay: 0.11s; }
  table tbody tr:nth-child(5)  { animation-delay: 0.14s; }
  table tbody tr:nth-child(6)  { animation-delay: 0.17s; }
  table tbody tr:nth-child(7)  { animation-delay: 0.20s; }
  table tbody tr:nth-child(8)  { animation-delay: 0.23s; }
  table tbody tr:nth-child(9)  { animation-delay: 0.26s; }
  table tbody tr:nth-child(10) { animation-delay: 0.29s; }

  /* ----- Modal transitions ----- */
  .modal.fade .modal-dialog {
    transition: transform 0.36s cubic-bezier(0.22,1,0.36,1),
                opacity   0.36s ease !important;
    transform: translateY(-28px) scale(0.97);
    opacity: 0;
  }
  .modal.show .modal-dialog {
    transform: translateY(0) scale(1);
    opacity: 1;
  }
  .modal-content {
    box-shadow: 0 20px 60px rgba(0,0,0,0.18);
  }

  /* ----- Form inputs focus glow ----- */
  .form-control,
  .form-select {
    transition: border-color 0.22s ease,
                box-shadow  0.22s ease,
                transform   0.18s ease !important;
  }
  .form-control:focus,
  .form-select:focus {
    transform: scale(1.01);
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.18) !important;
  }

  /* ----- HR divider slide-in ----- */
  hr {
    animation: hrSlide 0.55s cubic-bezier(0.22,1,0.36,1) 0.18s both;
    transform-origin: left;
  }
  @keyframes hrSlide {
    from { transform: scaleX(0); opacity: 0; }
    to   { transform: scaleX(1); opacity: 1; }
  }

  /* ----- Checkbox hover ----- */
  input[type="checkbox"] {
    transition: transform 0.18s ease;
    cursor: pointer;
  }
  input[type="checkbox"]:hover {
    transform: scale(1.18);
  }

  /* ----- Navbar & menu items ----- */
  .menu-item {
    transition: background-color 0.2s ease, padding-left 0.2s ease;
  }
  .app-brand {
    animation: fadeInDown 0.4s cubic-bezier(0.22,1,0.36,1) 0.05s both;
  }
</style>
