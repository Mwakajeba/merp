import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

import '../../config/api_config.dart';
import '../../services/milipuko_service.dart';

class ScanScreen extends StatefulWidget {
  final String aina;

  const ScanScreen({super.key, required this.aina});

  @override
  State<ScanScreen> createState() => _ScanScreenState();
}

class _ScanScreenState extends State<ScanScreen> {
  late final MobileScannerController _camera = MobileScannerController(
    formats: const [BarcodeFormat.qrCode],
    detectionSpeed: DetectionSpeed.noDuplicates,
  );
  bool _inasoma = false;
  Map<String, dynamic>? _jibu;
  String? _code;

  @override
  void dispose() {
    _camera.dispose();
    super.dispose();
  }

  String get _kichwa {
    switch (widget.aina) {
      case 'mawe':
        return 'Thibitisha kibali cha mawe';
      case 'mlipuzi':
        return 'Thibitisha kitambulisho';
      default:
        return 'Thibitisha kibali cha mlipuko';
    }
  }

  String get _maelezo {
    switch (widget.aina) {
      case 'mawe':
        return 'Weka QR ya kibali cha mawe ndani ya fremu';
      case 'mlipuzi':
        return 'Weka QR ya kitambulisho ndani ya fremu';
      default:
        return 'Weka QR ya kibali cha mlipuko ndani ya fremu';
    }
  }

  Future<void> _soma(String code) async {
    if (_inasoma || _jibu != null) return;
    setState(() => _inasoma = true);
    await _camera.stop();
    final result = await MilipukoService.get('${ApiConfig.milipukoScan}?code=${Uri.encodeQueryComponent(code)}');
    if (!mounted) return;
    setState(() {
      _inasoma = false;
      _code = code;
      _jibu = (result['data'] as Map<String, dynamic>?) ?? {
        'aina': 'haijulikani',
        'halali': false,
        'ujumbe': result['message'] ?? 'Imeshindikana.',
      };
    });
  }

