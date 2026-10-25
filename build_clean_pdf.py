"""
Builds the 17-page publication-grade PDF for RGV Multi-Tech Services.
Every page is explicitly designed to fit its content without spillover.
Uses --no-pdf-header-footer to eliminate all browser headers/URLs.
"""

import os
import sys
import base64
import subprocess
import pypdf

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
LOGO_PATH = os.path.join(BASE_DIR, "logo", "RGV-Logo.png")
OUTPUT_HTML = os.path.join(BASE_DIR, "rgv_features_clean.html")
OUTPUT_PDF = os.path.join(BASE_DIR, "RGV-System-Features-Specification.pdf")
EDGE_PATH = r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"

if not os.path.exists(EDGE_PATH):
    EDGE_PATH = r"C:\Program Files\Microsoft\Edge\Application\msedge.exe"
if not os.path.exists(EDGE_PATH):
    EDGE_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"

# Read logo base64
logo_b64 = ""
if os.path.exists(LOGO_PATH):
    with open(LOGO_PATH, "rb") as f:
        logo_b64 = f"data:image/png;base64,{base64.b64encode(f.read()).decode('utf-8')}"

# Helper to wrap a page
def make_page(page_num, title_crumb, content_html, is_cover=False):
    if is_cover:
        return f"""
        <div class="sheet cover-sheet">
          {content_html}
        </div>
        """
    header_html = f"""
    <div class="page-header">
      <span class="crumb">RGV Multi-Tech Services &bull; {title_crumb}</span>
      <span class="pnum">Page {page_num} of 17</span>
    </div>
    """
    footer_html = f"""
    <div class="page-footer">
      <span>Comprehensive Whole-System Features Specification &bull; October 2026</span>
      <span>Confidential &amp; Proprietary</span>
    </div>
    """
    return f"""
    <div class="sheet content-sheet">
      {header_html}
      <div class="sheet-body">
        {content_html}
      </div>
      {footer_html}
    </div>
    """

# ==========================================
# PAGE 1: COVER
# ==========================================
p1_content = f"""
<div class="cover-container">
  <div class="cover-top">
    <img src="{logo_b64}" class="cover-logo" alt="RGV Logo">
    <div class="cover-org">RGV Multi-Tech Services</div>
    <div class="cover-title">Whole System Features<br><span>Specification &amp; Capability Directory</span></div>
    <div class="cover-subtitle">
      The authoritative, full-scope technical manual documenting every feature across the Public Web Portal, Employee Web Portal, Flutter Android Mobile App, and Administrator Command Center.
    </div>

    <div class="cover-tags">
      <span class="cover-tag">Laravel 12.0 Backend</span>
      <span class="cover-tag">PHP 8.2+ Architecture</span>
      <span class="cover-tag">Supabase PostgreSQL</span>
      <span class="cover-tag">Flutter Android Mobile Client</span>
      <span class="cover-tag">Tailwind CSS 3 + Alpine.js</span>
      <span class="cover-tag">Spatie RBAC &amp; MFA Security</span>
      <span class="cover-tag">AI Predictive Analytics</span>
      <span class="cover-tag">Automated Backups &amp; SHA-256 Audit</span>
    </div>

    <div class="cover-stats-grid">
      <div class="stat-card">
        <div class="num">4</div>
        <div class="lbl">Platform Surfaces</div>
      </div>
      <div class="stat-card">
        <div class="num">33+</div>
        <div class="lbl">Database Tables</div>
      </div>
      <div class="stat-card">
        <div class="num">42</div>
        <div class="lbl">Controllers</div>
      </div>
      <div class="stat-card">
        <div class="num">18</div>
        <div class="lbl">Eloquent Models</div>
      </div>
      <div class="stat-card">
        <div class="num">14</div>
        <div class="lbl">Notification Classes</div>
      </div>
      <div class="stat-card">
        <div class="num">34+</div>
        <div class="lbl">API Endpoints</div>
      </div>
      <div class="stat-card">
        <div class="num">60+</div>
        <div class="lbl">Admin Workflows</div>
      </div>
      <div class="stat-card">
        <div class="num">9</div>
        <div class="lbl">Security Middleware</div>
      </div>
    </div>
  </div>

  <div class="cover-bottom">
    <strong>Document Version:</strong> 1.6 &nbsp;|&nbsp; <strong>Release Cycle:</strong> October 2026 &nbsp;|&nbsp; <strong>Classification:</strong> Operational Specification &amp; System Manual<br>
    Built &amp; Maintained for <strong>RGV Multi-Tech Services</strong> &bull; Open-source business operations platform licensed under MIT.
  </div>
</div>
"""

# ==========================================
# PAGE 2: CHAPTER 1 PART 1
# ==========================================
p2_content = """
<h1>1. Executive Summary &amp; System Architecture</h1>

<p class="section-intro">
  <strong>RGV-System</strong> is an enterprise-grade, multi-platform business operations management system engineered specifically for <strong>RGV Multi-Tech Services</strong>. The system unifies four core business operations—<strong>Customer Service Bookings</strong>, <strong>Physical Inventory &amp; Asset Management</strong>, <strong>Field Equipment Borrowing &amp; Returns</strong>, and <strong>Business Analytics &amp; Compliance</strong>—into a synchronized ecosystem powered by a single source of truth database.
</p>

<div class="callout callout-info">
  <strong>The Core Operational Vision:</strong> Customers submit and track technical service requests publicly without account friction; field employees manage assigned dispatches, review inventory, scan physical equipment via native Android QR scanning, and process returns on-site; administrators retain end-to-end command over approvals, stock thresholds, role-based security, automated backups, AI-driven demand forecasts, and tamper-evident audit trails.
</div>

<h2>1.1 Multi-Surface Ecosystem Architecture</h2>
<p>
  Unlike disjointed solutions that rely on batch synchronization workers or independent data stores, the RGV System connects all client surfaces directly to a unified PostgreSQL engine (hosted on Supabase) sharing identical business constraints, relations, and transactional integrity.
</p>

<div class="diagram-container">
  <div style="font-weight: 700; color: #1e3a8a; font-size: 8.5pt; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Unified Multi-Surface Architecture</div>
  <div class="diagram-flow">
    <div class="diagram-node">
      Public Web Portal
      <small>No-auth Bookings &amp; Tracking</small>
    </div>
    <div class="diagram-arrow">&harr;</div>
    <div class="diagram-node">
      Laravel 12 MVC Core
      <small>PHP 8.2+, Breeze, Spatie RBAC</small>
    </div>
    <div class="diagram-arrow">&harr;</div>
    <div class="diagram-node">
      Supabase PostgreSQL
      <small>33+ Tables, Triggers, RPCs</small>
    </div>
  </div>
  <div class="diagram-flow" style="margin-top: 8px;">
    <div class="diagram-node">
      Employee Web Portal
      <small>Assigned Bookings &amp; Requests</small>
    </div>
    <div class="diagram-arrow">&harr;</div>
    <div class="diagram-node">
      Flutter Android App
      <small>Direct RPCs + QR Scanner</small>
    </div>
    <div class="diagram-arrow">&harr;</div>
    <div class="diagram-node">
      Admin Command Center
      <small>Analytics, Backups, AI Tools</small>
    </div>
  </div>
</div>

<h2>1.2 Core Architectural Principles</h2>
<ul style="font-size: 8.4pt; margin-top: 4px;">
  <li><strong>Zero-Sync Discrepancy:</strong> Both web and mobile surfaces query and mutate the exact same live tables, eliminating data divergence.</li>
  <li><strong>Defense-in-Depth Security:</strong> Layered protection spanning global HTTPS enforcement, security headers, CSRF tokens, strict Eloquent ORM parameterized queries, and granular Spatie permissions.</li>
  <li><strong>Hermetic Test Isolation:</strong> The PHPUnit test harness is completely decoupled from production credentials, executing strictly in-memory via SQLite.</li>
</ul>
"""

