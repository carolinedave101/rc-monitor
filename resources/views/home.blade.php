@extends('layouts.app')

@section('title', 'ROYALTRICO — Consent-Based Device & Family Monitoring')

@section('content')
<style>
    html { scroll-behavior: smooth; }
    .marketing-nav { background: rgba(255,255,255,.85); backdrop-filter: blur(8px); border-bottom: 1px solid #e6e9f2; }
    .marketing-nav .navbar-brand { font-weight: 800; letter-spacing: -.02em; }
    .hero-card {
        background: linear-gradient(135deg, #0d3bbf 0%, #1b6ff5 55%, #3b82f6 100%);
        border-radius: 1.5rem;
        overflow: hidden;
        position: relative;
    }
    .hero-card::before {
        content: "";
        position: absolute; inset: 0;
        background-image: radial-gradient(rgba(255,255,255,.14) 1px, transparent 1px);
        background-size: 22px 22px;
    }
    .hero-card .btn-light { border: 0; }
    .phone-frame {
        width: 220px; aspect-ratio: 9/19; border-radius: 2rem;
        background: #0b1220; padding: .55rem;
        box-shadow: 0 1.5rem 3.5rem rgba(0,0,0,.45);
        transform: rotate(3deg);
    }
    .phone-screen {
        border-radius: 1.55rem; height: 100%; overflow: hidden;
        background: #ffffff; display: flex; flex-direction: column;
    }
    .phone-notch { height: 1.1rem; display: flex; justify-content: center; align-items: center; }
    .phone-notch span { width: 4rem; height: .5rem; background: #0b1220; border-radius: 999px; }
    .feature-card {
        border: 0; border-radius: 1rem; height: 100%;
        box-shadow: 0 .25rem 1rem rgba(13, 110, 253, .07);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .feature-card:hover { transform: translateY(-4px); box-shadow: 0 .75rem 1.75rem rgba(13, 110, 253, .14); }
    .feature-icon {
        width: 2.9rem; height: 2.9rem; border-radius: .85rem;
        display: flex; align-items: center; justify-content: center; font-size: 1.35rem;
    }
    .step-num {
        width: 2.6rem; height: 2.6rem; border-radius: 50%; display: flex;
        align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem;
    }
    .t-card { border: 0; border-radius: 1rem; box-shadow: 0 .25rem 1rem rgba(13,110,253,.06); }
    .t-card .bi-star-fill { color: #ffc107; }
    .icon-chip {
        display: inline-flex; align-items: center; gap: .35rem;
        background: rgba(255,255,255,.16); color: #fff; border: 1px solid rgba(255,255,255,.28);
        padding: .3rem .75rem; border-radius: 999px; font-size: .8rem; font-weight: 600;
    }
    .plan-card { border: 0; border-radius: 1.25rem; box-shadow: 0 .5rem 1.5rem rgba(13,110,253,.12); }
    .os-badge {
        display: inline-flex; align-items: center; gap: .5rem;
        border: 1px solid #e2e7f2; border-radius: .9rem; padding: .9rem 1.2rem;
        background: #fff; font-weight: 600;
    }
    .footer-note { font-size: .8rem; color: #8a93a6; }
</style>

<!-- Marketing navigation -->
<nav class="marketing-nav sticky-top">
    <div class="container d-flex align-items-center py-3">
        <a class="navbar-brand me-auto text-primary" href="#top">
            <span class="d-inline-flex align-items-center justify-content-center overflow-hidden rounded-3 me-2 shadow-sm" style="width:100px;height:100px;background:linear-gradient(135deg,#0d3bbf,#1b6ff5);">
                <img src="{{ asset('images/logo-transparent.png') }}" alt="ROYALTRICO logo" height="100" width="100">
            </span>
            ROYALTRICO
        </a>
        <ul class="nav d-none d-lg-flex gap-4 align-items-center">
            <li><a class="nav-link text-dark fw-medium" href="#features">Features</a></li>
            <li><a class="nav-link text-dark fw-medium" href="#compatibility">Compatibility</a></li>
            <li><a class="nav-link text-dark fw-medium" href="#how-it-works">How it works</a></li>
            <li><a class="nav-link text-dark fw-medium" href="#support">Support</a></li>
        </ul>
        <div class="d-flex gap-2 ms-4">
            <a href="{{ route('login') }}" class="btn btn-outline-primary rounded-pill px-4">Login</a>
            <a href="{{ route('register') }}" class="btn btn-primary rounded-pill px-4">Get started</a>
        </div>
    </div>
</nav>

<!-- Hero -->
<section id="top" class="mt-4">
    <div class="hero-card text-white">
        <div class="container position-relative py-5">
            <div class="row align-items-center py-lg-4">
                <div class="col-lg-7">
                    <span class="icon-chip mb-3"><i class="bi bi-shield-lock-fill"></i> Consent-first by design</span>
                    <h1 class="display-5 fw-bold mb-3">
                        Family &amp; workplace safety,<br class="d-none d-md-block">
                        built on consent.
                    </h1>
                    <p class="lead mb-4 opacity-75" style="max-width: 34rem;">
                        Enroll devices you own — or devices whose owner has given permission — and review
                        calls, texts, locations, app activity and more from one clean, live dashboard.
                        Transparent. Auditable. Responsible.
                    </p>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="icon-chip"><i class="bi bi-chat-dots-fill"></i> SMS</span>
                        <span class="icon-chip"><i class="bi bi-whatsapp"></i> WhatsApp</span>
                        <span class="icon-chip"><i class="bi bi-telephone-fill"></i> Calls</span>
                        <span class="icon-chip"><i class="bi bi-geo-alt-fill"></i> GPS</span>
                        <span class="icon-chip"><i class="bi bi-facebook"></i> Facebook</span>
                        <span class="icon-chip"><i class="bi bi-instagram"></i> Instagram</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('register') }}" class="btn btn-light btn-lg rounded-pill px-4 fw-semibold">
                            Start free <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                        <a href="#how-it-works" class="btn btn-outline-light btn-lg rounded-pill px-4">See how it works</a>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-flex justify-content-center py-4">
                    <div class="phone-frame">
                        <div class="phone-screen">
                            <div class="phone-notch"><span></span></div>
                            <div class="p-2 pb-1 d-flex justify-content-between align-items-center">
                                <span class="fw-bold" style="font-size:.72rem; color:#0b1220;">ROYALTRICO Live</span>
                                <span class="badge text-bg-success" style="font-size:.55rem;">● active</span>
                            </div>
                            <div class="px-2 pb-1">
                                <div class="bg-primary rounded-2 p-1 px-2 text-white d-flex align-items-center gap-1" style="font-size:.6rem;">
                                    <i class="bi bi-geo-alt-fill"></i> Home zone — inside
                                </div>
                            </div>
                            <div class="px-2 d-flex flex-column gap-1" style="font-size:.62rem;">
                                <div class="d-flex justify-content-between border-bottom pb-1"><span class="text-secondary">Mom's phone</span><span class="fw-semibold">Active</span></div>
                                <div class="d-flex justify-content-between border-bottom pb-1"><span class="text-secondary">Kid's tablet</span><span class="fw-semibold">Active</span></div>
                                <div class="d-flex justify-content-between border-bottom pb-1"><span class="text-secondary">Work phone</span><span class="fw-semibold text-warning">Pending</span></div>
                                <div class="d-flex justify-content-between pb-1"><span class="text-secondary">Family laptop</span><span class="fw-semibold text-danger">Suspended</span></div>
                            </div>
                            <div class="px-2 py-2 bg-light mt-auto">
                                <div class="rounded-2 bg-white border p-2">
                                    <div class="text-secondary mb-1" style="font-size:.58rem;">Latest alert</div>
                                    <div class="fw-semibold" style="font-size:.62rem; color:#0b1220;">Geofence: school zone exit</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trust strip -->
    <div class="container">
        <div class="row g-3 my-4">
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center gap-2 bg-white border rounded-4 px-3 py-3 h-100">
                    <i class="bi bi-check-circle-fill text-success fs-4"></i>
                    <div class="small"><div class="fw-semibold">Owner-authorized</div><div class="text-secondary">Consent recorded per device</div></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center gap-2 bg-white border rounded-4 px-3 py-3 h-100">
                    <i class="bi bi-cash-coin text-primary fs-4"></i>
                    <div class="small"><div class="fw-semibold">No monthly fees</div><div class="text-secondary">One simple plan</div></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center gap-2 bg-white border rounded-4 px-3 py-3 h-100">
                    <i class="bi bi-rocket-takeoff text-danger fs-4"></i>
                    <div class="small"><div class="fw-semibold">Installs in minutes</div><div class="text-secondary">Secure link or QR</div></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center gap-2 bg-white border rounded-4 px-3 py-3 h-100">
                    <i class="bi bi-shield-check text-warning fs-4"></i>
                    <div class="small"><div class="fw-semibold">No hidden cost</div><div class="text-secondary">Transparent pricing</div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How it works -->
<section id="how-it-works" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">How it works</h2>
            <p class="text-secondary mx-auto" style="max-width: 36rem;">
                Every device is enrolled with explicit, recorded consent from the device owner.
                There is no stealth mode, no hidden access — just clear, responsible monitoring.
            </p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card stat-card h-100 text-center p-4">
                    <div class="step-num bg-primary text-white mx-auto mb-3">1</div>
                    <h5 class="fw-semibold">Enroll &amp; record consent</h5>
                    <p class="text-secondary mb-0">
                        Create your account and register the device — your own phone, your child's device,
                        or a company-issued device. Consent is confirmed and stored as part of the record.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card h-100 text-center p-4">
                    <div class="step-num bg-primary text-white mx-auto mb-3">2</div>
                    <h5 class="fw-semibold">Install the visible agent</h5>
                    <p class="text-secondary mb-0">
                        The device owner installs the ROYALTRICO agent from the App Store or Google Play,
                        or via the secure link we provide — no rooting, no jailbreaking, nothing hidden.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card h-100 text-center p-4">
                    <div class="step-num bg-primary text-white mx-auto mb-3">3</div>
                    <h5 class="fw-semibold">Monitor from the live dashboard</h5>
                    <p class="text-secondary mb-0">
                        Review calls, messages, locations and app activity in real time from any phone,
                        tablet or computer — with alerts you control and can pause any time.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section id="features" class="py-5 bg-white rounded-4 mb-4">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Everything you need, on devices you're authorized to monitor</h2>
            <p class="text-secondary mx-auto" style="max-width: 36rem;">
                One dashboard for activity across your family's or team's enrolled devices.
            </p>
        </div>
        <div class="row g-4">
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-primary-subtle text-primary mb-3"><i class="bi bi-telephone"></i></div>
                    <h6 class="fw-semibold mb-1">Call log</h6>
                    <p class="text-secondary small mb-0">Record and review call details on enrolled devices.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-success-subtle text-success mb-3"><i class="bi bi-chat-dots"></i></div>
                    <h6 class="fw-semibold mb-1">Text messages</h6>
                    <p class="text-secondary small mb-0">Sent and received SMS activity, kept in one place.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-danger-subtle text-danger mb-3"><i class="bi bi-geo-alt"></i></div>
                    <h6 class="fw-semibold mb-1">GPS location logs</h6>
                    <p class="text-secondary small mb-0">Location history and geofence alerts for enrolled devices.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-warning-subtle text-warning-emphasis mb-3"><i class="bi bi-envelope"></i></div>
                    <h6 class="fw-semibold mb-1">Email activity</h6>
                    <p class="text-secondary small mb-0">Overview of sent and received email on company devices.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-info-subtle text-info-emphasis mb-3"><i class="bi bi-globe2"></i></div>
                    <h6 class="fw-semibold mb-1">Browser history</h6>
                    <p class="text-secondary small mb-0">Visited-site overview to help keep browsing safe.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-secondary-subtle text-secondary mb-3"><i class="bi bi-images"></i></div>
                    <h6 class="fw-semibold mb-1">Photo log</h6>
                    <p class="text-secondary small mb-0">See photos stored on your enrolled devices.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-primary-subtle text-primary mb-3"><i class="bi bi-camera-video"></i></div>
                    <h6 class="fw-semibold mb-1">Videos log</h6>
                    <p class="text-secondary small mb-0">See videos stored on your enrolled devices.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-success-subtle text-success mb-3"><i class="bi bi-whatsapp"></i></div>
                    <h6 class="fw-semibold mb-1">WhatsApp activity</h6>
                    <p class="text-secondary small mb-0">App activity summaries for WhatsApp on enrolled devices.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-info-subtle text-info-emphasis mb-3"><i class="bi bi-person-lines-fill"></i></div>
                    <h6 class="fw-semibold mb-1">Contact list</h6>
                    <p class="text-secondary small mb-0">View contacts saved on enrolled devices.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-danger-subtle text-danger mb-3"><i class="bi bi-heart-pulse"></i></div>
                    <h6 class="fw-semibold mb-1">Device diagnostics</h6>
                    <p class="text-secondary small mb-0">Monitor battery, storage and device health status.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-warning-subtle text-warning-emphasis mb-3"><i class="bi bi-broadcast-pin"></i></div>
                    <h6 class="fw-semibold mb-1">Family location sharing</h6>
                    <p class="text-secondary small mb-0">Periodic, transparent location updates from enrolled devices.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-primary-subtle text-primary mb-3"><i class="bi bi-phone"></i></div>
                    <h6 class="fw-semibold mb-1">Monitor from mobile</h6>
                    <p class="text-secondary small mb-0">Full dashboard access from your phone, tablet or computer.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-success-subtle text-success mb-3"><i class="bi bi-facebook"></i></div>
                    <h6 class="fw-semibold mb-1">Facebook activity</h6>
                    <p class="text-secondary small mb-0">Summaries of Facebook use on enrolled devices.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-info-subtle text-info-emphasis mb-3"><i class="bi bi-skype"></i></div>
                    <h6 class="fw-semibold mb-1">Skype activity</h6>
                    <p class="text-secondary small mb-0">Summaries of Skype use on enrolled devices.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-danger-subtle text-danger mb-3"><i class="bi bi-instagram"></i></div>
                    <h6 class="fw-semibold mb-1">Instagram activity</h6>
                    <p class="text-secondary small mb-0">Summaries of Instagram use on enrolled devices.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-warning-subtle text-warning-emphasis mb-3"><i class="bi bi-speedometer2"></i></div>
                    <h6 class="fw-semibold mb-1">Live control panel</h6>
                    <p class="text-secondary small mb-0">Log in from any device to see everything at a glance.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-primary-subtle text-primary mb-3"><i class="bi bi-lock-fill"></i></div>
                    <h6 class="fw-semibold mb-1">Lock a lost device</h6>
                    <p class="text-secondary small mb-0">Remotely lock a lost or stolen enrolled device.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-success-subtle text-success mb-3"><i class="bi bi-journal-text"></i></div>
                    <h6 class="fw-semibold mb-1">Notes overview</h6>
                    <p class="text-secondary small mb-0">See notes stored on enrolled devices.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-info-subtle text-info-emphasis mb-3"><i class="bi bi-calendar-event"></i></div>
                    <h6 class="fw-semibold mb-1">Calendar overview</h6>
                    <p class="text-secondary small mb-0">See calendar entries on enrolled devices.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-danger-subtle text-danger mb-3"><i class="bi bi-wechat"></i></div>
                    <h6 class="fw-semibold mb-1">WeChat activity</h6>
                    <p class="text-secondary small mb-0">Summaries of WeChat use on enrolled devices.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-warning-subtle text-warning-emphasis mb-3"><i class="bi bi-chat-square-text"></i></div>
                    <h6 class="fw-semibold mb-1">Line activity</h6>
                    <p class="text-secondary small mb-0">Summaries of Line use on enrolled devices.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card feature-card p-4">
                    <div class="feature-icon bg-primary-subtle text-primary mb-3"><i class="bi bi-twitter"></i></div>
                    <h6 class="fw-semibold mb-1">X (Twitter) activity</h6>
                    <p class="text-secondary small mb-0">Summaries of X use on enrolled devices.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Compatibility -->
<section id="compatibility" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Compatibility</h2>
            <p class="text-secondary mx-auto" style="max-width: 36rem;">
                Works with the devices your family and team already use — no rooting, no jailbreaking.
            </p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="os-badge w-100 justify-content-center">
                    <i class="bi bi-android2 text-success fs-3"></i>
                    <div><div class="fw-bold">Android</div><small class="text-secondary">Android 8 and above</small></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="os-badge w-100 justify-content-center">
                    <i class="bi bi-apple text-dark fs-3"></i>
                    <div><div class="fw-bold">iPhone</div><small class="text-secondary">iOS 14 and above, no jailbreak</small></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="os-badge w-100 justify-content-center">
                    <i class="bi bi-tablet text-primary fs-3"></i>
                    <div><div class="fw-bold">Tablets &amp; web</div><small class="text-secondary">Dashboard on any browser</small></div>
                </div>
            </div>
        </div>
        <p class="text-center text-secondary small mt-4 mb-0">
            An internet connection is required for the agent to report activity. Apple devices do not need to be
            jailbroken; Android devices do not need to be rooted.
        </p>
    </div>
</section>

<!-- Why ROYALTRICO -->
<section class="py-5 bg-white rounded-4 mb-4">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Why families and teams choose ROYALTRICO</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card stat-card h-100 p-4">
                    <i class="bi bi-shield-check text-primary fs-3 mb-3"></i>
                    <h6 class="fw-semibold">Consent-first, always</h6>
                    <p class="text-secondary small mb-0">
                        Every device has a recorded consent record before monitoring begins. Pause or remove
                        a device at any time — transparency is a feature, not a limitation.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card h-100 p-4">
                    <i class="bi bi-lightning-charge-fill text-warning fs-3 mb-3"></i>
                    <h6 class="fw-semibold">Easy setup</h6>
                    <p class="text-secondary small mb-0">
                        Install the agent from the official app store or a secure link in minutes.
                        No technical skills, no physical device access needed.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card h-100 p-4">
                    <i class="bi bi-person-heart text-danger fs-3 mb-3"></i>
                    <h6 class="fw-semibold">Made for caregivers &amp; employers</h6>
                    <p class="text-secondary small mb-0">
                        Keep children safe online, stay informed as a caregiver, or manage
                        company-issued devices — with the people involved aware and on board.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card h-100 p-4">
                    <i class="bi bi-bell-fill text-success fs-3 mb-3"></i>
                    <h6 class="fw-semibold">Alerts you control</h6>
                    <p class="text-secondary small mb-0">
                        Keyword and geofence rules raise alerts that you review and acknowledge —
                        not a firehose, just what matters.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card h-100 p-4">
                    <i class="bi bi-lock-fill text-info fs-3 mb-3"></i>
                    <h6 class="fw-semibold">Private by default</h6>
                    <p class="text-secondary small mb-0">
                        Activity data is encrypted in transit and at rest, and is only accessible
                        to the account that owns the device record.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card h-100 p-4">
                    <i class="bi bi-headset text-primary fs-3 mb-3"></i>
                    <h6 class="fw-semibold">Real support</h6>
                    <p class="text-secondary small mb-0">
                        Questions about setup, consent, or compliance? Our team responds within
                        one business day.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Trusted by responsible parents and employers</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card t-card h-100 p-4">
                    <div class="mb-2">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    </div>
                    <p class="small mb-3">
                        I sat down with my daughter and we set this up on her phone together with her full
                        permission. The alert rules are great — we check the weekly activity summary as a family.
                    </p>
                    <div class="mt-auto"><strong>Heather</strong><br><small class="text-secondary">Parent, Poughkeepsie, NY</small></div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card t-card h-100 p-4">
                    <div class="mb-2">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    </div>
                    <p class="small mb-3">
                        We rolled out ROYALTRICO on company-issued phones so the team knows how company devices
                        are being used. Transparent policy, easy enrollment, zero fuss.
                    </p>
                    <div class="mt-auto"><strong>Yada</strong><br><small class="text-secondary">Business owner, Fah, LN</small></div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card t-card h-100 p-4">
                    <div class="mb-2">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    </div>
                    <p class="small mb-3">
                        Location sharing with my elderly parents gives me peace of mind. They see the same map
                        I do — nothing is hidden from anyone.
                    </p>
                    <div class="mt-auto"><strong>David</strong><br><small class="text-secondary">Caregiver, Riggit, DE</small></div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card t-card h-100 p-4">
                    <div class="mb-2">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    </div>
                    <p class="small mb-3">
                        As a small business owner I monitor our company tablets to keep an eye on stock and
                        delivery apps. Employees signed the policy, everyone is comfortable with it.
                    </p>
                    <div class="mt-auto"><strong>Raj</strong><br><small class="text-secondary">Business owner, IN</small></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing / CTA -->
<section id="pricing" class="py-5">
    <div class="container">
        <div class="plan-card bg-primary text-white p-5">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h2 class="fw-bold mb-2">Start monitoring today</h2>
                    <p class="mb-0 opacity-75">
                        One simple plan. No monthly fees, no hidden costs. Enroll up to five devices,
                        install the agent with consent, and monitor from your live dashboard.
                    </p>
                </div>
                <div class="col-lg-5 text-lg-end mt-4 mt-lg-0">
                    <div class="mb-3">
                        <span class="display-6 fw-bold">$49</span>
                        <span class="opacity-75">one-time</span>
                    </div>
                    <a href="{{ route('register') }}" class="btn btn-light btn-lg rounded-pill px-5 fw-semibold">
                        Get started <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <div class="small opacity-75 mt-2">14-day money-back guarantee</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Support / FAQ -->
<section id="support" class="py-5 bg-white rounded-4 mb-4">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5">
                <h2 class="fw-bold mb-3">Support</h2>
                <p class="text-secondary mb-4">
                    Questions about setup, consent records, or device policies? We're here to help.
                </p>
                <ul class="list-unstyled">
                    <li class="d-flex gap-3 mb-3">
                        <i class="bi bi-envelope-fill text-primary fs-5"></i>
                        <span>{{ \App\Services\Settings::get('support_email', 'support@royaltrico.example') }}</span>
                    </li>
                    <li class="d-flex gap-3 mb-3">
                        <i class="bi bi-chat-fill text-primary fs-5"></i>
                        <span>Live chat, Mon–Fri, 9am–6pm</span>
                    </li>
                    <li class="d-flex gap-3">
                        <i class="bi bi-book-fill text-primary fs-5"></i>
                        <span>Setup guides for every platform</span>
                    </li>
                </ul>
            </div>
            <div class="col-lg-7">
                <div class="accordion" id="faq">
                    <div class="accordion-item border-0 shadow-sm rounded-4 mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button rounded-4" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Is consent really required?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faq">
                            <div class="accordion-body text-secondary">
                                Yes — every enrolled device needs a recorded consent confirmation from its owner.
                                ROYALTRICO has no stealth features, and monitoring a device without authorization
                                is illegal in most jurisdictions.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 shadow-sm rounded-4 mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-4" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Does the agent show on the device?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faq">
                            <div class="accordion-body text-secondary">
                                Yes. The ROYALTRICO agent is a normal, visible app installed from the official app
                                store, so the device owner always knows it is there and can remove it at any time.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 shadow-sm rounded-4 mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-4" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Can I monitor a phone I don't own?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faq">
                            <div class="accordion-body text-secondary">
                                Only with the owner's explicit permission — for example a parent setting up their
                                child's device, or an employer managing company-issued devices under a written policy.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 shadow-sm rounded-4">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-4" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Do I need monthly fees or extra hardware?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faq">
                            <div class="accordion-body text-secondary">
                                No. One-time pricing, no hidden costs. Just a device with an internet connection.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="pt-5 pb-4">
    <div class="border-top pt-4">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="fw-bold mb-2"><span class="d-inline-flex align-items-center justify-content-center overflow-hidden rounded-3 me-2 align-middle" style="width:100px;height:100px;background:linear-gradient(135deg,#0d3bbf,#1b6ff5);"><img src="{{ asset('images/logo-transparent.png') }}" alt="ROYALTRICO logo" height="100" width="100"></span> ROYALTRICO</div>
                <p class="footer-note">
                    Consent-based device monitoring for families, caregivers and employers.
                    Only ever monitor devices you own or are authorized to monitor.
                </p>
            </div>
            <div class="col-6 col-lg-2">
                <div class="fw-semibold mb-2">Product</div>
                <ul class="list-unstyled footer-note">
                    <li class="mb-1"><a class="text-decoration-none" href="#features">Features</a></li>
                    <li class="mb-1"><a class="text-decoration-none" href="#compatibility">Compatibility</a></li>
                    <li class="mb-1"><a class="text-decoration-none" href="#how-it-works">How it works</a></li>
                    <li class="mb-1"><a class="text-decoration-none" href="#pricing">Pricing</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <div class="fw-semibold mb-2">Company</div>
                <ul class="list-unstyled footer-note">
                    <li class="mb-1"><a class="text-decoration-none" href="{{ route('pages.about') }}">About</a></li>
                    <li class="mb-1"><a class="text-decoration-none" href="#support">Support</a></li>
                    <li class="mb-1"><a class="text-decoration-none" href="{{ route('pages.privacy') }}">Privacy policy</a></li>
                    <li class="mb-1"><a class="text-decoration-none" href="{{ route('pages.terms') }}">Terms of service</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <div class="fw-semibold mb-2">Contact</div>
                <ul class="list-unstyled footer-note">
                    <li class="mb-1"><i class="bi bi-geo-alt me-1"></i>150 Motor Pkwy, Suite 401, Hauppauge, NY 11788</li>
                        <li class="mb-1"><i class="bi bi-envelope me-1"></i>{{ \App\Services\Settings::get('support_email', 'support@royaltrico.example') }}</li>
                </ul>
            </div>
        </div>
        <div class="border-top mt-4 pt-3 d-flex flex-wrap justify-content-between footer-note">
            <div>&copy; {{ date('Y') }} ROYALTRICO. All rights reserved.</div>
            <div>Apple, Android and all other trademarks are property of their respective owners.</div>
        </div>
        <div class="footer-note mt-3 p-3 bg-light rounded-3">
            <strong>Responsible use.</strong> ROYALTRICO is for monitoring devices you own or are authorized to
            monitor — your own devices, your children's devices with parental consent, or employer-owned devices
            under a written policy. It is a federal and state offense in many jurisdictions to install monitoring
            software on a device without proper authorization. You are solely responsible for complying with all
            applicable laws, and for any consequences of unauthorized use. The use of the software is at your own
            discretion and risk, and the publisher shall not be liable for any losses arising from its use.
        </div>
    </div>
</footer>
@endsection
