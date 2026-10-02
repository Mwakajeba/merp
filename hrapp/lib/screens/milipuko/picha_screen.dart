import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../../config/api_config.dart';
import '../../services/milipuko_service.dart';

class PichaScreen extends StatefulWidget {
  const PichaScreen({super.key});

  @override
  State<PichaScreen> createState() => _PichaScreenState();
}

class _PichaScreenState extends State<PichaScreen> {
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
      _walipuaji = ((data['walipuaji'] as List?) ?? []).cast<Map<String, dynamic>>();
    });
  }

  Future<void> _piga(Map<String, dynamic> mtu) async {
    final file = await ImagePicker().pickImage(source: ImageSource.camera, imageQuality: 70, maxWidth: 1200);
    if (file == null || !mounted) return;
    final result = await MilipukoService.uploadPicha(mtu['id'] as int, file.path);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text((result['message'] ?? 'Imeshindikana.').toString())));
    if (result['success'] == true) _load();
  }

  @override
  Widget build(BuildContext context) {
    final orodha = _walipuaji.where((m) {
      final jina = '${m['jina'] ?? ''} ${m['bc_no'] ?? ''}'.toLowerCase();
      return jina.contains(_tafuta.toLowerCase());
    }).toList();

    return Scaffold(
      appBar: AppBar(title: const Text('Picha ya mlipuaji'), backgroundColor: const Color(0xFF1A4F8B), foregroundColor: Colors.white),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                if (_error != null) Padding(padding: const EdgeInsets.all(12), child: Text(_error!)),
                Padding(
                  padding: const EdgeInsets.all(12),
                  child: TextField(
                    decoration: const InputDecoration(labelText: 'Tafuta mlipuaji', prefixIcon: Icon(Icons.search)),
                    onChanged: (value) => setState(() => _tafuta = value),
                  ),
                ),
                Expanded(
                  child: ListView.builder(
                    itemCount: orodha.length,
                    itemBuilder: (context, index) {
                      final mtu = orodha[index];
                      final picha = MilipukoService.mediaUrl(mtu['picha']?.toString());
                      return ListTile(
                        leading: CircleAvatar(
                          backgroundImage: picha.isEmpty ? null : NetworkImage(picha),
                          child: picha.isEmpty ? const Icon(Icons.person) : null,
                        ),
                        title: Text((mtu['jina'] ?? '').toString()),
                        subtitle: Text('${mtu['bc_no'] ?? '—'} · ${mtu['hali'] ?? ''}'),
                        trailing: IconButton(icon: const Icon(Icons.photo_camera), onPressed: () => _piga(mtu)),
                      );
                    },
                  ),
                ),
              ],
            ),
    );
  }
}
