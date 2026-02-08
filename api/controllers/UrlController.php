<?php

class UrlController {

    /* ================= ANALISIS URL ================= */
    private static function analyzeUrl(string $url): array {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';
        
        return [
            'scheme' => $parsed['scheme'] ?? null,
            'host' => $host,
            'domain' => self::extractDomain($host),
            'path' => $parsed['path'] ?? '/',
            'query' => $parsed['query'] ?? null,
            'fragment' => $parsed['fragment'] ?? null,
            'port' => $parsed['port'] ?? null,
            'is_ip' => filter_var($host, FILTER_VALIDATE_IP) !== false,
            'has_www' => strpos($host, 'www.') === 0,
            'url_length' => strlen($url),
            'domain_length' => strlen($host),
            'subdomain_count' => self::countSubdomains($host),
            'has_port' => isset($parsed['port']),
            'has_at_symbol' => strpos($url, '@') !== false,
            'double_slash_position' => strpos($url, '//'),
            'hyphen_count' => substr_count($host, '-'),
            'special_char_count' => preg_match_all('/[^a-zA-Z0-9.-]/', $host) ?: 0,
        ];
    }

    /* ================= EKSTRAK DOMAIN UTAMA ================= */
    private static function extractDomain(string $host): string {
        if (empty($host)) return '';
        
        $parts = explode('.', $host);
        if (count($parts) > 1) {
            return $parts[count($parts)-2] . '.' . $parts[count($parts)-1];
        }
        return $host;
    }

    /* ================= HITUNG SUBDOMAIN ================= */
    private static function countSubdomains(string $host): int {
        if (empty($host)) return 0;
        
        $parts = explode('.', $host);
        return max(0, count($parts) - 2);
    }

