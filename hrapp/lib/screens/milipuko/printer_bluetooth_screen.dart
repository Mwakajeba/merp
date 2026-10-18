import 'package:flutter/material.dart';
import 'package:print_bluetooth_thermal/print_bluetooth_thermal.dart';

import '../../services/bluetooth_printer_service.dart';
import 'risiti.dart';

class PrinterBluetoothScreen extends StatefulWidget {
  const PrinterBluetoothScreen({super.key});

  @override
  State<PrinterBluetoothScreen> createState() => _PrinterBluetoothScreenState();
}

class _PrinterBluetoothScreenState extends State<PrinterBluetoothScreen> {
  bool _inatafuta = true;
  bool _inaunganisha = false;
  String? _kosa;
  PrinterIliyohifadhiwa? _iliyohifadhiwa;
  String? _macIliyounganishwa;
  List<BluetoothInfo> _vifaa = [];

  @override
  void initState() {
    super.initState();
    _tafuta();
  }

  Future<void> _tafuta() async {
    setState(() {
      _inatafuta = true;
      _kosa = null;
    });
    try {
      final iliyohifadhiwa = await BluetoothPrinterService.iliyohifadhiwa();
      final vifaa = await BluetoothPrinterService.vifaaVilivyopair();
      if (!mounted) return;
      setState(() {
        _iliyohifadhiwa = iliyohifadhiwa;
        _vifaa = vifaa;
        _inatafuta = false;
      });
    } on BluetoothPrintException catch (e) {
      if (!mounted) return;
      setState(() {
        _kosa = e.message;
        _vifaa = [];
        _inatafuta = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _kosa = 'Imeshindwa kusoma vifaa vya Bluetooth.';
        _inatafuta = false;
      });
    }
  }

