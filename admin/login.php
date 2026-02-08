<?php
session_start();
if (isset($_SESSION['admin'])) {
  header('location: /admin');
  exit;
}
$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EyeSec Admin | Secure Login</title>
  <link rel="icon" type="image/png" href="https://eyesecs.site/assets/icons/logo-eyesec.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #00E5FF 0%, #7C4DFF 100%);
      --secondary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --dark-bg: #0A0E17;
      --card-bg: rgba(26, 34, 56, 0.95);
      --accent-blue: #00E5FF;
      --accent-purple: #7C4DFF;
      --text-primary: #FFFFFF;
      --text-secondary: #94A3B8;
      --text-muted: #64748B;
      --border-color: rgba(45, 55, 72, 0.6);
      --success: #00E676;
      --error: #FF5252;
      --warning: #FFB74D;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      background: var(--dark-bg);
      color: var(--text-primary);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      overflow-x: hidden;
      position: relative;
    }

    /* Background Particles */
    .particles {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      pointer-events: none;
    }

    .particle {
      position: absolute;
      border-radius: 50%;
      background: var(--accent-blue);
      opacity: 0.3;
      animation: float 15s infinite linear;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0) translateX(0); }
      25% { transform: translateY(-20px) translateX(10px); }
      50% { transform: translateY(-40px) translateX(-10px); }
      75% { transform: translateY(-20px) translateX(-20px); }
    }

    /* Container for horizontal layout */
    .container {
      width: 100%;
      max-width: 1000px;
      z-index: 1;
    }

    /* Horizontal Login Card */
    .login-card-horizontal {
      display: flex;
      background: var(--card-bg);
      backdrop-filter: blur(20px);
      border-radius: 28px;
      border: 1px solid rgba(124, 77, 255, 0.3);
      box-shadow: 
        0 25px 75px rgba(0, 0, 0, 0.6),
        inset 0 1px 0 rgba(255, 255, 255, 0.1),
        0 0 50px rgba(124, 77, 255, 0.2);
      position: relative;
      overflow: hidden;
      animation: cardAppear 0.8s cubic-bezier(0.4, 0, 0.2, 1);
      min-height: 600px;
    }

    .login-card-horizontal::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: conic-gradient(
        transparent,
        rgba(0, 229, 255, 0.1),
        transparent 30%
      );
      animation: rotate 6s linear infinite;
      z-index: 0;
    }

    .login-card-horizontal::after {
      content: '';
      position: absolute;
      inset: 2px;
      background: var(--card-bg);
      border-radius: 26px;
      z-index: 1;
    }

    @keyframes rotate {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    @keyframes cardAppear {
      from {
        opacity: 0;
        transform: translateY(40px) scale(0.95);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    /* Left Side - Logo/Brand Section */
    .login-left {
      flex: 1;
      padding: 60px 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      position: relative;
      z-index: 2;
      background: linear-gradient(135deg, rgba(0, 229, 255, 0.1) 0%, rgba(124, 77, 255, 0.1) 100%);
      border-right: 1px solid rgba(124, 77, 255, 0.2);
    }

    .logo-wrapper {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 30px;
      text-align: center;
    }

    .logo-main {
      width: 180px;
      height: 180px;
      background: var(--primary-gradient);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      box-shadow: 
        0 10px 15px rgba(0, 229, 255, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
      animation: logoFloat 4s ease-in-out infinite;
      transition: all 0.3s ease;
    }

    .logo-main:hover {
      transform: scale(1.05);
      box-shadow: 
        0 20px 40px rgba(0, 229, 255, 0.6),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    }

    @keyframes logoFloat {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      50% { transform: translateY(-10px) rotate(2deg); }
    }

    .logo-main img {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
    }

    .logo-glow {
      position: absolute;
      inset: -10px;
      background: var(--primary-gradient);
      border-radius: 30px;
      filter: blur(20px);
      opacity: 0.5;
      z-index: -1;
      animation: glowPulse 3s ease-in-out infinite;
    }

    @keyframes glowPulse {
      0%, 100% { opacity: 0.3; transform: scale(1); }
      50% { opacity: 0.6; transform: scale(1.05); }
    }

    .brand-text {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 12px;
    }

    .brand-name {
      font-size: 48px;
      font-weight: 900;
      letter-spacing: 1.5px;
      background: var(--primary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      position: relative;
    }

    .brand-name::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 10%;
      width: 80%;
      height: 3px;
      background: var(--primary-gradient);
      border-radius: 2px;
      transform: scaleX(0);
      transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .brand-name:hover::after {
      transform: scaleX(1);
    }

    .brand-tagline {
      font-size: 16px;
      color: var(--text-secondary);
      font-weight: 500;
      letter-spacing: 1px;
      text-transform: uppercase;
      position: relative;
      padding: 0 20px;
    }

    .brand-tagline::before,
    .brand-tagline::after {
      content: '✦';
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      color: var(--accent-blue);
      font-size: 12px;
      animation: sparkle 2s ease-in-out infinite;
    }

    .brand-tagline::before {
      left: 0;
      animation-delay: 0s;
    }

    .brand-tagline::after {
      right: 0;
      animation-delay: 1s;
    }

    @keyframes sparkle {
      0%, 100% { opacity: 0.3; transform: translateY(-50%) scale(1); }
      50% { opacity: 1; transform: translateY(-50%) scale(1.2); }
    }

    /* Right Side - Form Section */
    .login-right {
      flex: 1.2;
      padding: 60px 50px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      position: relative;
      z-index: 2;
    }

    /* Header */
    .header {
      text-align: center;
      margin-bottom: 36px;
    }

    .header h1 {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 12px;
      background: linear-gradient(135deg, var(--text-primary) 0%, var(--text-secondary) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      position: relative;
      display: inline-block;
    }

    .header h1::after {
      content: '';
      position: absolute;
      bottom: -4px;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 3px;
      background: var(--primary-gradient);
      border-radius: 2px;
    }

    .header p {
      color: var(--text-secondary);
      font-size: 15px;
      line-height: 1.6;
      max-width: 300px;
      margin: 0 auto;
    }

    /* Error Message */
    .error-message {
      background: rgba(255, 82, 82, 0.15);
      border: 1px solid rgba(255, 82, 82, 0.3);
      color: var(--error);
      padding: 18px;
      border-radius: 14px;
      margin-bottom: 28px;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 12px;
      animation: slideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
      backdrop-filter: blur(10px);
    }

    .error-message i {
      font-size: 18px;
      flex-shrink: 0;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(-20px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    /* Form */
    .form-container {
      position: relative;
      z-index: 2;
    }

    .form-group {
      margin-bottom: 24px;
      position: relative;
    }

    .form-label {
      display: block;
      margin-bottom: 10px;
      color: var(--text-secondary);
      font-size: 14px;
      font-weight: 600;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      transition: color 0.3s ease;
    }

    .input-wrapper {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted);
      font-size: 18px;
      transition: all 0.3s ease;
      z-index: 1;
    }

    .form-input {
      width: 100%;
      padding: 18px 18px 18px 52px;
      background: rgba(36, 46, 66, 0.8);
      border: 2px solid var(--border-color);
      border-radius: 14px;
      color: var(--text-primary);
      font-size: 16px;
      font-weight: 500;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      z-index: 2;
    }

    .form-input:focus {
      outline: none;
      border-color: var(--accent-blue);
      box-shadow: 
        0 0 0 4px rgba(0, 229, 255, 0.15),
        inset 0 0 20px rgba(0, 229, 255, 0.05);
      background: rgba(36, 46, 66, 0.95);
      transform: translateY(-2px);
    }

    .form-input:focus + .input-icon {
      color: var(--accent-blue);
      transform: translateY(-50%) scale(1.1);
    }

    .password-toggle {
      position: absolute;
      right: 18px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--text-muted);
      cursor: pointer;
      font-size: 18px;
      padding: 8px;
      transition: all 0.3s ease;
      z-index: 3;
    }

    .password-toggle:hover {
      color: var(--accent-blue);
      transform: translateY(-50%) scale(1.1);
    }

    /* Submit Button */
    .submit-btn {
      width: 100%;
      padding: 20px;
      background: var(--primary-gradient);
      border: none;
      border-radius: 14px;
      color: #000;
      font-size: 17px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      position: relative;
      overflow: hidden;
      margin-top: 32px;
      z-index: 2;
    }

    .submit-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, 
        transparent, 
        rgba(255, 255, 255, 0.2), 
        transparent
      );
      transition: 0.5s;
    }

    .submit-btn:hover {
      transform: translateY(-3px);
      box-shadow: 
        0 15px 35px rgba(0, 229, 255, 0.4),
        0 5px 15px rgba(124, 77, 255, 0.3);
    }

    .submit-btn:hover::before {
      left: 100%;
    }

    .submit-btn:active {
      transform: translateY(-1px);
    }

    .submit-btn.loading {
      background: var(--border-color);
      color: transparent;
      cursor: not-allowed;
    }

    .submit-btn.loading::after {
      content: '';
      position: absolute;
      width: 24px;
      height: 24px;
      border: 3px solid rgba(255, 255, 255, 0.3);
      border-top-color: var(--accent-blue);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    /* Security Note */
    .security-note {
      margin-top: 36px;
      padding-top: 28px;
      border-top: 1px solid var(--border-color);
    }

    .security-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
      margin-bottom: 20px;
    }

    .security-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px;
      background: rgba(36, 46, 66, 0.5);
      border-radius: 10px;
      border: 1px solid rgba(0, 229, 255, 0.1);
      transition: all 0.3s ease;
    }

    .security-item:hover {
      background: rgba(36, 46, 66, 0.8);
      border-color: rgba(0, 229, 255, 0.3);
      transform: translateY(-2px);
    }

    .security-item i {
      color: var(--accent-blue);
      font-size: 16px;
      flex-shrink: 0;
    }

    .security-item span {
      font-size: 13px;
      color: var(--text-secondary);
      font-weight: 500;
    }

    .security-disclaimer {
      font-size: 12px;
      color: var(--text-muted);
      line-height: 1.6;
      margin-top: 20px;
    }

    /* Footer */
    .footer {
      margin-top: 40px;
      text-align: center;
      padding-top: 24px;
      border-top: 1px solid var(--border-color);
      position: relative;
      z-index: 2;
    }

    .footer-content {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .footer-links {
      display: flex;
      justify-content: center;
      gap: 24px;
      flex-wrap: wrap;
    }

    .footer-link {
      color: var(--text-secondary);
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      transition: all 0.3s ease;
      position: relative;
      padding: 4px 8px;
    }

    .footer-link:hover {
      color: var(--accent-blue);
    }

    .footer-link::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 8px;
      right: 8px;
      height: 2px;
      background: var(--accent-blue);
      border-radius: 1px;
      transform: scaleX(0);
      transition: transform 0.3s ease;
    }

    .footer-link:hover::after {
      transform: scaleX(1);
    }

    .copyright {
      font-size: 12px;
      color: var(--text-muted);
      opacity: 0.8;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
      .login-card-horizontal {
        flex-direction: column;
        max-width: 500px;
        margin: 0 auto;
      }
      
      .login-left {
        border-right: none;
        border-bottom: 1px solid rgba(124, 77, 255, 0.2);
        padding: 40px 30px;
      }
      
      .login-right {
        padding: 40px 30px;
      }
      
      .logo-main {
        width: 140px;
        height: 140px;
      }
      
      .logo-main img {
        width: 100px;
        height: 100px;
      }
      
      .brand-name {
        font-size: 40px;
      }
    }

    @media (max-width: 480px) {
      .login-card-horizontal {
        border-radius: 24px;
      }
      
      .login-left {
        padding: 30px 20px;
      }
      
      .login-right {
        padding: 30px 20px;
      }
      
      .logo-main {
        width: 120px;
        height: 120px;
      }
      
      .logo-main img {
        width: 80px;
        height: 80px;
      }
      
      .brand-name {
        font-size: 36px;
      }
      
      .header h1 {
        font-size: 26px;
      }
      
      .security-grid {
        grid-template-columns: 1fr;
        gap: 12px;
      }
      
      .form-input {
        padding: 16px 16px 16px 48px;
      }
      
      .input-icon {
        left: 16px;
      }
    }

    @media (max-width: 360px) {
      .login-left {
        padding: 25px 15px;
      }
      
      .login-right {
        padding: 25px 15px;
      }
      
      .brand-name {
        font-size: 32px;
      }
      
      .header h1 {
        font-size: 24px;
      }
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
      .login-card-horizontal {
        background: rgba(20, 25, 40, 0.95);
      }
    }

    /* Accessibility */
    .form-input:focus-visible {
      outline: 3px solid var(--accent-blue);
      outline-offset: 2px;
    }

    .submit-btn:focus-visible {
      outline: 3px solid rgba(255, 255, 255, 0.5);
      outline-offset: 3px;
    }

    /* Print styles */
    @media print {
      .login-card-horizontal {
        box-shadow: none;
        border: 2px solid #000;
      }
      
      .submit-btn {
        background: #000 !important;
        color: #fff !important;
      }
    }
  </style>
</head>
<body>
  <!-- Animated Background Particles -->
  <!-- <div class="particles" id="particles"></div> -->

  <div class="container">
    <div class="login-card-horizontal">
      <!-- Left Side - Logo/Brand Section -->
      <div class="login-left">
          <!-- Header -->
        <div class="header">
          <h1>Admin Portal</h1>
          <p>Secure access to administration dashboard</p>
        </div>
        <div class="logo-wrapper">
          <div class="logo-main">
            <div class="logo-glow"></div>
            <img src="https://eyesecs.site/assets/icons/logo-eyesec.png" alt="EyeSec Security Logo">
          </div>
          <div class="brand-text">
            <div class="brand-name">Eye-Sec</div>
            <div class="brand-tagline">Advanced Security Platform</div>
          </div>
        </div>
      </div>

      <!-- Right Side - Login Form Section -->
      <div class="login-right">
        <!-- Header -->
        <div class="header">
          <!-- <h1>Admin Portal</h1> -->
          <p>Secure access to administration dashboard</p>
        </div>

        <!-- Error Message -->
        <?php if ($error): ?>
          <div class="error-message">
            <i class="fas fa-shield-alt"></i>
            <span><?= htmlspecialchars($error) ?></span>
          </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="login_process.php" id="loginForm" class="form-container">
          <!-- Username Field -->
          <div class="form-group">
            <label class="form-label">Administrator ID</label>
            <div class="input-wrapper">
              <i class="input-icon fas fa-user-secret"></i>
              <input 
                type="text" 
                name="username" 
                class="form-input" 
                placeholder="Enter your admin ID" 
                required
                autocomplete="username"
                autocapitalize="none"
                spellcheck="false"
              >
            </div>
          </div>

          <!-- Password Field -->
          <div class="form-group">
            <label class="form-label">Security Key</label>
            <div class="input-wrapper">
              <i class="input-icon fas fa-key"></i>
              <input 
                type="password" 
                name="password" 
                id="password"
                class="form-input" 
                placeholder="Enter your security key" 
                required
                autocomplete="current-password"
              >
              <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                <i class="fas fa-eye"></i>
              </button>
            </div>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="submit-btn" id="submitBtn">
            <i class="fas fa-fingerprint"></i>
            <span>Authenticate & Access</span>
          </button>
        </form>

        <!-- Security Features -->
        <div class="security-note">
          <div class="security-grid">
            <div class="security-item">
              <i class="fas fa-shield-check"></i>
              <span>256-bit Encryption</span>
            </div>
            <div class="security-item">
              <i class="fas fa-history"></i>
              <span>Activity Logging</span>
            </div>
            <div class="security-item">
              <i class="fas fa-clock"></i>
              <span>Session Timeout</span>
            </div>
            <div class="security-item">
              <i class="fas fa-bell"></i>
              <span>Real-time Alerts</span>
            </div>
          </div>
          <p class="security-disclaimer">
            <i class="fas fa-info-circle" style="margin-right: 6px;"></i>
            This system is protected by advanced security protocols. All access attempts are monitored, logged, and analyzed for suspicious activities.
          </p>
        </div>

        <!-- Footer -->
        <div class="footer">
          <div class="footer-content">
            <div class="footer-links">
              <a href="#" class="footer-link" onclick="return false;">Privacy Policy</a>
              <a href="#" class="footer-link" onclick="return false;">Terms of Service</a>
              <a href="#" class="footer-link" onclick="alert('Contact: security@eyesec.site\nPhone: +1-800-EYESEC')">Support</a>
              <a href="#" class="footer-link" onclick="return false;">Documentation</a>
            </div>
            <div class="copyright">
              &copy; 2024 EyeSec Security Systems. All rights reserved. v2.1.0
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Initialize particles
    function createParticles() {
      const particlesContainer = document.getElementById('particles');
      const particleCount = 25;
      
      for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        // Random properties
        const size = Math.random() * 6 + 2;
        const posX = Math.random() * 100;
        const posY = Math.random() * 100;
        const delay = Math.random() * 15;
        const duration = Math.random() * 10 + 15;
        
        // Apply styles
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        particle.style.left = `${posX}%`;
        particle.style.top = `${posY}%`;
        particle.style.animationDelay = `${delay}s`;
        particle.style.animationDuration = `${duration}s`;
        particle.style.opacity = Math.random() * 0.4 + 0.1;
        particle.style.background = Math.random() > 0.5 ? 'var(--accent-blue)' : 'var(--accent-purple)';
        
        particlesContainer.appendChild(particle);
      }
    }

    // Toggle password visibility
    function togglePasswordVisibility() {
      const toggleBtn = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('password');
      const icon = toggleBtn.querySelector('i');
      
      const isPassword = passwordInput.type === 'password';
      passwordInput.type = isPassword ? 'text' : 'password';
      icon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
      
      // Add animation
      toggleBtn.style.transform = 'translateY(-50%) scale(1.2)';
      setTimeout(() => {
        toggleBtn.style.transform = 'translateY(-50%) scale(1)';
      }, 200);
    }

    // Form submission handling
    function handleFormSubmit(e) {
      const submitBtn = document.getElementById('submitBtn');
      const originalContent = submitBtn.innerHTML;
      
      // Prevent double submission
      if (submitBtn.classList.contains('loading')) {
        e.preventDefault();
        return;
      }
      
      // Show loading state
      submitBtn.classList.add('loading');
      submitBtn.disabled = true;
      
      // Simulate security check delay (1 second only)
      setTimeout(() => {
        // Remove loading state
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalContent;
      }, 1000);
    }

    // Caps lock detection
    function detectCapsLock(e) {
      const passwordInput = document.getElementById('password');
      const capsWarning = document.querySelector('.caps-warning');
      
      if (e.getModifierState && e.getModifierState('CapsLock')) {
        if (!capsWarning) {
          const warning = document.createElement('div');
          warning.className = 'caps-warning';
          warning.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Caps Lock is enabled';
          warning.style.cssText = `
            position: absolute;
            top: -30px;
            right: 0;
            background: rgba(255, 183, 77, 0.2);
            color: var(--warning);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            animation: slideIn 0.3s ease;
          `;
          passwordInput.parentElement.style.position = 'relative';
          passwordInput.parentElement.appendChild(warning);
        }
      } else if (capsWarning) {
        capsWarning.remove();
      }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
      // Create particles
      createParticles();
      
      // Setup event listeners
      document.getElementById('togglePassword').addEventListener('click', togglePasswordVisibility);
      document.getElementById('loginForm').addEventListener('submit', handleFormSubmit);
      document.getElementById('password').addEventListener('keyup', detectCapsLock);
      
      // Add focus effects
      const inputs = document.querySelectorAll('.form-input');
      inputs.forEach(input => {
        input.addEventListener('focus', () => {
          input.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', () => {
          input.parentElement.classList.remove('focused');
        });
      });
      
      // Add keyboard shortcut (Enter to submit)
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !document.getElementById('submitBtn').classList.contains('loading')) {
          const focused = document.activeElement;
          if (focused.tagName === 'INPUT') {
            // Form will submit normally
          }
        }
      });
      
      // Add subtle hover effects to cards
      const securityItems = document.querySelectorAll('.security-item');
      securityItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
          const icon = item.querySelector('i');
          icon.style.transform = 'scale(1.2)';
          icon.style.transition = 'transform 0.3s ease';
        });
        
        item.addEventListener('mouseleave', () => {
          const icon = item.querySelector('i');
          icon.style.transform = 'scale(1)';
        });
      });

      // Form validation
      const loginForm = document.getElementById('loginForm');
      const usernameInput = document.querySelector('input[name="username"]');
      const passwordInput = document.getElementById('password');

      function validateForm() {
        let isValid = true;
        
        // Clear previous error styles
        usernameInput.style.borderColor = '';
        passwordInput.style.borderColor = '';
        
        // Validate username
        if (!usernameInput.value.trim()) {
          usernameInput.style.borderColor = 'var(--error)';
          usernameInput.focus();
          isValid = false;
        }
        
        // Validate password
        if (!passwordInput.value) {
          passwordInput.style.borderColor = 'var(--error)';
          if (isValid) passwordInput.focus();
          isValid = false;
        }
        
        return isValid;
      }

      // Add form validation on submit
      loginForm.addEventListener('submit', function(e) {
        if (!validateForm()) {
          e.preventDefault();
          
          // Show error animation
          if (!usernameInput.value.trim()) {
            usernameInput.style.animation = 'shake 0.5s ease-in-out';
            setTimeout(() => {
              usernameInput.style.animation = '';
            }, 500);
          }
          
          if (!passwordInput.value) {
            passwordInput.style.animation = 'shake 0.5s ease-in-out';
            setTimeout(() => {
              passwordInput.style.animation = '';
            }, 500);
          }
        }
      });
    });

    // Add window resize handler
    window.addEventListener('resize', () => {
      const particles = document.querySelectorAll('.particle');
      particles.forEach(particle => {
        const currentAnim = particle.style.animation;
        particle.style.animation = 'none';
        setTimeout(() => {
          particle.style.animation = currentAnim;
        }, 10);
      });
    });
  </script>
</body>
</html>