import 'dart:typed_data';

import 'package:http/http.dart' as http;
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';

import '../../services/milipuko_service.dart';

const _kijani = PdfColor.fromInt(0xFF0B6E4F);

Future<Uint8List> jengaKitambulisho(Map<String, dynamic> mtu) async {
  final jina = (mtu['jina'] ?? '').toString();
  final bc = (mtu['bc_no'] ?? '').toString().trim();
  final simu = (mtu['simu'] ?? '').toString();
  final mkoa = (mtu['mkoa'] ?? '').toString();
  final wilaya = (mtu['wilaya'] ?? '').toString();
  final kampuni = (mtu['kampuni'] ?? 'Msasa Gold Mine').toString();
  final simuYaKampuni = (mtu['simu_ya_kampuni'] ?? '').toString().trim();
  final ukaguzi = (mtu['ukaguzi_url'] ?? '').toString();
  final picha = await _picha(MilipukoService.mediaUrl(mtu['picha']?.toString()));
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
              color: _kijani,
              padding: const pw.EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              child: pw.Row(
                children: [
                  pw.Text('MILIPUKO', style: pw.TextStyle(color: PdfColors.white, fontSize: 8, fontWeight: pw.FontWeight.bold)),
                  pw.SizedBox(width: 6),
                  pw.Expanded(
                    child: pw.Text(
                      kampuni,
                      textAlign: pw.TextAlign.right,
                      maxLines: 1,
                      style: const pw.TextStyle(color: PdfColors.white, fontSize: 7),
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
                          style: pw.TextStyle(fontSize: 8, fontWeight: pw.FontWeight.bold, letterSpacing: 0.2),
                        ),
                        if (bc.isNotEmpty)
                          pw.Text(bc, textAlign: pw.TextAlign.center, style: pw.TextStyle(fontSize: 10, fontWeight: pw.FontWeight.bold)),
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
                                child: pw.Text('Hakuna\npicha', textAlign: pw.TextAlign.center, style: const pw.TextStyle(fontSize: 6)),
                              )
                            : pw.Image(picha, width: upande, height: upande, fit: pw.BoxFit.cover),
                        pw.SizedBox(width: 5),
                        pw.Expanded(
                          child: pw.SizedBox(
                            height: upande,
                            child: pw.Column(
                              mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                              crossAxisAlignment: pw.CrossAxisAlignment.start,
                              children: [
                                _mstari('Jina', jina),
                                _mstari('Jinsia', 'ME'),
                                _mstari('Simu', simu),
                                _mstari('Mkoa', mkoa),
                                _mstari('Wilaya', wilaya),
                              ],
                            ),
                          ),
                        ),
                        if (ukaguzi.isNotEmpty) ...[
                          pw.SizedBox(width: 4),
                          pw.BarcodeWidget(barcode: pw.Barcode.qrCode(), data: ukaguzi, width: upande, height: upande),
                        ],
                      ],
                    ),
                    if (bc.isNotEmpty)
                      pw.BarcodeWidget(
                        barcode: pw.Barcode.code128(),
                        data: bc,
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
      margin: const pw.EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      build: (context) {
        return pw.Column(
          mainAxisAlignment: pw.MainAxisAlignment.center,
          crossAxisAlignment: pw.CrossAxisAlignment.center,
          children: [
            pw.Text('Kimetolewa na Idara ya Milipuko', textAlign: pw.TextAlign.center, style: pw.TextStyle(fontSize: 8, fontWeight: pw.FontWeight.bold)),
            pw.Text('Msasa Gold Mine', textAlign: pw.TextAlign.center, style: pw.TextStyle(fontSize: 8, fontWeight: pw.FontWeight.bold)),
            pw.Text('S.L.P 02 BUKOMBE, GEITA', textAlign: pw.TextAlign.center, style: const pw.TextStyle(fontSize: 7.5)),
            pw.SizedBox(height: 3),
            pw.Text(
              'Ukikiokota tafadhali wasiliana nasi kupitia ${simuYaKampuni.isEmpty ? '—' : simuYaKampuni}',
              textAlign: pw.TextAlign.center,
              style: const pw.TextStyle(fontSize: 7.5),
            ),
            pw.SizedBox(height: 8),
            pw.Container(width: 130, height: 0.8, color: PdfColors.black),
            pw.SizedBox(height: 3),
            pw.Text('KATIBU WA IDARA', textAlign: pw.TextAlign.center, style: pw.TextStyle(fontSize: 7.5, fontWeight: pw.FontWeight.bold, letterSpacing: 0.4)),
          ],
        );
      },
    ),
  );

  return doc.save();
}

pw.Widget _mstari(String kichwa, String thamani) {
  final andishi = thamani.trim().isEmpty ? '—' : thamani.trim().toUpperCase();
  return pw.RichText(
    maxLines: 2,
    text: pw.TextSpan(
      children: [
        pw.TextSpan(text: '$kichwa: ', style: const pw.TextStyle(fontSize: 6, color: PdfColors.grey700)),
        pw.TextSpan(text: andishi, style: pw.TextStyle(fontSize: 8, fontWeight: pw.FontWeight.bold)),
      ],
    ),
  );
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