    /* ================= AMBIL SEBAGIAN KONTEN WEBSITE (AMAN) ================= */
    private static function fetchPartialContent(string $url): array {
        $content = '';
        $title = '';
        $meta_description = '';
        $meta_keywords = '';
        
        try {
            // Cek apakah URL valid dan bisa diakses
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new Exception("URL tidak valid");
            }
            
            // Batasi waktu dan ukuran untuk keamanan
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_RANGE => '0-50000', // Ambil hanya 50KB pertama
                CURLOPT_FAILONERROR => true,
            ]);
            
            $html = curl_exec($ch);
            
            if (curl_errno($ch)) {
                throw new Exception("CURL Error: " . curl_error($ch));
            }
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && !empty($html)) {
                // Ekstrak judul
                if (preg_match('/<title>(.*?)<\/title>/si', (string)$html, $matches)) {
                    $title = html_entity_decode(trim($matches[1]));
                }
                
                // Ekstrak meta description
                if (preg_match('/<meta\s+name="description"\s+content="([^"]*)"/si', (string)$html, $matches)) {
                    $meta_description = html_entity_decode(trim($matches[1]));
                }
                
                // Ekstrak meta keywords
                if (preg_match('/<meta\s+name="keywords"\s+content="([^"]*)"/si', (string)$html, $matches)) {
                    $meta_keywords = html_entity_decode(trim($matches[1]));
                }
                
                // Ambil 200 karakter pertama dari body (tanpa tag)
                $clean_text = strip_tags((string)$html);
                $content = substr($clean_text, 0, 200);
            }
            
        } catch (Exception $e) {
            error_log("Error fetching content for {$url}: " . $e->getMessage());
        }
        
        return [
            'title' => $title,
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords,
            'preview_content' => $content,
            'has_content' => !empty($content)
        ];
    }

    /* ================= DETEKSI KATA MENCURIGAKAN ================= */
    private static function detectSuspiciousWords(string $text): array {
        if (!is_string($text) || empty($text)) {
            return [
                'suspicious_words' => [],
                'count' => 0,
                'risk_level' => 'Rendah'
            ];
        }
        
        $text = strtolower($text);
        
        $phishing_keywords = [
            'urgent', 'immediate action', 'segera', 'penting', 'segera verifikasi',
            'account suspended', 'account locked', 'terkunci', 'ditangguhkan',
            'verify your account', 'konfirmasi akun', 'verifikasi akun',
            'security alert', 'alert keamanan', 'security breach', 'pelanggaran keamanan',
            'unauthorized access', 'akses tidak sah', 'login attempt', 'percobaan login',
            'suspicious activity', 'aktivitas mencurigakan',
            'update your information', 'perbarui informasi',
            'billing information', 'informasi penagihan',
            'payment overdue', 'pembayaran tertunda',
            'invoice', 'faktur', 'tagihan',
            'refund', 'pengembalian dana',
            'you have won', 'anda menang', 'hadiah', 'prize', 'reward',
            'free gift', 'hadiah gratis', 'lottery', 'undian',
            'login now', 'masuk sekarang', 'sign in', 'log in',
            'password expired', 'kata sandi kedaluwarsa',
            'change your password', 'ubah kata sandi',
            'bank', 'bni', 'bca', 'mandiri', 'bri', 'btpn', 'cimb',
            'paypal', 'dana', 'ovo', 'gopay', 'linkaja',
            'facebook', 'instagram', 'twitter', 'whatsapp', 'telegram',
            'pajak', 'djp', 'ditjen pajak', 'bank indonesia',
            'kemenkeu', 'kementerian keuangan',
            'click here', 'klik disini', 'download', 'unduh',
            'limited time', 'waktu terbatas', 'offer', 'penawaran',
        ];
        
        $found = [];
        foreach ($phishing_keywords as $keyword) {
            if (stripos($text, $keyword) !== false) {
                $found[] = $keyword;
            }
        }
        
        return [
            'suspicious_words' => $found,
            'count' => count($found),
            'risk_level' => count($found) > 5 ? 'Tinggi' : (count($found) > 2 ? 'Sedang' : 'Rendah')
        ];
    }

    /* ================= CEK UMUR DOMAIN ================= */
    private static function checkDomainAge(string $domain): array {
        if (empty($domain)) {
            return [
                'creation_date' => null,
                'age_days' => null,
                'age_years' => null,
                'risk_level' => 'UNKNOWN',
                'is_new' => null,
                'status' => 'Tidak dapat diperiksa'
            ];
        }

        $dns_records = @dns_get_record($domain, DNS_ANY);
        
        if ($dns_records) {
            $whois_raw = @shell_exec("whois {$domain} 2>/dev/null");
            
            if ($whois_raw) {
                $creation_patterns = [
                    '/Creation Date:\s*(.+)/i',
                    '/Created On:\s*(.+)/i',
                    '/Registered on:\s*(.+)/i',
                    '/Registration Time:\s*(.+)/i',
                    '/created:\s*(.+)/i',
                    '/Domain Registration Date:\s*(.+)/i',
                ];
                
                foreach ($creation_patterns as $pattern) {
                    if (preg_match($pattern, $whois_raw, $matches)) {
                        $date_str = trim($matches[1]);
                        $created = strtotime($date_str);
                        
                        if ($created !== false) {
                            $days = floor((time() - $created) / 86400);
                            
                            return [
                                'creation_date' => date('Y-m-d', $created),
                                'age_days' => $days,
                                'age_years' => floor($days / 365),
                                'risk_level' => $days < 30 ? 'TINGGI' : ($days < 365 ? 'SEDANG' : 'RENDAH'),
                                'is_new' => $days < 30,
                                'status' => 'Ditemukan via WHOIS'
                            ];
                        }
                    }
                }
            }
        }

        return [
            'creation_date' => null,
            'age_days' => null,
            'age_years' => null,
            'risk_level' => 'UNKNOWN',
            'is_new' => null,
            'status' => 'Tidak dapat diperiksa'
        ];
    }

    /* ================= CEK BLACKLIST DENGAN URLHAUS ================= */
    private static function isBlacklistedUrl(string $inputUrl): bool {
        $inputHash = hash('sha256', trim($inputUrl));

        try {
            $handle = fopen("https://urlhaus.abuse.ch/downloads/text/", "r");
            if (!$handle) {
                error_log("Gagal membuka URLhaus database");
                return false;
            }

            $checked = 0;
            $maxChecks = 1000; // Batasi jumlah pengecekan untuk performa
            
            while (($line = fgets($handle)) !== false && $checked < $maxChecks) {
                $url = trim($line);
                if ($url === "" || strpos($url, '#') === 0) continue;

                $checked++;
                
                if (hash('sha256', $url) === $inputHash) {
                    fclose($handle);
                    return true;
                }
            }

            fclose($handle);
            return false;
            
        } catch (Exception $e) {
            error_log("Error checking blacklist: " . $e->getMessage());
            return false;
        }
    }

    /* ================= WEBSITE AVAILABILITY CHECK ================= */
    private static function checkWebsiteAvailability(string $url): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);

        curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        curl_close($ch);

        if ($error || $httpCode === 0) {
            return [
                'reachable' => false,
                'http_code' => $httpCode,
                'response_time' => round($total_time, 2),
                'risk' => 20,
                'status' => 'UNREACHABLE',
                'message' => 'Website tidak dapat diakses'
            ];
        }

        if ($httpCode >= 500) {
            return [
                'reachable' => false,
                'http_code' => $httpCode,
                'response_time' => round($total_time, 2),
                'risk' => 15,
                'status' => 'SERVER_ERROR',
                'message' => 'Server error (' . $httpCode . ')'
            ];
        }

        if ($httpCode >= 400 && $httpCode < 500) {
            return [
                'reachable' => true,
                'http_code' => $httpCode,
                'response_time' => round($total_time, 2),
                'risk' => 5,
                'status' => 'CLIENT_ERROR',
                'message' => 'Client error (' . $httpCode . ')'
            ];
        }

        return [
            'reachable' => true,
            'http_code' => $httpCode,
            'response_time' => round($total_time, 2),
            'risk' => 0,
            'status' => 'OK',
            'message' => 'Website dapat diakses'
        ];
    }

    /* ================= CEK BLACKLIST ================= */
    private static function checkBlacklists(string $domain, string $url): array {
        if (empty($domain)) {
            return [
                'blacklists' => [],
                'blacklisted_count' => 0,
                'is_blacklisted' => false,
                'status' => 'Domain kosong'
            ];
        }
        
        // Cek URLhaus blacklist
        $isBlacklisted = self::isBlacklistedUrl($url);
        
        $results = [
            'Phishing' => [
                ['source' => 'Google Safe Browsing', 'listed' => false, 'status' => 'Tidak terdeteksi'],
                ['source' => 'PhishTank', 'listed' => false, 'status' => 'Tidak terdeteksi'],
                ['source' => 'OpenPhish', 'listed' => false, 'status' => 'Tidak terdeteksi'],
                ['source' => 'URLhaus', 'listed' => $isBlacklisted, 'status' => $isBlacklisted ? 'Terblacklist' : 'Tidak terdeteksi']
            ],
            'Malware' => [
                ['source' => 'Malware Domain List', 'listed' => false, 'status' => 'Tidak terdeteksi'],
                ['source' => 'MalwareDomains.com', 'listed' => false, 'status' => 'Tidak terdeteksi']
            ],
            'Spam' => [
                ['source' => 'Spamhaus', 'listed' => false, 'status' => 'Tidak terdeteksi'],
                ['source' => 'URIBL', 'listed' => false, 'status' => 'Tidak terdeteksi']
            ]
        ];
        
        $blacklisted_count = $isBlacklisted ? 1 : 0;
        
        return [
            'blacklists' => $results,
            'blacklisted_count' => $blacklisted_count,
            'is_blacklisted' => $isBlacklisted,
            'status' => $isBlacklisted ? 'Terblacklist' : 'Tidak terblacklist'
        ];
    }

    /* ================= SKOR RISIKO DINAMIS ================= */
    private static function calculateDynamicRiskScore(array $analysis, array $suspicious, array $domain_age, array $blacklist, array $trust, array $availability): array {
        $risk_score = 0;
        $risk_factors = [];
        
        // 1. Analisis URL Structure (25%)
        $url_risk = 0;
        if ($analysis['is_ip']) {
            $url_risk += 20;
            $risk_factors[] = 'Menggunakan alamat IP langsung';
        }
        if ($analysis['has_at_symbol']) {
            $url_risk += 20;
            $risk_factors[] = 'Mengandung simbol @ yang mencurigakan';
        }
        if ($analysis['hyphen_count'] > 3) {
            $url_risk += 10;
            $risk_factors[] = 'Terlalu banyak tanda hubung (-)';
        }
        if ($analysis['special_char_count'] > 2) {
            $url_risk += 15;
            $risk_factors[] = 'Mengandung karakter khusus';
        }
        if ($analysis['url_length'] > 75) {
            $url_risk += 5;
            $risk_factors[] = 'URL terlalu panjang';
        }
        if ($analysis['subdomain_count'] > 2) {
            $url_risk += 10;
            $risk_factors[] = 'Terlalu banyak subdomain';
        }
        
        $url_risk = min($url_risk, 25);
        $risk_score += $url_risk;
        
        // 2. Kata mencurigakan (20%)
        $keyword_risk = min($suspicious['count'] * 4, 20);
        if ($keyword_risk > 0 && !empty($suspicious['suspicious_words'])) {
            $risk_factors[] = 'Mengandung kata-kata phishing: ' . 
                implode(', ', array_slice($suspicious['suspicious_words'], 0, 5));
        }
        $risk_score += $keyword_risk;
        
        // 3. Umur domain (15%)
        $age_risk = 0;
        if ($domain_age['age_days'] !== null) {
            if ($domain_age['age_days'] < 30) {
                $age_risk = 15;
                $risk_factors[] = 'Domain sangat baru (< 30 hari)';
            } elseif ($domain_age['age_days'] < 365) {
                $age_risk = 8;
                $risk_factors[] = 'Domain relatif baru (< 1 tahun)';
            }
        } else {
            $age_risk = 5;
            $risk_factors[] = 'Umur domain tidak dapat diperiksa';
        }
        $risk_score += $age_risk;
        
        // 4. Trust Score dari API (20%)
        $trust_risk = 20 - ($trust['score'] * 0.2);
        if ($trust['score'] < 50) {
            $risk_factors[] = 'Skor kepercayaan rendah';
        }
        $risk_score += $trust_risk;
        
        // 5. Blacklist (10%)
        if ($blacklist['is_blacklisted']) {
            $risk_score += 10;
            $risk_factors[] = 'Domain/URL terdapat dalam blacklist';
        }
        
        // 6. Website Availability (10%)
        $availability_risk = $availability['risk'];
        if ($availability_risk > 0) {
            $risk_factors[] = 'Masalah aksesibilitas: ' . $availability['message'];
        }
        $risk_score += $availability_risk;
        
        // Normalisasi ke 0-100
        $risk_score = min(100, max(0, $risk_score));
        
        // Tentukan level risiko
        if ($risk_score >= 70) {
            $risk_level = 'SANGAT TINGGI';
            $color = '#f44336';
        } elseif ($risk_score >= 50) {
            $risk_level = 'TINGGI';
            $color = '#ff9800';
        } elseif ($risk_score >= 30) {
            $risk_level = 'SEDANG';
            $color = '#ffc107';
        } else {
            $risk_level = 'RENDAH';
            $color = '#4caf50';
        }
        
        // Tentukan status akhir
        if (!$availability['reachable']) {
            $final_status = 'TIDAK DAPAT DIVERIFIKASI';
        } elseif ($risk_level === 'SANGAT TINGGI' || $risk_level === 'TINGGI') {
            $final_status = 'BERBAHAYA';
        } else {
            $final_status = 'AMAN';
        }
        
        // Jika blacklisted, override menjadi BERBAHAYA
        if ($blacklist['is_blacklisted']) {
            $final_status = 'BERBAHAYA';
            $risk_level = 'SANGAT TINGGI';
            $color = '#f44336';
        }
        
        return [
            'score' => round($risk_score, 1),
            'level' => $risk_level,
            'color' => $color,
            'final_status' => $final_status,
            'factors' => $risk_factors,
            'breakdown' => [
                'url_structure' => $url_risk,
                'suspicious_keywords' => $keyword_risk,
                'domain_age' => $age_risk,
                'trust_api' => $trust_risk,
                'blacklist' => $blacklist['is_blacklisted'] ? 10 : 0,
                'availability' => $availability_risk
            ]
        ];
    }

    /* ================= PENJELASAN USER FRIENDLY ================= */
    private static function generateUserFriendlyExplanation(array $risk_data, array $analysis, array $availability, array $blacklist): string {
        $score = $risk_data['score'];
        $level = $risk_data['level'];
        $final_status = $risk_data['final_status'];
        
        $base_explanations = [
            'TIDAK DAPAT DIVERIFIKASI' => "⚠️ **PERINGATAN!** Website ini tidak dapat diakses atau diverifikasi. Kemungkinan server down, domain tidak aktif, atau terdapat masalah jaringan.",
            'BERBAHAYA' => "⚠️ **PERINGATAN TINGGI!** URL ini menunjukkan karakteristik berbahaya. Jangan masukkan data pribadi atau klik link!",
            'AMAN' => "✅ **Terlihat aman.** URL ini tidak menunjukkan tanda-tanda berbahaya yang jelas.",
        ];
        
        $explanation = $base_explanations[$final_status] ?? "Tidak dapat menentukan tingkat keamanan.";
        
        $details = [];
        
        if (!$availability['reachable']) {
            $details[] = "• Website tidak dapat diakses (Error: " . $availability['message'] . ")";
        }
        
        if ($analysis['is_ip']) {
            $details[] = "• Menggunakan alamat IP langsung, bukan nama domain";
        }
        
        if ($analysis['has_at_symbol']) {
            $details[] = "• Mengandung simbol @ yang sering digunakan untuk menyembunyikan URL asli";
        }
        
        if ($analysis['special_char_count'] > 2) {
            $details[] = "• Mengandung karakter khusus yang tidak biasa pada URL";
        }
        
        if ($blacklist['is_blacklisted']) {
            $details[] = "• Terdeteksi dalam database blacklist (URLhaus)";
        }
        
        if (!empty($details)) {
            $explanation .= "\n\n**Alasan:**\n" . implode("\n", $details);
        }
        
        $advice = [
            'TIDAK DAPAT DIVERIFIKASI' => "**SARAN:** Website tidak dapat diperiksa. Coba lagi nanti atau pastikan URL yang dimasukkan benar.",
            'BERBAHAYA' => "**SARAN:** JANGAN LANJUTKAN! Tutup halaman ini dan hapus email/pesan yang mengandung link ini.",
            'AMAN' => "**SARAN:** Tetap waspada dan periksa sertifikat SSL sebelum memasukkan data sensitif.",
        ];
        
        $explanation .= "\n\n" . ($advice[$final_status] ?? "Tetap waspada saat browsing.");
        
        return $explanation;
    }

    /* ================= GOOGLE SAFE BROWSING ================= */
    private static function checkGSB(string $url): bool {
        $apiKey = getenv("GSB_API_KEY");
        
        if (empty($apiKey)) {
            return true;
        }

        $payload = [
            "client" => [
                "clientId" => "eyesec",
                "clientVersion" => "1.0"
            ],
            "threatInfo" => [
                "threatTypes" => [
                    "MALWARE",
                    "SOCIAL_ENGINEERING",
                    "UNWANTED_SOFTWARE",
                    "POTENTIALLY_HARMFUL_APPLICATION"
                ],
                "platformTypes" => ["ANY_PLATFORM"],
                "threatEntryTypes" => ["URL"],
                "threatEntries" => [
                    ["url" => $url]
                ]
            ]
        ];

        $ch = curl_init(
            "https://safebrowsing.googleapis.com/v4/threatMatches:find?key={$apiKey}"
        );

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $res = curl_exec($ch);
        
        if (curl_errno($ch)) {
            curl_close($ch);
            return true;
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return true;
        }
        
        $data = json_decode($res, true);
        return empty($data['matches']);
    }

    /* ================= VIRUSTOTAL ================= */
    private static function checkVirusTotal(string $domain): array {
        $apiKey = getenv("VT_API_KEY");
        
        if (empty($apiKey) || empty($domain)) {
            return [
                "malicious" => 0,
                "suspicious" => 0,
                "harmless" => 0,
                "undetected" => 0,
                "total" => 0,
                "status" => "API key tidak tersedia"
            ];
        }

        $ch = curl_init("https://www.virustotal.com/api/v3/domains/{$domain}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["x-apikey: {$apiKey}"],
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $res = curl_exec($ch);
        
        if (curl_errno($ch)) {
            curl_close($ch);
            return [
                "malicious" => 0,
                "suspicious" => 0,
                "harmless" => 0,
                "undetected" => 0,
                "total" => 0,
                "status" => "Connection error"
            ];
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return [
                "malicious" => 0,
                "suspicious" => 0,
                "harmless" => 0,
                "undetected" => 0,
                "total" => 0,
                "status" => "API error: " . $httpCode
            ];
        }
        
        $data = json_decode($res, true);
        $stats = $data['data']['attributes']['last_analysis_stats'] ?? [];

        return [
            "malicious"  => $stats['malicious']  ?? 0,
            "suspicious" => $stats['suspicious'] ?? 0,
            "harmless"   => $stats['harmless']   ?? 0,
            "undetected" => $stats['undetected'] ?? 0,
            "total"      => ($stats['malicious'] ?? 0) + ($stats['suspicious'] ?? 0) + 
                           ($stats['harmless'] ?? 0) + ($stats['undetected'] ?? 0),
            "status"     => "Success"
        ];
    }

    /* ================= TRUST SCORE ================= */
    private static function calculateTrustScore(bool $gsbSafe, array $vt, bool $ssl): array {
        $score = 0;

        if ($gsbSafe) {
            $score += 50;
        } else {
            $score -= 20;
        }
        
        $total = $vt['total'] ?? 0;
        $malicious = $vt['malicious'] ?? 0;
        
        if ($total > 0) {
            $malicious_percent = ($malicious / $total) * 100;
            if ($malicious_percent == 0) {
                $score += 40;
            } elseif ($malicious_percent < 10) {
                $score += 20;
            } elseif ($malicious_percent < 30) {
                $score += 5;
            } else {
                $score -= 20;
            }
        } else {
            $score += 20;
        }
        
        if ($ssl) {
            $score += 10;
        } else {
            $score -= 5;
        }
        
        $score = max(0, min(100, $score));
        
        $status =
            $score >= 80 ? "TRUSTED" :
            ($score >= 50 ? "CAUTION" : "DANGEROUS");

        return [
            "score"  => round($score),
            "status" => $status
        ];
    }

    /* ================= TRUSTED DOMAIN CHECK ================= */
    private static function isTrustedDomain(string $domain): bool {
        $trusted = [
            'google.com','facebook.com','github.com',
            'microsoft.com','apple.com','cloudflare.com',
            'youtube.com','instagram.com','twitter.com',
            'linkedin.com','wikipedia.org','amazon.com'
        ];

        foreach ($trusted as $t) {
            if ($domain === $t || str_ends_with($domain, ".$t")) {
                return true;
            }
        }
        return false;
    }

    /* ================= MAIN FUNCTION ================= */
    public static function check(PDO $pdo = null) {
        header('Content-Type: application/json');
        
        $input = file_get_contents("php://input");
        if (!$input) {
            http_response_code(400);
            echo json_encode(["error" => "Data input tidak ditemukan"]);
            return;
        }
        
        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(["error" => "Format JSON tidak valid"]);
            return;
        }
        
        if (empty($data['url'])) {
            http_response_code(400);
            echo json_encode(["error" => "URL wajib diisi"]);
            return;
        }

        $url = filter_var(trim($data['url']), FILTER_VALIDATE_URL);
        if (!$url) {
            http_response_code(400);
            echo json_encode(["error" => "URL tidak valid"]);
            return;
        }

        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['host'])) {
            http_response_code(400);
            echo json_encode(["error" => "Domain tidak valid"]);
            return;
        }
        
        $domain = $parsedUrl['host'];
        $ssl = ($parsedUrl['scheme'] ?? '') === 'https';
        
        $isTrusted = self::isTrustedDomain($domain);
        
        try {
            // Lakukan semua pemeriksaan
            $analysis = self::analyzeUrl($url);
            $availability = self::checkWebsiteAvailability($url);
            $gsbSafe = self::checkGSB($url);
            $vtData = self::checkVirusTotal($domain);
            $trustScore = self::calculateTrustScore($gsbSafe, $vtData, $ssl);
            $domainAge = self::checkDomainAge($domain);
            $blacklist = self::checkBlacklists($domain, $url); // Tambahkan parameter $url
            
            // Ambil konten untuk analisis phishing
            $content = self::fetchPartialContent($url);
            $allText = $content['title'] . ' ' . $content['meta_description'] . ' ' . $content['preview_content'];
            $suspiciousWords = self::detectSuspiciousWords($allText);
            
            // Hitung skor risiko
            $riskAnalysis = self::calculateDynamicRiskScore(
                $analysis, 
                $suspiciousWords, 
                $domainAge, 
                $blacklist, 
                $trustScore, 
                $availability
            );
            
            // Buat penjelasan untuk user (tambahkan parameter $blacklist)
            $userExplanation = self::generateUserFriendlyExplanation($riskAnalysis, $analysis, $availability, $blacklist);
            
            // Siapkan response
            $response = [
                "success" => true,
                "data" => [
                    "url" => $url,
                    "domain" => $domain,
                    "trust_score" => $trustScore['score'],
                    "risk_score" => round($riskAnalysis['score'] / 100, 2),
                    "status" => $riskAnalysis['final_status'],
                    "risk_analysis" => [
                        "score" => $riskAnalysis['score'],
                        "level" => $riskAnalysis['level'],
                        "color" => $riskAnalysis['color'],
                        "factors" => $riskAnalysis['factors'],
                        "breakdown" => $riskAnalysis['breakdown'],
                        "explanation" => $userExplanation,
                        "is_phishing" => $riskAnalysis['final_status'] === 'BERBAHAYA'
                    ],
                    "phishing_detection" => $suspiciousWords,
                    "domain_info" => [
                        "age" => $domainAge,
                        "blacklist_status" => $blacklist
                    ],
                    "url_analysis" => $analysis,
                    "content_preview" => $content,
                    "external_checks" => [
                        "google_safe_browsing" => $gsbSafe ? "clean" : "threat_detected",
                        "virustotal" => $vtData,
                        "trust_score" => $trustScore
                    ],
                    "security" => [
                        "ssl_enabled" => $ssl,
                        "is_trusted_domain" => $isTrusted
                    ],
                    "availability" => $availability,
                    "checked_at" => date("Y-m-d H:i:s"),
                    "report_id" => uniqid('scan_', true)
                ]
            ];
            
            $urlId = null;

            if ($pdo instanceof PDO) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO urls
                        (
                            url,
                            domain,
                            risk_score,
                            is_phishing,
                            ssl_valid,
                            redirect_count,
                            suspicious_words,
                            domain_age_days,
                            blacklisted,
                            url_analysis,
                            checked_at
                        )
                        VALUES
                        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");

                    $stmt->execute([
                        $url,
                        $domain,
                        round($riskAnalysis['score'] / 100, 2),
                        $riskAnalysis['is_phishing'] ?? 0,
                        $riskAnalysis['ssl_valid'] ?? 0,
                        $riskAnalysis['redirect_count'] ?? 0,
                        isset($riskAnalysis['suspicious_words'])
                            ? implode(',', $riskAnalysis['suspicious_words'])
                            : null,
                        $riskAnalysis['domain_age_days'] ?? 0,
                        $riskAnalysis['blacklisted'] ?? 0,
                        json_encode($riskAnalysis, JSON_UNESCAPED_SLASHES)
                    ]);

                    $urlId = $pdo->lastInsertId();

                } catch (Exception $e) {
                    error_log("URL insert error: " . $e->getMessage());
                }
            }

            if ($pdo instanceof PDO) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO api_logs
                        (
                            api_key_id,
                            url_id,
                            client_ip,
                            method,
                            endpoint,
                            user_agent,
                            created_at
                        )
                        VALUES
                        (?, ?, ?, ?, ?, ?, NOW())
                    ");

                    $stmt->execute([
                        $apiKeyId ?? null,
                        $urlId,
                        $_SERVER['REMOTE_ADDR'] ?? null,
                        $_SERVER['REQUEST_METHOD'] ?? null,
                        $_SERVER['REQUEST_URI'] ?? null,
                        $_SERVER['HTTP_USER_AGENT'] ?? null
                    ]);

                } catch (Exception $e) {
                    error_log("API log error: " . $e->getMessage());
                }
            }
            
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            
        } catch (Exception $e) {
            error_log("Error in UrlController::check: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "error" => "Terjadi kesalahan internal saat memproses URL",
                "debug" => $e->getMessage()
            ]);
        }
    }
}