<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ESC/POS connector (server-side only — artisan escpos:test)
    |--------------------------------------------------------------------------
    |
    | Users who access the app via a remote server cannot print to a USB printer on
    | their PC through this config. Use browser POS Print on invoices / POS sales
    | instead, or a network printer reachable from the server.
    |
    | windows      — Printer installed on the same machine that runs PHP.
    | network      — Raw TCP to a network receipt printer (host + port).
    | linux_device — Direct Linux device file (e.g. /dev/usb/lp1).
    | auto         — windows on Windows PHP host, else network if host set, else linux_device.
    |
    */
    'connector' => env('ESCPOS_CONNECTOR', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Windows printer
    |--------------------------------------------------------------------------
    |
    | Exact name from Windows Settings → Bluetooth & devices → Printers & scanners
    | (e.g. "Romeson" or "POS-80"). Share the USB printer locally if needed.
    |
    */
    'windows_printer' => env('ESCPOS_WINDOWS_PRINTER', ''),

    /*
    |--------------------------------------------------------------------------
    | Network printer (optional)
    |--------------------------------------------------------------------------
    */
    'network_host' => env('ESCPOS_NETWORK_HOST', ''),
    'network_port' => (int) env('ESCPOS_NETWORK_PORT', 9100),

    /*
    |--------------------------------------------------------------------------
    | Linux device path (optional, Linux servers only)
    |--------------------------------------------------------------------------
    */
    'linux_device' => env('ESCPOS_LINUX_DEVICE', ''),
];
