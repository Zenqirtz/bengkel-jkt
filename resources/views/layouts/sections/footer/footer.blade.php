@php
  $containerFooter =
      isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
          ? 'container-xxl'
          : 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
  <div class="container-xxl py-2">
    <div class="d-flex align-items-center gap-3">
      <strong class="text-uppercase small mb-0">PENGUMUMAN:</strong>
      <div class="ticker-wrap flex-grow-1">
        <div class="ticker">
          <!-- Item akan diisi via JS -->
        </div>
      </div>
    </div>
  </div>
  <div class="{{ $containerFooter }}">
    <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
      <div class="mb-2 mb-md-0">
        {{-- &#169; 2025 --}}&copy; {{ date('Y') }}
        , <a href="{{ !empty(config('variables.creatorUrl')) ? config('variables.creatorUrl') : '' }}"
          target="_blank"
          class="footer-link fw-medium">{{ !empty(config('variables.creatorName')) ? config('variables.creatorName') : '' }}</a>
          . All Rights Reserved
      </div>
    </div>
  </div>
</footer>
<!-- / Footer -->