# ==========================================
# PAGE 3: CHAPTER 1 PART 2 (TECH STACK)
# ==========================================
p3_content = """
<h1>1.3 Complete Technology Stack Deep Dive</h1>
<p class="section-intro">
  The RGV System leverages modern, stable enterprise technologies to provide high reliability, fast response times, and rapid developer extensibility.
</p>

<table>
  <thead>
    <tr>
      <th style="width: 22%;">Layer</th>
      <th style="width: 36%;">Technologies &amp; Libraries</th>
      <th style="width: 42%;">Purpose &amp; Operational Role</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Backend Engine</strong></td>
      <td>Laravel 12.0, PHP 8.2+, Eloquent ORM</td>
      <td>RESTful API routing, MVC architecture, transactional database management, job queues, and service orchestration.</td>
    </tr>
    <tr>
      <td><strong>Database &amp; Storage</strong></td>
      <td>Supabase PostgreSQL &amp; SQLite In-Memory</td>
      <td>33+ relational tables, strict foreign keys, transactional stored procedures (RPCs), and automated SQLite isolation during testing.</td>
    </tr>
    <tr>
      <td><strong>Frontend Web Portals</strong></td>
      <td>Tailwind CSS 3.1, Alpine.js 3.4, Blade, Vite 7.0</td>
      <td>Lightweight, reactive, responsive interfaces for public booking submission, technician tools, and administrator control center.</td>
    </tr>
    <tr>
      <td><strong>Employee Mobile Client</strong></td>
      <td>Flutter (Dart), <code>mobile_scanner</code>, <code>supabase_flutter</code></td>
      <td>Dedicated Android application featuring instant camera QR recognition, direct Supabase RPC execution, and offline resilience.</td>
    </tr>
    <tr>
      <td><strong>Authentication &amp; RBAC</strong></td>
      <td>Laravel Breeze, Spatie Permission, TOTP MFA</td>
      <td>Role-based permissions (Admin, Employee), multi-factor email &amp; authenticator tokens, session monitors, and force-logout tools.</td>
    </tr>
    <tr>
      <td><strong>AI Intelligence Suite</strong></td>
      <td>Ollama (Local llama3.1), Google Gemini 2.0, OpenAI</td>
      <td>Multi-tier fallback architecture powering operational chatbot, predictive booking forecasts, and inventory stock recommendations.</td>
    </tr>
    <tr>
      <td><strong>Documents &amp; Barcodes</strong></td>
      <td><code>barryvdh/laravel-dompdf</code>, <code>maatwebsite/excel</code>, Simple-QRCode</td>
      <td>Vector QR code generation, automated PDF invoices/work-orders, audit compliance exports, and queued Excel spreadsheet imports/exports.</td>
    </tr>
    <tr>
      <td><strong>Security &amp; Disaster Recovery</strong></td>
      <td><code>spatie/laravel-backup</code>, HMAC-SHA256, Tiered Throttling</td>
      <td>Automated daily encrypted backups, SHA-256 verification, tamper-evident audit chaining, and multi-tier API rate throttling.</td>
    </tr>
    <tr>
      <td><strong>Code Quality &amp; CI</strong></td>
      <td>PHPUnit 11.5, Laravel Pint, GitHub Actions</td>
      <td>Automated test suite guaranteeing regression-free deployments and uniform PSR-12 code styling across all services.</td>
    </tr>
  </tbody>
</table>

<div class="callout callout-success">
  <strong>Key Architectural Advantage:</strong> By decoupling the Flutter mobile client from the Laravel HTTP request cycle through direct Supabase RPCs, field technicians can continue looking up inventory and processing returns even when the main web server undergoes scheduled maintenance.
</div>
"""

# ==========================================
# PAGE 4: CHAPTER 2 PART 1 (ROLES & BOOKING)
# ==========================================
p4_content = """
<h1>2. Role-Based Access &amp; Public Customer Portal</h1>

<p class="section-intro">
  The system implements a strictly segregated role hierarchy. Public users interact without authentication to eliminate customer friction, while internal staff and administrators are protected by Spatie Role-Based Access Control (RBAC), multi-factor authentication (MFA), and session monitors.
</p>

<h2>2.1 Stakeholder Matrix &amp; Operational Privileges</h2>
<table>
  <thead>
    <tr>
      <th style="width: 16%;">Role</th>
      <th style="width: 22%;">Client Platform</th>
      <th style="width: 32%;">Accessible Features</th>
      <th style="width: 30%;">Security Controls</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><span class="badge badge-green">Public</span></td>
      <td>Web Browser (Desktop &amp; Mobile)</td>
      <td>Submit service booking requests, track booking progress in real-time, scan public inventory QR verification tokens, browse services.</td>
      <td>Zero credentials required; public rate throttling (30 req/min); CSRF protection; input sanitization.</td>
    </tr>
    <tr>
      <td><span class="badge badge-blue">Employee</span></td>
      <td>Web Portal + Flutter Android App</td>
      <td>Manage assigned service jobs, create equipment borrow requisitions, conduct stock checks, process returns, view notifications.</td>
      <td>Password hashing (Bcrypt); Mandatory MFA (TOTP / Email); Sanctum bearer token; Supabase AAL2 JWT; session timeout.</td>
    </tr>
    <tr>
      <td><span class="badge badge-purple">Administrator</span></td>
      <td>Admin Web Control Center</td>
      <td>Full oversight: approve/reject bookings, assign staff, manage inventory, execute backups, audit logs, AI forecasting, site settings.</td>
      <td>Admin middleware; MFA protection; Force-logout capability; Impersonation audit tracking; Password-confirmed deletion.</td>
    </tr>
  </tbody>
</table>

<h2>2.2 Public Booking Submission Engine</h2>
<p>
  Customers can request service visits through an intuitive, mobile-responsive booking form accessible directly from the homepage (<code>/booking</code>). The interface is tailored to capture complete technical and scheduling specifications upfront.
</p>

<div class="grid-2">
  <div class="card card-accent-blue">
    <div class="card-title">Customer Information Captured</div>
    <ul style="font-size: 8pt; margin-top: 4px;">
      <li><strong>Full Customer Name:</strong> Contact person for service delivery.</li>
      <li><strong>Valid Email Address:</strong> Destination for instant lifecycle email alerts.</li>
      <li><strong>Contact Telephone:</strong> Direct phone number for technician field coordination.</li>
      <li><strong>Physical Service Address:</strong> Precise destination for on-site execution.</li>
    </ul>
  </div>
  <div class="card card-accent-blue">
    <div class="card-title">Service &amp; Scheduling Parameters</div>
    <ul style="font-size: 8pt; margin-top: 4px;">
      <li><strong>Preferred Execution Date:</strong> Requested service calendar date.</li>
      <li><strong>Preferred Time Window:</strong> Morning / afternoon time slot selection.</li>
      <li><strong>Purpose Category:</strong> Installation, Maintenance, Repair, or Inspection.</li>
      <li><strong>Reason &amp; Technical Notes:</strong> Detailed problem or work description.</li>
    </ul>
  </div>
</div>

<div class="callout callout-success">
  <strong>Automated Reference Token Generation:</strong> Upon successful submission, the system issues a unique, tamper-resistant reference identifier formatted as <code>BK-XXXXXXXXXXXXX</code> (e.g., <code>BK-20261025-A8F9</code>). This reference code serves as the customer's permanent lookup key.
</div>
"""

# ==========================================
# PAGE 5: CHAPTER 2 PART 2 (TRACKING & QR)
# ==========================================
p5_content = """
<h1>2.3 Public Real-Time Service Tracking &amp; Verification</h1>
<p class="section-intro">
  Customers can monitor their service request at any time by visiting <code>/booking/track/{reference}</code> or entering their reference code on the homepage tracking form. The tracker reveals live operational milestones:
</p>

<div class="grid-3">
  <div class="card card-accent-amber">
    <div class="card-header"><span class="card-title">1. Pending Review</span><span class="badge badge-amber">Awaiting Admin</span></div>
    <p style="font-size: 7.8pt; margin: 0;">Request logged in database. Awaiting administrative review for technician availability, parts, and schedule feasibility.</p>
  </div>
  <div class="card card-accent-blue">
    <div class="card-header"><span class="card-title">2. Approved &amp; Assigned</span><span class="badge badge-blue">Technician Set</span></div>
    <p style="font-size: 7.8pt; margin: 0;">Admin confirmed schedule. Designated technician is assigned, notified, and preparing required technical tools.</p>
  </div>
  <div class="card card-accent-green">
    <div class="card-header"><span class="card-title">3. Completed</span><span class="badge badge-green">Service Finalized</span></div>
    <p style="font-size: 7.8pt; margin: 0;">Technician executed work on-site, filed field remarks, and marked booking successfully fulfilled.</p>
  </div>
</div>

<h2>2.4 Alternative Lifecycle Outcomes</h2>
<div class="grid-2">
  <div class="card card-accent-red">
    <div class="card-title">&bull; Rejected Bookings</div>
    <p style="font-size: 8pt; margin-top: 3px;">
      If a service request cannot be fulfilled (e.g. out of service territory, fully booked date), administrators document a mandatory reason. The customer tracking screen transparently presents this explanation along with support contact numbers.
    </p>
  </div>
  <div class="card card-accent-amber">
    <div class="card-title">&bull; Cancelled Requests</div>
    <p style="font-size: 8pt; margin-top: 3px;">
      Bookings cancelled prior to execution are clearly tagged with cancellation timestamps and logged in the system audit trail, automatically freeing up technician scheduling slots.
    </p>
  </div>
</div>

<h2>2.5 Public QR Code Asset Verification</h2>
<p>
  Physical equipment and tools managed by RGV Multi-Tech Services feature permanent, weather-resistant QR code labels linking to immutable public tokens (<code>/inventory/qr/{token}</code>). When scanned by clients or site inspectors:
</p>
<ul style="font-size: 8.3pt;">
  <li><strong>Authentication of Authenticity:</strong> Confirms the equipment is officially owned, calibrated, and maintained by RGV Multi-Tech Services.</li>
  <li><strong>Non-Sensitive Specifications Displayed:</strong> Reveals asset model, equipment category, unit specifications, and service compatibility.</li>
  <li><strong>Security Redaction:</strong> Sensitive internal warehouse details—including purchase prices, internal supplier references, and available stock quantities—are strictly hidden from public lookup.</li>
</ul>

<div class="callout callout-info">
  <strong>No-Login Frictionless Customer Experience:</strong> The public portal requires no user registration, password creation, or email verification to book services. All access is keyed to the customer's secure booking reference code.
</div>
"""

