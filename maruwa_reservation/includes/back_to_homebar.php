<?php
// includes/back_to_home.php
if (!function_exists('render_back_to_homebar')) {
    function render_back_to_homebar(string $to='index.php', string $label='← 戻る') {
        static $styled = false;
        if (!$styled) {
            echo '<style>
                .topbar{display:flex;justify-content:center;align-items:center;height:56px;border-bottom:1px solid #eee;margin-bottom:12px}
                .btn-back{display:inline-block;padding:12px 22px;font-size:16px;line-height:1;border:1px solid #ccc;border-radius:12px;text-decoration:none;color:#333}
                .btn-back:hover{background:#f5f5f5}
            </style>';
            $styled = true;
        }
        $href = htmlspecialchars($to, ENT_QUOTES, 'UTF-8');
        $text = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        echo '<div class="topbar"><a href="'.$href.'" class="btn-back">'.$text.'</a></div>';
    }
}
