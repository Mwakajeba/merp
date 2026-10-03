import 'package:flutter/material.dart';

import '../../config/api_config.dart';
import '../../services/milipuko_service.dart';
import 'mlipuzi_screen.dart';
import 'sajili_mlipuzi_screen.dart';

class WalipuajiScreen extends StatefulWidget {
  const WalipuajiScreen({super.key});

  @override
  State<WalipuajiScreen> createState() => _WalipuajiScreenState();
}

class _WalipuajiScreenState extends State<WalipuajiScreen> {
  bool _loading = true;
  String _tafuta = '';
  String? _error;
  List<Map<String, dynamic>> _walipuaji = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final result = await MilipukoService.get(ApiConfig.milipukoWalipuaji);
    if (!mounted) return;
    final data = result['data'] as Map<String, dynamic>? ?? {};
    setState(() {
      _loading = false;
      _error = result['success'] == true ? null : (result['message'] ?? 'Imeshindikana.').toString();
      _walipuaji = ((data['walipuaji'] as List?) ?? []).whereType<Map>().map((m) => Map<String, dynamic>.from(m)).toList();
    });
  }

  Future<void> _sajili() async {
    final id = await Navigator.push<int>(context, MaterialPageRoute(builder: (_) => const SajiliMlipuziScreen()));
    if (!mounted || id == null) return;
    await _load();
    if (!mounted) return;
    Navigator.push(context, MaterialPageRoute(builder: (_) => MlipuziScreen(id: id)));
  }

  @override
  Widget build(BuildContext context) {
    final orodha = _walipuaji.where((m) {
      final jina = '${m['jina'] ?? ''} ${m['bc_no'] ?? ''}'.toLowerCase();
      return jina.contains(_tafuta.toLowerCase());
    }).toList();

    return Scaffold(
      backgroundColor: const Color(0xFFF3F6FB),
      appBar: AppBar(
        title: const Text('Walipuaji'),
        backgroundColor: const Color(0xFF1A4F8B),
        foregroundColor: Colors.white,
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _sajili,
        backgroundColor: const Color(0xFF0B6E4F),
        foregroundColor: Colors.white,
        icon: const Icon(Icons.person_add_alt_1_rounded),
        label: const Text('Sajili'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                if (_error != null)
                  Padding(padding: const EdgeInsets.fromLTRB(16, 12, 16, 0), child: Text(_error!)),
                Padding(
                  padding: const EdgeInsets.all(12),
                  child: TextField(
                    decoration: const InputDecoration(
                      labelText: 'Tafuta jina au BC',
                      prefixIcon: Icon(Icons.search),
                      filled: true,
                      fillColor: Colors.white,
                      border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(14)), borderSide: BorderSide.none),
                    ),
                    onChanged: (value) => setState(() => _tafuta = value),
                  ),
                ),
                Expanded(
                  child: orodha.isEmpty
                      ? const Center(child: Text('Hakuna mlipuaji aliyepatikana.'))
                      : ListView.separated(
                          padding: const EdgeInsets.fromLTRB(12, 0, 12, 88),
                          itemCount: orodha.length,
                          separatorBuilder: (context, index) => const SizedBox(height: 8),
                          itemBuilder: (context, index) {
                            final mtu = orodha[index];
                            final picha = MilipukoService.mediaUrl(mtu['picha']?.toString());
                            final amefungwa = mtu['hali'] == 'blocked';
                            return Material(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(16),
                              child: ListTile(
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                                leading: CircleAvatar(
                                  backgroundImage: picha.isEmpty ? null : NetworkImage(picha),
                                  child: picha.isEmpty ? const Icon(Icons.person) : null,
                                ),
                                title: Text((mtu['jina'] ?? '').toString(), style: const TextStyle(fontWeight: FontWeight.w700)),
                                subtitle: Text('${mtu['bc_no'] ?? '—'} · ${mtu['simu'] ?? ''}'),
                                trailing: Icon(Icons.circle, size: 12, color: amefungwa ? const Color(0xFFB42318) : const Color(0xFF0B6E4F)),
                                onTap: () {
                                  final id = int.tryParse('${mtu['id']}');
                                  if (id == null) return;
                                  Navigator.push(context, MaterialPageRoute(builder: (_) => MlipuziScreen(id: id)));
                                },
                              ),
                            );
                          },
                        ),
                ),
              ],
            ),
    );
  }
}