# ==========================================
# PAGE 6: CHAPTER 3 PART 1 (EMPLOYEE WEB)
# ==========================================
p6_content = """
<h1>3. Employee Operations: Web Portal Workspace</h1>

<p class="section-intro">
  Employees represent the core field service workforce. They utilize a dedicated <strong>Web Portal</strong> (<code>/employee</code>) for dispatch review, customer details, and equipment requisitions, paired with a companion <strong>Flutter Android App</strong> for mobility on the job site.
</p>

<h2>3.1 Employee Web Portal Modules</h2>

<div class="grid-2">
  <div class="card card-accent-blue">
    <div class="card-title">&bull; Technician Command Dashboard</div>
    <p style="font-size: 8pt; margin-top: 4px;">
      Centralized home screen displaying active assigned service bookings, pending equipment requisitions, items currently borrowed, overdue return alerts, and an unread notification counter.
    </p>
  </div>
  <div class="card card-accent-blue">
    <div class="card-title">&bull; Assigned Dispatch Management</div>
    <p style="font-size: 8pt; margin-top: 4px;">
      Technicians view complete customer details, service locations, and scheduling requirements. They can append job remarks, document field progress, and mark bookings as Completed upon job fulfillment.
    </p>
  </div>
  <div class="card card-accent-purple">
    <div class="card-title">&bull; Equipment Requisition (Borrow System)</div>
    <p style="font-size: 8pt; margin-top: 4px;">
      Searchable inventory catalog with real-time stock availability. Technicians submit multi-item borrow requests specifying required quantities, operational purpose, borrow start date, and expected return due date.
    </p>
  </div>
  <div class="card card-accent-green">
    <div class="card-title">&bull; Return &amp; Condition Reporting</div>
    <p style="font-size: 8pt; margin-top: 4px;">
      Itemized return workflow allowing staff to declare returned item condition (Good, Fair, Poor), document damage notes, specify warehouse return locations, and soft-delete closed records.
    </p>
  </div>
</div>

<h2>3.2 Equipment Requisition Workflow Rules</h2>
<p>
  The borrow request system enforces strict business logic to prevent equipment loss, tool hoarding, or double-allocation:
</p>
<table>
  <thead>
    <tr>
      <th style="width: 20%;">Requisition Stage</th>
      <th style="width: 40%;">System Validation &amp; Behavior</th>
      <th style="width: 40%;">Operational Impact</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Catalog Browsing</strong></td>
      <td>Displays real-time available stock (total quantity minus currently reserved and borrowed units).</td>
      <td>Prevents requesting items that are out of stock or reserved for other jobs.</td>
    </tr>
    <tr>
      <td><strong>Draft Submission</strong></td>
      <td>Staff specifies item quantities, borrow start date, expected return date, and job reason.</td>
      <td>Request created in <code>pending</code> status; items immediately reserved.</td>
    </tr>
    <tr>
      <td><strong>Admin Decision</strong></td>
      <td>Admin reviews requisition. Approval sets status to <code>approved</code>; rejection releases reservation.</td>
      <td>Staff notified via email and in-app alert immediately upon decision.</td>
    </tr>
    <tr>
      <td><strong>Overdue Monitoring</strong></td>
      <td>System evaluates expected return date against current timestamp.</td>
      <td>Past-due requests automatically flagged as <code>overdue</code> with alerts sent to staff.</td>
    </tr>
  </tbody>
</table>

<div class="callout callout-info">
  <strong>Self-Service Cleanliness:</strong> Employees can soft-delete their own returned or rejected borrow requests from their personal list, maintaining a clutter-free view while preserving full administrative audit trails.
</div>
"""

# ==========================================
# PAGE 7: CHAPTER 3 PART 2 (FLUTTER APP)
# ==========================================
p7_content = """
<h1>3.3 Flutter Android Mobile Application (Field Operations)</h1>
<p class="section-intro">
  The native Android app in <code>mobile/</code> is purpose-built for technicians working in equipment yards, service vans, and customer facilities. Built with Flutter, it operates independently of the web application server by interacting directly with Supabase PostgreSQL.
</p>

<table>
  <thead>
    <tr>
      <th style="width: 24%;">Mobile Feature</th>
      <th style="width: 38%;">Implementation Mechanism</th>
      <th style="width: 38%;">Field Technician Benefit</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Hardware QR Scanner</strong></td>
      <td>Integrated <code>mobile_scanner</code> with hardware camera acceleration.</td>
      <td>Point-and-shoot barcode/QR recognition on physical tool labels; zero typing required.</td>
    </tr>
    <tr>
      <td><strong>Manual Item Lookup</strong></td>
      <td>Alphanumeric code search with real-time substring filtering.</td>
      <td>Instant fallback for scratched, dirty, or damaged QR tags, or low-light field conditions.</td>
    </tr>
    <tr>
      <td><strong>Direct Supabase RPCs</strong></td>
      <td>Transactional stored procedures: <code>borrow_request</code>, <code>return_items</code>.</td>
      <td>App continues functioning even if the Laravel web application is restarting or offline.</td>
    </tr>
    <tr>
      <td><strong>Stock Reservation</strong></td>
      <td>Automated atomic decrement of available quantity upon request.</td>
      <td>Prevents multiple technicians from reserving the same critical tools simultaneously.</td>
    </tr>
    <tr>
      <td><strong>Idempotent Returns</strong></td>
      <td>Client-side UUID generation via Dart <code>uuid</code> package.</td>
      <td>Guarantees that poor network connectivity or double-tapping never creates duplicate return logs.</td>
    </tr>
    <tr>
      <td><strong>Mobile Security &amp; MFA</strong></td>
      <td>Supabase Auth with mandatory TOTP MFA (AAL2 compliance).</td>
      <td>High security; credentials encrypted using <code>flutter_secure_storage</code>.</td>
    </tr>
  </tbody>
</table>

<h2>3.4 Equipment Lifecycle Workflow</h2>
<div class="diagram-container">
  <div style="font-weight: 700; color: #1e3a8a; font-size: 8.5pt; margin-bottom: 8px; text-transform: uppercase;">Equipment Lifecycle Workflow</div>
  <div class="diagram-flow">
    <div class="diagram-node">
      1. Item Requisition
      <small>Technician scans QR or selects items</small>
    </div>
    <div class="diagram-arrow">&rarr;</div>
    <div class="diagram-node">
      2. Stock Reservation
      <small>Items held in pending status</small>
    </div>
    <div class="diagram-arrow">&rarr;</div>
    <div class="diagram-node">
      3. Admin Approval
      <small>Stock officially released as Borrowed</small>
    </div>
  </div>
  <div class="diagram-flow" style="margin-top: 8px;">
    <div class="diagram-node">
      4. Field Execution
      <small>Tools utilized during service</small>
    </div>
    <div class="diagram-arrow">&rarr;</div>
    <div class="diagram-node">
      5. Return &amp; Condition
      <small>Good/Fair/Poor rating + Damage log</small>
    </div>
    <div class="diagram-arrow">&rarr;</div>
    <div class="diagram-node">
      6. Stock Restoration
      <small>Items returned to active inventory</small>
    </div>
  </div>
</div>

<div class="callout callout-info">
  <strong>Dual Mobile API Architecture:</strong> In addition to native Supabase RPCs, the Laravel backend maintains authenticated REST endpoints under <code>/api/v1/mobile/*</code> (protected by Laravel Sanctum bearer tokens with dedicated rate limiting of 20 req/min for auth and 120 req/min for operational endpoints) to support external integrations and legacy clients.
</div>
"""

