<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Book</title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            height: 100%;
            background: #2b2b2b;
            font-family: -apple-system, Segoe UI, Roboto, sans-serif;
        }

        /* ── TOPBAR: satu baris di desktop ── */
        #topbar {
            display: none;
            position: sticky;
            top: 0;
            z-index: 10;
            background: #1f1f1f;
            border-bottom: 1px solid #3a3a3a;
            padding: 8px 14px;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: nowrap;
        }

        #topbar-nav,
        #topbar-search {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        #topbar button {
            background: #444;
            color: #fff;
            border: none;
            padding: 7px 13px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
            white-space: nowrap;
            transition: background 0.15s;
        }

        #topbar button:hover {
            background: #555;
        }

        #topbar button:active {
            background: #666;
        }

        #pageInfo {
            font-size: 13px;
            color: #ccc;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        #pageInput {
            width: 44px;
            background: #2b2b2b;
            border: 1px solid #555;
            color: #fff;
            text-align: center;
            border-radius: 4px;
            font-size: 13px;
            padding: 4px 2px;
        }

        #pageInput::-webkit-outer-spin-button,
        #pageInput::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        #downloadBtn {
            text-decoration: none;
        }

        #downloadBtn button {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        #loadProgress {
            font-size: 12px;
            color: #7fd17f;
            white-space: nowrap;
        }

        #searchInput {
            background: #2b2b2b;
            border: 1px solid #555;
            color: #fff;
            padding: 7px 10px;
            border-radius: 6px;
            font-size: 13px;
            width: 180px;
        }

        #searchInput::placeholder {
            color: #888;
        }

        #searchStatus {
            font-size: 12px;
            color: #f0ad4e;
            white-space: nowrap;
        }

        /* ── MOBILE: dua baris ── */
        @media (max-width: 520px) {
            #topbar {
                flex-direction: column;
                align-items: stretch;
                gap: 7px;
                padding: 8px 10px;
            }

            #topbar-nav,
            #topbar-search {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }

            #downloadBtn .dlLabel {
                display: none;
            }

            #searchInput {
                flex: 1 1 120px;
                width: auto;
            }
        }

        /* ── BOOK AREA ── */
        #wrapper {
            width: 100%;
            min-height: calc(100vh - 60px);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #loading {
            color: #ccc;
            font-size: 14px;
        }

        #book {
            margin: 20px auto;
        }

        /* ── SEARCH HIGHLIGHT ── */
        #searchHighlight {
            position: fixed;
            display: none;
            pointer-events: none;
            background: rgba(255, 221, 0, 0.40);
            border: 2px solid #ffdd00;
            border-radius: 3px;
            box-shadow: 0 0 10px rgba(255, 221, 0, 0.75);
            z-index: 50;
            transition: opacity 0.35s ease;
            opacity: 1;
        }
    </style>
</head>

