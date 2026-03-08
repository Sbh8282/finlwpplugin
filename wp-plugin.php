<?php
/*
Plugin Name: Bank Application Multi Step Form
Description: Pixel perfect multi step banking form like Forminator Pro.
Version: 1.0
Author: Your Name
License: GPL v2 or later
*/

// Enqueue scripts and styles
function enqueue_bank_form_assets() {
    wp_enqueue_script('bank-form-script', plugin_dir_url(__FILE__) . 'assets/script.js', array('jquery'), '1.0', true);
    wp_enqueue_style('bank-form-style', plugin_dir_url(__FILE__) . 'assets/style.css', array(), '1.0');
    
    // Localize script with AJAX URL and nonce
    wp_localize_script('bank-form-script', 'bankFormAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'bankNonce' => wp_create_nonce('bank_form_nonce'),
        'corporateNonce' => wp_create_nonce('corporate_form_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_bank_form_assets');

// Create custom post types
function create_bank_application_post_type() {
    register_post_type('bank_application',
        array(
            'labels' => array(
                'name' => 'Bank Applications',
                'singular_name' => 'Bank Application'
            ),
            'public' => false,
            'show_ui' => true,
            'supports' => array('title'),
            'capability_type' => 'post',
        )
    );

    register_post_type('corporate_application',
        array(
            'labels' => array(
                'name' => 'Corporate Applications',
                'singular_name' => 'Corporate Application'
            ),
            'public' => false,
            'show_ui' => true,
            'supports' => array('title'),
            'capability_type' => 'post',
        )
    );
}
add_action('init', 'create_bank_application_post_type');

function submit_bank_form() {
    check_ajax_referer('bank_form_nonce', '_wpnonce');

    $data = $_POST;

    $post_id = wp_insert_post(array(
        'post_title' => 'Personal Application from ' . sanitize_text_field($data['name'] ?? 'Guest'),
        'post_type' => 'bank_application',
        'post_status' => 'publish'
    ));

    // Save all form data
    foreach ($data as $key => $value) {
        if ($key !== '_wpnonce' && $key !== 'action') {
            if (is_array($value)) {
                update_post_meta($post_id, sanitize_key($key), array_map('sanitize_text_field', $value));
            } else {
                update_post_meta($post_id, sanitize_key($key), sanitize_text_field($value));
            }
        }
    }

    wp_send_json_success(array(
        'message' => 'Personal application submitted successfully',
        'post_id' => $post_id
    ));
}
add_action('wp_ajax_nopriv_submit_bank_form', 'submit_bank_form');
add_action('wp_ajax_submit_bank_form', 'submit_bank_form');

function submit_corporate_form() {
    check_ajax_referer('corporate_form_nonce', '_wpnonce');

    $data = $_POST;

    $post_id = wp_insert_post(array(
        'post_title' => 'Corporate Application from ' . sanitize_text_field($data['company_name'] ?? 'Company'),
        'post_type' => 'corporate_application',
        'post_status' => 'publish'
    ));

    // Save all form data
    foreach ($data as $key => $value) {
        if ($key !== '_wpnonce' && $key !== 'action') {
            if (is_array($value)) {
                update_post_meta($post_id, sanitize_key($key), array_map('sanitize_text_field', $value));
            } else {
                update_post_meta($post_id, sanitize_key($key), sanitize_text_field($value));
            }
        }
    }

    wp_send_json_success(array(
        'message' => 'Corporate application submitted successfully',
        'post_id' => $post_id
    ));
}
add_action('wp_ajax_nopriv_submit_corporate_form', 'submit_corporate_form');
add_action('wp_ajax_submit_corporate_form', 'submit_corporate_form');

// Admin dashboard for personal applications
function add_personal_app_admin_menu() {
    add_menu_page(
        'Personal Applications',
        'Personal Apps',
        'manage_options',
        'personal-applications',
        'personal_applications_page',
        'dashicons-people',
        30
    );
}
add_action('admin_menu', 'add_personal_app_admin_menu');

function personal_applications_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $args = array(
        'post_type' => 'bank_application',
        'posts_per_page' => 20
    );
    $query = new WP_Query($args);
    ?>
    <div class="wrap">
        <h1>Personal Bank Applications</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Account Type</th>
                    <th>Date Submitted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($query->have_posts()) {
                    $query->the_post();
                    $post_id = get_the_ID();
                    $name = get_post_meta($post_id, 'name', true) ?: 'N/A';
                    $email = get_post_meta($post_id, 'email', true) ?: 'N/A';
                    $account_type = get_post_meta($post_id, 'account', true) ?: 'N/A';
                    $date = get_the_date();
                    ?>
                    <tr>
                        <td><?php echo $post_id; ?></td>
                        <td><?php echo esc_html($name); ?></td>
                        <td><?php echo esc_html($email); ?></td>
                        <td><?php echo esc_html($account_type); ?></td>
                        <td><?php echo $date; ?></td>
                        <td><a href="<?php echo admin_url('post.php?post=' . $post_id . '&action=edit'); ?>">View Details</a></td>
                    </tr>
                    <?php
                }
                wp_reset_postdata();
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Admin dashboard for corporate applications
function add_corporate_app_admin_menu() {
    add_menu_page(
        'Corporate Applications',
        'Corporate Apps',
        'manage_options',
        'corporate-applications',
        'corporate_applications_page',
        'dashicons-building',
        31
    );
}
add_action('admin_menu', 'add_corporate_app_admin_menu');

function corporate_applications_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $args = array(
        'post_type' => 'corporate_application',
        'posts_per_page' => 20
    );
    $query = new WP_Query($args);
    ?>
    <div class="wrap">
        <h1>Corporate Bank Applications</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Company Name</th>
                    <th>Email</th>
                    <th>Account Type</th>
                    <th>Date Submitted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($query->have_posts()) {
                    $query->the_post();
                    $post_id = get_the_ID();
                    $company_name = get_post_meta($post_id, 'company_name', true) ?? 'N/A';
                    $email = get_post_meta($post_id, 'business_email', true) ?? 'N/A';
                    $account_type = get_post_meta($post_id, 'account_type', true) ?? 'N/A';
                    $date = get_the_date();
                    ?>
                    <tr>
                        <td><?php echo $post_id; ?></td>
                        <td><?php echo esc_html($company_name); ?></td>
                        <td><?php echo esc_html($email); ?></td>
                        <td><?php echo esc_html($account_type); ?></td>
                        <td><?php echo $date; ?></td>
                        <td><a href="<?php echo admin_url('post.php?post=' . $post_id . '&action=edit'); ?>">View Details</a></td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

function bank_form_assets(){

wp_enqueue_style(
'bank-form-style',
plugin_dir_url(__FILE__).'assets/style.css'
);

wp_enqueue_script(
'bank-form-script',
plugin_dir_url(__FILE__).'assets/script.js',
array('jquery'),
null,
true
);

wp_localize_script('bank-form-script', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));

}

add_action('wp_enqueue_scripts','bank_form_assets');


function bank_application_form(){

ob_start();
?>

<div class="bank-form-container">
    <!-- tabs switching personal / corporate -->
    <div class="account-type-tabs">
        <button type="button" class="tab active" data-target="personal">Personal Account</button>
        <button type="button" class="tab" data-target="corporate">Corporate Account</button>
    </div>

    <div class="form-section" id="personal-section">


<!-- Steps Navigation -->

<div class="steps">
<div class="step active">Account Selection</div>
<div class="step">Personal Profile</div>
<div class="step">Transfer Activity</div>
<div class="step">Funding & Account</div>
<div class="step">Fee Payment</div>
<div class="step">Payment Instructions</div>
<div class="step">Agreed</div>
</div>

<div class="progress-bar">
<div class="progress"></div>
</div>


<form id="bankForm">

<?php wp_nonce_field('bank_form_nonce'); ?>

<input type="hidden" name="action" value="submit_bank_form">

<!-- STEP 1 -->

<div class="form-step active">

<h2>Apply for a New Personal Bank Account</h2>

<div class="account-card selected">
<input type="radio" name="account" value="Savings" checked>
<div>
<h3>Savings Account</h3>
<p>Account Opening Fee (Onboarding & Compliance Processing Fee)</p>
</div>
<span>€25,000</span>
</div>

<div class="account-card">
<input type="radio" name="account" value="Custody">
<div>
<h3>Custody Account</h3>
<p>Account Opening Fee (Onboarding & Compliance Processing Fee)</p>
</div>
<span>€25,000</span>
</div>

<div class="account-card">
<input type="radio" name="account" value="Numbered">
<div>
<h3>Numbered Account</h3>
<p>Account Opening Fee (Onboarding & Compliance Processing Fee)</p>
</div>
<span>€50,000</span>
</div>

<div class="account-card">
<input type="radio" name="account" value="Cryptocurrency">
<div>
<h3>Cryptocurrency Account</h3>
<p>Account Opening Fee (Onboarding & Compliance Processing Fee)</p>
</div>
<span>€25,000</span>
</div>

<button type="button" class="next-btn">Continue</button>

</div>

<!-- removed -->

<div class="form-step">
	<h2>Account Opening Fee — Payment Instructions</h2>
	<p style="font-style:italic;color:#6b7280;margin-bottom:18px;">Applicable to all new account types listed below.</p>
	<div style="margin-bottom:18px;">
		<strong>Account Opening Fee (Onboarding & Compliance Processing Fee)</strong><br>
		<span style="color:#6b7280;font-size:15px;">Payment of the Account Opening Fee does not guarantee approval or account opening.</span>
		<ul style="margin-top:12px;margin-bottom:12px;">
			<li>€25,000 — Euro Account</li>
			<li>$25,000 — USD Account</li>
			<li>€25,000 — Custody Account</li>
			<li>€25,000 — Cryptocurrency Account</li>
			<li>€50,000 — Numbered Account</li>
		</ul>
	</div>
	<div style="margin-bottom:18px;">
		<strong>REFUND POLICY (NO EXCEPTIONS)</strong>
		<p style="color:#6b7280;font-size:15px;">If the application is declined and no account is opened, the Account Opening Fee will be refunded in full by PCM (no PCM deductions). Refunds are issued to the original sender (same payment route) <strong>within ten (10) business days</strong> after the application is formally declined in the Bank’s records.<br><br>If the application is approved and an account is opened, the Account Opening Fee is deemed fully earned upon account opening and is <strong>non-refundable</strong>, as it covers completed onboarding, administrative coordination, and compliance processing services.</p>
	</div>
	<div style="margin-bottom:18px;">
		<strong>PAYMENT OPTION 1: INTERNATIONAL WIRE (SWIFT)</strong>
		<ul style="margin-top:8px;margin-bottom:8px;">
			<li><strong>EURO (EUR) CURRENCY</strong><br>
				<span style="color:#6b7280;font-size:15px;">
					Bank Name: Wise Europe<br>
					Bank Address: Rue du Trône 100, 3rd floor. Brussels. 1050. Belgium<br>
					SWIFT Code: TRWIBEB1XXX<br>
					Account Name: PROMINENCE CLIENT MANAGEMENT<br>
					Account Number/IBAN: BE31905177979455<br>
					Payment Reference / Memo <span style="color:#bfa14a;font-weight:600;">(REQUIRED)</span>: Application ID: 69aa9430 | Onboarding and Compliance Processing Fee
				</span>
			</li>
			<li><strong>USD (US) CURRENCY</strong><br>
				<span style="color:#6b7280;font-size:15px;">
					Bank Name: Wise US Inc.<br>
					Bank Address: 108 W 13th St, Wilmington, DE, 19801, United States<br>
					SWIFT Code: TRWIUS35XXX<br>
					Account Name: PROMINENCE CLIENT MANAGEMENT<br>
					Account Number: 205414015428310<br>
					Payment Reference / Memo <span style="color:#bfa14a;font-weight:600;">(REQUIRED)</span>: Application ID: 69aa9430 | Onboarding and Compliance Processing Fee
				</span>
			</li>
		</ul>
	</div>
	<div style="margin-bottom:18px;">
		<strong>PAYMENT OPTION 2: CRYPTOCURRENCY (USDT TRC20)</strong>
		<ul style="margin-top:8px;margin-bottom:8px;">
			<li><strong>USDT Wallet Address (TRC20):</strong> TPYjsZk3bZRZAVhBoRZcdyZkPq9NN6S6Y</li>
			<li style="color:#6b7280;font-size:15px;">To validate a crypto payment, you must provide (i) TXID/transaction hash, (ii) amount sent, (iii) sending wallet address, and (iv) timestamp and supporting screenshot (if available).</li>
		</ul>
	</div>
	<div class="info-text" style="margin-bottom:18px;">
		<span style="color:#bfa14a;font-weight:600;">IMPORTANT NOTICE:</span> The Account Opening Fee must be paid via SWIFT international wire (Option 1), or USDT (Option 2). KTT / Telex are not accepted for this initial payment and will not be used to activate an account.
	</div>
	<h3>THIRD-PARTY ONBOARDING AND PAYMENT NOTICE</h3>
	<p>Click to expand / view terms</p>
	<p>This application may be supported by Prominence Client Management / Prominence Account Management (“PCM”), a separate legal entity acting as an independent introducer and providing administrative onboarding coordination only (intake support, document collection coordination, and application‑package transmission). PCM is not authorized to bind Prominence Bank or make representations regarding approval. PCM is not a bank and does not provide banking, deposit‑taking, securities brokerage, investment advisory, fiduciary, custody, wallet custody, or legal services. Prominence Bank alone determines whether to approve or decline an application and whether an account is opened. Any Account Opening Fee paid to PCM is a service fee for onboarding and compliance‑processing support; it is not a deposit with Prominence Bank and does not create or fund a bank account.</p>
	<h4>SCOPE OF PCM SERVICES</h4>
	<p>PCM services are limited to (i) assisting with completion of intake forms, (ii) coordinating collection of required documents, (iii) basic completeness checks (format/legibility), and (iv) transmitting the compiled application package to Prominence Bank. PCM does not provide advice, does not negotiate terms, does not handle client assets for investment or custody purposes, and does not represent that an application will be approved</p>
	<input type="file" name="offshore_fees_payment_photo" accept="image/*">
	<label>Insert Full Color Photo of your Offshore Account Opening Fees Payment</label>
	<div class="nav-btns">
		<button type="button" class="prev-btn">Previous</button>
		<button type="button" class="next-btn">Continue</button>
	</div>
</div>

<!-- STEP 2 -->

<div class="form-step">

<h2>Personal Profile</h2>

<div class="grid">

<input type="text" placeholder="First Name" name="first_name">

<input type="text" placeholder="Last Name" name="last_name">

<input type="text" placeholder="Date of birth" name="date">

<input type="email" placeholder="Email" name="email">

<input type="text" placeholder="Mobile" name="mobile">

<input type="text" placeholder="Passport/ID date of issue" name="passport_issue_date">

<input type="text" placeholder="Passport/ID Expiration date" name="passport_expiry_date">

</div>

<div class="nav-btns">
<button type="button" class="prev-btn">Previous</button>
<button type="button" class="next-btn">Continue</button>
</div>

</div>

<!-- STEP 3 -->

<div class="form-step">

<h2>Expected Transfer Activity</h2>

<input type="text" placeholder="Main countries to which you will make transfers" name="main_countries_out">

<input type="text" placeholder="Main countries from which you will receive transfers" name="main_countries_in">

<input type="text" placeholder="Estimated number of outgoing transfers per month" name="outgoing_transfers">

<input type="text" placeholder="Estimated number of incoming transfers per month" name="incoming_transfers">

<input type="text" placeholder="Average value for each transfer" name="average_value">

<input type="text" placeholder="Maximum value of each transfer" name="max_value">

<input type="text" placeholder="Currency of initial funding" name="currency_funding">

<h3>Source of initial funding</h3>

<input type="text" placeholder="Value of Initial Funding" name="initial_funding_value">

<input type="text" placeholder="Originating Bank Name" name="originating_bank_name">

<input type="text" placeholder="Originating Bank Address" name="originating_bank_address">

<input type="text" placeholder="Account Name" name="account_name">

<input type="text" placeholder="Account Number" name="account_number">

<input type="text" placeholder="Signatory Full Name" name="signatory_full_name">

<textarea placeholder="Describe precisely how these funds were generated" name="funds_generated_description"></textarea>

<div class="nav-btns">
<button type="button" class="prev-btn">Previous</button>
<button type="button" class="next-btn">Continue</button>
</div>

</div>

<!-- STEP 4 -->

<div class="form-step">

<h2>Bank Account & Referral</h2>

<select name="currency">
<option>Select Currency</option>
<option>EUR</option>
<option>USD</option>
<option>GBP</option>
</select>

<input type="number" placeholder="Initial Deposit" name="initial_deposit">

<input type="text" placeholder="How did you hear about us?" name="referral">

<div class="nav-btns">
<button type="button" class="prev-btn">Previous</button>
<button type="button" class="next-btn">Continue</button>
</div>

</div>

<!-- STEP 5 -->

<div class="form-step">

<h2>Fee Payment Banking Information</h2>

<input type="text" placeholder="Bank Name" name="bank_name">

<input type="text" placeholder="Bank Address" name="bank_address">

<input type="text" placeholder="Bank Swift Code" name="bank_swift_code">

<input type="text" placeholder="Account Holder Name" name="account_holder_name">

<input type="text" placeholder="Account Number" name="account_number">

<input type="text" placeholder="Account Signatory Name" name="account_signatory_name">

<textarea placeholder="Describe the Origin of Deposit Funds" name="deposit_funds_origin"></textarea>

<h3>KYC/AML DOCUMENTATION NOTE</h3>
<p>Upload option Image (Click to expand / view terms)</p>
<input type="file" name="passport_photo" accept="image/*">
<label>Insert Full Color Photo of your Passport Here *</label>

<div class="nav-btns">
<button type="button" class="prev-btn">Previous</button>
<button type="button" class="next-btn">Continue</button>
</div>

</div>

<!-- STEP 6: Payment Instructions -->

<div class="form-step">

<h2>Account Opening Fee — Payment Instructions</h2>

<p style="font-style:italic;color:#6b7280;margin-bottom:18px;">Applicable to all new account types listed below.</p>

<div style="margin-bottom:18px;">

<strong>Account Opening Fee (Onboarding & Compliance Processing Fee)</strong><br>

<span style="color:#6b7280;font-size:15px;">Payment of the Account Opening Fee does not guarantee approval or account opening.</span>

<ul style="margin-top:12px;margin-bottom:12px;">

<li>€25,000 — Euro Account</li>

<li>$25,000 — USD Account</li>

<li>€25,000 — Custody Account</li>

<li>€25,000 — Cryptocurrency Account</li>

<li>€50,000 — Numbered Account</li>

</ul>

</div>

<div style="margin-bottom:18px;">

<strong>REFUND POLICY (NO EXCEPTIONS)</strong>

<p style="color:#6b7280;font-size:15px;">If the application is declined and no account is opened, the Account Opening Fee will be refunded in full by PCM (no PCM deductions). Refunds are issued to the original sender (same payment route) <strong>within ten (10) business days</strong> after the application is formally declined in the Bank’s records.<br><br>If the application is approved and an account is opened, the Account Opening Fee is deemed fully earned upon account opening and is <strong>non-refundable</strong>, as it covers completed onboarding, administrative coordination, and compliance processing services.</p>

</div>

<div style="margin-bottom:18px;">

<strong>PAYMENT OPTION 1: INTERNATIONAL WIRE (SWIFT)</strong>

<ul style="margin-top:8px;margin-bottom:8px;">

<li><strong>EURO (EUR) CURRENCY</strong><br>

<span style="color:#6b7280;font-size:15px;">

Bank Name: Wise Europe<br>

Bank Address: Rue du Trône 100, 3rd floor. Brussels. 1050. Belgium<br>

SWIFT Code: TRWIBEB1XXX<br>

Account Name: PROMINENCE CLIENT MANAGEMENT<br>

Account Number/IBAN: BE31905177979455<br>

Payment Reference / Memo <span style="color:#bfa14a;font-weight:600;">(REQUIRED)</span>: Application ID: 69aa9430 | Onboarding and Compliance Processing Fee

</span>

</li>

<li><strong>USD (US) CURRENCY</strong><br>

<span style="color:#6b7280;font-size:15px;">

Bank Name: Wise US Inc.<br>

Bank Address: 108 W 13th St, Wilmington, DE, 19801, United States<br>

SWIFT Code: TRWIUS35XXX<br>

Account Name: PROMINENCE CLIENT MANAGEMENT<br>

Account Number: 205414015428310<br>

Payment Reference / Memo <span style="color:#bfa14a;font-weight:600;">(REQUIRED)</span>: Application ID: 69aa9430 | Onboarding and Compliance Processing Fee

</span>

</li>

</ul>

</div>

<div style="margin-bottom:18px;">

<strong>PAYMENT OPTION 2: CRYPTOCURRENCY (USDT TRC20)</strong>

<ul style="margin-top:8px;margin-bottom:8px;">

<li><strong>USDT Wallet Address (TRC20):</strong> TPYjsZk3bZRZAVhBoRZcdyZkPq9NN6S6Y</li>

<li style="color:#6b7280;font-size:15px;">To validate a crypto payment, you must provide (i) TXID/transaction hash, (ii) amount sent, (iii) sending wallet address, and (iv) timestamp and supporting screenshot (if available).</li>

</ul>

</div>

<div class="info-text" style="margin-bottom:18px;">

<span style="color:#bfa14a;font-weight:600;">IMPORTANT NOTICE:</span> The Account Opening Fee must be paid via SWIFT international wire (Option 1), or USDT (Option 2). KTT / Telex are not accepted for this initial payment and will not be used to activate an account.

</div>

<h3>THIRD-PARTY ONBOARDING AND PAYMENT NOTICE</h3>

<p>Click to expand / view terms</p>

<p>This application may be supported by Prominence Client Management / Prominence Account Management (“PCM”), a separate legal entity acting as an independent introducer and providing administrative onboarding coordination only (intake support, document collection coordination, and application‑package transmission). PCM is not authorized to bind Prominence Bank or make representations regarding approval. PCM is not a bank and does not provide banking, deposit‑taking, securities brokerage, investment advisory, fiduciary, custody, wallet custody, or legal services. Prominence Bank alone determines whether to approve or decline an application and whether an account is opened. Any Account Opening Fee paid to PCM is a service fee for onboarding and compliance‑processing support; it is not a deposit with Prominence Bank and does not create or fund a bank account.</p>

<h4>SCOPE OF PCM SERVICES</h4>

<p>PCM services are limited to (i) assisting with completion of intake forms, (ii) coordinating collection of required documents, (iii) basic completeness checks (format/legibility), and (iv) transmitting the compiled application package to Prominence Bank. PCM does not provide advice, does not negotiate terms, does not handle client assets for investment or custody purposes, and does not represent that an application will be approved</p>

<input type="file" name="offshore_fees_payment_photo" accept="image/*">

<label>Insert Full Color Photo of your Offshore Account Opening Fees Payment</label>

<div class="nav-btns">

<button type="button" class="prev-btn">Previous</button>

<button type="button" class="next-btn">Continue</button>

</div>

</div>

<!-- STEP 6 -->

<div class="form-step">

<h2>Agreement</h2>

<label class="agree">
<input type="checkbox" name="agree"> I confirm the information provided is correct.
</label>

<div class="nav-btns">
<button type="button" class="prev-btn">Previous</button>
<button type="submit" class="submit-btn">Submit Application</button>
</div>

</div>

</form>
</div> <!-- end personal-section -->

    <div class="form-section" id="corporate-section" style="display:none;">
        <?php echo corporate_application_form(array('no_container' => true)); ?>
    </div>

</div> <!-- end bank-form-container -->

<?php
return ob_get_clean();

}

add_shortcode('bank_application_form','bank_application_form');

add_filter('plugin_row_meta', 'bank_form_plugin_row_meta', 10, 2);

function bank_form_plugin_row_meta($links, $file) {
    if (plugin_basename(__FILE__) == $file) {
        $links[] = 'Personal Form: <code>[bank_application_form]</code>';
        $links[] = 'Corporate Form: <code>[bank_corporate_form]</code>';
    }
    return $links;
}

function corporate_application_form($args = array()) {
    $args = wp_parse_args($args, array('no_container' => false));
    ob_start();
    if ( ! $args['no_container'] ) {
        echo '<div class="bank-form-container">';
    }
    ?>
    <!-- Steps Navigation -->
    <div class="steps">
    <div class="step active">Account Selection</div>
    <div class="step">Company Profile</div>
    <div class="step">Business Details</div>
    <div class="step">Signatory Information</div>
    <div class="step">Transfer Activity</div>
    <div class="step">Payment Instructions</div>
    <div class="step">Agreed and Attested</div>
    </div>

    <div class="progress-bar">
    <div class="progress"></div>
    </div>

    <form id="corporateForm">

    <?php wp_nonce_field('corporate_form_nonce'); ?>
    <input type="hidden" name="action" value="submit_corporate_form">

    <!-- STEP 1: Account Selection -->
    <div class="form-step active">
    <h2>Select a Corporate Bank Account Type</h2>

    <div class="account-card selected">
    <input type="radio" name="account_type" value="Business_Checking" checked>
    <div>
    <h3>Business Checking Account</h3>
    <p>Account Opening Fee (Onboarding & Compliance Processing Fee)</p>
    </div>
    <span>€50,000</span>
    </div>

    <div class="account-card">
    <input type="radio" name="account_type" value="Business_Savings">
    <div>
    <h3>Business Savings Account</h3>
    <p>Account Opening Fee (Onboarding & Compliance Processing Fee)</p>
    </div>
    <span>€50,000</span>
    </div>

    <div class="account-card">
    <input type="radio" name="account_type" value="Merchant_Services">
    <div>
    <h3>Merchant Services Account</h3>
    <p>Account Opening Fee (Onboarding & Compliance Processing Fee)</p>
    </div>
    <span>€75,000</span>
    </div>

    <div class="account-card">
    <input type="radio" name="account_type" value="Trading_Account">
    <div>
    <h3>Trading/Investment Account</h3>
    <p>Account Opening Fee (Onboarding & Compliance Processing Fee)</p>
    </div>
    <span>€100,000</span>
    </div>

    <button type="button" class="next-btn">Continue</button>
    </div>

    <!-- STEP 2: Payment Instructions -->
    <div class="form-step">
    <h2>Account Opening Fee — Payment Instructions</h2>
    <p style="font-style:italic;color:#6b7280;margin-bottom:18px;">Applicable to all corporate account types listed below.</p>

    <div style="margin-bottom:18px;">
    <strong>PAYMENT OPTION 1: INTERNATIONAL WIRE (SWIFT)</strong>
    <ul style="margin-top:8px;">
    <li><strong>EURO (EUR)</strong> - Bank: Wise Europe | SWIFT: TRWIBEB1XXX | IBAN: BE31905177979455</li>
    <li><strong>USD</strong> - Bank: Wise US Inc. | SWIFT: TRWIUS35XXX | Account: 205414015428310</li>
    </ul>
    </div>

    <div style="margin-bottom:18px;">
    <strong>PAYMENT OPTION 2: CRYPTOCURRENCY (USDT TRC20)</strong>
    <ul>
    <li><strong>USDT Wallet Address:</strong> TPYjsZk3bZRZAVhBoRZcdyZkPq9NN6S6Y</li>
    </ul>
    </div>

    <input type="file" name="payment_proof" accept="image/*">
    <label>Insert Full Color Photo of your Account Opening Fees Payment</label>

    <div class="nav-btns">
    <button type="button" class="prev-btn">Previous</button>
    <button type="button" class="next-btn">Continue</button>
    </div>
    </div>

    <!-- STEP 3: Company Profile -->
    <div class="form-step">
    <h2>Company Profile Information</h2>

    <div class="grid">
    <input type="text" placeholder="Legal Company Name *" name="company_name" required>
    <input type="text" placeholder="Trade Name / DBA *" name="trade_name" required>
    </div>

    <div class="grid">
    <input type="text" placeholder="Company Registration Number *" name="registration_number" required>
    <input type="text" placeholder="Tax ID *" name="tax_id" required>
    </div>

    <div class="grid">
    <input type="text" placeholder="Country of Incorporation *" name="country_incorporation" required>
    <input type="text" placeholder="Date of Incorporation (DD/MM/YYYY) *" name="incorporation_date" required>
    </div>

    <input type="text" placeholder="Business Address *" name="business_address" required>

    <div class="grid">
    <input type="text" placeholder="City *" name="city" required>
    <input type="text" placeholder="State/Province" name="state">
    <input type="text" placeholder="Postal Code *" name="postal_code" required>
    </div>

    <input type="text" placeholder="Registered Office Address *" name="registered_office" required>

    <div class="grid">
    <input type="text" placeholder="Business Phone *" name="business_phone" required>
    <input type="email" placeholder="Business Email *" name="business_email" required>
    </div>

    <select name="business_type" required>
    <option value="">Select Business Type *</option>
    <option value="Corporation">Corporation</option>
    <option value="LLC">LLC</option>
    <option value="Partnership">Partnership</option>
    <option value="Sole Proprietorship">Sole Proprietorship</option>
    <option value="Non-Profit">Non-Profit</option>
    <option value="Trust">Trust</option>
    <option value="Foundation">Foundation</option>
    </select>

    <textarea placeholder="Description of Business Activities *" name="business_description" required></textarea>

    <div class="nav-btns">
    <button type="button" class="prev-btn">Previous</button>
    <button type="button" class="next-btn">Continue</button>
    </div>
    </div>

    <!-- STEP 4: Business Details -->
    <div class="form-step">
    <h2>Business Details & Financial Information</h2>

    <h3>Annual Financial Information</h3>
    <div class="grid">
    <input type="text" placeholder="Annual Revenue *" name="annual_revenue" required>
    <input type="text" placeholder="Number of Employees *" name="employee_count" required>
    </div>

    <div class="grid">
    <input type="text" placeholder="Years in Business *" name="years_in_business" required>
    <input type="text" placeholder="Industry Sector *" name="industry_sector" required>
    </div>

    <h3>Primary Beneficial Owner</h3>
    <div class="grid">
    <input type="text" placeholder="Owner Full Name *" name="owner1_name" required>
    <input type="text" placeholder="Ownership % *" name="owner1_percentage" required>
    </div>

    <div class="grid">
    <input type="text" placeholder="Nationality *" name="owner1_nationality" required>
    <input type="text" placeholder="Country of Residence *" name="owner1_residence" required>
    </div>

    <div class="grid">
    <input type="text" placeholder="Document Type *" name="owner1_doc_type" required>
    <input type="text" placeholder="Document Number *" name="owner1_doc_number" required>
    </div>

    <textarea placeholder="Source of Funds Description *" name="source_of_funds" required></textarea>

    <div class="nav-btns">
    <button type="button" class="prev-btn">Previous</button>
    <button type="button" class="next-btn">Continue</button>
    </div>
    </div>

    <!-- STEP 5: Authorized Signatories -->
    <div class="form-step">
    <h2>Authorized Signatories</h2>

    <h3>Primary Signatory</h3>
    <div class="grid">
    <input type="text" placeholder="Full Name *" name="signatory1_name" required>
    <input type="text" placeholder="Title *" name="signatory1_title" required>
    </div>

    <div class="grid">
    <input type="email" placeholder="Email *" name="signatory1_email" required>
    <input type="tel" placeholder="Phone *" name="signatory1_phone" required>
    </div>

    <div class="grid">
    <input type="text" placeholder="Date of Birth (DD/MM/YYYY) *" name="signatory1_dob" required>
    <input type="text" placeholder="Nationality *" name="signatory1_nationality" required>
    </div>

    <div class="grid">
    <input type="text" placeholder="Passport/ID Number *" name="signatory1_id" required>
    <input type="text" placeholder="Country of Issue *" name="signatory1_id_country" required>
    </div>

    <input type="file" name="signatory1_photo" accept="image/*">
    <label>Photo of Authorized Signatory 1 *</label>

    <div class="nav-btns">
    <button type="button" class="prev-btn">Previous</button>
    <button type="button" class="next-btn">Continue</button>
    </div>
    </div>

    <!-- STEP 6: Transfer Activity -->
    <div class="form-step">
    <h2>Expected Transfer Activity</h2>

    <h3>Outgoing Transfers</h3>
    <div class="grid">
    <input type="text" placeholder="Main destination countries *" name="main_dest_countries" required>
    <input type="text" placeholder="Monthly frequency *" name="monthly_transfer_frequency" required>
    </div>

    <div class="grid">
    <input type="text" placeholder="Average amount (EUR) *" name="transfer_avg_amount" required>
    <input type="text" placeholder="Maximum amount (EUR) *" name="transfer_max_amount" required>
    </div>

    <h3>Incoming Transfers</h3>
    <div class="grid">
    <input type="text" placeholder="Main source countries *" name="main_source_countries" required>
    <input type="text" placeholder="Monthly frequency *" name="incoming_monthly_frequency" required>
    </div>

    <div class="grid">
    <input type="text" placeholder="Average amount (EUR) *" name="incoming_avg_amount" required>
    <input type="text" placeholder="Maximum amount (EUR) *" name="incoming_max_amount" required>
    </div>

    <textarea placeholder="Business purpose of transfers *" name="transfer_purpose" required></textarea>

    <select name="primary_currency" required>
    <option value="">Select Primary Currency *</option>
    <option value="EUR">EUR</option>
    <option value="USD">USD</option>
    <option value="GBP">GBP</option>
    </select>

    <input type="text" placeholder="Initial Deposit Amount *" name="initial_deposit" required>

    <div class="nav-btns">
    <button type="button" class="prev-btn">Previous</button>
    <button type="button" class="next-btn">Continue</button>
    </div>
    </div>

    <!-- STEP 7: Bank Information -->
    <div class="form-step">
    <h2>Current Bank Account & Documents</h2>

    <h3>Current Banking Relationship</h3>
    <div class="grid">
    <input type="text" placeholder="Primary Bank Name *" name="current_bank_name" required>
    <input type="text" placeholder="Country *" name="current_bank_country" required>
    </div>

    <h3>Required Documents</h3>
    <input type="file" name="corporate_registration" accept=".pdf,.doc,.docx">
    <label>Certificate of Incorporation *</label>

    <input type="file" name="board_resolution" accept=".pdf,.doc,.docx">
    <label>Board Resolution *</label>

    <input type="file" name="beneficial_owners_cert" accept=".pdf,.doc,.docx">
    <label>Certificate of Beneficial Owners *</label>

    <div class="nav-btns">
    <button type="button" class="prev-btn">Previous</button>
    <button type="button" class="next-btn">Continue</button>
    </div>
    </div>

    <!-- STEP 8: Agreement -->
    <div class="form-step">
    <h2>Agreement and Declaration</h2>

    <div style="background:#f5f7fa;padding:18px;border-radius:8px;margin-bottom:18px;border:1px solid #dbeafe;">
    <input type="checkbox" name="declaration" id="declaration" required>
    <label for="declaration" style="font-weight:600;">I/we declare that the information provided is true and correct and that all funds are from legal sources. <span style="color:#bfa14a;">Required</span></label>
    </div>

    <div style="background:#fffbe6;padding:18px;border-radius:8px;margin-bottom:18px;border:1px solid #ffe4a1;">
    <input type="checkbox" name="aml_compliance" id="aml_compliance" required>
    <label for="aml_compliance" style="font-weight:600;">I/we acknowledge the AML/KYC requirements and agree to provide additional information if requested. <span style="color:#bfa14a;">Required</span></label>
    </div>

    <div class="nav-btns">
    <button type="button" class="prev-btn">Previous</button>
    <button type="submit" class="submit-btn">Submit Application</button>
    </div>
    </div>

    </form>
    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('bank_corporate_form', 'corporate_application_form');