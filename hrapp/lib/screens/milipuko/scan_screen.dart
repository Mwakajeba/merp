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
  final MobileScannerController _camera = MobileScannerController();
  bool _inasoma = false;
  Map<String, dynamic>? _jibu;
  String? _code;

  @override
  void dispose() {
    _camera.dispose();
    super.dispose();
  }

  Future<void> _soma(String code) async {
    if (_inasoma) return;
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
    final kichwa = widget.aina == 'kibali' ? 'Scan kibali' : 'Scan kitambulisho';
    return Scaffold(
      appBar: AppBar(title: Text(kichwa), backgroundColor: const Color(0xFF1A4F8B), foregroundColor: Colors.white),
      body: _jibu == null ? _kamera() : _matokeo(),
    );
  }

  Widget _kamera() {
    return Column(
      children: [
        Expanded(
          child: MobileScanner(
            controller: _camera,
            onDetect: (capture) {
              final code = capture.barcodes.isEmpty ? null : capture.barcodes.first.rawValue;
              if (code != null && code.isNotEmpty) _soma(code);
            },
          ),
        ),
        const Padding(
          padding: EdgeInsets.all(16),
          child: Text('Elekeza kamera kwenye QR ya kibali au kitambulisho cha blasta.'),
        ),
      ],
    );
  }

  Widget _matokeo() {
    final jibu = _jibu!;
    final halali = jibu['halali'] == true;
    final kimetumika = jibu['kimetumika'] == true;
    final aina = (jibu['aina'] ?? '').toString();
    final siAinaSahihi = widget.aina == 'kibali' ? aina == 'mlipuzi' : aina == 'kibali';
    final rangi = !halali || siAinaSahihi ? Colors.red.shade700 : (kimetumika ? Colors.orange.shade800 : Colors.green.shade700);
    final kibali = jibu['kibali'] as Map<String, dynamic>?;
    final mtu = jibu['mlipuzi'] as Map<String, dynamic>?;
    final picha = MilipukoService.mediaUrl(mtu?['picha']?.toString());

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(color: rangi, borderRadius: BorderRadius.circular(12)),
          child: Text(
            siAinaSahihi ? 'Msimbo huu si ${widget.aina == 'kibali' ? 'kibali' : 'kitambulisho cha blasta'}.' : (jibu['ujumbe'] ?? '').toString(),
            style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w700),
          ),
        ),
        const SizedBox(height: 16),
        if (kibali != null) ...[
          _mstari('Namba', kibali['namba']),
          _mstari('Tarehe', kibali['tarehe']),
          _mstari('Duara', kibali['duara']),
          _mstari('Mlipuaji', kibali['mlipuaji']),
          _mstari('Msimamizi', kibali['msimamizi']),
          _mstari('Hali', kibali['hali']),
          _mstari('Aina', kibali['aina']),
          _mstari('Kilichotumika', kibali['imetumika_saa']),
          if (halali && !kimetumika && !siAinaSahihi)
            Padding(
              padding: const EdgeInsets.only(top: 12),
              child: FilledButton(onPressed: _tumia, child: const Text('Weka kimetumika')),
            ),
        ],
        if (mtu != null) ...[
          if (picha.isNotEmpty)
            Center(child: Image.network(picha, height: 160, errorBuilder: (_, _, _) => const Icon(Icons.person, size: 80))),
          _mstari('Jina', mtu['jina']),
          _mstari('BC No.', mtu['bc_no']),
          _mstari('Hali', mtu['amefungwa'] == true ? 'Blocked' : 'Active'),
          _mstari('Simu', mtu['simu']),
        ],
        const SizedBox(height: 16),
        OutlinedButton(onPressed: _scanTena, child: const Text('Scan tena')),
      ],
    );
  }

  Widget _mstari(String kichwa, dynamic thamani) {
    return ListTile(
      contentPadding: EdgeInsets.zero,
      title: Text(kichwa),
      subtitle: Text((thamani ?? '—').toString()),
    );
  }
}
