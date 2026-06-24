<?php

namespace App\Services\Printing;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\PrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class EscposPrinter
{
    /**
     * @param  callable(Printer): void  $callback
     */
    public function run(callable $callback): void
    {
        $connector = null;
        $printer = null;

        try {
            $connector = $this->createConnector();
            $printer = new Printer($connector);

            $callback($printer);
        } finally {
            if ($printer) {
                $printer->close();
            } elseif ($connector) {
                $connector->finalize();
            }
        }
    }

    public function targetLabel(): string
    {
        $connector = $this->resolvedConnectorType();

        return match ($connector) {
            'windows' => (string) config('escpos.windows_printer'),
            'network' => config('escpos.network_host') . ':' . config('escpos.network_port'),
            'linux_device' => (string) config('escpos.linux_device'),
            default => 'printer',
        };
    }

    private function createConnector(): PrintConnector
    {
        return match ($this->resolvedConnectorType()) {
            'windows' => $this->createWindowsConnector(),
            'network' => $this->createNetworkConnector(),
            'linux_device' => $this->createLinuxDeviceConnector(),
            default => throw new \RuntimeException('Unsupported ESC/POS connector configuration.'),
        };
    }

    private function resolvedConnectorType(): string
    {
        $configured = strtolower(trim((string) config('escpos.connector', 'auto')));

        if ($configured !== 'auto' && $configured !== '') {
            return $configured;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            return 'windows';
        }

        if (trim((string) config('escpos.network_host')) !== '') {
            return 'network';
        }

        if (trim((string) config('escpos.linux_device')) !== '') {
            return 'linux_device';
        }

        return 'windows';
    }

    private function createWindowsConnector(): PrintConnector
    {
        $name = trim((string) config('escpos.windows_printer'));

        if ($name === '') {
            throw new \RuntimeException(
                'POS printer is not configured. Set ESCPOS_WINDOWS_PRINTER in .env to the exact printer name from Windows Settings → Printers.'
            );
        }

        return new WindowsPrintConnector($name);
    }

    private function createNetworkConnector(): PrintConnector
    {
        $host = trim((string) config('escpos.network_host'));

        if ($host === '') {
            throw new \RuntimeException(
                'Network POS printer is not configured. Set ESCPOS_NETWORK_HOST (and optionally ESCPOS_NETWORK_PORT) in .env.'
            );
        }

        $port = (int) config('escpos.network_port', 9100);

        return new NetworkPrintConnector($host, $port);
    }

    private function createLinuxDeviceConnector(): PrintConnector
    {
        $device = trim((string) config('escpos.linux_device'));

        if ($device === '') {
            throw new \RuntimeException(
                'Linux device path is not configured. Set ESCPOS_LINUX_DEVICE (e.g. /dev/usb/lp1) in .env.'
            );
        }

        return new FilePrintConnector($device);
    }
}
