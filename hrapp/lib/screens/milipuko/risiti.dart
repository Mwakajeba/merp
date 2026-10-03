import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';

Future<void> chapishaRisiti({
  required String karatasi,
  required String kichwa,
  required String namba,
  required List<MapEntry<String, String>> mistari,
  List<String> majina = const [],
  String? majinaKichwa,
  List<String> sahihi = const [],
  String kampuni = '',
  String anuani = '',
  String ukaguziUrl = '',
}) async {
  final upana = (karatasi == '58' ? 58 : 80) * PdfPageFormat.mm;
  final format = PdfPageFormat(upana, 320 * PdfPageFormat.mm, marginAll: 2.4 * PdfPageFormat.mm);
  final doc = pw.Document();

  doc.addPage(
    pw.Page(
      pageFormat: format,
      build: (context) {
        return pw.Column(
          crossAxisAlignment: pw.CrossAxisAlignment.stretch,
          children: [
            pw.Text(
              'BLASTING OFFICE',
              textAlign: pw.TextAlign.center,
              style: pw.TextStyle(fontSize: 8, color: PdfColors.red800, fontWeight: pw.FontWeight.bold),
            ),
            pw.Text(
              kampuni.isEmpty ? 'MSASA GOLD MINE' : kampuni,
              textAlign: pw.TextAlign.center,
              style: pw.TextStyle(fontSize: 9, fontWeight: pw.FontWeight.bold),
            ),
            if (anuani.trim().isNotEmpty)
              pw.Text(anuani, textAlign: pw.TextAlign.center, style: const pw.TextStyle(fontSize: 7)),
            pw.SizedBox(height: 4),
            pw.Text(
              kichwa,
              textAlign: pw.TextAlign.center,
              style: pw.TextStyle(fontSize: 8, fontWeight: pw.FontWeight.bold),
            ),
            pw.SizedBox(height: 2),
            pw.Text(
              'Na. $namba',
              textAlign: pw.TextAlign.center,
              style: pw.TextStyle(fontSize: 12, fontWeight: pw.FontWeight.bold),
            ),
            pw.SizedBox(height: 6),
            ...mistari.map(
              (mstari) => pw.Padding(
                padding: const pw.EdgeInsets.only(bottom: 2),
                child: pw.Row(
                  crossAxisAlignment: pw.CrossAxisAlignment.start,
                  children: [
                    pw.SizedBox(width: upana * 0.38, child: pw.Text(mstari.key, style: const pw.TextStyle(fontSize: 8))),
                    pw.Expanded(
                      child: pw.Text(mstari.value, style: pw.TextStyle(fontSize: 8, fontWeight: pw.FontWeight.bold)),
                    ),
                  ],
                ),
              ),
            ),
            if (majina.isNotEmpty) ...[
              pw.SizedBox(height: 4),
              pw.Text(majinaKichwa ?? 'MAJINA', style: pw.TextStyle(fontSize: 8, fontWeight: pw.FontWeight.bold)),
              ...majina.map((jina) => pw.Text('• $jina', style: const pw.TextStyle(fontSize: 8))),
            ],
            ...sahihi.map(
              (jina) => pw.Padding(
                padding: const pw.EdgeInsets.only(top: 8),
                child: pw.Column(
                  crossAxisAlignment: pw.CrossAxisAlignment.start,
                  children: [
                    pw.Text(jina, style: const pw.TextStyle(fontSize: 7)),
                    pw.SizedBox(height: 10),
                    pw.Container(width: 90, height: 0.4, color: PdfColors.black),
                  ],
                ),
              ),
            ),
            if (ukaguziUrl.isNotEmpty) ...[
              pw.SizedBox(height: 10),
              pw.Center(
                child: pw.BarcodeWidget(
                  barcode: pw.Barcode.qrCode(),
                  data: ukaguziUrl,
                  width: karatasi == '58' ? 62 : 74,
                  height: karatasi == '58' ? 62 : 74,
                ),
              ),
              pw.SizedBox(height: 2),
              pw.Text(
                'SKANI KWA UKAGUZI',
                textAlign: pw.TextAlign.center,
                style: pw.TextStyle(fontSize: 7, fontWeight: pw.FontWeight.bold),
              ),
            ],
          ],
        );
      },
    ),
  );

  await Printing.layoutPdf(onLayout: (_) async => doc.save(), name: 'kibali-$namba');
}

Future<void> chapishaKibaliChaMlipuko(Map<String, dynamic> kibali, String karatasi) {
  final wachorongaji = ((kibali['wachorongaji'] as List?) ?? []).map((jina) => jina.toString()).where((jina) => jina.trim().isNotEmpty).toList();
  return chapishaRisiti(
    karatasi: karatasi,
    kichwa: 'KIBALI CHA KUCHORONGA MWAMBA NA KULIPUA',
    namba: (kibali['namba'] ?? '').toString(),
    kampuni: (kibali['kampuni'] ?? '').toString(),
    anuani: (kibali['anuani'] ?? '').toString(),
    ukaguziUrl: (kibali['ukaguzi_url'] ?? '').toString(),
    mistari: [
      MapEntry('HALI', (kibali['hali'] ?? '').toString()),
      MapEntry('DUARA', (kibali['duara'] ?? '').toString()),
      MapEntry('MATUNDU', (kibali['matundu'] ?? '').toString()),
      MapEntry('BC', (kibali['bc_no'] ?? '').toString()),
      MapEntry('TAREHE', (kibali['tarehe'] ?? '').toString()),
      MapEntry('AINA', (kibali['aina'] ?? '').toString()),
    ],
    majina: wachorongaji,
    majinaKichwa: 'MAJINA YA WACHORONGAJI',
    sahihi: [
      'MSIMAMIZI WA DUARA: ${kibali['msimamizi'] ?? ''}',
      'MLIPUAJI: ${kibali['mlipuaji'] ?? ''}',
      'INSPECTOR: ${kibali['msimamizi_wa_idara'] ?? ''}',
      'KATIBU: ${kibali['katibu'] ?? ''}',
    ],
  );
}

Future<void> chapishaKibaliChaMawe(Map<String, dynamic> mawe, String karatasi) {
  return chapishaRisiti(
    karatasi: karatasi,
    kichwa: 'KIBALI CHA KUSAFIRISHA MZIGO KUTOKA MADUARANI KWENDA OFISINI',
    namba: (mawe['namba'] ?? '').toString(),
    kampuni: (mawe['kampuni'] ?? '').toString(),
    anuani: (mawe['anuani'] ?? '').toString(),
    ukaguziUrl: (mawe['ukaguzi_url'] ?? '').toString(),
    mistari: [
      MapEntry('DUARA', (mawe['duara'] ?? '').toString()),
      MapEntry('MZIGO', (mawe['aina'] ?? '').toString()),
      MapEntry('MIFUKO', (mawe['mifuko'] ?? '').toString()),
      MapEntry('TAREHE', (mawe['tarehe'] ?? '').toString()),
      MapEntry('MSIMAMIZI', (mawe['msimamizi'] ?? '').toString()),
    ],
    sahihi: ['KATIBU: ${mawe['katibu'] ?? ''}'],
  );
}
