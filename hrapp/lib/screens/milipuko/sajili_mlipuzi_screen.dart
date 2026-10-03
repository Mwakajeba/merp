import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../../config/api_config.dart';
import '../../services/milipuko_service.dart';

class SajiliMlipuziScreen extends StatefulWidget {
  const SajiliMlipuziScreen({super.key});

  @override
  State<SajiliMlipuziScreen> createState() => _SajiliMlipuziScreenState();
}

class _SajiliMlipuziScreenState extends State<SajiliMlipuziScreen> {
  final _form = GlobalKey<FormState>();
  final _jina = TextEditingController();
  final _bc = TextEditingController();
  final _simu = TextEditingController();
  final _simuMbadala = TextEditingController();
  final _eneo = TextEditingController();
  final _mwasilianoJina = TextEditingController();
  final _mwasilianoSimu = TextEditingController();
  final _uhusiano = TextEditingController();

  bool _loading = true;
  bool _inahifadhi = false;
  String _hali = 'active';
  String _aina = 'yake';
  String? _mkoa;
  String? _wilaya;
  int? _mwenyeBc;
  String? _picha;
  List<String> _mikoa = [];
  Map<String, List<String>> _wilayaZote = {};
  List<Map<String, dynamic>> _wenyeBc = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _jina.dispose();
    _bc.dispose();
    _simu.dispose();
    _simuMbadala.dispose();
    _eneo.dispose();
    _mwasilianoJina.dispose();
    _mwasilianoSimu.dispose();
    _uhusiano.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    final mikoa = await MilipukoService.get(ApiConfig.milipukoMikoa);
    final watu = await MilipukoService.get(ApiConfig.milipukoWalipuaji);
    if (!mounted) return;
    final data = mikoa['data'] as Map<String, dynamic>? ?? {};
    final wilaya = <String, List<String>>{};
    final ghafi = data['wilaya'];
    if (ghafi is Map) {
      ghafi.forEach((k, v) {
        if (v is List) wilaya[k.toString()] = v.map((e) => e.toString()).toList();
      });
    }
    final orodha = ((watu['data'] as Map?)?['walipuaji'] as List?) ?? [];
    setState(() {
      _loading = false;
      _mikoa = ((data['mikoa'] as List?) ?? []).map((e) => e.toString()).toList();
      _wilayaZote = wilaya;
      _wenyeBc = orodha.whereType<Map>().map((m) => Map<String, dynamic>.from(m)).where((m) => m['aina_ya_bc'] == 'yake').toList();
    });
  }

  Future<void> _chaguaPicha() async {
    final chanzo = await showModalBottomSheet<ImageSource>(
      context: context,
      builder: (context) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.photo_camera_rounded),
              title: const Text('Piga picha'),
              onTap: () => Navigator.pop(context, ImageSource.camera),
            ),
            ListTile(
              leading: const Icon(Icons.photo_library_rounded),
              title: const Text('Chagua kwenye simu'),
              onTap: () => Navigator.pop(context, ImageSource.gallery),
            ),
          ],
        ),
      ),
    );
    if (chanzo == null) return;
    final file = await ImagePicker().pickImage(source: chanzo, imageQuality: 70, maxWidth: 1200);
    if (file == null || !mounted) return;
    setState(() => _picha = file.path);
  }

  Future<void> _hifadhi() async {
    if (!_form.currentState!.validate() || _inahifadhi) return;
    final anaMwasiliano = _mwasilianoJina.text.trim().isNotEmpty || _mwasilianoSimu.text.trim().isNotEmpty || _uhusiano.text.trim().isNotEmpty;
    if (anaMwasiliano && (_mwasilianoJina.text.trim().isEmpty || _mwasilianoSimu.text.trim().isEmpty || _uhusiano.text.trim().isEmpty)) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Jaza jina, simu na uhusiano wa mtu wa kuwasiliana navyo.')));
      return;
    }

    setState(() => _inahifadhi = true);
    final fields = <String, String>{
      'jina': _jina.text.trim(),
      'hali': _hali,
      'aina_ya_bc': _aina,
      'simu': _simu.text.trim(),
      'mkoa': _mkoa ?? '',
      'wilaya': _wilaya ?? '',
    };
    if (_aina == 'yake') {
      fields['bc_no'] = _bc.text.trim();
    } else if (_mwenyeBc != null) {
      fields['bc_ya_mlipuzi_id'] = _mwenyeBc.toString();
    }
    if (_simuMbadala.text.trim().isNotEmpty) fields['simu_mbadala'] = _simuMbadala.text.trim();
    if (_eneo.text.trim().isNotEmpty) fields['eneo'] = _eneo.text.trim();
    if (anaMwasiliano) {
      fields['mwasiliano_jina'] = _mwasilianoJina.text.trim();
      fields['mwasiliano_simu'] = _mwasilianoSimu.text.trim();
      fields['mwasiliano_uhusiano'] = _uhusiano.text.trim();
    }

    final result = await MilipukoService.postMultipart(
      ApiConfig.milipukoWalipuaji,
      fields,
      fileField: 'picha',
      filePath: _picha,
    );
    if (!mounted) return;
    setState(() => _inahifadhi = false);
    if (result['success'] != true) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text((result['message'] ?? 'Imeshindikana.').toString())));
      return;
    }
    final mlipuzi = (result['data'] as Map?)?['mlipuzi'];
    final id = mlipuzi is Map ? mlipuzi['id'] : null;
    Navigator.pop(context, id is int ? id : int.tryParse('$id'));
  }

  @override
  Widget build(BuildContext context) {
    final wilaya = _mkoa == null ? <String>[] : (_wilayaZote[_mkoa] ?? []);

    return Scaffold(
      backgroundColor: const Color(0xFFF3F6FB),
      appBar: AppBar(
        title: const Text('Sajili mlipuaji'),
        backgroundColor: const Color(0xFF1A4F8B),
        foregroundColor: Colors.white,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : Form(
              key: _form,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
                children: [
                  TextFormField(
                    controller: _jina,
                    decoration: const InputDecoration(labelText: 'Jina', filled: true, fillColor: Colors.white),
                    validator: (v) => (v ?? '').trim().isEmpty ? 'Jina linahitajika.' : null,
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    initialValue: _hali,
                    decoration: const InputDecoration(labelText: 'Hali', filled: true, fillColor: Colors.white),
                    items: const [
                      DropdownMenuItem(value: 'active', child: Text('Active')),
                      DropdownMenuItem(value: 'blocked', child: Text('Blocked')),
                    ],
                    onChanged: (v) => setState(() => _hali = v ?? 'active'),
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    initialValue: _aina,
                    decoration: const InputDecoration(labelText: 'BC', filled: true, fillColor: Colors.white),
                    items: const [
                      DropdownMenuItem(value: 'yake', child: Text('BC yake')),
                      DropdownMenuItem(value: 'mtu', child: Text('Anatumia BC ya mtu')),
                    ],
                    onChanged: (v) => setState(() => _aina = v ?? 'yake'),
                  ),
                  const SizedBox(height: 12),
                  if (_aina == 'yake')
                    TextFormField(
                      controller: _bc,
                      decoration: const InputDecoration(labelText: 'BC No.', filled: true, fillColor: Colors.white),
                      validator: (v) => _aina == 'yake' && (v ?? '').trim().isEmpty ? 'BC No. inahitajika.' : null,
                    )
                  else
                    DropdownButtonFormField<int>(
                      initialValue: _mwenyeBc,
                      decoration: const InputDecoration(labelText: 'Mwenye BC', filled: true, fillColor: Colors.white),
                      items: _wenyeBc
                          .where((m) => m['id'] is int)
                          .map((m) => DropdownMenuItem(value: m['id'] as int, child: Text('${m['jina']} · ${m['bc_no'] ?? ''}')))
                          .toList(),
                      onChanged: (v) => setState(() => _mwenyeBc = v),
                      validator: (v) => _aina == 'mtu' && v == null ? 'Chagua mwenye BC.' : null,
                    ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _simu,
                    keyboardType: TextInputType.phone,
                    decoration: const InputDecoration(labelText: 'Simu', filled: true, fillColor: Colors.white),
                    validator: (v) => (v ?? '').trim().isEmpty ? 'Simu inahitajika.' : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _simuMbadala,
                    keyboardType: TextInputType.phone,
                    decoration: const InputDecoration(labelText: 'Simu mbadala', filled: true, fillColor: Colors.white),
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    initialValue: _mkoa,
                    decoration: const InputDecoration(labelText: 'Mkoa', filled: true, fillColor: Colors.white),
                    items: _mikoa.map((m) => DropdownMenuItem(value: m, child: Text(m))).toList(),
                    onChanged: (v) => setState(() {
                      _mkoa = v;
                      _wilaya = null;
                    }),
                    validator: (v) => v == null ? 'Chagua mkoa.' : null,
                  ),
                  const SizedBox(height: 12),
                    DropdownButtonFormField<String>(
                    key: ValueKey('wilaya-${_mkoa ?? ''}'),
                    initialValue: _wilaya,
                    decoration: const InputDecoration(labelText: 'Wilaya', filled: true, fillColor: Colors.white),
                    items: wilaya.map((m) => DropdownMenuItem(value: m, child: Text(m))).toList(),
                    onChanged: (v) => setState(() => _wilaya = v),
                    validator: (v) => v == null ? 'Chagua wilaya.' : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _eneo,
                    maxLines: 2,
                    decoration: const InputDecoration(labelText: 'Eneo', filled: true, fillColor: Colors.white),
                  ),
                  const SizedBox(height: 12),
                  OutlinedButton.icon(
                    onPressed: _chaguaPicha,
                    icon: const Icon(Icons.add_a_photo_rounded),
                    label: Text(_picha == null ? 'Picha (si lazima)' : 'Picha imechaguliwa'),
                  ),
                  const SizedBox(height: 18),
                  const Text('Mtu wa kuwasiliana naye (si lazima)', style: TextStyle(fontWeight: FontWeight.w700)),
                  const SizedBox(height: 8),
                  TextFormField(
                    controller: _mwasilianoJina,
                    decoration: const InputDecoration(labelText: 'Jina', filled: true, fillColor: Colors.white),
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _mwasilianoSimu,
                    keyboardType: TextInputType.phone,
                    decoration: const InputDecoration(labelText: 'Simu', filled: true, fillColor: Colors.white),
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _uhusiano,
                    decoration: const InputDecoration(labelText: 'Uhusiano', filled: true, fillColor: Colors.white),
                  ),
                  const SizedBox(height: 20),
                  FilledButton(
                    onPressed: _inahifadhi ? null : _hifadhi,
                    child: Text(_inahifadhi ? 'Inahifadhi...' : 'Hifadhi'),
                  ),
                ],
              ),
            ),
    );
  }
}
