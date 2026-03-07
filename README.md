# Bank Application Forms Plugin

A comprehensive WordPress plugin providing pixel-perfect multi-step bank application forms for both personal and corporate accounts.

## Features

✅ **Two Complete Application Forms:**
- Personal Bank Account Application Form (7 steps)
- Corporate Bank Account Application Form (8 steps)

✅ **Professional UI/UX:**
- Responsive design (mobile, tablet, desktop)
- Progress bar tracking
- Step indicator navigation
- Smooth transitions between steps

✅ **Comprehensive Data Collection:**
- Personal/Corporate information
- Financial details
- Transfer activity expectations
- Authorized signatories and officers
- Payment instructions
- Legal declarations and AML/KYC compliance

✅ **Security & Compliance:**
- WordPress nonce verification
- Data sanitization and validation
- AJAX form submission
- Secure post storage
- File upload support

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin dashboard
3. The forms are ready to use via shortcodes

## Usage

### Personal Bank Account Application Form

Add this shortcode to any page or post:

```
[bank_application_form]
```

> 📌 **Note:** this shortcode now renders a two‑tab selector at the top of the page. Visitors can toggle between **Personal Account** and **Corporate Account** without needing a separate shortcode. The corporate form is still available on its own via `[bank_corporate_form]` if you prefer.

**Form Steps (7):**
1. Account Selection
2. Personal Profile
3. Expected Transfer Activity
4. Bank Account & Referral
5. Fee Payment Banking Information
6. Payment Instructions
7. Agreement & Declaration

**Account Types:**
- Savings Account (€25,000)
- Custody Account (€25,000)
- Numbered Account (€50,000)
- Cryptocurrency Account (€25,000)

### Corporate Bank Account Application Form

Add this shortcode to any page or post:

```
[bank_corporate_form]
```

**Form Steps (8):**
1. Account Selection
2. Company Profile Information
3. Business Details & Financial Information
4. Authorized Signatories & Officers
5. Expected Transfer Activity
6. Payment Instructions
7. Bank Account & Financial Information
8. Agreement and Declaration

**Account Types:**
- Business Checking Account (€50,000)
- Business Savings Account (€50,000)
- Merchant Services Account (€75,000)
- Trading/Investment Account (€100,000)

## File Structure

```
/form-plugin/
├── wp-plugin.php              # Main plugin file with all functions
├── index.html                 # Personal form standalone HTML
├── index-corporate.html       # Corporate form standalone HTML
├── assets/
│   ├── style.css             # Styling for both forms
│   └── script.js             # JavaScript functionality
├── docker-compose.yml        # Docker setup for local development
└── README.md                 # This file
```

## Form Data Storage

Both forms create custom WordPress post types:

- **Personal Applications:** Post type `bank_application`
- **Corporate Applications:** Post type `corporate_application`

All form data is stored as post meta, making it accessible through WordPress admin.

## Payment Methods

Both forms support multiple payment methods for account opening fees:

### Method 1: International Wire (SWIFT)
- **EURO:** Bank: Wise Europe | SWIFT: TRWIBEB1XXX | IBAN: BE31905177979455
- **USD:** Bank: Wise US Inc. | SWIFT: TRWIUS35XXX | Account: 205414015428310

### Method 2: Cryptocurrency
- **USDT (TRC20):** TPYjsZk3bZRZAVhBoRZcdyZkPq9NN6S6Y

## Document Requirements

### Personal Form Required Documents
- Full color passport/ID photo
- Proof of account opening fees payment

### Corporate Form Required Documents
- Certificate of Incorporation
- Board Resolution
- Certificate of Beneficial Owners
- Proof of Business Registration
- Passports/IDs of All Authorized Signatories
- Proof of Business Address

## Customization

### Styling

The forms use a unified CSS file (`assets/style.css`) that can be customized:

- Colors: `#bfa14a` (gold accent), `#1e293b` (dark blue)
- Fonts: DM Sans, Inter
- Responsive breakpoints: 600px, 900px

### Payment Instructions

Modify payment details in `wp-plugin.php` functions:
- `submit_bank_form()` - Personal form submission
- `submit_corporate_form()` - Corporate form submission

### Form Fields

All form fields can be modified in the respective shortcode functions:
- `bank_application_form()` - Personal form
- `corporate_application_form()` - Corporate form

## Local Development

### With Docker

Start WordPress locally with included Docker setup:

```bash
docker-compose up -d
```

Access at: `http://localhost:8080`

### With Python Server (Static HTML Preview)

```bash
python3 -m http.server 8888
```

Access forms at: `http://localhost:8888`

## Browser Compatibility

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers

## Compliance Notes

- All forms include AML/KYC compliance acknowledgments
- Supporting documentation collection for regulatory requirements
- Nonce verification for AJAX submissions
- Comprehensive data sanitization

## Support

For issues or questions, please refer to the WordPress admin panel where all submitted applications are stored as posts with metadata.

## Version

Current Version: 1.0

## License

GPL v2 or later

---

**Created for:** Prominence Bank Account Application
**Form Type:** Multi-Step, Professional, Responsive
**Last Updated:** March 2026
