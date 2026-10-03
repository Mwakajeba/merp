import 'package:flutter/material.dart';

import '../../config/api_config.dart';
import '../../services/milipuko_service.dart';
import 'risiti.dart';

class KataMaweScreen extends StatefulWidget {
  const KataMaweScreen({super.key});

  @override
  State<KataMaweScreen> createState() => _KataMaweScreenState();
}

class _KataMaweScreenState extends State<KataMaweScreen> {
  bool _loading = true;
  bool _saving = false;
  String? _error;
  String _katibu = '';
  String _karatasi = '80';
  String _aina = 'mawe';
  List<Map<String, dynamic>> _maduara = [];
  Map<String, dynamic>? _duara;
  Map<String, dynamic>? _msimamizi;
  final _mifuko = TextEditingController(text: '1');
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
    _mifuko.dispose();
    _msimamiziJina.dispose();
    _msimamiziSimu.dispose();
    super.dispose();
  }

  String get _tareheIso => '${_tarehe.year.toString().padLeft(4, '0')}-${_tarehe.month.toString().padLeft(2, '0')}-${_tarehe.day.toString().padLeft(2, '0')}';

  Future<void> _load({bool wekaUpyaDuara = false}) async {
    final result = await MilipukoService.get('${ApiConfig.milipukoFomu}?tarehe=${Uri.encodeQueryComponent(_tareheIso)}');
    if (!mounted) return;
    final data = result['data'] as Map<String, dynamic>? ?? {};
    final maduara = ((data['maduara'] as List?) ?? []).cast<Map<String, dynamic>>();
    setState(() {
      _loading = false;
      _error = result['success'] == true ? null : (result['message'] ?? 'Imeshindikana kupakia fomu.').toString();
      _katibu = (data['katibu'] ?? '').toString();
      _maduara = maduara;
      if (wekaUpyaDuara || !maduara.any((duara) => duara['id'] == _duara?['id'])) {
        _duara = null;
        _msimamizi = null;
      }
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
    final mifuko = int.tryParse(_mifuko.text.trim()) ?? 0;
    final andikaMsimamizi = _duara != null && _wasimamizi.isEmpty;
    if (_duara == null || mifuko < 1 || (andikaMsimamizi ? (_msimamiziJina.text.trim().isEmpty || _msimamiziSimu.text.trim().isEmpty) : _msimamizi == null)) {
      _onyesha('Jaza duara, msimamizi na idadi ya mifuko.');
      return;
    }
    setState(() => _saving = true);
    final result = await MilipukoService.post(ApiConfig.milipukoMawe, {
      'duara_id': _duara!['id'],
      if (andikaMsimamizi) 'msimamizi_jina': _msimamiziJina.text.trim(),
      if (andikaMsimamizi) 'msimamizi_simu': _msimamiziSimu.text.trim(),
      if (!andikaMsimamizi) 'msimamizi_id': _msimamizi!['id'],
      'idadi_ya_mifuko': mifuko,
      'aina_ya_mzigo': _aina,
      'tarehe': _tareheIso,
    });
    if (!mounted) return;
    setState(() => _saving = false);
    _onyesha((result['message'] ?? 'Imeshindikana.').toString());
    if (result['success'] == true) {
      final mawe = (result['data'] as Map?)?['mawe'];
      if (mawe is Map) {
        await chapishaKibaliChaMawe(Map<String, dynamic>.from(mawe), _karatasi);
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
      appBar: AppBar(title: const Text('Kata kibali cha mawe'), backgroundColor: const Color(0xFF1A4F8B), foregroundColor: Colors.white),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Padding(padding: const EdgeInsets.all(24), child: Text(_error!, textAlign: TextAlign.center)))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    const Text('Aina ya mzigo', style: TextStyle(fontWeight: FontWeight.w700)),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(child: _PrinterMawe(label: 'Mawe', selected: _aina == 'mawe', onTap: () => setState(() => _aina = 'mawe'))),
                        const SizedBox(width: 10),
                        Expanded(child: _PrinterMawe(label: 'Chorongeo', selected: _aina == 'chorongeo', onTap: () => setState(() => _aina = 'chorongeo'))),
                      ],
                    ),
                    if (_maduara.isEmpty)
                      const Padding(
                        padding: EdgeInsets.only(top: 12),
                        child: Text('Hakuna duara lililosajiliwa kuwa limezalisha tarehe hii.'),
                      ),
                    const SizedBox(height: 12),
                    _ChaguoMawe(
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
                      _ChaguoMawe(
                        label: 'Msimamizi wa duara',
                        value: _msimamizi == null ? null : '${_msimamizi!['jina']} (${_msimamizi!['simu'] ?? ''})',
                        onTap: () async {
                          final item = await _chagua('Tafuta msimamizi', _wasimamizi, (m) => '${m['jina']} ${m['simu'] ?? ''}');
                          if (item != null) setState(() => _msimamizi = item);
                        },
                      ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _mifuko,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Idadi ya mifuko'),
                    ),
                    ListTile(
                      contentPadding: EdgeInsets.zero,
                      title: const Text('Tarehe'),
                      subtitle: Text('${_tarehe.day.toString().padLeft(2, '0')}/${_tarehe.month.toString().padLeft(2, '0')}/${_tarehe.year}'),
                      trailing: const Icon(Icons.calendar_today),
                      onTap: () async {
                        final picked = await showDatePicker(context: context, firstDate: DateTime(2020), lastDate: DateTime(2100), initialDate: _tarehe);
                        if (picked != null) {
                          setState(() => _tarehe = picked);
                          _load(wekaUpyaDuara: true);
                        }
                      },
                    ),
                    InputDecorator(
                      decoration: const InputDecoration(labelText: 'Katibu'),
                      child: Text(_katibu.isEmpty ? '—' : _katibu),
                    ),
                    const SizedBox(height: 16),
                    const Text('Printer', style: TextStyle(fontWeight: FontWeight.w700)),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(child: _PrinterMawe(label: 'POS 58mm', selected: _karatasi == '58', onTap: () => setState(() => _karatasi = '58'))),
                        const SizedBox(width: 10),
                        Expanded(child: _PrinterMawe(label: 'POS 80mm', selected: _karatasi == '80', onTap: () => setState(() => _karatasi = '80'))),
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

class _PrinterMawe extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _PrinterMawe({required this.label, required this.selected, required this.onTap});

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

class _ChaguoMawe extends StatelessWidget {
  final String label;
  final String? value;
  final VoidCallback onTap;

  const _ChaguoMawe({required this.label, required this.value, required this.onTap});

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
