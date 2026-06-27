#!/usr/bin/env bash
# Download third-party front-end libraries for offline/local use.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
VENDOR="$ROOT/public/assets/vendor"

mkdir -p "$VENDOR"/{jquery,sweetalert2,select2,datatables/{css,js},jszip,pdfmake,chart.js,html5-qrcode,qrcodejs,fullcalendar,apexcharts,html2pdf,jspdf,html2canvas}

curl -fsSL -o "$VENDOR/jquery/jquery-3.6.0.min.js" https://code.jquery.com/jquery-3.6.0.min.js
curl -fsSL -o "$VENDOR/sweetalert2/sweetalert2.min.js" https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js
curl -fsSL -o "$VENDOR/sweetalert2/sweetalert2.min.css" https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css
curl -fsSL -o "$VENDOR/select2/select2.min.js" https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js
curl -fsSL -o "$VENDOR/select2/select2.min.css" https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css
curl -fsSL -o "$VENDOR/select2/select2-bootstrap-5-theme.min.css" https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css
curl -fsSL -o "$VENDOR/datatables/css/dataTables.bootstrap5.min.css" https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css
curl -fsSL -o "$VENDOR/datatables/css/buttons.bootstrap5.min.css" https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css
curl -fsSL -o "$VENDOR/datatables/js/jquery.dataTables.min.js" https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js
curl -fsSL -o "$VENDOR/datatables/js/dataTables.bootstrap5.min.js" https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js
curl -fsSL -o "$VENDOR/datatables/js/dataTables.buttons.min.js" https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js
curl -fsSL -o "$VENDOR/datatables/js/buttons.bootstrap5.min.js" https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js
curl -fsSL -o "$VENDOR/datatables/js/buttons.html5.min.js" https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js
curl -fsSL -o "$VENDOR/datatables/js/buttons.print.min.js" https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js
curl -fsSL -o "$VENDOR/jszip/jszip.min.js" https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js
curl -fsSL -o "$VENDOR/pdfmake/pdfmake.min.js" https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js
curl -fsSL -o "$VENDOR/pdfmake/vfs_fonts.js" https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js
curl -fsSL -o "$VENDOR/chart.js/chart.umd.min.js" https://cdn.jsdelivr.net/npm/chart.js/dist/chart.umd.min.js
curl -fsSL -o "$VENDOR/html5-qrcode/html5-qrcode.min.js" https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js
curl -fsSL -o "$VENDOR/qrcodejs/qrcode.min.js" https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js
curl -fsSL -o "$VENDOR/fullcalendar/main.min.js" https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js
curl -fsSL -o "$VENDOR/fullcalendar/main.min.css" https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css
curl -fsSL -o "$VENDOR/apexcharts/apexcharts.min.js" https://cdn.jsdelivr.net/npm/apexcharts/dist/apexcharts.min.js
curl -fsSL -o "$VENDOR/html2pdf/html2pdf.bundle.min.js" https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js
curl -fsSL -o "$VENDOR/jspdf/jspdf.umd.min.js" https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js
curl -fsSL -o "$VENDOR/html2canvas/html2canvas.min.js" https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js

mkdir -p "$ROOT/public/assets/plugins/datatable/css" "$ROOT/public/assets/plugins/datatable/js"
cp "$VENDOR/datatables/css/dataTables.bootstrap5.min.css" "$ROOT/public/assets/plugins/datatable/css/"
cp "$VENDOR/datatables/js/jquery.dataTables.min.js" "$ROOT/public/assets/plugins/datatable/js/"
cp "$VENDOR/datatables/js/dataTables.bootstrap5.min.js" "$ROOT/public/assets/plugins/datatable/js/"
cp "$VENDOR/jquery/jquery-3.6.0.min.js" "$ROOT/public/assets/js/jquery.min.js"

echo "Vendor assets synced to $VENDOR"
