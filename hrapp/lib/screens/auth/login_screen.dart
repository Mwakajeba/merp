import 'package:flutter/material.dart';
import '../../services/auth_service.dart';
import '../home/home_screen.dart';
import 'forgot_password_screen.dart';
import 'select_branch_location_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> with SingleTickerProviderStateMixin {
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  bool _obscurePassword = true;
  bool _isLoading = false;
  bool _pinMode = true;
  String _pin = '';
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    );
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeInOut),
    );
    _slideAnimation = Tween<Offset>(
      begin: const Offset(0, 0.3),
      end: Offset.zero,
    ).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeOutCubic),
    );
    _animationController.forward();
  }

  @override
  void dispose() {
    _phoneController.dispose();
    _passwordController.dispose();
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isLoading = true;
    });

    final result = await AuthService.login(
      _phoneController.text.trim(),
      _passwordController.text,
    );

    setState(() {
      _isLoading = false;
    });

    await _afterLogin(result);
  }

  Future<void> _submitPin() async {
    if (_pin.length != 4 || _isLoading) return;
    setState(() => _isLoading = true);
    final result = await AuthService.loginWithPin(_pin);
    if (!mounted) return;
    setState(() {
      _isLoading = false;
      if (result['success'] != true) _pin = '';
    });
    await _afterLogin(result, pin: true);
  }

  void _addDigit(String digit) {
    if (_isLoading || _pin.length >= 4) return;
    setState(() => _pin += digit);
    if (_pin.length == 4) _submitPin();
  }

  Future<void> _afterLogin(Map<String, dynamic> result, {bool pin = false}) async {
    if (!mounted) return;

    if (result['success'] == true) {
      final user = result['user'] as Map<String, dynamic>? ?? {};
      final branchId = user['branch_id'];
      final locationId = user['location_id'];
      final branchInt = branchId is int ? branchId : int.tryParse(branchId?.toString() ?? '');
      final locInt = locationId is int ? locationId : int.tryParse(locationId?.toString() ?? '');

      if (pin && branchInt != null) {
        await AuthService.setSelectedContext(branchId: branchInt, locationId: locInt);
        if (!mounted) return;
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => const HomeScreen()),
        );
        return;
      }

      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (context) => SelectBranchLocationScreen(user: user)),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.error_outline, color: Colors.white),
              const SizedBox(width: 12),
              Expanded(child: Text(result['message'] ?? 'Login failed')),
            ],
          ),
          backgroundColor: const Color(0xFFE53935),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          margin: const EdgeInsets.all(16),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              const Color(0xFF1E3A8A), // Deep blue
              const Color(0xFF3B82F6), // Bright blue
              const Color(0xFF60A5FA), // Light blue
            ],
            stops: const [0.0, 0.5, 1.0],
          ),
        ),
        child: SafeArea(
          child: SingleChildScrollView(
            child: FadeTransition(
              opacity: _fadeAnimation,
              child: SlideTransition(
                position: _slideAnimation,
                child: Column(
                  children: [
                    // Header Section with Logo
                    Container(
                      padding: const EdgeInsets.fromLTRB(24, 32, 24, 0),
                      child: Column(
                        children: [
                          // Logo Container
                          Container(
                            width: 80,
                            height: 80,
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(20),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.15),
                                  blurRadius: 20,
                                  offset: const Offset(0, 8),
                                ),
                              ],
                            ),
                            child: Stack(
                              alignment: Alignment.center,
                              children: [
                                Container(
                                  decoration: BoxDecoration(
                                    gradient: LinearGradient(
                                      begin: Alignment.topLeft,
                                      end: Alignment.bottomRight,
                                      colors: [
                                        const Color(0xFF3B82F6).withValues(alpha: 0.1),
                                        const Color(0xFF1E3A8A).withValues(alpha: 0.1),
                                      ],
                                    ),
                                    borderRadius: BorderRadius.circular(18),
                                  ),
                                ),
                                const Icon(
                                  Icons.verified_user_rounded,
                                  color: Color(0xFF3B82F6),
                                  size: 40,
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 24),
                          const Text(
                            'Milipuko',
                            style: TextStyle(
                              fontSize: 32,
                              fontWeight: FontWeight.w900,
                              color: Colors.white,
                              letterSpacing: -0.5,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Welcome back!',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w500,
                              color: Colors.white.withValues(alpha: 0.9),
                              letterSpacing: 0.2,
                            ),
                          ),
                          const SizedBox(height: 40),
                          // Modern Illustration
                          Container(
                            height: 140,
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(24),
                              border: Border.all(
                                color: Colors.white.withValues(alpha: 0.2),
                                width: 1,
                              ),
                            ),
                            child: Stack(
                              children: [
                                // Decorative circles
                                Positioned(
                                  top: 16,
                                  left: 20,
                                  child: Container(
                                    width: 40,
                                    height: 40,
                                    decoration: BoxDecoration(
                                      color: Colors.white.withValues(alpha: 0.2),
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(
                                      Icons.business_center_rounded,
                                      color: Colors.white,
                                      size: 20,
                                    ),
                                  ),
                                ),
                                Positioned(
                                  top: 20,
                                  right: 24,
                                  child: Container(
                                    width: 36,
                                    height: 36,
                                    decoration: BoxDecoration(
                                      color: Colors.white.withValues(alpha: 0.2),
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(
                                      Icons.people_rounded,
                                      color: Colors.white,
                                      size: 18,
                                    ),
                                  ),
                                ),
                                // Center illustration
                                Positioned(
                                  bottom: 16,
                                  left: 0,
                                  right: 0,
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      // Laptop
                                      Container(
                                        width: 50,
                                        height: 32,
                                        decoration: BoxDecoration(
                                          color: Colors.white.withValues(alpha: 0.25),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: const Icon(
                                          Icons.laptop_mac_rounded,
                                          color: Colors.white,
                                          size: 24,
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      // Person
                                      Container(
                                        width: 56,
                                        height: 70,
                                        decoration: BoxDecoration(
                                          color: Colors.white.withValues(alpha: 0.2),
                                          borderRadius: BorderRadius.circular(28),
                                        ),
                                        child: Stack(
                                          children: [
                                            // Head
                                            Positioned(
                                              top: 4,
                                              left: 16,
                                              child: Container(
                                                width: 24,
                                                height: 24,
                                                decoration: BoxDecoration(
                                                  color: Colors.white.withValues(alpha: 0.4),
                                                  shape: BoxShape.circle,
                                                ),
                                              ),
                                            ),
                                            // Body
                                            Positioned(
                                              top: 24,
                                              left: 10,
                                              child: Container(
                                                width: 36,
                                                height: 40,
                                                decoration: BoxDecoration(
                                                  color: Colors.white.withValues(alpha: 0.3),
                                                  borderRadius: BorderRadius.circular(18),
                                                ),
                                              ),
                                            ),
                                            // Phone
                                            Positioned(
                                              top: 30,
                                              right: -2,
                                              child: Container(
                                                width: 16,
                                                height: 24,
                                                decoration: BoxDecoration(
                                                  color: Colors.white,
                                                  borderRadius: BorderRadius.circular(4),
                                                ),
                                                child: const Icon(
                                                  Icons.phone_android_rounded,
                                                  color: Color(0xFF3B82F6),
                                                  size: 14,
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      // Chart/Graph
                                      Container(
                                        width: 44,
                                        height: 44,
                                        decoration: BoxDecoration(
                                          color: Colors.white.withValues(alpha: 0.25),
                                          borderRadius: BorderRadius.circular(12),
                                        ),
                                        child: const Icon(
                                          Icons.bar_chart_rounded,
                                          color: Colors.white,
                                          size: 24,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),

                    // White Form Section
                    Container(
                      margin: const EdgeInsets.only(top: 32),
                      decoration: const BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.only(
                          topLeft: Radius.circular(40),
                          topRight: Radius.circular(40),
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: Color(0x1A000000),
                            blurRadius: 30,
                            offset: Offset(0, -10),
                          ),
                        ],
                      ),
                      child: Padding(
                        padding: const EdgeInsets.all(32.0),
                        child: Form(
                          key: _formKey,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _pinMode ? 'Ingia kwa PIN' : 'Ingia kwa simu',
                                style: const TextStyle(
                                  fontSize: 28,
                                  fontWeight: FontWeight.w800,
                                  color: Color(0xFF1F2937),
                                  letterSpacing: -0.8,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                _pinMode
                                    ? 'Weka PIN yako ya tarakimu 4 ili uingie haraka.'
                                    : 'Weka namba ya simu na nenosiri.',
                                style: TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.w500,
                                  color: Colors.grey.shade600,
                                ),
                              ),
                              const SizedBox(height: 32),

                              if (_pinMode) ...[
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: List.generate(4, (index) {
                                    final filled = index < _pin.length;
                                    return Container(
                                      width: 18,
                                      height: 18,
                                      margin: const EdgeInsets.symmetric(horizontal: 10),
                                      decoration: BoxDecoration(
                                        shape: BoxShape.circle,
                                        color: filled ? const Color(0xFF1A4F8B) : Colors.transparent,
                                        border: Border.all(color: const Color(0xFF1A4F8B), width: 2),
                                      ),
                                    );
                                  }),
                                ),
                                const SizedBox(height: 28),
                                _PinKeypad(
                                  enabled: !_isLoading,
                                  onDigit: _addDigit,
                                  onClear: () => setState(() => _pin = ''),
                                  onBackspace: () => setState(() {
                                    if (_pin.isNotEmpty) _pin = _pin.substring(0, _pin.length - 1);
                                  }),
                                ),
                                const SizedBox(height: 16),
                                Center(
                                  child: TextButton(
                                    onPressed: _isLoading ? null : () => setState(() => _pinMode = false),
                                    child: const Text('Ingia kwa simu na nenosiri'),
                                  ),
                                ),
                              ] else ...[
                              // Phone Number Field
                              _ModernInputField(
                                controller: _phoneController,
                                label: 'Phone Number',
                                hint: '+255 700 123 456',
                                icon: Icons.phone_android_rounded,
                                keyboardType: TextInputType.phone,
                                validator: (value) {
                                  if (value == null || value.isEmpty) {
                                    return 'Please enter your phone number';
                                  }
                                  return null;
                                },
                              ),
                              const SizedBox(height: 24),

                              // Password Field
                              _ModernInputField(
                                controller: _passwordController,
                                label: 'Password',
                                hint: 'Enter your password',
                                icon: Icons.lock_outline_rounded,
                                obscureText: _obscurePassword,
                                onToggleVisibility: () {
                                  setState(() {
                                    _obscurePassword = !_obscurePassword;
                                  });
                                },
                                validator: (value) {
                                  if (value == null || value.isEmpty) {
                                    return 'Please enter your password';
                                  }
                                  return null;
                                },
                              ),
                              const SizedBox(height: 16),

                              // Forgot Password
                              Align(
                                alignment: Alignment.centerRight,
                                child: TextButton(
                                  onPressed: () {
                                    Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                        builder: (context) => const ForgotPasswordScreen(),
                                      ),
                                    );
                                  },
                                  style: TextButton.styleFrom(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                  ),
                                  child: const Text(
                                    'Forgot Password?',
                                    style: TextStyle(
                                      color: Color(0xFF3B82F6),
                                      fontWeight: FontWeight.w600,
                                      fontSize: 15,
                                    ),
                                  ),
                                ),
                              ),
                              const SizedBox(height: 32),

                              // Login Button
                              SizedBox(
                                width: double.infinity,
                                height: 58,
                                child: ElevatedButton(
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: const Color(0xFF3B82F6),
                                    foregroundColor: Colors.white,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(16),
                                    ),
                                    elevation: 4,
                                    shadowColor: const Color(0xFF3B82F6).withValues(alpha: 0.4),
                                  ),
                                  onPressed: _isLoading ? null : _handleLogin,
                                  child: _isLoading
                                      ? const SizedBox(
                                          width: 26,
                                          height: 26,
                                          child: CircularProgressIndicator(
                                            strokeWidth: 3,
                                            valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                          ),
                                        )
                                      : const Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Text(
                                              'Login',
                                              style: TextStyle(
                                                fontWeight: FontWeight.w700,
                                                fontSize: 17,
                                                letterSpacing: 0.5,
                                              ),
                                            ),
                                            SizedBox(width: 8),
                                            Icon(Icons.arrow_forward_rounded, size: 20),
                                          ],
                                        ),
                                ),
                              ),
                              const SizedBox(height: 12),
                              Center(
                                child: TextButton(
                                  onPressed: _isLoading
                                      ? null
                                      : () => setState(() {
                                            _pinMode = true;
                                            _pin = '';
                                          }),
                                  child: const Text('Ingia kwa PIN'),
                                ),
                              ),
                              ],

                              const SizedBox(height: 16),

                              // Contact Admin
                              Center(
                                child: RichText(
                                  text: TextSpan(
                                    style: TextStyle(
                                      fontSize: 15,
                                      color: Colors.grey.shade600,
                                    ),
                                    children: [
                                      const TextSpan(text: "Don't have an account? "),
                                      WidgetSpan(
                                        child: GestureDetector(
                                          onTap: () {
                                            // Handle contact admin
                                          },
                                          child: const Text(
                                            'Contact Admin',
                                            style: TextStyle(
                                              color: Color(0xFF3B82F6),
                                              fontWeight: FontWeight.w700,
                                            ),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                              const SizedBox(height: 16),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _ModernInputField extends StatefulWidget {
  final TextEditingController controller;
  final String label;
  final String hint;
  final IconData icon;
  final bool obscureText;
  final VoidCallback? onToggleVisibility;
  final TextInputType? keyboardType;
  final String? Function(String?)? validator;

  const _ModernInputField({
    required this.controller,
    required this.label,
    required this.hint,
    required this.icon,
    this.obscureText = false,
    this.onToggleVisibility,
    this.keyboardType,
    this.validator,
  });

  @override
  State<_ModernInputField> createState() => _ModernInputFieldState();
}

class _ModernInputFieldState extends State<_ModernInputField> {
  bool _isFocused = false;

  @override
  Widget build(BuildContext context) {
    return Focus(
      onFocusChange: (hasFocus) {
        setState(() {
          _isFocused = hasFocus;
        });
      },
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            widget.label,
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w600,
              color: _isFocused ? const Color(0xFF3B82F6) : const Color(0xFF374151),
            ),
          ),
          const SizedBox(height: 10),
          TextFormField(
            controller: widget.controller,
            obscureText: widget.obscureText,
            keyboardType: widget.keyboardType,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w600,
              color: Color(0xFF1F2937),
            ),
            decoration: InputDecoration(
              hintText: widget.hint,
              hintStyle: TextStyle(
                color: Colors.grey.shade400,
                fontSize: 16,
                fontWeight: FontWeight.w400,
              ),
              prefixIcon: Icon(
                widget.icon,
                color: _isFocused ? const Color(0xFF3B82F6) : Colors.grey.shade400,
                size: 24,
              ),
              suffixIcon: widget.obscureText && widget.onToggleVisibility != null
                  ? IconButton(
                      icon: Icon(
                        widget.obscureText ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                        color: _isFocused ? const Color(0xFF3B82F6) : Colors.grey.shade400,
                        size: 22,
                      ),
                      onPressed: widget.onToggleVisibility,
                    )
                  : null,
              filled: true,
              fillColor: _isFocused
                  ? const Color(0xFF3B82F6).withValues(alpha: 0.05)
                  : Colors.grey.shade50,
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(16),
                borderSide: BorderSide(
                  color: _isFocused ? const Color(0xFF3B82F6) : Colors.grey.shade300,
                  width: _isFocused ? 2 : 1,
                ),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(16),
                borderSide: BorderSide(
                  color: _isFocused ? const Color(0xFF3B82F6) : Colors.grey.shade300,
                  width: _isFocused ? 2 : 1,
                ),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(16),
                borderSide: const BorderSide(
                  color: Color(0xFF3B82F6),
                  width: 2,
                ),
              ),
              errorBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(16),
                borderSide: const BorderSide(
                  color: Color(0xFFEF4444),
                  width: 1,
                ),
              ),
              focusedErrorBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(16),
                borderSide: const BorderSide(
                  color: Color(0xFFEF4444),
                  width: 2,
                ),
              ),
              contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
            ),
            validator: widget.validator,
          ),
        ],
      ),
    );
  }
}

class _PinKeypad extends StatelessWidget {
  final bool enabled;
  final ValueChanged<String> onDigit;
  final VoidCallback onClear;
  final VoidCallback onBackspace;

  const _PinKeypad({
    required this.enabled,
    required this.onDigit,
    required this.onClear,
    required this.onBackspace,
  });

  @override
  Widget build(BuildContext context) {
    final keys = ['1', '2', '3', '4', '5', '6', '7', '8', '9', 'clear', '0', 'back'];
    return GridView.count(
      crossAxisCount: 3,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 12,
      crossAxisSpacing: 12,
      childAspectRatio: 1.6,
      children: keys.map((key) {
        if (key == 'clear') {
          return _key(icon: Icons.backspace_outlined, onTap: enabled ? onClear : null);
        }
        if (key == 'back') {
          return _key(icon: Icons.arrow_back, onTap: enabled ? onBackspace : null);
        }
        return _key(label: key, onTap: enabled ? () => onDigit(key) : null);
      }).toList(),
    );
  }

  Widget _key({String? label, IconData? icon, VoidCallback? onTap}) {
    return Material(
      color: const Color(0xFFF3F6FB),
      borderRadius: BorderRadius.circular(16),
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: onTap,
        child: Center(
          child: icon != null
              ? Icon(icon, color: const Color(0xFF1A4F8B))
              : Text(label ?? '', style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w700)),
        ),
      ),
    );
  }
}
