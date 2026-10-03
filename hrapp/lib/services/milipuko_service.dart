import 'dart:convert';

import 'package:http/http.dart' as http;

import '../config/api_config.dart';
import 'auth_service.dart';

class MilipukoService {
  static Future<Map<String, String>> _headers({bool json = true}) async {
    final headers = <String, String>{'Accept': 'application/json'};
    if (json) headers['Content-Type'] = 'application/json';
    final token = await AuthService.getToken();
    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }
    final branchId = await AuthService.getSelectedBranchId();
    if (branchId != null) headers['X-Branch-Id'] = branchId.toString();
    return headers;
  }

  static String mediaUrl(String? path) {
    if (path == null || path.isEmpty) return '';
    if (path.startsWith('http')) return path;
    final origin = ApiConfig.baseUrl.replaceFirst(RegExp(r'/api$'), '');
    return origin + (path.startsWith('/') ? path : '/$path');
  }

  static Future<Map<String, dynamic>> get(String endpoint) async {
    final response = await http.get(
      Uri.parse(ApiConfig.getUrl(endpoint)),
      headers: await _headers(),
    );
    return _decode(response);
  }

  static Future<Map<String, dynamic>> post(String endpoint, Map<String, dynamic> body) async {
    final response = await http.post(
      Uri.parse(ApiConfig.getUrl(endpoint)),
      headers: await _headers(),
      body: jsonEncode(body),
    );
    return _decode(response);
  }

  static Future<Map<String, dynamic>> postMultipart(
    String endpoint,
    Map<String, String> fields, {
    String? fileField,
    String? filePath,
  }) async {
    final request = http.MultipartRequest('POST', Uri.parse(ApiConfig.getUrl(endpoint)));
    request.headers.addAll(await _headers(json: false));
    request.fields.addAll(fields);
    if (fileField != null && filePath != null && filePath.isNotEmpty) {
      request.files.add(await http.MultipartFile.fromPath(fileField, filePath));
    }
    final streamed = await request.send();
    final response = await http.Response.fromStream(streamed);
    return _decode(response);
  }

  static Future<Map<String, dynamic>> uploadPicha(int mlipuziId, String filePath) async {
    final request = http.MultipartRequest(
      'POST',
      Uri.parse(ApiConfig.getUrl('${ApiConfig.milipukoWalipuaji}/$mlipuziId/picha')),
    );
    request.headers.addAll(await _headers(json: false));
    request.files.add(await http.MultipartFile.fromPath('picha', filePath));
    final streamed = await request.send();
    final response = await http.Response.fromStream(streamed);
    return _decode(response);
  }

  static Map<String, dynamic> _decode(http.Response response) {
    Map<String, dynamic> data;
    try {
      data = jsonDecode(response.body) as Map<String, dynamic>;
    } catch (_) {
      return {
        'success': false,
        'message': 'Jibu la seva halijasomeka (${response.statusCode}).',
      };
    }
    data['status'] = response.statusCode;
    if (response.statusCode >= 400 && data['success'] != false) {
      data['success'] = false;
    }
    if (data['message'] == null && data['errors'] is Map) {
      final errors = data['errors'] as Map;
      final first = errors.values.first;
      data['message'] = first is List && first.isNotEmpty ? first.first.toString() : 'Imeshindikana.';
    }
    return data;
  }
}
