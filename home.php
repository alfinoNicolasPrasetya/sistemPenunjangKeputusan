<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Paskibra Sekolah – Kabupaten Tangerang</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;600&family=Playfair+Display:wght@700;900&display=swap"
    rel="stylesheet" />

  <style>
    /* ── RESET & TOKENS ── */
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --primary: #4f46e5;
      --primary-hover: #4338ca;
      --primary-light: #eef2ff;
      --primary-glow: rgba(79, 70, 229, 0.15);
      --bg: #f8fafc;
      --surface: #ffffff;
      --surface-2: #f1f5f9;
      --border: #e2e8f0;
      --border-focus: #a5b4fc;
      --text: #0f172a;
      --text-muted: #64748b;
      --text-light: #94a3b8;
      --sidebar-bg: #0f172a;
      --success: #10b981;
      --warning: #f59e0b;
      --danger: #ef4444;
      --info: #3b82f6;
      --radius: 10px;
      --radius-lg: 14px;
      --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
      --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.1);
      --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.15);
      --transition: 0.2s ease;
      --nav-h: 68px;
    }

    html {
      font-size: 15px;
      scroll-behavior: smooth;
    }

    body {
      font-family: "Plus Jakarta Sans", system-ui, sans-serif;
      background: var(--bg);
      color: var(--text);
      line-height: 1.55;
      overflow-x: hidden;
    }

    /* ── SCROLLBAR ── */
    ::-webkit-scrollbar {
      width: 6px;
    }

    ::-webkit-scrollbar-track {
      background: var(--surface-2);
    }

    ::-webkit-scrollbar-thumb {
      background: var(--border-focus);
      border-radius: 4px;
    }

    /* ═══════════════════════════
   NAVBAR
═══════════════════════════ */
    .navbar {
      position: sticky;
      top: 0;
      z-index: 1000;
      height: var(--nav-h);
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border-bottom: 1.5px solid var(--border);
      box-shadow: var(--shadow-sm);
      transition: background var(--transition);
    }

    .nav-inner {
      max-width: 1200px;
      margin: 0 auto;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 28px;
      gap: 20px;
    }

    /* Brand */
    .nav-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      flex-shrink: 0;
    }

    .nav-logo {
      width: 42px;
      height: 42px;
      border-radius: 11px;
      background: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 14px var(--primary-glow);
      position: relative;
      overflow: hidden;
    }

    .nav-logo::after {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), transparent);
      border-radius: inherit;
    }

    .nav-logo svg {
      width: 22px;
      height: 22px;
      color: #fff;
      position: relative;
      z-index: 1;
    }

    .nav-brand-text {
      display: flex;
      flex-direction: column;
    }

    .nav-brand-name {
      font-size: 14px;
      font-weight: 800;
      color: var(--text);
      line-height: 1.2;
    }

    .nav-brand-sub {
      font-family: "IBM Plex Mono", monospace;
      font-size: 10px;
      color: var(--text-muted);
      letter-spacing: 0.05em;
    }

    /* Nav links */
    .nav-links {
      display: flex;
      align-items: center;
      gap: 4px;
      list-style: none;
    }

    .nav-link {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 8px 14px;
      border-radius: 9px;
      font-size: 13.5px;
      font-weight: 600;
      color: var(--text-muted);
      text-decoration: none;
      transition: background var(--transition), color var(--transition);
      white-space: nowrap;
    }

    .nav-link:hover {
      background: var(--surface-2);
      color: var(--text);
    }

    .nav-link.active {
      background: var(--primary-light);
      color: var(--primary);
    }

    .nav-link svg {
      width: 15px;
      height: 15px;
    }

    /* Nav right */
    .nav-right {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-shrink: 0;
    }

    .btn-login {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 9px 20px;
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 9px;
      font-family: inherit;
      font-size: 13.5px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      box-shadow: 0 2px 10px rgba(79, 70, 229, 0.28);
      transition: background var(--transition), box-shadow var(--transition), transform 0.1s;
    }

    .btn-login:hover {
      background: var(--primary-hover);
      box-shadow: 0 4px 18px rgba(79, 70, 229, 0.38);
    }

    .btn-login:active {
      transform: scale(0.97);
    }

    .btn-login svg {
      width: 16px;
      height: 16px;
    }

    /* Hamburger */
    .nav-hamburger {
      display: none;
      width: 38px;
      height: 38px;
      border-radius: 9px;
      background: none;
      border: 1.5px solid var(--border);
      color: var(--text-muted);
      cursor: pointer;
      align-items: center;
      justify-content: center;
      transition: background var(--transition);
    }

    .nav-hamburger:hover {
      background: var(--surface-2);
      color: var(--text);
    }

    .nav-hamburger svg {
      width: 20px;
      height: 20px;
    }

    /* Mobile menu */
    .mobile-menu {
      display: none;
      flex-direction: column;
      background: var(--surface);
      border-bottom: 1.5px solid var(--border);
      padding: 12px 20px 16px;
      gap: 4px;
      box-shadow: var(--shadow-md);
    }

    .mobile-menu.is-open {
      display: flex;
    }

    .mobile-menu .nav-link {
      border-radius: 9px;
    }

    .mobile-menu .btn-login {
      margin-top: 6px;
      justify-content: center;
    }

    /* ═══════════════════════════
   HERO
═══════════════════════════ */
    .hero {
      position: relative;
      min-height: calc(100vh - var(--nav-h));
      display: flex;
      align-items: center;
      overflow: hidden;
      background: var(--sidebar-bg);
    }

    /* Background decorations */
    .hero-bg {
      position: absolute;
      inset: 0;
      z-index: 0;
    }

    .hero-grid {
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(79, 70, 229, 0.07) 1px, transparent 1px),
        linear-gradient(90deg, rgba(79, 70, 229, 0.07) 1px, transparent 1px);
      background-size: 48px 48px;
    }

    .hero-glow-1 {
      position: absolute;
      top: -120px;
      left: -120px;
      width: 600px;
      height: 600px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(79, 70, 229, 0.25) 0%, transparent 70%);
      animation: floatGlow 8s ease-in-out infinite alternate;
    }

    .hero-glow-2 {
      position: absolute;
      bottom: -100px;
      right: -80px;
      width: 500px;
      height: 500px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(124, 58, 237, 0.2) 0%, transparent 70%);
      animation: floatGlow 10s ease-in-out infinite alternate-reverse;
    }

    @keyframes floatGlow {
      from {
        transform: translate(0, 0) scale(1);
      }

      to {
        transform: translate(30px, 20px) scale(1.08);
      }
    }

    /* Floating elements */
    .hero-float {
      position: absolute;
      border: 1.5px solid rgba(79, 70, 229, 0.3);
      border-radius: var(--radius-lg);
      background: rgba(79, 70, 229, 0.08);
      backdrop-filter: blur(6px);
      animation: floatEl 6s ease-in-out infinite alternate;
    }

    .hero-float:nth-child(1) {
      width: 80px;
      height: 80px;
      top: 15%;
      right: 12%;
      animation-delay: 0s;
    }

    .hero-float:nth-child(2) {
      width: 48px;
      height: 48px;
      bottom: 22%;
      right: 20%;
      animation-delay: 2s;
    }

    .hero-float:nth-child(3) {
      width: 60px;
      height: 60px;
      top: 55%;
      left: 6%;
      animation-delay: 1s;
    }

    @keyframes floatEl {
      from {
        transform: translateY(0) rotate(0deg);
      }

      to {
        transform: translateY(-18px) rotate(8deg);
      }
    }

    .hero-inner {
      position: relative;
      z-index: 1;
      max-width: 1200px;
      margin: 0 auto;
      padding: 80px 28px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 60px;
      align-items: center;
      width: 100%;
    }

    .hero-left {
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 7px 16px;
      border-radius: 50px;
      background: rgba(79, 70, 229, 0.15);
      border: 1px solid rgba(165, 180, 252, 0.3);
      color: #a5b4fc;
      font-size: 12.5px;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      width: fit-content;
      animation: fadeSlideUp 0.6s ease both;
    }

    .hero-badge-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: #a5b4fc;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
        transform: scale(1);
      }

      50% {
        opacity: 0.5;
        transform: scale(1.4);
      }
    }

    .hero-title {
      font-family: "Playfair Display", Georgia, serif;
      font-size: clamp(32px, 5vw, 58px);
      font-weight: 900;
      color: #fff;
      line-height: 1.12;
      animation: fadeSlideUp 0.7s ease 0.1s both;
    }

    .hero-title span {
      background: linear-gradient(135deg, #a5b4fc, #818cf8);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .hero-desc {
      font-size: 15px;
      color: #94a3b8;
      line-height: 1.75;
      max-width: 500px;
      animation: fadeSlideUp 0.7s ease 0.2s both;
    }

    .hero-actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      animation: fadeSlideUp 0.7s ease 0.3s both;
    }

    .btn-hero-primary {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 13px 28px;
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-family: inherit;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      box-shadow: 0 4px 20px rgba(79, 70, 229, 0.4);
      transition: background var(--transition), box-shadow var(--transition), transform 0.1s;
    }

    .btn-hero-primary:hover {
      background: var(--primary-hover);
      box-shadow: 0 6px 28px rgba(79, 70, 229, 0.5);
    }

    .btn-hero-primary:active {
      transform: scale(0.97);
    }

    .btn-hero-primary svg {
      width: 18px;
      height: 18px;
    }

    .btn-hero-secondary {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 13px 24px;
      background: rgba(255, 255, 255, 0.07);
      color: #e2e8f0;
      border: 1.5px solid rgba(255, 255, 255, 0.15);
      border-radius: 10px;
      font-family: inherit;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: background var(--transition), border-color var(--transition);
    }

    .btn-hero-secondary:hover {
      background: rgba(255, 255, 255, 0.12);
      border-color: rgba(255, 255, 255, 0.3);
    }

    .btn-hero-secondary svg {
      width: 18px;
      height: 18px;
    }

    /* Hero stats */
    .hero-stats {
      display: flex;
      gap: 28px;
      padding-top: 8px;
      animation: fadeSlideUp 0.7s ease 0.4s both;
    }

    .hero-stat {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .hero-stat-val {
      font-size: 28px;
      font-weight: 800;
      color: #fff;
      font-family: "IBM Plex Mono", monospace;
    }

    .hero-stat-lbl {
      font-size: 12px;
      color: #64748b;
    }

    .hero-stat-div {
      width: 1px;
      background: rgba(255, 255, 255, 0.08);
      align-self: stretch;
      margin: 0 4px;
    }

    /* Hero right — Card */
    .hero-card-wrap {
      position: relative;
      animation: fadeSlideUp 0.8s ease 0.2s both;
    }

    .hero-card {
      background: rgba(255, 255, 255, 0.05);
      border: 1.5px solid rgba(255, 255, 255, 0.1);
      border-radius: 20px;
      padding: 28px;
      backdrop-filter: blur(20px);
    }

    .hero-card-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 20px;
    }

    .hero-card-icon {
      width: 46px;
      height: 46px;
      border-radius: 12px;
      background: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
    }

    .hero-card-icon svg {
      width: 24px;
      height: 24px;
      color: #fff;
    }

    .hero-card-title {
      font-size: 16px;
      font-weight: 700;
      color: #fff;
    }

    .hero-card-sub {
      font-size: 12px;
      color: #64748b;
      font-family: "IBM Plex Mono", monospace;
      margin-top: 1px;
    }

    .timeline {
      display: flex;
      flex-direction: column;
      gap: 0;
      position: relative;
    }

    .timeline-item {
      display: flex;
      gap: 16px;
      position: relative;
      padding-bottom: 22px;
    }

    .timeline-item:last-child {
      padding-bottom: 0;
    }

    .timeline-left {
      display: flex;
      flex-direction: column;
      align-items: center;
      flex-shrink: 0;
    }

    .timeline-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: var(--primary);
      border: 2px solid rgba(79, 70, 229, 0.3);
      flex-shrink: 0;
      margin-top: 3px;
      box-shadow: 0 0 8px rgba(79, 70, 229, 0.5);
    }

    .timeline-dot.done {
      background: var(--success);
      border-color: rgba(16, 185, 129, 0.3);
      box-shadow: 0 0 8px rgba(16, 185, 129, 0.4);
    }

    .timeline-dot.upcoming {
      background: #64748b;
      border-color: rgba(100, 116, 139, 0.3);
      box-shadow: none;
    }

    .timeline-line {
      flex: 1;
      width: 1.5px;
      background: rgba(255, 255, 255, 0.08);
      margin-top: 6px;
    }

    .timeline-item:last-child .timeline-line {
      display: none;
    }

    .timeline-content {
      display: flex;
      flex-direction: column;
      gap: 3px;
    }

    .timeline-date {
      font-family: "IBM Plex Mono", monospace;
      font-size: 11px;
      color: #64748b;
    }

    .timeline-title {
      font-size: 13.5px;
      font-weight: 600;
      color: #e2e8f0;
    }

    .timeline-badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 20px;
      font-size: 10px;
      font-weight: 700;
      background: rgba(16, 185, 129, 0.15);
      color: #6ee7b7;
      border: 1px solid rgba(16, 185, 129, 0.2);
      width: fit-content;
      margin-top: 2px;
    }

    .timeline-badge.upcoming {
      background: rgba(100, 116, 139, 0.15);
      color: #94a3b8;
      border-color: rgba(100, 116, 139, 0.2);
    }

    .timeline-badge.active {
      background: rgba(245, 158, 11, 0.15);
      color: #fbbf24;
      border-color: rgba(245, 158, 11, 0.2);
    }

    @keyframes fadeSlideUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* ═══════════════════════════
   SECTION BASE
