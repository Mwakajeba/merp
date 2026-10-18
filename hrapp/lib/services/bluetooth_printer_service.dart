import 'dart:io';

import 'package:print_bluetooth_thermal/print_bluetooth_thermal.dart';
import 'package:shared_preferences/shared_preferences.dart';

class PrinterIliyohifadhiwa {
  final String jina;
  final String mac;

  const PrinterIliyohifadhiwa({required this.jina, required this.mac});
}

class BluetoothPrintException implements Exception {
  final String message;

  const BluetoothPrintException(this.message);

  @override
  String toString() => message;
}

class BluetoothPrinterService {
  static const _jinaKey = 'pos_printer_name';
  static const _macKey = 'pos_printer_mac';

  static bool get inaBluetooth => Platform.isAndroid || Platform.isIOS || Platform.isMacOS;

  static Future<PrinterIliyohifadhiwa?> iliyohifadhiwa() async {
    final prefs = await SharedPreferences.getInstance();
    final mac = (prefs.getString(_macKey) ?? '').trim();
    if (mac.isEmpty) return null;
    return PrinterIliyohifadhiwa(jina: (prefs.getString(_jinaKey) ?? mac).trim(), mac: mac);
  }

  static Future<void> hifadhi(String jina, String mac) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_jinaKey, jina.trim());
    await prefs.setString(_macKey, mac.trim());
  }

  static Future<void> futa() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_jinaKey);
    await prefs.remove(_macKey);
    if (inaBluetooth) {
      await PrintBluetoothThermal.disconnect;
    }
  }

  static Future<bool> ombaRuhusa() async {
    if (!inaBluetooth) return false;
    return PrintBluetoothThermal.isPermissionBluetoothGranted;
  }

  static Future<bool> bluetoothImewashwa() async {
    if (!inaBluetooth) return false;
    return PrintBluetoothThermal.bluetoothEnabled;
  }

  static Future<List<BluetoothInfo>> vifaaVilivyopair() async {
    final ruhusa = await ombaRuhusa();
    if (!ruhusa) {
      throw const BluetoothPrintException('Ruhusu Bluetooth ili kuona printer.');
    }
    final imewashwa = await bluetoothImewashwa();
    if (!imewashwa) {
      throw const BluetoothPrintException('Washa Bluetooth kwenye simu, kisha tafuta tena.');
    }
    final vifaa = await PrintBluetoothThermal.pairedBluetooths;
    vifaa.sort((a, b) {
      final aPrinter = _inaonekanaPrinter(a.name);
      final bPrinter = _inaonekanaPrinter(b.name);
      if (aPrinter == bPrinter) return a.name.compareTo(b.name);
      return aPrinter ? -1 : 1;
    });
    return vifaa;
  }

  static Future<void> unganishaNaHifadhi(BluetoothInfo kifaa) async {
    final mac = kifaa.macAdress.trim();
    if (mac.isEmpty) {
      throw const BluetoothPrintException('Printer hii haina anwani ya Bluetooth.');
    }
    final ok = await PrintBluetoothThermal.connect(macPrinterAddress: mac);
    if (!ok) {
      throw BluetoothPrintException('Imeshindwa kuunganisha ${kifaa.name}. Washa printer na uhakikishe ime-pair.');
    }
    await hifadhi(kifaa.name, mac);
  }

  static Future<void> chapisha(List<int> bytes) async {
    if (!inaBluetooth) {
      throw const BluetoothPrintException('Chapisha moja kwa moja inafanya kazi kwenye simu.');
    }
    final printer = await iliyohifadhiwa();
    if (printer == null) {
      throw const BluetoothPrintException('Chagua printer ya Bluetooth kwanza.');
    }
    final ruhusa = await ombaRuhusa();
    if (!ruhusa) {
      throw const BluetoothPrintException('Ruhusu Bluetooth ili kuchapisha.');
    }
    final imewashwa = await bluetoothImewashwa();
    if (!imewashwa) {
      throw const BluetoothPrintException('Washa Bluetooth kwenye simu.');
    }
    final imeunganishwa = await PrintBluetoothThermal.connect(macPrinterAddress: printer.mac);
    if (!imeunganishwa) {
      throw BluetoothPrintException('Imeshindwa kuunganisha ${printer.jina}. Washa printer KP58 na uhakikishe iko karibu.');
    }
    await Future<void>.delayed(const Duration(milliseconds: 250));
    final ok = await PrintBluetoothThermal.writeBytes(bytes);
    if (!ok) {
      throw const BluetoothPrintException('Printer haijachapisha. Washa karatasi na ujaribu tena.');
    }
  }

  static bool _inaonekanaPrinter(String jina) {
    final jinaDog = jina.toLowerCase();
    return jinaDog.contains('kp58') || jinaDog.contains('pos') || jinaDog.contains('print') || jinaDog.contains('58');
  }
}
