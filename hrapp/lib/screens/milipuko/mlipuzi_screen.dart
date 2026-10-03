import 'package:flutter/material.dart';

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
    final amefungwa = mtu['hali'] == 'blocked';
    return AspectRatio(
      aspectRatio: 85.6 / 54,
      child: Container(
        decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: const Color(0xFFE4EAF2))),
        clipBehavior: Clip.antiAlias,
        child: Column(
          children: [
            Container(
              color: const Color(0xFF0B6E4F),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              child: Row(
                children: [
                  const Text('MILIPUKO', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 12)),
                  const Spacer(),
                  Flexible(child: Text((mtu['kampuni'] ?? 'Msasa Gold Mine').toString(), overflow: TextOverflow.ellipsis, style: const TextStyle(color: Colors.white, fontSize: 11))),
                ],
              ),
            ),
            const Align(
              alignment: Alignment.centerLeft,
              child: Padding(
                padding: EdgeInsets.fromLTRB(12, 6, 12, 0),
                child: Text('KITAMBULISHO CHA MLIPUAJI', style: TextStyle(fontSize: 9, letterSpacing: 0.4, color: Color(0xFF667085))),
              ),
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(12, 6, 12, 8),
                child: Row(
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(6),
                      child: picha.isEmpty
                          ? Container(width: 58, height: 70, color: const Color(0xFFD7DEE6), alignment: Alignment.center, child: const Text('Hakuna\npicha', textAlign: TextAlign.center, style: TextStyle(fontSize: 9)))
                          : Image.network(picha, width: 58, height: 70, fit: BoxFit.cover),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text((mtu['jina'] ?? '').toString(), maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w800)),
                          Text('BC No. ${mtu['bc_no'] ?? '—'}', style: const TextStyle(fontSize: 12)),
                          Text((mtu['simu'] ?? '').toString(), style: const TextStyle(fontSize: 12)),
                          Text('${mtu['mkoa'] ?? ''}, ${mtu['wilaya'] ?? ''}', style: const TextStyle(fontSize: 12)),
                          const SizedBox(height: 4),
                          _Beji(amefungwa: amefungwa),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
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
            Image.asset('assets/muhuri.jpg', height: 72, fit: BoxFit.contain),
            const SizedBox(height: 4),
            const Text('Kimetolewa na Idara ya Milipuko', textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
            const Text('Msasa Gold Mine', textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
            const Text('S.L.P 02 BUKOMBE, GEITA', textAlign: TextAlign.center, style: TextStyle(fontSize: 11)),
            const SizedBox(height: 2),
            Text('Ukikiokota tafadhali wasiliana nasi kupitia $simu', textAlign: TextAlign.center, style: const TextStyle(fontSize: 11)),
          ],
        ),
      ),
    );
  }
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
