import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';

import '../../services/milipuko_service.dart';

const _kijani = PdfColor.fromInt(0xFF0B6E4F);
const _nyekundu = PdfColor.fromInt(0xFFB42318);

Future<Uint8List> jengaKitambulisho(Map<String, dynamic> mtu) async {
  final jina = (mtu['jina'] ?? '').toString();
  final bc = (mtu['bc_no'] ?? '—').toString();
  final simu = (mtu['simu'] ?? '').toString();
  final mkoa = (mtu['mkoa'] ?? '').toString();
  final wilaya = (mtu['wilaya'] ?? '').toString();
  final kampuni = (mtu['kampuni'] ?? 'Msasa Gold Mine').toString();
  final simuYaKampuni = (mtu['simu_ya_kampuni'] ?? '').toString().trim();
  final ukaguzi = (mtu['ukaguzi_url'] ?? '').toString();
  final amefungwa = mtu['hali'] == 'blocked' || mtu['amefungwa'] == true;
  final mwenyeBc = (mtu['mwenye_bc'] ?? '').toString();
  final picha = await _picha(MilipukoService.mediaUrl(mtu['picha']?.toString()));
  final muhuri = pw.MemoryImage((await rootBundle.load('assets/muhuri.jpg')).buffer.asUint8List());
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
              color: _kijani,
              padding: const pw.EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              child: pw.Row(
                mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                children: [
                  pw.Text('MILIPUKO', style: pw.TextStyle(color: PdfColors.white, fontSize: 8, fontWeight: pw.FontWeight.bold)),
                  pw.Text(kampuni, style: const pw.TextStyle(color: PdfColors.white, fontSize: 7)),
                ],
              ),
            ),
            pw.Padding(
              padding: const pw.EdgeInsets.fromLTRB(10, 4, 10, 0),
              child: pw.Align(
                alignment: pw.Alignment.centerLeft,
                child: pw.Text(
                  'KITAMBULISHO CHA MLIPUAJI',
                  style: const pw.TextStyle(fontSize: 6.5, color: PdfColors.grey700, letterSpacing: 0.4),
                ),
              ),
            ),
            pw.Expanded(
              child: pw.Padding(
                padding: const pw.EdgeInsets.fromLTRB(10, 4, 10, 8),
                child: pw.Row(
                  crossAxisAlignment: pw.CrossAxisAlignment.center,
                  children: [
                    picha == null
                        ? pw.Container(
                            width: 52,
                            height: 62,
                            color: PdfColors.grey300,
                            alignment: pw.Alignment.center,
                            child: pw.Text('Hakuna\npicha', textAlign: pw.TextAlign.center, style: const pw.TextStyle(fontSize: 6)),
                          )
                        : pw.Image(picha, width: 52, height: 62, fit: pw.BoxFit.cover),
                    pw.SizedBox(width: 8),
                    pw.Expanded(
                      child: pw.Column(
                        mainAxisAlignment: pw.MainAxisAlignment.center,
                        crossAxisAlignment: pw.CrossAxisAlignment.start,
                        children: [
                          pw.Text(jina, style: pw.TextStyle(fontSize: 11, fontWeight: pw.FontWeight.bold)),
                          pw.SizedBox(height: 2),
                          pw.Text('BC No. $bc', style: const pw.TextStyle(fontSize: 8)),
                          if (mwenyeBc.isNotEmpty) pw.Text('Ya $mwenyeBc', style: const pw.TextStyle(fontSize: 7)),
                          pw.Text(simu, style: const pw.TextStyle(fontSize: 8)),
                          pw.Text('$mkoa, $wilaya', style: const pw.TextStyle(fontSize: 8)),
                          pw.SizedBox(height: 3),
                          pw.Container(
                            padding: const pw.EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                            decoration: pw.BoxDecoration(
                              color: amefungwa ? _nyekundu : _kijani,
                              borderRadius: pw.BorderRadius.circular(8),
                            ),
                            child: pw.Text(
                              amefungwa ? 'Blocked' : 'Active',
                              style: pw.TextStyle(color: PdfColors.white, fontSize: 7, fontWeight: pw.FontWeight.bold),
                            ),
                          ),
                        ],
                      ),
                    ),
                    if (ukaguzi.isNotEmpty)
                      pw.Column(
                        children: [
                          pw.BarcodeWidget(barcode: pw.Barcode.qrCode(), data: ukaguzi, width: 52, height: 52),
                          pw.Text('Skani', style: const pw.TextStyle(fontSize: 6, color: PdfColors.grey700)),
                        ],
                      ),
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
      margin: const pw.EdgeInsets.all(10),
      build: (context) {
        return pw.Column(
          mainAxisAlignment: pw.MainAxisAlignment.center,
          crossAxisAlignment: pw.CrossAxisAlignment.center,
          children: [
            pw.Image(muhuri, height: 78, fit: pw.BoxFit.contain),
            pw.SizedBox(height: 4),
            pw.Text('Kimetolewa na Idara ya Milipuko', style: pw.TextStyle(fontSize: 8, fontWeight: pw.FontWeight.bold)),
            pw.Text('Msasa Gold Mine', style: pw.TextStyle(fontSize: 8, fontWeight: pw.FontWeight.bold)),
            pw.Text('S.L.P 02 BUKOMBE, GEITA', style: const pw.TextStyle(fontSize: 7.5)),
            pw.SizedBox(height: 3),
            pw.Text(
              'Ukikiokota tafadhali wasiliana nasi kupitia ${simuYaKampuni.isEmpty ? '—' : simuYaKampuni}',
              textAlign: pw.TextAlign.center,
              style: const pw.TextStyle(fontSize: 7.5),
            ),
          ],
        );
      },
    ),
  );

  return doc.save();
}

Future<void> chapishaKitambulisho(Map<String, dynamic> mtu) async {
  final bytes = await jengaKitambulisho(mtu);
  final jina = (mtu['jina'] ?? 'mlipuaji').toString();
  await Printing.layoutPdf(onLayout: (_) async => bytes, name: 'kitambulisho-$jina.pdf');
}

Future<void> shirikiKitambulisho(Map<String, dynamic> mtu) async {
  final bytes = await jengaKitambulisho(mtu);
  final jina = (mtu['jina'] ?? 'mlipuaji').toString().replaceAll(RegExp(r'[^\w\-]+'), '_');
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