<body>

    <div id="topbar">
        <!-- Navigasi halaman + download -->
        <div id="topbar-nav">
            <button id="prevBtn" title="Halaman sebelumnya">&#9664;</button>
            <span id="pageInfo">
                <input type="number" id="pageInput" min="1" value="1">
                <span>/ <span id="pageTotal">0</span></span>
            </span>
            <button id="nextBtn" title="Halaman selanjutnya">&#9654;</button>
            <a id="downloadBtn" href="{{ route('manual-book.download') }}">
                <button type="button" title="Download PDF">&#11015; <span class="dlLabel">Download PDF</span></button>
            </a>
            <span id="loadProgress"></span>
        </div>

        <!-- Pencarian -->
        <div id="topbar-search">
            <input type="text" id="searchInput" placeholder="Cari kata di manual...">
            <button id="searchBtn">Cari</button>
            <span id="searchStatus"></span>
        </div>
    </div>

    <div id="wrapper">
        <p id="loading">Memuat manual book...</p>
        <div id="book" style="display:none;"></div>
    </div>

    <div id="searchHighlight"></div>

    <!-- PDF.js 3.11.174 — versi 4.x punya bug "pdfjsLib is not defined" -->
    <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/legacy/build/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/legacy/build/pdf.worker.min.js';

        var pdfUrl = "{{ route('manual-book.view') }}";
        var pageTexts = [];
        var pageItems = [];
        var pageImages = [];
        var pageFlip = null;
        var pdfDocRef = null;
        var totalPages = 0;
        var loadedCount = 0;
        var renderScale = 1.5;

        function createPlaceholder(w, h) {
            var c = document.createElement('canvas');
            c.width = w;
            c.height = h;
            var ctx = c.getContext('2d');
            ctx.fillStyle = '#e8e8e8';
            ctx.fillRect(0, 0, w, h);
            ctx.fillStyle = '#aaa';
            ctx.font = '18px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('Memuat halaman...', w / 2, h / 2);
            return c.toDataURL('image/jpeg', 0.7);
        }

        function renderSinglePage(pageNum) {
            return pdfDocRef.getPage(pageNum).then(function(page) {
                var vp = page.getViewport({
                    scale: renderScale
                });
                var canvas = document.createElement('canvas');
                canvas.width = vp.width;
                canvas.height = vp.height;
                var ctx = canvas.getContext('2d');

                var renderTask = page.render({
                    canvasContext: ctx,
                    viewport: vp
                }).promise;
                var textTask = page.getTextContent().then(function(tc) {
                    pageTexts[pageNum - 1] = tc.items.map(function(it) {
                        return it.str;
                    }).join(' ').toLowerCase();
                    pageItems[pageNum - 1] = {
                        items: tc.items,
                        viewport: vp
                    };
                });

                return Promise.all([renderTask, textTask]).then(function() {
                    return canvas.toDataURL('image/jpeg', 0.85);
                });
            });
        }

        fetch(pdfUrl)
            .then(function(res) {
                if (!res.ok) throw new Error('Manual book tidak ditemukan');
                return res.arrayBuffer();
            })
            .then(function(buffer) {
                return pdfjsLib.getDocument({
                    data: buffer,
                    verbosity: pdfjsLib.VerbosityLevel.ERRORS
                }).promise;
            })
            .then(function(pdf) {
                pdfDocRef = pdf;
                totalPages = pdf.numPages;
                return pdf.getPage(1).then(function(page) {
                    var vp = page.getViewport({
                        scale: renderScale
                    });
                    pageImages = new Array(totalPages).fill(createPlaceholder(vp.width, vp.height));
                });
            })
            .then(function() {
                return renderSinglePage(1).then(function(dataUrl) {
                    pageImages[0] = dataUrl;
                    loadedCount = 1;
                    renderBook(pageImages);
                    loadRemainingPagesInBackground();
                });
            })
            .catch(function(err) {
                document.getElementById('loading').textContent = 'Gagal memuat: ' + err.message;
            });

        function loadRemainingPagesInBackground() {
            var CONCURRENCY = 3;
            var nextPage = 2;
            var activeCount = 0;
            var updateScheduled = false;

            updateProgressUI();

            function scheduleFlipbookUpdate() {
                if (updateScheduled) return;
                updateScheduled = true;
                setTimeout(function() {
                    updateScheduled = false;
                    if (pageFlip && typeof pageFlip.updateFromImages === 'function') {
                        pageFlip.updateFromImages(pageImages);
                    }
                }, 250);
            }

            function updateProgressUI() {
                var el = document.getElementById('loadProgress');
                if (!el) return;
                el.textContent = loadedCount >= totalPages ? '' : 'Memuat: ' + loadedCount + '/' + totalPages;
            }

            function startNext() {
                while (activeCount < CONCURRENCY && nextPage <= totalPages) {
                    (function(pn) {
                        activeCount++;
                        renderSinglePage(pn)
                            .then(function(url) {
                                pageImages[pn - 1] = url;
                            })
                            .catch(function(e) {
                                console.error('Gagal hal. ' + pn, e);
                            })
                            .then(function() {
                                loadedCount++;
                                activeCount--;
                                updateProgressUI();
                                scheduleFlipbookUpdate();
                                startNext();
                            });
                    })(nextPage);
                    nextPage++;
                }
            }

            startNext();
        }

        function renderBook(images) {
            document.getElementById('loading').style.display = 'none';
            var bookEl = document.getElementById('book');
            bookEl.style.display = 'block';
            document.getElementById('topbar').style.display = 'flex';

            pageImages = images;

            pageFlip = new St.PageFlip(bookEl, {
                width: 500,
                height: 700,
                size: 'stretch',
                minWidth: 300,
                maxWidth: 900,
                minHeight: 400,
                maxHeight: 1200,
                showCover: false
            });
            pageFlip.loadFromImages(images);

            var pageInput = document.getElementById('pageInput');
            var pageTotalEl = document.getElementById('pageTotal');
            pageInput.max = images.length;
            pageTotalEl.textContent = images.length;

            function updateInfo() {
                pageInput.value = pageFlip.getCurrentPageIndex() + 1;
            }
            updateInfo();
            pageFlip.on('flip', updateInfo);

            function goToPage() {
                var t = parseInt(pageInput.value, 10);
                if (isNaN(t)) return;
                t = Math.max(1, Math.min(t, images.length));
                pageInput.value = t;
                hideHighlight();
                pageFlip.flip(t - 1);
            }

            pageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    goToPage();
                }
            });
            pageInput.addEventListener('blur', goToPage);

            document.getElementById('prevBtn').addEventListener('click', function() {
                hideHighlight();
                pageFlip.flipPrev();
            });
            document.getElementById('nextBtn').addEventListener('click', function() {
                hideHighlight();
                pageFlip.flipNext();
            });

            /* ── Highlight ── */
            function hideHighlight() {
                var hl = document.getElementById('searchHighlight');
                clearTimeout(hl._fadeTimer);
                hl.style.display = 'none';
            }

            function showHighlight(pageIndex, rect, retriesLeft) {
                if (retriesLeft === undefined) retriesLeft = 8;
                var targetSrc = pageImages[pageIndex];
                var imgs = bookEl.querySelectorAll('img');
                var targetImg = null,
                    bestArea = 0;

                for (var i = 0; i < imgs.length; i++) {
                    if (imgs[i].src === targetSrc) {
                        var r = imgs[i].getBoundingClientRect();
                        var area = r.width * r.height;
                        if (area > bestArea) {
                            bestArea = area;
                            targetImg = imgs[i];
                        }
                    }
                }

                if (!targetImg || bestArea === 0) {
                    if (retriesLeft <= 0) return;
                    setTimeout(function() {
                        showHighlight(pageIndex, rect, retriesLeft - 1);
                    }, 200);
                    return;
                }

                var ir = targetImg.getBoundingClientRect();
                var scaleX = ir.width / rect.canvasWidth;
                var scaleY = ir.height / rect.canvasHeight;
                var hl = document.getElementById('searchHighlight');
                clearTimeout(hl._fadeTimer);
                hl.style.left = (ir.left + rect.left * scaleX) + 'px';
                hl.style.top = (ir.top + rect.top * scaleY) + 'px';
                hl.style.width = (rect.width * scaleX) + 'px';
                hl.style.height = (rect.height * scaleY) + 'px';
                hl.style.display = 'block';
                hl.style.opacity = '1';
            }

            /* ── Search ── */
            function findAllMatches(query) {
                var matches = [];
                for (var p = 0; p < pageItems.length; p++) {
                    var data = pageItems[p];
                    if (!data) continue;
                    for (var i = 0; i < data.items.length; i++) {
                        var it = data.items[i];
                        if (it.str && it.str.toLowerCase().indexOf(query) !== -1) {
                            var tx = pdfjsLib.Util.transform(data.viewport.transform, it.transform);
                            var fontHeight = Math.hypot(tx[2], tx[3]) || 12;
                            var fontWidth = Math.hypot(tx[0], tx[1]) || 1;
                            var width = (it.width || it.str.length * 5) * fontWidth;
                            matches.push({
                                pageIndex: p,
                                rect: {
                                    left: tx[4],
                                    top: tx[5] - fontHeight,
                                    width: width,
                                    height: fontHeight * 1.3,
                                    canvasWidth: data.viewport.width,
                                    canvasHeight: data.viewport.height
                                }
                            });
                        }
                    }
                }
                return matches;
            }

            var currentMatches = [];
            var currentMatchPos = -1;
            var lastQuery = '';

            function goToMatch(idx) {
                if (!currentMatches.length) return;
                if (idx < 0) idx = currentMatches.length - 1;
                if (idx >= currentMatches.length) idx = 0;
                currentMatchPos = idx;
                var match = currentMatches[idx];
                document.getElementById('searchStatus').textContent =
                    'Hasil ' + (idx + 1) + ' / ' + currentMatches.length +
                    ' (hal. ' + (match.pageIndex + 1) + ')';
                hideHighlight();
                pageFlip.flip(match.pageIndex);
                setTimeout(function() {
                    showHighlight(match.pageIndex, match.rect);
                }, 450);
            }

            function doSearch() {
                var query = document.getElementById('searchInput').value.trim().toLowerCase();
                var statusEl = document.getElementById('searchStatus');

                // Query berubah → cari ulang dari awal
                if (query !== lastQuery) {
                    hideHighlight();
                    lastQuery = query;

                    if (!query) {
                        statusEl.textContent = '';
                        currentMatches = [];
                        currentMatchPos = -1;
                        return;
                    }

                    currentMatches = findAllMatches(query);
                    currentMatchPos = -1;

                    if (!currentMatches.length) {
                        statusEl.textContent = loadedCount < totalPages ?
                            'Tidak ditemukan (masih memuat...)' :
                            'Tidak ditemukan';
                        return;
                    }

                    goToMatch(0);
                    return;
                }

                // Query sama → loncat ke hasil berikutnya
                if (!currentMatches.length) {
                    statusEl.textContent = 'Tidak ditemukan';
                    return;
                }
                goToMatch(currentMatchPos + 1);
            }

            document.getElementById('searchBtn').addEventListener('click', doSearch);
            document.getElementById('searchInput').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') doSearch();
            });

            // Kalau input diubah, reset supaya Cari berikutnya mulai dari awal lagi
            document.getElementById('searchInput').addEventListener('input', function() {
                lastQuery = null;
            });

            window.addEventListener('resize', function() {
                document.getElementById('searchHighlight').style.display = 'none';
            });
        }
    </script>
</body>

</html>
