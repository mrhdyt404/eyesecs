// ============================================
// Website Security Checker - Eye-Sec
// ============================================

// API Configuration
const API_BASE = "https://eyesecs.site/api";

// DOM Elements
const urlInput = document.getElementById("urlInput");
const scanBtn = document.getElementById("scanBtn");
const loadingIndicator = document.getElementById("loadingIndicator");
const errorMessage = document.getElementById("errorMessage");
const errorText = document.getElementById("errorText");
const resultSection = document.getElementById("result");
const generateApiKeyBtn = document.getElementById("generateApiKey");
const apiKeyDisplay = document.getElementById("apiKeyDisplay");
const apiKeyExpire = document.getElementById("apiKeyExpire");

// ============================================
// Utility Functions
// ============================================

/**
 * Shows loading indicator
 */
function showLoading(show) {
  if (show) {
    loadingIndicator.classList.remove("hidden");
    errorMessage.classList.add("hidden");
  } else {
    loadingIndicator.classList.add("hidden");
  }
}

/**
 * Shows error message
 */
function showError(message) {
  errorText.textContent = message;
  errorMessage.classList.remove("hidden");
  errorMessage.scrollIntoView({ behavior: "smooth" });
}

/**
 * Validates URL format
 */
function isValidUrl(url) {
  try {
    new URL(url);
    return true;
  } catch {
    return false;
  }
}

// ============================================
// API Key Generator Functions
// ============================================

/**
 * Generates a guest API key
 */
function generateApiKey() {
  // Show loading state
  const originalText = generateApiKeyBtn.innerHTML;
  generateApiKeyBtn.innerHTML =
    '<i class="fas fa-spinner fa-spin"></i> Generating...';
  generateApiKeyBtn.disabled = true;

  fetch(`${API_BASE}/v1/apikey/guest`, {
    method: "POST",
    headers: {
      Accept: "application/json",
    },
  })
    .then((res) => {
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    })
    .then((data) => {
      if (!data.api_key) {
        throw new Error("No API key received");
      }

      // Display API key
      apiKeyDisplay.textContent = data.api_key;

      // Display expiration
      if (data.expired_at) {
        apiKeyExpire.textContent = `limit 10 request per hari Berlaku sampai: ${data.expired_at}`;
        apiKeyExpire.style.display = "block";
      }

      // Copy to clipboard automatically
      navigator.clipboard.writeText(data.api_key).then(() => {
        // Show success message
        generateApiKeyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        generateApiKeyBtn.style.background =
          "linear-gradient(90deg, var(--secondary), #00a844)";

        // Show notification
        showNotification(
          "API Key berhasil digenerate dan disalin ke clipboard!",
        );
      });

      // Reset button after 3 seconds
      setTimeout(() => {
        generateApiKeyBtn.innerHTML = originalText;
        generateApiKeyBtn.style.background = "";
        generateApiKeyBtn.disabled = false;
      }, 3000);
    })
    .catch((err) => {
      console.error("Error generating API key:", err);
      showNotification("❌ Gagal generate API Key. Coba lagi nanti.");

      // Reset button
      generateApiKeyBtn.innerHTML = originalText;
      generateApiKeyBtn.disabled = false;
    });
}

// ============================================
// URL Security Check Functions - DIPERBAIKI
// ============================================

/**
 * Get status styling berdasarkan level risiko
 */
function getStatusStyling(level, isReachable) {
  if (!isReachable) {
    return {
      color: "#9e9e9e",
      icon: "fa-question-circle",
      class: "status-unknown",
      text: "TIDAK DAPAT DIVERIFIKASI",
    };
  }

  const levels = {
    "SANGAT TINGGI": {
      color: "#f44336",
      icon: "fa-skull-crossbones",
      class: "status-danger",
    },
    TINGGI: {
      color: "#ff9800",
      icon: "fa-exclamation-triangle",
      class: "status-warning",
    },
    SEDANG: {
      color: "#ffc107",
      icon: "fa-shield-alt",
      class: "status-caution",
    },
    RENDAH: {
      color: "#4caf50",
      icon: "fa-shield-check",
      class: "status-safe",
    },
    default: {
      color: "#757575",
      icon: "fa-question-circle",
      class: "status-unknown",
    },
  };

  return levels[level] || levels.default;
}

/**
 * Menampilkan availability info
 */
function displayAvailabilityInfo(availability) {
  return `
        <div class="availability-card ${availability.reachable ? "reachable" : "unreachable"}">
            <div class="availability-header">
                <i class="fas ${availability.reachable ? "fa-check-circle success" : "fa-times-circle danger"}"></i>
                <span>Status Akses Website</span>
            </div>
            <div class="availability-body">
                <div class="availability-status">
                    ${availability.reachable ? "✅ Dapat diakses" : "❌ Tidak dapat diakses"}
                </div>
                <div class="availability-message">${availability.message || "Tidak ada informasi tambahan"}</div>
                ${
                  availability.response_time
                    ? `<div class="response-time"><i class="far fa-clock"></i> Waktu respons: ${availability.response_time} detik</div>`
                    : ""
                }
            </div>
        </div>
    `;
}

/**
 * Menampilkan skor risiko yang lebih informatif
 */
function displayEnhancedRiskScore(score, level, color) {
  const riskDescription = getRiskDescription(score, level);
  const progressWidth = Math.min(score, 100);

  return `
        <div class="enhanced-risk-score">
            <div class="score-header">
                <h3><i class="fas fa-analytics"></i> Analisis Risiko Detail</h3>
                <div class="score-badge" style="background: ${color}20; color: ${color}; border-color: ${color}">
                    ${level}
                </div>
            </div>
            
            <div class="score-main">
                <div class="score-circle-large">
                    <div class="circle-progress" style="background: conic-gradient(${color} ${progressWidth}%, #f0f0f0 0);">
                        <span class="score-number" style="color: ${color};">${score.toFixed(1)}</span>
                    </div>
                    <div class="score-label">Skor Risiko</div>
                </div>
                
                <div class="score-details">
                    <div class="progress-container">
                        <div class="progress-labels">
                            <span>Rendah</span>
                            <span>Sedang</span>
                            <span>Tinggi</span>
                            <span>Sangat Tinggi</span>
                        </div>
                        <div class="progress-bar-enhanced">
                            <div class="progress-fill" style="width: ${progressWidth}%; background: ${color};"></div>
                            <div class="progress-marker" style="left: ${progressWidth}%; border-color: ${color};"></div>
                        </div>
                        <div class="progress-scale">
                            <span>0</span>
                            <span>25</span>
                            <span>50</span>
                            <span>75</span>
                            <span>100</span>
                        </div>
                    </div>
                    
                    <div class="risk-description-card">
                        <i class="fas fa-info-circle"></i>
                        <p>${riskDescription}</p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Get deskripsi risiko berdasarkan skor
 */
function getRiskDescription(score, level) {
  if (score >= 75) {
    return "Website ini memiliki risiko keamanan yang SANGAT TINGGI. Kemungkinan besar merupakan situs phishing atau berbahaya. Hindari mengakses atau memberikan informasi pribadi.";
  } else if (score >= 50) {
    return "Website ini memiliki risiko keamanan TINGGI. Terdapat indikasi yang mencurigakan, perlu kehati-hatian ekstra saat mengakses.";
  } else if (score >= 25) {
    return "Website ini memiliki risiko keamanan SEDANG. Beberapa faktor perlu diperhatikan, namun masih relatif aman untuk diakses.";
  } else {
    return "Website ini memiliki risiko keamanan RENDAH. Tidak ditemukan indikasi mencurigakan yang signifikan.";
  }
}

/**
 * Menampilkan analisis phishing yang lebih detail
 */
function displayEnhancedPhishingAnalysis(data) {
  const phishing = data.phishing_detection;

  if (!phishing || phishing.count === 0) {
    return `
            <div class="enhanced-card safe">
                <div class="card-header">
                    <i class="fas fa-search"></i>
                    <h4>Analisis Konten Phishing</h4>
                    <span class="card-badge success">AMAN</span>
                </div>
                <div class="card-body">
                    <div class="success-result">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Tidak ditemukan kata kunci phishing</strong>
                            <p>Teks yang dianalisis tidak mengandung kata-kata yang mencurigakan.</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
  }

  const riskLevel = phishing.risk_level || "Rendah";
  const words = phishing.suspicious_words || [];
  const count = phishing.count || 0;

  const riskClass = riskLevel.toLowerCase().replace(" ", "-");
  const riskColor = getRiskColor(riskLevel);

  const wordsList = words
    .slice(0, 6)
    .map(
      (word) =>
        `<span class="keyword-bubble" style="background: ${riskColor}20; color: ${riskColor};">
            ${word}
        </span>`,
    )
    .join("");

  const moreCount = words.length > 6 ? words.length - 6 : 0;

  return `
        <div class="enhanced-card ${riskClass}">
            <div class="card-header">
                <i class="fas fa-search"></i>
                <h4>Analisis Konten Phishing</h4>
                <span class="card-badge ${riskClass}" style="background: ${riskColor}20; color: ${riskColor}; border-color: ${riskColor}">
                    ${riskLevel.toUpperCase()}
                </span>
            </div>
            <div class="card-body">
                <div class="analysis-result">
                    <div class="result-summary">
                        <i class="fas fa-exclamation-triangle" style="color: ${riskColor};"></i>
                        <div>
                            <strong>Ditemukan ${count} kata kunci mencurigakan</strong>
                            <p class="result-meta">Level risiko: ${riskLevel}</p>
                        </div>
                    </div>
                    
                    ${
                      words.length > 0
                        ? `
                    <div class="keywords-section">
                        <div class="section-title">
                            <i class="fas fa-tags"></i>
                            <span>Kata Kunci yang Terdeteksi</span>
                        </div>
                        <div class="keywords-grid">
                            ${wordsList}
                            ${
                              moreCount > 0
                                ? `
                                <span class="more-keywords">
                                    +${moreCount} lainnya
                                </span>
                            `
                                : ""
                            }
                        </div>
                        <div class="keywords-info">
                            <i class="fas fa-info-circle"></i>
                            <small>Kata kunci ini sering digunakan dalam serangan phishing</small>
                        </div>
                    </div>
                    `
                        : ""
                    }
                </div>
            </div>
        </div>
    `;
}

