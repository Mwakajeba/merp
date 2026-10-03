import 'package:flutter/material.dart';

import '../../services/auth_service.dart';
import '../auth/login_screen.dart';
import '../milipuko/kata_kibali_screen.dart';
import '../milipuko/kata_mawe_screen.dart';
import '../milipuko/picha_screen.dart';
import '../milipuko/scan_screen.dart';
import '../milipuko/walipuaji_screen.dart';

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
    final tawi = (_user?['branch_name'] ?? '').toString();

    final vitendo = <_Kitendo>[
      if (!_verifierOnly)
        _Kitendo(
          icon: Icons.local_fire_department_rounded,
          title: 'Kata kibali',
          subtitle: 'Mlipuko',
          color: const Color(0xFF1A4F8B),
          onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const KataKibaliScreen())),
        ),
      if (!_verifierOnly)
        _Kitendo(
          icon: Icons.inventory_2_rounded,
          title: 'Kata kibali',
          subtitle: 'Mawe au chorongeo',
          color: const Color(0xFFB42318),
          onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const KataMaweScreen())),
        ),
      if (!_verifierOnly)
        _Kitendo(
          icon: Icons.badge_outlined,
          title: 'Walipuaji',
          subtitle: 'Sajili na kitambulisho',
          color: const Color(0xFF0B6E4F),
          onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const WalipuajiScreen())),
        ),
      if (!_verifierOnly)
        _Kitendo(
          icon: Icons.add_a_photo_rounded,
          title: 'Picha',
          subtitle: 'Mlipuaji',
          color: const Color(0xFFB86E00),
          onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PichaScreen())),
        ),
      _Kitendo(
        icon: Icons.qr_code_scanner_rounded,
        title: 'Thibitisha',
        subtitle: 'Kibali cha mlipuko',
        color: const Color(0xFF0E7C66),
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ScanScreen(aina: 'kibali'))),
      ),
      _Kitendo(
        icon: Icons.qr_code_2_rounded,
        title: 'Thibitisha',
        subtitle: 'Kibali cha mawe',
        color: const Color(0xFF7A3E12),
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ScanScreen(aina: 'mawe'))),
      ),
      _Kitendo(
        icon: Icons.badge_rounded,
        title: 'Thibitisha',
        subtitle: 'Kitambulisho',
        color: const Color(0xFF8E2F4F),
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ScanScreen(aina: 'mlipuzi'))),
      ),
    ];

    return Scaffold(
      backgroundColor: const Color(0xFFF3F6FB),
      body: ListView(
        padding: EdgeInsets.zero,
        children: [
          Container(
            padding: EdgeInsets.fromLTRB(20, MediaQuery.of(context).padding.top + 16, 12, 28),
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [Color(0xFF123A68), Color(0xFF1A4F8B)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.vertical(bottom: Radius.circular(28)),
            ),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Milipuko', style: TextStyle(color: Colors.white70, fontSize: 13, letterSpacing: 0.4)),
                      const SizedBox(height: 4),
                      Text(name, style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.w800)),
                      const SizedBox(height: 4),
                      Text(
                        _verifierOnly ? 'Ukaguzi wa vibali na vitambulisho' : (tawi.isEmpty ? 'Kata kibali au thibitisha' : tawi),
                        style: const TextStyle(color: Colors.white70),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  onPressed: _logout,
                  icon: const Icon(Icons.logout_rounded, color: Colors.white),
                  tooltip: 'Toka',
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 18, 16, 24),
            child: GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              childAspectRatio: 1.15,
              children: vitendo.map((kitendo) => _Kadi(kitendo: kitendo)).toList(),
            ),
          ),
        ],
      ),
    );
  }
}

class _Kitendo {
  final IconData icon;
  final String title;
  final String subtitle;
  final Color color;
  final VoidCallback onTap;

  const _Kitendo({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.color,
    required this.onTap,
  });
}

class _Kadi extends StatelessWidget {
  final _Kitendo kitendo;

  const _Kadi({required this.kitendo});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(20),
      elevation: 0,
      child: InkWell(
        borderRadius: BorderRadius.circular(20),
        onTap: kitendo.onTap,
        child: Ink(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: const Color(0xFFE4EAF2)),
          ),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: kitendo.color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(kitendo.icon, color: kitendo.color),
              ),
              const Spacer(),
              Text(kitendo.title, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
              const SizedBox(height: 2),
              Text(kitendo.subtitle, style: const TextStyle(color: Color(0xFF5C6B80), fontSize: 13)),
            ],
          ),
        ),
      ),
    );
  }
}
