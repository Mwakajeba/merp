import 'package:flutter/material.dart';

import '../../config/api_config.dart';
import '../../services/milipuko_service.dart';
import 'printer_bluetooth_screen.dart';
import 'risiti.dart';

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
  String _karatasi = '58';
  Map<String, dynamic>? _duara;
  Map<String, dynamic>? _msimamizi;
  Map<String, dynamic>? _mlipuzi;
  final List<Map<String, dynamic>?> _wachorongaji = List.filled(5, null);
  final _matundu = TextEditingController();
  final _idara = TextEditingController();
  final _msimamiziJina = TextEditingController();
  final _msimamiziSimu = TextEditingController();
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
    _msimamiziJina.dispose();
    _msimamiziSimu.dispose();
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
    final andikaMsimamizi = _duara != null && _wasimamizi.isEmpty;
    if (_duara == null || _mlipuzi == null || _matundu.text.trim().isEmpty || _idara.text.trim().isEmpty || (andikaMsimamizi ? (_msimamiziJina.text.trim().isEmpty || _msimamiziSimu.text.trim().isEmpty) : _msimamizi == null)) {
      _onyesha('Jaza duara, msimamizi, mlipuaji, matundu na msimamizi wa idara.');
      return;
    }
    setState(() => _saving = true);
    final result = await MilipukoService.post(ApiConfig.milipukoVibali, {
      'hali': _hali,
      'duara_id': _duara!['id'],
      if (andikaMsimamizi) 'msimamizi_jina': _msimamiziJina.text.trim(),
      if (andikaMsimamizi) 'msimamizi_simu': _msimamiziSimu.text.trim(),
      if (!andikaMsimamizi) 'msimamizi_id': _msimamizi!['id'],
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
    if (result['success'] == true) {
      final kibali = (result['data'] as Map?)?['kibali'];
      if (kibali is Map && mounted) {
        await chapishaMojaKwaMoja(context, chapisha: () => chapishaKibaliChaMlipuko(Map<String, dynamic>.from(kibali), _karatasi));
      }
      if (mounted) Navigator.pop(context);
    }
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
                    if (_duara != null && _wasimamizi.isEmpty) ...[
                      const Text('Duara hili halina msimamizi. Andika hapa.', style: TextStyle(color: Color(0xFFB54708))),
                      const SizedBox(height: 8),
                      TextFormField(controller: _msimamiziJina, decoration: const InputDecoration(labelText: 'Jina la msimamizi')),
                      const SizedBox(height: 8),
                      TextFormField(controller: _msimamiziSimu, keyboardType: TextInputType.phone, decoration: const InputDecoration(labelText: 'Simu ya msimamizi')),
                    ] else
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
                    const SizedBox(height: 16),
                    const Text('Printer', style: TextStyle(fontWeight: FontWeight.w700)),
                    const ChaguaPrinterTile(),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(child: _PrinterChip(label: 'POS 58mm', selected: _karatasi == '58', onTap: () => setState(() => _karatasi = '58'))),
                        const SizedBox(width: 10),
                        Expanded(child: _PrinterChip(label: 'POS 80mm', selected: _karatasi == '80', onTap: () => setState(() => _karatasi = '80'))),
                      ],
                    ),
                    const SizedBox(height: 20),
                    FilledButton(
                      onPressed: _saving ? null : _hifadhi,
                      child: Text(_saving ? 'Inahifadhi...' : 'Hifadhi na chapisha'),
                    ),
                  ],
                ),
    );
  }
}

class _PrinterChip extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _PrinterChip({required this.label, required this.selected, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(14),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          color: selected ? const Color(0xFF1A4F8B) : Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: const Color(0xFF1A4F8B), width: selected ? 0 : 1.2),
        ),
        child: Text(
          label,
          textAlign: TextAlign.center,
          style: TextStyle(fontWeight: FontWeight.w700, color: selected ? Colors.white : const Color(0xFF1A4F8B)),
        ),
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