# ==========================================
# PAGE 8: CHAPTER 4 PART 1 (ADMIN DASHBOARD & BOOKINGS)
# ==========================================
p8_content = """
<h1>4. Administrator Command Center: Core Governance</h1>

<p class="section-intro">
  The Administrator Control Center (<code>/admin</code>) serves as the central operational hub of RGV-System. It provides complete oversight across service dispatches, physical asset inventories, staff requisitions, system security, and organizational reporting.
</p>

<h2>4.1 Administrator Command Dashboard</h2>
<p>
  Upon logging in, administrators are presented with real-time operational metrics that automatically aggregate key figures across the organization:
</p>
<div class="grid-4">
  <div class="stat-card">
    <div class="num">Live</div>
    <div class="lbl">Pending Bookings</div>
  </div>
  <div class="stat-card">
    <div class="num">Alert</div>
    <div class="lbl">Low Stock Items</div>
  </div>
  <div class="stat-card">
    <div class="num">Active</div>
    <div class="lbl">Borrowed Equipment</div>
  </div>
  <div class="stat-card">
    <div class="num">Active</div>
    <div class="lbl">Registered Staff</div>
  </div>
</div>

<h2>4.2 Booking Dispatch &amp; Lifecycle Management</h2>
<p>
  Administrators oversee the full lifecycle of customer bookings with granular controls:
</p>

<table>
  <thead>
    <tr>
      <th style="width: 20%;">Workflow Step</th>
      <th style="width: 40%;">Administrative Action &amp; System Execution</th>
      <th style="width: 40%;">Resulting System Outcome</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Review &amp; Filter</strong></td>
      <td>Filter by status (Pending, Approved, Completed, Cancelled), customer name, date range, or reference number.</td>
      <td>Instant identification of urgent customer requests needing review.</td>
    </tr>
    <tr>
      <td><strong>Approve Request</strong></td>
      <td>Confirm service feasibility and lock the schedule.</td>
      <td>Status shifts to <code>approved</code>; automated email notification dispatched to customer.</td>
    </tr>
    <tr>
      <td><strong>Reject Request</strong></td>
      <td>Decline with mandatory administrative reason log.</td>
      <td>Status set to <code>rejected</code>; customer notified with detailed explanation.</td>
    </tr>
    <tr>
      <td><strong>Assign Technician</strong></td>
      <td>Select available field technician from dynamic dropdown.</td>
      <td>Booking assigned to employee; technician receives in-app alert &amp; email notification.</td>
    </tr>
    <tr>
      <td><strong>Interactive Calendar</strong></td>
      <td>Full visual calendar view (<code>/admin/bookings/calendar</code>) showing scheduled work orders.</td>
      <td>Prevents technician overbooking and enables balanced workload dispatching.</td>
    </tr>
    <tr>
      <td><strong>Invoice Generation</strong></td>
      <td>One-click PDF generation via <code>barryvdh/laravel-dompdf</code>.</td>
      <td>Produces formal, branded customer service invoice / work-order document.</td>
    </tr>
  </tbody>
</table>

<div class="callout callout-info">
  <strong>Dynamic Scheduling Calendar:</strong> The admin calendar visualizes all approved and scheduled service visits on month, week, and day views, giving dispatchers complete visual awareness of technician availability.
</div>
"""

# ==========================================
# PAGE 9: CHAPTER 4 PART 2 (INVENTORY & BORROW GOVERNANCE)
# ==========================================
p9_content = """
<h1>4.3 Inventory Management &amp; Equipment Governance</h1>
<p class="section-intro">
  The inventory management module tracks all physical tools, technical instruments, machinery, replacement parts, and consumable supplies across warehouse storage locations.
</p>

<div class="grid-2">
  <div class="card card-accent-blue">
    <div class="card-title">Comprehensive Asset Tracking</div>
    <ul style="font-size: 8pt; margin-top: 4px;">
      <li><strong>Catalog Attributes:</strong> Item Code, Name, Category, Quantity, Unit (pcs, box, kg), Unit Cost, Location, and Status.</li>
      <li><strong>Physical Condition:</strong> Categorized as New, Good, Fair, or Poor.</li>
      <li><strong>Permanent QR Code Generator:</strong> Generates unique vector QR tags for asset labeling (<code>simplesoftwareio/simple-qrcode</code>).</li>
    </ul>
  </div>
  <div class="card card-accent-amber">
    <div class="card-title">Thresholds &amp; Low-Stock Prevention</div>
    <ul style="font-size: 8pt; margin-top: 4px;">
      <li><strong>Configurable Thresholds:</strong> Set minimum stock warnings per item.</li>
      <li><strong>Dedicated Low-Stock Screen:</strong> Filter and review all items requiring urgent reordering (<code>/admin/inventories/low-stock</code>).</li>
      <li><strong>Automated Administrator Alerts:</strong> Trigger notifications when stock drops below minimum operating limits.</li>
    </ul>
  </div>
</div>

<h2>4.4 Equipment Borrow Request Governance</h2>
<p>
  Administrators maintain complete custody oversight of all equipment deployed into the field:
</p>

<table>
  <thead>
    <tr>
      <th style="width: 25%;">Governance Action</th>
      <th style="width: 40%;">Administrative Inspection &amp; Criteria</th>
      <th style="width: 35%;">System Execution</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Approval / Rejection</strong></td>
      <td>Inspect requesting technician, purpose, project site, and requested duration.</td>
      <td>Approval deducts active stock; rejection releases reserved units immediately.</td>
    </tr>
    <tr>
      <td><strong>Overdue Tracking</strong></td>
      <td>Review borrow requests whose due date has elapsed without item check-in.</td>
      <td>Triggers overdue status flags and sends reminder notifications to technicians.</td>
    </tr>
    <tr>
      <td><strong>Condition Auditing</strong></td>
      <td>Compare return condition against check-out condition (e.g. Good &rarr; Damaged).</td>
      <td>Logs wear-and-tear records and initiates maintenance tickets if damaged.</td>
    </tr>
    <tr>
      <td><strong>Drop-off Verification</strong></td>
      <td>Confirm physical equipment returned to designated warehouse shelf or bin.</td>
      <td>Restores quantity to available inventory catalog.</td>
    </tr>
  </tbody>
</table>

<div class="callout callout-success">
  <strong>Asset Protection:</strong> No tool can be checked out without an approved borrow request. Every equipment transaction records the technician ID, timestamp, expected return date, and condition report.
</div>
"""

# ==========================================
# PAGE 10: CHAPTER 5 PART 1 (USER ADMIN & TRASH)
# ==========================================
p10_content = """
<h1>5. User Administration, RBAC &amp; Trash Recovery</h1>

<p class="section-intro">
  The system embeds enterprise-grade governance controls, identity management, tamper-evident audit logging, and automated disaster recovery routines to protect corporate data integrity.
</p>

<h2>5.1 User Administration &amp; Identity Management</h2>
<table>
  <thead>
    <tr>
      <th style="width: 22%;">Feature</th>
      <th style="width: 42%;">Operational Mechanism</th>
      <th style="width: 36%;">Administrative Utility</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>RBAC Authorization</strong></td>
      <td>Spatie Laravel-Permission with strict Admin and Employee role boundaries.</td>
      <td>Prevents unauthorized access to financial, audit, and administrative menus.</td>
    </tr>
    <tr>
      <td><strong>Account Status Toggles</strong></td>
      <td>One-click Active / Inactive switch.</td>
      <td>Instantly revoke system access for departing or suspended personnel.</td>
    </tr>
    <tr>
      <td><strong>Force Logout</strong></td>
      <td>Immediate termination of all active sessions and Sanctum tokens for selected user.</td>
      <td>Neutralize compromised accounts or lost devices immediately.</td>
    </tr>
    <tr>
      <td><strong>Administrator Impersonation</strong></td>
      <td>Temporary session assumption with sticky top exit banner.</td>
      <td>Troubleshoot technician issues directly; all actions strictly logged in audit trail.</td>
    </tr>
    <tr>
      <td><strong>User Analytics</strong></td>
      <td>Graphical breakdown of active users, role distribution, and registration trends.</td>
      <td>Monitor system adoption and staffing levels.</td>
    </tr>
  </tbody>
</table>

<h2>5.2 Multi-Entity Trash &amp; Soft-Delete Management</h2>
<p>
  To prevent accidental permanent data loss, the system incorporates a centralized trash management engine (<code>/admin/trash</code>) covering 5 key entity types:
</p>
<table>
  <thead>
    <tr>
      <th style="width: 25%;">Entity Type</th>
      <th style="width: 35%;">Soft-Delete Protection</th>
      <th style="width: 40%;">Administrative Recovery Actions</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Bookings</strong></td>
      <td>Cancelled or deleted customer service bookings.</td>
      <td>One-click restoration to active listings or password-confirmed permanent purge.</td>
    </tr>
    <tr>
      <td><strong>Borrow Requests</strong></td>
      <td>Completed or rejected equipment requisitions.</td>
      <td>Restore historical borrow requests or securely delete after audit retention.</td>
    </tr>
    <tr>
      <td><strong>Inventory Items</strong></td>
      <td>Decommissioned tools or depleted parts.</td>
      <td>Restore items with full stock histories intact, or permanently delete.</td>
    </tr>
    <tr>
      <td><strong>Inventory Categories</strong></td>
      <td>Removed equipment categories.</td>
      <td>Restores category relationships without corrupting associated item records.</td>
    </tr>
    <tr>
      <td><strong>User Accounts</strong></td>
      <td>Removed employee or administrator profiles.</td>
      <td>Restores credentials and historical assignments safely.</td>
    </tr>
  </tbody>
</table>

<div class="callout callout-warning">
  <strong>Password-Verified Deletion:</strong> Permanent deletion of any item from the trash requires re-entry of administrative credentials, preventing rogue or accidental data destruction.
</div>
"""

