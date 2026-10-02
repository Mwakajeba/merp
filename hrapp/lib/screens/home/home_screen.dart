import 'package:flutter/material.dart';

import '../../services/auth_service.dart';
import '../auth/login_screen.dart';
import '../milipuko/kata_kibali_screen.dart';
import '../milipuko/picha_screen.dart';
import '../milipuko/scan_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  Map<String, dynamic>? _user;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final user = await AuthService.getCurrentUser();
    if (!mounted) return;
    setState(() => _user = user);
  }

  bool get _verifierOnly => _user?['verifier_only'] == true;

  Future<void> _logout() async {
    await AuthService.logout();
    if (!mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginScreen()),
      (_) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    final name = (_user?['name'] ?? 'Mtumiaji').toString();

    return Scaffold(
      backgroundColor: const Color(0xFFF4F7FB),
      appBar: AppBar(
        title: const Text('Milipuko'),
        backgroundColor: const Color(0xFF1A4F8B),
        foregroundColor: Colors.white,
        actions: [
          IconButton(onPressed: _logout, icon: const Icon(Icons.logout), tooltip: 'Toka'),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(name, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800)),
          const SizedBox(height: 4),
          Text(
            _verifierOnly
                ? 'Verifier: thibitisha vibali na vitambulisho tu.'
                : 'Kata kibali, sasisha picha, au scan ukaguzi.',
            style: const TextStyle(color: Colors.black54),
          ),
          const SizedBox(height: 18),
          if (!_verifierOnly) ...[
            _Kadi(
              icon: Icons.assignment_turned_in,
              title: 'Kata kibali cha kulipua',
              subtitle: 'Sajili kibali kipya cha mlipuko.',
              color: const Color(0xFF1A4F8B),
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const KataKibaliScreen())),
            ),
            _Kadi(
              icon: Icons.photo_camera,
              title: 'Picha ya mlipuaji',
              subtitle: 'Piga picha na usasishe taarifa zake.',
              color: const Color(0xFFB86E00),
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PichaScreen())),
            ),
          ],
          _Kadi(
            icon: Icons.qr_code_scanner,
            title: 'Scan kibali',
            subtitle: 'Jua kama kibali ni halali na kama kimetumika.',
            color: const Color(0xFF0E7C66),
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ScanScreen(aina: 'kibali')),
            ),
          ),
          _Kadi(
            icon: Icons.badge,
            title: 'Scan kitambulisho cha blasta',
            subtitle: 'Jua kama kitambulisho ni halali na kama amefungwa.',
            color: const Color(0xFF8E2F4F),
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ScanScreen(aina: 'mlipuzi')),
            ),
          ),
        ],
      ),
    );
  }
}

class _Kadi extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final Color color;
  final VoidCallback onTap;

  const _Kadi({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        leading: CircleAvatar(backgroundColor: color, child: Icon(icon, color: Colors.white)),
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.w700)),
        subtitle: Text(subtitle),
        trailing: const Icon(Icons.chevron_right),
        onTap: onTap,
      ),
    );
  }
}
