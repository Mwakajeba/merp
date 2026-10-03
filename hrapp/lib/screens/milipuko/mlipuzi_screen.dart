import 'dart:math' as math;

import 'package:barcode/barcode.dart';
import 'package:flutter/material.dart';
import 'package:qr/qr.dart';

import '../../config/api_config.dart';
import '../../services/milipuko_service.dart';
import 'kitambulisho.dart';

class MlipuziScreen extends StatefulWidget {
  final int id;

  const MlipuziScreen({super.key, required this.id});

  @override
  State<MlipuziScreen> createState() => _MlipuziScreenState();
}

class _MlipuziScreenState extends State<MlipuziScreen> {
  bool _loading = true;
  bool _inachapisha = false;
  String? _error;
  Map<String, dynamic>? _mtu;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final result = await MilipukoService.get(ApiConfig.milipukoMlipuzi(widget.id));
    if (!mounted) return;
    final data = result['data'] as Map<String, dynamic>? ?? {};
    setState(() {
      _loading = false;
      _error = result['success'] == true ? null : (result['message'] ?? 'Imeshindikana.').toString();
      _mtu = data['mlipuzi'] as Map<String, dynamic>?;
    });
  }

  Future<void> _kitambulisho(Future<void> Function(Map<String, dynamic>) kitendo) async {
    final mtu = _mtu;
    if (mtu == null || _inachapisha) return;
    setState(() => _inachapisha = true);
    try {
      await kitendo(mtu);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Kitambulisho hakijatoka: $e')));
    } finally {
      if (mounted) setState(() => _inachapisha = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final mtu = _mtu;
    final picha = MilipukoService.mediaUrl(mtu?['picha']?.toString());
    final wawasiliani = ((mtu?['wawasiliani'] as List?) ?? []).cast<Map<String, dynamic>>();

    return Scaffold(
      backgroundColor: const Color(0xFFF3F6FB),
      appBar: AppBar(
        title: Text((mtu?['jina'] ?? 'Mlipuaji').toString()),
        backgroundColor: const Color(0xFF1A4F8B),
        foregroundColor: Colors.white,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null || mtu == null
              ? Center(child: Padding(padding: const EdgeInsets.all(24), child: Text(_error ?? 'Mlipuaji hajapatikana.')))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    Center(
                      child: CircleAvatar(
                        radius: 52,
                        backgroundColor: const Color(0xFFD7DEE6),
                        backgroundImage: picha.isEmpty ? null : NetworkImage(picha),
                        child: picha.isEmpty ? const Icon(Icons.person, size: 48) : null,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Center(
                      child: Text((mtu['jina'] ?? '').toString(), style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800)),
                    ),
                    const SizedBox(height: 6),
                    Center(child: _Beji(amefungwa: mtu['hali'] == 'blocked')),
                    const SizedBox(height: 16),
                    _KadiMaelezo(mistari: [
                      _Mstari('BC No.', (mtu['bc_no'] ?? '—').toString()),
                      if ((mtu['mwenye_bc'] ?? '').toString().isNotEmpty) _Mstari('Mwenye BC', mtu['mwenye_bc'].toString()),
                      _Mstari('Simu', (mtu['simu'] ?? '—').toString()),
                      if ((mtu['simu_mbadala'] ?? '').toString().isNotEmpty) _Mstari('Simu mbadala', mtu['simu_mbadala'].toString()),
                      _Mstari('Mkoa', (mtu['mkoa'] ?? '—').toString()),
                      _Mstari('Wilaya', (mtu['wilaya'] ?? '—').toString()),
                      if ((mtu['eneo'] ?? '').toString().isNotEmpty) _Mstari('Eneo', mtu['eneo'].toString()),
                    ]),
                    if (wawasiliani.isNotEmpty) ...[
                      const SizedBox(height: 12),
                      const Text('Watu wa kuwasiliana navyo', style: TextStyle(fontWeight: FontWeight.w700)),
                      const SizedBox(height: 8),
                      ...wawasiliani.map(
                        (mtu) => _KadiMaelezo(mistari: [
                          _Mstari('Jina', (mtu['jina'] ?? '').toString()),
                          _Mstari('Simu', (mtu['simu'] ?? '').toString()),
                          _Mstari('Uhusiano', (mtu['uhusiano'] ?? '').toString()),
                        ]),
                      ),
                    ],
                    const SizedBox(height: 18),
                    const Text('Kitambulisho', style: TextStyle(fontWeight: FontWeight.w800)),
                    const SizedBox(height: 8),
                    const Text('Mbele', style: TextStyle(color: Color(0xFF667085), fontWeight: FontWeight.w700)),
                    const SizedBox(height: 6),
                    _KadiMbele(mtu: mtu, picha: picha),
                    const SizedBox(height: 12),
                    const Text('Nyuma', style: TextStyle(color: Color(0xFF667085), fontWeight: FontWeight.w700)),
                    const SizedBox(height: 6),
                    _KadiNyuma(simuYaKampuni: (mtu['simu_ya_kampuni'] ?? '').toString()),
                    const SizedBox(height: 18),
                    Row(
                      children: [
                        Expanded(
                          child: FilledButton.icon(
                            onPressed: _inachapisha ? null : () => _kitambulisho(chapishaKitambulisho),
                            icon: const Icon(Icons.print_rounded),
                            label: const Text('Chapisha'),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: _inachapisha ? null : () => _kitambulisho(shirikiKitambulisho),
                            icon: const Icon(Icons.share_rounded),
                            label: const Text('Share'),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
    );
  }
}

class _KadiMbele extends StatelessWidget {
  final Map<String, dynamic> mtu;
  final String picha;

  const _KadiMbele({required this.mtu, required this.picha});

  @override
  Widget build(BuildContext context) {
    final ukaguzi = (mtu['ukaguzi_url'] ?? '').toString();
    final bc = (mtu['bc_no'] ?? '').toString().trim();
    return AspectRatio(
      aspectRatio: 85.6 / 54,
      child: Container(
        decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: const Color(0xFFE4EAF2))),
        clipBehavior: Clip.antiAlias,
        child: Column(
          children: [
            Container(
              color: const Color(0xFF0B6E4F),
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              child: Row(
                children: [
                  const Text('MILIPUKO', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 11)),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      (mtu['kampuni'] ?? 'Msasa Gold Mine').toString(),
                      textAlign: TextAlign.right,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(color: Colors.white, fontSize: 10),
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(8, 4, 8, 4),
                child: LayoutBuilder(
                  builder: (context, box) {
                    final upande = math.min(math.max(box.maxHeight - 58, 36).toDouble(), box.maxWidth * 0.26);
                    return Column(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          children: [
                            const Text(
                              'KITAMBULISHO CHA MLIPUAJI',
                              textAlign: TextAlign.center,
                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, letterSpacing: 0.2),
                            ),
                            if (bc.isNotEmpty) Text(bc, textAlign: TextAlign.center, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800)),
                          ],
                        ),
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(4),
                              child: picha.isEmpty
                                  ? Container(
                                      width: upande,
                                      height: upande,
                                      color: const Color(0xFFD7DEE6),
                                      alignment: Alignment.center,
                                      child: const Text('Hakuna\npicha', textAlign: TextAlign.center, style: TextStyle(fontSize: 8)),
                                    )
                                  : Image.network(picha, width: upande, height: upande, fit: BoxFit.cover),
                            ),
                            const SizedBox(width: 6),
                            Expanded(
                              child: SizedBox(
                                height: upande,
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    _MstariKadi('Jina', (mtu['jina'] ?? '').toString()),
                                    const _MstariKadi('Jinsia', 'ME'),
                                    _MstariKadi('Simu', (mtu['simu'] ?? '').toString()),
                                    _MstariKadi('Mkoa', (mtu['mkoa'] ?? '').toString()),
                                    _MstariKadi('Wilaya', (mtu['wilaya'] ?? '').toString()),
                                  ],
                                ),
                              ),
                            ),
                            if (ukaguzi.isNotEmpty) ...[
                              const SizedBox(width: 6),
                              _QrNdogo(data: ukaguzi, size: upande),
                            ],
                          ],
                        ),
                        if (bc.isNotEmpty) _BarcodeNdogo(data: bc) else const SizedBox(height: 22),
                      ],
                    );
                  },
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _MstariKadi extends StatelessWidget {
  final String kichwa;
  final String thamani;

  const _MstariKadi(this.kichwa, this.thamani);

  @override
  Widget build(BuildContext context) {
    final andishi = thamani.trim().isEmpty ? '—' : thamani.trim().toUpperCase();
    return Text.rich(
      TextSpan(
        children: [
          TextSpan(text: '$kichwa: ', style: const TextStyle(fontSize: 8, color: Color(0xFF667085))),
          TextSpan(text: andishi, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800)),
        ],
      ),
      maxLines: 2,
      overflow: TextOverflow.ellipsis,
    );
  }
}

class _QrNdogo extends StatelessWidget {
  final String data;
  final double size;

  const _QrNdogo({required this.data, required this.size});

  @override
  Widget build(BuildContext context) {
    final code = QrCode.fromData(data: data, errorCorrectLevel: QrErrorCorrectLevel.M);
    final image = QrImage(code);
    return CustomPaint(size: Size(size, size), painter: _QrPainter(image));
  }
}

class _QrPainter extends CustomPainter {
  final QrImage image;

  _QrPainter(this.image);

  @override
  void paint(Canvas canvas, Size size) {
    final count = image.moduleCount;
    final cell = size.width / count;
    final paint = Paint()..color = const Color(0xFF1B2430);
    for (var row = 0; row < count; row++) {
      for (var col = 0; col < count; col++) {
        if (image.isDark(row, col)) {
          canvas.drawRect(Rect.fromLTWH(col * cell, row * cell, cell, cell), paint);
        }
      }
    }
  }

  @override
  bool shouldRepaint(covariant _QrPainter oldDelegate) => oldDelegate.image != image;
}

class _KadiNyuma extends StatelessWidget {
  final String simuYaKampuni;

  const _KadiNyuma({required this.simuYaKampuni});

  @override
  Widget build(BuildContext context) {
    final simu = simuYaKampuni.trim().isEmpty ? '—' : simuYaKampuni.trim();
    return AspectRatio(
      aspectRatio: 85.6 / 54,
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: const Color(0xFFE4EAF2))),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Text('Kimetolewa na Idara ya Milipuko', textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
            const Text('Msasa Gold Mine', textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
            const Text('S.L.P 02 BUKOMBE, GEITA', textAlign: TextAlign.center, style: TextStyle(fontSize: 11)),
            const SizedBox(height: 2),
            Text('Ukikiokota tafadhali wasiliana nasi kupitia $simu', textAlign: TextAlign.center, style: const TextStyle(fontSize: 11)),
            const SizedBox(height: 10),
            Container(width: 150, height: 1.2, color: const Color(0xFF1B2430)),
            const SizedBox(height: 4),
            const Text('KATIBU WA IDARA', textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.w800, fontSize: 11, letterSpacing: 0.4)),
          ],
        ),
      ),
    );
  }
}