# ==========================================
# PAGE 11: CHAPTER 5 PART 2 (BACKUPS & AUDIT)
# ==========================================
p11_content = """
<h1>5.3 Automated Disaster Recovery, Backups &amp; Auditing</h1>
<p class="section-intro">
  RGV-System incorporates automated disaster recovery routines powered by <code>spatie/laravel-backup</code> and an immutable, cryptographic audit trail.
</p>

<h2>Automated Scheduled Backups</h2>
<div class="grid-3">
  <div class="card card-accent-green">
    <div class="card-title">Scheduled Automation</div>
    <p style="font-size: 7.8pt; margin-top: 4px;">
      &bull; <strong>Daily Full Backup:</strong> Database + Application files at 2:00 AM.<br>
      &bull; <strong>Weekly DB Snapshot:</strong> Sundays at 3:00 AM.<br>
      &bull; <strong>Automated Cleanup:</strong> Daily at 3:30 AM enforcing retention rules.
    </p>
  </div>
  <div class="card card-accent-blue">
    <div class="card-title">SHA-256 Integrity</div>
    <p style="font-size: 7.8pt; margin-top: 4px;">
      Every backup ZIP file is hashed with SHA-256. Administrators can trigger a one-click cryptographic verification to guarantee archives are corruption-free and uncompromised.
    </p>
  </div>
  <div class="card card-accent-purple">
    <div class="card-title">Cloud Storage &amp; Alerts</div>
    <p style="font-size: 7.8pt; margin-top: 4px;">
      &bull; Direct ZIP download from admin panel.<br>
      &bull; Optional Amazon S3 cloud replication.<br>
      &bull; Automated email alerts to administrator upon backup completion or failure.
    </p>
  </div>
</div>

<div class="callout callout-info">
  <strong>Strict Retention Policy:</strong> Retains all backups for 1 day, daily backups for 7 days, weekly snapshots for 4 weeks, monthly archives for 12 months, and yearly milestones for 2 years, enforcing an automatic 5,000 MB storage cap.
</div>

<h2>5.4 Tamper-Evident Cryptographic Audit Trail</h2>
<p>
  The system logs all administrative, technician, and model operations in a secure audit log (<code>/admin/audit-logs</code>):
</p>
<table>
  <thead>
    <tr>
      <th style="width: 25%;">Audit Component</th>
      <th style="width: 40%;">Mechanism &amp; Implementation</th>
      <th style="width: 35%;">Compliance &amp; Security Value</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>HMAC-SHA256 Chaining</strong></td>
      <td>Each log entry computes a cryptographic hash over timestamp, user ID, event, and prior hash.</td>
      <td>Prevents database tampering: modifying any historical record breaks the cryptographic chain.</td>
    </tr>
    <tr>
      <td><strong>Model Mutation Tracking</strong></td>
      <td>Spatie ActivityLog records old and new values on all Eloquent model updates.</td>
      <td>Provides complete accountability of who changed stock counts, prices, or statuses.</td>
    </tr>
    <tr>
      <td><strong>Security Events</strong></td>
      <td>Logs login successes, failed password attempts, MFA events, and impersonation starts/stops.</td>
      <td>Enables instant forensic investigation during suspicious access incidents.</td>
    </tr>
    <tr>
      <td><strong>Compliance Export</strong></td>
      <td>One-click CSV and PDF export of audit entries with date range filters.</td>
      <td>Simplifies external operational audits and regulatory compliance.</td>
    </tr>
  </tbody>
</table>
"""

# ==========================================
# PAGE 12: CHAPTER 6 PART 1 (REPORTING & IMPORTS)
# ==========================================
p12_content = """
<h1>6. Analytics, Bulk Data Operations &amp; AI Intelligence</h1>

<p class="section-intro">
  RGV-System equips managers with actionable business intelligence through multi-domain reporting, asynchronous bulk data ingestion/export, and a robust, multi-tier Artificial Intelligence engine.
</p>

<h2>6.1 Business Reporting &amp; Analytics Engine</h2>
<table>
  <thead>
    <tr>
      <th style="width: 22%;">Report Domain</th>
      <th style="width: 42%;">Metrics Analyzed &amp; Visualized</th>
      <th style="width: 36%;">Supported Export Formats</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Bookings Report</strong></td>
      <td>Total service volume, completion rates, cancellation ratios, popular purpose categories, and technician performance.</td>
      <td>PDF (formatted table), Excel (.xlsx), CSV, JSON.</td>
    </tr>
    <tr>
      <td><strong>Inventory Report</strong></td>
      <td>Total catalog valuation, stock levels by category, low-stock threshold status, and item condition distributions.</td>
      <td>PDF (formatted table), Excel (.xlsx), CSV, JSON.</td>
    </tr>
    <tr>
      <td><strong>Borrow Requests Report</strong></td>
      <td>Requisition trends, most borrowed tools, overdue frequency, employee utilization, and return condition ratios.</td>
      <td>PDF (formatted table), Excel (.xlsx), CSV, JSON.</td>
    </tr>
    <tr>
      <td><strong>User Activity Report</strong></td>
      <td>Login frequencies, security events, MFA adoption, and administrative operation audits.</td>
      <td>PDF (formatted table), Excel (.xlsx), CSV, JSON.</td>
    </tr>
  </tbody>
</table>

<h2>6.2 High-Capacity Bulk Import &amp; Export Engine</h2>
<div class="grid-2">
  <div class="card card-accent-blue">
    <div class="card-title">Bulk Ingestion Engine (Imports)</div>
    <p style="font-size: 8pt; margin-top: 4px;">
      Mass-import inventory catalogs and user directories from CSV or Excel spreadsheets (<code>/admin/import-export</code>). Features downloadable standard templates and intelligent duplicate-handling:
    </p>
    <ul style="font-size: 7.8pt; margin-top: 4px;">
      <li><strong>Skip:</strong> Ignores duplicate items while importing new entries.</li>
      <li><strong>Update:</strong> Refreshes existing records with updated stock/prices.</li>
      <li><strong>Abort:</strong> Stops execution on syntax or validation errors.</li>
      <li><strong>Error Logs:</strong> Download row-by-row failure reports with error notes.</li>
    </ul>
  </div>
  <div class="card card-accent-purple">
    <div class="card-title">Custom Data Export Builder</div>
    <p style="font-size: 8pt; margin-top: 4px;">
      Export complete databases or filtered slices with custom column selection:
    </p>
    <ul style="font-size: 7.8pt; margin-top: 4px;">
      <li><strong>Asynchronous Background Queuing:</strong> Large exports process via Laravel Queues without freezing HTTP requests.</li>
      <li><strong>Multi-Format:</strong> Formatted PDF tables, Microsoft Excel (.xlsx), and raw CSV.</li>
      <li><strong>Export Audit Logs:</strong> Tracks who requested exports and when files were downloaded.</li>
    </ul>
  </div>
</div>

<div class="callout callout-info">
  <strong>Queued Export Execution:</strong> High-volume exports containing thousands of records execute in background worker queues (<code>ExportLog</code> model), allowing managers to continue working without browser timeouts.
</div>
"""