/**
 * Get warna berdasarkan level risiko
 */
function getRiskColor(level) {
  const colors = {
    "SANGAT TINGGI": "#f44336",
    TINGGI: "#ff9800",
    SEDANG: "#ffc107",
    RENDAH: "#4caf50",
  };
  return colors[level] || "#757575";
}

/**
 * Menampilkan informasi domain yang lebih baik
 */
function displayEnhancedDomainInfo(data) {
  const domainInfo = data.domain_info;
  const age = domainInfo?.age;
  const blacklist = domainInfo?.blacklist_status;

  const ageDays = age?.age_days || 0;
  const isNew = age?.is_new;
  const isYoung = ageDays < 365;
  const isBlacklisted = blacklist?.is_blacklisted;

  const ageBadge = isNew
    ? '<span class="info-badge warning">BARU (< 30 hari)</span>'
    : isYoung
      ? '<span class="info-badge caution">RELATIF BARU</span>'
      : '<span class="info-badge success">SUDAH LAMA</span>';

  const blacklistStatus = isBlacklisted
    ? '<span class="status-danger"><i class="fas fa-exclamation-circle"></i> Terblacklist</span>'
    : '<span class="status-success"><i class="fas fa-check-circle"></i> Tidak terblacklist</span>';

  return `
        <div class="enhanced-card">
            <div class="card-header">
                <i class="fas fa-globe"></i>
                <h4>Informasi Domain</h4>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Umur Domain</span>
                        </div>
                        <div class="info-value">
                            ${ageDays > 0 ? `${ageDays} hari` : "Tidak diketahui"}
                            ${ageBadge}
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-shield-alt"></i>
                            <span>Status Blacklist</span>
                        </div>
                        <div class="info-value">
                            ${blacklistStatus}
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-calendar-check"></i>
                            <span>Tanggal Pendaftaran</span>
                        </div>
                        <div class="info-value">
                            ${age?.creation_date || "Tidak diketahui"}
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-history"></i>
                            <span>Status Domain</span>
                        </div>
                        <div class="info-value">
                            ${
                              isNew
                                ? '<span class="status-warning"><i class="fas fa-exclamation-triangle"></i> Domain baru perlu diwaspadai</span>'
                                : '<span class="status-success"><i class="fas fa-check-circle"></i> Domain aktif</span>'
                            }
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Menampilkan konten preview yang lebih baik
 */
function displayEnhancedContentPreview(data) {
  const content = data.content_preview;

  if (!content || !content.has_content) {
    return `
            <div class="enhanced-card">
                <div class="card-header">
                    <i class="fas fa-file-alt"></i>
                    <h4>Preview Konten</h4>
                    <span class="card-badge muted">TIDAK TERSEDIA</span>
                </div>
                <div class="card-body">
                    <div class="no-content">
                        <i class="fas fa-eye-slash"></i>
                        <p>Tidak dapat mengambil preview konten dari website ini</p>
                    </div>
                </div>
            </div>
        `;
  }

  const title = content.title || "Tidak ada judul";
  const metaDesc = content.meta_description || "Tidak ada meta description";
  let previewText = content.preview_content || "";

  if (previewText.length > 200) {
    previewText = previewText.substring(0, 200) + "...";
  }

  return `
        <div class="enhanced-card">
            <div class="card-header">
                <i class="fas fa-file-alt"></i>
                <h4>Preview Konten</h4>
                <span class="card-badge info">TERSEDIA</span>
            </div>
            <div class="card-body">
                <div class="content-preview-container">
                    ${
                      title
                        ? `
                    <div class="content-section">
                        <div class="section-title">
                            <i class="fas fa-heading"></i>
                            <span>Judul Halaman</span>
                        </div>
                        <div class="content-text title-text">${title}</div>
                    </div>
                    `
                        : ""
                    }
                    
                    ${
                      metaDesc
                        ? `
                    <div class="content-section">
                        <div class="section-title">
                            <i class="fas fa-tag"></i>
                            <span>Meta Description</span>
                        </div>
                        <div class="content-text meta-text">${metaDesc}</div>
                    </div>
                    `
                        : ""
                    }
                    
                    ${
                      previewText
                        ? `
                    <div class="content-section">
                        <div class="section-title">
                            <i class="fas fa-paragraph"></i>
                            <span>Preview Konten</span>
                        </div>
                        <div class="content-text preview-text">
                            "${previewText}"
                        </div>
                    </div>
                    `
                        : ""
                    }
                </div>
            </div>
        </div>
    `;
}

/**
 * Menampilkan faktor risiko dengan ikon yang berbeda
 */
function displayEnhancedRiskFactors(factors) {
  if (!factors || factors.length === 0) {
    return `
            <div class="enhanced-card success">
                <div class="card-header">
                    <i class="fas fa-clipboard-check"></i>
                    <h4>Faktor Risiko</h4>
                    <span class="card-badge success">BAIK</span>
                </div>
                <div class="card-body">
                    <div class="no-risks">
                        <i class="fas fa-check-circle"></i>
                        <p>Tidak ditemukan faktor risiko yang signifikan</p>
                    </div>
                </div>
            </div>
        `;
  }

  const factorsHTML = factors
    .map((factor, index) => {
      const icon = getRiskFactorIcon(factor);
      const type = getRiskFactorType(factor);

      return `
            <div class="risk-factor-item ${type}">
                <div class="factor-icon">
                    <i class="${icon}"></i>
                </div>
                <div class="factor-content">
                    <div class="factor-title">Faktor Risiko ${index + 1}</div>
                    <div class="factor-description">${factor}</div>
                </div>
            </div>
        `;
    })
    .join("");

  return `
        <div class="enhanced-card warning">
            <div class="card-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Faktor Risiko</h4>
                <span class="card-badge warning">${factors.length} FAKTOR</span>
            </div>
            <div class="card-body">
                <div class="risk-factors-list">
                    ${factorsHTML}
                </div>
            </div>
        </div>
    `;
}

/**
 * Get ikon berdasarkan jenis faktor risiko
 */
function getRiskFactorIcon(factor) {
  if (factor.includes("URL")) return "fas fa-link";
  if (factor.includes("domain")) return "fas fa-globe";
  if (factor.includes("SSL") || factor.includes("HTTPS")) return "fas fa-lock";
  if (factor.includes("konten")) return "fas fa-file-alt";
  if (factor.includes("keyword")) return "fas fa-search";
  return "fas fa-exclamation-circle";
}

/**
 * Get tipe faktor risiko
 */
function getRiskFactorType(factor) {
  if (factor.includes("tinggi") || factor.includes("kritis"))
    return "high-risk";
  if (factor.includes("sedang") || factor.includes("menengah"))
    return "medium-risk";
  return "low-risk";
}

/**
 * Menampilkan breakdown skor yang lebih visual
 */
function displayEnhancedRiskBreakdown(breakdown) {
  if (!breakdown) return "";

  const factors = [
    {
      label: "Struktur URL",
      value: breakdown.url_structure,
      icon: "fas fa-link",
      description: "Analisis struktur URL untuk pola mencurigakan",
    },
    {
      label: "Kata Kunci",
      value: breakdown.suspicious_keywords,
      icon: "fas fa-search",
      description: "Deteksi kata kunci phishing dalam konten",
    },
    {
      label: "Umur Domain",
      value: breakdown.domain_age,
      icon: "fas fa-calendar",
      description: "Domain baru lebih berisiko",
    },
    {
      label: "Trust API",
      value: breakdown.trust_api,
      icon: "fas fa-shield-alt",
      description: "Hasil dari API keamanan eksternal",
    },
    {
      label: "Status Blacklist",
      value: breakdown.blacklist,
      icon: "fas fa-ban",
      description: "Pengecekan database blacklist",
    },
  ];

  const totalScore = factors.reduce((sum, factor) => sum + factor.value, 0);
  const averageScore = totalScore / factors.length;

  const barsHTML = factors
    .map((factor) => {
      const width = Math.min(factor.value, 100);
      const color = getScoreColor(factor.value);

      return `
            <div class="breakdown-item">
                <div class="breakdown-header">
                    <div class="breakdown-label">
                        <i class="${factor.icon}"></i>
                        <span>${factor.label}</span>
                    </div>
                    <div class="breakdown-value" style="color: ${color}">
                        ${factor.value.toFixed(1)}
                    </div>
                </div>
                <div class="breakdown-bar">
                    <div class="bar-bg"></div>
                    <div class="bar-fill" style="width: ${width}%; background: ${color};"></div>
                </div>
                <div class="breakdown-description">
                    ${factor.description}
                </div>
            </div>
        `;
    })
    .join("");

  return `
        <div class="enhanced-card">
            <div class="card-header">
                <i class="fas fa-chart-pie"></i>
                <h4>Detail Skor Risiko</h4>
                <span class="card-badge info">RATA-RATA: ${averageScore.toFixed(1)}</span>
            </div>
            <div class="card-body">
                <div class="breakdown-container">
                    ${barsHTML}
                </div>
                <div class="breakdown-footer">
                    <div class="legend">
                        <div class="legend-item">
                            <span class="legend-color" style="background: #4caf50;"></span>
                            <span>Rendah (0-25)</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background: #ffc107;"></span>
                            <span>Sedang (26-50)</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background: #ff9800;"></span>
                            <span>Tinggi (51-75)</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background: #f44336;"></span>
                            <span>Sangat Tinggi (76-100)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Get warna berdasarkan skor
 */
function getScoreColor(score) {
  if (score >= 75) return "#f44336";
  if (score >= 50) return "#ff9800";
  if (score >= 25) return "#ffc107";
  return "#4caf50";
}

/**
 * Menampilkan penjelasan yang lebih baik untuk pengguna
 */
function displayEnhancedUserExplanation(explanation) {
  if (!explanation) return "";

  return `
        <div class="explanation-card">
            <div class="explanation-header">
                <i class="fas fa-comments"></i>
                <h4>Penjelasan Hasil Analisis</h4>
            </div>
            <div class="explanation-body">
                <div class="explanation-content">
                    ${explanation.replace(/\n/g, "<br>")}
                </div>
                <div class="explanation-tips">
                    <div class="tip">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Tips Keamanan:</strong>
                        <ul>
                            <li>Selalu periksa URL sebelum memasukkan informasi sensitif</li>
                            <li>Pastikan website menggunakan HTTPS</li>
                            <li>Hati-hati dengan website yang meminta informasi pribadi mendadak</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Menampilkan pemeriksaan eksternal yang lebih baik
 */
function displayEnhancedExternalChecks(data) {
  const external = data.external_checks || {};

  return `
        <div class="enhanced-card">
            <div class="card-header">
                <i class="fas fa-external-link-alt"></i>
                <h4>Pemeriksaan Eksternal</h4>
                <span class="card-badge info">3 SUMBER</span>
            </div>
            <div class="card-body">
                <div class="external-grid">
                    <!-- Google Safe Browsing -->
                    <div class="external-card ${external.google_safe_browsing === "clean" ? "safe" : "danger"}">
                        <div class="external-icon">
                            <i class="fab fa-google"></i>
                        </div>
                        <div class="external-content">
                            <div class="external-title">Google Safe Browsing</div>
                            <div class="external-status">
                                ${
                                  external.google_safe_browsing === "clean"
                                    ? '<span class="status-success"><i class="fas fa-check"></i> Aman</span>'
                                    : '<span class="status-danger"><i class="fas fa-times"></i> Terancam</span>'
                                }
                            </div>
                            <div class="external-desc">Database ancaman dari Google</div>
                        </div>
                    </div>
                    
                    <!-- VirusTotal -->
                    <div class="external-card">
                        <div class="external-icon">
                            <i class="fas fa-shield-virus"></i>
                        </div>
                        <div class="external-content">
                            <div class="external-title">VirusTotal</div>
                            <div class="external-stats">
                                <div class="stat">
                                    <span class="stat-label">Malicious:</span>
                                    <span class="stat-value ${external.virustotal?.malicious > 0 ? "danger" : "safe"}">
                                        ${external.virustotal?.malicious || 0}
                                    </span>
                                </div>
                                <div class="stat">
                                    <span class="stat-label">Suspicious:</span>
                                    <span class="stat-value ${external.virustotal?.suspicious > 0 ? "warning" : "safe"}">
                                        ${external.virustotal?.suspicious || 0}
                                    </span>
                                </div>
                                <div class="stat">
                                    <span class="stat-label">Total:</span>
                                    <span class="stat-value">${external.virustotal?.total || 0}</span>
                                </div>
                            </div>
                            <div class="external-desc">Analisis multi-engine antivirus</div>
                        </div>
                    </div>
                    
                    <!-- Trust Score -->
                    <div class="external-card">
                        <div class="external-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="external-content">
                            <div class="external-title">Trust Score</div>
                            <div class="trust-display">
                                <div class="trust-score">${external.trust_score?.score || 0}/100</div>
                                <div class="trust-status ${(external.trust_score?.status || "UNKNOWN").toLowerCase()}">
                                    ${external.trust_score?.status || "UNKNOWN"}
                                </div>
                            </div>
                            <div class="external-desc">Skor kepercayaan website</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Menampilkan informasi teknis yang lebih baik
 */
function displayEnhancedTechnicalInfo(data) {
  const security = data.security || {};
  const urlAnalysis = data.url_analysis || {};
  const riskAnalysis = data.risk_analysis || {};

  return `
        <div class="enhanced-card">
            <div class="card-header">
                <i class="fas fa-cogs"></i>
                <h4>Informasi Teknis</h4>
            </div>
            <div class="card-body">
                <div class="tech-grid">
                    <div class="tech-card ${security.ssl_enabled ? "success" : "danger"}">
                        <div class="tech-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div class="tech-content">
                            <div class="tech-title">SSL/TLS</div>
                            <div class="tech-value">
                                ${security.ssl_enabled ? "HTTPS Aktif" : "HTTP Saja"}
                            </div>
                        </div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">
                            <i class="fas fa-sitemap"></i>
                        </div>
                        <div class="tech-content">
                            <div class="tech-title">Subdomain</div>
                            <div class="tech-value">
                                ${urlAnalysis.subdomain_count || 0}
                            </div>
                        </div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">
                            <i class="fas fa-ruler-combined"></i>
                        </div>
                        <div class="tech-content">
                            <div class="tech-title">Panjang URL</div>
                            <div class="tech-value">
                                ${urlAnalysis.url_length || 0} karakter
                            </div>
                        </div>
                    </div>
                    
                    <div class="tech-card ${riskAnalysis.is_phishing ? "danger" : "success"}">
                        <div class="tech-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="tech-content">
                            <div class="tech-title">Hasil Analisis</div>
                            <div class="tech-value">
                                ${riskAnalysis.is_phishing ? "PHISHING" : "AMAN"}
                            </div>
                        </div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">
                            <i class="fas fa-server"></i>
                        </div>
                        <div class="tech-content">
                            <div class="tech-title">Tipe Server</div>
                            <div class="tech-value">
                                ${security.server_type || "Tidak diketahui"}
                            </div>
                        </div>
                    </div>
                    
                    <div class="tech-card">
                        <div class="tech-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <div class="tech-content">
                            <div class="tech-title">Teknologi</div>
                            <div class="tech-value">
                                ${security.technologies ? security.technologies.slice(0, 2).join(", ") : "Tidak terdeteksi"}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Main function to display enhanced results
 */
function displayEnhancedResults(data) {
  const d = data.data;
  const riskAnalysis = d.risk_analysis;
  const availability = d.availability;

  // Get status styling
  const status = getStatusStyling(riskAnalysis.level, availability.reachable);

  // Create HTML for results
  const resultsHTML = `
        <!-- Result Container -->
        <div class="result-container">
            <!-- Header Section -->
            <div class="result-header-section ${status.class}">
                <div class="header-content">
                    <div class="status-display">
                        <div class="status-icon">
                            <i class="fas ${status.icon}" style="color: ${status.color};"></i>
                        </div>
                        <div class="status-info">
                            <h2 class="status-title" style="color: ${status.color};">${status.text}</h2>
                            <p class="url-display">${d.url}</p>
                            <div class="domain-info">
                                <span class="domain-badge">
                                    <i class="fas fa-globe"></i>
                                    ${d.domain}
                                </span>
                                <span class="score-badge">
                                    <i class="fas fa-chart-bar"></i>
                                    Skor: ${riskAnalysis.score.toFixed(1)}/100
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="header-actions">
                        <div class="score-display">
                            <div class="score-circle-main" style="border-color: ${status.color};">
                                <span style="color: ${status.color};">${riskAnalysis.score.toFixed(1)}</span>
                            </div>
                            <div class="score-label">Skor Risiko</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="result-main-grid">
                <!-- Left Column -->
                <div class="result-column">
                    ${displayEnhancedRiskScore(riskAnalysis.score, status.text, status.color)}
                    
                    ${displayAvailabilityInfo(availability)}
                    
                    ${displayEnhancedPhishingAnalysis(d)}
                </div>
                
                <!-- Right Column -->
                <div class="result-column">
                    ${displayEnhancedDomainInfo(d)}
                    
                    ${displayEnhancedRiskFactors(riskAnalysis.factors)}
                    
                    ${displayEnhancedRiskBreakdown(riskAnalysis.breakdown)}
                </div>
            </div>
            
            <!-- Additional Information -->
            <div class="result-full-width">
                ${displayEnhancedContentPreview(d)}
            </div>
            
            <!-- External Checks -->
            <div class="result-full-width">
                ${displayEnhancedExternalChecks(d)}
            </div>
            
            <!-- Technical Information -->
            <div class="result-full-width">
                ${displayEnhancedTechnicalInfo(d)}
            </div>
            
            <!-- User Explanation -->
            <div class="result-full-width">
                ${displayEnhancedUserExplanation(riskAnalysis.explanation)}
            </div>
            
            <!-- Timestamp -->
            <div class="timestamp-section">
                <div class="timestamp-content">
                    <i class="far fa-clock"></i>
                    <span>Diperiksa pada: ${d.checked_at}</span>
                    <span class="scan-id">ID: ${d.scan_id || "N/A"}</span>
                </div>
            </div>
        </div>
    `;

  // Update DOM
  resultSection.innerHTML = resultsHTML;
  resultSection.classList.add("visible");

  // Add result-specific styles
  injectEnhancedResultStyles();

  // Scroll to results dengan animasi
  setTimeout(() => {
    resultSection.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }, 300);
}

/**
 * Main function to check URL security
 */
function checkUrl() {
  const url = urlInput.value.trim();

  // Validation
  if (!url) {
    showError("Harap masukkan URL website untuk diperiksa.");
    return;
  }

  if (!isValidUrl(url)) {
    showError(
      "Format URL tidak valid. Harap masukkan URL lengkap (contoh: https://example.com).",
    );
    return;
  }

  // Reset previous results
  resultSection.classList.remove("visible");
  resultSection.innerHTML = "";
  showLoading(true);

  // Make API request
  fetch(`${API_BASE}/v1/url/check`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-API-KEY": "GUEST_aeb786006975acb2f6e9e383fd798bb1",
    },
    body: JSON.stringify({ url }),
  })
    .then((res) => {
      if (!res.ok) {
        if (res.status === 400) {
          return res.json().then((data) => {
            throw new Error(data.error || "Format URL tidak valid");
          });
        }
        if (res.status === 404) {
          throw new Error("Website tidak ditemukan atau tidak dapat diakses");
        }
        if (res.status === 429) {
          throw new Error(
            "Rate limit harian telah mencapai batas maksimum. Silakan coba lagi besok.",
          );
        }
        throw new Error(`Server error: ${res.status}`);
      }
      return res.json();
    })
    .then((data) => {
      showLoading(false);

      if (!data.success) {
        showError(
          data.error ||
            "Gagal memeriksa website. Pastikan URL yang dimasukkan benar.",
        );
        return;
      }

      // Display results
      displayEnhancedResults(data);
    })
    .catch((err) => {
      showLoading(false);

      if (
        err.message.includes("tidak ditemukan") ||
        err.message.includes("tidak dapat diakses")
      ) {
        showError(
          "Website tidak dapat diakses. Periksa URL dan koneksi internet Anda.",
        );
      } else if (err.message.includes("Format URL")) {
        showError(err.message);
      } else if (err.message.includes("Rate limit")) {
        showError(err.message);
      } else {
        showError(
          "Terjadi kesalahan koneksi ke server. Periksa koneksi internet Anda.",
        );
      }

      console.error("Error:", err);
    });
}

// ============================================
// Notification System
// ============================================

/**
 * Shows a temporary notification
 */
function showNotification(message, type = "success") {
  // Create notification element
  const notification = document.createElement("div");
  notification.className = `notification ${type}`;
  notification.innerHTML = `
        <i class="fas fa-${type === "success" ? "check-circle" : "exclamation-circle"}"></i>
        <span>${message}</span>
    `;

  // Add to body
  document.body.appendChild(notification);

  // Add CSS for notification if not already present
  if (!document.querySelector("#notification-styles")) {
    const style = document.createElement("style");
    style.id = "notification-styles";
    style.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                padding: 15px 20px;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                color: rgba(0, 0, 0, 0.62);
                display: flex;
                align-items: center;
                gap: 12px;
                z-index: 1000;
                animation: slideInRight 0.3s ease-out;
                border-left: 4px solid var(--primary);
            }
            .notification.success {
                border-left-color: var(--secondary);
            }
            .notification.error {
                border-left-color: var(--danger);
            }
            .notification i {
                font-size: 1.2rem;
            }
            .notification.success i {
                color: var(--secondary);
            }
            .notification.error i {
                color: var(--danger);
            }
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
    document.head.appendChild(style);
  }

  // Remove after 3 seconds
  setTimeout(() => {
    notification.style.animation = "slideOutRight 0.3s ease-out forwards";
    setTimeout(() => notification.remove(), 300);
  }, 3000);
}

// ============================================
// Enhanced CSS Styles untuk hasil
// ============================================

function injectEnhancedResultStyles() {
  if (!document.querySelector("#enhanced-result-styles")) {
    const style = document.createElement("style");
    style.id = "enhanced-result-styles";
    style.textContent = `
            /* ============================================
               VARIABLES UNTUK KONSISTENSI
               ============================================ */
            :root {
                /* Primary Theme Colors */
                --primary: #1a73e8;
                --primary-dark: #0d47a1;
                --secondary: #00c853;
                --warning: #ff9800;
                --danger: #f44336;
                --success: #00c853;
                
                /* Neutral Colors */
                --dark: #0f172a;
                --light: #f8fafc;
                --gray: #64748b;
                --gray-light: #e2e8f0;
                --gray-dark: #475569;
                --white: #ffffff;
                --black: #000000;
                
                /* Card Colors */
                --card-bg: #ffffff;
                --card-shadow: 0 3px 15px rgba(0,0,0,0.05);
                --card-shadow-hover: 0 5px 20px rgba(0,0,0,0.1);
                --card-header-bg: #f8fafc;
                --card-border: #e2e8f0;
                
                /* Background Colors */
                --light-gray: #f1f5f9;
                --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                
                /* Transparency Colors */
                --primary-light: rgba(26, 115, 232, 0.1);
                --primary-light-2: rgba(26, 115, 232, 0.15);
                --primary-light-3: rgba(26, 115, 232, 0.2);
                
                --secondary-light: rgba(0, 200, 83, 0.1);
                --secondary-light-2: rgba(0, 200, 83, 0.15);
                --secondary-light-3: rgba(0, 200, 83, 0.2);
                
                --warning-light: rgba(255, 152, 0, 0.1);
                --warning-light-2: rgba(255, 152, 0, 0.15);
                --warning-light-3: rgba(255, 152, 0, 0.2);
                
                --danger-light: rgba(244, 67, 54, 0.1);
                --danger-light-2: rgba(244, 67, 54, 0.15);
                --danger-light-3: rgba(244, 67, 54, 0.2);
                
                /* Gradients */
                --gradient-safe: linear-gradient(135deg, rgba(0, 200, 83, 0.05), white);
                --gradient-warning: linear-gradient(135deg, rgba(255, 152, 0, 0.05), white);
                --gradient-danger: linear-gradient(135deg, rgba(244, 67, 54, 0.05), white);
                --gradient-primary: linear-gradient(135deg, rgba(26, 115, 232, 0.05), white);
                --gradient-unknown: linear-gradient(135deg, rgba(158, 158, 158, 0.05), white);
                
                /* Typography */
                --font-sans: 'Inter', sans-serif;
                --font-mono: 'JetBrains Mono', monospace;
                
                /* Spacing */
                --spacing-xs: 0.25rem;
                --spacing-sm: 0.5rem;
                --spacing-md: 1rem;
                --spacing-lg: 1.5rem;
                --spacing-xl: 2rem;
                --spacing-2xl: 3rem;
                
                /* Border Radius */
                --radius-sm: 0.375rem;
                --radius-md: 0.75rem;
                --radius-lg: 1rem;
                --radius-xl: 1.5rem;
                --radius-full: 9999px;
                
                /* Transitions */
                --transition-fast: 150ms ease-in-out;
                --transition-normal: 250ms ease-in-out;
                --transition-slow: 350ms ease-in-out;
            }
            
            /* ============================================
               RESULT CONTAINER
               ============================================ */
            .result-container {
                display: flex;
                flex-direction: column;
                gap: var(--spacing-lg);
            }
            
            /* ============================================
               HEADER SECTION
               ============================================ */
            .result-header-section {
                border-radius: var(--radius-lg);
                padding: var(--spacing-xl);
                margin-bottom: var(--spacing-md);
                background: var(--card-bg);
                box-shadow: 0 4px 20px rgba(0,0,0,0.08);
                border: 1px solid var(--card-border);
            }
            
            .result-header-section.status-danger {
                border-left: 6px solid var(--danger);
                background: var(--gradient-danger);
            }
            
            .result-header-section.status-warning {
                border-left: 6px solid var(--warning);
                background: var(--gradient-warning);
            }
            
            .result-header-section.status-caution {
                border-left: 6px solid #ffc107;
                background: linear-gradient(135deg, rgba(255, 193, 7, 0.05), white);
            }
            
            .result-header-section.status-safe {
                border-left: 6px solid var(--secondary);
                background: var(--gradient-safe);
            }
            
            .result-header-section.status-unknown {
                border-left: 6px solid var(--gray);
                background: var(--gradient-unknown);
            }
            
            .header-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: var(--spacing-xl);
            }
            
            .status-display {
                display: flex;
                align-items: center;
                gap: var(--spacing-lg);
                flex: 1;
            }
            
            .status-icon {
                font-size: 3.5rem;
            }
            
            .status-info {
                flex: 1;
            }
            
            .status-title {
                margin: 0 0 var(--spacing-sm) 0;
                font-size: 2rem;
                font-weight: 700;
                color: var(--dark);
            }
            
            .url-display {
                margin: 0 0 var(--spacing-md) 0;
                color: var(--gray-dark);
                font-size: 1rem;
                word-break: break-all;
                font-family: var(--font-mono);
            }
            
            .domain-info {
                display: flex;
                gap: var(--spacing-md);
                flex-wrap: wrap;
            }
            
            .domain-badge, .score-badge {
                background: var(--light-gray);
                padding: var(--spacing-sm) var(--spacing-md);
                border-radius: var(--radius-full);
                font-size: 0.875rem;
                display: inline-flex;
                align-items: center;
                gap: var(--spacing-sm);
                color: var(--gray-dark);
                font-weight: 500;
            }
            
            .score-badge {
                background: var(--primary-light);
                color: var(--primary);
            }
            
            .header-actions {
                flex-shrink: 0;
            }
            
            .score-display {
                text-align: center;
            }
            
            .score-circle-main {
                width: 100px;
                height: 100px;
                border: 4px solid;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2rem;
                font-weight: 700;
                margin: 0 auto var(--spacing-sm);
                font-family: var(--font-mono);
            }
            
            /* ============================================
               MAIN GRID LAYOUT
               ============================================ */
            .result-main-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: var(--spacing-lg);
                margin-bottom: var(--spacing-lg);
            }
            
            .result-column {
                display: flex;
                flex-direction: column;
                gap: var(--spacing-lg);
            }
            
            .result-full-width {
                margin-bottom: var(--spacing-lg);
            }
            
            /* ============================================
               ENHANCED CARD STYLES
               ============================================ */
            .enhanced-card {
                background: var(--card-bg);
                border-radius: var(--radius-lg);
                overflow: hidden;
                box-shadow: var(--card-shadow);
                transition: transform var(--transition-normal), box-shadow var(--transition-normal);
                width: 100%;
                margin-bottom: var(--spacing-lg);
                border: 1px solid var(--card-border);
            }
            
            .enhanced-card:hover {
                transform: translateY(-2px);
                box-shadow: var(--card-shadow-hover);
                border-color: var(--primary-light-3);
            }
            
            /* Status Cards */
            .enhanced-card.safe {
                border-left: 4px solid var(--secondary);
                background: var(--gradient-safe);
            }
            
            .enhanced-card.warning {
                border-left: 4px solid var(--warning);
                background: var(--gradient-warning);
            }
            
            .enhanced-card.danger {
                border-left: 4px solid var(--danger);
                background: var(--gradient-danger);
            }
            
            .enhanced-card.primary {
                border-left: 4px solid var(--primary);
                background: var(--gradient-primary);
            }
            
            /* Card Header */
            .enhanced-card .card-header {
                display: flex;
                align-items: center;
                gap: var(--spacing-md);
                padding: 1.25rem 1.5rem;
                background: var(--card-header-bg);
                border-bottom: 2px solid var(--card-border);
                flex-wrap: wrap;
            }
            
            .enhanced-card .card-header i {
                font-size: 1.25rem;
                color: var(--primary);
                flex-shrink: 0;
            }
            
            .enhanced-card .card-header h4 {
                margin: 0;
                flex: 1;
                font-size: 1.1rem;
                color: var(--dark);
                min-width: 200px;
                font-weight: 600;
            }
            
            /* Card Badges */
            .enhanced-card .card-badge {
                padding: 0.375rem 0.875rem;
                border-radius: var(--radius-full);
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.3px;
                white-space: nowrap;
                border: 1px solid;
            }
            
            .enhanced-card .card-badge.success {
                background: var(--secondary-light);
                color: var(--secondary);
                border-color: var(--secondary-light-3);
            }
            
            .enhanced-card .card-badge.warning {
                background: var(--warning-light);
                color: var(--warning);
                border-color: var(--warning-light-3);
            }
            
            .enhanced-card .card-badge.danger {
                background: var(--danger-light);
                color: var(--danger);
                border-color: var(--danger-light-3);
            }
            
            .enhanced-card .card-badge.info {
                background: var(--primary-light);
                color: var(--primary);
                border-color: var(--primary-light-3);
            }
            
            .enhanced-card .card-badge.muted {
                background: rgba(100, 116, 139, 0.1);
                color: var(--gray);
                border-color: rgba(100, 116, 139, 0.3);
            }
            
            /* Card Body */
            .enhanced-card .card-body {
                padding: 1.5rem;
            }
            
            /* ============================================
               RISK SCORE DISPLAY
               ============================================ */
            .enhanced-risk-score {
                background: var(--card-bg);
                border-radius: var(--radius-lg);
                padding: 1.5rem;
                box-shadow: var(--card-shadow);
                border: 1px solid var(--card-border);
            }
            
            .score-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: var(--spacing-lg);
                padding-bottom: var(--spacing-md);
                border-bottom: 2px solid var(--card-border);
            }
            
            .score-header h3 {
                color: var(--dark);
                font-size: 1.3rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
            
            .score-header h3 i {
                color: var(--primary);
            }
            
            .score-main {
                display: grid;
                grid-template-columns: 200px 1fr;
                gap: var(--spacing-xl);
                align-items: center;
            }
            
            .score-circle-large {
                text-align: center;
            }
            
            .circle-progress {
                width: 150px;
                height: 150px;
                border-radius: 50%;
                position: relative;
                margin: 0 auto var(--spacing-md);
                display: flex;
                align-items: center;
                justify-content: center;
                background: conic-gradient(var(--primary) 0%, var(--light-gray) 0%);
            }
            
            .score-number {
                font-size: 2.5rem;
                font-weight: 700;
                background: var(--card-bg);
                width: 120px;
                height: 120px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: var(--font-mono);
                color: var(--dark);
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            
            .score-label {
                font-size: 0.875rem;
                color: var(--gray-dark);
                font-weight: 500;
            }
            
            .progress-container {
                margin-bottom: var(--spacing-md);
            }
            
            .progress-labels {
                display: flex;
                justify-content: space-between;
                margin-bottom: var(--spacing-sm);
                font-size: 0.75rem;
                color: var(--gray);
                font-weight: 500;
            }
            
            .progress-bar-enhanced {
                height: 8px;
                background: var(--light-gray);
                border-radius: var(--radius-sm);
                position: relative;
                margin: var(--spacing-sm) 0;
                overflow: hidden;
            }
            
            .progress-fill {
                height: 100%;
                border-radius: var(--radius-sm);
                transition: width 1s var(--transition-normal);
                background: var(--primary);
            }
            
            .progress-marker {
                position: absolute;
                top: -4px;
                width: 16px;
                height: 16px;
                border: 3px solid var(--primary);
                border-radius: 50%;
                background: var(--card-bg);
                transform: translateX(-50%);
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .progress-scale {
                display: flex;
                justify-content: space-between;
                font-size: 0.75rem;
                color: var(--gray);
            }
            
            .risk-description-card {
                background: var(--card-header-bg);
                padding: var(--spacing-md);
                border-radius: var(--radius-md);
                display: flex;
                gap: var(--spacing-md);
                margin-top: var(--spacing-md);
                border-left: 3px solid var(--primary);
            }
            
            .risk-description-card i {
                color: var(--primary);
                font-size: 1.25rem;
                flex-shrink: 0;
            }
            
            .risk-description-card p {
                margin: 0;
                color: var(--gray-dark);
                font-size: 0.9rem;
                line-height: 1.5;
            }
            
            /* ============================================
               AVAILABILITY CARD
               ============================================ */
            .availability-card {
                background: var(--card-bg);
                border-radius: var(--radius-lg);
                overflow: hidden;
                box-shadow: var(--card-shadow);
                border: 1px solid var(--card-border);
            }
            
            .availability-card.reachable {
                border-left: 4px solid var(--secondary);
                background: var(--gradient-safe);
            }
            
            .availability-card.unreachable {
                border-left: 4px solid var(--gray);
                background: var(--gradient-unknown);
            }
            
            .availability-header {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 1rem 1.5rem;
                background: var(--card-header-bg);
                border-bottom: 1px solid var(--card-border);
            }
            
            .availability-header i.success {
                color: var(--secondary);
            }
            
            .availability-header i.danger {
                color: var(--danger);
            }
            
            .availability-body {
                padding: 1.5rem;
            }
            
            .availability-status {
                font-size: 1.125rem;
                font-weight: 600;
                margin-bottom: 0.5rem;
                color: var(--dark);
            }
            
            .availability-message {
                color: var(--gray-dark);
                margin-bottom: 0.75rem;
                font-size: 0.9rem;
                line-height: 1.5;
            }
            
            .response-time {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: var(--gray);
                font-size: 0.85rem;
            }
            
            .response-time i {
                color: var(--primary);
            }
            
            /* ============================================
               INFO GRID & ITEMS
               ============================================ */
            .info-grid {
                display: grid;
                gap: var(--spacing-md);
            }
            
            .info-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem;
                background: var(--light-gray);
                border-radius: var(--radius-md);
                border-left: 3px solid var(--primary);
                transition: all var(--transition-fast);
            }
            
            .info-item:hover {
                background: var(--card-bg);
                transform: translateX(4px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            }
            
            .info-item:last-child {
                border-bottom: none;
            }
            
            .info-label {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                color: var(--gray-dark);
                font-weight: 500;
            }
            
            .info-label i {
                color: var(--primary);
            }
            
            .info-value {
                font-weight: 600;
                font-family: var(--font-mono);
                color: var(--dark);
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .info-value.success {
                color: var(--secondary);
            }
            
            .info-value.warning {
                color: var(--warning);
            }
            
            .info-value.danger {
                color: var(--danger);
            }
            
            .info-badge {
                padding: 0.25rem 0.75rem;
                border-radius: var(--radius-full);
                font-size: 0.75rem;
                font-weight: 600;
                margin-left: 0.5rem;
            }
            
            .info-badge.warning {
                background: var(--warning-light);
                color: var(--warning);
                border: 1px solid var(--warning-light-3);
            }
            
            .info-badge.caution {
                background: rgba(255, 193, 7, 0.1);
                color: #ff9800;
                border: 1px solid rgba(255, 193, 7, 0.3);
            }
            
            .info-badge.success {
                background: var(--secondary-light);
                color: var(--secondary);
                border: 1px solid var(--secondary-light-3);
            }
            
            /* Status Text Styles */
            .status-success {
                color: var(--secondary);
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 0.375rem;
            }
            
            .status-danger {
                color: var(--danger);
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 0.375rem;
            }
            
            .status-warning {
                color: var(--warning);
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 0.375rem;
            }
            
            /* ============================================
               KEYWORDS & TAGS
               ============================================ */
            .keywords-section {
                margin-top: 1.5rem;
                padding-top: 1rem;
                border-top: 1px solid var(--card-border);
            }
            
            .section-title {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin-bottom: 0.75rem;
                color: var(--dark);
                font-weight: 600;
            }
            
            .section-title i {
                color: var(--primary);
            }
            
            .keywords-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-bottom: 0.75rem;
            }
            
            .keyword-bubble {
                padding: 0.375rem 0.875rem;
                border-radius: var(--radius-full);
                font-size: 0.8125rem;
                font-weight: 500;
                transition: all var(--transition-fast);
                border: 1px solid;
            }
            
            .keyword-bubble.high {
                background: var(--danger-light);
                color: var(--danger);
                border-color: var(--danger-light-3);
            }
            
            .keyword-bubble.medium {
                background: var(--warning-light);
                color: var(--warning);
                border-color: var(--warning-light-3);
            }
            
            .keyword-bubble.low {
                background: var(--primary-light);
                color: var(--primary);
                border-color: var(--primary-light-3);
            }
            
            .keyword-bubble:hover {
                transform: translateY(-2px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            
            .more-keywords {
                padding: 0.375rem 0.875rem;
                background: var(--light-gray);
                color: var(--gray);
                border-radius: var(--radius-full);
                font-size: 0.8125rem;
                font-style: italic;
            }
            
            .keywords-info {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: var(--gray);
                font-size: 0.8125rem;
            }
            
            /* ============================================
               RISK FACTORS
               ============================================ */
            .risk-factors-list {
                display: flex;
                flex-direction: column;
                gap: var(--spacing-md);
            }
            
            .risk-factor-item {
                display: flex;
                gap: var(--spacing-md);
                padding: 1rem;
                background: var(--card-header-bg);
                border-radius: var(--radius-md);
                transition: all var(--transition-normal);
            }
            
            .risk-factor-item:hover {
                background: var(--card-bg);
                transform: translateX(4px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            }
            
            .risk-factor-item.high-risk {
                border-left: 3px solid var(--danger);
            }
            
            .risk-factor-item.medium-risk {
                border-left: 3px solid var(--warning);
            }
            
            .risk-factor-item.low-risk {
                border-left: 3px solid var(--primary);
            }
            
            .factor-icon {
                flex-shrink: 0;
                width: 40px;
                height: 40px;
                background: var(--card-bg);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.125rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .risk-factor-item.high-risk .factor-icon {
                color: var(--danger);
                background: var(--danger-light);
            }
            
            .risk-factor-item.medium-risk .factor-icon {
                color: var(--warning);
                background: var(--warning-light);
            }
            
            .risk-factor-item.low-risk .factor-icon {
                color: var(--primary);
                background: var(--primary-light);
            }
            
            .factor-content {
                flex: 1;
            }
            
            .factor-title {
                font-weight: 600;
                margin-bottom: 0.25rem;
                color: var(--dark);
                font-size: 0.95rem;
            }
            
            .factor-description {
                color: var(--gray-dark);
                font-size: 0.875rem;
                line-height: 1.4;
            }
            
            /* ============================================
               BREAKDOWN & ANALYSIS
               ============================================ */
            .breakdown-container {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .breakdown-item {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .breakdown-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .breakdown-label {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: var(--dark);
                font-weight: 500;
            }
            
            .breakdown-label i {
                color: var(--primary);
            }
            
            .breakdown-bar {
                height: 8px;
                background: var(--light-gray);
                border-radius: var(--radius-sm);
                position: relative;
                overflow: hidden;
            }
            
            .bar-bg {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(90deg, 
                    var(--secondary-light) 0%, 
                    var(--warning-light) 50%, 
                    var(--danger-light) 100%);
            }
            
            .bar-fill {
                position: absolute;
                top: 0;
                left: 0;
                height: 100%;
                border-radius: var(--radius-sm);
                transition: width 1s var(--transition-normal);
            }
            
            .bar-fill.high {
                background: linear-gradient(90deg, var(--danger), #ef5350);
            }
            
            .bar-fill.medium {
                background: linear-gradient(90deg, var(--warning), #ffb74d);
            }
            
            .bar-fill.low {
                background: linear-gradient(90deg, var(--secondary), #69f0ae);
            }
            
            .breakdown-description {
                color: var(--gray);
                font-size: 0.8125rem;
                margin-top: 0.25rem;
            }
            
            .breakdown-footer {
                margin-top: 1.5rem;
                padding-top: 1rem;
                border-top: 1px solid var(--card-border);
            }
            
            .legend {
                display: flex;
                justify-content: center;
                gap: 1.5rem;
                flex-wrap: wrap;
            }
            
            .legend-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.8125rem;
                color: var(--gray-dark);
            }
            
            .legend-color {
                width: 12px;
                height: 12px;
                border-radius: 2px;
            }
            
            .legend-color.high {
                background: var(--danger);
            }
            
            .legend-color.medium {
                background: var(--warning);
            }
            
            .legend-color.low {
                background: var(--secondary);
            }
            
            /* ============================================
               EXTERNAL CHECKS
               ============================================ */
            .external-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: var(--spacing-md);
            }
            
            .external-card {
                padding: 1.5rem;
                border-radius: var(--radius-md);
                text-align: center;
                transition: transform var(--transition-normal);
                border: 1px solid var(--card-border);
                background: var(--card-bg);
            }
            
            .external-card:hover {
                transform: translateY(-2px);
                box-shadow: var(--card-shadow-hover);
            }
            
            .external-card.safe {
                border-color: var(--secondary-light-3);
                background: var(--gradient-safe);
            }
            
            .external-card.danger {
                border-color: var(--danger-light-3);
                background: var(--gradient-danger);
            }
            
            .external-card.warning {
                border-color: var(--warning-light-3);
                background: var(--gradient-warning);
            }
            
            .external-icon {
                font-size: 2rem;
                margin-bottom: var(--spacing-md);
                color: var(--primary);
            }
            
            .external-title {
                font-weight: 600;
                margin-bottom: 0.5rem;
                color: var(--dark);
                font-size: 1rem;
            }
            
            .external-stats {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
                margin-bottom: 0.5rem;
            }
            
            .stat {
                display: flex;
                justify-content: space-between;
                font-size: 0.875rem;
            }
            
            .stat-label {
                color: var(--gray-dark);
            }
            
            .stat-value {
                font-weight: 600;
                font-family: var(--font-mono);
            }
            
            .stat-value.danger {
                color: var(--danger);
            }
            
            .stat-value.warning {
                color: var(--warning);
            }
            
            .stat-value.safe {
                color: var(--secondary);
            }
            
            .trust-display {
                margin-bottom: 0.5rem;
            }
            
            .trust-score {
                font-size: 2rem;
                font-weight: 700;
                color: var(--dark);
                line-height: 1;
                font-family: var(--font-mono);
            }
            
            .trust-status {
                font-size: 0.75rem;
                padding: 0.375rem 0.875rem;
                border-radius: var(--radius-full);
                display: inline-block;
                margin-top: 0.25rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.3px;
            }
            
            .trust-status.trusted {
                background: var(--secondary-light);
                color: var(--secondary);
                border: 1px solid var(--secondary-light-3);
            }
            
            .trust-status.dangerous {
                background: var(--danger-light);
                color: var(--danger);
                border: 1px solid var(--danger-light-3);
            }
            
            .trust-status.caution {
                background: var(--warning-light);
                color: var(--warning);
                border: 1px solid var(--warning-light-3);
            }
            
            .trust-status.unknown {
                background: rgba(100, 116, 139, 0.1);
                color: var(--gray);
                border: 1px solid rgba(100, 116, 139, 0.3);
            }
            
            .external-desc {
                color: var(--gray);
                font-size: 0.8125rem;
            }
            
            /* ============================================
               TECHNICAL INFO GRID
               ============================================ */
            .tech-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: var(--spacing-md);
            }
            
            .tech-card {
                padding: 1.25rem;
                border-radius: var(--radius-md);
                text-align: center;
                transition: transform var(--transition-normal);
                border: 1px solid var(--card-border);
                background: var(--card-bg);
            }
            
            .tech-card:hover {
                transform: translateY(-2px);
                box-shadow: var(--card-shadow-hover);
            }
            
            .tech-card.success {
                border-color: var(--secondary-light-3);
                background: var(--gradient-safe);
            }
            
            .tech-card.danger {
                border-color: var(--danger-light-3);
                background: var(--gradient-danger);
            }
            
            .tech-card.warning {
                border-color: var(--warning-light-3);
                background: var(--gradient-warning);
            }
            
            .tech-icon {
                font-size: 1.5rem;
                margin-bottom: 0.75rem;
                color: var(--primary);
            }
            
            .tech-title {
                font-weight: 500;
                margin-bottom: 0.25rem;
                color: var(--gray-dark);
                font-size: 0.875rem;
            }
            
            .tech-value {
                font-weight: 600;
                color: var(--dark);
                font-size: 1.125rem;
                font-family: var(--font-mono);
            }
            
            .tech-value.success {
                color: var(--secondary);
            }
            
            .tech-value.danger {
                color: var(--danger);
            }
            
            /* ============================================
               EXPLANATION & TIPS
               ============================================ */
            .explanation-card {
                background: var(--card-bg);
                border-radius: var(--radius-lg);
                overflow: hidden;
                box-shadow: var(--card-shadow);
                border: 1px solid var(--card-border);
            }
            
            .explanation-header {
                display: flex;
                align-items: center;
                gap: var(--spacing-md);
                padding: 1.25rem 1.5rem;
                background: var(--card-header-bg);
                border-bottom: 2px solid var(--primary);
            }
            
            .explanation-header i {
                color: var(--primary);
            }
            
            .explanation-body {
                padding: 1.5rem;
            }
            
            .explanation-content {
                background: var(--card-header-bg);
                padding: 1.5rem;
                border-radius: var(--radius-md);
                margin-bottom: 1.5rem;
                line-height: 1.6;
                color: var(--dark);
                border-left: 3px solid var(--primary);
            }
            
            .explanation-tips {
                background: var(--primary-light);
                padding: 1.5rem;
                border-radius: var(--radius-md);
                border-left: 4px solid var(--primary);
            }
            
            .tip ul {
                margin: 0.5rem 0 0 0;
                padding-left: 1.25rem;
                color: var(--dark);
            }
            
            .tip li {
                margin-bottom: 0.25rem;
                line-height: 1.5;
            }
            
            /* ============================================
               TIMESTAMP SECTION
               ============================================ */
            .timestamp-section {
                background: var(--card-header-bg);
                border-radius: var(--radius-md);
                padding: 1.25rem;
                text-align: center;
                margin-top: var(--spacing-md);
                border-top: 2px solid var(--card-border);
            }
            
            .timestamp-content {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: var(--spacing-md);
                flex-wrap: wrap;
                color: var(--gray-dark);
                font-size: 0.9rem;
            }
            
            .timestamp-content i {
                color: var(--primary);
            }
            
            .scan-id {
                background: var(--primary-light);
                color: var(--primary);
                padding: 0.25rem 0.875rem;
                border-radius: var(--radius-full);
                font-size: 0.8rem;
                font-family: var(--font-mono);
                border: 1px solid var(--primary-light-3);
            }
            
            /* ============================================
               SUCCESS/RESULT STATES
               ============================================ */
            .success-result {
                display: flex;
                align-items: center;
                gap: var(--spacing-md);
                padding: 1.5rem;
                background: var(--gradient-safe);
                border-radius: var(--radius-md);
            }
            
            .success-result i {
                font-size: 2.5rem;
                color: var(--secondary);
                flex-shrink: 0;
            }
            
            .analysis-result {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .result-summary {
                display: flex;
                align-items: center;
                gap: var(--spacing-md);
                padding: 1rem;
                background: var(--card-header-bg);
                border-radius: var(--radius-md);
            }
            
            .result-meta {
                color: var(--gray-dark);
                font-size: 0.9rem;
                margin: 0.25rem 0 0 0;
            }
            
            .no-content, .no-risks {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: var(--spacing-md);
                padding: 2rem;
                text-align: center;
                color: var(--gray);
            }
            
            .no-content i, .no-risks i {
                font-size: 3rem;
                color: var(--gray-light);
            }
            
            /* ============================================
               CONTENT PREVIEW
               ============================================ */
            .content-preview-container {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .content-section {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .content-text {
                background: var(--card-header-bg);
                padding: 1rem;
                border-radius: var(--radius-md);
                color: var(--gray-dark);
                line-height: 1.5;
                border-left: 3px solid var(--card-border);
            }
            
            .title-text {
                border-left-color: var(--primary);
                background: var(--primary-light);
            }
            
            .meta-text {
                border-left-color: var(--secondary);
                background: var(--secondary-light);
            }
            
            .preview-text {
                border-left-color: #9c27b0;
                background: rgba(156, 39, 176, 0.05);
            }
            
            /* ============================================
               RESPONSIVE DESIGN
               ============================================ */
            
            /* Tablet Styles (max-width: 1024px) */
            @media (max-width: 1024px) {
                .enhanced-card .external-grid {
                    grid-template-columns: repeat(3, 1fr);
                    gap: 1rem;
                }
                
                .enhanced-card .tech-grid {
                    grid-template-columns: repeat(3, 1fr);
                    gap: 1rem;
                }
                
                .enhanced-card .card-header {
                    padding: 1rem 1.5rem;
                }
                
                .enhanced-card .card-body {
                    padding: 1.25rem 1.5rem;
                }
            }
            
            /* Tablet Portrait (max-width: 768px) */
            @media (max-width: 768px) {
                .result-main-grid {
                    grid-template-columns: 1fr;
                }
                
                .header-content {
                    flex-direction: column;
                    text-align: center;
                }
                
                .status-display {
                    flex-direction: column;
                    text-align: center;
                }
                
                .domain-info {
                    justify-content: center;
                }
                
                .score-main {
                    grid-template-columns: 1fr;
                }
                
                .external-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
                
                .tech-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
                
                /* Enhanced Card Tablet Adjustments */
                .enhanced-card {
                    border-radius: 0.9375rem;
                }
                
                .enhanced-card:hover {
                    transform: translateY(-1px);
                }
                
                .enhanced-card .external-grid {
                    grid-template-columns: repeat(2, 1fr);
                    gap: 0.75rem;
                }
                
                .enhanced-card .tech-grid {
                    grid-template-columns: repeat(2, 1fr);
                    gap: 0.75rem;
                }
                
                .enhanced-card .info-grid {
                    gap: 0.75rem;
                }
                
                .enhanced-card .info-item {
                    padding: 0.625rem;
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 0.375rem;
                }
                
                .enhanced-card .risk-factors-list {
                    gap: 0.75rem;
                }
                
                .enhanced-card .risk-factor-item {
                    padding: 0.75rem;
                    gap: 0.75rem;
                }
                
                /* Card Content Responsive Adjustments */
                .enhanced-card p,
                .enhanced-card .info-value,
                .enhanced-card .factor-description {
                    font-size: 0.875rem;
                }
                
                .enhanced-card .section-title i,
                .enhanced-card .info-label i {
                    font-size: 0.875rem;
                }
                
                .enhanced-card .keywords-grid {
                    gap: 0.375rem;
                }
                
                .enhanced-card .keyword-bubble {
                    padding: 0.25rem 0.5rem;
                    font-size: 0.75rem;
                }
                
                .enhanced-card .breakdown-item {
                    gap: 0.375rem;
                }
                
                .enhanced-card .breakdown-label {
                    font-size: 0.875rem;
                }
                
                .enhanced-card .breakdown-value {
                    font-size: 0.875rem;
                }
                
                .progress-labels {
                    flex-wrap: wrap;
                    gap: 0.5rem;
                }
                
                .legend {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 0.5rem;
                }
                
                .timestamp-content {
                    flex-direction: column;
                    gap: 0.5rem;
                }
            }
            
            /* Mobile (max-width: 480px) */
            @media (max-width: 480px) {
                .result-header-section {
                    padding: 1.5rem;
                }
                
                .status-title {
                    font-size: 1.5rem;
                }
                
                .card-header {
                    flex-wrap: wrap;
                }
                
                .score-circle-main {
                    width: 80px;
                    height: 80px;
                    font-size: 1.5rem;
                }
                
                .circle-progress {
                    width: 120px;
                    height: 120px;
                }
                
                .score-number {
                    width: 90px;
                    height: 90px;
                    font-size: 2rem;
                }
                
                .external-grid {
                    grid-template-columns: 1fr;
                }
                
                .tech-grid {
                    grid-template-columns: 1fr;
                }
                
                /* Enhanced Card Mobile Adjustments */
                .enhanced-card {
                    border-radius: 0.875rem;
                    margin-bottom: 1.25rem;
                }
                
                .enhanced-card .card-header {
                    padding: 1rem 1.25rem;
                    flex-wrap: nowrap;
                }
                
                .enhanced-card .card-header h4 {
                    font-size: 1.05rem;
                    min-width: unset;
                }
                
                .enhanced-card .card-header i {
                    font-size: 1.1rem;
                }
                
                .enhanced-card .card-badge {
                    padding: 0.3rem 0.6rem;
                    font-size: 0.7rem;
                }
                
                .enhanced-card .card-body {
                    padding: 1.25rem;
                }
            }
            
            /* Mobile Small (max-width: 360px) */
            @media (max-width: 360px) {
                .enhanced-card {
                    border-radius: 0.75rem;
                    margin-bottom: 1rem;
                }
                
                .enhanced-card .card-header {
                    padding: 1rem;
                    gap: 0.75rem;
                }
                
                .enhanced-card .card-header h4 {
                    font-size: 1rem;
                    min-width: unset;
                    flex: 0 0 100%;
                    order: 1;
                }
                
                .enhanced-card .card-header i {
                    order: 0;
                }
                
                .enhanced-card .card-badge {
                    order: 2;
                    margin-top: 0.5rem;
                    padding: 0.25rem 0.5rem;
                    font-size: 0.7rem;
                }
                
                .enhanced-card .card-body {
                    padding: 1rem;
                }
                
                /* Adjust border-left thickness on small screens */
                .enhanced-card.safe,
                .enhanced-card.warning,
                .enhanced-card.danger {
                    /* border-left: 3px solid; */
                }
            }
            
            /* Extra Small Devices (max-width: 320px) */
            @media (max-width: 320px) {
                .enhanced-card {
                    border-radius: 0.5rem;
                    margin-bottom: 0.75rem;
                }
                
                .enhanced-card .card-header {
                    padding: 0.75rem;
                    gap: 0.5rem;
                }
                
                .enhanced-card .card-header h4 {
                    font-size: 0.875rem;
                }
                
                .enhanced-card .card-header i {
                    font-size: 0.875rem;
                }
                
                .enhanced-card .card-badge {
                    font-size: 0.65rem;
                    padding: 0.2rem 0.4rem;
                }
                
                .enhanced-card .card-body {
                    padding: 0.75rem;
                }
                
                /* Hide some decorative elements on very small screens */
                .enhanced-card .info-badge,
                .enhanced-card .more-keywords {
                    display: none;
                }
            }
            
            /* Desktop Small (max-width: 1200px) */
            @media (max-width: 1200px) {
                .enhanced-card {
                    max-width: 100%;
                }
                
                .enhanced-card .external-grid {
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                }
                
                .enhanced-card .tech-grid {
                    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                }
            }
            
            /* Desktop (min-width: 1201px) */
            @media (min-width: 1201px) {
                .enhanced-card {
                    max-width: 100%;
                }
                
                .enhanced-card:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
                }
                
                .enhanced-card {
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                }
            }
            
            /* Print Styles */
            @media print {
                .enhanced-card {
                    box-shadow: none;
                    border: 1px solid #ddd;
                    break-inside: avoid;
                    page-break-inside: avoid;
                }
                
                .enhanced-card:hover {
                    transform: none;
                    box-shadow: none;
                }
                
                .enhanced-card.safe,
                .enhanced-card.warning,
                .enhanced-card.danger {
                    border-left: 3px solid;
                }
                
                /* Ensure good contrast for print */
                .enhanced-card .card-header {
                    background: #f5f5f5 !important;
                    -webkit-print-color-adjust: exact;
                }
            }
            
            /* Touch Device Optimizations */
            @media (hover: none) and (pointer: coarse) {
                .enhanced-card:hover {
                    transform: none;
                }
                
                .enhanced-card:active {
                    transform: scale(0.98);
                    transition: transform 0.1s ease;
                }
                
                /* Larger tap targets for touch */
                .enhanced-card .card-badge,
                .enhanced-card .keyword-bubble {
                    min-height: 32px;
                    display: inline-flex;
                    align-items: center;
                }
            }
            
            /* High Contrast Mode */
            @media (prefers-contrast: high) {
                .enhanced-card {
                    border: 2px solid #000;
                    box-shadow: 0 3px 15px rgba(0,0,0,0.3);
                }
                
                .enhanced-card.safe {
                    border-left: 4px solid #000;
                    background-color: #f0fff0;
                }
                
                .enhanced-card.warning {
                    border-left: 4px solid #000;
                    background-color: #fffaf0;
                }
                
                .enhanced-card.danger {
                    border-left: 4px solid #000;
                    background-color: #fff0f0;
                }
            }
            
            /* Reduced Motion */
            @media (prefers-reduced-motion: reduce) {
                .enhanced-card {
                    transition: none;
                }
                
                .enhanced-card:hover {
                    transform: none;
                }
            }
            
            /* Orientation Specific */
            @media (max-width: 768px) and (orientation: landscape) {
                .enhanced-card {
                    margin-bottom: 1rem;
                }
                
                .enhanced-card .card-header {
                    padding: 0.875rem 1rem;
                }
                
                .enhanced-card .card-body {
                    padding: 1rem;
                }
                
                /* Adjust grid layouts for landscape */
                .enhanced-card .external-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
                
                .enhanced-card .tech-grid {
                    grid-template-columns: repeat(3, 1fr);
                }
            }
            
            /* 2x Pixel Density Screens */
            @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
                .enhanced-card {
                    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
                }
                
                .enhanced-card:hover {
                    box-shadow: 0 3px 15px rgba(0,0,0,0.12);
                }
                
                .enhanced-card.safe,
                .enhanced-card.warning,
                .enhanced-card.danger {
                    border-left-width: 3px;
                }
            }
        `;
    document.head.appendChild(style);
  }
}

// ============================================
// Event Listeners & Initialization
// ============================================

document.addEventListener("DOMContentLoaded", function () {
  // Inject CSS styles for results
  injectEnhancedResultStyles();

  // Set current year in footer
  const currentYearElement = document.getElementById("currentYear");
  if (currentYearElement) {
    currentYearElement.textContent = new Date().getFullYear();
  }

  // Auto-highlight code blocks on click
  document.querySelectorAll("pre").forEach((block) => {
    block.addEventListener("click", function () {
      const selection = window.getSelection();
      const range = document.createRange();
      range.selectNodeContents(this);
      selection.removeAllRanges();
      selection.addRange(range);
    });
  });

  // Add example URL if empty
  if (urlInput && !urlInput.value.trim()) {
    urlInput.value = "https://example.com";
  }

  // Auto-focus URL input on page load
  setTimeout(() => {
    if (urlInput) urlInput.focus();
  }, 500);

  // Add event listener for Enter key in URL input
  if (urlInput) {
    urlInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        checkUrl();
      }
    });
  }

  // Add event listener for API key generation
  if (generateApiKeyBtn) {
    generateApiKeyBtn.addEventListener("click", generateApiKey);
  }
});