═══════════════════════════ */
    .section {
      padding: 80px 28px;
    }

    .section-inner {
      max-width: 1200px;
      margin: 0 auto;
    }

    .section-header {
      text-align: center;
      margin-bottom: 48px;
    }

    .section-eyebrow {
      font-family: "IBM Plex Mono", monospace;
      font-size: 11.5px;
      font-weight: 600;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--primary);
      margin-bottom: 10px;
    }

    .section-title {
      font-family: "Playfair Display", Georgia, serif;
      font-size: clamp(26px, 4vw, 42px);
      font-weight: 700;
      color: var(--text);
      line-height: 1.2;
      margin-bottom: 12px;
    }

    .section-desc {
      font-size: 14.5px;
      color: var(--text-muted);
      max-width: 540px;
      margin: 0 auto;
      line-height: 1.75;
    }

    /* ═══════════════════════════
   GALERI
═══════════════════════════ */
    #galeri {
      background: var(--surface-2);
    }

    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
    }

    .gallery-grid .gallery-featured {
      grid-column: span 2;
      grid-row: span 2;
    }

    .gallery-item {
      border-radius: var(--radius-lg);
      overflow: hidden;
      position: relative;
      aspect-ratio: 4/3;
      cursor: pointer;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      background: var(--surface);
      border: 1.5px solid var(--border);
    }

    .gallery-item.gallery-featured {
      aspect-ratio: 16/10;
    }

    .gallery-item:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg);
    }

    .gallery-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.4s ease;
    }

    .gallery-item:hover .gallery-img {
      transform: scale(1.05);
    }

    /* Placeholder imgs using SVG patterns */
    .gallery-placeholder {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .gallery-placeholder svg {
      width: 40px;
      height: 40px;
      opacity: 0.4;
    }

    .gallery-placeholder span {
      font-size: 12px;
      color: var(--text-muted);
    }

    .gallery-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(15, 23, 42, 0.9) 0%, transparent 60%);
      opacity: 0;
      transition: opacity 0.3s ease;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      padding: 20px;
    }

    .gallery-item:hover .gallery-overlay {
      opacity: 1;
    }

    .gallery-overlay-title {
      color: #fff;
      font-size: 14px;
      font-weight: 700;
    }

    .gallery-overlay-meta {
      font-family: "IBM Plex Mono", monospace;
      font-size: 11px;
      color: #94a3b8;
      margin-top: 3px;
    }

    /* Gradient "photos" */
    .gp-1 {
      background: linear-gradient(135deg, #1e1b4b, #3730a3, #4f46e5);
    }

    .gp-2 {
      background: linear-gradient(135deg, #0f172a, #1e3a5f, #1d4ed8);
    }

    .gp-3 {
      background: linear-gradient(135deg, #14532d, #166534, #16a34a);
    }

    .gp-4 {
      background: linear-gradient(135deg, #1c1917, #292524, #78350f);
    }

    .gp-5 {
      background: linear-gradient(135deg, #0c0a09, #1c1917, #44403c);
    }

    .gp-6 {
      background: linear-gradient(135deg, #1e1b4b, #312e81, #7c3aed);
    }

    .gp-7 {
      background: linear-gradient(135deg, #0f172a, #1e293b, #334155);
    }

    /* Overlay shapes on gallery items */
    .gp-overlay-circle {
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.07);
    }

    /* ═══════════════════════════
   PENGUMUMAN
═══════════════════════════ */
    #pengumuman {
      background: var(--surface);
    }

    .announce-grid {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 24px;
    }

    .announce-list {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .announce-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 20px;
      display: flex;
      gap: 16px;
      transition: border-color var(--transition), box-shadow var(--transition), transform 0.2s;
      cursor: pointer;
    }

    .announce-card:hover {
      border-color: var(--border-focus);
      box-shadow: 0 0 0 4px var(--primary-glow);
      transform: translateY(-2px);
    }

    .announce-card.important {
      border-left: 4px solid var(--primary);
    }

    .announce-card.warning {
      border-left: 4px solid var(--warning);
    }

    .announce-card.success {
      border-left: 4px solid var(--success);
    }

    .announce-icon {
      width: 44px;
      height: 44px;
      border-radius: 11px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .announce-icon.primary {
      background: var(--primary-light);
      color: var(--primary);
    }

    .announce-icon.warning {
      background: #fffbeb;
      color: var(--warning);
    }

    .announce-icon.success {
      background: #ecfdf5;
      color: var(--success);
    }

    .announce-icon svg {
      width: 22px;
      height: 22px;
    }

    .announce-content {
      flex: 1;
    }

    .announce-title {
      font-size: 14.5px;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 5px;
    }

    .announce-excerpt {
      font-size: 13px;
      color: var(--text-muted);
      line-height: 1.65;
      margin-bottom: 10px;
    }

    .announce-meta {
      display: flex;
      align-items: center;
      gap: 10px;
      font-family: "IBM Plex Mono", monospace;
      font-size: 11px;
      color: var(--text-light);
    }

    .announce-meta svg {
      width: 13px;
      height: 13px;
    }

    .announce-tag {
      display: inline-block;
      padding: 2px 9px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
    }

    .tag-penting {
      background: var(--primary-light);
      color: var(--primary);
      border: 1px solid #c7d2fe;
    }

    .tag-jadwal {
      background: #fffbeb;
      color: #b45309;
      border: 1px solid #fde68a;
    }

    .tag-hasil {
      background: #ecfdf5;
      color: #065f46;
      border: 1px solid #a7f3d0;
    }

    /* Sidebar announce */
    .announce-sidebar {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .info-card {
      background: var(--surface-2);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 20px;
    }

    .info-card-title {
      font-size: 13px;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 14px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .info-card-title svg {
      width: 16px;
      height: 16px;
      color: var(--primary);
    }

    .deadline-ring {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
      padding: 10px 0;
    }

    .ring-wrap {
      position: relative;
      width: 120px;
      height: 120px;
    }

    .ring-svg {
      width: 100%;
      height: 100%;
      transform: rotate(-90deg);
    }

    .ring-bg {
      fill: none;
      stroke: var(--border);
      stroke-width: 8;
    }

    .ring-fg {
      fill: none;
      stroke: var(--primary);
      stroke-width: 8;
      stroke-linecap: round;
      stroke-dasharray: 314;
      stroke-dashoffset: 94;
      transition: stroke-dashoffset 1s ease;
    }

    .ring-center {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .ring-days {
      font-size: 32px;
      font-weight: 800;
      color: var(--text);
      font-family: "IBM Plex Mono", monospace;
      line-height: 1;
    }

    .ring-label {
      font-size: 10.5px;
      color: var(--text-muted);
    }

    .deadline-info {
      text-align: center;
    }

    .deadline-title {
      font-size: 13px;
      font-weight: 600;
      color: var(--text);
    }

    .deadline-date {
      font-size: 12px;
      color: var(--text-muted);
      font-family: "IBM Plex Mono", monospace;
    }

    .syarat-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .syarat-item {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      font-size: 13px;
      color: var(--text);
    }

    .syarat-item svg {
      width: 16px;
      height: 16px;
      color: var(--success);
      flex-shrink: 0;
      margin-top: 1px;
    }

    /* ═══════════════════════════
   KONTAK
═══════════════════════════ */
    #kontak {
      background: var(--surface-2);
    }

    .kontak-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
      align-items: start;
    }

    .kontak-info {
      display: flex;
      flex-direction: column;
      gap: 28px;
    }

    .kontak-info-title {
      font-family: "Playfair Display", Georgia, serif;
      font-size: 28px;
      font-weight: 700;
      color: var(--text);
      line-height: 1.25;
    }

    .kontak-info-desc {
      font-size: 14px;
      color: var(--text-muted);
      line-height: 1.75;
    }

    .kontak-items {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .kontak-item {
      display: flex;
      align-items: flex-start;
      gap: 14px;
    }

    .kontak-icon {
      width: 44px;
      height: 44px;
      border-radius: 11px;
      background: var(--primary-light);
      color: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .kontak-icon svg {
      width: 21px;
      height: 21px;
    }

    .kontak-item-label {
      font-size: 11.5px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-muted);
      margin-bottom: 3px;
    }

    .kontak-item-value {
      font-size: 13.5px;
      font-weight: 600;
      color: var(--text);
    }

    .kontak-item-sub {
      font-size: 12px;
      color: var(--text-muted);
      font-family: "IBM Plex Mono", monospace;
    }

    /* Form */
    .kontak-form-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-lg);
      overflow: hidden;
    }

    .kontak-form-header {
      padding: 18px 22px;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .kontak-form-header svg {
      width: 18px;
      height: 18px;
      color: var(--primary);
    }

    .kontak-form-title {
      font-size: 15px;
      font-weight: 700;
      color: var(--text);
    }

    .kontak-form-body {
      padding: 22px;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .form-label {
      font-size: 13px;
      font-weight: 600;
      color: var(--text);
    }

    .form-control {
      width: 100%;
      padding: 10px 13px;
      border: 1.5px solid var(--border);
      border-radius: 9px;
      background: var(--surface);
      font-family: inherit;
      font-size: 13.5px;
      color: var(--text);
      outline: none;
      transition: border-color var(--transition), box-shadow var(--transition);
      appearance: none;
      resize: vertical;
    }

    .form-control:focus {
      border-color: var(--border-focus);
      box-shadow: 0 0 0 4px var(--primary-glow);
    }

    .form-control::placeholder {
      color: var(--text-light);
    }

    .btn-submit {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      justify-content: center;
      width: 100%;
      padding: 11px 22px;
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 9px;
      font-family: inherit;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 2px 12px rgba(79, 70, 229, 0.28);
      transition: background var(--transition), box-shadow var(--transition), transform 0.1s;
    }

    .btn-submit:hover {
      background: var(--primary-hover);
      box-shadow: 0 4px 18px rgba(79, 70, 229, 0.38);
    }

    .btn-submit:active {
      transform: scale(0.98);
    }

    .btn-submit svg {
      width: 17px;
      height: 17px;
    }

    /* ═══════════════════════════
   FOOTER
═══════════════════════════ */
    .site-footer {
      background: var(--sidebar-bg);
      color: #94a3b8;
      padding: 48px 28px 28px;
    }

    .footer-inner {
      max-width: 1200px;
      margin: 0 auto;
    }

    .footer-top {
      display: grid;
      grid-template-columns: 1.5fr 1fr 1fr;
      gap: 48px;
      padding-bottom: 40px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
      margin-bottom: 28px;
    }

    .footer-brand {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .footer-brand .nav-logo {
      box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);
    }

    .footer-brand-name {
      font-size: 15px;
      font-weight: 700;
      color: #fff;
      margin-top: 4px;
    }

    .footer-brand-desc {
      font-size: 13px;
      line-height: 1.7;
    }

    .footer-col-title {
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: #fff;
      margin-bottom: 16px;
    }

    .footer-links {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .footer-link {
      font-size: 13.5px;
      color: #64748b;
      text-decoration: none;
      transition: color var(--transition);
    }

    .footer-link:hover {
      color: #a5b4fc;
    }

    .footer-bottom {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }

    .footer-copy {
      font-size: 12.5px;
    }

    .footer-copy strong {
      color: #e2e8f0;
    }

    .footer-meta {
      font-family: "IBM Plex Mono", monospace;
      font-size: 11px;
      color: #475569;
    }

    /* ═══════════════════════════
   SCROLL-TO-TOP
═══════════════════════════ */
    .scroll-top {
      position: fixed;
      bottom: 28px;
      right: 28px;
      width: 44px;
      height: 44px;
      border-radius: 11px;
      background: var(--primary);
      color: #fff;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 16px rgba(79, 70, 229, 0.35);
      opacity: 0;
      transform: translateY(16px);
      transition: opacity 0.3s, transform 0.3s, background var(--transition);
      z-index: 900;
      pointer-events: none;
    }

    .scroll-top.is-visible {
      opacity: 1;
      transform: translateY(0);
      pointer-events: auto;
    }

    .scroll-top:hover {
      background: var(--primary-hover);
    }

    .scroll-top svg {
      width: 20px;
      height: 20px;
    }

    /* ═══════════════════════════
   DIVIDER
═══════════════════════════ */
    .wave-divider {
      overflow: hidden;
      line-height: 0;
      background: var(--sidebar-bg);
    }

    .wave-divider svg {
      display: block;
      width: 100%;
    }

    /* ═══════════════════════════
   RESPONSIVE
═══════════════════════════ */
    @media (max-width: 960px) {
      .hero-inner {
        grid-template-columns: 1fr;
        gap: 48px;
      }

      .gallery-grid {
        grid-template-columns: 1fr 1fr;
      }

      .gallery-grid .gallery-featured {
        grid-column: span 2;
      }

      .announce-grid {
        grid-template-columns: 1fr;
      }

      .kontak-grid {
        grid-template-columns: 1fr;
      }

      .footer-top {
        grid-template-columns: 1fr 1fr;
      }
    }

    @media (max-width: 640px) {
      .nav-links {
        display: none;
      }

      .nav-hamburger {
        display: flex;
      }

      .gallery-grid {
        grid-template-columns: 1fr;
      }

      .gallery-grid .gallery-featured {
        grid-column: span 1;
      }

      .hero-stats {
        gap: 16px;
      }

      .footer-top {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>

  <!-- ═══ NAVBAR ═══ -->
  <header>
    <nav class="navbar" id="navbar">
      <div class="nav-inner">
        <!-- Brand -->
        <a href="#beranda" class="nav-brand">
          <div class="nav-logo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9L3 21" />
            </svg>
          </div>
          <div class="nav-brand-text">
            <span class="nav-brand-name">PASKIBRA SEKOLAH</span>
            <span class="nav-brand-sub">Kabupaten Tangerang</span>
          </div>
        </a>

        <!-- Links -->
        <ul class="nav-links">
          <li><a href="#beranda" class="nav-link active" onclick="setActive(this)">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 9.75L12 3l9 6.75V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.75z" />
              </svg>
              Beranda
            </a></li>
          <li><a href="#galeri" class="nav-link" onclick="setActive(this)">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v13.5A1.5 1.5 0 0 0 3.75 21z" />
              </svg>
              Galeri Kegiatan
            </a></li>
          <li><a href="#pengumuman" class="nav-link" onclick="setActive(this)">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783M10.34 6.66c.253-.962.584-1.892.985-2.783M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
              </svg>
              Pengumuman
            </a></li>
          <li><a href="#kontak" class="nav-link" onclick="setActive(this)">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
              </svg>
              Kontak
            </a></li>
        </ul>

        <!-- Right -->
        <div class="nav-right">
          <a href="app.php" class="btn-login">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
            </svg>
            Login
          </a>
          <button class="nav-hamburger" id="hamburger" onclick="toggleMenu()">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="2" id="hamburger-icon">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
          </button>
        </div>
      </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
      <a href="#beranda" class="nav-link" onclick="setActive(this); closeMenu()">Beranda</a>
      <a href="#galeri" class="nav-link" onclick="setActive(this); closeMenu()">Galeri Kegiatan</a>
      <a href="#pengumuman" class="nav-link" onclick="setActive(this); closeMenu()">Pengumuman</a>
      <a href="#kontak" class="nav-link" onclick="setActive(this); closeMenu()">Kontak</a>
      <a href="#login" class="btn-login" onclick="closeMenu()">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
        </svg>
        Login
      </a>
    </div>
  </header>

  <!-- ═══ HERO ═══ -->
  <section id="beranda" class="hero">
    <div class="hero-bg">
      <div class="hero-grid"></div>
      <div class="hero-glow-1"></div>
      <div class="hero-glow-2"></div>
      <div class="hero-float"></div>
      <div class="hero-float"></div>
      <div class="hero-float"></div>
    </div>

    <div class="hero-inner">
      <!-- Left -->
      <div class="hero-left">
        <div class="hero-badge">
          <div class="hero-badge-dot"></div>
          Seleksi Terbuka 2025
        </div>

        <h1 class="hero-title">
          Seleksi <span>Paskibra</span><br />Sekolah Kabupaten<br />Tangerang
        </h1>

        <p class="hero-desc">
          Bergabunglah dalam barisan kehormatan pengibaran bendera. Kami mencari putra-putri terbaik sekolah yang siap
          mengabdi dengan bangga, disiplin, dan semangat kebangsaan.
        </p>

        <div class="hero-actions">
          <a href="#pengumuman" class="btn-hero-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l6-6m0 0l-6-6m6 6H9a6 6 0 0 0 0 12h3" />
            </svg>
            Daftar Sekarang
          </a>
          <a href="#galeri" class="btn-hero-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909" />
            </svg>
            Lihat Galeri
          </a>
        </div>

        <div class="hero-stats">
          <div class="hero-stat">
            <span class="hero-stat-val">48</span>
            <span class="hero-stat-lbl">Anggota Aktif</span>
          </div>
          <div class="hero-stat-div"></div>
          <div class="hero-stat">
            <span class="hero-stat-val">12</span>
            <span class="hero-stat-lbl">Sekolah Peserta</span>
          </div>
          <div class="hero-stat-div"></div>
          <div class="hero-stat">
            <span class="hero-stat-val">7</span>
            <span class="hero-stat-lbl">Tahap Seleksi</span>
          </div>
        </div>
      </div>

      <!-- Right card -->
      <div class="hero-card-wrap">
        <div class="hero-card">
          <div class="hero-card-header">
            <div class="hero-card-icon">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
              </svg>
            </div>
            <div>
              <div class="hero-card-title">Timeline Seleksi</div>
              <!-- <div class="hero-card-sub">Tahun Ajaran 2025/2026</div> -->
            </div>
          </div>
          <div class="timeline">
            <div class="timeline-item">
              <div class="timeline-left">
                <div class="timeline-dot done"></div>
                <div class="timeline-line"></div>
              </div>
              <div class="timeline-content">
                <div class="timeline-date">Soon</div>
                <div class="timeline-title">Pendaftaran Online</div>
                <span class="timeline-badge">Belum Dimulai</span>
              </div>
            </div>
            <div class="timeline-item">
              <div class="timeline-left">
                <div class="timeline-dot"
                  style="background:var(--warning);border-color:rgba(245,158,11,0.3);box-shadow:0 0 8px rgba(245,158,11,0.4)">
                </div>
                <div class="timeline-line"></div>
              </div>
              <div class="timeline-content">
                <div class="timeline-date">Soon</div>
                <div class="timeline-title">Tes Administrasi & Fisik</div>
                <span class="timeline-badge active">Belum Dimulai</span>
              </div>
            </div>
            <div class="timeline-item">
              <div class="timeline-left">
                <div class="timeline-dot upcoming"></div>
                <div class="timeline-line"></div>
              </div>
              <div class="timeline-content">
                <div class="timeline-date">Soon</div>
                <div class="timeline-title">Tes Wawasan Kebangsaan</div>
                <span class="timeline-badge upcoming">Belum Dimulai</span>
              </div>
            </div>
            <div class="timeline-item">
              <div class="timeline-left">
                <div class="timeline-dot upcoming"></div>
                <div class="timeline-line"></div>
              </div>
              <div class="timeline-content">
                <div class="timeline-date">Soon</div>
                <div class="timeline-title">Pengumuman Hasil Akhir</div>
                <span class="timeline-badge upcoming">Belum Dimulai</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Wave divider -->
  <div class="wave-divider">
    <svg viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="height:60px">
      <path d="M0,0 C360,60 1080,0 1440,60 L1440,0 L0,0 Z" fill="#f1f5f9" />
    </svg>
  </div>

  <!-- ═══ GALERI ═══ -->
  <section id="galeri" class="section">
    <div class="section-inner">
      <div class="section-header">
        <p class="section-eyebrow">Dokumentasi Kegiatan</p>
        <h2 class="section-title">Galeri Kegiatan Paskibra</h2>
        <p class="section-desc">Rekam jejak perjalanan dan kegiatan kami dalam mendidik calon anggota paskibra terbaik
          Kabupaten Tangerang.</p>
      </div>

      <div class="gallery-grid">
        <!-- Featured -->
        <div class="gallery-item gallery-featured gp-1">
          <div class="gp-overlay-circle" style="width:200px;height:200px;top:-60px;right:-40px;"></div>
          <div class="gp-overlay-circle" style="width:120px;height:120px;bottom:-30px;left:40px;"></div>
          <div
            style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;color:rgba(255,255,255,0.5)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="1.5" style="width:56px;height:56px;opacity:0.6">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 3h2.25a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 0 9.75 21h6.75A2.25 2.25 0 0 0 18.75 18.75v-1.5m-12-9H3.75a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 3.75 21h1.5" />
            </svg>
            <span style="font-size:14px;font-weight:600">Upacara Pengibaran Bendera</span>
            <span style="font-family:'IBM Plex Mono',monospace;font-size:11px;opacity:0.6">17 Agustus 2024</span>
          </div>
          <div class="gallery-overlay">
            <div class="gallery-overlay-title">Upacara Pengibaran Bendera Merah Putih</div>
            <div class="gallery-overlay-meta">Lapangan Utama · 17 Agustus 2024</div>
          </div>
        </div>

        <!-- Item 2 -->
        <div class="gallery-item gp-2">
          <div
            style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;color:rgba(255,255,255,0.5)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="1.5" style="width:36px;height:36px;opacity:0.6">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0z" />
            </svg>
            <span style="font-size:12px;font-weight:600">Latihan Formasi</span>
          </div>
          <div class="gallery-overlay">
            <div class="gallery-overlay-title">Latihan Formasi Baris Berbaris</div>
            <div class="gallery-overlay-meta">Lapangan · Maret 2025</div>
          </div>
        </div>

        <!-- Item 3 -->
        <div class="gallery-item gp-3">
          <div
            style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;color:rgba(255,255,255,0.5)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="1.5" style="width:36px;height:36px;opacity:0.6">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.040.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5z" />
            </svg>
            <span style="font-size:12px;font-weight:600">Pelantikan Anggota</span>
          </div>
          <div class="gallery-overlay">
            <div class="gallery-overlay-title">Pelantikan Anggota Baru</div>
            <div class="gallery-overlay-meta">Aula Sekolah · Januari 2025</div>
          </div>
        </div>

        <!-- Item 4 -->
        <div class="gallery-item gp-4">
          <div
            style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;color:rgba(255,255,255,0.5)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="1.5" style="width:36px;height:36px;opacity:0.6">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
            </svg>
            <span style="font-size:12px;font-weight:600">Bimtek Instruktur</span>
          </div>
          <div class="gallery-overlay">
            <div class="gallery-overlay-title">Bimbingan Teknis Pelatih</div>
            <div class="gallery-overlay-meta">Gedung Dinas · April 2025</div>
          </div>
        </div>

        <!-- Item 5 -->
        <div class="gallery-item gp-6">
          <div
            style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;color:rgba(255,255,255,0.5)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="1.5" style="width:36px;height:36px;opacity:0.6">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904" />
            </svg>
            <span style="font-size:12px;font-weight:600">Seleksi Fisik 2024</span>
          </div>
          <div class="gallery-overlay">
            <div class="gallery-overlay-title">Seleksi Fisik & Kesegaran Jasmani</div>
            <div class="gallery-overlay-meta">GOR · Mei 2024</div>
          </div>
        </div>

        <!-- Item 6 -->
        <div class="gallery-item gp-7">
          <div
            style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;color:rgba(255,255,255,0.5)">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="1.5" style="width:36px;height:36px;opacity:0.6">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
            </svg>
            <span style="font-size:12px;font-weight:600">Materi Wawasan Bangsa</span>
          </div>
          <div class="gallery-overlay">
            <div class="gallery-overlay-title">Materi Wawasan Kebangsaan</div>
            <div class="gallery-overlay-meta">Aula · Februari 2025</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══ PENGUMUMAN ═══ -->
  <section id="pengumuman" class="section">
    <div class="section-inner">
      <div class="section-header">
        <p class="section-eyebrow">Informasi Terkini</p>
        <h2 class="section-title">Pengumuman Resmi</h2>
        <p class="section-desc">Pantau terus informasi dan pengumuman resmi terkait tahapan seleksi Paskibra Sekolah
          Kabupaten Tangerang.</p>
      </div>

      <div class="announce-grid">
        <!-- Main list -->
        <div class="announce-list">

          <div class="announce-card important">
            <div class="announce-icon primary">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
              </svg>
            </div>
            <div class="announce-content">
              <div class="announce-title">📋 Pengumuman Hasil Seleksi Administrasi Tahap I</div>
              <div class="announce-excerpt">Peserta yang dinyatakan lolos seleksi administrasi tahap pertama diharapkan
                melakukan konfirmasi kehadiran untuk tes fisik pada tanggal 20 Mei 2025. Daftar nama dapat dilihat di
                lampiran berikut.</div>
              <div class="announce-meta">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25" />
                </svg>
                15 Mei 2025
                <span class="announce-tag tag-penting">PENTING</span>
              </div>
            </div>
          </div>

          <div class="announce-card warning">
            <div class="announce-icon warning">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008z" />
              </svg>
            </div>
            <div class="announce-content">
              <div class="announce-title">📅 Jadwal Tes Fisik & Kesegaran Jasmani</div>
              <div class="announce-excerpt">Tes fisik akan dilaksanakan di Lapangan GOR Tangerang pada 20–22 Mei 2025.
                Peserta diwajibkan hadir 30 menit sebelum waktu yang ditentukan dengan membawa perlengkapan olahraga
                lengkap.</div>
              <div class="announce-meta">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25" />
                </svg>
                12 Mei 2025
                <span class="announce-tag tag-jadwal">JADWAL</span>
              </div>
            </div>
          </div>

          <div class="announce-card success">
            <div class="announce-icon success">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
              </svg>
            </div>
            <div class="announce-content">
              <div class="announce-title">✅ Pendaftaran Online Resmi Ditutup</div>
              <div class="announce-excerpt">Pendaftaran online Seleksi Paskibra Sekolah 2025 telah resmi ditutup pada 15
                Mei 2025. Sebanyak 312 pendaftar telah berhasil mendaftar dari 12 sekolah di Kabupaten Tangerang.</div>
              <div class="announce-meta">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25" />
                </svg>
                15 Mei 2025
                <span class="announce-tag tag-hasil">HASIL</span>
              </div>
            </div>
          </div>

          <div class="announce-card important">
            <div class="announce-icon primary">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9z" />
              </svg>
            </div>
            <div class="announce-content">
              <div class="announce-title">📄 Persyaratan Dokumen Wajib Peserta</div>
              <div class="announce-excerpt">Seluruh peserta wajib membawa berkas persyaratan lengkap saat hari seleksi,
                termasuk fotokopi KTP/kartu pelajar, surat keterangan sehat, pas foto terbaru, dan surat rekomendasi
                dari kepala sekolah.</div>
              <div class="announce-meta">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25" />
                </svg>
                08 Mei 2025
                <span class="announce-tag tag-penting">PENTING</span>
              </div>
            </div>
          </div>

        </div>

        <!-- Sidebar -->
        <div class="announce-sidebar">
          <!-- Deadline ring -->
          <div class="info-card">
            <div class="info-card-title">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <circle cx="12" cy="12" r="9" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
              </svg>
              Hitung Mundur
            </div>
            <div class="deadline-ring">
              <div class="ring-wrap">
                <svg class="ring-svg" viewBox="0 0 120 120">
                  <circle class="ring-bg" cx="60" cy="60" r="50" />
                  <circle class="ring-fg" cx="60" cy="60" r="50" id="ringFg" />
                </svg>
                <div class="ring-center">
                  <div class="ring-days" id="daysLeft">—</div>
                  <div class="ring-label">Hari Lagi</div>
                </div>
              </div>
              <div class="deadline-info">
                <div class="deadline-title">Tes Wawasan Kebangsaan</div>
                <div class="deadline-date">28 Mei 2025</div>
              </div>
            </div>
          </div>

          <!-- Syarat -->
          <div class="info-card">
            <div class="info-card-title">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
              </svg>
              Syarat Peserta
            </div>
            <ul class="syarat-list">
              <li class="syarat-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                </svg>
                Siswa aktif kelas X atau XI
              </li>
              <li class="syarat-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                </svg>
                Tinggi badan min. 165 cm (L) / 160 cm (P)
              </li>
              <li class="syarat-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                </svg>
                Nilai rata-rata minimal 75
              </li>
              <li class="syarat-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                </svg>
                Sehat jasmani & rohani
              </li>
              <li class="syarat-item">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                </svg>
                Ijin orang tua / wali
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══ KONTAK ═══ -->
  <section id="kontak" class="section">
    <div class="section-inner">
      <div class="section-header">
        <p class="section-eyebrow">Hubungi Kami</p>
        <h2 class="section-title">Kontak & Informasi</h2>
        <p class="section-desc">Punya pertanyaan seputar seleksi? Kami siap membantu. Hubungi panitia melalui saluran
          resmi di bawah ini.</p>
      </div>

      <div class="kontak-grid">
        <div class="kontak-info">
          <h3 class="kontak-info-title">Panitia Seleksi Paskibra<br />Kabupaten Tangerang</h3>
          <p class="kontak-info-desc">Proses seleksi diselenggarakan oleh Tim Panitia Paskibra Dinas Pendidikan
            Kabupaten Tangerang. Untuk pertanyaan resmi, gunakan saluran komunikasi berikut pada hari dan jam kerja.</p>

          <div class="kontak-items">
            <div class="kontak-item">
              <div class="kontak-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0z" />
                </svg>
              </div>
              <div>
                <div class="kontak-item-label">Alamat</div>
                <div class="kontak-item-value">Dinas Pendidikan Kab. Tangerang</div>
                <div class="kontak-item-sub">Jl. H. Abdul Hamid No. 1, Tigaraksa</div>
              </div>
            </div>
            <div class="kontak-item">
              <div class="kontak-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25z" />
                </svg>
              </div>
              <div>
                <div class="kontak-item-label">Telepon</div>
                <div class="kontak-item-value">(021) 5992-8810</div>
                <div class="kontak-item-sub">Senin–Jumat, 08.00–15.00 WIB</div>
              </div>
            </div>
            <div class="kontak-item">
              <div class="kontak-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
              </div>
              <div>
                <div class="kontak-item-label">Email</div>
                <div class="kontak-item-value">paskibra@disdik.tangerangkab.go.id</div>
                <div class="kontak-item-sub">Respon dalam 1×24 jam kerja</div>
              </div>
            </div>
            <div class="kontak-item">
              <div class="kontak-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                </svg>
              </div>
              <div>
                <div class="kontak-item-label">WhatsApp Panitia</div>
                <div class="kontak-item-value">+62 812-9988-4567</div>
                <div class="kontak-item-sub">Khusus pertanyaan seleksi</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Form -->
        <div class="kontak-form-card">
          <div class="kontak-form-header">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M21.75 9v.906a2.25 2.25 0 0 1-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 0 0 1.183 1.981l6.478 3.488m8.839 2.51-4.66-2.51m0 0-1.023-.55a2.25 2.25 0 0 0-2.134 0l-1.022.55m0 0-4.661 2.51m16.5 1.615a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V8.844a2.25 2.25 0 0 1 1.183-1.981l7.5-4.039a2.25 2.25 0 0 1 2.134 0l7.5 4.039a2.25 2.25 0 0 1 1.183 1.98V19.5z" />
            </svg>
            <div class="kontak-form-title">Kirim Pesan</div>
          </div>
          <div class="kontak-form-body">
            <div class="form-group">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" class="form-control" placeholder="Masukkan nama lengkap Anda" />
            </div>
            <div class="form-group">
              <label class="form-label">Asal Sekolah</label>
              <input type="text" class="form-control" placeholder="Nama sekolah Anda" />
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" placeholder="email@contoh.com" />
            </div>
            <div class="form-group">
              <label class="form-label">Pesan / Pertanyaan</label>
              <textarea class="form-control" rows="4" placeholder="Tulis pertanyaan atau pesan Anda..."></textarea>
            </div>
            <button class="btn-submit" onclick="handleSubmit()">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12zm0 0h7.5" />
              </svg>
              Kirim Pesan
            </button>
            <div id="submitMsg"
              style="display:none;padding:12px 14px;border-radius:9px;background:#ecfdf5;border:1.5px solid #a7f3d0;color:#065f46;font-size:13px;font-weight:600;text-align:center;">
              ✅ Pesan berhasil dikirim! Kami akan segera menghubungi Anda.
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══ FOOTER ═══ -->
  <footer class="site-footer">
    <div class="footer-inner">
      <div class="footer-top">
        <div class="footer-brand">
          <div class="nav-logo" style="width:40px;height:40px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="2" style="width:20px;height:20px;color:#fff;position:relative;z-index:1">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9L3 21" />
            </svg>
          </div>
          <div class="footer-brand-name">Paskibra Sekolah Kabupaten Tangerang</div>
          <p class="footer-brand-desc">Program resmi pengembangan Pasukan Pengibar Bendera Sekolah di lingkungan Dinas
            Pendidikan Kabupaten Tangerang.</p>
        </div>
        <div>
          <div class="footer-col-title">Navigasi</div>
          <ul class="footer-links">
            <li><a href="#beranda" class="footer-link">Beranda</a></li>
            <li><a href="#galeri" class="footer-link">Galeri Kegiatan</a></li>
            <li><a href="#pengumuman" class="footer-link">Pengumuman</a></li>
            <li><a href="#kontak" class="footer-link">Kontak</a></li>
          </ul>
        </div>
        <div>
          <div class="footer-col-title">Informasi</div>
          <ul class="footer-links">
            <li><a href="#" class="footer-link">Panduan Pendaftaran</a></li>
            <li><a href="#" class="footer-link">Materi Seleksi</a></li>
            <li><a href="#" class="footer-link">FAQ</a></li>
            <li><a href="#login" class="footer-link">Portal Peserta</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <div class="footer-copy">&copy; 2025 <strong>Paskibra Sekolah Kabupaten Tangerang</strong>. Semua hak
          dilindungi.</div>
        <div class="footer-meta">v2.0.0 · Dinas Pendidikan Kab. Tangerang</div>
      </div>
    </div>
  </footer>

  <!-- Scroll to top -->
  <button class="scroll-top" id="scrollTop" onclick="window.scrollTo({top:0,behavior:'smooth'})"
    title="Kembali ke atas">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
      <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
    </svg>
  </button>

  <script>
    // ── Nav active ──
    function setActive(el) {
      document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
      el.classList.add('active');
    }

    // ── Mobile menu ──
    function toggleMenu() {
      const m = document.getElementById('mobileMenu');
      m.classList.toggle('is-open');
    }

    function closeMenu() {
      document.getElementById('mobileMenu').classList.remove('is-open');
    }

    // ── Scroll top ──
    window.addEventListener('scroll', () => {
      const st = document.getElementById('scrollTop');
      st.classList.toggle('is-visible', window.scrollY > 400);
    });

    // ── Countdown ──
    (function () {
      const target = new Date('2025-05-28T00:00:00');
      const now = new Date();
      const diff = Math.ceil((target - now) / 86400000);
      const days = Math.max(0, diff);
      document.getElementById('daysLeft').textContent = days;
      // Update ring
      const totalDays = 30;
      const pct = Math.max(0, Math.min(1, days / totalDays));
      const circumference = 314;
      const offset = circumference * (1 - pct);
      document.getElementById('ringFg').style.strokeDashoffset = offset;
    })();

    // ── Form submit ──
    function handleSubmit() {
      const msg = document.getElementById('submitMsg');
      msg.style.display = 'block';
      setTimeout(() => {
        msg.style.display = 'none';
      }, 5000);
    }

    // ── Smooth nav highlight on scroll ──
    const sections = ['beranda', 'galeri', 'pengumuman', 'kontak'];
    const navLinks = document.querySelectorAll('.nav-links .nav-link');
    window.addEventListener('scroll', () => {
      let current = '';
      sections.forEach(id => {
        const el = document.getElementById(id);
        if (el && window.scrollY >= el.offsetTop - 100) current = id;
      });
      navLinks.forEach(l => {
        l.classList.remove('active');
        if (l.getAttribute('href') === '#' + current) l.classList.add('active');
      });
    });
  </script>
</body>

</html>