# ==========================================
# PAGE 13: CHAPTER 6 PART 2 (AI SUITE)
# ==========================================
p13_content = """
<h1>6.3 Multi-Tier Artificial Intelligence (AI) Suite</h1>
<p class="section-intro">
  The system incorporates a multi-provider fallback architecture ensuring AI tools remain available regardless of external API outages or rate limits:
</p>

<div class="diagram-container">
  <div style="font-weight: 700; color: #1e3a8a; font-size: 8.5pt; margin-bottom: 8px; text-transform: uppercase;">Multi-Tier AI Provider Fallback Architecture</div>
  <div class="diagram-flow">
    <div class="diagram-node">
      Tier 1: Ollama Local
      <small>Private, Zero Latency (llama3.1)</small>
    </div>
    <div class="diagram-arrow">&rarr;</div>
    <div class="diagram-node">
      Tier 2: Google Gemini
      <small>gemini-2.0-flash (Cloud Intelligence)</small>
    </div>
    <div class="diagram-arrow">&rarr;</div>
    <div class="diagram-node">
      Tier 3: OpenAI GPT
      <small>openai-php Secondary Fallback</small>
    </div>
    <div class="diagram-arrow">&rarr;</div>
    <div class="diagram-node">
      Tier 4: Rule-Based
      <small>Deterministic System Fallback</small>
    </div>
  </div>
</div>

<h2>Core AI Capabilities</h2>
<div class="grid-3">
  <div class="card card-accent-blue">
    <div class="card-title">1. Operational Chatbot</div>
    <p style="font-size: 7.8pt; margin-top: 4px;">
      Admin chatbot answering contextual operational questions (e.g., <em>"Which equipment is currently overdue?"</em>, <em>"How many repair bookings are scheduled for this week?"</em>) grounded in live database queries.
    </p>
  </div>
  <div class="card card-accent-green">
    <div class="card-title">2. Predictive Booking Forecast</div>
    <p style="font-size: 7.8pt; margin-top: 4px;">
      Predictive analysis of historical booking patterns, seasonal spikes, and customer demand curves, providing staffing and dispatch recommendations for coming months.
    </p>
  </div>
  <div class="card card-accent-amber">
    <div class="card-title">3. Inventory Optimization</div>
    <p style="font-size: 7.8pt; margin-top: 4px;">
      AI algorithms calculate optimal reorder points, flag fast-depleting consumables before stock-outs occur, and highlight stagnant tools to maximize capital efficiency.
    </p>
  </div>
</div>

<h2>AI Architecture Highlights</h2>
<table>
  <thead>
    <tr>
      <th style="width: 25%;">Component</th>
      <th style="width: 38%;">Technical Implementation</th>
      <th style="width: 37%;">Operational Advantage</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Context Grounding</strong></td>
      <td>Queries execute parameterized aggregate queries on PostgreSQL prior to LLM synthesis.</td>
      <td>Eliminates hallucination; all answers reflect factual, real-time database numbers.</td>
    </tr>
    <tr>
      <td><strong>Data Privacy</strong></td>
      <td>Primary provider is a local Ollama instance running inside the private infrastructure.</td>
      <td>Proprietary client details and booking records never leave the local network.</td>
    </tr>
    <tr>
      <td><strong>Access Restriction</strong></td>
      <td>Chatbot API (<code>/api/chatbot/query</code>) is rate-limited (30 req/min) and locked to Admin sessions.</td>
      <td>Prevents unauthorized probing or token depletion by unauthenticated callers.</td>
    </tr>
  </tbody>
</table>

<div class="callout callout-success">
  <strong>Continuous Availability:</strong> If local Ollama is offline, requests cascade automatically to Google Gemini 2.0 Flash, then OpenAI, and finally internal rule-based algorithms, ensuring zero downtime.
</div>
"""

# ==========================================
# PAGE 14: CHAPTER 7 PART 1 (NOTIFICATIONS)
# ==========================================
p14_content = """
<h1>7. Omnichannel Notifications &amp; System Configuration</h1>

<p class="section-intro">
  The system ensures tight coordination across customers, field technicians, and management through a dual-channel notification architecture and a centralized 7-tab system configuration suite.
</p>

<h2>7.1 Complete 14-Class Notification Directory</h2>
<p>
  Every significant business event triggers a dedicated notification class dispatching alerts across both in-app notification bells and responsive HTML transactional emails:
</p>

<table>
  <thead>
    <tr>
      <th style="width: 26%;">Notification Class</th>
      <th style="width: 20%;">Target Recipient</th>
      <th style="width: 24%;">Triggering Event</th>
      <th style="width: 30%;">Operational Action Conveyed</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>NewBookingReceived</code></td>
      <td><span class="badge badge-purple">Admin</span></td>
      <td>Customer submits public booking.</td>
      <td>Alerts dispatchers to review schedule and assign technician.</td>
    </tr>
    <tr>
      <td><code>BookingApproved</code></td>
      <td><span class="badge badge-green">Customer</span></td>
      <td>Admin approves service booking.</td>
      <td>Confirms service date, time slot, and booking reference.</td>
    </tr>
    <tr>
      <td><code>BookingRejected</code></td>
      <td><span class="badge badge-green">Customer</span></td>
      <td>Admin declines service booking.</td>
      <td>Explains specific reason for refusal and alternative options.</td>
    </tr>
    <tr>
      <td><code>BookingAssigned</code></td>
      <td><span class="badge badge-blue">Employee</span></td>
      <td>Admin allocates technician to job.</td>
      <td>Transmits client contact info, service address, and scope of work.</td>
    </tr>
    <tr>
      <td><code>BookingCompleted</code></td>
      <td><span class="badge badge-green">Customer</span> &amp; <span class="badge badge-purple">Admin</span></td>
      <td>Technician marks job finished.</td>
      <td>Sends final service summary and generates billing invoice.</td>
    </tr>
    <tr>
      <td><code>BookingCancelled</code></td>
      <td><span class="badge badge-purple">Admin</span> &amp; <span class="badge badge-blue">Employee</span></td>
      <td>Booking cancelled by party.</td>
      <td>Releases technician schedule and cancels preparation.</td>
    </tr>
    <tr>
      <td><code>NewBorrowRequestReceived</code></td>
      <td><span class="badge badge-purple">Admin</span></td>
      <td>Technician requests tools.</td>
      <td>Alerts warehouse manager to review and authorize tool release.</td>
    </tr>
    <tr>
      <td><code>BorrowRequestApproved</code></td>
      <td><span class="badge badge-blue">Employee</span></td>
      <td>Admin approves equipment release.</td>
      <td>Instructs technician that tools are ready for pickup.</td>
    </tr>
    <tr>
      <td><code>BorrowRequestRejected</code></td>
      <td><span class="badge badge-blue">Employee</span></td>
      <td>Admin rejects equipment release.</td>
      <td>Provides reason (e.g., equipment reserved for higher priority).</td>
    </tr>
    <tr>
      <td><code>BorrowRequestBorrowed</code></td>
      <td><span class="badge badge-blue">Employee</span></td>
      <td>Items handed over to technician.</td>
      <td>Logs custody transfer and sets active due date clock.</td>
    </tr>
    <tr>
      <td><code>BorrowRequestReturned</code></td>
      <td><span class="badge badge-purple">Admin</span> &amp; <span class="badge badge-blue">Employee</span></td>
      <td>Equipment returned to storage.</td>
      <td>Confirms stock restoration and records condition ratings.</td>
    </tr>
    <tr>
      <td><code>LowStockAlert</code></td>
      <td><span class="badge badge-purple">Admin</span></td>
      <td>Item quantity &le; threshold.</td>
      <td>Warns managers to replenish critical tools or consumables.</td>
    </tr>
    <tr>
      <td><code>UserStatusChanged</code></td>
      <td><span class="badge badge-blue">Employee</span></td>
      <td>Admin activates/deactivates user.</td>
      <td>Notifies user of account privilege updates.</td>
    </tr>
  </tbody>
</table>

<div class="callout callout-info">
  <strong>Per-User Preference Matrix:</strong> Users can customize their alert experience in their profile (<code>NotificationPreference</code> model). Each user independently toggles <em>Email Delivery</em> and <em>In-App Delivery</em> for every notification type according to their personal workflow.
</div>
"""

# ==========================================
# PAGE 15: CHAPTER 7 PART 2 (SITE SETTINGS)
# ==========================================
p15_content = """
<h1>7.2 Site Settings (7-Tab Administrative Suite)</h1>
<p class="section-intro">
  Administrators control system-wide behavior through <code>/admin/settings</code> with real-time environment persistence:
</p>

<table>
  <thead>
    <tr>
      <th style="width: 18%;">Settings Tab</th>
      <th style="width: 42%;">Configurable Parameters</th>
      <th style="width: 40%;">System Impact</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>1. Branding</strong></td>
      <td>Application Title, Tagline, Brand Accent Color, Logo.</td>
      <td>Dynamically skins navigation bars, login screens, and PDF headers.</td>
    </tr>
    <tr>
      <td><strong>2. Email (SMTP)</strong></td>
      <td>Host, Port, Encryption (TLS/SSL), Username, Password, From Address.</td>
      <td>Governs transactional email reliability and delivery infrastructure.</td>
    </tr>
    <tr>
      <td><strong>3. Security</strong></td>
      <td>Password minimum length, session lifetime, max login attempts.</td>
      <td>Enforces defense-in-depth security policies across all surfaces.</td>
    </tr>
    <tr>
      <td><strong>4. Backup</strong></td>
      <td>Notification email, admin update email, S3 cloud storage toggle.</td>
      <td>Coordinates disaster recovery destinations and health alerts.</td>
    </tr>
    <tr>
      <td><strong>5. Notifications</strong></td>
      <td>System alert email recipients, global email delivery master toggle.</td>
      <td>Permits pausing outbound emails during bulk maintenance operations.</td>
    </tr>
    <tr>
      <td><strong>6. Maintenance</strong></td>
      <td>Maintenance mode toggle, custom public message, IP whitelist.</td>
      <td>Safely locks public/employee access while allowing admins to perform upgrades.</td>
    </tr>
    <tr>
      <td><strong>7. API Limits</strong></td>
      <td>Tiered rate limits: Public (30/m), Standard (60/m), Premium (300/m), Admin (1000/m).</td>
      <td>Protects server from Denial of Service (DoS) and API abuse.</td>
    </tr>
  </tbody>
</table>

<h2>7.3 System Health Monitoring</h2>
<p>
  Administrators can inspect real-time system vitals through the dedicated System Health screen (<code>/admin/system-health</code>):
</p>
<div class="grid-3">
  <div class="card card-accent-blue">
    <div class="card-title">&bull; Database Vitals</div>
    <p style="font-size: 7.8pt; margin-top: 3px;">
      PostgreSQL connection status, active session count, database size, and query response latency.
    </p>
  </div>
  <div class="card card-accent-green">
    <div class="card-title">&bull; Queue Workers</div>
    <p style="font-size: 7.8pt; margin-top: 3px;">
      Queue listener status, pending background jobs, and failed job exception logs.
    </p>
  </div>
  <div class="card card-accent-purple">
    <div class="card-title">&bull; Storage &amp; Backups</div>
    <p style="font-size: 7.8pt; margin-top: 3px;">
      Disk space availability, backup archive counts, and last completed snapshot integrity.
    </p>
  </div>
</div>

<div class="callout callout-success">
  <strong>Dynamic Configuration Persistence:</strong> Settings changes save directly to configuration files, taking effect immediately without requiring application rebuilds or server reboots.
</div>
"""