  Future<void> _unganisha(BluetoothInfo kifaa) async {
    setState(() => _inaunganisha = true);
    try {
      await BluetoothPrinterService.unganishaNaHifadhi(kifaa);
      if (!mounted) return;
      setState(() {
        _iliyohifadhiwa = PrinterIliyohifadhiwa(jina: kifaa.name, mac: kifaa.macAdress);
        _macIliyounganishwa = kifaa.macAdress;
        _inaunganisha = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('${kifaa.name} imeunganishwa.')));
    } on BluetoothPrintException catch (e) {
      if (!mounted) return;
      setState(() => _inaunganisha = false);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Future<void> _majaribio() async {
    setState(() => _inaunganisha = true);
    try {
      await chapishaMajaribio();
      if (!mounted) return;
      setState(() {
        _macIliyounganishwa = _iliyohifadhiwa?.mac;
        _inaunganisha = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Majaribio yamechapishwa.')));
    } on BluetoothPrintException catch (e) {
      if (!mounted) return;
      setState(() => _inaunganisha = false);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  @override
  Widget build(BuildContext context) {
    final imeunganishwa = _iliyohifadhiwa != null && _macIliyounganishwa == _iliyohifadhiwa!.mac;
    return Scaffold(
      backgroundColor: const Color(0xFFF3F6FB),
      appBar: AppBar(
        title: const Text('Set bluetooth connection'),
        backgroundColor: const Color(0xFF1A4F8B),
        foregroundColor: Colors.white,
      ),
      body: Column(
        children: [
          Expanded(
            child: ListView(
              children: [
                _Kichwa('Connected device'),
                if (imeunganishwa)
                  _Safu(jina: _iliyohifadhiwa!.jina, mac: _iliyohifadhiwa!.mac)
                else
                  const ListTile(title: Text('Not connected')),
                if (_iliyohifadhiwa != null && !imeunganishwa)
                  ListTile(
                    title: Text(_iliyohifadhiwa!.jina),
                    subtitle: Text('${_iliyohifadhiwa!.mac}\nImehifadhiwa. Gusa tena ili kuunganisha.'),
                    isThreeLine: true,
                  ),
                _Kichwa('Paired Device'),
                if (_inatafuta)
                  const Padding(padding: EdgeInsets.all(24), child: Center(child: CircularProgressIndicator()))
                else if (_kosa != null)
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Text(_kosa!, style: const TextStyle(color: Color(0xFFB42318))),
                  )
                else if (_vifaa.isEmpty)
                  const ListTile(
                    title: Text('Hakuna kifaa kilichopair'),
                    subtitle: Text('Pair printer kwenye mipangilio ya simu (Bluetooth), kisha gusa Search.'),
                  )
                else
                  ..._vifaa.map(
                    (kifaa) => _Safu(
                      jina: kifaa.name,
                      mac: kifaa.macAdress,
                      imechaguliwa: kifaa.macAdress == _iliyohifadhiwa?.mac,
                      onTap: _inaunganisha ? null : () => _unganisha(kifaa),
                    ),
                  ),
                _Kichwa('Connectable device'),
                if (!_inatafuta && _kosa == null)
                  ..._vifaa.map(
                    (kifaa) => _Safu(
                      jina: kifaa.name,
                      mac: kifaa.macAdress,
                      onTap: _inaunganisha ? null : () => _unganisha(kifaa),
                    ),
                  ),
                if (_iliyohifadhiwa != null)
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
                    child: FilledButton(
                      onPressed: _inaunganisha ? null : _majaribio,
                      child: Text(_inaunganisha ? 'Inachapisha...' : 'Chapisha majaribio'),
                    ),
                  ),
              ],
            ),
          ),
          SafeArea(
            child: SizedBox(
              width: double.infinity,
              child: TextButton(
                onPressed: _inatafuta ? null : _tafuta,
                style: TextButton.styleFrom(
                  backgroundColor: const Color(0xFFE6E8EC),
                  foregroundColor: Colors.black87,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: const RoundedRectangleBorder(),
                ),
                child: const Text('Search'),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class ChaguaPrinterTile extends StatefulWidget {
  const ChaguaPrinterTile({super.key});

  @override
  State<ChaguaPrinterTile> createState() => _ChaguaPrinterTileState();
}

class _ChaguaPrinterTileState extends State<ChaguaPrinterTile> {
  PrinterIliyohifadhiwa? _printer;

  @override
  void initState() {
    super.initState();
    _pakia();
  }

  Future<void> _pakia() async {
    final printer = await BluetoothPrinterService.iliyohifadhiwa();
    if (mounted) setState(() => _printer = printer);
  }

  @override
  Widget build(BuildContext context) {
    final printer = _printer;
    return ListTile(
      contentPadding: EdgeInsets.zero,
      leading: const Icon(Icons.print_rounded, color: Color(0xFF1A4F8B)),
      title: Text(printer == null ? 'Hakuna printer' : printer.jina),
      subtitle: Text(printer == null ? 'Unganisha POS 58mm ili kuchapisha moja kwa moja' : printer.mac),
      trailing: const Icon(Icons.chevron_right),
      onTap: () async {
        await Navigator.push(context, MaterialPageRoute(builder: (_) => const PrinterBluetoothScreen()));
        _pakia();
      },
    );
  }
}

Future<void> chapishaMojaKwaMoja(
  BuildContext context, {
  required Future<void> Function() chapisha,
}) async {
  while (context.mounted) {
    final printer = await BluetoothPrinterService.iliyohifadhiwa();
    if (!context.mounted) return;
    if (printer == null) {
      await Navigator.push(context, MaterialPageRoute(builder: (_) => const PrinterBluetoothScreen()));
      final baada = await BluetoothPrinterService.iliyohifadhiwa();
      if (baada == null) {
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Imehifadhiwa. Chagua printer ili kuchapisha.')),
          );
        }
        return;
      }
      continue;
    }
    try {
      await chapisha();
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Imechapishwa kwa ${printer.jina}.')));
      }
      return;
    } on BluetoothPrintException catch (e) {
      if (!context.mounted) return;
      final tena = await showDialog<bool>(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('Print imeshindwa'),
          content: Text('${e.message}\n\nWasha printer, hakikisha iko karibu, kisha jaribu tena.'),
          actions: [
            TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Acha')),
            FilledButton(onPressed: () => Navigator.pop(context, true), child: const Text('Jaribu tena')),
          ],
        ),
      );
      if (tena != true) return;
    }
  }
}

class _Kichwa extends StatelessWidget {
  final String jina;

  const _Kichwa(this.jina);

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      color: const Color(0xFFE6E8EC),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Text(jina, style: const TextStyle(color: Color(0xFF3D4450))),
    );
  }
}

class _Safu extends StatelessWidget {
  final String jina;
  final String mac;
  final bool imechaguliwa;
  final VoidCallback? onTap;

  const _Safu({required this.jina, required this.mac, this.imechaguliwa = false, this.onTap});

  @override
  Widget build(BuildContext context) {
    return ListTile(
      title: Text(jina.isEmpty ? 'Printer' : jina, style: const TextStyle(fontWeight: FontWeight.w600)),
      subtitle: Text(mac),
      trailing: imechaguliwa ? const Icon(Icons.check_circle, color: Color(0xFF0B6E4F)) : null,
      onTap: onTap,
    );
  }
}
