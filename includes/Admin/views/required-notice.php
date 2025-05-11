<?php
/*
STYLE GUIDE FOR WP SUBSCRIPTION ADMIN PAGES:
- Use .wp-subscription-admin-content for main content area.
- Use .wp-subscription-admin-box for white card/box with shadow and 6-8px border-radius.
- Use compact, modern, visually unified design for all sections.
- Use Georgia, serif for titles, system sans-serif for body.
- All new UI/UX changes must follow these conventions.
*/
?>
<div class="wp-subscription-admin-box" style="margin-bottom:18px;display:flex;align-items:center;gap:18px;justify-content:space-between;">
	<div>
		<h3 style="font-family:Georgia,serif;font-size:1.1em;margin:0 0 6px 0;">Upgrade to Pro</h3>
		<p style="margin:0 0 6px 0;font-size:14px;color:#444;max-width:340px;">Unlock advanced features, analytics, and premium support with WP Subscription Pro. Take your subscriptions to the next level!</p>
		<a href="https://wpsubscription.co" target="_blank" class="button button-primary" style="font-size:14px;padding:7px 18px;">Upgrade Now</a>
	</div>
	<img src="<?php echo esc_url( WP_SUBSCRIPTION_ASSETS . '/images/subscrpt-ads.png' ); ?>" alt="Upgrade to Pro" style="max-width:120px;border-radius:8px;box-shadow:0 1px 6px rgba(0,0,0,0.06);background:#f6f7f9;" />
</div>