# ==========================================
# PAGE 16: CHAPTER 8 PART 1 (MASTER MATRIX)
# ==========================================
p16_content = """
<h1>8. Master Feature Comparison Matrix</h1>

<p class="section-intro">
  The following master matrix provides a complete, side-by-side comparison of system capabilities across all four client interfaces:
</p>

<table>
  <thead>
    <tr>
      <th style="width: 34%;">System Feature / Functional Capability</th>
      <th style="width: 15%; text-align: center;">Public Web</th>
      <th style="width: 17%; text-align: center;">Employee Web</th>
      <th style="width: 17%; text-align: center;">Employee Mobile</th>
      <th style="width: 17%; text-align: center;">Admin Portal</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Submit Service Booking Requests</td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
    </tr>
    <tr>
      <td>Real-Time Booking Status Tracking</td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
    </tr>
    <tr>
      <td>Public Inventory QR Code Verification</td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
    </tr>
    <tr>
      <td>Assigned Booking Work Order Execution</td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
    </tr>
    <tr>
      <td>Inventory Catalog Browsing &amp; Details</td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
    </tr>
    <tr>
      <td>Hardware Camera QR Code Scanning</td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes (Native)</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
    </tr>
    <tr>
      <td>Equipment Requisition (Borrow Requests)</td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
    </tr>
    <tr>
      <td>Equipment Return &amp; Condition Reporting</td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
    </tr>
    <tr>
      <td>Interactive Visual Dispatch Calendar</td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
    </tr>
    <tr>
      <td>PDF Work Orders &amp; Invoice Generation</td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
    </tr>
    <tr>
      <td>Multi-Domain Reports (PDF/Excel/CSV)</td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
    </tr>
    <tr>
      <td>Bulk CSV/Excel Ingestion &amp; Export</td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
    </tr>
    <tr>
      <td>Automated Daily Encrypted Backups</td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
    </tr>
    <tr>
      <td>Tamper-Evident SHA-256 Audit Trail</td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
    </tr>
    <tr>
      <td>Multi-Entity Soft-Delete Trash Recovery</td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-blue">Own Requests</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Full (5 Types)</span></td>
    </tr>
    <tr>
      <td>AI Forecast, Chatbot &amp; Stock Recommendations</td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-gray">No</span></td>
      <td style="text-align: center;"><span class="badge badge-green">Yes</span></td>
    </tr>
  </tbody>
</table>
"""

# ==========================================
# PAGE 17: CHAPTER 8 PART 2 (TECH SPECS & CONCLUSION)
# ==========================================
p17_content = """
<h1>8.2 Official Technical Specifications</h1>
<p class="section-intro">
  Summary of technical specifications, environment requirements, and architectural standards:
</p>

<table>
  <thead>
    <tr>
      <th style="width: 25%;">System Attribute</th>
      <th style="width: 75%;">Technical Specification &amp; Configuration Value</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Application Version</strong></td>
      <td>RGV-System v1.6 (Release Date: October 2026)</td>
    </tr>
    <tr>
      <td><strong>Backend Architecture</strong></td>
      <td>Laravel 12.0 LTS &bull; PHP 8.2+ &bull; Eloquent ORM &bull; Service / Repository Layer Pattern</td>
    </tr>
    <tr>
      <td><strong>Database Engine</strong></td>
      <td>Supabase PostgreSQL (Session Pooler &amp; Direct Connection) &bull; Hermetic SQLite in-memory for testing</td>
    </tr>
    <tr>
      <td><strong>Employee Mobile Platform</strong></td>
      <td>Flutter Android Native Client &bull; <code>supabase_flutter</code> &bull; <code>mobile_scanner</code> hardware integration</td>
    </tr>
    <tr>
      <td><strong>Frontend Stack</strong></td>
      <td>Tailwind CSS 3.1 &bull; Alpine.js 3.4 &bull; Vite 7.0 &bull; Blade Templates &bull; Axios HTTP</td>
    </tr>
    <tr>
      <td><strong>Security Protocols</strong></td>
      <td>Spatie RBAC &bull; TOTP Two-Factor (AAL2) &bull; Email OTP &bull; CSRF &bull; HSTS &bull; CSP &bull; Tiered Rate Throttling</td>
    </tr>
    <tr>
      <td><strong>Integrity &amp; Auditing</strong></td>
      <td>Cryptographic HMAC-SHA256 Chained Checksum Audit Logging &bull; SHA-256 Backup Archive Verification</td>
    </tr>
    <tr>
      <td><strong>AI Intelligence Stack</strong></td>
      <td>Multi-Tier Fallback: Ollama Local (llama3.1) &rarr; Google Gemini 2.0 Flash &rarr; OpenAI GPT &rarr; Rule-Based</td>
    </tr>
  </tbody>
</table>

<h2>8.3 System Verification Checklist</h2>
<div class="grid-3">
  <div class="card card-accent-green">
    <div class="card-title">&bull; Hermetic Test Suite</div>
    <p style="font-size: 7.8pt; margin-top: 3px;">
      100% test isolation forced through <code>phpunit.xml</code> SQLite in-memory environment, shielding live database.
    </p>
  </div>
  <div class="card card-accent-blue">
    <div class="card-title">&bull; Mobile Independence</div>
    <p style="font-size: 7.8pt; margin-top: 3px;">
      Verified field execution: Flutter app borrows and returns items without web process dependencies.
    </p>
  </div>
  <div class="card card-accent-purple">
    <div class="card-title">&bull; Disaster Resilience</div>
    <p style="font-size: 7.8pt; margin-top: 3px;">
      Automated daily snapshots and SHA-256 verification ensure swift, reliable disaster recovery.
    </p>
  </div>
</div>

<div class="callout callout-success" style="margin-top: 10px;">
  <strong>Executive Summary &amp; Operational Readiness:</strong> The RGV Multi-Tech Services platform provides a battle-tested, synchronized, and resilient infrastructure. By unifying public booking convenience, technician mobility via native Flutter Android scanning, and administrative governance powered by AI analytics and cryptographic audit controls, the system ensures zero data loss, transparent operations, and maximum business efficiency.
</div>
"""

# Assemble all pages
pages = [
    (1, "", p1_content, True),
    (2, "Architecture & Executive Overview", p2_content, False),
    (3, "Technology Stack Deep Dive", p3_content, False),
    (4, "Roles & Public Booking Engine", p4_content, False),
    (5, "Tracking & QR Asset Verification", p5_content, False),
    (6, "Employee Web Portal Operations", p6_content, False),
    (7, "Flutter Android Field Operations", p7_content, False),
    (8, "Admin Command Dashboard & Bookings", p8_content, False),
    (9, "Inventory & Requisition Governance", p9_content, False),
    (10, "User Admin, RBAC & Trash System", p10_content, False),
    (11, "Automated Backups & SHA-256 Audit", p11_content, False),
    (12, "Business Reporting & Data Imports", p12_content, False),
    (13, "Multi-Tier Artificial Intelligence", p13_content, False),
    (14, "Omnichannel Notifications System", p14_content, False),
    (15, "Site Settings & Configuration", p15_content, False),
    (16, "Master Feature Comparison Matrix", p16_content, False),
    (17, "Technical Specifications & Readiness", p17_content, False)
]

page_htmls = [make_page(p[0], p[1], p[2], p[3]) for p in pages]

