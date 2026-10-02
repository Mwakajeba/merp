import 'package:flutter/material.dart';

import '../../config/api_config.dart';
import '../../services/milipuko_service.dart';

class KataKibaliScreen extends StatefulWidget {
  const KataKibaliScreen({super.key});

  @override
  State<KataKibaliScreen> createState() => _KataKibaliScreenState();
}

class _KataKibaliScreenState extends State<KataKibaliScreen> {
  bool _loading = true;
  bool _saving = false;
  String? _error;
  String _katibu = '';
  List<Map<String, dynamic>> _maduara = [];
  List<Map<String, dynamic>> _walipuaji = [];

  String _hali = 'uzalishaji';
  String _aina = 'cotex';
  Map<String, dynamic>? _duara;
  Map<String, dynamic>? _msimamizi;
  Map<String, dynamic>? _mlipuzi;
  final List<Map<String, dynamic>?> _wachorongaji = List.filled(5, null);
  final _matundu = TextEditingController();
  final _idara = TextEditingController();
  DateTime _tarehe = DateTime.now();

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _matundu.dispose();
    _idara.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    final result = await MilipukoService.get(ApiConfig.milipukoFomu);
    if (!mounted) return;
    final data = result['data'] as Map<String, dynamic>? ?? {};
    setState(() {
      _loading = false;
      _error = result['success'] == true ? null : (result['message'] ?? 'Imeshindikana kupakia fomu.').toString();
      _katibu = (data['katibu'] ?? '').toString();
      _maduara = ((data['maduara'] as List?) ?? []).cast<Map<String, dynamic>>();
      _walipuaji = ((data['walipuaji'] as List?) ?? []).cast<Map<String, dynamic>>();
    });
  }

  List<Map<String, dynamic>> get _wasimamizi {
    final list = (_duara?['wasimamizi'] as List?) ?? [];
    return list.cast<Map<String, dynamic>>();
  }

  Future<Map<String, dynamic>?> _chagua(String kichwa, List<Map<String, dynamic>> orodha, String Function(Map<String, dynamic>) lebo) {
    return showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      builder: (context) {
        var chujio = '';
        return StatefulBuilder(
          builder: (context, setSheet) {
            final yaliyochujwa = orodha.where((item) => lebo(item).toLowerCase().contains(chujio.toLowerCase())).toList();
            return Padding(
              padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
              child: SizedBox(
                height: 420,
                child: Column(
                  children: [
                    Padding(
                      padding: const EdgeInsets.all(12),
                      child: TextField(
                        decoration: InputDecoration(labelText: kichwa, prefixIcon: const Icon(Icons.search)),
                        onChanged: (value) => setSheet(() => chujio = value),
                      ),
                    ),
                    Expanded(
                      child: ListView(
                        children: yaliyochujwa
                            .map((item) => ListTile(title: Text(lebo(item)), onTap: () => Navigator.pop(context, item)))
                            .toList(),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Future<void> _hifadhi() async {
    if (_duara == null || _msimamizi == null || _mlipuzi == null || _matundu.text.trim().isEmpty || _idara.text.trim().isEmpty) {
      _onyesha('Jaza duara, msimamizi, mlipuaji, matundu na msimamizi wa idara.');
      return;
    }
    setState(() => _saving = true);
    final result = await MilipukoService.post(ApiConfig.milipukoVibali, {
      'hali': _hali,
      'duara_id': _duara!['id'],
      'msimamizi_id': _msimamizi!['id'],
      'mlipuzi_id': _mlipuzi!['id'],
      'idadi_ya_matundu': int.tryParse(_matundu.text.trim()) ?? 0,
      'tarehe': '${_tarehe.year.toString().padLeft(4, '0')}-${_tarehe.month.toString().padLeft(2, '0')}-${_tarehe.day.toString().padLeft(2, '0')}',
      'aina_ya_mlipuko': _aina,
      'msimamizi_wa_idara': _idara.text.trim(),
      'wachorongaji': _wachorongaji.whereType<Map<String, dynamic>>().map((m) => m['id']).toList(),
    });
    if (!mounted) return;
    setState(() => _saving = false);
    _onyesha((result['message'] ?? 'Imeshindikana.').toString());
    if (result['success'] == true) Navigator.pop(context);
  }

  void _onyesha(String ujumbe) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ujumbe)));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Kata kibali'), backgroundColor: const Color(0xFF1A4F8B), foregroundColor: Colors.white),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Padding(padding: const EdgeInsets.all(24), child: Text(_error!, textAlign: TextAlign.center)))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    DropdownButtonFormField<String>(
                      initialValue: _hali,
                      decoration: const InputDecoration(labelText: 'Hali'),
                      items: const [
                        DropdownMenuItem(value: 'uzalishaji', child: Text('Uzalishaji')),
                        DropdownMenuItem(value: 'ufreshiaji', child: Text('Ufreshiaji')),
                        DropdownMenuItem(value: 'ufukuziaji', child: Text('Ufukuziaji')),
                      ],
                      onChanged: (value) => setState(() => _hali = value ?? 'uzalishaji'),
                    ),
                    const SizedBox(height: 12),
                    _Chaguo(
                      label: 'Duara No.',
                      value: _duara?['namba']?.toString(),
                      onTap: () async {
                        final item = await _chagua('Tafuta duara', _maduara, (m) => m['namba'].toString());
                        if (item == null) return;
                        setState(() {
                          _duara = item;
                          _msimamizi = null;
                        });
                      },
                    ),
                    const SizedBox(height: 12),
                    _Chaguo(
                      label: 'Msimamizi wa duara',
                      value: _msimamizi == null ? null : '${_msimamizi!['jina']} (${_msimamizi!['simu'] ?? ''})',
                      onTap: () async {
                        final item = await _chagua('Tafuta msimamizi', _wasimamizi, (m) => '${m['jina']} ${m['simu'] ?? ''}');
                        if (item != null) setState(() => _msimamizi = item);
                      },
                    ),
                    const SizedBox(height: 12),
                    _Chaguo(
                      label: 'Mlipuaji (blasta)',
                      value: _mlipuzi == null ? null : '${_mlipuzi!['jina']} — ${_mlipuzi!['bc_no'] ?? ''}',
                      onTap: () async {
                        final item = await _chagua('Tafuta mlipuaji', _walipuaji, (m) => '${m['jina']} ${m['bc_no'] ?? ''}');
                        if (item != null) setState(() => _mlipuzi = item);
                      },
                    ),
                    const SizedBox(height: 8),
                    Text('BC No.: ${_mlipuzi?['bc_no'] ?? '—'}'),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _matundu,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Idadi ya matundu'),
                    ),
                    const SizedBox(height: 12),
                    ListTile(
                      contentPadding: EdgeInsets.zero,
                      title: const Text('Tarehe'),
                      subtitle: Text('${_tarehe.day.toString().padLeft(2, '0')}/${_tarehe.month.toString().padLeft(2, '0')}/${_tarehe.year}'),
                      trailing: const Icon(Icons.calendar_today),
                      onTap: () async {
                        final picked = await showDatePicker(context: context, firstDate: DateTime(2020), lastDate: DateTime(2100), initialDate: _tarehe);
                        if (picked != null) setState(() => _tarehe = picked);
                      },
                    ),
                    const Text('Wachorongaji (si lazima)', style: TextStyle(fontWeight: FontWeight.w700)),
                    for (var i = 0; i < 5; i++)
                      Padding(
                        padding: const EdgeInsets.only(top: 8),
                        child: _Chaguo(
                          label: 'Mchorongaji ${i + 1}',
                          value: _wachorongaji[i] == null ? null : _wachorongaji[i]!['jina']?.toString(),
                          onTap: () async {
                            final item = await _chagua('Tafuta mchorongaji', _walipuaji, (m) => '${m['jina']} ${m['bc_no'] ?? ''}');
                            if (item != null) setState(() => _wachorongaji[i] = item);
                          },
                        ),
                      ),
                    const SizedBox(height: 12),
                    const Text('Aina ya mlipuko', style: TextStyle(fontWeight: FontWeight.w700)),
                    RadioListTile<String>(value: 'cotex', groupValue: _aina, title: const Text('COTEX'), onChanged: (v) => setState(() => _aina = v!)),
                    RadioListTile<String>(value: 'dull_fuse', groupValue: _aina, title: const Text('DULL FUSE'), onChanged: (v) => setState(() => _aina = v!)),
                    TextFormField(controller: _idara, decoration: const InputDecoration(labelText: 'Msimamizi wa idara')),
                    const SizedBox(height: 12),
                    InputDecorator(
                      decoration: const InputDecoration(labelText: 'Imethibitishwa na katibu'),
                      child: Text(_katibu.isEmpty ? '—' : _katibu),
                    ),
                    const SizedBox(height: 20),
                    FilledButton(
                      onPressed: _saving ? null : _hifadhi,
                      child: Text(_saving ? 'Inahifadhi...' : 'Hifadhi kibali'),
                    ),
                  ],
                ),
    );
  }
}

class _Chaguo extends StatelessWidget {
  final String label;
  final String? value;
  final VoidCallback onTap;

  const _Chaguo({required this.label, required this.value, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: InputDecorator(
        decoration: InputDecoration(labelText: label),
        child: Text(value ?? 'Chagua'),
      ),
    );
  }
}
