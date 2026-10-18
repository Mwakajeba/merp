import 'package:esc_pos_utils_plus/esc_pos_utils_plus.dart';

import '../../services/bluetooth_printer_service.dart';

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
  final bytes = await _andikaRisiti(
    karatasi: karatasi,
    kichwa: kichwa,
    namba: namba,
    mistari: mistari,
    majina: majina,
    majinaKichwa: majinaKichwa,
    sahihi: sahihi,
    kampuni: kampuni,
    anuani: anuani,
    ukaguziUrl: ukaguziUrl,
  );
  await BluetoothPrinterService.chapisha(bytes);
}

Future<void> chapishaMajaribio() async {
  final profile = await CapabilityProfile.load();
  final generator = Generator(PaperSize.mm58, profile);
  final bytes = <int>[
    ...generator.reset(),
    ...generator.text('BLASTING OFFICE', styles: const PosStyles(align: PosAlign.center, bold: true)),
    ...generator.text('POS 58mm', styles: const PosStyles(align: PosAlign.center, bold: true, height: PosTextSize.size2, width: PosTextSize.size2)),
    ...generator.hr(),
    ...generator.text('Printer iko tayari.', styles: const PosStyles(align: PosAlign.center)),
    ...generator.feed(3),
  ];
  await BluetoothPrinterService.chapisha(bytes);
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

Future<List<int>> _andikaRisiti({
  required String karatasi,
  required String kichwa,
  required String namba,
  required List<MapEntry<String, String>> mistari,
  required List<String> majina,
  required String? majinaKichwa,
  required List<String> sahihi,
  required String kampuni,
  required String anuani,
  required String ukaguziUrl,
}) async {
  final profile = await CapabilityProfile.load();
  final upana = karatasi == '80' ? PaperSize.mm80 : PaperSize.mm58;
  final generator = Generator(upana, profile);
  final safu = upana == PaperSize.mm80 ? 48 : 32;
  final bytes = <int>[
    ...generator.reset(),
    ...generator.text('BLASTING OFFICE', styles: const PosStyles(align: PosAlign.center, bold: true)),
    ...generator.text(kampuni.trim().isEmpty ? 'MSASA GOLD MINE' : kampuni.trim(), styles: const PosStyles(align: PosAlign.center, bold: true)),
  ];
  if (anuani.trim().isNotEmpty) {
    bytes.addAll(generator.text(anuani.trim(), styles: const PosStyles(align: PosAlign.center)));
  }
  bytes.addAll(generator.hr(ch: '-', len: safu));
  bytes.addAll(generator.text(kichwa, styles: const PosStyles(align: PosAlign.center, bold: true)));
  bytes.addAll(generator.text(
    'Na. $namba',
    styles: PosStyles(
      align: PosAlign.center,
      bold: true,
      height: namba.length > 12 ? PosTextSize.size1 : PosTextSize.size2,
      width: namba.length > 12 ? PosTextSize.size1 : PosTextSize.size2,
    ),
  ));
  bytes.addAll(generator.hr(ch: '-', len: safu));
  for (final mstari in mistari) {
    bytes.addAll(_mstari(generator, safu, mstari.key, mstari.value));
  }
  if (majina.isNotEmpty) {
    bytes.addAll(generator.feed(1));
    bytes.addAll(generator.text(majinaKichwa ?? 'MAJINA', styles: const PosStyles(bold: true)));
    for (final jina in majina) {
      bytes.addAll(generator.text('- $jina'));
    }
  }
  for (final jina in sahihi) {
    bytes.addAll(generator.feed(1));
    bytes.addAll(generator.text(jina));
    bytes.addAll(generator.text('................'));
  }
  if (ukaguziUrl.trim().isNotEmpty) {
    bytes.addAll(generator.feed(1));
    bytes.addAll(generator.qrcode(ukaguziUrl.trim(), size: QRSize.size6, align: PosAlign.center));
    bytes.addAll(generator.text('SKANI KWA UKAGUZI', styles: const PosStyles(align: PosAlign.center, bold: true)));
  }
  bytes.addAll(generator.feed(3));
  return bytes;
}

List<int> _mstari(Generator generator, int safu, String key, String value) {
  final kushoto = key.trim();
  final kulia = value.trim().isEmpty ? '-' : value.trim();
  if (kushoto.length + kulia.length + 1 <= safu) {
    final nafasi = safu - kushoto.length - kulia.length;
    return generator.text('$kushoto${' ' * nafasi}$kulia');
  }
  return [
    ...generator.text(kushoto),
    ...generator.text(kulia, styles: const PosStyles(bold: true)),
  ];
}
