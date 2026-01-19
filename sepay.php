<?php

// ================= CONFIG =================
$DISCORD_WEBHOOK = "https://discord.com/api/webhooks/1415706218789601321/u743PNM3APkkvx91aQW9JPh8Bk_vXX2b6w9OPcmje52y4ImC2uzL_dRy4ssG6f7QXZts";

// ================= POST ONLY =================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(404);
    exit;
}

// ================= READ JSON =================
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!$data) {
    http_response_code(400);
    exit;
}

// ================= BASIC DATA =================
$gateway      = $data['gateway'] ?? 'Unknown';
$time         = $data['transactionDate'] ?? date("Y-m-d H:i:s");
$amount       = $data['transferAmount'] ?? 0;
$type         = $data['transferType'] ?? 'unknown';
$ref          = $data['referenceCode'] ?? '—';
$accumulated  = $data['accumulated'] ?? 0;
$content      = $data['content'] ?? '';
$account      = $data['subAccount'] ?? '—';

// ================= PARSE CONTENT =================
preg_match('/\.CT tu (\d+)\s+(.+?)\s+toi/', $content, $m_from);
preg_match('/toi (\d+)\s+(.+?)\s+tai/', $content, $m_to);


$accountNumber    = $data['accountNumber'] ?? '—';

// ================= EMBED =================
$payload = [
    "username" => "API BANKING",
    "embeds" => [[
        "title" => "💸 Giao dịch vào",
        "color" => 0x2ecc71,
        "fields" => [
            [
                "name" => "Số tiền",
                "value" => "``" . number_format($amount) . " VND``",
                "inline" => true
            ],
            [
                "name" => "Ngân hàng",
                "value" => "``$gateway``",
                "inline" => true
            ],
            [
                "name" => "Từ Số TK",
                "value" => "`$accountNumber`",
                "inline" => true
            ],
            [
                "name" => "Mã GD",
                "value" => "`$ref`",
                "inline" => true
            ],
            [
                "name" => "Tài khoản nhận",
                "value" => "`$account`",
                "inline" => true
            ],
            [
                "name" => "Nội Dung",
                "value" => "`$content`",
                "inline" => true
            ]
        ],
        "footer" => [
            "text" => $time
        ]
    ]]
];

// ================= SEND TO DISCORD =================
$ch = curl_init($DISCORD_WEBHOOK);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true
]);
curl_exec($ch);
curl_close($ch);

// ================= RESPONSE =================
echo json_encode([
    "status" => "ok",
    "sepay_id" => $data['id'] ?? null
]);