  Future<void> _tumia() async {
    if (_code == null) return;
    final result = await MilipukoService.post(ApiConfig.milipukoTumia, {'code': _code});
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text((result['message'] ?? '').toString())));
    if (result['data'] is Map<String, dynamic>) {
      setState(() => _jibu = result['data'] as Map<String, dynamic>);
    }
  }

  void _scanTena() {
    setState(() {
      _jibu = null;
      _code = null;
    });
    _camera.start();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF07111F),
      appBar: AppBar(
        title: Text(_kichwa),
        backgroundColor: const Color(0xFF07111F),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: _jibu == null ? _kamera() : _matokeo(),
    );
  }

  Widget _kamera() {
    return Stack(
      fit: StackFit.expand,
      children: [
        MobileScanner(
          controller: _camera,
          fit: BoxFit.cover,
          onDetect: (capture) {
            final code = capture.barcodes.isEmpty ? null : capture.barcodes.first.rawValue;
            if (code != null && code.isNotEmpty) _soma(code);
          },
        ),
        const CustomPaint(painter: _FremuYaQr(), child: SizedBox.expand()),
        Align(
          alignment: const Alignment(0, 0.42),
          child: Container(
            margin: const EdgeInsets.symmetric(horizontal: 32),
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            decoration: BoxDecoration(
              color: Colors.black.withValues(alpha: 0.55),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(
              _inasoma ? 'Inasoma QR...' : _maelezo,
              textAlign: TextAlign.center,
              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
            ),
          ),
        ),
      ],
    );
  }

  Widget _matokeo() {
    final jibu = _jibu!;
    final halali = jibu['halali'] == true;
    final kimetumika = jibu['kimetumika'] == true;
    final aina = (jibu['aina'] ?? '').toString();
    final siAinaSahihi = aina != 'haijulikani' && aina.isNotEmpty && aina != widget.aina;
    final sawa = halali && !siAinaSahihi && !kimetumika;
    final rangi = siAinaSahihi || !halali ? const Color(0xFFB42318) : (kimetumika ? const Color(0xFFB54708) : const Color(0xFF067647));
    final kichwa = siAinaSahihi
        ? 'QR hii si ya ukaguzi huu'
        : (jibu['ujumbe'] ?? '').toString();
    final kibali = jibu['kibali'] as Map<String, dynamic>?;
    final mawe = jibu['mawe'] as Map<String, dynamic>?;
    final mtu = jibu['mlipuzi'] as Map<String, dynamic>?;
    final picha = MilipukoService.mediaUrl(mtu?['picha']?.toString());
    final mistari = <MapEntry<String, String>>[];

    if (!siAinaSahihi && kibali != null) {
      mistari.addAll([
        MapEntry('Namba', '${kibali['namba'] ?? '—'}'),
        MapEntry('Tarehe', '${kibali['tarehe'] ?? '—'}'),
        MapEntry('Duara', '${kibali['duara'] ?? '—'}'),
        MapEntry('Mlipuaji', '${kibali['mlipuaji'] ?? '—'}'),
        MapEntry('Msimamizi', '${kibali['msimamizi'] ?? '—'}'),
        MapEntry('Hali', '${kibali['hali'] ?? '—'}'),
        MapEntry('Aina', '${kibali['aina'] ?? '—'}'),
        if ((kibali['imetumika_saa'] ?? '').toString().isNotEmpty) MapEntry('Imetumika', '${kibali['imetumika_saa']}'),
      ]);
    }
    if (!siAinaSahihi && mawe != null) {
      mistari.addAll([
        MapEntry('Namba', '${mawe['namba'] ?? '—'}'),
        MapEntry('Tarehe', '${mawe['tarehe'] ?? '—'}'),
        MapEntry('Duara', '${mawe['duara'] ?? '—'}'),
        MapEntry('Aina ya mzigo', '${mawe['aina'] ?? '—'}'),
        MapEntry('Mifuko', '${mawe['mifuko'] ?? '—'}'),
        MapEntry('Msimamizi', '${mawe['msimamizi'] ?? '—'}'),
        MapEntry('Katibu', '${mawe['katibu'] ?? '—'}'),
      ]);
    }
    if (!siAinaSahihi && mtu != null) {
      mistari.addAll([
        MapEntry('Jina', '${mtu['jina'] ?? '—'}'),
        MapEntry('BC No.', '${mtu['bc_no'] ?? '—'}'),
        MapEntry('Hali', mtu['amefungwa'] == true ? 'Blocked' : 'Active'),
        MapEntry('Simu', '${mtu['simu'] ?? '—'}'),
      ]);
    }

    return Container(
      color: const Color(0xFFF3F6FB),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 20, 16, 24),
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.fromLTRB(18, 22, 18, 20),
            decoration: BoxDecoration(
              color: rangi,
              borderRadius: BorderRadius.circular(24),
            ),
            child: Column(
              children: [
                Container(
                  width: 64,
                  height: 64,
                  decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.16), shape: BoxShape.circle),
                  child: Icon(sawa ? Icons.verified_rounded : (kimetumika && !siAinaSahihi ? Icons.history_rounded : Icons.gpp_bad_rounded), color: Colors.white, size: 34),
                ),
                const SizedBox(height: 12),
                Text(kichwa, textAlign: TextAlign.center, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w800, height: 1.3)),
              ],
            ),
          ),
          if (picha.isNotEmpty && !siAinaSahihi) ...[
            const SizedBox(height: 16),
            Center(
              child: ClipRRect(
                borderRadius: BorderRadius.circular(18),
                child: Image.network(picha, height: 180, width: 140, fit: BoxFit.cover, errorBuilder: (_, _, _) => const SizedBox.shrink()),
              ),
            ),
          ],
          if (mistari.isNotEmpty) ...[
            const SizedBox(height: 16),
            Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: const Color(0xFFE4EAF2)),
              ),
              child: Column(
                children: [
                  for (var i = 0; i < mistari.length; i++)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
                      decoration: BoxDecoration(
                        border: i == mistari.length - 1 ? null : const Border(bottom: BorderSide(color: Color(0xFFEEF2F6))),
                      ),
                      child: Row(
                        children: [
                          SizedBox(width: 108, child: Text(mistari[i].key, style: const TextStyle(color: Color(0xFF667085)))),
                          Expanded(child: Text(mistari[i].value, style: const TextStyle(fontWeight: FontWeight.w700))),
                        ],
                      ),
                    ),
                ],
              ),
            ),
          ],
          if (widget.aina == 'kibali' && kibali != null && halali && !kimetumika && !siAinaSahihi) ...[
            const SizedBox(height: 16),
            FilledButton(
              style: FilledButton.styleFrom(
                backgroundColor: const Color(0xFF1A4F8B),
                minimumSize: const Size.fromHeight(52),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              ),
              onPressed: _tumia,
              child: const Text('Weka kimetumika'),
            ),
          ],
          const SizedBox(height: 12),
          OutlinedButton(
            style: OutlinedButton.styleFrom(
              minimumSize: const Size.fromHeight(52),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            ),
            onPressed: _scanTena,
            child: const Text('Scan tena'),
          ),
        ],
      ),
    );
  }
}

class _FremuYaQr extends CustomPainter {
  const _FremuYaQr();

  @override
  void paint(Canvas canvas, Size size) {
    final upana = size.width * 0.68;
    final fremu = Rect.fromCenter(
      center: Offset(size.width / 2, size.height * 0.42),
      width: upana,
      height: upana,
    );
    final shimo = RRect.fromRectAndRadius(fremu, const Radius.circular(22));
    final kivuli = Path()
      ..fillType = PathFillType.evenOdd
      ..addRect(Offset.zero & size)
      ..addRRect(shimo);
    canvas.drawPath(kivuli, Paint()..color = const Color(0xC007111F));

    final rangi = Paint()
      ..color = Colors.white
      ..strokeWidth = 5
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;
    const urefu = 34.0;
    final pembe = [
      [fremu.topLeft, const Offset(1, 0), const Offset(0, 1)],
      [fremu.topRight, const Offset(-1, 0), const Offset(0, 1)],
      [fremu.bottomLeft, const Offset(1, 0), const Offset(0, -1)],
      [fremu.bottomRight, const Offset(-1, 0), const Offset(0, -1)],
    ];
    for (final pembeMoja in pembe) {
      final mwanzo = pembeMoja[0];
      final kulia = pembeMoja[1];
      final chini = pembeMoja[2];
      canvas.drawLine(mwanzo, mwanzo + kulia * urefu, rangi);
      canvas.drawLine(mwanzo, mwanzo + chini * urefu, rangi);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