class _BarcodeNdogo extends StatelessWidget {
  final String data;

  const _BarcodeNdogo({required this.data});

  @override
  Widget build(BuildContext context) {
    return SizedBox(width: double.infinity, height: 26, child: CustomPaint(painter: _BarcodePainter(data)));
  }
}

class _BarcodePainter extends CustomPainter {
  final String data;

  _BarcodePainter(this.data);

  @override
  void paint(Canvas canvas, Size size) {
    final code = data.trim();
    if (code.isEmpty) return;
    final bars = Barcode.code128().make(code, width: size.width, height: size.height, drawText: false);
    final paint = Paint()..color = const Color(0xFF1B2430);
    for (final bar in bars) {
      if (bar is BarcodeBar && bar.black) {
        canvas.drawRect(Rect.fromLTWH(bar.left, bar.top, bar.width, bar.height), paint);
      }
    }
  }

  @override
  bool shouldRepaint(covariant _BarcodePainter oldDelegate) => oldDelegate.data != data;
}

class _Beji extends StatelessWidget {
  final bool amefungwa;

  const _Beji({required this.amefungwa});

  @override
  Widget build(BuildContext context) {
    final rangi = amefungwa ? const Color(0xFFB42318) : const Color(0xFF0B6E4F);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(color: rangi, borderRadius: BorderRadius.circular(20)),
      child: Text(amefungwa ? 'Blocked' : 'Active', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
    );
  }
}

class _Mstari {
  final String kichwa;
  final String thamani;

  const _Mstari(this.kichwa, this.thamani);
}

class _KadiMaelezo extends StatelessWidget {
  final List<_Mstari> mistari;

  const _KadiMaelezo({required this.mistari});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE4EAF2)),
      ),
      child: Column(
        children: mistari
            .map(
              (mstari) => Padding(
                padding: const EdgeInsets.symmetric(vertical: 6),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SizedBox(width: 110, child: Text(mstari.kichwa, style: const TextStyle(color: Color(0xFF5C6B7A)))),
                    Expanded(child: Text(mstari.thamani, style: const TextStyle(fontWeight: FontWeight.w600))),
                  ],
                ),
              ),
            )
            .toList(),
      ),
    );
  }
}
