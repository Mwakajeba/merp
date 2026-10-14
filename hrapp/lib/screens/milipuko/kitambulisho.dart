import 'dart:math' as math;
import 'dart:typed_data';

import 'package:http/http.dart' as http;
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';

import '../../services/milipuko_service.dart';

const _kijani = PdfColor.fromInt(0xFF0B6E4F);
const _machungwa = PdfColor.fromInt(0xFFE85D04);
const _buluu = PdfColor.fromInt(0xFF1A4F8C);

bool _niUsajiliWaMs(String usajili) =>
    RegExp(r'^MS\.\d{4}$', caseSensitive: false).hasMatch(usajili.trim());

Future<Uint8List> jengaKitambulisho(Map<String, dynamic> mtu) async {
  final jina = (mtu['jina'] ?? '').toString();
  final usajili = (mtu['namba_ya_usajili'] ?? '').toString().trim();
  final jinsia = (mtu['jinsia_label'] ?? '').toString().trim();
  final simu = (mtu['simu'] ?? '').toString();
  final mkoa = (mtu['mkoa'] ?? '').toString();
  final wilaya = (mtu['wilaya'] ?? '').toString();
  final kampuni = (mtu['kampuni'] ?? 'Msasa Gold Mine').toString();
  final simuYaKampuni = (mtu['simu_ya_kampuni'] ?? '').toString().trim();
  final ukaguzi = (mtu['ukaguzi_url'] ?? '').toString();
  final picha = await _picha(
    MilipukoService.mediaUrl(mtu['picha']?.toString()),
  );
  final upande = 22 * PdfPageFormat.mm;
  final kadi = PdfPageFormat(85.6 * PdfPageFormat.mm, 54 * PdfPageFormat.mm);
  final doc = pw.Document();

  doc.addPage(
    pw.Page(
      pageFormat: kadi,
      margin: pw.EdgeInsets.zero,
      build: (context) {
        return pw.Column(
          children: [
            pw.Container(
              color: _niUsajiliWaMs(usajili) ? _machungwa : _kijani,
              padding: const pw.EdgeInsets.symmetric(
                horizontal: 8,
                vertical: 4,
              ),
              child: pw.Row(
                children: [
                  pw.Text(
                    'MILIPUKO',
                    style: pw.TextStyle(
                      color: PdfColors.white,
                      fontSize: 8,
                      fontWeight: pw.FontWeight.bold,
                    ),
                  ),
                  pw.SizedBox(width: 6),
                  pw.Expanded(
                    child: pw.Text(
                      kampuni,
                      textAlign: pw.TextAlign.right,
                      maxLines: 1,
                      style: const pw.TextStyle(
                        color: PdfColors.white,
                        fontSize: 7,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            pw.Expanded(
              child: pw.Padding(
                padding: const pw.EdgeInsets.fromLTRB(7, 4, 7, 4),
                child: pw.Column(
                  mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                  children: [
                    pw.Column(
                      children: [
                        pw.Text(
                          'KITAMBULISHO CHA MLIPUAJI',
                          textAlign: pw.TextAlign.center,
                          style: pw.TextStyle(
                            fontSize: 8,
                            fontWeight: pw.FontWeight.bold,
                            letterSpacing: 0.2,
                          ),
                        ),
                        if (usajili.isNotEmpty)
                          pw.Text(
                            usajili,
                            textAlign: pw.TextAlign.center,
                            style: pw.TextStyle(
                              fontSize: 10,
                              fontWeight: pw.FontWeight.bold,
                            ),
                          ),
                      ],
                    ),
                    pw.Row(
                      crossAxisAlignment: pw.CrossAxisAlignment.start,
                      children: [
                        picha == null
                            ? pw.Container(
                                width: upande,
                                height: upande,
                                color: PdfColors.grey300,
                                alignment: pw.Alignment.center,
                                child: pw.Text(
                                  'Hakuna\npicha',
                                  textAlign: pw.TextAlign.center,
                                  style: const pw.TextStyle(fontSize: 6),
                                ),
                              )
                            : pw.Image(
                                picha,
                                width: upande,
                                height: upande,
                                fit: pw.BoxFit.cover,
                              ),
                        pw.SizedBox(width: 5),
                        pw.Expanded(
                          child: pw.SizedBox(
                            height: upande,
                            child: pw.Column(
                              mainAxisAlignment:
                                  pw.MainAxisAlignment.spaceBetween,
                              crossAxisAlignment: pw.CrossAxisAlignment.start,
                              children: [
                                _mstari('Jina', jina),
                                _mstari('Jinsia', jinsia),
                                _mstari('Simu', simu),
                                _mstari('Mkoa', mkoa),
                                _mstari('Wilaya', wilaya),
                              ],
                            ),
                          ),
                        ),
                        if (ukaguzi.isNotEmpty) ...[
                          pw.SizedBox(width: 4),
                          pw.BarcodeWidget(
                            barcode: pw.Barcode.qrCode(),
                            data: ukaguzi,
                            width: upande,
                            height: upande,
                          ),
                        ],
                      ],
                    ),
                    if (usajili.isNotEmpty)
                      pw.BarcodeWidget(
                        barcode: pw.Barcode.code128(),
                        data: usajili,
                        width: 210,
                        height: 22,
                        drawText: false,
                      )
                    else
                      pw.SizedBox(height: 22),
                  ],
                ),
              ),
            ),
          ],
        );
      },
    ),
  );

  doc.addPage(
    pw.Page(
      pageFormat: kadi,
      margin: pw.EdgeInsets.zero,
      build: (context) => _nyumaYaKadi(kadi.width, kadi.height, simuYaKampuni),
    ),
  );

  return doc.save();
}

pw.Widget _nyumaYaKadi(double upana, double urefu, String simuYaKampuni) {
  final r = urefu / 2 - 4;
  final cx = upana / 2;
  final cy = urefu / 2;
  final maandishiR = r * 0.815;
  final simu = simuYaKampuni.isEmpty ? '—' : simuYaKampuni;
  return pw.Container(
    color: PdfColors.white,
    child: pw.Stack(
      children: [
        pw.Positioned.fill(
          child: pw.CustomPaint(
            painter: (canvas, size) => _choraMuhuri(canvas, size),
          ),
        ),
        ..._herufiZaMzunguko(
          'IDARA YA MILIPUKO',
          cx,
          cy,
          maandishiR,
          juu: true,
        ),
        ..._herufiZaMzunguko(
          'KATIBU WA MABLASTA',
          cx,
          cy,
          maandishiR,
          juu: false,
        ),
        pw.Positioned.fill(
          child: pw.Padding(
            padding: const pw.EdgeInsets.fromLTRB(36, 12, 36, 28),
            child: pw.Column(
              mainAxisAlignment: pw.MainAxisAlignment.center,
              crossAxisAlignment: pw.CrossAxisAlignment.center,
              children: [
                pw.Text(
                  'Kimetolewa na Idara ya Milipuko',
                  textAlign: pw.TextAlign.center,
                  style: pw.TextStyle(
                    fontSize: 8,
                    fontWeight: pw.FontWeight.bold,
                  ),
                ),
                pw.Text(
                  'Msasa Gold Mine',
                  textAlign: pw.TextAlign.center,
                  style: pw.TextStyle(
                    fontSize: 8,
                    fontWeight: pw.FontWeight.bold,
                  ),
                ),
                pw.Text(
                  'S.L.P 02 BUKOMBE, GEITA',
                  textAlign: pw.TextAlign.center,
                  style: const pw.TextStyle(fontSize: 7.5),
                ),
                pw.SizedBox(height: 3),
                pw.Text(
                  'Ukikiokota tafadhali wasiliana nasi kupitia $simu',
                  textAlign: pw.TextAlign.center,
                  style: pw.TextStyle(
                    fontSize: 8.6,
                    fontWeight: pw.FontWeight.bold,
                  ),
                ),
                pw.SizedBox(height: 5),
                pw.Container(width: 148, height: 1.4, color: PdfColors.black),
                pw.SizedBox(height: 3),
                pw.Text(
                  'KATIBU WA IDARA',
                  textAlign: pw.TextAlign.center,
                  style: pw.TextStyle(
                    fontSize: 7.5,
                    fontWeight: pw.FontWeight.bold,
                    letterSpacing: 0.4,
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    ),
  );
}

void _choraMuhuri(PdfGraphics canvas, PdfPoint size) {
  final cx = size.x / 2;
  final cy = size.y / 2;
  final r = size.y / 2 - 4;
  final maandishiR = r * 0.815;

  const rangi = PdfColor.fromInt(0xFF2F6FAD);
  const hatua = 36;
  for (var pete = hatua; pete >= 1; pete--) {
    final nje = r * pete / hatua;
    final ndani = r * (pete - 1) / hatua;
    canvas
      ..setGraphicState(
        PdfGraphicState(fillOpacity: 0.015 + 0.22 * (1 - pete / hatua)),
      )
      ..setFillColor(rangi)
      ..drawEllipse(cx, cy, nje, nje);
    if (ndani > 0.4) canvas.drawEllipse(cx, cy, ndani, ndani, clockwise: false);
    canvas.fillPath(evenOdd: true);
  }

  canvas
    ..setGraphicState(
      const PdfGraphicState(strokeOpacity: 0.42, fillOpacity: 0.42),
    )
    ..setStrokeColor(_buluu)
    ..setLineWidth(r * 0.028)
    ..drawEllipse(cx, cy, r, r)
    ..strokePath()
    ..setLineWidth(r * 0.01)
    ..drawEllipse(cx, cy, r * 0.93, r * 0.93)
    ..strokePath()
    ..setLineWidth(r * 0.014)
    ..drawEllipse(cx, cy, r * 0.70, r * 0.70)
    ..strokePath();

  _almasiPdf(canvas, cx + maandishiR, cy, r * 0.045);
  _almasiPdf(canvas, cx - maandishiR, cy, r * 0.045);
  canvas.setGraphicState(const PdfGraphicState(opacity: 1));
}

void _almasiPdf(PdfGraphics canvas, double x, double y, double s) {
  canvas
    ..setFillColor(_buluu)
    ..moveTo(x, y + s)
    ..lineTo(x + s * 0.62, y)
    ..lineTo(x, y - s)
    ..lineTo(x - s * 0.62, y)
    ..fillPath();
}

List<pw.Widget> _herufiZaMzunguko(
  String maandishi,
  double cx,
  double cyKutokaJuu,
  double radius, {
  required bool juu,
}) {
  final herufi = maandishi.split('');
  final n = herufi.length;
  final span = juu ? 2.05 : 2.25;
  const sanduku = 12.0;
  final mtindo = pw.TextStyle(
    fontSize: juu ? 8.4 : 8.0,
    fontWeight: pw.FontWeight.bold,
    color: _buluu,
  );
  return [
    for (var i = 0; i < n; i++)
      if (herufi[i] != ' ')
        pw.Positioned(
          left:
              cx +
              radius * math.cos(_pembeYaHerufi(i, n, span, juu)) -
              sanduku / 2,
          top:
              cyKutokaJuu -
              radius * math.sin(_pembeYaHerufi(i, n, span, juu)) -
              sanduku / 2,
          child: pw.Opacity(
            opacity: 0.45,
            child: pw.Transform.rotate(
              angle: _mzungukoWaHerufi(i, n, span, juu),
              child: pw.Container(
                width: sanduku,
                height: sanduku,
                alignment: pw.Alignment.center,
                child: pw.Text(herufi[i], style: mtindo),
              ),
            ),
          ),
        ),
  ];
}

double _pembeYaHerufi(int i, int n, double span, bool juu) {
  final t = n == 1 ? 0.5 : i / (n - 1);
  return juu
      ? math.pi / 2 + span / 2 - t * span
      : -math.pi / 2 - span / 2 + t * span;
}

double _mzungukoWaHerufi(int i, int n, double span, bool juu) {
  final angle = _pembeYaHerufi(i, n, span, juu);
  return juu
      ? math.atan2(-math.cos(angle), math.sin(angle))
      : math.atan2(math.cos(angle), -math.sin(angle));
}

pw.Widget _mstari(String kichwa, String thamani) {
  final andishi = thamani.trim().isEmpty ? '—' : thamani.trim().toUpperCase();
  return pw.RichText(
    maxLines: 2,
    text: pw.TextSpan(
      children: [
        pw.TextSpan(
          text: '$kichwa: ',
          style: const pw.TextStyle(fontSize: 6, color: PdfColors.grey700),
        ),
        pw.TextSpan(
          text: andishi,
          style: pw.TextStyle(fontSize: 8, fontWeight: pw.FontWeight.bold),
        ),
      ],
    ),
  );
}

Future<void> chapishaKitambulisho(Map<String, dynamic> mtu) async {
  final bytes = await jengaKitambulisho(mtu);
  final jina = (mtu['jina'] ?? 'mlipuaji').toString();
  await Printing.layoutPdf(
    onLayout: (_) async => bytes,
    name: 'kitambulisho-$jina.pdf',
  );
}

Future<void> shirikiKitambulisho(Map<String, dynamic> mtu) async {
  final bytes = await jengaKitambulisho(mtu);
  final jina = (mtu['jina'] ?? 'mlipuaji').toString().replaceAll(
    RegExp(r'[^\w\-]+'),
    '_',
  );
  await Printing.sharePdf(bytes: bytes, filename: 'kitambulisho-$jina.pdf');
}

Future<pw.MemoryImage?> _picha(String? url) async {
  if (url == null || url.isEmpty) return null;
  try {
    final response = await http.get(Uri.parse(url));
    if (response.statusCode != 200 || response.bodyBytes.isEmpty) return null;
    return pw.MemoryImage(response.bodyBytes);
  } catch (_) {
    return null;
  }
}
