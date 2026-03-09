<?php
session_start();
include "includes/lang.php";
include "includes/db.php";

$total = 0;
$items = [];

foreach ($_SESSION['cart'] as $id) {
    $id = (int) $id;
    $r = $conn->query("SELECT p.name, p.price FROM products p WHERE p.product_id=$id");
    $row = $r->fetch_assoc();
    $items[] = $row;
    $total += $row['price'];
}

// Donation
$donation = isset($_POST['donation_amount']) ? floatval($_POST['donation_amount']) : 0;
if (!in_array($donation, [0, 0.50, 1.00])) {
    $donation = 0;
}
$total += $donation;

$date = date("Y-m-d");
$count = $conn->query("SELECT COUNT(*) as c FROM orders WHERE DATE(datetime)='$date'");
$row = $count->fetch_assoc();
$pickup = ($row['c'] % 99) + 1;

$conn->query("INSERT INTO orders (order_status_id,pickup_number,price_total)
VALUES (2,$pickup,$total)");

$order_id = $conn->insert_id;

foreach ($_SESSION['cart'] as $id) {
    $id = (int) $id;
    $r = $conn->query("SELECT price FROM products WHERE product_id=$id");
    $row = $r->fetch_assoc();
    $conn->query("INSERT INTO order_product (order_id,product_id,price)
    VALUES ($order_id,$id,{$row['price']})");
}

$_SESSION['cart'] = [];

// Prepare data for JavaScript receipt printing
$receiptData = json_encode([
    'items' => $items,
    'donation' => $donation,
    'total' => $total,
    'pickup' => $pickup,
    'order_id' => $order_id
]);
?>

<!DOCTYPE html>
<html>

<head>
    <title><?= t('order_confirmed') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Auto redirect na 10 seconden (extra tijd voor printen) -->
    <meta http-equiv="refresh" content="10;url=<?= lang_url('index.php') ?>">
</head>

<body class="confirm-screen">

    <?php include "includes/language_switch.php"; ?>

    <div class="confirm-container">

        <h1><?= t('order_confirmed') ?></h1>
        <h2><?= t('your_number') ?></h2>

        <div class="pickup-number">
            #<?= str_pad($pickup, 3, "0", STR_PAD_LEFT) ?>
        </div>

        <!-- Order summary -->
        <div class="receipt-summary">
            <h3><?= t('order_summary') ?></h3>
            <ul class="receipt-items">
                <?php foreach ($items as $item): ?>
                    <li>
                        <span class="item-name"><?= htmlspecialchars($item['name']) ?></span>
                        <span class="item-price">€<?= number_format($item['price'], 2) ?></span>
                    </li>
                <?php endforeach; ?>
                <?php if ($donation > 0): ?>
                    <li class="donation-line">
                        <span class="item-name"><?= t('donation_title') ?></span>
                        <span class="item-price">€<?= number_format($donation, 2) ?></span>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="receipt-total-line">
                <span><?= t('total') ?></span>
                <span>€<?= number_format($total, 2) ?></span>
            </div>
        </div>

        <!-- Print status -->
        <div class="print-status" id="print-status">
            <span class="print-spinner"></span>
            <?= t('receipt_printing') ?>
        </div>

        <button class="print-btn" id="print-btn" onclick="printReceipt()" style="display:none;">
            🖨️ <?= t('receipt_print_btn') ?>
        </button>

        <p class="redirect-text">
            <?= t('redirecting') ?>
        </p>

        <a href="<?= lang_url('index.php') ?>" class="start-button">
            <?= t('new_order') ?>
        </a>

    </div>

    <script>
        const receiptData = <?= $receiptData ?>;
        let selectedDevice = null;

        // Printer vendor IDs (common thermal printer manufacturers)
        const PRINTER_VENDORS = [
            0x0483, // STM Microelectronics (Xprinter)
            0x04b8, // Seiko Epson
            0x0456, // Microtek
            0x067b, // Prolific Technology
        ];

        // Pad string to fixed width for receipt formatting
        function padLine(left, right, width = 42) {
            const spaces = width - left.length - right.length;
            return left + ' '.repeat(Math.max(1, spaces)) + right;
        }

        // Build ESC/POS receipt content
        function buildReceipt() {
            const init = "\x1B\x40";           // Initialize printer
            const center = "\x1B\x61\x01";     // Center align
            const left = "\x1B\x61\x00";       // Left align
            const bold_on = "\x1B\x45\x01";    // Bold on
            const bold_off = "\x1B\x45\x00";   // Bold off
            const big_on = "\x1D\x21\x11";     // Double height+width
            const big_off = "\x1D\x21\x00";    // Normal size
            const cut = "\x1D\x56\x00";        // Full cut

            const sep = "──────────────────────────────────────────\n";
            const pickup = String(receiptData.pickup).padStart(3, '0');

            let receipt = init;

            // Header
            receipt += center;
            receipt += bold_on;
            receipt += "HAPPY HERBIVORE\n";
            receipt += bold_off;
            receipt += "100% Plant-Based • Fresh • Delicious\n";
            receipt += sep;

            // Order number
            receipt += big_on;
            receipt += "#" + pickup + "\n";
            receipt += big_off;
            receipt += sep;

            // Items
            receipt += left;
            receiptData.items.forEach(item => {
                const price = "EUR " + parseFloat(item.price).toFixed(2);
                receipt += padLine("1x " + item.name, price) + "\n";
            });

            // Donation
            if (receiptData.donation > 0) {
                const donPrice = "EUR " + parseFloat(receiptData.donation).toFixed(2);
                receipt += padLine("   Donatie", donPrice) + "\n";
            }

            receipt += sep;

            // Total
            receipt += bold_on;
            receipt += padLine("TOTAAL:", "EUR " + parseFloat(receiptData.total).toFixed(2)) + "\n";
            receipt += bold_off;

            receipt += sep;

            // Footer
            receipt += center;
            receipt += "\n";
            receipt += "Bestelnummer: #" + pickup + "\n";
            receipt += "\n";
            receipt += "Bedankt voor uw bezoek!\n";
            receipt += "Healthy in a Hurry\n";
            receipt += "\n\n\n";

            // Cut paper
            receipt += cut;

            return receipt;
        }

        // Try to auto-detect an already authorized USB printer
        async function autoDetectPrinter() {
            try {
                if (!navigator.usb) return false;

                const devices = await navigator.usb.getDevices();
                const printer = devices.find(device =>
                    PRINTER_VENDORS.includes(device.vendorId)
                );

                if (printer) {
                    selectedDevice = printer;
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Auto-detect error:', error);
                return false;
            }
        }

        // Select USB printer manually
        async function selectPrinter() {
            try {
                if (!navigator.usb) return false;

                const filters = PRINTER_VENDORS.map(vendorId => ({ vendorId }));
                selectedDevice = await navigator.usb.requestDevice({ filters });
                return true;
            } catch (error) {
                console.error('Select printer error:', error);
                return false;
            }
        }

        // Print receipt via WebUSB
        async function printReceipt() {
            const statusEl = document.getElementById('print-status');
            const btnEl = document.getElementById('print-btn');

            try {
                if (!navigator.usb) {
                    throw new Error('WebUSB not supported');
                }

                // Try auto-detect first, then ask user to select
                if (!selectedDevice) {
                    const found = await autoDetectPrinter();
                    if (!found) {
                        await selectPrinter();
                    }
                }

                if (!selectedDevice) {
                    throw new Error('No printer selected');
                }

                statusEl.innerHTML = '<span class="print-spinner"></span> <?= t("receipt_printing") ?>';
                statusEl.className = 'print-status';
                statusEl.style.display = 'block';
                btnEl.style.display = 'none';

                await selectedDevice.open();

                if (selectedDevice.configuration === null) {
                    await selectedDevice.selectConfiguration(1);
                }

                try {
                    await selectedDevice.claimInterface(0);
                } catch (e) {
                    console.log('Interface already claimed, continuing...');
                }

                const encoder = new TextEncoder();
                const receipt = buildReceipt();

                // Find the output endpoint
                const intf = selectedDevice.configuration.interfaces[0].alternates[0];
                const endpoint = intf.endpoints.find(e => e.direction === 'out');

                if (!endpoint) {
                    throw new Error('Output endpoint not found');
                }

                await selectedDevice.transferOut(endpoint.endpointNumber, encoder.encode(receipt));

                statusEl.innerHTML = '✅ <?= t("receipt_success") ?>';
                statusEl.className = 'print-status print-success';

                setTimeout(() => {
                    selectedDevice.close();
                }, 1000);

            } catch (error) {
                console.error('Print error:', error);
                statusEl.innerHTML = '⚠️ <?= t("receipt_failed") ?>';
                statusEl.className = 'print-status print-error';
                btnEl.style.display = 'inline-block';
            }
        }

        // Auto-print on page load
        window.addEventListener('load', () => {
            if (navigator.usb) {
                printReceipt();
            } else {
                // No WebUSB support — show manual print button
                const statusEl = document.getElementById('print-status');
                statusEl.style.display = 'none';
                document.getElementById('print-btn').style.display = 'inline-block';
            }
        });
    </script>

</body>

</html>