full_document_html = f"""<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>RGV Multi-Tech Services — Whole Features Specification</title>
<style>
  @page {{
    size: A4 portrait;
    margin: 0;
  }}

  * {{
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }}

  body {{
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #1e293b;
    background: #e2e8f0;
    font-size: 8.8pt;
    line-height: 1.5;
  }}

  .sheet {{
    width: 210mm;
    height: 297mm;
    margin: 0 auto;
    background: #ffffff;
    page-break-after: always;
    break-after: page;
    position: relative;
    overflow: hidden;
  }}

  .cover-sheet {{
    padding: 24mm 18mm 20mm 18mm;
  }}

  .content-sheet {{
    padding: 14mm 16mm 14mm 16mm;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }}

  .page-header {{
    height: 8mm;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #cbd5e1;
    font-size: 7.6pt;
    color: #64748b;
    margin-bottom: 6mm;
  }}

  .page-header .crumb {{
    font-weight: 600;
    color: #3b82f6;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }}

  .page-header .pnum {{
    font-weight: 600;
    color: #475569;
  }}

  .sheet-body {{
    flex: 1;
  }}

  .page-footer {{
    height: 7mm;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top: 1px solid #e2e8f0;
    font-size: 7.2pt;
    color: #94a3b8;
    margin-top: 4mm;
  }}

  /* Headings */
  h1, h2, h3, h4 {{
    color: #0f172a;
    font-weight: 700;
  }}

  h1 {{
    font-size: 14.5pt;
    margin-bottom: 6px;
    padding-bottom: 4px;
    border-bottom: 2px solid #2563eb;
    color: #1e3a8a;
  }}

  h2 {{
    font-size: 11pt;
    margin-top: 10px;
    margin-bottom: 4px;
    color: #1e40af;
  }}

  h3 {{
    font-size: 9.5pt;
    margin-top: 8px;
    margin-bottom: 3px;
    color: #334155;
  }}

  p {{
    margin-bottom: 5px;
    text-align: justify;
  }}

  ul, ol {{
    margin-left: 16px;
    margin-bottom: 6px;
  }}

  li {{
    margin-bottom: 2.5px;
  }}

  /* Cover Page */
  .cover-container {{
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    text-align: center;
  }}

  .cover-top {{
    margin-top: 10px;
  }}

  .cover-logo {{
    max-width: 155px;
    height: auto;
    margin-bottom: 18px;
    filter: drop-shadow(0 4px 6px rgba(0,0,0,0.08));
  }}

  .cover-org {{
    font-size: 12.5pt;
    font-weight: 700;
    color: #475569;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 10px;
  }}

  .cover-title {{
    font-size: 23pt;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
    margin-bottom: 10px;
    letter-spacing: -0.5px;
  }}

  .cover-title span {{
    color: #2563eb;
  }}

  .cover-subtitle {{
    font-size: 11pt;
    font-weight: 400;
    color: #475569;
    max-width: 620px;
    margin: 0 auto 24px auto;
    line-height: 1.45;
  }}

  .cover-tags {{
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 28px;
  }}

  .cover-tag {{
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    padding: 3.5px 10px;
    border-radius: 9999px;
    font-size: 8pt;
    font-weight: 600;
  }}

  .cover-stats-grid {{
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 9px;
    max-width: 640px;
    margin: 0 auto 24px auto;
  }}

  .stat-card {{
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 9px 5px;
    text-align: center;
  }}

  .stat-card .num {{
    font-size: 16pt;
    font-weight: 800;
    color: #1e40af;
    line-height: 1.1;
  }}

  .stat-card .lbl {{
    font-size: 7pt;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 3px;
  }}

  .cover-bottom {{
    border-top: 1px solid #e2e8f0;
    padding-top: 14px;
    font-size: 8pt;
    color: #64748b;
    line-height: 1.5;
  }}

  /* Feature Grid & Cards */
  .grid-2 {{
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 8px;
  }}

  .grid-3 {{
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 8px;
  }}

  .grid-4 {{
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-bottom: 8px;
  }}

  .card {{
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    padding: 8px 10px;
    margin-bottom: 6px;
  }}

  .card-header {{
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 4px;
  }}

  .card-title {{
    font-size: 9pt;
    font-weight: 700;
    color: #0f172a;
  }}

  .card-accent-blue {{
    border-left: 3.5px solid #2563eb;
    background: #f8fafc;
  }}

  .card-accent-green {{
    border-left: 3.5px solid #059669;
    background: #f0fdf4;
  }}

  .card-accent-amber {{
    border-left: 3.5px solid #d97706;
    background: #fffbeb;
  }}

  .card-accent-purple {{
    border-left: 3.5px solid #7c3aed;
    background: #faf5ff;
  }}

  .card-accent-red {{
    border-left: 3.5px solid #dc2626;
    background: #fef2f2;
  }}

  /* Callout box */
  .callout {{
    padding: 8px 12px;
    border-radius: 5px;
    margin: 7px 0;
    font-size: 8.3pt;
    line-height: 1.45;
  }}

  .callout-info {{
    background: #eff6ff;
    border-left: 3.5px solid #3b82f6;
    color: #1e3a8a;
  }}

  .callout-success {{
    background: #f0fdf4;
    border-left: 3.5px solid #10b981;
    color: #064e3b;
  }}

  .callout-warning {{
    background: #fffbeb;
    border-left: 3.5px solid #f59e0b;
    color: #78350f;
  }}

  /* Badges */
  .badge {{
    display: inline-block;
    padding: 2px 5px;
    border-radius: 3px;
    font-size: 6.8pt;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
  }}

  .badge-blue {{ background: #dbeafe; color: #1e40af; }}
  .badge-green {{ background: #dcfce7; color: #15803d; }}
  .badge-amber {{ background: #fef3c7; color: #b45309; }}
  .badge-purple {{ background: #f3e8ff; color: #6b21a8; }}
  .badge-red {{ background: #fee2e2; color: #b91c1c; }}
  .badge-gray {{ background: #f1f5f9; color: #475569; }}

  /* Tables */
  table {{
    width: 100%;
    border-collapse: collapse;
    margin: 6px 0 10px 0;
    font-size: 8.1pt;
  }}

  th, td {{
    padding: 4.5px 7px;
    text-align: left;
    border: 1px solid #e2e8f0;
    vertical-align: top;
  }}

  th {{
    background-color: #1e3a8a;
    color: #ffffff;
    font-weight: 600;
    font-size: 7.5pt;
    text-transform: uppercase;
    letter-spacing: 0.4px;
  }}

  tr:nth-child(even) {{
    background-color: #f8fafc;
  }}

  /* Diagram Box */
  .diagram-container {{
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 9px;
    margin: 8px 0;
    text-align: center;
  }}

  .diagram-flow {{
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    margin: 5px 0;
  }}

  .diagram-node {{
    flex: 1;
    background: #ffffff;
    border: 1.5px solid #3b82f6;
    border-radius: 5px;
    padding: 6px 4px;
    font-size: 7.5pt;
    font-weight: 600;
    color: #1e3a8a;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
  }}

  .diagram-node small {{
    display: block;
    font-size: 6.4pt;
    color: #64748b;
    font-weight: 400;
    margin-top: 1.5px;
  }}

  .diagram-arrow {{
    font-weight: bold;
    color: #94a3b8;
    font-size: 10pt;
  }}

  code {{
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 7.6pt;
    background: #f1f5f9;
    padding: 1px 3.5px;
    border-radius: 3px;
    color: #0f172a;
  }}

  .section-intro {{
    font-size: 8.8pt;
    color: #334155;
    margin-bottom: 7px;
    line-height: 1.48;
  }}
</style>
</head>
<body>
{"".join(page_htmls)}
</body>
</html>
"""

with open(OUTPUT_HTML, "w", encoding="utf-8") as f:
    f.write(full_document_html)

print(f"Generated clean HTML ({len(full_document_html)} bytes)")

# Compile using Microsoft Edge headless with --no-pdf-header-footer
cmd = [
    EDGE_PATH,
    "--headless",
    "--disable-gpu",
    "--run-all-compositor-stages-before-draw",
    f"--print-to-pdf={OUTPUT_PDF}",
    "--no-pdf-header-footer",
    OUTPUT_HTML
]

print(f"Compiling PDF via {EDGE_PATH} with --no-pdf-header-footer...")
res = subprocess.run(cmd, capture_output=True, text=True)

if os.path.exists(OUTPUT_PDF):
    size = os.path.getsize(OUTPUT_PDF)
    reader = pypdf.PdfReader(OUTPUT_PDF)
    pages = len(reader.pages)
    print("\n" + "="*50)
    print(f"SUCCESS: Generated PDF at:")
    print(f"  {OUTPUT_PDF}")
    print(f"File Size: {size:,} bytes")
    print(f"Total Pages: {pages} pages")
    print("="*50)
else:
    print("ERROR: PDF was not generated.")
    print("Edge stderr:", res.stderr)
    sys.exit(1)

