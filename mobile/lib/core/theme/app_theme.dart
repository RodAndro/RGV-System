import 'package:flutter/material.dart';

/// RGV Multi-Tech Services brand palette.
class RgvColors {
  const RgvColors._();

  static const Color blue = Color(0xFF2563EB);
  static const Color blueDark = Color(0xFF1D4ED8);
  static const Color blueLight = Color(0xFFDBEAFE);
  static const Color surface = Color(0xFFF8FAFC);
  static const Color amber = Color(0xFFF59E0B);
  static const Color slate = Color(0xFF334155);
  static const Color slateLight = Color(0xFF64748B);
  static const Color danger = Color(0xFFDC2626);
  static const Color success = Color(0xFF16A34A);
}

/// Builds the Material 3 theme used across the app.
ThemeData buildAppTheme() {
  final scheme = ColorScheme.fromSeed(
    seedColor: RgvColors.blue,
    primary: RgvColors.blue,
    secondary: RgvColors.blueDark,
    error: RgvColors.danger,
  );

  final base = ThemeData(
    useMaterial3: true,
    colorScheme: scheme,
    scaffoldBackgroundColor: RgvColors.surface,
    fontFamily: 'Figtree',
  );

  return base.copyWith(
    appBarTheme: const AppBarTheme(
      backgroundColor: RgvColors.blue,
      foregroundColor: Colors.white,
      elevation: 0,
      centerTitle: false,
      titleTextStyle: TextStyle(
        color: Colors.white,
        fontSize: 18,
        fontWeight: FontWeight.w600,
      ),
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: RgvColors.blue,
        foregroundColor: Colors.white,
        minimumSize: const Size.fromHeight(52),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: RgvColors.blue,
        minimumSize: const Size.fromHeight(52),
        side: const BorderSide(color: RgvColors.blue),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
      ),
    ),
    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(foregroundColor: RgvColors.blue),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: Colors.white,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: RgvColors.blue, width: 2),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: RgvColors.danger),
      ),
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
    ),
    cardTheme: CardThemeData(
      color: Colors.white,
      elevation: 1,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
    ),
    chipTheme: base.chipTheme.copyWith(
      backgroundColor: RgvColors.surface,
      side: BorderSide.none,
    ),
    snackBarTheme: const SnackBarThemeData(
      behavior: SnackBarBehavior.floating,
    ),
  );
}
